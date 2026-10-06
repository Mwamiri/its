<?= view('itsupport/_header') ?>
<?php $tok = \App\Models\SettingModel::get('ical_token', ''); ?>
<div class="card"><div class="section-heading"><h2>Billing &amp; retainers</h2>
<form method="get" action="<?= base_url('its-billing') ?>"><label for="month">Month</label><input id="month" type="month" name="month" value="<?= esc($month) ?>"><button class="btn">Show</button> <a class="button secondary" href="<?= base_url('its-billing-csv?month=' . $month) ?>">Download CSV</a></form></div>
<p class="muted">Amount due = billable hours beyond the client's retainer hours, times the hourly rate. Hours come from time logged on tickets.</p>
<div class="tbl"><table><thead><tr><th>Client</th><th>Retainer h</th><th>Rate</th><th>Billable h</th><th>Total h</th><th>Overage h</th><th>Amount due</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $r) { ?><tr><form method="post" action="<?= base_url('its-billing-save/' . $r['id'] . '?month=' . $month) ?>"><?= csrf_field() ?>
<td><?= esc($r['name']) ?></td><td><input aria-label="Retainer hours" type="number" step="0.5" min="0" name="retainer_hours" value="<?= esc($r['retainer']) ?>" style="width:90px"></td><td><input aria-label="Hourly rate" type="number" step="0.01" min="0" name="hourly_rate" value="<?= esc($r['rate']) ?>" style="width:100px"></td>
<td><?= $r['billable_h'] ?></td><td><?= $r['total_h'] ?></td><td><?= $r['over_h'] ?></td><td><strong><?= number_format($r['amount'], 2) ?></strong></td><td><button class="btn">Save</button></td></form></tr><?php } ?>
<?php if (!$rows) { ?><tr><td colspan="8" class="muted">No clients yet.</td></tr><?php } ?></tbody></table></div></div>
<div class="card"><h3>Calendar feed (iCal)</h3>
<p class="muted">Subscribe in Outlook, Google Calendar or Apple Calendar to see open ticket due dates and visits. Anyone with the link can read it, so keep it private.</p>
<?php if ($tok) { ?><p><code><?= esc(base_url('its-calendar.ics?token=' . $tok)) ?></code></p><?php } ?>
<form method="post" action="<?= base_url('its-calendar-token') ?>"><?= csrf_field() ?><button class="btn"><?= $tok ? 'Regenerate link' : 'Create link' ?></button></form></div>
<?= view('itsupport/_footer') ?>