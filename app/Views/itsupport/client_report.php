<?= view('itsupport/_header') ?>
<?php
$resolved = ($byStatus['completed'] ?? 0) + ($byStatus['closed'] ?? 0);
$assetTotal = array_sum(array_map('intval', array_column($assetStatus, 'n')));
?>
<div class="doc-actions no-print">
  <a class="button secondary" href="<?= base_url('its-clients-view/' . $client['id']) ?>">Back</a>
  <form method="get" action="<?= base_url('its-clients-report/' . $client['id']) ?>" style="display:flex;flex-wrap:wrap;gap:6px;align-items:center;margin:0">
    <label for="cr-from" class="muted" style="margin:0">From</label><input id="cr-from" type="date" name="from" value="<?= esc($from) ?>" style="margin:0;width:auto">
    <label for="cr-to" class="muted" style="margin:0">To</label><input id="cr-to" type="date" name="to" value="<?= esc($to) ?>" style="margin:0;width:auto">
    <button class="btn secondary">Update</button></form>
  <button class="btn" type="button" onclick="window.print()">Print / save as PDF</button>
</div>
<article class="doc">
<?= view('itsupport/_doc_header', ['docTitle' => 'Service summary', 'docRef' => $from . ' to ' . $to, 'docTo' => $client]) ?>
<table><tr><th>Tickets raised</th><th>Resolved</th><th>Still open</th><th>Time logged</th><th>Assets</th><th>Satisfaction</th><?php if ($showCosts) { ?><th>Parts + labour</th><?php } ?></tr>
<tr><td><?= count($tickets) ?></td><td><?= $resolved ?></td><td><?= count($tickets) - $resolved ?></td><td><?= intdiv($minutes, 60) ?>h <?= $minutes % 60 ?>m</td><td><?= $assetTotal ?></td><td><?= $avgRating !== null ? esc((string) $avgRating) . ' / 5' : '-' ?></td><?php if ($showCosts) { ?><td><?= number_format($cost, 2) ?></td><?php } ?></tr></table>
<?php if ($cfDefs) { ?><h3>Account details</h3><table><?php foreach ($cfDefs as $d) { ?><tr><th style="width:30%"><?= esc($d['label']) ?></th><td><?= esc(\App\Libraries\CustomFields::display($d, $cf[$d['field_key']] ?? '')) ?: '-' ?></td></tr><?php } ?></table><?php } ?>
<h3>Tickets in this period</h3>
<table><thead><tr><th>Ticket</th><th>Opened</th><th>Subject</th><th>Priority</th><th>Status</th><th>Resolved</th></tr></thead><tbody>
<?php foreach ($tickets as $t) { ?><tr><td><?= esc($t['ticket_number']) ?></td><td><?= esc(substr((string) $t['created_at'], 0, 10)) ?></td><td><?= esc($t['subject']) ?></td><td><?= esc($t['priority']) ?></td><td><?= esc(str_replace('_', ' ', $t['status'])) ?></td><td><?= esc(substr((string) ($t['resolved_at'] ?? ''), 0, 10) ?: '-') ?></td></tr><?php } ?>
<?php if (!$tickets) { ?><tr><td colspan="6">No tickets in this period.</td></tr><?php } ?></tbody></table>
<?php if ($tasks) { ?><h3>Work carried out</h3>
<table><thead><tr><th>Item</th><th>Complaint</th><th>Action taken</th><th>Recommendation</th><?php if ($showCosts) { ?><th>Cost</th><?php } ?></tr></thead><tbody>
<?php foreach ($tasks as $k) { ?><tr><td><?= esc($k['item']) ?><?= $k['serial_number'] ? '<br><small>' . esc($k['serial_number']) . '</small>' : '' ?></td><td><?= nl2br(esc((string) $k['complaint'])) ?></td><td><?= nl2br(esc((string) $k['action_taken'])) ?></td><td><?= nl2br(esc((string) $k['recommendation'])) ?></td><?php if ($showCosts) { ?><td><?= number_format((float) $k['parts_cost'] + (float) $k['labor_cost'], 2) ?></td><?php } ?></tr><?php } ?>
</tbody></table><?php } ?>
<?= view('itsupport/_doc_footer') ?>
</article>
<?= view('itsupport/_footer') ?>
