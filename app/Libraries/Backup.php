<?php

namespace App\Libraries;

class Backup
{
    public const TABLES = ['settings','clients','assets','tickets','tasks','task_photos','signatures','quotes','quote_items','maintenance_schedules','document_templates','login_attempts','network_devices','cameras','vault_credentials','custom_forms','form_submissions','users','ticket_updates','audit_log','role_permissions','time_entries','kb_articles','canned_replies','api_keys','webhooks','asset_events'];

    public static function sql(): string
    {
        $db = \Config\Database::connect();
        $sql = "-- IT Support CI4 backup " . date('c') . "\n";
        foreach (self::TABLES as $table) {
            if (! $db->tableExists($table)) continue;
            foreach ($db->table($table)->get()->getResultArray() as $row) {
                $vals = array_map(static fn($v) => $v === null ? 'NULL' : $db->escape($v), array_values($row));
                $sql .= "INSERT INTO `$table` (`" . implode('`,`', array_keys($row)) . "`) VALUES (" . implode(',', $vals) . ");\n";
            }
        }
        return $sql;
    }

    /** Writes a gzip backup to writable/backups and prunes files beyond $keep. */
    public static function run(int $keep = 14): ?string
    {
        $dir = WRITEPATH . 'backups/';
        if (! is_dir($dir) && ! @mkdir($dir, 0775, true)) return null;
        if (! is_file($dir . '.htaccess')) @file_put_contents($dir . '.htaccess', "Require all denied\n");
        $gz = gzencode(self::sql(), 9);
        $file = $dir . 'auto-' . date('Ymd-His') . '.sql.gz';
        if ($gz === false || file_put_contents($file, $gz) === false) return null;
        $all = glob($dir . 'auto-*.sql.gz') ?: [];
        rsort($all);
        foreach (array_slice($all, max(1, $keep)) as $old) @unlink($old);
        return $file;
    }

    public static function latest(): ?array
    {
        $all = glob(WRITEPATH . 'backups/auto-*.sql.gz') ?: [];
        if (! $all) return null;
        rsort($all);
        return ['file' => basename($all[0]), 'time' => filemtime($all[0]), 'size' => filesize($all[0]), 'count' => count($all)];
    }
}