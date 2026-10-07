<?= view('itsupport/_header') ?>
<?php $tcf = \App\Libraries\CustomFields::defs('tickets'); $tcfVals = $tcf ? \App\Libraries\CustomFields::one('tickets', (int) $ticket['id']) : []; ?>
<div class="doc-actions no-print"><a class="button secondary" href="<?= base_url('its-tickets-view/' . $ticket['id']) ?>">Back to ticket</a><button class="btn" type="button" onclick="window.print()">Print / save as PDF</button>
<?php if ($templates) { ?><label for="report-template" class="muted" style="margin:0">Introduction</label><select id="report-template" style="width:auto;margin:0"><option value="">None</option><?php foreach ($templates as $template) { ?><option value="<?= esc($template['id']) ?>"><?= esc($template['name']) ?></option><?php } ?></select><?php } ?></div>
<article class="doc">
<?= view('itsupport/_doc_header', ['docTitle' => 'Service report', 'docRef' => $ticket['ticket_number'], 'docDate' => substr((string) ($ticket['visit_date'] ?: $ticket['created_at']), 0, 10), 'docTo' => ['name' => $ticket['client_name'], 'email' => $ticket['email'] ?? '', 'logo_path' => $ticket['client_logo'] ?? '']]) ?>
<p id="report-template-body" class="report-intro"></p>
<table><tr><th style="width:22%">Subject</th><td><?= esc($ticket['subject']) ?></td></tr>
<?php if (!empty($asset)) { ?><tr><th>Equipment</th><td><?= esc($asset['name']) ?><?= $asset['serial_number'] ? ' · S/N ' . esc($asset['serial_number']) : '' ?></td></tr><?php } ?>
<?php foreach ($tcf as $d) { if (!(int) $d['show_in_report']) continue; $v = \App\Libraries\CustomFields::display($d, $tcfVals[$d['field_key']] ?? ''); if ($v === '') continue; ?><tr><th><?= esc($d['label']) ?></th><td><?= esc($v) ?></td></tr><?php } ?>
<?php if (!empty($ticket['description'])) { ?><tr><th>Reported issue</th><td><?= nl2br(esc($ticket['description'])) ?></td></tr><?php } ?></table>
<h3>Work carried out</h3>
<table><thead><tr><th>Item</th><th>Complaint</th><th>Action</th><th>Recommendation</th><th>Status</th></tr></thead><tbody>
<?php foreach ($tasks as $t) { ?><tr><td><?= esc($t['item']) ?><?= !empty($t['serial_number']) ? '<br><small>' . esc($t['serial_number']) . '</small>' : '' ?></td><td><?= nl2br(esc((string) $t['complaint'])) ?></td><td><?= nl2br(esc((string) $t['action_taken'])) ?></td><td><?= nl2br(esc((string) $t['recommendation'])) ?></td><td><?= esc(str_replace('_', ' ', $t['status'])) ?></td></tr><?php } ?>
<?php if (!$tasks) { ?><tr><td colspan="5">No tasks recorded.</td></tr><?php } ?></tbody></table>
<h3>Sign-off</h3>
<?php if ($signature) { ?>
<table><tr><th style="width:22%">Signed by</th><td><?= esc($signature['signed_by_name']) ?></td></tr><tr><th>Signed at</th><td><?= esc($signature['signed_at']) ?></td></tr><tr><th>Verification code</th><td><code><?= esc($signature['verification_code']) ?></code> <span class="muted">(check at <?= esc(base_url('its-verify')) ?>)</span></td></tr></table>
<img src="<?= base_url($signature['signature_path']) ?>" alt="Client signature" style="max-width:300px;border:1px solid #dbe2ea;background:#fff;margin-top:8px">
<?php } else { ?>
<form method="post" action="<?= base_url('its-sign/' . $ticket['id']) ?>" id="sigForm" class="no-print"><?= csrf_field() ?>
<label for="sig-name">Client name</label><input id="sig-name" name="signed_by_name" maxlength="190" required>
<label style="display:flex;gap:8px;align-items:center"><input type="checkbox" name="agree" value="1" required style="width:auto;"> I confirm the work is completed satisfactorily.</label>
<canvas id="cv" width="350" height="150" style="border:1px solid #cbd5e1;border-radius:10px;background:#fff;touch-action:none;max-width:100%;"></canvas>
<input type="hidden" name="signature_data" id="sd"><br>
<button type="button" class="btn secondary" onclick="var c=document.getElementById('cv');c.getContext('2d').clearRect(0,0,c.width,c.height);">Clear</button>
<button type="button" class="btn" onclick="var f=document.getElementById('sigForm');if(!f.reportValidity())return;document.getElementById('sd').value=document.getElementById('cv').toDataURL('image/png');f.submit();">Save signature</button></form>
<p class="muted print-only">Awaiting client signature.</p>
<script>
var cv=document.getElementById('cv'),cx=cv.getContext('2d'),d=false;cx.lineWidth=2;cx.lineCap='round';cx.strokeStyle='#000';
function p(e){var r=cv.getBoundingClientRect(),s=e.touches?e.touches[0]:e;return{x:(s.clientX-r.left)*cv.width/r.width,y:(s.clientY-r.top)*cv.height/r.height};}
cv.addEventListener('mousedown',function(e){d=true;var q=p(e);cx.beginPath();cx.moveTo(q.x,q.y);e.preventDefault();});
cv.addEventListener('mousemove',function(e){if(!d)return;var q=p(e);cx.lineTo(q.x,q.y);cx.stroke();e.preventDefault();});
window.addEventListener('mouseup',function(){d=false;});
cv.addEventListener('touchstart',function(e){d=true;var q=p(e);cx.beginPath();cx.moveTo(q.x,q.y);e.preventDefault();});
cv.addEventListener('touchmove',function(e){if(!d)return;var q=p(e);cx.lineTo(q.x,q.y);cx.stroke();e.preventDefault();});
window.addEventListener('touchend',function(){d=false;});
</script>
<?php } ?>
<?= view('itsupport/_doc_footer') ?>
</article>
<?php if ($templates) { ?><script id="report-templates-data" type="application/json"><?= json_encode($templates, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<script>
const reportTemplates=JSON.parse(document.getElementById('report-templates-data').textContent);
document.getElementById('report-template').addEventListener('change',function(){const template=reportTemplates.find(item=>String(item.id)===this.value);document.getElementById('report-template-body').textContent=template?template.body||template.subject||'':'';});
</script><?php } ?>
<style>.print-only{display:none}@media print{.print-only{display:block}}</style>
<?= view('itsupport/_footer') ?>
