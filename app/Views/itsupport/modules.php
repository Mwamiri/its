<?= view('itsupport/_header') ?>
<?php use App\Libraries\Modules; ?>
<div class="section-heading"><div><p class="eyebrow">System</p><h2>Modules</h2><p class="muted">Turn optional features on or off. Switching a module off hides it from every menu and blocks its pages and API; its data is kept and returns when you switch it back on. Dashboard, tickets, clients, assets, administration, security and updates are always on.</p></div></div>
<div class="mod-grid">
<?php foreach (Modules::LIST as $key => [$name, $desc, $prefixes]) { $on = Modules::enabled($key); ?>
<article class="card mod<?= $on ? '' : ' is-off' ?>">
  <div class="mod-head"><h3><?= esc($name) ?></h3><span class="badge <?= $on ? 'green' : 'gray' ?>"><?= $on ? 'On' : 'Off' ?></span></div>
  <p class="muted"><?= esc($desc) ?></p>
  <p class="mod-paths"><?php foreach ($prefixes as $p) { ?><code>/<?= esc($p) ?></code> <?php } ?></p>
  <form method="post" action="<?= base_url('its-modules-toggle') ?>"><?= csrf_field() ?><input type="hidden" name="module" value="<?= esc($key) ?>"><input type="hidden" name="state" value="<?= $on ? 'off' : 'on' ?>">
  <button class="btn <?= $on ? 'secondary' : '' ?>" <?= $on && in_array($key, ['website', 'portal'], true) ? 'onclick="return confirm(\'Switch off ' . esc($name, 'attr') . '? Visitors will no longer be able to use it.\')"' : '' ?>><?= $on ? 'Switch off' : 'Switch on' ?></button></form>
</article>
<?php } ?>
</div>
<style>.mod-grid{display:grid;gap:14px;grid-template-columns:repeat(auto-fill,minmax(min(100%,280px),1fr))}.mod{margin:0;display:flex;flex-direction:column}.mod.is-off{opacity:.75;border-style:dashed}.mod-head{display:flex;justify-content:space-between;align-items:center;gap:10px}.mod-head h3{margin:0}.mod-paths{margin-top:auto;font-size:12px}.mod form{margin:0}</style>
<?= view('itsupport/_footer') ?>
