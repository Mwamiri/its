<?php
namespace App\Controllers;
use App\Models\{TicketModel, TaskModel, TaskPhotoModel, ClientModel, DocumentTemplateModel};
class ItsTickets extends ItsBase {
    public function index() {
        if ($r = $this->needLogin()) return $r;
        return view('itsupport/tickets', ['title' => 'Tickets', 'tickets' => $this->db->table('tickets')->select('tickets.*, clients.name AS client_name')->join('clients', 'clients.id = tickets.client_id')->orderBy('tickets.created_at', 'DESC')->get()->getResultArray(), 'clients' => (new ClientModel())->orderBy('name')->findAll(), 'templates' => (new DocumentTemplateModel())->where('template_type', 'ticket')->orderBy('name')->findAll()]);
    }
    public function store() {
        if ($r = $this->needLogin()) return $r;
        if (trim((string) $this->request->getPost('subject')) === '' || !(new \App\Models\ClientModel())->find((int) $this->request->getPost('client_id'))) return redirect()->back()->with('err', 'Subject and a valid client are required.');
        $id = \App\Libraries\TicketService::create(['client_id' => $this->request->getPost('client_id'), 'subject' => $this->request->getPost('subject'), 'description' => $this->request->getPost('description'), 'priority' => $this->request->getPost('priority'), 'ticket_type' => $this->request->getPost('ticket_type'), 'visit_date' => $this->request->getPost('visit_date') ?: date('Y-m-d')]);
        $this->audit('ticket_created', 'tickets', (string) $id);
        return redirect()->to(base_url('its-tickets-view/' . $id));
    }
    public function view($id) {
        if ($r = $this->needLogin()) return $r;
        $ticket = (new TicketModel())->find($id);
        if (!$ticket) return redirect()->to(base_url('its-tickets'));
        $tasks = (new TaskModel())->where('ticket_id', $id)->orderBy('id', 'DESC')->findAll();
        $photos = [];
        foreach ($tasks as $t) $photos[$t['id']] = (new TaskPhotoModel())->where('task_id', $t['id'])->findAll();
        return view('itsupport/ticket_view', ['title' => 'Ticket', 'ticket' => $ticket, 'tasks' => $tasks, 'photos' => $photos, 'updates' => (new \App\Models\TicketUpdateModel())->where('ticket_id', $id)->orderBy('id')->findAll(), 'staff' => $this->db->table('users')->select('id,name')->where('role !=', 'client')->orderBy('name')->get()->getResultArray(), 'time' => $this->db->table('time_entries')->where('ticket_id', $id)->orderBy('id', 'DESC')->get()->getResultArray(), 'canned' => $this->db->table('canned_replies')->orderBy('title')->get()->getResultArray()]);
    }
    public function reply($id) {
        if ($r = $this->needLogin()) return $r;
        $ticket = (new TicketModel())->find($id);
        if (!$ticket) return redirect()->to(base_url('its-tickets'));
        $msg = trim((string) $this->request->getPost('message'));
        $vis = $this->request->getPost('visibility') === 'internal' ? 'internal' : 'public';
        $status = (string) $this->request->getPost('status');
        if (!$ticket || mb_strlen($msg) > 3000) return redirect()->to(base_url('its-tickets'));
        $u = $this->user();
        if ($msg !== '' && $vis === 'public') \App\Libraries\TicketService::staffResponded((int) $id);
        if ($msg !== '') (new \App\Models\TicketUpdateModel())->insert(['ticket_id' => $id, 'user_id' => $u['id'], 'author_name' => $u['name'], 'author_role' => 'staff', 'visibility' => $vis, 'message' => $msg, 'created_at' => date('Y-m-d H:i:s')]);
        if (in_array($status, ['new','in_progress','waiting_parts','waiting_approval','completed','closed'], true) && $status !== $ticket['status']) {
            \App\Libraries\TicketService::setStatus((int) $id, $status);
            $this->audit('ticket_status', 'tickets', $id . ':' . $status);
        }
        return redirect()->to(base_url('its-tickets-view/' . $id));
    }
    public function meta($id) {
        if ($r = $this->needLogin()) return $r;
        $ticket = (new TicketModel())->find($id);
        if (!$ticket) return redirect()->to(base_url('its-tickets'));
        $type = (string) $this->request->getPost('ticket_type');
        $assignee = (int) $this->request->getPost('assigned_to');
        if ($assignee && !$this->db->table('users')->where('id', $assignee)->where('role !=', 'client')->countAllResults()) $assignee = 0;
        (new TicketModel())->update($id, ['ticket_type' => in_array($type, ['incident', 'request', 'problem', 'change'], true) ? $type : 'incident', 'assigned_to' => $assignee ?: null]);
        $this->audit('ticket_meta', 'tickets', (string) $id);
        return redirect()->to(base_url('its-tickets-view/' . $id))->with('ok', 'Ticket updated.');
    }
    public function addTime($id) {
        if ($r = $this->needLogin()) return $r;
        $ticket = (new TicketModel())->find($id);
        $min = (int) $this->request->getPost('minutes');
        if (!$ticket || $min < 1 || $min > 1440) return redirect()->back()->with('err', 'Enter minutes between 1 and 1440.');
        $this->db->table('time_entries')->insert(['ticket_id' => $id, 'user_id' => $this->user()['id'], 'minutes' => $min, 'billable' => $this->request->getPost('billable') ? 1 : 0, 'note' => mb_substr(trim((string) $this->request->getPost('note')), 0, 255), 'created_at' => date('Y-m-d H:i:s')]);
        $this->audit('time_logged', 'tickets', $id . ':' . $min);
        return redirect()->to(base_url('its-tickets-view/' . $id))->with('ok', 'Time logged.');
    }
    public function addTask($id) {
        if ($r = $this->needLogin()) return $r;
        if (!(new TicketModel())->find($id)) return redirect()->to(base_url('its-tickets'));
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
        return redirect()->to(base_url('its-tickets-view/' . $id));
    }
}