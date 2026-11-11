<?= view('itsupport/_header', ['title' => $title]) ?>
<style>
.sub-status-pending{background:#fff7ed;color:#9a3412;border:1px solid #fed7aa}
.sub-status-active{background:#f0fdf4;color:#166534;border:1px solid #bbf7d0}
.sub-status-suspended{background:#f1f5f9;color:#475569;border:1px solid #e2e8f0}
.sub-status-expired{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}
.key-box{font-family:monospace;font-size:12px;background:#f4f7fb;padding:6px 10px;border-radius:6px;word-break:break-all;color:#526278;cursor:pointer;border:1px solid #e2e8f0}
html[data-mode=dark] .key-box{background:#172235;color:#a4b2c5;border-color:#334155}
</style>

<div class="section-heading">
  <div><h2>Extension Subscriptions</h2><p class="muted">Manage who can download extensions and their access packages.</p></div>
  <div style="display:flex;gap:8px;flex-wrap:wrap">
    <button class="btn" onclick="document.getElementById('create-sub-modal').showModal()"><i class="bi bi-person-plus"></i> Create Subscriber</button>
    <button class="btn secondary" onclick="document.getElementById('pkg-modal').showModal()"><i class="bi bi-box-seam"></i> Manage Packages</button>
    <a class="button secondary" href="<?= base_url('extensions') ?>" target="_blank"><i class="bi bi-box-arrow-up-right"></i> View Marketplace</a>
  </div>
</div>

<!-- Stats -->
<div class="grid" style="grid-template-columns:repeat(auto-fit,minmax(140px,1fr));margin-bottom:16px">
  <?php foreach (['total' => ['Total','bi-people','gray'], 'pending' => ['Pending','bi-hourglass-split','orange'], 'active' => ['Active','bi-check-circle','green'], 'suspended' => ['Suspended','bi-pause-circle','gray']] as $k => [$label,$icon,$color]) { ?>
  <div class="card" style="padding:16px;text-align:center">
    <i class="bi <?= $icon ?>" style="font-size:22px;color:var(--brand)"></i>
    <div style="font-size:26px;font-weight:700;letter-spacing:-.03em;margin:4px 0"><?= $stats[$k] ?></div>
    <div class="muted" style="font-size:12px"><?= $label ?></div>
  </div>
  <?php } ?>
</div>

<?php if (session('ok')) { ?><div class="flash flash-ok"><i class="bi bi-check-circle-fill"></i><div class="flash-body"><?= esc(session('ok')) ?></div></div><?php } ?>
<?php if (session('err')) { ?><div class="flash flash-err"><i class="bi bi-exclamation-triangle-fill"></i><div class="flash-body"><?= esc(session('err')) ?></div></div><?php } ?>

<!-- Subscriptions table -->
<div class="card" style="padding:0">
  <div style="padding:16px 20px;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between">
    <h3 style="margin:0">Subscriber Access Requests</h3>
    <span class="badge gray"><?= count($subs) ?> total</span>
  </div>
  <?php if (!$subs) { ?>
  <div style="padding:32px;text-align:center;color:var(--muted)">
    <i class="bi bi-people" style="font-size:32px;display:block;margin-bottom:8px;opacity:.4"></i>
    No subscription requests yet. Subscribers register from the public <a href="<?= base_url('extensions') ?>">extension marketplace</a>.
  </div>
  <?php } else { ?>
  <div class="tbl">
  <table>
    <thead><tr><th>Subscriber</th><th>System</th><th>Package</th><th>Status</th><th>Download Key</th><th>Expires</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($subs as $s) { ?>
    <tr>
      <td>
        <strong><?= esc($s['subscriber_name']) ?></strong><br>
        <span class="muted"><?= esc($s['subscriber_email']) ?></span>
        <?php if ($s['notes']) { ?><br><span class="muted" title="<?= esc($s['notes']) ?>" style="font-size:11px"><?= esc(mb_strimwidth((string) $s['notes'], 0, 60, '…')) ?></span><?php } ?>
      </td>
      <td><span class="badge gray"><?= esc($s['subscriber_system'] ?: '—') ?></span></td>
      <td><?= esc($s['pkg_name'] ?? '<span class="muted">—</span>') ?></td>
      <td><span class="badge sub-status-<?= $s['status'] ?>"><?= ucfirst($s['status']) ?></span></td>
      <td>
        <?php if ($s['download_key']) { ?>
        <div class="key-box" onclick="navigator.clipboard.writeText('<?= esc($s['download_key']) ?>').then(()=>this.textContent='Copied!')" title="Click to copy"><?= substr($s['download_key'], 0, 16) ?>…</div>
        <?php } else { ?><span class="muted">—</span><?php } ?>
      </td>
      <td><?= $s['expires_at'] ? esc(substr($s['expires_at'], 0, 10)) : '<span class="muted">Never</span>' ?></td>
      <td style="white-space:nowrap">
        <?php if ($s['status'] === 'pending') { ?>
        <button class="btn" style="font-size:12px;padding:7px 10px" onclick="openApprove(<?= $s['id'] ?>, '<?= esc(addslashes($s['subscriber_name'])) ?>', '<?= esc(addslashes($s['subscriber_email'])) ?>')">
          <i class="bi bi-check-lg"></i> Approve
        </button>
        <?php } elseif ($s['status'] === 'active') { ?>
        <form method="post" action="<?= base_url('its-ext-sub-suspend/' . $s['id']) ?>" style="display:inline"><?= csrf_field() ?>
          <button class="btn secondary" style="font-size:12px;padding:7px 10px" onclick="return confirm('Suspend this subscription?')"><i class="bi bi-pause"></i> Suspend</button>
        </form>
        <?php } elseif ($s['status'] === 'suspended') { ?>
        <form method="post" action="<?= base_url('its-ext-sub-reactivate/' . $s['id']) ?>" style="display:inline"><?= csrf_field() ?>
          <button class="btn" style="font-size:12px;padding:7px 10px"><i class="bi bi-play"></i> Reactivate</button>
        </form>
        <?php } ?>
        <form method="post" action="<?= base_url('its-ext-sub-delete/' . $s['id']) ?>" style="display:inline"><?= csrf_field() ?>
          <button class="btn danger" style="font-size:12px;padding:7px 10px" onclick="return confirm('Delete this subscription?')"><i class="bi bi-trash3"></i></button>
        </form>
      </td>
    </tr>
    <?php } ?>
    </tbody>
  </table>
  </div>
  <?php } ?>
</div>

<!-- Packages list -->
<div class="card" style="margin-top:20px;padding:0">
  <div style="padding:16px 20px;border-bottom:1px solid var(--line)">
    <h3 style="margin:0">Access Packages</h3>
  </div>
  <?php if (!$packages) { ?>
  <div style="padding:24px;text-align:center;color:var(--muted)">No packages defined yet. <button class="btn" onclick="document.getElementById('pkg-modal').showModal()">Create one</button></div>
  <?php } else { ?>
  <div class="tbl"><table>
    <thead><tr><th>Package</th><th>Scope</th><th>Platforms / Extensions</th><th>Price</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($packages as $p) { ?>
    <tr>
      <td><strong><?= esc($p['name']) ?></strong><?php if ($p['description']) echo '<br><span class="muted" style="font-size:12px">' . esc($p['description']) . '</span>'; ?></td>
      <td><span class="badge blue"><?= ucfirst($p['scope']) ?></span></td>
      <td class="muted" style="font-size:12px">
        <?php if ($p['scope'] === 'platforms') echo esc($p['allowed_platforms'] ?: '—');
              elseif ($p['scope'] === 'extensions') echo 'IDs: ' . esc($p['allowed_extension_ids'] ?: '—');
              else echo 'All extensions'; ?>
      </td>
      <td><?= esc($p['price_label'] ?: '—') ?></td>
      <td style="white-space:nowrap">
        <button class="btn secondary" style="font-size:12px;padding:7px 10px"
          onclick="openEditPkg(<?= $p['id'] ?>, '<?= esc(addslashes($p['name'])) ?>', '<?= esc(addslashes($p['description'])) ?>', '<?= $p['scope'] ?>', '<?= esc(addslashes($p['allowed_platforms'])) ?>', '<?= esc(addslashes($p['allowed_extension_ids'] ?? '')) ?>', '<?= esc(addslashes($p['price_label'])) ?>')">
          <i class="bi bi-pencil"></i> Edit
        </button>
        <form method="post" action="<?= base_url('its-ext-pkg-delete/' . $p['id']) ?>" style="display:inline"><?= csrf_field() ?>
          <button class="btn danger" style="font-size:12px;padding:7px 10px" onclick="return confirm('Delete this package? Subscribers assigned to it will lose their package assignment.')"><i class="bi bi-trash3"></i></button>
        </form>
      </td>
    </tr>
    <?php } ?>
    </tbody>
  </table></div>
  <?php } ?>
</div>

<!-- Create Subscriber modal -->
<dialog id="create-sub-modal" style="border:1px solid var(--line);border-radius:16px;padding:0;background:var(--paper);color:var(--ink);min-width:min(500px,95vw);box-shadow:0 20px 60px rgba(0,0,0,.18)">
  <form method="post" action="<?= base_url('its-ext-sub-create') ?>">
    <?= csrf_field() ?>
    <div style="padding:20px 24px;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between">
      <div>
        <h3 style="margin:0">Create Subscriber</h3>
        <p class="muted" style="font-size:12px;margin:4px 0 0">A download key is auto-generated and emailed immediately.</p>
      </div>
      <button type="button" onclick="document.getElementById('create-sub-modal').close()" style="border:0;background:transparent;font-size:20px;cursor:pointer;color:var(--muted)">&times;</button>
    </div>
    <div style="padding:20px 24px">
      <div class="form-grid">
        <div class="field">
          <label for="cs-name">Full Name *</label>
          <input type="text" id="cs-name" name="name" required maxlength="150" placeholder="e.g. St Mary's Parish">
        </div>
        <div class="field">
          <label for="cs-email">Email Address *</label>
          <input type="email" id="cs-email" name="email" required maxlength="190" placeholder="admin@example.com">
        </div>
        <div class="field">
          <label for="cs-system">System / Platform</label>
          <select id="cs-system" name="system">
            <option value="">— Select —</option>
            <option value="churchz">Churchz</option>
            <option value="umi">UMI</option>
            <option value="hr">HR Suite</option>
            <option value="hmisoz">HMISOZ</option>
            <option value="jasemlz">JasemlZ</option>
            <option value="kksports">KKSports</option>
            <option value="other">Other</option>
          </select>
        </div>
        <div class="field">
          <label for="cs-package">Access Package</label>
          <select id="cs-package" name="package_id">
            <option value="">— Full Access (no package) —</option>
            <?php foreach ($packages as $p) { ?><option value="<?= $p['id'] ?>"><?= esc($p['name']) ?></option><?php } ?>
          </select>
        </div>
        <div class="field">
          <label for="cs-expires">Expiry Date <span class="muted">(leave blank = never)</span></label>
          <input type="date" id="cs-expires" name="expires_at" min="<?= date('Y-m-d') ?>">
        </div>
        <div class="field">
          <label for="cs-notes">Internal Notes</label>
          <input type="text" id="cs-notes" name="notes" maxlength="500">
        </div>
      </div>
    </div>
    <div style="padding:16px 24px;border-top:1px solid var(--line);display:flex;gap:10px;justify-content:flex-end">
      <button type="button" class="btn secondary" onclick="document.getElementById('create-sub-modal').close()">Cancel</button>
      <button type="submit" class="btn"><i class="bi bi-key"></i> Create &amp; Generate Key</button>
    </div>
  </form>
</dialog>

<!-- Approve modal -->
<dialog id="approve-modal" style="border:1px solid var(--line);border-radius:16px;padding:0;background:var(--paper);color:var(--ink);min-width:min(440px,95vw);box-shadow:0 20px 60px rgba(0,0,0,.18)">
  <form method="post" id="approve-form">
    <?= csrf_field() ?>
    <div style="padding:20px 24px;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between">
      <h3 style="margin:0">Approve Subscription</h3>
      <button type="button" onclick="document.getElementById('approve-modal').close()" style="border:0;background:transparent;font-size:20px;cursor:pointer;color:var(--muted)">&times;</button>
    </div>
    <div style="padding:20px 24px">
      <p id="approve-label" class="muted" style="margin-top:0"></p>
      <div class="field">
        <label for="app-package">Access Package</label>
        <select id="app-package" name="package_id">
          <option value="">— No package (Full Access) —</option>
          <?php foreach ($packages as $p) { ?><option value="<?= $p['id'] ?>"><?= esc($p['name']) ?></option><?php } ?>
        </select>
      </div>
      <div class="field">
        <label for="app-expires">Expiry Date <span class="muted">(leave blank = never expires)</span></label>
        <input type="date" id="app-expires" name="expires_at" min="<?= date('Y-m-d') ?>">
      </div>
      <div class="field">
        <label for="app-notes">Internal Notes</label>
        <textarea id="app-notes" name="notes" rows="2"></textarea>
      </div>
    </div>
    <div style="padding:16px 24px;border-top:1px solid var(--line);display:flex;gap:10px;justify-content:flex-end">
      <button type="button" class="btn secondary" onclick="document.getElementById('approve-modal').close()">Cancel</button>
      <button type="submit" class="btn"><i class="bi bi-check-lg"></i> Approve &amp; Send Key</button>
    </div>
  </form>
</dialog>

<!-- Package modal -->
<dialog id="pkg-modal" style="border:1px solid var(--line);border-radius:16px;padding:0;background:var(--paper);color:var(--ink);min-width:min(520px,95vw);box-shadow:0 20px 60px rgba(0,0,0,.18)">
  <form method="post" id="pkg-form" action="<?= base_url('its-ext-pkg-save') ?>">
    <?= csrf_field() ?>
    <input type="hidden" id="pkg-id" name="id" value="0">
    <div style="padding:20px 24px;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between">
      <h3 style="margin:0" id="pkg-modal-title">Create Package</h3>
      <button type="button" onclick="document.getElementById('pkg-modal').close()" style="border:0;background:transparent;font-size:20px;cursor:pointer;color:var(--muted)">&times;</button>
    </div>
    <div style="padding:20px 24px">
      <div class="form-grid">
        <div class="field wide">
          <label for="pkg-name">Package Name *</label>
          <input type="text" id="pkg-name" name="name" required maxlength="100" placeholder="e.g. Churchz Full Access">
        </div>
        <div class="field wide">
          <label for="pkg-desc">Description</label>
          <input type="text" id="pkg-desc" name="description" maxlength="500">
        </div>
        <div class="field">
          <label for="pkg-scope">Scope *</label>
          <select id="pkg-scope" name="scope" onchange="updatePkgScopeFields()">
            <option value="all">All extensions</option>
            <option value="platforms">Specific platforms</option>
            <option value="extensions">Specific extension IDs</option>
          </select>
        </div>
        <div class="field">
          <label for="pkg-price">Price Label</label>
          <input type="text" id="pkg-price" name="price_label" maxlength="60" placeholder="e.g. KES 5,000/yr">
        </div>
      </div>
      <div id="pkg-platforms-row" class="field">
        <label for="pkg-platforms">Allowed Platforms <span class="muted">(comma-separated)</span></label>
        <input type="text" id="pkg-platforms" name="allowed_platforms" placeholder="e.g. churchz,umi">
        <p class="hint">Keys: churchz, umi, hr, hmisoz, other</p>
      </div>
      <div id="pkg-extids-row" class="field" style="display:none">
        <label for="pkg-extids">Allowed Extension IDs <span class="muted">(comma-separated)</span></label>
        <input type="text" id="pkg-extids" name="allowed_extension_ids" placeholder="e.g. 1,4,7,12">
      </div>
    </div>
    <div style="padding:16px 24px;border-top:1px solid var(--line);display:flex;gap:10px;justify-content:flex-end">
      <button type="button" class="btn secondary" onclick="document.getElementById('pkg-modal').close()">Cancel</button>
      <button type="submit" class="btn"><i class="bi bi-floppy"></i> Save Package</button>
    </div>
  </form>
</dialog>

<script>
function openApprove(id, name, email) {
    document.getElementById('approve-form').action = `<?= base_url('its-ext-sub-approve/') ?>${id}`;
    document.getElementById('approve-label').textContent = `Approving access for: ${name} (${email})`;
    document.getElementById('approve-modal').showModal();
}
function openEditPkg(id, name, desc, scope, platforms, extIds, price) {
    document.getElementById('pkg-id').value    = id;
    document.getElementById('pkg-name').value  = name;
    document.getElementById('pkg-desc').value  = desc;
    document.getElementById('pkg-scope').value = scope;
    document.getElementById('pkg-platforms').value = platforms;
    document.getElementById('pkg-extids').value    = extIds;
    document.getElementById('pkg-price').value     = price;
    document.getElementById('pkg-modal-title').textContent = 'Edit Package';
    updatePkgScopeFields();
    document.getElementById('pkg-modal').showModal();
}
function updatePkgScopeFields() {
    const s = document.getElementById('pkg-scope').value;
    document.getElementById('pkg-platforms-row').style.display = s === 'platforms'  ? '' : 'none';
    document.getElementById('pkg-extids-row').style.display    = s === 'extensions' ? '' : 'none';
}
updatePkgScopeFields();
// Reset pkg modal on open (for new package)
document.getElementById('pkg-modal').addEventListener('close', () => {
    if (!document.getElementById('pkg-id').value || document.getElementById('pkg-id').value === '0') return;
    // keep values if editing
});
document.querySelector('[onclick*="pkg-modal"]')?.addEventListener('click', () => {
    document.getElementById('pkg-id').value = '0';
    document.getElementById('pkg-modal-title').textContent = 'Create Package';
    ['pkg-name','pkg-desc','pkg-platforms','pkg-extids','pkg-price'].forEach(i => document.getElementById(i).value = '');
    document.getElementById('pkg-scope').value = 'all';
    updatePkgScopeFields();
});
</script>
<?= view('itsupport/_footer') ?>
