<?= view('itsupport/_header') ?>
<?php $e = fn($x) => esc((string) $x); ?>
<?php if (!$cur) { ?>
<div class="tw:flex tw:flex-wrap tw:items-center tw:gap-2 tw:mb-4"><h2 class="tw:m-0 tw:mr-auto tw:text-2xl tw:font-bold">Training <span class="tw:text-muted tw:text-base">(<?= count($sessions) ?>)</span></h2></div>
<div class="grid grid-2">
<div class="card tbl"><table><tr><th>Date</th><th>Session</th><th>Client</th><th>Hours</th><th>Present</th><th></th></tr>
<?php foreach ($sessions as $s) { ?><tr><td><?= $e($s['session_date']) ?></td><td><?= $e($s['title']) ?></td><td><?= $e($s['client_name']) ?></td><td><?= $e($s['hours']) ?><?= $s['counts_hours'] ? '' : ' <span class="badge">free</span>' ?></td><td><?= (int) $s['present'] ?></td><td><a class="button secondary" href="<?= base_url('its-training-view/' . $s['id']) ?>">Attendance</a></td></tr><?php } ?>
<?php if (!$sessions) { ?><tr><td colspan="6">No training sessions yet. Create one to start capturing attendance.</td></tr><?php } ?>
</table></div>
<div class="card"><h3>New training session</h3>
<form method="post" action="<?= base_url('its-training-save') ?>"><?= csrf_field() ?>
<label>Client / contract</label><select name="client_id" required><option value="">Choose client</option><?php foreach ($clients as $c) { ?><option value="<?= $c['id'] ?>"><?= $e($c['name']) ?></option><?php } ?></select>
<label>Title</label><input name="title" required placeholder="e.g. Staff onboarding on new email system">
<label>Date</label><input type="date" name="session_date" value="<?= date('Y-m-d') ?>" required>
<label>Start time</label><input type="time" name="start_time">
<label>Duration (hours)</label><input type="number" name="hours" step="0.25" min="0" value="1">
<label>Location</label><input name="location" placeholder="Client office, online, etc.">
<label><input type="checkbox" name="counts_hours" value="1" checked> Counts toward the contract / retainer hours</label>
<label>Notes</label><textarea name="notes" placeholder="Topics covered, materials used"></textarea>
<button type="submit">Create session</button></form></div>
</div>
<?php } else { $link = base_url('its-attend/' . $cur['sign_token']); ?>
<div class="tw:flex tw:flex-wrap tw:items-center tw:gap-2 tw:mb-4"><h2 class="tw:m-0 tw:mr-auto tw:text-2xl tw:font-bold"><?= $e($cur['title']) ?></h2><a class="button secondary" href="<?= base_url('its-training') ?>">All sessions</a><a class="button secondary" href="<?= base_url('its-training-csv/' . $cur['id']) ?>">Export CSV</a><button class="secondary" onclick="window.print()" type="button">Print sheet</button></div>
<p><?= $e($cur['client_name']) ?> · <?= $e($cur['session_date']) ?> <?= $e(substr((string) $cur['start_time'], 0, 5)) ?> · <?= $e($cur['hours']) ?> h<?= $cur['location'] ? ' · ' . $e($cur['location']) : '' ?></p>
<div class="grid grid-2">
<div class="card"><h3>Attendance</h3>
<form method="post" action="<?= base_url('its-training-mark/' . $cur['id']) ?>"><?= csrf_field() ?>
<div class="tbl"><table><tr><th>Present</th><th>Name</th><th>Email</th><th>Signed</th><th></th></tr>
<?php foreach ($att as $a) { ?><tr><td><input type="checkbox" name="present[]" value="<?= $a['id'] ?>" <?= $a['attended'] ? 'checked' : '' ?> aria-label="Present"></td><td><?= $e($a['name']) ?><?= $a['role_title'] ? '<br><small>' . $e($a['role_title']) . '</small>' : '' ?></td><td><?= $e($a['email']) ?></td><td><?= $e($a['signed_at']) ?><?= $a['signed_via'] === 'self' ? ' <span class="badge blue">self</span>' : '' ?></td>
<td><button type="submit" class="secondary" formaction="<?= base_url('its-training-remove/' . $cur['id'] . '/' . $a['id']) ?>" formnovalidate>Remove</button></td></tr><?php } ?>
<?php if (!$att) { ?><tr><td colspan="5">No attendees yet. Add names or share the sign-in link.</td></tr><?php } ?>
</table></div>
<label><input type="checkbox" name="signin_open" value="1" <?= $cur['signin_open'] ? 'checked' : '' ?>> Self sign-in link open</label>
<label>Session notes</label><textarea name="notes"><?= $e($cur['notes']) ?></textarea>
<button type="submit">Save attendance</button></form></div>
<div>
<div class="card"><h3>Self sign-in link</h3><p>Share this link or display it on screen. Attendees enter their own name, no login needed.</p><input readonly value="<?= $e($link) ?>" onclick="this.select()" aria-label="Sign-in link"></div>
<div class="card"><h3>Add attendees</h3>
<form method="post" action="<?= base_url('its-training-attendees/' . $cur['id']) ?>"><?= csrf_field() ?>
<label>One per line: Name, email (email optional)</label><textarea name="names" rows="5" placeholder="Jane Wanjiku, jane@client.com&#10;Peter Otieno"></textarea><button type="submit">Add</button></form></div>
</div></div>
<?php } ?>
<?= view('itsupport/_footer') ?>