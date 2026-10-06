<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAssetServiceLog extends Migration
{
    public function up()
    {
        $this->forge->addColumn('assets', [
            'hostname'       => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'cpu'            => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'ram'            => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'storage'        => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'os'             => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'purchase_date'  => ['type' => 'DATE', 'null' => true],
            'warranty_until' => ['type' => 'DATE', 'null' => true],
        ]);
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'asset_id'   => ['type' => 'INT', 'unsigned' => true],
            'ticket_id'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'user_id'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'event_type' => ['type' => 'VARCHAR', 'constraint' => 20],
            'component'  => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'old_spec'   => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'new_spec'   => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'details'    => ['type' => 'TEXT', 'null' => true],
            'parts_cost' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'labor_cost' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('asset_id');
        $this->forge->createTable('asset_events', true);
    }

    public function down()
    {
        $this->forge->dropTable('asset_events', true);
        $this->forge->dropColumn('assets', ['hostname', 'cpu', 'ram', 'storage', 'os', 'purchase_date', 'warranty_until']);
    }
}