<?php $brand = \App\Libraries\Brand::info(); ?>
<div class="doc-foot">
  <span><?= $brand['footer'] !== '' ? nl2br(esc($brand['footer'])) : esc($brand['name']) ?></span>
  <span>Generated <?= esc(date('Y-m-d H:i')) ?><?= session('its_user') ? ' by ' . esc(session('its_user')['name'] ?? '') : '' ?></span>
</div>
