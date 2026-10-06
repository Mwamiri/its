<?php
namespace App\Controllers;
class ItsDash extends ItsBase {
    public function index() {
        if ($r = $this->needLogin()) return $r;
        return view('itsupport/dashboard', array_merge(['title' => 'Dashboard'], $this->dashboardStats()));
    }
    public function stats() {
        if (!$this->user()) return $this->response->setStatusCode(401)->setJSON(['error' => 'Authentication required.']);
        $this->response->setHeader('Cache-Control', 'no-store, private');
        return $this->response->setJSON($this->dashboardStats());
    }
    private function dashboardStats(): array {
        $d = $this->db;
        $currentMonth = new \DateTimeImmutable('first day of this month');
        $firstMonth = $currentMonth->modify('-5 months');
        $monthCounts = [];
        $monthLabels = [];
        for ($offset = 0; $offset < 6; $offset++) {
            $month = $firstMonth->modify('+' . $offset . ' months');
            $key = $month->format('Y-m');
            $monthCounts[$key] = 0;
            $monthLabels[$key] = $month->format('M');
        }
        $monthlyRows = $d->table('tickets')
            ->select("DATE_FORMAT(created_at, '%Y-%m') AS month_key, COUNT(*) AS total", false)
            ->where('created_at >=', $firstMonth->format('Y-m-01 00:00:00'))
            ->where('created_at <', $currentMonth->modify('+1 month')->format('Y-m-01 00:00:00'))
            ->groupBy('month_key')
            ->get()
            ->getResultArray();
        foreach ($monthlyRows as $row) {
            if (array_key_exists($row['month_key'], $monthCounts)) {
                $monthCounts[$row['month_key']] = (int) $row['total'];
            }
        }
        $ticketStatuses = $d->table('tickets')
            ->select('status, COUNT(*) AS total')
            ->groupBy('status')
            ->orderBy('status')
            ->get()
            ->getResultArray();
        $costsByMonth = [];
        foreach ($monthCounts as $key => $_) {
            $costsByMonth[$key] = ['project' => 0.0, 'retainer' => 0.0];
        }
        $costRows = $d->table('tasks')
            ->select("DATE_FORMAT(created_at, '%Y-%m') AS month_key", false)
            ->select("SUM(CASE WHEN scope = 'project' THEN parts_cost + labor_cost ELSE 0 END) AS project_total", false)
            ->select("SUM(CASE WHEN scope = 'retainer' THEN parts_cost + labor_cost ELSE 0 END) AS retainer_total", false)
            ->where('created_at >=', $firstMonth->format('Y-m-01 00:00:00'))
            ->where('created_at <', $currentMonth->modify('+1 month')->format('Y-m-01 00:00:00'))
            ->groupBy('month_key')
            ->get()
            ->getResultArray();
        foreach ($costRows as $row) {
            if (array_key_exists($row['month_key'], $costsByMonth)) {
                $costsByMonth[$row['month_key']] = [
                    'project' => (float) $row['project_total'],
                    'retainer' => (float) $row['retainer_total'],
                ];
            }
        }
        $devicesByManufacturer = $d->table('network_devices')
            ->select("COALESCE(NULLIF(manufacturer, ''), 'Unspecified') AS manufacturer", false)
            ->select('COUNT(*) AS total')
            ->groupBy('manufacturer')
            ->orderBy('manufacturer')
            ->get()
            ->getResultArray();
        return [
            'clients' => $d->table('clients')->countAllResults(false),
            'assets' => $d->table('assets')->countAllResults(false),
            'openTickets' => $d->table('tickets')->whereNotIn('status', ['completed','closed'])->countAllResults(false),
            'pendingQuotes' => $d->table('quotes')->whereIn('status', ['draft','sent'])->countAllResults(false),
            'awaitingApproval' => $d->table('tasks')->where('status', 'waiting_approval')->countAllResults(false),
            'maintenanceDue' => $d->table('maintenance_schedules')->where('active', 1)->groupStart()->where('next_run <=', date('Y-m-d'))->orWhere('next_run', null)->groupEnd()->countAllResults(false),
            'devicesOnline' => $d->table('network_devices')->where('monitor_state', 'online')->countAllResults(false),
            'projectCost' => (float) ($d->table('tasks')->select('COALESCE(SUM(parts_cost+labor_cost),0) AS c')->where('scope', 'project')->get()->getRow()->c ?? 0),
            'monthLabels' => $monthLabels,
            'monthCounts' => $monthCounts,
            'ticketStatuses' => $ticketStatuses,
            'costsByMonth' => $costsByMonth,
            'devicesByManufacturer' => $devicesByManufacturer,
        ];
    }
    public function help() { if ($r = $this->needLogin()) return $r; return view('itsupport/help', ['title' => 'Help']); }
    public function features() { if ($r = $this->needLogin()) return $r; return view('itsupport/features', ['title' => 'Features']); }
}