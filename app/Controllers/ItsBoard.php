<?php
namespace App\Controllers;
use App\Models\TicketModel;

class ItsBoard extends ItsBase {
    public const STATUSES = ['new' => 'New', 'in_progress' => 'In progress', 'waiting_parts' => 'Waiting parts', 'waiting_approval' => 'Waiting approval', 'completed' => 'Completed', 'closed' => 'Closed'];

    public function index() {
        if ($r = $this->needLogin()) return $r;
        $rows = $this->db->table('tickets')->select('tickets.id, tickets.ticket_number, tickets.subject, tickets.priority, tickets.status, clients.name AS client_name')
            ->join('clients', 'clients.id = tickets.client_id')->orderBy('tickets.created_at', 'DESC')->limit(500)->get()->getResultArray();
        $cols = array_fill_keys(array_keys(self::STATUSES), []);
        foreach ($rows as $t) $cols[isset($cols[$t['status']]) ? $t['status'] : 'new'][] = $t;
        return view('itsupport/board', ['title' => 'Board', 'statuses' => self::STATUSES, 'cols' => $cols]);
    }

    public function move() {
        if ($r = $this->needLogin()) return $this->response->setStatusCode(401)->setJSON(['ok' => false, 'error' => 'Not signed in', 'csrf' => csrf_hash()]);
        $id = (int) $this->request->getPost('id');
        $status = (string) $this->request->getPost('status');
        $ticket = (new TicketModel())->find($id);
        if (!$ticket || !isset(self::STATUSES[$status])) return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'error' => 'Invalid ticket or status', 'csrf' => csrf_hash()]);
        if ($ticket['status'] !== $status) {
            \App\Libraries\TicketService::setStatus($id, $status);
            $this->audit('ticket_status', 'tickets', $id . ':' . $status . ' (board)');
        }
        return $this->response->setJSON(['ok' => true, 'csrf' => csrf_hash()]);
    }
}