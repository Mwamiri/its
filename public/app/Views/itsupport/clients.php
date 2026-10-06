<?= view('itsupport/_header') ?>
<div class="grid grid-2">
<div class="card tbl"><table><tr><th>Name</th><th>Contact</th><th>Email</th><th></th></tr>
<?php foreach ($clients as $c) { ?><tr><td><?= esc($c['name'] ?? $c->name) ?></td><td><?= esc($c['contact_person'] ?? $c->contact_person) ?></td><td><?= esc($c['email'] ?? $c->email) ?></td><td><a class="button secondary" href="<?= base_url('its-clients-edit/' . ($c['id'] ?? $c->id)) ?>">Edit</a></td></tr><?php } ?>
</table></div>
<div class="card"><h3><?= $edit ? 'Edit' : 'Add' ?> Client</h3>
<form method="post" action="<?= base_url('its-clients-save/' . ($edit['id'] ?? '')) ?>" enctype="multipart/form-data"><?= csrf_field() ?>
<label>Name</label><input name="name" value="<?= esc($edit['name'] ?? '') ?>" required>
<label>Contact</label><input name="contact_person" value="<?= esc($edit['contact_person'] ?? '') ?>">
<label>Phone</label><input name="phone" value="<?= esc($edit['phone'] ?? '') ?>">
<label>Email</label><input type="email" name="email" value="<?= esc($edit['email'] ?? '') ?>">
<label>Address</label><textarea name="address"><?= esc($edit['address'] ?? '') ?></textarea>
<label>Logo</label><input type="file" name="logo" accept="image/*">
<label>Notes</label><textarea name="notes"><?= esc($edit['notes'] ?? '') ?></textarea>
<button class="btn">Save</button></form></div>
</div>
<?= view('itsupport/_footer') ?>