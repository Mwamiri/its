<?= view('itsupport/_header') ?>
<div class="card"><h2><?= esc($ticket['ticket_number']) ?></h2>
<p><strong>Subject:</strong> <?= esc($ticket['subject']) ?><br><strong>Status:</strong> <?= esc($ticket['status']) ?></p>
<a class="button" href="<?= base_url('its-report/' . $ticket['id']) ?>">Report & Sign-Off</a></div>
<?php $slaState = \App\Libraries\Sla::state($ticket); $slaCls = ['ok' => 'green', 'warning' => 'orange', 'breached' => 'red', 'met' => 'green', 'none' => 'gray'][$slaState] ?? 'gray'; ?>
<div class="card"><h3>SLA &amp; assignment</h3>
<p><span class="badge <?= $slaCls ?>">SLA: <?= esc($slaState) ?></span> <?= esc(\App\Libraries\Sla::remaining($ticket)) ?><?php if (!empty($ticket['due_at'])) { ?> · Due <?= esc(substr((string) $ticket['due_at'], 0, 16)) ?><?php } ?><?php if (!empty($ticket['rating'])) { ?> · Client rating <strong><?= (int) $ticket['rating'] ?>/5</strong><?= $ticket['rating_comment'] ? ' – ' . esc($ticket['rating_comment']) : '' ?><?php } ?></p>
<form method="post" action="<?= base_url('its-tickets-meta/' . $ticket['id']) ?>"><?= csrf_field() ?>
<div class="grid grid-2"><div><label for="tt">Type</label><select id="tt" name="ticket_type"><?php foreach (['incident', 'request', 'problem', 'change'] as $ty) { ?><option <?= ($ticket['ticket_type'] ?? 'incident') === $ty ? 'selected' : '' ?>><?= $ty ?></option><?php } ?></select></div>
<div><label for="as">Assigned to</label><select id="as" name="assigned_to"><option value="0">Unassigned</option><?php foreach (($staff ?? []) as $s) { ?><option value="<?= (int) $s['id'] ?>" <?= (int) ($ticket['assigned_to'] ?? 0) === (int) $s['id'] ? 'selected' : '' ?>><?= esc($s['name']) ?></option><?php } ?></select></div></div>
<button class="btn">Save</button></form></div>
<div class="card"><h3>Time tracking</h3>
<?php $tot = array_sum(array_column($time ?? [], 'minutes')); ?><p class="muted">Total <?= floor($tot / 60) ?>h <?= $tot % 60 ?>m</p>
<?php foreach (($time ?? []) as $te) { ?><p><?= (int) $te['minutes'] ?> min <span class="badge <?= $te['billable'] ? 'green' : 'gray' ?>"><?= $te['billable'] ? 'billable' : 'non-billable' ?></span> <?= esc($te['note']) ?> <span class="muted"><?= esc($te['created_at']) ?></span></p><?php } ?>
<form method="post" action="<?= base_url('its-tickets-time/' . $ticket['id']) ?>"><?= csrf_field() ?>
<div class="grid grid-2"><div><label for="tm">Minutes</label><input id="tm" type="number" name="minutes" min="1" max="1440" required></div><div><label for="tn">Note</label><input id="tn" name="note" maxlength="255"></div></div>
<label><input type="checkbox" name="billable" value="1" checked> Billable</label> <button class="btn">Log time</button></form></div>
<div class="card"><h3>Conversation</h3>
<?php foreach (($updates ?? []) as $u) { ?><p><strong><?= esc($u['author_name']) ?></strong> <span class="badge <?= $u['visibility'] === 'internal' ? 'orange' : 'blue' ?>"><?= esc($u['visibility']) ?></span> <span class="muted"><?= esc($u['created_at']) ?></span><br><?= nl2br(esc($u['message'])) ?></p><?php } ?>
<form method="post" action="<?= base_url('its-tickets-reply/' . $ticket['id']) ?>"><?= csrf_field() ?>
<?php if (!empty($canned)) { ?><label for="canned">Insert canned reply</label><select id="canned" onchange="if(this.value){var m=document.querySelector('textarea[name=message]');m.value+=(m.value?'\n':'')+this.value;this.selectedIndex=0}"><option value="">-</option><?php foreach ($canned as $c) { ?><option value="<?= esc($c['body'], 'attr') ?>"><?= esc($c['title']) ?></option><?php } ?></select><?php } ?>
<label>Message</label><textarea name="message" maxlength="3000"></textarea>
<div class="grid grid-2"><div><label>Visible to</label><select name="visibility"><option value="public">Client and staff</option><option value="internal">Internal note (staff only)</option></select></div>
<div><label>Status</label><select name="status"><?php foreach (['new','in_progress','waiting_parts','waiting_approval','completed','closed'] as $s) { ?><option <?= $s === $ticket['status'] ? 'selected' : '' ?>><?= $s ?></option><?php } ?></select></div></div>
<button class="btn">Update ticket</button></form></div>
<div class="card"><h3>Add Task</h3>
<form method="post" action="<?= base_url('its-tasks/' . $ticket['id']) ?>" enctype="multipart/form-data"><?= csrf_field() ?>
<label>Item</label><input name="item" required>
<label>Serial</label><input name="serial_number">
<label>Complaint</label><textarea name="complaint" required></textarea>
<label>Diagnosis</label><textarea name="diagnosis"></textarea>
<label>Action Taken</label><textarea name="action_taken"></textarea>
<label>Recommendation</label><textarea name="recommendation"></textarea>
<div class="grid grid-2">
<div><label>Scope</label><select name="scope"><option>retainer</option><option>project</option></select></div>
<div><label>Status</label><select name="status"><?php foreach (['new','in_progress','completed','waiting_parts','waiting_approval'] as $s) { ?><option><?= $s ?></option><?php } ?></select></div>
<div><label>Parts Cost</label><input type="number" step="0.01" name="parts_cost" value="0"></div>
<div><label>Labor Cost</label><input type="number" step="0.01" name="labor_cost" value="0"></div>
</div>
<label>Photos</label><input type="file" name="photos[]" accept="image/*" multiple>
<button class="btn">Save Task</button></form></div>
<?php foreach ($tasks as $t) { ?>
<div class="card"><strong><?= esc($t['item']) ?></strong> <span class="badge <?= $t['scope'] === 'project' ? 'orange' : 'gray' ?>"><?= esc($t['scope']) ?></span><br>
<strong>Complaint:</strong> <?= esc($t['complaint']) ?><br><strong>Action:</strong> <?= esc($t['action_taken']) ?><br><strong>Recommendation:</strong> <?= esc($t['recommendation']) ?><br>
<span class="badge blue"><?= esc($t['status']) ?></span>
<div><?php foreach (($photos[$t['id']] ?? []) as $p) { ?><img class="photo-thumb" src="<?= base_url($p['photo_path']) ?>"><?php } ?></div></div>
<?php } ?>
<?= view('itsupport/_footer') ?>