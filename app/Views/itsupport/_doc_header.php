<?php
/**
 * Branded letterhead for printable documents.
 * Expects $docTitle; optional $docRef, $docDate, $docTo (array with name/contact/email/phone/address/logo_path).
 */
$brand = \App\Libraries\Brand::info();
?>
<style>
.doc{background:#fff;color:#0f172a;border:1px solid var(--line);border-radius:16px;padding:28px;margin-bottom:16px}
.doc-head{display:flex;flex-wrap:wrap;justify-content:space-between;gap:18px;align-items:flex-start;border-bottom:3px solid var(--brand,#2563eb);padding-bottom:16px;margin-bottom:18px}
.doc-brand{display:flex;gap:14px;align-items:center}.doc-brand img{max-width:150px;max-height:70px;object-fit:contain}
.doc-brand h1{margin:0;font-size:20px;color:#0f172a}.doc-brand p,.doc-meta p,.doc-to p{margin:2px 0;font-size:12px;color:#475569}
.doc-meta{text-align:end}.doc-meta h2{margin:0 0 4px;font-size:18px;letter-spacing:.06em;text-transform:uppercase;color:var(--brand-dark,#1e40af)}
.doc-to{display:flex;gap:12px;align-items:center;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:10px 14px;margin-bottom:16px}.doc-to img{max-width:70px;max-height:46px;object-fit:contain}.doc-to strong{font-size:14px}
.doc table{width:100%;border-collapse:collapse;font-size:12.5px}.doc th,.doc td{border:1px solid #e2e8f0;padding:6px 8px;text-align:start;vertical-align:top}.doc th{background:#f1f5f9;color:#0f172a}
.doc h3{font-size:14px;margin:18px 0 8px;color:#0f172a}
.doc-foot{margin-top:22px;padding-top:10px;border-top:1px solid #e2e8f0;font-size:11px;color:#64748b;display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap}
.doc-actions{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px}
@media print{body{padding:0!important;background:#fff!important}.doc{border:0;border-radius:0;padding:0;margin:0}.doc-actions,.appbar,.tabbar,.ui-toolbar,body>header{display:none!important}main{padding:0!important}.doc th{background:#f1f5f9!important;-webkit-print-color-adjust:exact;print-color-adjust:exact}.doc-head{-webkit-print-color-adjust:exact;print-color-adjust:exact}tr{break-inside:avoid}}
</style>
<div class="doc-head">
  <div class="doc-brand">
    <?php if ($brand['logo_url'] !== '') { ?><img src="<?= esc($brand['logo_url']) ?>" alt="<?= esc($brand['name']) ?> logo"><?php } ?>
    <div><h1><?= esc($brand['name']) ?></h1>
      <?php if ($brand['address'] !== '') { ?><p><?= nl2br(esc($brand['address'])) ?></p><?php } ?>
      <p><?= esc(implode(' · ', array_filter([$brand['phone'], $brand['email'], $brand['website']]))) ?></p>
      <?php if ($brand['tax_id'] !== '') { ?><p>Reg/Tax: <?= esc($brand['tax_id']) ?></p><?php } ?>
    </div>
  </div>
  <div class="doc-meta"><h2><?= esc($docTitle) ?></h2>
    <?php if (!empty($docRef)) { ?><p><strong><?= esc($docRef) ?></strong></p><?php } ?>
    <p><?= esc($docDate ?? date('Y-m-d')) ?></p>
  </div>
</div>
<?php if (!empty($docTo)) { ?>
<div class="doc-to"><?php if (!empty($docTo['logo_path'])) { ?><img src="<?= esc(base_url($docTo['logo_path'])) ?>" alt=""><?php } ?>
  <div><p>Prepared for</p><strong><?= esc($docTo['name'] ?? '') ?></strong>
  <p><?= esc(implode(' · ', array_filter([$docTo['contact_person'] ?? '', $docTo['phone'] ?? '', $docTo['email'] ?? '']))) ?></p>
  <?php if (!empty($docTo['address'])) { ?><p><?= esc($docTo['address']) ?></p><?php } ?></div>
</div>
<?php } ?>
