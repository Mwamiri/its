<?php
namespace App\Models;
use CodeIgniter\Model;

class SettingModel extends Model {
    protected $table = 'settings';
    protected $primaryKey = 'setting_key';
    protected $useAutoIncrement = false;
    protected $useTimestamps = false;
    protected $allowedFields = ['setting_key','setting_value'];

    /** All settings, loaded with one query on first use and kept for the rest of the request. */
    private static ?array $cache = null;

    private static function all(): array {
        if (self::$cache === null) {
            self::$cache = array_column((new static())->select('setting_key, setting_value')->findAll(), 'setting_value', 'setting_key');
        }
        return self::$cache;
    }

    public static function get(string $k, ?string $d = null): ?string {
        return self::all()[$k] ?? $d;
    }

    public static function put(string $k, ?string $v): void {
        $m = new static();
        if (array_key_exists($k, self::all())) {
            $m->update($k, ['setting_value' => $v]);
        } else {
            $m->insert(['setting_key' => $k, 'setting_value' => $v]);
        }
        self::$cache[$k] = $v;
    }

    /** Drops the per-request cache (called at the end of each request so long-running workers never serve stale values). */
    public static function flush(): void {
        self::$cache = null;
    }
}