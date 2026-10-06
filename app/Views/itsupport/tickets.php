<?= view('itsupport/_header') ?>
<?php $cnt = []; foreach ($tickets as $t0) { $cnt[$t0['status']] = ($cnt[$t0['status']] ?? 0) + 1; } ?>
<div class="tw-flex tw-flex-wrap tw-items-center tw-gap-2 tw-mb-4">
<h2 class="tw-m-0 tw-mr-auto tw-text-2xl tw-font-bold">Tickets <span class="tw-text-muted tw-text-base">(<?= count($tickets) ?>)</span></h2>
<?php foreach ($cnt as $s => $n) { ?><span class="tw-rounded-full tw-bg-brand-soft tw-px-3 tw-py-1 tw-text-sm tw-font-semibold"><?= esc($s) ?>: <?= $n ?></span><?php } ?>
<input id="row-filter" type="search" placeholder="Filter tickets" aria-label="Filter tickets" class="tw-w-56">
</div>
<div class="grid grid-2">
<div class="card tbl"><table id="filter-table"><tr><th>#</th><th>Client</th><th>Subject</th><th>Status</th><th></th></tr>
<?php foreach ($tickets as $t) { ?><tr><td><?= esc($t['ticket_number']) ?></td><td><?= esc($t['client_name']) ?></td><td><?= esc($t['subject']) ?></td><td><span class="badge blue"><?= esc($t['status']) ?></span></td><td><a class="button secondary" href="<?= base_url('its-tickets-view/' . $t['id']) ?>">Open</a></td></tr><?php } ?>
</table></div>
<div class="card"><h3>New Ticket</h3>
<form method="post" action="<?= base_url('its-tickets') ?>"><?= csrf_field() ?>
<?php if ($templates) { ?><label for="ticket-template">Start from a template</label><select id="ticket-template"><option value="">Choose a starting point</option><?php foreach ($templates as $template) { ?><option value="<?= esc($template['id']) ?>"><?= esc($template['name']) ?></option><?php } ?></select><?php } ?>
<label for="ticket-client">Client</label><select id="ticket-client" name="client_id" required><option value="">Select a client</option><?php foreach ($clients as $c) { ?><option value="<?= $c['id'] ?>"><?= esc($c['name']) ?></option><?php } ?></select>
<label for="ticket-subject">Subject</label><input id="ticket-subject" name="subject" required maxlength="190" placeholder="Short summary, e.g. Laptop will not boot">
<label for="ticket-description">Description</label><textarea id="ticket-description" name="description" maxlength="5000" placeholder="What is happening, error messages, when it started, what has been tried"></textarea>
<div class="field"><label>Priority</label><div class="seg" role="radiogroup" aria-label="Priority"><?php foreach (['low','medium','high','urgent'] as $p) { ?><input type="radio" name="priority" id="pr-<?= $p ?>" value="<?= $p ?>" <?= $p === 'medium' ? 'checked' : '' ?>><label for="pr-<?= $p ?>"><?= $p ?></label><?php } ?></div></div>
<label for="visit-date">Visit date</label><input id="visit-date" type="date" name="visit_date" value="<?= date('Y-m-d') ?>">
<button class="btn">Create</button></form></div>
</div>
<?php if ($templates) { ?><script id="ticket-templates-data" type="application/json"><?= json_encode($templates, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<script>
const ticketTemplates=JSON.parse(document.getElementById('ticket-templates-data').textContent);
document.getElementById('ticket-template').addEventListener('change',function(){const template=ticketTemplates.find(item=>String(item.id)===this.value);if(template){document.getElementById('ticket-subject').value=template.subject||'';document.getElementById('ticket-description').value=template.body||'';}});
</script><?php } ?>
<script>document.getElementById('row-filter').addEventListener('input',function(){const q=this.value.toLowerCase();document.querySelectorAll('#filter-table tr').forEach((r,i)=>{if(i)r.hidden=!r.textContent.toLowerCase().includes(q);});});</script>
<?= view('itsupport/_footer') ?>