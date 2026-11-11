<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class AddPhase1Features extends Migration
{
    public function up()
    {
        // Ticket watchers (staff follow a ticket without being assigned)
        if (!$this->db->tableExists('ticket_watchers')) {
            $this->forge->addField([
                'ticket_id'  => ['type' => 'INT', 'unsigned' => true],
                'user_id'    => ['type' => 'INT', 'unsigned' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addPrimaryKey(['ticket_id', 'user_id']);
            $this->forge->addKey('user_id');
            $this->forge->createTable('ticket_watchers', true);
        }

        // CSAT: token for public rating link + flag that email was sent
        foreach (['rating_token' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true], 'csat_sent' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0]] as $col => $def) {
            if (!$this->db->fieldExists($col, 'tickets')) {
                $this->forge->addColumn('tickets', [$col => $def]);
            }
        }

        // Domains to watch for SSL / expiry
        if (!$this->db->tableExists('watched_domains')) {
            $this->forge->addField([
                'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'domain'     => ['type' => 'VARCHAR', 'constraint' => 253],
                'label'      => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
                'client_id'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addPrimaryKey('id');
            $this->forge->addUniqueKey('domain');
            $this->forge->createTable('watched_domains', true);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('csat_sent', 'tickets'))    $this->forge->dropColumn('tickets', ['rating_token', 'csat_sent']);
        $this->forge->dropTable('watched_domains', true);
        $this->forge->dropTable('ticket_watchers', true);
    }
}
