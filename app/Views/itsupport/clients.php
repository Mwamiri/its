<?= view('itsupport/_header') ?>
<?php $ev = static fn(string $k) => old($k) ?? ($edit[$k] ?? ''); ?>
<div class="tw-flex tw-flex-wrap tw-items-center tw-gap-2 tw-mb-4">
<h2 class="tw-m-0 tw-mr-auto tw-text-2xl tw-font-bold">Clients <span class="tw-text-muted tw-text-base">(<?= count($clients) ?>)</span></h2>
<input id="row-filter" type="search" placeholder="Filter clients" aria-label="Filter clients" class="tw-w-56">
</div>
<div class="grid grid-2">
<div class="card tbl"><table id="filter-table"><tr><th>Name</th><th>Contact</th><?php foreach ($listFields as $lf) { ?><th><?= esc($lf['label']) ?></th><?php } ?><th>Assets</th><th>Open</th><th></th></tr>
<?php foreach ($clients as $c) { ?><tr>
<td><a href="<?= base_url('its-clients-view/' . $c['id']) ?>"><strong><?= esc($c['name']) ?></strong></a><?php if ($c['email']) { ?><br><span class="muted"><?= esc($c['email']) ?></span><?php } ?></td>
<td><?= esc($c['contact_person']) ?><?php if ($c['phone']) { ?><br><span class="muted"><?= esc($c['phone']) ?></span><?php } ?></td>
<?php foreach ($listFields as $lf) { ?><td><?= esc(\App\Libraries\CustomFields::display($lf, $cfValues[$c['id']][$lf['field_key']] ?? '')) ?></td><?php } ?>
<td><?= (int) $c['asset_count'] ?></td><td><?= (int) $c['open_count'] ? '<span class="badge orange">' . (int) $c['open_count'] . '</span>' : '0' ?></td>
<td style="white-space:nowrap"><a class="button secondary" href="<?= base_url('its-clients-view/' . $c['id']) ?>">Open</a> <a class="button secondary" href="<?= base_url('its-clients-edit/' . $c['id']) ?>">Edit</a></td></tr><?php } ?>
<?php if (!$clients) { ?><tr><td colspan="<?= 5 + count($listFields) ?>" class="muted">No clients yet. Add your first client using the form.</td></tr><?php } ?>
</table></div>
<div class="card"><h3><?= $edit ? 'Edit ' . esc($edit['name']) : 'Add client' ?></h3>
<form method="post" action="<?= base_url('its-clients-save' . ($edit ? '/' . $edit['id'] : '')) ?>" enctype="multipart/form-data"><?= csrf_field() ?>
<div class="form-grid">
<div class="field wide"><label for="c-name">Name</label><input id="c-name" name="name" maxlength="190" value="<?= esc($ev('name')) ?>" required></div>
<div class="field"><label for="c-contact">Contact person</label><input id="c-contact" name="contact_person" maxlength="190" value="<?= esc($ev('contact_person')) ?>"></div>
<div class="field"><label for="c-phone">Phone</label><input id="c-phone" type="tel" name="phone" maxlength="60" value="<?= esc($ev('phone')) ?>"></div>
<div class="field wide"><label for="c-email">Email</label><input id="c-email" type="email" name="email" maxlength="190" value="<?= esc($ev('email')) ?>"></div>
<div class="field wide"><label for="c-address">Address</label><textarea id="c-address" name="address" rows="2"><?= esc($ev('address')) ?></textarea></div>
</div>
<?= view('itsupport/_custom_fields', ['entity' => 'clients', 'values' => $editValues]) ?>
<label for="c-logo">Client logo (shown on their reports)</label><input id="c-logo" type="file" name="logo" accept="image/png,image/jpeg,image/gif,image/webp">
<?php if (!empty($edit['logo_path'])) { ?><img src="<?= esc(base_url($edit['logo_path'])) ?>" alt="" style="max-height:48px;margin:-6px 0 12px"><?php } ?>
<label for="c-notes">Notes</label><textarea id="c-notes" name="notes"><?= esc($ev('notes')) ?></textarea>
<div class="form-actions"><button class="btn">Save client</button><?php if ($edit) { ?><a class="button secondary" href="<?= base_url('its-clients-view/' . $edit['id']) ?>">Cancel</a><?php } ?></div></form></div>
</div>
<script>document.getElementById('row-filter').addEventListener('input',function(){const q=this.value.toLowerCase();document.querySelectorAll('#filter-table tr').forEach((r,i)=>{if(i)r.hidden=!r.textContent.toLowerCase().includes(q);});});</script>
<?= view('itsupport/_footer') ?>
