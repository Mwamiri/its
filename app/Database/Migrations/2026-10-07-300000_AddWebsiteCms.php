<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * v3.0.0: public company website with a built-in CMS (pages, content items, contact leads).
 * Seeds starter content for Mwamiri Computers Limited only when the tables are empty.
 */
class AddWebsiteCms extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('site_pages')) {
            $this->forge->addField([
                'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'slug'             => ['type' => 'VARCHAR', 'constraint' => 80],
                'title'            => ['type' => 'VARCHAR', 'constraint' => 190],
                'menu_label'       => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
                'show_in_menu'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'menu_order'       => ['type' => 'INT', 'default' => 0],
                'hero_title'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'hero_subtitle'    => ['type' => 'TEXT', 'null' => true],
                'hero_cta_label'   => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
                'hero_cta_url'     => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'hero_image'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'body'             => ['type' => 'MEDIUMTEXT', 'null' => true],
                'sections'         => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'meta_title'       => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
                'meta_description' => ['type' => 'VARCHAR', 'constraint' => 300, 'null' => true],
                'published'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'is_system'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'updated_at'       => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addPrimaryKey('id');
            $this->forge->addUniqueKey('slug');
            $this->forge->createTable('site_pages', true);
        }
        if (! $this->db->tableExists('site_items')) {
            $this->forge->addField([
                'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'kind'       => ['type' => 'VARCHAR', 'constraint' => 20],
                'title'      => ['type' => 'VARCHAR', 'constraint' => 190],
                'subtitle'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'body'       => ['type' => 'TEXT', 'null' => true],
                'bullets'    => ['type' => 'TEXT', 'null' => true],
                'icon'       => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
                'image_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'price'      => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
                'tags'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'link_url'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'featured'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'sort_order' => ['type' => 'INT', 'default' => 0],
                'active'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addPrimaryKey('id');
            $this->forge->addKey(['kind', 'active', 'sort_order']);
            $this->forge->createTable('site_items', true);
        }
        if (! $this->db->tableExists('site_leads')) {
            $this->forge->addField([
                'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'name'        => ['type' => 'VARCHAR', 'constraint' => 150],
                'phone'       => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
                'email'       => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
                'service'     => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
                'message'     => ['type' => 'TEXT', 'null' => true],
                'source_page' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
                'ip_address'  => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
                'status'      => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'new'],
                'client_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'created_at'  => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addPrimaryKey('id');
            $this->forge->addKey('status');
            $this->forge->createTable('site_leads', true);
        }
        $this->seed();
    }

    private function seed(): void
    {
        $now = date('Y-m-d H:i:s');
        if (! $this->db->table('site_pages')->countAllResults()) {
            $p = static fn(string $slug, string $title, string $menu, int $order, string $heroT, string $heroS, string $body, string $sections, string $metaT, string $metaD, string $cta = 'Request a quote', string $ctaUrl = 'contact') => compact('slug', 'title') + ['menu_label' => $menu, 'show_in_menu' => 1, 'menu_order' => $order, 'hero_title' => $heroT, 'hero_subtitle' => $heroS, 'hero_cta_label' => $cta, 'hero_cta_url' => $ctaUrl, 'body' => $body, 'sections' => $sections, 'meta_title' => $metaT, 'meta_description' => $metaD, 'published' => 1, 'is_system' => 1, 'updated_at' => $now];
            $this->db->table('site_pages')->insertBatch([
                $p('home', 'Home', 'Home', 0,
                    'Reliable Computer and ICT Solutions for Homes and Businesses',
                    'Mwamiri Computers Limited provides computer sales, repairs, networking, CCTV installation, software solutions and professional IT support.',
                    "## Welcome to Mwamiri Computers Limited\n\nWe are your trusted partner for computer products, technical support, networking and business technology solutions. We help individuals and organisations use technology safely, efficiently and affordably.\n\nWhether you need a laptop, an office network, security cameras, software installation or ongoing IT support, our team is ready to help.",
                    'service,stat,value,solution,project,testimonial,partner,cta',
                    'Mwamiri Computers Limited | Computer Sales, Repairs, Networking & IT Support',
                    'Trusted ICT solutions provider: computer sales and repairs, office networking, CCTV installation, software, web design and managed IT support for homes, businesses and schools.'),
                $p('about', 'About Us', 'About', 10,
                    'Your Trusted Technology Partner',
                    'We make technology simple, affordable and useful for homes, businesses, schools and institutions.',
                    "## About Mwamiri Computers Limited\n\nMwamiri Computers Limited is a technology solutions company dedicated to providing reliable, affordable and innovative ICT services to individuals, businesses, schools, churches and institutions.\n\nWe specialise in computer sales, computer repairs, networking, software installation, cybersecurity, CCTV installation, web development and ongoing IT support. Our goal is to help our customers operate efficiently using modern technology that is secure, dependable and tailored to their needs.\n\nWe believe technology should not be complicated. We take the time to understand each client's challenges and recommend practical solutions that deliver real value.\n\n## Our Mission\n\nTo provide reliable and affordable technology solutions that empower individuals and organisations to work smarter, communicate better and grow securely.\n\n## Our Vision\n\nTo become a trusted ICT solutions partner recognised for quality service, innovation and customer satisfaction.\n\n## Why work with us?\n\nWe do not just sell products. We provide practical technology solutions backed by support and advice. Our customers trust us because we solve their problems honestly and efficiently.",
                    'value,stat,testimonial,cta',
                    'About Mwamiri Computers Limited | ICT Solutions Company',
                    'Learn about Mwamiri Computers Limited: our mission, vision and values as a reliable ICT solutions partner for homes, businesses, schools and institutions.'),
                $p('services', 'Our Services', 'Services', 20,
                    'ICT Products and Services',
                    'A complete range of computer, network, security, software and support services for homes, businesses, schools and institutions.',
                    "Mwamiri Computers Limited offers a wide range of ICT products and services. Choose a service below or talk to us for a free assessment.",
                    'service,cta',
                    'IT Services | Computer Repair, Networking, CCTV, Software & IT Support',
                    'Computer sales and supply, laptop and desktop repair, office networking and Wi-Fi, CCTV installation, software solutions, web development and managed IT support.'),
                $p('solutions', 'Solutions', 'Solutions', 30,
                    'Complete ICT Solutions for Every Sector',
                    'Hardware, networking, security systems and ongoing support, designed around the way your organisation works.',
                    "We provide complete ICT solutions for businesses, schools and institutions, including hardware supply, networking, security systems and ongoing technical support.",
                    'solution,project,cta',
                    'ICT Solutions for Businesses, Schools & Institutions',
                    'Business IT, school ICT labs, office networking, CCTV security and data backup solutions from Mwamiri Computers Limited.'),
                $p('products', 'Products', 'Products', 40,
                    'Quality Computers, Accessories and Equipment',
                    'Genuine products from trusted brands, matched to your budget and needs. Ask us for the latest prices.',
                    "We supply quality computers, laptops, printers, accessories, networking equipment, CCTV cameras and genuine software.",
                    'product,partner,cta',
                    'Computers, Laptops, Printers, Networking & CCTV Equipment',
                    'Buy laptops, desktops, printers, accessories, networking equipment, CCTV cameras, storage devices and genuine software from Mwamiri Computers Limited.',
                    'Ask for a price', 'contact'),
                $p('projects', 'Projects', 'Projects', 50,
                    'Projects and Case Studies',
                    'A selection of the work we have delivered for our clients.',
                    "Every project starts with understanding the problem. Here is how we have helped organisations like yours.",
                    'project,testimonial,cta',
                    'Projects & Case Studies | Mwamiri Computers Limited',
                    'Office networks, school computer labs, CCTV systems, laptop supply and websites delivered by Mwamiri Computers Limited.'),
                $p('support', 'Support', 'Support', 60,
                    'Dependable Technical Support',
                    'Remote and on-site support, maintenance plans and emergency help to keep your systems running smoothly.',
                    "Existing customers can log in to the **client portal** to report an issue, follow progress and see their equipment.\n\n- Remote support\n- On-site support\n- Maintenance plans\n- Emergency support\n- Backup and recovery\n- Software troubleshooting",
                    'package,faq,cta',
                    'IT Support & Maintenance Plans | Mwamiri Computers Limited',
                    'Remote and on-site IT support, maintenance packages for small offices, businesses and institutions, emergency support and backup services.',
                    'Open the client portal', 'its-login'),
                $p('contact', 'Contact Us', 'Contact', 70,
                    'Talk to Our Team',
                    'Have a computer issue, need IT support or want a quote? Send us a message and we will respond as soon as possible.',
                    '',
                    'faq',
                    'Contact Mwamiri Computers Limited | Request a Quote',
                    'Contact Mwamiri Computers Limited for computer sales, repairs, networking, CCTV and IT support. Email support@mwamiri.co.ke or send us a message.',
                    'Email us', 'mailto:support@mwamiri.co.ke'),
            ]);
            $srBlocks = json_encode([
                ['type' => 'hero', 'id' => bin2hex(random_bytes(5)), 'title' => 'Submit a Support Request', 'subtitle' => 'Tell us about your IT issue and our team will contact you promptly to help resolve it.', 'button_label' => '', 'button_url' => '', 'button2_label' => '', 'button2_url' => '', 'image' => '', 'video' => '', 'style' => 'center', 'show_trust' => false],
                ['type' => 'support', 'id' => bin2hex(random_bytes(5)), 'title' => 'Describe your issue', 'text' => 'Fill in the form below. We will reply by phone or email as soon as possible.', 'bg' => 'auto'],
            ]);
            $this->db->table('site_pages')->insert(['slug' => 'support-request', 'title' => 'IT Support Request', 'menu_label' => 'Support Request', 'show_in_menu' => 0, 'menu_order' => 90, 'hero_title' => 'Submit a Support Request', 'hero_subtitle' => 'Tell us about your IT issue and our team will contact you promptly.', 'hero_cta_label' => '', 'hero_cta_url' => '', 'body' => '', 'sections' => '', 'blocks' => $srBlocks, 'meta_title' => 'Submit a Support Request', 'meta_description' => 'Report a computer, network or equipment issue. Our IT support team will contact you promptly.', 'published' => 1, 'is_system' => 0, 'updated_at' => $now]);
        }
        if (! $this->db->table('site_items')->countAllResults()) {
            $rows = [];
            $add = static function (string $kind, string $title, string $body = '', string $bullets = '', string $icon = '', array $extra = []) use (&$rows, $now) {
                static $order = [];
                $order[$kind] = ($order[$kind] ?? 0) + 10;
                $rows[] = array_merge(['kind' => $kind, 'title' => $title, 'subtitle' => null, 'body' => $body, 'bullets' => $bullets, 'icon' => $icon, 'image_path' => null, 'price' => null, 'tags' => null, 'link_url' => null, 'featured' => 1, 'sort_order' => $order[$kind], 'active' => 1, 'updated_at' => $now], $extra);
            };
            $add('service', 'Computer Sales and Supply', 'Quality laptops, desktops, printers, accessories and genuine software from trusted brands, matched to your budget and business needs.', "Laptops and desktops\nComputer accessories\nPrinters and scanners\nNetworking equipment\nUPS and power backup\nStorage devices\nGenuine software licences", 'laptop');
            $add('service', 'Computer Repair and Maintenance', 'Fast, reliable repairs including hardware replacement, software troubleshooting, virus removal and system maintenance.', "Hardware diagnostics\nScreen, keyboard and battery replacement\nVirus and malware removal\nSystem optimisation\nData recovery", 'wrench');
            $add('service', 'Networking Solutions', 'Secure office networks, Wi-Fi and internet infrastructure for businesses, schools and organisations.', "Office LAN and Wi-Fi installation\nStructured cabling\nRouter and switch configuration\nServer setup and management\nNetwork security", 'network');
            $add('service', 'CCTV and Security Systems', 'Surveillance systems that let you monitor your home or business locally and remotely.', "CCTV camera installation\nDVR/NVR configuration\nRemote viewing on your phone\nAccess control systems", 'camera');
            $add('service', 'Software Solutions', 'Installation, licensing, configuration and support for homes, offices, schools and institutions.', "Windows and Microsoft Office\nAntivirus solutions\nBusiness and accounting software\nEmail configuration\nSchool management software support", 'code');
            $add('service', 'Web Development', 'Professional websites and web applications built to showcase your business, generate leads and serve your customers online.', "Business and company profile websites\nE-commerce and online stores\nHosting and domain registration\nBusiness email setup\nLanding pages and campaigns\nBasic SEO and Google presence", 'globe');
            $add('service', 'App Development', 'We design and build custom applications — Android and iOS mobile apps, progressive web apps and internal business tools — tailored to your processes.', "Android and iOS mobile apps\nProgressive web apps (PWA)\nBusiness and workflow automation apps\nClient and staff portals\nAPI integration\nMaintenance and updates", 'smartphone');
            $add('service', 'IT Support and Managed Services', 'Ongoing support and maintenance so your systems stay secure, updated and available.', "Monthly IT support contracts\nHelpdesk support\nBackup and recovery\nIT consultation\nSecurity updates", 'headset');

            foreach ([['Business IT Solutions', 'Reliable computers, networking, software and technical support for offices, retail shops, clinics, hotels and SMEs.', "Offices\nRetail shops\nClinics and hospitals\nHotels\nSmall and medium enterprises", 'building'],
                      ['School ICT Solutions', 'Computer labs, networking, software and technical maintenance for learning institutions.', "Primary and secondary schools\nColleges\nTraining institutions", 'school'],
                      ['Office Networking', 'Secure networks that let staff share internet, files and printers efficiently.', "Structured cabling\nWi-Fi setup\nRouter and switch configuration\nUser access control", 'network'],
                      ['CCTV Security', 'Reliable surveillance for homes and businesses, with recording and remote viewing.', "Camera installation\nRemote viewing\nRecording setup\nSystem maintenance", 'camera'],
                      ['Data Protection and Backup', 'Protect important data with backups, antivirus and security best practice.', "Backup setup\nRecovery support\nAntivirus protection\nSecurity recommendations", 'shield']] as [$t, $b, $l, $i]) $add('solution', $t, $b, $l, $i);

            foreach ([['Laptops', 'Business, student and performance laptops from leading brands.', 'laptop'], ['Desktops', 'Office desktops and all-in-one computers, configured and ready to use.', 'desktop'], ['Printers and Scanners', 'Inkjet, laser and multifunction printers and scanners.', 'printer'], ['Accessories', 'Keyboards, mice, headsets, bags, cables and more.', 'mouse'], ['Networking Equipment', 'Routers, switches, access points and cabling.', 'network'], ['CCTV Cameras', 'IP and analogue cameras, DVRs, NVRs and storage.', 'camera'], ['Storage Devices', 'External drives, flash drives, SSDs and NAS.', 'database'], ['Software', 'Genuine Windows, Office, antivirus and business software.', 'code']] as [$t, $b, $i]) $add('product', $t, $b, '', $i, ['price' => 'Request price']);

            foreach ([['School Computer Lab Setup', 'Education', 'A school needed a modern computer lab for ICT lessons.', 'We supplied and installed 20 computers, configured the network and provided basic staff training.', 'Lab ready for the new term, with one network shared by all classrooms.', 'school'],
                      ['Office Network Installation', 'Business', 'A growing office had unreliable Wi-Fi and no shared printing.', 'We designed and installed structured cabling, business Wi-Fi and secure shared printing.', 'Stable connectivity for every desk and fewer support calls.', 'network'],
                      ['CCTV for a Retail Shop', 'Retail', 'A shop owner wanted to monitor the premises after hours.', 'We installed HD cameras with an NVR and set up remote viewing on the owner\'s phone.', '24/7 visibility of the shop from anywhere.', 'camera']] as [$t, $tag, $prob, $sol, $res, $i]) $add('project', $t, "**Challenge:** $prob\n\n**Solution:** $sol\n\n**Result:** $res", '', $i, ['subtitle' => $tag, 'tags' => $tag]);
            foreach ([
                ['CDE Kenya — Company Website', 'Web Development', "**Client:** CDE Kenya\n\n**Project:** Professional company profile website.\n\n**Result:** Clean, mobile-friendly website with a strong online presence.", 'https://cde.co.ke', 'globe2'],
                ['HHC JK Kenya Region — Organisation Site', 'Web Development', "**Client:** HHC JK Kenya Region\n\n**Project:** Organisation website for announcements, events and contacts.\n\n**Result:** Informative site accessible to members and partners across Kenya.", 'https://www.hhcjkenyaregion.or.ke/', 'globe2'],
                ['KAEAM — Association Website', 'Web Development', "**Client:** KAEAM\n\n**Project:** Association website with membership info, news and contacts.\n\n**Result:** Polished online presence supporting the association's outreach.", 'https://kaeam.or.ke', 'globe2'],
                ['St Brigitta\'s Hospital — Hospital Website', 'Web Development', "**Client:** St Brigitta's Hospital\n\n**Project:** Hospital website with departments, services and patient information.\n\n**Result:** Accessible website that helps patients find information and contacts.", 'https://www.stbrigittashospital.or.ke/', 'globe2'],
            ] as [$t, $tag, $b, $url, $i]) $add('project', $t, $b, '', $i, ['subtitle' => $tag, 'tags' => $tag, 'link_url' => $url]);

            foreach ([['Integrity', 'We conduct business honestly and transparently.', 'shield'], ['Reliability', 'We deliver dependable products and services.', 'check'], ['Customer Focus', 'We listen to clients and solve their real technology needs.', 'users'], ['Innovation', 'We keep up with modern technology to offer better solutions.', 'spark'], ['Professionalism', 'We serve every client with courtesy, respect and expertise.', 'star']] as [$t, $b, $i]) $add('value', $t, $b, '', $i);

            foreach ([['Experienced technicians', 'tool'], ['Affordable pricing', 'tag'], ['Genuine products', 'check'], ['Fast response', 'clock'], ['Reliable support', 'headset'], ['Practical solutions', 'spark']] as [$t, $i]) $add('stat', $t, '', '', $i);

            foreach ([['Basic Support', 'For individuals and small offices.', "Computer check-up\nSoftware installation\nVirus removal\nBasic optimisation", 'Contact us'], ['Business Support', 'For companies that need ongoing IT help.', "Monthly maintenance\nNetwork monitoring\nPriority support\nSoftware updates\nBackup management", 'Custom quote'], ['Enterprise / Institution', 'For schools and larger organisations.', "Network management\nServer support\nCCTV support\nUser support\nSystem monitoring", 'Custom quote']] as [$t, $b, $l, $pr]) $add('package', $t, $b, $l, 'headset', ['price' => $pr]);

            foreach ([['Do you offer on-site support?', 'Yes. We support clients on-site and remotely, depending on the issue and your location.'], ['Can I get a quote before work starts?', 'Yes. Tell us what you need through the contact form or by email and we will send a quote.'], ['Do you supply genuine software?', 'Yes. We supply and install genuine, licensed software only.'], ['How do existing clients report a problem?', 'Log in to the client portal to report an issue, follow progress and approve quotes, or email support@mwamiri.co.ke.']] as [$q, $a]) $add('faq', $q, $a);
            $this->db->table('site_items')->insertBatch($rows);
        }
        $settings = $this->db->table('settings');
        foreach (['site_enabled' => '1', 'site_email' => 'support@mwamiri.co.ke', 'site_tagline' => 'Your Trusted Technology Partner', 'site_primary_color' => '#0b4ea2', 'site_accent_color' => '#f97316', 'site_hours' => "Monday – Friday: 8:00 – 17:00\nSaturday: 9:00 – 13:00\nSunday: Closed (emergency support only)", 'site_keywords' => 'computer shop, computer repair, laptop repair, CCTV installation, networking services, IT support company, ICT solutions provider, web design, Mwamiri Computers Limited'] as $k => $v) {
            if (! $settings->where('setting_key', $k)->countAllResults()) $this->db->table('settings')->insert(['setting_key' => $k, 'setting_value' => $v]);
        }
        if (! $this->db->table('settings')->where('setting_key', 'site_name')->countAllResults()) $this->db->table('settings')->insert(['setting_key' => 'site_name', 'setting_value' => 'Mwamiri Computers Limited']);
    }

    public function down()
    {
        foreach (['site_leads', 'site_items', 'site_pages'] as $t) $this->forge->dropTable($t, true);
    }
}
