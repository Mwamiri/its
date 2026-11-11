<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class AddExtPricingFields extends Migration {
    public function up() {
        // price_type on extensions
        if ($this->db->tableExists('extensions') && !in_array('price_type', $this->db->getFieldNames('extensions'))) {
            $this->forge->addColumn('extensions', [
                'price_type' => ['type' => 'ENUM', 'constraint' => ['free','paid'], 'default' => 'free', 'after' => 'type'],
            ]);
        }
        // pricing & display fields on ext_packages
        if ($this->db->tableExists('ext_packages')) {
            $cols = $this->db->getFieldNames('ext_packages');
            $add  = [];
            if (!in_array('install_fee', $cols))  $add['install_fee']  = ['type' => 'VARCHAR', 'constraint' => 40, 'default' => '', 'after' => 'price_label'];
            if (!in_array('annual_fee',  $cols))  $add['annual_fee']   = ['type' => 'VARCHAR', 'constraint' => 40, 'default' => '', 'after' => 'install_fee'];
            if (!in_array('is_quote',    $cols))  $add['is_quote']     = ['type' => 'TINYINT', 'constraint' => 1,  'default' => 0,  'after' => 'annual_fee'];
            if (!in_array('highlight',   $cols))  $add['highlight']    = ['type' => 'TINYINT', 'constraint' => 1,  'default' => 0,  'after' => 'is_quote'];
            if (!in_array('sort_order',  $cols))  $add['sort_order']   = ['type' => 'INT', 'unsigned' => true, 'default' => 0, 'after' => 'highlight'];
            if ($add) $this->forge->addColumn('ext_packages', $add);
            // Seed the 3 standard pricing plans
            $now = date('Y-m-d H:i:s');
            foreach ([
                [
                    'name' => 'Starter', 'description' => 'One app of your choice.',
                    'scope' => 'extensions', 'allowed_platforms' => '', 'price_label' => '$600 install + $150/yr',
                    'install_fee' => '$600', 'annual_fee' => '$150 /yr', 'is_quote' => 0, 'highlight' => 0, 'sort_order' => 10,
                ],
                [
                    'name' => 'Professional', 'description' => 'One APP plus two plugins for your platform.',
                    'scope' => 'platforms', 'allowed_platforms' => '', 'price_label' => '$1,000 install + $300/yr',
                    'install_fee' => '$1,000', 'annual_fee' => '$300 /yr', 'is_quote' => 0, 'highlight' => 1, 'sort_order' => 20,
                ],
                [
                    'name' => 'Enterprise', 'description' => 'Full APP with all free plugins — custom quote.',
                    'scope' => 'all', 'allowed_platforms' => '', 'price_label' => 'Get a Quote',
                    'install_fee' => 'Quote', 'annual_fee' => 'Quote', 'is_quote' => 1, 'highlight' => 0, 'sort_order' => 30,
                ],
            ] as $plan) {
                if (!$this->db->table('ext_packages')->where('name', $plan['name'])->countAllResults()) {
                    $this->db->table('ext_packages')->insert($plan + ['created_at' => $now]);
                }
            }
        }
    }
    public function down() {
        if ($this->db->tableExists('extensions') && in_array('price_type', $this->db->getFieldNames('extensions'))) {
            $this->forge->dropColumn('extensions', 'price_type');
        }
        if ($this->db->tableExists('ext_packages')) {
            foreach (['install_fee','annual_fee','is_quote','highlight','sort_order'] as $col) {
                if (in_array($col, $this->db->getFieldNames('ext_packages'))) {
                    $this->forge->dropColumn('ext_packages', $col);
                }
            }
            $this->db->table('ext_packages')->whereIn('name', ['Starter','Professional','Enterprise'])->delete();
        }
    }
}
