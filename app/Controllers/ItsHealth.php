<?php
namespace App\Controllers;

class ItsHealth extends ItsBase {
    public function index() {
        if ($r = $this->needAdmin()) return $r;
        $free = @disk_free_space(WRITEPATH); $total = @disk_total_space(WRITEPATH);
        $one = fn(string $sql) => (int) ($this->db->query($sql)->getRow()->n ?? 0);
        $checks = [
            ['PHP version', PHP_VERSION, version_compare(PHP_VERSION, '8.1', '>=')],
            ['CodeIgniter', \CodeIgniter\CodeIgniter::CI_VERSION, true],
            ['App version', trim((string) @file_get_contents(FCPATH . '../VERSION')) ?: 'n/a', true],
            ['Environment', ENVIRONMENT, ENVIRONMENT === 'production'],
            ['HTTPS', $this->request->isSecure() ? 'on' : 'off', $this->request->isSecure()],
            ['Writable folder', is_writable(WRITEPATH) ? 'writable' : 'NOT writable', is_writable(WRITEPATH)],
            ['Disk free', $total ? round($free / 1073741824, 1) . ' GB of ' . round($total / 1073741824, 1) . ' GB' : 'unknown', $total && $free / $total > 0.1],
            ['Extensions', implode(', ', array_filter(['mysqli', 'curl', 'mbstring', 'zip', 'openssl', 'intl'], 'extension_loaded')), extension_loaded('zip') && extension_loaded('openssl') && extension_loaded('mbstring')],
            ['Database', $this->db->getVersion(), true],
            ['Open tickets', (string) $one("SELECT COUNT(*) n FROM tickets WHERE status NOT IN ('completed','closed')"), true],
            ['SLA breached (open)', (string) $one("SELECT COUNT(*) n FROM tickets WHERE status NOT IN ('completed','closed') AND due_at IS NOT NULL AND due_at < NOW()"), $one("SELECT COUNT(*) n FROM tickets WHERE status NOT IN ('completed','closed') AND due_at IS NOT NULL AND due_at < NOW()") === 0],
            ['Devices monitored', (string) $one("SELECT COUNT(*) n FROM network_devices WHERE monitor_enabled=1"), true],
            ['Last device check', (string) ($this->db->query('SELECT MAX(last_checked) m FROM network_devices')->getRow()->m ?? 'never'), true],
            ['Admins without 2FA', (string) $one("SELECT COUNT(*) n FROM users WHERE role='admin' AND active=1 AND totp_enabled=0"), $one("SELECT COUNT(*) n FROM users WHERE role='admin' AND active=1 AND totp_enabled=0") === 0],
            ['Locked accounts', (string) $one("SELECT COUNT(*) n FROM users WHERE locked_until > NOW()"), true],
        ];
        $bk = \App\Libraries\Backup::latest();
        $checks[] = ['Last scheduled backup', $bk ? date('Y-m-d H:i', $bk['time']) . ' (' . round($bk['size'] / 1024) . ' KB, ' . $bk['count'] . ' kept)' : 'none - schedule: php spark backup:run', $bk && time() - $bk['time'] < 172800];
        $log = '';
        $files = glob(WRITEPATH . 'logs/log-*.log') ?: [];
        if ($files) { rsort($files); $lines = file($files[0], FILE_IGNORE_NEW_LINES) ?: []; $log = implode("\n", array_slice($lines, -60)); }
        return view('itsupport/health', ['title' => 'System Health', 'checks' => $checks, 'log' => $log]);
    }
}