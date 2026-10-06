<?= view('itsupport/_header') ?>
<div class="card"><h2>System health</h2>
<table><thead><tr><th>Check</th><th>Value</th><th>Status</th></tr></thead><tbody>
<?php foreach ($checks as [$name, $val, $ok]) { ?><tr><td><?= esc($name) ?></td><td><?= esc($val) ?></td><td><span class="badge <?= $ok ? 'green' : 'orange' ?>"><?= $ok ? 'OK' : 'Review' ?></span></td></tr><?php } ?>
</tbody></table></div>
<div class="card"><h3>Latest log entries</h3><pre style="max-height:320px;overflow:auto;white-space:pre-wrap"><?= esc($log !== '' ? $log : 'No log entries.') ?></pre></div>
<?= view('itsupport/_footer') ?>