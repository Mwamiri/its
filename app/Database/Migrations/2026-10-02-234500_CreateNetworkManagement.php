<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNetworkManagement extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'client_id' => ['type' => 'INT', 'unsigned' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 190],
            'device_type' => ['type' => 'VARCHAR', 'constraint' => 40],
            'hostname' => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'mac_address' => ['type' => 'VARCHAR', 'constraint' => 32, 'null' => true],
            'manufacturer' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'model' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'location' => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'active'],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'TIMESTAMP', 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('client_id');
        $this->forge->addKey('ip_address');
        $this->forge->createTable('network_devices', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'client_id' => ['type' => 'INT', 'unsigned' => true],
            'network_device_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 190],
            'camera_type' => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'ip'],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'location' => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'recorder' => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'channel_number' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'active'],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'TIMESTAMP', 'default' => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP')],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('client_id');
        $this->forge->addKey('network_device_id');
        $this->forge->createTable('cameras', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'client_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'network_device_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'label' => ['type' => 'VARCHAR', 'constraint' => 190],
            'username' => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'secret_ciphertext' => ['type' => 'MEDIUMTEXT'],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME'],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('client_id');
        $this->forge->addKey('network_device_id');
        $this->forge->createTable('vault_credentials', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('vault_credentials', true);
        $this->forge->dropTable('cameras', true);
        $this->forge->dropTable('network_devices', true);
    }
}
