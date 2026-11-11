<?= view('itsupport/_header') ?>
<?php
use App\Controllers\ItsExtensions;
$platforms   = ItsExtensions::PLATFORMS;
$types       = ItsExtensions::TYPES;
$licenses    = ItsExtensions::LICENSES;
$imgUrl      = ItsExtensions::IMG_URL;
$typeIcon    = ['plugin' => 'puzzle', 'theme' => 'palette', 'addon' => 'plus-circle', 'integration' => 'arrow-left-right'];
$platIcon    = ['churchz' => 'church', 'umi' => 'globe', 'hr' => 'people', 'hmisoz' => 'hospital', 'jasemlz' => 'briefcase', 'kksports' => 'trophy', 'other' => 'box'];
$statusBadge = ['approved' => 'green', 'pending' => 'orange', 'rejected' => 'red'];
$tab = $filter ?: array_key_first($platforms);
if (!isset($platforms[$tab])) $tab = array_key_first($platforms);
$totalAll = array_sum(array_map('count', $grouped));
// Compute top downloads across all platforms
$_allExts = array_merge(...array_values($grouped));
usort($_allExts, fn($a, $b) => (int)$b['download_count'] - (int)$a['download_count']);
$_topDl = array_slice(array_filter($_allExts, fn($e) => (int)$e['download_count'] > 0), 0, 6);
$_dlMax = max(1, (int)($_topDl[0]['download_count'] ?? 1));
$_totalDl = array_sum(array_column($_allExts, 'download_count'));
?>

<div class="section-heading">
  <div>
    <p class="eyebrow">System</p>
    <h2>Extension Portal</h2>
    <p class="muted">Download and upload extensions — plugins, themes and add-ons — for Churchz, UMI and compatible platforms.</p>
  </div>
  <button class="btn" onclick="document.getElementById('ext-upload-modal').removeAttribute('hidden');document.getElementById('ext-name').focus()">
    <i class="bi bi-upload"></i> Upload extension
  </button>
</div>

<?php if ($isAdmin) { ?>
<div class="grid grid-2" style="margin-bottom:20px;gap:16px">
  <div class="card" style="margin:0">
    <div class="chart-heading" style="margin-bottom:16px">
      <div><h3 style="margin:0 0 4px">Download stats</h3><p class="muted" style="margin:0">All-time downloads across <?= $totalAll ?> extension<?= $totalAll !== 1 ? 's' : '' ?> · <?= number_format($_totalDl) ?> total</p></div>
    </div>
    <?php if ($_topDl) { ?>
    <?php foreach ($_topDl as $_e) { $_pct = max(4, (int)round((int)$_e['download_count'] / $_dlMax * 100)); ?>
    <div style="margin-bottom:10px">
      <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:5px">
        <span><?= esc($_e['name']) ?> <span class="badge gray" style="font-size:10px"><?= esc($platforms[$_e['platform']] ?? $_e['platform']) ?></span></span>
        <strong><?= number_format((int)$_e['download_count']) ?></strong>
      </div>
      <div style="height:8px;background:var(--canvas);border-radius:99px;overflow:hidden"><div style="width:<?= $_pct ?>%;height:100%;background:linear-gradient(90deg,var(--brand),var(--brand-dark));border-radius:99px"></div></div>
    </div>
    <?php } ?>
    <?php } else { ?><p class="muted" style="padding:16px 0;text-align:center;font-size:13px">No downloads recorded yet.</p><?php } ?>
  </div>
  <div class="card" style="margin:0">
    <div class="chart-heading" style="margin-bottom:16px">
      <div><h3 style="margin:0 0 4px">By platform</h3><p class="muted" style="margin:0">Extension count per platform</p></div>
    </div>
    <?php $_platMax = max(1, max(array_map('count', $grouped))); ?>
    <?php foreach ($platforms as $_pk => $_pl) { $_cnt = count($grouped[$_pk] ?? []); $_pct = max(0, (int)round($_cnt / $_platMax * 100)); if (!$_cnt) continue; ?>
    <div style="margin-bottom:10px">
      <div style="display:flex;align-items:center;gap:8px;font-size:13px;margin-bottom:5px">
        <i class="bi bi-<?= esc($platIcon[$_pk]) ?>" style="color:var(--brand)"></i>
        <span style="flex:1"><?= esc($_pl) ?></span>
        <strong><?= $_cnt ?></strong>
      </div>
      <div style="height:8px;background:var(--canvas);border-radius:99px;overflow:hidden"><div style="width:<?= $_pct ?>%;height:100%;background:linear-gradient(90deg,var(--brand),var(--brand-dark));border-radius:99px"></div></div>
    </div>
    <?php } ?>
  </div>
</div>
<?php } ?>

<!-- Platform tabs -->
<div class="tabbar-inline" role="tablist" aria-label="Platforms">
  <?php foreach ($platforms as $key => $label):
    $count = count($grouped[$key] ?? []);
    $active = $key === $tab;
  ?>
  <a href="<?= base_url('its-extensions?platform=' . $key) ?>"
     class="tab<?= $active ? ' active' : '' ?>"
     role="tab" aria-selected="<?= $active ? 'true' : 'false' ?>">
    <i class="bi bi-<?= esc($platIcon[$key]) ?>"></i>
    <?= esc($label) ?>
    <?php if ($count): ?><span class="badge <?= $active ? 'blue' : 'gray' ?>"><?= $count ?></span><?php endif ?>
  </a>
  <?php endforeach ?>
  <?php if ($filter): ?>
  <a href="<?= base_url('its-extensions') ?>" class="tab muted" style="margin-left:auto;font-size:12px">
    <i class="bi bi-x-circle"></i> All platforms
  </a>
  <?php endif ?>
</div>

<?php foreach ($platforms as $platKey => $platLabel):
  if ($filter && $filter !== $platKey) continue;
  $items = $grouped[$platKey] ?? [];
?>
<div class="ext-section<?= ($filter && $filter === $platKey) || !$filter ? '' : ' hidden' ?>" id="section-<?= $platKey ?>">

<?php if (!$filter): ?>
<div class="ext-platform-header">
  <h3><i class="bi bi-<?= esc($platIcon[$platKey]) ?>"></i> <?= esc($platLabel) ?> Extensions</h3>
  <?php if (count($items)): ?>
  <a href="<?= base_url('its-extensions?platform=' . $platKey) ?>" class="muted" style="font-size:13px">
    View all <?= count($items) ?> →
  </a>
  <?php endif ?>
</div>
<?php endif ?>

<?php if (!$items): ?>
<p class="muted" style="padding:24px 0">No <?= $filter ? '' : strtolower($platLabel) . ' ' ?>extensions yet. Be the first to upload one.</p>
<?php else: ?>
<div class="ext-grid">
<?php foreach ($items as $ext):
  $sizeLabel = $ext['file_size'] > 0 ? round($ext['file_size'] / 1048576, 1) . ' MB' : '';
?>
<article class="card ext-card<?= $ext['status'] !== 'approved' ? ' ext-pending' : '' ?>">
  <?php if (!empty($ext['screenshot'])): ?>
  <div class="ext-screenshot">
    <img src="<?= base_url($imgUrl . esc($ext['screenshot'])) ?>" alt="<?= esc($ext['name']) ?> screenshot" loading="lazy">
  </div>
  <?php endif ?>
  <div class="ext-icon ext-icon-<?= esc($ext['type']) ?>">
    <i class="bi bi-<?= esc($typeIcon[$ext['type']] ?? 'box') ?>"></i>
  </div>
  <div class="ext-body">
    <div class="ext-head">
      <h4><?= esc($ext['name']) ?></h4>
      <span class="badge gray" style="font-size:11px;flex-shrink:0">v<?= esc($ext['version']) ?></span>
    </div>
    <div class="ext-meta">
      <span class="badge blue"><?= esc($types[$ext['type']] ?? $ext['type']) ?></span>
      <span class="badge gray"><?= esc($platforms[$ext['platform']] ?? $ext['platform']) ?></span>
      <?php $pt = $ext['price_type'] ?? 'free'; ?><span class="badge <?= $pt === 'paid' ? 'orange' : 'green' ?>"><?= $pt === 'paid' ? 'Paid' : 'Free' ?></span>
      <?php if (!empty($ext['license'])): ?>
      <span class="badge gray"><?= esc($ext['license']) ?></span>
      <?php endif ?>
      <?php if ($isAdmin || $ext['status'] !== 'approved'): ?>
      <span class="badge <?= esc($statusBadge[$ext['status']] ?? 'gray') ?>"><?= esc(ucfirst($ext['status'])) ?></span>
      <?php endif ?>
    </div>
    <?php if ($ext['description']): ?>
    <p class="ext-desc"><?= esc(mb_strimwidth($ext['description'], 0, 180, '…')) ?></p>
    <?php endif ?>
    <?php if (!empty($ext['tags'])): ?>
    <div class="ext-tags">
      <?php foreach (array_slice(array_map('trim', explode(',', $ext['tags'])), 0, 5) as $tag) if ($tag !== ''): ?>
      <span class="ext-tag"><?= esc($tag) ?></span>
      <?php endif ?>
    </div>
    <?php endif ?>
    <div class="ext-footer">
      <span class="muted">
        <?php if ($ext['author']): ?>
        <i class="bi bi-person"></i> <?= esc($ext['author']) ?>
        <?php endif ?>
        <?php if ($ext['download_count'] > 0): ?>
        &nbsp;·&nbsp;<i class="bi bi-download"></i> <?= (int) $ext['download_count'] ?>
        <?php endif ?>
        <?php if ($sizeLabel): ?>
        &nbsp;·&nbsp;<?= esc($sizeLabel) ?>
        <?php endif ?>
        <?php if (!empty($ext['min_version'])): ?>
        &nbsp;·&nbsp;<i class="bi bi-info-circle"></i> Requires <?= esc($ext['min_version']) ?>
        <?php endif ?>
      </span>
      <div class="ext-actions">
        <?php if (!empty($ext['changelog'])): ?>
        <button class="btn secondary" style="padding:5px 10px;font-size:12px"
          onclick="openChangelog(<?= $ext['id'] ?>, '<?= esc($ext['name'], 'attr') ?>', <?= json_encode($ext['changelog']) ?>)" title="View changelog">
          <i class="bi bi-clock-history"></i>
        </button>
        <?php endif ?>
        <?php if ($ext['homepage_url']): ?>
        <a href="<?= esc($ext['homepage_url']) ?>" target="_blank" rel="noopener" class="btn secondary" style="padding:5px 10px;font-size:12px" title="Homepage / docs">
          <i class="bi bi-box-arrow-up-right"></i>
        </a>
        <?php endif ?>
        <?php if ($ext['status'] === 'approved' || $isAdmin): ?>
        <a href="<?= base_url('its-extensions-download/' . $ext['id']) ?>" class="btn" style="padding:5px 12px;font-size:12px">
          <i class="bi bi-download"></i> Download
        </a>
        <?php endif ?>
        <?php if ($isAdmin && $ext['status'] === 'pending'): ?>
        <form method="post" action="<?= base_url('its-extensions-approve/' . $ext['id']) ?>" style="display:inline"><?= csrf_field() ?>
          <button class="btn" style="padding:5px 10px;font-size:12px;background:var(--success,#059669)">
            <i class="bi bi-check-lg"></i> Approve
          </button>
        </form>
        <button class="btn secondary" style="padding:5px 10px;font-size:12px"
          onclick="openReject(<?= $ext['id'] ?>, '<?= esc($ext['name'], 'attr') ?>')">
          <i class="bi bi-x-lg"></i> Reject
        </button>
        <?php endif ?>
        <?php if ($isAdmin): ?>
        <form method="post" action="<?= base_url('its-extensions-delete/' . $ext['id']) ?>"
          onsubmit="return confirm('Delete <?= esc($ext['name'], 'attr') ?>? This cannot be undone.')"
          style="display:inline"><?= csrf_field() ?>
          <button class="btn secondary" style="padding:5px 10px;font-size:12px;color:var(--danger,#dc2626)" title="Delete">
            <i class="bi bi-trash"></i>
          </button>
        </form>
        <?php endif ?>
      </div>
    </div>
    <?php if ($isAdmin && $ext['status'] === 'rejected' && $ext['notes']): ?>
    <p class="muted" style="margin:8px 0 0;font-size:12px"><i class="bi bi-exclamation-circle"></i> <?= esc($ext['notes']) ?></p>
    <?php endif ?>
  </div>
</article>
<?php endforeach ?>
</div>
<?php endif ?>
</div>
<?php endforeach ?>

<?php if (!$totalAll && !$filter): ?>
<div class="card" style="text-align:center;padding:48px 24px">
  <i class="bi bi-puzzle" style="font-size:2.5rem;color:var(--muted)"></i>
  <p style="margin:12px 0 0;font-size:1.1rem;font-weight:600">No extensions yet</p>
  <p class="muted">Upload the first extension for Churchz, UMI or another platform.</p>
</div>
<?php endif ?>

<!-- ───── Upload modal ───── -->
<div id="ext-upload-modal" hidden
     style="position:fixed;inset:0;z-index:9000;align-items:flex-start;justify-content:center;padding:24px 12px;overflow-y:auto;background:rgba(15,23,42,.55);backdrop-filter:blur(3px)"
     onclick="if(event.target===this)this.setAttribute('hidden','')">
  <div style="background:var(--paper);border-radius:18px;padding:28px 30px;width:min(620px,98vw);box-shadow:0 24px 64px rgba(0,0,0,.25)">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
      <h3 style="margin:0"><i class="bi bi-upload"></i> Upload extension</h3>
      <button onclick="document.getElementById('ext-upload-modal').setAttribute('hidden','')"
              class="btn secondary" style="padding:4px 10px;font-size:18px" aria-label="Close">&times;</button>
    </div>
    <form method="post" action="<?= base_url('its-extensions-upload') ?>" enctype="multipart/form-data" class="ext-form">
      <?= csrf_field() ?>

      <!-- Basic info -->
      <p class="ext-section-label"><i class="bi bi-info-circle"></i> Basic info</p>
      <div class="ext-row2">
        <div>
          <label for="ext-name">Name <span class="req">*</span></label>
          <input id="ext-name" name="name" maxlength="120" required placeholder="e.g. Weekly Bulletin Generator">
        </div>
        <div>
          <label for="ext-version">Version <span class="req">*</span></label>
          <input id="ext-version" name="version" maxlength="30" required placeholder="1.0.0">
        </div>
      </div>
      <div class="ext-row2">
        <div>
          <label for="ext-platform">Platform <span class="req">*</span></label>
          <select id="ext-platform" name="platform" required>
            <?php foreach ($platforms as $k => $v): ?>
            <option value="<?= esc($k) ?>"><?= esc($v) ?></option>
            <?php endforeach ?>
          </select>
        </div>
        <div>
          <label for="ext-type">Type <span class="req">*</span></label>
          <select id="ext-type" name="type" required>
            <?php foreach ($types as $k => $v): ?>
            <option value="<?= esc($k) ?>"><?= esc($v) ?></option>
            <?php endforeach ?>
          </select>
        </div>
      </div>
      <div class="ext-row2">
        <div>
          <label for="ext-license">License</label>
          <select id="ext-license" name="license">
            <option value="">— Select —</option>
            <?php foreach ($licenses as $lic): ?>
            <option value="<?= esc($lic) ?>"><?= esc($lic) ?></option>
            <?php endforeach ?>
          </select>
        </div>
        <div>
          <label for="ext-min-version">Minimum platform version</label>
          <input id="ext-min-version" name="min_version" maxlength="60" placeholder="e.g. Churchz 2.1+">
        </div>
      </div>
      <div class="ext-row2">
        <div>
          <label for="ext-price-type">Pricing</label>
          <select id="ext-price-type" name="price_type">
            <?php foreach (ItsExtensions::PRICE_TYPES as $k => $v): ?>
            <option value="<?= esc($k) ?>"><?= esc($v) ?></option>
            <?php endforeach ?>
          </select>
        </div>
      </div>

      <label for="ext-description">Description</label>
      <textarea id="ext-description" name="description" rows="3" placeholder="What does this extension do? What problem does it solve?"></textarea>

      <label for="ext-tags">Tags <span class="muted" style="font-weight:400;font-size:11px">(comma-separated)</span></label>
      <input id="ext-tags" name="tags" maxlength="255" placeholder="e.g. bulletin, reports, pdf, attendance">

      <!-- Author -->
      <p class="ext-section-label" style="margin-top:18px"><i class="bi bi-person-circle"></i> Author</p>
      <div class="ext-row2">
        <div>
          <label for="ext-author">Author name</label>
          <input id="ext-author" name="author" maxlength="120" placeholder="Your name or organisation">
        </div>
        <div>
          <label for="ext-email">Author email</label>
          <input id="ext-email" name="author_email" type="email" maxlength="120" placeholder="you@example.com">
        </div>
      </div>
      <label for="ext-homepage">Homepage / documentation URL</label>
      <input id="ext-homepage" name="homepage_url" type="url" maxlength="255" placeholder="https://…">

      <!-- Files -->
      <p class="ext-section-label" style="margin-top:18px"><i class="bi bi-file-earmark-zip"></i> Files</p>
      <label for="ext-file">Extension ZIP <span class="req">*</span> <span class="muted" style="font-weight:400;font-size:11px">max <?= ItsExtensions::MAX_MB ?> MB</span></label>
      <input id="ext-file" type="file" name="ext_file" accept=".zip" required class="ext-file-input">

      <label for="ext-screenshot" style="margin-top:10px">Screenshot / banner image <span class="muted" style="font-weight:400;font-size:11px">PNG, JPG, GIF or WebP — max <?= ItsExtensions::MAX_IMG_MB ?> MB</span></label>
      <input id="ext-screenshot" type="file" name="screenshot" accept="image/png,image/jpeg,image/gif,image/webp" class="ext-file-input">
      <div id="ext-img-preview" style="display:none;margin-top:8px">
        <img id="ext-img-thumb" src="" alt="Preview" style="max-height:120px;border-radius:8px;border:1px solid var(--line)">
      </div>

      <!-- Changelog -->
      <p class="ext-section-label" style="margin-top:18px"><i class="bi bi-clock-history"></i> Changelog <span class="muted" style="font-weight:400;font-size:11px">optional — what's new in this version?</span></p>
      <textarea id="ext-changelog" name="changelog" rows="4" placeholder="v1.0.0 — Initial release&#10;• Feature A&#10;• Fixed issue with …"></textarea>

      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:22px">
        <button type="button" class="btn secondary"
          onclick="document.getElementById('ext-upload-modal').setAttribute('hidden','')">Cancel</button>
        <button type="submit" class="btn"><i class="bi bi-upload"></i> Upload</button>
      </div>
    </form>
  </div>
</div>

<!-- ───── Changelog modal ───── -->
<div id="ext-changelog-modal" hidden
     style="position:fixed;inset:0;z-index:9002;align-items:center;justify-content:center;background:rgba(15,23,42,.55);backdrop-filter:blur(3px)"
     onclick="if(event.target===this)this.setAttribute('hidden','')">
  <div style="background:var(--paper);border-radius:18px;padding:28px 32px;width:min(480px,96vw);max-height:80vh;display:flex;flex-direction:column;box-shadow:0 24px 64px rgba(0,0,0,.25)">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
      <h3 id="ext-changelog-title" style="margin:0"><i class="bi bi-clock-history"></i> Changelog</h3>
      <button onclick="document.getElementById('ext-changelog-modal').setAttribute('hidden','')"
              class="btn secondary" style="padding:4px 10px;font-size:18px" aria-label="Close">&times;</button>
    </div>
    <pre id="ext-changelog-body" style="margin:0;overflow-y:auto;font:inherit;font-size:13.5px;line-height:1.7;white-space:pre-wrap;color:var(--ink)"></pre>
  </div>
</div>

<!-- ───── Reject modal ───── -->
<div id="ext-reject-modal" hidden
     style="position:fixed;inset:0;z-index:9001;align-items:center;justify-content:center;background:rgba(15,23,42,.55);backdrop-filter:blur(3px)"
     onclick="if(event.target===this)this.setAttribute('hidden','')">
  <div style="background:var(--paper);border-radius:18px;padding:28px 32px;width:min(420px,96vw);box-shadow:0 24px 64px rgba(0,0,0,.25)">
    <h3 id="ext-reject-title" style="margin:0 0 14px">Reject extension</h3>
    <form id="ext-reject-form" method="post">
      <?= csrf_field() ?>
      <label for="ext-reject-notes">Reason (optional, shown to submitter)</label>
      <textarea id="ext-reject-notes" name="notes" rows="3" maxlength="255" placeholder="e.g. Missing readme, incompatible version…" style="margin-top:6px"></textarea>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px">
        <button type="button" class="btn secondary"
          onclick="document.getElementById('ext-reject-modal').setAttribute('hidden','')">Cancel</button>
        <button type="submit" class="btn" style="background:var(--danger,#dc2626)">Reject</button>
      </div>
    </form>
  </div>
</div>

<style>
.tabbar-inline{display:flex;flex-wrap:wrap;gap:4px;margin-bottom:18px;border-bottom:2px solid var(--line);padding-bottom:0}
.tabbar-inline .tab{display:flex;align-items:center;gap:6px;padding:9px 16px;font-size:14px;font-weight:600;color:var(--muted);text-decoration:none;border-radius:10px 10px 0 0;border:1px solid transparent;border-bottom:none;position:relative;bottom:-2px;transition:color .15s,background .15s}
.tabbar-inline .tab:hover{color:var(--ink);background:var(--soft)}
.tabbar-inline .tab.active{color:var(--brand);background:var(--paper);border-color:var(--line);border-bottom-color:var(--paper)}
.ext-platform-header{display:flex;align-items:center;justify-content:space-between;margin:4px 0 12px}
.ext-platform-header h3{margin:0;display:flex;align-items:center;gap:8px;font-size:1rem}
#ext-upload-modal:not([hidden]),#ext-changelog-modal:not([hidden]),#ext-reject-modal:not([hidden]){display:flex}
.ext-grid{display:grid;gap:16px;grid-template-columns:repeat(auto-fill,minmax(min(100%,300px),1fr));margin-bottom:24px}
.ext-card{margin:0;display:flex;flex-direction:column;gap:0;padding:0;overflow:hidden}
.ext-card.ext-pending{border-style:dashed;opacity:.88}
.ext-screenshot{width:100%;aspect-ratio:16/7;overflow:hidden;background:var(--soft)}.ext-screenshot img{width:100%;height:100%;object-fit:cover;display:block}
.ext-icon{width:44px;height:44px;flex-shrink:0;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;margin:14px 0 0 16px}
.ext-icon-plugin{background:#eff6ff;color:#2563eb}
.ext-icon-theme{background:#fdf4ff;color:#9333ea}
.ext-icon-addon{background:#f0fdf4;color:#16a34a}
.ext-icon-integration{background:#fff7ed;color:#ea580c}
[data-mode=dark] .ext-icon-plugin{background:#1e3a5f;color:#60a5fa}
[data-mode=dark] .ext-icon-theme{background:#2e1a3f;color:#c084fc}
[data-mode=dark] .ext-icon-addon{background:#14291e;color:#4ade80}
[data-mode=dark] .ext-icon-integration{background:#2c1810;color:#fb923c}
.ext-body{flex:1;min-width:0;padding:10px 16px 14px;display:flex;flex-direction:column;gap:6px}
.ext-head{display:flex;align-items:flex-start;gap:8px;justify-content:space-between}
.ext-head h4{margin:0;font-size:.95rem;line-height:1.3}
.ext-meta{display:flex;flex-wrap:wrap;gap:5px}
.ext-desc{margin:0;font-size:13px;color:var(--muted);line-height:1.5}
.ext-tags{display:flex;flex-wrap:wrap;gap:5px}
.ext-tag{background:var(--soft,#f1f5f9);color:var(--muted);border-radius:6px;padding:2px 8px;font-size:11px;font-weight:500}
.ext-footer{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:8px;margin-top:auto;padding-top:8px;border-top:1px solid var(--line)}
.ext-footer .muted{font-size:12px;display:flex;align-items:center;gap:4px;flex-wrap:wrap}
.ext-actions{display:flex;flex-wrap:wrap;gap:6px;align-items:center}
.ext-form label{display:block;font-size:13px;font-weight:600;margin:10px 0 4px}
.ext-form input,.ext-form select,.ext-form textarea{width:100%;margin:0}
.ext-row2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.ext-section-label{margin:0 0 2px;font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--muted);display:flex;align-items:center;gap:6px;border-bottom:1px solid var(--line);padding-bottom:6px}
.ext-file-input{cursor:pointer}
.req{color:var(--danger,#dc2626)}
@media(max-width:480px){.ext-row2{grid-template-columns:1fr}}
.ext-section.hidden{display:none}
</style>

<script>
function openReject(id, name) {
  document.getElementById('ext-reject-title').textContent = 'Reject: ' + name;
  document.getElementById('ext-reject-form').action = '<?= base_url('its-extensions-reject/') ?>' + id;
  document.getElementById('ext-reject-notes').value = '';
  document.getElementById('ext-reject-modal').removeAttribute('hidden');
  document.getElementById('ext-reject-notes').focus();
}
function openChangelog(id, name, text) {
  document.getElementById('ext-changelog-title').innerHTML = '<i class="bi bi-clock-history"></i> Changelog — ' + name;
  document.getElementById('ext-changelog-body').textContent = text || '(No changelog provided)';
  document.getElementById('ext-changelog-modal').removeAttribute('hidden');
}
// Screenshot preview
document.addEventListener('DOMContentLoaded', () => {
  const imgInput = document.getElementById('ext-screenshot');
  const preview  = document.getElementById('ext-img-preview');
  const thumb    = document.getElementById('ext-img-thumb');
  if (imgInput) {
    imgInput.addEventListener('change', () => {
      const f = imgInput.files[0];
      if (f && f.type.startsWith('image/')) {
        thumb.src = URL.createObjectURL(f);
        preview.style.display = 'block';
      } else {
        preview.style.display = 'none';
      }
    });
  }
  <?php if (session()->getFlashdata('err') && old('name')): ?>
  document.getElementById('ext-upload-modal').removeAttribute('hidden');
  <?php if (old('name')): ?>document.getElementById('ext-name').value = <?= json_encode(old('name')) ?>;<?php endif ?>
  <?php if (old('version')): ?>document.getElementById('ext-version').value = <?= json_encode(old('version')) ?>;<?php endif ?>
  <?php endif ?>
});
</script>

<?= view('itsupport/_footer') ?>
