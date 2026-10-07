<?= view('itsupport/_header') ?>
<?php
$steps = ['new' => 'Received', 'in_progress' => 'In progress', 'waiting_parts' => 'Waiting parts', 'waiting_approval' => 'Needs your approval', 'completed' => 'Resolved', 'closed' => 'Closed'];
$name = session('its_user')['name'] ?? '';
$today = date('Y-m-d');
$warrantySoon = array_filter($assets, static fn($a) => !empty($a['warranty_until']) && $a['warranty_until'] >= $today && $a['warranty_until'] <= date('Y-m-d', strtotime('+60 days')));
?>
<section class="card pt-hero">
  <div class="pt-hero-id"><?php if (!empty($client['logo_path'])) { ?><img src="<?= esc(base_url($client['logo_path'])) ?>" alt=""><?php } ?>
    <div><p class="eyebrow"><?= esc($client['name'] ?? 'My support') ?></p><h2>Hello<?= $name !== '' ? ', ' . esc(explode(' ', $name)[0]) : '' ?></h2><p class="muted">Track requests, approve quotes and see your equipment in one place.</p></div></div>
  <div class="pt-actions"><a class="button" href="<?= base_url('its-portal-new') ?>">Report an issue</a><a class="button secondary" href="<?= base_url('its-portal-assets') ?>">My equipment</a><a class="button secondary" href="<?= base_url('its-portal-kb') ?>">Help articles</a></div>
</section>

<section class="pt-kpis" aria-label="Summary">
  <a class="card pt-kpi" href="#my-tickets"><span>Open requests</span><strong><?= count($openTickets) ?></strong></a>
  <a class="card pt-kpi<?= $attention ? ' is-hot' : '' ?>" href="#attention"><span>Needs your attention</span><strong><?= count($attention) ?></strong></a>
  <a class="card pt-kpi<?= $quotes ? ' is-hot' : '' ?>" href="#quotes"><span>Quotes to review</span><strong><?= count($quotes) ?></strong></a>
  <div class="card pt-kpi"><span>Resolved this month</span><strong><?= (int) $resolvedMonth ?></strong></div>
  <a class="card pt-kpi" href="<?= base_url('its-portal-assets') ?>"><span>Devices covered</span><strong><?= count($assets) ?></strong></a>
  <div class="card pt-kpi"><span>Your rating of us</span><strong><?= $avgRating !== null ? esc((string) $avgRating) . '<small>/5</small>' : '-' ?></strong></div>
</section>

<div class="grid grid-2">
  <section class="card" id="attention"><h3>Needs your attention</h3>
    <?php if (!$attention) { ?><p class="muted">Nothing needs you right now. 🎉</p><?php } ?>
    <?php foreach ($attention as $a) { $t = $a['ticket']; ?><a class="pt-row" href="<?= base_url('its-portal-ticket/' . $t['id']) ?>"><span class="badge orange"><?= esc($a['reason']) ?></span><span><strong><?= esc($t['ticket_number']) ?></strong> <?= esc($t['subject']) ?></span></a><?php } ?>
  </section>
  <section class="card" id="quotes"><h3>Quotes to review</h3>
    <?php if (!$quotes) { ?><p class="muted">No quotes are waiting for a decision.</p><?php } ?>
    <?php foreach ($quotes as $q) { ?><div class="pt-row"><span><strong><?= esc($q['quote_number']) ?></strong> <?= esc($q['subject']) ?><br><span class="muted">Total <?= number_format((float) $q['total'], 2) ?> · sent <?= esc(substr((string) $q['created_at'], 0, 10)) ?></span></span><a class="button" href="<?= esc($q['link']) ?>">Review</a></div><?php } ?>
  </section>
</div>

<div class="grid grid-2">
  <section class="card"><h3>Upcoming visits</h3>
    <?php if (!$visits) { ?><p class="muted">No visits scheduled.</p><?php } ?>
    <?php foreach ($visits as $v) { ?><a class="pt-row" href="<?= base_url('its-portal-ticket/' . $v['id']) ?>"><span class="pt-date"><strong><?= esc(date('d', strtotime($v['visit_date']))) ?></strong><?= esc(date('M', strtotime($v['visit_date']))) ?></span><span><strong><?= esc($v['ticket_number']) ?></strong> <?= esc($v['subject']) ?></span></a><?php } ?>
  </section>
  <section class="card"><h3>Latest replies from support</h3>
    <?php if (!$recent) { ?><p class="muted">No replies yet.</p><?php } ?>
    <?php foreach ($recent as $u) { ?><a class="pt-row" href="<?= base_url('its-portal-ticket/' . $u['ticket_id']) ?>"><span><strong><?= esc($u['ticket_number']) ?></strong> · <?= esc($u['author_name']) ?><br><span class="muted"><?= esc(mb_strimwidth((string) $u['message'], 0, 110, '…')) ?></span></span><span class="muted"><?= esc(substr((string) $u['created_at'], 0, 10)) ?></span></a><?php } ?>
  </section>
</div>

<?php if ($warrantySoon || $forms) { ?><div class="grid grid-2">
  <?php if ($warrantySoon) { ?><section class="card"><h3>Warranty ending soon</h3><?php foreach ($warrantySoon as $w) { ?><div class="pt-row"><span><strong><?= esc($w['name']) ?></strong> <span class="muted"><?= esc($w['serial_number']) ?></span></span><span class="badge orange"><?= esc($w['warranty_until']) ?></span></div><?php } ?></section><?php } ?>
  <?php if ($forms) { ?><section class="card"><h3>Forms</h3><?php foreach ($forms as $f) { ?><a class="pt-row" href="<?= base_url('its-form/' . $f['id']) ?>"><span><strong><?= esc($f['title']) ?></strong><?php if ($f['description']) { ?><br><span class="muted"><?= esc(mb_strimwidth((string) $f['description'], 0, 100, '…')) ?></span><?php } ?></span><span class="button secondary">Open</span></a><?php } ?></section><?php } ?>
</div><?php } ?>

<section class="card" id="my-tickets"><div class="section-heading"><h3>My requests</h3>
  <div class="pt-filter" role="group" aria-label="Filter requests"><button type="button" data-f="open" aria-pressed="true">Open</button><button type="button" data-f="done" aria-pressed="false">Resolved</button><button type="button" data-f="all" aria-pressed="false">All</button></div></div>
<div class="tbl"><table id="pt-table"><tr><th>Request</th><th>Subject</th><th>Priority</th><th>Status</th><th>Opened</th></tr>
<?php foreach ($tickets as $t) { $isDone = in_array($t['status'], ['completed', 'closed'], true); ?>
<tr data-done="<?= $isDone ? '1' : '0' ?>"><td><a href="<?= base_url('its-portal-ticket/' . $t['id']) ?>"><?= esc($t['ticket_number']) ?></a></td><td><?= esc($t['subject']) ?></td><td><?= esc($t['priority']) ?></td><td><span class="badge <?= $isDone ? 'green' : ($t['status'] === 'waiting_approval' ? 'orange' : 'blue') ?>"><?= esc($steps[$t['status']] ?? $t['status']) ?></span></td><td><?= esc(substr((string) $t['created_at'], 0, 10)) ?></td></tr>
<?php } ?>
<tr data-empty hidden><td colspan="5" class="muted">No requests here.</td></tr>
</table></div></section>
<style>
.pt-hero{display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:14px}.pt-hero-id{display:flex;gap:14px;align-items:center}.pt-hero-id img{max-width:90px;max-height:56px;object-fit:contain}.pt-hero h2,.pt-hero p{margin:0}.pt-actions{display:flex;flex-wrap:wrap;gap:8px}
.pt-kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,150px),1fr));gap:12px;margin-bottom:16px}.pt-kpi{display:flex;flex-direction:column;gap:4px;margin:0;text-decoration:none;color:inherit}.pt-kpi span{font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.04em}.pt-kpi strong{font-size:1.8rem}.pt-kpi small{font-size:.9rem;color:var(--muted)}.pt-kpi.is-hot{border-color:#f59e0b;box-shadow:inset 4px 0 #f59e0b}
.pt-row{display:flex;justify-content:space-between;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--line);text-decoration:none;color:inherit}.pt-row:last-child{border-bottom:0}a.pt-row:hover{background:var(--brand-soft)}
.pt-date{display:grid;place-items:center;min-width:46px;padding:4px;border-radius:10px;background:var(--brand-soft);font-size:11px;text-transform:uppercase}.pt-date strong{font-size:18px;line-height:1}
.pt-filter{display:flex;gap:6px}.pt-filter button{border:1px solid var(--line);background:var(--paper);color:var(--ink);border-radius:999px;padding:6px 12px;min-height:0;font-size:13px;cursor:pointer}.pt-filter button[aria-pressed=true]{background:var(--brand);border-color:var(--brand);color:#fff}
</style>
<script>
(()=>{const rows=[...document.querySelectorAll('#pt-table tr[data-done]')],empty=document.querySelector('#pt-table [data-empty]'),btns=[...document.querySelectorAll('.pt-filter button')];
const apply=f=>{let n=0;rows.forEach(r=>{const show=f==='all'||(f==='done')===(r.dataset.done==='1');r.hidden=!show;if(show)n++;});empty.hidden=n>0;btns.forEach(b=>b.setAttribute('aria-pressed',String(b.dataset.f===f)));};
btns.forEach(b=>b.addEventListener('click',()=>apply(b.dataset.f)));apply('open');})();
</script>
<?= view('itsupport/_footer') ?>
