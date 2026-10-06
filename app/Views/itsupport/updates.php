<?= view('itsupport/_header') ?>
<div class="tw-grid tw-grid-cols-2 tw-gap-3 md:tw-grid-cols-4 tw-mb-4">
<?php foreach ([['App', $version], ['CodeIgniter', $frameworkVersion], ['PHP', $system['php']], ['Database', $system['rows']['Database']]] as [$k, $val]) { ?>
<div class="tw-rounded-2xl tw-border tw-border-line tw-bg-paper tw-p-4 tw-shadow-sm"><p class="tw-m-0 tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-muted"><?= esc($k) ?></p><p class="tw-m-0 tw-mt-1 tw-text-lg tw-font-bold tw-text-ink tw-break-words"><?= esc($val) ?></p></div>
<?php } ?>
</div><div class="card"><div class="section-heading"><div><p class="eyebrow">SOFTWARE</p><h2>System updates</h2></div>
<form method="post" action="<?= base_url('its-updates-check') ?>"><?= csrf_field() ?><button class="btn">Check for updates</button></form></div>
<div class="tbl"><table>
<tr><th>Component</th><th>Installed</th><th>Latest</th><th>Status</th></tr>
<tr><td>IT Support application</td><td><?= esc($version) ?></td><td><?= esc($last['app']['version'] ?? '—') ?></td><td><?php if (!$last) { ?><span class="badge gray">Not checked</span><?php } elseif (empty($last['app'])) { ?><span class="badge gray">No update source</span><?php } elseif ($last['app']['available']) { ?><span class="badge orange"><?= esc(ucfirst($last['app']['type'])) ?> update available</span><?php } else { ?><span class="badge green">Up to date</span><?php } ?></td></tr>
<tr><td>CodeIgniter framework</td><td><?= esc($frameworkVersion) ?></td><td><?= esc($last['framework']['version'] ?? '—') ?></td><td><?php if (empty($last['framework'])) { ?><span class="badge gray">Not checked</span><?php } elseif ($last['framework']['available']) { ?><span class="badge orange"><?= esc(ucfirst($last['framework']['type'])) ?> update available</span><?php } else { ?><span class="badge green">Up to date</span><?php } ?></td></tr>
<tr><td>PHP</td><td><?= esc($system['php']) ?></td><td colspan="2"><span class="muted"><?= esc($system['phpNote']) ?></span></td></tr>
</table></div>
<?php if ($last) { ?><p class="muted">Last checked <?= esc($last['checked'] ?? '') ?></p><?php } ?>
</div>

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
<?= view('itsupport/_footer') ?>
