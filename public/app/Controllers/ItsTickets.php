<?php
namespace App\Controllers;
use App\Models\{TicketModel, TaskModel, TaskPhotoModel, ClientModel};
class ItsTickets extends ItsBase {
    public function index() {
        if ($r = $this->needLogin()) return $r;
        return view('itsupport/tickets', ['title' => 'Tickets', 'tickets' => $this->db->table('tickets')->select('tickets.*, clients.name AS client_name')->join('clients', 'clients.id = tickets.client_id')->orderBy('tickets.created_at', 'DESC')->get()->getResultArray(), 'clients' => (new ClientModel())->orderBy('name')->findAll()]);
    }
    public function store() {
        if ($r = $this->needLogin()) return $r;
        $m = new TicketModel();
        $id = $m->insert(['ticket_number' => $this->seq('ticket', 'TCK-'), 'client_id' => $this->request->getPost('client_id'), 'subject' => $this->request->getPost('subject'), 'description' => $this->request->getPost('description'), 'priority' => $this->request->getPost('priority') ?: 'medium', 'status' => $this->request->getPost('status') ?: 'new', 'visit_date' => $this->request->getPost('visit_date') ?: date('Y-m-d')]);
        $this->audit('ticket_created', 'tickets', (string) $id);
        return redirect()->to('/its-tickets-view/' . $id);
    }
    public function view($id) {
        if ($r = $this->needLogin()) return $r;
        $ticket = (new TicketModel())->find($id);
        if (!$ticket) return redirect()->to('/its-tickets');
        $tasks = (new TaskModel())->where('ticket_id', $id)->orderBy('id', 'DESC')->findAll();
        $photos = [];
        foreach ($tasks as $t) $photos[$t['id']] = (new TaskPhotoModel())->where('task_id', $t['id'])->findAll();
        return view('itsupport/ticket_view', ['title' => 'Ticket', 'ticket' => $ticket, 'tasks' => $tasks, 'photos' => $photos]);
    }
    public function addTask($id) {
        if ($r = $this->needLogin()) return $r;
        $tm = new TaskModel();
        $taskId = $tm->insert(['ticket_id' => $id, 'item' => $this->request->getPost('item'), 'serial_number' => $this->request->getPost('serial_number'), 'complaint' => $this->request->getPost('complaint'), 'diagnosis' => $this->request->getPost('diagnosis'), 'action_taken' => $this->request->getPost('action_taken'), 'recommendation' => $this->request->getPost('recommendation'), 'status' => $this->request->getPost('status') ?: 'new', 'scope' => $this->request->getPost('scope') ?: 'retainer', 'parts_cost' => (float) ($this->request->getPost('parts_cost') ?? 0), 'labor_cost' => (float) ($this->request->getPost('labor_cost') ?? 0)]);
        $files = $this->request->getFileMultiple('photos');
        if ($files) foreach ($files as $i => $f) {
            if ($f->isValid() && !$f->hasMoved()) {
                $p = $this->saveUpload($f, 'photos');
                if ($p) (new TaskPhotoModel())->insert(['task_id' => $taskId, 'photo_path' => $p, 'label' => 'Photo ' . ($i + 1)]);
            }
        }
        $this->audit('task_created', 'tasks', (string) $this->request->getPost('item'));
        return redirect()->to('/its-tickets-view/' . $id);
    }
}