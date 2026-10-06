<?= view('itsupport/_header', ['title' => 'Two-factor verification']) ?>
<div class="card" style="max-width:420px;margin:40px auto;">
    <h2>Two-factor verification</h2>
    <p class="muted">Enter the 6-digit code from your authenticator app, or one of your recovery codes.</p>
    <?php if (!empty($error)) { ?><p style="color:#dc2626;" role="alert"><?= esc($error) ?></p><?php } ?>
    <form method="post" action="<?= base_url('its-2fa') ?>"><?= csrf_field() ?>
    <label for="code">Code</label><input id="code" name="code" inputmode="numeric" autocomplete="one-time-code" autofocus required>
    <button class="btn">Verify</button></form>
    <p><a href="<?= base_url('its-login') ?>">Back to login</a></p>
</div>
<?= view('itsupport/_footer') ?>