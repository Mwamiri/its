<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= esc($title) ?></title>
<style>*{box-sizing:border-box}body{font-family:Inter,"Segoe UI",sans-serif;background:#f4f7fb;margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.card{background:#fff;border-radius:20px;padding:40px 36px;max-width:480px;width:100%;box-shadow:0 12px 40px rgba(15,23,42,.1);text-align:center}
h2{margin:0 0 6px;font-size:1.4rem;color:#14243b}p.sub{color:#68788e;font-size:14px;margin:0 0 28px}
.stars{display:flex;justify-content:center;gap:8px;margin:0 0 24px}
.stars input{position:absolute;opacity:0;pointer-events:none}.stars label{font-size:42px;cursor:pointer;transition:transform .1s;color:#d1d5db;user-select:none}
.stars input:checked~label,.stars label:hover,.stars label:hover~label{color:#fbbf24}
.stars{flex-direction:row-reverse}.stars label:hover,.stars label:hover~label,.stars input:checked~label{color:#fbbf24}
textarea{width:100%;padding:11px 12px;border:1px solid #d6dfeb;border-radius:10px;font:inherit;font-size:14px;min-height:80px;resize:vertical;margin-bottom:16px}
button{background:linear-gradient(135deg,#2563eb,#1746a2);color:#fff;border:0;border-radius:10px;padding:13px 28px;font:600 14px/1 inherit;cursor:pointer;width:100%}
.ticket-ref{font-size:12px;color:#9aa5b4;margin-bottom:20px}
.flash-err{background:#fff0f0;border:1px solid #f1caca;color:#b42332;border-radius:10px;padding:10px 14px;margin-bottom:16px;font-size:13px}
</style></head><body>
<div class="card">
<?php if (session()->getFlashdata('err')) { ?><div class="flash-err"><?= esc(session()->getFlashdata('err')) ?></div><?php } ?>
<h2>How did we do?</h2>
<p class="sub">Please rate your recent support experience.</p>
<p class="ticket-ref">Ticket <?= esc($ticket['ticket_number']) ?>: <?= esc(mb_strimwidth($ticket['subject'], 0, 60, '…')) ?></p>
<form method="post" action="<?= base_url('its-rate/' . $token) ?>"><?= csrf_field() ?>
<div class="stars">
<?php for ($i = 5; $i >= 1; $i--) { ?><input type="radio" name="rating" id="r<?= $i ?>" value="<?= $i ?>" required><label for="r<?= $i ?>" title="<?= $i ?> star<?= $i > 1 ? 's' : '' ?>">★</label><?php } ?>
</div>
<textarea name="comment" placeholder="Any comments? (optional)" maxlength="500"></textarea>
<button type="submit">Submit rating</button>
</form>
</div>
</body></html>
