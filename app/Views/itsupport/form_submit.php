<?= view('itsupport/_header') ?>
<section class="form-submit">
    <a class="button secondary" href="<?= base_url('its-forms') ?>">Back to forms</a>
    <div class="card"><p class="eyebrow">TEAM FORM</p><h2><?= esc($form['title']) ?></h2>
        <?php if ($form['description']) { ?><p class="muted"><?= nl2br(esc($form['description'])) ?></p><?php } ?>
        <?php if (isset($errors['_form'])) { ?><div class="flash flash-err" role="alert"><?= esc($errors['_form']) ?></div><?php } ?>
        <form method="post" action="<?= base_url('its-form/' . $form['id']) ?>" enctype="multipart/form-data"><?= csrf_field() ?>
            <?php foreach ($fields as $field) { $key = $field['key']; $value = $submitted[$key] ?? ''; ?>
            <div class="dynamic-field">
                <?php if ($field['type'] === 'checkbox') { ?>
                    <label class="checkbox-label"><input type="checkbox" name="answer[<?= esc($key) ?>]" value="1" <?= $value === 'Yes' ? 'checked' : '' ?> <?= $field['required'] ? 'required' : '' ?>> <?= esc($field['label']) ?><?= $field['required'] ? ' *' : '' ?></label>
                <?php } elseif ($field['type'] === 'radio') { ?>
                    <fieldset><legend><?= esc($field['label']) ?><?= $field['required'] ? ' *' : '' ?></legend><?php foreach ($field['options'] as $option) { ?><label class="radio-label"><input type="radio" name="answer[<?= esc($key) ?>]" value="<?= esc($option) ?>" <?= $value === $option ? 'checked' : '' ?> <?= $field['required'] ? 'required' : '' ?>> <?= esc($option) ?></label><?php } ?></fieldset>
                <?php } elseif ($field['type'] === 'file') { ?>
                    <label for="answer-<?= esc($key) ?>"><?= esc($field['label']) ?><?= $field['required'] ? ' *' : '' ?></label>
                    <input id="answer-<?= esc($key) ?>" type="file" name="answer[<?= esc($key) ?>]" accept=".pdf,.jpg,.jpeg,.png,.webp,.txt,.csv" <?= $field['required'] ? 'required' : '' ?>>
                    <p class="muted">PDF, JPG, PNG, WebP, TXT, or CSV; maximum 5 MB.</p>
                <?php } else { ?>
                    <label for="answer-<?= esc($key) ?>"><?= esc($field['label']) ?><?= $field['required'] ? ' *' : '' ?></label>
                    <?php if ($field['type'] === 'textarea') { ?><textarea id="answer-<?= esc($key) ?>" name="answer[<?= esc($key) ?>]" maxlength="5000" <?= $field['required'] ? 'required' : '' ?>><?= esc($value) ?></textarea>
                    <?php } elseif ($field['type'] === 'select') { ?><select id="answer-<?= esc($key) ?>" name="answer[<?= esc($key) ?>]" <?= $field['required'] ? 'required' : '' ?>><option value="">Choose...</option><?php foreach ($field['options'] as $option) { ?><option value="<?= esc($option) ?>" <?= $value === $option ? 'selected' : '' ?>><?= esc($option) ?></option><?php } ?></select>
                    <?php } else { ?><input id="answer-<?= esc($key) ?>" type="<?= esc($field['type'] === 'phone' ? 'tel' : $field['type']) ?>" name="answer[<?= esc($key) ?>]" value="<?= esc($value) ?>" <?= in_array($field['type'], ['text','email','number','date','phone','url'], true) ? 'maxlength="5000"' : '' ?> <?= $field['required'] ? 'required' : '' ?>><?php } ?>
                <?php } ?>
                <?php if (isset($errors[$key])) { ?><p class="field-error" role="alert"><?= esc($errors[$key]) ?></p><?php } ?>
            </div>
            <?php } ?>
            <button class="btn">Submit response</button>
        </form>
    </div>
</section>
<style>
.form-submit{max-width:760px;margin:0 auto}.form-submit>.button{margin-bottom:14px}.form-submit h2{margin:4px 0 12px}.dynamic-field{margin:15px 0}.dynamic-field>label{display:block;margin-bottom:4px}.checkbox-label,.radio-label{display:flex!important;align-items:center;gap:8px;margin:8px 0}.checkbox-label input,.radio-label input{width:18px;margin:0}fieldset{border:1px solid var(--line);border-radius:12px;padding:12px}legend{font-weight:600}.field-error{margin:-8px 0 10px;color:#b42332;font-size:13px;font-weight:600}
</style>
<?= view('itsupport/_footer') ?>
