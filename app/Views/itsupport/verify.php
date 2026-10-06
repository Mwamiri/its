<?= view('itsupport/_header') ?>
<div class="card" style="max-width:600px;margin:20px auto;"><h2>Verify Signed Report</h2>
<form method="post" action="<?= base_url('its-verify') ?>"><?= csrf_field() ?><input name="code" value="<?= esc($code) ?>" placeholder="Verification code"><button class="btn">Verify</button></form>
<?php if ($code && !$sig) { ?><p style="color:#dc2626;">No report found.</p><?php } ?>
<?php if ($sig) { ?><p style="color:#16a34a;"><strong>Valid.</strong> Ticket <?= esc($ticket['ticket_number']) ?>, client <?= esc($ticket['client_name']) ?>, signed by <?= esc($sig['signed_by_name']) ?></p><?php } ?>
</div>
<?= view('itsupport/_footer') ?>