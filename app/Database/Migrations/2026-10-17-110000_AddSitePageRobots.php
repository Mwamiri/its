<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class AddSitePageRobots extends Migration {
    public function up() {
        if ($this->db->tableExists('site_pages') && ! $this->db->fieldExists('robots', 'site_pages')) {
            $this->forge->addColumn('site_pages', [
                'robots' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => '', 'after' => 'meta_description'],
            ]);
        }
    }

    public function down() {
        if ($this->db->tableExists('site_pages') && $this->db->fieldExists('robots', 'site_pages')) {
            $this->forge->dropColumn('site_pages', 'robots');
        }
    }
}
