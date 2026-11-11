<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class AddExtensionMeta extends Migration {
    public function up() {
        if (!$this->db->tableExists('extensions')) return;
        $add = [];
        if (!$this->db->fieldExists('screenshot', 'extensions'))
            $add['screenshot'] = ['type' => 'VARCHAR', 'constraint' => 255, 'default' => '', 'after' => 'homepage_url'];
        if (!$this->db->fieldExists('changelog', 'extensions'))
            $add['changelog'] = ['type' => 'TEXT', 'null' => true, 'after' => 'screenshot'];
        if (!$this->db->fieldExists('tags', 'extensions'))
            $add['tags'] = ['type' => 'VARCHAR', 'constraint' => 255, 'default' => '', 'after' => 'changelog'];
        if (!$this->db->fieldExists('min_version', 'extensions'))
            $add['min_version'] = ['type' => 'VARCHAR', 'constraint' => 60, 'default' => '', 'after' => 'tags'];
        if (!$this->db->fieldExists('license', 'extensions'))
            $add['license'] = ['type' => 'VARCHAR', 'constraint' => 60, 'default' => '', 'after' => 'min_version'];
        if ($add) $this->forge->addColumn('extensions', $add);
    }

    public function down() {
        if (!$this->db->tableExists('extensions')) return;
        foreach (['screenshot', 'changelog', 'tags', 'min_version', 'license'] as $col) {
            if ($this->db->fieldExists($col, 'extensions'))
                $this->forge->dropColumn('extensions', $col);
        }
    }
}
