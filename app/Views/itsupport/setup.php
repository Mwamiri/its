<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>ITSupportz setup</title></head>
<body>
<main style="max-width:32rem;margin:3rem auto;font:16px sans-serif">
    <h1>ITSupportz setup</h1>
    <?php if (! empty($success)) { ?>
        <p>Setup completed. Remove <code>its.setupKey</code> from your server's <code>.env</code> file now.</p>
        <p><a href="<?= esc(base_url('its-login')) ?>">Sign in</a></p>
    <?php } else { ?>
        <?php if (! empty($error)) { ?><p role="alert"><?= esc($error) ?></p><?php } ?>
        <p>Import the base schema before continuing. Use HTTPS and the private setup key from your server configuration.</p>
        <form method="post" action="<?= esc(base_url('its-setup')) ?>">
            <?= csrf_field() ?>
            <p><label>Setup key<br><input type="password" name="setup_key" required autocomplete="off"></label></p>
            <p><label>Company name<br><input name="company" required maxlength="190"></label></p>
            <p><label>Company email<br><input type="email" name="email" maxlength="190"></label></p>
            <p><label>Administrator username<br><input name="username" required maxlength="100" autocomplete="username"></label></p>
            <p><label>Administrator password (8 to 72 bytes)<br><input type="password" name="password" required minlength="8" maxlength="72" autocomplete="new-password"></label></p>
            <button type="submit">Complete setup</button>
        </form>
    <?php } ?>
</main>
</body>
</html>
