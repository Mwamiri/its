<?php

namespace App\Commands;

use App\Libraries\SystemUpdater;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ReleaseBuild extends BaseCommand
{
    protected $group       = 'ITSupport';
    protected $name        = 'release:build';
    protected $usage       = 'release:build <version> [base-url] [notes]';
    protected $description = 'Builds writable/releases/itsupport-<version>.zip and manifest.json for hosting over HTTPS.';

    public function run(array $params)
    {
        $version = $params[0] ?? '';
        if (! preg_match('/^\d+\.\d+\.\d+$/', $version)) {
            CLI::error('Usage: php spark release:build <x.y.z> [https://host/path] [notes]');
            return EXIT_ERROR;
        }
        $base  = rtrim($params[1] ?? 'https://example.com/itsupport', '/');
        $notes = $params[2] ?? "Release $version";
        $u     = new SystemUpdater();
        $root  = ROOTPATH;
        $dir   = WRITEPATH . 'releases';
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $zipPath = $dir . DIRECTORY_SEPARATOR . "itsupport-$version.zip";
        @unlink($zipPath);
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
            CLI::error('Cannot create ZIP.');
            return EXIT_ERROR;
        }
        $count = 0;
        foreach (['app', 'public', 'composer.json', 'spark'] as $entry) {
            $path = $root . $entry;
            $files = is_dir($path) ? new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)) : [new \SplFileInfo($path)];
            foreach ($files as $f) {
                if (! $f->isFile()) {
                    continue;
                }
                $rel = str_replace('\\', '/', substr($f->getPathname(), strlen($root)));
                if (! $u->allowedPath($rel)) {
                    continue;
                }
                $zip->addFile($f->getPathname(), $rel);
                $count++;
            }
        }
        $zip->addFromString('VERSION', $version . "\n");
        $zip->close();
        $sha = hash_file('sha256', $zipPath);
        $manifest = ['version' => $version, 'url' => "$base/itsupport-$version.zip", 'sha256' => $sha, 'notes' => $notes];
        file_put_contents($dir . DIRECTORY_SEPARATOR . 'manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
        CLI::write("Built $zipPath ($count files)", 'green');
        CLI::write("sha256 $sha");
        CLI::write('Upload the ZIP and manifest.json to ' . $base . '/ and set the manifest URL on the Updates page.');
        return EXIT_SUCCESS;
    }
}