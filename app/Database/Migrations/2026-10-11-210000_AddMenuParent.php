<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMenuParent extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('menu_parent', 'site_pages')) {
            $this->forge->addColumn('site_pages', [
                'menu_parent' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true, 'default' => null, 'after' => 'menu_order'],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('menu_parent', 'site_pages')) {
            $this->forge->dropColumn('site_pages', 'menu_parent');
        }
    }
}
