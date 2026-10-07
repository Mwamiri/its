<?= view('itsupport/_header') ?>
<?php
$ev = static fn(string $k, $d = '') => old($k) ?? ($edit[$k] ?? $d);
$selClient = (int) (old('client_id') ?? ($edit['client_id'] ?? $filters['client_id']));
$qs = http_build_query(array_filter($filters));
$today = date('Y-m-d');
$badge = ['active' => 'green', 'under_repair' => 'orange', 'faulty' => 'orange', 'retired' => 'gray'];
?>
<div class="section-heading"><div><p class="eyebrow">Equipment</p><h2>Assets <span class="muted">(<?= count($assets) ?>)</span></h2></div>
<div class="quick-actions"><a class="button secondary" href="<?= base_url('its-assets-csv' . ($qs ? '?' . $qs : '')) ?>">Export CSV</a><?php if ($filters['client_id']) { ?><a class="button secondary" href="<?= base_url('its-clients-assets-report/' . $filters['client_id']) ?>">Branded register</a><?php } ?><a class="button secondary" href="<?= base_url('its-import') ?>">Bulk import</a></div></div>
<form method="get" action="<?= base_url('its-assets') ?>" class="card filter-bar">
<div><label for="f-client">Client</label><select id="f-client" name="client_id"><option value="">All clients</option><?php foreach ($clients as $c) { ?><option value="<?= (int) $c['id'] ?>" <?= $filters['client_id'] === (int) $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option><?php } ?></select></div>
<div><label for="f-status">Status</label><select id="f-status" name="status"><option value="">Any</option><?php foreach (\App\Controllers\ItsAssets::STATUSES as $s) { ?><option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= esc(str_replace('_', ' ', $s)) ?></option><?php } ?></select></div>
<div><label for="f-war">Warranty</label><select id="f-war" name="warranty"><option value="">Any</option><option value="expiring" <?= $filters['warranty'] === 'expiring' ? 'selected' : '' ?>>Ending within 60 days</option><option value="expired" <?= $filters['warranty'] === 'expired' ? 'selected' : '' ?>>Expired</option></select></div>
<div><label for="f-q">Search</label><input id="f-q" type="search" name="q" value="<?= esc($filters['q']) ?>" placeholder="Name, serial, hostname, model"></div>
<div class="filter-actions"><button class="btn">Apply</button><?php if ($qs) { ?><a class="button secondary" href="<?= base_url('its-assets') ?>">Clear</a><?php } ?></div>
</form>
<div class="grid grid-2">
<div class="card tbl"><table><tr><th>Asset</th><th>Client</th><th>Serial</th><?php foreach ($listFields as $lf) { ?><th><?= esc($lf['label']) ?></th><?php } ?><th>Warranty</th><th>Status</th><th></th></tr>
<?php foreach ($assets as $a) { ?><tr>
<td><a href="<?= base_url('its-assets-view/' . $a['id']) ?>"><strong><?= esc($a['name']) ?></strong></a><br><span class="muted"><?= esc(trim($a['type'] . ' · ' . trim($a['brand'] . ' ' . $a['model']), ' ·')) ?></span></td>
<td><?php if ($a['client_id']) { ?><a href="<?= base_url('its-clients-view/' . $a['client_id']) ?>"><?= esc($a['client_name']) ?></a><?php } ?></td><td><?= esc($a['serial_number']) ?></td>
<?php foreach ($listFields as $lf) { ?><td><?= esc(\App\Libraries\CustomFields::display($lf, $cfValues[$a['id']][$lf['field_key']] ?? '')) ?></td><?php } ?>
<td><?= esc($a['warranty_until'] ?: '-') ?><?= !empty($a['warranty_until']) && $a['warranty_until'] < $today ? ' <span class="badge orange">expired</span>' : '' ?></td>
<td><span class="badge <?= $badge[$a['status']] ?? 'gray' ?>"><?= esc(str_replace('_', ' ', $a['status'])) ?></span></td>
<td style="white-space:nowrap"><a class="button secondary" href="<?= base_url('its-assets-edit/' . $a['id']) ?>">Edit</a> <a class="button secondary" href="<?= base_url('its-qr/' . $a['id']) ?>" target="_blank" rel="noopener" title="QR label">QR</a></td></tr><?php } ?>
<?php if (!$assets) { ?><tr><td colspan="<?= 6 + count($listFields) ?>" class="muted">No assets match these filters.</td></tr><?php } ?>
</table></div>
<div class="card" id="asset-form"><h3><?= $edit ? 'Edit ' . esc($edit['name']) : 'Add asset' ?></h3>
<form method="post" action="<?= base_url('its-assets-save' . ($edit ? '/' . $edit['id'] : '')) ?>"><?= csrf_field() ?>
<div class="form-grid">
<div class="field wide"><label for="a-client">Client</label><select id="a-client" name="client_id" required><option value="">Choose a client</option><?php foreach ($clients as $c) { ?><option value="<?= (int) $c['id'] ?>" <?= $selClient === (int) $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option><?php } ?></select></div>
<div class="field wide"><label for="a-name">Name</label><input id="a-name" name="name" maxlength="190" value="<?= esc($ev('name')) ?>" required placeholder="e.g. Reception PC"></div>
<div class="field"><label for="a-type">Type</label><input id="a-type" name="type" list="asset-types" maxlength="100" value="<?= esc($ev('type')) ?>"><datalist id="asset-types"><?php foreach (['Desktop', 'Laptop', 'Server', 'Printer', 'Router', 'Switch', 'Access point', 'Firewall', 'CCTV camera', 'NVR / DVR', 'UPS', 'Phone', 'Tablet', 'Monitor', 'Software licence'] as $t) { ?><option value="<?= esc($t) ?>"><?php } ?></datalist></div>
<div class="field"><label for="a-status">Status</label><select id="a-status" name="status"><?php foreach (\App\Controllers\ItsAssets::STATUSES as $s) { ?><option value="<?= $s ?>" <?= $ev('status', 'active') === $s ? 'selected' : '' ?>><?= esc(str_replace('_', ' ', $s)) ?></option><?php } ?></select></div>
<div class="field"><label for="a-brand">Brand</label><input id="a-brand" name="brand" maxlength="100" value="<?= esc($ev('brand')) ?>"></div>
<div class="field"><label for="a-model">Model</label><input id="a-model" name="model" maxlength="100" value="<?= esc($ev('model')) ?>"></div>
<div class="field"><label for="a-serial">Serial number</label><input id="a-serial" name="serial_number" maxlength="150" value="<?= esc($ev('serial_number')) ?>"></div>
<div class="field"><label for="a-host">Hostname</label><input id="a-host" name="hostname" maxlength="100" value="<?= esc($ev('hostname')) ?>"></div>
<div class="field"><label for="a-cpu">CPU</label><input id="a-cpu" name="cpu" maxlength="120" placeholder="e.g. Core i5-1135G7" value="<?= esc($ev('cpu')) ?>"></div>
<div class="field"><label for="a-ram">RAM</label><input id="a-ram" name="ram" maxlength="60" placeholder="e.g. 8GB DDR4" value="<?= esc($ev('ram')) ?>"></div>
<div class="field"><label for="a-sto">Storage</label><input id="a-sto" name="storage" maxlength="120" placeholder="e.g. 256GB SSD" value="<?= esc($ev('storage')) ?>"></div>
<div class="field"><label for="a-os">Operating system</label><input id="a-os" name="os" maxlength="80" placeholder="e.g. Windows 11 Pro" value="<?= esc($ev('os')) ?>"></div>
<div class="field"><label for="a-pd">Purchase date</label><input id="a-pd" type="date" name="purchase_date" value="<?= esc($ev('purchase_date')) ?>"></div>
<div class="field"><label for="a-wu">Warranty until</label><input id="a-wu" type="date" name="warranty_until" value="<?= esc($ev('warranty_until')) ?>"></div>
<div class="field wide"><label for="a-loc">Location</label><input id="a-loc" name="location" maxlength="190" value="<?= esc($ev('location')) ?>" placeholder="e.g. 2nd floor, Accounts office"></div>
</div>
<?= view('itsupport/_custom_fields', ['entity' => 'assets', 'values' => $editValues]) ?>
<label for="a-notes">Notes</label><textarea id="a-notes" name="notes" maxlength="5000"><?= esc($ev('notes')) ?></textarea>
<div class="form-actions"><button class="btn">Save asset</button><?php if (!$edit) { ?><button class="btn secondary" name="save_and_new" value="1">Save and add another</button><?php } else { ?><a class="button secondary" href="<?= base_url('its-assets-view/' . $edit['id']) ?>">Cancel</a><?php } ?></div>
</form></div>
</div>
<style>.filter-bar{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,170px),1fr));gap:4px 12px;align-items:end}.filter-bar label{font-size:12px;color:var(--muted);font-weight:700}.filter-bar select,.filter-bar input{margin:4px 0 0}.filter-actions{display:flex;gap:6px}.quick-actions{display:flex;flex-wrap:wrap;gap:8px}</style>
<?= view('itsupport/_footer') ?>
