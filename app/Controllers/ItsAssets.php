<?php
namespace App\Controllers;
use App\Models\{AssetModel, ClientModel};
class ItsAssets extends ItsBase {
    public function index() {
        if ($r = $this->needLogin()) return $r;
        return view('itsupport/assets', ['title' => 'Assets', 'assets' => $this->db->table('assets')->select('assets.*, clients.name AS client_name')->join('clients', 'clients.id = assets.client_id')->orderBy('assets.name')->get()->getResultArray(), 'clients' => (new ClientModel())->orderBy('name')->findAll(), 'edit' => null]);
    }
    public function edit($id = null) {
        if ($r = $this->needLogin()) return $r;
        return view('itsupport/assets', ['title' => 'Assets', 'assets' => $this->db->table('assets')->select('assets.*, clients.name AS client_name')->join('clients', 'clients.id = assets.client_id')->get()->getResultArray(), 'clients' => (new ClientModel())->orderBy('name')->findAll(), 'edit' => (new AssetModel())->find($id)]);
    }
    public function save($id = null) {
        if ($r = $this->needLogin()) return $r;
        $m = new AssetModel();
        if (trim((string) $this->request->getPost('name')) === '' || !(new ClientModel())->find((int) $this->request->getPost('client_id'))) return redirect()->back()->with('err', 'Asset name and a valid client are required.');
        $data = ['client_id' => $this->request->getPost('client_id'), 'name' => $this->request->getPost('name'), 'type' => $this->request->getPost('type'), 'brand' => $this->request->getPost('brand'), 'model' => $this->request->getPost('model'), 'serial_number' => $this->request->getPost('serial_number'), 'location' => $this->request->getPost('location'), 'status' => $this->request->getPost('status') ?: 'active', 'notes' => $this->request->getPost('notes')];
        foreach (['hostname' => 100, 'cpu' => 120, 'ram' => 60, 'storage' => 120, 'os' => 80] as $k => $len) $data[$k] = mb_substr(trim((string) $this->request->getPost($k)), 0, $len) ?: null;
        foreach (['purchase_date', 'warranty_until'] as $k) { $v = (string) $this->request->getPost($k); $data[$k] = preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? $v : null; }
        if ($id) { $m->update($id, $data); $this->audit('asset_updated', 'assets', $data['name']); }
        else {
            $newId = $m->insert($data); $this->audit('asset_created', 'assets', $data['name']);
            $this->db->table('asset_events')->insert(['asset_id' => $newId, 'user_id' => $this->user()['id'], 'event_type' => 'new_build', 'details' => 'Registered. ' . trim(implode(', ', array_filter([$data['cpu'], $data['ram'], $data['storage'], $data['os']]))), 'created_at' => date('Y-m-d H:i:s')]);
        }
        return redirect()->to(base_url('its-assets'));
    }
    public function view($id) {
        if ($r = $this->needLogin()) return $r;
        $asset = (new AssetModel())->find($id);
        if (!$asset) return redirect()->to(base_url('its-assets'));
        $history = $this->db->table('tasks')->select('tasks.*, tickets.ticket_number')->join('tickets', 'tickets.id = tasks.ticket_id')->where('tasks.serial_number', $asset['serial_number'])->orderBy('tasks.created_at', 'DESC')->get()->getResultArray();
        $events = $this->db->table('asset_events')->select('asset_events.*, users.name AS tech, tickets.ticket_number')->join('users', 'users.id = asset_events.user_id', 'left')->join('tickets', 'tickets.id = asset_events.ticket_id', 'left')->where('asset_id', $id)->orderBy('asset_events.id', 'DESC')->get()->getResultArray();
        $tickets = $this->db->table('tickets')->select('id, ticket_number, subject')->where('client_id', $asset['client_id'])->whereNotIn('status', ['closed'])->orderBy('id', 'DESC')->limit(50)->get()->getResultArray();
        return view('itsupport/asset_view', ['title' => 'Asset', 'asset' => $asset, 'history' => $history, 'events' => $events, 'tickets' => $tickets]);
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