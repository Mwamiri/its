<?= view('itsupport/_header', ['title' => 'Two-factor verification']) ?>
<?php $pending = session('its_pending') ?? []; $isEmail = ($pending['method'] ?? 'totp') === 'email'; ?>
<div class="card" style="max-width:440px;margin:40px auto;">
    <h2>Two-factor verification</h2>
    <?php if ($isEmail) { ?>
    <p class="muted">We emailed you a 6-digit code and a one-click sign-in link. Enter the code here, or open the link on any device. Both expire in 10 minutes.</p>
    <?php } else { ?>
    <p class="muted">Enter the 6-digit code from your authenticator app, or one of your recovery codes.</p>
    <?php } ?>
    <?php if (!empty($error)) { ?><p style="color:#dc2626;" role="alert"><?= esc($error) ?></p><?php } ?>
    <form method="post" action="<?= base_url('its-2fa') ?>"><?= csrf_field() ?>
    <label for="code"><?= $isEmail ? 'Code from the email' : 'Code' ?></label><input id="code" name="code" inputmode="numeric" autocomplete="one-time-code" autofocus required maxlength="24" style="font-size:22px;letter-spacing:6px;text-align:center">
    <button class="btn" style="width:100%">Verify and continue</button></form>
    <?php if (!empty($emailOption)) { ?>
    <form method="post" action="<?= base_url('its-2fa-email') ?>" style="margin-top:12px"><?= csrf_field() ?><button class="btn secondary" style="width:100%"><?= $isEmail ? 'Send a new email' : 'Email me a code instead' ?></button></form>
    <?php } ?>
    <p style="margin-top:14px"><a href="<?= base_url('its-login') ?>">Back to login</a></p>
</div>
<?= view('itsupport/_footer') ?>
