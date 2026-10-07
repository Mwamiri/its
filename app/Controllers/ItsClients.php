<?php
namespace App\Controllers;
use App\Models\ClientModel;
use App\Libraries\{CustomFields, Perm};
class ItsClients extends ItsBase {
    private function listData(?array $edit): array {
        $clients = $this->db->table('clients')->select('clients.*')
            ->select('(SELECT COUNT(*) FROM ' . $this->db->prefixTable('assets') . ' a WHERE a.client_id = clients.id) AS asset_count', false)
            ->select("(SELECT COUNT(*) FROM " . $this->db->prefixTable('tickets') . " t WHERE t.client_id = clients.id AND t.status NOT IN ('completed','closed')) AS open_count", false)
            ->orderBy('name')->get()->getResultArray();
        $listFields = array_values(array_filter(CustomFields::defs('clients'), static fn($d) => (int) $d['show_in_list'] === 1));
        $old = old('cf');
        return ['title' => 'Clients', 'clients' => $clients, 'edit' => $edit, 'listFields' => $listFields,
            'cfValues' => $listFields ? CustomFields::values('clients', array_column($clients, 'id')) : [],
            'editValues' => is_array($old) ? $old : ($edit ? CustomFields::one('clients', (int) $edit['id']) : [])];
    }
    public function index() {
        if ($r = $this->needLogin()) return $r;
        return view('itsupport/clients', $this->listData(null));
    }
    public function edit($id = null) {
        if ($r = $this->needLogin()) return $r;
        $edit = (new ClientModel())->find($id);
        if (!$edit) return redirect()->to(base_url('its-clients'))->with('err', 'Client not found.');
        return view('itsupport/clients', $this->listData($edit));
    }
    public function save($id = null) {
        if ($r = $this->needLogin()) return $r;
        $m = new ClientModel();
        if ($id && !$m->find($id)) return redirect()->to(base_url('its-clients'))->with('err', 'Client not found.');
        $name = mb_substr(trim((string) $this->request->getPost('name')), 0, 190);
        $email = trim((string) $this->request->getPost('email'));
        if ($name === '') return redirect()->back()->withInput()->with('err', 'Client name is required.');
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) return redirect()->back()->withInput()->with('err', 'Enter a valid email address.');
        [$cf, $cfErrors] = CustomFields::validate('clients', $this->request->getPost('cf'));
        if ($cfErrors) return redirect()->back()->withInput()->with('err', implode(' ', $cfErrors));
        $data = ['name' => $name, 'contact_person' => mb_substr(trim((string) $this->request->getPost('contact_person')), 0, 190), 'phone' => mb_substr(trim((string) $this->request->getPost('phone')), 0, 60), 'email' => $email, 'address' => mb_substr(trim((string) $this->request->getPost('address')), 0, 2000), 'notes' => mb_substr(trim((string) $this->request->getPost('notes')), 0, 5000)];
        $file = $this->request->getFile('logo');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $logo = $this->saveUpload($file, 'clients');
            if ($logo === null) return redirect()->back()->withInput()->with('err', 'The logo must be a PNG, JPEG, GIF or WebP image under 5 MB.');
            $data['logo_path'] = $logo;
        }
        if ($id) { $m->update($id, $data); $this->audit('client_updated', 'clients', $data['name']); }
        else { $id = $m->insert($data); $this->audit('client_created', 'clients', $data['name']); }
        CustomFields::save('clients', (int) $id, $cf);
        return redirect()->to(base_url('its-clients-view/' . $id))->with('ok', 'Client saved.');
    }
    /** Client overview: details, equipment, tickets, quotes and report shortcuts on one page. */
    public function view($id) {
        if ($r = $this->needLogin()) return $r;
        $client = (new ClientModel())->find((int) $id);
        if (!$client) return redirect()->to(base_url('its-clients'))->with('err', 'Client not found.');
        $role = (string) $this->user()['role'];
        $assets = Perm::can($role, 'assets', 1) ? $this->db->table('assets')->where('client_id', $client['id'])->orderBy('name')->get()->getResultArray() : [];
        $tickets = Perm::can($role, 'tickets', 1) ? $this->db->table('tickets')->where('client_id', $client['id'])->orderBy('created_at', 'DESC')->limit(25)->get()->getResultArray() : [];
        $quotes = Perm::can($role, 'quotes', 1) ? $this->db->table('quotes')->where('client_id', $client['id'])->orderBy('created_at', 'DESC')->limit(10)->get()->getResultArray() : [];
        $open = $this->db->table('tickets')->where('client_id', $client['id'])->whereNotIn('status', ['completed', 'closed'])->countAllResults();
        $warrantySoon = count(array_filter($assets, static fn($a) => !empty($a['warranty_until']) && $a['warranty_until'] >= date('Y-m-d') && $a['warranty_until'] <= date('Y-m-d', strtotime('+60 days'))));
        $portalUsers = $this->db->table('users')->select('id, name, username, active')->where('role', 'client')->where('client_id', $client['id'])->get()->getResultArray();
        return view('itsupport/client_view', ['title' => $client['name'], 'client' => $client, 'assets' => $assets, 'tickets' => $tickets, 'quotes' => $quotes, 'open' => $open, 'warrantySoon' => $warrantySoon, 'portalUsers' => $portalUsers, 'cf' => CustomFields::one('clients', (int) $client['id']), 'canAssets' => Perm::can($role, 'assets', 1)]);
    }
    /** Printable, branded register of a client's equipment (also as CSV with ?format=csv). */
    public function assetReport($id) {
        if ($r = $this->needLogin()) return $r;
        if (!Perm::can((string) $this->user()['role'], 'assets', 1)) return redirect()->to(base_url('its-clients-view/' . (int) $id))->with('err', 'Your role cannot view assets.');
        $client = (new ClientModel())->find((int) $id);
        if (!$client) return redirect()->to(base_url('its-clients'))->with('err', 'Client not found.');
        [$assets, $fields, $values, $status] = self::registerData($this->db, (int) $client['id'], (string) $this->request->getGet('status'), 'show_in_report');
        if ($this->request->getGet('format') === 'csv') return $this->assetCsv($client, $assets, $fields, $values);
        $this->audit('asset_register_viewed', 'assets', $client['name']);
        return view('itsupport/client_asset_report', ['title' => 'Asset register - ' . $client['name'], 'client' => $client, 'assets' => $assets, 'fields' => $fields, 'values' => $values, 'status' => $status, 'back' => base_url('its-clients-view/' . $client['id']), 'self' => base_url('its-clients-assets-report/' . $client['id'])]);
    }
    /** Shared by the staff register and the client's own register in the portal. */
    public static function registerData($db, int $clientId, string $status, string $flag): array {
        $q = $db->table('assets')->where('client_id', $clientId)->orderBy('type')->orderBy('name');
        if (!in_array($status, ['active', 'faulty', 'under_repair', 'retired'], true)) $status = '';
        if ($status !== '') $q->where('status', $status);
        $assets = $q->get()->getResultArray();
        $fields = array_values(array_filter(CustomFields::defs('assets'), static fn($d) => (int) $d[$flag] === 1));
        return [$assets, $fields, $fields ? CustomFields::values('assets', array_column($assets, 'id')) : [], $status];
    }
    private function assetCsv(array $client, array $assets, array $fields, array $values) {
        $out = fopen('php://temp', 'w+b');
        $safe = static fn($v) => preg_match('/^\s*[=+\-@]/', (string) $v) ? "'" . $v : (string) $v;
        fputcsv($out, array_merge(['Name', 'Type', 'Brand', 'Model', 'Serial', 'Hostname', 'CPU', 'RAM', 'Storage', 'OS', 'Location', 'Status', 'Purchased', 'Warranty until'], array_column($fields, 'label')), ',', '"', '');
        foreach ($assets as $a) {
            $row = [$a['name'], $a['type'], $a['brand'], $a['model'], $a['serial_number'], $a['hostname'] ?? '', $a['cpu'] ?? '', $a['ram'] ?? '', $a['storage'] ?? '', $a['os'] ?? '', $a['location'], $a['status'], $a['purchase_date'] ?? '', $a['warranty_until'] ?? ''];
            foreach ($fields as $f) $row[] = CustomFields::display($f, $values[$a['id']][$f['field_key']] ?? '');
            fputcsv($out, array_map($safe, $row), ',', '"', '');
        }
        rewind($out); $csv = (string) stream_get_contents($out); fclose($out);
        $name = trim((string) preg_replace('/[^A-Za-z0-9]+/', '-', $client['name']), '-') ?: 'client';
        return $this->response->download('asset-register-' . $name . '-' . date('Ymd') . '.csv', $csv)->setContentType('text/csv; charset=UTF-8')->setHeader('Cache-Control', 'no-store, private');
    }
    /** Branded activity summary for a client over a date range. */
    public function report($id) {
        if ($r = $this->needLogin()) return $r;
        $client = (new ClientModel())->find((int) $id);
        if (!$client) return redirect()->to(base_url('its-clients'))->with('err', 'Client not found.');
        $from = (string) ($this->request->getGet('from') ?: date('Y-m-d', strtotime('-90 days')));
        $to = (string) ($this->request->getGet('to') ?: date('Y-m-d'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to) || $from > $to) { $from = date('Y-m-d', strtotime('-90 days')); $to = date('Y-m-d'); }
        $role = (string) $this->user()['role'];
        $tickets = $this->db->table('tickets')->where('client_id', $client['id'])->where('created_at >=', $from . ' 00:00:00')->where('created_at <=', $to . ' 23:59:59')->orderBy('created_at')->get()->getResultArray();
        $ids = array_column($tickets, 'id') ?: [0];
        $tasks = $this->db->table('tasks')->whereIn('ticket_id', $ids)->get()->getResultArray();
        $minutes = $this->db->tableExists('time_entries') ? (int) ($this->db->table('time_entries')->selectSum('minutes', 'm')->whereIn('ticket_id', $ids)->get()->getRow()->m ?? 0) : 0;
        $assetStatus = $this->db->table('assets')->select('status, COUNT(*) AS n')->where('client_id', $client['id'])->groupBy('status')->get()->getResultArray();
        $ratings = array_filter(array_map('intval', array_column($tickets, 'rating')));
        $byStatus = [];
        foreach ($tickets as $t) $byStatus[$t['status']] = ($byStatus[$t['status']] ?? 0) + 1;
        $this->audit('client_report_viewed', 'clients', $client['name'] . " $from..$to");
        return view('itsupport/client_report', [
            'title' => 'Client report - ' . $client['name'], 'client' => $client, 'from' => $from, 'to' => $to,
            'tickets' => $tickets, 'byStatus' => $byStatus, 'tasks' => $tasks, 'minutes' => $minutes, 'assetStatus' => $assetStatus,
            'avgRating' => $ratings ? round(array_sum($ratings) / count($ratings), 1) : null,
            'showCosts' => Perm::can($role, 'costs', 1),
            'cost' => array_sum(array_map(static fn($t) => (float) $t['parts_cost'] + (float) $t['labor_cost'], $tasks)),
            'cfDefs' => array_values(array_filter(CustomFields::defs('clients'), static fn($d) => (int) $d['show_in_report'] === 1)),
            'cf' => CustomFields::one('clients', (int) $client['id']),
        ]);
    }
}
