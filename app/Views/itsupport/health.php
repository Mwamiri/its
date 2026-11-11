<?= view('itsupport/_header') ?>
<?php
$okCount = $total - $issues;
$allOk   = $issues === 0;
?>
<div class="section-heading">
  <div><p class="eyebrow">System</p><h2>System Health</h2><p class="muted">Live status of every subsystem. Checks run on page load. Red rows have a fix link.</p></div>
  <div style="display:flex;gap:10px;align-items:center">
    <span class="badge <?= $allOk ? 'green' : 'red' ?>" style="font-size:14px;padding:8px 16px">
      <?= $allOk ? '✓ All systems go' : "{$issues} issue" . ($issues !== 1 ? 's' : '') . " · {$okCount}/{$total} OK" ?>
    </span>
    <a class="button secondary" href="<?= base_url('its-health') ?>">Refresh</a>
  </div>
</div>

<div class="health-grid">
<?php foreach ($sections as $sectionName => $checks): ?>
<?php
$secIssues = count(array_filter($checks, fn($c) => !$c[2]));
$sectionIcon = ['Server' => 'cpu', 'Storage' => 'hdd', 'Database' => 'database', 'Mail' => 'envelope', 'Security' => 'shield-lock'][$sectionName] ?? 'circle';
?>
<div class="card health-card">
  <div class="health-card-head">
    <span class="health-card-icon"><i class="bi bi-<?= $sectionIcon ?>"></i></span>
    <h3><?= esc($sectionName) ?></h3>
    <?php if ($secIssues > 0): ?><span class="badge red" style="margin-left:auto"><?= $secIssues ?> issue<?= $secIssues !== 1 ? 's' : '' ?></span><?php else: ?><span class="badge green" style="margin-left:auto">OK</span><?php endif ?>
  </div>
  <div class="health-rows">
  <?php foreach ($checks as [$name, $val, $ok, $link]): ?>
  <div class="health-row <?= $ok ? 'health-ok' : 'health-warn' ?>">
    <span class="health-dot"><?= $ok ? '●' : '●' ?></span>
    <span class="health-name"><?= esc($name) ?></span>
    <span class="health-val"><?= esc($val) ?></span>
    <?php if (!$ok && $link): ?><a href="<?= esc($link) ?>" class="health-fix">Fix →</a><?php endif ?>
  </div>
  <?php endforeach ?>
  </div>
</div>
<?php endforeach ?>
</div>

<div class="card" id="domains" style="margin-top:0">
  <div class="section-heading"><div><h3 style="margin:0">SSL &amp; Domain Monitoring</h3><p class="muted" style="margin:4px 0 0">SSL cert expiry is checked live on page load. Add any domain — your own site, a client's site, or any HTTPS URL.</p></div></div>
  <form method="post" action="<?= base_url('its-health-domains') ?>" style="display:flex;flex-wrap:wrap;gap:8px;align-items:flex-end;margin:12px 0"><?= csrf_field() ?>
    <div><label style="font-size:12px">Domain</label><input name="domain" placeholder="example.com" style="width:200px;margin:4px 0 0"></div>
    <div><label style="font-size:12px">Label (optional)</label><input name="label" placeholder="Client main site" style="width:180px;margin:4px 0 0"></div>
    <div><label style="font-size:12px">Client (optional)</label><select name="client_id" style="width:160px;margin:4px 0 0"><option value="">—</option><?php foreach ($clients as $c) { ?><option value="<?= (int) $c['id'] ?>"><?= esc($c['name']) ?></option><?php } ?></select></div>
    <button class="btn">Add domain</button>
  </form>
  <?php if ($watchedDomains) { ?>
  <div class="tbl"><table><tr><th>Domain</th><th>Label / Client</th><th>SSL expiry</th><th></th></tr>
  <?php foreach ($watchedDomains as $wd) {
    $days = $wd['ssl_days']; $ok = $wd['ssl_ok'];
    $badge = $days === null ? '<span class="badge gray">Unknown</span>' : ($days <= 0 ? '<span class="badge orange">Expired</span>' : ($days <= 14 ? '<span class="badge orange">' . $days . ' days</span>' : ($days <= 30 ? '<span class="badge blue">' . $days . ' days</span>' : '<span class="badge green">' . $days . ' days</span>')));
  ?><tr>
    <td><strong><?= esc($wd['domain']) ?></strong></td>
    <td><?= esc($wd['label'] ?? '') ?><?= $wd['client_name'] ? ' <span class="muted">· ' . esc($wd['client_name']) . '</span>' : '' ?></td>
    <td><?= $badge ?></td>
    <td><form method="post" action="<?= base_url('its-health-domains-delete/' . $wd['id']) ?>" onsubmit="return confirm('Remove this domain?')"><?= csrf_field() ?><button class="btn secondary" style="padding:5px 10px;font-size:12px">Remove</button></form></td>
  </tr><?php } ?>
  </table></div>
  <?php } else { ?><p class="muted">No domains added yet.</p><?php } ?>
</div>

<div class="card" style="margin-top:0">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
    <h3 style="margin:0">Recent log entries</h3>
    <div style="display:flex;gap:8px">
      <a class="button secondary" href="<?= base_url('its-logs') ?>">Full log viewer</a>
    </div>
  </div>
  <pre style="max-height:300px;overflow:auto;white-space:pre-wrap;font-size:12px;background:var(--canvas);border-radius:10px;padding:14px;margin:0;color:var(--ink)"><?= esc($log !== '' ? $log : 'No log entries today.') ?></pre>
</div>

<style>
.health-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,420px),1fr));gap:16px;margin-bottom:20px}
.health-card{margin:0}
.health-card-head{display:flex;align-items:center;gap:10px;padding-bottom:14px;margin-bottom:4px;border-bottom:1px solid var(--line)}
.health-card-head h3{margin:0;font-size:15px}
.health-card-icon{width:32px;height:32px;border-radius:8px;background:var(--brand-soft);color:var(--brand);display:grid;place-items:center;font-size:15px;flex-shrink:0}
.health-rows{display:grid;gap:2px}
.health-row{display:flex;align-items:center;gap:10px;padding:7px 4px;border-radius:8px;font-size:13px}
.health-ok .health-dot{color:#16a34a}
.health-warn{background:color-mix(in srgb,#ef4444 5%,var(--paper))}.health-warn .health-dot{color:#dc2626}
.health-name{font-weight:600;min-width:160px;flex-shrink:0;color:var(--ink)}
.health-val{flex:1;color:var(--muted);font-size:12.5px}
.health-fix{font-size:12px;font-weight:700;color:var(--brand);text-decoration:none;white-space:nowrap;flex-shrink:0}.health-fix:hover{text-decoration:underline}
</style>
<?= view('itsupport/_footer') ?>
