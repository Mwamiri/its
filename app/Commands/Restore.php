<?php

namespace App\Commands;

use App\Libraries\SystemUpdater;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Emergency recovery when the web interface is down (for example after a bad update):
 *   php spark its:restore            lists code backups
 *   php spark its:restore <file>     restores the application files from that backup
 * Database backups (db-*.sql.gz) are listed for reference; restore those with your database tool if needed.
 */
class Restore extends BaseCommand
{
    protected $group       = 'ITSupport';
    protected $name        = 'its:restore';
    protected $usage       = 'its:restore [code-backup.zip]';
    protected $description = 'Restores application files from a backup in writable/backups (works even if the website is down).';

    public function run(array $params)
    {
        $u = new SystemUpdater();
        $backups = $u->backups();
        $name = $params[0] ?? '';
        if ($name === '') {
            CLI::write('Backups in writable/backups (newest first):', 'yellow');
            foreach ($backups as $b) CLI::write('  ' . ($b['name'] ?? '') . '  ' . ($b['time'] ?? '') . '  ' . number_format(((int) ($b['size'] ?? 0)) / 1048576, 1) . ' MB');
            CLI::write('Restore files with: php spark its:restore code-YYYYMMDD-HHMMSS-....zip');
            return EXIT_SUCCESS;
        }
        $path = $u->backupPath($name);
        if (! $path || ! str_starts_with($name, 'code-')) {
            CLI::error('Choose a code-*.zip backup from the list (run without arguments to see it).');
            return EXIT_ERROR;
        }
        SystemUpdater::longRunning();
        $u->maintenance(true);
        try {
            $done = $u->restoreFromZip($path);
        } finally {
            $u->maintenance(false);
        }
        $u->recordBaseline();
        $u->log('Emergency restore from ' . $name . ' via CLI (' . count($done) . ' files)');
        CLI::write('Restored ' . count($done) . ' files from ' . $name . '. Installed version is now ' . $u->version() . '.', 'green');
        CLI::write('If that backup is older than the database schema, roll back with: php spark migrate:rollback');
        return EXIT_SUCCESS;
    }
}
