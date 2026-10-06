<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#0f172a">
<title><?= esc($title ?? 'App') ?> - <?= esc(\App\Models\SettingModel::get('company_name','IT Support')) ?></title>
<link rel="manifest" href="<?= base_url('its-manifest') ?>">
<link rel="stylesheet" href="<?= base_url('assets/tailwind.css') ?>?v=<?= (int) @filemtime(FCPATH . 'assets/tailwind.css') ?>">
<?php
$themePalettes = [
    'ocean' => ['#2563eb', '#1746a2', '#eaf1ff', '#10213a', '#2563eb', 'rgba(37,99,235,.14)'],
    'emerald' => ['#059669', '#047857', '#e7f7ef', '#102b27', '#047857', 'rgba(5,150,105,.14)'],
    'violet' => ['#7c3aed', '#5b21b6', '#f1ebff', '#21153d', '#7040c9', 'rgba(124,58,237,.14)'],
    'sunset' => ['#ea580c', '#c2410c', '#fff1e8', '#382119', '#d24b18', 'rgba(234,88,12,.14)'],
];
$selectedTheme = \App\Models\SettingModel::get('appearance_theme', 'ocean');
$palette = $themePalettes[$selectedTheme] ?? $themePalettes['ocean'];
?>
<script>try{var m=localStorage.getItem('itsupport-mode');if(m==='dark'||(!m&&matchMedia('(prefers-color-scheme: dark)').matches))document.documentElement.dataset.mode='dark'}catch(error){console.error('Could not load appearance preference.',error)}</script>
<style>
*{box-sizing:border-box}
:root{color-scheme:light;--ink:#14243b;--muted:#68788e;--line:#e2e8f0;--paper:#fff;--canvas:#f4f7fb;--brand:<?= $palette[0] ?>;--brand-dark:<?= $palette[1] ?>;--brand-soft:<?= $palette[2] ?>;--banner-start:<?= $palette[3] ?>;--banner-end:<?= $palette[4] ?>;--brand-shadow:<?= $palette[5] ?>;--shadow:0 12px 34px rgba(29,49,77,.075);--ui-scale:1}
html{font-size:100%}
body{margin:0;font-family:Inter,"Segoe UI",Arial,sans-serif;background:radial-gradient(ellipse at 8% 0%,var(--brand-soft) 0,transparent 34%),radial-gradient(ellipse at 96% 12%,rgba(236,72,153,.08) 0,transparent 27%),var(--canvas);color:var(--ink);line-height:1.55;font-size:1rem}
header{position:relative;overflow:hidden;background:linear-gradient(115deg,var(--banner-start),var(--banner-end));color:#fff;padding:20px max(16px,calc((100% - 1240px)/2))}
header:after{position:absolute;content:"";width:250px;height:250px;right:7%;top:-190px;border:1px solid rgba(255,255,255,.15);border-radius:50%;box-shadow:0 0 0 34px rgba(255,255,255,.045),0 0 0 70px rgba(255,255,255,.035)}
.brand{position:relative;z-index:1;display:flex;align-items:center;gap:12px;min-width:0}.brand-mark{display:grid;width:42px;height:42px;place-items:center;flex:0 0 auto;border:1px solid rgba(255,255,255,.25);border-radius:13px;background:rgba(255,255,255,.13);box-shadow:inset 0 1px rgba(255,255,255,.2)}
.brand-copy{min-width:0}.brand h1{margin:0;font-size:19px;font-weight:700;letter-spacing:-.02em;overflow-wrap:anywhere}.brand-caption{margin:2px 0 0;color:rgba(255,255,255,.72);font-size:12px}
nav{position:sticky;top:0;z-index:5;min-height:60px;background:rgba(255,255,255,.9);backdrop-filter:blur(18px);border-bottom:1px solid var(--line);box-shadow:0 4px 18px rgba(15,23,42,.05);padding:8px max(16px,calc((100% - 1240px)/2));display:flex;align-items:center;gap:7px;flex-wrap:wrap}
nav a,nav summary,nav button{flex:0 0 auto;border:0;background:transparent;color:#526278;padding:9px 12px;border-radius:10px;font-size:13px;font-weight:600;text-decoration:none;transition:background .15s,color .15s;cursor:pointer;list-style:none}
nav summary::-webkit-details-marker{display:none}nav summary:after{content:"⌄";margin-inline-start:8px;color:#8996a8;font-size:12px}
nav a:hover,nav summary:hover,nav button.mode-toggle:hover{background:#f0f5fc;color:var(--ink)}nav a[aria-current=page],nav details.is-active>summary{background:var(--brand-soft);color:var(--brand-dark)}
nav .nav-group{position:relative;flex:0 0 auto}nav .nav-menu{position:absolute;top:calc(100% + 7px);inset-inline-start:0;z-index:10;display:grid;min-width:205px;padding:7px;background:var(--paper);border:1px solid var(--line);border-radius:13px;box-shadow:0 14px 36px rgba(15,23,42,.16)}
nav .nav-menu a{padding:10px 11px;border-radius:8px}nav .nav-menu a:hover{background:var(--canvas)}
nav form{margin-inline-start:auto;flex:0 0 auto}nav button.mode-toggle{border:1px solid var(--line);background:var(--paper);color:#526278}
nav form button{background:#fff!important;color:#b42332!important;border:1px solid #f1d6d9!important}nav form button:hover{background:#fff3f3!important;color:#a31928!important}
main{width:min(100%,1272px);min-height:calc(100vh - 205px);margin:0 auto;padding:30px 20px 64px}
.card{background:var(--paper);border:1px solid rgba(220,228,239,.94);border-radius:20px;padding:20px;margin-bottom:17px;box-shadow:var(--shadow);transition:transform .18s ease,box-shadow .18s ease}.card:hover{box-shadow:0 16px 38px rgba(29,49,77,.09)}
.grid{display:grid;grid-template-columns:1fr;gap:14px}@media(min-width:800px){.grid{grid-template-columns:1fr 1fr 1fr}.grid-2{grid-template-columns:1fr 1fr}}
h2,h3,h4{color:var(--ink);letter-spacing:-.025em}h2{font-size:23px}h3{font-size:17px}h4{font-size:14px}p{line-height:1.65}
table{width:100%;border-collapse:separate;border-spacing:0;overflow:hidden}th,td{border:0;border-bottom:1px solid #e9eef5;padding:11px 12px;font-size:13px;text-align:left;vertical-align:top}th{background:#f6f8fc;color:#526278;font-size:11px;letter-spacing:.045em;text-transform:uppercase}tbody tr:last-child td,tr:last-child td{border-bottom:0}tbody tr:hover td{background:#f9fbfe}.tbl{overflow-x:auto;border:1px solid var(--line);border-radius:12px}
label{display:inline-block;color:#34445b;font-size:13px;font-weight:600}
input,select,textarea{width:100%;padding:11px 12px;border:1px solid #d6dfeb;border-radius:10px;background:#fff;color:var(--ink);font:inherit;font-size:14px;margin:5px 0 14px;transition:border .15s,box-shadow .15s}
input:focus,select:focus,textarea:focus{outline:0;border-color:#75a5fa;box-shadow:0 0 0 3px rgba(37,99,235,.12)}textarea{min-height:100px;resize:vertical}
button.btn{border:0;background:linear-gradient(135deg,var(--brand),var(--brand-dark));color:#fff;border-radius:10px;padding:11px 15px;font:inherit;font-size:13px;font-weight:600;box-shadow:0 3px 8px var(--brand-shadow);cursor:pointer;transition:transform .15s,filter .15s}
button.btn:hover,a.button:hover{transform:translateY(-1px);filter:brightness(.97)}button.danger{background:#dc2626}button.secondary{background:#64748b}
a.button{display:inline-block;background:linear-gradient(135deg,var(--brand),var(--brand-dark));color:#fff;border-radius:10px;padding:10px 14px;font-size:13px;font-weight:600;text-decoration:none;box-shadow:0 3px 8px var(--brand-shadow);transition:transform .15s,filter .15s}a.button.secondary{background:#edf2f8;color:#40516a;box-shadow:none}a.button.secondary:hover{background:#e3ebf5}
.badge{display:inline-block;padding:4px 9px;border-radius:999px;font-size:11px;font-weight:700}.badge.gray{background:#edf1f6;color:#526278}.badge.blue{background:var(--brand-soft);color:var(--brand-dark)}.badge.green{background:#e4f7ec;color:#166534}.badge.orange{background:#fff0df;color:#9a4c0b}
.muted{color:var(--muted);font-size:13px}.photo-thumb{width:80px;height:80px;object-fit:cover;border-radius:8px;margin:4px}
.eyebrow{color:var(--brand)}
footer{padding:24px 16px;text-align:center;color:#8290a3;font-size:.78rem}
.ui-toolbar{display:flex;align-items:center;justify-content:flex-end;gap:8px;padding:8px max(16px,calc((100% - 1240px)/2));background:rgba(255,255,255,.68);border-bottom:1px solid rgba(226,232,240,.72)}
.ui-toolbar label{font-size:12px}.ui-toolbar select{width:auto;margin:0;padding:6px 9px;font-size:12px}.text-size-tools{display:flex;align-items:center;gap:4px}.text-size-tools button{border:1px solid var(--line);border-radius:8px;background:var(--paper);color:var(--ink);padding:5px 8px;font-weight:700;cursor:pointer}.text-size-tools button[aria-pressed=true]{background:var(--brand-soft);color:var(--brand-dark);border-color:var(--brand)}
html[data-mode=dark] .ui-toolbar{background:#111b2b;border-color:var(--line)}html[data-mode=dark] .text-size-tools button{background:#172235;color:var(--ink);border-color:var(--line)}html[data-mode=dark] .text-size-tools button[aria-pressed=true]{background:#253855;color:#cfe0ff}
html[dir=rtl] body{text-align:right}html[dir=rtl] nav form{margin-left:0;margin-right:auto}html[dir=rtl] .ui-toolbar{justify-content:flex-start}
.flash{border-radius:14px;padding:13px 16px;margin-bottom:16px;font-size:14px;font-weight:600}.flash-ok{background:#e9f8ef;border:1px solid #c4ebd1;color:#166534}.flash-err{background:#fff0f0;border:1px solid #f1caca;color:#b42332}
@media(max-width:600px){header{padding-top:15px;padding-bottom:15px}.brand h1{font-size:17px}nav{min-height:54px;gap:2px}nav a,nav summary,nav button{padding:8px 10px;font-size:12px}nav .nav-menu{max-width:calc(100vw - 26px)}nav form{margin-inline-start:0}main{padding:20px 13px 48px}.card{padding:16px;border-radius:15px}}
@media(prefers-reduced-motion:reduce){*,*:before,*:after{scroll-behavior:auto!important;transition:none!important;animation:none!important}}
html[data-mode=dark]{color-scheme:dark;--ink:#e5edf8;--muted:#a4b2c5;--line:#334155;--paper:#172235;--canvas:#0b1220;--brand-soft:#1c2d49;--banner-start:#081222;--banner-end:#17263b;--shadow:0 8px 26px rgba(0,0,0,.22)}
html[data-mode=dark] body{background:radial-gradient(ellipse at 8% 0%,#192741 0,transparent 34%),var(--canvas);color:var(--ink)}
html[data-mode=dark] nav{background:rgba(14,23,38,.96);border-color:var(--line)}
html[data-mode=dark] nav a,html[data-mode=dark] nav summary,html[data-mode=dark] nav button.mode-toggle{color:#c4d0df}
html[data-mode=dark] nav a:hover,html[data-mode=dark] nav summary:hover,html[data-mode=dark] nav button.mode-toggle:hover{background:#26354a;color:#fff}
html[data-mode=dark] nav a[aria-current=page],html[data-mode=dark] nav details.is-active>summary{background:#253855;color:#cfe0ff}
html[data-mode=dark] nav button.mode-toggle{border-color:#40516a}
html[data-mode=dark] nav .nav-menu{background:var(--paper);border-color:var(--line)}
html[data-mode=dark] .card,html[data-mode=dark] .network-tabs a,html[data-mode=dark] .form-item{background:var(--paper);border-color:var(--line);color:var(--ink)}
html[data-mode=dark] input,html[data-mode=dark] select,html[data-mode=dark] textarea{background:#101a2b;border-color:#40516a;color:var(--ink)}
html[data-mode=dark] th{background:#202e42;color:#c1cede}
html[data-mode=dark] td{border-color:#2e3b4f}
html[data-mode=dark] tbody tr:hover td{background:#1c2a3e}
html[data-mode=dark] .badge.gray{background:#2b394d;color:#d1d9e5}
html[data-mode=dark] .badge.blue{background:#253855;color:#cfe0ff}
html[data-mode=dark] .badge.green{background:#153b32;color:#9ce0bf}
html[data-mode=dark] .badge.orange{background:#49351f;color:#f3c78b}
html[data-mode=dark] a.button.secondary{background:#26354a;color:#d9e4f2}
html[data-mode=dark] .month-track,html[data-mode=dark] .status-track{background:#29364a}
html[data-mode=dark] .month-count,html[data-mode=dark] .status-label strong{color:#e5edf8}
html[data-mode=dark] .status-label span{color:#c1cede}
html[data-mode=dark] .revealed-secret{background:#4b401f;color:#ffe7a3}
.brand-logo{display:block;max-width:52px;max-height:52px;object-fit:contain;flex:0 0 auto;border-radius:8px}
@media print{header,nav,footer,.no-print{display:none!important}}
/* Modern layer: fluid type, tokens, focus, container-aware grids, mobile-first nav */
:root{--radius:16px;--radius-sm:10px;--ring:0 0 0 3px color-mix(in srgb,var(--brand) 28%,transparent);--space:clamp(14px,2.2vw,22px)}
html{scroll-padding-top:76px;-webkit-text-size-adjust:100%}
body{min-height:100dvh;text-rendering:optimizeLegibility;-webkit-font-smoothing:antialiased}
h1,h2,h3{text-wrap:balance}p,li{text-wrap:pretty}
h2{font-size:clamp(1.3rem,1.1rem + .9vw,1.75rem)}h3{font-size:clamp(1.05rem,1rem + .35vw,1.25rem)}
:focus-visible{outline:2px solid var(--brand);outline-offset:2px;border-radius:6px}
input:focus,select:focus,textarea:focus{border-color:var(--brand);box-shadow:var(--ring)}
.skip-link{position:absolute;inset-inline-start:12px;top:-60px;z-index:100;background:var(--brand);color:#fff;padding:10px 14px;border-radius:var(--radius-sm);font-weight:700;text-decoration:none}.skip-link:focus{top:10px}
header{padding-block:14px}header:after{display:none}
.brand h1{font-size:clamp(1.05rem,1rem + .5vw,1.35rem)}
nav{padding-block:6px;gap:4px;container-type:inline-size}
nav summary,nav a,nav button{min-height:40px;display:inline-flex;align-items:center}
nav .nav-menu{animation:menuIn .14s ease-out;border-radius:var(--radius)}
@keyframes menuIn{from{opacity:0;transform:translateY(-4px)}to{opacity:1;transform:none}}
nav .nav-menu a[aria-current=page]{box-shadow:inset 3px 0 var(--brand)}
main{padding-inline:var(--space)}
.card{border-radius:var(--radius);padding:var(--space)}
.card:hover{transform:none}
.grid{grid-template-columns:repeat(auto-fit,minmax(min(100%,240px),1fr))}
@media(min-width:800px){.grid{grid-template-columns:repeat(auto-fit,minmax(min(100%,240px),1fr))}.grid-2{grid-template-columns:repeat(auto-fit,minmax(min(100%,380px),1fr))}}
.section-heading{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px}.section-heading h2,.section-heading h3,.section-heading p{margin:0}
.eyebrow{font-size:.72rem;font-weight:700;letter-spacing:.09em;text-transform:uppercase}
.tbl{max-width:100%;overscroll-behavior-x:contain;scrollbar-width:thin}
thead th,.tbl th{position:sticky;top:0}
table{font-variant-numeric:tabular-nums}
button,a.button,.btn{min-height:40px}
button:active,a.button:active{transform:scale(.98)}
button:disabled{opacity:.5;cursor:not-allowed;transform:none}
code,pre{font-family:ui-monospace,SFMono-Regular,Consolas,monospace;font-size:.85em}
pre{background:color-mix(in srgb,var(--ink) 6%,var(--paper));border:1px solid var(--line);border-radius:var(--radius-sm);padding:12px}
code{background:color-mix(in srgb,var(--ink) 7%,transparent);padding:1px 6px;border-radius:6px}pre code{background:none;padding:0}
details>summary{cursor:pointer}
input[type=checkbox],input[type=radio]{width:auto;margin:0 6px 0 0;accent-color:var(--brand)}
input[type=file]{padding:9px}
@media(max-width:700px){
 .ui-toolbar{justify-content:space-between;flex-wrap:wrap;gap:6px}
 nav{position:sticky;overflow-x:visible}
 nav .nav-group{position:static}
 nav .nav-menu{position:fixed;inset-inline:10px;top:auto;margin-top:4px;max-width:none;max-height:60dvh;overflow:auto}
 .tbl table{min-width:560px}
 .card{border-radius:14px}
}
@media(prefers-color-scheme:dark){html:not([data-mode=light]){}}
@media(forced-colors:active){.badge{border:1px solid CanvasText}}
@media(min-width:1000px){
body:has(nav[aria-label="Main navigation"]){padding-inline-start:236px}
nav[aria-label="Main navigation"]{position:fixed;inset-block:0;inset-inline-start:0;width:236px;z-index:20;flex-direction:column;align-items:stretch;flex-wrap:nowrap;gap:2px;padding:14px 10px;overflow-y:auto;border-bottom:0;border-inline-end:1px solid var(--line);container-type:normal}
nav[aria-label="Main navigation"] .nav-group{width:100%}
nav[aria-label="Main navigation"] summary{pointer-events:none;font-size:11px;letter-spacing:.08em;text-transform:uppercase;color:#8996a8;padding:12px 10px 4px;min-height:0}
nav[aria-label="Main navigation"] summary:after{display:none}
nav[aria-label="Main navigation"] details::details-content{content-visibility:visible;display:block}
nav[aria-label="Main navigation"] .nav-menu{position:static;display:grid;min-width:0;padding:0;border:0;box-shadow:none;background:transparent;animation:none}
nav[aria-label="Main navigation"] .nav-menu a{padding:9px 10px}
nav[aria-label="Main navigation"]>a,nav[aria-label="Main navigation"]>button{justify-content:flex-start;width:100%}
nav[aria-label="Main navigation"] form{margin:auto 0 0;width:100%}nav[aria-label="Main navigation"] form button{width:100%;justify-content:flex-start}
.nav-extra{display:none!important}
.appbar{position:fixed;inset-block-start:0;inset-inline:236px 0;z-index:15;display:flex;align-items:center;justify-content:flex-end;gap:12px;height:56px;padding:0 24px;background:var(--paper);backdrop-filter:blur(14px);border-bottom:1px solid var(--line)}
body:has(.appbar)>header{margin-block-start:56px}
.ab-bell{margin-inline-start:auto}.ab-search{display:flex;align-items:center;gap:8px;min-width:280px;border:1px solid var(--line);background:var(--canvas);color:var(--muted);padding:8px 12px;border-radius:10px;font-size:13px;cursor:pointer}
.ab-search kbd{margin-inline-start:auto;font:11px monospace;border:1px solid var(--line);border-radius:5px;padding:1px 6px}
.ab-menu{position:relative}.ab-menu>summary{list-style:none;cursor:pointer;display:flex;align-items:center;gap:8px;padding:6px 10px;border-radius:10px}.ab-menu>summary::-webkit-details-marker{display:none}.ab-menu>summary:hover{background:var(--brand-soft)}
.ab-bell>summary{position:relative;font-size:18px}.ab-dot{position:absolute;top:0;inset-inline-end:0;min-width:18px;padding:0 5px;border-radius:9px;background:#dc2626;color:#fff;font:700 11px/18px sans-serif;text-align:center}
.ab-avatar{display:grid;place-items:center;width:30px;height:30px;border-radius:50%;background:var(--brand);color:#fff;font-weight:700;font-size:13px}.ab-name{font-size:13px;font-weight:600}
.ab-panel{position:absolute;inset-inline-end:0;top:calc(100% + 6px);z-index:30;display:grid;gap:2px;width:320px;max-height:70vh;overflow:auto;padding:10px;background:var(--paper);border:1px solid var(--line);border-radius:14px;box-shadow:0 18px 44px rgba(15,23,42,.18)}
.ab-user .ab-panel{width:220px}.ab-panel strong{padding:4px 8px}.ab-panel small{display:block;color:var(--muted);padding:0 8px}
.ab-panel a,.ab-panel button{display:block;text-align:start;width:100%;padding:8px;border:0;border-radius:8px;background:transparent;color:var(--ink);font:inherit;font-size:13px;text-decoration:none;cursor:pointer}.ab-panel a:hover,.ab-panel button:hover{background:var(--brand-soft)}
.ab-panel p{padding:8px;margin:0}
nav[aria-label="Main navigation"]{padding-top:18px}
nav[aria-label="Main navigation"] .nav-group:first-child summary{padding-top:0}
nav[aria-label="Main navigation"] details.is-active>summary{background:transparent!important;color:#8996a8!important}
}
@media(max-width:999px){.appbar{display:none}.tabbar{position:fixed;inset-inline:0;bottom:0;z-index:25;display:flex;background:var(--paper);border-top:1px solid var(--line);padding-bottom:env(safe-area-inset-bottom)}.tabbar a,.tabbar button{flex:1;display:flex;flex-direction:column;align-items:center;gap:2px;padding:8px 2px;border:0;background:transparent;color:var(--muted);font:600 11px sans-serif;text-decoration:none}.tabbar span{font-size:18px}.tabbar [aria-current=page]{color:var(--brand-dark)}body:has(.tabbar){padding-bottom:62px}}
@media(min-width:1000px){.tabbar{display:none}}
@media(prefers-reduced-motion:reduce){*{scroll-behavior:auto!important}}
</style></head><body>
<a class="skip-link" href="#main">Skip to content</a>
<?php
$companyName = \App\Models\SettingModel::get('company_name', 'IT Support');
$companyLogo = \App\Models\SettingModel::get('company_logo', '');
$headerTagline = \App\Models\SettingModel::get('header_tagline', 'IT support workspace');
?>
<header><div class="brand"><?php if ($companyLogo !== '') { ?><img class="brand-logo" src="<?= esc(base_url($companyLogo)) ?>" alt="<?= esc($companyName) ?> logo"><?php } else { ?><span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 24 24" width="23" height="23" fill="none"><path d="M12 2.8 20 6v5.5c0 4.6-3.2 8-8 9.7-4.8-1.7-8-5.1-8-9.7V6l8-3.2Z" stroke="currentColor" stroke-width="1.7"/><path d="M8 12h2.3l1.3-2.4 1.8 4.8 1.2-2.4H17" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"/></svg></span><?php } ?><div class="brand-copy"><h1><?= esc($companyName) ?></h1><?php if ($headerTagline !== '') { ?><p class="brand-caption"><?= esc($headerTagline) ?></p><?php } ?></div></div></header>
<div class="ui-toolbar" aria-label="Display preferences">
<label for="ui-language" data-i18n="Language">Language</label><select id="ui-language" aria-label="Language"><option value="en">🇬🇧 English</option><option value="fr">🇫🇷 Français</option><option value="es">🇪🇸 Español</option><option value="sw">🇰🇪 Kiswahili</option><option value="ar">🇸🇦 العربية</option><option value="zh">🇨🇳 中文</option></select>
<span class="text-size-tools" role="group" aria-label="Text size"><span class="muted" data-i18n="Text">Text</span><button type="button" data-text-size="small" aria-label="Small text">A−</button><button type="button" data-text-size="medium" aria-label="Medium text">A</button><button type="button" data-text-size="large" aria-label="Large text">A+</button><button type="button" data-text-size="xlarge" aria-label="Extra large text">A++</button></span>
</div>
<?php $u = session('its_user'); if ($u) { ?>
<?php
$activeRoute = service('uri')->getSegment(1);
$navGroups = [
    ['label' => 'Work', 'items' => [
        ['label' => 'Dashboard', 'url' => 'its-dashboard', 'active' => $activeRoute === 'its-dashboard'],
        ['label' => 'Tickets', 'url' => 'its-tickets', 'active' => str_starts_with($activeRoute, 'its-tickets') || $activeRoute === 'its-report'],
        ['label' => 'Board', 'url' => 'its-board', 'active' => $activeRoute === 'its-board'],
        ['label' => 'Quotes', 'url' => 'its-quotes', 'active' => str_starts_with($activeRoute, 'its-quotes')],
    ]],
    ['label' => 'Customers', 'items' => [
        ['label' => 'Clients', 'url' => 'its-clients', 'active' => str_starts_with($activeRoute, 'its-clients')],
        ['label' => 'Assets', 'url' => 'its-assets', 'active' => str_starts_with($activeRoute, 'its-assets')],
    ]],
    ['label' => 'IT Operations', 'items' => [
        ['label' => 'Network', 'url' => 'its-network', 'active' => $activeRoute === 'its-network'],
        ['label' => 'Forms', 'url' => 'its-forms', 'active' => str_starts_with($activeRoute, 'its-form')],
    ]],
];
if (($u['role'] ?? '') === 'admin') {
    $navGroups[] = ['label' => 'Insights & Admin', 'items' => [
        ['label' => 'Reports', 'url' => 'its-report-builder', 'active' => str_starts_with($activeRoute, 'its-report-builder')],
        ['label' => 'Admin', 'url' => 'its-admin', 'active' => str_starts_with($activeRoute, 'its-admin')],
        ['label' => 'Updates', 'url' => 'its-updates', 'active' => str_starts_with($activeRoute, 'its-updates')],
        ['label' => 'Billing', 'url' => 'its-billing', 'active' => str_starts_with($activeRoute, 'its-billing')],
        ['label' => 'Health', 'url' => 'its-health', 'active' => $activeRoute === 'its-health'],
    ]];
}
$navGroups[] = ['label' => 'Resources', 'items' => [
    ['label' => 'Knowledge Base', 'url' => 'its-kb', 'active' => str_starts_with($activeRoute, 'its-kb')],
    ['label' => 'Security', 'url' => 'its-security', 'active' => str_starts_with($activeRoute, 'its-security')],
    ['label' => 'Features', 'url' => 'its-features', 'active' => $activeRoute === 'its-features'],
    ['label' => 'Help', 'url' => 'its-help', 'active' => $activeRoute === 'its-help'],
]];
if (($u['role'] ?? '') === 'client') {
$navGroups = [['label' => 'My Support', 'items' => [
    ['label' => 'My Tickets', 'url' => 'its-portal', 'active' => in_array($activeRoute, ['its-portal', 'its-portal-ticket'], true)],
    ['label' => 'Report an Issue', 'url' => 'its-portal-new', 'active' => $activeRoute === 'its-portal-new'],
    ['label' => 'Help articles', 'url' => 'its-portal-kb', 'active' => $activeRoute === 'its-portal-kb'],
]]];
}
$navMod = ['its-dashboard' => 'dashboard', 'its-tickets' => 'tickets', 'its-board' => 'board', 'its-quotes' => 'quotes', 'its-clients' => 'clients', 'its-assets' => 'assets', 'its-network' => 'network', 'its-forms' => 'forms', 'its-report-builder' => 'reports', 'its-kb' => 'kb'];
$navRole = (string) ($u['role'] ?? '');
foreach ($navGroups as $gi => $g) {
    $navGroups[$gi]['items'] = array_values(array_filter($g['items'], static fn(array $i): bool => !isset($navMod[$i['url']]) || \App\Libraries\Perm::can($navRole, $navMod[$i['url']], 1)));
    if (!$navGroups[$gi]['items']) unset($navGroups[$gi]);
}
$alerts = 0; $alertTickets = []; $alertDevices = [];
if ($navRole !== 'client') {
    try {
        $db = \Config\Database::connect();
        $alertTickets = $db->table('tickets')->select('id,ticket_number,subject,due_at')->whereNotIn('status', ['completed', 'closed'])->where('due_at <', date('Y-m-d H:i:s'))->orderBy('due_at', 'ASC')->limit(6)->get()->getResultArray();
        $alertDevices = $db->table('network_devices')->select('id,name,ip_address')->where('monitor_enabled', 1)->where('monitor_state', 'offline')->limit(6)->get()->getResultArray();
        $alerts = $db->table('tickets')->whereNotIn('status', ['completed', 'closed'])->where('due_at <', date('Y-m-d H:i:s'))->countAllResults()
            + $db->table('network_devices')->where('monitor_enabled', 1)->where('monitor_state', 'offline')->countAllResults();
    } catch (\Throwable $e) { $alerts = 0; }
}
?>
<nav aria-label="Main navigation">
<?php foreach ($navGroups as $group) { $groupActive = count(array_filter($group['items'], static fn(array $item): bool => $item['active'])) > 0; ?>
<details class="nav-group<?= $groupActive ? ' is-active' : '' ?>"><summary data-i18n="<?= esc($group['label']) ?>"><?= esc($group['label']) ?></summary><div class="nav-menu">
<?php foreach ($group['items'] as $item) { ?><a href="<?= base_url($item['url']) ?>" <?= $item['active'] ? 'aria-current="page"' : '' ?> data-i18n="<?= esc($item['label']) ?>"><?= esc($item['label']) ?></a><?php } ?>
</div></details>
<?php } ?>
<?php if ($navRole !== 'client') { ?><button type="button" id="cmd-open" class="nav-extra" aria-label="Search (Ctrl+K)" title="Search (Ctrl+K)">🔍 <span data-i18n="Search">Search</span></button>
<a class="nav-extra" href="<?= base_url('its-tickets') ?>" aria-label="<?= $alerts ?> alerts" title="Overdue tickets and devices down">🔔<?php if ($alerts) { ?> <span class="badge red"><?= $alerts ?></span><?php } ?></a><?php } ?>
<button type="button" class="mode-toggle nav-extra" data-mode-toggle aria-pressed="false" aria-label="Switch to dark mode" data-i18n="Dark mode">Dark mode</button>
<form class="nav-extra" method="post" action="<?= base_url('its-logout') ?>"><?= csrf_field() ?><button data-i18n="Logout">Logout</button></form>
</nav>
<?php if ($navRole !== 'client') { ?><div class="tabbar" role="navigation" aria-label="Quick navigation"><a href="<?= base_url('its-dashboard') ?>" <?= $activeRoute === 'its-dashboard' ? 'aria-current="page"' : '' ?>><span aria-hidden="true">🏠</span>Home</a><a href="<?= base_url('its-tickets') ?>" <?= str_starts_with($activeRoute, 'its-tickets') ? 'aria-current="page"' : '' ?>><span aria-hidden="true">🎫</span>Tickets</a><a href="<?= base_url('its-board') ?>"><span aria-hidden="true">🗂</span>Board</a><a href="<?= base_url('its-assets') ?>"><span aria-hidden="true">💻</span>Assets</a><button type="button" data-cmd-open><span aria-hidden="true">🔍</span>Search</button></div><?php } ?>
<div class="appbar">
<?php if ($navRole !== 'client') { ?><button type="button" class="ab-search" data-cmd-open aria-label="Search (Ctrl+K)"><span aria-hidden="true">🔍</span> <span data-i18n="Search">Search</span> <kbd>Ctrl K</kbd></button><?php } else { ?><span></span><?php } ?>
<?php if ($navRole !== 'client') { ?><details class="ab-menu ab-bell"><summary aria-label="<?= $alerts ?> notifications"><span aria-hidden="true">🔔</span><?php if ($alerts) { ?><span class="ab-dot"><?= $alerts > 99 ? '99+' : $alerts ?></span><?php } ?></summary>
<div class="ab-panel" role="region" aria-label="Notifications"><strong>Notifications</strong>
<?php foreach ($alertTickets as $at) { ?><a href="<?= base_url('its-tickets-view/' . $at['id']) ?>"><span class="badge red">Overdue</span> <?= esc($at['ticket_number']) ?> <?= esc(mb_strimwidth((string) $at['subject'], 0, 38, '…')) ?><small>due <?= esc($at['due_at']) ?></small></a><?php } ?>
<?php foreach ($alertDevices as $ad) { ?><a href="<?= base_url('its-network') ?>"><span class="badge red">Offline</span> <?= esc($ad['name']) ?><small><?= esc($ad['ip_address']) ?></small></a><?php } ?>
<?php if (!$alerts) { ?><p class="muted">You are all caught up.</p><?php } elseif ($alerts > count($alertTickets) + count($alertDevices)) { ?><a href="<?= base_url('its-tickets') ?>">View all <?= $alerts ?> alerts</a><?php } ?></div></details><?php } ?>
<details class="ab-menu ab-user"><summary><span class="ab-avatar" aria-hidden="true"><?= esc(mb_strtoupper(mb_substr((string) ($u['name'] ?? $u['username'] ?? 'U'), 0, 1))) ?></span><span class="ab-name"><?= esc($u['name'] ?? $u['username' ] ?? '') ?></span></summary>
<div class="ab-panel"><strong><?= esc($u['name'] ?? $u['username'] ?? '') ?></strong><small><?= esc(ucfirst($navRole)) ?></small>
<?php if ($navRole !== 'client') { ?><a href="<?= base_url('its-security') ?>">Security &amp; 2FA</a><a href="<?= base_url('its-help') ?>">Help</a><?php } ?>
<button type="button" data-mode-toggle aria-pressed="false" data-i18n="Dark mode">Dark mode</button>
<form method="post" action="<?= base_url('its-logout') ?>"><?= csrf_field() ?><button data-i18n="Logout">Logout</button></form></div></details>
</div>
<?php } ?>
<main id="main" tabindex="-1">
<?php if (session('ok')) { ?><div class="flash flash-ok" role="status"><?= esc(session('ok')) ?></div><?php } ?>
<?php if (session('err')) { ?><div class="flash flash-err" role="alert"><?= esc(session('err')) ?></div><?php } ?>