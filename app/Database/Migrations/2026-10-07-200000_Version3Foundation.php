<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * v3.0.0: custom fields, ticket-to-asset links, client-facing forms.
 * Every step checks first, so the migration is safe to re-run after a failed or partial update.
 */
class Version3Foundation extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('asset_id', 'tickets')) {
            $this->forge->addColumn('tickets', ['asset_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true]]);
            $this->db->query('CREATE INDEX tickets_asset_id ON ' . $this->db->prefixTable('tickets') . ' (asset_id)');
        }

        if (! $this->db->tableExists('custom_fields')) {
            $this->forge->addField([
                'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'entity'         => ['type' => 'VARCHAR', 'constraint' => 20],
                'field_key'      => ['type' => 'VARCHAR', 'constraint' => 40],
                'label'          => ['type' => 'VARCHAR', 'constraint' => 120],
                'type'           => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'text'],
                'options_json'   => ['type' => 'TEXT', 'null' => true],
                'help'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'required'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'show_in_list'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'show_in_report' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'show_in_portal' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'sort_order'     => ['type' => 'INT', 'default' => 0],
                'active'         => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'created_at'     => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addPrimaryKey('id');
            $this->forge->addUniqueKey(['entity', 'field_key']);
            $this->forge->createTable('custom_fields', true);
        }

        if (! $this->db->tableExists('custom_field_values')) {
            $this->forge->addField([
                'id'        => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
                'field_id'  => ['type' => 'INT', 'unsigned' => true],
                'entity_id' => ['type' => 'INT', 'unsigned' => true],
                'value'     => ['type' => 'TEXT', 'null' => true],
            ]);
            $this->forge->addPrimaryKey('id');
            $this->forge->addUniqueKey(['field_id', 'entity_id']);
            $this->forge->addKey('entity_id');
            $this->forge->createTable('custom_field_values', true);
        }

        if (! $this->db->fieldExists('audience', 'custom_forms')) {
            $this->forge->addColumn('custom_forms', [
                'audience'      => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'staff'],
                'settings_json' => ['type' => 'TEXT', 'null' => true],
            ]);
        }
        if (! $this->db->fieldExists('client_id', 'form_submissions')) {
            $this->forge->addColumn('form_submissions', ['client_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true]]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('client_id', 'form_submissions')) $this->forge->dropColumn('form_submissions', 'client_id');
        if ($this->db->fieldExists('audience', 'custom_forms')) $this->forge->dropColumn('custom_forms', ['audience', 'settings_json']);
        $this->forge->dropTable('custom_field_values', true);
        $this->forge->dropTable('custom_fields', true);
        if ($this->db->fieldExists('asset_id', 'tickets')) $this->forge->dropColumn('tickets', 'asset_id');
    }
}
