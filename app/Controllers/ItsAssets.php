<?php
namespace App\Controllers;
use App\Models\{AssetModel, ClientModel};
class ItsAssets extends ItsBase {
    public const STATUSES = ['active', 'under_repair', 'faulty', 'retired'];
    /** Filtered asset list: ?client_id=, ?status=, ?warranty=expired|expiring, ?q= */
    private function filtered(): array {
        $f = ['client_id' => (int) $this->request->getGet('client_id'), 'status' => (string) $this->request->getGet('status'), 'warranty' => (string) $this->request->getGet('warranty'), 'q' => trim((string) $this->request->getGet('q'))];
        $q = $this->db->table('assets')->select('assets.*, clients.name AS client_name')->join('clients', 'clients.id = assets.client_id', 'left')->orderBy('clients.name')->orderBy('assets.name');
        if ($f['client_id']) $q->where('assets.client_id', $f['client_id']);
        if (in_array($f['status'], self::STATUSES, true)) $q->where('assets.status', $f['status']); else $f['status'] = '';
        if ($f['warranty'] === 'expired') $q->where('assets.warranty_until <', date('Y-m-d'));
        elseif ($f['warranty'] === 'expiring') $q->where('assets.warranty_until >=', date('Y-m-d'))->where('assets.warranty_until <=', date('Y-m-d', strtotime('+60 days')));
        else $f['warranty'] = '';
        if ($f['q'] !== '') $q->groupStart()->like('assets.name', $f['q'])->orLike('assets.serial_number', $f['q'])->orLike('assets.hostname', $f['q'])->orLike('assets.model', $f['q'])->groupEnd();
        return [$q->get()->getResultArray(), $f];
    }
    private function page(?array $edit) {
        [$assets, $filters] = $this->filtered();
        $listFields = array_values(array_filter(\App\Libraries\CustomFields::defs('assets'), static fn($d) => (int) $d['show_in_list'] === 1));
        $old = old('cf');
        return view('itsupport/assets', ['title' => 'Assets', 'assets' => $assets, 'filters' => $filters, 'clients' => (new ClientModel())->orderBy('name')->findAll(), 'edit' => $edit,
            'listFields' => $listFields, 'cfValues' => $listFields ? \App\Libraries\CustomFields::values('assets', array_column($assets, 'id')) : [],
            'editValues' => is_array($old) ? $old : ($edit ? \App\Libraries\CustomFields::one('assets', (int) $edit['id']) : [])]);
    }
    public function index() {
        if ($r = $this->needLogin()) return $r;
        return $this->page(null);
    }
    public function edit($id = null) {
        if ($r = $this->needLogin()) return $r;
        $edit = (new AssetModel())->find($id);
        if (!$edit) return redirect()->to(base_url('its-assets'))->with('err', 'Asset not found.');
        return $this->page($edit);
    }
    /** CSV of the current filtered list, including list and report custom fields. */
    public function csv() {
        if ($r = $this->needLogin()) return $r;
        [$assets] = $this->filtered();
        $fields = array_values(array_filter(\App\Libraries\CustomFields::defs('assets'), static fn($d) => (int) $d['show_in_list'] === 1 || (int) $d['show_in_report'] === 1));
        $values = \App\Libraries\CustomFields::values('assets', array_column($assets, 'id'));
        $out = fopen('php://temp', 'w+b');
        $safe = static fn($v) => preg_match('/^\s*[=+\-@]/', (string) $v) ? "'" . $v : (string) $v;
        fputcsv($out, array_merge(['Client', 'Name', 'Type', 'Brand', 'Model', 'Serial', 'Hostname', 'CPU', 'RAM', 'Storage', 'OS', 'Location', 'Status', 'Purchased', 'Warranty until'], array_column($fields, 'label')), ',', '"', '');
        foreach ($assets as $a) {
            $row = [$a['client_name'], $a['name'], $a['type'], $a['brand'], $a['model'], $a['serial_number'], $a['hostname'] ?? '', $a['cpu'] ?? '', $a['ram'] ?? '', $a['storage'] ?? '', $a['os'] ?? '', $a['location'], $a['status'], $a['purchase_date'] ?? '', $a['warranty_until'] ?? ''];
            foreach ($fields as $f) $row[] = \App\Libraries\CustomFields::display($f, $values[$a['id']][$f['field_key']] ?? '');
            fputcsv($out, array_map($safe, $row), ',', '"', '');
        }
        rewind($out); $csv = (string) stream_get_contents($out); fclose($out);
        $this->audit('assets_exported', 'assets', (string) count($assets));
        return $this->response->download('assets-' . date('Ymd-His') . '.csv', $csv)->setContentType('text/csv; charset=UTF-8')->setHeader('Cache-Control', 'no-store, private');
    }
    public function save($id = null) {
        if ($r = $this->needLogin()) return $r;
        $m = new AssetModel();
        if ($id && !$m->find($id)) return redirect()->to(base_url('its-assets'))->with('err', 'Asset not found.');
        if (trim((string) $this->request->getPost('name')) === '' || !(new ClientModel())->find((int) $this->request->getPost('client_id'))) return redirect()->back()->withInput()->with('err', 'Asset name and a valid client are required.');
        [$cf, $cfErrors] = \App\Libraries\CustomFields::validate('assets', $this->request->getPost('cf'));
        if ($cfErrors) return redirect()->back()->withInput()->with('err', implode(' ', $cfErrors));
        $txt = fn(string $k, int $len) => mb_substr(trim((string) $this->request->getPost($k)), 0, $len);
        $status = (string) $this->request->getPost('status');
        $data = ['client_id' => (int) $this->request->getPost('client_id'), 'name' => $txt('name', 190), 'type' => $txt('type', 100), 'brand' => $txt('brand', 100), 'model' => $txt('model', 100), 'serial_number' => $txt('serial_number', 150), 'location' => $txt('location', 190), 'status' => in_array($status, self::STATUSES, true) ? $status : 'active', 'notes' => $txt('notes', 5000)];
        foreach (['hostname' => 100, 'cpu' => 120, 'ram' => 60, 'storage' => 120, 'os' => 80] as $k => $len) $data[$k] = mb_substr(trim((string) $this->request->getPost($k)), 0, $len) ?: null;
        foreach (['purchase_date', 'warranty_until'] as $k) { $v = (string) $this->request->getPost($k); $data[$k] = preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? $v : null; }
        if ($id) { $m->update($id, $data); $this->audit('asset_updated', 'assets', $data['name']); }
        else {
            $id = $m->insert($data); $this->audit('asset_created', 'assets', $data['name']);
            $this->db->table('asset_events')->insert(['asset_id' => $id, 'user_id' => $this->user()['id'], 'event_type' => 'new_build', 'details' => 'Registered. ' . trim(implode(', ', array_filter([$data['cpu'], $data['ram'], $data['storage'], $data['os']]))), 'created_at' => date('Y-m-d H:i:s')]);
        }
        \App\Libraries\CustomFields::save('assets', (int) $id, $cf);
        if ($this->request->getPost('save_and_new')) return redirect()->to(base_url('its-assets?client_id=' . $data['client_id']))->with('ok', 'Asset "' . $data['name'] . '" saved. Add the next one.');
        return redirect()->to(base_url('its-assets-view/' . $id))->with('ok', 'Asset saved.');
    }
    public function view($id) {
        if ($r = $this->needLogin()) return $r;
        $asset = (new AssetModel())->find($id);
        if (!$asset) return redirect()->to(base_url('its-assets'));
        $history = $this->db->table('tasks')->select('tasks.*, tickets.ticket_number')->join('tickets', 'tickets.id = tasks.ticket_id')->where('tasks.serial_number', $asset['serial_number'])->orderBy('tasks.created_at', 'DESC')->get()->getResultArray();
        $events = $this->db->table('asset_events')->select('asset_events.*, users.name AS tech, tickets.ticket_number')->join('users', 'users.id = asset_events.user_id', 'left')->join('tickets', 'tickets.id = asset_events.ticket_id', 'left')->where('asset_events.asset_id', $id)->orderBy('asset_events.id', 'DESC')->get()->getResultArray();
        $tickets = $this->db->table('tickets')->select('id, ticket_number, subject')->where('client_id', $asset['client_id'])->whereNotIn('status', ['closed'])->orderBy('id', 'DESC')->limit(50)->get()->getResultArray();
        $client = $this->db->table('clients')->select('id, name')->where('id', $asset['client_id'])->get()->getRowArray();
        return view('itsupport/asset_view', ['title' => 'Asset', 'asset' => $asset, 'client' => $client, 'history' => $history, 'events' => $events, 'tickets' => $tickets, 'cf' => \App\Libraries\CustomFields::one('assets', (int) $asset['id'])]);
    }
    public const EVENTS = ['repair' => 'Repair', 'upgrade' => 'Upgrade (RAM / SSD / other)', 'install' => 'OS / software install', 'new_build' => 'New machine set-up', 'cleaning' => 'Cleaning / maintenance', 'replacement' => 'Part replacement', 'diagnosis' => 'Diagnosis only', 'other' => 'Other'];
    public const COMPONENTS = ['RAM' => 'ram', 'SSD' => 'storage', 'HDD' => 'storage', 'Storage' => 'storage', 'CPU' => 'cpu', 'OS' => 'os', 'Battery' => null, 'Screen' => null, 'Keyboard' => null, 'Motherboard' => null, 'PSU' => null, 'Fan / thermal paste' => null, 'Other' => null];
    public function log($id) {
        if ($r = $this->needLogin()) return $r;
        $asset = (new AssetModel())->find($id);
        $type = (string) $this->request->getPost('event_type');
        if (!$asset || !isset(self::EVENTS[$type])) return redirect()->back()->with('err', 'Choose what was done.');
        $comp = (string) $this->request->getPost('component');
        if (!array_key_exists($comp, self::COMPONENTS)) $comp = '';
        $old = mb_substr(trim((string) $this->request->getPost('old_spec')), 0, 190);
        $new = mb_substr(trim((string) $this->request->getPost('new_spec')), 0, 190);
        $details = mb_substr(trim((string) $this->request->getPost('details')), 0, 3000);
        if ($details === '' && $new === '') return redirect()->back()->with('err', 'Describe the work or enter the new spec.');
        $tid = (int) $this->request->getPost('ticket_id');
        if ($tid && !$this->db->table('tickets')->where('id', $tid)->where('client_id', $asset['client_id'])->countAllResults()) $tid = 0;
        $this->db->table('asset_events')->insert(['asset_id' => $id, 'ticket_id' => $tid ?: null, 'user_id' => $this->user()['id'], 'event_type' => $type, 'component' => $comp ?: null, 'old_spec' => $old ?: null, 'new_spec' => $new ?: null, 'details' => $details, 'parts_cost' => max(0, min(9999999, (float) $this->request->getPost('parts_cost'))), 'labor_cost' => max(0, min(9999999, (float) $this->request->getPost('labor_cost'))), 'created_at' => date('Y-m-d H:i:s')]);
        $upd = [];
        $field = self::COMPONENTS[$comp] ?? null;
        if ($field && $new !== '' && in_array($type, ['upgrade', 'replacement', 'install', 'new_build'], true)) $upd[$field] = mb_substr($new, 0, 120);
        $status = (string) $this->request->getPost('status');
        if (in_array($status, ['active', 'faulty', 'under_repair', 'retired'], true) && $status !== $asset['status']) $upd['status'] = $status;
        if ($upd) (new AssetModel())->update($id, $upd);
        $this->audit('asset_service_logged', 'assets', $id . ':' . $type . ':' . $comp);
        return redirect()->to(base_url('its-assets-view/' . $id))->with('ok', 'Work logged on this machine.');
    }
}