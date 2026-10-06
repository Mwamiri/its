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
    protected function user(): ?array { return session('its_user') ?? null; }
    protected function needLogin() { if (!$this->user()) return redirect()->to('/its-install'); return null; }
    protected function needAdmin() { $u = $this->user(); if (!$u || $u['role'] !== 'admin') return redirect()->to('/its-dashboard'); return null; }
    protected function setting(string $k, ?string $d = null): ?string { return SettingModel::get($k, $d); }
    protected function audit(string $a, string $m = '', string $d = ''): void {
        AuditModel::insert(['user_id' => $this->user()['id'] ?? null, 'action' => $a, 'module' => $m, 'details' => $d, 'ip_address' => $this->request->getIPAddress()]);
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
        SettingModel::set('customization_log', json_encode(array_slice($log, -100)));
    }
}