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

    /** Files each installation configures for itself; an update must never overwrite them. */
    private const SITE_FILES = [
        'public/index.php', 'public/.htaccess', 'public/robots.txt',
        'app/Config/App.php', 'app/Config/Database.php', 'app/Config/Email.php', 'app/Config/Encryption.php',
        'app/Config/Cookie.php', 'app/Config/Session.php', 'app/Config/Security.php', 'app/Config/Cache.php',
        'app/Config/Logger.php', 'app/Config/Paths.php', 'app/Config/Constants.php', 'app/Config/Hostnames.php',
    ];

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
        $skipped = [];
        foreach (['app', 'public'] as $entry) {
            $path = $root . $entry;
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS));
            foreach ($files as $f) {
                if (! $f->isFile()) {
                    continue;
                }
                $rel = str_replace('\\', '/', substr($f->getPathname(), strlen($root)));
                if (! $u->allowedPath($rel)) {
                    continue;
                }
                // Never ship per-site files: they hold the server's own paths, URLs and secrets.
                if (in_array($rel, self::SITE_FILES, true) || str_starts_with($rel, 'app/Config/Boot/') || preg_match('/\.(bak|orig|swp|log)$|~$/', $rel)) {
                    $skipped[] = $rel;
                    continue;
                }
                $zip->addFile($f->getPathname(), $rel);
                $count++;
            }
        }
        $zip->addFromString('VERSION', $version . "\n");
        // Read by v3+ updaters before anything is changed; older updaters ignore it.
        $zip->addFromString('update.json', json_encode(['version' => $version, 'min_php' => '8.2.0', 'built' => date('c'), 'remove' => []], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
        if ($skipped) CLI::write('Kept site-specific files out of the package: ' . implode(', ', $skipped), 'yellow');
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