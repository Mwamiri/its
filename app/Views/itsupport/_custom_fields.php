<?php
/** Inputs for admin-defined fields. Expects $entity and optional $values (field_key => value). */
$cfDefs = \App\Libraries\CustomFields::defs($entity);
$cfValues = $values ?? [];
if ($cfDefs) { ?>
<fieldset class="cf-set"><legend>Additional details</legend>
<?php foreach ($cfDefs as $d) { $k = $d['field_key']; $id = 'cf-' . $entity . '-' . $k; $v = (string) ($cfValues[$k] ?? ''); $req = $d['required'] ? 'required' : ''; ?>
<?php if ($d['type'] === 'checkbox') { ?>
<label class="cf-check"><input type="hidden" name="cf[<?= esc($k) ?>]" value="0"><input type="checkbox" id="<?= esc($id) ?>" name="cf[<?= esc($k) ?>]" value="1" <?= $v === '1' ? 'checked' : '' ?> <?= $req ?>> <?= esc($d['label']) ?></label>
<?php } else { ?>
<label for="<?= esc($id) ?>"><?= esc($d['label']) ?></label>
<?php if ($d['type'] === 'textarea') { ?><textarea id="<?= esc($id) ?>" name="cf[<?= esc($k) ?>]" maxlength="5000" <?= $req ?>><?= esc($v) ?></textarea>
<?php } elseif ($d['type'] === 'select') { ?><select id="<?= esc($id) ?>" name="cf[<?= esc($k) ?>]" <?= $req ?>><option value="">Choose…</option><?php foreach ($d['options'] as $o) { ?><option <?= $v === $o ? 'selected' : '' ?>><?= esc($o) ?></option><?php } ?></select>
<?php } else { $t = ['number' => 'number', 'date' => 'date', 'email' => 'email', 'phone' => 'tel', 'url' => 'url'][$d['type']] ?? 'text'; ?><input id="<?= esc($id) ?>" type="<?= $t ?>" <?= $t === 'number' ? 'step="any"' : '' ?> name="cf[<?= esc($k) ?>]" maxlength="500" value="<?= esc($v) ?>" <?= $req ?>>
<?php } ?>
<?php } ?>
<?php if (!empty($d['help'])) { ?><p class="muted cf-help"><?= esc($d['help']) ?></p><?php } ?>
<?php } ?>
</fieldset>
<style>.cf-set{border:1px dashed var(--line);border-radius:12px;padding:10px 12px;margin:6px 0 14px}.cf-set legend{font-size:12px;font-weight:700;color:var(--muted);padding:0 6px}.cf-check{display:flex;align-items:center;gap:8px;margin:8px 0}.cf-help{margin:-8px 0 10px;font-size:12px}</style>
<?php } ?>
