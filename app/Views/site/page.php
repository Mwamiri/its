<?php
use App\Libraries\Site;
$p = $site['primary_color']; $a = $site['accent_color'];
$d = $design;
$slug = $page['slug'];
$hero = null; foreach ($blocks as $b) if ($b['type'] === 'hero') { $hero = $b; break; }
$firstText = ''; foreach ($blocks as $b) if (in_array($b['type'], ['text', 'image_text'], true) && trim($b['text']) !== '') { $firstText = $b['text']; break; }
$metaTitle = $page['meta_title'] ?: ($slug === 'home' ? ($site['meta_title'] ?: $site['name'] . ($site['tagline'] ? ' | ' . $site['tagline'] : '')) : $page['title'] . ' | ' . $site['name']);
$metaDesc = $page['meta_description'] ?: ($site['meta_description'] ?: mb_strimwidth(trim(strip_tags(Site::md(($hero['subtitle'] ?? '') ?: $firstText))), 0, 160, '…'));
$canonical = $slug === '404' ? '' : Site::url($slug);
$logoUrl = $site['logo'] !== '' ? base_url($site['logo']) : '';
$ogImage = !empty($hero['image']) ? base_url($hero['image']) : $logoUrl;
$wa = preg_replace('/\D+/', '', (string) $site['whatsapp']);
$waLink = static fn(string $text = '') => 'https://wa.me/' . $wa . ($text !== '' ? '?text=' . rawurlencode($text) : '');
$tel = preg_replace('/[^\d+]/', '', (string) $site['phone']);
$anchor = static fn(string $t) => trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower($t)), '-');
$socials = array_filter(['facebook' => $site['facebook'], 'linkedin' => $site['linkedin'], 'x' => $site['x'], 'instagram' => $site['instagram'], 'youtube' => $site['youtube']], static fn($u) => preg_match('#^https?://#i', (string) $u));
$org = ['@context' => 'https://schema.org', '@type' => 'LocalBusiness', 'name' => $site['name'], 'url' => base_url(), 'description' => $site['meta_description'] ?: $site['tagline']];
if ($logoUrl) { $org['logo'] = $logoUrl; $org['image'] = $logoUrl; }
$protect = Site::setting('site_email_protect', '1') !== '0';
if ($site['email'] && !$protect) $org['email'] = $site['email'];
if ($site['phone']) $org['telephone'] = $site['phone'];
if ($site['address']) $org['address'] = ['@type' => 'PostalAddress', 'streetAddress' => preg_replace('/\s+/', ' ', $site['address'])];
if ($socials) $org['sameAs'] = array_values($socials);
if (($site['email'] && !$protect) || $site['phone']) $org['contactPoint'] = array_filter(['@type' => 'ContactPoint', 'contactType' => 'customer support', 'email' => $protect ? null : ($site['email'] ?: null), 'telephone' => $site['phone'] ?: null]);
$jsonFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP;
$user = $user ?? null;
$portalUrl = $user ? base_url(($user['role'] ?? '') === 'client' ? 'its-portal' : 'its-dashboard') : base_url('its-login');
$clientUrl = $user ? $portalUrl : base_url('its-login?for=client');
$socialMark = ['facebook' => 'f', 'linkedin' => 'in', 'x' => 'X', 'instagram' => 'ig', 'youtube' => 'yt'];
$fontStack = ['system' => '"Segoe UI",Roboto,Helvetica,Arial,sans-serif', 'inter' => 'Inter,"Segoe UI",sans-serif', 'poppins' => 'Poppins,"Segoe UI",sans-serif', 'montserrat' => 'Montserrat,"Segoe UI",sans-serif', 'roboto' => 'Roboto,"Segoe UI",sans-serif', 'open-sans' => '"Open Sans","Segoe UI",sans-serif'][$d['font']];
$btnRadius = ['rounded' => '12px', 'pill' => '999px', 'square' => '4px'][$d['button_style']];
$space = ['compact' => 'clamp(32px,4vw,56px)', 'normal' => 'clamp(44px,5.5vw,84px)', 'airy' => 'clamp(64px,8vw,120px)'][$d['spacing']];
$hdr = ['light' => ['rgba(255,255,255,.94)', 'var(--ink)', 'var(--line)'], 'dark' => ['#0f172a', '#e2e8f0', '#1e293b'], 'brand' => ['var(--p)', '#fff', 'transparent']][$d['header_style']];
$ftr = ['dark' => ['#0f172a', '#b8c3d6', '#fff', '#23314a'], 'brand' => ['var(--p)', '#dbe6fb', '#fff', 'rgba(255,255,255,.18)'], 'light' => ['#f1f5f9', '#475569', '#0f172a', '#dbe2ea']][$d['footer_style']];
$faqItems = in_array('faq', array_column($blocks, 'type'), true) ? ($items['faq'] ?? []) : [];
$ctaUrl = Site::link($d['cta_url'] ?: 'contact');
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= esc($metaTitle) ?></title>
<meta name="description" content="<?= esc($metaDesc) ?>">
<?php if ($site['keywords'] !== '') { ?><meta name="keywords" content="<?= esc($site['keywords']) ?>"><?php } ?>
<meta name="robots" content="<?= !empty($page['robots']) || !empty($preview) ? 'noindex' : 'index,follow' ?>">
<?php if ($canonical) { ?><link rel="canonical" href="<?= esc($canonical) ?>"><?php } ?>
<meta property="og:type" content="website"><meta property="og:site_name" content="<?= esc($site['name']) ?>"><meta property="og:title" content="<?= esc($metaTitle) ?>"><meta property="og:description" content="<?= esc($metaDesc) ?>"><?php if ($canonical) { ?><meta property="og:url" content="<?= esc($canonical) ?>"><?php } ?><?php if ($ogImage) { ?><meta property="og:image" content="<?= esc($ogImage) ?>"><?php } ?>
<meta name="twitter:card" content="summary_large_image"><meta name="twitter:title" content="<?= esc($metaTitle) ?>"><meta name="twitter:description" content="<?= esc($metaDesc) ?>">
<?php if ($site['search_console'] !== '') { ?><meta name="google-site-verification" content="<?= esc($site['search_console']) ?>"><?php } ?>
<meta name="theme-color" content="<?= esc($p) ?>">
<?php if ($logoUrl) { ?><link rel="icon" href="<?= esc($logoUrl) ?>"><?php } else { ?><link rel="icon" href="<?= base_url('icon.svg') ?>" type="image/svg+xml"><?php } ?>
<?php if (isset(Site::FONTS[$d['font']])) { ?><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=<?= Site::FONTS[$d['font']] ?>:wght@400;500;600;700;800&display=swap"><?php } ?>
<script type="application/ld+json"><?= json_encode($org, $jsonFlags) ?></script>
<?php if ($slug !== 'home' && $slug !== '404') { ?><script type="application/ld+json"><?= json_encode(['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => [['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => base_url()], ['@type' => 'ListItem', 'position' => 2, 'name' => $page['title'], 'item' => $canonical]]], $jsonFlags) ?></script><?php } ?>
<?php if ($faqItems) { ?><script type="application/ld+json"><?= json_encode(['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array_map(static fn($f) => ['@type' => 'Question', 'name' => $f['title'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags(Site::md($f['body']))]], $faqItems)], $jsonFlags) ?></script><?php } ?>
<?php if (empty($preview) && preg_match('/^G-[A-Z0-9]{4,15}$/', $site['ga_id'])) { ?><script async src="https://www.googletagmanager.com/gtag/js?id=<?= esc($site['ga_id']) ?>"></script><script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config','<?= esc($site['ga_id']) ?>');</script><?php } ?>
<style>
:root{--p:<?= esc($p) ?>;--a:<?= esc($a) ?>;--ink:#0f172a;--muted:#55627a;--line:#e3e8f0;--bg:#f6f8fc;--card:#fff;--r:<?= (int) $d['radius'] ?>px;--btn-r:<?= $btnRadius ?>;--space:<?= $space ?>;--hdr-bg:<?= $hdr[0] ?>;--hdr-fg:<?= $hdr[1] ?>;--hdr-line:<?= $hdr[2] ?>;--ft-bg:<?= $ftr[0] ?>;--ft-fg:<?= $ftr[1] ?>;--ft-h:<?= $ftr[2] ?>;--ft-line:<?= $ftr[3] ?>}
*{box-sizing:border-box}html,body{max-width:100%;overflow-x:clip}html{scroll-behavior:smooth;scroll-padding-top:90px;font-size:<?= (int) $d['font_size'] ?>px}body{margin:0;font-family:<?= $fontStack ?>;color:var(--ink);background:#fff;line-height:1.6;-webkit-font-smoothing:antialiased}
a{color:var(--p)}img{max-width:100%;height:auto}h1,h2,h3{line-height:1.2;text-wrap:balance;margin:0 0 .5em}h1{font-size:clamp(2rem,1.4rem + 2.6vw,3.3rem);letter-spacing:-.02em}h2{font-size:clamp(1.5rem,1.2rem + 1.2vw,2.2rem);letter-spacing:-.01em}h3{font-size:1.15rem}p{margin:0 0 1em;text-wrap:pretty}
.wrap{width:min(1180px,100% - 40px);margin-inline:auto}
.skip{position:absolute;left:-999px}.skip:focus{left:12px;top:12px;z-index:99;background:#fff;padding:8px 12px;border-radius:8px}
.preview-bar{position:sticky;top:0;z-index:60;background:#fde68a;color:#78350f;text-align:center;font:600 13px/1.4 sans-serif;padding:6px}
.topbar{background:var(--ink);color:#cbd5e1;font-size:13px}.topbar .wrap{display:flex;flex-wrap:wrap;gap:6px 22px;justify-content:space-between;padding-block:7px}.topbar a{color:#e2e8f0;text-decoration:none}.topbar span,.topbar a{display:inline-flex;align-items:center;gap:6px}.topbar .ic{width:15px;height:15px}
.announce{background:var(--a);color:#fff;text-align:center;font-weight:600;font-size:14px;padding:8px 16px}
.nav{<?= $d['header_sticky'] ? 'position:sticky;top:0;' : 'position:relative;' ?>z-index:40;background:var(--hdr-bg);backdrop-filter:saturate(1.4) blur(12px);border-bottom:1px solid var(--hdr-line);color:var(--hdr-fg)}
.nav .wrap{display:flex;align-items:center;gap:14px;min-height:72px}.nav .wrap>*{min-width:0}
.brand{display:flex;align-items:center;gap:10px;text-decoration:none;color:var(--hdr-fg);font-weight:800;font-size:1.05rem;<?= $d['menu_align'] === 'center' ? '' : 'margin-inline-end:auto' ?>}.brand img{max-height:<?= (int) $d['logo_height'] ?>px;max-width:220px;object-fit:contain}.brand-mark{display:grid;place-items:center;width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,var(--p),var(--a));color:#fff}.brand-mark .ic{width:22px;height:22px}.brand small{display:block;font-weight:500;opacity:.75;font-size:12px}
<?= $d['menu_align'] === 'center' ? '.menu-wrap{margin-inline:auto}' : '' ?>
.menu{display:flex;align-items:center;gap:2px;list-style:none;margin:0;padding:0}.menu a{display:block;white-space:nowrap;padding:8px 13px;border-radius:10px;text-decoration:none;color:var(--hdr-fg);font-weight:600;font-size:15px;position:relative;transition:background .15s,color .15s}.menu a:after{content:"";position:absolute;bottom:2px;left:13px;right:13px;height:2px;border-radius:2px;background:var(--p);transform:scaleX(0);transition:transform .2s}.menu a:hover:after,.menu a[aria-current=page]:after{transform:scaleX(1)}.menu a:hover,.menu a[aria-current=page]{background:color-mix(in srgb,currentColor 8%,transparent)}<?= $d['header_style'] === 'light' ? '.menu a:hover,.menu a[aria-current=page]{color:var(--p);background:color-mix(in srgb,var(--p) 8%,#fff)}' : '' ?><?= $d['header_style'] === 'brand' ? '.menu a:after{background:#fff}' : '' ?>
.btn{white-space:nowrap;display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:12px 20px;border-radius:var(--btn-r);font-weight:700;text-decoration:none;border:2px solid transparent;cursor:pointer;font-size:15px;font-family:inherit;transition:transform .15s,box-shadow .15s}.btn:hover{transform:translateY(-1px)}.btn .ic{width:18px;height:18px}
.btn-p{background:var(--p);color:#fff;box-shadow:0 8px 20px color-mix(in srgb,var(--p) 30%,transparent)}.btn-a{background:var(--a);color:#fff}.btn-o{border-color:currentColor;color:var(--p);background:transparent}.btn-w{background:#fff;color:var(--p)}.btn-sm{padding:8px 14px;font-size:14px}
.nav .btn-o{color:var(--hdr-fg)}<?= $d['header_style'] === 'brand' ? '.nav .btn-p{background:#fff;color:var(--p)}' : '' ?>
.has-sub{position:relative}.sub-menu{display:none;position:absolute;top:calc(100% + 4px);left:0;list-style:none;margin:0;padding:6px;min-width:200px;background:var(--card);border:1px solid var(--line);border-radius:var(--r);box-shadow:0 8px 30px rgba(15,23,42,.12);z-index:50}.has-sub:hover .sub-menu,.has-sub:focus-within .sub-menu{display:block}.sub-menu a{display:flex;padding:9px 13px;border-radius:8px;color:var(--ink)!important;font-size:14.5px;font-weight:600;white-space:nowrap;text-decoration:none;background:none}.sub-menu a:after{display:none!important}.sub-menu a:hover{background:color-mix(in srgb,var(--p) 8%,#fff);color:var(--p)!important}.has-sub>a{padding-right:6px}.sub-tog{display:none;background:none;border:none;color:var(--hdr-fg);cursor:pointer;padding:0 5px;font-size:12px;line-height:1}.sub-menu .has-sub{position:relative}.sub-sub-menu{display:none;position:absolute;top:-6px;left:calc(100% + 4px);list-style:none;margin:0;padding:6px;min-width:200px;background:var(--card);border:1px solid var(--line);border-radius:var(--r);box-shadow:0 8px 30px rgba(15,23,42,.12);z-index:60}.sub-menu .has-sub:hover .sub-sub-menu,.sub-menu .has-sub:focus-within .sub-sub-menu{display:block}.sub-sub-menu a{display:flex;padding:9px 13px;border-radius:8px;color:var(--ink)!important;font-size:14.5px;font-weight:600;white-space:nowrap;text-decoration:none;background:none}.sub-sub-menu a:after{display:none!important}.sub-sub-menu a:hover{background:color-mix(in srgb,var(--p) 8%,#fff);color:var(--p)!important}.sub-menu .has-sub>a{padding-right:4px}.sub-menu .has-sub>a::before{content:"›";float:right;margin-left:6px;opacity:.55;font-size:13px;font-weight:400}
@media(max-width:1199px){.sub-menu{position:static;display:none;padding:0 0 4px 14px;box-shadow:none;border:none;background:transparent;min-width:0}.has-sub.open .sub-menu{display:block}.sub-menu a{color:var(--ink)!important;font-size:15px;padding:9px 12px}.sub-tog{display:inline-block;margin-left:4px}.has-sub>a{padding-right:2px}.sub-sub-menu{position:static;display:none;padding:0 0 4px 14px;box-shadow:none;border:none;background:transparent;min-width:0}.sub-menu .has-sub.open .sub-sub-menu{display:block}.sub-sub-menu a{color:var(--ink)!important;font-size:14px;padding:7px 12px}.sub-menu .has-sub>a::before{display:none}}
.nav-actions{display:flex;gap:8px;align-items:center}.nav-toggle{display:none;background:none;border:1px solid var(--hdr-line);color:var(--hdr-fg);border-radius:10px;padding:8px 10px;cursor:pointer}.nav-toggle .ic{width:22px;height:22px}
@media(max-width:1199px){.nav-toggle{display:inline-flex}.brand{margin-inline-end:auto}.menu-wrap{position:absolute;inset-inline:0;top:100%;background:#fff;border-bottom:1px solid var(--line);box-shadow:0 18px 40px rgba(15,23,42,.1);opacity:0;pointer-events:none;transform:translateY(-8px);transition:opacity .2s,transform .2s;padding:10px 20px 18px;margin:0}.menu-wrap.open{opacity:1;pointer-events:auto;transform:none}.menu{flex-direction:column;align-items:stretch}.menu a{color:var(--ink);padding:11px 12px;border-radius:8px;font-size:16px}.menu a:after{display:none}.menu a[aria-current=page]{background:color-mix(in srgb,var(--p) 8%,#fff);color:var(--p)}.nav-actions .btn-o{display:none}.menu-wrap:after{content:"";display:block;margin-top:10px;padding-top:10px;border-top:1px solid var(--line)}}
.hero{position:relative;overflow:hidden;background:radial-gradient(1200px 500px at 85% -10%,color-mix(in srgb,var(--a) 16%,transparent),transparent),linear-gradient(180deg,color-mix(in srgb,var(--p) 7%,#fff),#fff)}
.hero .wrap>*{min-width:0}.hero .wrap{display:grid;grid-template-columns:minmax(0,1.05fr) minmax(0,.95fr);gap:40px;align-items:center;padding-block:clamp(48px,7vw,96px)}.hero.center .wrap{grid-template-columns:1fr;padding-block:clamp(40px,5vw,70px);text-align:center}.hero.center .hero-cta,.hero.center .trust{justify-content:center}
.eyebrow{display:inline-flex;align-items:center;gap:8px;color:var(--p);font-weight:700;font-size:13px;letter-spacing:.08em;text-transform:uppercase;margin-bottom:12px}.eyebrow:before{content:"";width:22px;height:2px;background:var(--a)}
.lead{font-size:clamp(1.05rem,1rem + .35vw,1.25rem);color:var(--muted);max-width:60ch}.hero.center .lead{margin-inline:auto}
.hero-cta{display:flex;flex-wrap:wrap;gap:12px;margin-top:22px}.hero-art{width:100%;max-width:520px;justify-self:center;filter:drop-shadow(0 30px 40px rgba(15,23,42,.12))}.hero-img{width:100%;border-radius:24px;box-shadow:0 30px 60px rgba(15,23,42,.18);object-fit:cover;aspect-ratio:4/3}
.trust{display:flex;flex-wrap:wrap;gap:8px 18px;margin-top:22px;color:var(--muted);font-size:14px}.trust span{display:inline-flex;gap:6px;align-items:center}.trust .ic{width:17px;height:17px;color:var(--p)}
@media(max-width:900px){.hero .wrap{grid-template-columns:minmax(0,1fr);gap:24px;padding-block:36px 8px}.hero-art{max-width:380px}}
.sec{padding-block:var(--space)}.sec.alt{background:var(--bg)}.sec.brand{background:linear-gradient(135deg,var(--p),color-mix(in srgb,var(--p) 70%,var(--a)));color:#fff}.sec.dark{background:#0f172a;color:#e2e8f0}
.sec.brand .eyebrow,.sec.dark .eyebrow{color:#fff}.sec.brand .sec-head p,.sec.dark .sec-head p,.sec.brand .prose,.sec.dark .prose{color:inherit;opacity:.92}.sec.brand a:not(.btn),.sec.dark a:not(.btn){color:#fff}
.sec-head{max-width:720px;margin:0 auto 34px;text-align:center}.sec-head p{color:var(--muted)}
.prose{max-width:800px;margin-inline:auto;font-size:1.05rem}.prose.center{text-align:center}.prose h2{margin-top:1.4em}.prose h2:first-child{margin-top:0}.prose ul{padding-inline-start:1.2em}.prose li{margin:.3em 0}
.split{display:grid;grid-template-columns:1fr 1fr;gap:clamp(24px,4vw,56px);align-items:center}.split.img-right .split-img{order:2}.split-img img,.split-img svg{width:100%;border-radius:var(--r);box-shadow:0 24px 50px rgba(15,23,42,.14);display:block}@media(max-width:860px){.split{grid-template-columns:1fr}.split.img-right .split-img{order:0}}
.grid{display:grid;gap:20px;grid-template-columns:repeat(auto-fill,minmax(min(100%,270px),1fr))}
.card{background:var(--card);color:var(--ink);border:1px solid var(--line);border-radius:var(--r);padding:24px;transition:transform .2s,box-shadow .2s;display:flex;flex-direction:column}.card:hover{transform:translateY(-3px);box-shadow:0 18px 40px rgba(15,23,42,.08)}
.ic-badge{display:grid;place-items:center;width:52px;height:52px;border-radius:14px;background:color-mix(in srgb,var(--p) 10%,#fff);color:var(--p);margin-bottom:14px}.ic-badge .ic{width:26px;height:26px}.card p{color:var(--muted)}
.ticks{list-style:none;padding:0;margin:4px 0 0;display:grid;gap:6px;font-size:14.5px}.ticks li{display:flex;gap:8px;align-items:flex-start}.ticks li:before{content:"";flex:0 0 16px;height:16px;margin-top:4px;border-radius:50%;background:var(--a) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath d='M4 8.5l2.5 2.5L12 5.5' fill='none' stroke='white' stroke-width='2' stroke-linecap='round'/%3E%3C/svg%3E") center/12px no-repeat}
.media{margin:-24px -24px 18px;border-radius:var(--r) var(--r) 0 0;overflow:hidden;aspect-ratio:16/9;background:var(--bg)}.media img,.media svg{width:100%;height:100%;object-fit:cover;display:block}
.price{margin-top:auto;padding-top:12px;font-weight:800;color:var(--p)}.card-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:14px}
.chips{display:flex;flex-wrap:wrap;gap:8px;justify-content:center;margin-bottom:24px}.chip{border:1px solid var(--line);background:#fff;color:var(--ink);border-radius:999px;padding:7px 14px;font-weight:600;cursor:pointer;font-size:14px;font-family:inherit}.chip[aria-pressed=true]{background:var(--p);border-color:var(--p);color:#fff}.tag{display:inline-block;align-self:flex-start;background:color-mix(in srgb,var(--a) 14%,#fff);color:color-mix(in srgb,var(--a) 70%,#000);border-radius:999px;padding:3px 10px;font-size:12px;font-weight:700;margin-bottom:8px}
.stats{display:grid;gap:14px;grid-template-columns:repeat(auto-fit,minmax(min(100%,170px),1fr))}.stat{display:flex;gap:12px;align-items:center;background:#fff;color:var(--ink);border:1px solid var(--line);border-radius:16px;padding:16px}.stat .ic{width:28px;height:28px;color:var(--p);flex:0 0 auto}.stat strong{display:block;font-size:1.35rem;color:var(--p);line-height:1.1}
.quote{position:relative;margin:0}.quote .ic{position:absolute;top:18px;right:18px;width:34px;height:34px;color:color-mix(in srgb,var(--p) 20%,#fff)}.quote footer{margin-top:auto;font-weight:700}.quote footer span{display:block;font-weight:500;color:var(--muted);font-size:14px}
.pkg.featured{border:2px solid var(--p);box-shadow:0 20px 40px color-mix(in srgb,var(--p) 16%,transparent)}
.faq{max-width:820px;margin-inline:auto;display:grid;gap:10px}.faq details{border:1px solid var(--line);border-radius:14px;background:#fff;color:var(--ink);padding:16px 20px}.faq summary{cursor:pointer;font-weight:700;list-style:none;display:flex;justify-content:space-between;gap:12px}.faq summary:after{content:"+";color:var(--p);font-size:22px;line-height:1}.faq details[open] summary:after{content:"–"}.faq details p{margin:.7em 0 0;color:var(--muted)}
.logos{display:flex;flex-wrap:wrap;gap:16px 28px;align-items:center;justify-content:center}.logos .logo{height:54px;display:grid;place-items:center;padding:8px 18px;border:1px solid var(--line);border-radius:12px;background:#fff;font-weight:800;color:var(--muted)}.logos img{max-height:38px;max-width:140px;object-fit:contain;filter:grayscale(1);opacity:.8}.logos .logo:hover img{filter:none;opacity:1}
.cta{color:#fff;border-radius:calc(var(--r) + 10px);padding:clamp(28px,5vw,54px);display:grid;grid-template-columns:1fr auto;gap:22px;align-items:center;position:relative;overflow:hidden}.cta.brand{background:linear-gradient(120deg,var(--p),color-mix(in srgb,var(--p) 55%,var(--a)))}.cta.accent{background:linear-gradient(120deg,var(--a),color-mix(in srgb,var(--a) 60%,#000))}.cta.dark{background:#0f172a}.cta h2{color:#fff}.cta p{color:#e7eefc;margin:0}.cta .hero-cta{margin:0}.cta:after{content:"";position:absolute;width:320px;height:320px;border-radius:50%;background:rgba(255,255,255,.08);right:-90px;top:-120px}
@media(max-width:780px){.cta{grid-template-columns:1fr}}
.spacer-s{height:24px}.spacer-m{height:56px}.spacer-l{height:96px}
.contact{display:grid;grid-template-columns:1.2fr .8fr;gap:26px}@media(max-width:900px){.contact{grid-template-columns:1fr}}
.form{display:grid;grid-template-columns:1fr 1fr;gap:4px 16px}.form .full{grid-column:1/-1}.form label{font-weight:600;font-size:14px}.form input,.form select,.form textarea{width:100%;padding:12px 14px;border:1px solid #cdd6e3;border-radius:12px;font:inherit;margin:6px 0 12px;background:#fff}.form input:focus,.form select:focus,.form textarea:focus{outline:2px solid color-mix(in srgb,var(--p) 40%,transparent);border-color:var(--p)}@media(max-width:600px){.form{grid-template-columns:1fr}}
.hp{position:absolute!important;left:-5000px!important}
.info-list{list-style:none;padding:0;margin:0;display:grid;gap:14px}.info-list li{display:flex;gap:12px}.info-list .ic{width:22px;height:22px;color:var(--p);flex:0 0 auto;margin-top:2px}.info-list strong{display:block}
.map{width:100%;aspect-ratio:16/10;border:0;border-radius:16px;margin-top:16px}
.flash{padding:14px 18px;border-radius:12px;margin-bottom:16px;font-weight:600}.flash.ok{background:#e7f8ef;color:#13653b}.flash.err{background:#fdeceb;color:#9b1c1c}
.foot{background:var(--ft-bg);color:var(--ft-fg);padding-block:60px 28px;font-size:15px}.foot a{color:var(--ft-h);text-decoration:none}.foot a:hover{opacity:.8;text-decoration:underline}.foot h3{color:var(--ft-h);font-size:.875rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;margin:0 0 16px}.foot-grid{display:grid;gap:32px 48px;grid-template-columns:minmax(200px,1.6fr) repeat(auto-fit,minmax(160px,1fr))}.foot ul{list-style:none;padding:0;margin:0;display:grid;gap:9px}.foot ul li a{display:flex;align-items:center;gap:6px;opacity:.9}.foot ul li a:hover{opacity:1}.foot .ic{color:var(--ft-h)}.foot-cta{margin-top:18px;display:flex;flex-wrap:wrap;gap:10px}.foot-cta .btn{font-size:14px;padding:9px 18px}.foot-bottom{border-top:1px solid var(--ft-line);margin-top:38px;padding-top:20px;display:flex;flex-wrap:wrap;gap:8px;justify-content:space-between;align-items:center;font-size:13px;opacity:.8}.foot-bottom-links{display:flex;gap:16px}.foot-bottom-links a{opacity:.9}
.social{display:flex;flex-wrap:wrap;gap:8px;margin-top:14px}.social a{display:grid;place-items:center;width:38px;height:38px;border-radius:10px;border:1px solid var(--ft-line);background:color-mix(in srgb,var(--ft-h) 6%,transparent);transition:background .15s;font-size:13px;font-weight:800;color:var(--ft-h);text-decoration:none!important}.social a:hover{background:color-mix(in srgb,var(--ft-h) 18%,transparent);opacity:1}
@media(max-width:960px){.foot-grid{grid-template-columns:1fr 1fr}.foot-grid>div:first-child{grid-column:1/-1}}
@media(max-width:480px){.foot-grid{grid-template-columns:1fr}}
.wa{position:fixed;right:18px;bottom:18px;z-index:50;display:flex;align-items:center;gap:8px;background:#25d366;color:#fff;border-radius:999px;padding:12px 18px;font-weight:700;text-decoration:none;box-shadow:0 12px 30px rgba(0,0,0,.2)}.wa svg{width:22px;height:22px}
@media(max-width:600px){.brand span{font-size:.92rem}.brand small{display:none}.nav-actions .btn-p{padding:8px 12px;font-size:13px}.topbar .wrap{justify-content:center}.hero-cta .btn{flex:1 1 auto}.wa span{display:none}.wa{padding:13px}}
@media print{.nav,.topbar,.wa,.foot{display:none}}
@media(prefers-reduced-motion:reduce){*{transition:none!important;scroll-behavior:auto!important}}
/* ── Slider ── */
.slider{position:relative;overflow:hidden;background:#0f172a;color:#fff}.slider.h-short{height:min(360px,55vw)}.slider.h-medium{height:min(520px,70vw)}.slider.h-tall{height:min(700px,88vw)}.slider.h-full{height:100svh}
.slides{height:100%;position:relative}.slide{position:absolute;inset:0;opacity:0;transition:opacity .8s ease}.slide.is-on{opacity:1;z-index:1}
.bg-hero .bg-media,.slide .bg-media{position:absolute;inset:0;overflow:hidden}.bg-hero .bg-media img,.bg-hero .bg-media video,.slide .bg-media img,.slide .bg-media video{width:100%;height:100%;object-fit:cover}
.slider.ov-dark .bg-media::after,.slider.ov-medium .bg-media::after,.slider.ov-light .bg-media::after{content:"";position:absolute;inset:0;pointer-events:none}
.slider.ov-dark .bg-media::after{background:rgba(0,0,0,.55)}.slider.ov-medium .bg-media::after{background:rgba(0,0,0,.35)}.slider.ov-light .bg-media::after{background:rgba(0,0,0,.18)}
.slide-copy{position:relative;z-index:2;height:100%;display:flex;align-items:center;padding-block:40px}
.slide-copy>div{max-width:820px}.slider.al-center .slide-copy{justify-content:center;text-align:center}.slider.al-center .hero-cta{justify-content:center}.slider.al-right .slide-copy{justify-content:flex-end}
.slide-title{font-size:clamp(1.8rem,1.2rem + 3vw,4rem);font-weight:800;color:#fff;letter-spacing:-.02em;text-shadow:0 2px 24px rgba(0,0,0,.4);margin-bottom:.4em}
.sl-nav{position:absolute;top:50%;transform:translateY(-50%);z-index:4;background:rgba(255,255,255,.14);border:none;color:#fff;width:50px;height:50px;border-radius:50%;font-size:28px;cursor:pointer;backdrop-filter:blur(6px);transition:background .15s;display:grid;place-items:center;line-height:1}.sl-nav:hover{background:rgba(255,255,255,.28)}.sl-nav.prev{left:16px}.sl-nav.next{right:16px}
.sl-dots{position:absolute;bottom:20px;left:0;right:0;z-index:4;display:flex;justify-content:center;gap:8px}.sl-dots button{width:32px;height:4px;border-radius:4px;border:none;background:rgba(255,255,255,.4);cursor:pointer;transition:background .2s,width .2s;padding:0}.sl-dots button[aria-current=true]{background:#fff;width:52px}
@media(max-width:600px){.slide-title{font-size:clamp(1.4rem,1rem + 2.5vw,2.4rem)}.sl-nav{display:none}}
/* ── Marquee ── */
@keyframes _mq{to{transform:translateX(-50%)}}
.marquee{overflow:hidden;-webkit-mask:linear-gradient(90deg,transparent,#000 8%,#000 92%,transparent)}.marquee-track{display:inline-flex;gap:24px;align-items:center}.marquee.fast .marquee-track{animation:_mq 20s linear infinite}.marquee.medium .marquee-track{animation:_mq 30s linear infinite}.marquee.slow .marquee-track{animation:_mq 45s linear infinite}.marquee:hover .marquee-track{animation-play-state:paused}
.in-colour .logo img{filter:none!important;opacity:1!important}.named .logo{flex-direction:column;height:auto;gap:6px}.named .logo small{font-size:12px;font-weight:600;color:var(--muted)}.logo-name{font-weight:800;font-size:15px;color:var(--muted)}
/* ── Counters ── */
.counters{display:grid;gap:14px;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));text-align:center}.counter{background:#fff;border:1px solid var(--line);border-radius:16px;padding:24px 16px}.counter strong{display:block;font-size:2.2rem;font-weight:800;color:var(--p);line-height:1}.counter span{font-size:14px;color:var(--muted);margin-top:6px;display:block}
/* ── Gallery ── */
.gallery{display:grid;gap:10px}.gallery.cols-2{grid-template-columns:repeat(2,1fr)}.gallery.cols-3{grid-template-columns:repeat(3,1fr)}.gallery.cols-4{grid-template-columns:repeat(4,1fr)}
.gallery figure{margin:0;border-radius:var(--r);overflow:hidden;background:var(--bg)}.gallery figure a{display:block;overflow:hidden}.gallery img{width:100%;height:220px;object-fit:cover;transition:transform .4s,filter .4s;display:block}.gallery figure:hover img{transform:scale(1.06);filter:brightness(1.08)}.gallery figcaption{padding:7px 10px;font-size:13px;color:var(--muted)}
@media(max-width:600px){.gallery.cols-3,.gallery.cols-4{grid-template-columns:repeat(2,1fr)}}
/* ── Video ── */
.video-box{border-radius:var(--r);overflow:hidden;background:#000}.video-box video,.video-box iframe{width:100%;aspect-ratio:16/9;display:block;border:none}.video-box.wide{max-width:960px;margin-inline:auto}
/* ── Lightbox ── */
#lb{position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.93);display:grid;place-items:center;animation:_pi .18s ease;cursor:zoom-out}
#lb-img{max-width:min(94vw,1320px);max-height:90vh;border-radius:8px;object-fit:contain;display:block;cursor:default}
#lb-close{position:absolute;top:16px;right:16px;background:rgba(255,255,255,.12);border:none;color:#fff;font-size:20px;cursor:pointer;padding:8px 12px;border-radius:10px;backdrop-filter:blur(4px);line-height:1;z-index:1}#lb-close:hover{background:rgba(255,255,255,.26)}
.lb-arrow{position:absolute;top:50%;transform:translateY(-50%);background:rgba(255,255,255,.12);border:none;color:#fff;font-size:36px;cursor:pointer;padding:10px 15px;border-radius:12px;backdrop-filter:blur(6px);line-height:1;z-index:1;transition:background .15s}.lb-arrow:hover{background:rgba(255,255,255,.26)}.lb-arrow.lb-prev{left:14px}.lb-arrow.lb-next{right:14px}
#lb-cap{position:absolute;bottom:18px;left:0;right:0;text-align:center;color:rgba(255,255,255,.82);font-size:14px;padding:0 80px;pointer-events:none}
#lb-ctr{position:absolute;top:18px;left:18px;color:rgba(255,255,255,.72);font-size:13px;font-weight:600;background:rgba(0,0,0,.4);padding:4px 10px;border-radius:20px}
/* ── Enhancements ── */
/* Page load */
body{animation:_pi .3s ease}@keyframes _pi{from{opacity:0}to{opacity:1}}
/* Scroll reveal */
@media(prefers-reduced-motion:no-preference){
  [data-rv]{opacity:0;transform:translateY(24px);transition:opacity .6s ease,transform .6s ease}
  [data-rv].in{opacity:1;transform:none}
  [data-rv-d1]{transition-delay:.12s}[data-rv-d2]{transition-delay:.24s}[data-rv-d3]{transition-delay:.36s}
}
/* Eyebrow pill */
.eyebrow{background:color-mix(in srgb,var(--p) 10%,transparent);border:1px solid color-mix(in srgb,var(--p) 22%,transparent);border-radius:999px;padding:4px 13px 4px 10px}
.eyebrow::before{width:6px;height:6px;border-radius:50%;background:var(--a);animation:_pulse 2.4s infinite}
@keyframes _pulse{0%,100%{opacity:1}50%{opacity:.4}}
/* Hero ambient glow */
.hero::before{content:"";position:absolute;inset:-60% -40%;background:radial-gradient(ellipse 70% 50% at 75% 60%,color-mix(in srgb,var(--a) 18%,transparent),transparent);pointer-events:none;animation:_hg 12s ease-in-out infinite alternate}
@keyframes _hg{from{transform:scale(1) translate(0,0)}to{transform:scale(1.12) translate(3%,2%)}}
/* Better hero heading */
.hero h1{font-size:clamp(2.1rem,1.5rem + 2.8vw,3.5rem)}
/* Cards */
.card{transition:transform .2s ease,box-shadow .2s ease,border-color .25s ease}
.card:hover{border-color:color-mix(in srgb,var(--p) 35%,var(--line));box-shadow:0 20px 44px rgba(15,23,42,.1)}
.ic-badge{background:linear-gradient(135deg,color-mix(in srgb,var(--p) 12%,#fff),color-mix(in srgb,var(--a) 8%,#fff))}
/* Stats */
.stat{transition:transform .2s,box-shadow .2s}.stat:hover{transform:translateY(-3px);box-shadow:0 12px 28px rgba(15,23,42,.08)}
.stat strong{font-size:clamp(1.5rem,1.2rem + 1vw,2rem);font-weight:800;letter-spacing:-.02em}
/* Section dividers */
.sec+.sec.alt::before,.sec.alt+.sec::before{content:"";display:block;height:1px;background:linear-gradient(90deg,transparent,var(--line),transparent);margin-bottom:0}
/* Testimonials */
.quote{border-top:3px solid var(--p)}
/* CTA polish */
.cta::after{width:400px;height:400px;right:-120px;top:-140px;opacity:.07}
/* Nav overlay (mobile) */
.nav-overlay{position:fixed;inset:0;background:rgba(15,23,42,.5);z-index:38;backdrop-filter:blur(4px);opacity:0;pointer-events:none;transition:opacity .22s}
.nav-overlay.open{opacity:1;pointer-events:auto}
/* Back to top */
#btt{position:fixed;right:20px;bottom:88px;z-index:49;width:44px;height:44px;border-radius:12px;background:var(--p);color:#fff;border:none;cursor:pointer;display:grid;place-items:center;font-size:22px;line-height:1;opacity:0;transform:translateY(10px);pointer-events:none;transition:opacity .2s,transform .2s;box-shadow:0 4px 18px color-mix(in srgb,var(--p) 40%,transparent)}
#btt.show{opacity:1;transform:none;pointer-events:auto}#btt:hover{filter:brightness(1.12)}
<?= str_ireplace('</style', '', (string) $d['custom_css']) ?>
</style>
</head>
<body>
<?php if (!empty($preview)) { ?><div class="preview-bar">Preview: this is how the page will look after you save. Nothing has been published yet.</div><?php } ?>
<a class="skip" href="#main">Skip to content</a>
<?php if ($site['announcement'] !== '') { ?><div class="announce"><?= esc($site['announcement']) ?></div><?php } ?>
<?php if ($d['topbar']) { ?><div class="topbar"><div class="wrap">
  <div style="display:flex;flex-wrap:wrap;gap:6px 22px"><?php if ($site['email'] && $d['topbar_email']) { echo Site::email($site['email'], '', '', Site::icon('mail')); } ?><?php if ($site['phone']) { ?><a href="tel:<?= esc($tel) ?>"><?= Site::icon('phone') ?><?= esc($site['phone']) ?></a><?php } ?></div>
  <div style="display:flex;flex-wrap:wrap;gap:6px 22px"><?php if ($site['hours']) { ?><span><?= Site::icon('clock') ?><?= esc(Site::lines($site['hours'])[0] ?? '') ?></span><?php } ?><?php if ($socials && $d['topbar_social']) { ?><span class="top-social"><?php foreach ($socials as $k => $u) { ?><a href="<?= esc($u) ?>" target="_blank" rel="noopener" aria-label="<?= esc(ucfirst($k)) ?>"><?= esc($socialMark[$k]) ?></a><?php } ?></span><?php } ?><a href="<?= esc($clientUrl) ?>"><?= Site::icon('users') ?><?= $user ? 'My workspace' : 'Client portal' ?></a></div>
</div></div><?php } ?>
<header class="nav"><div class="wrap">
  <a class="brand" href="<?= base_url() ?>" aria-label="<?= esc($site['name']) ?> home"><?php if ($logoUrl) { ?><img src="<?= esc($logoUrl) ?>" alt="<?= esc($site['name']) ?> logo" height="<?= (int) $d['logo_height'] ?>"><?php } else { ?><span class="brand-mark"><?= Site::icon('laptop') ?></span><span><?= esc($site['name']) ?><?php if ($site['tagline']) { ?><small><?= esc($site['tagline']) ?></small><?php } ?></span><?php } ?></a>
  <nav class="menu-wrap" id="menu" aria-label="Main"><ul class="menu"><?php foreach ($menu as $m) { $hasSub = !empty($m['children']); ?><li<?= $hasSub ? ' class="has-sub"' : '' ?>><a href="<?= esc(Site::url($m['slug'])) ?>" <?= $m['slug'] === $slug ? 'aria-current="page"' : '' ?>><?= esc($m['menu_label'] ?: $m['title']) ?></a><?php if ($hasSub) { ?><button class="sub-tog" type="button" aria-expanded="false" aria-label="Toggle submenu">▾</button><ul class="sub-menu"><?php foreach ($m['children'] as $c) { $hasSub3 = !empty($c['children'] ?? []); ?><li<?= $hasSub3 ? ' class="has-sub"' : '' ?>><a href="<?= esc(Site::url($c['slug'])) ?>" <?= ($c['slug'] ?? '') === $slug ? 'aria-current="page"' : '' ?>><?= esc($c['menu_label'] ?: $c['title']) ?></a><?php if ($hasSub3) { ?><button class="sub-tog" type="button" aria-expanded="false" aria-label="Toggle submenu">▸</button><ul class="sub-sub-menu"><?php foreach ($c['children'] as $g) { ?><li><a href="<?= esc(Site::url($g['slug'])) ?>" <?= ($g['slug'] ?? '') === $slug ? 'aria-current="page"' : '' ?>><?= esc($g['menu_label'] ?: $g['title']) ?></a></li><?php } ?></ul><?php } ?></li><?php } ?></ul><?php } ?></li><?php } ?>
<li><a href="<?= base_url('products') ?>" <?= $slug === 'products' ? 'aria-current="page"' : '' ?>>APP</a></li>
<li><a href="<?= base_url('extensions') ?>" <?= $slug === 'extensions' ? 'aria-current="page"' : '' ?>>Extensions</a></li>
</ul></nav>
  <div class="nav-actions"><a class="btn btn-o btn-sm" href="<?= esc($portalUrl) ?>"><?= $user ? 'Open workspace' : 'Login' ?></a><?php if ($d['cta_label'] !== '') { ?><a class="btn btn-p btn-sm" href="<?= esc($ctaUrl) ?>"><?= esc($d['cta_label']) ?></a><?php } ?>
  <button class="nav-toggle" type="button" aria-controls="menu" aria-expanded="false" aria-label="Open menu"><svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button></div>
</div></header>
<div class="nav-overlay" id="nav-overlay" aria-hidden="true"></div>

<main id="main">
<?php if (session('site_ok') || session('site_err')) { ?><div class="wrap" style="padding-top:20px"><div class="flash <?= session('site_ok') ? 'ok' : 'err' ?>" role="status"><?= esc(session('site_ok') ?: session('site_err')) ?></div></div><?php } ?>
<?php $alt = false; foreach ($blocks as $i => $b) {
    $kind = Site::BLOCK_KIND[$b['type']] ?? null;
    $list = $kind ? ($items[$kind] ?? []) : [];
    if ($b['type'] === 'partners' && $b['group'] !== '') $list = Site::partners($b['group']);
    $first = $i === 0;
    if ($kind && !empty($b['featured_only'])) $list = array_values(array_filter($list, static fn($it) => (int) $it['featured'] === 1));
    if ($kind && !empty($b['limit'])) $list = array_slice($list, 0, (int) $b['limit']);
    if ($kind && !$list) continue;
    if ($b['type'] === 'hero') { echo view('site/_block', ['b' => $b, 'cls' => '', 'list' => [], 'ctx' => compact('site', 'page', 'slug', 'p', 'a', 'wa', 'waLink', 'tel', 'anchor', 'services', 'items', 'first')]); continue; }
    if ($b['type'] === 'slider') { echo view('site/_block', ['b' => $b, 'cls' => '', 'list' => [], 'ctx' => compact('site', 'page', 'slug', 'p', 'a', 'wa', 'waLink', 'tel', 'anchor', 'services', 'items', 'first')]); continue; }
    if ($b['type'] === 'spacer') { echo '<div class="spacer-' . esc($b['size']) . '" aria-hidden="true"></div>'; continue; }
    if ($b['bg'] === 'auto') { $alt = !$alt; $cls = $b['type'] === 'cta' ? '' : ($alt ? '' : 'alt'); } else { $cls = ['white' => '', 'light' => 'alt', 'brand' => 'brand', 'dark' => 'dark'][$b['bg']]; }
    echo view('site/_block', ['b' => $b, 'cls' => $cls, 'list' => $list, 'ctx' => compact('site', 'page', 'slug', 'p', 'a', 'wa', 'waLink', 'tel', 'anchor', 'services', 'items', 'first')]);
} ?>
</main>

<footer class="foot"><div class="wrap">
  <div class="foot-grid">
    <div>
      <?php if ($logoUrl) { ?><img src="<?= esc($logoUrl) ?>" alt="<?= esc($site['name']) ?>" style="max-height:44px;max-width:180px;object-fit:contain;margin-bottom:14px;display:block"><?php } else { ?><h3 style="font-size:1.1rem;letter-spacing:normal;text-transform:none;margin-bottom:10px"><?= esc($site['name']) ?></h3><?php } ?>
      <p style="opacity:.82;font-size:14px;margin:0 0 14px;max-width:320px"><?= esc($d['footer_about'] ?: ($site['tagline'] ?: 'Reliable computer and ICT solutions.')) ?></p>
      <?php if ($socials) { ?><div class="social"><?php foreach ($socials as $k => $u) { ?><a href="<?= esc($u) ?>" target="_blank" rel="noopener" aria-label="<?= esc(ucfirst($k)) ?>"><?= esc($socialMark[$k] ?? strtoupper($k[0])) ?></a><?php } ?></div><?php } ?>
      <?php if ($d['footer_cta'] || $d['footer_whatsapp']) { ?><div class="foot-cta">
        <?php if ($d['footer_whatsapp'] && $wa !== '') { ?><a class="btn btn-p btn-sm" href="<?= esc($waLink('Hello, I would like a quote')) ?>" target="_blank" rel="noopener" style="gap:8px"><svg viewBox="0 0 24 24" fill="currentColor" style="width:16px;height:16px"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm4.5 12.1c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.2-.4.7-1.3.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.7 11.8 11.8 0 0 0 4.5 4c1.7.7 2.3.8 3.2.6.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.2-1.2-.1-.1-.3-.2-.5-.3z"/></svg> WhatsApp</a><?php } ?>
        <?php if ($d['footer_cta']) { ?><a class="btn btn-o btn-sm" style="border-color:var(--ft-h);color:var(--ft-h)" href="<?= esc(Site::url('contact')) ?>">Get a quote</a><?php } ?>
      </div><?php } ?>
    </div>
    <?php foreach ($d['footer_order'] as $fsec) { switch ($fsec) {
      case 'menu': if (!$d['footer_menu']) break; ?><div><h3>Company</h3><ul><?php foreach ($menu as $m) { ?><li><a href="<?= esc(Site::url($m['slug'])) ?>"><?= esc($m['menu_label'] ?: $m['title']) ?></a></li><?php } ?></ul></div><?php break;
      case 'services': if (!$d['footer_services']) break; $ftrServices = array_slice(Site::items('service'), 0, 6); if (!$ftrServices) break; ?><div><h3>Services</h3><ul><?php foreach ($ftrServices as $s) { ?><li><a href="<?= base_url('services') ?>#<?= esc($anchor($s['title'])) ?>"><?= esc($s['title']) ?></a></li><?php } ?></ul></div><?php break;
      case 'custom': $footLinks = array_filter(array_map(fn($fl) => array_map('trim', explode('|', $fl, 2)), array_filter(array_map('trim', explode("\n", $d['footer_links'] ?? '')))), fn($r) => count($r) === 2 && $r[0] !== '' && $r[1] !== ''); if (!$footLinks) break; ?><div><h3>Quick links</h3><ul><?php foreach ($footLinks as $fl) { ?><li><a href="<?= esc($fl[1]) ?>"><?= esc($fl[0]) ?></a></li><?php } ?></ul></div><?php break;
      case 'contact': if (!$d['footer_contact']) break; ?><div><h3>Contact us</h3><?php if ($site['email'] || $site['phone'] || $site['address'] || $site['hours']) { ?><ul style="list-style:none;padding:0;margin:0 0 14px;display:grid;gap:10px;font-size:14px"><?php if ($site['email']) { ?><li style="display:flex;gap:8px;align-items:center"><?= Site::icon('mail') ?><?= Site::email($site['email']) ?></li><?php } ?><?php if ($site['phone']) { ?><li style="display:flex;gap:8px;align-items:center"><?= Site::icon('phone') ?><a href="tel:<?= esc($tel) ?>"><?= esc($site['phone']) ?></a></li><?php } ?><?php if ($site['address']) { ?><li style="display:flex;gap:8px;align-items:flex-start"><?= Site::icon('map') ?><span style="opacity:.85"><?= nl2br(esc($site['address'])) ?></span></li><?php } ?><?php if ($site['hours']) { ?><li style="display:flex;gap:8px;align-items:flex-start"><?= Site::icon('clock') ?><span style="opacity:.85"><?= nl2br(esc($site['hours'])) ?></span></li><?php } ?></ul><?php } ?><a class="btn btn-o btn-sm" style="border-color:var(--ft-line);color:var(--ft-h)" href="<?= esc($portalUrl) ?>"><?= Site::icon('users') ?> <?= $user ? 'My workspace' : 'Client login' ?></a></div><?php break;
      case 'support': if (!$d['footer_support']) break; ?><div><h3>Get support</h3><p style="font-size:14px;opacity:.82;margin:0 0 12px">Something not working? Submit a request and our team will get back to you promptly.</p><a class="btn btn-p btn-sm" href="<?= esc(Site::url('support')) ?>#support-form" style="display:inline-flex">Submit a request</a><?php if ($site['phone']) { ?><p style="font-size:13px;margin-top:10px;opacity:.8"><a href="tel:<?= esc($tel) ?>" style="color:var(--ft-h)"><?= esc($site['phone']) ?></a></p><?php } ?></div><?php break;
    } } ?>
  </div>
  <div class="foot-bottom">
    <span>© <?= date('Y') ?> <?= esc($site['name']) ?>.<?= $site['footer_note'] ? ' ' . esc($site['footer_note']) : ' All rights reserved.' ?></span>
    <span class="foot-bottom-links"><a href="<?= base_url('products') ?>">APP</a><a href="<?= base_url('extensions') ?>">Extensions</a><a href="<?= base_url('sitemap.xml') ?>">Sitemap</a><a href="<?= esc($portalUrl) ?>"><?= $user ? 'Workspace' : 'Staff login' ?></a></span>
  </div>
</div></footer>
<button id="btt" aria-label="Back to top" title="Back to top">↑</button>
<?php if ($wa !== '') { ?><a class="wa" href="<?= esc($waLink('Hello ' . $site['name'] . ', ')) ?>" target="_blank" rel="noopener" aria-label="Chat on WhatsApp"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.2-.4.7-1.3.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.7 11.8 11.8 0 0 0 4.5 4c1.7.7 2.3.8 3.2.6.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.2-1.2-.1-.1-.3-.2-.5-.3z"/></svg><span>WhatsApp</span></a><?php } ?>
<script>
(()=>{
// Nav toggle + overlay
const t=document.querySelector('.nav-toggle'),m=document.getElementById('menu'),ov=document.getElementById('nav-overlay');
const closeNav=()=>{m?.classList.remove('open');t?.setAttribute('aria-expanded','false');ov?.classList.remove('open');ov?.setAttribute('aria-hidden','true')};
t&&t.addEventListener('click',()=>{const o=m.classList.toggle('open');t.setAttribute('aria-expanded',String(o));ov?.classList.toggle('open',o);ov?.setAttribute('aria-hidden',String(!o));});
ov?.addEventListener('click',closeNav);
document.addEventListener('keydown',e=>{if(e.key==='Escape')closeNav()});
document.querySelectorAll('.sub-tog').forEach(b=>{b.addEventListener('click',e=>{e.preventDefault();const li=b.closest('.has-sub');if(li){const o=li.classList.toggle('open');b.setAttribute('aria-expanded',String(o));}});});
document.querySelectorAll('[data-projects]').forEach(grid=>{const chips=[...grid.parentElement.querySelectorAll('.chip')],cards=[...grid.querySelectorAll('[data-tags]')];chips.forEach(c=>c.addEventListener('click',()=>{const tag=c.dataset.tag;chips.forEach(x=>x.setAttribute('aria-pressed',String(x===c)));cards.forEach(k=>{k.hidden=tag!==''&&!k.dataset.tags.split(',').map(s=>s.trim()).includes(tag);});}));});
document.querySelectorAll('[data-em]').forEach(a=>{const e=atob(a.dataset.em).split('').reverse().join('');a.href='mailto:'+e;if(!a.hasAttribute('data-em-label'))a.querySelector('span').textContent=e;});

// Slider with touch/swipe
document.querySelectorAll('[data-slider]').forEach(sl=>{
  const slides=[...sl.querySelectorAll('.slide')],dots=[...sl.querySelectorAll('.sl-dots button')];
  if(slides.length<2)return;
  let cur=0,timer=null,sx=0;
  const go=n=>{
    slides[cur].classList.remove('is-on');slides[cur].setAttribute('aria-hidden','true');
    dots[cur]?.setAttribute('aria-current','false');
    cur=(n+slides.length)%slides.length;
    slides[cur].classList.add('is-on');slides[cur].removeAttribute('aria-hidden');
    dots[cur]?.setAttribute('aria-current','true');
    slides.forEach((s,i)=>{const v=s.querySelector('video');if(v){if(i===cur)v.play?.().catch(()=>{});else{v.pause?.();v.currentTime=0}}});
  };
  const restart=int=>{clearInterval(timer);if(int>0)timer=setInterval(()=>go(cur+1),int*1000)};
  const int=parseInt(sl.dataset.interval)||0;
  sl.querySelector('.prev')?.addEventListener('click',()=>{go(cur-1);restart(int)});
  sl.querySelector('.next')?.addEventListener('click',()=>{go(cur+1);restart(int)});
  dots.forEach((d,i)=>d.addEventListener('click',()=>{go(i);restart(int)}));
  sl.addEventListener('touchstart',e=>{sx=e.touches[0].clientX},{passive:true});
  sl.addEventListener('touchend',e=>{const dx=e.changedTouches[0].clientX-sx;if(Math.abs(dx)>44){go(dx<0?cur+1:cur-1);restart(int)}});
  if(int>0)timer=setInterval(()=>go(cur+1),int*1000);
});

// Gallery lightbox
(()=>{
  const _e=s=>s.replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;');
  let items=[],cur=0;
  document.querySelectorAll('[data-gallery]').forEach(g=>{
    [...g.querySelectorAll('a[data-full]')].forEach(a=>{
      const idx=items.length;
      items.push({src:a.href,cap:a.closest('figure')?.querySelector('figcaption')?.textContent??''});
      a.addEventListener('click',e=>{e.preventDefault();open(idx)});
    });
  });
  if(!items.length)return;
  const open=i=>{
    cur=i;document.getElementById('lb')?.remove();
    const n=items.length,{src,cap}=items[i];
    const lb=document.createElement('div');lb.id='lb';lb.setAttribute('role','dialog');lb.setAttribute('aria-modal','true');lb.setAttribute('aria-label','Image lightbox');
    lb.innerHTML=`${n>1?`<div id="lb-ctr">${i+1} / ${n}</div>`:''}${n>1?`<button class="lb-arrow lb-prev" aria-label="Previous image">&#8249;</button><button class="lb-arrow lb-next" aria-label="Next image">&#8250;</button>`:''}<img id="lb-img" src="${_e(src)}" alt="${_e(cap)}"><button id="lb-close" aria-label="Close lightbox">&#10005;</button>${cap?`<div id="lb-cap">${_e(cap)}</div>`:''}`;
    document.body.appendChild(lb);document.body.style.overflow='hidden';
    lb.addEventListener('click',e=>{if(e.target===lb||e.target.id==='lb-close')close()});
    lb.querySelector('.lb-prev')?.addEventListener('click',e=>{e.stopPropagation();open((cur-1+n)%n)});
    lb.querySelector('.lb-next')?.addEventListener('click',e=>{e.stopPropagation();open((cur+1)%n)});
    lb.querySelector('#lb-img')?.addEventListener('click',e=>e.stopPropagation());
  };
  const close=()=>{document.getElementById('lb')?.remove();document.body.style.overflow=''};
  document.addEventListener('keydown',e=>{if(!document.getElementById('lb'))return;if(e.key==='Escape')close();if(items.length>1){if(e.key==='ArrowRight')open((cur+1)%items.length);if(e.key==='ArrowLeft')open((cur-1+items.length)%items.length)}});
})();

<?php if (!empty($preview)) { ?>document.querySelectorAll('a').forEach(a=>a.addEventListener('click',e=>{if(!a.getAttribute('href').startsWith('#'))e.preventDefault();}));document.querySelectorAll('form').forEach(f=>f.addEventListener('submit',e=>e.preventDefault()));<?php } ?>

// Scroll reveal
if(window.IntersectionObserver){
  const rv=new IntersectionObserver(entries=>{entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('in');rv.unobserve(e.target)}})},{threshold:.07,rootMargin:'0px 0px -32px 0px'});
  document.querySelectorAll('.card,.stat,.counter,.sec-head,.cta,.faq details').forEach((el,i)=>{el.setAttribute('data-rv','');const d=i%3;if(d===1)el.setAttribute('data-rv-d1','');else if(d===2)el.setAttribute('data-rv-d2','');rv.observe(el)});
}

// Counter animation — stat strong and counters block [data-count]
const animateCount=el=>{
  const raw=el.dataset.count??el.textContent;const n=parseFloat(raw.replace(/,/g,''));if(isNaN(n))return;
  const pre=el.dataset.pre??'',post=el.dataset.post??el.textContent.slice(String(Math.round(n)).length);
  const obs=new IntersectionObserver(entries=>{
    if(!entries[0].isIntersecting)return;obs.unobserve(el);
    const t0=performance.now(),dur=1200;
    const tick=now=>{const p=Math.min(1,(now-t0)/dur),ease=p<.5?2*p*p:(4-2*p)*p-1;
      el.textContent=pre+Math.round(ease*n).toLocaleString()+post;if(p<1)requestAnimationFrame(tick);};
    requestAnimationFrame(tick);
  },{threshold:.5});obs.observe(el);
};
document.querySelectorAll('.stat strong,[data-count]').forEach(animateCount);

// Back to top
const btt=document.getElementById('btt');
window.addEventListener('scroll',()=>btt?.classList.toggle('show',scrollY>500),{passive:true});
btt?.addEventListener('click',()=>scrollTo({top:0,behavior:'smooth'}));
})();
</script>
</body>
</html>
