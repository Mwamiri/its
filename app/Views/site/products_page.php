<?php
use App\Libraries\Site;
$p = $site['primary_color']; $a = $site['accent_color'];
$d = $design;
$logoUrl = $site['logo'] !== '' ? base_url($site['logo']) : '';
$wa = preg_replace('/\D+/', '', (string) $site['whatsapp']);
$waLink = static fn(string $text = '') => 'https://wa.me/' . $wa . ($text !== '' ? '?text=' . rawurlencode($text) : '');
$tel = preg_replace('/[^\d+]/', '', (string) $site['phone']);
$socials = array_filter(['facebook' => $site['facebook'], 'linkedin' => $site['linkedin'], 'x' => $site['x'], 'instagram' => $site['instagram'], 'youtube' => $site['youtube']], static fn($u) => preg_match('#^https?://#i', (string) $u));
$socialMark = ['facebook' => 'f', 'linkedin' => 'in', 'x' => 'X', 'instagram' => 'ig', 'youtube' => 'yt'];
$fontStack = ['system' => '"Segoe UI",Roboto,Helvetica,Arial,sans-serif', 'inter' => 'Inter,"Segoe UI",sans-serif', 'poppins' => 'Poppins,"Segoe UI",sans-serif', 'montserrat' => 'Montserrat,"Segoe UI",sans-serif', 'roboto' => 'Roboto,"Segoe UI",sans-serif', 'open-sans' => '"Open Sans","Segoe UI",sans-serif'][$d['font']];
$btnRadius = ['rounded' => '12px', 'pill' => '999px', 'square' => '4px'][$d['button_style']];
$space = ['compact' => 'clamp(32px,4vw,56px)', 'normal' => 'clamp(44px,5.5vw,84px)', 'airy' => 'clamp(64px,8vw,120px)'][$d['spacing']];
$hdr = ['light' => ['rgba(255,255,255,.94)', 'var(--ink)', 'var(--line)'], 'dark' => ['#0f172a', '#e2e8f0', '#1e293b'], 'brand' => ['var(--p)', '#fff', 'transparent']][$d['header_style']];
$ftr = ['dark' => ['#0f172a', '#b8c3d6', '#fff', '#23314a'], 'brand' => ['var(--p)', '#dbe6fb', '#fff', 'rgba(255,255,255,.18)'], 'light' => ['#f1f5f9', '#475569', '#0f172a', '#dbe2ea']][$d['footer_style']];
$portalUrl = $user ? base_url(($user['role'] ?? '') === 'client' ? 'its-portal' : 'its-dashboard') : base_url('its-login');
$ctaUrl = Site::link($d['cta_url'] ?: 'contact');
$metaTitle = 'Our Apps | ' . $site['name'];
$metaDesc  = 'Church management, business ERP, HR, health management and sports systems built by ' . $site['name'] . '.';
$canonical = base_url('products');
$jsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP;

$products = [
  [
    'slug'    => 'churchz',
    'name'    => 'Churchz',
    'tagline' => 'Complete Church Management System',
    'color'   => '#7c3aed',
    'accent'  => '#5b21b6',
    'icon'    => 'church',
    'target'  => 'Catholic and Protestant churches',
    'desc'    => 'Churchz is a comprehensive digital platform designed to help churches manage their congregation, sacramental registers, finances, Mass schedules, and communications from one place.',
    'features' => [
      'Member & family records management',
      'Sacramental registers (Baptism, Confirmation, Marriage, Funerals)',
      'Mass intentions & celebrant printouts',
      'Collections, pledges & donor management',
      'SMS & email parish communications',
      'Bulletins, notices & announcements',
      'Clergy & staff accounts with role-based access',
    ],
    'demo_screens' => [
      ['label' => 'Dashboard', 'items' => ['Active members', 'Upcoming masses', 'Recent collections', 'Sacraments this month']],
      ['label' => 'Registers', 'items' => ['Baptism register', 'Confirmation register', 'Marriages', 'Funerals']],
    ],
  ],
  [
    'slug'    => 'umi',
    'name'    => 'UMI',
    'tagline' => 'Unified Business Management Platform',
    'color'   => '#0b4ea2',
    'accent'  => '#1d4ed8',
    'icon'    => 'briefcase',
    'target'  => 'SMEs, NGOs and growing businesses',
    'desc'    => 'UMI (Unified Management Interface) is an all-in-one business platform that brings CRM, inventory, HR, invoicing and analytics under one roof — giving SMEs the tools of a large enterprise at an affordable cost.',
    'features' => [
      'Customer & supplier relationship management (CRM)',
      'Inventory and stock control',
      'Invoicing, receipts & quotations',
      'HR with employee records & payroll',
      'Procurement & purchase orders',
      'Financial reports & analytics',
      'Extension marketplace for custom modules',
    ],
    'demo_screens' => [
      ['label' => 'Overview', 'items' => ['Sales this month', 'Outstanding invoices', 'Low stock alerts', 'Recent activity']],
      ['label' => 'Inventory', 'items' => ['Items catalogue', 'Stock movements', 'Purchase orders', 'Valuation report']],
    ],
  ],
  [
    'slug'    => 'hr',
    'name'    => 'HR Suite',
    'tagline' => 'Human Resources & Payroll System',
    'color'   => '#059669',
    'accent'  => '#047857',
    'icon'    => 'people',
    'target'  => 'Organizations with 10+ employees',
    'desc'    => 'HR Suite digitises your people operations — from biometric attendance and leave management to payroll, payslips and training events — giving HR teams and employees a modern self-service experience.',
    'features' => [
      'Employee records & contract management',
      'Biometric attendance & relay device integration',
      'Payroll engine with payslip generation',
      'Leave request & approval workflows',
      'Training events with QR-code check-in',
      'Appraisal & performance tracking',
      'Role-based access for HR, managers & staff',
    ],
    'demo_screens' => [
      ['label' => 'Payroll', 'items' => ['Monthly payrun', 'Payslips', 'Deductions', 'Bank transfer list']],
      ['label' => 'Attendance', 'items' => ['Daily report', 'Biometric devices', 'Leave calendar', 'Overtime tracker']],
    ],
  ],
  [
    'slug'    => 'hmisoz',
    'name'    => 'HMISOZ',
    'tagline' => 'Health Management Information System',
    'color'   => '#0891b2',
    'accent'  => '#0e7490',
    'icon'    => 'heart-pulse',
    'target'  => 'Clinics, health centres & hospitals',
    'desc'    => 'HMISOZ is a full electronic medical records and health information system built for Kenyan and East African health facilities. It covers patient registration, clinical workflows, pharmacy, lab and billing in one integrated platform.',
    'features' => [
      'Patient registration & unique health IDs',
      'Appointment scheduling & queue management',
      'Clinical notes & diagnosis recording (ICD codes)',
      'Lab order & results management',
      'Pharmacy dispensary & stock control',
      'Billing, insurance claims & receipts',
      'MOH-compliant reporting & indicators',
    ],
    'demo_screens' => [
      ['label' => 'Reception', 'items' => ['Today\'s queue', 'New patients', 'Appointments', 'Walk-ins']],
      ['label' => 'Clinical', 'items' => ['Triage vitals', 'Diagnosis & notes', 'Lab requests', 'Prescriptions']],
    ],
  ],
  [
    'slug'    => 'kksports',
    'name'    => 'KKSports',
    'tagline' => 'Sports League & Tournament Management',
    'color'   => '#ea580c',
    'accent'  => '#c2410c',
    'icon'    => 'trophy',
    'target'  => 'Sports associations, leagues & clubs',
    'desc'    => 'KKSports gives sports administrators a modern platform to run leagues and tournaments — managing teams, registering players, scheduling fixtures, recording results and publishing live standings to fans.',
    'features' => [
      'League & tournament creation',
      'Team & player registration with profiles',
      'Fixture scheduling & venue management',
      'Live results entry & auto-standings',
      'Statistics: top scorers, assists, cards',
      'Season archive & historical records',
      'Public-facing website for fans',
    ],
    'demo_screens' => [
      ['label' => 'Standings', 'items' => ['League table', 'Goal difference', 'Form guide', 'Top scorers']],
      ['label' => 'Fixtures', 'items' => ['Upcoming matches', 'Results', 'Venues', 'Match report']],
    ],
  ],
  [
    'slug'    => 'jasemlz',
    'name'    => 'JasemlZ',
    'tagline' => 'Academic Journal Management System',
    'color'   => '#0f766e',
    'accent'  => '#0d9488',
    'icon'    => 'journal-text',
    'target'  => 'Universities, research institutes & journals',
    'desc'    => 'JasemlZ is a full-featured open-access journal platform. It manages the complete scholarly publishing workflow — from article submission and double-blind peer review through to issue publication, DOI assignment and OAI-PMH metadata harvesting.',
    'features' => [
      'Online article submission portal',
      'Double-blind peer review workflow',
      'Issue & volume management',
      'Author, reviewer & editor accounts',
      'Announcements & call-for-papers',
      'OAI-PMH protocol for metadata harvesting',
      'PDF article publishing & full-text archiving',
    ],
    'demo_screens' => [
      ['label' => 'Journal', 'items' => ['Current issue', 'Recent articles', 'Announcements', 'Archive']],
      ['label' => 'Workflow', 'items' => ['New submissions', 'Under review', 'Accepted', 'Published']],
    ],
  ],
];
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= esc($metaTitle) ?></title>
<meta name="description" content="<?= esc($metaDesc) ?>">
<meta name="robots" content="index,follow">
<link rel="canonical" href="<?= esc($canonical) ?>">
<meta property="og:type" content="website"><meta property="og:title" content="<?= esc($metaTitle) ?>"><meta property="og:description" content="<?= esc($metaDesc) ?>"><meta property="og:url" content="<?= esc($canonical) ?>">
<meta name="theme-color" content="<?= esc($p) ?>">
<?php if ($logoUrl) { ?><link rel="icon" href="<?= esc($logoUrl) ?>"><?php } else { ?><link rel="icon" href="<?= base_url('icon.svg') ?>" type="image/svg+xml"><?php } ?>
<?php if (isset(Site::FONTS[$d['font']])) { ?><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=<?= Site::FONTS[$d['font']] ?>:wght@400;500;600;700;800&display=swap"><?php } ?>
<?php if (preg_match('/^G-[A-Z0-9]{4,15}$/', $site['ga_id'])) { ?><script async src="https://www.googletagmanager.com/gtag/js?id=<?= esc($site['ga_id']) ?>"></script><script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config','<?= esc($site['ga_id']) ?>');</script><?php } ?>
<style>
:root{--p:<?= esc($p) ?>;--a:<?= esc($a) ?>;--ink:#0f172a;--muted:#55627a;--line:#e3e8f0;--bg:#f6f8fc;--card:#fff;--r:<?= (int) $d['radius'] ?>px;--btn-r:<?= $btnRadius ?>;--space:<?= $space ?>;--hdr-bg:<?= $hdr[0] ?>;--hdr-fg:<?= $hdr[1] ?>;--hdr-line:<?= $hdr[2] ?>;--ft-bg:<?= $ftr[0] ?>;--ft-fg:<?= $ftr[1] ?>;--ft-h:<?= $ftr[2] ?>;--ft-line:<?= $ftr[3] ?>}
*{box-sizing:border-box}html,body{max-width:100%;overflow-x:clip}html{scroll-behavior:smooth;scroll-padding-top:90px;font-size:<?= (int) $d['font_size'] ?>px}body{margin:0;font-family:<?= $fontStack ?>;color:var(--ink);background:#fff;line-height:1.6;-webkit-font-smoothing:antialiased}
a{color:var(--p)}img{max-width:100%;height:auto}h1,h2,h3{line-height:1.2;text-wrap:balance;margin:0 0 .5em}h1{font-size:clamp(2rem,1.4rem + 2.6vw,3.3rem);letter-spacing:-.02em}h2{font-size:clamp(1.5rem,1.2rem + 1.2vw,2.2rem);letter-spacing:-.01em}h3{font-size:1.15rem}p{margin:0 0 1em;text-wrap:pretty}
.wrap{width:min(1180px,100% - 40px);margin-inline:auto}
.skip{position:absolute;left:-999px}.skip:focus{left:12px;top:12px;z-index:99;background:#fff;padding:8px 12px;border-radius:8px}
.topbar{background:var(--ink);color:#cbd5e1;font-size:13px}.topbar .wrap{display:flex;flex-wrap:wrap;gap:6px 22px;justify-content:space-between;padding-block:7px}.topbar a{color:#e2e8f0;text-decoration:none}.topbar span,.topbar a{display:inline-flex;align-items:center;gap:6px}
.nav{<?= $d['header_sticky'] ? 'position:sticky;top:0;' : 'position:relative;' ?>z-index:40;background:var(--hdr-bg);backdrop-filter:saturate(1.4) blur(12px);border-bottom:1px solid var(--hdr-line);color:var(--hdr-fg)}
.nav .wrap{display:flex;align-items:center;gap:14px;min-height:72px}.nav .wrap>*{min-width:0}
.brand{display:flex;align-items:center;gap:10px;text-decoration:none;color:var(--hdr-fg);font-weight:800;font-size:1.05rem;margin-inline-end:auto}.brand img{max-height:<?= (int) $d['logo_height'] ?>px;max-width:220px;object-fit:contain}.brand-mark{display:grid;place-items:center;width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,var(--p),var(--a));color:#fff}.brand small{display:block;font-weight:500;opacity:.75;font-size:12px}
.menu{display:flex;align-items:center;gap:2px;list-style:none;margin:0;padding:0}.menu a{display:block;white-space:nowrap;padding:8px 13px;border-radius:10px;text-decoration:none;color:var(--hdr-fg);font-weight:600;font-size:15px;transition:background .15s}.menu a:hover{background:color-mix(in srgb,currentColor 8%,transparent)}
.btn{white-space:nowrap;display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:12px 20px;border-radius:var(--btn-r);font-weight:700;text-decoration:none;border:2px solid transparent;cursor:pointer;font-size:15px;font-family:inherit;transition:transform .15s,box-shadow .15s}.btn:hover{transform:translateY(-1px)}.btn-p{background:var(--p);color:#fff;box-shadow:0 8px 20px color-mix(in srgb,var(--p) 30%,transparent)}.btn-o{border-color:currentColor;color:var(--p);background:transparent}.btn-w{background:#fff;color:var(--p)}.btn-sm{padding:8px 14px;font-size:14px}
.nav-actions{display:flex;gap:8px;align-items:center}.nav-toggle{display:none;background:none;border:1px solid var(--hdr-line);color:var(--hdr-fg);border-radius:10px;padding:8px 10px;cursor:pointer}
.menu-wrap{display:flex;align-items:center}
@media(max-width:1199px){.nav-toggle{display:inline-flex}.menu-wrap{position:absolute;inset-inline:0;top:100%;background:#fff;border-bottom:1px solid var(--line);box-shadow:0 18px 40px rgba(15,23,42,.1);opacity:0;pointer-events:none;transform:translateY(-8px);transition:opacity .2s,transform .2s;padding:10px 20px 18px;margin:0}.menu-wrap.open{opacity:1;pointer-events:auto;transform:none}.menu{flex-direction:column;align-items:stretch}.menu a{color:var(--ink);padding:11px 12px;border-radius:8px;font-size:16px}}
/* Hero */
.hero{padding-block:clamp(64px,8vw,120px);background:radial-gradient(1200px 600px at 70% -15%,color-mix(in srgb,var(--a) 12%,transparent),transparent),linear-gradient(180deg,color-mix(in srgb,var(--p) 6%,#fff),#fff);text-align:center}
.eyebrow{display:inline-flex;align-items:center;gap:8px;color:var(--p);font-weight:700;font-size:13px;letter-spacing:.08em;text-transform:uppercase;margin-bottom:12px}.eyebrow:before{content:"";width:22px;height:2px;background:var(--a)}
.lead{font-size:clamp(1.05rem,1rem + .35vw,1.25rem);color:var(--muted)}
/* Product cards */
.prod-grid{display:grid;gap:clamp(32px,5vw,72px);padding-block:var(--space)}
.prod-row{display:grid;gap:32px 48px;grid-template-columns:1fr 1fr;align-items:center}
.prod-row.reverse .prod-mockup{order:-1}
.prod-badge{display:inline-flex;align-items:center;gap:8px;border-radius:999px;padding:5px 14px;font-weight:700;font-size:12px;letter-spacing:.04em;text-transform:uppercase;margin-bottom:14px}
.prod-features{list-style:none;padding:0;margin:16px 0 24px;display:grid;gap:8px}
.prod-features li{display:flex;align-items:flex-start;gap:10px;font-size:15px;color:var(--muted)}.prod-features li:before{content:"✓";font-weight:800;flex:0 0 auto;margin-top:1px}
.prod-actions{display:flex;flex-wrap:wrap;gap:10px}
.prod-target{font-size:13px;color:var(--muted);margin-top:10px}
/* Mockup */
.mockup{border-radius:14px;overflow:hidden;box-shadow:0 24px 64px rgba(15,23,42,.14);border:1px solid rgba(0,0,0,.06)}
.mockup-bar{padding:10px 14px;display:flex;align-items:center;gap:8px;color:#fff;font-size:13px;font-weight:700}
.mockup-bar .dots{display:flex;gap:5px}.mockup-bar .dot{width:10px;height:10px;border-radius:50%;background:rgba(255,255,255,.35)}
.mockup-body{background:#f6f8fc;padding:16px;display:grid;gap:10px}
.mockup-screen{border-radius:8px;background:#fff;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.06)}
.mockup-screen-head{padding:8px 12px;font-weight:700;font-size:11px;letter-spacing:.06em;text-transform:uppercase;color:#fff}
.mockup-screen-rows{padding:4px 0}
.mockup-row{display:flex;align-items:center;justify-content:space-between;padding:7px 12px;font-size:12px;border-bottom:1px solid #f0f4f8}
.mockup-row:last-child{border-bottom:0}.mockup-row .label{color:#475569;font-weight:600}.mockup-row .val{font-weight:700;color:#0f172a}
/* Divider & CTA section */
.cta-band{background:linear-gradient(135deg,var(--p),color-mix(in srgb,var(--p) 55%,var(--a)));color:#fff;padding:clamp(40px,6vw,80px);text-align:center;margin-block:var(--space)}
.cta-band h2{color:#fff;margin-bottom:12px}.cta-band p{opacity:.9;max-width:52ch;margin:0 auto 28px;font-size:1.05rem}
.cta-band .btn-w{box-shadow:0 8px 24px rgba(0,0,0,.18)}
/* Footer */
.foot{background:var(--ft-bg);color:var(--ft-fg);padding-block:60px 28px;font-size:15px}.foot a{color:var(--ft-h);text-decoration:none}.foot h3{color:var(--ft-h);font-size:.875rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;margin:0 0 16px}.foot-grid{display:grid;gap:32px 48px;grid-template-columns:minmax(200px,1.6fr) repeat(auto-fit,minmax(160px,1fr))}.foot ul{list-style:none;padding:0;margin:0;display:grid;gap:9px}.foot ul li a{display:flex;align-items:center;gap:6px;opacity:.9}.foot-bottom{border-top:1px solid var(--ft-line);margin-top:38px;padding-top:20px;display:flex;flex-wrap:wrap;gap:8px;justify-content:space-between;align-items:center;font-size:13px;opacity:.8}.foot-bottom-links{display:flex;gap:16px}
.social{display:flex;flex-wrap:wrap;gap:8px;margin-top:14px}.social a{display:grid;place-items:center;width:38px;height:38px;border-radius:10px;border:1px solid var(--ft-line);font-size:13px;font-weight:800;color:var(--ft-h);text-decoration:none!important;transition:background .15s}.social a:hover{background:color-mix(in srgb,var(--ft-h) 18%,transparent)}
.wa{position:fixed;right:18px;bottom:18px;z-index:50;display:flex;align-items:center;gap:8px;background:#25d366;color:#fff;border-radius:999px;padding:12px 18px;font-weight:700;text-decoration:none;box-shadow:0 12px 30px rgba(0,0,0,.2)}.wa svg{width:22px;height:22px}
@media(max-width:960px){.prod-row{grid-template-columns:1fr}.prod-row.reverse .prod-mockup{order:unset}.foot-grid{grid-template-columns:1fr 1fr}.foot-grid>div:first-child{grid-column:1/-1}}
@media(max-width:600px){.foot-grid{grid-template-columns:1fr}.wa span{display:none}.wa{padding:13px}}
@media(prefers-reduced-motion:reduce){*{transition:none!important;scroll-behavior:auto!important}}
</style>
</head>
<body>
<a class="skip" href="#main">Skip to content</a>
<?php if ($site['announcement'] !== '') { ?><div style="background:var(--a);color:#fff;text-align:center;font-weight:600;font-size:14px;padding:8px 16px"><?= esc($site['announcement']) ?></div><?php } ?>
<?php if ($d['topbar']) { ?><div class="topbar"><div class="wrap">
  <div><?php if ($site['email'] && $d['topbar_email']) { echo Site::email($site['email']); } ?><?php if ($site['phone']) { ?><a href="tel:<?= esc($tel) ?>"><?= esc($site['phone']) ?></a><?php } ?></div>
  <div><a href="<?= esc($user ? base_url(($user['role'] ?? '') === 'client' ? 'its-portal' : 'its-dashboard') : base_url('its-login')) ?>"><?= $user ? 'My workspace' : 'Client portal' ?></a></div>
</div></div><?php } ?>
<header class="nav"><div class="wrap">
  <a class="brand" href="<?= base_url() ?>" aria-label="<?= esc($site['name']) ?> home"><?php if ($logoUrl) { ?><img src="<?= esc($logoUrl) ?>" alt="<?= esc($site['name']) ?> logo" height="<?= (int) $d['logo_height'] ?>"><?php } else { ?><span class="brand-mark"><?= Site::icon('laptop') ?></span><span><?= esc($site['name']) ?><?php if ($site['tagline']) { ?><small><?= esc($site['tagline']) ?></small><?php } ?></span><?php } ?></a>
  <nav class="menu-wrap" id="menu" aria-label="Main"><ul class="menu"><?php foreach ($menu as $m) { $hasSub = !empty($m['children']); ?><li<?= $hasSub ? ' class="has-sub"' : '' ?>><a href="<?= esc(Site::url($m['slug'])) ?>"><?= esc($m['menu_label'] ?: $m['title']) ?></a><?php if ($hasSub) { ?><button class="sub-tog" type="button" aria-expanded="false" aria-label="Toggle submenu">▾</button><ul class="sub-menu"><?php foreach ($m['children'] as $c) { ?><li><a href="<?= esc(Site::url($c['slug'])) ?>"><?= esc($c['menu_label'] ?: $c['title']) ?></a></li><?php } ?></ul><?php } ?></li><?php } ?>
<li><a href="<?= base_url('products') ?>" aria-current="page">APP</a></li>
<li><a href="<?= base_url('extensions') ?>">Extensions</a></li>
</ul></nav>
  <div class="nav-actions">
    <a class="btn btn-o btn-sm" href="<?= esc($portalUrl) ?>"><?= $user ? 'Open workspace' : 'Login' ?></a>
    <?php if ($d['cta_label'] !== '') { ?><a class="btn btn-p btn-sm" href="<?= esc($ctaUrl) ?>"><?= esc($d['cta_label']) ?></a><?php } ?>
    <button class="nav-toggle" type="button" aria-controls="menu" aria-expanded="false" aria-label="Open menu"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button>
  </div>
</div></header>

<main id="main">

<!-- Hero -->
<section class="hero">
  <div class="wrap">
    <div class="eyebrow">Our Software</div>
    <h1>Built for Kenya, designed to last</h1>
    <p class="lead" style="max-width:60ch;margin:0 auto 28px">We build specialised management systems for churches, businesses, healthcare, HR and sports organisations. Each system is designed for the African context — affordable, reliable and locally supported.</p>
    <div style="display:flex;flex-wrap:wrap;gap:10px;justify-content:center">
      <a class="btn btn-p" href="<?= esc($ctaUrl) ?>">Get a quote</a>
      <a class="btn btn-o" href="#systems">Explore systems</a>
    </div>
  </div>
</section>

<!-- Systems -->
<section id="systems" style="padding-bottom:var(--space)"><div class="wrap">
<div class="prod-grid">
<?php foreach ($products as $i => $prod) {
  $isEven = $i % 2 === 0;
  $screens = $prod['demo_screens'];
?>
<article id="<?= esc($prod['slug']) ?>" class="prod-row<?= !$isEven ? ' reverse' : '' ?>" aria-label="<?= esc($prod['name']) ?>">

  <!-- Text -->
  <div>
    <div class="prod-badge" style="background:<?= esc($prod['color']) ?>18;color:<?= esc($prod['color']) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="width:15px;height:15px"><circle cx="12" cy="12" r="10"/></svg>
      <?= esc($prod['name']) ?>
    </div>
    <h2 style="color:<?= esc($prod['color']) ?>"><?= esc($prod['tagline']) ?></h2>
    <p style="font-size:1.05rem;color:var(--muted)"><?= esc($prod['desc']) ?></p>
    <ul class="prod-features">
      <?php foreach ($prod['features'] as $f) { ?><li style="color:var(--muted)"><span style="color:<?= esc($prod['color']) ?>;font-weight:800">✓</span><?= esc($f) ?></li><?php } ?>
    </ul>
    <div class="prod-actions">
      <a class="btn btn-p btn-sm" href="<?= esc($ctaUrl) ?>?product=<?= esc($prod['slug']) ?>" style="background:<?= esc($prod['color']) ?>;box-shadow:0 8px 20px <?= esc($prod['color']) ?>40">Get a Quote</a>
      <a class="btn btn-o btn-sm" href="<?= base_url('contact') ?>" style="color:<?= esc($prod['color']) ?>;border-color:<?= esc($prod['color']) ?>40">Request Demo</a>
    </div>
    <p class="prod-target"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="width:14px;height:14px;vertical-align:-.2em"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg> For: <?= esc($prod['target']) ?></p>
  </div>

  <!-- Mockup -->
  <div class="prod-mockup">
    <div class="mockup" role="img" aria-label="<?= esc($prod['name']) ?> interface preview">
      <div class="mockup-bar" style="background:<?= esc($prod['color']) ?>">
        <div class="dots"><span class="dot"></span><span class="dot"></span><span class="dot"></span></div>
        <span style="margin-left:8px;opacity:.9"><?= esc($prod['name']) ?></span>
      </div>
      <div class="mockup-body" style="background:color-mix(in srgb,<?= esc($prod['color']) ?> 4%,#f6f8fc)">
        <?php foreach ($screens as $screen) { ?>
        <div class="mockup-screen">
          <div class="mockup-screen-head" style="background:<?= esc($prod['accent']) ?>"><?= esc($screen['label']) ?></div>
          <div class="mockup-screen-rows">
            <?php
            $vals = [
              ['Today', '0'], ['Total', '0'], ['Active', '0'], ['Pending', '—'],
              ['Revenue', '—'], ['Count', '0'], ['Last 30 days', '—'],
            ];
            foreach ($screen['items'] as $j => $item) {
              $v = $vals[$j] ?? ['—', '—'];
            ?>
            <div class="mockup-row">
              <span class="label"><?= esc($item) ?></span>
              <span class="val" style="color:<?= esc($prod['color']) ?>"><?= $v[1] ?></span>
            </div>
            <?php } ?>
          </div>
        </div>
        <?php } ?>
        <!-- Stats bar -->
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-top:4px">
          <?php $statLabels = ['Users', 'Reports', 'Records']; foreach ($statLabels as $sl) { ?>
          <div style="background:#fff;border-radius:8px;padding:10px;text-align:center;box-shadow:0 1px 4px rgba(0,0,0,.06)">
            <div style="font-size:18px;font-weight:800;color:<?= esc($prod['color']) ?>">—</div>
            <div style="font-size:10px;color:#8290a3;margin-top:2px"><?= esc($sl) ?></div>
          </div>
          <?php } ?>
        </div>
      </div>
    </div>
  </div>

</article>
<?php if ($i < count($products) - 1) { ?><hr style="border:0;border-top:1px solid var(--line)"><?php } ?>
<?php } ?>
</div>
</div></section>

<!-- CTA Band -->
<section>
  <div class="wrap">
    <div class="cta-band" style="border-radius:calc(var(--r) + 8px)">
      <h2>Ready to get started?</h2>
      <p>All our systems are priced to suit your organisation size. Get in touch for a custom quote, a live demo, or to find out which system fits your needs.</p>
      <div style="display:flex;flex-wrap:wrap;gap:12px;justify-content:center">
        <a class="btn btn-w" href="<?= esc($ctaUrl) ?>">Get a Quote</a>
        <a class="btn" style="border:2px solid rgba(255,255,255,.6);color:#fff;background:rgba(255,255,255,.12)" href="<?= base_url('extensions') ?>">Extension Marketplace</a>
      </div>
    </div>
  </div>
</section>

</main>

<footer class="foot"><div class="wrap">
  <div class="foot-grid">
    <div>
      <?php if ($logoUrl) { ?><img src="<?= esc($logoUrl) ?>" alt="<?= esc($site['name']) ?>" style="max-height:44px;max-width:180px;object-fit:contain;margin-bottom:14px;display:block"><?php } else { ?><h3 style="font-size:1.1rem;letter-spacing:normal;text-transform:none;margin-bottom:10px"><?= esc($site['name']) ?></h3><?php } ?>
      <p style="opacity:.82;font-size:14px;margin:0 0 14px;max-width:320px"><?= esc($d['footer_about'] ?: ($site['tagline'] ?: 'Specialised software for churches, businesses and healthcare.')) ?></p>
      <h3 style="margin-top:8px">Our Systems</h3>
      <ul><?php foreach ($products as $prod) { ?><li><a href="#<?= esc($prod['slug']) ?>"><?= esc($prod['name']) ?> — <?= esc($prod['tagline']) ?></a></li><?php } ?></ul>
      <?php if ($socials) { ?><div class="social"><?php foreach ($socials as $k => $u) { ?><a href="<?= esc($u) ?>" target="_blank" rel="noopener" aria-label="<?= esc(ucfirst($k)) ?>"><?= esc($socialMark[$k] ?? strtoupper($k[0])) ?></a><?php } ?></div><?php } ?>
    </div>
    <?php foreach ($d['footer_order'] as $fsec) { switch ($fsec) {
      case 'contact': if (!$d['footer_contact']) break; ?><div><h3>Contact us</h3><?php if ($site['email'] || $site['phone'] || $site['address']) { ?><ul style="list-style:none;padding:0;margin:0 0 14px;display:grid;gap:10px;font-size:14px"><?php if ($site['email']) { ?><li style="display:flex;gap:8px;align-items:center"><?= Site::icon('mail') ?><?= Site::email($site['email']) ?></li><?php } ?><?php if ($site['phone']) { ?><li style="display:flex;gap:8px;align-items:center"><?= Site::icon('phone') ?><a href="tel:<?= esc($tel) ?>"><?= esc($site['phone']) ?></a></li><?php } ?><?php if ($site['address']) { ?><li style="display:flex;gap:8px"><?= Site::icon('map') ?><span style="opacity:.85"><?= nl2br(esc($site['address'])) ?></span></li><?php } ?></ul><?php } ?></div><?php break;
      case 'menu': if (!$d['footer_menu']) break; ?><div><h3>Quick links</h3><ul><?php foreach ($menu as $m) { ?><li><a href="<?= esc(Site::url($m['slug'])) ?>"><?= esc($m['menu_label'] ?: $m['title']) ?></a></li><?php } ?></ul></div><?php break;
    } } ?>
  </div>
  <div class="foot-bottom">
    <span>© <?= date('Y') ?> <?= esc($site['name']) ?>.<?= $site['footer_note'] ? ' ' . esc($site['footer_note']) : ' All rights reserved.' ?></span>
    <span class="foot-bottom-links"><a href="<?= base_url('products') ?>">APP</a><a href="<?= base_url('extensions') ?>">Extensions</a><a href="<?= esc($portalUrl) ?>"><?= $user ? 'Workspace' : 'Staff login' ?></a></span>
  </div>
</div></footer>
<?php if ($wa !== '') { ?><a class="wa" href="<?= esc($waLink('Hello, I\'d like a demo of your software.')) ?>" target="_blank" rel="noopener" aria-label="Chat on WhatsApp"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.2-.4.7-1.3.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.7 11.8 11.8 0 0 0 4.5 4c1.7.7 2.3.8 3.2.6.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.2-1.2-.1-.1-.3-.2-.5-.3z"/></svg><span>WhatsApp</span></a><?php } ?>
<script>
const t=document.querySelector('.nav-toggle'),m=document.getElementById('menu');
t&&t.addEventListener('click',()=>{const o=m.classList.toggle('open');t.setAttribute('aria-expanded',String(o));});
</script>
</body>
</html>
