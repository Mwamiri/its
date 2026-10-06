<?php
namespace App\Controllers;
use App\Models\SettingModel;
use App\Models\AuditModel;
class ItsBase extends BaseController {
    protected $helpers = ['text', 'form', 'url'];
    protected $db;
    public function __construct() {
        $this->db = \Config\Database::connect();
        foreach (['clients','signatures','photos','branding'] as $sub) {
            $dir = FCPATH . 'uploads/' . $sub;
            if (!is_dir($dir)) @mkdir($dir, 0775, true);
        }
        $ht = FCPATH . 'uploads/.htaccess';
        if (!file_exists($ht)) @file_put_contents($ht, "<IfModule mod_php.c>\nphp_flag engine off\n</IfModule>\nRemoveHandler .php .phtml .php3 .php4 .php5 .phar\n<FilesMatch \"\\.(?i:php|phtml|phar)$\">\nRequire all denied\n</FilesMatch>\n");
        if (!is_dir(WRITEPATH . 'backups')) @mkdir(WRITEPATH . 'backups', 0775, true);
    }
    private ?array $liveUser = null;
    private bool $liveChecked = false;
    /** Session user, re-validated against the database so deleted or deactivated accounts lose access immediately. */
    protected function user(): ?array {
        $s = session('its_user');
        if (!$s) return null;
        if ($this->liveChecked) return $this->liveUser;
        $this->liveChecked = true;
        $timeout = max(5, (int) SettingModel::get('session_timeout', '120')) * 60;
        if (time() - (int) session('its_last') > $timeout && session('its_last')) {
            session()->remove(['its_user', 'its_last']);
            return $this->liveUser = null;
        }
        session()->set('its_last', time());
        $row = $this->db->table('users')->select('id, username, name, role, client_id, active')->where('id', (int) ($s['id'] ?? 0))->get()->getRowArray();
        if (!$row || (int) $row['active'] !== 1) {
            session()->remove('its_user');
            return $this->liveUser = null;
        }
        unset($row['active']);
        session()->set('its_user', $row);
        return $this->liveUser = $row;
    }
    protected function needLogin() {
        $u = $this->user();
        if (!$u) return redirect()->to(base_url('its-install'));
        if (($u['role'] ?? '') === 'client') return redirect()->to(base_url('its-portal'));
        if (($u['role'] ?? '') === 'admin' && SettingModel::get('require_2fa_admin', '0') === '1' && !$this->hasTotp($u['id'])) {
            return redirect()->to(base_url('its-security'))->with('err', 'Two-factor authentication is required for administrators. Enable it to continue.');
        }
        $module = self::MODULES[(new \ReflectionClass($this))->getShortName()] ?? null;
        if ($module) {
            $need = $this->request->getMethod() === 'GET' ? 1 : 2;
            if (!\App\Libraries\Perm::can($u['role'], $module, $need)) {
                if ($this->request->isAJAX()) return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'error' => 'Not permitted']);
                return redirect()->to(base_url($module === 'dashboard' ? 'its-help' : 'its-dashboard'))->with('err', 'Your role does not have access to that area.');
            }
        }
        return null;
    }
    protected const MODULES = ['ItsTickets' => 'tickets', 'ItsBoard' => 'board', 'ItsQuotes' => 'quotes', 'ItsClients' => 'clients', 'ItsAssets' => 'assets', 'ItsNetwork' => 'network', 'ItsForms' => 'forms', 'ItsReport' => 'tickets', 'ItsReportBuilder' => 'reports', 'ItsKb' => 'kb'];
    protected function hasTotp(int $id): bool {
        return (int) ($this->db->table('users')->select('totp_enabled')->where('id', $id)->get()->getRow()->totp_enabled ?? 0) === 1;
    }
    protected function needClient() {
        $u = $this->user();
        if (!$u) return redirect()->to(base_url('its-login'));
        if (($u['role'] ?? '') !== 'client' || empty($u['client_id'])) return redirect()->to(base_url('its-dashboard'));
        return null;
    }
    protected function homeFor(?array $u): string { return base_url(($u['role'] ?? '') === 'client' ? 'its-portal' : 'its-dashboard'); }
    protected function needAdmin() { $u = $this->user(); if (!$u || $u['role'] !== 'admin') return redirect()->to(base_url('its-dashboard')); return null; }
    protected function setting(string $k, ?string $d = null): ?string { return SettingModel::get($k, $d); }
    protected function audit(string $a, string $m = '', string $d = ''): void {
        (new AuditModel())->insert(['user_id' => $this->user()['id'] ?? null, 'action' => $a, 'module' => $m, 'details' => $d, 'ip_address' => $this->request->getIPAddress()]);
    }
    protected function seq(string $name, string $prefix): string {
        $this->db->table('sequences')->where('name', $name)->set('current_value', 'current_value + 1', false)->update();
        $v = (int) $this->db->table('sequences')->where('name', $name)->get()->getRow()->current_value;
        return $prefix . date('Ymd') . '-' . str_pad((string) $v, 4, '0', STR_PAD_LEFT);
    }
    protected function saveUpload(\CodeIgniter\HTTP\Files\UploadedFile $file, string $sub): ?string {
        if (!$file->isValid() || $file->hasMoved()) return null;
        $map = ['image/png'=>'png','image/jpeg'=>'jpg','image/gif'=>'gif','image/webp'=>'webp'];
        $mime = $file->getMimeType();
        if (!isset($map[$mime]) || $file->getSize() > 5 * 1024 * 1024) return null;
        $name = date('Ymd-His') . '-' . bin2hex(random_bytes(6)) . '.' . $map[$mime];
        $dir = FCPATH . 'uploads/' . $sub;
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $file->move($dir, $name);
        return 'uploads/' . $sub . '/' . $name;
    }
    protected function logCustom(string $cat, string $change, string $details = ''): void {
        $log = json_decode((string) SettingModel::get('customization_log', '[]'), true) ?: [];
        $log[] = ['timestamp' => date('Y-m-d H:i:s'), 'user' => $this->user()['name'] ?? 'system', 'category' => $cat, 'change' => $change, 'details' => $details];
        SettingModel::put('customization_log', json_encode(array_slice($log, -100)));
    }
}