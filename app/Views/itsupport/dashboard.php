<?= view('itsupport/_header') ?>
<section class="dashboard-intro">
    <div><p class="eyebrow">OVERVIEW</p><h2>Welcome back, <?= esc(session('its_user')['name'] ?? 'Team') ?></h2><p class="muted">A quick view of your support operations.</p></div>
    <div class="quick-actions">
        <a class="button" href="<?= base_url('its-tickets') ?>">New ticket</a>
        <a class="button secondary" href="<?= base_url('its-clients') ?>">Add client</a>
    </div>
</section>
<p class="muted live-refresh" data-refresh-status role="status" aria-live="polite">Dashboard data refreshes automatically every minute.</p>
<section class="grid dashboard-metrics" aria-label="Support overview" data-dashboard-stats="<?= esc(base_url('its-dashboard-stats')) ?>">
    <a class="card metric" href="<?= base_url('its-clients') ?>"><span class="metric-label">Clients</span><strong data-stat="clients"><?= $clients ?></strong><span class="metric-link">View clients</span></a>
    <a class="card metric" href="<?= base_url('its-assets') ?>"><span class="metric-label">Assets</span><strong data-stat="assets"><?= $assets ?></strong><span class="metric-link">View assets</span></a>
    <a class="card metric" href="<?= base_url('its-tickets') ?>"><span class="metric-label">Open tickets</span><strong data-stat="openTickets"><?= $openTickets ?></strong><span class="metric-link">Manage tickets</span></a>
    <a class="card metric" href="<?= base_url('its-quotes') ?>"><span class="metric-label">Pending quotes</span><strong data-stat="pendingQuotes"><?= $pendingQuotes ?></strong><span class="metric-link">View quotes</span></a>
    <a class="card metric" href="<?= base_url('its-tickets') ?>"><span class="metric-label">Awaiting approval</span><strong data-stat="awaitingApproval"><?= $awaitingApproval ?></strong><span class="metric-link">Review tasks</span></a>
    <a class="card metric" href="<?= base_url('its-network') ?>"><span class="metric-label">Devices online</span><strong data-stat="devicesOnline"><?= $devicesOnline ?></strong><span class="metric-link">View network</span></a>
    <div class="card metric"><span class="metric-label">Maintenance due</span><strong data-stat="maintenanceDue"><?= $maintenanceDue ?></strong><span class="metric-link">Active schedules</span></div>
    <div class="card metric"><span class="metric-label">Project cost</span><strong data-stat="projectCost"><?= number_format($projectCost, 2) ?></strong><span class="metric-link">Current total</span></div>
</section>
<?php
$monthlyMax = max(1, max($monthCounts));
$statusTotal = array_sum(array_column($ticketStatuses, 'total'));
?>
<section class="grid grid-2 dashboard-charts" aria-label="Ticket statistics">
    <article class="card chart-card">
        <div class="chart-heading"><div><h3>Ticket volume</h3><p class="muted">Created over the last six months</p></div><span class="badge blue" data-month-total><?= array_sum($monthCounts) ?> total</span></div>
        <div class="month-chart" role="img" aria-label="Tickets created each month for the last six months">
            <?php foreach ($monthLabels as $monthKey => $monthLabel) { ?>
                <?php $count = $monthCounts[$monthKey]; $barHeight = $count > 0 ? max(5, (int) round($count / $monthlyMax * 100)) : 0; ?>
                <div class="month-column" data-month-key="<?= esc($monthKey) ?>" aria-label="<?= esc($monthLabel) ?>: <?= $count ?> tickets">
                    <span class="month-count" data-month-count><?= $count ?></span>
                    <div class="month-track"><span class="month-bar" data-month-bar style="height:<?= $barHeight ?>%"></span></div>
                    <span class="month-label"><?= esc($monthLabel) ?></span>
                </div>
            <?php } ?>
        </div>
    </article>
    <article class="card chart-card">
        <div class="chart-heading"><div><h3>Tickets by status</h3><p class="muted">Current workload breakdown</p></div><span class="badge gray" data-status-total><?= $statusTotal ?> total</span></div>
            <div class="status-chart" role="img" aria-label="Tickets grouped by current status" data-live-statuses>
                <?php if ($ticketStatuses) { ?>
                <?php foreach ($ticketStatuses as $ticketStatus) { $share = $statusTotal > 0 ? (int) round($ticketStatus['total'] / $statusTotal * 100) : 0; ?>
                    <div class="status-row" aria-label="<?= esc(ucfirst(str_replace('_', ' ', $ticketStatus['status']))) ?>: <?= (int) $ticketStatus['total'] ?> tickets">
                        <div class="status-label"><span><?= esc(ucfirst(str_replace('_', ' ', $ticketStatus['status']))) ?></span><strong><?= (int) $ticketStatus['total'] ?></strong></div>
                        <div class="status-track"><span class="status-bar" style="width:<?= $share ?>%"></span></div>
                    </div>
                <?php } ?>
                <?php } else { ?><p class="chart-empty">No tickets yet. Create a ticket to see its status here.</p><?php } ?>
            </div>
    </article>
</section>
<section class="grid grid-2 dashboard-charts" aria-label="Cost and network statistics">
    <article class="card chart-card">
        <div class="chart-heading"><div><h3>Monthly service costs</h3><p class="muted">Project and retainer costs by task creation date</p></div></div>
        <div class="cost-chart" role="img" aria-label="Monthly project and retainer costs for the last six months" data-cost-chart>
            <?php $costMax = 0.0; foreach ($costsByMonth as $cost) { $costMax = max($costMax, $cost['project'] + $cost['retainer']); } $costMax = max(1.0, $costMax); ?>
            <?php foreach ($monthLabels as $monthKey => $monthLabel) { $cost = $costsByMonth[$monthKey]; $total = $cost['project'] + $cost['retainer']; ?>
                <div class="cost-column" data-cost-month="<?= esc($monthKey) ?>" aria-label="<?= esc($monthLabel) ?>: <?= number_format($total, 2) ?>">
                    <span class="cost-total" data-cost-total><?= number_format($total, 2) ?></span>
                    <div class="cost-track">
                        <span class="cost-project" data-cost-project style="height:<?= $total > 0 ? max(3, (int) round($cost['project'] / $costMax * 100)) : 0 ?>%"></span>
                        <span class="cost-retainer" data-cost-retainer style="height:<?= $total > 0 ? max(3, (int) round($cost['retainer'] / $costMax * 100)) : 0 ?>%"></span>
                    </div>
                    <span class="month-label"><?= esc($monthLabel) ?></span>
                </div>
            <?php } ?>
        </div>
        <div class="chart-legend"><span><i class="legend-project"></i>Project</span><span><i class="legend-retainer"></i>Retainer</span></div>
    </article>
    <article class="card chart-card">
        <div class="chart-heading"><div><h3>Network devices by manufacturer</h3><p class="muted">Registered devices grouped by manufacturer</p></div></div>
        <div class="status-chart" data-manufacturer-chart>
            <?php $manufacturerTotal = array_sum(array_column($devicesByManufacturer, 'total')); ?>
            <?php if ($devicesByManufacturer) { foreach ($devicesByManufacturer as $deviceGroup) { $share = $manufacturerTotal > 0 ? (int) round($deviceGroup['total'] / $manufacturerTotal * 100) : 0; ?>
                <div class="status-row" aria-label="<?= esc($deviceGroup['manufacturer']) ?>: <?= (int) $deviceGroup['total'] ?> devices">
                    <div class="status-label"><span><?= esc($deviceGroup['manufacturer']) ?></span><strong><?= (int) $deviceGroup['total'] ?></strong></div>
                    <div class="status-track"><span class="status-bar" style="width:<?= $share ?>%"></span></div>
                </div>
            <?php } } else { ?><p class="chart-empty">No network devices have been registered yet.</p><?php } ?>
        </div>
    </article>
</section>
<section class="card dashboard-links">
    <div><h3>Workspace</h3><p class="muted">Go straight to a section or get help using the system.</p></div>
    <div class="quick-actions">
        <a class="button secondary" href="<?= base_url('its-features') ?>">Explore features</a>
        <a class="button secondary" href="<?= base_url('its-help') ?>">Help &amp; guides</a>
        <?php if ((session('its_user')['role'] ?? '') === 'admin') { ?><a class="button secondary" href="<?= base_url('its-admin') ?>">Administration</a><?php } ?>
    </div>
</section>
<style>
.dashboard-intro{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-bottom:20px}.dashboard-intro h2{font-size:26px;margin:0 0 6px}.dashboard-intro p{margin:0}.eyebrow{color:var(--brand);font-size:11px;font-weight:700;letter-spacing:.12em;margin-bottom:8px!important}.quick-actions{display:flex;flex-wrap:wrap;gap:8px}.dashboard-metrics{grid-template-columns:repeat(auto-fit,minmax(175px,1fr))}.metric{display:flex;min-height:145px;flex-direction:column;gap:10px;margin:0;color:inherit;text-decoration:none;transition:transform .15s ease,box-shadow .15s ease}.metric[href]:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(15,23,42,.09)}.metric-label{color:#64748b;font-size:13px;font-weight:600}.metric strong{font-size:30px;line-height:1.1}.metric-link{margin-top:auto;color:var(--brand);font-size:12px;font-weight:600}.chart-card{min-width:0}.chart-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:24px}.chart-heading h3{margin:0 0 5px}.chart-heading p{margin:0}.month-chart{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:10px;min-width:290px;height:205px;align-items:end}.month-column{display:flex;height:100%;min-width:0;flex-direction:column;align-items:center;justify-content:flex-end;gap:8px}.month-count{font-size:12px;font-weight:700;color:#334155}.month-track{display:flex;width:min(100%,42px);height:145px;align-items:flex-end;overflow:hidden;border-radius:9px 9px 3px 3px;background:linear-gradient(to top,#f1f5f9,#f8fafc)}.month-bar{display:block;width:100%;min-height:0;border-radius:8px 8px 2px 2px;background:linear-gradient(180deg,var(--brand),var(--brand-dark));transition:height .25s ease}.month-label{font-size:12px;color:#64748b}.status-chart{display:grid;gap:18px}.status-row{min-width:0}.status-label{display:flex;justify-content:space-between;gap:12px;margin-bottom:7px;font-size:13px}.status-label span{color:#475569}.status-label strong{color:#0f172a}.status-track{height:9px;overflow:hidden;border-radius:99px;background:#f1f5f9}.status-bar{display:block;height:100%;min-width:0;border-radius:99px;background:linear-gradient(90deg,var(--brand),var(--brand-dark))}.chart-empty{padding:20px 0;color:#64748b;font-size:14px}.dashboard-links{display:flex;align-items:center;justify-content:space-between;gap:18px}.dashboard-links h3{margin:0 0 6px}.dashboard-links p{margin:0}@media(max-width:650px){.dashboard-intro,.dashboard-links{align-items:flex-start;flex-direction:column}.dashboard-intro h2{font-size:22px}.dashboard-charts{grid-template-columns:1fr}}
.live-refresh{margin:0 0 10px;font-size:12px}
.cost-chart{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:10px;height:205px;align-items:end}.cost-column{display:flex;height:100%;min-width:0;flex-direction:column;align-items:center;justify-content:flex-end;gap:8px}.cost-total{font-size:10px;font-weight:700;color:#334155;white-space:nowrap}.cost-track{display:flex;width:min(100%,42px);height:145px;flex-direction:column-reverse;overflow:hidden;border-radius:9px 9px 3px 3px;background:linear-gradient(to top,#f1f5f9,#f8fafc)}.cost-project,.cost-retainer{display:block;width:100%;min-height:0;transition:height .25s ease}.cost-project{background:linear-gradient(180deg,var(--brand),var(--brand-dark))}.cost-retainer{background:#22c55e}.chart-legend{display:flex;justify-content:center;gap:18px;margin-top:12px;color:#64748b;font-size:12px}.chart-legend span{display:flex;align-items:center;gap:6px}.chart-legend i{width:9px;height:9px;border-radius:3px}.legend-project{background:var(--brand)}.legend-retainer{background:#22c55e}
</style>
<script>
(() => {
    const grid = document.querySelector('[data-dashboard-stats]');
    if (!grid) return;
    const status = document.querySelector('[data-refresh-status]');
    const update = async () => {
        try {
            const response = await fetch(grid.dataset.dashboardStats, {headers:{Accept:'application/json'}, cache:'no-store'});
            if (!response.ok) throw new Error(`Dashboard refresh returned HTTP ${response.status}.`);
            const data = await response.json();
            for (const [key, value] of Object.entries(data)) {
                if (['clients','assets','openTickets','pendingQuotes','awaitingApproval','devicesOnline','maintenanceDue'].includes(key)) {
                    const target = grid.querySelector(`[data-stat="${key}"]`);
                    if (target) target.textContent = String(value);
                } else if (key === 'projectCost') {
                    const target = grid.querySelector('[data-stat="projectCost"]');
                    if (target) target.textContent = Number(value).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
                }
            }
            const monthValues = Object.values(data.monthCounts);
            const monthMax = Math.max(1, ...monthValues);
            let monthTotal = 0;
            for (const [month, count] of Object.entries(data.monthCounts)) {
                monthTotal += Number(count);
                const column = document.querySelector(`[data-month-key="${month}"]`);
                if (!column) continue;
                column.querySelector('[data-month-count]').textContent = String(count);
                column.querySelector('[data-month-bar]').style.height = count > 0 ? `${Math.max(5, Math.round(count / monthMax * 100))}%` : '0%';
                column.setAttribute('aria-label', `${column.querySelector('.month-label').textContent}: ${count} tickets`);
            }
            document.querySelector('[data-month-total]').textContent = `${monthTotal} total`;
            const costValues = Object.values(data.costsByMonth).map(cost => Number(cost.project) + Number(cost.retainer));
            const costMax = Math.max(1, ...costValues);
            for (const [month, cost] of Object.entries(data.costsByMonth)) {
                const column = document.querySelector(`[data-cost-month="${month}"]`);
                if (!column) continue;
                const project = Number(cost.project);
                const retainer = Number(cost.retainer);
                const total = project + retainer;
                column.querySelector('[data-cost-total]').textContent = total.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
                column.querySelector('[data-cost-project]').style.height = total > 0 ? `${Math.max(3, Math.round(project / costMax * 100))}%` : '0%';
                column.querySelector('[data-cost-retainer]').style.height = total > 0 ? `${Math.max(3, Math.round(retainer / costMax * 100))}%` : '0%';
                column.setAttribute('aria-label', `${column.querySelector('.month-label').textContent}: ${total.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}`);
            }
            const statuses = document.querySelector('[data-live-statuses]');
            const statusRows = data.ticketStatuses;
            const total = statusRows.reduce((sum, row) => sum + Number(row.total), 0);
            document.querySelector('[data-status-total]').textContent = `${total} total`;
            statuses.replaceChildren();
            if (!statusRows.length) {
                const empty = document.createElement('p');
                empty.className = 'chart-empty';
                empty.textContent = 'No tickets yet. Create a ticket to see its status here.';
                statuses.append(empty);
            } else {
                for (const row of statusRows) {
                    const item = document.createElement('div');
                    item.className = 'status-row';
                    const label = document.createElement('div');
                    label.className = 'status-label';
                    const name = document.createElement('span');
                    name.textContent = String(row.status).replaceAll('_', ' ').replace(/\b\w/g, char => char.toUpperCase());
                    const count = document.createElement('strong');
                    count.textContent = String(row.total);
                    label.append(name, count);
                    const track = document.createElement('div');
                    track.className = 'status-track';
                    const bar = document.createElement('span');
                    bar.className = 'status-bar';
                    bar.style.width = `${total ? Math.round(Number(row.total) / total * 100) : 0}%`;
                    track.append(bar);
                    item.append(label, track);
                    statuses.append(item);
                }
            }
            const manufacturerChart = document.querySelector('[data-manufacturer-chart]');
            manufacturerChart.replaceChildren();
            const manufacturerTotal = data.devicesByManufacturer.reduce((sum, item) => sum + Number(item.total), 0);
            if (!data.devicesByManufacturer.length) {
                const empty = document.createElement('p');
                empty.className = 'chart-empty';
                empty.textContent = 'No network devices have been registered yet.';
                manufacturerChart.append(empty);
            } else {
                for (const device of data.devicesByManufacturer) {
                    const row = document.createElement('div');
                    row.className = 'status-row';
                    const label = document.createElement('div');
                    label.className = 'status-label';
                    const name = document.createElement('span');
                    name.textContent = String(device.manufacturer);
                    const count = document.createElement('strong');
                    count.textContent = String(device.total);
                    label.append(name, count);
                    const track = document.createElement('div');
                    track.className = 'status-track';
                    const bar = document.createElement('span');
                    bar.className = 'status-bar';
                    bar.style.width = `${manufacturerTotal ? Math.round(Number(device.total) / manufacturerTotal * 100) : 0}%`;
                    track.append(bar);
                    row.append(label, track);
                    manufacturerChart.append(row);
                }
            }
            status.textContent = `Updated ${new Date().toLocaleTimeString()}.`;
        } catch (error) {
            console.error('Dashboard refresh failed.', error);
            status.textContent = 'Could not refresh dashboard data. Retrying in one minute.';
        }
    };
    update();
    window.setInterval(update, 5000);
})();
</script>
<?= view('itsupport/_footer') ?>