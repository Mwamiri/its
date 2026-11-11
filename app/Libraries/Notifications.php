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
                self::domains($user),
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

    private static function domains(array $user): array
    {
        if (($user['role'] ?? '') !== 'admin') return [];
        $db = db_connect();
        if (! $db->tableExists('watched_domains')) return [];
        $rows = $db->table('watched_domains')->get()->getResultArray();
        $expiring = 0;
        foreach ($rows as $row) {
            try {
                $ctx  = stream_context_create(['ssl' => ['capture_peer_cert' => true, 'verify_peer' => false, 'verify_peer_name' => false]]);
                $sock = @stream_socket_client("ssl://{$row['domain']}:443", $errno, $errstr, 3, STREAM_CLIENT_CONNECT, $ctx);
                if (!$sock) continue;
                $info = stream_context_get_params($sock);
                fclose($sock);
                $cert = openssl_x509_parse($info['options']['ssl']['peer_certificate'] ?? null);
                $days = $cert ? (int) ceil(((int)($cert['validTo_time_t'] ?? 0) - time()) / 86400) : null;
                if ($days !== null && $days <= 30) $expiring++;
            } catch (\Throwable) {}
        }
        if ($expiring > 0) {
            return [['icon' => 'shield-exclamation', 'tone' => 'danger', 'title' => "{$expiring} SSL cert(s) expiring soon", 'text' => 'Check System → Health for details', 'url' => base_url('its-health#domains')]];
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
