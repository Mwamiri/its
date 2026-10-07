<?= view('itsupport/_header') ?>
<?php
$typeLabels = ['text' => 'Short text', 'textarea' => 'Long text', 'number' => 'Number', 'email' => 'Email', 'phone' => 'Phone', 'date' => 'Date', 'url' => 'Link', 'select' => 'Dropdown', 'radio' => 'Choice buttons', 'checkbox' => 'Checkbox', 'file' => 'File upload', 'section' => 'Section heading'];
$audLabels = ['staff' => 'Staff', 'clients' => 'Clients (portal)', 'both' => 'Staff and clients'];
?>
<section class="forms-intro">
    <p class="eyebrow">TEAM &amp; CLIENT WORKFLOWS</p>
    <h2>Forms</h2>
    <p class="muted">Build checklists, site surveys, onboarding and feedback forms. Publish them to staff, to clients in the portal, or both.</p>
</section>
<?php if ($isAdmin) { ?>
<section class="forms-layout">
    <div class="card"><p class="eyebrow">FORM BUILDER</p><h3><?= $editForm ? 'Edit form' : 'Create a form' ?></h3>
        <form method="post" action="<?= base_url('its-forms') ?>" id="fb-form"><?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= esc($editForm['id'] ?? '') ?>">
            <div class="form-grid">
            <div class="field wide"><label for="fb-title">Form title</label><input id="fb-title" name="title" maxlength="190" value="<?= esc($editForm['title'] ?? '') ?>" required placeholder="e.g. New site survey"></div>
            <div class="field wide"><label for="fb-desc">Description / instructions</label><textarea id="fb-desc" name="description" maxlength="4000" rows="2"><?= esc($editForm['description'] ?? '') ?></textarea></div>
            <?php if ($hasAudience) { ?>
            <div class="field"><label for="fb-aud">Who fills this form</label><select id="fb-aud" name="audience"><?php foreach ($audLabels as $k => $l) { ?><option value="<?= $k ?>" <?= $formSettings['audience'] === $k ? 'selected' : '' ?>><?= esc($l) ?></option><?php } ?></select></div>
            <div class="field"><label for="fb-notify">Email each response to</label><input id="fb-notify" name="notify" maxlength="500" value="<?= esc($formSettings['notify']) ?>" placeholder="ops@example.com, manager@example.com"></div>
            <div class="field wide"><label for="fb-conf">Confirmation message</label><input id="fb-conf" name="confirmation" maxlength="500" value="<?= esc($formSettings['confirmation']) ?>" placeholder="Thanks! We have received your response."></div>
            <?php } ?>
            </div>
            <label class="check-line"><input type="checkbox" name="active" value="1" <?= !$editForm || (int) $editForm['active'] ? 'checked' : '' ?>> Published (available to fill)</label>
            <div class="section-heading"><h3 style="margin:0">Fields</h3><span class="muted" data-fb-count></span></div>
            <ol class="fb-list" id="fb-list"></ol>
            <noscript><p class="flash flash-err">The form builder needs JavaScript enabled.</p></noscript>
            <div class="quick-actions fb-add"><button type="button" class="btn secondary" data-fb-add="text">+ Add field</button><button type="button" class="btn secondary" data-fb-add="section">+ Add section heading</button></div>
            <div class="form-actions"><button class="btn"><?= $editForm ? 'Save form' : 'Create form' ?></button><?php if ($editForm) { ?><a class="button secondary" href="<?= base_url('its-forms') ?>">Cancel</a><a class="button secondary" href="<?= base_url('its-form/' . $editForm['id']) ?>" target="_blank" rel="noopener">Preview</a><?php } ?></div>
        </form>
        <template id="fb-row"><li class="fb-item">
            <div class="fb-bar"><span class="fb-handle" aria-hidden="true">⋮⋮</span><strong class="fb-title">New field</strong><span class="badge gray fb-type-badge"></span>
              <span class="fb-tools"><button type="button" data-act="up" aria-label="Move up">↑</button><button type="button" data-act="down" aria-label="Move down">↓</button><button type="button" data-act="dup" aria-label="Duplicate">⧉</button><button type="button" data-act="del" aria-label="Remove" class="fb-del">✕</button></span></div>
            <div class="fb-body form-grid">
              <div class="field wide"><label>Label / question</label><input data-k="label" maxlength="160" required></div>
              <div class="field"><label>Type</label><select data-k="type"><?php foreach ($fieldTypes as $t) { ?><option value="<?= $t ?>"><?= esc($typeLabels[$t] ?? $t) ?></option><?php } ?></select></div>
              <div class="field fb-input-only"><label>Width</label><select data-k="width"><option value="full">Full row</option><option value="half">Half row</option></select></div>
              <div class="field wide fb-opts"><label>Options (comma separated)</label><input data-k="options" maxlength="1500" placeholder="Yes, No, Not sure"></div>
              <div class="field fb-input-only"><label>Placeholder</label><input data-k="placeholder" maxlength="120"></div>
              <div class="field"><label>Help text</label><input data-k="help" maxlength="300"></div>
              <div class="field fb-input-only"><label>Show only if</label><select data-k="show_if_field"><option value="">Always show</option></select></div>
              <div class="field fb-input-only"><label>…equals (blank = has any answer)</label><input data-k="show_if_value" maxlength="100"></div>
              <div class="field fb-input-only"><label>Key (for reports, optional)</label><input data-k="key" maxlength="40" pattern="[a-z][a-z0-9_]*" placeholder="auto"></div>
              <div class="field fb-input-only"><label class="check-line" style="margin-top:26px"><input type="checkbox" data-k="required" value="1"> Required</label></div>
            </div></li></template>
    </div>
    <div class="card">
        <div class="section-heading"><div><p class="eyebrow">FORM LIBRARY</p><h3>Saved forms</h3><p class="muted"><?= count($forms) ?> forms · <?= number_format($submissionCount) ?> responses</p></div><div class="quick-actions"><a class="button secondary" href="<?= base_url('its-form-reports') ?>">Responses</a></div></div>
        <?php if ($forms) { ?><div class="form-list"><?php foreach ($forms as $form) { $aud = $form['audience'] ?? 'staff'; ?><article class="form-item"><div><span class="badge <?= (int) $form['active'] ? 'green' : 'gray' ?>"><?= (int) $form['active'] ? 'Published' : 'Archived' ?></span> <span class="badge blue"><?= esc($audLabels[$aud] ?? 'Staff') ?></span><strong><?= esc($form['title']) ?></strong><p class="muted"><?= ($fieldCounts[$form['id']] ?? 0) ?> fields<?= $form['description'] ? ' · ' . esc(mb_strimwidth((string) $form['description'], 0, 80, '…')) : '' ?></p></div><div class="quick-actions"><?php if ((int) $form['active']) { ?><a class="button secondary" href="<?= base_url('its-form/' . $form['id']) ?>">Open</a><?php } ?><a class="button secondary" href="<?= base_url('its-forms?edit=' . $form['id']) ?>">Edit</a><a class="button secondary" href="<?= base_url('its-form-reports?form_id=' . $form['id']) ?>">Responses</a><?php if ((int) $form['active']) { ?><form method="post" action="<?= base_url('its-forms-archive/' . $form['id']) ?>" onsubmit="return confirm('Archive this form? Existing responses will be kept.')"><?= csrf_field() ?><button class="btn secondary">Archive</button></form><?php } ?></div></article><?php } ?></div><?php } else { ?><p class="muted">No forms yet. Create a form to begin collecting responses.</p><?php } ?>
    </div>
</section>
<script id="fb-data" type="application/json"><?= json_encode(['fields' => $formFields, 'max' => $maxFields], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<script>
(() => {
  const data = JSON.parse(document.getElementById('fb-data').textContent);
  const list = document.getElementById('fb-list'), tpl = document.getElementById('fb-row'), form = document.getElementById('fb-form');
  const count = document.querySelector('[data-fb-count]');
  const typeLabels = <?= json_encode($typeLabels, JSON_HEX_TAG | JSON_HEX_AMP) ?>;
  const slug = s => { let k = s.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '').slice(0, 34); return /^[a-z]/.test(k) ? k : 'f_' + k; };
  const val = (li, k) => { const el = li.querySelector(`[data-k="${k}"]`); return el.type === 'checkbox' ? el.checked : el.value; };
  const keyOf = li => val(li, 'key').trim() || slug(val(li, 'label') || 'field');
  const refresh = () => {
    const items = [...list.children];
    items.forEach((li, i) => {
      const type = val(li, 'type'), isSection = type === 'section';
      li.classList.toggle('is-section', isSection);
      li.querySelector('.fb-title').textContent = val(li, 'label') || (isSection ? 'New section' : 'New field');
      li.querySelector('.fb-type-badge').textContent = typeLabels[type] || type;
      li.querySelector('.fb-opts').hidden = !['select', 'radio'].includes(type);
      li.querySelectorAll('.fb-input-only').forEach(el => el.hidden = isSection);
      const sel = li.querySelector('[data-k="show_if_field"]'), cur = sel.dataset.want ?? sel.value;
      sel.replaceChildren(new Option('Always show', ''));
      items.slice(0, i).filter(p => val(p, 'type') !== 'section').forEach(p => sel.add(new Option(val(p, 'label') || keyOf(p), keyOf(p))));
      sel.value = [...sel.options].some(o => o.value === cur) ? cur : '';
      delete sel.dataset.want;
      li.querySelector('[data-act="up"]').disabled = i === 0;
      li.querySelector('[data-act="down"]').disabled = i === items.length - 1;
    });
    count.textContent = `${items.length} / ${data.max}`;
    document.querySelectorAll('[data-fb-add]').forEach(b => b.disabled = items.length >= data.max);
  };
  const add = (f = {}, after = null) => {
    const li = tpl.content.firstElementChild.cloneNode(true);
    const set = (k, v) => { const el = li.querySelector(`[data-k="${k}"]`); if (el.type === 'checkbox') el.checked = !!v; else el.value = v ?? ''; };
    set('label', f.label); set('type', f.type || 'text'); set('width', f.width || 'full'); set('options', (f.options || []).join(', '));
    set('placeholder', f.placeholder); set('help', f.help); set('key', f.key); set('required', f.required); set('show_if_value', f.show_if?.value);
    li.querySelector('[data-k="show_if_field"]').dataset.want = f.show_if?.field || '';
    after ? after.after(li) : list.append(li);
    refresh(); return li;
  };
  list.addEventListener('input', refresh); list.addEventListener('change', refresh);
  list.addEventListener('click', e => {
    const b = e.target.closest('[data-act]'); if (!b) return; const li = b.closest('.fb-item');
    if (b.dataset.act === 'up' && li.previousElementSibling) li.previousElementSibling.before(li);
    if (b.dataset.act === 'down' && li.nextElementSibling) li.nextElementSibling.after(li);
    if (b.dataset.act === 'del') li.remove();
    if (b.dataset.act === 'dup' && list.children.length < data.max) { const f = {}; ['label','type','width','options','placeholder','help','required','show_if_value'].forEach(k => f[k] = val(li, k)); f.options = String(f.options).split(','); f.label += ' (copy)'; add(f, li); }
    refresh();
  });
  document.querySelectorAll('[data-fb-add]').forEach(b => b.addEventListener('click', () => add({type: b.dataset.fbAdd}).querySelector('[data-k="label"]').focus()));
  form.addEventListener('submit', e => {
    const used = new Set();
    [...list.children].forEach((li, i) => {
      if (!val(li, 'label').trim()) return;
      let k = keyOf(li), base = k, n = 2; while (used.has(k)) k = base + '_' + n++; used.add(k);
      li.querySelector('[data-k="key"]').value = k;
      li.querySelectorAll('[data-k]').forEach(el => { el.name = `fields[${i}][${el.dataset.k}]`; });
    });
    if (!used.size) { e.preventDefault(); alert('Add at least one field with a label.'); }
  });
  (data.fields.length ? data.fields : [{type: 'text'}]).forEach(f => add(f));
})();
</script>
<?php } else { ?>
<section class="card"><h3>Available forms</h3>
    <?php if ($forms) { ?><div class="form-list"><?php foreach ($forms as $form) { ?><article class="form-item"><div><strong><?= esc($form['title']) ?></strong><p class="muted"><?= esc($form['description'] ?: 'Open to view and complete this form.') ?></p></div><a class="button" href="<?= base_url('its-form/' . $form['id']) ?>">Open form</a></article><?php } ?></div><?php } else { ?><p class="muted">There are no active forms right now.</p><?php } ?>
</section>
<?php } ?>
<style>
.forms-intro{padding:7px 2px 14px}.forms-intro h2{margin:4px 0;font-size:28px}.forms-intro p{margin:0}
.forms-layout{display:grid;gap:16px;grid-template-columns:minmax(0,1.35fr) minmax(0,1fr);align-items:start}@media(max-width:1100px){.forms-layout{grid-template-columns:1fr}}
.check-line{display:flex;align-items:center;gap:8px;margin:5px 0 12px}.check-line input{width:18px;margin:0}
.fb-list{list-style:none;margin:8px 0 12px;padding:0;display:grid;gap:10px}
.fb-item{border:1px solid var(--line);border-radius:12px;background:var(--paper);overflow:hidden}.fb-item.is-section{border-color:var(--brand);box-shadow:inset 4px 0 var(--brand)}
.fb-bar{display:flex;align-items:center;gap:8px;padding:8px 10px;background:color-mix(in srgb,var(--brand) 6%,var(--paper));border-bottom:1px solid var(--line)}.fb-handle{color:var(--muted);letter-spacing:-2px}.fb-title{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.fb-tools{display:flex;gap:4px}.fb-tools button{min-height:30px;min-width:30px;padding:0 8px;border:1px solid var(--line);border-radius:8px;background:var(--paper);color:var(--ink);cursor:pointer}.fb-tools .fb-del:hover{background:#fee2e2;color:#991b1b}
.fb-body{padding:10px 12px 0}.fb-body .field>input,.fb-body .field>select{margin-bottom:10px;padding:8px;font-size:13px}.fb-body label{font-size:12px;color:var(--muted);font-weight:700}
.form-list{display:grid;gap:10px;margin-top:14px}.form-item{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px;border:1px solid var(--line);border-radius:12px}.form-item strong{display:block;margin:7px 0 2px}.form-item p{margin:3px 0 0}.form-item form{margin:0}.quick-actions{display:flex;align-items:center;gap:7px;flex-wrap:wrap}.quick-actions .button,.quick-actions .btn{padding:8px 10px;font-size:12px}@media(max-width:700px){.form-item{align-items:flex-start;flex-direction:column}}
</style>
<?= view('itsupport/_footer') ?>
