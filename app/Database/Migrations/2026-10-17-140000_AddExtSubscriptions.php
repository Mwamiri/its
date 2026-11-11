<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class AddExtSubscriptions extends Migration {
    public function up() {
        // Packages: define what a subscription allows
        if (!$this->db->tableExists('ext_packages')) {
            $this->forge->addField([
                'id'                   => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'name'                 => ['type' => 'VARCHAR', 'constraint' => 100],
                'description'          => ['type' => 'VARCHAR', 'constraint' => 500, 'default' => ''],
                'scope'                => ['type' => 'ENUM', 'constraint' => ['all', 'platforms', 'extensions'], 'default' => 'all'],
                'allowed_platforms'    => ['type' => 'VARCHAR', 'constraint' => 255, 'default' => ''],
                'allowed_extension_ids'=> ['type' => 'TEXT', 'null' => true],
                'price_label'          => ['type' => 'VARCHAR', 'constraint' => 60, 'default' => ''],
                'created_at'           => ['type' => 'DATETIME'],
                'updated_at'           => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addPrimaryKey('id');
            $this->forge->createTable('ext_packages');
            // Seed default packages
            $now = date('Y-m-d H:i:s');
            $this->db->table('ext_packages')->insertBatch([
                ['name' => 'Full Access',    'description' => 'Download all extensions across all platforms.',             'scope' => 'all',       'allowed_platforms' => '',                         'price_label' => '',          'created_at' => $now],
                ['name' => 'Churchz Pack',   'description' => 'All Churchz extensions.',                                   'scope' => 'platforms', 'allowed_platforms' => 'churchz',                  'price_label' => '',          'created_at' => $now],
                ['name' => 'UMI Pack',       'description' => 'All UMI business extensions.',                              'scope' => 'platforms', 'allowed_platforms' => 'umi',                       'price_label' => '',          'created_at' => $now],
                ['name' => 'HR Pack',        'description' => 'All HR Suite extensions.',                                  'scope' => 'platforms', 'allowed_platforms' => 'hr',                        'price_label' => '',          'created_at' => $now],
                ['name' => 'HMISOZ Pack',    'description' => 'All HMISOZ extensions.',                                    'scope' => 'platforms', 'allowed_platforms' => 'hmisoz',                    'price_label' => '',          'created_at' => $now],
                ['name' => 'Multi-Platform', 'description' => 'Churchz + UMI extensions.',                                 'scope' => 'platforms', 'allowed_platforms' => 'churchz,umi',              'price_label' => '',          'created_at' => $now],
            ]);
        }
        // Subscriptions: who has access, keyed by download_key
        if (!$this->db->tableExists('ext_subscriptions')) {
            $this->forge->addField([
                'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'subscriber_name'  => ['type' => 'VARCHAR', 'constraint' => 150],
                'subscriber_email' => ['type' => 'VARCHAR', 'constraint' => 190],
                'subscriber_system'=> ['type' => 'VARCHAR', 'constraint' => 60, 'default' => ''],
                'package_id'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'download_key'     => ['type' => 'VARCHAR', 'constraint' => 64, 'default' => ''],
                'status'           => ['type' => 'ENUM', 'constraint' => ['pending', 'active', 'suspended', 'expired'], 'default' => 'pending'],
                'expires_at'       => ['type' => 'DATE', 'null' => true],
                'notes'            => ['type' => 'TEXT', 'null' => true],
                'approved_by'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'created_at'       => ['type' => 'DATETIME'],
                'updated_at'       => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addPrimaryKey('id');
            $this->forge->addUniqueKey('download_key');
            $this->forge->addKey('status');
            $this->forge->createTable('ext_subscriptions');
        }
    }
    public function down() {
        $this->forge->dropTable('ext_subscriptions', true);
        $this->forge->dropTable('ext_packages', true);
    }
}
