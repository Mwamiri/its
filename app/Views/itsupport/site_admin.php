<?= view('itsupport/_header') ?>
<?php
use App\Libraries\Site;
$tabs = ['pages' => 'Pages (builder)', 'menu' => 'Menu', 'content' => 'Content', 'design' => 'Design', 'settings' => 'Settings & SEO', 'leads' => 'Leads' . ($newLeads ? " ($newLeads new)" : '')];
$ep = $editPage ?: [];
$ei = $editItem ?: [];
$secSel = array_filter(explode(',', (string) ($ep['sections'] ?? 'cta')));
$kindLabel = Site::KINDS[$kind][0];
$pv = static fn(array $row, string $k, string $d = '') => (string) (old($k) ?? ($row[$k] ?? $d));
?>
<div class="section-heading"><div><p class="eyebrow">Website CMS</p><h2>Website</h2><p class="muted">Edit the public website at <a href="<?= base_url() ?>" target="_blank" rel="noopener"><?= esc(base_url()) ?></a>. Text supports simple formatting: <code>## Heading</code>, <code>- list item</code>, <code>**bold**</code>, <code>[link](https://…)</code>.</p></div>
<a class="button" href="<?= base_url() ?>" target="_blank" rel="noopener">View website ↗</a></div>
<div class="view-tabs" role="navigation" aria-label="Website sections"><?php foreach ($tabs as $k => $l) { ?><a href="<?= base_url('its-site?tab=' . $k) ?>" <?= $tab === $k ? 'aria-current="page"' : '' ?>><?= esc($l) ?></a><?php } ?></div>


<?php if ($tab === 'pages') { ?>
<?php $isNew = (string) service('request')->getGet('edit') === '0'; ?>
<?php if (!$editPage && !$isNew) { ?>
<div class="card"><div class="section-heading"><div><h3 style="margin:0">Pages</h3><p class="muted" style="margin:0">Click a page to open it in the drag-and-drop builder.</p></div><a class="button" href="<?= base_url('its-site?tab=pages&edit=0') ?>">+ New page</a></div>
<div class="tbl"><table><tr><th>Page</th><th>Address</th><th>Menu</th><th>Status</th><th>Updated</th><th></th></tr>
<?php foreach ($pages as $pg) { ?><tr><td><strong><?= esc($pg['title']) ?></strong><?= $pg['is_system'] ? ' <span class="badge gray">built-in</span>' : '' ?></td><td><a href="<?= esc(Site::url($pg['slug'])) ?>" target="_blank" rel="noopener">/<?= esc($pg['slug'] === 'home' ? '' : $pg['slug']) ?></a></td><td><?= $pg['show_in_menu'] ? (int) $pg['menu_order'] : '<span class="muted">hidden</span>' ?></td><td><span class="badge <?= $pg['published'] ? 'green' : 'gray' ?>"><?= $pg['published'] ? 'Published' : 'Draft' ?></span></td><td class="muted"><?= esc(substr((string) $pg['updated_at'], 0, 16)) ?></td><td><a class="button" href="<?= base_url('its-site?tab=pages&edit=' . $pg['id']) ?>">Edit in builder</a></td></tr><?php } ?></table></div></div>
<?php } else { ?>
<form method="post" action="<?= base_url('its-site-page') ?>" id="bb-form"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) ($ep['id'] ?? 0) ?>"><input type="hidden" name="blocks" id="bb-json">
<div class="bb-top card"><div><a href="<?= base_url('its-site?tab=pages') ?>">← All pages</a> <strong class="bb-name"><?= esc($ep['title'] ?? 'New page') ?></strong></div>
<div class="quick-actions"><span class="muted" data-bb-status>All changes previewed live</span><?php if ($ep) { ?><a class="button secondary" href="<?= esc(Site::url($ep['slug'])) ?>" target="_blank" rel="noopener">Open live page ↗</a><?php } ?><button class="btn">Save &amp; publish</button></div></div>
<div class="bb">
<div class="bb-side">
  <details class="card bb-settings" <?= $ep ? '' : 'open' ?>><summary><strong>Page settings &amp; SEO</strong></summary>
  <div class="form-grid">
  <div class="field"><label for="pg-title">Page title</label><input id="pg-title" name="title" maxlength="190" required value="<?= esc($pv($ep, 'title')) ?>" data-pv></div>
  <div class="field"><label for="pg-slug">Address</label><input id="pg-slug" name="slug" maxlength="80" value="<?= esc($pv($ep, 'slug')) ?>" <?= !empty($ep['is_system']) ? 'readonly' : '' ?> placeholder="auto from title" data-pv></div>
  <div class="field"><label for="pg-menu">Menu label</label><input id="pg-menu" name="menu_label" maxlength="60" value="<?= esc($pv($ep, 'menu_label')) ?>" data-pv></div>
  <div class="field"><label for="pg-order">Menu position</label><input id="pg-order" type="number" min="0" max="999" name="menu_order" value="<?= esc($pv($ep, 'menu_order', '80')) ?>" data-pv></div>
  <div class="field wide"><label for="pg-mt">SEO title <span class="muted" data-count="pg-mt" data-max="60"></span></label><input id="pg-mt" name="meta_title" maxlength="190" value="<?= esc($pv($ep, 'meta_title')) ?>" data-pv></div>
  <div class="field wide"><label for="pg-md">SEO description <span class="muted" data-count="pg-md" data-max="160"></span></label><textarea id="pg-md" name="meta_description" rows="2" maxlength="300" data-pv><?= esc($pv($ep, 'meta_description')) ?></textarea></div>
  </div>
  <div class="serp" aria-label="Search result preview"><span class="serp-url"><?= esc(rtrim(base_url(), '/')) ?>/<?= esc(($ep['slug'] ?? '') === 'home' ? '' : ($ep['slug'] ?? '')) ?></span><span class="serp-title" data-serp-title></span><span class="serp-desc" data-serp-desc></span></div>
  <label class="check-line"><input type="checkbox" name="show_in_menu" value="1" <?= ($ep['show_in_menu'] ?? 1) ? 'checked' : '' ?>> Show in the menu</label>
  <label class="check-line"><input type="checkbox" name="published" value="1" <?= ($ep['published'] ?? 1) ? 'checked' : '' ?> <?= ($ep['slug'] ?? '') === 'home' ? 'disabled' : '' ?>> Published</label>
  <?php if ($ep && !$ep['is_system']) { ?><p><button class="btn danger" type="submit" form="bb-delete">Delete this page</button></p><?php } ?>
  </details>
  <div class="card"><div class="section-heading"><h3 style="margin:0">Blocks</h3><span class="muted">Drag ⋮⋮ to reorder</span></div>
  <ol class="bb-list" id="bb-list"></ol>
  <details class="bb-add"><summary class="btn secondary">+ Add block</summary><div class="bb-palette"><?php foreach (Site::BLOCKS as $bt => [$bl]) { ?><button type="button" class="bb-pal" data-add="<?= $bt ?>"><?= esc($bl) ?></button><?php } ?></div></details>
  <p class="muted" style="margin:10px 0 0">Text fields accept simple formatting: <code>## Heading</code>, <code>- list</code>, <code>**bold**</code>, <code>[link](https://…)</code>. Content blocks (services, products…) show items from the <a href="<?= base_url('its-site?tab=content') ?>">Content</a> tab.</p></div>
</div>
<div class="bb-preview card"><div class="bb-devices" role="group" aria-label="Preview size"><button type="button" data-w="100%" aria-pressed="true">Desktop</button><button type="button" data-w="820px" aria-pressed="false">Tablet</button><button type="button" data-w="400px" aria-pressed="false">Phone</button><button type="button" data-refresh>↻ Refresh</button></div>
<div class="bb-frame-wrap"><iframe name="bb-frame" id="bb-frame" title="Live preview" src="about:blank"></iframe></div></div>
</div></form>
<?php if ($ep && !$ep['is_system']) { ?><form method="post" action="<?= base_url('its-site-page-delete/' . $ep['id']) ?>" id="bb-delete" onsubmit="return confirm('Delete this page?')"><?= csrf_field() ?></form><?php } ?>
<form method="post" action="<?= base_url('its-site-preview') ?>" target="bb-frame" id="bb-preview" style="display:none"><input type="hidden" name="blocks"><input type="hidden" name="title"><input type="hidden" name="slug"><input type="hidden" name="menu_label"><input type="hidden" name="meta_title"><input type="hidden" name="meta_description"></form>
<script id="bb-data" type="application/json"><?= json_encode(['defs' => Site::BLOCKS, 'bgs' => Site::BACKGROUNDS, 'blocks' => $editBlocks, 'upload' => base_url('its-site-upload'), 'csrfName' => csrf_token()], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<script>
(() => {
  const D = JSON.parse(document.getElementById('bb-data').textContent);
  const list = document.getElementById('bb-list'), form = document.getElementById('bb-form'), pform = document.getElementById('bb-preview'), frame = document.getElementById('bb-frame'), status = document.querySelector('[data-bb-status]');
  const el = (tag, attrs = {}, kids = []) => { const n = document.createElement(tag); for (const [k, v] of Object.entries(attrs)) { if (k === 'text') n.textContent = v; else if (k in n && k !== 'list') n[k] = v; else n.setAttribute(k, v); } kids.forEach(c => c && n.append(c)); return n; };
  const uid = () => Math.random().toString(16).slice(2, 12);
  const summary = b => (b.title || b.text || b.subtitle || '').toString().replace(/[#*\n]+/g, ' ').trim().slice(0, 60);
  let dragged = null, timer = null, dirty = false;

  function field(li, b, key, def) {
    const [label, type, opts] = def, id = 'f-' + b.id + '-' + key, wrap = el('div', {className: 'field' + (['markdown', 'textarea', 'image'].includes(type) ? ' wide' : '')});
    let input;
    if (type === 'bool') { input = el('input', {type: 'checkbox', id, checked: !!b[key]}); wrap.append(el('label', {className: 'check-line', for: id}, [input, document.createTextNode(' ' + label)])); }
    else {
      wrap.append(el('label', {for: id, text: label}));
      if (type === 'select') { input = el('select', {id}); for (const [v, t] of Object.entries(opts)) input.add(new Option(t, v, false, b[key] === v)); }
      else if (type === 'markdown' || type === 'textarea') input = el('textarea', {id, rows: type === 'markdown' ? 7 : 3, value: b[key] || ''});
      else if (type === 'number') input = el('input', {id, type: 'number', min: 0, max: 60, value: b[key] || 0});
      else if (type === 'image') {
        input = el('input', {type: 'hidden', id, value: b[key] || ''});
        const thumb = el('img', {className: 'bb-thumb', alt: ''}); if (b[key]) thumb.src = D.upload.replace(/its-site-upload$/, '') + b[key];
        const file = el('input', {type: 'file', accept: 'image/png,image/jpeg,image/gif,image/webp'});
        const clear = el('button', {type: 'button', className: 'btn secondary', text: 'Remove'});
        file.addEventListener('change', async () => {
          if (!file.files[0]) return; const fd = new FormData(); fd.append('image', file.files[0]); fd.append(D.csrfName, document.querySelector('#bb-form input[name="' + D.csrfName + '"]').value);
          status.textContent = 'Uploading image…';
          try { const r = await fetch(D.upload, {method: 'POST', body: fd, headers: {'X-Requested-With': 'XMLHttpRequest'}, credentials: 'same-origin'}); const j = await r.json(); if (j.csrf) document.querySelectorAll('input[name="' + D.csrfName + '"]').forEach(i => i.value = j.csrf); if (!r.ok) throw new Error(j.error || 'Upload failed'); input.value = j.path; thumb.src = j.url; changed(); }
          catch (e) { status.textContent = e.message; } file.value = '';
        });
        clear.addEventListener('click', () => { input.value = ''; thumb.removeAttribute('src'); changed(); });
        wrap.append(input, el('div', {className: 'bb-img'}, [thumb, file, clear])); input.dataset.key = key; return wrap;
      }
      else input = el('input', {id, value: b[key] || '', maxLength: 255});
      wrap.append(input);
    }
    input.dataset.key = key; return wrap;
  }

  function card(b) {
    const def = D.defs[b.type]; if (!def) return null;
    b.id = b.id || uid();
    const li = el('li', {className: 'bb-item'}); li.dataset.type = b.type; li.dataset.id = b.id;
    const handle = el('span', {className: 'bb-handle', title: 'Drag to move', text: '⋮⋮'});
    const title = el('strong', {className: 'bb-title', text: def[0]}), sum = el('span', {className: 'bb-sum muted', text: summary(b)});
    const tools = el('span', {className: 'bb-tools'}, [['up', '↑', 'Move up'], ['down', '↓', 'Move down'], ['dup', '⧉', 'Duplicate'], ['del', '✕', 'Remove']].map(([a, t, l]) => { const btn = el('button', {type: 'button', text: t, title: l}); btn.dataset.act = a; btn.setAttribute('aria-label', l); return btn; }));
    const head = el('div', {className: 'bb-head'}, [handle, el('span', {className: 'bb-label'}, [title, sum]), tools]);
    const body = el('div', {className: 'bb-body form-grid'});
    for (const [k, d] of Object.entries(def[1])) body.append(field(li, b, k, d));
    body.append(field(li, b, 'bg', ['Background', 'select', D.bgs]), field(li, b, 'anchor', ['Anchor (optional, for #links)', 'text']));
    head.addEventListener('click', e => { if (!e.target.closest('button') && !e.target.closest('.bb-handle')) li.classList.toggle('open'); });
    handle.addEventListener('mousedown', () => li.draggable = true); handle.addEventListener('touchstart', () => li.draggable = true, {passive: true});
    li.addEventListener('dragstart', e => { dragged = li; li.classList.add('dragging'); e.dataTransfer.effectAllowed = 'move'; e.dataTransfer.setData('text/plain', b.id); });
    li.addEventListener('dragend', () => { li.draggable = false; li.classList.remove('dragging'); dragged = null; changed(); });
    li.append(head, body); return li;
  }

  function read(li) {
    const b = {type: li.dataset.type, id: li.dataset.id};
    li.querySelectorAll('[data-key]').forEach(i => { b[i.dataset.key] = i.type === 'checkbox' ? i.checked : i.value; });
    return b;
  }
  const serialize = () => JSON.stringify([...list.children].map(read));
  function refreshMeta() { [...list.children].forEach((li, i, all) => { li.querySelector('[data-act="up"]').disabled = i === 0; li.querySelector('[data-act="down"]').disabled = i === all.length - 1; li.querySelector('.bb-sum').textContent = summary(read(li)); }); }
  function preview() {
    pform.querySelector('[name="blocks"]').value = serialize();
    ['title', 'slug', 'menu_label', 'meta_title', 'meta_description'].forEach(n => { const s = form.querySelector('[name="' + n + '"]'); pform.querySelector('[name="' + n + '"]').value = s ? s.value : ''; });
    const y = frame.contentWindow && frame.contentWindow.scrollY || 0; frame.dataset.y = y;
    pform.submit(); status.textContent = dirty ? 'Unsaved changes, previewed live' : 'All changes previewed live';
  }
  function changed() { dirty = true; refreshMeta(); clearTimeout(timer); timer = setTimeout(preview, 700); }
  frame.addEventListener('load', () => { try { frame.contentWindow.scrollTo(0, +frame.dataset.y || 0); } catch (e) {} });

  list.addEventListener('dragover', e => { if (!dragged) return; e.preventDefault(); const after = [...list.children].find(c => c !== dragged && e.clientY < c.getBoundingClientRect().top + c.offsetHeight / 2); after ? list.insertBefore(dragged, after) : list.append(dragged); });
  list.addEventListener('input', changed); list.addEventListener('change', changed);
  list.addEventListener('click', e => {
    const btn = e.target.closest('[data-act]'); if (!btn) return; const li = btn.closest('.bb-item');
    if (btn.dataset.act === 'up' && li.previousElementSibling) li.previousElementSibling.before(li);
    if (btn.dataset.act === 'down' && li.nextElementSibling) li.nextElementSibling.after(li);
    if (btn.dataset.act === 'dup') { const b = read(li); b.id = uid(); li.after(card(b)); }
    if (btn.dataset.act === 'del' && confirm('Remove this block?')) li.remove();
    changed();
  });
  document.querySelectorAll('[data-add]').forEach(b => b.addEventListener('click', () => { const li = card({type: b.dataset.add}); li.classList.add('open'); list.append(li); b.closest('details').open = false; changed(); li.scrollIntoView({behavior: 'smooth', block: 'center'}); }));
  document.querySelectorAll('[data-pv]').forEach(i => i.addEventListener('input', changed));
  document.querySelectorAll('[data-w]').forEach(b => b.addEventListener('click', () => { frame.style.width = b.dataset.w; document.querySelectorAll('[data-w]').forEach(x => x.setAttribute('aria-pressed', String(x === b))); }));
  document.querySelector('[data-refresh]').addEventListener('click', preview);
  form.addEventListener('submit', () => { document.getElementById('bb-json').value = serialize(); dirty = false; });
  window.addEventListener('beforeunload', e => { if (dirty) { e.preventDefault(); e.returnValue = ''; } });
  D.blocks.forEach(b => { const li = card(b); if (li) list.append(li); });
  refreshMeta(); preview();
  // SEO snippet preview
  const t = document.getElementById('pg-mt'), d = document.getElementById('pg-md'), tt = document.getElementById('pg-title'), st = document.querySelector('[data-serp-title]'), sd = document.querySelector('[data-serp-desc]'), site = <?= json_encode($site['name'], JSON_HEX_TAG | JSON_HEX_AMP) ?>;
  const upd = () => { st.textContent = (t.value || ((tt.value || 'Page') + ' | ' + site)).slice(0, 70); const hero = [...list.children].map(read).find(x => x.type === 'hero'); sd.textContent = (d.value || (hero && hero.subtitle) || '').slice(0, 165); document.querySelectorAll('[data-count]').forEach(c => { const n = document.getElementById(c.dataset.count).value.length, max = +c.dataset.max; c.textContent = '(' + n + '/' + max + ')'; c.style.color = n > max ? '#b45309' : ''; }); };
  [t, d, tt].forEach(x => x.addEventListener('input', upd)); list.addEventListener('input', upd); upd();
})();
</script>
<?php } ?>

<?php } elseif ($tab === 'design') { $grp = ['header' => 'Header', 'body' => 'Body & buttons', 'footer' => 'Footer & advanced']; ?>
<form method="post" action="<?= base_url('its-site-design') ?>"><?= csrf_field() ?>
<div class="grid grid-2">
<?php foreach ($grp as $g => $gl) { ?><div class="card"><h3><?= esc($gl) ?></h3><div class="form-grid">
<?php foreach (Site::DESIGN as $k => [$l, $type, $opt, $group]) { if ($group !== $g) continue; $short = substr($k, 5); $cur = $design[$short]; ?>
<div class="field<?= in_array($type, ['textarea', 'css', 'order'], true) ? ' wide' : '' ?>">
<?php if ($type === 'bool') { ?><label class="check-line" style="margin-top:22px"><input type="checkbox" name="<?= $k ?>" value="1" <?= $cur ? 'checked' : '' ?>> <?= esc($l) ?></label>
<?php } else { ?><label for="d-<?= $k ?>"><?= esc($l) ?></label>
<?php if ($type === 'select') { ?><select id="d-<?= $k ?>" name="<?= $k ?>"><?php foreach ($opt as $ov => $ol) { ?><option value="<?= esc($ov) ?>" <?= $cur === $ov ? 'selected' : '' ?>><?= esc($ol) ?></option><?php } ?></select>
<?php } elseif ($type === 'number') { ?><input id="d-<?= $k ?>" type="number" name="<?= $k ?>" min="0" max="200" value="<?= (int) $cur ?>">
<?php } elseif ($type === 'textarea' || $type === 'css') { ?><textarea id="d-<?= $k ?>" name="<?= $k ?>" rows="<?= $type === 'css' ? 6 : 3 ?>" <?= $type === 'css' ? 'spellcheck="false" style="font-family:monospace;font-size:12px" placeholder=".hero h1 { letter-spacing: 0 }"' : '' ?>><?= esc((string) $cur) ?></textarea>
<?php } elseif ($type === 'order') {
    $secOpts = ['menu' => 'Navigation links', 'services' => 'Services list', 'custom' => 'Custom links', 'contact' => 'Contact info', 'support' => '"Get support" section'];
    $ordArr = is_array($cur) ? $cur : array_filter(array_map('trim', explode(',', $cur)));
    foreach (array_keys($secOpts) as $os) if (!in_array($os, $ordArr, true)) $ordArr[] = $os;
?><input type="hidden" id="fo-hidden-<?= $k ?>" name="<?= $k ?>" value="<?= esc(implode(',', $ordArr)) ?>">
<div id="fo-list-<?= $k ?>" data-fo style="display:grid;gap:5px;margin-top:6px">
<?php foreach ($ordArr as $os) { if (!isset($secOpts[$os])) continue; ?><div class="fo-row" data-sec="<?= esc($os) ?>" draggable="true" style="display:flex;align-items:center;gap:8px;padding:7px 10px;border:1px solid var(--line);border-radius:8px;background:var(--paper);cursor:grab;user-select:none"><span style="color:var(--muted);font-weight:700;letter-spacing:-2px">⋮⋮</span><span style="flex:1;font-size:13px"><?= esc($secOpts[$os]) ?></span></div><?php } ?>
</div>
<p class="muted" style="font-size:12px;margin:4px 0 0">Drag to change the column order. Use the toggles above to show or hide each section.</p>
<?php } else { ?><input id="d-<?= $k ?>" name="<?= $k ?>" maxlength="255" value="<?= esc((string) $cur) ?>"><?php } ?>
<?php } ?></div>
<?php } ?></div></div><?php } ?>
<div class="card"><h3>Colours, logo &amp; contact details</h3><p class="muted">Brand colours, contact details and SEO defaults are on the <a href="<?= base_url('its-site?tab=settings') ?>">Settings &amp; SEO</a> tab; the logo comes from Admin → Branding &amp; Settings.</p>
<div class="form-actions"><button class="btn">Save design</button><a class="button secondary" href="<?= base_url() ?>" target="_blank" rel="noopener">View website ↗</a></div></div>
</div></form>
<script>
document.querySelectorAll('[data-fo]').forEach(function(list) {
  var key = list.id.replace('fo-list-', ''), inp = document.getElementById('fo-hidden-' + key), drag = null;
  function upd() { if (inp) inp.value = [...list.querySelectorAll('[data-sec]')].map(r => r.dataset.sec).join(','); }
  list.addEventListener('dragstart', function(e) { drag = e.target.closest('.fo-row'); if (drag) { drag.style.opacity = '.4'; e.dataTransfer.effectAllowed = 'move'; } });
  list.addEventListener('dragend', function() { if (drag) drag.style.opacity = ''; drag = null; upd(); });
  list.addEventListener('dragover', function(e) { e.preventDefault(); var t = e.target.closest('.fo-row'); if (!t || t === drag) return; var r = t.getBoundingClientRect(); list.insertBefore(drag, e.clientY < r.top + r.height / 2 ? t : t.nextSibling); });
});
</script>

<?php } elseif ($tab === 'menu') { ?>
<div class="card">
<h3>Menu builder</h3>
<p class="muted">Set the order and visibility of pages in the main navigation. Add custom links (for example to external sites or page anchors) below the page list.</p>
<?php $hasParent = \Config\Database::connect()->fieldExists('menu_parent', 'site_pages'); ?>
<form method="post" action="<?= base_url('its-site-menu') ?>"><?= csrf_field() ?>
<h4 style="margin:16px 0 8px">Page items</h4>
<p class="muted" style="margin:0 0 8px">Drag <strong>⋮⋮</strong> to reorder. Drag auto-renumbers the Order field. Sub-menu items are indented under their parent in the nav.</p>
<div class="tbl"><table id="mn-pages">
<tr><th style="width:28px"></th><th>Page</th><th>Menu label <span class="muted">(blank = page title)</span></th><th>Order</th><?php if ($hasParent) { ?><th>Sub-menu of</th><?php } ?><th>Show</th></tr>
<?php foreach ($pages as $pg) { ?>
<tr>
  <td class="mn-handle" style="cursor:grab;color:var(--muted);font-weight:700;letter-spacing:-2px;user-select:none">⋮⋮</td>
  <td><strong><?= esc($pg['title']) ?></strong><?= $pg['is_system'] ? ' <span class="badge gray">built-in</span>' : '' ?><br><a class="muted" style="font-size:12px" href="<?= esc(Site::url($pg['slug'])) ?>" target="_blank" rel="noopener">/<?= esc($pg['slug'] === 'home' ? '' : $pg['slug']) ?></a></td>
  <td><input name="pages[<?= (int) $pg['id'] ?>][label]" value="<?= esc($pg['menu_label'] ?? '') ?>" placeholder="<?= esc($pg['title']) ?>" maxlength="60" style="width:140px"></td>
  <td><input type="number" name="pages[<?= (int) $pg['id'] ?>][order]" value="<?= (int) $pg['menu_order'] ?>" min="0" max="999" style="width:60px"></td>
  <?php if ($hasParent) { ?><td><select name="pages[<?= (int) $pg['id'] ?>][parent]" style="width:150px"><option value="">— top level —</option><?php foreach ($pages as $pp) { if ($pp['id'] === $pg['id']) continue; ?><option value="<?= esc($pp['slug']) ?>" <?= ($pg['menu_parent'] ?? '') === $pp['slug'] ? 'selected' : '' ?>><?= esc($pp['title']) ?></option><?php } ?></select></td><?php } ?>
  <td style="text-align:center"><label class="check-line" style="justify-content:center;margin:0"><input type="checkbox" name="pages[<?= (int) $pg['id'] ?>][show]" value="1" <?= $pg['show_in_menu'] ? 'checked' : '' ?>></label></td>
</tr>
<?php } ?>
</table></div>
<h4 style="margin:20px 0 4px">Custom links</h4>
<p class="muted" style="margin:0 0 10px">Links to external sites, anchors or pages not listed above. Set "Sub-menu of" to nest a link under a page item.</p>
<?php $menuCustom = json_decode((string) \App\Models\SettingModel::get('site_menu_custom', ''), true) ?: [];
$parentOptHtml = '<option value="">— top level —</option>' . implode('', array_map(fn($p) => '<option value="' . esc($p['slug']) . '">' . esc($p['title']) . '</option>', $pages)); ?>
<div id="mn-links">
<?php foreach ($menuCustom as $cl) { ?>
<div class="mn-row" style="display:flex;gap:8px;align-items:center;margin-bottom:8px;flex-wrap:wrap">
  <input name="link_label[]" placeholder="Label" value="<?= esc($cl['label'] ?? '') ?>" maxlength="60" style="width:140px">
  <input name="link_url[]" placeholder="https://…" value="<?= esc($cl['url'] ?? '') ?>" maxlength="500" style="flex:1;min-width:160px">
  <select name="link_parent[]" style="width:150px"><?php foreach ($pages as $pp) { ?><option value="<?= esc($pp['slug']) ?>" <?= ($cl['parent'] ?? '') === $pp['slug'] ? 'selected' : '' ?>><?= esc($pp['title']) ?></option><?php } ?><?= $parentOptHtml ?></select>
  <input type="number" name="link_order[]" value="<?= (int) ($cl['order'] ?? 80) ?>" min="0" max="999" style="width:60px" title="Order">
  <button type="button" class="btn secondary" onclick="this.closest('.mn-row').remove()" title="Remove">✕</button>
</div>
<?php } ?>
</div>
<button type="button" id="mn-add" class="btn secondary" style="margin-top:4px">+ Add link</button>
<div class="form-actions"><button class="btn">Save menu</button><a class="button secondary" href="<?= base_url() ?>" target="_blank" rel="noopener">View website ↗</a></div>
</form>
</div>
<script>
(function() {
  var pSel = <?= json_encode('<select name="link_parent[]" style="width:150px">' . $parentOptHtml . '</select>', JSON_HEX_TAG | JSON_HEX_AMP) ?>;
  document.getElementById('mn-add').addEventListener('click', function() {
    var r = document.createElement('div'); r.className = 'mn-row';
    r.style.cssText = 'display:flex;gap:8px;align-items:center;margin-bottom:8px;flex-wrap:wrap';
    r.innerHTML = '<input name="link_label[]" placeholder="Label" maxlength="60" style="width:140px"><input name="link_url[]" placeholder="https://…" maxlength="500" style="flex:1;min-width:160px">' + pSel + '<input type="number" name="link_order[]" value="80" min="0" max="999" style="width:60px" title="Order"><button type="button" class="btn secondary" onclick="this.closest(\'.mn-row\').remove()" title="Remove">✕</button>';
    document.getElementById('mn-links').appendChild(r); r.querySelector('input').focus();
  });
  // Drag-to-reorder pages table
  var tbody = document.querySelector('#mn-pages tbody'), drag = null;
  if (!tbody) return;
  tbody.querySelectorAll('.mn-handle').forEach(function(td) {
    td.addEventListener('mousedown', function() { td.closest('tr').draggable = true; });
    td.addEventListener('touchstart', function() { td.closest('tr').draggable = true; }, {passive: true});
  });
  tbody.addEventListener('dragstart', function(e) { drag = e.target.closest('tr'); if (drag) { drag.style.opacity = '.45'; e.dataTransfer.effectAllowed = 'move'; } });
  tbody.addEventListener('dragend', function() { if (drag) { drag.style.opacity = ''; drag.draggable = false; } drag = null; renumber(); });
  tbody.addEventListener('dragover', function(e) { e.preventDefault(); var over = e.target.closest('tr'); if (!over || over === drag) return; var r = over.getBoundingClientRect(); tbody.insertBefore(drag, e.clientY < r.top + r.height / 2 ? over : over.nextSibling); });
  function renumber() { [...tbody.querySelectorAll('tr')].forEach(function(r, i) { var inp = r.querySelector('input[type=number]'); if (inp) inp.value = (i + 1) * 10; }); }
})();
</script>

<?php } elseif ($tab === 'content') { ?>
<div class="view-tabs" role="navigation" aria-label="Content types"><?php foreach (Site::KINDS as $k => [$l]) { ?><a href="<?= base_url('its-site?tab=content&kind=' . $k) ?>" <?= $kind === $k ? 'aria-current="page"' : '' ?>><?= esc($l) ?> <span class="muted">(<?= (int) ($counts[$k] ?? 0) ?>)</span></a><?php } ?></div>
<div class="grid grid-2">
<div class="card"><div class="section-heading"><div><h3 style="margin:0"><?= esc($kindLabel) ?></h3><p class="muted" style="margin:0"><?= esc(Site::KINDS[$kind][1]) ?></p></div><a class="button secondary" href="<?= base_url('its-site?tab=content&kind=' . $kind) ?>">+ Add</a></div>
<div class="tbl"><table><tr><th></th><th>Title</th><th>Order</th><th>Status</th><th></th></tr>
<?php foreach ($items as $it) { ?><tr><td style="width:40px;color:var(--brand)"><?= $it['image_path'] ? '<img src="' . esc(base_url($it['image_path'])) . '" alt="" style="width:34px;height:34px;object-fit:cover;border-radius:6px">' : Site::icon($it['icon'], 'ic-sm') ?></td><td><strong><?= esc($it['title']) ?></strong><?= $it['subtitle'] ? '<br><span class="muted">' . esc($it['subtitle']) . '</span>' : '' ?></td><td><?= (int) $it['sort_order'] ?></td><td><span class="badge <?= $it['active'] ? 'green' : 'gray' ?>"><?= $it['active'] ? 'Live' : 'Hidden' ?></span><?= $it['featured'] ? ' <span class="badge blue">home</span>' : '' ?></td><td><a class="button secondary" href="<?= base_url('its-site?tab=content&kind=' . $kind . '&edit=' . $it['id']) ?>">Edit</a></td></tr><?php } ?>
<?php if (!$items) { ?><tr><td colspan="5" class="muted">Nothing here yet.</td></tr><?php } ?></table></div></div>
<div class="card"><h3><?= $ei ? 'Edit "' . esc($ei['title']) . '"' : 'Add to ' . esc(strtolower($kindLabel)) ?></h3>
<form method="post" action="<?= base_url('its-site-item') ?>" enctype="multipart/form-data"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) ($ei['id'] ?? 0) ?>"><input type="hidden" name="kind" value="<?= esc($kind) ?>">
<div class="form-grid">
<div class="field wide"><label for="it-title"><?= in_array($kind, ['faq'], true) ? 'Question' : ($kind === 'testimonial' ? 'Client name' : 'Title') ?></label><input id="it-title" name="title" maxlength="190" required value="<?= esc($pv($ei, 'title')) ?>"></div>
<div class="field wide"><label for="it-sub"><?= ['testimonial' => 'Company / role', 'project' => 'Sector label (shown as a tag)', 'stat' => 'Big number (optional, e.g. 500+)'][$kind] ?? 'Subtitle (optional)' ?></label><input id="it-sub" name="subtitle" maxlength="255" value="<?= esc($pv($ei, 'subtitle')) ?>"></div>
<div class="field wide"><label for="it-body"><?= ['faq' => 'Answer', 'testimonial' => 'Quote', 'project' => 'Story (challenge, solution, result)'][$kind] ?? 'Description' ?></label><textarea id="it-body" name="body" rows="5"><?= esc($pv($ei, 'body')) ?></textarea></div>
<?php if (in_array($kind, ['service', 'solution', 'package'], true)) { ?><div class="field wide"><label for="it-bul">Bullet points (one per line)</label><textarea id="it-bul" name="bullets" rows="5"><?= esc($pv($ei, 'bullets')) ?></textarea></div><?php } ?>
<div class="field"><label for="it-icon">Icon</label><select id="it-icon" name="icon"><option value="">Default</option><?php foreach (array_keys(Site::ICONS) as $ic) { ?><option value="<?= $ic ?>" <?= $pv($ei, 'icon') === $ic ? 'selected' : '' ?>><?= esc(ucfirst($ic)) ?></option><?php } ?></select><span class="icon-preview" data-icon-preview></span></div>
<div class="field"><label for="it-order">Order</label><input id="it-order" type="number" min="0" max="9999" name="sort_order" value="<?= esc($pv($ei, 'sort_order', (string) ((count($items) + 1) * 10))) ?>"></div>
<?php if (in_array($kind, ['product', 'package'], true)) { ?><div class="field"><label for="it-price">Price label</label><input id="it-price" name="price" maxlength="60" value="<?= esc($pv($ei, 'price')) ?>" placeholder="KES 45,000 / Request price"></div><?php } ?>
<?php if ($kind === 'project') { ?><div class="field"><label for="it-tags">Filter tags (comma separated)</label><input id="it-tags" name="tags" maxlength="255" value="<?= esc($pv($ei, 'tags')) ?>" placeholder="Education, Networking"></div><?php } ?>
</div>
<label for="it-img">Image (optional; a branded illustration is used if empty)</label><input id="it-img" type="file" name="image" accept="image/png,image/jpeg,image/gif,image/webp">
<?php if (!empty($ei['image_path'])) { ?><p><img src="<?= esc(base_url($ei['image_path'])) ?>" alt="" style="max-height:70px;border-radius:8px"> <label style="display:inline-flex;gap:6px;align-items:center"><input type="checkbox" name="remove_image" value="1" style="width:auto"> Remove image</label></p><?php } ?>
<label class="check-line"><input type="checkbox" name="active" value="1" <?= ($ei['active'] ?? 1) ? 'checked' : '' ?>> Live on the website</label>
<label class="check-line"><input type="checkbox" name="featured" value="1" <?= ($ei['featured'] ?? 1) ? 'checked' : '' ?>> Show on the home page</label>
<div class="form-actions"><button class="btn">Save</button><?php if ($ei) { ?><a class="button secondary" href="<?= base_url('its-site?tab=content&kind=' . $kind) ?>">Cancel</a><?php } ?></div></form>
<?php if ($ei) { ?><form method="post" action="<?= base_url('its-site-item-delete/' . $ei['id']) ?>" onsubmit="return confirm('Delete this item?')" style="margin-top:10px"><?= csrf_field() ?><button class="btn danger">Delete</button></form><?php } ?>
</div></div>

<?php } elseif ($tab === 'settings') { ?>
<div class="card"><h3>Website settings &amp; SEO</h3><p class="muted">Logo and company name come from Admin → Branding when left empty here.</p>
<form method="post" action="<?= base_url('its-site-settings') ?>"><?= csrf_field() ?><div class="form-grid">
<?php foreach (Site::SETTINGS as $k => [$l, $type]) { $v = (string) (old($k) ?? \App\Models\SettingModel::get($k, '')); $wide = in_array($type, ['textarea'], true) || in_array($k, ['site_meta_title', 'site_announcement', 'site_map_url'], true); ?>
<div class="field<?= $wide ? ' wide' : '' ?>"><?php if ($type === 'bool') { ?><label class="check-line" style="margin-top:22px"><input type="checkbox" name="<?= $k ?>" value="1" <?= $v !== '0' ? 'checked' : '' ?>> <?= esc($l) ?></label>
<?php } else { ?><label for="s-<?= $k ?>"><?= esc($l) ?></label><?php if ($type === 'textarea') { ?><textarea id="s-<?= $k ?>" name="<?= $k ?>" rows="3"><?= esc($v) ?></textarea><?php } else { ?><input id="s-<?= $k ?>" name="<?= $k ?>" type="<?= $type === 'color' ? 'color' : ($type === 'email' ? 'email' : ($type === 'url' ? 'url' : 'text')) ?>" value="<?= esc($v !== '' ? $v : ($type === 'color' ? ($k === 'site_accent_color' ? '#f97316' : '#0b4ea2') : '')) ?>" maxlength="300"><?php } ?><?php } ?></div>
<?php } ?></div>
<div class="form-actions"><button class="btn">Save settings</button><a class="button secondary" href="<?= base_url('sitemap.xml') ?>" target="_blank" rel="noopener">Sitemap ↗</a></div></form>
<p class="muted" style="margin-top:12px">After going live: add the site to Google Search Console and submit <code><?= esc(base_url('sitemap.xml')) ?></code>; create a Google Business Profile with the same name, address and phone as here.</p></div>

<?php } elseif ($tab === 'leads') { ?>
<div class="card tbl"><table><tr><th>Received</th><th>From</th><th>Service</th><th>Message</th><th>Status</th><th></th></tr>
<?php foreach ($leads as $l) { ?><tr><td><?= esc(substr((string) $l['created_at'], 0, 16)) ?></td><td><strong><?= esc($l['name']) ?></strong><br><?php if ($l['email']) { ?><a href="mailto:<?= esc($l['email']) ?>"><?= esc($l['email']) ?></a><br><?php } ?><?= esc($l['phone']) ?></td><td><?= esc($l['service']) ?></td><td style="max-width:420px"><?= nl2br(esc($l['message'])) ?></td>
<td><span class="badge <?= ['new' => 'orange', 'contacted' => 'blue', 'closed' => 'green'][$l['status']] ?? 'gray' ?>"><?= esc($l['status']) ?></span><?= $l['client_id'] ? '<br><a href="' . base_url('its-clients-view/' . $l['client_id']) . '">client</a>' : '' ?></td>
<td><form method="post" action="<?= base_url('its-site-lead/' . $l['id']) ?>" style="display:flex;gap:4px;flex-wrap:wrap;margin:0"><?= csrf_field() ?><?php foreach (['contacted' => 'Contacted', 'closed' => 'Close'] as $ak => $al) { if ($l['status'] === $ak) continue; ?><button class="btn secondary" name="action" value="<?= $ak ?>"><?= $al ?></button><?php } ?><?php if (!$l['client_id']) { ?><button class="btn" name="action" value="client">Make client</button><?php } ?></form></td></tr><?php } ?>
<?php if (!$leads) { ?><tr><td colspan="6" class="muted">No enquiries yet. Messages from the website contact form appear here and are emailed to <?= esc($site['email'] ?: 'the website email') ?>.</td></tr><?php } ?></table></div>
<?php } ?>
<style>
.view-tabs{display:flex;flex-wrap:wrap;gap:6px;margin:0 0 12px}.view-tabs a{padding:7px 14px;border:1px solid var(--line);border-radius:999px;text-decoration:none;color:var(--ink);background:var(--paper);font-size:13px;font-weight:600}.view-tabs a[aria-current=page]{background:var(--brand);border-color:var(--brand);color:#fff}.view-tabs a[aria-current=page] .muted{color:#e2e8f0}
.chk-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:4px 12px}.chk-grid label{display:flex;gap:8px;align-items:center;margin:0;font-size:13px}.chk-grid input{width:auto;margin:0}
.check-line{display:flex;align-items:center;gap:8px;margin:5px 0 10px}.check-line input{width:18px;margin:0}
.cf-set{border:1px dashed var(--line);border-radius:12px;padding:10px 12px;margin:6px 0 14px}.cf-set legend{font-size:12px;font-weight:700;color:var(--muted);padding:0 6px}
.serp{display:grid;gap:2px;padding:12px 14px;border:1px solid var(--line);border-radius:12px;background:#fff;font-family:Arial,sans-serif;margin-bottom:6px}.serp-url{color:#202124;font-size:12px}.serp-title{color:#1a0dab;font-size:18px;line-height:1.3}.serp-desc{color:#4d5156;font-size:13px}
.ic-sm{width:26px;height:26px}.icon-preview svg{width:28px;height:28px;color:var(--brand);margin-top:-6px}
/* page builder */
.bb-top{display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:10px;position:sticky;top:64px;z-index:20}.bb-name{margin-inline-start:8px}
.bb{display:grid;grid-template-columns:minmax(330px,440px) minmax(0,1fr);gap:16px;align-items:start}@media(max-width:1100px){.bb{grid-template-columns:1fr}}
.bb-side>.card{margin-bottom:14px}.bb-settings>summary{cursor:pointer}.bb-settings[open]>summary{margin-bottom:10px}
.bb-list{list-style:none;margin:0;padding:0;display:grid;gap:8px}
.bb-item{border:1px solid var(--line);border-radius:12px;background:var(--paper);overflow:hidden}.bb-item.dragging{opacity:.45;border-style:dashed;border-color:var(--brand)}
.bb-head{display:flex;align-items:center;gap:8px;padding:9px 10px;cursor:pointer;background:color-mix(in srgb,var(--brand) 5%,var(--paper))}.bb-handle{cursor:grab;color:var(--muted);letter-spacing:-2px;font-weight:700;user-select:none;padding:2px 4px}.bb-handle:active{cursor:grabbing}
.bb-label{flex:1;min-width:0;display:flex;flex-direction:column}.bb-title{font-size:13.5px}.bb-sum{font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.bb-tools{display:flex;gap:3px}.bb-tools button{min-height:28px;min-width:28px;padding:0 6px;border:1px solid var(--line);border-radius:7px;background:var(--paper);color:var(--ink);cursor:pointer;font-size:13px}.bb-tools button:disabled{opacity:.35}
.bb-body{display:none;padding:10px 12px 2px;border-top:1px solid var(--line)}.bb-item.open .bb-body{display:grid}.bb-body label{font-size:12px;color:var(--muted);font-weight:700}.bb-body .field>input,.bb-body .field>select,.bb-body .field>textarea{padding:8px;font-size:13px;margin-bottom:10px}
.bb-img{display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin-bottom:10px}.bb-thumb{width:84px;height:56px;object-fit:cover;border-radius:8px;background:var(--canvas,#f1f5f9);border:1px solid var(--line)}.bb-thumb:not([src]){display:none}.bb-img input[type=file]{flex:1;min-width:0}
.bb-add{margin-top:10px}.bb-add>summary{list-style:none;display:inline-flex}.bb-add>summary::-webkit-details-marker{display:none}.bb-palette{display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-top:8px}.bb-pal{border:1px dashed var(--line);background:var(--paper);color:var(--ink);border-radius:10px;padding:9px;text-align:start;cursor:pointer;font-size:13px}.bb-pal:hover{border-color:var(--brand);color:var(--brand)}
.bb-preview{position:sticky;top:130px;padding:10px}.bb-devices{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:8px}.bb-devices button{border:1px solid var(--line);background:var(--paper);color:var(--ink);border-radius:999px;padding:5px 12px;font-size:12px;cursor:pointer;min-height:0}.bb-devices button[aria-pressed=true]{background:var(--brand);border-color:var(--brand);color:#fff}
.bb-frame-wrap{background:#e2e8f0;border-radius:12px;padding:8px;display:flex;justify-content:center;overflow:auto}#bb-frame{width:100%;height:calc(100vh - 230px);min-height:520px;border:0;border-radius:8px;background:#fff;box-shadow:0 10px 30px rgba(15,23,42,.12);transition:width .2s}
</style>
<script>
(()=>{const g=id=>document.getElementById(id);
const icons=<?= json_encode(array_map(static fn($k) => Site::icon($k), array_combine(array_keys(Site::ICONS), array_keys(Site::ICONS))), JSON_HEX_TAG | JSON_HEX_AMP) ?>,sel=g('it-icon'),pv=document.querySelector('[data-icon-preview]');
const sh=()=>{if(sel&&pv)pv.innerHTML=icons[sel.value]||'';};sel&&sel.addEventListener('change',sh);sh();})();
</script>
<?= view('itsupport/_footer') ?>
