<?= view('itsupport/_header', ['title' => 'Login']) ?>
<div class="card" style="max-width:420px;margin:40px auto;">
    <h2>Login</h2>
    <?php if (!empty($error)) { ?><p style="color:#dc2626;"><?= esc($error) ?></p><?php } ?>
    <form method="post" action="<?= base_url('its-login') ?>"><?= csrf_field() ?>
    <label>Username</label><input name="username" required>
    <label>Password</label><input type="password" name="password" required>
    <button class="btn">Login</button></form>
</div>
<?= view('itsupport/_footer') ?>