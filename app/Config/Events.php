<?php

namespace Config;

use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\FrameworkException;
use CodeIgniter\HotReloader\HotReloader;

/*
 * --------------------------------------------------------------------
 * Application Events
 * --------------------------------------------------------------------
 * Events allow you to tap into the execution of the program without
 * modifying or extending core files. This file provides a central
 * location to define your events, though they can always be added
 * at run-time, also, if needed.
 *
 * You create code that can execute by subscribing to events with
 * the 'on()' method. This accepts any form of callable, including
 * Closures, that will be executed when the event is triggered.
 *
 * Example:
 *      Events::on('create', [$myInstance, 'myMethod']);
 */

// While the updater replaces files, other visitors see a short "updating" page (v3).
// A flag older than 15 minutes is treated as stale (e.g. a crashed request) and ignored.
Events::on('pre_system', static function (): void {
    $flag = WRITEPATH . 'maintenance.flag';
    if (is_cli() || ! is_file($flag)) return;
    [$since, $token] = array_pad(explode('|', (string) @file_get_contents($flag), 2), 2, '');
    if (time() - (int) $since > 900) { @unlink($flag); return; }
    // The updater's own post-update health check carries the one-time token and is let through.
    if ($token !== '' && hash_equals($token, (string) ($_SERVER['HTTP_X_ITS_HEALTH'] ?? ''))) return;
    http_response_code(503);
    header('Retry-After: 30');
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta http-equiv="refresh" content="20"><title>Updating</title><body style="font-family:system-ui,sans-serif;display:grid;place-items:center;min-height:90vh;color:#0f172a;background:#f8fafc"><div style="text-align:center"><h1 style="font-size:22px">The system is being updated</h1><p>This usually takes less than a minute. The page will reload automatically.</p></div></body>';
    exit;
}, Events::PRIORITY_HIGH);

Events::on('pre_system', static function (): void {
    if (ENVIRONMENT !== 'testing') {
        $value = ini_get('zlib.output_compression');

        if (filter_var($value, FILTER_VALIDATE_BOOLEAN) || (int) $value > 0) {
            throw FrameworkException::forEnabledZlibOutputCompression();
        }

        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        ob_start(static fn ($buffer) => $buffer);
    }

    /*
     * --------------------------------------------------------------------
     * Debug Toolbar Listeners.
     * --------------------------------------------------------------------
     * If you delete, they will no longer be collected.
     */
    if (CI_DEBUG && ! is_cli()) {
        Events::on('DBQuery', 'CodeIgniter\Debug\Toolbar\Collectors\Database::collect');
        service('toolbar')->respond();
        // Hot Reload route - for framework use on the hot reloader.
        if (ENVIRONMENT === 'development') {
            service('routes')->get('__hot-reload', static function (): void {
                (new HotReloader())->run();
            });
        }
    }
});

// Per-request caches (settings, role permissions) are cleared once the response is sent.
Events::on('post_system', static function (): void {
    \App\Models\SettingModel::flush();
    \App\Libraries\Perm::reset();
    \App\Libraries\Modules::reset();
});
