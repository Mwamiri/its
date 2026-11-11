<?php
namespace App\Controllers;

class ItsExtSubscriptions extends ItsBase {

    public function index() {
        if ($r = $this->needAdmin()) return $r;

        $subs = $this->db->table('ext_subscriptions s')
            ->select('s.*, p.name pkg_name, p.scope pkg_scope, p.allowed_platforms pkg_platforms, p.price_label pkg_price')
            ->join('ext_packages p', 'p.id = s.package_id', 'left')
            ->orderBy('s.created_at', 'DESC')
            ->get()->getResultArray();

        $packages = $this->db->table('ext_packages')->orderBy('name')->get()->getResultArray();

        // extension count for stats
        $stats = ['total' => 0, 'pending' => 0, 'active' => 0, 'suspended' => 0];
        foreach ($subs as $s) {
            $stats['total']++;
            $k = $s['status'];
            if (isset($stats[$k])) $stats[$k]++;
        }

        return view('itsupport/ext_subscriptions', [
            'title'    => 'Extension Subscriptions',
            'subs'     => $subs,
            'packages' => $packages,
            'stats'    => $stats,
        ]);
    }

    public function savePackage() {
        if ($r = $this->needAdmin()) return $r;
        $id    = (int) $this->request->getPost('id');
        $scope = $this->request->getPost('scope');
        if (!in_array($scope, ['all', 'platforms', 'extensions'], true)) $scope = 'all';
        $platforms   = implode(',', array_map('trim', explode(',', (string) $this->request->getPost('allowed_platforms'))));
        $extIds      = implode(',', array_filter(array_map('intval', explode(',', (string) $this->request->getPost('allowed_extension_ids')))));
        $row = [
            'name'                  => mb_substr(trim((string) $this->request->getPost('name')), 0, 100),
            'description'           => mb_substr(trim((string) $this->request->getPost('description')), 0, 500),
            'scope'                 => $scope,
            'allowed_platforms'     => $platforms,
            'allowed_extension_ids' => $extIds,
            'price_label'           => mb_substr(trim((string) $this->request->getPost('price_label')), 0, 60),
            'updated_at'            => date('Y-m-d H:i:s'),
        ];
        if ($row['name'] === '') return redirect()->back()->with('err', 'Package name is required.');
        if ($id > 0) {
            $this->db->table('ext_packages')->where('id', $id)->update($row);
            $this->audit('ext_package_update', "Package #{$id} updated");
        } else {
            $row['created_at'] = date('Y-m-d H:i:s');
            $this->db->table('ext_packages')->insert($row);
            $this->audit('ext_package_create', 'Package created: ' . $row['name']);
        }
        return redirect()->to(base_url('its-ext-subscriptions'))->with('ok', 'Package saved.');
    }

    public function deletePackage(int $id) {
        if ($r = $this->needAdmin()) return $r;
        $this->db->table('ext_subscriptions')->where('package_id', $id)->set('package_id', null)->update();
        $this->db->table('ext_packages')->where('id', $id)->delete();
        $this->audit('ext_package_delete', "Package #{$id} deleted");
        return redirect()->to(base_url('its-ext-subscriptions'))->with('ok', 'Package deleted.');
    }

    /** Approve a pending subscription: generate download key, assign package. */
    public function approve(int $id) {
        if ($r = $this->needAdmin()) return $r;
        $sub = $this->db->table('ext_subscriptions')->where('id', $id)->get()->getRowArray();
        if (!$sub) return redirect()->back()->with('err', 'Subscription not found.');

        $pkg     = (int) $this->request->getPost('package_id');
        $expires = trim((string) $this->request->getPost('expires_at'));
        $notes   = mb_substr(trim((string) $this->request->getPost('notes')), 0, 2000);
        $key     = $sub['download_key'] ?: bin2hex(random_bytes(32));

        $this->db->table('ext_subscriptions')->where('id', $id)->update([
            'status'      => 'active',
            'package_id'  => $pkg ?: null,
            'download_key'=> $key,
            'expires_at'  => $expires ?: null,
            'notes'       => $notes,
            'approved_by' => (int) $this->user()['id'],
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
        $this->audit('ext_sub_approve', "Subscription #{$id} approved for {$sub['subscriber_email']}");

        // Try to email the key to the subscriber
        if ($sub['subscriber_email'] !== '') {
            try {
                (new \App\Libraries\MailLib())->send(
                    [$sub['subscriber_email']],
                    'Your Extension Marketplace Download Key',
                    '<h2 style="font-family:Arial">Extension Marketplace Access Approved</h2>'
                    . '<p style="font-family:Arial">Hello ' . esc($sub['subscriber_name']) . ',</p>'
                    . '<p style="font-family:Arial">Your access to the extension marketplace has been approved. Use the key below when downloading extensions:</p>'
                    . '<pre style="font-family:monospace;background:#f4f7fb;padding:16px;border-radius:8px;font-size:14px">' . esc($key) . '</pre>'
                    . '<p style="font-family:Arial">Visit <a href="' . base_url('extensions') . '">' . base_url('extensions') . '</a> to browse and download extensions.</p>'
                    . '<p style="font-family:Arial;color:#666;font-size:13px">Enter your key on the marketplace page, or append <code>?key=' . esc($key) . '</code> to any download URL.</p>'
                );
            } catch (\Throwable $e) {
                log_message('error', 'Subscription key email failed: {m}', ['m' => $e->getMessage()]);
            }
        }

        return redirect()->to(base_url('its-ext-subscriptions'))->with('ok', 'Subscription approved. Key emailed to ' . $sub['subscriber_email'] . '.');
    }

    public function suspend(int $id) {
        if ($r = $this->needAdmin()) return $r;
        $this->db->table('ext_subscriptions')->where('id', $id)->update(['status' => 'suspended', 'updated_at' => date('Y-m-d H:i:s')]);
        $this->audit('ext_sub_suspend', "Subscription #{$id} suspended");
        return redirect()->to(base_url('its-ext-subscriptions'))->with('ok', 'Subscription suspended.');
    }

    public function reactivate(int $id) {
        if ($r = $this->needAdmin()) return $r;
        $this->db->table('ext_subscriptions')->where('id', $id)->update(['status' => 'active', 'updated_at' => date('Y-m-d H:i:s')]);
        $this->audit('ext_sub_reactivate', "Subscription #{$id} reactivated");
        return redirect()->to(base_url('its-ext-subscriptions'))->with('ok', 'Subscription reactivated.');
    }

    public function deleteSub(int $id) {
        if ($r = $this->needAdmin()) return $r;
        $this->db->table('ext_subscriptions')->where('id', $id)->delete();
        $this->audit('ext_sub_delete', "Subscription #{$id} deleted");
        return redirect()->to(base_url('its-ext-subscriptions'))->with('ok', 'Subscription deleted.');
    }

    /** Admin directly creates an active subscription with an auto-generated key. */
    public function createDirect() {
        if ($r = $this->needAdmin()) return $r;
        $name   = mb_substr(trim((string) $this->request->getPost('name')), 0, 150);
        $email  = mb_substr(trim((string) $this->request->getPost('email')), 0, 190);
        $system = mb_substr(trim((string) $this->request->getPost('system')), 0, 60);
        $pkg    = (int) $this->request->getPost('package_id');
        $notes  = mb_substr(trim((string) $this->request->getPost('notes')), 0, 2000);
        $expires= trim((string) $this->request->getPost('expires_at'));
        if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('err', 'Please enter a valid name and email address.');
        }
        $key = bin2hex(random_bytes(32));
        $this->db->table('ext_subscriptions')->insert([
            'subscriber_name'   => $name,
            'subscriber_email'  => $email,
            'subscriber_system' => $system,
            'package_id'        => $pkg ?: null,
            'download_key'      => $key,
            'status'            => 'active',
            'expires_at'        => $expires ?: null,
            'notes'             => $notes,
            'approved_by'       => (int) $this->user()['id'],
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ]);
        $this->audit('ext_sub_create', "Subscription created for {$email}");
        // Email the key
        if ($email !== '') {
            try {
                (new \App\Libraries\MailLib())->send(
                    [$email],
                    'Your Extension Marketplace Access',
                    '<h2 style="font-family:Arial">Extension Marketplace Access</h2>'
                    . '<p style="font-family:Arial">Hello ' . esc($name) . ',</p>'
                    . '<p style="font-family:Arial">Your extension marketplace subscription is active. To download extensions, visit the marketplace and enter your registered email address:</p>'
                    . '<p style="font-family:monospace;background:#f4f7fb;padding:16px;border-radius:8px;font-size:14px;font-family:Arial"><strong>' . esc($email) . '</strong></p>'
                    . '<p style="font-family:Arial"><a href="' . base_url('extensions') . '" style="background:#2563eb;color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:600">Go to Extensions</a></p>'
                    . '<p style="font-family:Arial;color:#666;font-size:12px">Your internal reference key: <code>' . esc($key) . '</code></p>'
                );
            } catch (\Throwable $e) { log_message('error', 'Sub key email: {m}', ['m' => $e->getMessage()]); }
        }
        return redirect()->to(base_url('its-ext-subscriptions'))->with('ok', 'Subscription created and key emailed to ' . $email . '.');
    }

    /** Public self-registration — no auth required. Creates a pending subscription. */
    public function register() {
        $name   = mb_substr(trim((string) $this->request->getPost('name')), 0, 150);
        $email  = mb_substr(trim((string) $this->request->getPost('email')), 0, 190);
        $system = mb_substr(trim((string) $this->request->getPost('system')), 0, 60);
        $pkg    = (int) $this->request->getPost('package_id');
        $notes  = mb_substr(trim((string) $this->request->getPost('notes')), 0, 500);
        $back   = redirect()->to(base_url('extensions') . '#access-section');

        if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $back->with('access_err', 'Please enter your name and a valid email address.');
        }
        // Rate limit: 3 requests per email per day
        $bucket = 'extsub_' . md5($email) . '_' . date('Ymd');
        if ((int) cache($bucket) >= 3) {
            return $back->with('access_err', 'Too many requests. Please email us directly.');
        }
        // Check for existing pending/active for this email
        $existing = $this->db->table('ext_subscriptions')
            ->where('subscriber_email', $email)
            ->whereIn('status', ['pending', 'active'])
            ->get()->getRowArray();
        if ($existing) {
            $msg = $existing['status'] === 'active'
                ? 'You already have an active subscription. Use your email address (' . $email . ') to unlock downloads on the extensions page.'
                : 'Your request is already pending review. We will contact you at ' . $email . '.';
            return $back->with('access_ok', $msg);
        }
        $this->db->table('ext_subscriptions')->insert([
            'subscriber_name'   => $name,
            'subscriber_email'  => $email,
            'subscriber_system' => $system,
            'package_id'        => $pkg ?: null,
            'download_key'      => '',
            'status'            => 'pending',
            'notes'             => $notes,
            'created_at'        => date('Y-m-d H:i:s'),
        ]);
        cache()->save($bucket, (int) cache($bucket) + 1, 86400);

        // Notify admin
        try {
            $to = \App\Models\SettingModel::get('company_email', '');
            if ($to) (new \App\Libraries\MailLib())->send(
                [$to],
                'New Extension Marketplace Access Request',
                '<h2 style="font-family:Arial">New Access Request</h2>'
                . '<p style="font-family:Arial"><b>Name:</b> ' . esc($name) . '<br><b>Email:</b> ' . esc($email) . '<br><b>System:</b> ' . esc($system) . '</p>'
                . '<p><a href="' . base_url('its-ext-subscriptions') . '">Review in admin panel</a></p>'
            );
        } catch (\Throwable $e) { log_message('error', 'Sub notify failed: {m}', ['m' => $e->getMessage()]); }

        return $back->with('access_ok', 'Thank you, ' . $name . '. Your request has been received. We will notify you at ' . $email . ' once your access is approved.');
    }
}
