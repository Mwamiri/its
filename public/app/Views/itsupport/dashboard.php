<?= view('itsupport/_header') ?>
<div class="grid">
<div class="card"><h3>Clients</h3><h2><?= $clients ?></h2></div>
<div class="card"><h3>Assets</h3><h2><?= $assets ?></h2></div>
<div class="card"><h3>Open Tickets</h3><h2><?= $openTickets ?></h2></div>
</div>
<div class="grid">
<div class="card"><h3>Pending Quotes</h3><h2><?= $pendingQuotes ?></h2></div>
<div class="card"><h3>Awaiting Approval</h3><h2><?= $awaitingApproval ?></h2></div>
<div class="card"><h3>Project Cost</h3><h2><?= number_format($projectCost,2) ?></h2></div>
</div>
<div class="card"><a class="button" href="<?= base_url('its-tickets') ?>">Tickets</a> <a class="button" href="<?= base_url('its-clients') ?>">Clients</a> <a class="button" href="<?= base_url('its-help') ?>">Help</a></div>
<?= view('itsupport/_footer') ?>