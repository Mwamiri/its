<?= view('itsupport/_header') ?>
<div class="card"><h2><?= esc($asset['name']) ?></h2>
<p><strong>Serial:</strong> <?= esc($asset['serial_number']) ?><br><strong>Location:</strong> <?= esc($asset['location']) ?><br><strong>Status:</strong> <?= esc($asset['status']) ?></p>
<img src="<?= base_url('its-qr/' . $asset['id']) ?>" style="width:200px;height:200px;background:#fff;border:1px solid #dbe2ea;"></div>
<div class="card"><h3>History</h3>
<?php foreach ($history as $h) { ?><p><strong><?= esc($h['ticket_number']) ?> - <?= esc($h['item']) ?></strong><br><?= esc($h['action_taken']) ?></p><?php } ?>
</div>
<?= view('itsupport/_footer') ?>