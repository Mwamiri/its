<?= view('itsupport/_header') ?>
<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px">
<h2 style="margin:0">Sign-in Activity <span class="badge gray"><?= number_format($total) ?></span></h2>
<?php if (!($hasHistory ?? false)) { ?><span class="badge orange">Failed attempts only — run migration to track all logins</span><?php } ?>
</div>

<div class="grid grid-2" style="align-items:start">
<div style="grid-column:1/3">
<form method="get" class="card" style="padding:14px;margin-bottom:14px">
<div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
<input name="q" style="width:auto;flex:1;min-width:200px;margin:0" placeholder="Search username or IP address" value="<?= esc($q ?? '') ?>">
<?php if ($hasHistory ?? false) { ?>
<select name="result" style="width:auto;margin:0">
<option value="">All results</option>
<option value="ok" <?= ($result ?? '') === 'ok' ? 'selected' : '' ?>>Signed in</option>
<option value="fail" <?= ($result ?? '') === 'fail' ? 'selected' : '' ?>>Failed</option>
</select>
<?php } ?>
<button class="btn">Search</button>
<?php if (($q ?? '') || ($result ?? '')) { ?><a class="button secondary" href="<?= base_url('its-signins') ?>">Clear</a><?php } ?>
</div>
</form>

<div class="card tbl"><table>
<?php if ($hasHistory ?? false) { ?>
<tr><th>When</th><th>Username</th><th>IP Address</th><th>Result</th></tr>
<?php foreach ($rows as $r) { ?>
<tr>
<td class="muted" style="white-space:nowrap;font-size:12px"><?= esc($r['created_at']) ?></td>
<td><?= esc($r['username'] ?: '—') ?></td>
<td style="font-family:monospace;font-size:12px"><?= esc($r['ip_address']) ?></td>
<td><?= (int) $r['success'] ? '<span class="badge green">Signed in</span>' : '<span class="badge orange">Failed' . (empty($r['reason']) ? '' : ': ' . esc($r['reason'])) . '</span>' ?></td>
</tr>
<?php } ?>
<?php } else { ?>
<tr><th>When</th><th>Username</th><th>IP Address</th></tr>
<?php foreach ($rows as $r) { ?>
<tr>
<td class="muted" style="white-space:nowrap;font-size:12px"><?= esc($r['attempted_at']) ?></td>
<td><?= esc($r['username'] ?: '—') ?></td>
<td style="font-family:monospace;font-size:12px"><?= esc($r['ip_address']) ?></td>
</tr>
<?php } ?>
<?php } ?>
<?php if (!$rows) { ?><tr><td colspan="4" style="text-align:center;padding:24px;color:#8290a3">No sign-in records yet.</td></tr><?php } ?>
</table></div>

<?php if (($pages ?? 1) > 1) { ?>
<div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:14px">
<?php for ($p = max(1, ($page ?? 1) - 3); $p <= min($pages, ($page ?? 1) + 3); $p++) { ?>
<a class="button <?= $p === ($page ?? 1) ? '' : 'secondary' ?>" href="?<?= esc(http_build_query(array_filter(['q' => $q ?? '', 'result' => $result ?? '', 'page' => $p], fn($v) => $v !== '' && $v !== '1')), 'attr') ?>"><?= $p ?></a>
<?php } ?>
</div>
<?php } ?>
</div>

<div class="card" style="grid-column:1/3">
<h3>Top IPs (all time)</h3>
<?php foreach ($byIp as $b) { ?>
<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #e9eef5">
<code style="font-size:13px"><?= esc($b['ip_address']) ?></code>
<span class="badge gray"><?= number_format((int) $b['n']) ?></span>
</div>
<?php } ?>
<?php if (!$byIp) { ?><p class="muted">No data yet.</p><?php } ?>
<p class="muted" style="margin-top:12px">Manage IP allow/block lists in <a href="<?= base_url('its-admin?tab=security') ?>">Admin → Security</a>.</p>
</div>
</div>
<?= view('itsupport/_footer') ?>
