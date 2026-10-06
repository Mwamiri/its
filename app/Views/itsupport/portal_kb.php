<?= view('itsupport/_header') ?>
<div class="card"><h2>Help articles</h2>
<form method="get" action="<?= base_url('its-portal-kb') ?>"><label for="q">Search for a solution</label><input id="q" name="q" value="<?= esc($q) ?>"><button class="btn">Search</button></form></div>
<?php foreach ($articles as $a) { ?><div class="card"><details><summary><strong><?= esc($a['title']) ?></strong></summary><p><?= nl2br(esc($a['body'])) ?></p></details></div><?php } ?>
<?php if (!$articles) { ?><div class="card"><p class="muted">No matching articles. <a href="<?= base_url('its-portal-new') ?>">Report an issue</a>.</p></div><?php } ?>
<?= view('itsupport/_footer') ?>