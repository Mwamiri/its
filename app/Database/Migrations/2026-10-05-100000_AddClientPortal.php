<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddClientPortal extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('users') && !$this->db->fieldExists('client_id', 'users')) {
            $this->forge->addColumn('users', [
                'client_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            ]);
        }

        if (!$this->db->tableExists('ticket_updates')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'ticket_id' => ['type' => 'INT', 'unsigned' => true],
                'user_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'author_name' => ['type' => 'VARCHAR', 'constraint' => 190],
                'author_role' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'staff'],
                'visibility' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'public'],
                'message' => ['type' => 'TEXT'],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['ticket_id', 'visibility']);
            $this->forge->createTable('ticket_updates', true);
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('ticket_updates', true);
        if ($this->db->tableExists('users') && $this->db->fieldExists('client_id', 'users')) {
            $this->forge->dropColumn('users', 'client_id');
        }
    }
}
