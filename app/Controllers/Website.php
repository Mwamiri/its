<?php

namespace App\Controllers;

use App\Libraries\Site;

/** Public company website (CMS-driven). Staff and clients sign in through the Login button. */
class Website extends BaseController
{
    private function db() { return \Config\Database::connect(); }

    private function render(array $page, int $status = 200)
    {
        $this->response->setStatusCode($status);
        return Site::render($page);
    }

    /** The website is shown only when its tables exist, the module is on and it is enabled in Website settings. */
    private function live(): bool
    {
        return Site::ready() && \App\Libraries\Modules::enabled('website') && Site::info()['enabled'];
    }

    public function index()
    {
        if (!$this->live()) return redirect()->to(base_url('its-login'));
        return $this->page('home');
    }

    public function page(string $slug = 'home')
    {
        if (!$this->live()) return redirect()->to(base_url('its-login'));
        $slug = strtolower($slug);
        $page = preg_match('/^[a-z0-9-]{1,80}$/', $slug) ? $this->db()->table('site_pages')->where('slug', $slug)->where('published', 1)->get()->getRowArray() : null;
        if (!$page) {
            $nf = ['slug' => '404', 'title' => 'Page not found', 'hero_title' => 'Page not found', 'hero_subtitle' => 'The page you are looking for has moved or no longer exists.', 'hero_cta_label' => 'Go to the home page', 'hero_cta_url' => '', 'hero_image' => null, 'body' => '', 'sections' => 'service,cta', 'meta_title' => 'Page not found', 'meta_description' => '', 'robots' => 'noindex'];
            return $this->render($nf, 404);
        }
        if ($slug === 'home' && service('uri')->getSegment(1) === 'home') return redirect()->to(base_url(), 301);
        return $this->render($page);
    }

    /** Contact form → lead in Admin → Website → Leads, plus an email to the website address. */
    public function contact()
    {
        $back = redirect()->to(base_url('contact') . '#contact-form');
        if (!Site::ready()) return $back;
        // Spam guards: hidden honeypot field, minimum fill time and a per-IP hourly limit.
        if ((string) $this->request->getPost('website') !== '' || time() - (int) $this->request->getPost('t') < 3) return $back->with('site_ok', 'Thank you. We will get back to you shortly.');
        $ip = $this->request->getIPAddress();
        $bucket = 'lead_' . md5($ip) . '_' . date('YmdH');
        if ((int) cache($bucket) >= 5) return $back->withInput()->with('site_err', 'Too many messages from your network. Please email us instead.');
        $in = fn(string $k, int $max) => mb_substr(trim((string) $this->request->getPost($k)), 0, $max);
        $lead = ['name' => $in('name', 150), 'phone' => $in('phone', 60), 'email' => $in('email', 190), 'service' => $in('service', 150), 'message' => $in('message', 3000)];
        if ($lead['name'] === '' || $lead['message'] === '' || ($lead['email'] === '' && $lead['phone'] === '')) return $back->withInput()->with('site_err', 'Please give your name, a message, and an email or phone number.');
        if ($lead['email'] !== '' && !filter_var($lead['email'], FILTER_VALIDATE_EMAIL)) return $back->withInput()->with('site_err', 'Please enter a valid email address.');
        $this->db()->table('site_leads')->insert($lead + ['source_page' => mb_substr((string) $this->request->getPost('source'), 0, 80), 'ip_address' => $ip, 'status' => 'new', 'created_at' => date('Y-m-d H:i:s')]);
        cache()->save($bucket, (int) cache($bucket) + 1, 3600);
        $to = Site::info()['email'];
        if ($to !== '') {
            $rows = '';
            foreach (['Name' => 'name', 'Phone' => 'phone', 'Email' => 'email', 'Service' => 'service', 'Message' => 'message'] as $l => $k) if ($lead[$k] !== '') $rows .= '<tr><th style="text-align:left;padding:6px;border:1px solid #ddd;background:#f8fafc">' . $l . '</th><td style="padding:6px;border:1px solid #ddd">' . nl2br(esc($lead[$k])) . '</td></tr>';
            try { (new \App\Libraries\MailLib())->send([$to], 'Website enquiry from ' . $lead['name'], '<h2 style="font-family:Arial">New website enquiry</h2><table style="border-collapse:collapse;font-family:Arial;font-size:14px">' . $rows . '</table><p style="font-family:Arial"><a href="' . base_url('its-site?tab=leads') . '">Open the leads inbox</a></p>'); } catch (\Throwable $e) { log_message('error', 'Lead email failed: {m}', ['m' => $e->getMessage()]); }
        }
        return $back->with('site_ok', 'Thank you, ' . $lead['name'] . '. Your message has been received and we will get back to you shortly.');
    }

    /** Public IT support request form → lead in Admin → Website → Leads, plus email notification. */
    public function supportRequest()
    {
        $rawRet = trim((string) $this->request->getPost('redirect_to'));
        $sameHost = filter_var($rawRet, FILTER_VALIDATE_URL) && rtrim((string) parse_url($rawRet, PHP_URL_HOST), '/') === rtrim((string) parse_url(base_url(), PHP_URL_HOST), '/');
        $back = redirect()->to(($sameHost ? $rawRet : base_url('support-request')) . '#support-form');
        if (!Site::ready()) return $back;
        // Spam guards: honeypot, minimum fill time, per-IP hourly limit.
        if ((string) $this->request->getPost('website') !== '' || time() - (int) $this->request->getPost('t') < 3)
            return $back->with('site_ok', 'Your request has been submitted. We will contact you shortly.');
        $ip = $this->request->getIPAddress();
        $bucket = 'support_' . md5($ip) . '_' . date('YmdH');
        if ((int) cache($bucket) >= 5)
            return $back->withInput()->with('site_err', 'Too many submissions from your network. Please call or email us directly.');
        $in = fn(string $k, int $max) => mb_substr(trim((string) $this->request->getPost($k)), 0, $max);
        $name    = $in('name', 150);
        $email   = $in('email', 190);
        $phone   = $in('phone', 60);
        $company = $in('company', 150);
        $device  = $in('device', 300);
        $issue   = $in('issue', 3000);
        $urgency = in_array($in('urgency', 20), ['low', 'normal', 'urgent'], true) ? $in('urgency', 20) : 'normal';
        if ($name === '' || $issue === '')
            return $back->withInput()->with('site_err', 'Please give your name and describe the issue.');
        if ($email === '' && $phone === '')
            return $back->withInput()->with('site_err', 'Please give an email address or phone number so we can contact you.');
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL))
            return $back->withInput()->with('site_err', 'Please enter a valid email address.');
        $message = "**Issue:** {$issue}\n\n**Device/Equipment:** {$device}\n\n**Urgency:** {$urgency}" . ($company !== '' ? "\n\n**Company:** {$company}" : '');
        $this->db()->table('site_leads')->insert(['name' => $name, 'phone' => $phone, 'email' => $email, 'service' => 'IT Support Request', 'message' => $message, 'source_page' => 'support-request', 'ip_address' => $ip, 'status' => 'new', 'created_at' => date('Y-m-d H:i:s')]);
        $ref = 'SR-' . strtoupper(base_convert((string) ($this->db()->insertID() + 10000), 10, 36));
        cache()->save($bucket, (int) cache($bucket) + 1, 3600);
        $to = Site::info()['email'];
        if ($to !== '') {
            $rows = '';
            foreach (['Name' => $name, 'Phone' => $phone, 'Email' => $email, 'Company' => $company, 'Device' => $device, 'Urgency' => ucfirst($urgency), 'Issue' => $issue] as $l => $v) {
                if ($v !== '') $rows .= '<tr><th style="text-align:left;padding:6px;border:1px solid #ddd;background:#f8fafc">' . $l . '</th><td style="padding:6px;border:1px solid #ddd">' . nl2br(esc($v)) . '</td></tr>';
            }
            try { (new \App\Libraries\MailLib())->send([$to], 'IT Support Request (' . $ref . ') from ' . $name, '<h2 style="font-family:Arial">New IT Support Request</h2><p style="font-family:Arial;color:#6b7280">Ref: ' . $ref . '</p><table style="border-collapse:collapse;font-family:Arial;font-size:14px">' . $rows . '</table><p style="font-family:Arial"><a href="' . base_url('its-site?tab=leads') . '">Open the leads inbox</a></p>'); } catch (\Throwable $e) { log_message('error', 'Support request email failed: {m}', ['m' => $e->getMessage()]); }
        }
        return $back->with('site_ok', 'Thank you, ' . $name . '. Your support request (' . $ref . ') has been received. We will contact you shortly.');
    }

    /**
     * Public JSON API for remote systems (UMI, Churchz, etc.) to fetch approved extensions.
     * GET /extensions-api?platform=umi  (omit param for all platforms)
     */
    public function extensionsApi()
    {
        $db = $this->db();
        if (!$db->tableExists('extensions')) {
            return $this->response
                ->setContentType('application/json')
                ->setHeader('Access-Control-Allow-Origin', '*')
                ->setBody('[]');
        }
        $q = $db->table('extensions')->where('status', 'approved');
        $platform = strtolower(trim((string) $this->request->getGet('platform')));
        if ($platform !== '') {
            $q->where('platform', $platform);
        }
        $rows = $q->orderBy('name')->get()->getResultArray();

        $out = array_map(function (array $r) {
            return [
                'id'             => (int)    $r['id'],
                'name'           => (string) $r['name'],
                'version'        => (string) $r['version'],
                'platform'       => (string) $r['platform'],
                'type'           => (string) $r['type'],
                'description'    => (string) ($r['description'] ?? ''),
                'author'         => (string) ($r['author'] ?? ''),
                'license'        => (string) ($r['license'] ?? ''),
                'tags'           => (string) ($r['tags'] ?? ''),
                'min_version'    => (string) ($r['min_version'] ?? ''),
                'screenshot_url' => !empty($r['screenshot']) ? base_url('uploads/extensions/' . $r['screenshot']) : '',
                'download_url'   => base_url('extensions-download/' . $r['id']),
                'file_size'      => (int)    ($r['file_size'] ?? 0),
                'download_count' => (int)    ($r['download_count'] ?? 0),
            ];
        }, $rows);

        return $this->response
            ->setContentType('application/json')
            ->setHeader('Access-Control-Allow-Origin', '*')
            ->setHeader('Cache-Control', 'public, max-age=300')
            ->setBody(json_encode($out, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /** Public extension marketplace — approved extensions, no login required. */
    public function extensions()
    {
        $db = $this->db();
        $rows = [];
        if ($db->tableExists('extensions')) {
            $rows = $db->table('extensions')->where('status', 'approved')->orderBy('platform')->orderBy('name')->get()->getResultArray();
        }
        $grouped = [];
        foreach ($rows as $r) {
            $grouped[$r['platform']][] = $r;
        }
        return view('site/extensions_page', [
            'site'       => Site::info(),
            'design'     => Site::design(),
            'menu'       => Site::menu(),
            'user'       => session('its_user'),
            'extensions' => $grouped,
            'total'      => count($rows),
        ]);
    }

    /** Public download of an approved extension — requires a registered email unless logged in as staff. */
    public function extensionDownload(int $id)
    {
        $db = $this->db();
        if (!$db->tableExists('extensions')) {
            return $this->response->setStatusCode(404)->setBody('Extension not found.');
        }
        $ext = $db->table('extensions')->where('id', $id)->where('status', 'approved')->get()->getRowArray();
        $filePath = $ext ? WRITEPATH . 'extensions/' . $ext['file_path'] : '';
        if (!$ext || !file_exists($filePath)) {
            return $this->response->setStatusCode(404)->setBody('Extension not found.');
        }
        // Staff session always passes the gate
        if (!session('its_user')) {
            $email = strtolower(trim((string) ($this->request->getGet('email') ?? session('ext_download_email') ?? '')));
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return redirect()->to(base_url('extensions') . '?access=required&ext=' . $id . '#access-section');
            }
            if (!$db->tableExists('ext_subscriptions') || !$this->subscriptionAllowsByEmail($db, $email, $ext)) {
                return redirect()->to(base_url('extensions') . '?access=denied&ext=' . $id . '#access-section');
            }
            session()->set('ext_download_email', $email);
        }
        $db->table('extensions')->where('id', $id)->set('download_count', 'download_count + 1', false)->update();
        $dlName = strtolower(preg_replace('/[^a-z0-9._-]/i', '-', (string) $ext['name'])) . '-' . $ext['version'] . '.zip';
        return $this->response->download($filePath, null)->setFileName($dlName);
    }

    private function subscriptionAllowsByEmail($db, string $email, array $ext): bool
    {
        $sub = $db->table('ext_subscriptions s')
            ->select('s.status, s.expires_at, s.package_id, p.scope, p.allowed_platforms, p.allowed_extension_ids')
            ->join('ext_packages p', 'p.id = s.package_id', 'left')
            ->where('LOWER(s.subscriber_email)', $email)
            ->where('s.status', 'active')
            ->get()->getRowArray();
        if (!$sub) return false;
        if (!empty($sub['expires_at']) && strtotime((string) $sub['expires_at']) < time()) return false;
        // Paid extensions require an assigned package
        $isPaid = ($ext['price_type'] ?? 'free') === 'paid';
        if ($isPaid && !$sub['package_id']) return false;
        $scope = $sub['scope'] ?? 'all';
        if ($scope === 'all' || !$sub['package_id']) return true;
        if ($scope === 'platforms') {
            $platforms = array_map('trim', explode(',', (string) ($sub['allowed_platforms'] ?? '')));
            return in_array($ext['platform'], $platforms, true);
        }
        if ($scope === 'extensions') {
            $ids = array_map('intval', array_filter(explode(',', (string) ($sub['allowed_extension_ids'] ?? ''))));
            return in_array((int) $ext['id'], $ids, true);
        }
        return false;
    }

    /** Public software products showcase. */
    public function products()
    {
        return view('site/products_page', [
            'site'   => Site::info(),
            'design' => Site::design(),
            'menu'   => Site::menu(),
            'user'   => session('its_user'),
        ]);
    }

    public function sitemap()
    {
        if (!Site::ready()) return $this->response->setStatusCode(404);
        $x = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($this->db()->table('site_pages')->select('slug, updated_at')->where('published', 1)->orderBy('menu_order')->get()->getResultArray() as $p) {
            $x .= '<url><loc>' . esc(Site::url($p['slug'])) . '</loc>' . ($p['updated_at'] ? '<lastmod>' . esc(substr($p['updated_at'], 0, 10)) . '</lastmod>' : '') . '<priority>' . ($p['slug'] === 'home' ? '1.0' : '0.8') . '</priority></url>';
        }
        return $this->response->setContentType('application/xml')->setBody($x . '</urlset>');
    }

    public function robots()
    {
        return $this->response->setContentType('text/plain')->setBody("User-agent: *\nDisallow: /its-\nDisallow: /api/\nAllow: /\nSitemap: " . base_url('sitemap.xml') . "\n");
    }
}
