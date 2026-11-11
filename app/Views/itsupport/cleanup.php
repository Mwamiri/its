<?= view('itsupport/_header') ?>
<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px">
<h2 style="margin:0">System Cleanup</h2>
<span class="muted">Nothing is deleted until you press Clean.</span>
</div>

<div class="grid grid-2" style="align-items:start">
<form method="post" action="<?= base_url('its-cleanup-run') ?>" onsubmit="return confirm('Delete the selected files? This cannot be undone (backups are not affected).')">
<?= csrf_field() ?>
<div class="card">
<h3>Junk found <span class="badge <?= $totalFiles ? 'orange' : 'green' ?>"><?= $totalFiles ? number_format($totalFiles) . ' files · ' . number_format($totalBytes / 1048576, 2) . ' MB' : 'all clean' ?></span></h3>
<?php foreach ($groups as $key => $g) { $n = $g['count']; ?>
<label style="display:flex;align-items:flex-start;gap:10px;padding:12px 0;border-bottom:1px solid #e9eef5;opacity:<?= $n ? '1' : '.5' ?>;cursor:<?= $n ? 'pointer' : 'default' ?>">
<input type="checkbox" name="groups[]" value="<?= esc($key) ?>" <?= ($n && $key !== 'logs') ? 'checked' : '' ?> <?= $n ? '' : 'disabled' ?> style="width:auto;margin:0;margin-top:2px">
<span>
<span style="font-weight:600;display:block"><?= esc($g['label']) ?></span>
<span class="muted"><?= number_format($n) ?> file(s) · <?= number_format($g['bytes'] / 1024, 0) ?> KB</span>
</span>
</label>
<?php } ?>
<div style="margin-top:16px"><button class="btn" <?= $totalFiles ? '' : 'disabled' ?>><i class="bi bi-trash3"></i> Clean selected</button></div>
</div>
</form>

<div>
<div class="card">
<h3><i class="bi bi-shield-check" style="color:var(--brand)"></i> Clean uploads</h3>
<p class="muted">Every uploaded file is checked before it is kept:</p>
<ul class="muted" style="padding-left:18px;line-height:2">
<li>Real content must match its extension</li>
<li>Images are processed to strip hidden metadata and GPS data</li>
<li>Files with embedded scripts, macros, or auto-run actions are refused</li>
<li>All refused uploads are recorded in the audit trail</li>
</ul>
</div>
<div class="card" style="margin-top:0">
<h3>Other tools</h3>
<div style="display:grid;gap:8px">
<a class="button secondary" href="<?= base_url('its-logs') ?>"><i class="bi bi-file-text"></i> View error logs</a>
<a class="button secondary" href="<?= base_url('its-health') ?>"><i class="bi bi-heart-pulse"></i> Health check</a>
<a class="button secondary" href="<?= base_url('its-admin-backup') ?>"><i class="bi bi-database-down"></i> Download database backup</a>
</div>
</div>
</div>
</div>
<?= view('itsupport/_footer') ?>
