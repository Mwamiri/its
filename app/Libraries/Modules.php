<?php

namespace App\Libraries;

use App\Models\SettingModel;

/**
 * Module manager: optional features an administrator can switch off. Core areas (dashboard, tickets,
 * clients, assets, admin, security, updates) are always on. A disabled module disappears from menus
 * and its URLs are refused by the ModuleGate filter.
 */
class Modules
{
    /** key => [name, description, URL prefixes (first path segment) it owns] */
    public const LIST = [
        'website' => ['Public website & CMS', 'Company website at the site address, page builder, leads inbox.', ['its-site']],
        'portal' => ['Client portal', 'Clients sign in to report issues, approve quotes and see their equipment.', ['its-portal']],
        'board' => ['Ticket board', 'Kanban view of tickets by status.', ['its-board']],
        'quotes' => ['Quotes', 'Quotations with emailed approval links.', ['its-quotes', 'its-approve']],
        'network' => ['Network, CCTV & vault', 'Network devices, monitoring, cameras and the credential vault.', ['its-network', 'its-cameras', 'its-vault', 'its-cron-device-monitor']],
        'forms' => ['Forms', 'Form builder, responses and exports.', ['its-forms', 'its-form', 'its-form-reports', 'its-form-file']],
        'kb' => ['Knowledge base', 'Help articles and canned replies.', ['its-kb', 'its-canned', 'its-portal-kb']],
        'training' => ['Training', 'Training sessions, attendance and QR sign-in.', ['its-training', 'its-attend']],
        'billing' => ['Billing & calendar', 'Retainers, billable hours and the iCal feed.', ['its-billing', 'its-calendar-token', 'its-calendar.ics']],
        'import' => ['Bulk import', 'CSV/Excel import of clients, assets and attendees.', ['its-import']],
        'reports' => ['Report builder', 'Custom reports with saved templates.', ['its-report-builder']],
        'api' => ['REST API & webhooks', 'API keys, /api/v1 endpoints and webhooks.', ['api']],
    ];

    private static ?array $off = null;

    public static function enabled(string $key): bool
    {
        if (!isset(self::LIST[$key])) return true;
        if (self::$off === null) {
            self::$off = [];
            foreach (array_keys(self::LIST) as $k) if (SettingModel::get('module_off_' . $k, '0') === '1') self::$off[$k] = true;
        }
        return !isset(self::$off[$key]);
    }

    public static function set(string $key, bool $on): void
    {
        if (!isset(self::LIST[$key])) return;
        SettingModel::put('module_off_' . $key, $on ? '0' : '1');
        self::$off = null;
    }

    /** Which module owns a URL path (first segment, with "its-x-y" falling back to its longest known prefix). */
    public static function forPath(string $path): ?string
    {
        $seg = strtolower(explode('/', trim($path, '/'))[0] ?? '');
        if ($seg === '') return null;
        $best = null; $len = 0;
        foreach (self::LIST as $k => [, , $prefixes]) {
            foreach ($prefixes as $p) {
                if (($seg === $p || str_starts_with($seg, $p . '-')) && strlen($p) > $len) { $best = $k; $len = strlen($p); }
            }
        }
        return $best;
    }

    public static function reset(): void { self::$off = null; }
}
