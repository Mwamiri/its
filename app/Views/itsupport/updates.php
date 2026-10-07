<?= view('itsupport/_header') ?>
<div class="tw-grid tw-grid-cols-2 tw-gap-3 md:tw-grid-cols-4 tw-mb-4">
<?php foreach ([['App', $version], ['CodeIgniter', $frameworkVersion], ['PHP', $system['php']], ['Database', $system['rows']['Database']]] as [$k, $val]) { ?>
<div class="tw-rounded-2xl tw-border tw-border-line tw-bg-paper tw-p-4 tw-shadow-sm"><p class="tw-m-0 tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-muted"><?= esc($k) ?></p><p class="tw-m-0 tw-mt-1 tw-text-lg tw-font-bold tw-text-ink tw-break-words"><?= esc($val) ?></p></div>
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

<div class="card" id="packages"><div class="section-heading"><div><p class="eyebrow">Update packages folder</p><h3 style="margin:0">Packages ready to install</h3><p class="muted" style="margin:4px 0 0">Add a ZIP here (or copy a ZIP or unzipped package folder into <code><?= esc(str_replace('\\', '/', $inboxDir)) ?></code> by FTP), review it, then install. Every install backs up first and rolls back automatically if anything fails.</p></div></div>
<form method="post" action="<?= base_url('its-updates-folder-upload') ?>" enctype="multipart/form-data" class="pk-add"><?= csrf_field() ?><input type="file" name="package" accept=".zip" required aria-label="Update package ZIP"><button class="btn secondary">Add to folder</button></form>
<?php if (!$inbox) { ?><p class="muted">The folder is empty.</p><?php } else { ?><div class="tbl"><table><tr><th>Package</th><th>Version</th><th>Type</th><th>Added</th><th></th></tr>
<?php foreach ($inbox as $pk) { ?><tr>
<td><strong><?= esc($pk['name']) ?></strong><br><span class="muted"><?= $pk['kind'] === 'folder' ? 'Folder' : number_format($pk['size'] / 1048576, 1) . ' MB ZIP' ?><?= $pk['notes'] !== '' ? ' · ' . esc(mb_strimwidth($pk['notes'], 0, 80, '…')) : '' ?></span><?php if ($pk['error']) { ?><br><span class="badge orange"><?= esc($pk['error']) ?></span><?php } ?></td>
<td><?= esc($pk['version'] ?: '?') ?><?= $pk['version'] === $version ? ' <span class="badge green">installed</span>' : '' ?></td>
<td><?php if ($pk['error'] === '') { ?><span class="badge <?= $pk['newer'] ? ($pk['type'] === 'major' ? 'orange' : 'blue') : 'gray' ?>"><?= $pk['newer'] ? esc(ucfirst($pk['type'])) . ' update' : 'Not newer' ?></span><?php } ?></td>
<td class="muted"><?= esc($pk['time']) ?></td>
<td><?php if ($pk['error'] === '') { ?><form method="post" action="<?= base_url('its-updates-folder-install') ?>" class="pk-install"><?= csrf_field() ?><input type="hidden" name="name" value="<?= esc($pk['name']) ?>">
<?php if ($pk['type'] === 'major' && $pk['newer']) { ?><input name="confirm" placeholder="Type UPDATE MAJOR" aria-label="Type UPDATE MAJOR to confirm" autocomplete="off"><?php } ?>
<?php if (!$pk['newer']) { ?><label class="pk-same"><input type="checkbox" name="allow_same" value="1"> reinstall</label><?php } ?>
<button class="btn" <?= !$integrity['ok'] ? 'disabled title="Resolve integrity problems first"' : '' ?>>Install <?= esc($pk['version']) ?></button></form><?php } ?>
<form method="post" action="<?= base_url('its-updates-folder-delete') ?>" onsubmit="return confirm('Delete this package from the folder?')"><?= csrf_field() ?><input type="hidden" name="name" value="<?= esc($pk['name']) ?>"><button class="btn secondary">Delete</button></form></td></tr><?php } ?></table></div><?php } ?>
<?php if (!$integrity['ok']) { ?><p class="muted">Installing is disabled until the integrity check below is clean (record a new baseline or auto-heal).</p><?php } ?>
</div>
<style>.pk-add{display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin:8px 0 14px}.pk-add input{width:auto;flex:1;min-width:200px;margin:0}.pk-install{display:flex;flex-wrap:wrap;gap:6px;align-items:center;margin:0 0 6px}.pk-install input[name=confirm]{width:150px;margin:0;padding:7px}.pk-same{display:flex;gap:4px;align-items:center;font-size:12px;margin:0}.pk-same input{width:auto;margin:0}</style>
<?php if ($last) { ?><div class="card"><h3>Update details</h3>
<?php foreach (($last['errors'] ?? []) as $e) { ?><p class="muted">⚠ <?= esc($e) ?></p><?php } ?>
<?php if (!empty($last['app']['available'])) { $a = $last['app']; ?>
<?php if ($a['notes']) { ?><p><?= nl2br(esc($a['notes'])) ?></p><?php } ?>
<p class="muted">Before installing, files and database are backed up automatically. The update only runs if the package checksum and the integrity check pass, and files are rolled back if anything fails.</p>
<form method="post" action="<?= base_url('its-updates-apply') ?>"><?= csrf_field() ?><input type="hidden" name="version" value="<?= esc($a['version']) ?>">
<?php if ($a['type'] === 'major') { ?><label>Major update: type UPDATE MAJOR to confirm</label><input name="confirm" autocomplete="off"><?php } ?>
<button class="btn" <?= !$integrity['ok'] ? 'disabled' : '' ?>>Install <?= esc($a['version']) ?></button><?= !$integrity['ok'] ? ' <span class="muted">Resolve integrity problems first.</span>' : '' ?></form><?php } ?>
<?php if (!empty($last['framework']['available'])) { $f = $last['framework']; ?>
<p class="muted">Framework updates are installed with Composer, not from this page. Take a backup, then run <code>composer update codeigniter4/framework</code> in the project folder<?= $f['type'] === 'major' ? ' (major release: read the CodeIgniter upgrade guide first)' : '' ?>.</p><?php } ?>
</div><?php } ?>

<div class="card"><h3>Upload an update package</h3>
<p class="muted">Upload a ZIP for a future application or framework release. Files and the database are backed up first and rolled back on failure. Application packages may only touch <code>app/</code>, <code>public/</code>, <code>system/</code>, <code>composer.json</code>, <code>spark</code> and <code>VERSION</code>; uploads, <code>.env</code> and <code>writable/</code> are never changed. Framework packages (official CodeIgniter release ZIP) only apply their <code>system/</code> folder.</p>
<form method="post" action="<?= base_url('its-updates-upload') ?>" enctype="multipart/form-data"><?= csrf_field() ?>
<label for="pk">Package type</label><select id="pk" name="kind"><option value="app">Application update</option><option value="framework">Framework (CodeIgniter) update</option></select>
<label for="pf">ZIP file (max 100 MB)</label><input id="pf" type="file" name="package" accept=".zip" required>
<label for="pv">Version (application packages, x.y.z; framework version is read from the ZIP)</label><input id="pv" name="version" maxlength="20" placeholder="2.2.0">
<label for="ps">SHA-256 checksum (optional, verified if given)</label><input id="ps" name="sha256" maxlength="64" autocomplete="off">
<label for="pc">Type UPDATE MAJOR here if this is a major version jump</label><input id="pc" name="confirm" autocomplete="off">
<label><input type="checkbox" name="allow_same" value="1"> Allow reinstalling the same or an older version</label>
<button class="btn" onclick="return confirm('Install this package now?')">Upload and install</button></form></div>

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

<div class="card"><h3>System integrity</h3>
<?php if (!$integrity['baseline']) { ?><p>No baseline recorded yet.</p>
<?php } else { ?><p><span class="badge <?= $integrity['ok'] ? 'green' : 'orange' ?>"><?= $integrity['ok'] ? 'Healthy' : 'Problems found' ?></span> <?= (int) $integrity['checked'] ?> files checked · baseline <?= esc($integrity['created']) ?></p>
<?php foreach (['modified' => 'Modified', 'missing' => 'Missing', 'added' => 'New (informational)'] as $k => $label) { if ($integrity[$k]) { ?><details><summary><?= $label ?> (<?= count($integrity[$k]) ?>)</summary><ul><?php foreach (array_slice($integrity[$k], 0, 100) as $p) { ?><li><code><?= esc($p) ?></code></li><?php } ?></ul></details><?php } } } ?>
<form method="post" action="<?= base_url('its-updates-baseline') ?>" onsubmit="return confirm('Record the current files as the trusted baseline?')"><?= csrf_field() ?><button class="btn">Record current state as baseline</button></form></div>

<div class="card"><h3>Backups on the server</h3>
<form method="post" action="<?= base_url('its-updates-backup') ?>"><?= csrf_field() ?><button class="btn">Create backup now</button></form>
<form method="post" action="<?= base_url('its-updates-heal') ?>"><?= csrf_field() ?>
<div class="tbl"><table><tr><th></th><th>File</th><th>Type</th><th>Size</th><th>Created</th><th></th></tr>
<?php foreach ($backups as $b) { ?><tr><td><?php if ($b['type'] === 'code') { ?><input type="radio" name="backup" value="<?= esc($b['name']) ?>" aria-label="Select <?= esc($b['name']) ?>"><?php } ?></td><td><?= esc($b['name']) ?></td><td><?= esc($b['type']) ?></td><td><?= number_format($b['size'] / 1024, 1) ?> KB</td><td><?= esc($b['time']) ?></td><td><a href="<?= base_url('its-updates-download?name=' . urlencode($b['name'])) ?>">Download</a></td></tr>
<?php } if (!$backups) { ?><tr><td colspan="6" class="muted">No backups yet.</td></tr><?php } ?></table></div>
<button class="btn" <?= ($integrity['ok'] || !$integrity['baseline']) ? 'disabled' : '' ?>>Auto-heal from selected backup</button>
<p class="muted">Auto-heal restores only modified or missing files from the chosen code backup.</p></form></div>
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
<script>(()=>{const b=[...document.querySelectorAll('[data-vh]')];b.forEach(x=>x.addEventListener('click',()=>{const t=x.dataset.vh;b.forEach(y=>y.setAttribute('aria-pressed',String(y===x)));document.querySelectorAll('.vh li[data-tag]').forEach(li=>li.hidden=t!==''&&li.dataset.tag!==t);if(t)document.querySelectorAll('.vh details').forEach(d=>d.open=true);}));})();</script>
<?= view('itsupport/_footer') ?>
