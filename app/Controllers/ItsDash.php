<?php
namespace App\Controllers;
class ItsDash extends ItsBase {
    public const WIDGETS = ['kpis' => 'Key figures', 'mywork' => 'My work & due soon', 'activity' => 'Recent activity', 'charts' => 'Ticket charts', 'costs' => 'Cost chart', 'network' => 'Network devices', 'links' => 'Workspace links'];
    public const PINS = [
        'tickets'       => ['label' => 'Tickets',       'url' => 'its-tickets',              'icon' => 'ticket-detailed'],
        'clients'       => ['label' => 'Clients',        'url' => 'its-clients',              'icon' => 'people'],
        'assets'        => ['label' => 'Assets',         'url' => 'its-assets',               'icon' => 'hdd'],
        'quotes'        => ['label' => 'Quotes',         'url' => 'its-quotes',               'icon' => 'file-text'],
        'network'       => ['label' => 'Network',        'url' => 'its-network',              'icon' => 'wifi'],
        'reports'       => ['label' => 'Reports',        'url' => 'its-reports',              'icon' => 'bar-chart'],
        'extensions'    => ['label' => 'Extensions',     'url' => 'its-extensions',           'icon' => 'plugin'],
        'subscriptions' => ['label' => 'Subscriptions',  'url' => 'its-ext-subscriptions',    'icon' => 'key'],
        'signins'       => ['label' => 'Sign-ins',       'url' => 'its-signins',              'icon' => 'person-lock'],
        'logs'          => ['label' => 'Error Logs',     'url' => 'its-logs',                 'icon' => 'bug'],
        'updates'       => ['label' => 'Updates',        'url' => 'its-updates',              'icon' => 'arrow-up-circle'],
        'site'          => ['label' => 'Website',        'url' => 'its-site',                 'icon' => 'globe2'],
        'maintenance'   => ['label' => 'Maintenance',    'url' => 'its-admin?tab=maintenance','icon' => 'tools'],
    ];
    private function canCosts(): bool { return \App\Libraries\Perm::can((string) $this->user()['role'], 'costs', 1); }
    private function hiddenWidgets(): array {
        $h = json_decode((string) \App\Models\SettingModel::get('dash_hidden_' . (int) $this->user()['id'], '[]'), true);
        return is_array($h) ? array_values(array_intersect($h, array_keys(self::WIDGETS))) : [];
    }
    private function pinnedItems(): array {
        $saved = json_decode((string) \App\Models\SettingModel::get('dash_pins_' . (int) $this->user()['id'], '[]'), true);
        if (!is_array($saved)) return [];
        $out = [];
        foreach ($saved as $key) {
            if (isset(self::PINS[$key])) $out[$key] = self::PINS[$key];
        }
        return $out;
    }
    private function alertNotices(): array {
        $notices = [];
        $d = $this->db;
        $u = $this->user();
        $isAdmin = ($u['role'] ?? '') === 'admin';
        // Failed logins last 24h — only show to admin
        if ($isAdmin && $d->tableExists('login_history')) {
            $n = $d->table('login_history')->where('success', 0)->where('created_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')))->countAllResults();
            if ($n > 0) $notices[] = ['type' => 'warn', 'icon' => 'shield-exclamation', 'msg' => "$n failed login attempt" . ($n !== 1 ? 's' : '') . ' in the last 24 hours.', 'link' => base_url('its-signins'), 'cta' => 'View sign-ins'];
        }
        // SMTP selected but not configured
        if ($isAdmin && \App\Models\SettingModel::get('mail_method', 'mail') === 'smtp' && !\App\Models\SettingModel::get('smtp_host', '')) {
            $notices[] = ['type' => 'warn', 'icon' => 'envelope-exclamation', 'msg' => 'SMTP is selected but the host is not configured — outgoing emails will fail.', 'link' => base_url('its-admin?tab=mail'), 'cta' => 'Configure SMTP'];
        }
        // Last mail log entry was a failure
        if ($isAdmin && $d->tableExists('mail_log')) {
            $last = $d->table('mail_log')->orderBy('id', 'DESC')->limit(1)->get()->getRowArray();
            if ($last && $last['status'] === 'failed') $notices[] = ['type' => 'err', 'icon' => 'envelope-x', 'msg' => 'Last email delivery failed: ' . mb_strimwidth($last['subject'] ?? '(no subject)', 0, 50, '…'), 'link' => base_url('its-admin?tab=maillog'), 'cta' => 'Mail log'];
        }
        // Pending extension subscriptions
        if ($isAdmin && $d->tableExists('ext_subscriptions')) {
            $n = $d->table('ext_subscriptions')->where('status', 'pending')->countAllResults();
            if ($n > 0) $notices[] = ['type' => 'info', 'icon' => 'key', 'msg' => "$n extension subscription" . ($n !== 1 ? 's' : '') . ' awaiting approval.', 'link' => base_url('its-ext-subscriptions'), 'cta' => 'Review'];
        }
        // Disk space < 15%
        if ($isAdmin) {
            $free = @disk_free_space(WRITEPATH); $total = @disk_total_space(WRITEPATH);
            if ($total > 0 && $free / $total < 0.15) {
                $pct = round($free / $total * 100);
                $notices[] = ['type' => 'err', 'icon' => 'hdd', 'msg' => "Disk space critical: only {$pct}% free (" . round($free / 1073741824, 1) . ' GB remaining).', 'link' => base_url('its-health'), 'cta' => 'View health'];
            }
        }
        // Required tables missing
        if ($isAdmin) {
            $required = ['login_history', 'extensions', 'ext_subscriptions', 'mail_log'];
            $missing = array_values(array_filter($required, fn($t) => !$d->tableExists($t)));
            if ($missing) $notices[] = ['type' => 'err', 'icon' => 'database-exclamation', 'msg' => 'Missing database tables: ' . implode(', ', $missing) . '. Run migrations.', 'link' => base_url('its-updates'), 'cta' => 'Updates'];
        }
        return $notices;
    }
    public function index() {
        if ($r = $this->needLogin()) return $r;
        $u = $this->user();
        if (!\App\Libraries\Perm::can($u['role'], 'dashboard', 1)) return redirect()->to(base_url('its-help'))->with('err', 'Your role does not have access to the dashboard.');
        $now = date('Y-m-d H:i:s');
        $done = ['completed', 'closed'];
        $canTickets = \App\Libraries\Perm::can($u['role'], 'tickets', 1);
        $myWork = $canTickets ? $this->db->table('tickets')->select('tickets.id, ticket_number, subject, priority, status, due_at, clients.name AS client_name')->join('clients', 'clients.id = tickets.client_id', 'left')->where('assigned_to', (int) $u['id'])->whereNotIn('status', $done)->orderBy('due_at IS NULL', '', false)->orderBy('due_at', 'ASC')->limit(8)->get()->getResultArray() : [];
        $dueSoon = $canTickets ? $this->db->table('tickets')->select('tickets.id, ticket_number, subject, due_at, assigned_to, clients.name AS client_name')->join('clients', 'clients.id = tickets.client_id', 'left')->whereNotIn('status', $done)->where('due_at IS NOT NULL', null, false)->where('due_at <=', date('Y-m-d H:i:s', strtotime('+24 hours')))->orderBy('due_at', 'ASC')->limit(8)->get()->getResultArray() : [];
        $activity = $this->db->table('audit_log')->select('audit_log.action, audit_log.module, audit_log.details, audit_log.created_at, users.name AS user_name')->join('users', 'users.id = audit_log.user_id', 'left')->whereNotIn('audit_log.action', ['login', 'logout'])->orderBy('audit_log.id', 'DESC')->limit(8)->get()->getResultArray();
        return view('itsupport/dashboard', array_merge(['title' => 'Dashboard', 'myWork' => $myWork, 'dueSoon' => $dueSoon, 'now' => $now, 'activity' => $activity, 'hidden' => $this->hiddenWidgets(), 'canCosts' => $this->canCosts(), 'notices' => $this->alertNotices(), 'pins' => $this->pinnedItems(), 'pinItems' => self::PINS], $this->dashboardStats()));
    }
    public function stats() {
        $u = $this->user();
        if (!$u) return $this->response->setStatusCode(401)->setJSON(['error' => 'Authentication required.']);
        if ($u['role'] === 'client' || !\App\Libraries\Perm::can($u['role'], 'dashboard', 1)) return $this->response->setStatusCode(403)->setJSON(['error' => 'Not permitted.']);
        $this->response->setHeader('Cache-Control', 'no-store, private');
        $stats = $this->dashboardStats();
        if (!$this->canCosts()) unset($stats['projectCost'], $stats['costsByMonth']);
        return $this->response->setJSON($stats);
    }
    /** Per-user show/hide of dashboard sections. */
    public function widgets() {
        if ($r = $this->needLogin()) return $r;
        $show = (array) ($this->request->getPost('show') ?? []);
        $hidden = array_values(array_diff(array_keys(self::WIDGETS), $show));
        \App\Models\SettingModel::put('dash_hidden_' . (int) $this->user()['id'], json_encode($hidden));
        $showPins = array_values(array_intersect((array) ($this->request->getPost('pins') ?? []), array_keys(self::PINS)));
        \App\Models\SettingModel::put('dash_pins_' . (int) $this->user()['id'], json_encode($showPins));
        return redirect()->to(base_url('its-dashboard'))->with('ok', 'Dashboard layout saved.');
    }
    private function dashboardStats(): array {
        try { return $this->_dashboardStats(); }
        catch (\Throwable $e) {
            log_message('error', 'Dashboard stats failed: {m}', ['m' => $e->getMessage()]);
            return ['clients' => '?', 'assets' => '?', 'openTickets' => '?', 'pendingQuotes' => '?', 'awaitingApproval' => '?', 'maintenanceDue' => '?', 'devicesOnline' => '?', 'projectCost' => 0.0, 'monthLabels' => [], 'monthCounts' => [], 'ticketStatuses' => [], 'costsByMonth' => [], 'devicesByManufacturer' => []];
        }
    }
    private function _dashboardStats(): array {
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