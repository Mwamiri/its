<?php

namespace App\Libraries;

use App\Models\SettingModel;

/** Public website: settings, CMS content, safe Markdown, vector icons and SEO helpers. */
class Site
{
    public const KINDS = [
        'service' => ['Services', 'Services you offer (shown on Home and Services).'],
        'solution' => ['Solutions / sectors', 'Who you serve: business, schools, offices and so on.'],
        'product' => ['Products', 'Product categories or items with an optional price.'],
        'project' => ['Projects / case studies', 'Challenge, solution and result; tags filter the list.'],
        'testimonial' => ['Testimonials', 'Quote in the text; name in the title; company in the subtitle.'],
        'value' => ['Values', 'Core values (About page).'],
        'stat' => ['Why choose us / stats', 'Short trust points or numbers, e.g. "500+ clients served".'],
        'package' => ['Support packages', 'Plans on the Support page; price shows as a label.'],
        'faq' => ['FAQs', 'Question in the title, answer in the text.'],
        'partner' => ['Partners & clients', 'Logos of partners, brands and clients. Group (e.g. "Technology partner", "Client") in the subtitle; the link opens their website. Logos dropped in the partners folder also appear.'],
        'news' => ['News & insights', 'Articles, announcements and success stories. Each gets its own page at /news/…; the summary goes in the subtitle.'],
    ];
    public const SECTIONS = ['service' => 'Services', 'solution' => 'Solutions', 'product' => 'Products', 'project' => 'Projects', 'testimonial' => 'Testimonials', 'value' => 'Values', 'stat' => 'Why choose us', 'package' => 'Support packages', 'faq' => 'FAQs', 'partner' => 'Partners', 'cta' => 'Call to action banner', 'contact' => 'Contact form'];
    /** Logos placed here (by FTP or Website → Media) appear in partner blocks; a sub-folder name becomes the group. */
    public const PARTNER_DIR = 'uploads/site/partners';
    public const VIDEO_MAX_MB = 64;
    public const SETTINGS = [
        'site_enabled' => ['Show the website at the site address (off = go straight to sign-in)', 'bool'],
        'site_name' => ['Company name on the website', 'text'],
        'site_tagline' => ['Tagline', 'text'],
        'site_email' => ['Email', 'email'],
        'site_phone' => ['Phone', 'text'],
        'site_whatsapp' => ['WhatsApp number (international format, e.g. 2547XXXXXXXX)', 'text'],
        'site_address' => ['Physical address', 'textarea'],
        'site_hours' => ['Business hours', 'textarea'],
        'site_map_url' => ['Google Maps embed link (optional)', 'url'],
        'site_facebook' => ['Facebook URL', 'url'], 'site_linkedin' => ['LinkedIn URL', 'url'], 'site_x' => ['X / Twitter URL', 'url'], 'site_instagram' => ['Instagram URL', 'url'], 'site_youtube' => ['YouTube URL', 'url'],
        'site_primary_color' => ['Primary colour', 'color'], 'site_accent_color' => ['Accent colour', 'color'],
        'site_announcement' => ['Top announcement bar (optional)', 'text'],
        'site_meta_title' => ['Default SEO title', 'text'], 'site_meta_description' => ['Default SEO description (about 155 characters)', 'textarea'],
        'site_keywords' => ['SEO keywords (comma separated)', 'textarea'],
        'site_ga_id' => ['Google Analytics measurement ID (G-XXXXXXX, optional)', 'text'],
        'site_search_console' => ['Google Search Console verification code (optional)', 'text'],
        'site_footer_note' => ['Footer note', 'text'],
        'site_email_protect' => ['Hide the email address from spam robots (shown to visitors, scrambled in the page source)', 'bool'],
    ];

    /**
     * Page-builder blocks (v3.5). Each block type lists its editable fields: [field => [label, type, options/default]].
     * Every block also has "bg" (background) and "anchor" (in-page link id).
     */
    public const BLOCKS = [
        'hero' => ['Hero banner', ['title' => ['Headline', 'text'], 'subtitle' => ['Text', 'textarea'], 'button_label' => ['Button', 'text'], 'button_url' => ['Button link', 'text'], 'button2_label' => ['Second button', 'text'], 'button2_url' => ['Second button link', 'text'], 'image' => ['Image', 'image'], 'video' => ['Background video (MP4/WebM, for the full-width layout)', 'video'], 'style' => ['Layout', 'select', ['split' => 'Text + illustration', 'center' => 'Centred text', 'background' => 'Full-width photo or video background']], 'show_trust' => ['Show trust ticks', 'bool']]],
        'slider' => ['Slider (images & video)', ['slides' => ['Slides', 'slides'], 'height' => ['Height', 'select', ['m' => 'Medium', 'l' => 'Large', 's' => 'Small', 'full' => 'Full screen']], 'overlay' => ['Text shading', 'select', ['dark' => 'Dark', 'brand' => 'Brand colour', 'light' => 'Light', 'none' => 'None']], 'align' => ['Text position', 'select', ['left' => 'Left', 'center' => 'Centre']], 'interval' => ['Seconds per slide (0 = manual)', 'number']]],
        'video' => ['Video', ['title' => ['Heading', 'text'], 'text' => ['Text', 'markdown'], 'video' => ['Video file (MP4/WebM)', 'video'], 'embed_url' => ['…or a YouTube / Vimeo link', 'text'], 'image' => ['Cover image (optional)', 'image'], 'layout' => ['Layout', 'select', ['wide' => 'Wide video', 'split' => 'Video + text side by side']], 'autoplay' => ['Play silently on loop', 'bool']]],
        'gallery' => ['Photo gallery', ['title' => ['Heading', 'text'], 'subtitle' => ['Intro', 'text'], 'images' => ['Photos', 'gallery'], 'columns' => ['Columns', 'select', ['3' => '3', '4' => '4', '2' => '2']]]],
        'counters' => ['Animated counters', ['title' => ['Heading', 'text'], 'subtitle' => ['Intro', 'text'], 'lines' => ['One per line: number | label (e.g. 18+ | Years in business)', 'textarea']]],
        'news' => ['News & insights', ['title' => ['Heading', 'text'], 'subtitle' => ['Intro', 'text'], 'limit' => ['How many (0 = all)', 'number'], 'featured_only' => ['Only home-page items', 'bool']]],
        'text' => ['Text', ['title' => ['Heading', 'text'], 'text' => ['Content', 'markdown'], 'align' => ['Align', 'select', ['left' => 'Left', 'center' => 'Centre']]]],
        'image_text' => ['Image + text', ['title' => ['Heading', 'text'], 'text' => ['Content', 'markdown'], 'image' => ['Image', 'image'], 'image_side' => ['Image side', 'select', ['left' => 'Left', 'right' => 'Right']], 'button_label' => ['Button', 'text'], 'button_url' => ['Button link', 'text']]],
        'services' => ['Services', ['title' => ['Heading', 'text'], 'subtitle' => ['Intro', 'text'], 'limit' => ['How many (0 = all)', 'number'], 'featured_only' => ['Only "show on home page" items', 'bool'], 'show_bullets' => ['Show bullet points', 'bool']]],
        'solutions' => ['Solutions / sectors', ['title' => ['Heading', 'text'], 'subtitle' => ['Intro', 'text'], 'limit' => ['How many (0 = all)', 'number'], 'featured_only' => ['Only home-page items', 'bool']]],
        'products' => ['Products', ['title' => ['Heading', 'text'], 'subtitle' => ['Intro', 'text'], 'limit' => ['How many (0 = all)', 'number'], 'featured_only' => ['Only home-page items', 'bool']]],
        'projects' => ['Projects / case studies', ['title' => ['Heading', 'text'], 'subtitle' => ['Intro', 'text'], 'limit' => ['How many (0 = all)', 'number'], 'featured_only' => ['Only home-page items', 'bool'], 'filters' => ['Show tag filters', 'bool']]],
        'testimonials' => ['Testimonials', ['title' => ['Heading', 'text'], 'subtitle' => ['Intro', 'text'], 'limit' => ['How many (0 = all)', 'number']]],
        'values' => ['Values', ['title' => ['Heading', 'text'], 'subtitle' => ['Intro', 'text']]],
        'stats' => ['Why choose us / stats', ['title' => ['Heading', 'text'], 'subtitle' => ['Intro', 'text']]],
        'packages' => ['Support packages', ['title' => ['Heading', 'text'], 'subtitle' => ['Intro', 'text']]],
        'faq' => ['FAQs', ['title' => ['Heading', 'text'], 'subtitle' => ['Intro', 'text']]],
        'partners' => ['Partner & client logos', ['title' => ['Heading', 'text'], 'subtitle' => ['Intro', 'text'], 'style' => ['Display', 'select', ['slider' => 'Sliding strip', 'grid' => 'Grid']], 'group' => ['Only this group (blank = all)', 'text'], 'speed' => ['Slide speed', 'select', ['normal' => 'Normal', 'slow' => 'Slow', 'fast' => 'Fast']], 'show_names' => ['Show names under logos', 'bool'], 'colour' => ['Show logos in colour', 'bool']]],
        'cta' => ['Call-to-action banner', ['title' => ['Heading', 'text'], 'text' => ['Text', 'textarea'], 'button_label' => ['Button', 'text'], 'button_url' => ['Button link', 'text'], 'style' => ['Colour', 'select', ['brand' => 'Brand gradient', 'accent' => 'Accent', 'dark' => 'Dark']]]],
        'contact' => ['Contact form + details', ['title' => ['Heading', 'text'], 'text' => ['Intro', 'textarea']]],
        'support' => ['IT Support Request form', ['title' => ['Heading', 'text'], 'text' => ['Intro', 'textarea']]],
        'spacer' => ['Spacer', ['size' => ['Height', 'select', ['s' => 'Small', 'm' => 'Medium', 'l' => 'Large']]]],
    ];
    public const BLOCK_KIND = ['services' => 'service', 'solutions' => 'solution', 'products' => 'product', 'projects' => 'project', 'testimonials' => 'testimonial', 'values' => 'value', 'stats' => 'stat', 'packages' => 'package', 'faq' => 'faq', 'partners' => 'partner', 'news' => 'news'];
    /** Sub-fields of list fields: slides and gallery photos. */
    public const SLIDE_FIELDS = ['image' => 'image', 'video' => 'video', 'title' => 'text', 'text' => 'textarea', 'button_label' => 'text', 'button_url' => 'text'];
    public const PHOTO_FIELDS = ['image' => 'image', 'caption' => 'text'];
    public const BACKGROUNDS = ['auto' => 'Automatic (alternating)', 'white' => 'White', 'light' => 'Light grey', 'brand' => 'Brand colour', 'dark' => 'Dark'];

    /** Header, body and footer design options (v3.5). [label, type, options-or-default, group] */
    public const DESIGN = [
        'site_header_style' => ['Header style', 'select', ['light' => 'Light', 'dark' => 'Dark', 'brand' => 'Brand colour'], 'header'],
        'site_header_sticky' => ['Keep header visible when scrolling', 'bool', '1', 'header'],
        'site_topbar' => ['Show top contact bar', 'bool', '1', 'header'],
        'site_topbar_email' => ['Show the email address in the top bar', 'bool', '1', 'header'],
        'site_topbar_social' => ['Show social media icons in the top bar', 'bool', '1', 'header'],
        'site_menu_align' => ['Menu position', 'select', ['right' => 'Right', 'center' => 'Centre'], 'header'],
        'site_logo_height' => ['Logo height (px)', 'number', '46', 'header'],
        'site_cta_label' => ['Header button label', 'text', 'Get a quote', 'header'],
        'site_cta_url' => ['Header button link', 'text', 'contact#contact-form', 'header'],
        'site_font' => ['Font', 'select', ['system' => 'System (fastest)', 'inter' => 'Inter', 'poppins' => 'Poppins', 'montserrat' => 'Montserrat', 'roboto' => 'Roboto', 'open-sans' => 'Open Sans'], 'body'],
        'site_font_size' => ['Base text size (px)', 'number', '16', 'body'],
        'site_radius' => ['Corner roundness (px)', 'number', '18', 'body'],
        'site_button_style' => ['Button shape', 'select', ['rounded' => 'Rounded', 'pill' => 'Pill', 'square' => 'Square'], 'body'],
        'site_spacing' => ['Section spacing', 'select', ['normal' => 'Normal', 'compact' => 'Compact', 'airy' => 'Airy'], 'body'],
        'site_footer_style' => ['Footer style', 'select', ['dark' => 'Dark', 'brand' => 'Brand colour', 'light' => 'Light'], 'footer'],
        'site_footer_about' => ['Footer "about" text', 'textarea', '', 'footer'],
        'site_footer_menu' => ['Show page links in footer', 'bool', '1', 'footer'],
        'site_footer_services' => ['Show services in footer', 'bool', '1', 'footer'],
        'site_footer_cta' => ['Show "Get a quote" button in footer', 'bool', '1', 'footer'],
        'site_footer_whatsapp' => ['Show WhatsApp button in footer', 'bool', '1', 'footer'],
        'site_footer_contact' => ['Show contact info section in footer', 'bool', '1', 'footer'],
        'site_footer_links' => ['Custom footer links (one per line: Label|URL)', 'textarea', '', 'footer'],
        'site_custom_css' => ['Custom CSS (advanced)', 'css', '', 'footer'],
    ];
    public const FONTS = ['inter' => 'Inter', 'poppins' => 'Poppins', 'montserrat' => 'Montserrat', 'roboto' => 'Roboto', 'open-sans' => 'Open+Sans'];

    public static function design(): array
    {
        $d = [];
        foreach (self::DESIGN as $k => [$label, $type, $opt]) {
            $v = (string) SettingModel::get($k, '');
            $short = substr($k, 5);
            if ($type === 'select') $d[$short] = isset($opt[$v]) ? $v : array_key_first($opt);
            elseif ($type === 'bool') $d[$short] = $v === '' ? $opt === '1' : $v === '1';
            elseif ($type === 'number') $d[$short] = $v === '' ? (int) $opt : (int) $v;
            else $d[$short] = $v === '' ? (string) $opt : $v;
        }
        $d['logo_height'] = max(24, min(120, $d['logo_height']));
        $d['font_size'] = max(13, min(22, $d['font_size']));
        $d['radius'] = max(0, min(40, $d['radius']));
        return $d;
    }

    /** Converts a page saved before v3.5 (hero fields + body + section list) into builder blocks. */
    public static function legacyBlocks(array $page): array
    {
        $map = ['service' => 'services', 'solution' => 'solutions', 'product' => 'products', 'project' => 'projects', 'testimonial' => 'testimonials', 'value' => 'values', 'stat' => 'stats', 'package' => 'packages', 'faq' => 'faq', 'partner' => 'partners', 'cta' => 'cta', 'contact' => 'contact'];
        $home = ($page['slug'] ?? '') === 'home';
        $b = [['type' => 'hero', 'title' => (string) ($page['hero_title'] ?: $page['title']), 'subtitle' => (string) $page['hero_subtitle'], 'button_label' => (string) $page['hero_cta_label'], 'button_url' => (string) $page['hero_cta_url'], 'button2_label' => $home ? 'Our services' : '', 'button2_url' => $home ? 'services' : '', 'image' => (string) ($page['hero_image'] ?? ''), 'style' => $home ? 'split' : 'center', 'show_trust' => $home]];
        if (trim((string) $page['body']) !== '') $b[] = ['type' => 'text', 'title' => '', 'text' => (string) $page['body'], 'align' => 'left'];
        foreach (array_filter(array_map('trim', explode(',', (string) $page['sections']))) as $s) {
            if (!isset($map[$s])) continue;
            $blk = ['type' => $map[$s], 'featured_only' => $home];
            if ($map[$s] === 'services') $blk['show_bullets'] = true;
            if ($map[$s] === 'projects') $blk['filters'] = !$home;
            $b[] = $blk;
        }
        if (($page['slug'] ?? '') === 'contact' && !in_array('contact', array_column($b, 'type'), true)) $b[] = ['type' => 'contact'];
        return self::normalizeBlocks($b);
    }

    /** Validates builder input: known types and fields only, sane lengths, at most 40 blocks. */
    public static function normalizeBlocks($blocks): array
    {
        if (is_string($blocks)) $blocks = json_decode($blocks, true);
        if (!is_array($blocks)) return [];
        $out = [];
        foreach (array_slice(array_values($blocks), 0, 40) as $raw) {
            if (!is_array($raw) || !isset(self::BLOCKS[$raw['type'] ?? ''])) continue;
            $type = $raw['type'];
            $b = ['type' => $type, 'id' => preg_match('/^[a-z0-9]{6,16}$/', (string) ($raw['id'] ?? '')) ? $raw['id'] : bin2hex(random_bytes(5))];
            foreach (self::BLOCKS[$type][1] as $f => $def) {
                $v = $raw[$f] ?? null;
                $b[$f] = match ($def[1]) {
                    'bool' => !empty($v) && $v !== '0' && $v !== 'false',
                    'number' => max(0, min(60, (int) $v)),
                    'select' => isset($def[2][(string) $v]) ? (string) $v : array_key_first($def[2]),
                    'slides' => self::cleanList($v, self::SLIDE_FIELDS, 12),
                    'gallery' => self::cleanList($v, self::PHOTO_FIELDS, 60),
                    default => self::cleanValue($def[1], $v),
                };
            }
            if ($type === 'video') $b['embed_url'] = self::embedUrl($b['embed_url']) !== '' ? $b['embed_url'] : '';
            $b['bg'] = isset(self::BACKGROUNDS[(string) ($raw['bg'] ?? '')]) ? (string) $raw['bg'] : 'auto';
            $b['anchor'] = trim((string) preg_replace('/[^a-z0-9-]+/', '-', strtolower((string) ($raw['anchor'] ?? ''))), '-');
            $out[] = $b;
        }
        return $out;
    }

    /** Uploaded website files: images, or MP4/WebM videos for video fields. Sub-folders allowed, no "..". */
    public static function mediaPath($v, bool $video = false): string
    {
        $v = (string) $v;
        $ext = $video ? 'mp4|webm' : 'png|jpe?g|gif|webp|svg';
        return preg_match('#^uploads/site/(?:[A-Za-z0-9_-][A-Za-z0-9._ -]*/)*[A-Za-z0-9_-][A-Za-z0-9._ -]*\.(' . $ext . ')$#i', $v) && !str_contains($v, '..') ? $v : '';
    }

    private static function cleanValue(string $type, $v): string|bool
    {
        if (is_array($v)) $v = '';
        return match ($type) {
            'image' => self::mediaPath($v),
            'video' => self::mediaPath($v, true),
            'markdown' => mb_substr((string) $v, 0, 20000),
            'textarea' => mb_substr((string) $v, 0, 2000),
            default => mb_substr(trim((string) $v), 0, 255),
        };
    }

    /** List fields (slides, gallery photos) arrive as JSON from the builder; empty rows are dropped. */
    private static function cleanList($v, array $fields, int $max): array
    {
        if (is_string($v)) $v = json_decode($v, true);
        if (!is_array($v)) return [];
        $out = [];
        foreach (array_slice(array_values($v), 0, $max) as $row) {
            if (!is_array($row)) continue;
            $r = [];
            foreach ($fields as $f => $t) $r[$f] = self::cleanValue($t, $row[$f] ?? '');
            if (implode('', $r) !== '') $out[] = $r;
        }
        return $out;
    }

    /** YouTube / Vimeo page link → privacy-friendly embed URL; anything else → ''. */
    public static function embedUrl(?string $url): string
    {
        $url = trim((string) $url);
        if (preg_match('#^https?://(?:www\.|m\.)?(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{11})#', $url, $m)) return 'https://www.youtube-nocookie.com/embed/' . $m[1] . '?rel=0';
        if (preg_match('#^https?://(?:www\.|player\.)?vimeo\.com/(?:video/)?(\d{6,12})#', $url, $m)) return 'https://player.vimeo.com/video/' . $m[1] . '?dnt=1';
        return '';
    }

    /**
     * Partner logos: items from Content → Partners plus image files in the partners folder.
     * A sub-folder name becomes the group, e.g. uploads/site/partners/Clients/acme.png → group "Clients".
     */
    public static function partners(string $group = ''): array
    {
        $list = self::items('partner');
        $known = array_flip(array_filter(array_column($list, 'image_path')));
        $root = FCPATH . self::PARTNER_DIR;
        if (is_dir($root)) {
            $files = [];
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)) as $f) {
                $rel = self::PARTNER_DIR . '/' . str_replace('\\', '/', substr($f->getPathname(), strlen($root) + 1));
                if (!$f->isFile() || self::mediaPath($rel) === '' || isset($known[$rel])) continue;
                $sub = trim(str_replace('\\', '/', substr($f->getPath(), strlen($root))), '/');
                $files[$rel] = ['id' => 0, 'title' => ucwords(trim((string) preg_replace('/[-_]+/', ' ', preg_replace('/^\d+[-_ ]*/', '', pathinfo($f->getFilename(), PATHINFO_FILENAME))))) ?: 'Partner',
                    'subtitle' => $sub !== '' ? str_replace('/', ' / ', $sub) : null, 'image_path' => $rel, 'link_url' => null, 'body' => '', 'icon' => null, 'featured' => 1];
            }
            ksort($files, SORT_NATURAL | SORT_FLAG_CASE);
            $list = array_merge($list, array_values($files));
        }
        if ($group !== '') $list = array_values(array_filter($list, static fn($p) => strcasecmp(trim((string) $p['subtitle']), trim($group)) === 0));
        return $list;
    }

    /** Address for a news article: /news/12-title-words */
    public static function newsUrl(array $it): string
    {
        return base_url('news/' . (int) $it['id'] . '-' . trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower(mb_substr((string) $it['title'], 0, 70))), '-'));
    }

    /**
     * Email link that spam robots cannot read: the address is stored reversed and base64-encoded and
     * put back together by a tiny script; without JavaScript visitors see "name [at] domain".
     */
    public static function email(string $email, string $label = '', string $attrs = '', string $iconHtml = ''): string
    {
        if ($email === '') return '';
        if (self::setting('site_email_protect', '1') === '0') return '<a href="mailto:' . esc($email) . '"' . $attrs . '>' . $iconHtml . esc($label !== '' ? $label : $email) . '</a>';
        $shown = $label !== '' ? esc($label) : esc(str_replace(['@', '.'], [' [at] ', ' [dot] '], $email));
        return '<a href="#" data-em="' . esc(base64_encode(strrev($email))) . '"' . ($label !== '' ? ' data-em-label' : '') . $attrs . '>' . $iconHtml . '<span>' . $shown . '</span></a>';
    }

    public static function blocksFor(array $page): array
    {
        $b = isset($page['blocks']) ? self::normalizeBlocks((string) $page['blocks']) : [];
        return $b ?: self::legacyBlocks($page);
    }

    /** Renders a full page; used by the public site and by the builder's live preview so both always match. */
    public static function render(array $page, ?array $blocks = null, bool $preview = false): string
    {
        $blocks ??= self::blocksFor($page);
        $kinds = [];
        foreach ($blocks as $b) if (isset(self::BLOCK_KIND[$b['type']])) $kinds[self::BLOCK_KIND[$b['type']]] = true;
        $items = [];
        foreach (array_keys($kinds) as $k) $items[$k] = $k === 'partner' ? self::partners() : self::items($k);
        $hasContact = in_array('contact', array_column($blocks, 'type'), true);
        return view('site/page', ['page' => $page, 'blocks' => $blocks, 'items' => $items, 'site' => self::info(), 'design' => self::design(), 'menu' => self::menu(),
            'services' => $hasContact ? ($items['service'] ?? self::items('service')) : [], 'user' => session('its_user'), 'preview' => $preview]);
    }

    public static function setting(string $key, string $default = ''): string
    {
        $v = SettingModel::get($key);
        return $v === null || $v === '' ? $default : $v;
    }

    public static function info(): array
    {
        $out = [];
        foreach (array_keys(self::SETTINGS) as $k) $out[substr($k, 5)] = self::setting($k);
        $out['name'] = $out['name'] ?: (string) SettingModel::get('company_name', 'Our company');
        $out['email'] = $out['email'] ?: (string) SettingModel::get('company_email', '');
        $out['logo'] = (string) SettingModel::get('company_logo', '');
        $out['primary_color'] = preg_match('/^#[0-9a-f]{6}$/i', $out['primary_color']) ? $out['primary_color'] : '#0b4ea2';
        $out['accent_color'] = preg_match('/^#[0-9a-f]{6}$/i', $out['accent_color']) ? $out['accent_color'] : '#f97316';
        $out['enabled'] = self::setting('site_enabled', '1') === '1';
        return $out;
    }

    public static function ready(): bool
    {
        return \Config\Database::connect()->tableExists('site_pages');
    }

    private static ?array $published = null;

    public static function hasPage(string $slug): bool
    {
        self::$published ??= array_flip(array_column(\Config\Database::connect()->table('site_pages')->select('slug')->where('published', 1)->get()->getResultArray(), 'slug'));
        return isset(self::$published[$slug]);
    }

    public static function menu(): array
    {
        $pages = \Config\Database::connect()->table('site_pages')
            ->select('slug, title, menu_label, menu_order')->where('published', 1)->where('show_in_menu', 1)
            ->orderBy('menu_order')->orderBy('id')->get()->getResultArray();
        $custom = json_decode((string) SettingModel::get('site_menu_custom', ''), true);
        if (!is_array($custom) || empty($custom)) return $pages;
        foreach ($custom as $c) {
            if (!empty($c['label']) && !empty($c['url'])) {
                $pages[] = ['slug' => $c['url'], 'title' => $c['label'], 'menu_label' => $c['label'], 'menu_order' => (int) ($c['order'] ?? 80)];
            }
        }
        usort($pages, fn($a, $b) => ((int) $a['menu_order']) <=> ((int) $b['menu_order']));
        return $pages;
    }

    public static function items(string $kind, bool $featuredOnly = false): array
    {
        $q = \Config\Database::connect()->table('site_items')->where('kind', $kind)->where('active', 1);
        if ($kind === 'news') $q->orderBy('id', 'DESC'); else $q->orderBy('sort_order')->orderBy('id');
        if ($featuredOnly) $q->where('featured', 1);
        return $q->get()->getResultArray();
    }

    public static function url(string $slug): string
    {
        if (str_starts_with($slug, 'http://') || str_starts_with($slug, 'https://') || str_starts_with($slug, '/')) return $slug;
        return base_url($slug === 'home' ? '' : $slug);
    }

    /** Resolves CMS link values: page slugs, its-* app routes, absolute http(s), mailto: and tel: links. */
    public static function link(?string $v): string
    {
        $v = trim((string) $v);
        if ($v === '') return '#';
        if (preg_match('#^(https?://|mailto:|tel:)#i', $v)) return $v;
        if (str_starts_with($v, '#')) return $v;
        return base_url(ltrim($v, '/'));
    }

    /**
     * Small, safe Markdown: everything is escaped first, then ## / ### headings, - lists,
     * **bold**, *italic* and [links](url) (http/https/mailto/tel or relative) are allowed.
     */
    public static function md(?string $text): string
    {
        $text = str_replace("\r\n", "\n", trim((string) $text));
        if ($text === '') return '';
        $inline = static function (string $s): string {
            $s = esc($s);
            $s = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $s);
            $s = preg_replace('/(?<!\*)\*(?!\s)(.+?)(?<!\s)\*(?!\*)/s', '<em>$1</em>', $s);
            return preg_replace_callback('/\[([^\]]+)\]\(([^)\s]+)\)/', static function ($m) {
                $url = html_entity_decode($m[2], ENT_QUOTES);
                if (!preg_match('#^(https?://|mailto:|tel:|/|[a-z0-9-]+$|\#)#i', $url)) return $m[0];
                $ext = preg_match('#^https?://#i', $url) ? ' target="_blank" rel="noopener"' : '';
                return '<a href="' . esc(self::link($url)) . '"' . $ext . '>' . $m[1] . '</a>';
            }, $s);
        };
        $html = ''; $list = false; $para = [];
        $flush = static function () use (&$para, &$html, $inline) { if ($para) { $html .= '<p>' . $inline(implode(' ', $para)) . '</p>'; $para = []; } };
        foreach (explode("\n", $text) as $line) {
            $t = trim($line);
            if (preg_match('/^[-*]\s+(.+)$/', $t, $m)) { $flush(); if (!$list) { $html .= '<ul>'; $list = true; } $html .= '<li>' . $inline($m[1]) . '</li>'; continue; }
            if ($list) { $html .= '</ul>'; $list = false; }
            if ($t === '') { $flush(); continue; }
            if (preg_match('/^(#{2,3})\s+(.+)$/', $t, $m)) { $flush(); $tag = strlen($m[1]) === 2 ? 'h2' : 'h3'; $html .= "<$tag>" . $inline($m[2]) . "</$tag>"; continue; }
            $para[] = $t;
        }
        $flush();
        if ($list) $html .= '</ul>';
        return $html;
    }

    public static function lines(?string $text): array
    {
        return array_values(array_filter(array_map('trim', explode("\n", str_replace("\r", '', (string) $text))), 'strlen'));
    }

    public const ICONS = [
        'laptop' => '<rect x="4" y="5" width="16" height="10" rx="1.5"/><path d="M2 19h20l-2-4H4z"/>',
        'desktop' => '<rect x="3" y="4" width="18" height="12" rx="1.5"/><path d="M9 20h6M12 16v4"/>',
        'wrench' => '<path d="M14.5 6.5a4 4 0 0 0-5.3 5.3L3.5 17.5a1.5 1.5 0 0 0 2.1 2.1l5.7-5.7a4 4 0 0 0 5.3-5.3l-2.4 2.4-2.2-.6-.6-2.2z"/>',
        'network' => '<rect x="9.5" y="3" width="5" height="4" rx="1"/><rect x="2.5" y="17" width="5" height="4" rx="1"/><rect x="16.5" y="17" width="5" height="4" rx="1"/><path d="M12 7v5M5 17v-2.5h14V17M12 12v2.5"/>',
        'camera' => '<path d="M3 8l12-3 1.5 5L4.5 13z"/><path d="M16.5 10l3.5-1v4l-3 1"/><path d="M8 12.5V18M5 18h6"/>',
        'code' => '<path d="M8 8l-4 4 4 4M16 8l4 4-4 4M13.5 5l-3 14"/>',
        'globe' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 3.8 5.6 3.8 9s-1.3 6.4-3.8 9c-2.5-2.6-3.8-5.6-3.8-9S9.5 5.6 12 3z"/>',
        'headset' => '<path d="M4 14v-2a8 8 0 0 1 16 0v2"/><rect x="3" y="13" width="4" height="6" rx="1.5"/><rect x="17" y="13" width="4" height="6" rx="1.5"/><path d="M19 19c0 1.5-2 2.5-5 2.5"/>',
        'building' => '<rect x="4" y="3" width="11" height="18" rx="1"/><path d="M15 9h5v12h-5M7.5 7h1M10.5 7h1M7.5 11h1M10.5 11h1M7.5 15h1M10.5 15h1M9 21v-3"/>',
        'school' => '<path d="M2 9l10-5 10 5-10 5z"/><path d="M6 11v5c3 2.5 9 2.5 12 0v-5M22 9v6"/>',
        'shield' => '<path d="M12 3l8 3v6c0 4.5-3.4 8-8 9-4.6-1-8-4.5-8-9V6z"/><path d="M8.5 12l2.5 2.5 4.5-5"/>',
        'printer' => '<path d="M7 8V3h10v5"/><rect x="3" y="8" width="18" height="9" rx="1.5"/><path d="M7 14h10v7H7z"/>',
        'mouse' => '<rect x="6" y="3" width="12" height="18" rx="6"/><path d="M12 7v4"/>',
        'database' => '<ellipse cx="12" cy="5.5" rx="7" ry="2.5"/><path d="M5 5.5v13c0 1.4 3.1 2.5 7 2.5s7-1.1 7-2.5v-13M5 12c0 1.4 3.1 2.5 7 2.5s7-1.1 7-2.5"/>',
        'check' => '<circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.7 2.7L16 9.5"/>',
        'users' => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c.5-3.6 3.2-6 6.5-6s6 2.4 6.5 6"/><circle cx="17" cy="9" r="2.5"/><path d="M17 14c2.6 0 4.2 2 4.5 5"/>',
        'spark' => '<path d="M12 3v4M12 17v4M3 12h4M17 12h4M6 6l2.5 2.5M15.5 15.5L18 18M18 6l-2.5 2.5M8.5 15.5L6 18"/>',
        'star' => '<path d="M12 3.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8-5.2-2.7-5.2 2.7 1-5.8-4.3-4.1 5.9-.9z"/>',
        'tool' => '<path d="M3 21l7-7M13 4l7 7-3 3-7-7z"/><path d="M10 14l-2-2"/>',
        'tag' => '<path d="M3 12V4h8l10 10-8 8z"/><circle cx="7.5" cy="8" r="1.5"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>',
        'phone' => '<path d="M5 3h4l2 5-2.5 1.5a11 11 0 0 0 6 6L16 13l5 2v4a2 2 0 0 1-2 2C10.7 21 3 13.3 3 5a2 2 0 0 1 2-2z"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3.5 6.5l8.5 6.5 8.5-6.5"/>',
        'map' => '<path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
        'chat' => '<path d="M4 4h16v12H8l-4 4z"/><path d="M8 9h8M8 12h5"/>',
        'cloud' => '<path d="M7 18h10a4 4 0 0 0 .5-8A6 6 0 0 0 6 9a4.5 4.5 0 0 0 1 9z"/>',
        'server' => '<rect x="4" y="3" width="16" height="7" rx="1.5"/><rect x="4" y="14" width="16" height="7" rx="1.5"/><path d="M8 6.5h.01M8 17.5h.01M12 6.5h5M12 17.5h5"/>',
        'quote' => '<path d="M5 17c0-4 1.5-7 5-9M14 17c0-4 1.5-7 5-9"/><path d="M5 17h4v-4H5zM14 17h4v-4h-4z"/>',
    ];

    public static function icon(?string $name, string $class = 'ic'): string
    {
        $p = self::ICONS[$name ?? ''] ?? self::ICONS['spark'];
        return '<svg class="' . esc($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $p . '</svg>';
    }

    /** Hero illustration: laptop, network nodes, shield and camera drawn as one vector scene, tinted with the brand colours. */
    public static function heroArt(string $primary, string $accent, string $variant = 'home'): string
    {
        $p = esc($primary); $a = esc($accent);
        $extra = match ($variant) {
            'school' => '<path d="M330 60l50-25 50 25-50 25z" fill="' . $a . '" opacity=".9"/><path d="M350 72v22c18 12 42 12 60 0V72" fill="none" stroke="#fff" stroke-width="5"/>',
            default => '<g transform="translate(345 40)"><path d="M40 0l38 14v28c0 22-16 39-38 46-22-7-38-24-38-46V14z" fill="' . $a . '"/><path d="M24 44l12 12 22-24" fill="none" stroke="#fff" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/></g>',
        };
        return <<<SVG
<svg class="hero-art" viewBox="0 0 480 360" role="img" aria-label="Illustration of connected computers, network and security" xmlns="http://www.w3.org/2000/svg">
<defs><linearGradient id="hg1" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="{$p}" stop-opacity=".18"/><stop offset="1" stop-color="{$a}" stop-opacity=".14"/></linearGradient>
<linearGradient id="hg2" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="{$p}"/><stop offset="1" stop-color="{$p}" stop-opacity=".75"/></linearGradient></defs>
<circle cx="250" cy="185" r="165" fill="url(#hg1)"/>
<g stroke="{$p}" stroke-width="3" stroke-dasharray="6 7" opacity=".55" fill="none"><path d="M120 110 L230 170"/><path d="M380 110 L270 170"/><path d="M100 270 L210 230"/><path d="M400 270 L290 230"/></g>
<g fill="#fff" stroke="{$p}" stroke-width="4"><circle cx="110" cy="105" r="22"/><circle cx="92" cy="275" r="22"/><circle cx="408" cy="275" r="22"/></g>
<g stroke="{$p}" stroke-width="3" fill="none" stroke-linecap="round"><path d="M100 105h20M110 95v20"/><path d="M82 270h20M82 280h14"/><path d="M398 268l10 8 10-8"/></g>
<rect x="150" y="120" width="200" height="130" rx="12" fill="url(#hg2)"/>
<rect x="163" y="133" width="174" height="104" rx="6" fill="#fff"/>
<rect x="176" y="148" width="70" height="10" rx="5" fill="{$p}" opacity=".85"/><rect x="176" y="166" width="120" height="8" rx="4" fill="{$p}" opacity=".25"/><rect x="176" y="181" width="100" height="8" rx="4" fill="{$p}" opacity=".25"/>
<rect x="176" y="200" width="44" height="24" rx="5" fill="{$a}"/><rect x="228" y="200" width="44" height="24" rx="5" fill="{$p}" opacity=".18"/><rect x="280" y="200" width="44" height="24" rx="5" fill="{$p}" opacity=".18"/>
<path d="M120 252h260l-22 26H142z" fill="{$p}" opacity=".9"/><rect x="225" y="258" width="50" height="6" rx="3" fill="#fff" opacity=".6"/>
{$extra}
<g transform="translate(48 175)"><rect width="66" height="40" rx="8" fill="#fff" stroke="{$p}" stroke-width="4"/><circle cx="44" cy="20" r="9" fill="{$a}"/><path d="M14 40v18M2 58h26" stroke="{$p}" stroke-width="4" stroke-linecap="round"/></g>
<g transform="translate(380 175)"><rect width="56" height="70" rx="8" fill="#fff" stroke="{$p}" stroke-width="4"/><path d="M12 18h32M12 32h32M12 46h32" stroke="{$p}" stroke-width="4" stroke-linecap="round" opacity=".6"/><circle cx="44" cy="58" r="4" fill="{$a}"/></g>
</svg>
SVG;
    }

    /** Small generated cover image for items without an uploaded picture. */
    public static function cover(string $icon, string $primary, string $accent, int $seed = 0): string
    {
        $p = esc($primary); $a = esc($accent);
        $rot = ($seed * 37) % 360;
        $path = self::ICONS[$icon] ?? self::ICONS['spark'];
        return '<svg class="cover" viewBox="0 0 320 180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"><defs><linearGradient id="cv' . $seed . '" gradientTransform="rotate(' . $rot . ' .5 .5)"><stop offset="0" stop-color="' . $p . '"/><stop offset="1" stop-color="' . $a . '"/></linearGradient></defs><rect width="320" height="180" fill="url(#cv' . $seed . ')"/><circle cx="270" cy="30" r="70" fill="#fff" opacity=".08"/><circle cx="40" cy="170" r="90" fill="#fff" opacity=".07"/><g transform="translate(124 54) scale(3)" fill="none" stroke="#fff" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">' . $path . '</g></svg>';
    }
}
