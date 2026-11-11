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
$clientUrl = $user ? $portalUrl : base_url('its-login?for=client');
$ctaUrl = Site::link($d['cta_url'] ?: 'contact');
$metaTitle = 'Extension Marketplace | ' . $site['name'];
$metaDesc  = 'Download plugins, themes and add-ons for Churchz, UMI and other platforms.';
$canonical = base_url('extensions');
$org = ['@context' => 'https://schema.org', '@type' => 'LocalBusiness', 'name' => $site['name'], 'url' => base_url()];
if ($logoUrl) $org['logo'] = $logoUrl;
$jsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP;

$platforms = ['churchz' => 'Churchz', 'umi' => 'UMI', 'hr' => 'HR Suite', 'hmisoz' => 'HMISOZ', 'jasemlz' => 'JasemlZ', 'kksports' => 'KKSports', 'other' => 'Other'];
$typeColors = ['plugin' => '#0b4ea2', 'theme' => '#7c3aed', 'addon' => '#059669', 'integration' => '#d97706'];
$typeIcons  = ['plugin' => 'puzzle-piece', 'theme' => 'palette', 'addon' => 'plus-circle', 'integration' => 'link-45deg'];
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= esc($metaTitle) ?></title>
<meta name="description" content="<?= esc($metaDesc) ?>">
<meta name="robots" content="index,follow">
<link rel="canonical" href="<?= esc($canonical) ?>">
<meta property="og:type" content="website"><meta property="og:site_name" content="<?= esc($site['name']) ?>"><meta property="og:title" content="<?= esc($metaTitle) ?>"><meta property="og:description" content="<?= esc($metaDesc) ?>"><meta property="og:url" content="<?= esc($canonical) ?>">
<meta name="theme-color" content="<?= esc($p) ?>">
<?php if ($logoUrl) { ?><link rel="icon" href="<?= esc($logoUrl) ?>"><?php } else { ?><link rel="icon" href="<?= base_url('icon.svg') ?>" type="image/svg+xml"><?php } ?>
<?php if (isset(Site::FONTS[$d['font']])) { ?><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=<?= Site::FONTS[$d['font']] ?>:wght@400;500;600;700;800&display=swap"><?php } ?>
<script type="application/ld+json"><?= json_encode($org, $jsonFlags) ?></script>
<?php if (preg_match('/^G-[A-Z0-9]{4,15}$/', $site['ga_id'])) { ?><script async src="https://www.googletagmanager.com/gtag/js?id=<?= esc($site['ga_id']) ?>"></script><script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config','<?= esc($site['ga_id']) ?>');</script><?php } ?>
<style>
:root{--p:<?= esc($p) ?>;--a:<?= esc($a) ?>;--ink:#0f172a;--muted:#55627a;--line:#e3e8f0;--bg:#f6f8fc;--card:#fff;--r:<?= (int) $d['radius'] ?>px;--btn-r:<?= $btnRadius ?>;--space:<?= $space ?>;--hdr-bg:<?= $hdr[0] ?>;--hdr-fg:<?= $hdr[1] ?>;--hdr-line:<?= $hdr[2] ?>;--ft-bg:<?= $ftr[0] ?>;--ft-fg:<?= $ftr[1] ?>;--ft-h:<?= $ftr[2] ?>;--ft-line:<?= $ftr[3] ?>}
*{box-sizing:border-box}html,body{max-width:100%;overflow-x:clip}html{scroll-behavior:smooth;scroll-padding-top:90px;font-size:<?= (int) $d['font_size'] ?>px}body{margin:0;font-family:<?= $fontStack ?>;color:var(--ink);background:#fff;line-height:1.6;-webkit-font-smoothing:antialiased}
a{color:var(--p)}img{max-width:100%;height:auto}h1,h2,h3{line-height:1.2;text-wrap:balance;margin:0 0 .5em}h1{font-size:clamp(2rem,1.4rem + 2.6vw,3.3rem);letter-spacing:-.02em}h2{font-size:clamp(1.5rem,1.2rem + 1.2vw,2.2rem);letter-spacing:-.01em}h3{font-size:1.15rem}p{margin:0 0 1em;text-wrap:pretty}
.wrap{width:min(1180px,100% - 40px);margin-inline:auto}
.skip{position:absolute;left:-999px}.skip:focus{left:12px;top:12px;z-index:99;background:#fff;padding:8px 12px;border-radius:8px}
.topbar{background:var(--ink);color:#cbd5e1;font-size:13px}.topbar .wrap{display:flex;flex-wrap:wrap;gap:6px 22px;justify-content:space-between;padding-block:7px}.topbar a{color:#e2e8f0;text-decoration:none}.topbar span,.topbar a{display:inline-flex;align-items:center;gap:6px}.topbar .ic{width:15px;height:15px}
.nav{<?= $d['header_sticky'] ? 'position:sticky;top:0;' : 'position:relative;' ?>z-index:40;background:var(--hdr-bg);backdrop-filter:saturate(1.4) blur(12px);border-bottom:1px solid var(--hdr-line);color:var(--hdr-fg)}
.nav .wrap{display:flex;align-items:center;gap:14px;min-height:72px}.nav .wrap>*{min-width:0}
.brand{display:flex;align-items:center;gap:10px;text-decoration:none;color:var(--hdr-fg);font-weight:800;font-size:1.05rem;<?= $d['menu_align'] === 'center' ? '' : 'margin-inline-end:auto' ?>}.brand img{max-height:<?= (int) $d['logo_height'] ?>px;max-width:220px;object-fit:contain}.brand-mark{display:grid;place-items:center;width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,var(--p),var(--a));color:#fff}.brand-mark .ic{width:22px;height:22px}.brand small{display:block;font-weight:500;opacity:.75;font-size:12px}
<?= $d['menu_align'] === 'center' ? '.menu-wrap{margin-inline:auto}' : '' ?>
.menu{display:flex;align-items:center;gap:2px;list-style:none;margin:0;padding:0}.menu a{display:block;white-space:nowrap;padding:8px 13px;border-radius:10px;text-decoration:none;color:var(--hdr-fg);font-weight:600;font-size:15px;position:relative;transition:background .15s,color .15s}.menu a:after{content:"";position:absolute;bottom:2px;left:13px;right:13px;height:2px;border-radius:2px;background:var(--p);transform:scaleX(0);transition:transform .2s}.menu a:hover:after{transform:scaleX(1)}.menu a:hover{background:color-mix(in srgb,currentColor 8%,transparent)}<?= $d['header_style'] === 'light' ? '.menu a:hover{color:var(--p);background:color-mix(in srgb,var(--p) 8%,#fff)}' : '' ?>
.btn{white-space:nowrap;display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:12px 20px;border-radius:var(--btn-r);font-weight:700;text-decoration:none;border:2px solid transparent;cursor:pointer;font-size:15px;font-family:inherit;transition:transform .15s,box-shadow .15s}.btn:hover{transform:translateY(-1px)}.btn .ic{width:18px;height:18px}
.btn-p{background:var(--p);color:#fff;box-shadow:0 8px 20px color-mix(in srgb,var(--p) 30%,transparent)}.btn-a{background:var(--a);color:#fff}.btn-o{border-color:currentColor;color:var(--p);background:transparent}.btn-w{background:#fff;color:var(--p)}.btn-sm{padding:8px 14px;font-size:14px}
.nav .btn-o{color:var(--hdr-fg)}<?= $d['header_style'] === 'brand' ? '.nav .btn-p{background:#fff;color:var(--p)}' : '' ?>
.has-sub{position:relative}.sub-menu{display:none;position:absolute;top:calc(100% + 4px);left:0;list-style:none;margin:0;padding:6px;min-width:200px;background:var(--card);border:1px solid var(--line);border-radius:var(--r);box-shadow:0 8px 30px rgba(15,23,42,.12);z-index:50}.has-sub:hover .sub-menu,.has-sub:focus-within .sub-menu{display:block}.sub-menu a{display:flex;padding:9px 13px;border-radius:8px;color:var(--ink)!important;font-size:14.5px;font-weight:600;white-space:nowrap;text-decoration:none;background:none}.sub-menu a:after{display:none!important}.sub-menu a:hover{background:color-mix(in srgb,var(--p) 8%,#fff);color:var(--p)!important}.has-sub>a{padding-right:6px}.sub-tog{display:none;background:none;border:none;color:var(--hdr-fg);cursor:pointer;padding:0 5px;font-size:12px;line-height:1}
@media(max-width:1199px){.sub-tog{display:inline-block;margin-left:4px}.nav-toggle{display:inline-flex}.brand{margin-inline-end:auto}.menu-wrap{position:absolute;inset-inline:0;top:100%;background:#fff;border-bottom:1px solid var(--line);box-shadow:0 18px 40px rgba(15,23,42,.1);opacity:0;pointer-events:none;transform:translateY(-8px);transition:opacity .2s,transform .2s;padding:10px 20px 18px;margin:0}.menu-wrap.open{opacity:1;pointer-events:auto;transform:none}.menu{flex-direction:column;align-items:stretch}.menu a{color:var(--ink);padding:11px 12px;border-radius:8px;font-size:16px}.menu a:after{display:none}.nav-actions .btn-o{display:none}}
.nav-actions{display:flex;gap:8px;align-items:center}.nav-toggle{display:none;background:none;border:1px solid var(--hdr-line);color:var(--hdr-fg);border-radius:10px;padding:8px 10px;cursor:pointer}
/* Extensions page layout */
.ext-hero{background:radial-gradient(1200px 500px at 85% -10%,color-mix(in srgb,var(--a) 16%,transparent),transparent),linear-gradient(180deg,color-mix(in srgb,var(--p) 7%,#fff),#fff);padding-block:clamp(48px,6vw,80px)}
.eyebrow{display:inline-flex;align-items:center;gap:8px;color:var(--p);font-weight:700;font-size:13px;letter-spacing:.08em;text-transform:uppercase;margin-bottom:12px}.eyebrow:before{content:"";width:22px;height:2px;background:var(--a)}
.lead{font-size:clamp(1.05rem,1rem + .35vw,1.25rem);color:var(--muted)}
.plat-tabs{display:flex;gap:4px;flex-wrap:wrap;margin-bottom:28px}
.plat-tab{background:none;border:2px solid var(--line);border-radius:var(--btn-r);padding:8px 18px;font:600 15px/1 inherit;color:var(--muted);cursor:pointer;transition:all .15s}.plat-tab.active,.plat-tab:hover{border-color:var(--p);color:var(--p);background:color-mix(in srgb,var(--p) 6%,#fff)}.plat-tab.active{background:var(--p);color:#fff;border-color:var(--p)}.plat-tab .cnt{display:inline-block;margin-left:6px;background:rgba(0,0,0,.12);border-radius:99px;padding:1px 7px;font-size:12px}
.plat-section{display:none}.plat-section.active{display:block}
.ext-grid{display:grid;gap:20px;grid-template-columns:repeat(auto-fill,minmax(min(100%,300px),1fr));margin-top:0}
.ext-card{background:var(--card);border:1px solid var(--line);border-radius:var(--r);overflow:hidden;display:flex;flex-direction:column;transition:transform .2s,box-shadow .2s}.ext-card:hover{transform:translateY(-3px);box-shadow:0 18px 40px rgba(15,23,42,.09)}
.ext-screenshot{width:100%;aspect-ratio:16/7;overflow:hidden;background:var(--bg)}.ext-screenshot img{width:100%;height:100%;object-fit:cover;display:block;user-select:none;-webkit-user-select:none;-webkit-touch-callout:none}
.ext-card-body{padding:18px;display:flex;flex-direction:column;gap:10px;flex:1}
.ext-icon{width:46px;height:46px;border-radius:12px;display:grid;place-items:center;flex:0 0 auto;font-size:20px}
.ext-header{display:flex;gap:12px;align-items:flex-start}
.ext-meta{flex:1;min-width:0}.ext-meta h3{margin:0 0 2px;font-size:1rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.ext-meta .ver{font-size:12px;color:var(--muted)}
.badge{display:inline-block;border-radius:99px;padding:2px 9px;font-size:11px;font-weight:700;letter-spacing:.03em}
.ext-desc{font-size:14px;color:var(--muted);flex:1;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}
.ext-footer{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-top:auto;padding-top:6px;border-top:1px solid var(--line);font-size:13px;color:var(--muted)}
.ext-footer .dl{display:flex;align-items:center;gap:4px}
.ext-footer .dl svg{width:14px;height:14px}
.empty-plat{padding:48px 0;text-align:center;color:var(--muted)}.empty-plat p{margin:8px 0 0;font-size:15px}
.submit-banner{background:linear-gradient(135deg,var(--p),color-mix(in srgb,var(--p) 55%,var(--a)));color:#fff;border-radius:calc(var(--r) + 8px);padding:clamp(24px,4vw,44px);display:flex;flex-wrap:wrap;gap:20px;align-items:center;justify-content:space-between;margin-top:var(--space)}
.submit-banner h2{color:#fff;margin:0 0 6px}.submit-banner p{margin:0;opacity:.9;font-size:15px;max-width:56ch}
/* Access section */
.access-section{margin-top:var(--space);scroll-margin-top:90px}
.access-alert{padding:14px 18px;border-radius:12px;margin-bottom:20px;font-weight:600;font-size:14px;display:flex;align-items:center;gap:10px}
.access-alert.required{background:#fff7ed;border:1px solid #fed7aa;color:#9a3412}
.access-alert.denied{background:#fef2f2;border:1px solid #fecaca;color:#991b1b}
.access-alert.ok{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534}
.access-grid{display:grid;gap:24px;grid-template-columns:repeat(auto-fit,minmax(min(100%,320px),1fr))}
.access-panel{background:var(--card);border:1px solid var(--line);border-radius:var(--r);padding:28px}
.access-panel h3{margin:0 0 6px;font-size:1.05rem}.access-panel p.sub{color:var(--muted);font-size:14px;margin:0 0 18px}
.key-input-wrap{display:flex;gap:8px;margin-bottom:10px}.key-input-wrap input{flex:1;padding:10px 13px;border:1px solid var(--line);border-radius:10px;font:inherit;font-size:14px}.key-input-wrap button{white-space:nowrap}
.form-field{margin-bottom:14px}.form-field label{display:block;font-weight:600;font-size:13px;color:#34445b;margin-bottom:5px}.form-field input,.form-field select,.form-field textarea{width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:10px;font:inherit;font-size:14px}.form-field textarea{min-height:72px;resize:vertical}
/* Pricing */
.pricing-grid{display:grid;gap:24px;grid-template-columns:repeat(auto-fit,minmax(min(100%,260px),1fr));align-items:start}
.pricing-card{background:var(--card);border:1px solid var(--line);border-radius:calc(var(--r) + 4px);padding:32px;position:relative;display:flex;flex-direction:column;gap:0}
.pricing-card.pricing-featured{border-color:var(--p);box-shadow:0 20px 60px color-mix(in srgb,var(--p) 16%,transparent)}
.pricing-pill{display:inline-block;background:var(--p);color:#fff;font-size:11px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;padding:4px 12px;border-radius:99px;margin-bottom:14px;align-self:flex-start}
.pricing-plan{font-size:1.15rem;font-weight:800;margin:0 0 8px}
.pricing-price{font-size:2.4rem;font-weight:900;letter-spacing:-.04em;line-height:1;margin:0}.pricing-price span{font-size:1rem;font-weight:600;color:var(--muted);letter-spacing:0}
.pricing-annual{color:var(--muted);font-size:13px;margin:6px 0 22px}
.pricing-features{list-style:none;margin:0 0 26px;padding:0;display:grid;gap:9px;font-size:14px}.pricing-features li{display:flex;align-items:flex-start;gap:8px}.pricing-features li:before{content:"✓";color:var(--p);font-weight:700;flex:0 0 auto}
.badge-free{background:#d1fae5;color:#065f46}.badge-paid{background:#fef3c7;color:#92400e}
.search-wrap{position:relative;max-width:420px;margin-bottom:22px}.search-wrap input{width:100%;padding:10px 14px 10px 40px;border:1px solid var(--line);border-radius:12px;font:inherit;font-size:15px;background:#fff}.search-wrap input:focus{outline:2px solid color-mix(in srgb,var(--p) 40%,transparent);border-color:var(--p)}.search-wrap svg{position:absolute;left:13px;top:50%;transform:translateY(-50%);width:17px;height:17px;color:var(--muted);pointer-events:none}
.foot{background:var(--ft-bg);color:var(--ft-fg);padding-block:60px 28px;font-size:15px}.foot a{color:var(--ft-h);text-decoration:none}.foot a:hover{opacity:.8;text-decoration:underline}.foot h3{color:var(--ft-h);font-size:.875rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;margin:0 0 16px}.foot-grid{display:grid;gap:32px 48px;grid-template-columns:minmax(200px,1.6fr) repeat(auto-fit,minmax(160px,1fr))}.foot ul{list-style:none;padding:0;margin:0;display:grid;gap:9px}.foot ul li a{display:flex;align-items:center;gap:6px;opacity:.9}.foot ul li a:hover{opacity:1}.foot .ic{color:var(--ft-h)}.foot-cta{margin-top:18px;display:flex;flex-wrap:wrap;gap:10px}.foot-cta .btn{font-size:14px;padding:9px 18px}.foot-bottom{border-top:1px solid var(--ft-line);margin-top:38px;padding-top:20px;display:flex;flex-wrap:wrap;gap:8px;justify-content:space-between;align-items:center;font-size:13px;opacity:.8}.foot-bottom-links{display:flex;gap:16px}.foot-bottom-links a{opacity:.9}
.social{display:flex;flex-wrap:wrap;gap:8px;margin-top:14px}.social a{display:grid;place-items:center;width:38px;height:38px;border-radius:10px;border:1px solid var(--ft-line);background:color-mix(in srgb,var(--ft-h) 6%,transparent);transition:background .15s;font-size:13px;font-weight:800;color:var(--ft-h);text-decoration:none!important}.social a:hover{background:color-mix(in srgb,var(--ft-h) 18%,transparent);opacity:1}
.wa{position:fixed;right:18px;bottom:18px;z-index:50;display:flex;align-items:center;gap:8px;background:#25d366;color:#fff;border-radius:999px;padding:12px 18px;font-weight:700;text-decoration:none;box-shadow:0 12px 30px rgba(0,0,0,.2)}.wa svg{width:22px;height:22px}
@media(max-width:960px){.foot-grid{grid-template-columns:1fr 1fr}.foot-grid>div:first-child{grid-column:1/-1}}
@media(max-width:600px){.foot-grid{grid-template-columns:1fr}.brand span{font-size:.92rem}.brand small{display:none}.nav-actions .btn-p{padding:8px 12px;font-size:13px}.wa span{display:none}.wa{padding:13px}}
@media print{.nav,.topbar,.wa,.foot{display:none}}
@media(prefers-reduced-motion:reduce){*{transition:none!important;scroll-behavior:auto!important}}
<?= str_ireplace('</style', '', (string) $d['custom_css']) ?>
</style>
</head>
<body>
<a class="skip" href="#main">Skip to content</a>
<?php if ($site['announcement'] !== '') { ?><div style="background:var(--a);color:#fff;text-align:center;font-weight:600;font-size:14px;padding:8px 16px"><?= esc($site['announcement']) ?></div><?php } ?>
<?php if ($d['topbar']) { ?><div class="topbar"><div class="wrap">
  <div style="display:flex;flex-wrap:wrap;gap:6px 22px"><?php if ($site['email'] && $d['topbar_email']) { echo Site::email($site['email'], '', '', Site::icon('mail')); } ?><?php if ($site['phone']) { ?><a href="tel:<?= esc($tel) ?>"><?= Site::icon('phone') ?><?= esc($site['phone']) ?></a><?php } ?></div>
  <div style="display:flex;flex-wrap:wrap;gap:6px 22px"><?php if ($socials && $d['topbar_social']) { ?><span><?php foreach ($socials as $k => $u) { ?><a href="<?= esc($u) ?>" target="_blank" rel="noopener" aria-label="<?= esc(ucfirst($k)) ?>"><?= esc($socialMark[$k]) ?></a><?php } ?></span><?php } ?><a href="<?= esc($clientUrl) ?>"><?= Site::icon('users') ?><?= $user ? 'My workspace' : 'Client portal' ?></a></div>
</div></div><?php } ?>
<header class="nav"><div class="wrap">
  <a class="brand" href="<?= base_url() ?>" aria-label="<?= esc($site['name']) ?> home"><?php if ($logoUrl) { ?><img src="<?= esc($logoUrl) ?>" alt="<?= esc($site['name']) ?> logo" height="<?= (int) $d['logo_height'] ?>"><?php } else { ?><span class="brand-mark"><?= Site::icon('laptop') ?></span><span><?= esc($site['name']) ?><?php if ($site['tagline']) { ?><small><?= esc($site['tagline']) ?></small><?php } ?></span><?php } ?></a>
  <nav class="menu-wrap" id="menu" aria-label="Main"><ul class="menu"><?php foreach ($menu as $m) { $hasSub = !empty($m['children']); ?><li<?= $hasSub ? ' class="has-sub"' : '' ?>><a href="<?= esc(Site::url($m['slug'])) ?>"><?= esc($m['menu_label'] ?: $m['title']) ?></a><?php if ($hasSub) { ?><button class="sub-tog" type="button" aria-expanded="false" aria-label="Toggle submenu">▾</button><ul class="sub-menu"><?php foreach ($m['children'] as $c) { ?><li><a href="<?= esc(Site::url($c['slug'])) ?>"><?= esc($c['menu_label'] ?: $c['title']) ?></a></li><?php } ?></ul><?php } ?></li><?php } ?>
<li><a href="<?= base_url('products') ?>">APP</a></li>
<li><a href="<?= base_url('extensions') ?>" aria-current="page">Extensions</a></li>
</ul></nav>
  <div class="nav-actions"><a class="btn btn-o btn-sm" href="<?= esc($portalUrl) ?>"><?= $user ? 'Open workspace' : 'Login' ?></a><?php if ($d['cta_label'] !== '') { ?><a class="btn btn-p btn-sm" href="<?= esc($ctaUrl) ?>"><?= esc($d['cta_label']) ?></a><?php } ?>
  <button class="nav-toggle" type="button" aria-controls="menu" aria-expanded="false" aria-label="Open menu"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button></div>
</div></header>

<main id="main">
<!-- Hero -->
<section class="ext-hero">
  <div class="wrap">
    <div class="eyebrow">Extension Marketplace</div>
    <h1>Plugins, Themes &amp; Add-ons</h1>
    <p class="lead" style="max-width:60ch;margin-bottom:0">Download free extensions for <strong>Churchz</strong>, <strong>UMI</strong> and other platforms. <?= $total > 0 ? esc((string) $total) . ' extension' . ($total !== 1 ? 's' : '') . ' available.' : 'Be the first to upload one.' ?></p>
  </div>
</section>

<!-- Pricing Plans -->
<section style="padding-block:var(--space);background:var(--bg)" id="pricing">
<div class="wrap">
  <div style="text-align:center;margin-bottom:40px">
    <div class="eyebrow">Pricing Plans</div>
    <h2 style="margin-bottom:8px">Choose Your Plan</h2>
    <p style="color:var(--muted);max-width:52ch;margin-inline:auto">One-time installation fee plus an annual support and maintenance plan. Free plugins are available on any plan.</p>
  </div>
  <div class="pricing-grid">

    <!-- Starter -->
    <div class="pricing-card">
      <p class="pricing-plan">Starter</p>
      <p class="pricing-price">$600<span> install</span></p>
      <p class="pricing-annual">+ $150 / year</p>
      <ul class="pricing-features">
        <li>One APP of your choice</li>
        <li>All free plugins included</li>
        <li>Email support</li>
        <li>Annual maintenance</li>
      </ul>
      <a class="btn btn-o" href="#access-section" style="width:100%;justify-content:center">Get Started</a>
    </div>

    <!-- Professional -->
    <div class="pricing-card pricing-featured">
      <span class="pricing-pill">Most Popular</span>
      <p class="pricing-plan">Professional</p>
      <p class="pricing-price">$1,000<span> install</span></p>
      <p class="pricing-annual">+ $300 / year</p>
      <ul class="pricing-features">
        <li>One APP of your choice</li>
        <li>Two paid plugins of choice</li>
        <li>All free plugins included</li>
        <li>Priority email &amp; phone support</li>
        <li>Annual maintenance</li>
      </ul>
      <a class="btn btn-p" href="#access-section" style="width:100%;justify-content:center">Get Started</a>
    </div>

    <!-- Enterprise -->
    <div class="pricing-card">
      <p class="pricing-plan">Enterprise</p>
      <p class="pricing-price" style="font-size:1.9rem">Custom<span> quote</span></p>
      <p class="pricing-annual">Annual plan on request</p>
      <ul class="pricing-features">
        <li>Full APP suite</li>
        <li>All free plugins included</li>
        <li>Custom integrations &amp; modules</li>
        <li>Dedicated support</li>
        <li>SLA &amp; on-site option</li>
      </ul>
      <a class="btn btn-o" href="<?= esc($ctaUrl) ?>" style="width:100%;justify-content:center">Get a Quote</a>
    </div>

  </div>
  <p style="text-align:center;color:var(--muted);font-size:13px;margin-top:22px">All prices in USD &middot; Plugins marked <strong>Free</strong> are included on all plans &middot; <strong>Paid</strong> plugins require Professional or above</p>
</div>
</section>

<!-- Marketplace -->
<section style="padding-block:var(--space)">
  <div class="wrap">

    <!-- Search -->
    <div class="search-wrap">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
      <input type="search" id="ext-search" placeholder="Search extensions…" aria-label="Search extensions">
    </div>

    <!-- Platform tabs -->
    <div class="plat-tabs" role="tablist" aria-label="Platform">
      <button class="plat-tab active" role="tab" aria-selected="true"  aria-controls="plat-all"     data-plat="">All <span class="cnt"><?= $total ?></span></button>
      <?php foreach ($platforms as $pKey => $pLabel) { $cnt = count($extensions[$pKey] ?? []); ?>
      <button class="plat-tab" role="tab" aria-selected="false" aria-controls="plat-<?= esc($pKey) ?>" data-plat="<?= esc($pKey) ?>"><?= esc($pLabel) ?> <span class="cnt"><?= $cnt ?></span></button>
      <?php } ?>
    </div>

    <!-- All extensions (default view) -->
    <div id="plat-all" class="plat-section active" role="tabpanel">
      <?php if ($total === 0) { ?>
        <div class="empty-plat">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:48px;height:48px;color:var(--muted)"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" stroke-linecap="round" stroke-linejoin="round"/></svg>
          <p>No extensions published yet. <a href="<?= base_url('its-login') ?>">Log in</a> to upload the first one.</p>
        </div>
      <?php } else { ?>
        <div class="ext-grid" id="ext-all-grid">
          <?php foreach ($extensions as $platExts) foreach ($platExts as $ext) { ?>
          <?php $tc = $typeColors[$ext['type']] ?? '#0b4ea2'; $ti = $typeIcons[$ext['type']] ?? 'box'; ?>
          <div class="ext-card" data-name="<?= esc(strtolower($ext['name'])) ?>" data-platform="<?= esc($ext['platform']) ?>">
            <?php if (!empty($ext['screenshot'])) { ?>
            <div class="ext-screenshot"><img src="<?= base_url('uploads/extensions/' . esc($ext['screenshot'])) ?>" alt="<?= esc($ext['name']) ?>" loading="lazy"></div>
            <?php } ?>
            <div class="ext-card-body">
              <div class="ext-header">
                <div class="ext-icon" style="background:<?= esc($tc) ?>18;color:<?= esc($tc) ?>">
                  <?= Site::icon($ti) ?>
                </div>
                <div class="ext-meta">
                  <h3 title="<?= esc($ext['name']) ?>"><?= esc($ext['name']) ?></h3>
                  <span class="ver">v<?= esc($ext['version']) ?></span>
                </div>
              </div>
              <div style="display:flex;flex-wrap:wrap;gap:5px">
                <span class="badge" style="background:<?= esc($tc) ?>18;color:<?= esc($tc) ?>"><?= esc(ucfirst($ext['type'])) ?></span>
                <span class="badge" style="background:color-mix(in srgb,var(--p) 10%,#fff);color:var(--p)"><?= esc($platforms[$ext['platform']] ?? ucfirst($ext['platform'])) ?></span>
                <?php $pt = $ext['price_type'] ?? 'free'; ?><span class="badge badge-<?= $pt ?>"><?= $pt === 'paid' ? 'Paid' : 'Free' ?></span>
                <?php if (!empty($ext['license'])) { ?><span class="badge" style="background:#f1f5f9;color:#475569"><?= esc($ext['license']) ?></span><?php } ?>
              </div>
              <?php if ($ext['description']) { ?>
              <p class="ext-desc"><?= esc($ext['description']) ?></p>
              <?php } ?>
              <?php if (!empty($ext['tags'])) { $tagList = array_slice(array_filter(array_map('trim', explode(',', $ext['tags']))), 0, 4); if ($tagList) { ?>
              <div style="display:flex;flex-wrap:wrap;gap:4px">
                <?php foreach ($tagList as $tag) { ?><span style="background:#f1f5f9;color:#475569;border-radius:5px;padding:2px 7px;font-size:11px"><?= esc($tag) ?></span><?php } ?>
              </div>
              <?php } } ?>
              <div class="ext-footer">
                <span>by <?= esc($ext['author'] ?: 'Unknown') ?></span>
                <span class="dl">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                  <?= number_format((int) $ext['download_count']) ?>
                </span>
              </div>
              <div style="display:flex;gap:8px;margin-top:8px">
                <?php if (!empty($ext['homepage_url'])) { ?>
                <a class="btn btn-o btn-sm" href="<?= esc($ext['homepage_url']) ?>" target="_blank" rel="noopener" style="flex:1;justify-content:center">Docs</a>
                <?php } ?>
                <a class="btn btn-p btn-sm" href="<?= base_url('extensions-download/' . (int) $ext['id']) ?>" style="flex:2;justify-content:center">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="width:15px;height:15px"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                  Download
                </a>
              </div>
            </div>
          </div>
          <?php } ?>
        </div>
      <?php } ?>
    </div>

    <!-- Per-platform sections -->
    <?php foreach ($platforms as $pKey => $pLabel) { ?>
    <div id="plat-<?= esc($pKey) ?>" class="plat-section" role="tabpanel">
      <?php $platExts = $extensions[$pKey] ?? []; ?>
      <?php if (!$platExts) { ?>
        <div class="empty-plat">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:48px;height:48px;color:var(--muted)"><rect x="3" y="3" width="18" height="18" rx="3"/><path d="M9 12h6M12 9v6" stroke-linecap="round"/></svg>
          <p>No <?= esc($pLabel) ?> extensions yet. <a href="<?= base_url('its-login') ?>">Log in</a> to upload the first one.</p>
        </div>
      <?php } else { ?>
        <div class="ext-grid">
          <?php foreach ($platExts as $ext) { $tc = $typeColors[$ext['type']] ?? '#0b4ea2'; $ti = $typeIcons[$ext['type']] ?? 'box'; ?>
          <div class="ext-card">
            <?php if (!empty($ext['screenshot'])) { ?>
            <div class="ext-screenshot"><img src="<?= base_url('uploads/extensions/' . esc($ext['screenshot'])) ?>" alt="<?= esc($ext['name']) ?>" loading="lazy"></div>
            <?php } ?>
            <div class="ext-card-body">
              <div class="ext-header">
                <div class="ext-icon" style="background:<?= esc($tc) ?>18;color:<?= esc($tc) ?>">
                  <?= Site::icon($ti) ?>
                </div>
                <div class="ext-meta">
                  <h3 title="<?= esc($ext['name']) ?>"><?= esc($ext['name']) ?></h3>
                  <span class="ver">v<?= esc($ext['version']) ?></span>
                </div>
              </div>
              <div style="display:flex;flex-wrap:wrap;gap:5px">
                <span class="badge" style="background:<?= esc($tc) ?>18;color:<?= esc($tc) ?>"><?= esc(ucfirst($ext['type'])) ?></span>
                <?php $pt = $ext['price_type'] ?? 'free'; ?><span class="badge badge-<?= $pt ?>"><?= $pt === 'paid' ? 'Paid' : 'Free' ?></span>
                <?php if (!empty($ext['license'])) { ?><span class="badge" style="background:#f1f5f9;color:#475569"><?= esc($ext['license']) ?></span><?php } ?>
              </div>
              <?php if ($ext['description']) { ?>
              <p class="ext-desc"><?= esc($ext['description']) ?></p>
              <?php } ?>
              <div class="ext-footer">
                <span>by <?= esc($ext['author'] ?: 'Unknown') ?></span>
                <span class="dl">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                  <?= number_format((int) $ext['download_count']) ?>
                </span>
              </div>
              <div style="display:flex;gap:8px;margin-top:8px">
                <?php if (!empty($ext['homepage_url'])) { ?>
                <a class="btn btn-o btn-sm" href="<?= esc($ext['homepage_url']) ?>" target="_blank" rel="noopener" style="flex:1;justify-content:center">Docs</a>
                <?php } ?>
                <a class="btn btn-p btn-sm" href="<?= base_url('extensions-download/' . (int) $ext['id']) ?>" style="flex:2;justify-content:center">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="width:15px;height:15px"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                  Download
                </a>
              </div>
            </div>
          </div>
          <?php } ?>
        </div>
      <?php } ?>
    </div>
    <?php } ?>

    <!-- Submit your extension -->
    <div class="submit-banner">
      <div>
        <h2>Have an extension to share?</h2>
        <p>Log in with your staff account to upload plugins, themes and add-ons for any platform.</p>
      </div>
      <a class="btn btn-w" href="<?= base_url('its-login') ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="width:18px;height:18px"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
        Submit an extension
      </a>
    </div>

    <!-- Access & subscriptions -->
    <div class="access-section" id="access-section">
      <?php
      $accessParam = service('request')->getGet('access') ?? '';
      if ($accessParam === 'required') { ?>
        <div class="access-alert required">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;flex:0 0 auto"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01" stroke-linecap="round"/></svg>
          Downloads require a subscription. Enter your registered email below to unlock, or choose a plan above.
        </div>
      <?php } elseif ($accessParam === 'denied') { ?>
        <div class="access-alert denied">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;flex:0 0 auto"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6" stroke-linecap="round"/></svg>
          Your email does not have an active subscription for this extension. Check your plan covers it, or request access below.
        </div>
      <?php } ?>
      <?php $accessOk = session('access_ok') ?? ''; $accessErr = session('access_err') ?? ''; ?>
      <?php if ($accessOk) { ?><div class="access-alert ok"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;flex:0 0 auto"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" stroke-linecap="round"/><path d="M22 4L12 14.01l-3-3" stroke-linecap="round"/></svg><?= esc($accessOk) ?></div><?php } ?>
      <?php if ($accessErr) { ?><div class="access-alert denied"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;flex:0 0 auto"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01" stroke-linecap="round"/></svg><?= esc($accessErr) ?></div><?php } ?>

      <div style="margin-bottom:16px">
        <div class="eyebrow">Downloads</div>
        <h2 style="margin-bottom:6px">Get Access to Downloads</h2>
        <p style="color:var(--muted);max-width:60ch;margin:0">Browsing is free. Downloading requires an active subscription. Enter your registered email to unlock downloads, or choose a plan above and register below.</p>
      </div>

      <div class="access-grid">
        <!-- Panel 1: Email unlock -->
        <div class="access-panel">
          <h3>Already subscribed?</h3>
          <p class="sub">Enter your registered email address to unlock downloads for this session.</p>
          <div class="key-input-wrap">
            <input type="email" id="dl-email-input" placeholder="your@email.com" autocomplete="email">
            <button class="btn btn-p btn-sm" onclick="activateEmail()">Unlock</button>
          </div>
          <p id="email-status" style="font-size:13px;margin:0;display:none"></p>
        </div>

        <!-- Panel 2: Request Access -->
        <div class="access-panel">
          <h3>Request access</h3>
          <p class="sub">Submit your details and we will review your request. Once approved, use your email address to unlock downloads.</p>
          <?php $packages = \Config\Database::connect()->tableExists('ext_packages') ? \Config\Database::connect()->table('ext_packages')->orderBy('name')->get()->getResultArray() : []; ?>
          <form method="post" action="<?= base_url('extensions-access-request') ?>">
            <?= csrf_field() ?>
            <div class="form-field">
              <label>Your name *</label>
              <input type="text" name="name" required maxlength="150" placeholder="Full name">
            </div>
            <div class="form-field">
              <label>Email address *</label>
              <input type="email" name="email" required maxlength="190" placeholder="you@example.com">
            </div>
            <div class="form-field">
              <label>Your system / platform</label>
              <select name="system">
                <option value="">— Select —</option>
                <option value="churchz">Churchz</option>
                <option value="umi">UMI</option>
                <option value="hr">HR Suite</option>
                <option value="hmisoz">HMISOZ</option>
                <option value="other">Other</option>
              </select>
            </div>
            <?php if ($packages) { ?>
            <div class="form-field">
              <label>Package of interest</label>
              <select name="package_id">
                <option value="">— Not sure yet —</option>
                <?php foreach ($packages as $pkg) { ?><option value="<?= $pkg['id'] ?>"><?= esc($pkg['name']) ?><?= $pkg['price_label'] ? ' — ' . esc($pkg['price_label']) : '' ?></option><?php } ?>
              </select>
            </div>
            <?php } ?>
            <button type="submit" class="btn btn-p" style="width:100%">Submit Request</button>
          </form>
        </div>
      </div>
    </div>

  </div>
</section>
</main>

<footer class="foot"><div class="wrap">
  <div class="foot-grid">
    <div>
      <?php if ($logoUrl) { ?><img src="<?= esc($logoUrl) ?>" alt="<?= esc($site['name']) ?>" style="max-height:44px;max-width:180px;object-fit:contain;margin-bottom:14px;display:block"><?php } else { ?><h3 style="font-size:1.1rem;letter-spacing:normal;text-transform:none;margin-bottom:10px"><?= esc($site['name']) ?></h3><?php } ?>
      <p style="opacity:.82;font-size:14px;margin:0 0 14px;max-width:320px"><?= esc($d['footer_about'] ?: ($site['tagline'] ?: 'Reliable computer and ICT solutions.')) ?></p>
      <?php if ($socials) { ?><div class="social"><?php foreach ($socials as $k => $u) { ?><a href="<?= esc($u) ?>" target="_blank" rel="noopener" aria-label="<?= esc(ucfirst($k)) ?>"><?= esc($socialMark[$k] ?? strtoupper($k[0])) ?></a><?php } ?></div><?php } ?>
      <div style="margin-top:18px;display:flex;flex-wrap:wrap;gap:10px">
        <?php if ($wa !== '') { ?><a class="btn btn-p btn-sm" href="<?= esc($waLink('Hello, I would like a quote')) ?>" target="_blank" rel="noopener" style="gap:8px"><svg viewBox="0 0 24 24" fill="currentColor" style="width:16px;height:16px"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm4.5 12.1c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.2-.4.7-1.3.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.7 11.8 11.8 0 0 0 4.5 4c1.7.7 2.3.8 3.2.6.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.2-1.2-.1-.1-.3-.2-.5-.3z"/></svg> WhatsApp</a><?php } ?>
        <a class="btn btn-o btn-sm" style="border-color:var(--ft-line);color:var(--ft-h)" href="<?= esc($ctaUrl) ?>">Get a quote</a>
      </div>
    </div>
    <?php foreach ($d['footer_order'] as $fsec) { switch ($fsec) {
      case 'menu': if (!$d['footer_menu']) break; ?><div><h3>Company</h3><ul><?php foreach ($menu as $m) { ?><li><a href="<?= esc(Site::url($m['slug'])) ?>"><?= esc($m['menu_label'] ?: $m['title']) ?></a></li><?php } ?></ul></div><?php break;
      case 'contact': if (!$d['footer_contact']) break; ?><div><h3>Contact us</h3><?php if ($site['email'] || $site['phone'] || $site['address']) { ?><ul style="list-style:none;padding:0;margin:0 0 14px;display:grid;gap:10px;font-size:14px"><?php if ($site['email']) { ?><li style="display:flex;gap:8px;align-items:center"><?= Site::icon('mail') ?><?= Site::email($site['email']) ?></li><?php } ?><?php if ($site['phone']) { ?><li style="display:flex;gap:8px;align-items:center"><?= Site::icon('phone') ?><a href="tel:<?= esc($tel) ?>"><?= esc($site['phone']) ?></a></li><?php } ?><?php if ($site['address']) { ?><li style="display:flex;gap:8px;align-items:flex-start"><?= Site::icon('map') ?><span style="opacity:.85"><?= nl2br(esc($site['address'])) ?></span></li><?php } ?></ul><?php } ?></div><?php break;
    } } ?>
  </div>
  <div class="foot-bottom">
    <span>© <?= date('Y') ?> <?= esc($site['name']) ?>.<?= $site['footer_note'] ? ' ' . esc($site['footer_note']) : ' All rights reserved.' ?></span>
    <span class="foot-bottom-links"><a href="<?= base_url('products') ?>">APP</a><a href="<?= base_url('extensions') ?>">Extensions</a><a href="<?= base_url('sitemap.xml') ?>">Sitemap</a><a href="<?= esc($portalUrl) ?>"><?= $user ? 'Workspace' : 'Staff login' ?></a></span>
  </div>
</div></footer>
<?php if ($wa !== '') { ?><a class="wa" href="<?= esc($waLink('Hello ' . $site['name'] . ', ')) ?>" target="_blank" rel="noopener" aria-label="Chat on WhatsApp"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.2-.4.7-1.3.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.7 11.8 11.8 0 0 0 4.5 4c1.7.7 2.3.8 3.2.6.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.2-1.2-.1-.1-.3-.2-.5-.3z"/></svg><span>WhatsApp</span></a><?php } ?>
<script>
(()=>{
const t=document.querySelector('.nav-toggle'),m=document.getElementById('menu');
t&&t.addEventListener('click',()=>{const o=m.classList.toggle('open');t.setAttribute('aria-expanded',String(o));});
document.querySelectorAll('.sub-tog').forEach(b=>{b.addEventListener('click',e=>{e.preventDefault();const li=b.closest('.has-sub');if(li){const o=li.classList.toggle('open');b.setAttribute('aria-expanded',String(o));}});});

// Platform tabs
const tabs=document.querySelectorAll('.plat-tab');
const panes=document.querySelectorAll('.plat-section');
tabs.forEach(tab=>{tab.addEventListener('click',()=>{tabs.forEach(x=>{x.classList.remove('active');x.setAttribute('aria-selected','false');});panes.forEach(p=>p.classList.remove('active'));tab.classList.add('active');tab.setAttribute('aria-selected','true');const id=tab.getAttribute('aria-controls');document.getElementById(id).classList.add('active');});});

// Search (filters the "All" grid when on the all tab)
const si=document.getElementById('ext-search');
if(si){si.addEventListener('input',()=>{const q=si.value.trim().toLowerCase();const grid=document.getElementById('ext-all-grid');if(!grid)return;grid.querySelectorAll('.ext-card').forEach(c=>{c.hidden=q!==''&&!c.dataset.name.includes(q);});});}

// Download email management — stored in sessionStorage; injected into all download URLs
const DL_EMAIL_KEY='ext_dl_email';
function getStoredEmail(){try{return sessionStorage.getItem(DL_EMAIL_KEY)||'';}catch(e){return '';}}
function setStoredEmail(v){try{sessionStorage.setItem(DL_EMAIL_KEY,v);}catch(e){}}
function rewriteDownloadLinks(email){
  document.querySelectorAll('a[href*="extensions-download/"]').forEach(a=>{
    const url=new URL(a.href,location.origin);
    if(email){url.searchParams.set('email',email);}else{url.searchParams.delete('email');}
    a.href=url.toString();
  });
}
function activateEmail(){
  const inp=document.getElementById('dl-email-input');
  const st=document.getElementById('email-status');
  const e=(inp?inp.value.trim():'');
  if(!e||!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e)){st.textContent='Please enter a valid email address.';st.style.color='#9a3412';st.style.display='block';return;}
  setStoredEmail(e);
  rewriteDownloadLinks(e);
  st.textContent='Email confirmed — download links are now unlocked for this session.';
  st.style.color='#166534';
  st.style.display='block';
}
// On page load, restore email and rewrite links
const storedEmail=getStoredEmail();
if(storedEmail){rewriteDownloadLinks(storedEmail);const inp=document.getElementById('dl-email-input');if(inp)inp.value=storedEmail;}
// If access was denied, clear the stored email so the user isn't stuck
const urlParams=new URLSearchParams(location.search);
const accessStatus=urlParams.get('access');
if(accessStatus==='denied'){setStoredEmail('');rewriteDownloadLinks('');}
// Auto-retry download only when redirected because no email was present (not when email was rejected)
const extId=urlParams.get('ext');
if(extId&&storedEmail&&accessStatus==='required'){location.href='<?= base_url('extensions-download/') ?>'+extId+'?email='+encodeURIComponent(storedEmail);}

// Right-click protection on extension cards and screenshots
document.addEventListener('contextmenu',e=>{
  if(e.target.closest('.ext-card,.ext-screenshot')){e.preventDefault();return false;}
});
document.querySelectorAll('.ext-screenshot img').forEach(img=>{
  img.setAttribute('draggable','false');
  img.addEventListener('dragstart',e=>e.preventDefault());
});
// Prevent download link URL inspection via right-click
document.querySelectorAll('a[href*="extensions-download/"]').forEach(a=>{
  a.addEventListener('contextmenu',e=>e.preventDefault());
});
})();
</script>
</body>
</html>
