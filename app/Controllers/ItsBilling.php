<?php
namespace App\Controllers;

class ItsBilling extends ItsBase {
    private function rows(string $month): array {
        $from = $month . '-01 00:00:00';
        $to = date('Y-m-d 00:00:00', strtotime($month . '-01 +1 month'));
        $mins = [];
        foreach ($this->db->query('SELECT t.client_id, SUM(CASE WHEN e.billable=1 THEN e.minutes ELSE 0 END) b, SUM(e.minutes) a FROM time_entries e JOIN tickets t ON t.id=e.ticket_id WHERE e.created_at >= ? AND e.created_at < ? GROUP BY t.client_id', [$from, $to])->getResultArray() as $r) $mins[$r['client_id']] = $r;
        $out = [];
        foreach ($this->db->table('clients')->orderBy('name')->get()->getResultArray() as $c) {
            $billable = (int) ($mins[$c['id']]['b'] ?? 0);
            $total = (int) ($mins[$c['id']]['a'] ?? 0);
            $ret = (float) $c['retainer_hours'];
            $over = max(0, $billable / 60 - $ret);
            $out[] = ['id' => $c['id'], 'name' => $c['name'], 'retainer' => $ret, 'rate' => (float) $c['hourly_rate'], 'billable_h' => round($billable / 60, 2), 'total_h' => round($total / 60, 2), 'over_h' => round($over, 2), 'amount' => round($over * (float) $c['hourly_rate'], 2)];
        }
        return $out;
    }
    private function month(): string {
        $m = (string) $this->request->getGet('month');
        return preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $m) ? $m : date('Y-m');
    }
    public function index() {
        if ($r = $this->needAdmin()) return $r;
        $month = $this->month();
        return view('itsupport/billing', ['title' => 'Billing', 'rows' => $this->rows($month), 'month' => $month]);
    }
    public function save($id) {
        if ($r = $this->needAdmin()) return $r;
        $hours = max(0, min(9999, (float) $this->request->getPost('retainer_hours')));
        $rate = max(0, min(999999, (float) $this->request->getPost('hourly_rate')));
        $this->db->table('clients')->where('id', (int) $id)->update(['retainer_hours' => $hours, 'hourly_rate' => $rate]);
        $this->audit('retainer_saved', 'clients', $id . ':' . $hours . 'h@' . $rate);
        return redirect()->to(base_url('its-billing?month=' . $this->month()))->with('ok', 'Retainer saved.');
    }
    public function csv() {
        if ($r = $this->needAdmin()) return $r;
        $month = $this->month();
        $fh = fopen('php://temp', 'w+');
        fputcsv($fh, ['Client', 'Retainer hours', 'Rate', 'Billable hours', 'Total hours', 'Overage hours', 'Amount due']);
        foreach ($this->rows($month) as $r) fputcsv($fh, [ltrim($r['name'], "=+-@\t\r"), $r['retainer'], $r['rate'], $r['billable_h'], $r['total_h'], $r['over_h'], $r['amount']]);
        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);
        return $this->response->download('billing-' . $month . '.csv', $csv)->setContentType('text/csv');
    }
    public function icalToken() {
        if ($r = $this->needAdmin()) return $r;
        \App\Models\SettingModel::put('ical_token', bin2hex(random_bytes(20)));
        $this->audit('ical_token', 'settings', 'regenerated');
        return redirect()->to(base_url('its-billing'))->with('ok', 'Calendar link updated.');
    }
    public function ical() {
        $tok = (string) \App\Models\SettingModel::get('ical_token', '');
        if ($tok === '' || !hash_equals($tok, (string) $this->request->getGet('token'))) return $this->response->setStatusCode(404);
        $esc = static fn(string $s) => str_replace(["\\", ';', ',', "\r", "\n"], ["\\\\", '\;', '\,', '', '\n'], $s);
        $l = ['BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//IT Support//EN', 'CALSCALE:GREGORIAN'];
        $rows = $this->db->table('tickets')->select('tickets.*, clients.name AS cn')->join('clients', 'clients.id = tickets.client_id')->whereNotIn('status', ['completed', 'closed'])->get()->getResultArray();
        foreach ($rows as $t) {
            $when = $t['due_at'] ?: ($t['visit_date'] ? $t['visit_date'] . ' 09:00:00' : null);
            if (!$when) continue;
            $s = gmdate('Ymd\THis\Z', strtotime($when));
            $l = array_merge($l, ['BEGIN:VEVENT', 'UID:ticket-' . $t['id'] . '@itsupport', 'DTSTAMP:' . gmdate('Ymd\THis\Z'), 'DTSTART:' . $s, 'DTEND:' . gmdate('Ymd\THis\Z', strtotime($when) + 3600), 'SUMMARY:' . $esc($t['ticket_number'] . ' ' . $t['subject'] . ' (' . $t['cn'] . ')'), 'END:VEVENT']);
        }
        $l[] = 'END:VCALENDAR';
        return $this->response->setContentType('text/calendar')->setBody(implode("\r\n", $l) . "\r\n");
    }
}