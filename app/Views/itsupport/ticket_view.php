<?= view('itsupport/_header') ?>
<?php $tcfDefs = \App\Libraries\CustomFields::defs('tickets'); ?>
<div class="card"><div class="section-heading"><div><p class="eyebrow"><?= esc($ticket['ticket_number']) ?> · <?= esc(str_replace('_', ' ', $ticket['priority'])) ?> priority</p><h2><?= esc($ticket['subject']) ?></h2></div>
<div class="quick-actions" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
<span class="badge blue"><?= esc(str_replace('_', ' ', $ticket['status'])) ?></span>
<a class="button secondary" href="<?= base_url('its-report/' . $ticket['id']) ?>">Report &amp; sign-off</a>
<form method="post" action="<?= base_url('its-tickets-watch/' . $ticket['id']) ?>" style="display:contents"><?= csrf_field() ?>
<button class="btn secondary" title="<?= $watching ? 'Stop watching' : 'Watch this ticket — get emailed on updates' ?>"><?= $watching ? '🔔 Watching' : '🔕 Watch' ?></button>
</form>
<button class="btn secondary" onclick="document.getElementById('merge-modal').removeAttribute('hidden')">⇄ Merge</button>
</div></div>
<?php if ($watchers) { ?><p class="muted" style="font-size:12px;margin:4px 0 0">Watching: <?= esc(implode(', ', array_column($watchers, 'name'))) ?></p><?php } ?>
<p class="muted"><?php if ($client) { ?>Client: <a href="<?= base_url('its-clients-view/' . $client['id']) ?>"><?= esc($client['name']) ?></a><?= $client['phone'] ? ' · ' . esc($client['phone']) : '' ?><?php } ?>
<?php if ($asset) { ?> · Equipment: <a href="<?= base_url('its-assets-view/' . $asset['id']) ?>"><?= esc($asset['name']) ?></a><?= $asset['serial_number'] ? ' (S/N ' . esc($asset['serial_number']) . ')' : '' ?><?php } ?>
 · Opened <?= esc(substr((string) $ticket['created_at'], 0, 16)) ?></p>
<?php if (!empty($ticket['description'])) { ?><p><?= nl2br(esc($ticket['description'])) ?></p><?php } ?>
<?php if ($tcfDefs) { ?><table><?php foreach ($tcfDefs as $d) { $v = \App\Libraries\CustomFields::display($d, $cf[$d['field_key']] ?? ''); ?><tr><th style="width:30%"><?= esc($d['label']) ?></th><td><?= $v === '' ? '<span class="muted">-</span>' : nl2br(esc($v)) ?></td></tr><?php } ?></table><?php } ?>
</div>
<?php $slaState = \App\Libraries\Sla::state($ticket); $slaCls = ['ok' => 'green', 'warning' => 'orange', 'breached' => 'red', 'met' => 'green', 'none' => 'gray'][$slaState] ?? 'gray'; ?>
<div class="card"><h3>SLA &amp; assignment</h3>
<p><span class="badge <?= $slaCls ?>">SLA: <?= esc($slaState) ?></span> <?= esc(\App\Libraries\Sla::remaining($ticket)) ?><?php if (!empty($ticket['due_at'])) { ?> · Due <?= esc(substr((string) $ticket['due_at'], 0, 16)) ?><?php } ?><?php if (!empty($ticket['rating'])) { ?> · Client rating <strong><?= (int) $ticket['rating'] ?>/5</strong><?= $ticket['rating_comment'] ? ' – ' . esc($ticket['rating_comment']) : '' ?><?php } ?></p>
<form method="post" action="<?= base_url('its-tickets-meta/' . $ticket['id']) ?>"><?= csrf_field() ?>
<div class="grid grid-2"><div><label for="tt">Type</label><select id="tt" name="ticket_type"><?php foreach (['incident', 'request', 'problem', 'change'] as $ty) { ?><option <?= ($ticket['ticket_type'] ?? 'incident') === $ty ? 'selected' : '' ?>><?= $ty ?></option><?php } ?></select></div>
<div><label for="as">Assigned to</label><select id="as" name="assigned_to"><option value="0">Unassigned</option><?php foreach (($staff ?? []) as $s) { ?><option value="<?= (int) $s['id'] ?>" <?= (int) ($ticket['assigned_to'] ?? 0) === (int) $s['id'] ? 'selected' : '' ?>><?= esc($s['name']) ?></option><?php } ?></select></div>
<?php if ($clientAssets) { ?><div><label for="ta">Equipment</label><select id="ta" name="asset_id"><option value="0">Not linked</option><?php foreach ($clientAssets as $ca) { ?><option value="<?= (int) $ca['id'] ?>" <?= (int) ($ticket['asset_id'] ?? 0) === (int) $ca['id'] ? 'selected' : '' ?>><?= esc($ca['name'] . ($ca['serial_number'] ? ' · ' . $ca['serial_number'] : '')) ?></option><?php } ?></select></div><?php } ?></div>
<?php if ($tcfDefs) { ?><details><summary>Edit additional details</summary><?= view('itsupport/_custom_fields', ['entity' => 'tickets', 'values' => $cf]) ?></details><?php } ?>
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
<!-- Merge ticket modal -->
<div id="merge-modal" hidden style="position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;background:rgba(15,23,42,.55);backdrop-filter:blur(3px)">
  <div style="background:var(--paper);border-radius:18px;padding:30px 32px;min-width:320px;max-width:500px;width:92%;box-shadow:0 24px 64px rgba(0,0,0,.25)">
    <p style="margin:0 0 14px;font-size:1.05rem;font-weight:700;color:var(--ink)">Merge into another ticket</p>
    <p class="muted">All updates, tasks and time entries from <strong><?= esc($ticket['ticket_number']) ?></strong> will be moved to the target ticket, and this ticket will be closed.</p>
    <form method="post" action="<?= base_url('its-tickets-merge/' . $ticket['id']) ?>"><?= csrf_field() ?>
    <label for="merge-into">Target ticket</label>
    <select id="merge-into" name="merge_into" required style="margin:8px 0 16px">
      <option value="">— Select a ticket —</option>
      <?php foreach ($allTickets as $at) { ?><option value="<?= (int) $at['id'] ?>"><?= esc($at['ticket_number']) ?> – <?= esc(mb_strimwidth($at['subject'], 0, 60, '…')) ?></option><?php } ?>
    </select>
    <div style="display:flex;gap:10px;justify-content:flex-end">
      <button type="button" class="btn secondary" onclick="document.getElementById('merge-modal').setAttribute('hidden','')">Cancel</button>
      <button class="btn" onclick="return confirm('Merge this ticket? This cannot be undone.')">Merge</button>
    </div>
    </form>
  </div>
</div>
<?= view('itsupport/_footer') ?>