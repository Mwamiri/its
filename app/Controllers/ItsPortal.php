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
    /** Client dashboard: what needs attention, quotes to decide, equipment, visits and recent replies. */
    public function index() {
        if ($r = $this->needClient()) return $r;
        $cid = (int) $this->user()['client_id'];
        $tickets = (new TicketModel())->where('client_id', $cid)->orderBy('created_at', 'DESC')->findAll();
        $openTickets = array_values(array_filter($tickets, fn($t) => !in_array($t['status'], self::DONE, true)));
        $doneTickets = array_filter($tickets, fn($t) => in_array($t['status'], self::DONE, true));
        $ids = array_column($tickets, 'id') ?: [0];
        // The latest public message on each ticket tells us whose turn it is to reply.
        $last = [];
        foreach ($this->db->table('ticket_updates')->select('ticket_id, author_role, created_at')->whereIn('ticket_id', $ids)->where('visibility', 'public')->orderBy('id')->get()->getResultArray() as $u) $last[$u['ticket_id']] = $u;
        $attention = [];
        foreach ($tickets as $t) {
            $reason = null;
            if (!in_array($t['status'], self::DONE, true) && isset($last[$t['id']]) && $last[$t['id']]['author_role'] !== 'client') $reason = 'Support replied';
            if ($t['status'] === 'waiting_approval') $reason = 'Waiting for your approval';
            if (in_array($t['status'], self::DONE, true) && empty($t['rating']) && strtotime((string) ($t['resolved_at'] ?: $t['created_at'])) > strtotime('-30 days')) $reason = 'Rate this fix';
            if ($reason) $attention[] = ['ticket' => $t, 'reason' => $reason];
        }
        $quotes = $this->db->table('quotes')->where('client_id', $cid)->where('status', 'sent')->orderBy('created_at', 'DESC')->get()->getResultArray();
        foreach ($quotes as &$q) $q['link'] = \App\Libraries\QuoteLink::url($q);
        unset($q);
        $assets = $this->db->table('assets')->where('client_id', $cid)->where('status !=', 'retired')->orderBy('name')->get()->getResultArray();
        $ratings = array_filter(array_map('intval', array_column($tickets, 'rating')));
        $forms = $this->db->fieldExists('audience', 'custom_forms') ? $this->db->table('custom_forms')->select('id, title, description')->where('active', 1)->whereIn('audience', ['clients', 'both'])->orderBy('title')->get()->getResultArray() : [];
        return view('itsupport/portal', [
            'title' => 'My Support', 'client' => $this->db->table('clients')->where('id', $cid)->get()->getRowArray(),
            'tickets' => $tickets, 'openTickets' => $openTickets, 'attention' => array_slice($attention, 0, 8), 'quotes' => $quotes, 'assets' => $assets, 'forms' => $forms,
            'resolvedMonth' => count(array_filter($doneTickets, fn($t) => substr((string) ($t['resolved_at'] ?: $t['created_at']), 0, 7) === date('Y-m'))),
            'avgRating' => $ratings ? round(array_sum($ratings) / count($ratings), 1) : null,
            'visits' => array_values(array_filter($openTickets, fn($t) => !empty($t['visit_date']) && $t['visit_date'] >= date('Y-m-d'))),
            'recent' => $this->db->table('ticket_updates')->select('ticket_updates.*, tickets.ticket_number, tickets.subject')->join('tickets', 'tickets.id = ticket_updates.ticket_id')->where('tickets.client_id', $cid)->where('ticket_updates.visibility', 'public')->where('ticket_updates.author_role !=', 'client')->orderBy('ticket_updates.id', 'DESC')->limit(5)->get()->getResultArray(),
        ]);
    }
    public function create() {
        if ($r = $this->needClient()) return $r;
        $cid = (int) $this->user()['client_id'];
        if ($this->request->getMethod() === 'POST') {
            $subject = trim((string) $this->request->getPost('subject'));
            $desc = trim((string) $this->request->getPost('description'));
            $priority = (string) $this->request->getPost('priority');
            if (!in_array($priority, ['low', 'medium', 'high'], true)) $priority = 'medium';
            if ($subject === '' || mb_strlen($subject) > 190 || $desc === '' || mb_strlen($desc) > 5000) {
                return redirect()->back()->withInput()->with('err', 'Enter a subject (max 190 characters) and a description (max 5000).');
            }
            // TicketService only links the asset if it belongs to this client.
            $id = \App\Libraries\TicketService::create(['client_id' => $cid, 'asset_id' => (int) $this->request->getPost('asset_id'), 'subject' => $subject, 'description' => $desc, 'priority' => $priority, 'ticket_type' => 'request']);
            $this->audit('portal_ticket_created', 'tickets', (string) $id);
            return redirect()->to(base_url('its-portal-ticket/' . $id))->with('ok', 'Your request was submitted. We will follow up here.');
        }
        $assets = $this->db->fieldExists('asset_id', 'tickets') ? $this->db->table('assets')->select('id, name, serial_number, location')->where('client_id', $cid)->where('status !=', 'retired')->orderBy('name')->get()->getResultArray() : [];
        return view('itsupport/portal_new', ['title' => 'Report an Issue', 'assets' => $assets, 'preAsset' => (int) $this->request->getGet('asset_id')]);
    }
    /** The client's own equipment, with fields the admin marked as visible in the portal. */
    public function assets() {
        if ($r = $this->needClient()) return $r;
        $cid = (int) $this->user()['client_id'];
        [$assets, $fields, $values] = ItsClients::registerData($this->db, $cid, (string) $this->request->getGet('status'), 'show_in_portal');
        $openByAsset = [];
        if ($this->db->fieldExists('asset_id', 'tickets')) foreach ($this->db->table('tickets')->select('asset_id, COUNT(*) AS n')->where('client_id', $cid)->whereNotIn('status', self::DONE)->where('asset_id IS NOT NULL', null, false)->groupBy('asset_id')->get()->getResultArray() as $row) $openByAsset[(int) $row['asset_id']] = (int) $row['n'];
        return view('itsupport/portal_assets', ['title' => 'My Equipment', 'assets' => $assets, 'fields' => $fields, 'values' => $values, 'openByAsset' => $openByAsset]);
    }
    /** Branded, printable register of the client's own equipment. */
    public function assetReport() {
        if ($r = $this->needClient()) return $r;
        $cid = (int) $this->user()['client_id'];
        [$assets, $fields, $values, $status] = ItsClients::registerData($this->db, $cid, (string) $this->request->getGet('status'), 'show_in_portal');
        return view('itsupport/client_asset_report', ['title' => 'Asset register', 'client' => $this->db->table('clients')->where('id', $cid)->get()->getRowArray(), 'assets' => $assets, 'fields' => $fields, 'values' => $values, 'status' => $status, 'back' => base_url('its-portal-assets'), 'self' => base_url('its-portal-assets-report'), 'noCsv' => true]);
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
