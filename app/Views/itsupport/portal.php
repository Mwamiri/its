<?= view('itsupport/_header') ?>
<div class="card"><h2>Welcome<?= $client ? ', ' . esc($client['name']) : '' ?></h2>
<p class="muted">Track your support requests and report new issues.</p>
<a class="button" href="<?= base_url('its-portal-new') ?>">Report an issue</a></div>
<div class="grid grid-3">
<div class="card"><h3>Open</h3><p style="font-size:2rem;margin:0"><?= (int) $open ?></p></div>
<div class="card"><h3>Resolved</h3><p style="font-size:2rem;margin:0"><?= (int) $done ?></p></div>
<div class="card"><h3>Total</h3><p style="font-size:2rem;margin:0"><?= (int) $total ?></p></div>
</div>
<div class="card"><h3>My tickets</h3>
<table class="tbl"><tr><th>Ticket</th><th>Subject</th><th>Priority</th><th>Status</th><th>Opened</th></tr>
<?php foreach ($tickets as $t) { $done_ = in_array($t['status'], ['completed', 'closed'], true); ?>
<tr><td><a href="<?= base_url('its-portal-ticket/' . $t['id']) ?>"><?= esc($t['ticket_number']) ?></a></td><td><?= esc($t['subject']) ?></td><td><?= esc($t['priority']) ?></td><td><span class="badge <?= $done_ ? 'green' : 'blue' ?>"><?= esc(str_replace('_', ' ', $t['status'])) ?></span></td><td><?= esc(substr((string) $t['created_at'], 0, 10)) ?></td></tr>
<?php } if (!$tickets) { ?><tr><td colspan="5" class="muted">No tickets yet.</td></tr><?php } ?></table></div>
<?= view('itsupport/_footer') ?>
