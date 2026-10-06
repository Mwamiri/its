<?php
namespace App\Controllers;
use App\Libraries\SystemUpdater;
use App\Models\SettingModel;
class ItsUpdates extends ItsBase {
    private function back(string $kind, string $msg) { return redirect()->to(base_url('its-updates'))->with($kind, $msg); }
    public function index() {
        if ($r = $this->needAdmin()) return $r;
        $u = new SystemUpdater();
        $this->autoCheck($u);
        return view('itsupport/updates', [
            'title' => 'Updates', 'version' => $u->version(), 'frameworkVersion' => \CodeIgniter\CodeIgniter::CI_VERSION,
            'integrity' => $u->verify(), 'backups' => $u->backups(), 'system' => $this->systemInfo(), 'log' => $u->readLog(), 'auto' => SettingModel::get('update_auto_check', '0') === '1',
            'last' => json_decode((string) SettingModel::get('update_last_check', ''), true),
            'manifestUrl' => SettingModel::get('update_manifest_url', ''),
        ]);
    }
    private function systemInfo(): array {
        $dbVersion = '';
        try { $dbVersion = $this->db->getPlatform() . ' ' . $this->db->getVersion(); } catch (\Throwable $e) { log_message('error', 'Database version lookup failed: {m}', ['m' => $e->getMessage()]); }
        $writable = fn(string $p): string => is_writable($p) ? 'Writable' : 'Not writable';
        $ok = version_compare(PHP_VERSION, '8.2.0', '>=');
        return [
            'php' => PHP_VERSION,
            'phpNote' => $ok ? 'Meets the 8.2+ requirement' : 'PHP 8.2 or newer is recommended',
            'rows' => [
                'Application version' => (new SystemUpdater())->version(),
                'CodeIgniter version' => \CodeIgniter\CodeIgniter::CI_VERSION,
                'PHP version' => PHP_VERSION . ' (' . PHP_SAPI . ')',
                'Database' => $dbVersion ?: 'Unavailable',
                'Web server' => (string) ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'),
                'Operating system' => php_uname('s') . ' ' . php_uname('r'),
                'Environment' => ENVIRONMENT,
                'PHP extensions' => implode(', ', array_map(fn($e) => $e . (extension_loaded($e) ? ' ✓' : ' ✗'), ['mysqli', 'curl', 'openssl', 'mbstring', 'intl', 'zip', 'gd'])),
                'Memory limit' => (string) ini_get('memory_limit'),
                'Upload limit' => (string) ini_get('upload_max_filesize'),
                'Writable folder' => $writable(WRITEPATH),
                'Uploads folder' => $writable(FCPATH . 'uploads'),
                'Server time' => date('Y-m-d H:i:s T'),
            ],
        ];
    }
    public function saveUrl() {
        if ($r = $this->needAdmin()) return $r;
        $url = trim((string) $this->request->getPost('manifest_url'));
        if ($url !== '' && (!filter_var($url, FILTER_VALIDATE_URL) || !str_starts_with($url, 'https://') || mb_strlen($url) > 500)) return $this->back('err', 'The manifest URL must be a valid https:// address.');
        SettingModel::put('update_manifest_url', $url);
        $this->audit('update_url_saved', 'updates', $url);
        return $this->back('ok', 'Update source saved.');
    }
    private function autoCheck(SystemUpdater $u): void {
        if (SettingModel::get('update_auto_check', '0') !== '1') return;
        $last = json_decode((string) SettingModel::get('update_last_check', ''), true);
        if ($last && isset($last['checked']) && strtotime($last['checked']) > time() - 86400) return;
        $this->runCheck($u, 'auto');
    }
    private function runCheck(SystemUpdater $u, string $how): array {
        $res = $u->checkOnline();
        SettingModel::put('update_last_check', json_encode($res));
        $u->log("Update check ($how): app " . ($res['app'] ? $res['app']['version'] . ($res['app']['available'] ? ' available' : ' current') : 'n/a') . ', framework ' . ($res['framework'] ? $res['framework']['version'] . ($res['framework']['available'] ? ' available' : ' current') : 'n/a') . ($res['errors'] ? ' | ' . implode('; ', $res['errors']) : ''));
        return $res;
    }
    public function toggleAuto() {
        if ($r = $this->needAdmin()) return $r;
        $on = $this->request->getPost('auto') === '1' ? '1' : '0';
        SettingModel::put('update_auto_check', $on);
        (new SystemUpdater())->log('Automatic daily checks ' . ($on === '1' ? 'enabled' : 'disabled'));
        $this->audit('update_auto_check', 'updates', $on);
        return $this->back('ok', 'Automatic checking ' . ($on === '1' ? 'enabled (runs daily when an admin opens this page).' : 'disabled.'));
    }
    public function clearLog() {
        if ($r = $this->needAdmin()) return $r;
        (new SystemUpdater())->clearLog();
        $this->audit('update_log_cleared', 'updates');
        return $this->back('ok', 'Update log cleared.');
    }
    public function upload() {
        if ($r = $this->needAdmin()) return $r;
        $u = new SystemUpdater();
        $kind = $this->request->getPost('kind') === 'framework' ? 'framework' : 'app';
        $file = $this->request->getFile('package');
        if (!$file || !$file->isValid() || $file->hasMoved()) return $this->back('err', 'Choose a valid ZIP file to upload.');
        if (strtolower($file->getClientExtension()) !== 'zip' || $file->getSize() > 100 * 1024 * 1024) return $this->back('err', 'The package must be a .zip file of at most 100 MB.');
        $sha = strtolower(trim((string) $this->request->getPost('sha256')));
        if ($sha !== '' && !preg_match('/^[0-9a-f]{64}$/', $sha)) return $this->back('err', 'The SHA-256 checksum must be 64 hex characters.');
        $cur = $kind === 'framework' ? \CodeIgniter\CodeIgniter::CI_VERSION : $u->version();
        if ($kind === 'framework') {
            $ver = $u->frameworkVersionFromZip($file->getTempName());
            if (!$ver) return $this->back('err', 'This does not look like a CodeIgniter framework package (system/CodeIgniter.php not found).');
        } else {
            $ver = trim((string) $this->request->getPost('version'));
            if (!preg_match('/^\d+\.\d+\.\d+$/', $ver)) return $this->back('err', 'Enter the package version as x.y.z.');
        }
        if (version_compare($ver, $cur, '<=') && $this->request->getPost('allow_same') !== '1') return $this->back('err', "Package version $ver is not newer than the installed $cur. Tick the reinstall option to apply it anyway.");
        $type = SystemUpdater::classify($cur, $ver);
        if ($type === 'major' && trim((string) $this->request->getPost('confirm')) !== 'UPDATE MAJOR') return $this->back('err', 'Type UPDATE MAJOR to confirm a major update.');
        $u->log("Uploaded $kind package " . $file->getClientName() . " ($ver) by " . ($this->user()['username'] ?? 'admin'));
        try { $res = $u->applyPackage($file->getTempName(), $sha, $ver, $this->db, $kind); }
        catch (\Throwable $e) { $u->log('Upload update failed: ' . $e->getMessage()); $this->audit('update_failed', 'updates', $e->getMessage()); return $this->back('err', $e->getMessage()); }
        $this->audit('update_uploaded', 'updates', "$kind $ver (" . $res['files'] . ' files)');
        return $this->back('ok', ucfirst($kind) . " updated to $ver ({$res['files']} files). Backups: {$res['codeBackup']}, {$res['dbBackup']}.");
    }
    public function check() {
        if ($r = $this->needAdmin()) return $r;
        $this->runCheck(new SystemUpdater(), 'manual');
        $this->audit('update_check', 'updates');
        return $this->back('ok', 'Update check finished.');
    }
    public function baseline() {
        if ($r = $this->needAdmin()) return $r;
        $n = (new SystemUpdater())->recordBaseline();
        (new SystemUpdater())->log("Integrity baseline recorded ($n files)");
        $this->audit('integrity_baseline', 'updates', (string) $n);
        return $this->back('ok', "Integrity baseline recorded for $n files.");
    }
    public function backupNow() {
        if ($r = $this->needAdmin()) return $r;
        $u = new SystemUpdater();
        try { $c = $u->createCodeBackup('manual'); $d = $u->createDbBackup($this->db); }
        catch (\Throwable $e) { return $this->back('err', 'Backup failed: ' . $e->getMessage()); }
        $u->log("Manual backup: $c, $d");
        $this->audit('server_backup', 'updates', $c . ', ' . $d);
        return $this->back('ok', "Created $c and $d.");
    }
    public function heal() {
        if ($r = $this->needAdmin()) return $r;
        $u = new SystemUpdater();
        $v = $u->verify();
        if (!$v['baseline']) return $this->back('err', 'No integrity baseline exists.');
        try { $done = $u->heal((string) $this->request->getPost('backup'), $v); }
        catch (\Throwable $e) { return $this->back('err', $e->getMessage()); }
        $left = $u->verify();
        $u->log('Auto-heal restored ' . count($done) . ' file(s) from ' . $this->request->getPost('backup'));
        $this->audit('auto_heal', 'updates', count($done) . ' restored from ' . $this->request->getPost('backup'));
        return $this->back($left['ok'] ? 'ok' : 'err', count($done) . ' file(s) restored. ' . ($left['ok'] ? 'Integrity is clean.' : 'Some files could not be restored from that backup.'));
    }
    public function download() {
        if ($r = $this->needAdmin()) return $r;
        $p = (new SystemUpdater())->backupPath((string) $this->request->getGet('name'));
        if (!$p) return $this->back('err', 'Backup not found.');
        $this->audit('backup_download', 'updates', basename($p));
        return $this->response->download($p, null)->setHeader('Cache-Control', 'no-store, private');
    }
    public function apply() {
        if ($r = $this->needAdmin()) return $r;
        $last = json_decode((string) SettingModel::get('update_last_check', ''), true);
        $app = $last['app'] ?? null;
        if (!$app || empty($app['available'])) return $this->back('err', 'Run an update check first; no update is available.');
        if ($app['type'] === 'major' && trim((string) $this->request->getPost('confirm')) !== 'UPDATE MAJOR') return $this->back('err', 'Type UPDATE MAJOR to confirm a major update.');
        if (trim((string) $this->request->getPost('version')) !== $app['version']) return $this->back('err', 'Version mismatch; run the check again.');
        try { $res = (new SystemUpdater())->applyOnline($app, $this->db); }
        catch (\Throwable $e) { $this->audit('update_failed', 'updates', $e->getMessage()); return $this->back('err', $e->getMessage()); }
        SettingModel::put('update_last_check', '');
        $this->audit('update_applied', 'updates', $app['version'] . ' (' . $res['files'] . ' files)');
        return $this->back('ok', "Updated to {$app['version']}. Backups: {$res['codeBackup']}, {$res['dbBackup']}.");
    }
}
