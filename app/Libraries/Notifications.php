<?php

namespace App\Libraries;

use App\Models\SettingModel;
use Throwable;

/**
 * Items for the top-bar bell: overdue tickets, pending approvals, quote actions and
 * system warnings for administrators. Cached in the session for 5 minutes.
 */
class Notifications
{
    public static function forCurrentUser(array $user): array
    {
        $cached = session('notif_cache');
        if ($cached && time() - $cached['at'] < 300) {
            return $cached['items'];
        }
        $items = [];
        try {
            $items = array_merge(
                self::tickets($user),
                self::quotes($user),
                self::tasks($user),
                self::system($user),
            );
        } catch (Throwable $e) {
            log_message('error', 'Notifications: ' . $e->getMessage());
        }
        session()->set('notif_cache', ['at' => time(), 'items' => $items]);
        return $items;
    }

    public static function flush(): void
    {
        session()->remove('notif_cache');
    }

    private static function tickets(array $user): array
    {
        $out = [];
        $db  = db_connect();
        $q   = $db->table('tickets')->where('status', 'open');
        if (($user['role'] ?? '') === 'tech') {
            $q->where('assigned_to', $user['id']);
        }
        $overdue = (clone $q)->where('sla_due <', date('Y-m-d H:i:s'))->countAllResults();
        if ($overdue > 0) {
            $out[] = ['icon' => 'clock', 'tone' => 'danger', 'title' => "{$overdue} ticket(s) past SLA", 'text' => 'Overdue tickets need attention', 'url' => base_url('its-tickets?filter=overdue')];
        }
        return $out;
    }

    private static function quotes(array $user): array
    {
        if (! in_array($user['role'] ?? '', ['admin', 'tech'], true)) {
            return [];
        }
        $pending = db_connect()->table('quotes')->where('status', 'approved')->countAllResults();
        if ($pending > 0) {
            return [['icon' => 'tag', 'tone' => 'brand', 'title' => "{$pending} quote(s) approved", 'text' => 'Ready to invoice or close', 'url' => base_url('its-quotes?status=approved')]];
        }
        return [];
    }

    private static function tasks(array $user): array
    {
        $waiting = db_connect()->table('tasks')->where('status', 'waiting_approval')->countAllResults();
        if ($waiting > 0) {
            return [['icon' => 'check', 'tone' => 'warning', 'title' => "{$waiting} task(s) waiting approval", 'text' => 'Review and approve to close the ticket', 'url' => base_url('its-tickets')]];
        }
        return [];
    }

    private static function system(array $user): array
    {
        if (($user['role'] ?? '') !== 'admin') {
            return [];
        }
        $out = [];
        // Backup age
        $files = glob(WRITEPATH . 'backups/*.zip') ?: [];
        if (! $files) {
            $out[] = ['icon' => 'database', 'tone' => 'warning', 'title' => 'No backup found', 'text' => 'Run a backup from System → Backups', 'url' => base_url('its-admin?tab=backup')];
        } else {
            $newest = max(array_map('filemtime', $files));
            if (time() - $newest > 3 * 86400) {
                $out[] = ['icon' => 'database', 'tone' => 'warning', 'title' => 'No recent backup', 'text' => 'Last backup ' . date('d M Y', $newest), 'url' => base_url('its-admin?tab=backup')];
            }
        }
        // Pending DB migrations
        try {
            $migrations = service('migrations');
            $pending    = count($migrations->findAny()) - count($migrations->history());
            if ($pending > 0) {
                $out[] = ['icon' => 'server', 'tone' => 'warning', 'title' => 'Database update pending', 'text' => "{$pending} migration(s) waiting", 'url' => base_url('its-admin')];
            }
        } catch (Throwable) {
        }
        // Maintenance mode
        if (SettingModel::get('maintenance_mode', '0') === '1') {
            $out[] = ['icon' => 'wrench', 'tone' => 'danger', 'title' => 'Maintenance mode is on', 'text' => 'Only admins can sign in', 'url' => base_url('its-admin')];
        }
        return $out;
    }
}
