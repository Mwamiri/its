<?= view('itsupport/_header') ?>
<div class="card"><h2><?= esc($ticket['ticket_number']) ?></h2>
<p><strong>Subject:</strong> <?= esc($ticket['subject']) ?><br><strong>Status:</strong> <?= esc($ticket['status']) ?></p>
<a class="button" href="<?= base_url('its-report/' . $ticket['id']) ?>">Report & Sign-Off</a></div>
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