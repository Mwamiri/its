<?php
namespace App\Controllers;
use App\Models\{TicketModel, TaskModel, TaskPhotoModel, ClientModel, DocumentTemplateModel};
class ItsTickets extends ItsBase {
    public const STATUSES = ['new', 'in_progress', 'waiting_parts', 'waiting_approval', 'completed', 'closed'];
    /** Ticket list with filters: ?view=open|mine|overdue|unassigned|all, ?status=, ?client_id=, ?q= */
    public function index() {
        if ($r = $this->needLogin()) return $r;
        $f = ['view' => (string) ($this->request->getGet('view') ?: 'open'), 'status' => (string) $this->request->getGet('status'), 'client_id' => (int) $this->request->getGet('client_id'), 'q' => trim((string) $this->request->getGet('q'))];
        $q = $this->db->table('tickets')->select('tickets.*, clients.name AS client_name, users.name AS assignee')->join('clients', 'clients.id = tickets.client_id', 'left')->join('users', 'users.id = tickets.assigned_to', 'left');
        $done = ['completed', 'closed'];
        match ($f['view']) {
            'mine' => $q->where('tickets.assigned_to', (int) $this->user()['id'])->whereNotIn('tickets.status', $done),
            'overdue' => $q->whereNotIn('tickets.status', $done)->where('tickets.due_at <', date('Y-m-d H:i:s')),
            'unassigned' => $q->whereNotIn('tickets.status', $done)->where('tickets.assigned_to', null),
            'all' => $q,
            default => $q->whereNotIn('tickets.status', $done),
        };
        if (!in_array($f['view'], ['open', 'mine', 'overdue', 'unassigned', 'all'], true)) $f['view'] = 'open';
        if (in_array($f['status'], self::STATUSES, true)) $q->where('tickets.status', $f['status']); else $f['status'] = '';
        if ($f['client_id']) $q->where('tickets.client_id', $f['client_id']);
        if ($f['q'] !== '') $q->groupStart()->like('tickets.ticket_number', $f['q'])->orLike('tickets.subject', $f['q'])->orLike('clients.name', $f['q'])->groupEnd();
        $tickets = $q->orderBy('tickets.created_at', 'DESC')->limit(500)->get()->getResultArray();
        $hasAsset = $this->db->fieldExists('asset_id', 'tickets');
        return view('itsupport/tickets', ['title' => 'Tickets', 'tickets' => $tickets, 'filters' => $f,
            'clients' => (new ClientModel())->orderBy('name')->findAll(),
            'assets' => $hasAsset ? $this->db->table('assets')->select('id, client_id, name, serial_number')->where('status !=', 'retired')->orderBy('name')->get()->getResultArray() : [],
            'templates' => (new DocumentTemplateModel())->where('template_type', 'ticket')->orderBy('name')->findAll(),
            'preClient' => (int) (old('client_id') ?? $f['client_id']), 'preAsset' => (int) (old('asset_id') ?? $this->request->getGet('asset_id'))]);
    }
    public function store() {
        if ($r = $this->needLogin()) return $r;
        $subject = mb_substr(trim((string) $this->request->getPost('subject')), 0, 190);
        if ($subject === '' || !(new \App\Models\ClientModel())->find((int) $this->request->getPost('client_id'))) return redirect()->back()->withInput()->with('err', 'Subject and a valid client are required.');
        [$cf, $cfErrors] = \App\Libraries\CustomFields::validate('tickets', $this->request->getPost('cf'));
        if ($cfErrors) return redirect()->back()->withInput()->with('err', implode(' ', $cfErrors));
        $visit = (string) $this->request->getPost('visit_date');
        $id = \App\Libraries\TicketService::create(['client_id' => (int) $this->request->getPost('client_id'), 'asset_id' => (int) $this->request->getPost('asset_id'), 'subject' => $subject, 'description' => mb_substr((string) $this->request->getPost('description'), 0, 5000), 'priority' => $this->request->getPost('priority'), 'ticket_type' => $this->request->getPost('ticket_type'), 'visit_date' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $visit) ? $visit : date('Y-m-d')]);
        \App\Libraries\CustomFields::save('tickets', $id, $cf);
        $assignee = (int) $this->request->getPost('assigned_to');
        if ($assignee && $this->db->table('users')->where('id', $assignee)->where('role !=', 'client')->where('active', 1)->countAllResults()) (new TicketModel())->update($id, ['assigned_to' => $assignee]);
        $this->audit('ticket_created', 'tickets', (string) $id);
        return redirect()->to(base_url('its-tickets-view/' . $id))->with('ok', 'Ticket created.');
    }
    public function view($id) {
        if ($r = $this->needLogin()) return $r;
        $ticket = (new TicketModel())->find($id);
        if (!$ticket) return redirect()->to(base_url('its-tickets'));
        $tasks = (new TaskModel())->where('ticket_id', $id)->orderBy('id', 'DESC')->findAll();
        $photos = [];
        foreach ($tasks as $t) $photos[$t['id']] = (new TaskPhotoModel())->where('task_id', $t['id'])->findAll();
        $client = $this->db->table('clients')->select('id, name, phone, email')->where('id', $ticket['client_id'])->get()->getRowArray();
        $asset = !empty($ticket['asset_id']) ? $this->db->table('assets')->select('id, name, serial_number, status')->where('id', (int) $ticket['asset_id'])->get()->getRowArray() : null;
        $clientAssets = $this->db->fieldExists('asset_id', 'tickets') ? $this->db->table('assets')->select('id, name, serial_number')->where('client_id', $ticket['client_id'])->orderBy('name')->get()->getResultArray() : [];
        return view('itsupport/ticket_view', ['title' => 'Ticket', 'ticket' => $ticket, 'client' => $client, 'asset' => $asset, 'clientAssets' => $clientAssets, 'cf' => \App\Libraries\CustomFields::one('tickets', (int) $ticket['id']), 'tasks' => $tasks, 'photos' => $photos, 'updates' => (new \App\Models\TicketUpdateModel())->where('ticket_id', $id)->orderBy('id')->findAll(), 'staff' => $this->db->table('users')->select('id,name')->where('role !=', 'client')->orderBy('name')->get()->getResultArray(), 'time' => $this->db->table('time_entries')->where('ticket_id', $id)->orderBy('id', 'DESC')->get()->getResultArray(), 'canned' => $this->db->table('canned_replies')->orderBy('title')->get()->getResultArray()]);
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
        [$cf, $cfErrors] = \App\Libraries\CustomFields::validate('tickets', $this->request->getPost('cf'));
        if ($cfErrors) return redirect()->to(base_url('its-tickets-view/' . $id))->with('err', implode(' ', $cfErrors));
        $data = ['ticket_type' => in_array($type, ['incident', 'request', 'problem', 'change'], true) ? $type : 'incident', 'assigned_to' => $assignee ?: null];
        if ($this->db->fieldExists('asset_id', 'tickets') && $this->request->getPost('asset_id') !== null) {
            $aid = (int) $this->request->getPost('asset_id');
            $data['asset_id'] = $aid && $this->db->table('assets')->where('id', $aid)->where('client_id', $ticket['client_id'])->countAllResults() ? $aid : null;
        }
        (new TicketModel())->update($id, $data);
        \App\Libraries\CustomFields::save('tickets', (int) $id, $cf);
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