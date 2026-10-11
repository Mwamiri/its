<?php
/**
 * Page header partial — port of CHURCHZ partials/page_header.php
 *
 * Variables:
 *   $title    (string) required — page heading
 *   $subtitle (string) optional — subtitle below the heading
 *   $actions  (string) optional — raw HTML for action buttons on the right
 *   $crumbs   (array)  optional — breadcrumbs as ['Label' => 'url', ...]
 *   $hero     (bool)   optional — true shows a gradient hero banner instead
 */
?>
<?php if (! empty($hero)): ?>
<div class="page-hero">
    <div class="page-hero-inner">
        <div>
            <p class="eyebrow"><?= esc(date('l, j F Y')) ?></p>
            <h1><?= esc($title) ?></h1>
            <?php if (! empty($subtitle)): ?><p class="page-hero-sub"><?= esc($subtitle) ?></p><?php endif ?>
        </div>
        <?php if (! empty($actions)): ?><div class="page-hero-actions no-print"><?= $actions ?></div><?php endif ?>
    </div>
</div>
<?php else: ?>
<div class="page-header">
    <div class="page-header-start">
        <?php if (! empty($crumbs)): ?>
        <nav class="breadcrumbs" aria-label="Breadcrumb">
            <?php foreach ($crumbs as $label => $url): ?>
                <a href="<?= esc($url, 'attr') ?>"><?= esc($label) ?></a>
                <i class="bi bi-chevron-right" aria-hidden="true"></i>
            <?php endforeach ?>
        </nav>
        <?php endif ?>
        <h1><?= esc($title) ?></h1>
        <?php if (! empty($subtitle)): ?><p class="page-header-sub"><?= esc($subtitle) ?></p><?php endif ?>
    </div>
    <?php if (! empty($actions)): ?><div class="page-header-actions no-print"><?= $actions ?></div><?php endif ?>
</div>
<?php endif ?>
<style>
.page-header{display:flex;flex-wrap:wrap;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:24px}
.page-header-start{min-width:0}
.breadcrumbs{display:flex;flex-wrap:wrap;align-items:center;gap:4px;margin-bottom:6px;font-size:12px;font-weight:600;color:var(--muted)}
.breadcrumbs a{color:var(--muted);text-decoration:none}.breadcrumbs a:hover{color:var(--brand)}
.breadcrumbs .bi-chevron-right{font-size:9px;opacity:.6}
.page-header h1{margin:0;font-size:clamp(1.3rem,1.1rem + .9vw,1.75rem);font-weight:700;letter-spacing:-.025em;color:var(--ink)}
.page-header-sub{margin:4px 0 0;color:var(--muted);font-size:13.5px;max-width:680px}
.page-header-actions{display:flex;flex-wrap:wrap;align-items:center;gap:8px}
.page-hero{overflow:hidden;border-radius:18px;background:linear-gradient(115deg,var(--banner-start),var(--banner-end));color:#fff;padding:28px 28px;margin-bottom:24px}
.page-hero-inner{display:flex;flex-wrap:wrap;align-items:flex-end;justify-content:space-between;gap:16px}
.page-hero h1{margin:0;font-size:clamp(1.4rem,1.2rem+1vw,2rem);font-weight:700;letter-spacing:-.03em;color:#fff}
.page-hero-sub{margin:6px 0 0;color:rgba(255,255,255,.8);font-size:13.5px}
.page-hero-actions{display:flex;flex-wrap:wrap;align-items:center;gap:8px}
html[data-mode=dark] .page-header h1{color:var(--ink)}
</style>
