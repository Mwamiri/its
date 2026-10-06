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
        $data = ['client_id' => $this->request->getPost('client_id'), 'name' => $this->request->getPost('name'), 'type' => $this->request->getPost('type'), 'brand' => $this->request->getPost('brand'), 'model' => $this->request->getPost('model'), 'serial_number' => $this->request->getPost('serial_number'), 'location' => $this->request->getPost('location'), 'status' => $this->request->getPost('status') ?: 'active', 'notes' => $this->request->getPost('notes')];
        if ($id) { $m->update($id, $data); $this->audit('asset_updated', 'assets', $data['name']); }
        else { $m->insert($data); $this->audit('asset_created', 'assets', $data['name']); }
        return redirect()->to('/its-assets');
    }
    public function view($id) {
        if ($r = $this->needLogin()) return $r;
        $asset = (new AssetModel())->find($id);
        if (!$asset) return redirect()->to('/its-assets');
        $history = $this->db->table('tasks')->select('tasks.*, tickets.ticket_number')->join('tickets', 'tickets.id = tasks.ticket_id')->where('tasks.serial_number', $asset['serial_number'])->orderBy('tasks.created_at', 'DESC')->get()->getResultArray();
        return view('itsupport/asset_view', ['title' => 'Asset', 'asset' => $asset, 'history' => $history]);
    }
}