<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class AddLoginHistory extends Migration {
    public function up() {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'    => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'default' => null],
            'username'   => ['type' => 'VARCHAR', 'constraint' => 100, 'default' => ''],
            'success'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'reason'     => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'default' => null],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45, 'default' => ''],
            'created_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['success']);
        $this->forge->addKey(['created_at']);
        $this->forge->createTable('login_history');
    }
    public function down() {
        $this->forge->dropTable('login_history', true);
    }
}
