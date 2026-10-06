<?php
namespace App\Controllers;
use App\Models\ClientModel;
class ItsClients extends ItsBase {
    public function index() {
        if ($r = $this->needLogin()) return $r;
        return view('itsupport/clients', ['title' => 'Clients', 'clients' => (new ClientModel())->orderBy('name')->findAll(), 'edit' => null]);
    }
    public function edit($id = null) {
        if ($r = $this->needLogin()) return $r;
        return view('itsupport/clients', ['title' => 'Clients', 'clients' => (new ClientModel())->orderBy('name')->findAll(), 'edit' => (new ClientModel())->find($id)]);
    }
    public function save($id = null) {
        if ($r = $this->needLogin()) return $r;
        $m = new ClientModel();
        $data = ['name' => $this->request->getPost('name'), 'contact_person' => $this->request->getPost('contact_person'), 'phone' => $this->request->getPost('phone'), 'email' => $this->request->getPost('email'), 'address' => $this->request->getPost('address'), 'notes' => $this->request->getPost('notes')];
        $file = $this->request->getFile('logo');
        if ($file && $file->isValid() && !$file->hasMoved()) $data['logo_path'] = $this->saveUpload($file, 'clients');
        if ($id) { $m->update($id, $data); $this->audit('client_updated', 'clients', $data['name']); }
        else { $m->insert($data); $this->audit('client_created', 'clients', $data['name']); }
        return redirect()->to('/its-clients');
    }
}