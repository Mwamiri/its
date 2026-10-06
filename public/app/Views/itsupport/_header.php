<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#0f172a">
<title><?= esc($title ?? 'App') ?> - <?= esc(\App\Models\SettingModel::get('company_name','IT Support')) ?></title>
<link rel="manifest" href="<?= base_url('its-manifest') ?>">
<style>
*{box-sizing:border-box}body{margin:0;font-family:Arial,sans-serif;background:#f1f5f9;color:#0f172a}
header{background:#0f172a;color:#fff;padding:14px 16px}header h1{margin:0;font-size:18px}
nav{background:#fff;border-bottom:1px solid #dbe2ea;padding:10px;display:flex;flex-wrap:wrap;gap:8px}
nav a,nav button{border:0;background:#e2e8f0;color:#0f172a;padding:8px 10px;border-radius:10px;font-size:13px;text-decoration:none;cursor:pointer}
main{padding:16px;padding-bottom:60px}.card{background:#fff;border:1px solid #dbe2ea;border-radius:14px;padding:16px;margin-bottom:16px}
.grid{display:grid;grid-template-columns:1fr;gap:12px}@media(min-width:800px){.grid{grid-template-columns:1fr 1fr 1fr}.grid-2{grid-template-columns:1fr 1fr}}
table{width:100%;border-collapse:collapse}th,td{border:1px solid #dbe2ea;padding:8px;font-size:14px;text-align:left;vertical-align:top}th{background:#f8fafc}.tbl{overflow-x:auto}
input,select,textarea{width:100%;padding:11px;border:1px solid #cbd5e1;border-radius:10px;font-size:15px;margin:5px 0 12px}
button.btn{border:0;background:#2563eb;color:#fff;border-radius:10px;padding:11px 14px;cursor:pointer}button.danger{background:#dc2626}button.secondary{background:#64748b}
a.button{display:inline-block;background:#2563eb;color:#fff;border-radius:10px;padding:9px 12px;font-size:14px;text-decoration:none}a.button.secondary{background:#64748b}
.badge{display:inline-block;padding:4px 8px;border-radius:999px;font-size:11px;font-weight:bold}.badge.gray{background:#e2e8f0}.badge.blue{background:#dbeafe;color:#1e40af}.badge.green{background:#dcfce7;color:#166534}.badge.orange{background:#ffedd5;color:#9a3412}
.muted{color:#64748b;font-size:13px}.photo-thumb{width:80px;height:80px;object-fit:cover;border-radius:8px;margin:4px}
footer{padding:16px;text-align:center;color:#64748b;font-size:12px}
@media print{header,nav,footer,.no-print{display:none!important}}
</style></head><body>
<header><h1><?= esc(\App\Models\SettingModel::get('company_name','IT Support')) ?></h1></header>
<?php $u = session('its_user'); if ($u) { ?>
<nav>
<a href="<?= base_url('its-dashboard') ?>">Dashboard</a>
<a href="<?= base_url('its-tickets') ?>">Tickets</a>
<a href="<?= base_url('its-quotes') ?>">Quotes</a>
<a href="<?= base_url('its-clients') ?>">Clients</a>
<a href="<?= base_url('its-assets') ?>">Assets</a>
<?php if ($u['role'] === 'admin') { ?><a href="<?= base_url('its-admin') ?>">Admin</a><?php } ?>
<a href="<?= base_url('its-features') ?>">Features</a>
<a href="<?= base_url('its-help') ?>">Help</a>
<form method="post" action="<?= base_url('its-logout') ?>" style="display:inline"><?= csrf_field() ?><button>Logout</button></form>
</nav>
<?php } ?>
<main>
<?php if (session('ok')) { ?><div class="card" style="color:#16a34a;"><?= esc(session('ok')) ?></div><?php } ?>
<?php if (session('err')) { ?><div class="card" style="color:#dc2626;"><?= esc(session('err')) ?></div><?php } ?>