<?= view('itsupport/_header') ?>
<section class="form-submit">
    <a class="button secondary" href="<?= base_url($home ?? 'its-forms') ?>">Back</a>
    <div class="card"><p class="eyebrow">FORM</p><h2><?= esc($form['title']) ?></h2>
        <?php if ($form['description']) { ?><p class="muted"><?= nl2br(esc($form['description'])) ?></p><?php } ?>
        <?php if (isset($errors['_form'])) { ?><div class="flash flash-err" role="alert"><?= esc($errors['_form']) ?></div><?php } ?>
        <?php if (array_diff_key($errors, ['_form' => 1])) { ?><div class="flash flash-err" role="alert">Please check the highlighted answers.</div><?php } ?>
        <form method="post" action="<?= base_url('its-form/' . $form['id']) ?>" enctype="multipart/form-data" class="fs-grid"><?= csrf_field() ?>
            <?php foreach ($fields as $field) { $key = $field['key']; $value = $submitted[$key] ?? ''; $rule = $field['show_if'] ?? null; $cond = is_array($rule) && !empty($rule['field']) ? ' data-show-if="' . esc($rule['field']) . '" data-show-val="' . esc($rule['value'] ?? '') . '"' : ''; ?>
            <?php if ($field['type'] === 'section') { ?>
            <div class="fs-section"<?= $cond ?>><h3><?= esc($field['label']) ?></h3><?php if ($field['help'] !== '') { ?><p class="muted"><?= esc($field['help']) ?></p><?php } ?></div>
            <?php continue; } ?>
            <div class="dynamic-field<?= ($field['width'] ?? 'full') === 'half' ? ' half' : '' ?>" data-key="<?= esc($key) ?>"<?= $cond ?>>
                <?php $req = $field['required'] ? 'required data-req="1"' : ''; $ph = $field['placeholder'] !== '' ? ' placeholder="' . esc($field['placeholder']) . '"' : ''; ?>
                <?php if ($field['type'] === 'checkbox') { ?>
                    <label class="checkbox-label"><input type="checkbox" name="answer[<?= esc($key) ?>]" value="1" <?= $value === 'Yes' ? 'checked' : '' ?> <?= $req ?>> <?= esc($field['label']) ?><?= $field['required'] ? ' *' : '' ?></label>
                <?php } elseif ($field['type'] === 'radio') { ?>
                    <fieldset><legend><?= esc($field['label']) ?><?= $field['required'] ? ' *' : '' ?></legend><?php foreach ($field['options'] as $option) { ?><label class="radio-label"><input type="radio" name="answer[<?= esc($key) ?>]" value="<?= esc($option) ?>" <?= $value === $option ? 'checked' : '' ?> <?= $req ?>> <?= esc($option) ?></label><?php } ?></fieldset>
                <?php } elseif ($field['type'] === 'file') { ?>
                    <label for="answer-<?= esc($key) ?>"><?= esc($field['label']) ?><?= $field['required'] ? ' *' : '' ?></label>
                    <input id="answer-<?= esc($key) ?>" type="file" name="answer[<?= esc($key) ?>]" accept=".pdf,.jpg,.jpeg,.png,.webp,.txt,.csv" <?= $req ?>>
                    <p class="muted">PDF, JPG, PNG, WebP, TXT, or CSV; maximum 5 MB.</p>
                <?php } else { ?>
                    <label for="answer-<?= esc($key) ?>"><?= esc($field['label']) ?><?= $field['required'] ? ' *' : '' ?></label>
                    <?php if ($field['type'] === 'textarea') { ?><textarea id="answer-<?= esc($key) ?>" name="answer[<?= esc($key) ?>]" maxlength="5000" <?= $req ?><?= $ph ?>><?= esc($value) ?></textarea>
                    <?php } elseif ($field['type'] === 'select') { ?><select id="answer-<?= esc($key) ?>" name="answer[<?= esc($key) ?>]" <?= $req ?>><option value="">Choose...</option><?php foreach ($field['options'] as $option) { ?><option value="<?= esc($option) ?>" <?= $value === $option ? 'selected' : '' ?>><?= esc($option) ?></option><?php } ?></select>
                    <?php } else { ?><input id="answer-<?= esc($key) ?>" type="<?= esc($field['type'] === 'phone' ? 'tel' : $field['type']) ?>" <?= $field['type'] === 'number' ? 'step="any"' : '' ?> name="answer[<?= esc($key) ?>]" value="<?= esc($value) ?>" maxlength="5000" <?= $req ?><?= $ph ?>><?php } ?>
                <?php } ?>
                <?php if ($field['help'] !== '') { ?><p class="muted fs-help"><?= esc($field['help']) ?></p><?php } ?>
                <?php if (isset($errors[$key])) { ?><p class="field-error" role="alert"><?= esc($errors[$key]) ?></p><?php } ?>
            </div>
            <?php } ?>
            <div class="form-actions fs-actions"><button class="btn">Submit response</button></div>
        </form>
    </div>
</section>
<style>
.form-submit{max-width:820px;margin:0 auto}.form-submit>.button{margin-bottom:14px}.form-submit h2{margin:4px 0 12px}
.fs-grid{display:grid;grid-template-columns:1fr 1fr;gap:0 18px}.fs-grid>*{grid-column:1/-1}.fs-grid>.dynamic-field.half{grid-column:auto}@media(max-width:640px){.fs-grid>.dynamic-field.half{grid-column:1/-1}}
.fs-section{margin:18px 0 4px;padding-top:12px;border-top:1px solid var(--line)}.fs-section h3{margin:0 0 4px}.fs-section p{margin:0}
.dynamic-field{margin:10px 0}.dynamic-field>label{display:block;margin-bottom:4px}.fs-help{margin:-8px 0 8px;font-size:12px}.checkbox-label,.radio-label{display:flex!important;align-items:center;gap:8px;margin:8px 0}.checkbox-label input,.radio-label input{width:18px;margin:0}fieldset{border:1px solid var(--line);border-radius:12px;padding:12px}legend{font-weight:600}.field-error{margin:-8px 0 10px;color:#b42332;font-size:13px;font-weight:600}
</style>
<script>
// Conditional fields: show a field only when the field it depends on has the chosen answer.
(() => {
  const form = document.querySelector('.fs-grid'); if (!form) return;
  const answerOf = key => { const holder = form.querySelector(`[data-key="${key}"]`); if (holder && holder.hidden) return ''; const els = form.querySelectorAll(`[name="answer[${key}]"]`); for (const el of els) { if (el.type === 'checkbox') return el.checked ? 'Yes' : 'No'; if (el.type === 'radio') { if (el.checked) return el.value; continue; } return el.value; } return ''; };
  const deps = [...form.querySelectorAll('[data-show-if]')];
  const apply = () => deps.forEach(box => {
    const want = box.dataset.showVal, got = answerOf(box.dataset.showIf);
    const show = want === '' ? (got !== '' && got !== 'No') : got === want;
    box.hidden = !show;
    box.querySelectorAll('input,select,textarea').forEach(el => { if (el.dataset.req) el.required = show; el.disabled = !show; });
  });
  form.addEventListener('input', apply); form.addEventListener('change', apply); apply();
})();
</script>
<?= view('itsupport/_footer') ?>
