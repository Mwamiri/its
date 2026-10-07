<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** v2.1.1: remembers the last accepted TOTP time step so a code cannot be used twice. */
class AddTotpReplayGuard extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('totp_last_step', 'users')) {
            $this->forge->addColumn('users', [
                'totp_last_step' => ['type' => 'BIGINT', 'null' => true],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('totp_last_step', 'users')) {
            $this->forge->dropColumn('users', 'totp_last_step');
        }
    }
}
