<?= view('itsupport/_header') ?>
<?php
$adminTabs = ['users' => 'Users', 'permissions' => 'Permissions', 'integrations' => 'API & Webhooks', 'settings' => 'Branding & Settings', 'fields' => 'Custom fields', 'templates' => 'Templates', 'reports' => 'Reports', 'mail' => 'Mail', 'maillog' => 'Mail Log', 'maintenance' => 'Maintenance', 'audit' => 'Audit', 'changes' => 'Changes'];
?>
<div class="tw:mb-4 tw:flex tw:flex-wrap tw:items-center tw:gap-2 tw:rounded-2xl tw:border tw:border-line tw:bg-paper tw:p-2 tw:shadow-sm">
<div role="tablist" aria-label="Admin sections" class="tw:flex tw:flex-1 tw:gap-1 tw:overflow-x-auto">
<?php foreach ($adminTabs as $key => $label) { $on = $tab === $key; ?>
<a role="tab" aria-selected="<?= $on ? 'true' : 'false' ?>" href="<?= base_url('its-admin?tab=' . $key) ?>" class="tw:whitespace-nowrap tw:rounded-xl tw:px-3.5 tw:py-2 tw:text-sm tw:font-semibold tw:no-underline tw:transition <?= $on ? 'tw:bg-brand tw:text-white tw:shadow' : 'tw:text-muted tw:hover:bg-brand-soft tw:hover:text-ink' ?>"><?= esc($label) ?></a>
<?php } ?>
</div>
<div class="tw:flex tw:gap-1">
<a href="<?= base_url('its-updates') ?>" class="tw:rounded-xl tw:border tw:border-line tw:px-3 tw:py-2 tw:text-sm tw:font-semibold tw:text-ink tw:no-underline tw:hover:bg-brand-soft">Updates</a>
<a href="<?= base_url('its-security') ?>" class="tw:rounded-xl tw:border tw:border-line tw:px-3 tw:py-2 tw:text-sm tw:font-semibold tw:text-ink tw:no-underline tw:hover:bg-brand-soft">Security</a>
<a href="<?= base_url('its-admin-backup') ?>" class="tw:rounded-xl tw:border tw:border-line tw:px-3 tw:py-2 tw:text-sm tw:font-semibold tw:text-ink tw:no-underline tw:hover:bg-brand-soft">Backup</a>
</div>
</div><?php if ($tab === 'users') { ?>
<div class="card"><h3>Add User</h3><form method="post" action="<?= base_url('its-admin-users') ?>"><?= csrf_field() ?>
<label>Name</label><input name="name" required><label>Username</label><input name="username" required>
<label>Role</label><select name="role"><option>admin</option><option>manager</option><option>technician</option><option value="client">client (portal login)</option></select>
<label>Client (for portal logins)</label><select name="client_id"><option value="">None</option><?php foreach ($clients as $c) { ?><option value="<?= (int) $c['id'] ?>"><?= esc($c['name']) ?></option><?php } ?></select>
<label>Password (min 8 characters)</label><input type="password" name="password" minlength="8" required>
<button class="btn">Add</button></form>
<table><tr><th>Name</th><th>Username</th><th>Role</th><th>2FA</th><th>Status</th><th></th></tr><?php foreach ($users as $u) { $locked = !empty($u['locked_until']) && strtotime($u['locked_until']) > time(); ?><tr><td><?= esc($u['name']) ?></td><td><?= esc($u['username']) ?></td><td><?= esc($u['role']) ?></td><td><?= !empty($u['totp_enabled']) ? '<span class="badge green">On</span>' : '<span class="badge gray">Off</span>' ?></td><td><?= $locked ? '<span class="badge orange">Locked</span>' : 'OK' ?></td><td><?php if (!empty($u['totp_enabled']) || $locked) { ?><form method="post" action="<?= base_url('its-admin-reset2fa/' . (int) $u['id']) ?>" onsubmit="return confirm('Reset two-factor and clear any lockout for this user?')"><?= csrf_field() ?><button class="button secondary">Reset 2FA / unlock</button></form><?php } ?></td></tr><?php } ?></table></div>
<?php } elseif ($tab === 'permissions') { ?>
<div class="card"><h3>Role permissions</h3><p class="muted">Admins always have full access. Choose what each other role can do per area: None, View, or Edit. Clients only see the portal.</p>
<form method="post" action="<?= base_url('its-admin-permissions') ?>"><?= csrf_field() ?>
<div class="tbl"><table><tr><th>Area</th><?php foreach (\App\Libraries\Perm::ROLES as $role) { ?><th><?= esc(ucfirst($role)) ?></th><?php } ?></tr>
<?php foreach (\App\Libraries\Perm::MODULES as $mod => $label) { ?><tr><td><?= esc($label) ?></td><?php foreach (\App\Libraries\Perm::ROLES as $role) { ?><td><select name="perm[<?= esc($role) ?>][<?= esc($mod) ?>]" aria-label="<?= esc($role . ' ' . $label) ?>"><?php foreach ([0 => 'None', 1 => 'View', 2 => 'Edit'] as $lv => $ln) { ?><option value="<?= $lv ?>" <?= (int) $matrix[$role][$mod] === $lv ? 'selected' : '' ?>><?= $ln ?></option><?php } ?></select></td><?php } ?></tr><?php } ?></table></div>
<button class="btn">Save permissions</button></form></div>
<?php } elseif ($tab === 'integrations') { ?>
<?php if (session('newKey')) { ?><div class="card" role="alert"><h3>New API key</h3><p>Copy it now. It will not be shown again.</p><p><code style="word-break:break-all"><?= esc(session('newKey')) ?></code></p></div><?php } ?>
<div class="grid grid-2">
<div class="card"><h3>REST API keys</h3><p class="muted">Send the key as <code>Authorization: Bearer &lt;key&gt;</code>. Endpoints: <code>GET <?= esc(base_url('api/v1/tickets')) ?></code>, <code>/api/v1/tickets/{id}</code>, <code>/api/v1/clients</code>, <code>/api/v1/assets</code>, <code>POST /api/v1/tickets</code>.</p>
<form method="post" action="<?= base_url('its-api-key-add') ?>"><?= csrf_field() ?><label for="kn">Key name</label><input id="kn" name="name" maxlength="120" required><button class="btn">Create key</button></form>
<table><tr><th>Name</th><th>Prefix</th><th>Last used</th><th></th></tr><?php foreach ($apiKeys as $k) { ?><tr><td><?= esc($k['name']) ?></td><td><code><?= esc($k['key_prefix']) ?>…</code></td><td><?= esc($k['last_used'] ?? 'never') ?></td><td><form method="post" action="<?= base_url('its-api-key-revoke/' . (int) $k['id']) ?>" onsubmit="return confirm('Revoke this key?')"><?= csrf_field() ?><button class="button secondary">Revoke</button></form></td></tr><?php } ?></table></div>
<div class="card"><h3>Webhooks</h3><p class="muted">HTTPS endpoints receive signed JSON (<code>X-ITSupport-Signature: sha256=…</code>) on <code>ticket.created</code> and <code>ticket.status_changed</code>.</p>
<form method="post" action="<?= base_url('its-webhook-add') ?>"><?= csrf_field() ?><label for="wu">Endpoint URL</label><input id="wu" name="url" type="url" maxlength="500" placeholder="https://example.com/hook" required><button class="btn">Add webhook</button></form>
<table><tr><th>URL</th><th>Secret</th><th>Last</th><th></th></tr><?php foreach ($webhooks as $h) { ?><tr><td style="word-break:break-all"><?= esc($h['url']) ?></td><td><code><?= esc($h['secret']) ?></code></td><td><?= esc(($h['last_status'] ?? '-') . ' ' . ($h['last_sent'] ?? '')) ?></td><td><form method="post" action="<?= base_url('its-webhook-delete/' . (int) $h['id']) ?>"><?= csrf_field() ?><button class="button secondary">Remove</button></form></td></tr><?php } ?></table>
<?php if ($webhooks) { ?><form method="post" action="<?= base_url('its-webhook-test') ?>"><?= csrf_field() ?><button class="button secondary">Send test event</button></form><?php } ?></div>
</div>
<?php } elseif ($tab === 'settings') { ?>
<div class="card"><h3>Company</h3><form method="post" action="<?= base_url('its-admin-settings') ?>" enctype="multipart/form-data"><?= csrf_field() ?>
<label for="company-name">Company name / header title</label><input id="company-name" name="company_name" maxlength="190" value="<?= esc(\App\Models\SettingModel::get('company_name','')) ?>" required>
<label for="company-email">Company email</label><input id="company-email" type="email" name="company_email" maxlength="190" value="<?= esc(\App\Models\SettingModel::get('company_email','')) ?>">
<label for="header-tagline">Header subtitle</label><input id="header-tagline" name="header_tagline" maxlength="190" value="<?= esc(\App\Models\SettingModel::get('header_tagline','IT support workspace')) ?>" placeholder="Shown beneath your company name">
<label for="footer-text">Footer text</label><input id="footer-text" name="footer_text" maxlength="255" value="<?= esc(\App\Models\SettingModel::get('footer_text','CodeIgniter 4 port')) ?>" placeholder="Optional text shown at the bottom of every page">
<p class="eyebrow" style="margin-top:14px">Letterhead on printed reports</p>
<?php foreach (\App\Libraries\Brand::FIELDS as $bk => [$bl, $bmax]) { ?><label for="brand-<?= $bk ?>"><?= esc($bl) ?></label><?php if ($bmax > 200) { ?><textarea id="brand-<?= $bk ?>" name="<?= $bk ?>" maxlength="<?= $bmax ?>" rows="2"><?= esc(\App\Models\SettingModel::get($bk, '')) ?></textarea><?php } else { ?><input id="brand-<?= $bk ?>" name="<?= $bk ?>" maxlength="<?= $bmax ?>" value="<?= esc(\App\Models\SettingModel::get($bk, '')) ?>"><?php } ?><?php } ?>
<label for="company-logo">Brand logo (PNG, JPEG, GIF, or WebP; max 5 MB). Used in the header and on every printed report.</label><input id="company-logo" type="file" name="company_logo" accept="image/png,image/jpeg,image/gif,image/webp">
<?php $companyLogo = \App\Models\SettingModel::get('company_logo', ''); if ($companyLogo !== '') { ?><p class="muted">Current logo</p><img src="<?= esc(base_url($companyLogo)) ?>" alt="Current company logo" style="max-width:180px;max-height:90px;object-fit:contain"><p class="muted">Upload a replacement image to update the logo.</p><?php } ?>
<button class="btn">Save branding</button></form></div>
<div class="card"><h3>Appearance</h3><p class="muted">Choose a workspace color theme for every screen.</p>
<?php $selectedTheme = \App\Models\SettingModel::get('appearance_theme', 'ocean'); ?>
<form method="post" action="<?= base_url('its-admin-theme') ?>"><?= csrf_field() ?>
<div class="theme-options">
<?php foreach (['ocean' => ['Ocean', '#2563eb'], 'emerald' => ['Emerald', '#059669'], 'violet' => ['Violet', '#7c3aed'], 'sunset' => ['Sunset', '#ea580c']] as $value => [$label, $color]) { ?>
<label class="theme-option"><input type="radio" name="theme" value="<?= esc($value) ?>" <?= $selectedTheme === $value ? 'checked' : '' ?>><span class="theme-swatch" style="--swatch:<?= esc($color) ?>"></span><span><?= esc($label) ?></span></label>
<?php } ?>
</div>
<button class="btn">Save appearance</button></form></div>
<?php } elseif ($tab === 'reports') { ?>
<div class="card"><h3>Report Settings</h3><form method="post" action="<?= base_url('its-admin-reports') ?>"><?= csrf_field() ?>
<label><input type="checkbox" name="monthly_report_enabled" value="1" <?= \App\Models\SettingModel::get('monthly_report_enabled','1')==='1'?'checked':'' ?> style="width:auto;"> Monthly auto</label>
<label>Monthly recipients</label><input name="monthly_report_recipients" value="<?= esc(\App\Models\SettingModel::get('monthly_report_recipients','')) ?>">
<label>Monthly send day</label><input name="monthly_report_send_day" value="<?= esc(\App\Models\SettingModel::get('monthly_report_send_day','last')) ?>">
<label><input type="checkbox" name="weekly_report_enabled" value="1" <?= \App\Models\SettingModel::get('weekly_report_enabled','1')==='1'?'checked':'' ?> style="width:auto;"> Weekly auto</label>
<label>Weekly recipients</label><input name="weekly_report_recipients" value="<?= esc(\App\Models\SettingModel::get('weekly_report_recipients','')) ?>">
<button class="btn">Save</button></form></div>
<?php } elseif ($tab === 'mail') { ?>
<div class="card"><h3>Mail Test</h3><p class="muted">SMTP is configured in .env or via Admin settings.</p>
<form method="post" action="<?= base_url('its-admin-mail') ?>"><?= csrf_field() ?><label>Test Email</label><input type="email" name="test_email" required><button class="btn">Send Test</button></form></div>
<?php } elseif ($tab === 'fields') { $ef = $editField ?: []; $cfE = \App\Libraries\CustomFields::ENTITIES; $cfT = \App\Libraries\CustomFields::TYPES; ?>
<div class="grid grid-2">
<div class="card"><p class="eyebrow">Flexible data</p><h3><?= $ef ? 'Edit field' : 'Add a custom field' ?></h3>
<p class="muted">Capture anything your business needs (licence keys, contract numbers, site codes, IP ranges). Fields appear on the add/edit forms, detail pages and printed reports.</p>
<form method="post" action="<?= base_url('its-admin-fields-save') ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) ($ef['id'] ?? 0) ?>">
<div class="form-grid">
<div class="field"><label for="cf-entity">Applies to</label><select id="cf-entity" name="entity" required><?php foreach ($cfE as $k => $l) { ?><option value="<?= $k ?>" <?= ($ef['entity'] ?? '') === $k ? 'selected' : '' ?>><?= esc($l) ?></option><?php } ?></select></div>
<div class="field"><label for="cf-type">Type</label><select id="cf-type" name="type" required><?php foreach ($cfT as $k => $l) { ?><option value="<?= $k ?>" <?= ($ef['type'] ?? 'text') === $k ? 'selected' : '' ?>><?= esc($l) ?></option><?php } ?></select></div>
<div class="field"><label for="cf-label">Label</label><input id="cf-label" name="label" maxlength="120" required value="<?= esc($ef['label'] ?? '') ?>" placeholder="e.g. Licence key"></div>
<div class="field"><label for="cf-key">Key (optional)</label><input id="cf-key" name="field_key" maxlength="40" pattern="[a-z][a-z0-9_]*" value="<?= esc($ef['field_key'] ?? '') ?>" placeholder="auto from label"></div>
<div class="field wide"><label for="cf-opt">Dropdown options (comma separated)</label><input id="cf-opt" name="options" maxlength="2000" value="<?= esc(implode(', ', json_decode((string) ($ef['options_json'] ?? ''), true) ?: [])) ?>" placeholder="Only for dropdowns, e.g. Gold, Silver, Bronze"></div>
<div class="field wide"><label for="cf-help">Help text</label><input id="cf-help" name="help" maxlength="255" value="<?= esc($ef['help'] ?? '') ?>"></div>
<div class="field"><label for="cf-sort">Order</label><input id="cf-sort" type="number" min="0" max="999" name="sort_order" value="<?= (int) ($ef['sort_order'] ?? 0) ?>"></div>
<div class="field"><label for="cf-act">Status</label><select id="cf-act" name="active"><option value="1">Active</option><option value="0" <?= isset($ef['active']) && !(int) $ef['active'] ? 'selected' : '' ?>>Hidden</option></select></div>
</div>
<?php foreach (['required' => 'Required', 'show_in_list' => 'Show as a column in lists', 'show_in_report' => 'Print on reports', 'show_in_portal' => 'Visible to the client in the portal'] as $fk => $fl) { $on = $ef ? !empty($ef[$fk]) : in_array($fk, ['show_in_report'], true); ?><label class="cf-check" style="display:flex;gap:8px;align-items:center;margin:6px 0"><input type="checkbox" name="<?= $fk ?>" value="1" <?= $on ? 'checked' : '' ?>> <?= esc($fl) ?></label><?php } ?>
<div class="form-actions"><button class="btn">Save field</button><?php if ($ef) { ?><a class="button secondary" href="<?= base_url('its-admin?tab=fields') ?>">Cancel</a><?php } ?></div>
</form></div>
<div class="card"><h3>Fields</h3>
<?php if (!$customFields) { ?><p class="muted">No custom fields yet.</p><?php } else { ?><div class="tbl"><table><tr><th>Applies to</th><th>Label</th><th>Type</th><th>Flags</th><th></th></tr>
<?php foreach ($customFields as $f) { ?><tr><td><?= esc($cfE[$f['entity']] ?? $f['entity']) ?></td><td><?= esc($f['label']) ?><br><code><?= esc($f['field_key']) ?></code></td><td><?= esc($cfT[$f['type']] ?? $f['type']) ?></td>
<td><?= (int) $f['active'] ? '' : '<span class="badge gray">hidden</span> ' ?><?= $f['required'] ? '<span class="badge orange">required</span> ' : '' ?><?= $f['show_in_list'] ? '<span class="badge blue">list</span> ' : '' ?><?= $f['show_in_report'] ? '<span class="badge green">report</span> ' : '' ?><?= $f['show_in_portal'] ? '<span class="badge blue">portal</span>' : '' ?></td>
<td><a class="button secondary" href="<?= base_url('its-admin?tab=fields&edit_field=' . $f['id']) ?>">Edit</a></td></tr><?php } ?></table></div><?php } ?>
</div></div>
<?php } elseif ($tab === 'templates') { ?>
<div class="grid grid-2 template-manager">
<section class="card"><div class="section-heading"><div><p class="eyebrow">WORKFLOW TOOLKIT</p><h3><?= $editTemplate ? 'Edit template' : 'Create a template' ?></h3><p class="muted">Save a reusable starting point for tickets, quotes, or service reports.</p></div></div>
<form method="post" action="<?= base_url('its-admin-templates') ?>"><?= csrf_field() ?>
<input type="hidden" name="id" value="<?= esc($editTemplate['id'] ?? '') ?>">
<label>Used for</label><select name="template_type" required><option value="ticket" <?= ($editTemplate['template_type'] ?? '') === 'ticket' ? 'selected' : '' ?>>Ticket</option><option value="quote" <?= ($editTemplate['template_type'] ?? '') === 'quote' ? 'selected' : '' ?>>Quote</option><option value="report" <?= ($editTemplate['template_type'] ?? '') === 'report' ? 'selected' : '' ?>>Service report</option></select>
<label>Template name</label><input name="name" maxlength="120" value="<?= esc($editTemplate['name'] ?? '') ?>" placeholder="e.g. Network troubleshooting" required>
<label>Default subject</label><input name="subject" maxlength="255" value="<?= esc($editTemplate['subject'] ?? '') ?>" placeholder="Short title to fill in">
<label>Default description or notes</label><textarea name="body" rows="7" placeholder="Add reusable instructions, scope, checklist, or report introduction."><?= esc($editTemplate['body'] ?? '') ?></textarea>
<div class="quick-actions"><button class="btn"><?= $editTemplate ? 'Save changes' : 'Create template' ?></button><?php if ($editTemplate) { ?><a class="button secondary" href="<?= base_url('its-admin?tab=templates') ?>">Cancel</a><?php } ?></div>
</form></section>
<section class="card"><div class="section-heading"><div><p class="eyebrow">LIBRARY</p><h3>Saved templates</h3><p class="muted">Select a template when creating a ticket, quote, or service report.</p></div><span class="badge gray"><?= count($templates) ?> saved</span></div>
<?php if ($templates) { ?><div class="template-list"><?php foreach ($templates as $template) { ?><article class="template-item"><div class="template-item-copy"><span class="badge blue"><?= esc(ucfirst($template['template_type'])) ?></span><strong><?= esc($template['name']) ?></strong><span class="muted"><?= esc($template['subject'] ?: 'No default subject') ?></span></div><div class="quick-actions"><a class="button secondary" href="<?= base_url('its-admin?tab=templates&edit_template=' . $template['id']) ?>">Edit</a><form method="post" action="<?= base_url('its-admin-templates-delete/' . $template['id']) ?>" onsubmit="return confirm('Delete this template?')"><?= csrf_field() ?><button class="btn danger">Delete</button></form></div></article><?php } ?></div><?php } else { ?><p class="template-empty">Your template library is empty. Add your first reusable workflow on the left.</p><?php } ?>
</section>
</div>
<?php } elseif ($tab === 'maintenance') { ?>
<div class="card"><h3>Add Schedule</h3><form method="post" action="<?= base_url('its-admin-schedules') ?>"><?= csrf_field() ?>
<label>Client</label><select name="client_id" required><?php foreach ($clients as $c) { ?><option value="<?= $c['id'] ?>"><?= esc($c['name']) ?></option><?php } ?></select>
<label>Name</label><input name="name" required><label>Description</label><textarea name="description"></textarea>
<label>Interval days</label><input type="number" name="interval_days" value="30" min="1">
<button class="btn">Add</button></form>
<table><tr><th>Name</th><th>Client</th><th>Interval</th><th>Next</th></tr><?php foreach ($schedules as $s) { ?><tr><td><?= esc($s['name']) ?></td><td><?= esc($s['client_name']) ?></td><td><?= $s['interval_days'] ?>d</td><td><?= esc($s['next_run']) ?></td></tr><?php } ?></table></div>
<?php } elseif ($tab === 'audit') { ?>
<div class="card tbl"><table><tr><th>Time</th><th>User</th><th>Action</th><th>Module</th><th>Details</th></tr>
<?php foreach ($logs as $l) { ?><tr><td><?= esc($l['created_at']) ?></td><td><?= esc($l['user_name'] ?? 'system') ?></td><td><?= esc($l['action']) ?></td><td><?= esc($l['module']) ?></td><td><?= esc($l['details']) ?></td></tr><?php } ?></table></div>
<?php } elseif ($tab === 'maillog') { ?>
<div class="card tbl"><table><tr><th>Time</th><th>To</th><th>Subject</th><th>Status</th></tr>
<?php foreach ($mails as $m) { ?><tr><td><?= esc($m['created_at']) ?></td><td><?= esc($m['recipients']) ?></td><td><?= esc($m['subject']) ?></td><td><?= esc($m['status']) ?></td></tr><?php } ?></table></div>
<?php } else { ?>
<div class="card tbl"><table><tr><th>Time</th><th>User</th><th>Category</th><th>Change</th></tr>
<?php foreach (array_reverse($customizations) as $c) { ?><tr><td><?= esc($c['timestamp']) ?></td><td><?= esc($c['user']) ?></td><td><?= esc($c['category']) ?></td><td><?= esc($c['change']) ?></td></tr><?php } ?></table></div>
<?php } ?>
<style>
.theme-options{display:grid;grid-template-columns:repeat(auto-fit,minmax(115px,1fr));gap:10px;margin:16px 0 20px}.theme-option{display:flex;align-items:center;gap:9px;border:1px solid #e2e8f0;border-radius:11px;padding:10px;cursor:pointer}.theme-option input{width:auto;margin:0;accent-color:var(--swatch)}.theme-swatch{width:18px;height:18px;border-radius:50%;background:var(--swatch);box-shadow:0 0 0 3px color-mix(in srgb,var(--swatch) 16%,white)}.template-manager{align-items:start}.section-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:18px}.section-heading h3{margin:0 0 5px}.section-heading p{margin:0}.template-item{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:13px 0;border-top:1px solid #e9eef5}.template-item-copy{display:grid;justify-items:start;gap:5px;min-width:0}.template-item-copy strong{overflow-wrap:anywhere}.template-item .quick-actions{flex:0 0 auto}.template-item form{margin:0}.template-empty{padding:26px 10px;border:1px dashed #d3ddea;border-radius:12px;color:#68788e;text-align:center}@media(max-width:560px){.template-item{align-items:flex-start;flex-direction:column}}
</style>
<?= view('itsupport/_footer') ?>