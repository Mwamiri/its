<?php

namespace App\Libraries;

/** Role/module permission levels: 0 none, 1 view, 2 edit. Admin is always full access. */
class Perm
{
    public const MODULES = ['dashboard' => 'Dashboard', 'tickets' => 'Tickets', 'board' => 'Board', 'quotes' => 'Quotes', 'clients' => 'Clients', 'assets' => 'Assets', 'network' => 'Network', 'forms' => 'Forms', 'reports' => 'Reports', 'kb' => 'Knowledge base', 'costs' => 'Costs & prices'];
    public const ROLES = ['manager', 'technician'];
    private const DEFAULTS = [
        'manager'    => ['dashboard' => 2, 'tickets' => 2, 'board' => 2, 'quotes' => 2, 'clients' => 2, 'assets' => 2, 'network' => 2, 'forms' => 2, 'reports' => 2, 'kb' => 2, 'costs' => 2],
        'technician' => ['dashboard' => 1, 'tickets' => 2, 'board' => 2, 'quotes' => 1, 'clients' => 1, 'assets' => 2, 'network' => 2, 'forms' => 2, 'reports' => 0, 'kb' => 2, 'costs' => 0],
    ];
    private static ?array $cache = null;

    public static function level(string $role, string $module): int
    {
        if ($role === 'admin') return 2;
        if (! isset(self::DEFAULTS[$role])) return 0;
        if (self::$cache === null) {
            self::$cache = [];
            try {
                foreach (\Config\Database::connect()->table('role_permissions')->get()->getResultArray() as $r) self::$cache[$r['role']][$r['module']] = (int) $r['level'];
            } catch (\Throwable $e) {
                self::$cache = [];
            }
        }
        return self::$cache[$role][$module] ?? self::DEFAULTS[$role][$module] ?? 0;
    }

    public static function can(string $role, string $module, int $need = 1): bool
    {
        return self::level($role, $module) >= $need;
    }

    public static function matrix(): array
    {
        $m = [];
        foreach (self::ROLES as $r) foreach (self::MODULES as $k => $_) $m[$r][$k] = self::level($r, $k);
        return $m;
    }

    public static function reset(): void { self::$cache = null; }
}