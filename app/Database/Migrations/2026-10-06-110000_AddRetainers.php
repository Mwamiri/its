<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRetainers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('clients', [
            'retainer_hours' => ['type' => 'DECIMAL', 'constraint' => '6,2', 'default' => 0],
            'hourly_rate'    => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('clients', ['retainer_hours', 'hourly_rate']);
    }
}