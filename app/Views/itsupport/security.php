<?= view('itsupport/_header') ?>
<div class="page-head"><h2 class="tw-m-0 tw-text-2xl tw-font-bold">Security</h2><p class="muted">Protect your account and set organisation policies.</p></div>
<?php if (!empty($codes)) { ?><div class="card" role="alert"><h3>Recovery codes</h3><p>Store these somewhere safe. Each works once if you lose your phone.</p><p style="font-family:monospace;columns:2;max-width:320px"><?php foreach ($codes as $c) { ?><?= esc($c) ?><br><?php } ?></p></div><?php } ?>
<div class="grid grid-2">
<div class="card"><h3>Two-factor authentication</h3>
<?php if (!empty($me['totp_enabled'])) { ?>
<p><span class="badge green">On</span> Your login asks for an authenticator code.</p>
<form method="post" action="<?= base_url('its-security-disable') ?>"><?= csrf_field() ?><label for="dp">Confirm your password to turn it off</label><input id="dp" type="password" name="password" required><button class="btn">Turn off 2FA</button></form>
<?php } else { ?>
<p><span class="badge gray">Off</span> Scan the code with Google Authenticator, Microsoft Authenticator or similar, then enter the 6-digit code.</p>
<div style="max-width:220px;background:#fff;padding:8px;border-radius:10px"><?= $setup['qr'] ?></div>
<p class="muted">Can't scan? Enter this key manually: <code><?= esc($setup['secret']) ?></code></p>
<form method="post" action="<?= base_url('its-security-enable') ?>"><?= csrf_field() ?><label for="ec">6-digit code</label><input id="ec" name="code" inputmode="numeric" autocomplete="one-time-code" required><button class="btn">Enable 2FA</button></form>
<?php } ?></div>
<div class="card"><h3>Change password</h3>
<form method="post" action="<?= base_url('its-security-password') ?>"><?= csrf_field() ?>
<label for="cp">Current password</label><input id="cp" type="password" name="current_password" required>
<label for="np">New password (min 8 characters)</label><input id="np" type="password" name="new_password" minlength="8" required>
<label for="cf">Confirm new password</label><input id="cf" type="password" name="confirm_password" minlength="8" required>
<button class="btn">Update password</button></form></div>
</div>
<?php if ($admin) { ?>
<div class="card"><h3>Organisation policies</h3>
<form method="post" action="<?= base_url('its-security-policy') ?>"><?= csrf_field() ?>
<label class="check-line"><input type="checkbox" name="require_2fa_admin" value="1" <?= $policy['require_2fa_admin'] === '1' ? 'checked' : '' ?>> Require two-factor authentication for administrators</label>
<label for="st">Idle session timeout (minutes)</label><input id="st" type="number" name="session_timeout" min="5" max="1440" value="<?= esc($policy['session_timeout']) ?>">
<h4>SLA targets (hours)</h4>
<table><tr><th>Priority</th><th>First response</th><th>Resolution</th></tr>
<?php foreach ($policy['sla'] as $p => [$resp, $res]) { ?><tr><td><?= esc($p) ?></td><td><input type="number" min="1" name="resp_<?= esc($p) ?>" value="<?= (int) $resp ?>"></td><td><input type="number" min="1" name="res_<?= esc($p) ?>" value="<?= (int) $res ?>"></td></tr><?php } ?></table>
<label class="check-line"><input type="checkbox" name="sla_business_hours" value="1" <?= $policy['sla_business_hours'] === '1' ? 'checked' : '' ?>> Count SLA time in business hours only (Mon-Fri 08:00-17:00)</label>
<button class="btn">Save policies</button></form></div>
<?php } ?>
<?= view('itsupport/_footer') ?>