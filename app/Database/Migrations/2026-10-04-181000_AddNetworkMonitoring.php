<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNetworkMonitoring extends Migration
{
    public function up(): void
    {
        if (!$this->db->tableExists('network_devices')) {
            return;
        }

        $fields = [];
        if (!$this->db->fieldExists('monitor_enabled', 'network_devices')) {
            $fields['monitor_enabled'] = ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0];
        }
        if (!$this->db->fieldExists('monitor_port', 'network_devices')) {
            $fields['monitor_port'] = ['type' => 'SMALLINT', 'unsigned' => true, 'null' => true];
        }
        if (!$this->db->fieldExists('monitor_state', 'network_devices')) {
            $fields['monitor_state'] = ['type' => 'VARCHAR', 'constraint' => 12, 'default' => 'unknown'];
        }
        if (!$this->db->fieldExists('last_checked', 'network_devices')) {
            $fields['last_checked'] = ['type' => 'DATETIME', 'null' => true];
        }
        if (!$this->db->fieldExists('last_seen', 'network_devices')) {
            $fields['last_seen'] = ['type' => 'DATETIME', 'null' => true];
        }
        if ($fields) {
            $this->forge->addColumn('network_devices', $fields);
        }
    }

    public function down(): void
    {
        if (!$this->db->tableExists('network_devices')) {
            return;
        }
        foreach (['last_seen', 'last_checked', 'monitor_state', 'monitor_port', 'monitor_enabled'] as $field) {
            if ($this->db->fieldExists($field, 'network_devices')) {
                $this->forge->dropColumn('network_devices', $field);
            }
        }
    }
}
