<?php
namespace App\Controllers;

class ItsHealth extends ItsBase {
    public function index() {
        if ($r = $this->needAdmin()) return $r;
        $one = function(string $sql): int { try { return (int)($this->db->query($sql)->getRow()->n ?? 0); } catch (\Throwable $e) { return -1; } };

        // Auto-create missing writable subdirs
        foreach (['cache', 'session', 'logs', 'uploads', 'extensions'] as $_d) {
            $p = WRITEPATH . $_d;
            if (!is_dir($p)) @mkdir($p, 0755, true);
        }

        $free = @disk_free_space(WRITEPATH); $total = @disk_total_space(WRITEPATH);
        $diskPct = $total > 0 ? $free / $total : 1.0;
        $diskLabel = $total ? round($free / 1073741824, 2) . ' GB free of ' . round($total / 1073741824, 1) . ' GB (' . round($diskPct * 100) . '%)' : 'unknown';

        // Mail config
        $mailMethod = \App\Models\SettingModel::get('mail_method', 'mail');
        $smtpHost   = \App\Models\SettingModel::get('smtp_host', '');
        $fromAddr   = \App\Models\SettingModel::get('mail_from_email', '') ?: \App\Models\SettingModel::get('company_email', '');
        $mailOk     = $mailMethod !== 'smtp' || ($smtpHost !== '' && $fromAddr !== '');
        $mailLabel  = $mailMethod === 'smtp' ? ($smtpHost ?: 'SMTP selected — host not set!') : ucfirst($mailMethod);

        // Required tables
        $required   = ['tickets', 'clients', 'assets', 'users', 'audit_log', 'login_history', 'extensions', 'ext_subscriptions', 'mail_log'];
        $missing    = array_values(array_filter($required, fn($t) => !$this->db->tableExists($t)));

        // Missing extension files
        $extRows = $this->db->table('extensions')->select('file_path')->where('status', 'approved')->where('file_path !=', '')->get()->getResultArray();
        $missingFiles = 0;
        foreach ($extRows as $row) { if (!is_file(WRITEPATH . 'extensions/' . ltrim($row['file_path'], '/'))) $missingFiles++; }

        // Error count in today's log
        $todayLog = WRITEPATH . 'logs/log-' . date('Y-m-d') . '.log';
        $errorCount = 0;
        if (is_file($todayLog)) { $errorCount = substr_count((string)file_get_contents($todayLog, false, null, 0, 500000), "\nERROR - ") + substr_count((string)file_get_contents($todayLog, false, null, 0, 500000), "\nCRITICAL - "); }

        $slaBreached = $one("SELECT COUNT(*) n FROM tickets WHERE status NOT IN ('completed','closed') AND due_at IS NOT NULL AND due_at < NOW()");
        $adminsNo2fa = $one("SELECT COUNT(*) n FROM users WHERE role='admin' AND active=1 AND totp_enabled=0");

        $sections = [
            'Server' => [
                ['PHP version',       PHP_VERSION,                                                        version_compare(PHP_VERSION, '8.1', '>='), null],
                ['CodeIgniter',       \CodeIgniter\CodeIgniter::CI_VERSION,                               true, null],
                ['App version',       trim((string)@file_get_contents(FCPATH . '../VERSION')) ?: 'n/a',   true, null],
                ['Environment',       ENVIRONMENT,                                                        ENVIRONMENT === 'production', ENVIRONMENT !== 'production' ? base_url('its-admin') : null],
                ['HTTPS',             $this->request->isSecure() ? 'Enabled' : 'Off (HTTP only)',         $this->request->isSecure(), null],
                ['PHP extensions',    implode(', ', array_filter(['mysqli','curl','mbstring','zip','openssl','intl'], 'extension_loaded')), extension_loaded('zip') && extension_loaded('openssl') && extension_loaded('mbstring'), null],
                ['Errors today',      $errorCount > 0 ? "{$errorCount} ERROR/CRITICAL in today's log" : 'None', $errorCount === 0, $errorCount > 0 ? base_url('its-logs') : null],
            ],
            'Storage' => [
                ['Disk space',        $diskLabel, $diskPct > 0.15, null],
                ['Writable root',     is_writable(WRITEPATH) ? 'Writable' : 'NOT writable', is_writable(WRITEPATH), null],
                ['Writable: cache/',  is_writable(WRITEPATH.'cache')  ? 'Writable' : 'NOT writable', is_writable(WRITEPATH.'cache'),  null],
                ['Writable: session/',is_writable(WRITEPATH.'session')? 'Writable' : 'NOT writable', is_writable(WRITEPATH.'session'),null],
                ['Writable: logs/',   is_writable(WRITEPATH.'logs')   ? 'Writable' : 'NOT writable', is_writable(WRITEPATH.'logs'),   null],
                ['Writable: uploads/',is_writable(WRITEPATH.'uploads')? 'Writable' : 'NOT writable', is_writable(WRITEPATH.'uploads'),null],
                ['Extension ZIPs',    $missingFiles > 0 ? "{$missingFiles} approved extension file(s) not found on disk" : count($extRows) . ' approved, all on disk', $missingFiles === 0, $missingFiles > 0 ? base_url('its-extensions') : null],
            ],
            'Database' => [
                ['MySQL version',     $this->db->getVersion(), true, null],
                ['Connection',        'OK — ' . $this->db->getDatabase(), true, null],
                ['Required tables',   $missing ? 'Missing: ' . implode(', ', $missing) : 'All ' . count($required) . ' present', !$missing, $missing ? base_url('its-updates') : null],
                ['Open tickets',      (string)$one("SELECT COUNT(*) n FROM tickets WHERE status NOT IN ('completed','closed')"), true, null],
                ['SLA breached',      $slaBreached > 0 ? "{$slaBreached} open ticket(s) past due" : 'None overdue', $slaBreached === 0, $slaBreached > 0 ? base_url('its-tickets?view=overdue') : null],
            ],
            'Mail' => [
                ['Mail method',       $mailLabel, $mailOk, !$mailOk ? base_url('its-admin?tab=mail') : null],
                ['From address',      $fromAddr ?: 'Not set', $fromAddr !== '', !$fromAddr ? base_url('its-admin?tab=mail') : null],
                ['Last mail status',  $this->_lastMailStatus(), $this->_lastMailStatus() !== 'failed', base_url('its-admin?tab=maillog')],
            ],
            'Security' => [
                ['Admins without 2FA',$adminsNo2fa > 0 ? "{$adminsNo2fa} admin account(s)" : 'All admins have 2FA', $adminsNo2fa === 0, $adminsNo2fa > 0 ? base_url('its-admin?tab=users') : null],
                ['Locked accounts',   (string)$one("SELECT COUNT(*) n FROM users WHERE locked_until > NOW()") . ' currently locked', true, null],
                ['Failed logins (24h)',(string)($this->db->tableExists('login_history') ? $one("SELECT COUNT(*) n FROM login_history WHERE success=0 AND created_at >= DATE_SUB(NOW(),INTERVAL 24 HOUR)") : 0) . ' failed attempt(s)', true, base_url('its-signins')],
                ['Backup age',        ...$this->_backupStatus()],
            ],
        ];

        $allChecks = array_merge(...array_values($sections));
        $issues = count(array_filter($allChecks, fn($c) => !$c[2]));

        $log = '';
        $logFiles = glob(WRITEPATH . 'logs/log-*.log') ?: [];
        if ($logFiles) { rsort($logFiles); $lines = file($logFiles[0], FILE_IGNORE_NEW_LINES) ?: []; $log = implode("\n", array_slice($lines, -80)); }

        // SSL / domain expiry checks
        $watchedDomains = [];
        if ($this->db->tableExists('watched_domains')) {
            $rows = $this->db->table('watched_domains wd')->select('wd.*, c.name AS client_name')->join('clients c', 'c.id = wd.client_id', 'left')->orderBy('wd.domain')->get()->getResultArray();
            foreach ($rows as &$row) {
                $row['ssl_days']  = $this->_sslDays($row['domain']);
                $row['ssl_ok']    = $row['ssl_days'] === null ? null : $row['ssl_days'] > 14;
            }
            $watchedDomains = $rows;
        }
        $clients = $this->db->table('clients')->select('id, name')->orderBy('name')->get()->getResultArray();
        return view('itsupport/health', ['title' => 'System Health', 'sections' => $sections, 'issues' => $issues, 'total' => count($allChecks), 'log' => $log, 'watchedDomains' => $watchedDomains, 'clients' => $clients]);
    }

    private function _sslDays(string $domain): ?int {
        try {
            $ctx  = stream_context_create(['ssl' => ['capture_peer_cert' => true, 'verify_peer' => false, 'verify_peer_name' => false]]);
            $sock = @stream_socket_client("ssl://{$domain}:443", $errno, $errstr, 5, STREAM_CLIENT_CONNECT, $ctx);
            if (!$sock) return null;
            $info = stream_context_get_params($sock);
            fclose($sock);
            $cert = openssl_x509_parse($info['options']['ssl']['peer_certificate'] ?? null);
            if (!$cert) return null;
            return (int) ceil(((int) ($cert['validTo_time_t'] ?? 0) - time()) / 86400);
        } catch (\Throwable) { return null; }
    }

    public function saveDomain() {
        if ($r = $this->needAdmin()) return $r;
        if (!$this->db->tableExists('watched_domains')) return redirect()->to(base_url('its-health'))->with('err', 'Run migrations first.');
        $domain = strtolower(trim((string) $this->request->getPost('domain')));
        $domain = preg_replace('#^https?://#i', '', $domain);
        $domain = rtrim(explode('/', $domain)[0], '.');
        if (!$domain || strlen($domain) > 253) return redirect()->to(base_url('its-health#domains'))->with('err', 'Enter a valid domain.');
        $label = mb_substr(trim((string) $this->request->getPost('label')), 0, 120);
        $clientId = (int) $this->request->getPost('client_id') ?: null;
        $existing = $this->db->table('watched_domains')->where('domain', $domain)->countAllResults();
        if (!$existing) $this->db->table('watched_domains')->insert(['domain' => $domain, 'label' => $label ?: null, 'client_id' => $clientId, 'created_at' => date('Y-m-d H:i:s')]);
        return redirect()->to(base_url('its-health#domains'))->with('ok', "Domain {$domain} added to monitoring.");
    }

    public function deleteDomain(int $id) {
        if ($r = $this->needAdmin()) return $r;
        $this->db->table('watched_domains')->where('id', $id)->delete();
        return redirect()->to(base_url('its-health#domains'))->with('ok', 'Domain removed.');
    }

    private function _lastMailStatus(): string {
        if (!$this->db->tableExists('mail_log')) return 'no log';
        $row = $this->db->table('mail_log')->orderBy('id', 'DESC')->limit(1)->get()->getRowArray();
        return $row ? $row['status'] : 'none';
    }

    private function _backupStatus(): array {
        $bk = \App\Libraries\Backup::latest();
        if (!$bk) return ['No backup found', false, base_url('its-admin?tab=maintenance')];
        $age = time() - $bk['time'];
        $label = date('Y-m-d H:i', $bk['time']) . ' · ' . round($bk['size'] / 1024) . ' KB';
        return [$label, $age < 172800, base_url('its-admin?tab=maintenance')];
    }

    public function logs() {
        if ($r = $this->needAdmin()) return $r;
        $files = glob(WRITEPATH . 'logs/log-*.log') ?: [];
        rsort($files);
        $current = basename((string) $this->request->getGet('file')) ?: (isset($files[0]) ? basename($files[0]) : null);
        $entries = [];
        $level = strtoupper((string) $this->request->getGet('level'));
        if ($current && preg_match('/^log-[\d-]+\.log$/', $current) && is_file(WRITEPATH . 'logs/' . $current)) {
            $text = (string) file_get_contents(WRITEPATH . 'logs/' . $current, false, null, 0, 2_000_000);
            foreach (preg_split('/\n(?=[A-Z]+ - \d{4}-)/', $text) as $chunk) {
                if (preg_match('/^([A-Z]+) - ([\d-]+ [\d:]+) --> (.*)$/s', trim($chunk), $m)) {
                    if (!$level || $m[1] === $level) {
                        $entries[] = ['level' => $m[1], 'time' => $m[2], 'message' => $m[3]];
                    }
                }
            }
            $entries = array_reverse($entries);
        }
        return view('itsupport/logs', [
            'title' => 'Error Logs',
            'files' => array_map('basename', $files),
            'current' => $current,
            'entries' => array_slice($entries, 0, 500),
            'level' => $level,
        ]);
    }

    public function downloadLogs() {
        if ($r = $this->needAdmin()) return $r;
        $file = basename((string) $this->request->getGet('file'));
        if (!preg_match('/^log-[\d-]+\.log$/', $file) || !is_file(WRITEPATH . 'logs/' . $file)) {
            return redirect()->to(base_url('its-logs'))->with('err', 'Log file not found.');
        }
        return $this->response->download(WRITEPATH . 'logs/' . $file, null)->setFileName($file);
    }

    public function clearLogs() {
        if ($r = $this->needAdmin()) return $r;
        $removed = 0;
        foreach (glob(WRITEPATH . 'logs/log-*.log') ?: [] as $f) {
            if (basename($f) !== 'log-' . date('Y-m-d') . '.log') {
                @unlink($f);
                $removed++;
            }
        }
        $this->audit('logs_cleared', 'system', "Removed {$removed} log files");
        return redirect()->to(base_url('its-logs'))->with('ok', "{$removed} old log file(s) removed — today's log kept.");
    }

    public function signins() {
        if ($r = $this->needAdmin()) return $r;
        $q = $this->db->table('login_attempts')->select('id, username, ip_address, attempted_at')->orderBy('id', 'DESC');
        if ($s = trim((string) $this->request->getGet('q'))) {
            $q->groupStart()->like('username', $s)->orLike('ip_address', $s)->groupEnd();
        }
        $page = max(1, (int) $this->request->getGet('page'));
        $total = $q->countAllResults(false);
        $rows = $q->limit(50, ($page - 1) * 50)->get()->getResultArray();
        $byIp = $this->db->table('login_attempts')->select('ip_address, COUNT(*) n')->groupBy('ip_address')->orderBy('n', 'DESC')->limit(10)->get()->getResultArray();
        $hasHistory = $this->db->tableExists('login_history');
        if ($hasHistory) {
            $hq = $this->db->table('login_history h')->select('h.id, h.username, h.ip_address, h.success, h.reason, h.created_at')->orderBy('h.id', 'DESC');
            if ($s = trim((string) $this->request->getGet('q'))) $hq->groupStart()->like('h.username', $s)->orLike('h.ip_address', $s)->groupEnd();
            if (($res = $this->request->getGet('result')) !== null && $res !== '') $hq->where('h.success', $res === 'ok' ? 1 : 0);
            $total = $hq->countAllResults(false);
            $rows  = $hq->limit(50, ($page - 1) * 50)->get()->getResultArray();
            $byIp  = $this->db->table('login_history')->select('ip_address, COUNT(*) n')->groupBy('ip_address')->orderBy('n', 'DESC')->limit(10)->get()->getResultArray();
        }
        return view('itsupport/signins', [
            'title'  => 'Sign-in Activity',
            'rows'   => $rows,
            'page'   => $page,
            'pages'  => (int) ceil(max(1, $total) / 50),
            'total'  => $total,
            'byIp'   => $byIp,
            'hasHistory' => $hasHistory,
            'q'      => trim((string) $this->request->getGet('q')),
            'result' => (string) ($this->request->getGet('result') ?? ''),
        ]);
    }

    public function cleanup() {
        if ($r = $this->needAdmin()) return $r;
        $groups = $this->scanGroups();
        return view('itsupport/cleanup', [
            'title' => 'System Cleanup',
            'groups' => $groups,
            'totalFiles' => array_sum(array_column($groups, 'count')),
            'totalBytes' => array_sum(array_column($groups, 'bytes')),
        ]);
    }

    public function runCleanup() {
        if ($r = $this->needAdmin()) return $r;
        $allowed = ['temp', 'sessions', 'logs', 'debug', 'updates'];
        $only = array_values(array_intersect((array) $this->request->getPost('groups'), $allowed));
        if (!$only) return redirect()->to(base_url('its-cleanup'))->with('err', 'Choose at least one category.');
        $groups = $this->scanGroups();
        $files = 0; $bytes = 0;
        foreach ($only as $key) {
            foreach ($groups[$key]['files'] ?? [] as $f) {
                $bytes += (int) @filesize($f);
                if (@unlink($f)) $files++;
            }
        }
        $this->audit('cleanup_run', 'system', "Removed {$files} file(s) from: " . implode(', ', $only));
        return redirect()->to(base_url('its-cleanup'))->with('ok', "Removed {$files} file(s) — freed " . number_format($bytes / 1048576, 2) . ' MB.');
    }

    private function scanGroups(): array {
        $files = fn(string $glob) => array_filter(glob($glob) ?: [], 'is_file');
        $size  = fn(array $list) => array_sum(array_map(fn($f) => (int) @filesize($f), $list));
        $today = WRITEPATH . 'logs/log-' . date('Y-m-d') . '.log';

        $temp = $files(WRITEPATH . 'cache/*');
        $sess = $files(WRITEPATH . 'session/*');
        $logs = array_filter($files(WRITEPATH . 'logs/log-*.log'), fn($f) => $f !== $today);
        $dbg  = $files(WRITEPATH . '*.html');
        $upd  = $files(WRITEPATH . 'updates/*.zip');

        return [
            'temp'     => ['label' => 'Cache files',              'files' => $temp, 'count' => count($temp), 'bytes' => $size($temp)],
            'sessions' => ['label' => 'Old session files',         'files' => $sess, 'count' => count($sess), 'bytes' => $size($sess)],
            'logs'     => ['label' => 'Old log files (keeps today)', 'files' => $logs, 'count' => count($logs), 'bytes' => $size($logs)],
            'debug'    => ['label' => 'Debug output (.html files)', 'files' => $dbg,  'count' => count($dbg),  'bytes' => $size($dbg)],
            'updates'  => ['label' => 'Leftover update packages',   'files' => $upd,  'count' => count($upd),  'bytes' => $size($upd)],
        ];
    }
}