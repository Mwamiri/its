<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEncryptedRtspUrls extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('cameras') && !$this->db->fieldExists('rtsp_url_ciphertext', 'cameras')) {
            $this->forge->addColumn('cameras', [
                'rtsp_url_ciphertext' => ['type' => 'MEDIUMTEXT', 'null' => true],
            ]);
        }
    }

    public function down(): void
    {
        if ($this->db->tableExists('cameras') && $this->db->fieldExists('rtsp_url_ciphertext', 'cameras')) {
            $this->forge->dropColumn('cameras', 'rtsp_url_ciphertext');
        }
    }
}
