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
            'integrity' => $u->verifyMeta(), 'backups' => $u->backups(), 'system' => $this->systemInfo(), 'log' => $u->readLog(), 'auto' => SettingModel::get('update_auto_check', '0') === '1',
            'last' => json_decode((string) SettingModel::get('update_last_check', ''), true),
            'manifestUrl' => SettingModel::get('update_manifest_url', ''),
            'releaseNotes' => (new \Config\ReleaseNotes())->releases[$u->version()] ?? null,
            'olderReleases' => array_filter((new \Config\ReleaseNotes())->releases, static fn($v) => version_compare((string) $v, $u->version(), '<'), ARRAY_FILTER_USE_KEY),
            'allReleases' => (new \Config\ReleaseNotes())->releases,
            'inbox' => $u->inbox(),
            'inboxDir' => $u->inboxDir(),
        ]);
    }

    /** Add a ZIP to the update packages folder without installing it yet. */
    public function folderUpload() {
        if ($r = $this->needAdmin()) return $r;
        $file = $this->request->getFile('package');
        if (!$file || !$file->isValid() || strtolower($file->getClientExtension()) !== 'zip' || $file->getSize() > 100 * 1024 * 1024) return $this->back('err', 'Choose a .zip update package of at most 100 MB.');
        $u = new SystemUpdater();
        $name = trim((string) preg_replace('/[^A-Za-z0-9._-]+/', '-', $file->getClientName()), '-.');
        if (!preg_match('/\.zip$/i', $name)) $name .= '.zip';
        if (is_file($u->inboxDir() . DIRECTORY_SEPARATOR . $name)) $name = date('Ymd-His') . '-' . $name;
        $file->move($u->inboxDir(), $name);
        $u->log('Package added to the updates folder: ' . $name);
        $this->audit('update_package_added', 'updates', $name);
        return $this->back('ok', $name . ' is in the updates folder. Review it below and click Install when ready.');
    }

    /** Install a package from the updates folder (ZIP or unzipped folder) with the same checks and rollback as an upload. */
    public function folderInstall() {
        if ($r = $this->needAdmin()) return $r;
        $u = new SystemUpdater();
        $name = (string) $this->request->getPost('name');
        $pkg = null; foreach ($u->inbox() as $p) if ($p['name'] === $name) $pkg = $p;
        if (!$pkg || ($path = $u->inboxPath($name)) === null) return $this->back('err', 'That package is no longer in the updates folder.');
        if ($pkg['error'] !== '') return $this->back('err', $name . ': ' . $pkg['error']);
        if (!$pkg['newer'] && $this->request->getPost('allow_same') !== '1') return $this->back('err', "Package version {$pkg['version']} is not newer than the installed " . $u->version() . '. Tick "reinstall" to apply it anyway.');
        if ($pkg['type'] === 'major' && trim((string) $this->request->getPost('confirm')) !== 'UPDATE MAJOR') return $this->back('err', 'This is a major update. Type UPDATE MAJOR to confirm.');
        $u->log("Installing {$pkg['version']} from the updates folder ($name) by " . ($this->user()['username'] ?? 'admin'));
        session_write_close();
        $tmp = null;
        try {
            $zip = $pkg['kind'] === 'folder' ? ($tmp = $u->folderToZip($path)) : $path;
            $res = $u->applyPackage($zip, '', $pkg['version'], $this->db, 'app');
        } catch (\Throwable $e) {
            $this->audit('update_failed', 'updates', $e->getMessage());
            return $this->back('err', $e->getMessage());
        } finally {
            if ($tmp) @unlink($tmp);
        }
        $this->audit('update_installed', 'updates', $pkg['version'] . ' from folder (' . $res['files'] . ' files)');
        $msg = "Updated to {$pkg['version']} ({$res['files']} files). Backups: {$res['codeBackup']}, {$res['dbBackup']}. You can delete the package from the folder now.";
        if ($this->request->isAJAX()) return $this->response->setJSON(['ok' => true, 'msg' => $msg]);
        return $this->back('ok', $msg);
    }

    public function folderDelete() {
        if ($r = $this->needAdmin()) return $r;
        $name = (string) $this->request->getPost('name');
        $ok = (new SystemUpdater())->inboxRemove($name);
        if ($ok) $this->audit('update_package_deleted', 'updates', $name);
        return $this->back($ok ? 'ok' : 'err', $ok ? $name . ' deleted from the updates folder.' : 'Could not delete that package.');
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

    /** Polled by JS every 500ms during install/backup/integrity operations. */
    public function progress() {
        if ($r = $this->needAdmin()) return $r;
        // Release the session file lock immediately so the long-running install
        // request (which holds the same session) does not block polls.
        session_write_close();
        return $this->response->setJSON((new SystemUpdater())->getProgress());
    }

    /** Restore all files from a code backup archive (creates a safety backup of current state first). */
    public function restore() {
        if ($r = $this->needAdmin()) return $r;
        $u = new SystemUpdater();
        $name = (string) $this->request->getPost('backup');
        $p = $u->backupPath($name);
        if (!$p || !str_starts_with($name, 'code-')) {
            if ($this->request->isAJAX()) return $this->response->setJSON(['ok' => false, 'msg' => 'Choose a code backup to restore from.']);
            return $this->back('err', 'Choose a code backup to restore from.');
        }
        session_write_close();
        $u->setProgress(5, 'Creating safety backup of current state…', 'backup');
        try {
            $newCode = $u->createCodeBackup('pre-restore');
            $u->setProgress(22, 'Creating database safety backup…', 'db-backup');
            $newDb   = $u->createDbBackup($this->db);
        } catch (\Throwable $e) {
            $u->setProgress(0, 'Safety backup failed: ' . $e->getMessage(), 'error');
            if ($this->request->isAJAX()) return $this->response->setJSON(['ok' => false, 'msg' => 'Safety backup failed: ' . $e->getMessage()]);
            return $this->back('err', 'Could not create safety backup: ' . $e->getMessage());
        }
        $u->setProgress(30, 'Restoring files from ' . $name . '…', 'restore');
        $u->maintenance(true);
        try {
            $done = $u->restoreFromZip($p);
        } catch (\Throwable $e) {
            $u->maintenance(false);
            $u->setProgress(0, 'Restore failed: ' . $e->getMessage(), 'error');
            if ($this->request->isAJAX()) return $this->response->setJSON(['ok' => false, 'msg' => 'Restore failed: ' . $e->getMessage()]);
            return $this->back('err', 'Restore failed: ' . $e->getMessage());
        }
        $u->maintenance(false);
        $u->setProgress(92, 'Updating integrity baseline…', 'baseline');
        $u->recordBaseline();
        $u->setProgress(100, 'Restore complete — ' . count($done) . ' files restored.', 'done');
        $u->log('Restored ' . count($done) . ' files from ' . $name . '. Safety backup: ' . $newCode . ', ' . $newDb);
        $this->audit('restore', 'updates', $name . ' (' . count($done) . ' files)');
        $msg = 'Restored ' . count($done) . ' files from ' . $name . '. '
             . 'Current state was backed up as ' . $newCode . ' and ' . $newDb . '. '
             . 'Note: database schema was not changed — run migrations manually if needed.';
        if ($this->request->isAJAX()) return $this->response->setJSON(['ok' => true, 'msg' => $msg]);
        return $this->back('ok', $msg);
    }

    /** Runs the integrity check with progress reporting (XHR-driven). */
    public function integrityCheck() {
        if ($r = $this->needAdmin()) return $r;
        $u = new SystemUpdater();
        session_write_close();
        $u->setProgress(1, 'Starting integrity check…', 'verify');
        $v = $u->verify(true);
        $this->audit('integrity_check', 'updates', $v['checked'] . ' files, ' . ($v['ok'] ? 'clean' : 'problems found'));
        if ($this->request->isAJAX()) return $this->response->setJSON(['ok' => true, 'result' => $v]);
        return $this->back($v['ok'] ? 'ok' : 'err', ($v['ok'] ? 'Integrity is clean' : 'Problems found') . ' — ' . $v['checked'] . ' files checked.');
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
        session_write_close();
        try { $res = $u->applyPackage($file->getTempName(), $sha, $ver, $this->db, $kind); }
        catch (\Throwable $e) { $u->log('Upload update failed: ' . $e->getMessage()); $this->audit('update_failed', 'updates', $e->getMessage()); return $this->back('err', $e->getMessage()); }
        $this->audit('update_uploaded', 'updates', "$kind $ver (" . $res['files'] . ' files)');
        $msg = ucfirst($kind) . " updated to $ver ({$res['files']} files). Backups: {$res['codeBackup']}, {$res['dbBackup']}.";
        if ($this->request->isAJAX()) return $this->response->setJSON(['ok' => true, 'msg' => $msg]);
        return $this->back('ok', $msg);
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
        session_write_close();
        try { $c = $u->createCodeBackup('manual'); $d = $u->createDbBackup($this->db); }
        catch (\Throwable $e) { return $this->back('err', 'Backup failed: ' . $e->getMessage()); }
        $u->log("Manual backup: $c, $d");
        $this->audit('server_backup', 'updates', $c . ', ' . $d);
        $msg = "Created $c and $d.";
        if ($this->request->isAJAX()) return $this->response->setJSON(['ok' => true, 'msg' => $msg]);
        return $this->back('ok', $msg);
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
        session_write_close();
        try { $res = (new SystemUpdater())->applyOnline($app, $this->db); }
        catch (\Throwable $e) { $this->audit('update_failed', 'updates', $e->getMessage()); return $this->back('err', $e->getMessage()); }
        SettingModel::put('update_last_check', '');
        $this->audit('update_applied', 'updates', $app['version'] . ' (' . $res['files'] . ' files)');
        $msg = "Updated to {$app['version']}. Backups: {$res['codeBackup']}, {$res['dbBackup']}.";
        if ($this->request->isAJAX()) return $this->response->setJSON(['ok' => true, 'msg' => $msg]);
        return $this->back('ok', $msg);
    }
}
