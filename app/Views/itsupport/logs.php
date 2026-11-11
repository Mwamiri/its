<?= view('itsupport/_header') ?>
<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px">
<h2 style="margin:0">Error Logs</h2>
<div style="display:flex;gap:8px;flex-wrap:wrap">
<?php if ($current) { ?>
<a class="button secondary" href="<?= base_url('its-logs-download?file=' . urlencode($current)) ?>"><i class="bi bi-download"></i> Download</a>
<?php } ?>
<form method="post" action="<?= base_url('its-logs-clear') ?>" onsubmit="return confirm('Remove all log files except today\'s?')"><?= csrf_field() ?><button class="button secondary"><i class="bi bi-trash3"></i> Clear old logs</button></form>
</div>
</div>

<div class="grid grid-2" style="align-items:start">
<div class="card" style="grid-column:1/3">
<form method="get" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px">
<select name="file" onchange="this.form.submit()" style="width:auto;margin:0">
<?php foreach ($files as $f) { ?><option value="<?= esc($f) ?>" <?= $f === $current ? 'selected' : '' ?>><?= esc($f) ?></option><?php } ?>
<?php if (!$files) { ?><option>No log files</option><?php } ?>
</select>
<select name="level" onchange="this.form.submit()" style="width:auto;margin:0">
<option value="">All levels</option>
<?php foreach (['ERROR', 'CRITICAL', 'WARNING', 'INFO', 'DEBUG'] as $lv) { ?>
<option value="<?= $lv ?>" <?= $level === $lv ? 'selected' : '' ?>><?= $lv ?></option>
<?php } ?>
</select>
<span class="muted" style="align-self:center"><?= count($entries) ?> entries shown</span>
</form>

<?php if (!$entries && !$files) { ?>
<p class="muted">No log files found. CodeIgniter writes logs to <code>writable/logs/</code> when errors occur.</p>
<?php } elseif (!$entries) { ?>
<p class="muted">No entries at this level in the selected file.</p>
<?php } else { ?>
<div class="tbl"><table>
<tr><th style="width:90px">Level</th><th style="width:155px">Time</th><th>Message</th></tr>
<?php foreach ($entries as $e) {
    $cls = match($e['level']) { 'ERROR','CRITICAL' => 'orange', 'WARNING' => 'orange', default => 'gray' };
?>
<tr>
<td><span class="badge <?= $cls ?>"><?= esc($e['level']) ?></span></td>
<td class="muted" style="white-space:nowrap;font-size:12px"><?= esc($e['time']) ?></td>
<td style="font-size:12px;font-family:monospace;word-break:break-all;white-space:pre-wrap"><?= esc($e['message']) ?></td>
</tr>
<?php } ?>
</table></div>
<?php } ?>
</div>
</div>
<?= view('itsupport/_footer') ?>
