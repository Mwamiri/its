<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEnterpriseFeatures extends Migration
{
    private function addCols(string $table, array $cols): void
    {
        foreach ($cols as $name => $def) {
            if (! $this->db->fieldExists($name, $table)) {
                $this->forge->addColumn($table, [$name => $def]);
            }
        }
    }

    public function up()
    {
        $this->addCols('users', [
            'totp_secret'     => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'totp_enabled'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'recovery_codes'  => ['type' => 'TEXT', 'null' => true],
            'failed_logins'   => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'locked_until'    => ['type' => 'DATETIME', 'null' => true],
            'last_login'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->addCols('tickets', [
            'ticket_type'        => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'incident'],
            'assigned_to'        => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'parent_id'          => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'response_due_at'    => ['type' => 'DATETIME', 'null' => true],
            'due_at'             => ['type' => 'DATETIME', 'null' => true],
            'first_response_at'  => ['type' => 'DATETIME', 'null' => true],
            'resolved_at'        => ['type' => 'DATETIME', 'null' => true],
            'rating'             => ['type' => 'TINYINT', 'constraint' => 1, 'null' => true],
            'rating_comment'     => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
        ]);

        $this->forge->addField([
            'role'   => ['type' => 'VARCHAR', 'constraint' => 30],
            'module' => ['type' => 'VARCHAR', 'constraint' => 30],
            'level'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
        ]);
        $this->forge->addPrimaryKey(['role', 'module']);
        $this->forge->createTable('role_permissions', true);

        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'ticket_id'  => ['type' => 'INT', 'unsigned' => true],
            'user_id'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'minutes'    => ['type' => 'INT', 'unsigned' => true],
            'billable'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'note'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('ticket_id');
        $this->forge->createTable('time_entries', true);

        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'title'      => ['type' => 'VARCHAR', 'constraint' => 190],
            'category'   => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'body'       => ['type' => 'MEDIUMTEXT'],
            'published'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('kb_articles', true);

        $this->forge->addField([
            'id'    => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'title' => ['type' => 'VARCHAR', 'constraint' => 120],
            'body'  => ['type' => 'TEXT'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('canned_replies', true);

        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 120],
            'key_prefix' => ['type' => 'VARCHAR', 'constraint' => 12],
            'key_hash'   => ['type' => 'CHAR', 'constraint' => 64],
            'active'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'last_used'  => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('key_hash');
        $this->forge->createTable('api_keys', true);

        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'url'         => ['type' => 'VARCHAR', 'constraint' => 500],
            'secret'      => ['type' => 'VARCHAR', 'constraint' => 64],
            'active'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'last_status' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'last_sent'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('webhooks', true);
    }

    public function down()
    {
        foreach (['webhooks', 'api_keys', 'canned_replies', 'kb_articles', 'time_entries', 'role_permissions'] as $t) {
            $this->forge->dropTable($t, true);
        }
        foreach (['totp_secret', 'totp_enabled', 'recovery_codes', 'failed_logins', 'locked_until', 'last_login'] as $c) {
            if ($this->db->fieldExists($c, 'users')) $this->forge->dropColumn('users', $c);
        }
        foreach (['ticket_type', 'assigned_to', 'parent_id', 'response_due_at', 'due_at', 'first_response_at', 'resolved_at', 'rating', 'rating_comment'] as $c) {
            if ($this->db->fieldExists($c, 'tickets')) $this->forge->dropColumn('tickets', $c);
        }
    }
}