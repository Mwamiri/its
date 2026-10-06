<?= view('itsupport/_header') ?>
<div class="grid grid-2">
<div class="card tbl"><table><tr><th>#</th><th>Client</th><th>Total</th><th>Status</th><th></th></tr>
<?php foreach ($quotes as $q) { ?><tr><td><?= esc($q['quote_number']) ?></td><td><?= esc($q['client_name']) ?></td><td><?= number_format((float)$q['total'],2) ?></td><td><span class="badge blue"><?= esc($q['status']) ?></span></td><td><a class="button secondary" href="<?= base_url('its-quotes-view/' . $q['id']) ?>">Open</a></td></tr><?php } ?>
</table></div>
<div class="card"><h3><?= $edit ? 'Edit' : 'New' ?> Quote</h3>
<form method="post" action="<?= base_url('its-quotes-save/' . ($edit['id'] ?? '')) ?>"><?= csrf_field() ?>
<label>Client</label><select name="client_id" required><?php foreach ($clients as $c) { ?><option value="<?= $c['id'] ?? $c->id ?>" <?= ($edit && $edit['client_id'] == ($c['id'] ?? $c->id)) ? 'selected' : '' ?>><?= esc($c['name'] ?? $c->name) ?></option><?php } ?></select>
<label>Subject</label><input name="subject" value="<?= esc($edit['subject'] ?? '') ?>" required>
<label>Notes</label><textarea name="notes"><?= esc($edit['notes'] ?? '') ?></textarea>
<h4>Lines</h4><div id="lines">
<?php if ($edit) { foreach ((new \App\Models\QuoteItemModel())->where('quote_id', $edit['id'])->findAll() as $i) { ?>
<div style="display:grid;grid-template-columns:1fr 80px 100px 36px;gap:6px;margin-bottom:6px;">
<input name="item_desc[]" value="<?= esc($i['description']) ?>"><input name="item_qty[]" type="number" step="0.01" value="<?= $i['quantity'] ?>"><input name="item_unit[]" type="number" step="0.01" value="<?= $i['unit_cost'] ?>"><button type="button" class="btn danger" onclick="this.parentNode.remove()">x</button></div>
<?php } } ?></div>
<button type="button" class="btn secondary" onclick="var d=document.createElement('div');d.style='display:grid;grid-template-columns:1fr 80px 100px 36px;gap:6px;margin-bottom:6px;';d.innerHTML='<input name=item_desc[] placeholder=Description><input name=item_qty[] type=number step=0.01 placeholder=Qty><input name=item_unit[] type=number step=0.01 placeholder=Unit><button type=button class=&quot;btn danger&quot; onclick=this.parentNode.remove()>x</button>';document.getElementById('lines').appendChild(d);">+ Line</button><br>
<button class="btn">Save Quote</button></form>
<?php if ($edit) { ?>
<form method="post" action="<?= base_url('its-quotes-send/' . $edit['id']) ?>" style="margin-top:10px;"><?= csrf_field() ?><button class="btn">Send Quote Email</button></form>
<p class="muted">Approval link:<br><code style="word-break:break-all;"><?= esc(base_url('its-approve/' . $edit['id'] . '/' . $token)) ?></code></p>
<?php } ?></div>
</div>
<?= view('itsupport/_footer') ?>