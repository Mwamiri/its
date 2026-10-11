<?php

namespace App\Libraries;

use CodeIgniter\Events\Events;
use RuntimeException;
use Throwable;
use ZipArchive;

/**
 * Extensions: add-on packages that extend ITSupportz without a system update.
 *
 * An extension is a zip containing a module.json manifest. It may add controllers, views,
 * routes, database tables, dashboard widgets, menu items, permissions, settings, help articles,
 * assets, and code that reacts to system events. Installed extensions are listed in
 * writable/extensions.json. Developers: php spark its:extension make <id>.
 */
class Extensions
{
    /** Events extensions can listen to (Events::on(name, callback)) */
    public const EVENTS = [
        'its:ticket_created'  => 'A ticket was created: ($ticket)',
        'its:ticket_updated'  => 'A ticket was updated: ($ticket, $changes)',
        'its:ticket_closed'   => 'A ticket was closed: ($ticket)',
        'its:quote_approved'  => 'A quote was approved: ($quote)',
        'its:user_login'      => 'A user signed in: ($user)',
        'its:daily'           => 'Once a day, from php spark its:health',
    ];

    public const ID_PATTERN = '/^[a-z][a-z0-9_]{2,29}$/';

    private static ?string $dir      = null;
    private static ?string $registry = null;
    private static ?string $public   = null;
    private static ?array $cache     = null;
    private static bool $booted      = false;
    private static ?string $revision = null;

    // ---------------------------------------------------------------- paths

    public static function dir(): string
    {
        return self::$dir ?? ROOTPATH . 'modules/';
    }

    public static function registryFile(): string
    {
        return self::$registry ?? WRITEPATH . 'extensions.json';
    }

    public static function publicDir(): string
    {
        return self::$public ?? FCPATH . 'extensions/';
    }

    /** For tests: swap to alternate paths. */
    public static function usePaths(?string $dir, ?string $registry, ?string $public): void
    {
        [self::$dir, self::$registry, self::$public] = [$dir, $registry, $public];
        self::$cache    = null;
        self::$revision = null;
        self::$booted   = false;
    }

    // ---------------------------------------------------------------- registry

    /**
     * Installed extensions: id => [version, namespace, enabled, installed_at, publisher].
     */
    public static function registry(): array
    {
        $data = json_decode((string) @file_get_contents(self::registryFile()), true);

        return is_array($data) ? $data : [];
    }

    private static function saveRegistry(array $data): void
    {
        ksort($data);
        file_put_contents(self::registryFile(), json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        self::$cache    = null;
        self::$revision = null;
    }

    /** Changes whenever an extension is installed, removed, suspended or resumed. */
    public static function revision(): string
    {
        return self::$revision ??= md5(self::registryFile() . '|' . json_encode(self::registry()));
    }

    /** Installed extensions with their manifests: id => manifest + ['_reg' => registry entry]. */
    public static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }
        $out = [];
        foreach (self::registry() as $id => $reg) {
            $file = self::dir() . $id . '/module.json';
            $m    = is_file($file) ? json_decode((string) file_get_contents($file), true) : null;
            if (is_array($m) && ($m['id'] ?? '') === $id) {
                $out[$id] = $m + ['_reg' => $reg];
            }
        }

        return self::$cache = $out;
    }

    /** Extensions switched on (enabled in registry). */
    public static function active(): array
    {
        return array_filter(self::all(), static fn ($m) => ($m['_reg']['enabled'] ?? true) && empty($m['_reg']['suspended']));
    }

    public static function isActive(string $id): bool
    {
        return isset(self::active()[$id]);
    }

    /** Enable or disable an installed extension. */
    public static function setEnabled(string $id, bool $on): void
    {
        $reg = self::registry();
        if (! isset($reg[$id])) {
            return;
        }
        $reg[$id]['enabled'] = $on;
        self::saveRegistry($reg);
    }

    /** Recovery mode: switch an extension off after it caused an error. */
    public static function suspend(string $id, string $reason): void
    {
        $reg = self::registry();
        if (! isset($reg[$id]) || ! empty($reg[$id]['suspended'])) {
            return;
        }
        $reg[$id]['suspended'] = ['at' => date('c'), 'reason' => mb_substr($reason, 0, 500)];
        self::saveRegistry($reg);
        try {
            model(\App\Models\AuditModel::class)->add('extension_suspended', 0, 0, "Extension {$id} suspended: " . mb_substr($reason, 0, 200));
        } catch (Throwable) {
        }
    }

    public static function resume(string $id): void
    {
        $reg = self::registry();
        if (isset($reg[$id])) {
            unset($reg[$id]['suspended']);
            self::saveRegistry($reg);
        }
    }

    // ---------------------------------------------------------------- what extensions add

    /** Menu links of active extensions: section => [[label, url, icon], ...]. */
    public static function menu(): array
    {
        $out = [];
        foreach (self::active() as $m) {
            foreach ((array) ($m['menu'] ?? []) as $item) {
                $section         = (string) ($item['section'] ?? 'Extensions');
                $out[$section][] = [
                    'label' => (string) ($item['label'] ?? $m['name']),
                    'url'   => trim((string) ($item['url'] ?? ''), '/'),
                    'icon'  => preg_replace('/[^a-z0-9-]/', '', (string) ($item['icon'] ?? 'puzzle')),
                ];
            }
        }

        return $out;
    }

    /** Dashboard widgets: [[title, view, wide], ...]. */
    public static function widgets(): array
    {
        $out = [];
        foreach (self::active() as $m) {
            foreach ((array) ($m['widgets'] ?? []) as $w) {
                $out[] = ['title' => (string) ($w['title'] ?? $m['name']), 'view' => (string) $w['view'], 'wide' => ! empty($w['wide'])];
            }
        }

        return $out;
    }

    /** Extra permissions: key => [label, roles that get it by default]. */
    public static function permissions(): array
    {
        $out = [];
        foreach (self::active() as $id => $m) {
            foreach ((array) ($m['permissions'] ?? []) as $key => $p) {
                if (preg_match('/^[a-z][a-z0-9_]{2,40}$/', (string) $key)) {
                    $out[$key] = is_array($p) ? [(string) ($p['label'] ?? $key), (array) ($p['roles'] ?? [])] : [(string) $p, []];
                }
            }
        }

        return $out;
    }

    /** Settings of active extensions: id => [name, fields]. */
    public static function settings(): array
    {
        $out = [];
        foreach (self::active() as $id => $m) {
            $fields = [];
            foreach ((array) ($m['settings'] ?? []) as $f) {
                $key = (string) ($f['key'] ?? '');
                if (str_starts_with($key, $id . '_') && preg_match('/^[a-z0-9_]{3,60}$/', $key)) {
                    $fields[$key] = $f + ['label' => $key, 'type' => 'text', 'default' => ''];
                }
            }
            if ($fields) {
                $out[$id] = ['name' => $m['name'] ?? $id, 'fields' => $fields];
            }
        }

        return $out;
    }

    /** Stylesheets or scripts of active extensions (url => ...). */
    public static function assets(string $kind): array
    {
        $out = [];
        foreach (self::active() as $id => $m) {
            foreach ((array) ($m['assets'][$kind] ?? []) as $file) {
                $file = ltrim(str_replace('\\', '/', (string) $file), '/');
                if (! str_contains($file, '..') && is_file(self::publicDir() . $id . '/' . $file)) {
                    $out[] = base_url('extensions/' . $id . '/' . $file) . '?v=' . rawurlencode((string) ($m['version'] ?? ''));
                }
            }
        }

        return $out;
    }

    /** Override directory for a core view, if an active extension provides it. */
    public static function overrideDir(string $view): ?string
    {
        $found = null;
        foreach (self::active() as $id => $m) {
            if (! empty($m['overrides']) && is_file(self::dir() . $id . '/Overrides/' . $view . (str_ends_with($view, '.php') ? '' : '.php'))) {
                $found = self::dir() . $id . '/Overrides/';
            }
        }

        return $found;
    }

    /** Boot active extensions once per request (registers event listeners, etc.). */
    public static function boot(): void
    {
        if (self::$booted || ! function_exists('is_installed') || ! is_installed()) {
            return;
        }
        self::$booted = true;
        foreach (self::active() as $id => $m) {
            $class = rtrim((string) ($m['namespace'] ?? ''), '\\') . '\\Extension';
            try {
                if (! class_exists($class) && is_file(self::dir() . $id . '/Extension.php')) {
                    require_once self::dir() . $id . '/Extension.php';
                }
                if (class_exists($class) && method_exists($class, 'boot')) {
                    $class::boot();
                }
            } catch (Throwable $e) {
                log_message('error', "Extension {$id} failed to start: " . $e->getMessage());
            }
        }
    }

    public static function trigger(string $event, mixed ...$args): void
    {
        try {
            self::boot();
            Events::trigger($event, ...$args);
        } catch (Throwable $e) {
            log_message('error', "Extension event {$event} failed: " . $e->getMessage());
        }
    }

    // ---------------------------------------------------------------- packages

    /**
     * Read and validate a package without installing it.
     *
     * @return array{manifest: array, files: list<string>, root: string, signed: bool, publisher: ?string, installed: ?string, problems: list<string>, warnings: list<string>}
     */
    public static function inspect(string $zipPath): array
    {
        $cfg = config(\Config\Extensions::class);
        $out = ['manifest' => [], 'files' => [], 'root' => '', 'signed' => false, 'publisher' => null, 'installed' => null, 'problems' => [], 'warnings' => []];
        if (filesize($zipPath) > $cfg->maxPackageMb * 1048576) {
            $out['problems'][] = "The package is larger than {$cfg->maxPackageMb} MB.";

            return $out;
        }
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            $out['problems'][] = 'This is not a valid zip file.';

            return $out;
        }

        $names = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $names[] = (string) $zip->getNameIndex($i);
        }
        $root = in_array('module.json', $names, true) ? '' : null;
        if ($root === null) {
            foreach ($names as $n) {
                if (preg_match('#^([^/]+)/module\.json$#', $n, $mm)) {
                    $root = $mm[1] . '/';
                    break;
                }
            }
        }
        if ($root === null) {
            $zip->close();
            $out['problems'][] = 'module.json was not found - this is not an ITSupportz extension (system updates go in System > Updates).';

            return $out;
        }
        $out['root'] = $root;
        $m = json_decode((string) $zip->getFromName($root . 'module.json'), true);
        if (! is_array($m)) {
            $zip->close();
            $out['problems'][] = 'module.json is not valid JSON.';

            return $out;
        }
        $out['manifest'] = $m;

        foreach ($names as $n) {
            if (! str_starts_with($n, $root) || str_ends_with($n, '/')) {
                continue;
            }
            $rel = substr($n, strlen($root));
            if ($rel === '' || str_contains($rel, '..') || str_contains($rel, '\\') || $rel[0] === '/' || preg_match('/^[A-Za-z]:/', $rel) || str_contains($rel, "\0")) {
                $out['problems'][] = "Unsafe file name: {$n}";

                continue;
            }
            $ext = strtolower(pathinfo($rel, PATHINFO_EXTENSION));
            if (! in_array($ext, $cfg->allowedExtensions, true) && basename($rel) !== '.htaccess') {
                $out['problems'][] = "File type not allowed in an extension: {$rel}";

                continue;
            }
            $out['files'][] = $rel;
        }
        $out['problems'] = array_merge($out['problems'], self::validateManifest($m));

        $hashes = [];
        foreach ($out['files'] as $rel) {
            if ($rel !== 'module.sig') {
                $hashes[$rel] = hash('sha256', (string) $zip->getFromName($root . $rel));
            }
        }
        $sig = json_decode((string) $zip->getFromName($root . 'module.sig'), true);
        $zip->close();
        [$out['signed'], $out['publisher'], $sigError] = self::checkSignature($hashes, is_array($sig) ? $sig : null);
        if ($sigError) {
            $out['problems'][] = $sigError;
        }

        $id = (string) ($m['id'] ?? '');
        if (isset(self::registry()[$id])) {
            $out['installed'] = (string) self::registry()[$id]['version'];
            if (version_compare((string) ($m['version'] ?? '0'), $out['installed'], '<')) {
                $out['problems'][] = "Version {$out['installed']} is already installed; this package is older ({$m['version']}).";
            }
        }
        if (! $out['signed']) {
            $out['warnings'][] = 'This package is not signed by a trusted publisher. Extensions run code with full access to your data: install only if you trust the source.';
        }

        return $out;
    }

    /** @return list<string> problems */
    public static function validateManifest(array $m): array
    {
        $p  = [];
        $id = (string) ($m['id'] ?? '');
        if (! preg_match(self::ID_PATTERN, $id)) {
            $p[] = 'The extension id must be 3-30 lowercase letters, digits or _ and start with a letter.';
        }
        $core = array_merge(
            array_keys(Modules::LIST),
            ['dashboard', 'tickets', 'clients', 'assets', 'admin', 'security', 'updates', 'users', 'settings', 'system', 'help', 'audit', 'extensions']
        );
        if (in_array($id, $core, true)) {
            $p[] = "\"{$id}\" is the name of a built-in module.";
        }
        if (trim((string) ($m['name'] ?? '')) === '') {
            $p[] = 'The extension has no name.';
        }
        if (! preg_match('/^\d+\.\d+(\.\d+)?[a-z]?$/', (string) ($m['version'] ?? ''))) {
            $p[] = 'The version must look like 1.0, 1.0a or 1.0.0.';
        }
        if (! preg_match('/^Modules\\\\[A-Z][A-Za-z0-9]{1,40}$/', (string) ($m['namespace'] ?? ''))) {
            $p[] = 'The namespace must look like Modules\\NoticeBoard.';
        }
        foreach (self::registry() as $otherId => $reg) {
            if ($otherId !== $id && ($reg['namespace'] ?? '') === ($m['namespace'] ?? '')) {
                $p[] = "The namespace {$m['namespace']} is already used by extension {$otherId}.";
            }
        }
        $needCore = (string) ($m['requires']['itsupportz'] ?? '0');
        $curVer   = (string) (SystemUpdater::version() ?: '0');
        if (version_compare($curVer, $needCore, '<')) {
            $p[] = "Needs ITSupportz {$needCore} or newer (this site runs {$curVer}). Update first.";
        }
        $needPhp = (string) ($m['requires']['php'] ?? '0');
        if ($needPhp && version_compare(PHP_VERSION, $needPhp, '<')) {
            $p[] = "Needs PHP {$needPhp} or newer (this server has " . PHP_VERSION . ').';
        }
        foreach ((array) ($m['requires']['extensions'] ?? []) as $phpExt) {
            if (! extension_loaded((string) $phpExt)) {
                $p[] = "Needs the PHP extension {$phpExt}.";
            }
        }

        return $p;
    }

    /**
     * @param  array<string, string> $hashes path => sha256
     * @return array{0: bool, 1: ?string, 2: ?string} [signed, publisher, error]
     */
    public static function checkSignature(array $hashes, ?array $sig): array
    {
        if ($sig === null) {
            return [false, null, null];
        }
        if (! function_exists('sodium_crypto_sign_verify_detached')) {
            return [false, null, null];
        }
        $publisher = (string) ($sig['publisher'] ?? '');
        $key       = config(\Config\Extensions::class)->trustedKeys[$publisher] ?? null;
        if ($key === null) {
            return [false, $publisher ?: null, null];
        }
        try {
            $ok = sodium_crypto_sign_verify_detached(
                (string) base64_decode((string) ($sig['signature'] ?? ''), true),
                self::signedMessage($hashes),
                (string) base64_decode($key, true)
            );
        } catch (Throwable) {
            $ok = false;
        }

        return $ok ? [true, $publisher, null] : [false, $publisher, 'Signature mismatch - package was altered after signing. Do not install.'];
    }

    /** The text that is signed: "path sha256\n" per file, sorted. */
    public static function signedMessage(array $hashes): string
    {
        ksort($hashes, SORT_STRING);

        return implode("\n", array_map(static fn ($p, $h) => $p . ' ' . $h, array_keys($hashes), $hashes));
    }

    /**
     * Install or update an extension from a checked package.
     *
     * @return list<string> log lines
     */
    public static function install(string $zipPath, bool $trustUnsigned = false): array
    {
        $info = self::inspect($zipPath);
        if ($info['problems']) {
            throw new RuntimeException(implode(' ', $info['problems']));
        }
        if (! $info['signed'] && ! $trustUnsigned) {
            throw new RuntimeException('Package is not signed. Tick "I trust this publisher" to install anyway.');
        }
        $m      = $info['manifest'];
        $id     = (string) $m['id'];
        $target = self::dir() . $id;
        $log    = [];
        @set_time_limit(600);

        if (! is_dir(self::dir())) {
            mkdir(self::dir(), 0755, true);
            file_put_contents(self::dir() . '.htaccess', "Require all denied\n");
        }

        $backupFile = Backup::run(30);
        $log[]      = $backupFile ? 'Backup taken: ' . basename($backupFile) : 'Backup skipped (writable/backups not writable).';

        $tmp = self::dir() . '.tmp_' . $id . '_' . bin2hex(random_bytes(4));
        $zip = new ZipArchive();
        $zip->open($zipPath);
        mkdir($tmp, 0755, true);
        foreach ($info['files'] as $rel) {
            $dest = $tmp . '/' . $rel;
            if (! is_dir(dirname($dest))) {
                mkdir(dirname($dest), 0755, true);
            }
            file_put_contents($dest, (string) $zip->getFromName($info['root'] . $rel));
        }
        $zip->close();

        $old = null;
        if (is_dir($target)) {
            $old = self::dir() . '.old_' . $id . '_' . date('YmdHis');
            rename($target, $old);
        }
        rename($tmp, $target);

        $previousRegistry = self::registry();
        try {
            service('autoloader')->addNamespace($m['namespace'], $target);
            $ran   = self::migrate($id, $m['namespace']);
            $log[] = $ran ? "Database: {$ran} change(s) applied" : 'Database: nothing to change';

            self::removeDir(self::publicDir() . $id);
            if (is_dir($target . '/assets')) {
                self::copyDir($target . '/assets', self::publicDir() . $id);
                $log[] = 'Public assets published';
            }

            $reg      = $previousRegistry;
            $reg[$id] = [
                'name'         => (string) $m['name'],
                'version'      => $m['version'],
                'namespace'    => $m['namespace'],
                'enabled'      => true,
                'installed_at' => date('c'),
                'publisher'    => $info['signed'] ? $info['publisher'] : null,
                'signed'       => $info['signed'],
            ];
            self::saveRegistry($reg);

            self::syncHelp($id, $m);
        } catch (Throwable $e) {
            self::removeDir($target);
            if ($old) {
                rename($old, $target);
            }
            self::saveRegistry($previousRegistry);
            throw new RuntimeException('Install failed; previous files restored: ' . $e->getMessage());
        }

        if ($old) {
            self::removeDir($old);
        }
        $action = $info['installed'] ? "updated from {$info['installed']} to {$m['version']}" : "installed ({$m['version']})";
        $log[]  = "Extension {$m['name']} {$action}.";
        try {
            model(\App\Models\AuditModel::class)->add(
                $info['installed'] ? 'extension_update' : 'extension_install',
                0, 0,
                "Extension {$id} {$action}" . ($info['signed'] ? " signed by {$info['publisher']}" : ' unsigned')
            );
        } catch (Throwable) {
        }

        return $log;
    }

    /**
     * Remove an extension. Its tables and settings are kept unless $removeData.
     */
    public static function uninstall(string $id, bool $removeData = false): void
    {
        $reg = self::registry();
        if (! isset($reg[$id])) {
            throw new RuntimeException('That extension is not installed.');
        }
        $m = self::all()[$id] ?? ['name' => $id, 'namespace' => $reg[$id]['namespace'] ?? ''];

        if ($removeData) {
            Backup::run(30);
            self::rollbackMigrations($id, (string) $m['namespace']);
            db_connect()->table('settings')->like('key', $id . '_', 'after')->delete();
        }
        db_connect()->table('help_articles')->where('source_ext', $id)->delete();
        self::removeDir(self::publicDir() . $id);
        self::removeDir(self::dir() . $id);
        unset($reg[$id]);
        self::saveRegistry($reg);
        try {
            model(\App\Models\AuditModel::class)->add('extension_uninstall', 0, 0, "Extension {$id} removed" . ($removeData ? ' with data' : ''));
        } catch (Throwable) {
        }
    }

    // ---------------------------------------------------------------- helpers

    private static function migrate(string $id, string $namespace): int
    {
        $runner = service('migrations', null, null, false);
        $runner->setNamespace($namespace);
        $before = count(self::ranMigrations($namespace));
        $runner->latest();

        return count(self::ranMigrations($namespace)) - $before;
    }

    private static function ranMigrations(string $namespace): array
    {
        $db = db_connect();
        if (! $db->tableExists('migrations')) {
            return [];
        }

        return $db->table('migrations')->where('namespace', $namespace)->get()->getResultArray();
    }

    private static function rollbackMigrations(string $id, string $namespace): void
    {
        $dir = self::dir() . $id . '/Database/Migrations/';
        if (! is_dir($dir) || $namespace === '') {
            return;
        }
        service('autoloader')->addNamespace($namespace, self::dir() . $id);
        $files = glob($dir . '*.php') ?: [];
        rsort($files);
        $db = db_connect();
        foreach ($files as $file) {
            $class = $namespace . '\\Database\\Migrations\\' . preg_replace('/^\d{4}-\d{2}-\d{2}-\d{6}_/', '', basename($file, '.php'));
            require_once $file;
            if (class_exists($class)) {
                (new $class(\Config\Database::forge()))->down();
            }
        }
        $db->table('migrations')->where('namespace', $namespace)->delete();
    }

    private static function syncHelp(string $id, array $m): void
    {
        $db  = db_connect();
        if (! $db->tableExists('help_articles')) {
            return;
        }
        $now = date('Y-m-d H:i:s');
        $db->table('help_articles')->where('source_ext', $id)->delete();
        foreach ((array) ($m['help'] ?? []) as $i => $a) {
            $slug = 'ext-' . $id . '-' . preg_replace('/[^a-z0-9-]/', '', strtolower((string) ($a['slug'] ?? $i)));
            $db->table('help_articles')->insert([
                'slug'       => substr($slug, 0, 100),
                'category'   => (string) ($a['category'] ?? $m['name']),
                'title'      => (string) ($a['title'] ?? $m['name']),
                'body'       => (string) ($a['body'] ?? ''),
                'sort_order' => 900 + $i,
                'source_ext' => $id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public static function removeDir(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($it as $f) {
            $f->isDir() ? @rmdir($f->getPathname()) : @unlink($f->getPathname());
        }
        @rmdir($dir);
    }

    private static function copyDir(string $from, string $to): void
    {
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($from, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);
        if (! is_dir($to)) {
            mkdir($to, 0755, true);
        }
        foreach ($it as $f) {
            $dest = $to . '/' . substr($f->getPathname(), strlen($from) + 1);
            if ($f->isDir()) {
                is_dir($dest) || mkdir($dest, 0755, true);
            } elseif (! in_array(strtolower($f->getExtension()), ['php', 'phtml', 'phar'], true)) {
                copy($f->getPathname(), $dest);
            }
        }
    }
}
