<?= view('itsupport/_header') ?>
<div class="tw-flex tw-flex-wrap tw-items-center tw-gap-2 tw-mb-4">
<h2 class="tw-m-0 tw-mr-auto tw-text-2xl tw-font-bold">Assets <span class="tw-text-muted tw-text-base">(<?= count($assets) ?>)</span></h2>
<input id="row-filter" type="search" placeholder="Filter assets" aria-label="Filter assets" class="tw-w-56">
</div>
<div class="grid grid-2">
<div class="card tbl"><table id="filter-table"><tr><th>Asset</th><th>Client</th><th>Serial</th><th>QR</th><th></th></tr>
<?php foreach ($assets as $a) { ?><tr><td><?= esc($a['name']) ?></td><td><?= esc($a['client_name']) ?></td><td><?= esc($a['serial_number']) ?></td><td><a href="<?= base_url('its-qr/' . $a['id']) ?>" target="_blank">QR</a></td><td><a class="button secondary" href="<?= base_url('its-assets-view/' . $a['id']) ?>">Open</a></td></tr><?php } ?>
</table></div>
<div class="card"><h3><?= $edit ? 'Edit' : 'Add' ?> Asset</h3>
<form method="post" action="<?= base_url('its-assets-save/' . ($edit['id'] ?? '')) ?>"><?= csrf_field() ?>
<label>Client</label><select name="client_id" required><?php foreach ($clients as $c) { ?><option value="<?= $c['id'] ?>" <?= ($edit && ($edit['client_id'] == ($c['id']))) ? 'selected' : '' ?>><?= esc($c['name']) ?></option><?php } ?></select>
<label>Name</label><input name="name" value="<?= esc($edit['name'] ?? '') ?>" required>
<label>Type</label><input name="type" value="<?= esc($edit['type'] ?? '') ?>">
<label>Brand</label><input name="brand" value="<?= esc($edit['brand'] ?? '') ?>">
<label>Model</label><input name="model" value="<?= esc($edit['model'] ?? '') ?>">
<label>Serial</label><input name="serial_number" value="<?= esc($edit['serial_number'] ?? '') ?>">
<div class="grid grid-2"><div><label>Hostname</label><input name="hostname" value="<?= esc($edit['hostname'] ?? '') ?>"></div><div><label>CPU</label><input name="cpu" placeholder="e.g. Core i5-1135G7" value="<?= esc($edit['cpu'] ?? '') ?>"></div>
<div><label>RAM</label><input name="ram" placeholder="e.g. 8GB DDR4" value="<?= esc($edit['ram'] ?? '') ?>"></div><div><label>Storage</label><input name="storage" placeholder="e.g. 256GB SSD" value="<?= esc($edit['storage'] ?? '') ?>"></div>
<div><label>Operating system</label><input name="os" placeholder="e.g. Windows 11 Pro" value="<?= esc($edit['os'] ?? '') ?>"></div><div><label>Purchase date</label><input type="date" name="purchase_date" value="<?= esc($edit['purchase_date'] ?? '') ?>"></div>
<div><label>Warranty until</label><input type="date" name="warranty_until" value="<?= esc($edit['warranty_until'] ?? '') ?>"></div></div>
<label>Location</label><input name="location" value="<?= esc($edit['location'] ?? '') ?>">
<label>Status</label><select name="status"><?php foreach (['active','faulty','under_repair','retired'] as $s) { ?><option <?= ($edit['status'] ?? 'active') === $s ? 'selected' : '' ?>><?= $s ?></option><?php } ?></select>
<button class="btn">Save</button></form></div>
</div>
<script>document.getElementById('row-filter').addEventListener('input',function(){const q=this.value.toLowerCase();document.querySelectorAll('#filter-table tr').forEach((r,i)=>{if(i)r.hidden=!r.textContent.toLowerCase().includes(q);});});</script>
<?= view('itsupport/_footer') ?>