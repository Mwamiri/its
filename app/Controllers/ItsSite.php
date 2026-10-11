<?php

namespace App\Controllers;

use App\Libraries\Site;
use App\Models\SettingModel;

/** Website CMS: pages, content blocks, site settings/SEO and contact leads. */
class ItsSite extends ItsBase
{
    private function guard()
    {
        if ($r = $this->needLogin()) return $r;
        if (!Site::ready()) return redirect()->to(base_url('its-dashboard'))->with('err', 'Run the v3 database migration to enable the website CMS.');
        return null;
    }

    public function index()
    {
        if ($r = $this->guard()) return $r;
        $tab = (string) ($this->request->getGet('tab') ?: 'pages');
        $kind = (string) ($this->request->getGet('kind') ?: 'service');
        if (!isset(Site::KINDS[$kind])) $kind = 'service';
        $t = fn(string $n) => $this->db->table($n);
        return view('itsupport/site_admin', [
            'title' => 'Website', 'tab' => $tab, 'kind' => $kind,
            'pages' => $t('site_pages')->orderBy('menu_order')->orderBy('id')->get()->getResultArray(),
            'editPage' => $tab === 'pages' ? $t('site_pages')->where('id', (int) $this->request->getGet('edit'))->get()->getRowArray() : null,
            'items' => $t('site_items')->where('kind', $kind)->orderBy('sort_order')->orderBy('id')->get()->getResultArray(),
            'editItem' => $tab === 'content' ? $t('site_items')->where('id', (int) $this->request->getGet('edit'))->get()->getRowArray() : null,
            'counts' => array_column($t('site_items')->select('kind, COUNT(*) n')->groupBy('kind')->get()->getResultArray(), 'n', 'kind'),
            'leads' => $tab === 'leads' ? $t('site_leads')->orderBy('id', 'DESC')->limit(300)->get()->getResultArray() : [],
            'newLeads' => $t('site_leads')->where('status', 'new')->countAllResults(),
            'site' => Site::info(),
        ] + $this->builderData($tab));
    }

    private function builderData(string $tab): array
    {
        if ($tab !== 'pages') return ['design' => Site::design(), 'editBlocks' => []];
        $id = (int) $this->request->getGet('edit');
        $page = $id ? $this->db->table('site_pages')->where('id', $id)->get()->getRowArray() : null;
        $blocks = $page ? Site::blocksFor($page) : Site::normalizeBlocks([['type' => 'hero', 'style' => 'center', 'title' => 'New page'], ['type' => 'text', 'text' => "## Heading\n\nWrite your content here."], ['type' => 'cta']]);
        return ['design' => Site::design(), 'editBlocks' => $blocks];
    }

    private function txt(string $k, int $max): string { return mb_substr(trim((string) $this->request->getPost($k)), 0, $max); }

    public function savePage()
    {
        if ($r = $this->guard()) return $r;
        $id = (int) $this->request->getPost('id');
        $page = $id ? $this->db->table('site_pages')->where('id', $id)->get()->getRowArray() : null;
        if ($id && !$page) return redirect()->to(base_url('its-site'))->with('err', 'Page not found.');
        $title = $this->txt('title', 190);
        $slug = $page && $page['is_system'] ? $page['slug'] : trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower($this->txt('slug', 80) ?: $title)), '-');
        $reserved = ['its', 'api', 'home', 'contact', 'sitemap', 'robots', 'assets', 'uploads', 'public', 'index'];
        if ($title === '' || $slug === '' || (!$page || !$page['is_system']) && (in_array($slug, $reserved, true) || str_starts_with($slug, 'its-'))) {
            return redirect()->back()->withInput()->with('err', 'Enter a title. The page address cannot be empty or start with "its-", and some words (home, contact, api…) are reserved.');
        }
        if ($this->db->table('site_pages')->where('slug', $slug)->where('id !=', $id)->countAllResults()) return redirect()->back()->withInput()->with('err', 'Another page already uses the address /' . $slug . '.');
        $data = $this->pageFields($title, $slug) + [
            'published' => $page && $page['slug'] === 'home' ? 1 : ($this->request->getPost('published') ? 1 : 0), 'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($page) $this->db->table('site_pages')->where('id', $id)->update($data);
        else { $this->db->table('site_pages')->insert($data + ['is_system' => 0]); $id = (int) $this->db->insertID(); }
        $this->audit($page ? 'site_page_updated' : 'site_page_created', 'website', $slug);
        return redirect()->to(base_url('its-site?tab=pages&edit=' . $id))->with('ok', 'Page saved. View it at /' . ($slug === 'home' ? '' : $slug));
    }

    /** Page row from the builder form; the hero fields mirror the first hero block (used for SEO fallbacks). */
    private function pageFields(string $title, string $slug): array
    {
        $blocks = Site::normalizeBlocks((string) $this->request->getPost('blocks'));
        $hero = null; foreach ($blocks as $b) if ($b['type'] === 'hero') { $hero = $b; break; }
        $row = [
            'slug' => $slug, 'title' => $title, 'menu_label' => $this->txt('menu_label', 60) ?: null,
            'show_in_menu' => $this->request->getPost('show_in_menu') ? 1 : 0, 'menu_order' => max(0, min(999, (int) $this->request->getPost('menu_order'))),
            'meta_title' => $this->txt('meta_title', 190) ?: null, 'meta_description' => $this->txt('meta_description', 300) ?: null,
            'hero_title' => $hero['title'] ?? null, 'hero_subtitle' => $hero['subtitle'] ?? null, 'hero_cta_label' => $hero['button_label'] ?? null,
            'hero_cta_url' => $hero['button_url'] ?? null, 'hero_image' => ($hero['image'] ?? '') ?: null,
        ];
        if ($this->db->fieldExists('blocks', 'site_pages')) $row['blocks'] = json_encode($blocks, JSON_UNESCAPED_UNICODE);
        return $row;
    }

    /** Live preview for the builder: renders exactly what Save would publish, without saving. */
    public function preview()
    {
        if ($r = $this->guard()) return $r;
        $title = $this->txt('title', 190) ?: 'Preview';
        $slug = trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower($this->txt('slug', 80) ?: $title)), '-') ?: 'preview';
        $page = $this->pageFields($title, $slug) + ['id' => 0, 'body' => '', 'sections' => '', 'published' => 1, 'is_system' => 0];
        $this->response->setHeader('Cache-Control', 'no-store');
        return $this->response->setBody(Site::render($page, Site::normalizeBlocks((string) $this->request->getPost('blocks')), true));
    }

    /** Image upload for builder blocks (AJAX). Returns the stored path and a fresh CSRF token. */
    public function upload()
    {
        if ($r = $this->guard()) return $this->response->setStatusCode(403)->setJSON(['error' => 'Not allowed']);
        $file = $this->request->getFile('image');
        $path = $file && $file->getError() !== UPLOAD_ERR_NO_FILE ? $this->saveUpload($file, 'site') : null;
        if (!$path) return $this->response->setStatusCode(422)->setJSON(['error' => 'Choose a PNG, JPEG, GIF or WebP image under 5 MB.', 'csrf' => csrf_hash()]);
        $this->audit('site_image_uploaded', 'website', $path);
        return $this->response->setJSON(['path' => $path, 'url' => base_url($path), 'csrf' => csrf_hash()]);
    }

    /** Header, body and footer design options. */
    public function saveDesign()
    {
        if ($r = $this->guard()) return $r;
        foreach (Site::DESIGN as $k => [$label, $type, $opt]) {
            $v = $this->request->getPost($k);
            $v = match ($type) {
                'bool' => $v ? '1' : '0',
                'select' => isset($opt[(string) $v]) ? (string) $v : array_key_first($opt),
                'number' => (string) max(0, min(200, (int) $v)),
                'css' => mb_substr((string) $v, 0, 20000),
                default => mb_substr(trim((string) $v), 0, 1000),
            };
            SettingModel::put($k, $v);
        }
        $this->audit('site_design_saved', 'website');
        return redirect()->to(base_url('its-site?tab=design'))->with('ok', 'Design saved. Open the website to see it.');
    }

    public function saveMenu()
    {
        if ($r = $this->guard()) return $r;
        $hasParent = $this->db->fieldExists('menu_parent', 'site_pages');
        $pages = (array) $this->request->getPost('pages');
        foreach ($pages as $id => $data) {
            $id = (int) $id;
            if (!$id) continue;
            $row = [
                'show_in_menu' => empty($data['show']) ? 0 : 1,
                'menu_label'   => mb_substr(trim((string) ($data['label'] ?? '')), 0, 60) ?: null,
                'menu_order'   => max(0, min(999, (int) ($data['order'] ?? 0))),
            ];
            if ($hasParent) $row['menu_parent'] = mb_substr(trim((string) ($data['parent'] ?? '')), 0, 80) ?: null;
            $this->db->table('site_pages')->where('id', $id)->update($row);
        }
        $labels  = (array) $this->request->getPost('link_label');
        $urls    = (array) $this->request->getPost('link_url');
        $orders  = (array) $this->request->getPost('link_order');
        $parents = (array) $this->request->getPost('link_parent');
        $custom = [];
        foreach ($labels as $i => $label) {
            $label = mb_substr(trim((string) $label), 0, 60);
            $url   = mb_substr(trim((string) ($urls[$i] ?? '')), 0, 500);
            if ($label !== '' && $url !== '') {
                $item = ['label' => $label, 'url' => $url, 'order' => max(0, min(999, (int) ($orders[$i] ?? 80)))];
                $p = mb_substr(trim((string) ($parents[$i] ?? '')), 0, 80);
                if ($p !== '') $item['parent'] = $p;
                $custom[] = $item;
            }
        }
        SettingModel::put('site_menu_custom', json_encode($custom, JSON_UNESCAPED_UNICODE));
        $this->audit('site_menu_saved', 'website');
        return redirect()->to(base_url('its-site?tab=menu'))->with('ok', 'Menu saved.');
    }

    public function deletePage($id)
    {
        if ($r = $this->guard()) return $r;
        $page = $this->db->table('site_pages')->where('id', (int) $id)->get()->getRowArray();
        if (!$page || $page['is_system']) return redirect()->to(base_url('its-site'))->with('err', 'Built-in pages cannot be deleted; unpublish them instead.');
        $this->db->table('site_pages')->where('id', (int) $id)->delete();
        $this->audit('site_page_deleted', 'website', $page['slug']);
        return redirect()->to(base_url('its-site'))->with('ok', 'Page deleted.');
    }

    public function saveItem()
    {
        if ($r = $this->guard()) return $r;
        $id = (int) $this->request->getPost('id');
        $kind = (string) $this->request->getPost('kind');
        if (!isset(Site::KINDS[$kind])) return redirect()->to(base_url('its-site?tab=content'))->with('err', 'Choose a content type.');
        $back = base_url('its-site?tab=content&kind=' . $kind);
        $title = $this->txt('title', 190);
        if ($title === '') return redirect()->back()->withInput()->with('err', 'Enter a title.');
        $icon = (string) $this->request->getPost('icon');
        $data = ['kind' => $kind, 'title' => $title, 'subtitle' => $this->txt('subtitle', 255) ?: null, 'body' => mb_substr((string) $this->request->getPost('body'), 0, 10000),
            'bullets' => mb_substr((string) $this->request->getPost('bullets'), 0, 5000), 'icon' => isset(Site::ICONS[$icon]) ? $icon : null,
            'price' => $this->txt('price', 60) ?: null, 'tags' => $this->txt('tags', 255) ?: null, 'link_url' => $this->txt('link_url', 255) ?: null,
            'featured' => $this->request->getPost('featured') ? 1 : 0, 'active' => $this->request->getPost('active') ? 1 : 0,
            'sort_order' => max(0, min(9999, (int) $this->request->getPost('sort_order'))), 'updated_at' => date('Y-m-d H:i:s')];
        $img = $this->request->getFile('image');
        if ($img && $img->getError() !== UPLOAD_ERR_NO_FILE) {
            $path = $this->saveUpload($img, 'site');
            if (!$path) return redirect()->back()->withInput()->with('err', 'The image must be PNG, JPEG, GIF or WebP under 5 MB.');
            $data['image_path'] = $path;
        }
        if ($this->request->getPost('remove_image')) $data['image_path'] = null;
        if ($id) $this->db->table('site_items')->where('id', $id)->update($data);
        else $this->db->table('site_items')->insert($data);
        $this->audit($id ? 'site_item_updated' : 'site_item_created', 'website', $kind . ': ' . $title);
        return redirect()->to($back)->with('ok', '"' . $title . '" saved.');
    }

    public function deleteItem($id)
    {
        if ($r = $this->guard()) return $r;
        $it = $this->db->table('site_items')->where('id', (int) $id)->get()->getRowArray();
        if (!$it) return redirect()->to(base_url('its-site?tab=content'));
        $this->db->table('site_items')->where('id', (int) $id)->delete();
        $this->audit('site_item_deleted', 'website', $it['kind'] . ': ' . $it['title']);
        return redirect()->to(base_url('its-site?tab=content&kind=' . $it['kind']))->with('ok', 'Deleted.');
    }

    public function saveSettings()
    {
        if ($r = $this->guard()) return $r;
        foreach (Site::SETTINGS as $k => [$label, $type]) {
            $v = $type === 'bool' ? ($this->request->getPost($k) ? '1' : '0') : mb_substr(trim((string) $this->request->getPost($k)), 0, $type === 'textarea' ? 1000 : 300);
            if ($type === 'email' && $v !== '' && !filter_var($v, FILTER_VALIDATE_EMAIL)) return redirect()->back()->withInput()->with('err', $label . ' is not a valid email address.');
            if ($type === 'url' && $v !== '' && !preg_match('#^https://#i', $v)) return redirect()->back()->withInput()->with('err', $label . ' must start with https://');
            if ($type === 'color' && !preg_match('/^#[0-9a-f]{6}$/i', $v)) $v = '';
            SettingModel::put($k, $v);
        }
        $this->audit('site_settings_saved', 'website');
        return redirect()->to(base_url('its-site?tab=settings'))->with('ok', 'Website settings saved.');
    }

    /** Lead workflow: mark contacted/closed, or turn the enquiry into a client record. */
    public function lead($id)
    {
        if ($r = $this->guard()) return $r;
        $lead = $this->db->table('site_leads')->where('id', (int) $id)->get()->getRowArray();
        if (!$lead) return redirect()->to(base_url('its-site?tab=leads'));
        $action = (string) $this->request->getPost('action');
        if (in_array($action, ['new', 'contacted', 'closed'], true)) {
            $this->db->table('site_leads')->where('id', $lead['id'])->update(['status' => $action]);
            return redirect()->to(base_url('its-site?tab=leads'))->with('ok', 'Lead marked ' . $action . '.');
        }
        if ($action === 'client') {
            $cid = (int) $lead['client_id'];
            if (!$cid) {
                $this->db->table('clients')->insert(['name' => $lead['name'], 'contact_person' => $lead['name'], 'phone' => $lead['phone'], 'email' => $lead['email'], 'notes' => 'From website enquiry #' . $lead['id'] . ($lead['service'] ? ' (' . $lead['service'] . ')' : '') . ":\n" . $lead['message']]);
                $cid = (int) $this->db->insertID();
            }
            $this->db->table('site_leads')->where('id', $lead['id'])->update(['client_id' => $cid, 'status' => 'contacted']);
            $this->audit('lead_to_client', 'website', (string) $lead['id']);
            return redirect()->to(base_url('its-clients-view/' . $cid))->with('ok', 'Client created from the website enquiry.');
        }
        return redirect()->to(base_url('its-site?tab=leads'));
    }
}
