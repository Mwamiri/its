<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class WidenTotpSecret extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('users', ['totp_secret' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true]]);
    }

    public function down()
    {
        $this->forge->modifyColumn('users', ['totp_secret' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true]]);
    }
}