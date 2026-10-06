<?php
namespace App\Controllers;
class ItsDash extends ItsBase {
    public function index() {
        if ($r = $this->needLogin()) return $r;
        $d = $this->db;
        return view('itsupport/dashboard', [
            'title' => 'Dashboard',
            'clients' => $d->table('clients')->countAllResults(false),
            'assets' => $d->table('assets')->countAllResults(false),
            'openTickets' => $d->table('tickets')->whereNotIn('status', ['completed','closed'])->countAllResults(false),
            'pendingQuotes' => $d->table('quotes')->whereIn('status', ['draft','sent'])->countAllResults(false),
            'awaitingApproval' => $d->table('tasks')->where('status', 'waiting_approval')->countAllResults(false),
            'projectCost' => (float) ($d->table('tasks')->select('COALESCE(SUM(parts_cost+labor_cost),0) AS c')->where('scope', 'project')->get()->getRow()->c ?? 0),
        ]);
    }
    public function help() { if ($r = $this->needLogin()) return $r; return view('itsupport/help', ['title' => 'Help']); }
    public function features() { if ($r = $this->needLogin()) return $r; return view('itsupport/features', ['title' => 'Features']); }
}