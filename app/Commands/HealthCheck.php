<?php

namespace App\Commands;

use App\Libraries\Backup;
use App\Libraries\Locks;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Throwable;

/**
 * php spark its:health   — run daily with Task Scheduler or cron
 *
 * Checks core requirements, takes an automatic backup if none was made in the last 24 hours,
 * purges expired record locks and cleans leftover session/cache files.
 */
class HealthCheck extends BaseCommand
{
    protected $group       = 'ITSupport';
    protected $name        = 'its:health';
    protected $description = 'Health checks, auto-backup and housekeeping. Schedule daily with cron or Task Scheduler.';
    protected $usage       = 'its:health [--no-repair]';
    protected $options     = ['--no-repair' => 'Only report, do not fix anything.'];

    public function run(array $params)
    {
        $repair  = ! CLI::getOption('no-repair');
        $failed  = 0;
        $db      = db_connect();

        // --- Checks ---
        $checks = [
            ['PHP version ≥ 8.2',    PHP_VERSION_ID >= 80200,     PHP_VERSION],
            ['Writable writable/',    is_writable(WRITEPATH),      WRITEPATH],
            ['Writable uploads/',     is_writable(FCPATH . 'uploads'), FCPATH . 'uploads'],
            ['Database connected',    (bool) $db,                  $db ? $db->getDatabase() : 'n/a'],
            ['GD extension',          extension_loaded('gd'),      'images'],
            ['intl extension',        extension_loaded('intl'),    'localisation'],
            ['mbstring extension',    extension_loaded('mbstring'), 'strings'],
            ['ZipArchive',            class_exists('ZipArchive'),  'backups & updates'],
        ];

        // Pending DB migrations
        try {
            $pending = count(service('migrations')->findAny()) - count(service('migrations')->history());
            $checks[] = ['No pending migrations', $pending === 0, $pending > 0 ? "{$pending} pending" : 'up to date'];
        } catch (Throwable) {
        }

        foreach ($checks as [$name, $ok, $info]) {
            CLI::write(str_pad($name, 40) . ($ok ? CLI::color('OK  ', 'green') : CLI::color('FAIL', 'red')) . '  ' . $info);
            $failed += $ok ? 0 : 1;
        }

        if (! $repair) {
            CLI::write($failed ? "{$failed} check(s) need attention." : 'All checks passed.', $failed ? 'red' : 'green');
            return EXIT_SUCCESS;
        }

        // --- Auto-backup ---
        $latest = Backup::latest();
        if (! $latest || time() - $latest['time'] > 86400) {
            $file = Backup::run(14);
            CLI::write($file ? 'backup:  ' . basename($file) : 'backup:  FAILED', $file ? 'green' : 'red');
        } else {
            CLI::write('backup:  current (' . basename($latest['file']) . ')', 'yellow');
        }

        // --- Housekeeping ---
        Locks::purgeExpired();

        // Clean expired login attempts
        $db->table('login_attempts')->where('attempted_at <', date('Y-m-d H:i:s', strtotime('-1 hour')))->delete();

        // Clear old cache files
        try {
            cache()->clean();
        } catch (Throwable) {
        }

        CLI::write($failed ? "{$failed} check(s) need attention." : 'All checks passed.', $failed ? 'red' : 'green');
        return EXIT_SUCCESS;
    }
}
