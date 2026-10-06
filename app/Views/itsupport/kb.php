<?= view('itsupport/_header') ?>
<div class="card"><h2>Knowledge Base</h2>
<form method="get" action="<?= base_url('its-kb') ?>"><label for="q">Search</label><input id="q" name="q" value="<?= esc($q) ?>"><button class="btn">Search</button></form></div>
<?php foreach ($articles as $a) { ?><div class="card"><details><summary><strong><?= esc($a['title']) ?></strong> <span class="badge gray"><?= esc($a['category']) ?></span> <span class="badge <?= $a['published'] ? 'green' : 'orange' ?>"><?= $a['published'] ? 'public' : 'draft' ?></span></summary>
<form method="post" action="<?= base_url('its-kb-save') ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
<label>Title</label><input name="title" value="<?= esc($a['title']) ?>" required><label>Category</label><input name="category" value="<?= esc($a['category']) ?>"><label>Body</label><textarea name="body" required><?= esc($a['body']) ?></textarea>
<label><input type="checkbox" name="published" value="1" <?= $a['published'] ? 'checked' : '' ?>> Visible to clients</label> <button class="btn">Save</button></form>
<form method="post" action="<?= base_url('its-kb-delete/' . $a['id']) ?>" onsubmit="return confirm('Delete this article?')"><?= csrf_field() ?><button class="btn red">Delete</button></form></details></div><?php } ?>
<?php if (!$articles) { ?><div class="card"><p class="muted">No articles found.</p></div><?php } ?>
<div class="card"><h3>New article</h3><form method="post" action="<?= base_url('its-kb-save') ?>"><?= csrf_field() ?>
<label>Title</label><input name="title" required><label>Category</label><input name="category"><label>Body</label><textarea name="body" required></textarea>
<label><input type="checkbox" name="published" value="1" checked> Visible to clients</label> <button class="btn">Add article</button></form></div>
<div class="card"><h3>Canned replies</h3>
<?php foreach ($canned as $c) { ?><form method="post" action="<?= base_url('its-canned-delete/' . $c['id']) ?>"><?= csrf_field() ?><strong><?= esc($c['title']) ?></strong> – <?= esc(mb_substr($c['body'], 0, 90)) ?> <button class="btn red">Delete</button></form><?php } ?>
<form method="post" action="<?= base_url('its-canned-save') ?>"><?= csrf_field() ?><label>Title</label><input name="title" maxlength="120" required><label>Text</label><textarea name="body" maxlength="3000" required></textarea><button class="btn">Add canned reply</button></form></div>
<?= view('itsupport/_footer') ?>