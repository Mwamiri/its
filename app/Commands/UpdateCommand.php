<?php

namespace App\Commands;

use App\Libraries\SystemUpdater;
use App\Models\SettingModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Throwable;

/**
 * php spark its:update              show the current version and inbox packages
 * php spark its:update check        check the configured manifest feed for a newer version
 * php spark its:update apply <name> apply a named package from writable/updates/
 */
class UpdateCommand extends BaseCommand
{
    protected $group       = 'ITSupport';
    protected $name        = 'its:update';
    protected $description = 'Show the current version, check the feed, or apply an update package from writable/updates/.';
    protected $usage       = 'its:update [check|apply <package>]';

    public function run(array $params)
    {
        $u = new SystemUpdater();
        CLI::write('ITSupportz ' . $u->version());

        try {
            switch ($params[0] ?? '') {
                case 'check':
                    $url = SettingModel::get('update_manifest_url', '');
                    if ($url === '') {
                        CLI::error('No manifest URL configured (System → Updates).');
                        return EXIT_ERROR;
                    }
                    $http = service('curlrequest', ['timeout' => 10, 'http_errors' => false]);
                    $feed = json_decode((string) $http->get($url)->getBody(), true);
                    if (! is_array($feed) || ! isset($feed['version'])) {
                        CLI::error('Could not read the feed or it is malformed.');
                        return EXIT_ERROR;
                    }
                    if (version_compare($feed['version'], $u->version(), '>')) {
                        CLI::write("Version {$feed['version']} is available. Download it from System → Updates.", 'green');
                    } else {
                        CLI::write('Up to date.', 'green');
                    }
                    break;

                case 'apply':
                    $name = (string) ($params[1] ?? '');
                    $path = $name !== '' ? $u->inboxPath($name) : null;
                    if (! $path) {
                        CLI::error("Package not found: {$name}. Run without arguments to see what is available.");
                        return EXIT_ERROR;
                    }
                    $zip  = str_ends_with(strtolower($path), '.zip') ? $path : $u->folderToZip($path);
                    $meta = [];
                    $z    = new \ZipArchive();
                    if ($z->open($zip) === true) { $meta = json_decode((string) $z->getFromName('update.json'), true) ?: []; $z->close(); }
                    $sha     = (string) ($meta['sha256'] ?? hash_file('sha256', $zip));
                    $version = (string) ($meta['version'] ?? '');
                    $db      = \Config\Database::connect();
                    $lines   = $u->applyPackage($zip, $sha, $version, $db);
                    foreach ($lines as $line) {
                        CLI::write($line, 'green');
                    }
                    break;

                default:
                    $inbox = $u->inbox();
                    if (! $inbox) {
                        CLI::write('Nothing in writable/updates/. Copy a package there or upload it in System → Updates.', 'yellow');
                    } else {
                        foreach ($inbox as $p) {
                            $flag = $p['newer'] ? CLI::color('newer', 'green') : '';
                            CLI::write(str_pad($p['name'], 42) . str_pad($p['version'], 10) . $flag . ($p['error'] ? CLI::color('  ' . $p['error'], 'red') : ''));
                        }
                    }
            }
        } catch (Throwable $e) {
            CLI::error($e->getMessage());
            return EXIT_ERROR;
        }

        return EXIT_SUCCESS;
    }
}
