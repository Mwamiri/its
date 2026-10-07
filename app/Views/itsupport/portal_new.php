<?= view('itsupport/_header') ?>
<div class="card" style="max-width:760px;margin-inline:auto"><p class="eyebrow">New request</p><h2>Report an issue</h2>
<p class="muted">Tell us what is wrong. You can follow progress and reply on the request page.</p>
<form method="post" action="<?= base_url('its-portal-new') ?>"><?= csrf_field() ?>
<?php if (!empty($assets)) { ?><label for="asset">Which device? (optional)</label><select id="asset" name="asset_id"><option value="0">Not about a specific device</option><?php foreach ($assets as $a) { ?><option value="<?= (int) $a['id'] ?>" <?= (int) (old('asset_id') ?? $preAsset) === (int) $a['id'] ? 'selected' : '' ?>><?= esc($a['name'] . ($a['serial_number'] ? ' · ' . $a['serial_number'] : '') . ($a['location'] ? ' · ' . $a['location'] : '')) ?></option><?php } ?></select><?php } ?>
<label for="subject">Subject</label><input id="subject" name="subject" maxlength="190" required value="<?= esc(old('subject')) ?>" placeholder="e.g. Printer in Accounts is jamming">
<label for="description">What is happening?</label><textarea id="description" name="description" maxlength="5000" rows="6" required placeholder="What you see, any error message, when it started"><?= esc(old('description')) ?></textarea>
<div class="field"><label>Urgency</label><div class="seg" role="radiogroup" aria-label="Urgency"><?php foreach (['low' => 'Low', 'medium' => 'Normal', 'high' => 'High'] as $v => $l) { ?><input type="radio" name="priority" id="u-<?= $v ?>" value="<?= $v ?>" <?= (old('priority') ?? 'medium') === $v ? 'checked' : '' ?>><label for="u-<?= $v ?>"><?= $l ?></label><?php } ?></div></div>
<div class="form-actions"><button class="btn">Submit request</button><a class="button secondary" href="<?= base_url('its-portal') ?>">Cancel</a></div></form></div>
<?= view('itsupport/_footer') ?>
