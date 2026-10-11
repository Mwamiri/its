<?php

namespace App\Libraries;

use App\Models\SettingModel;
use RuntimeException;
use ZipArchive;

/**
 * Checks every uploaded file before it is kept, so only clean files are stored:
 *  - the real content type must match the extension (a renamed .exe/.php is refused)
 *  - images are re-encoded (removes hidden payloads, EXIF and GPS data); undecodable images are refused
 *  - script code (PHP, HTML/JS) hidden in a file is refused
 *  - PDFs with embedded JavaScript or auto-run actions are refused
 *  - Office files containing macros are refused
 *  - optional virus scan with ClamAV when the server has it (Settings → Security → ClamAV path)
 */
class UploadScanner
{
    /** extension => accepted real MIME types */
    private const MIMES = [
        'jpg'  => ['image/jpeg'], 'jpeg' => ['image/jpeg'], 'png' => ['image/png'], 'webp' => ['image/webp'],
        'pdf'  => ['application/pdf'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/octet-stream'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip', 'application/octet-stream'],
        'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/zip', 'application/octet-stream'],
        'odt'  => ['application/vnd.oasis.opendocument.text', 'application/zip'],
        'ods'  => ['application/vnd.oasis.opendocument.spreadsheet', 'application/zip'],
        'zip'  => ['application/zip', 'application/x-zip-compressed'],
        'doc'  => ['application/msword', 'application/CDFV2', 'application/vnd.ms-office', 'application/octet-stream'],
        'xls'  => ['application/vnd.ms-excel', 'application/CDFV2', 'application/vnd.ms-office', 'application/octet-stream'],
        'ppt'  => ['application/vnd.ms-powerpoint', 'application/CDFV2', 'application/vnd.ms-office', 'application/octet-stream'],
        'csv'  => ['text/csv', 'text/plain', 'application/csv', 'text/x-csv'],
        'txt'  => ['text/plain'],
    ];

    /** Patterns that never belong in a document or picture */
    private const SCRIPT_PATTERNS = ['/<\?php/i', '/<script\b/i', '/<%@\s*page/i', '/\beval\s*\(\s*base64_decode/i'];

    /**
     * @throws RuntimeException when the file is not clean (the caller must delete it)
     */
    public static function check(string $path, string $ext): void
    {
        $ext  = strtolower($ext);
        $mime = (string) (new \finfo(FILEINFO_MIME_TYPE))->file($path);

        if (isset(self::MIMES[$ext]) && ! in_array($mime, self::MIMES[$ext], true)) {
            throw new RuntimeException("The file content ({$mime}) does not match its .{$ext} extension.");
        }

        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            self::reencodeImage($path, $ext);
        } elseif ($ext === 'pdf') {
            self::checkPdf($path);
        } elseif (in_array($ext, ['docx', 'xlsx', 'pptx', 'odt', 'ods', 'zip'], true)) {
            self::checkZip($path, $ext);
        } elseif (in_array($ext, ['doc', 'xls', 'ppt'], true)) {
            // Old Office files keep macros in a "_VBA_PROJECT" / "Macros" stream (names stored as UTF-16)
            $data = (string) file_get_contents($path);
            if (str_contains($data, mb_convert_encoding('_VBA_PROJECT', 'UTF-16LE')) || str_contains($data, mb_convert_encoding('Macros', 'UTF-16LE'))) {
                throw new RuntimeException('The document contains macros and was refused. Save it as .docx/.xlsx without macros.');
            }
        } else {
            self::checkScripts((string) file_get_contents($path, false, null, 0, 5 * 1048576));
        }

        self::clamav($path);
    }

    private static function checkScripts(string $content): void
    {
        foreach (self::SCRIPT_PATTERNS as $p) {
            if (preg_match($p, $content)) {
                throw new RuntimeException('The file contains program code and was refused.');
            }
        }
    }

    /**
     * Decode and save the picture again: only pixels survive, any hidden content is dropped.
     */
    private static function reencodeImage(string $path, string $ext): void
    {
        $data = (string) file_get_contents($path);
        $img  = @imagecreatefromstring($data);
        if (! $img) {
            throw new RuntimeException('The picture could not be read — it may be damaged or not a real image.');
        }
        $ok = match ($ext) {
            'png'  => imagepng($img, $path, 6),
            'webp' => imagewebp($img, $path, 80),
            default => imagejpeg($img, $path, 85),
        };
        imagedestroy($img);
        if (! $ok) {
            throw new RuntimeException('The picture could not be processed.');
        }
    }

    private static function checkPdf(string $path): void
    {
        $data = (string) file_get_contents($path);
        if (! str_starts_with(ltrim(substr($data, 0, 1024)), '%PDF-')) {
            throw new RuntimeException('This is not a valid PDF file.');
        }
        // Active content: JavaScript, auto-run actions, launching programs, embedded files
        if (preg_match('#/(JavaScript|JS|Launch|OpenAction\s*<<[^>]*/S\s*/JavaScript|EmbeddedFile)\b#', $data)) {
            throw new RuntimeException('The PDF contains active content (scripts, auto-run actions or embedded files) and was refused. Print it to a new PDF and upload that.');
        }
        self::checkScripts(substr($data, 0, 4096));
    }

    private static function checkZip(string $path, string $ext): void
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('The file is damaged or not a valid ' . strtoupper($ext) . ' file.');
        }
        try {
            if ($zip->numFiles > 5000) {
                throw new RuntimeException('The archive has too many files.');
            }
            $total = 0;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $st    = $zip->statIndex($i);
                $name  = strtolower($st['name']);
                $total += $st['size'];
                if ($total > 500 * 1048576) {
                    throw new RuntimeException('The archive expands to more than 500 MB (possible zip bomb).');
                }
                if (str_contains($name, 'vbaproject.bin') || str_contains($name, 'macros/')) {
                    throw new RuntimeException('The document contains macros and was refused. Save it without macros (.docx/.xlsx) and upload again.');
                }
                if (preg_match('/\.(php\d?|phtml|phar|exe|dll|bat|cmd|com|scr|js|vbs|ps1|sh|jar|msi|hta)$/', $name)) {
                    throw new RuntimeException('The archive contains a program file (' . basename($st['name']) . ') and was refused.');
                }
            }
        } finally {
            $zip->close();
        }
    }

    /**
     * Virus scan when a ClamAV scanner is configured (Settings → Security → ClamAV binary path).
     */
    private static function clamav(string $path): void
    {
        $bin = trim((string) SettingModel::get('clamav_path', ''));
        if ($bin === '' || ! function_exists('exec') || ! is_file($bin)) {
            return;
        }
        $out  = [];
        $code = 0;
        @exec(escapeshellarg($bin) . ' --no-summary ' . escapeshellarg($path) . ' 2>&1', $out, $code);
        if ($code === 1) {
            throw new RuntimeException('The virus scanner found a threat in this file: ' . trim(implode(' ', $out)));
        }
    }
}
