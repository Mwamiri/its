<?= view('itsupport/_header') ?>
<div class="card">
<a class="button <?= $tab==='users'?'':'secondary' ?>" href="<?= base_url('its-admin?tab=users') ?>">Users</a>
<a class="button <?= $tab==='settings'?'':'secondary' ?>" href="<?= base_url('its-admin?tab=settings') ?>">Settings</a>
<a class="button <?= $tab==='reports'?'':'secondary' ?>" href="<?= base_url('its-admin?tab=reports') ?>">Reports</a>
<a class="button <?= $tab==='mail'?'':'secondary' ?>" href="<?= base_url('its-admin?tab=mail') ?>">Mail</a>
<a class="button <?= $tab==='maintenance'?'':'secondary' ?>" href="<?= base_url('its-admin?tab=maintenance') ?>">Maintenance</a>
<a class="button <?= $tab==='audit'?'':'secondary' ?>" href="<?= base_url('its-admin?tab=audit') ?>">Audit</a>
<a class="button <?= $tab==='maillog'?'':'secondary' ?>" href="<?= base_url('its-admin?tab=maillog') ?>">Mail Log</a>
<a class="button <?= $tab==='changes'?'':'secondary' ?>" href="<?= base_url('its-admin?tab=changes') ?>">Changes</a>
<a class="button secondary" href="<?= base_url('its-admin-backup') ?>">Backup</a>
</div>
<?php if ($tab === 'users') { ?>
<div class="card"><h3>Add User</h3><form method="post" action="<?= base_url('its-admin-users') ?>"><?= csrf_field() ?>
<label>Name</label><input name="name" required><label>Username</label><input name="username" required>
<label>Password</label><input type="password" name="password" required>
<label>Role</label><select name="role"><option>admin</option><option>manager</option><option>technician</option></select>
<button class="btn">Add</button></form>
<table><tr><th>Name</th><th>Username</th><th>Role</th></tr><?php foreach ($users as $u) { ?><tr><td><?= esc($u['name']) ?></td><td><?= esc($u['username']) ?></td><td><?= esc($u['role']) ?></td></tr><?php } ?></table></div>
<?php } elseif ($tab === 'settings') { ?>
<div class="card"><h3>Company</h3><form method="post" action="<?= base_url('its-admin-settings') ?>" enctype="multipart/form-data"><?= csrf_field() ?>
<label>Company Name</label><input name="company_name" value="<?= esc(\App\Models\SettingModel::get('company_name','')) ?>">
<label>Company Email</label><input type="email" name="company_email" value="<?= esc(\App\Models\SettingModel::get('company_email','')) ?>">
<label>Logo</label><input type="file" name="company_logo" accept="image/*">
<button class="btn">Save</button></form></div>
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
<?php } elseif ($tab === 'maintenance') { ?>
<div class="card"><h3>Add Schedule</h3><form method="post" action="<?= base_url('its-admin-schedules') ?>"><?= csrf_field() ?>
<label>Client</label><select name="client_id" required><?php foreach ($clients as $c) { ?><option value="<?= $c['id'] ?? $c->id ?>"><?= esc($c['name'] ?? $c->name) ?></option><?php } ?></select>
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
<?= view('itsupport/_footer') ?>