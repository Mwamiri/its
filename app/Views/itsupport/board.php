<?= view('itsupport/_header') ?>
<div class="section-heading"><div><p class="eyebrow">WORKFLOW</p><h2>Ticket board</h2></div><p class="muted">Drag a card to another column to change its status. Keyboard: focus a card and use the status menu.</p></div>
<div id="board-msg" class="muted" role="status" aria-live="polite"></div>
<div class="tw-flex tw-gap-3 tw-overflow-x-auto tw-pb-4" id="board" data-url="<?= base_url('its-board-move') ?>" data-token-name="<?= esc(csrf_token()) ?>" data-token="<?= esc(csrf_hash()) ?>">
<?php foreach ($statuses as $key => $label) { ?>
<section class="tw-flex tw-w-72 tw-shrink-0 tw-flex-col tw-rounded-2xl tw-border tw-border-line tw-bg-canvas tw-p-2" aria-label="<?= esc($label) ?>">
<h3 class="tw-m-2 tw-flex tw-items-center tw-justify-between tw-text-sm"><?= esc($label) ?> <span class="badge gray" data-count><?= count($cols[$key]) ?></span></h3>
<div class="tw-flex tw-min-h-24 tw-flex-1 tw-flex-col tw-gap-2 tw-rounded-xl tw-p-1" data-col="<?= esc($key) ?>">
<?php foreach ($cols[$key] as $t) { ?>
<article draggable="true" data-id="<?= (int) $t['id'] ?>" class="tw-cursor-grab tw-rounded-xl tw-border tw-border-line tw-bg-paper tw-p-3 tw-shadow-sm">
<a class="tw-text-sm tw-font-bold tw-text-ink" href="<?= base_url('its-tickets-view/' . $t['id']) ?>"><?= esc($t['ticket_number']) ?></a>
<p class="tw-m-0 tw-text-sm"><?= esc($t['subject']) ?></p>
<p class="tw-m-0 tw-mt-1 tw-flex tw-items-center tw-justify-between tw-text-xs tw-text-muted"><span><?= esc($t['client_name']) ?></span><span class="badge <?= in_array($t['priority'], ['high', 'urgent'], true) ? 'orange' : 'gray' ?>"><?= esc($t['priority']) ?></span></p>
<select aria-label="Move <?= esc($t['ticket_number']) ?> to" class="tw-mt-2 tw-text-xs" data-move><?php foreach ($statuses as $k => $l) { ?><option value="<?= esc($k) ?>" <?= $k === $key ? 'selected' : '' ?>><?= esc($l) ?></option><?php } ?></select>
</article>
<?php } ?>
</div></section>
<?php } ?>
</div>
<script>
(() => {
  const board = document.getElementById('board'), msg = document.getElementById('board-msg');
  let token = board.dataset.token, dragged = null;
  const recount = () => board.querySelectorAll('section').forEach(s => { s.querySelector('[data-count]').textContent = s.querySelectorAll('article').length; });
  async function move(card, col, from) {
    const status = col.dataset.col;
    col.appendChild(card); card.querySelector('[data-move]').value = status; recount();
    const body = new URLSearchParams({ id: card.dataset.id, status, [board.dataset.tokenName]: token });
    try {
      const r = await fetch(board.dataset.url, { method: 'POST', body, headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token } });
      const j = await r.json().catch(() => ({}));
      if (j.csrf) token = j.csrf;
      if (!r.ok || !j.ok) throw new Error(j.error || 'Could not save');
      msg.textContent = 'Saved.';
    } catch (e) { from.appendChild(card); card.querySelector('[data-move]').value = from.dataset.col; recount(); msg.textContent = 'Move failed: ' + e.message + ' (reverted).'; }
  }
  board.addEventListener('dragstart', e => { const c = e.target.closest('article'); if (!c) return; dragged = c; e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain', c.dataset.id); c.style.opacity = .5; });
  board.addEventListener('dragend', () => { if (dragged) dragged.style.opacity = ''; board.querySelectorAll('[data-col]').forEach(c => c.style.outline = ''); });
  board.addEventListener('dragover', e => { const col = e.target.closest('[data-col]'); if (col && dragged) { e.preventDefault(); col.style.outline = '2px dashed var(--brand)'; } });
  board.addEventListener('dragleave', e => { const col = e.target.closest('[data-col]'); if (col) col.style.outline = ''; });
  board.addEventListener('drop', e => { const col = e.target.closest('[data-col]'); if (!col || !dragged) return; e.preventDefault(); const from = dragged.parentElement; if (from !== col) move(dragged, col, from); dragged = null; });
  board.addEventListener('change', e => { const sel = e.target.closest('[data-move]'); if (!sel) return; const card = sel.closest('article'), from = card.parentElement, col = board.querySelector('[data-col="' + sel.value + '"]'); if (col && col !== from) move(card, col, from); });
})();
</script>
<?= view('itsupport/_footer') ?>