<?= view('itsupport/_header') ?>
<?php
$cfDefs = \App\Libraries\CustomFields::defs('clients');
$statusColor = static fn(string $s) => in_array($s, ['completed', 'closed', 'approved', 'active'], true) ? 'green' : (in_array($s, ['faulty', 'rejected', 'under_repair'], true) ? 'orange' : 'blue');
?>
<section class="cv-head card">
  <div class="cv-id"><?php if (!empty($client['logo_path'])) { ?><img src="<?= esc(base_url($client['logo_path'])) ?>" alt=""><?php } else { ?><span class="cv-mono" aria-hidden="true"><?= esc(mb_strtoupper(mb_substr($client['name'], 0, 1))) ?></span><?php } ?>
    <div><p class="eyebrow">Client</p><h2><?= esc($client['name']) ?></h2>
    <p class="muted"><?= esc(implode(' · ', array_filter([$client['contact_person'], $client['phone'], $client['email']]))) ?></p></div></div>
  <div class="quick-actions">
    <a class="button" href="<?= base_url('its-tickets?client_id=' . $client['id']) ?>">New ticket</a>
    <?php if ($canAssets) { ?><a class="button secondary" href="<?= base_url('its-assets?client_id=' . $client['id']) ?>">Add asset</a><?php } ?>
    <a class="button secondary" href="<?= base_url('its-clients-edit/' . $client['id']) ?>">Edit</a>
  </div>
</section>
<section class="grid cv-kpis">
  <div class="card metric"><span class="metric-label">Open tickets</span><strong><?= (int) $open ?></strong></div>
  <div class="card metric"><span class="metric-label">Assets</span><strong><?= count($assets) ?></strong></div>
  <div class="card metric"><span class="metric-label">Warranty ending (60 days)</span><strong><?= (int) $warrantySoon ?></strong></div>
  <div class="card metric"><span class="metric-label">Portal users</span><strong><?= count($portalUsers) ?></strong></div>
</section>
<div class="grid grid-2">
<div class="card"><div class="section-heading"><h3>Reports</h3></div>
  <p class="muted">Branded with your logo and letterhead. Print or save as PDF from the report page.</p>
  <div class="quick-actions">
    <?php if ($canAssets) { ?><a class="button" href="<?= base_url('its-clients-assets-report/' . $client['id']) ?>">Asset register</a><a class="button secondary" href="<?= base_url('its-clients-assets-report/' . $client['id'] . '?format=csv') ?>">Asset register (CSV)</a><?php } ?>
    <a class="button" href="<?= base_url('its-clients-report/' . $client['id']) ?>">Service summary</a>
  </div>
</div>
<div class="card"><h3>Details</h3>
  <table><tr><th>Address</th><td><?= nl2br(esc($client['address'] ?: '-')) ?></td></tr>
  <?php foreach ($cfDefs as $d) { $v = \App\Libraries\CustomFields::display($d, $cf[$d['field_key']] ?? ''); ?><tr><th><?= esc($d['label']) ?></th><td><?= $v === '' ? '<span class="muted">-</span>' : ($d['type'] === 'url' ? '<a href="' . esc($v) . '" target="_blank" rel="noopener">' . esc($v) . '</a>' : nl2br(esc($v))) ?></td></tr><?php } ?>
  <?php if ($client['notes']) { ?><tr><th>Notes</th><td><?= nl2br(esc($client['notes'])) ?></td></tr><?php } ?>
  <tr><th>Portal access</th><td><?php foreach ($portalUsers as $pu) { ?><span class="badge <?= (int) $pu['active'] ? 'green' : 'gray' ?>"><?= esc($pu['name'] ?: $pu['username']) ?></span> <?php } ?><?= $portalUsers ? '' : '<span class="muted">None. Add a user with the Client role in Admin → Users.</span>' ?></td></tr></table>
</div>
</div>
<?php if ($canAssets) { ?>
<div class="card"><div class="section-heading"><h3>Equipment (<?= count($assets) ?>)</h3><a class="button secondary" href="<?= base_url('its-assets?client_id=' . $client['id']) ?>">Add asset</a></div>
<div class="tbl"><table><tr><th>Asset</th><th>Type</th><th>Serial</th><th>Location</th><th>Warranty</th><th>Status</th></tr>
<?php foreach ($assets as $a) { $exp = !empty($a['warranty_until']) && $a['warranty_until'] < date('Y-m-d'); ?><tr><td><a href="<?= base_url('its-assets-view/' . $a['id']) ?>"><?= esc($a['name']) ?></a><br><span class="muted"><?= esc(trim($a['brand'] . ' ' . $a['model'])) ?></span></td><td><?= esc($a['type']) ?></td><td><?= esc($a['serial_number']) ?></td><td><?= esc($a['location']) ?></td><td><?= esc($a['warranty_until'] ?: '-') ?><?= $exp ? ' <span class="badge orange">expired</span>' : '' ?></td><td><span class="badge <?= $statusColor((string) $a['status']) ?>"><?= esc(str_replace('_', ' ', $a['status'])) ?></span></td></tr><?php } ?>
<?php if (!$assets) { ?><tr><td colspan="6" class="muted">No equipment recorded for this client yet.</td></tr><?php } ?></table></div></div>
<?php } ?>
<div class="grid grid-2">
<div class="card"><h3>Recent tickets</h3><div class="tbl"><table><tr><th>Ticket</th><th>Subject</th><th>Status</th><th>Opened</th></tr>
<?php foreach ($tickets as $t) { ?><tr><td><a href="<?= base_url('its-tickets-view/' . $t['id']) ?>"><?= esc($t['ticket_number']) ?></a></td><td><?= esc($t['subject']) ?></td><td><span class="badge <?= $statusColor((string) $t['status']) ?>"><?= esc(str_replace('_', ' ', $t['status'])) ?></span></td><td><?= esc(substr((string) $t['created_at'], 0, 10)) ?></td></tr><?php } ?>
<?php if (!$tickets) { ?><tr><td colspan="4" class="muted">No tickets yet.</td></tr><?php } ?></table></div></div>
<div class="card"><h3>Quotes</h3><div class="tbl"><table><tr><th>Quote</th><th>Subject</th><th>Total</th><th>Status</th></tr>
<?php foreach ($quotes as $q) { ?><tr><td><a href="<?= base_url('its-quotes-view/' . $q['id']) ?>"><?= esc($q['quote_number']) ?></a></td><td><?= esc($q['subject']) ?></td><td><?= number_format((float) $q['total'], 2) ?></td><td><span class="badge <?= $statusColor((string) $q['status']) ?>"><?= esc($q['status']) ?></span></td></tr><?php } ?>
<?php if (!$quotes) { ?><tr><td colspan="4" class="muted">No quotes.</td></tr><?php } ?></table></div></div>
</div>
<style>
.cv-head{display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:14px}.cv-id{display:flex;gap:14px;align-items:center}.cv-id img{max-width:84px;max-height:56px;object-fit:contain}.cv-id h2,.cv-id p{margin:0}
.cv-mono{display:grid;place-items:center;width:52px;height:52px;border-radius:14px;background:var(--brand);color:#fff;font-size:22px;font-weight:800}
.cv-kpis{grid-template-columns:repeat(auto-fit,minmax(min(100%,180px),1fr))}.metric{display:flex;flex-direction:column;gap:4px}.metric strong{font-size:1.7rem}.metric-label{font-size:12px;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:.05em}
.quick-actions{display:flex;flex-wrap:wrap;gap:8px}
</style>
<?= view('itsupport/_footer') ?>
