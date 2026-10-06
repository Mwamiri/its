<?php
namespace App\Controllers;
use App\Models\{UserModel, SettingModel, MaintenanceModel, ClientModel};
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
            'customizations' => json_decode((string) SettingModel::get('customization_log', '[]'), true) ?: [],
        ]);
    }
    public function addUser() {
        if ($r = $this->needAdmin()) return $r;
        (new UserModel())->insert(['username' => $this->request->getPost('username'), 'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT), 'name' => $this->request->getPost('name'), 'role' => $this->request->getPost('role'), 'active' => 1]);
        $this->audit('user_created', 'users', (string) $this->request->getPost('username'));
        return redirect()->to('/its-admin?tab=users');
    }
    public function saveSettings() {
        if ($r = $this->needAdmin()) return $r;
        $old = SettingModel::get('company_name', '');
        if ($this->request->getPost('company_name')) { SettingModel::set('company_name', $this->request->getPost('company_name')); if ($old !== $this->request->getPost('company_name')) $this->logCustom('Company', 'Name changed', $old . ' to ' . $this->request->getPost('company_name')); }
        if ($this->request->getPost('company_email')) SettingModel::set('company_email', $this->request->getPost('company_email'));
        $file = $this->request->getFile('company_logo');
        if ($file && $file->isValid() && !$file->hasMoved()) { $p = $this->saveUpload($file, 'clients'); if ($p) { SettingModel::set('company_logo', $p); $this->logCustom('Branding', 'Logo updated', ''); } }
        return redirect()->to('/its-admin?tab=settings');
    }
    public function saveReports() {
        if ($r = $this->needAdmin()) return $r;
        foreach (['monthly_report_recipients','monthly_report_send_day','monthly_report_send_hour','weekly_report_recipients','weekly_report_send_hour'] as $k) {
            if ($this->request->getPost($k) !== null) SettingModel::set($k, (string) $this->request->getPost($k));
        }
        SettingModel::set('monthly_report_enabled', $this->request->getPost('monthly_report_enabled') ? '1' : '0');
        SettingModel::set('weekly_report_enabled', $this->request->getPost('weekly_report_enabled') ? '1' : '0');
        $this->logCustom('Reports', 'Report settings updated', '');
        return redirect()->to('/its-admin?tab=reports');
    }
    public function mailTest() {
        if ($r = $this->needAdmin()) return $r;
        $ok = (new MailLib())->send([(string) $this->request->getPost('test_email')], 'Test Email', '<p>Test from IT Support System (CodeIgniter 4).</p>');
        return redirect()->to('/its-admin?tab=mail')->with($ok ? 'ok' : 'err', $ok ? 'Test sent.' : 'Test failed.');
    }
    public function addSchedule() {
        if ($r = $this->needAdmin()) return $r;
        (new MaintenanceModel())->insert(['client_id' => $this->request->getPost('client_id'), 'name' => $this->request->getPost('name'), 'description' => $this->request->getPost('description'), 'interval_days' => (int) $this->request->getPost('interval_days'), 'next_run' => date('Y-m-d')]);
        $this->audit('maintenance_created', 'maintenance', (string) $this->request->getPost('name'));
        return redirect()->to('/its-admin?tab=maintenance');
    }
    public function backup() {
        if ($r = $this->needAdmin()) return $r;
        $sql = "-- IT Support CI4 backup " . date('c') . "\n";
        foreach (['settings','clients','assets','tickets','tasks','task_photos','signatures','quotes','quote_items','maintenance_schedules','users'] as $table) {
            foreach ($this->db->table($table)->get()->getResultArray() as $row) {
                $cols = array_keys($row);
                $vals = array_map(fn($v) => $v === null ? 'NULL' : $this->db->escape($v), array_values($row));
                $sql .= "INSERT INTO `$table` (`" . implode('`,`', $cols) . "`) VALUES (" . implode(',', $vals) . ");\n";
            }
        }
        return $this->response->download('itsupport-ci4-' . date('Ymd-His') . '.sql', $sql);
    }
}