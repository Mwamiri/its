<?= view('itsupport/_header') ?>
<?php $labels = \App\Controllers\ItsAssets::EVENTS; $comps = array_keys(\App\Controllers\ItsAssets::COMPONENTS); ?>
<div class="grid grid-2">
<div class="card"><div class="section-heading"><h2><?= esc($asset['name']) ?></h2><a class="button secondary" href="<?= base_url('its-assets-edit/' . $asset['id']) ?>">Edit details</a></div>
<p><strong>Serial:</strong> <?= esc($asset['serial_number']) ?> · <strong>Status:</strong> <span class="badge <?= $asset['status'] === 'active' ? 'green' : 'orange' ?>"><?= esc($asset['status']) ?></span><br>
<strong>Brand / model:</strong> <?= esc(trim($asset['brand'] . ' ' . $asset['model'])) ?><br><strong>Location:</strong> <?= esc($asset['location']) ?></p>
<table><tr><th>Hostname</th><td><?= esc($asset['hostname'] ?: '-') ?></td></tr><tr><th>CPU</th><td><?= esc($asset['cpu'] ?: '-') ?></td></tr><tr><th>RAM</th><td><?= esc($asset['ram'] ?: '-') ?></td></tr><tr><th>Storage</th><td><?= esc($asset['storage'] ?: '-') ?></td></tr><tr><th>OS</th><td><?= esc($asset['os'] ?: '-') ?></td></tr><tr><th>Purchased</th><td><?= esc($asset['purchase_date'] ?: '-') ?></td></tr>
<tr><th>Warranty until</th><td><?= esc($asset['warranty_until'] ?: '-') ?><?php if (!empty($asset['warranty_until']) && $asset['warranty_until'] < date('Y-m-d')) { ?> <span class="badge red">expired</span><?php } ?></td></tr></table>
<img src="<?= base_url('its-qr/' . $asset['id']) ?>" alt="QR code" style="width:160px;height:160px;background:#fff;border:1px solid #dbe2ea;margin-top:10px"></div>
<div class="card"><h3>Log work on this machine</h3>
<form method="post" action="<?= base_url('its-assets-log/' . $asset['id']) ?>"><?= csrf_field() ?>
<label for="ev">What was done</label><select id="ev" name="event_type" required><?php foreach ($labels as $k => $l) { ?><option value="<?= $k ?>"><?= esc($l) ?></option><?php } ?></select>
<div class="grid grid-2"><div><label for="cp">Component</label><select id="cp" name="component"><option value="">-</option><?php foreach ($comps as $c) { ?><option><?= esc($c) ?></option><?php } ?></select></div>
<div><label for="tk">Link to ticket</label><select id="tk" name="ticket_id"><option value="0">None</option><?php foreach ($tickets as $t) { ?><option value="<?= (int) $t['id'] ?>"><?= esc($t['ticket_number'] . ' ' . $t['subject']) ?></option><?php } ?></select></div>
<div><label for="os1">Before (old spec)</label><input id="os1" name="old_spec" placeholder="e.g. 4GB DDR4" maxlength="190"></div><div><label for="ns1">After (new spec)</label><input id="ns1" name="new_spec" placeholder="e.g. 16GB DDR4 (2x8GB)" maxlength="190"></div>
<div><label for="pc">Parts cost</label><input id="pc" type="number" step="0.01" min="0" name="parts_cost" value="0"></div><div><label for="lc">Labour cost</label><input id="lc" type="number" step="0.01" min="0" name="labor_cost" value="0"></div></div>
<label for="dt">Notes (faults found, steps taken, part serials)</label><textarea id="dt" name="details" maxlength="3000"></textarea>
<label for="st">Machine status afterwards</label><select id="st" name="status"><?php foreach (['active', 'under_repair', 'faulty', 'retired'] as $s) { ?><option <?= $asset['status'] === $s ? 'selected' : '' ?>><?= $s ?></option><?php } ?></select>
<p class="muted">For RAM, SSD/HDD, CPU or OS changes, the "After" value also updates the machine's specs.</p>
<button class="btn">Save to machine log</button></form></div></div>
<div class="card"><h3>Service history</h3>
<?php foreach ($events as $e) { ?><p><span class="badge blue"><?= esc($labels[$e['event_type']] ?? $e['event_type']) ?></span> <?= $e['component'] ? '<strong>' . esc($e['component']) . '</strong> ' : '' ?><?= ($e['old_spec'] || $e['new_spec']) ? esc($e['old_spec'] ?: '?') . ' → ' . esc($e['new_spec'] ?: '?') : '' ?>
<?= $e['ticket_number'] ? ' · <a href="' . base_url('its-tickets-view/' . $e['ticket_id']) . '">' . esc($e['ticket_number']) . '</a>' : '' ?><br><?= nl2br(esc((string) $e['details'])) ?><br>
<span class="muted"><?= esc($e['created_at']) ?> · <?= esc($e['tech'] ?: 'system') ?><?= ($e['parts_cost'] + $e['labor_cost']) > 0 ? ' · parts ' . number_format($e['parts_cost'], 2) . ' + labour ' . number_format($e['labor_cost'], 2) : '' ?></span></p><?php } ?>
<?php foreach ($history as $h) { ?><p><span class="badge gray">Ticket task</span> <strong><?= esc($h['ticket_number']) ?> - <?= esc($h['item']) ?></strong><br><?= esc($h['action_taken']) ?></p><?php } ?>
<?php if (!$events && !$history) { ?><p class="muted">Nothing logged yet.</p><?php } ?></div>
<?= view('itsupport/_footer') ?>