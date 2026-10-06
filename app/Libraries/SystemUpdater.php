<?php
namespace App\Libraries;

use App\Models\SettingModel;

class SystemUpdater
{
    private const ROOTS = ['app', 'public', 'system'];
    private const EXTRA = ['composer.json', 'spark', 'VERSION'];
    private const MAX_PACKAGE = 100 * 1024 * 1024;
    public const FRAMEWORK_API = 'https://api.github.com/repos/codeigniter4/framework/releases/latest';

    private function root(): string { return rtrim(ROOTPATH, '/\\'); }
    private function backupDir(): string { $d = WRITEPATH . 'backups'; if (!is_dir($d)) @mkdir($d, 0775, true); return $d; }
    private function baselineFile(): string { return WRITEPATH . 'integrity.json'; }

    public function version(): string
    {
        $f = $this->root() . DIRECTORY_SEPARATOR . 'VERSION';
        $v = is_file($f) ? trim((string) file_get_contents($f)) : '';
        return preg_match('/^\d+\.\d+\.\d+$/', $v) ? $v : '2.1.0';
    }

    public function allowedPath(string $p): bool
    {
        if ($p === '' || str_contains($p, '..') || str_contains($p, '\\') || str_contains($p, ':') || $p[0] === '/' || str_contains($p, "\0")) return false;
        if (str_starts_with($p, 'public/uploads/') || str_starts_with($p, 'public/public/') || str_ends_with($p, '.env') || str_starts_with($p, 'writable/')) return false;
        if (in_array($p, self::EXTRA, true)) return true;
        return in_array(explode('/', $p)[0], self::ROOTS, true);
    }

    /** @return array<string,string> relative path => absolute path */
    private function files(): array
    {
        $out = [];
        foreach (self::ROOTS as $r) {
            $base = $this->root() . DIRECTORY_SEPARATOR . $r;
            if (!is_dir($base)) continue;
            $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base, \FilesystemIterator::SKIP_DOTS));
            foreach ($it as $f) {
                if (!$f->isFile()) continue;
                $rel = str_replace('\\', '/', substr($f->getPathname(), strlen($this->root()) + 1));
                if ($this->allowedPath($rel)) $out[$rel] = $f->getPathname();
            }
        }
        foreach (self::EXTRA as $e) {
            $p = $this->root() . DIRECTORY_SEPARATOR . $e;
            if (is_file($p)) $out[$e] = $p;
        }
        ksort($out);
        return $out;
    }

    public function recordBaseline(): int
    {
        $h = [];
        foreach ($this->files() as $rel => $abs) $h[$rel] = hash_file('sha256', $abs);
        file_put_contents($this->baselineFile(), json_encode(['version' => $this->version(), 'created' => date('c'), 'files' => $h]), LOCK_EX);
        return count($h);
    }

    public function verify(): array
    {
        $res = ['baseline' => false, 'ok' => false, 'modified' => [], 'missing' => [], 'added' => [], 'checked' => 0, 'created' => null];
        $b = is_file($this->baselineFile()) ? json_decode((string) file_get_contents($this->baselineFile()), true) : null;
        if (!is_array($b) || !isset($b['files'])) return $res;
        $res['baseline'] = true;
        $res['created'] = $b['created'] ?? null;
        $now = $this->files();
        foreach ($b['files'] as $rel => $hash) {
            if (!isset($now[$rel])) { $res['missing'][] = $rel; continue; }
            if (!hash_equals($hash, hash_file('sha256', $now[$rel]))) $res['modified'][] = $rel;
        }
        foreach ($now as $rel => $_) if (!isset($b['files'][$rel])) $res['added'][] = $rel;
        $res['checked'] = count($b['files']);
        $res['ok'] = !$res['modified'] && !$res['missing'];
        return $res;
    }

    public function backups(): array
    {
        $out = [];
        foreach (glob($this->backupDir() . DIRECTORY_SEPARATOR . '*') ?: [] as $f) {
            $n = basename($f);
            if (!is_file($f) || !preg_match('/^(code|db)-[A-Za-z0-9._-]+\.(zip|sql\.gz)$/', $n)) continue;
            $out[] = ['name' => $n, 'type' => str_starts_with($n, 'code-') ? 'code' : 'database', 'size' => filesize($f), 'time' => date('Y-m-d H:i:s', filemtime($f))];
        }
        usort($out, fn($a, $b) => strcmp($b['time'], $a['time']));
        return $out;
    }

    public function backupPath(string $name): ?string
    {
        if (!preg_match('/^(code|db)-[A-Za-z0-9._-]+\.(zip|sql\.gz)$/', $name)) return null;
        $p = $this->backupDir() . DIRECTORY_SEPARATOR . $name;
        return is_file($p) ? $p : null;
    }

    public function createCodeBackup(string $label = 'manual'): string
    {
        $label = preg_replace('/[^A-Za-z0-9.-]/', '', $label) ?: 'manual';
        $name = 'code-' . date('Ymd-His') . '-v' . $this->version() . '-' . $label . '.zip';
        $zip = new \ZipArchive();
        if ($zip->open($this->backupDir() . DIRECTORY_SEPARATOR . $name, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) throw new \RuntimeException('Cannot create backup archive.');
        foreach ($this->files() as $rel => $abs) $zip->addFile($abs, $rel);
        $zip->close();
        return $name;
    }

    public function createDbBackup($db): string
    {
        $sql = "-- IT Support CI4 backup " . date('c') . "\n";
        foreach ($db->listTables() as $table) {
            if (in_array($table, ['migrations', 'sessions', 'ci_sessions'], true)) continue;
            foreach ($db->table($table)->get()->getResultArray() as $row) {
                $vals = array_map(fn($v) => $v === null ? 'NULL' : $db->escape($v), array_values($row));
                $sql .= "INSERT INTO `$table` (`" . implode('`,`', array_keys($row)) . "`) VALUES (" . implode(',', $vals) . ");\n";
            }
        }
        $name = 'db-' . date('Ymd-His') . '-v' . $this->version() . '.sql.gz';
        file_put_contents($this->backupDir() . DIRECTORY_SEPARATOR . $name, gzencode($sql, 9), LOCK_EX);
        return $name;
    }

    /** Restore files from a code archive; $only limits the restore to given paths. Returns restored paths. */
    public function restoreFromZip(string $zipPath, ?array $only = null): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) throw new \RuntimeException('Cannot open archive.');
        $done = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $n = $zip->getNameIndex($i);
            if (str_ends_with($n, '/') || !$this->allowedPath($n) || ($only !== null && !in_array($n, $only, true))) continue;
            $data = $zip->getFromIndex($i);
            if ($data === false) continue;
            $dest = $this->root() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $n);
            if (!is_dir(dirname($dest))) mkdir(dirname($dest), 0775, true);
            file_put_contents($dest, $data, LOCK_EX);
            $done[] = $n;
        }
        $zip->close();
        return $done;
    }

    public function heal(string $backupName, array $verify): array
    {
        $p = $this->backupPath($backupName);
        if (!$p || !str_starts_with($backupName, 'code-')) throw new \RuntimeException('Choose a code backup (.zip).');
        $targets = array_merge($verify['modified'], $verify['missing']);
        return $targets ? $this->restoreFromZip($p, $targets) : [];
    }

    private function publicHost(string $url): bool
    {
        $p = parse_url($url);
        if (($p['scheme'] ?? '') !== 'https' || empty($p['host'])) return false;
        $ip = gethostbyname($p['host']);
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }

    /** Use writable/cacert.pem when PHP has no CA bundle configured (common on WAMP); verification stays on. */
    public function tlsOptions(): array
    {
        if (ini_get('curl.cainfo') || ini_get('openssl.cafile')) return [];
        $b = WRITEPATH . 'cacert.pem';
        return is_file($b) ? ['verify' => $b] : [];
    }

    private function getJson(string $url, array $headers = []): array
    {
        if (!$this->publicHost($url)) throw new \RuntimeException('Update URLs must be public HTTPS addresses.');
        $r = service('curlrequest')->get($url, ['timeout' => 10, 'http_errors' => false, 'headers' => $headers + ['User-Agent' => 'ITSupport-Updater']] + $this->tlsOptions());
        if ($r->getStatusCode() !== 200) throw new \RuntimeException('Server answered HTTP ' . $r->getStatusCode() . '.');
        $j = json_decode($r->getBody(), true);
        if (!is_array($j)) throw new \RuntimeException('Invalid JSON response.');
        return $j;
    }

    public static function classify(string $from, string $to): string
    {
        [$a, $b] = [array_map('intval', explode('.', $from)), array_map('intval', explode('.', $to))];
        return ($b[0] ?? 0) !== ($a[0] ?? 0) ? 'major' : ((($b[1] ?? 0) !== ($a[1] ?? 0)) ? 'minor' : 'patch');
    }

    public function checkOnline(): array
    {
        $cur = $this->version();
        $res = ['checked' => date('c'), 'current' => $cur, 'app' => null, 'framework' => null, 'errors' => []];
        $url = trim((string) SettingModel::get('update_manifest_url', ''));
        if ($url === '') {
            $res['errors'][] = 'No update manifest URL is configured.';
        } else {
            try {
                $m = $this->getJson($url);
                if (!preg_match('/^\d+\.\d+\.\d+$/', (string) ($m['version'] ?? ''))) throw new \RuntimeException('Manifest has no valid version.');
                $res['app'] = ['version' => $m['version'], 'available' => version_compare($m['version'], $cur, '>'), 'type' => self::classify($cur, $m['version']), 'notes' => (string) ($m['notes'] ?? ''), 'url' => (string) ($m['url'] ?? ''), 'sha256' => strtolower((string) ($m['sha256'] ?? ''))];
            } catch (\Throwable $e) { $res['errors'][] = 'App update check failed: ' . $e->getMessage(); }
        }
        try {
            $j = $this->getJson(self::FRAMEWORK_API, ['Accept' => 'application/vnd.github+json']);
            $latest = ltrim((string) ($j['tag_name'] ?? ''), 'v');
            $have = \CodeIgniter\CodeIgniter::CI_VERSION;
            if (!preg_match('/^\d+\.\d+\.\d+$/', $latest)) throw new \RuntimeException('Unexpected release tag.');
            $res['framework'] = ['current' => $have, 'version' => $latest, 'available' => version_compare($latest, $have, '>'), 'type' => self::classify($have, $latest)];
        } catch (\Throwable $e) { $res['errors'][] = 'Framework check failed: ' . $e->getMessage(); }
        return $res;
    }

    /** Download, verify, back up, apply, migrate; roll back files on failure. */
    public function applyOnline(array $app, $db): array
    {
        if (empty($app['available'])) throw new \RuntimeException('No update is available.');
        if (!preg_match('/^[0-9a-f]{64}$/', (string) $app['sha256'])) throw new \RuntimeException('The manifest has no SHA-256 checksum; refusing to update.');
        if (!$this->publicHost((string) $app['url'])) throw new \RuntimeException('The package URL must be a public HTTPS address.');
        $tmp = tempnam(sys_get_temp_dir(), 'itsupd');
        try {
            $r = service('curlrequest')->get($app['url'], ['timeout' => 180, 'http_errors' => false, 'sink' => $tmp, 'headers' => ['User-Agent' => 'ITSupport-Updater']] + $this->tlsOptions());
            if ($r->getStatusCode() !== 200 || filesize($tmp) > self::MAX_PACKAGE) throw new \RuntimeException('Download failed or package too large.');
            return $this->applyPackage($tmp, (string) $app['sha256'], (string) $app['version'], $db);
        } finally { @unlink($tmp); }
    }

    private function logFile(): string { return WRITEPATH . 'update.log'; }

    public function log(string $msg): void
    {
        $msg = preg_replace('/[\r\n]+/', ' ', $msg);
        @file_put_contents($this->logFile(), '[' . date('Y-m-d H:i:s') . '] ' . mb_substr($msg, 0, 500) . "\n", FILE_APPEND | LOCK_EX);
    }

    /** @return string[] newest first */
    public function readLog(int $max = 200): array
    {
        if (!is_file($this->logFile())) return [];
        $lines = array_slice(file($this->logFile(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [], -$max);
        return array_reverse($lines);
    }

    public function clearLog(): void { @unlink($this->logFile()); }

    /** Reads CI_VERSION from a framework package without extracting it. */
    public function frameworkVersionFromZip(string $zipPath): ?string
    {
        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) return null;
        $src = $zip->getFromName('system/CodeIgniter.php');
        $zip->close();
        return ($src !== false && preg_match("/CI_VERSION\s*=\s*'(\d+\.\d+\.\d+)'/", $src, $m)) ? $m[1] : null;
    }

    /**
     * @param string $kind 'app' (full allowlisted package) or 'framework' (only system/ is applied)
     * @param string $sha256 expected checksum; empty skips the comparison (admin-supplied upload)
     */
    public function applyPackage(string $zipPath, string $sha256, string $newVersion, $db, string $kind = 'app'): array
    {
        $actual = hash_file('sha256', $zipPath);
        if ($sha256 !== '' && !hash_equals(strtolower($sha256), $actual)) { $this->log("Aborted $kind $newVersion: checksum mismatch"); throw new \RuntimeException('Package checksum mismatch; update aborted.'); }
        $this->log("Starting $kind update to $newVersion (sha256 $actual)");
        $v = $this->verify();
        if (!$v['baseline']) throw new \RuntimeException('Record an integrity baseline before updating.');
        if (!$v['ok']) throw new \RuntimeException('System integrity check failed (' . count($v['modified']) . ' modified, ' . count($v['missing']) . ' missing). Auto-heal first.');
        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) throw new \RuntimeException('Package is not a valid ZIP.');
        $entries = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $n = $zip->getNameIndex($i);
            if (str_ends_with($n, '/')) continue;
            if ($kind === 'framework') { if (str_starts_with($n, 'system/') && $this->allowedPath($n)) $entries[$i] = $n; continue; }
            if ($n === 'update.json') continue;
            if (!$this->allowedPath($n)) { $zip->close(); throw new \RuntimeException('Package contains a forbidden path: ' . $n); }
            $entries[$i] = $n;
        }
        if (!$entries) { $zip->close(); throw new \RuntimeException($kind === 'framework' ? 'No system/ files found in the framework package.' : 'Package is empty.'); }
        $codeBackup = $this->createCodeBackup('pre-' . $newVersion);
        $dbBackup = $this->createDbBackup($db);
        $this->log("Backups created: $codeBackup, $dbBackup");
        try {
            foreach ($entries as $i => $n) {
                $data = $zip->getFromIndex($i);
                if ($data === false) throw new \RuntimeException('Cannot read ' . $n);
                $dest = $this->root() . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $n);
                if (!is_dir(dirname($dest))) mkdir(dirname($dest), 0775, true);
                file_put_contents($dest, $data, LOCK_EX);
            }
            $zip->close();
            if ($kind === 'app') {
                file_put_contents($this->root() . DIRECTORY_SEPARATOR . 'VERSION', $newVersion . "\n", LOCK_EX);
                \Config\Services::migrations()->latest();
            }
        } catch (\Throwable $e) {
            $this->restoreFromZip($this->backupPath($codeBackup));
            $this->log("FAILED, rolled back from $codeBackup: " . $e->getMessage());
            throw new \RuntimeException('Update failed and files were rolled back from ' . $codeBackup . ': ' . $e->getMessage());
        }
        $this->recordBaseline();
        $this->log("Applied $kind $newVersion (" . count($entries) . ' files)');
        return ['files' => count($entries), 'codeBackup' => $codeBackup, 'dbBackup' => $dbBackup];
    }
}