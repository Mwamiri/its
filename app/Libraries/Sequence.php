<?php

namespace App\Libraries;

/** Gap-free document numbers (TCK-20261007-0001) that stay unique under concurrent requests. */
class Sequence
{
    public static function next(string $name, string $prefix): string
    {
        $db = \Config\Database::connect();
        if ($db->DBDriver === 'MySQLi') {
            // LAST_INSERT_ID(expr) stores the incremented value on this connection, so the read cannot see another request's increment.
            $db->query('UPDATE ' . $db->prefixTable('sequences') . ' SET current_value = LAST_INSERT_ID(current_value + 1) WHERE name = ?', [$name]);
            $v = (int) $db->insertID();
        } else {
            $db->transStart();
            $db->table('sequences')->where('name', $name)->set('current_value', 'current_value + 1', false)->update();
            $v = (int) $db->table('sequences')->where('name', $name)->get()->getRow()->current_value;
            $db->transComplete();
        }
        if ($v < 1) throw new \RuntimeException("Sequence '$name' is missing; run php spark migrate.");
        return $prefix . date('Ymd') . '-' . str_pad((string) $v, 4, '0', STR_PAD_LEFT);
    }
}
