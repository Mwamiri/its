<?php

namespace App\Libraries;

use App\Models\SettingModel;

/** SLA targets per priority (hours), counted in business hours when configured. */
class Sla
{
    public const DEFAULTS = ['urgent' => [1, 4], 'high' => [4, 8], 'medium' => [8, 24], 'low' => [24, 72]];

    public static function targets(): array
    {
        $saved = json_decode((string) SettingModel::get('sla_hours', ''), true);
        $out = self::DEFAULTS;
        if (is_array($saved)) foreach ($saved as $p => $v) if (isset($out[$p]) && is_array($v) && count($v) === 2) $out[$p] = [max(1, (int) $v[0]), max(1, (int) $v[1])];
        return $out;
    }

    /** Adds hours, skipping non-business time when business hours are enabled (Mon-Fri 08:00-17:00). */
    public static function addHours(int $from, int $hours): int
    {
        if (SettingModel::get('sla_business_hours', '0') !== '1') return $from + $hours * 3600;
        $t = $from; $left = $hours * 3600;
        for ($guard = 0; $left > 0 && $guard < 5000; $guard++) {
            $dow = (int) date('N', $t);
            $dayStart = strtotime(date('Y-m-d 08:00:00', $t));
            $dayEnd = strtotime(date('Y-m-d 17:00:00', $t));
            if ($dow > 5 || $t >= $dayEnd) { $t = strtotime(date('Y-m-d 08:00:00', strtotime('+1 day', $t))); continue; }
            if ($t < $dayStart) $t = $dayStart;
            $take = min($left, $dayEnd - $t);
            $t += $take; $left -= $take;
        }
        return $t;
    }

    /** @return array{response_due_at:string,due_at:string} */
    public static function dueFor(string $priority, ?int $from = null): array
    {
        $from ??= time();
        [$resp, $resolve] = self::targets()[$priority] ?? self::targets()['medium'];
        return ['response_due_at' => date('Y-m-d H:i:s', self::addHours($from, $resp)), 'due_at' => date('Y-m-d H:i:s', self::addHours($from, $resolve))];
    }

    /** ok | warning (<25% left) | breached | met | none */
    public static function state(array $t): string
    {
        if (empty($t['due_at'])) return 'none';
        $closed = in_array($t['status'] ?? '', ['completed', 'closed'], true);
        $end = $closed ? strtotime((string) ($t['resolved_at'] ?: date('Y-m-d H:i:s'))) : time();
        $due = strtotime((string) $t['due_at']);
        if ($end > $due) return 'breached';
        if ($closed) return 'met';
        $start = strtotime((string) ($t['created_at'] ?? 'now'));
        return ($due - $end) < max(1, ($due - $start)) * 0.25 ? 'warning' : 'ok';
    }

    public static function remaining(array $t): string
    {
        if (empty($t['due_at'])) return '';
        $d = strtotime((string) $t['due_at']) - time();
        $abs = abs($d); $h = intdiv($abs, 3600); $m = intdiv($abs % 3600, 60);
        $txt = $h >= 48 ? intdiv($h, 24) . 'd ' . ($h % 24) . 'h' : ($h . 'h ' . $m . 'm');
        return $d >= 0 ? $txt . ' left' : $txt . ' overdue';
    }
}