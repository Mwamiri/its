<?php

namespace App\Libraries;

/**
 * Admin-defined extra fields for clients, assets and tickets.
 * Definitions live in custom_fields; values in custom_field_values (one row per field per record).
 */
class CustomFields
{
    public const ENTITIES = ['clients' => 'Clients', 'assets' => 'Assets', 'tickets' => 'Tickets'];
    public const TYPES = ['text' => 'Text', 'textarea' => 'Long text', 'number' => 'Number', 'date' => 'Date', 'select' => 'Dropdown', 'checkbox' => 'Yes / no', 'email' => 'Email', 'phone' => 'Phone', 'url' => 'Link'];

    private static array $defs = [];

    /** Active definitions for an entity, in display order. Returns [] if the v3 migration has not run. */
    public static function defs(string $entity, bool $includeInactive = false): array
    {
        $cacheKey = $entity . ($includeInactive ? ':all' : '');
        if (isset(self::$defs[$cacheKey])) return self::$defs[$cacheKey];
        $db = \Config\Database::connect();
        if (! $db->tableExists('custom_fields')) return self::$defs[$cacheKey] = [];
        $q = $db->table('custom_fields')->where('entity', $entity)->orderBy('sort_order')->orderBy('id');
        if (! $includeInactive) $q->where('active', 1);
        $rows = $q->get()->getResultArray();
        foreach ($rows as &$r) $r['options'] = json_decode((string) $r['options_json'], true) ?: [];
        return self::$defs[$cacheKey] = $rows;
    }

    public static function reset(): void { self::$defs = []; }

    /** @return array<int, array<string, string>> entity_id => [field_key => value] */
    public static function values(string $entity, array $ids): array
    {
        $defs = self::defs($entity);
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (! $defs || ! $ids) return [];
        $byId = array_column($defs, 'field_key', 'id');
        $out = [];
        $rows = \Config\Database::connect()->table('custom_field_values')->whereIn('field_id', array_keys($byId))->whereIn('entity_id', $ids)->get()->getResultArray();
        foreach ($rows as $r) $out[(int) $r['entity_id']][$byId[$r['field_id']]] = (string) $r['value'];
        return $out;
    }

    public static function one(string $entity, int $id): array
    {
        return self::values($entity, [$id])[$id] ?? [];
    }

    /** Validates posted values (cf[key]); returns [clean values, error messages]. */
    public static function validate(string $entity, $posted): array
    {
        $posted = is_array($posted) ? $posted : [];
        $clean = []; $errors = [];
        foreach (self::defs($entity) as $d) {
            $raw = $posted[$d['field_key']] ?? '';
            $v = is_string($raw) ? trim($raw) : '';
            if ($d['type'] === 'checkbox') $v = $v !== '' && $v !== '0' ? '1' : '0';
            $v = mb_substr($v, 0, $d['type'] === 'textarea' ? 5000 : 500);
            $ok = match (true) {
                $v === '' || $d['type'] === 'checkbox' => true,
                $d['type'] === 'number' => is_numeric($v),
                $d['type'] === 'date' => (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $v),
                $d['type'] === 'email' => filter_var($v, FILTER_VALIDATE_EMAIL) !== false,
                $d['type'] === 'url' => filter_var($v, FILTER_VALIDATE_URL) !== false && preg_match('#^https?://#i', $v),
                $d['type'] === 'phone' => (bool) preg_match('/^[0-9+().\-\s]{5,30}$/', $v),
                $d['type'] === 'select' => in_array($v, $d['options'], true),
                default => true,
            };
            if (! $ok) $errors[] = $d['label'] . ' is not valid.';
            elseif ($d['required'] && ($v === '' || ($d['type'] === 'checkbox' && $v !== '1'))) $errors[] = $d['label'] . ' is required.';
            $clean[$d['field_key']] = $v;
        }
        return [$clean, $errors];
    }

    public static function save(string $entity, int $id, array $clean): void
    {
        if (! $id || ! $clean) return;
        $db = \Config\Database::connect();
        foreach (self::defs($entity) as $d) {
            if (! array_key_exists($d['field_key'], $clean)) continue;
            $db->table('custom_field_values')->where('field_id', $d['id'])->where('entity_id', $id)->delete();
            if ($clean[$d['field_key']] !== '') $db->table('custom_field_values')->insert(['field_id' => $d['id'], 'entity_id' => $id, 'value' => $clean[$d['field_key']]]);
        }
    }

    /** Human-readable value for display. */
    public static function display(array $def, ?string $value): string
    {
        if ($value === null || $value === '') return '';
        return $def['type'] === 'checkbox' ? ($value === '1' ? 'Yes' : 'No') : $value;
    }
}
