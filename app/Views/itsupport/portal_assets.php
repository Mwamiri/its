<?= view('itsupport/_header') ?>
<?php $today = date('Y-m-d'); $badge = ['active' => 'green', 'under_repair' => 'orange', 'faulty' => 'orange', 'retired' => 'gray']; ?>
<div class="section-heading"><div><p class="eyebrow">My support</p><h2>My equipment <span class="muted">(<?= count($assets) ?>)</span></h2></div>
<div class="quick-actions" style="display:flex;gap:8px;flex-wrap:wrap"><a class="button secondary" href="<?= base_url('its-portal-assets-report') ?>">Printable register</a><a class="button" href="<?= base_url('its-portal-new') ?>">Report an issue</a></div></div>
<div class="pa-grid">
<?php foreach ($assets as $a) { $open = $openByAsset[(int) $a['id']] ?? 0; $exp = !empty($a['warranty_until']) && $a['warranty_until'] < $today; ?>
<article class="card pa-card"><div class="section-heading"><div><h3 style="margin:0"><?= esc($a['name']) ?></h3><p class="muted" style="margin:2px 0 0"><?= esc(trim($a['type'] . ' · ' . trim($a['brand'] . ' ' . $a['model']), ' ·')) ?></p></div><span class="badge <?= $badge[$a['status']] ?? 'gray' ?>"><?= esc(str_replace('_', ' ', $a['status'])) ?></span></div>
<dl><?php foreach (['Serial' => $a['serial_number'], 'Location' => $a['location'], 'Specification' => implode(' · ', array_filter([$a['cpu'] ?? '', $a['ram'] ?? '', $a['storage'] ?? '', $a['os'] ?? '']))] as $k => $v) { if ((string) $v === '') continue; ?><dt><?= $k ?></dt><dd><?= esc($v) ?></dd><?php } ?>
<?php if (!empty($a['warranty_until'])) { ?><dt>Warranty</dt><dd><?= esc($a['warranty_until']) ?><?= $exp ? ' <span class="badge orange">expired</span>' : '' ?></dd><?php } ?>
<?php foreach ($fields as $f) { $v = \App\Libraries\CustomFields::display($f, $values[$a['id']][$f['field_key']] ?? ''); if ($v === '') continue; ?><dt><?= esc($f['label']) ?></dt><dd><?= esc($v) ?></dd><?php } ?></dl>
<div class="pa-foot"><?= $open ? '<span class="badge blue">' . $open . ' open request' . ($open > 1 ? 's' : '') . '</span>' : '<span class="muted">No open requests</span>' ?><a class="button secondary" href="<?= base_url('its-portal-new?asset_id=' . $a['id']) ?>">Report a problem</a></div>
</article>
<?php } ?>
<?php if (!$assets) { ?><div class="card"><p class="muted">No equipment is recorded for your organisation yet. Your support team adds devices as they service them.</p></div><?php } ?>
</div>
<style>.pa-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(min(100%,300px),1fr));gap:14px}.pa-card{margin:0;display:flex;flex-direction:column}.pa-card dl{display:grid;grid-template-columns:auto 1fr;gap:4px 12px;margin:10px 0;font-size:13px}.pa-card dt{color:var(--muted);font-weight:600}.pa-card dd{margin:0;word-break:break-word}.pa-foot{margin-top:auto;display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;padding-top:8px;border-top:1px solid var(--line)}</style>
<?= view('itsupport/_footer') ?>
