<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class AddExtensions extends Migration {
    public function up() {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'platform'       => ['type' => 'ENUM', 'constraint' => ['churchz','umi','other'], 'default' => 'other'],
            'type'           => ['type' => 'ENUM', 'constraint' => ['plugin','theme','addon','integration'], 'default' => 'plugin'],
            'name'           => ['type' => 'VARCHAR', 'constraint' => 120],
            'slug'           => ['type' => 'VARCHAR', 'constraint' => 160, 'default' => ''],
            'version'        => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => ''],
            'description'    => ['type' => 'TEXT', 'null' => true, 'default' => null],
            'author'         => ['type' => 'VARCHAR', 'constraint' => 120, 'default' => ''],
            'author_email'   => ['type' => 'VARCHAR', 'constraint' => 120, 'default' => ''],
            'homepage_url'   => ['type' => 'VARCHAR', 'constraint' => 255, 'default' => ''],
            'file_path'      => ['type' => 'VARCHAR', 'constraint' => 255, 'default' => ''],
            'file_size'      => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'download_count' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'status'         => ['type' => 'ENUM', 'constraint' => ['pending','approved','rejected'], 'default' => 'pending'],
            'uploaded_by'    => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'default' => null],
            'notes'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'default' => null],
            'created_at'     => ['type' => 'DATETIME'],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true, 'default' => null],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['platform', 'status']);
        $this->forge->addKey(['status']);
        $this->forge->createTable('extensions');
    }

    public function down() {
        $this->forge->dropTable('extensions', true);
    }
}
