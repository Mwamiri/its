<?= view('itsupport/_header') ?>
<h2 class="tw-text-2xl tw-font-bold">Import from Excel or CSV</h2>
<?php if ($report) { ?><div class="card"><strong><?= esc($report['type']) ?>:</strong> <?= (int) $report['ok'] ?> imported, <?= (int) $report['skip'] ?> skipped as duplicates, <?= count($report['errs']) + $report['more'] ?> rejected.
<?php if ($report['errs']) { ?><ul><?php foreach ($report['errs'] as $e) { ?><li><?= esc($e) ?></li><?php } ?><?php if ($report['more']) { ?><li>and <?= (int) $report['more'] ?> more</li><?php } ?></ul><?php } ?></div><?php } ?>
<div class="grid grid-2">
<div class="card"><h3>1. Download a template</h3><p>Open it in Excel, fill the rows, save as .xlsx or .csv. Keep the header row unchanged.</p>
<?php foreach ($sets as $k => $s) { ?><p><a class="button secondary" href="<?= base_url('its-import-template/' . $k) ?>"><?= esc($s['label']) ?> template</a> <small>Columns: <?= esc(implode(', ', $s['cols'])) ?></small></p><?php } ?>
<p><small>Assets: the <b>client</b> column must match an existing client name exactly. Dates use YYYY-MM-DD. Existing clients (by name) and assets (by serial number) are skipped.</small></p></div>
<div class="card"><h3>2. Upload</h3>
<form method="post" action="<?= base_url('its-import-run') ?>" enctype="multipart/form-data"><?= csrf_field() ?>
<label>Data type</label><select name="type" id="imp-type" required><?php foreach ($sets as $k => $s) { ?><option value="<?= $k ?>"><?= esc($s['label']) ?></option><?php } ?></select>
<div id="imp-sess" style="display:none"><label>Training session</label><select name="session_id"><option value="">Choose session</option><?php foreach ($sessions as $s) { ?><option value="<?= $s['id'] ?>"><?= esc($s['session_date'] . ' - ' . $s['title']) ?></option><?php } ?></select></div>
<label>File (.xlsx or .csv, up to 5 MB)</label><input type="file" name="file" accept=".csv,.xlsx,.txt" required>
<button type="submit">Import</button></form></div></div>
<script>(function(){var t=document.getElementById('imp-type'),s=document.getElementById('imp-sess');function f(){s.style.display=t.value==='attendees'?'block':'none'}t.onchange=f;f()})();</script>
<?= view('itsupport/_footer') ?>