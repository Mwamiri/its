<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * v3.5.1: Split "Web Development and Digital Services" into two focused service entries
 * and add "App Development" as a new standalone service.
 * Safe to re-run (checks by title before inserting / updating).
 */
class AddWebAppDevServices extends Migration
{
    public function up()
    {
        $now  = date('Y-m-d H:i:s');
        $t    = $this->db->table('site_items');

        // Rename and refocus the existing Web Development entry
        $existing = $t->where('kind', 'service')
                      ->like('title', 'Web Development', 'after')
                      ->get()->getRowArray();

        if ($existing) {
            $t->where('id', $existing['id'])->update([
                'title'      => 'Web Development',
                'subtitle'   => 'Professional websites that attract clients and grow your business online.',
                'body'       => 'Professional websites and web applications built to showcase your business, generate leads and serve your customers online.',
                'bullets'    => "Business and company profile websites\nE-commerce and online stores\nHosting and domain registration\nBusiness email setup\nLanding pages and campaigns\nBasic SEO and Google presence",
                'icon'       => 'globe',
                'sort_order' => 60,
                'updated_at' => $now,
            ]);
        } else {
            $t->insert([
                'kind'       => 'service',
                'title'      => 'Web Development',
                'subtitle'   => 'Professional websites that attract clients and grow your business online.',
                'body'       => 'Professional websites and web applications built to showcase your business, generate leads and serve your customers online.',
                'bullets'    => "Business and company profile websites\nE-commerce and online stores\nHosting and domain registration\nBusiness email setup\nLanding pages and campaigns\nBasic SEO and Google presence",
                'icon'       => 'globe',
                'featured'   => 1,
                'sort_order' => 60,
                'active'     => 1,
                'updated_at' => $now,
            ]);
        }

        // Add App Development only if it does not exist yet
        $appExists = $t->where('kind', 'service')->where('title', 'App Development')->countAllResults();
        if (! $appExists) {
            $t->insert([
                'kind'       => 'service',
                'title'      => 'App Development',
                'subtitle'   => 'Custom mobile and web apps built for your workflows and your customers.',
                'body'       => 'We design and build custom applications — Android and iOS mobile apps, progressive web apps and internal business tools — tailored to your processes.',
                'bullets'    => "Android and iOS mobile apps\nProgressive web apps (PWA)\nBusiness and workflow automation apps\nClient and staff portals\nAPI integration\nMaintenance and updates",
                'icon'       => 'smartphone',
                'featured'   => 1,
                'sort_order' => 65,
                'active'     => 1,
                'updated_at' => $now,
            ]);
        }

        // Bump IT Support sort_order to 80 so App Dev slots in at 65
        $its = $t->where('kind', 'service')->where('title', 'IT Support and Managed Services')->get()->getRowArray();
        if ($its && (int) $its['sort_order'] < 70) {
            $t->where('id', $its['id'])->update(['sort_order' => 80]);
        }
    }

    public function down()
    {
        $t = $this->db->table('site_items');
        $t->where('kind', 'service')->where('title', 'App Development')->delete();
        // Restore original title
        $t->where('kind', 'service')->where('title', 'Web Development')
          ->update(['title' => 'Web Development and Digital Services', 'sort_order' => 60]);
    }
}
