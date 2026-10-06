<?= view('itsupport/_header') ?>
<div class="tw-flex tw-flex-wrap tw-items-center tw-gap-2 tw-mb-4">
<h2 class="tw-m-0 tw-mr-auto tw-text-2xl tw-font-bold">Clients <span class="tw-text-muted tw-text-base">(<?= count($clients) ?>)</span></h2>
<input id="row-filter" type="search" placeholder="Filter clients" aria-label="Filter clients" class="tw-w-56">
</div>
<div class="grid grid-2">
<div class="card tbl"><table id="filter-table"><tr><th>Name</th><th>Contact</th><th>Email</th><th></th></tr>
<?php foreach ($clients as $c) { ?><tr><td><?= esc($c['name']) ?></td><td><?= esc($c['contact_person']) ?></td><td><?= esc($c['email']) ?></td><td><a class="button secondary" href="<?= base_url('its-clients-edit/' . $c['id']) ?>">Edit</a></td></tr><?php } ?>
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
<script>document.getElementById('row-filter').addEventListener('input',function(){const q=this.value.toLowerCase();document.querySelectorAll('#filter-table tr').forEach((r,i)=>{if(i)r.hidden=!r.textContent.toLowerCase().includes(q);});});</script>
<?= view('itsupport/_footer') ?>