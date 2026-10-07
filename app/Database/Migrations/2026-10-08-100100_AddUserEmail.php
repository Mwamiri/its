<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** v3.5.0: user email address (needed for email sign-in codes); installs from the original schema did not have it. */
class AddUserEmail extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('email', 'users')) {
            $this->forge->addColumn('users', ['email' => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true]]);
        }
    }

    public function down()
    {
        // The column may have existed before this release (older installers created it), so it is not dropped.
    }
}
