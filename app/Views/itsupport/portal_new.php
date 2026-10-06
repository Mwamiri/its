<?= view('itsupport/_header') ?>
<div class="card"><h2>Report an issue</h2>
<form method="post" action="<?= base_url('its-portal-new') ?>"><?= csrf_field() ?>
<label for="subject">Subject</label><input id="subject" name="subject" maxlength="190" required value="<?= esc(old('subject')) ?>">
<label for="description">What is happening?</label><textarea id="description" name="description" maxlength="5000" rows="6" required><?= esc(old('description')) ?></textarea>
<label for="priority">Urgency</label><select id="priority" name="priority"><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option></select>
<button class="btn">Submit</button></form></div>
<?= view('itsupport/_footer') ?>
