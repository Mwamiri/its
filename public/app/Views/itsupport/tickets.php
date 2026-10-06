<?= view('itsupport/_header') ?>
<div class="grid grid-2">
<div class="card tbl"><table><tr><th>#</th><th>Client</th><th>Subject</th><th>Status</th><th></th></tr>
<?php foreach ($tickets as $t) { ?><tr><td><?= esc($t['ticket_number']) ?></td><td><?= esc($t['client_name']) ?></td><td><?= esc($t['subject']) ?></td><td><span class="badge blue"><?= esc($t['status']) ?></span></td><td><a class="button secondary" href="<?= base_url('its-tickets-view/' . $t['id']) ?>">Open</a></td></tr><?php } ?>
</table></div>
<div class="card"><h3>New Ticket</h3>
<form method="post" action="<?= base_url('its-tickets') ?>"><?= csrf_field() ?>
<label>Client</label><select name="client_id" required><?php foreach ($clients as $c) { ?><option value="<?= $c['id'] ?? $c->id ?>"><?= esc($c['name'] ?? $c->name) ?></option><?php } ?></select>
<label>Subject</label><input name="subject" required>
<label>Description</label><textarea name="description"></textarea>
<label>Priority</label><select name="priority"><?php foreach (['low','medium','high','urgent'] as $p) { ?><option><?= $p ?></option><?php } ?></select>
<label>Visit Date</label><input type="date" name="visit_date" value="<?= date('Y-m-d') ?>">
<button class="btn">Create</button></form></div>
</div>
<?= view('itsupport/_footer') ?>