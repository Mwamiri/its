<?php

namespace App\Commands;

use App\Libraries\Backup;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class BackupRun extends BaseCommand
{
    protected $group       = 'ITSupport';
    protected $name        = 'backup:run';
    protected $usage       = 'backup:run [keep]';
    protected $description = 'Writes a gzip database backup to writable/backups and keeps the newest N (default 14). Schedule daily with Task Scheduler or cron.';

    public function run(array $params)
    {
        $file = Backup::run((int) ($params[0] ?? 14));
        if (! $file) { CLI::error('Backup failed.'); return EXIT_ERROR; }
        CLI::write('Backup written: ' . $file, 'green');
        return EXIT_SUCCESS;
    }
}