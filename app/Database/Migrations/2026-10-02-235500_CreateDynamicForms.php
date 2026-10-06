<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDynamicForms extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'title' => ['type' => 'VARCHAR', 'constraint' => 190],
            'description' => ['type' => 'TEXT', 'null' => true],
            'fields_json' => ['type' => 'TEXT'],
            'active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME'],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('active');
        $this->forge->createTable('custom_forms', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'form_id' => ['type' => 'INT', 'unsigned' => true],
            'submitted_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'answers_json' => ['type' => 'LONGTEXT'],
            'submitted_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('form_id');
        $this->forge->addKey('submitted_at');
        $this->forge->createTable('form_submissions', true);
    }

    public function down(): void
    {
        $this->forge->dropTable('form_submissions', true);
        $this->forge->dropTable('custom_forms', true);
    }
}
