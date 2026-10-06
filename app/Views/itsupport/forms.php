<?= view('itsupport/_header') ?>
<section class="forms-intro">
    <p class="eyebrow">TEAM WORKFLOWS</p>
    <h2>Forms</h2>
    <p class="muted">Create simple structured forms and collect responses from your signed-in team.</p>
</section>
<?php if ($isAdmin) { ?>
<section class="grid grid-2">
    <div class="card"><p class="eyebrow">FORM BUILDER</p><h3><?= $editForm ? 'Edit form' : 'Create a form' ?></h3>
        <p class="muted">Add up to 10 fields. Use a short unique key; select and radio options are comma-separated.</p>
        <form method="post" action="<?= base_url('its-forms') ?>"><?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= esc($editForm['id'] ?? '') ?>">
            <label>Form title</label><input name="title" maxlength="190" value="<?= esc($editForm['title'] ?? '') ?>" required>
            <label>Description</label><textarea name="description" maxlength="4000"><?= esc($editForm['description'] ?? '') ?></textarea>
            <label class="check-line"><input type="checkbox" name="active" value="1" <?= !$editForm || (int) $editForm['active'] ? 'checked' : '' ?>> Available to staff</label>
            <div class="tbl form-fields"><table><thead><tr><th>Key</th><th>Label</th><th>Type</th><th>Select options</th><th>Required</th></tr></thead><tbody>
                <?php for ($i = 0; $i < 10; $i++) { $field = $formFields[$i] ?? []; ?>
                <tr><td><input aria-label="Field <?= $i + 1 ?> key" name="fields[<?= $i ?>][key]" maxlength="40" pattern="[a-zA-Z][a-zA-Z0-9_]*" value="<?= esc($field['key'] ?? '') ?>"></td>
                    <td><input aria-label="Field <?= $i + 1 ?> label" name="fields[<?= $i ?>][label]" maxlength="120" value="<?= esc($field['label'] ?? '') ?>"></td>
                    <td><select aria-label="Field <?= $i + 1 ?> type" name="fields[<?= $i ?>][type]"><?php foreach (['text'=>'Text','textarea'=>'Long text','number'=>'Number','email'=>'Email','phone'=>'Phone','date'=>'Date','url'=>'URL','select'=>'Select','radio'=>'Radio','checkbox'=>'Checkbox','file'=>'File upload'] as $value => $label) { ?><option value="<?= $value ?>" <?= ($field['type'] ?? 'text') === $value ? 'selected' : '' ?>><?= $label ?></option><?php } ?></select></td>
                    <td><input aria-label="Field <?= $i + 1 ?> options" name="fields[<?= $i ?>][options]" maxlength="1000" value="<?= esc(implode(', ', $field['options'] ?? [])) ?>" placeholder="For Select or Radio"></td>
                    <td><input aria-label="Field <?= $i + 1 ?> required" type="checkbox" name="fields[<?= $i ?>][required]" value="1" <?= !empty($field['required']) ? 'checked' : '' ?>></td>
                </tr>
                <?php } ?>
            </tbody></table></div>
            <div class="quick-actions"><button class="btn"><?= $editForm ? 'Save form' : 'Create form' ?></button><?php if ($editForm) { ?><a class="button secondary" href="<?= base_url('its-forms') ?>">Cancel</a><?php } ?></div>
        </form>
    </div>
    <div class="card">
        <div class="section-heading"><div><p class="eyebrow">FORM LIBRARY</p><h3>Saved forms</h3><p class="muted"><?= count($forms) ?> forms · <?= number_format($submissionCount) ?> submissions</p></div><div class="quick-actions"><a class="button secondary" href="<?= base_url('its-form-reports') ?>">Form responses</a><a class="button secondary" href="<?= base_url('its-report-builder') ?>">Report builder</a></div></div>
        <?php if ($forms) { ?><div class="form-list"><?php foreach ($forms as $form) { ?><article class="form-item"><div><span class="badge <?= (int) $form['active'] ? 'green' : 'gray' ?>"><?= (int) $form['active'] ? 'Available' : 'Archived' ?></span><strong><?= esc($form['title']) ?></strong><p class="muted"><?= esc($form['description'] ?: (($fieldCounts[$form['id']] ?? 0) . ' fields')) ?></p></div><div class="quick-actions"><?php if ((int) $form['active']) { ?><a class="button secondary" href="<?= base_url('its-form/' . $form['id']) ?>">Open</a><?php } ?><a class="button secondary" href="<?= base_url('its-forms?edit=' . $form['id']) ?>">Edit</a><?php if ((int) $form['active']) { ?><form method="post" action="<?= base_url('its-forms-archive/' . $form['id']) ?>" onsubmit="return confirm('Archive this form? Existing responses will be kept.')"><?= csrf_field() ?><button class="btn secondary">Archive</button></form><?php } ?></div></article><?php } ?></div><?php } else { ?><p class="muted">No forms yet. Create a form to begin collecting responses.</p><?php } ?>
    </div>
</section>
<?php } else { ?>
<section class="card"><h3>Available forms</h3>
    <?php if ($forms) { ?><div class="form-list"><?php foreach ($forms as $form) { ?><article class="form-item"><div><strong><?= esc($form['title']) ?></strong><p class="muted"><?= esc($form['description'] ?: 'Open to view and complete this form.') ?></p></div><a class="button" href="<?= base_url('its-form/' . $form['id']) ?>">Open form</a></article><?php } ?></div><?php } else { ?><p class="muted">There are no active forms right now.</p><?php } ?>
</section>
<?php } ?>
<style>
.forms-intro{padding:7px 2px 14px}.forms-intro h2{margin:4px 0;font-size:28px}.forms-intro p{margin:0}.section-heading{display:flex;align-items:center;justify-content:space-between;gap:12px}.section-heading h3{margin:3px 0}.form-fields{max-height:440px;margin:8px 0 16px}.form-fields input,.form-fields select{min-width:100px;margin:2px 0;padding:8px;font-size:12px}.form-fields td:last-child input{min-width:0;width:18px}.check-line{display:flex;align-items:center;gap:8px;margin:5px 0 12px}.check-line input{width:18px;margin:0}.form-list{display:grid;gap:10px;margin-top:14px}.form-item{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px;border:1px solid var(--line);border-radius:12px}.form-item strong{display:block;margin:7px 0 2px}.form-item p{margin:3px 0 0}.form-item form{margin:0}.quick-actions{display:flex;align-items:center;gap:7px;flex-wrap:wrap}.quick-actions .button,.quick-actions .btn{padding:8px 10px;font-size:12px}@media(max-width:700px){.form-item{align-items:flex-start;flex-direction:column}.form-fields table{min-width:660px}}
</style>
<?= view('itsupport/_footer') ?>
