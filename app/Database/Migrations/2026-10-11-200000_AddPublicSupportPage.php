<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPublicSupportPage extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('site_pages')) return;
        $exists = $this->db->table('site_pages')->where('slug', 'support-request')->countAllResults();
        if ($exists) return;
        $blocks = json_encode([
            ['type' => 'hero', 'id' => bin2hex(random_bytes(5)), 'title' => 'Submit a Support Request', 'subtitle' => 'Tell us about your IT issue and our team will contact you promptly to help resolve it.', 'button_label' => '', 'button_url' => '', 'button2_label' => '', 'button2_url' => '', 'image' => '', 'video' => '', 'style' => 'center', 'show_trust' => false],
            ['type' => 'support', 'id' => bin2hex(random_bytes(5)), 'title' => 'Describe your issue', 'text' => 'Fill in the form below. We will reply by phone or email as soon as possible.', 'bg' => 'auto'],
        ]);
        $this->db->table('site_pages')->insert([
            'slug'           => 'support-request',
            'title'          => 'IT Support Request',
            'menu_label'     => 'Support Request',
            'menu_order'     => 90,
            'show_in_menu'   => 0,
            'hero_title'     => 'Submit a Support Request',
            'hero_subtitle'  => 'Tell us about your IT issue and our team will contact you promptly to help resolve it.',
            'hero_cta_label' => '',
            'hero_cta_url'   => '',
            'hero_image'     => null,
            'body'           => '',
            'sections'       => '',
            'blocks'         => $blocks,
            'meta_title'     => 'Submit a Support Request',
            'meta_description' => 'Report a computer, network or equipment issue. Our IT support team will contact you promptly.',
            'published'      => 1,
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);
    }

    public function down()
    {
        if ($this->db->tableExists('site_pages')) {
            $this->db->table('site_pages')->where('slug', 'support-request')->delete();
        }
    }
}
