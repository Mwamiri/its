<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class AddExtensionPlatforms extends Migration {
    public function up() {
        if ($this->db->tableExists('extensions')) {
            $this->db->query("ALTER TABLE extensions MODIFY COLUMN platform ENUM('churchz','umi','hr','hmisoz','jasemlz','kksports','other') NOT NULL DEFAULT 'other'");
        }
    }
    public function down() {
        if ($this->db->tableExists('extensions')) {
            $this->db->query("ALTER TABLE extensions MODIFY COLUMN platform ENUM('churchz','umi','other') NOT NULL DEFAULT 'other'");
        }
    }
}
