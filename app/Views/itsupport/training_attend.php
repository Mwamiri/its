<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><title>Attendance</title>
<style>body{font-family:system-ui,sans-serif;background:#f1f5f9;margin:0;display:grid;place-items:center;min-height:100vh}.c{background:#fff;border-radius:14px;padding:24px;width:min(420px,92vw);box-shadow:0 8px 30px #0002}label{display:block;margin-top:12px;font-weight:600;font-size:14px}input{width:100%;padding:12px;border:1px solid #cbd5e1;border-radius:8px;font-size:16px;box-sizing:border-box}button{margin-top:16px;width:100%;padding:13px;border:0;border-radius:8px;background:#2563eb;color:#fff;font-size:16px}.ok{background:#dcfce7;padding:10px;border-radius:8px}.er{background:#fee2e2;padding:10px;border-radius:8px}</style></head><body><div class="c">
<h2 style="margin-top:0"><?= esc($s['title']) ?></h2><p style="color:#64748b"><?= esc($s['session_date']) ?></p>
<?php if ($msg) { ?><p class="ok"><?= esc($msg) ?></p><?php } elseif ($err) { ?><p class="er"><?= esc($err) ?></p><?php } ?>
<?php if (!$msg && $s['signin_open']) { ?><form method="post"><?= csrf_field() ?>
<label for="n">Full name</label><input id="n" name="name" required autocomplete="name">
<label for="e">Email (optional)</label><input id="e" name="email" type="email" autocomplete="email">
<label for="r">Job title (optional)</label><input id="r" name="role_title">
<button type="submit">Sign in</button></form><?php } ?>
</div></body></html>