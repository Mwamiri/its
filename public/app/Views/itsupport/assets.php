<?= view('itsupport/_header') ?>
<div class="grid grid-2">
<div class="card tbl"><table><tr><th>Asset</th><th>Client</th><th>Serial</th><th>QR</th><th></th></tr>
<?php foreach ($assets as $a) { ?><tr><td><?= esc($a['name']) ?></td><td><?= esc($a['client_name']) ?></td><td><?= esc($a['serial_number']) ?></td><td><a href="<?= base_url('its-qr/' . $a['id']) ?>" target="_blank">QR</a></td><td><a class="button secondary" href="<?= base_url('its-assets-view/' . $a['id']) ?>">Open</a></td></tr><?php } ?>
</table></div>
<div class="card"><h3><?= $edit ? 'Edit' : 'Add' ?> Asset</h3>
<form method="post" action="<?= base_url('its-assets-save/' . ($edit['id'] ?? '')) ?>"><?= csrf_field() ?>
<label>Client</label><select name="client_id" required><?php foreach ($clients as $c) { ?><option value="<?= $c['id'] ?? $c->id ?>" <?= ($edit && ($edit['client_id'] == ($c['id'] ?? $c->id))) ? 'selected' : '' ?>><?= esc($c['name'] ?? $c->name) ?></option><?php } ?></select>
<label>Name</label><input name="name" value="<?= esc($edit['name'] ?? '') ?>" required>
<label>Type</label><input name="type" value="<?= esc($edit['type'] ?? '') ?>">
<label>Brand</label><input name="brand" value="<?= esc($edit['brand'] ?? '') ?>">
<label>Model</label><input name="model" value="<?= esc($edit['model'] ?? '') ?>">
<label>Serial</label><input name="serial_number" value="<?= esc($edit['serial_number'] ?? '') ?>">
<label>Location</label><input name="location" value="<?= esc($edit['location'] ?? '') ?>">
<label>Status</label><select name="status"><?php foreach (['active','faulty','under_repair','retired'] as $s) { ?><option <?= ($edit['status'] ?? 'active') === $s ? 'selected' : '' ?>><?= $s ?></option><?php } ?></select>
<button class="btn">Save</button></form></div>
</div>
<?= view('itsupport/_footer') ?>