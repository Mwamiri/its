<?php
use App\Libraries\Site;
extract($ctx, EXTR_SKIP);
$t = static fn(string $k, string $def = '') => trim((string) ($b[$k] ?? '')) !== '' ? (string) $b[$k] : $def;
$id = $b['anchor'] !== '' ? ' id="' . esc($b['anchor']) . '"' : '';
$head = static function (string $eyebrow, string $title, string $sub) { ?>
<div class="sec-head"><?php if ($eyebrow !== '') { ?><p class="eyebrow"><?= esc($eyebrow) ?></p><?php } ?><?php if ($title !== '') { ?><h2><?= esc($title) ?></h2><?php } ?><?php if ($sub !== '') { ?><p><?= esc($sub) ?></p><?php } ?></div>
<?php };
$img = static fn(string $path, string $alt, string $icon, int $seed) => $path !== '' ? '<img src="' . esc(base_url($path)) . '" alt="' . esc($alt) . '" loading="lazy">' : Site::cover($icon, $p, $a, $seed);

switch ($b['type']) {
case 'hero':
    $split = $b['style'] === 'split'; $bgHero = $b['style'] === 'background'; ?>
<section class="hero <?= $split ? 'split-hero' : 'center' ?><?= $bgHero ? ' bg-hero' : '' ?>"<?= $id ?>>
<?php if ($bgHero) { ?><div class="bg-media" aria-hidden="true"><?php if ($t('video') !== '') { ?><video autoplay muted loop playsinline preload="metadata"<?= $t('image') !== '' ? ' poster="' . esc(base_url($b['image'])) . '"' : '' ?>><source src="<?= esc(base_url($b['video'])) ?>" type="video/<?= str_ends_with(strtolower($b['video']), '.webm') ? 'webm' : 'mp4' ?>"></video><?php } elseif ($t('image') !== '') { ?><img src="<?= esc(base_url($b['image'])) ?>" alt="" fetchpriority="high"><?php } ?></div><?php } ?>
<div class="wrap">
  <div>
    <?php if (!$split && $slug !== 'home') { ?><p class="eyebrow"><?= esc($page['title']) ?></p><?php } elseif ($site['tagline'] && $slug === 'home') { ?><p class="eyebrow"><?= esc($site['tagline']) ?></p><?php } ?>
    <h1><?= esc($t('title', $page['title'])) ?></h1>
    <?php if ($t('subtitle') !== '') { ?><p class="lead"><?= esc($b['subtitle']) ?></p><?php } ?>
    <div class="hero-cta">
      <?php if ($t('button_label') !== '') { ?><a class="btn btn-p" href="<?= esc(Site::link($t('button_url', 'contact'))) ?>"><?= esc($b['button_label']) ?></a><?php } ?>
      <?php if ($t('button2_label') !== '') { ?><a class="btn btn-o" href="<?= esc(Site::link($t('button2_url', 'services'))) ?>"><?= esc($b['button2_label']) ?></a><?php } ?>
      <?php if ($wa !== '' && in_array($slug, ['home', 'contact', 'support'], true)) { ?><a class="btn btn-o" href="<?= esc($waLink('Hello ' . $site['name'] . ', I would like help with ')) ?>" target="_blank" rel="noopener">WhatsApp us</a><?php } ?>
    </div>
    <?php if (!empty($b['show_trust'])) { $stats = Site::items('stat'); if ($stats) { ?><div class="trust"><?php foreach (array_slice($stats, 0, 4) as $s) { ?><span><?= Site::icon('check') ?><?= esc($s['title']) ?></span><?php } ?></div><?php } } ?>
  </div>
  <?php if ($split) { ?><div><?= $t('image') !== '' ? '<img class="hero-img" src="' . esc(base_url($b['image'])) . '" alt="' . esc($t('title', $site['name'])) . '" width="640" height="480">' : Site::heroArt($p, $a) ?></div><?php } ?>
</div></section>
<?php break;

case 'text': ?>
<section class="sec <?= $cls ?>"<?= $id ?>><div class="wrap prose<?= $b['align'] === 'center' ? ' center' : '' ?>"><?php if ($t('title') !== '') { ?><h2><?= esc($b['title']) ?></h2><?php } ?><?= Site::md($b['text']) ?></div></section>
<?php break;

case 'image_text': ?>
<section class="sec <?= $cls ?>"<?= $id ?>><div class="wrap split<?= $b['image_side'] === 'right' ? ' img-right' : '' ?>">
  <div class="split-img"><?= $t('image') !== '' ? '<img src="' . esc(base_url($b['image'])) . '" alt="' . esc($t('title', $site['name'])) . '" loading="lazy">' : Site::heroArt($p, $a) ?></div>
  <div class="prose" style="margin:0"><?php if ($t('title') !== '') { ?><h2><?= esc($b['title']) ?></h2><?php } ?><?= Site::md($b['text']) ?><?php if ($t('button_label') !== '') { ?><p><a class="btn btn-p" href="<?= esc(Site::link($t('button_url', 'contact'))) ?>"><?= esc($b['button_label']) ?></a></p><?php } ?></div>
</div></section>
<?php break;

case 'services': ?>
<section class="sec <?= $cls ?>"<?= $b['anchor'] !== '' ? $id : ' id="services"' ?>><div class="wrap"><?php $head('What we do', $t('title', $slug === 'home' ? 'Our services' : 'Everything you need in one place'), $t('subtitle', 'Practical technology services delivered by an experienced team.')); ?>
<div class="grid"><?php foreach ($list as $it) { ?><article class="card" id="<?= esc($anchor($it['title'])) ?>"><?php if ($it['image_path']) { ?><div class="media"><img src="<?= esc(base_url($it['image_path'])) ?>" alt="<?= esc($it['title']) ?>" loading="lazy"></div><?php } else { ?><span class="ic-badge"><?= Site::icon($it['icon']) ?></span><?php } ?><h3><?= esc($it['title']) ?></h3><?= Site::md($it['body']) ?>
<?php $bl = !empty($b['show_bullets']) ? Site::lines($it['bullets']) : []; if ($bl) { ?><ul class="ticks"><?php foreach ($slug === 'home' ? array_slice($bl, 0, 3) : $bl as $li) { ?><li><?= esc($li) ?></li><?php } ?></ul><?php } ?>
<div class="card-actions"><?php if ($slug !== 'services') { ?><a href="<?= base_url('services') ?>#<?= esc($anchor($it['title'])) ?>">Learn more →</a><?php } else { ?><a class="btn btn-o btn-sm" href="<?= base_url('contact') ?>?service=<?= rawurlencode($it['title']) ?>#contact-form">Request this service</a><?php } ?></div></article><?php } ?></div>
<?php if ($slug !== 'services' && ($slug === 'home' || !empty($b['limit']))) { ?><p style="text-align:center;margin-top:28px"><a class="btn btn-p" href="<?= base_url('services') ?>">View all services</a></p><?php } ?></div></section>
<?php break;

case 'solutions': ?>
<section class="sec <?= $cls ?>"<?= $b['anchor'] !== '' ? $id : ' id="solutions"' ?>><div class="wrap"><?php $head('Sectors we serve', $t('title', 'Solutions built around your organisation'), $t('subtitle')); ?>
<div class="grid"><?php foreach ($list as $it) { ?><article class="card"><span class="ic-badge"><?= Site::icon($it['icon']) ?></span><h3><?= esc($it['title']) ?></h3><?= Site::md($it['body']) ?><?php $bl = Site::lines($it['bullets']); if ($bl) { ?><p style="margin:.2em 0 .4em;font-weight:700;color:var(--ink)">Ideal for / includes</p><ul class="ticks"><?php foreach ($bl as $li) { ?><li><?= esc($li) ?></li><?php } ?></ul><?php } ?></article><?php } ?></div></div></section>
<?php break;

case 'products': ?>
<section class="sec <?= $cls ?>"<?= $b['anchor'] !== '' ? $id : ' id="products"' ?>><div class="wrap"><?php $head('Shop', $t('title', 'Products we supply'), $t('subtitle', 'Genuine products from trusted brands. Ask us for current prices and availability.')); ?>
<div class="grid"><?php foreach ($list as $it) { ?><article class="card"><div class="media"><?= $img((string) $it['image_path'], $it['title'], (string) $it['icon'], (int) $it['id']) ?></div><h3><?= esc($it['title']) ?></h3><?= Site::md($it['body']) ?><?php if ($it['price']) { ?><p class="price"><?= esc($it['price']) ?></p><?php } ?>
<div class="card-actions"><?php if ($wa !== '') { ?><a class="btn btn-a btn-sm" href="<?= esc($waLink('Hello, I am interested in: ' . $it['title'])) ?>" target="_blank" rel="noopener">Ask on WhatsApp</a><?php } ?><a class="btn btn-o btn-sm" href="<?= base_url('contact') ?>?service=<?= rawurlencode('Product: ' . $it['title']) ?>#contact-form">Enquire</a></div></article><?php } ?></div></div></section>
<?php break;

case 'projects': $tags = []; foreach ($list as $it) foreach (array_filter(array_map('trim', explode(',', (string) $it['tags']))) as $tg) $tags[$tg] = true; ?>
<section class="sec <?= $cls ?>"<?= $b['anchor'] !== '' ? $id : ' id="projects"' ?>><div class="wrap"><?php $head('Case studies', $t('title', 'Recent projects'), $t('subtitle', 'The challenge, what we did, and the result.')); ?>
<?php if (!empty($b['filters']) && count($tags) > 1) { ?><div class="chips" role="group" aria-label="Filter projects"><button class="chip" type="button" data-tag="" aria-pressed="true">All</button><?php foreach (array_keys($tags) as $tg) { ?><button class="chip" type="button" data-tag="<?= esc($tg) ?>" aria-pressed="false"><?= esc($tg) ?></button><?php } ?></div><?php } ?>
<div class="grid" data-projects><?php foreach ($list as $it) { ?><article class="card" data-tags="<?= esc((string) $it['tags']) ?>"><div class="media"><?= $img((string) $it['image_path'], $it['title'], (string) $it['icon'], (int) $it['id'] + 7) ?></div><?php if ($it['subtitle']) { ?><span class="tag"><?= esc($it['subtitle']) ?></span><?php } ?><h3><?= esc($it['title']) ?></h3><?= Site::md($it['body']) ?></article><?php } ?></div>
<?php if ($slug !== 'projects' && ($slug === 'home' || !empty($b['limit']))) { ?><p style="text-align:center;margin-top:28px"><a class="btn btn-o" href="<?= base_url('projects') ?>">More case studies</a></p><?php } ?></div></section>
<?php break;

case 'testimonials': ?>
<section class="sec <?= $cls ?>"<?= $id ?>><div class="wrap"><?php $head('Testimonials', $t('title', 'What our clients say'), $t('subtitle')); ?>
<div class="grid"><?php foreach ($list as $it) { ?><blockquote class="card quote"><?= Site::icon('quote') ?><?= Site::md($it['body']) ?><footer><?= esc($it['title']) ?><?php if ($it['subtitle']) { ?><span><?= esc($it['subtitle']) ?></span><?php } ?></footer></blockquote><?php } ?></div></div></section>
<?php break;

case 'values': ?>
<section class="sec <?= $cls ?>"<?= $id ?>><div class="wrap"><?php $head('What drives us', $t('title', 'Our core values'), $t('subtitle')); ?>
<div class="grid"><?php foreach ($list as $it) { ?><article class="card"><span class="ic-badge"><?= Site::icon($it['icon']) ?></span><h3><?= esc($it['title']) ?></h3><?= Site::md($it['body']) ?></article><?php } ?></div></div></section>
<?php break;

case 'stats': ?>
<section class="sec <?= $cls ?>"<?= $id ?>><div class="wrap"><?php $head('Why choose us', $t('title', 'Why clients choose ' . $site['name']), $t('subtitle')); ?>
<div class="stats"><?php foreach ($list as $it) { ?><div class="stat"><?= Site::icon($it['icon'] ?: 'check') ?><div><?php if ($it['subtitle']) { ?><strong><?= esc($it['subtitle']) ?></strong><?php } ?><?= esc($it['title']) ?></div></div><?php } ?></div></div></section>
<?php break;

case 'packages': ?>
<section class="sec <?= $cls ?>"<?= $b['anchor'] !== '' ? $id : ' id="packages"' ?>><div class="wrap"><?php $head('Plans', $t('title', 'Support packages'), $t('subtitle', 'Pricing depends on your requirements. Contact us for a custom quote.')); ?>
<div class="grid"><?php foreach ($list as $i => $it) { ?><article class="card pkg<?= $i === 1 ? ' featured' : '' ?>"><h3><?= esc($it['title']) ?></h3><?= Site::md($it['body']) ?><?php $bl = Site::lines($it['bullets']); if ($bl) { ?><ul class="ticks"><?php foreach ($bl as $li) { ?><li><?= esc($li) ?></li><?php } ?></ul><?php } ?><?php if ($it['price']) { ?><p class="price"><?= esc($it['price']) ?></p><?php } ?><div class="card-actions"><a class="btn <?= $i === 1 ? 'btn-p' : 'btn-o' ?> btn-sm" href="<?= base_url('contact') ?>?service=<?= rawurlencode('Package: ' . $it['title']) ?>#contact-form">Choose <?= esc($it['title']) ?></a></div></article><?php } ?></div></div></section>
<?php break;

case 'faq': ?>
<section class="sec <?= $cls ?>"<?= $b['anchor'] !== '' ? $id : ' id="faq"' ?>><div class="wrap"><?php $head('FAQ', $t('title', 'Frequently asked questions'), $t('subtitle')); ?>
<div class="faq"><?php foreach ($list as $it) { ?><details><summary><?= esc($it['title']) ?></summary><?= Site::md($it['body']) ?></details><?php } ?></div></div></section>
<?php break;

case 'partners':
    $logo = static function (array $it) use ($b): string {
        $inner = $it['image_path'] ? '<img src="' . esc(base_url($it['image_path'])) . '" alt="' . esc($it['title']) . '" loading="lazy">' : '<span class="logo-name">' . esc($it['title']) . '</span>';
        if (!empty($b['show_names']) && $it['image_path']) $inner .= '<small>' . esc($it['title']) . '</small>';
        $url = (string) ($it['link_url'] ?? '');
        $tip = esc($it['title'] . ($it['subtitle'] ? ' (' . $it['subtitle'] . ')' : ''));
        return preg_match('#^https?://#i', $url) ? '<a class="logo" href="' . esc($url) . '" target="_blank" rel="noopener" title="' . $tip . '">' . $inner . '</a>' : '<div class="logo" title="' . $tip . '">' . $inner . '</div>';
    };
    $logoCls = 'logos' . (!empty($b['colour']) ? ' in-colour' : '') . (!empty($b['show_names']) ? ' named' : ''); ?>
<section class="sec <?= $cls ?>"<?= $b['anchor'] !== '' ? $id : ' id="partners"' ?>><div class="wrap"><?php $head($b['group'] !== '' ? $b['group'] : 'Trusted by', $t('title', 'Our clients and partners'), $t('subtitle')); ?>
<?php if ($b['style'] === 'slider' && count($list) > 2) { ?>
<div class="marquee <?= esc($b['speed']) ?>" style="--n:<?= count($list) ?>"><div class="<?= $logoCls ?> marquee-track"><?php foreach ($list as $it) echo $logo($it); ?><?php foreach ($list as $it) echo str_replace(['<a class="logo"', '<div class="logo"'], ['<a class="logo" tabindex="-1" aria-hidden="true"', '<div class="logo" aria-hidden="true"'], $logo($it)); ?></div></div>
<?php } else { ?><div class="<?= $logoCls ?>"><?php foreach ($list as $it) echo $logo($it); ?></div><?php } ?>
<?php if ($slug !== 'partners' && Site::hasPage('partners')) { ?><p style="text-align:center;margin-top:22px"><a href="<?= base_url('partners') ?>">See all our clients and partners →</a></p><?php } ?></div></section>
<?php break;

case 'slider': $slides = $b['slides']; if (!$slides) break; ?>
<section class="slider h-<?= esc($b['height']) ?> ov-<?= esc($b['overlay']) ?> al-<?= esc($b['align']) ?>"<?= $id ?> aria-roledescription="carousel" aria-label="<?= esc($site['name']) ?> highlights" data-slider data-interval="<?= (int) $b['interval'] ?>">
<div class="slides">
<?php foreach ($slides as $i => $s) { $hTag = $i === 0 && !empty($first) ? 'h1' : 'h2'; ?><div class="slide<?= $i === 0 ? ' is-on' : '' ?>" role="group" aria-roledescription="slide" aria-label="<?= $i + 1 ?> of <?= count($slides) ?>"<?= $i ? ' aria-hidden="true"' : '' ?>>
  <div class="bg-media" aria-hidden="true"><?php if ($s['video'] !== '') { ?><video muted loop playsinline preload="<?= $i ? 'none' : 'metadata' ?>"<?= $s['image'] !== '' ? ' poster="' . esc(base_url($s['image'])) . '"' : '' ?>><source src="<?= esc(base_url($s['video'])) ?>" type="video/<?= str_ends_with(strtolower($s['video']), '.webm') ? 'webm' : 'mp4' ?>"></video><?php } elseif ($s['image'] !== '') { ?><img src="<?= esc(base_url($s['image'])) ?>" alt="" <?= $i ? 'loading="lazy"' : 'fetchpriority="high"' ?>><?php } else { echo Site::cover('spark', $p, $a, $i + 3); } ?></div>
  <?php if ($s['title'] !== '' || $s['text'] !== '' || $s['button_label'] !== '') { ?><div class="wrap slide-copy"><div><?php if ($s['title'] !== '') { ?><<?= $hTag ?> class="slide-title"><?= esc($s['title']) ?></<?= $hTag ?>><?php } ?><?php if ($s['text'] !== '') { ?><p class="lead"><?= nl2br(esc($s['text'])) ?></p><?php } ?><?php if ($s['button_label'] !== '') { ?><div class="hero-cta"><a class="btn btn-p" href="<?= esc(Site::link($s['button_url'] ?: 'contact')) ?>"<?= $i ? ' tabindex="-1"' : '' ?>><?= esc($s['button_label']) ?></a></div><?php } ?></div></div><?php } ?>
</div><?php } ?>
</div>
<?php if (count($slides) > 1) { ?><button class="sl-nav prev" type="button" aria-label="Previous slide">‹</button><button class="sl-nav next" type="button" aria-label="Next slide">›</button>
<div class="sl-dots"><?php foreach ($slides as $i => $s) { ?><button type="button" aria-label="Go to slide <?= $i + 1 ?>" aria-current="<?= $i === 0 ? 'true' : 'false' ?>"></button><?php } ?></div><?php } ?>
</section>
<?php break;

case 'video': $embed = Site::embedUrl($b['embed_url']); if ($b['video'] === '' && $embed === '') break;
    $player = $b['video'] !== ''
        ? '<video ' . (!empty($b['autoplay']) ? 'autoplay muted loop playsinline' : 'controls playsinline') . ' preload="metadata"' . ($b['image'] !== '' ? ' poster="' . esc(base_url($b['image'])) . '"' : '') . '><source src="' . esc(base_url($b['video'])) . '" type="video/' . (str_ends_with(strtolower($b['video']), '.webm') ? 'webm' : 'mp4') . '">Your browser cannot play this video.</video>'
        : '<iframe src="' . esc($embed . (!empty($b['autoplay']) ? '&autoplay=1&mute=1&muted=1&loop=1' : '')) . '" title="' . esc($t('title', 'Video')) . '" loading="lazy" allow="accelerometer; autoplay; encrypted-media; picture-in-picture; fullscreen" allowfullscreen></iframe>'; ?>
<section class="sec <?= $cls ?>"<?= $id ?>><div class="wrap<?= $b['layout'] === 'split' ? ' split' : '' ?>">
<?php if ($b['layout'] === 'split') { ?><div class="video-box"><?= $player ?></div><div class="prose" style="margin:0"><?php if ($t('title') !== '') { ?><h2><?= esc($b['title']) ?></h2><?php } ?><?= Site::md($b['text']) ?></div>
<?php } else { ?><?php if ($t('title') !== '' || $t('text') !== '') { ?><div class="sec-head"><?php if ($t('title') !== '') { ?><h2><?= esc($b['title']) ?></h2><?php } ?><?= Site::md($b['text']) ?></div><?php } ?><div class="video-box wide"><?= $player ?></div><?php } ?>
</div></section>
<?php break;

case 'gallery': if (!$b['images']) break; ?>
<section class="sec <?= $cls ?>"<?= $id ?>><div class="wrap"><?php $head('', $t('title'), $t('subtitle')); ?>
<div class="gallery cols-<?= esc($b['columns']) ?>" data-gallery><?php foreach ($b['images'] as $g) { if ($g['image'] === '') continue; ?><figure><a href="<?= esc(base_url($g['image'])) ?>" data-full><img src="<?= esc(base_url($g['image'])) ?>" alt="<?= esc($g['caption'] ?: $t('title', 'Photo')) ?>" loading="lazy"></a><?php if ($g['caption'] !== '') { ?><figcaption><?= esc($g['caption']) ?></figcaption><?php } ?></figure><?php } ?></div></div></section>
<?php break;

case 'counters': $rows = []; foreach (Site::lines($b['lines']) as $ln) { [$num, $lab] = array_map('trim', explode('|', $ln, 2) + [1 => '']); if ($num !== '') $rows[] = [$num, $lab]; } if (!$rows) break; ?>
<section class="sec <?= $cls ?>"<?= $id ?>><div class="wrap"><?php $head('', $t('title'), $t('subtitle')); ?>
<div class="counters"><?php foreach ($rows as [$num, $lab]) { $m = []; preg_match('/^(\D*?)(\d[\d,.]*)(.*)$/u', $num, $m); ?><div class="counter"><strong<?= $m ? ' data-count="' . esc(str_replace(',', '', $m[2])) . '" data-pre="' . esc($m[1]) . '" data-post="' . esc($m[3]) . '"' : '' ?>><?= esc($num) ?></strong><span><?= esc($lab) ?></span></div><?php } ?></div></div></section>
<?php break;

case 'news': ?>
<section class="sec <?= $cls ?>"<?= $b['anchor'] !== '' ? $id : ' id="news"' ?>><div class="wrap"><?php $head('Insights', $t('title', 'News and success stories'), $t('subtitle')); ?>
<div class="grid"><?php foreach ($list as $it) { $u = Site::newsUrl($it); ?><article class="card news-card"><a class="media" href="<?= esc($u) ?>" tabindex="-1" aria-hidden="true"><?= $img((string) $it['image_path'], $it['title'], (string) ($it['icon'] ?: 'chat'), (int) $it['id'] + 11) ?></a><?php if ($it['tags']) { ?><span class="tag"><?= esc(trim(explode(',', $it['tags'])[0])) ?></span><?php } ?><h3><a href="<?= esc($u) ?>"><?= esc($it['title']) ?></a></h3><p><?= esc($it['subtitle'] ?: mb_strimwidth(trim(strip_tags(Site::md($it['body']))), 0, 150, '…')) ?></p><div class="card-actions"><small style="color:var(--muted)"><?= esc(date('j M Y', strtotime((string) (($it['created_at'] ?? '') ?: ($it['updated_at'] ?? '')) ?: 'now'))) ?></small><a href="<?= esc($u) ?>" style="margin-inline-start:auto">Read more →</a></div></article><?php } ?></div>
<?php if ($slug !== 'news' && Site::hasPage('news')) { ?><p style="text-align:center;margin-top:28px"><a class="btn btn-o" href="<?= base_url('news') ?>">More news</a></p><?php } ?></div></section>
<?php break;

case 'cta': ?>
<section class="sec <?= $cls ?>"<?= $id ?>><div class="wrap"><div class="cta <?= esc($b['style']) ?>"><div><h2><?= esc($t('title', 'Need technology support?')) ?></h2><p><?= esc($t('text', 'Talk to ' . $site['name'] . ' for professional advice, quality products and dependable ICT services.')) ?></p></div>
<div class="hero-cta"><a class="btn btn-w" href="<?= esc(Site::link($t('button_url', 'contact#contact-form'))) ?>"><?= esc($t('button_label', 'Request a quote')) ?></a><?php if ($wa !== '') { ?><a class="btn btn-a" href="<?= esc($waLink()) ?>" target="_blank" rel="noopener">WhatsApp</a><?php } elseif ($site['email']) { ?><?= Site::email($site['email'], 'Email us', ' class="btn btn-a"') ?><?php } ?></div></div></div></section>
<?php break;

case 'contact': ?>
<section class="sec <?= $cls ?>" id="contact-form"><div class="wrap contact">
  <div class="card"><h2><?= esc($t('title', 'Send us a message')) ?></h2><p><?= esc($t('text', 'Tell us what you need and we will respond as soon as possible.')) ?></p>
  <form class="form" method="post" action="<?= base_url('contact') ?>"><?= csrf_field() ?>
    <input type="hidden" name="t" value="<?= time() ?>"><input type="hidden" name="source" value="<?= esc($slug) ?>">
    <div class="hp" aria-hidden="true"><label for="f-website">Leave this empty</label><input id="f-website" name="website" tabindex="-1" autocomplete="off"></div>
    <div><label for="f-name">Full name *</label><input id="f-name" name="name" maxlength="150" required autocomplete="name" value="<?= esc(old('name') ?? '') ?>"></div>
    <div><label for="f-phone">Phone number</label><input id="f-phone" name="phone" type="tel" maxlength="60" autocomplete="tel" value="<?= esc(old('phone') ?? '') ?>"></div>
    <div><label for="f-email">Email</label><input id="f-email" name="email" type="email" maxlength="190" autocomplete="email" value="<?= esc(old('email') ?? '') ?>"></div>
    <div><label for="f-service">Service needed</label><?php $pre = (string) (old('service') ?? service('request')->getGet('service') ?? ''); ?><select id="f-service" name="service"><option value="">Choose a service</option><?php foreach ($services as $s) { ?><option <?= $pre === $s['title'] ? 'selected' : '' ?>><?= esc($s['title']) ?></option><?php } ?><?php if ($pre !== '' && !in_array($pre, array_column($services, 'title'), true)) { ?><option selected><?= esc(mb_substr($pre, 0, 150)) ?></option><?php } ?><option>Other</option></select></div>
    <div class="full"><label for="f-msg">Message *</label><textarea id="f-msg" name="message" rows="5" maxlength="3000" required><?= esc(old('message') ?? '') ?></textarea></div>
    <div class="full"><button class="btn btn-p" type="submit">Send message</button> <span style="color:var(--muted);font-size:14px">We reply by email or phone. Please give at least one.</span></div>
  </form></div>
  <div class="card"><h2>Get in touch</h2><ul class="info-list">
    <?php if ($site['email']) { ?><li><?= Site::icon('mail') ?><div><strong>Email</strong><?= Site::email($site['email']) ?></div></li><?php } ?>
    <?php if ($site['phone']) { ?><li><?= Site::icon('phone') ?><div><strong>Phone</strong><a href="tel:<?= esc($tel) ?>"><?= esc($site['phone']) ?></a></div></li><?php } ?>
    <?php if ($wa !== '') { ?><li><?= Site::icon('chat') ?><div><strong>WhatsApp</strong><a href="<?= esc($waLink()) ?>" target="_blank" rel="noopener">Chat with us</a></div></li><?php } ?>
    <?php if ($site['address']) { ?><li><?= Site::icon('map') ?><div><strong>Visit us</strong><?= nl2br(esc($site['address'])) ?></div></li><?php } ?>
    <?php if ($site['hours']) { ?><li><?= Site::icon('clock') ?><div><strong>Business hours</strong><?= nl2br(esc($site['hours'])) ?></div></li><?php } ?>
    <li><?= Site::icon('users') ?><div><strong>Existing clients</strong><a href="<?= base_url('its-login?for=client') ?>">Log in to the client portal</a> to report an issue and follow progress.</div></li>
  </ul>
  <?php if (preg_match('#^https://(www\.)?google\.[a-z.]+/maps/embed#i', $site['map_url'])) { ?><iframe class="map" src="<?= esc($site['map_url']) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Map to our office"></iframe><?php } ?>
  </div>
</div></section>
<?php break;
}
