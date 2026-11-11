<?= view('itsupport/_header') ?>
<!-- Progress modal -->
<style>#upd-modal:not([hidden]),#major-modal:not([hidden]){display:flex}</style>
<div id="upd-modal" hidden style="position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center;background:rgba(15,23,42,.55);backdrop-filter:blur(3px)">
  <div style="background:var(--paper);border-radius:18px;padding:30px 36px;min-width:340px;max-width:500px;width:92%;box-shadow:0 24px 64px rgba(0,0,0,.25)">
    <p id="upd-modal-title" style="margin:0 0 18px;font-size:1.05rem;font-weight:700;color:var(--ink)">Working…</p>
    <div style="height:10px;background:var(--line);border-radius:6px;overflow:hidden">
      <div id="upd-prog-fill" style="height:100%;background:var(--brand);border-radius:6px;transition:width .35s ease;width:0%"></div>
    </div>
    <p id="upd-prog-pct" style="margin:6px 0 0;font-size:12px;font-weight:700;color:var(--brand)">0%</p>
    <p id="upd-prog-msg" style="margin:4px 0 0;font-size:13px;color:var(--muted)">Starting…</p>
    <p id="upd-prog-steps" style="margin:14px 0 0;font-size:12px;color:var(--muted);display:flex;flex-wrap:wrap;gap:6px"></p>
  </div>
</div>
<!-- Major-update confirmation modal -->
<div id="major-modal" hidden style="position:fixed;inset:0;z-index:10000;align-items:center;justify-content:center;background:rgba(15,23,42,.6);backdrop-filter:blur(4px)">
  <div style="background:var(--paper);border-radius:18px;padding:32px 36px;min-width:320px;max-width:460px;width:92%;box-shadow:0 24px 64px rgba(0,0,0,.3)">
    <p style="margin:0 0 6px;font-size:1.1rem;font-weight:700;color:var(--ink)">⚠ Major version update</p>
    <p id="major-modal-desc" style="margin:0 0 20px;font-size:14px;color:var(--muted)">This is a major release and may include breaking changes. Make sure you have reviewed the release notes and created a backup before continuing.</p>
    <div style="display:flex;gap:10px;justify-content:flex-end">
      <button id="major-cancel" class="btn secondary" type="button">Cancel</button>
      <button id="major-confirm-btn" class="btn" type="button" style="background:#ef4444;border-color:#ef4444">Yes, install major update</button>
    </div>
  </div>
</div>

<div class="tw:grid tw:grid-cols-2 tw:gap-3 tw:md:grid-cols-4 tw:mb-4">
<?php foreach ([['App', $version], ['CodeIgniter', $frameworkVersion], ['PHP', $system['php']], ['Database', $system['rows']['Database']]] as [$k, $val]) { ?>
<div class="tw:rounded-2xl tw:border tw:border-line tw:bg-paper tw:p-4 tw:shadow-sm"><p class="tw:m-0 tw:text-xs tw:font-bold tw:uppercase tw:tracking-wider tw:text-muted"><?= esc($k) ?></p><p class="tw:m-0 tw:mt-1 tw:text-lg tw:font-bold tw:text-ink tw:break-words"><?= esc($val) ?></p></div>
<?php } ?>
</div>
<?php if (!empty($releaseNotes)) {
$tagStyle = ['feature' => ['green', 'New'], 'security' => ['orange', 'Security'], 'fix' => ['blue', 'Fix'], 'performance' => ['green', 'Reliability'], 'change' => ['gray', 'Change']];
$noteList = static function (array $items) use ($tagStyle): string {
    $h = '<ul class="rn-list">';
    foreach ($items as [$tag, $text]) { [$color, $label] = $tagStyle[$tag] ?? ['gray', ucfirst($tag)]; $h .= '<li><span class="badge ' . esc($color) . '">' . esc($label) . '</span><span>' . esc($text) . '</span></li>'; }
    return $h . '</ul>';
};
$counts = array_count_values(array_column($releaseNotes['items'], 0)); ?>
<div class="card" id="whats-new"><div class="section-heading"><div><p class="eyebrow">What's new in v<?= esc($version) ?></p><h2><?= esc($releaseNotes['title']) ?></h2></div><span class="muted">Released <?= esc($releaseNotes['date']) ?> &middot; <?= count($releaseNotes['items']) ?> changes<?php foreach ($counts as $tg => $c) { ?> &middot; <?= (int) $c ?> <?= esc(strtolower($tagStyle[$tg][1] ?? $tg)) ?><?php } ?></span></div>
<?= $noteList($releaseNotes['items']) ?>
<p style="margin:12px 0 0"><a href="#history">See the full version history, from 1.0.0 to v<?= esc($version) ?> ↓</a> · <a href="#packages">Update packages folder ↓</a></p>
</div>
<style>.rn-list{list-style:none;margin:0;padding:0}.rn-list li{display:flex;gap:10px;align-items:flex-start;padding:7px 0;border-bottom:1px solid var(--line,#e2e8f0)}.rn-list .badge{flex:0 0 92px;text-align:center}.rn-older{margin-top:12px}.rn-older summary{font-weight:600}</style>
<?php } ?>
<div class="card"><div class="section-heading"><div><p class="eyebrow">SOFTWARE</p><h2>System updates</h2></div>
<form method="post" action="<?= base_url('its-updates-check') ?>"><?= csrf_field() ?><button class="btn">Check for updates</button></form></div>
<div class="tbl"><table>
<tr><th>Component</th><th>Installed</th><th>Latest</th><th>Status</th></tr>
<tr><td>IT Support application</td><td><?= esc($version) ?></td><td><?= esc($last['app']['version'] ?? '—') ?></td><td><?php if (!$last) { ?><span class="badge gray">Not checked</span><?php } elseif (empty($last['app'])) { ?><span class="badge gray">No update source</span><?php } elseif ($last['app']['available']) { ?><span class="badge orange"><?= esc(ucfirst($last['app']['type'])) ?> update available</span><?php } else { ?><span class="badge green">Up to date</span><?php } ?></td></tr>
<tr><td>CodeIgniter framework</td><td><?= esc($frameworkVersion) ?></td><td><?= esc($last['framework']['version'] ?? '—') ?></td><td><?php if (empty($last['framework'])) { ?><span class="badge gray">Not checked</span><?php } elseif ($last['framework']['available']) { ?><span class="badge orange"><?= esc(ucfirst($last['framework']['type'])) ?> update available</span><?php } else { ?><span class="badge green">Up to date</span><?php } ?></td></tr>
<tr><td>PHP</td><td><?= esc($system['php']) ?></td><td colspan="2"><span class="muted"><?= esc($system['phpNote']) ?></span></td></tr>
</table></div>
<?php if ($last) { ?><p class="muted">Last checked <?= esc($last['checked'] ?? '') ?></p><?php } ?>
</div>

<div class="card" id="packages"><div class="section-heading"><div><p class="eyebrow">Update packages folder</p><h3 style="margin:0">Packages ready to install</h3><p class="muted" style="margin:4px 0 0">Add a ZIP here (or copy a ZIP or unzipped package folder into <code><?= esc(str_replace('\\', '/', $inboxDir)) ?></code> by FTP), review it, then install. Every install auto-backs up files and database first and rolls back on failure.</p></div></div>
<form id="form-folder-upload" method="post" action="<?= base_url('its-updates-folder-upload') ?>" enctype="multipart/form-data" class="pk-add"><?= csrf_field() ?><input type="file" name="package" accept=".zip" required aria-label="Update package ZIP"><button class="btn secondary" type="submit">Add to folder</button></form>
<?php if (!$inbox) { ?><p class="muted">The folder is empty.</p><?php } else { ?><div class="tbl"><table><tr><th>Package</th><th>Version</th><th>Type</th><th>Added</th><th></th></tr>
<?php foreach ($inbox as $pk) { ?><tr>
<td><strong><?= esc($pk['name']) ?></strong><br><span class="muted"><?= $pk['kind'] === 'folder' ? 'Folder' : number_format($pk['size'] / 1048576, 1) . ' MB ZIP' ?><?= $pk['notes'] !== '' ? ' · ' . esc(mb_strimwidth($pk['notes'], 0, 80, '…')) : '' ?></span><?php if ($pk['error']) { ?><br><span class="badge orange"><?= esc($pk['error']) ?></span><?php } ?></td>
<td><?= esc($pk['version'] ?: '?') ?><?= $pk['version'] === $version ? ' <span class="badge green">installed</span>' : '' ?></td>
<td><?php if ($pk['error'] === '') { ?><span class="badge <?= $pk['newer'] ? ($pk['type'] === 'major' ? 'orange' : 'blue') : 'gray' ?>"><?= $pk['newer'] ? esc(ucfirst($pk['type'])) . ' update' : 'Not newer' ?></span><?php } ?></td>
<td class="muted"><?= esc($pk['time']) ?></td>
<td><?php if ($pk['error'] === '') { ?><form class="pk-install upd-install-form" method="post" action="<?= base_url('its-updates-folder-install') ?>" data-title="Installing <?= esc($pk['version']) ?>…"<?= ($pk['type'] === 'major' && $pk['newer']) ? ' data-needs-major-confirm="1" data-major-version="' . esc($pk['version']) . '"' : '' ?>><?= csrf_field() ?><input type="hidden" name="name" value="<?= esc($pk['name']) ?>">
<?php if ($pk['type'] === 'major' && $pk['newer']) { ?><input type="hidden" name="confirm" class="major-confirm-val"><?php } ?>
<?php if (!$pk['newer']) { ?><label class="pk-same"><input type="checkbox" name="allow_same" value="1"> reinstall</label><?php } ?>
<?php $integrityBlocks = $integrity['baseline'] && $integrity['ok'] === false; ?>
<button class="btn" <?= $integrityBlocks ? 'disabled title="Resolve integrity problems first"' : '' ?>>Install <?= esc($pk['version']) ?></button></form><?php } ?>
<form method="post" action="<?= base_url('its-updates-folder-delete') ?>" onsubmit="return confirm('Delete this package from the folder?')"><?= csrf_field() ?><input type="hidden" name="name" value="<?= esc($pk['name']) ?>"><button class="btn secondary">Delete</button></form></td></tr><?php } ?></table></div><?php } ?>
<?php if ($integrity['baseline'] && $integrity['ok'] === false) { ?><p class="muted">Installing is disabled until the integrity check below is clean (record a new baseline or auto-heal).</p><?php } ?>
</div>
<style>.pk-add{display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin:8px 0 14px}.pk-add input{width:auto;flex:1;min-width:200px;margin:0}.pk-install{display:flex;flex-wrap:wrap;gap:6px;align-items:center;margin:0 0 6px}.pk-install input[name=confirm]{width:150px;margin:0;padding:7px}.pk-same{display:flex;gap:4px;align-items:center;font-size:12px;margin:0}.pk-same input{width:auto;margin:0}</style>
<?php if ($last) { ?><div class="card"><h3>Update details</h3>
<?php foreach (($last['errors'] ?? []) as $e) { ?><p class="muted">⚠ <?= esc($e) ?></p><?php } ?>
<?php if (!empty($last['app']['available'])) { $a = $last['app']; ?>
<?php if ($a['notes']) { ?><p><?= nl2br(esc($a['notes'])) ?></p><?php } ?>
<p class="muted">Before installing, files and database are backed up automatically. The update only runs if the package checksum and the integrity check pass, and files are rolled back if anything fails.</p>
<form class="upd-install-form" method="post" action="<?= base_url('its-updates-apply') ?>" data-title="Installing <?= esc($a['version']) ?> from the update feed…"<?= $a['type'] === 'major' ? ' data-needs-major-confirm="1" data-major-version="' . esc($a['version']) . '"' : '' ?>><?= csrf_field() ?><input type="hidden" name="version" value="<?= esc($a['version']) ?>">
<?php if ($a['type'] === 'major') { ?><input type="hidden" name="confirm" class="major-confirm-val"><?php } ?>
<?php $intBlk = $integrity['baseline'] && !$integrity['ok']; ?>
<button class="btn" <?= $intBlk ? 'disabled' : '' ?>>Install <?= esc($a['version']) ?></button><?= $intBlk ? ' <span class="muted">Resolve integrity problems first.</span>' : '' ?></form><?php } ?>
<?php if (!empty($last['framework']['available'])) { $f = $last['framework']; ?>
<p class="muted">Framework updates are installed with Composer, not from this page. Take a backup, then run <code>composer update codeigniter4/framework</code> in the project folder<?= $f['type'] === 'major' ? ' (major release: read the CodeIgniter upgrade guide first)' : '' ?>.</p><?php } ?>
</div><?php } ?>

<div class="card"><h3>Upload and install a package</h3>
<p class="muted">Upload a ZIP directly. Files and the database are backed up first and rolled back on failure.</p>
<form id="form-upload" class="upd-install-form" method="post" action="<?= base_url('its-updates-upload') ?>" enctype="multipart/form-data" data-title="Uploading and installing…" data-cur-version="<?= esc($version) ?>"><?= csrf_field() ?>
<input type="hidden" name="confirm" class="major-confirm-val">
<label for="pk">Package type</label><select id="pk" name="kind"><option value="app">Application update</option><option value="framework">Framework (CodeIgniter) update</option></select>
<label for="pf">ZIP file <span class="muted">(max 100 MB)</span></label><input id="pf" type="file" name="package" accept=".zip" required>
<label for="pv">Version <span class="muted">(x.y.z — framework version is read from the ZIP)</span></label><input id="pv" name="version" maxlength="20" placeholder="e.g. <?= esc($version) ?>">
<details style="margin:6px 0 4px"><summary style="cursor:pointer;font-size:13px;color:var(--muted);user-select:none">Advanced options</summary>
<div style="padding:10px 0 4px;display:grid;gap:8px">
<label for="ps">SHA-256 checksum <span class="muted">(optional — verified if given)</span></label><input id="ps" name="sha256" maxlength="64" autocomplete="off">
<label style="display:flex;gap:6px;align-items:center;font-size:13px"><input type="checkbox" name="allow_same" value="1" style="width:auto;margin:0"> Allow reinstalling the same or an older version</label>
</div></details>
<button class="btn" id="btn-upload-install">Upload and install</button></form></div>

<div class="card"><h3>Automatic checking</h3>
<form method="post" action="<?= base_url('its-updates-auto') ?>"><?= csrf_field() ?>
<label><input type="checkbox" name="auto" value="1" <?= $auto ? 'checked' : '' ?>> Check for updates automatically once a day (when an admin opens this page)</label>
<button class="btn">Save</button></form></div>

<div class="card"><div class="section-heading"><h3>Update log</h3>
<form method="post" action="<?= base_url('its-updates-clearlog') ?>" onsubmit="return confirm('Clear the update log?')"><?= csrf_field() ?><button class="btn">Clear log</button></form></div>
<pre style="max-height:320px;overflow:auto;white-space:pre-wrap"><?= $log ? esc(implode("\n", $log)) : 'No log entries yet.' ?></pre></div>
<div class="card"><h3>Server environment</h3><div class="tbl"><table>
<?php foreach ($system['rows'] as $label => $value) { ?><tr><th style="width:230px"><?= esc($label) ?></th><td><?= esc($value) ?></td></tr><?php } ?>
</table></div></div>

<div class="card"><h3>Update source</h3>
<form method="post" action="<?= base_url('its-updates-url') ?>"><?= csrf_field() ?>
<label for="mu">Manifest URL (https JSON with version, url, sha256, notes)</label><input id="mu" name="manifest_url" maxlength="500" value="<?= esc($manifestUrl) ?>" placeholder="https://example.com/itsupport/manifest.json">
<button class="btn">Save</button></form></div>

<div class="card"><div class="section-heading"><h3>System integrity</h3>
<button id="btn-integrity" class="btn secondary">Run integrity check</button></div>
<div id="integrity-result">
<?php if (!$integrity['baseline']) { ?><p>No baseline recorded yet.</p>
<?php } elseif ($integrity['ok'] === null) { ?><p><span class="badge gray">Not checked yet</span> <?= (int) $integrity['checked'] ?> files in baseline · recorded <?= esc($integrity['created']) ?> — click Run to verify.</p>
<?php } else { ?><p><span class="badge <?= $integrity['ok'] ? 'green' : 'orange' ?>"><?= $integrity['ok'] ? 'Healthy' : 'Problems found' ?></span> <?= (int) $integrity['checked'] ?> files checked · baseline <?= esc($integrity['created']) ?></p>
<?php foreach (['modified' => 'Modified', 'missing' => 'Missing', 'added' => 'New (informational)'] as $k => $label) { if ($integrity[$k]) { ?><details><summary><?= $label ?> (<?= count($integrity[$k]) ?>)</summary><ul><?php foreach (array_slice($integrity[$k], 0, 100) as $p) { ?><li><code><?= esc($p) ?></code></li><?php } ?></ul></details><?php } } } ?>
</div>
<form method="post" action="<?= base_url('its-updates-baseline') ?>" onsubmit="return confirm('Record the current files as the trusted baseline?')" style="margin-top:12px"><?= csrf_field() ?><button class="btn">Record current state as baseline</button></form></div>

<div class="card"><div class="section-heading"><h3>Backups &amp; restore</h3>
<div style="display:flex;gap:8px">
<form method="post" action="<?= base_url('its-updates-backup') ?>" class="upd-backup-form"><?= csrf_field() ?><button class="btn">Create backup now</button></form>
</div></div>
<p class="muted" style="margin:0 0 12px">Every install automatically backs up both code and database before applying changes. You can also create manual backups and restore to any previous code backup.</p>
<form id="form-heal-restore" method="post" action="<?= base_url('its-updates-heal') ?>">
<?= csrf_field() ?>
<div class="tbl"><table>
<tr><th></th><th>Backup file</th><th>Type</th><th>Size</th><th>Created</th><th></th></tr>
<?php foreach ($backups as $b) { ?><tr>
<td style="width:32px"><?php if ($b['type'] === 'code') { ?><input type="radio" name="backup" value="<?= esc($b['name']) ?>" class="bk-radio" aria-label="Select <?= esc($b['name']) ?>"><?php } ?></td>
<td><?= esc($b['name']) ?><?php
    // Parse version from backup name: code-YYYYMMDD-HHiiss-vX.Y.Z-label.zip
    if (preg_match('/-v(\d+\.\d+\.\d+)-/', $b['name'], $bvm)) echo ' <span class="badge blue" style="font-size:11px">v' . esc($bvm[1]) . '</span>';
    if (str_contains($b['name'], '-pre-')) echo ' <span class="badge gray" style="font-size:11px">auto</span>';
    elseif (str_contains($b['name'], '-manual')) echo ' <span class="badge gray" style="font-size:11px">manual</span>';
    elseif (str_contains($b['name'], '-pre-restore')) echo ' <span class="badge gray" style="font-size:11px">pre-restore</span>';
?></td>
<td><?= esc($b['type']) ?></td>
<td><?= number_format($b['size'] / 1048576, 1) ?> MB</td>
<td class="muted"><?= esc($b['time']) ?></td>
<td style="display:flex;gap:6px;flex-wrap:wrap">
<a href="<?= base_url('its-updates-download?name=' . urlencode($b['name'])) ?>">Download</a>
<?php if ($b['type'] === 'code') { ?><button type="button" class="btn secondary btn-restore" data-name="<?= esc($b['name']) ?>" style="font-size:12px;padding:4px 10px" title="Restore all code files from this backup">Restore</button><?php } ?>
</td></tr>
<?php } if (!$backups) { ?><tr><td colspan="6" class="muted">No backups yet.</td></tr><?php } ?></table></div>
<div style="display:flex;gap:8px;margin-top:10px;flex-wrap:wrap">
<button class="btn secondary" type="submit" id="btn-heal" <?= ($integrity['ok'] !== false || !$integrity['baseline']) ? 'disabled' : '' ?>>Auto-heal from selected backup</button>
<button class="btn secondary" type="button" id="btn-restore-sel" disabled>Restore all files from selected</button>
</div>
<p class="muted">Auto-heal restores only modified or missing files. Restore replaces all code files with the backup version (database is not changed; a new backup is created first).</p>
</form>
<!-- Hidden restore form -->
<form id="form-restore" class="upd-install-form" method="post" action="<?= base_url('its-updates-restore') ?>" data-title="Restoring from backup…" style="display:none"><?= csrf_field() ?>
<input type="hidden" name="backup" id="restore-backup-input">
</form>
</div>

<?php $hTag = ['feature' => ['green', 'New'], 'security' => ['orange', 'Security'], 'fix' => ['blue', 'Fix'], 'performance' => ['green', 'Reliability'], 'change' => ['gray', 'Change']]; ?>
<div class="card" id="history"><div class="section-heading"><div><p class="eyebrow">Change history</p><h3 style="margin:0">Version history</h3><p class="muted" style="margin:4px 0 0">Every release from the first version to <?= esc($version) ?>, with what changed in each.</p></div>
<div class="vh-filter" role="group" aria-label="Filter changes"><?php foreach (['' => 'All', 'feature' => 'New', 'security' => 'Security', 'fix' => 'Fixes', 'performance' => 'Reliability'] as $fk => $fl) { ?><button type="button" data-vh="<?= $fk ?>" aria-pressed="<?= $fk === '' ? 'true' : 'false' ?>"><?= esc($fl) ?></button><?php } ?></div></div>
<ol class="vh">
<?php foreach ($allReleases as $ver => $rel) { $cur = $ver === $version; $newer = version_compare((string) $ver, $version, '>'); $cnt = array_count_values(array_column($rel['items'], 0)); ?>
<li class="vh-rel<?= $cur ? ' is-current' : '' ?>"><details <?= $cur ? 'open' : '' ?>><summary><span class="vh-ver">v<?= esc((string) $ver) ?></span> <strong><?= esc($rel['title']) ?></strong> <span class="muted"><?= esc($rel['date']) ?> · <?= count($rel['items']) ?> changes</span>
<?php if ($cur) { ?><span class="badge green">installed</span><?php } elseif ($newer) { ?><span class="badge orange">not installed yet</span><?php } ?>
<span class="vh-counts"><?php foreach ($cnt as $tg => $n) { ?><span class="badge <?= esc($hTag[$tg][0] ?? 'gray') ?>"><?= (int) $n ?> <?= esc(strtolower($hTag[$tg][1] ?? $tg)) ?></span> <?php } ?></span></summary>
<?php if (!empty($rel['note'])) { ?><p class="muted"><?= esc($rel['note']) ?></p><?php } ?>
<ul class="rn-list"><?php foreach ($rel['items'] as [$tg, $text]) { [$c, $l] = $hTag[$tg] ?? ['gray', ucfirst($tg)]; ?><li data-tag="<?= esc($tg) ?>"><span class="badge <?= esc($c) ?>"><?= esc($l) ?></span><span><?= esc($text) ?></span></li><?php } ?></ul></details></li>
<?php } ?>
</ol></div>
<style>.vh{list-style:none;margin:0;padding:0 0 0 18px;border-inline-start:2px solid var(--line);display:grid;gap:10px}.vh-rel{position:relative}.vh-rel:before{content:"";position:absolute;left:-25px;top:12px;width:12px;height:12px;border-radius:50%;background:var(--paper);border:2px solid var(--line)}.vh-rel.is-current:before{background:var(--brand);border-color:var(--brand)}.vh-rel summary{cursor:pointer;display:flex;flex-wrap:wrap;gap:6px 10px;align-items:center;padding:6px 0}.vh-ver{font-family:ui-monospace,monospace;font-weight:700;color:var(--brand)}.vh-counts{display:flex;gap:4px;flex-wrap:wrap}.vh-filter{display:flex;gap:6px;flex-wrap:wrap}.vh-filter button{border:1px solid var(--line);background:var(--paper);color:var(--ink);border-radius:999px;padding:5px 12px;font-size:12px;cursor:pointer;min-height:0}.vh-filter button[aria-pressed=true]{background:var(--brand);border-color:var(--brand);color:#fff}.rn-list{list-style:none;margin:0;padding:0}.rn-list li{display:flex;gap:10px;align-items:flex-start;padding:7px 0;border-bottom:1px solid var(--line)}.rn-list .badge{flex:0 0 92px;text-align:center}</style>
<script>
(()=>{
const progressUrl = '<?= base_url('its-updates-progress') ?>';
const csrfName = '<?= csrf_token() ?>';
const csrfVal = '<?= csrf_hash() ?>';
const modal = document.getElementById('upd-modal');
const fill = document.getElementById('upd-prog-fill');
const pctEl = document.getElementById('upd-prog-pct');
const msgEl = document.getElementById('upd-prog-msg');
const titleEl = document.getElementById('upd-modal-title');
const stepsEl = document.getElementById('upd-prog-steps');
const majorModal = document.getElementById('major-modal');
const majorDesc = document.getElementById('major-modal-desc');
let pollId = null;
const STEPS = {checksum:'Checksum',integrity:'Integrity',backup:'Code backup',['db-backup']:'DB backup',preflight:'Pre-flight',extract:'Extracting',migrations:'Migrations',health:'Health check',baseline:'Baseline',verify:'Verifying',restore:'Restoring',['code-backup']:'Backup',done:'Done'};

function isMajorBump(from, to) {
    const [fa] = (from||'').split('.').map(Number);
    const [ta] = (to||'').split('.').map(Number);
    return !isNaN(fa) && !isNaN(ta) && ta > fa;
}
function showMajorModal(desc, onConfirm) {
    majorDesc.textContent = desc;
    majorModal.removeAttribute('hidden');
    const confirm = document.getElementById('major-confirm-btn');
    const cancel = document.getElementById('major-cancel');
    const cleanup = () => { majorModal.setAttribute('hidden',''); confirm.onclick = null; cancel.onclick = null; };
    confirm.onclick = () => { cleanup(); onConfirm(); };
    cancel.onclick = cleanup;
}

function showModal(title) {
    titleEl.textContent = title || 'Working…';
    fill.style.width = '0%'; pctEl.textContent = '0%'; msgEl.textContent = 'Starting…'; stepsEl.innerHTML = '';
    modal.removeAttribute('hidden');
}
function hideModal() { modal.setAttribute('hidden',''); clearInterval(pollId); }
function updateProgress(pct, msg, step) {
    fill.style.width = pct + '%';
    pctEl.textContent = pct + '%';
    msgEl.textContent = msg || '…';
    if (step && STEPS[step] && !stepsEl.querySelector('[data-s="'+step+'"]')) {
        const sp = document.createElement('span');
        sp.dataset.s = step; sp.className = 'badge green'; sp.style.fontSize = '11px';
        sp.textContent = '✓ ' + STEPS[step]; stepsEl.appendChild(sp);
    }
}
function startPolling() {
    clearInterval(pollId);
    pollId = setInterval(async () => {
        try { const r = await fetch(progressUrl); const d = await r.json(); if (d.pct > 0) updateProgress(d.pct, d.msg, d.step); } catch(e){}
    }, 500);
}
function submitWithProgress(form, isUpload) {
    showModal(form.dataset.title || 'Working…');
    startPolling();
    const fd = new FormData(form);
    const xhr = new XMLHttpRequest();
    xhr.open('POST', form.action);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    if (isUpload) {
        xhr.upload.onprogress = ev => {
            if (ev.lengthComputable) {
                const pct = Math.round(ev.loaded / ev.total * 28);
                updateProgress(pct, 'Uploading… ' + Math.round(ev.loaded / ev.total * 100) + '%');
            }
        };
    }
    xhr.onload = () => {
        clearInterval(pollId);
        let ok = true; let msg = '';
        try { const d = JSON.parse(xhr.responseText); ok = !!d.ok; msg = d.msg || ''; } catch(e){}
        if (ok) {
            updateProgress(100, msg || 'Done!', 'done');
            setTimeout(() => { hideModal(); window.location.reload(); }, 1200);
        } else {
            updateProgress(0, msg || 'An error occurred. Please check the log.', 'error');
            fill.style.background = '#ef4444';
            setTimeout(() => { hideModal(); window.location.reload(); }, 3000);
        }
    };
    xhr.onerror = () => {
        clearInterval(pollId); hideModal();
        alert('Network error during upload. Please try again.');
    };
    xhr.send(fd);
    return false;
}

// Intercept all install forms (folder-install, apply, upload-and-install)
document.querySelectorAll('.upd-install-form').forEach(form => {
    const isUpload = form.id === 'form-upload' || form.id === 'form-folder-upload';
    form.addEventListener('submit', e => {
        e.preventDefault();
        // Determine if this needs major-update confirmation
        let needsMajor = form.dataset.needsMajorConfirm === '1';
        // For the upload form, detect major bump from the version field vs current version
        if (!needsMajor && form.dataset.curVersion) {
            const enteredVer = (form.querySelector('[name=version]') || {}).value || '';
            needsMajor = isMajorBump(form.dataset.curVersion, enteredVer);
        }
        const confirmInput = form.querySelector('.major-confirm-val');
        if (needsMajor && confirmInput && !confirmInput.value) {
            const toVer = form.dataset.majorVersion || (form.querySelector('[name=version]') || {}).value || 'the next';
            showMajorModal(
                'You are about to install v' + toVer + ', a major release. This may include breaking changes. A full backup will be created first. Are you sure?',
                () => { confirmInput.value = 'UPDATE MAJOR'; submitWithProgress(form, isUpload); }
            );
            return;
        }
        submitWithProgress(form, isUpload);
    });
});

// Folder upload (no progress bar — just upload, then reload)
const folderUploadForm = document.getElementById('form-folder-upload');
if (folderUploadForm) {
    folderUploadForm.addEventListener('submit', e => {
        e.preventDefault();
        showModal('Uploading package to folder…');
        const fd = new FormData(folderUploadForm);
        const xhr = new XMLHttpRequest();
        xhr.open('POST', folderUploadForm.action);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.upload.onprogress = ev => {
            if (ev.lengthComputable) { const pct = Math.round(ev.loaded / ev.total * 95); updateProgress(pct, 'Uploading… ' + Math.round(ev.loaded / ev.total * 100) + '%'); }
        };
        xhr.onload = () => { updateProgress(100, 'Package uploaded!', 'done'); setTimeout(() => { hideModal(); window.location.reload(); }, 800); };
        xhr.onerror = () => { hideModal(); alert('Upload failed.'); };
        xhr.send(fd);
    });
}

// Backup now
document.querySelectorAll('.upd-backup-form').forEach(f => {
    f.addEventListener('submit', e => {
        e.preventDefault();
        showModal('Creating backup…');
        updateProgress(5, 'Creating code backup…');
        const fd = new FormData(f);
        const xhr = new XMLHttpRequest();
        xhr.open('POST', f.action);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.onload = () => { updateProgress(100, 'Backup created.', 'done'); setTimeout(() => { hideModal(); window.location.reload(); }, 1000); };
        xhr.send(fd);
    });
});

// Integrity check button
document.getElementById('btn-integrity').addEventListener('click', () => {
    showModal('Checking file integrity…');
    startPolling();
    const fd = new FormData(); fd.append(csrfName, csrfVal);
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '<?= base_url('its-updates-integrity-check') ?>');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.onload = () => {
        clearInterval(pollId);
        try {
            const d = JSON.parse(xhr.responseText);
            const v = d.result;
            updateProgress(100, v.ok ? '✓ All ' + v.checked + ' files verified — integrity is clean.' : '⚠ Problems found: ' + v.modified.length + ' modified, ' + v.missing.length + ' missing.', 'done');
            setTimeout(() => { hideModal(); window.location.reload(); }, 1500);
        } catch(e) { hideModal(); window.location.reload(); }
    };
    xhr.send(fd);
});

// Restore: row-level button sets hidden input and submits form
document.querySelectorAll('.btn-restore').forEach(btn => {
    btn.addEventListener('click', () => {
        if (!confirm('Restore ALL code files from backup "' + btn.dataset.name + '"?\n\nA safety backup of the current state will be created first. The database will not be changed.')) return;
        document.getElementById('restore-backup-input').value = btn.dataset.name;
        document.getElementById('form-restore').dispatchEvent(new Event('submit', {bubbles:true,cancelable:true}));
    });
});

// Restore: radio + button
const radios = document.querySelectorAll('.bk-radio');
const restoreSelBtn = document.getElementById('btn-restore-sel');
radios.forEach(r => r.addEventListener('change', () => restoreSelBtn.disabled = false));
restoreSelBtn.addEventListener('click', () => {
    const sel = document.querySelector('.bk-radio:checked');
    if (!sel) return;
    if (!confirm('Restore ALL code files from backup "' + sel.value + '"?\n\nA safety backup will be created first. The database will not be changed.')) return;
    document.getElementById('restore-backup-input').value = sel.value;
    document.getElementById('form-restore').dispatchEvent(new Event('submit', {bubbles:true,cancelable:true}));
});

// Version history filter
const b=[...document.querySelectorAll('[data-vh]')];
b.forEach(x=>x.addEventListener('click',()=>{const t=x.dataset.vh;b.forEach(y=>y.setAttribute('aria-pressed',String(y===x)));document.querySelectorAll('.vh li[data-tag]').forEach(li=>li.hidden=t!==''&&li.dataset.tag!==t);if(t)document.querySelectorAll('.vh details').forEach(d=>d.open=true);}));
})();
</script>
<?= view('itsupport/_footer') ?>
