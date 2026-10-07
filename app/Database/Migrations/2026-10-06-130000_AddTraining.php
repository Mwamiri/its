<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTraining extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'client_id'    => ['type' => 'INT', 'unsigned' => true],
            'title'        => ['type' => 'VARCHAR', 'constraint' => 190],
            'session_date' => ['type' => 'DATE'],
            'start_time'   => ['type' => 'TIME', 'null' => true],
            'hours'        => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
            'location'     => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'trainer_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'counts_hours' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'notes'        => ['type' => 'TEXT', 'null' => true],
            'sign_token'   => ['type' => 'VARCHAR', 'constraint' => 32],
            'signin_open'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('client_id');
        $this->forge->createTable('training_sessions', true);

        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'session_id' => ['type' => 'INT', 'unsigned' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 150],
            'email'      => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'role_title' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'attended'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'signed_via' => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'manual'],
            'signed_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('session_id');
        $this->forge->createTable('training_attendees', true);
    }

    public function down()
    {
        $this->forge->dropTable('training_attendees', true);
        $this->forge->dropTable('training_sessions', true);
    }
}