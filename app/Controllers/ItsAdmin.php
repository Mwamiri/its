<?php
namespace App\Controllers;
use App\Models\{UserModel, SettingModel, MaintenanceModel, ClientModel, DocumentTemplateModel};
use App\Libraries\MailLib;
class ItsAdmin extends ItsBase {
    public function panel() {
        if ($r = $this->needAdmin()) return $r;
        $tab = $this->request->getGet('tab') ?? 'users';
        return view('itsupport/admin', [
            'title' => 'Admin', 'tab' => $tab,
            'users' => (new UserModel())->orderBy('name')->findAll(),
            'logs' => $this->db->table('audit_log')->select('audit_log.*, users.name AS user_name')->join('users', 'users.id = audit_log.user_id', 'left')->orderBy('audit_log.created_at', 'DESC')->limit(200)->get()->getResultArray(),
            'mails' => $this->db->table('mail_log')->orderBy('created_at', 'DESC')->limit(200)->get()->getResultArray(),
            'schedules' => $this->db->table('maintenance_schedules')->select('maintenance_schedules.*, clients.name AS client_name')->join('clients', 'clients.id = maintenance_schedules.client_id')->get()->getResultArray(),
            'clients' => (new ClientModel())->orderBy('name')->findAll(),
            'matrix' => \App\Libraries\Perm::matrix(),
            'apiKeys' => $this->db->table('api_keys')->orderBy('id', 'DESC')->get()->getResultArray(),
            'webhooks' => $this->db->table('webhooks')->orderBy('id')->get()->getResultArray(),
            'customizations' => json_decode((string) SettingModel::get('customization_log', '[]'), true) ?: [],
            'templates' => (new DocumentTemplateModel())->orderBy('template_type')->orderBy('name')->findAll(),
            'editTemplate' => (new DocumentTemplateModel())->find((int) $this->request->getGet('edit_template')),
            'customFields' => $tab === 'fields' ? array_merge(...array_map(static fn($e) => \App\Libraries\CustomFields::defs($e, true), array_keys(\App\Libraries\CustomFields::ENTITIES))) : [],
            'editField' => $tab === 'fields' && $this->db->tableExists('custom_fields') ? $this->db->table('custom_fields')->where('id', (int) $this->request->getGet('edit_field'))->get()->getRowArray() : null,
        ]);
    }

    /** Create or update an admin-defined field for clients, assets or tickets. */
    public function saveField() {
        if ($r = $this->needAdmin()) return $r;
        $back = redirect()->to(base_url('its-admin?tab=fields'));
        if (!$this->db->tableExists('custom_fields')) return $back->with('err', 'Run the v3 database migration first.');
        $id = (int) $this->request->getPost('id');
        $entity = (string) $this->request->getPost('entity');
        $type = (string) $this->request->getPost('type');
        $label = mb_substr(trim((string) $this->request->getPost('label')), 0, 120);
        $key = strtolower(trim((string) $this->request->getPost('field_key')));
        if ($key === '') $key = trim(preg_replace('/[^a-z0-9]+/', '_', strtolower($label)), '_');
        $key = substr($key, 0, 40);
        $options = array_values(array_unique(array_filter(array_map(static fn($o) => mb_substr(trim($o), 0, 100), explode(',', (string) $this->request->getPost('options'))), 'strlen')));
        if (!isset(\App\Libraries\CustomFields::ENTITIES[$entity]) || !isset(\App\Libraries\CustomFields::TYPES[$type]) || $label === '' || !preg_match('/^[a-z][a-z0-9_]{0,39}$/', $key)) {
            return $back->with('err', 'Choose where the field appears, a type, and a label (the key must start with a letter).');
        }
        if ($type === 'select' && (!$options || count($options) > 50)) return $back->with('err', 'Dropdown fields need 1 to 50 comma-separated options.');
        $dupe = $this->db->table('custom_fields')->where('entity', $entity)->where('field_key', $key)->where('id !=', $id)->countAllResults();
        if ($dupe) return $back->with('err', 'A field with that key already exists for ' . strtolower(\App\Libraries\CustomFields::ENTITIES[$entity]) . '.');
        $data = [
            'entity' => $entity, 'field_key' => $key, 'label' => $label, 'type' => $type,
            'options_json' => $type === 'select' ? json_encode($options, JSON_UNESCAPED_UNICODE) : null,
            'help' => mb_substr(trim((string) $this->request->getPost('help')), 0, 255) ?: null,
            'required' => $this->request->getPost('required') ? 1 : 0,
            'show_in_list' => $this->request->getPost('show_in_list') ? 1 : 0,
            'show_in_report' => $this->request->getPost('show_in_report') ? 1 : 0,
            'show_in_portal' => $this->request->getPost('show_in_portal') ? 1 : 0,
            'sort_order' => max(0, min(999, (int) $this->request->getPost('sort_order'))),
            'active' => $this->request->getPost('active') === '0' ? 0 : 1,
        ];
        if ($id) $this->db->table('custom_fields')->where('id', $id)->update($data);
        else $this->db->table('custom_fields')->insert($data + ['created_at' => date('Y-m-d H:i:s')]);
        \App\Libraries\CustomFields::reset();
        $this->audit($id ? 'custom_field_updated' : 'custom_field_created', 'settings', $entity . '.' . $key);
        return $back->with('ok', 'Field "' . $label . '" saved. It now appears on ' . strtolower(\App\Libraries\CustomFields::ENTITIES[$entity]) . ' forms.');
    }
    public function addUser() {
        if ($this->needAdmin()) return $this->needAdmin();
        $username = trim((string) $this->request->getPost('username'));
        $password = (string) $this->request->getPost('password');
        $role = (string) $this->request->getPost('role');
        $clientId = (int) $this->request->getPost('client_id');
        $back = redirect()->to(base_url('its-admin?tab=users'));
        if (!in_array($role, ['admin', 'manager', 'technician', 'client'], true) || $username === '' || strlen($password) < 8) {
            return $back->with('err', 'Enter a username, a valid role and a password of at least 8 characters.');
        }
        if ($role === 'client' && !(new ClientModel())->find($clientId)) return $back->with('err', 'Choose the client this portal login belongs to.');
        if ((new UserModel())->where('username', $username)->first()) return $back->with('err', 'That username already exists.');
        (new UserModel())->insert(['username' => $username, 'password' => password_hash($password, PASSWORD_DEFAULT), 'name' => trim((string) $this->request->getPost('name')) ?: $username, 'role' => $role, 'active' => 1, 'client_id' => $role === 'client' ? $clientId : null]);
        $this->audit('user_created', 'users', $username . ' (' . $role . ')');
        return $back->with('ok', 'User created.');
    }
    public function saveSettings() {
        if ($r = $this->needAdmin()) return $r;
        $companyName = trim((string) $this->request->getPost('company_name'));
        $companyEmail = trim((string) $this->request->getPost('company_email'));
        $headerTagline = trim((string) $this->request->getPost('header_tagline'));
        $footerText = trim((string) $this->request->getPost('footer_text'));
        if ($companyName === '' || mb_strlen($companyName) > 190 || mb_strlen($companyEmail) > 190
            || mb_strlen($headerTagline) > 190 || mb_strlen($footerText) > 255
            || ($companyEmail !== '' && !filter_var($companyEmail, FILTER_VALIDATE_EMAIL))) {
            return redirect()->to(base_url('its-admin?tab=settings'))
                ->with('err', 'Enter a company name, a valid email address, and branding text within the stated limits.');
        }

        $oldCompanyName = SettingModel::get('company_name', '');
        SettingModel::put('company_name', $companyName);
        SettingModel::put('company_email', $companyEmail);
        SettingModel::put('header_tagline', $headerTagline);
        SettingModel::put('footer_text', $footerText);
        // Letterhead details printed on service reports, asset registers and client reports.
        foreach (\App\Libraries\Brand::FIELDS as $key => [, $max]) {
            if ($this->request->getPost($key) !== null) SettingModel::put($key, mb_substr(trim((string) $this->request->getPost($key)), 0, $max));
        }
        if ($oldCompanyName !== $companyName) {
            $this->logCustom('Company', 'Name changed', $oldCompanyName . ' to ' . $companyName);
        }
        $this->logCustom('Branding', 'Header and footer updated', '');

        $file = $this->request->getFile('company_logo');
        if ($file && $file->getError() !== UPLOAD_ERR_NO_FILE) {
            if (!$file->isValid() || $file->hasMoved()) {
                return redirect()->to(base_url('its-admin?tab=settings'))
                    ->with('err', 'The logo upload was not valid. Choose a PNG, JPEG, GIF, or WebP image under 5 MB.');
            }
            $path = $this->saveUpload($file, 'branding');
            if ($path === null) {
                return redirect()->to(base_url('its-admin?tab=settings'))
                    ->with('err', 'The logo must be a PNG, JPEG, GIF, or WebP image under 5 MB.');
            }
            SettingModel::put('company_logo', $path);
            $this->logCustom('Branding', 'Logo updated', '');
        }

        return redirect()->to(base_url('its-admin?tab=settings'))->with('ok', 'Company branding updated.');
    }
    public function saveTheme()
    {
        if ($r = $this->needAdmin()) return $r;

        $themes = ['ocean', 'emerald', 'violet', 'sunset'];
        $theme = (string) $this->request->getPost('theme');
        if (!in_array($theme, $themes, true)) {
            return redirect()->to(base_url('its-admin?tab=settings'))
                ->with('err', 'Choose one of the available appearance themes.');
        }

        SettingModel::put('appearance_theme', $theme);
        $this->logCustom('Appearance', 'Theme changed', $theme);

        return redirect()->to(base_url('its-admin?tab=settings'))
            ->with('ok', 'Appearance updated.');
    }
    public function saveTemplate()
    {
        if ($r = $this->needAdmin()) return $r;

        $type = (string) $this->request->getPost('template_type');
        $name = trim((string) $this->request->getPost('name'));
        if (!in_array($type, ['ticket', 'quote', 'report'], true) || $name === '') {
            return redirect()->to(base_url('its-admin?tab=templates'))
                ->with('err', 'Choose a template type and enter a template name.');
        }

        $model = new DocumentTemplateModel();
        $id = (int) $this->request->getPost('id');
        $data = [
            'template_type' => $type,
            'name' => mb_substr($name, 0, 120),
            'subject' => trim((string) $this->request->getPost('subject')),
            'body' => trim((string) $this->request->getPost('body')),
        ];

        if ($id > 0) {
            if (!$model->find($id)) {
                return redirect()->to(base_url('its-admin?tab=templates'))
                    ->with('err', 'That template no longer exists.');
            }
            $model->update($id, $data);
            $this->audit('template_updated', 'templates', $data['name']);
            $message = 'Template updated.';
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $model->insert($data);
            $this->audit('template_created', 'templates', $data['name']);
            $message = 'Template created.';
        }

        return redirect()->to(base_url('its-admin?tab=templates'))->with('ok', $message);
    }
    public function deleteTemplate(int $id)
    {
        if ($r = $this->needAdmin()) return $r;

        $model = new DocumentTemplateModel();
        $template = $model->find($id);
        if (!$template) {
            return redirect()->to(base_url('its-admin?tab=templates'))
                ->with('err', 'That template no longer exists.');
        }

        $model->delete($id);
        $this->audit('template_deleted', 'templates', $template['name']);

        return redirect()->to(base_url('its-admin?tab=templates'))
            ->with('ok', 'Template deleted.');
    }
    public function saveReports() {
        if ($r = $this->needAdmin()) return $r;
        foreach (['monthly_report_recipients','monthly_report_send_day','monthly_report_send_hour','weekly_report_recipients','weekly_report_send_hour'] as $k) {
            if ($this->request->getPost($k) !== null) SettingModel::put($k, (string) $this->request->getPost($k));
        }
        SettingModel::put('monthly_report_enabled', $this->request->getPost('monthly_report_enabled') ? '1' : '0');
        SettingModel::put('weekly_report_enabled', $this->request->getPost('weekly_report_enabled') ? '1' : '0');
        $this->logCustom('Reports', 'Report settings updated', '');
        return redirect()->to(base_url('its-admin?tab=reports'));
    }
    public function mailTest() {
        if ($r = $this->needAdmin()) return $r;
        $ok = (new MailLib())->send([(string) $this->request->getPost('test_email')], 'Test Email', '<p>Test from IT Support System (CodeIgniter 4).</p>');
        return redirect()->to(base_url('its-admin?tab=mail'))->with($ok ? 'ok' : 'err', $ok ? 'Test sent.' : 'Test failed.');
    }
    public function addSchedule() {
        if ($r = $this->needAdmin()) return $r;
        (new MaintenanceModel())->insert(['client_id' => $this->request->getPost('client_id'), 'name' => $this->request->getPost('name'), 'description' => $this->request->getPost('description'), 'interval_days' => (int) $this->request->getPost('interval_days'), 'next_run' => date('Y-m-d')]);
        $this->audit('maintenance_created', 'maintenance', (string) $this->request->getPost('name'));
        return redirect()->to(base_url('its-admin?tab=maintenance'));
    }
    public function backup() {
        if ($r = $this->needAdmin()) return $r;
        $sql = \App\Libraries\Backup::sql();
        $compressed = gzencode($sql, 9);
        if ($compressed === false) {
            log_message('error', 'Unable to compress the database backup.');
            return redirect()->to(base_url('its-admin'))->with('err', 'The database backup could not be compressed.');
        }

        return $this->response
            ->download('itsupport-ci4-' . date('Ymd-His') . '.sql.gz', $compressed)
            ->setContentType('application/gzip')
            ->setHeader('Cache-Control', 'no-store, private');
    }
}