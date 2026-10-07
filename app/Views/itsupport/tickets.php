<?= view('itsupport/_header') ?>
<?php
$views = ['open' => 'Open', 'mine' => 'Assigned to me', 'overdue' => 'Overdue', 'unassigned' => 'Unassigned', 'all' => 'All'];
$keep = static fn(array $over) => base_url('its-tickets?' . http_build_query(array_filter(array_merge($filters, $over))));
$prio = ['low' => 'gray', 'medium' => 'blue', 'high' => 'orange', 'urgent' => 'orange'];
$now = date('Y-m-d H:i:s');
$staffList = \Config\Database::connect()->table('users')->select('id, name')->where('role !=', 'client')->where('active', 1)->orderBy('name')->get()->getResultArray();
?>
<div class="section-heading"><div><p class="eyebrow">Service desk</p><h2>Tickets <span class="muted">(<?= count($tickets) ?>)</span></h2></div>
<a class="button" href="#new-ticket">New ticket</a></div>
<div class="view-tabs" role="navigation" aria-label="Ticket views"><?php foreach ($views as $k => $l) { ?><a href="<?= $keep(['view' => $k, 'status' => '']) ?>" <?= $filters['view'] === $k ? 'aria-current="page"' : '' ?>><?= esc($l) ?></a><?php } ?></div>
<form method="get" action="<?= base_url('its-tickets') ?>" class="card filter-bar"><input type="hidden" name="view" value="<?= esc($filters['view']) ?>">
<div><label for="tf-client">Client</label><select id="tf-client" name="client_id"><option value="">All clients</option><?php foreach ($clients as $c) { ?><option value="<?= (int) $c['id'] ?>" <?= $filters['client_id'] === (int) $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option><?php } ?></select></div>
<div><label for="tf-status">Status</label><select id="tf-status" name="status"><option value="">Any</option><?php foreach (\App\Controllers\ItsTickets::STATUSES as $s) { ?><option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= esc(str_replace('_', ' ', $s)) ?></option><?php } ?></select></div>
<div><label for="tf-q">Search</label><input id="tf-q" type="search" name="q" value="<?= esc($filters['q']) ?>" placeholder="Number, subject or client"></div>
<div class="filter-actions"><button class="btn">Apply</button></div></form>
<div class="grid grid-2">
<div class="card tbl"><table><tr><th>Ticket</th><th>Client</th><th>Priority</th><th>Assignee</th><th>Due</th><th>Status</th></tr>
<?php foreach ($tickets as $t) { $late = !empty($t['due_at']) && $t['due_at'] < $now && !in_array($t['status'], ['completed', 'closed'], true); ?><tr>
<td><a href="<?= base_url('its-tickets-view/' . $t['id']) ?>"><strong><?= esc($t['ticket_number']) ?></strong></a><br><?= esc($t['subject']) ?></td>
<td><?= esc($t['client_name']) ?></td><td><span class="badge <?= $prio[$t['priority']] ?? 'gray' ?>"><?= esc($t['priority']) ?></span></td>
<td><?= esc($t['assignee'] ?? '') ?: '<span class="muted">-</span>' ?></td>
<td><?= !empty($t['due_at']) ? esc(substr((string) $t['due_at'], 0, 16)) : '-' ?><?= $late ? ' <span class="badge orange">overdue</span>' : '' ?></td>
<td><span class="badge <?= in_array($t['status'], ['completed', 'closed'], true) ? 'green' : 'blue' ?>"><?= esc(str_replace('_', ' ', $t['status'])) ?></span></td></tr><?php } ?>
<?php if (!$tickets) { ?><tr><td colspan="6" class="muted">No tickets in this view.</td></tr><?php } ?>
</table></div>
<div class="card" id="new-ticket"><h3>New ticket</h3>
<form method="post" action="<?= base_url('its-tickets') ?>"><?= csrf_field() ?>
<?php if ($templates) { ?><label for="ticket-template">Start from a template</label><select id="ticket-template"><option value="">Choose a starting point</option><?php foreach ($templates as $template) { ?><option value="<?= esc($template['id']) ?>"><?= esc($template['name']) ?></option><?php } ?></select><?php } ?>
<div class="form-grid">
<div class="field wide"><label for="ticket-client">Client</label><select id="ticket-client" name="client_id" required><option value="">Select a client</option><?php foreach ($clients as $c) { ?><option value="<?= (int) $c['id'] ?>" <?= $preClient === (int) $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option><?php } ?></select></div>
<?php if ($assets) { ?><div class="field wide"><label for="ticket-asset">Equipment (optional)</label><select id="ticket-asset" name="asset_id"><option value="0">Not linked to a device</option><?php foreach ($assets as $a) { ?><option value="<?= (int) $a['id'] ?>" data-client="<?= (int) $a['client_id'] ?>" <?= $preAsset === (int) $a['id'] ? 'selected' : '' ?>><?= esc($a['name'] . ($a['serial_number'] ? ' · ' . $a['serial_number'] : '')) ?></option><?php } ?></select></div><?php } ?>
<div class="field wide"><label for="ticket-subject">Subject</label><input id="ticket-subject" name="subject" required maxlength="190" value="<?= esc(old('subject') ?? '') ?>" placeholder="Short summary, e.g. Laptop will not boot"></div>
<div class="field wide"><label for="ticket-description">Description</label><textarea id="ticket-description" name="description" maxlength="5000" placeholder="What is happening, error messages, when it started, what has been tried"><?= esc(old('description') ?? '') ?></textarea></div>
<div class="field wide"><label>Priority</label><div class="seg" role="radiogroup" aria-label="Priority"><?php foreach (['low','medium','high','urgent'] as $p) { ?><input type="radio" name="priority" id="pr-<?= $p ?>" value="<?= $p ?>" <?= (old('priority') ?? 'medium') === $p ? 'checked' : '' ?>><label for="pr-<?= $p ?>"><?= $p ?></label><?php } ?></div></div>
<div class="field"><label for="ticket-type">Type</label><select id="ticket-type" name="ticket_type"><?php foreach (['incident', 'request', 'problem', 'change'] as $ty) { ?><option><?= $ty ?></option><?php } ?></select></div>
<div class="field"><label for="ticket-assign">Assign to</label><select id="ticket-assign" name="assigned_to"><option value="0">Unassigned</option><?php foreach ($staffList as $s) { ?><option value="<?= (int) $s['id'] ?>"><?= esc($s['name']) ?></option><?php } ?></select></div>
<div class="field"><label for="visit-date">Visit date</label><input id="visit-date" type="date" name="visit_date" value="<?= esc(old('visit_date') ?? date('Y-m-d')) ?>"></div>
</div>
<?= view('itsupport/_custom_fields', ['entity' => 'tickets', 'values' => is_array(old('cf')) ? old('cf') : []]) ?>
<div class="form-actions"><button class="btn">Create ticket</button></div></form></div>
</div>
<style>.view-tabs{display:flex;flex-wrap:wrap;gap:6px;margin:0 0 10px;background:none;border:0;padding:0;position:static}.view-tabs a{padding:7px 14px;border:1px solid var(--line);border-radius:999px;text-decoration:none;color:var(--ink);background:var(--paper);font-size:13px;font-weight:600;min-height:0}.view-tabs a[aria-current=page]{background:var(--brand);border-color:var(--brand);color:#fff}.filter-bar{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,170px),1fr));gap:4px 12px;align-items:end}.filter-bar label{font-size:12px;color:var(--muted);font-weight:700}.filter-bar select,.filter-bar input{margin:4px 0 0}.filter-actions{display:flex;gap:6px}</style>
<?php if ($templates) { ?><script id="ticket-templates-data" type="application/json"><?= json_encode($templates, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<script>
const ticketTemplates=JSON.parse(document.getElementById('ticket-templates-data').textContent);
document.getElementById('ticket-template').addEventListener('change',function(){const template=ticketTemplates.find(item=>String(item.id)===this.value);if(template){document.getElementById('ticket-subject').value=template.subject||'';document.getElementById('ticket-description').value=template.body||'';}});
</script><?php } ?>
<script>
(()=>{const c=document.getElementById('ticket-client'),a=document.getElementById('ticket-asset');if(!c||!a)return;
const sync=()=>{[...a.options].forEach(o=>{if(!o.dataset.client)return;const show=o.dataset.client===c.value;o.hidden=!show;o.disabled=!show;if(!show&&o.selected)a.value='0';});};
c.addEventListener('change',sync);sync();})();
</script>
<?= view('itsupport/_footer') ?>
