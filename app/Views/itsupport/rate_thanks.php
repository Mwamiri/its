<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= esc($title) ?></title>
<style>*{box-sizing:border-box}body{font-family:Inter,"Segoe UI",sans-serif;background:#f4f7fb;margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.card{background:#fff;border-radius:20px;padding:48px 36px;max-width:420px;width:100%;box-shadow:0 12px 40px rgba(15,23,42,.1);text-align:center}
.emoji{font-size:64px;line-height:1;margin-bottom:16px}
h2{margin:0 0 10px;font-size:1.4rem;color:#14243b}p{color:#68788e;font-size:14px;margin:0}
</style></head><body>
<div class="card">
<div class="emoji"><?= $rating >= 4 ? '🎉' : ($rating >= 3 ? '🙏' : '😔') ?></div>
<h2>Thank you for your feedback!</h2>
<p>Your rating has been recorded. We appreciate you taking the time to let us know how we did.</p>
</div>
</body></html>
