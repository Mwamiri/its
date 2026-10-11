<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWebDevPortfolio extends Migration
{
    private array $sites = [
        [
            'title'    => 'CDE Kenya — Company Website',
            'subtitle' => 'Web Development',
            'body'     => "**Client:** CDE Kenya\n\n**Project:** Professional company profile website showcasing services, team and contact information.\n\n**Result:** Clean, mobile-friendly website that establishes a credible online presence.",
            'link_url' => 'https://cde.co.ke',
            'icon'     => 'globe2',
        ],
        [
            'title'    => 'HHC JK Kenya Region — Organisation Site',
            'subtitle' => 'Web Development',
            'body'     => "**Client:** HHC JK Kenya Region\n\n**Project:** Organisation website for announcements, events and contact information for the regional body.\n\n**Result:** Informative site accessible to members and partners across Kenya.",
            'link_url' => 'https://www.hhcjkenyaregion.or.ke/',
            'icon'     => 'globe2',
        ],
        [
            'title'    => 'KAEAM — Association Website',
            'subtitle' => 'Web Development',
            'body'     => "**Client:** KAEAM\n\n**Project:** Professional website for the association with membership information, news and contact details.\n\n**Result:** A polished online presence that supports the association's outreach.",
            'link_url' => 'https://kaeam.or.ke',
            'icon'     => 'globe2',
        ],
        [
            'title'    => 'St Brigitta\'s Hospital — Hospital Website',
            'subtitle' => 'Web Development',
            'body'     => "**Client:** St Brigitta's Hospital\n\n**Project:** Hospital website with department listings, services, contact details and patient information.\n\n**Result:** Clear, accessible website that helps patients find information and contact the hospital.",
            'link_url' => 'https://www.stbrigittashospital.or.ke/',
            'icon'     => 'globe2',
        ],
    ];

    public function up()
    {
        $now = date('Y-m-d H:i:s');
        $t   = $this->db->table('site_items');

        $maxOrder = (int) $this->db->table('site_items')
            ->where('kind', 'project')->selectMax('sort_order')->get()
            ->getRowArray()['sort_order'] ?? 0;

        foreach ($this->sites as $site) {
            $exists = $t->where('kind', 'project')->where('title', $site['title'])->countAllResults();
            if (! $exists) {
                $maxOrder += 10;
                $t->insert([
                    'kind'       => 'project',
                    'title'      => $site['title'],
                    'subtitle'   => $site['subtitle'],
                    'body'       => $site['body'],
                    'bullets'    => '',
                    'icon'       => $site['icon'],
                    'link_url'   => $site['link_url'],
                    'tags'       => 'Web Development',
                    'featured'   => 1,
                    'sort_order' => $maxOrder,
                    'active'     => 1,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down()
    {
        $t = $this->db->table('site_items');
        foreach ($this->sites as $site) {
            $t->where('kind', 'project')->where('title', $site['title'])->delete();
        }
    }
}
