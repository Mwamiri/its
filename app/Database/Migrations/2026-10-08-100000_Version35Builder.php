<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * v3.5.0: email two-factor (codes and one-time sign-in links) and the drag-and-drop page builder.
 * Existing website pages are converted to blocks so they look exactly as before. Safe to re-run.
 */
class Version35Builder extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('email_2fa', 'users')) {
            $this->forge->addColumn('users', ['email_2fa' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0]]);
        }
        if (! $this->db->tableExists('login_tokens')) {
            $this->forge->addField([
                'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'user_id'    => ['type' => 'INT', 'unsigned' => true],
                'token_hash' => ['type' => 'CHAR', 'constraint' => 64],
                'purpose'    => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => '2fa_link'],
                'expires_at' => ['type' => 'DATETIME'],
                'used_at'    => ['type' => 'DATETIME', 'null' => true],
                'ip_address' => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addPrimaryKey('id');
            $this->forge->addUniqueKey('token_hash');
            $this->forge->addKey('user_id');
            $this->forge->createTable('login_tokens', true);
        }
        if ($this->db->tableExists('site_pages') && ! $this->db->fieldExists('blocks', 'site_pages')) {
            $this->forge->addColumn('site_pages', ['blocks' => ['type' => 'MEDIUMTEXT', 'null' => true]]);
        }
        if ($this->db->tableExists('site_pages')) {
            foreach ($this->db->table('site_pages')->where('blocks IS NULL', null, false)->get()->getResultArray() as $page) {
                $this->db->table('site_pages')->where('id', $page['id'])->update(['blocks' => json_encode(\App\Libraries\Site::legacyBlocks($page), JSON_UNESCAPED_UNICODE)]);
            }
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('blocks', 'site_pages')) $this->forge->dropColumn('site_pages', 'blocks');
        $this->forge->dropTable('login_tokens', true);
        if ($this->db->fieldExists('email_2fa', 'users')) $this->forge->dropColumn('users', 'email_2fa');
    }
}
