<?= view('itsupport/_header', ['title' => 'Install']) ?>
<div class="card" style="max-width:420px;margin:40px auto;">
    <h2>Install System</h2>
    <?php if (!empty($error)) { ?><p style="color:#dc2626;"><?= esc($error) ?></p><?php } ?>
    <form method="post" action="<?= base_url('its-install') ?>">
        <?= csrf_field() ?>
        <label>Company Name</label>
        <input name="company" required>
        <label>Company Email</label>
        <input type="email" name="email">
        <label>Admin Username</label>
        <input name="username" required>
        <label>Password</label>
        <input type="password" name="password" required minlength="6">
        <button class="btn" type="submit">Install</button>
    </form>
</div>
<?= view('itsupport/_footer') ?>