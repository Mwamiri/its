<?php
namespace App\Controllers;
use App\Libraries\{NetworkMonitor, Qr, ReportLib, MailLib};
use App\Models\SettingModel;
class ItsMisc extends ItsBase {
    public function manifest() {
        $icons = [['src' => base_url('icon.svg'), 'sizes' => 'any', 'type' => 'image/svg+xml', 'purpose' => 'any']];
        return $this->response->setContentType('application/manifest+json')->setBody(json_encode([
            'name' => $this->setting('company_name', 'IT Support'), 'short_name' => 'IT Support',
            'start_url' => base_url('its-dashboard'), 'scope' => base_url('/'), 'display' => 'standalone',
            'background_color' => '#0f172a', 'theme_color' => '#0f172a', 'icons' => $icons,
        ]));
    }
    public function qr($id) {
        $row = $this->db->table('assets')->where('id', $id)->get()->getRow();
        if (!$row) return $this->response->setStatusCode(404)->setBody('Not found');
        return $this->response->setContentType('image/svg+xml')->setBody((new Qr())->svg(base_url('its-assets-view/' . $id)));
    }
    public function cronMonthly() {
        if (($this->request->getGet('token') ?? '') !== $this->setting('cron_token', '')) return $this->response->setStatusCode(403)->setBody('Forbidden');
        $ym = $this->request->getGet('ym') ?: date('Y-m');
        return $this->response->setBody(service('reportlib')->sendMonthly($ym) ? 'Sent ' . $ym : 'Not sent');
    }
    public function cronWeekly() {
        if (($this->request->getGet('token') ?? '') !== $this->setting('cron_token', '')) return $this->response->setStatusCode(403)->setBody('Forbidden');
        return $this->response->setBody(service('reportlib')->sendWeekly($this->request->getGet('ymd') ?: date('Y-m-d', strtotime('last monday'))) ? 'Sent' : 'Not sent');
    }
    public function cronMaintenance() {
        if (($this->request->getGet('token') ?? '') !== $this->setting('cron_token', '')) return $this->response->setStatusCode(403)->setBody('Forbidden');
        $today = date('Y-m-d');
        $due = $this->db->table('maintenance_schedules')->where('active', 1)->groupStart()->where('next_run <=', $today)->orWhere('next_run', null)->groupEnd()->get()->getResultArray();
        foreach ($due as $m) {
            $this->db->table('tickets')->insert(['ticket_number' => $this->seq('ticket', 'TCK-'), 'client_id' => $m['client_id'], 'subject' => 'PM: ' . $m['name'], 'description' => $m['description'] . "\n\n(Auto-created by scheduler)", 'priority' => 'medium', 'status' => 'new', 'visit_date' => $today]);
            $this->db->table('maintenance_schedules')->where('id', $m['id'])->update(['last_run' => $today, 'next_run' => date('Y-m-d', strtotime('+' . $m['interval_days'] . ' days'))]);
        }
        return $this->response->setBody('OK ' . date('Y-m-d H:i:s') . ' due=' . count($due));
    }
    public function cronDeviceMonitor() {
        $token = (string) ($this->request->getGet('token') ?? '');
        $expected = (string) $this->setting('cron_token', '');
        if ($expected === '' || !hash_equals($expected, $token)) {
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }

        $devices = $this->db->table('network_devices')->where('monitor_enabled', 1)->orderBy('last_checked', 'ASC')->limit(100)->get()->getResultArray();
        $monitor = new NetworkMonitor();
        $checked = 0;
        foreach ($devices as $device) {
            $result = $monitor->check($device);
            $this->db->table('network_devices')->where('id', $device['id'])->update([
                'monitor_state' => $result['state'],
                'monitor_port' => $result['port'],
                'last_checked' => $result['last_checked'],
                'last_seen' => $result['last_seen'],
            ]);
            $checked++;
        }
        return $this->response->setBody('Checked ' . $checked . ' monitored device(s) at ' . date('Y-m-d H:i:s'));
    }
}