<?php
namespace App\Controllers;
use App\Models\{TicketModel, TaskModel, TicketUpdateModel};
class ItsPortal extends ItsBase {
    private const OPEN = ['new', 'in_progress', 'waiting_parts', 'waiting_approval'];
    private const DONE = ['completed', 'closed'];

    private function own(int $id): ?array {
        $t = (new TicketModel())->where('id', $id)->where('client_id', (int) $this->user()['client_id'])->first();
        return $t ?: null;
    }
    public function index() {
        if ($r = $this->needClient()) return $r;
        $cid = (int) $this->user()['client_id'];
        $tickets = (new TicketModel())->where('client_id', $cid)->orderBy('created_at', 'DESC')->findAll();
        $open = count(array_filter($tickets, fn($t) => in_array($t['status'], self::OPEN, true)));
        $done = count(array_filter($tickets, fn($t) => in_array($t['status'], self::DONE, true)));
        $client = $this->db->table('clients')->where('id', $cid)->get()->getRowArray();
        return view('itsupport/portal', ['title' => 'My Support', 'tickets' => $tickets, 'open' => $open, 'done' => $done, 'total' => count($tickets), 'client' => $client]);
    }
    public function create() {
        if ($r = $this->needClient()) return $r;
        if ($this->request->getMethod() === 'POST') {
            $subject = trim((string) $this->request->getPost('subject'));
            $desc = trim((string) $this->request->getPost('description'));
            $priority = (string) $this->request->getPost('priority');
            if (!in_array($priority, ['low', 'medium', 'high'], true)) $priority = 'medium';
            if ($subject === '' || mb_strlen($subject) > 190 || $desc === '' || mb_strlen($desc) > 5000) {
                return redirect()->back()->withInput()->with('err', 'Enter a subject (max 190 characters) and a description (max 5000).');
            }
            $id = \App\Libraries\TicketService::create(['client_id' => (int) $this->user()['client_id'], 'subject' => $subject, 'description' => $desc, 'priority' => $priority, 'ticket_type' => 'request']);
            $this->audit('portal_ticket_created', 'tickets', (string) $id);
            return redirect()->to(base_url('its-portal-ticket/' . $id))->with('ok', 'Your request was submitted. We will follow up here.');
        }
        return view('itsupport/portal_new', ['title' => 'Report an Issue']);
    }
    public function view($id) {
        if ($r = $this->needClient()) return $r;
        $ticket = $this->own((int) $id);
        if (!$ticket) return redirect()->to(base_url('its-portal'))->with('err', 'Ticket not found.');
        $updates = (new TicketUpdateModel())->where('ticket_id', $ticket['id'])->where('visibility', 'public')->orderBy('id', 'ASC')->findAll();
        $tasks = (new TaskModel())->select('item, status, action_taken')->where('ticket_id', $ticket['id'])->orderBy('id')->findAll();
        return view('itsupport/portal_ticket', ['title' => 'Ticket', 'ticket' => $ticket, 'updates' => $updates, 'tasks' => $tasks]);
    }
    public function rate($id) {
        if ($r = $this->needClient()) return $r;
        $ticket = $this->own((int) $id);
        $stars = (int) $this->request->getPost('rating');
        if (!$ticket || !in_array($ticket['status'], self::DONE, true) || $stars < 1 || $stars > 5) return redirect()->back()->with('err', 'You can rate a ticket once it is resolved.');
        (new TicketModel())->update($ticket['id'], ['rating' => $stars, 'rating_comment' => mb_substr(trim((string) $this->request->getPost('comment')), 0, 500)]);
        $this->audit('portal_rating', 'tickets', $ticket['id'] . ':' . $stars);
        return redirect()->to(base_url('its-portal-ticket/' . $ticket['id']))->with('ok', 'Thank you for your feedback.');
    }
    public function reply($id) {
        if ($r = $this->needClient()) return $r;
        $ticket = $this->own((int) $id);
        $msg = trim((string) $this->request->getPost('message'));
        if (!$ticket || $msg === '' || mb_strlen($msg) > 3000) return redirect()->back()->with('err', 'Enter a message up to 3000 characters.');
        $u = $this->user();
        (new TicketUpdateModel())->insert(['ticket_id' => $ticket['id'], 'user_id' => $u['id'], 'author_name' => $u['name'], 'author_role' => 'client', 'visibility' => 'public', 'message' => $msg, 'created_at' => date('Y-m-d H:i:s')]);
        if ($ticket['status'] === 'completed') \App\Libraries\TicketService::setStatus((int) $ticket['id'], 'in_progress');
        $this->audit('portal_reply', 'tickets', (string) $ticket['id']);
        return redirect()->to(base_url('its-portal-ticket/' . $ticket['id']))->with('ok', 'Reply sent.');
    }
}
