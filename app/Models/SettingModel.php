<?php
namespace App\Models;
use CodeIgniter\Model;

class SettingModel extends Model {
    protected $table = 'settings';
    protected $primaryKey = 'setting_key';
    protected $useAutoIncrement = false;
    protected $useTimestamps = false;
    protected $allowedFields = ['setting_key','setting_value'];

    public static function get(string $k, ?string $d = null): ?string {
        $row = (new static())->find($k);
        return $row['setting_value'] ?? $d;
    }

    public static function put(string $k, ?string $v): void {
        $m = new static();
        if ($m->find($k)) {
            $m->update($k, ['setting_value' => $v]);
        } else {
            $m->insert(['setting_key' => $k, 'setting_value' => $v]);
        }
    }
}