<?= view('itsupport/_header') ?>
<?php
$today = date('Y-m-d');
$counts = array_count_values(array_map(static fn($a) => (string) $a['status'], $assets));
$expired = count(array_filter($assets, static fn($a) => !empty($a['warranty_until']) && $a['warranty_until'] < $today));
$soon = count(array_filter($assets, static fn($a) => !empty($a['warranty_until']) && $a['warranty_until'] >= $today && $a['warranty_until'] <= date('Y-m-d', strtotime('+60 days'))));
?>
<div class="doc-actions no-print">
  <a class="button secondary" href="<?= esc($back) ?>">Back</a>
  <button class="btn" type="button" onclick="window.print()">Print / save as PDF</button>
  <?php if (empty($noCsv)) { ?><a class="button secondary" href="<?= esc($self . '?format=csv' . ($status ? '&status=' . $status : '')) ?>">Download CSV</a><?php } ?>
  <form method="get" action="<?= esc($self) ?>" style="display:flex;gap:6px;align-items:center;margin:0"><label for="ar-st" class="muted" style="margin:0">Status</label><select id="ar-st" name="status" onchange="this.form.submit()" style="margin:0;width:auto"><option value="">All</option><?php foreach (['active', 'under_repair', 'faulty', 'retired'] as $s) { ?><option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>><?= esc(str_replace('_', ' ', $s)) ?></option><?php } ?></select></form>
</div>
<article class="doc">
<?= view('itsupport/_doc_header', ['docTitle' => 'Asset register', 'docRef' => count($assets) . ' item' . (count($assets) === 1 ? '' : 's') . ($status ? ' · ' . str_replace('_', ' ', $status) : ''), 'docTo' => $client]) ?>
<table style="margin-bottom:14px"><tr><th>Total</th><th>Active</th><th>Under repair</th><th>Faulty</th><th>Retired</th><th>Warranty expired</th><th>Warranty ending ≤ 60 days</th></tr>
<tr><td><?= count($assets) ?></td><td><?= (int) ($counts['active'] ?? 0) ?></td><td><?= (int) ($counts['under_repair'] ?? 0) ?></td><td><?= (int) ($counts['faulty'] ?? 0) ?></td><td><?= (int) ($counts['retired'] ?? 0) ?></td><td><?= $expired ?></td><td><?= $soon ?></td></tr></table>
<table>
<thead><tr><th>#</th><th>Asset</th><th>Make / model</th><th>Serial</th><th>Specification</th><th>Location</th><th>Warranty</th><th>Status</th><?php foreach ($fields as $f) { ?><th><?= esc($f['label']) ?></th><?php } ?></tr></thead>
<tbody>
<?php foreach ($assets as $i => $a) { $spec = implode(' · ', array_filter([$a['cpu'] ?? '', $a['ram'] ?? '', $a['storage'] ?? '', $a['os'] ?? ''])); ?>
<tr><td><?= $i + 1 ?></td><td><strong><?= esc($a['name']) ?></strong><?= $a['type'] ? '<br>' . esc($a['type']) : '' ?><?= !empty($a['hostname']) ? '<br><small>' . esc($a['hostname']) . '</small>' : '' ?></td>
<td><?= esc(trim($a['brand'] . ' ' . $a['model'])) ?></td><td><?= esc($a['serial_number']) ?></td><td><?= esc($spec) ?></td><td><?= esc($a['location']) ?></td>
<td><?= esc($a['warranty_until'] ?: '-') ?><?= !empty($a['warranty_until']) && $a['warranty_until'] < $today ? ' (expired)' : '' ?></td><td><?= esc(str_replace('_', ' ', $a['status'])) ?></td>
<?php foreach ($fields as $f) { ?><td><?= esc(\App\Libraries\CustomFields::display($f, $values[$a['id']][$f['field_key']] ?? '')) ?></td><?php } ?></tr>
<?php } ?>
<?php if (!$assets) { ?><tr><td colspan="<?= 8 + count($fields) ?>">No equipment recorded.</td></tr><?php } ?>
</tbody></table>
<?= view('itsupport/_doc_footer') ?>
</article>
<?= view('itsupport/_footer') ?>
