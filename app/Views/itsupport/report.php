<?= view('itsupport/_header') ?>
<div class="card no-print"><button class="btn" onclick="window.print()">Print</button></div>
<div class="card"><h2>SERVICE REPORT <?= esc($ticket['ticket_number']) ?></h2>
<p><strong>Client:</strong> <?= esc($ticket['client_name']) ?><br><strong>Date:</strong> <?= esc($ticket['visit_date'] ?? $ticket['created_at']) ?></p>
<?php if ($templates) { ?><div class="no-print"><label for="report-template">Report template</label><select id="report-template"><option value="">Choose an introduction</option><?php foreach ($templates as $template) { ?><option value="<?= esc($template['id']) ?>"><?= esc($template['name']) ?></option><?php } ?></select></div><p id="report-template-body" class="report-intro"></p><?php } ?>
<div class="tbl"><table><tr><th>Item</th><th>Complaint</th><th>Action</th><th>Recommendation</th><th>Status</th></tr>
<?php foreach ($tasks as $t) { ?><tr><td><?= esc($t['item']) ?></td><td><?= esc($t['complaint']) ?></td><td><?= esc($t['action_taken']) ?></td><td><?= esc($t['recommendation']) ?></td><td><?= esc($t['status']) ?></td></tr><?php } ?>
</table></div></div>
<?php if ($templates) { ?><script id="report-templates-data" type="application/json"><?= json_encode($templates, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<script>
const reportTemplates=JSON.parse(document.getElementById('report-templates-data').textContent);
document.getElementById('report-template').addEventListener('change',function(){const template=reportTemplates.find(item=>String(item.id)===this.value);const intro=document.getElementById('report-template-body');intro.textContent=template?template.body||template.subject||'':'';});
</script><?php } ?>
<div class="card"><h3>Sign-Off</h3>
<?php if ($signature) { ?>
<p><strong>Signed By:</strong> <?= esc($signature['signed_by_name']) ?><br><strong>Code:</strong> <?= esc($signature['verification_code']) ?><br><strong>At:</strong> <?= esc($signature['signed_at']) ?></p>
<img src="<?= base_url($signature['signature_path']) ?>" style="max-width:300px;border:1px solid #dbe2ea;background:#fff;">
<?php } else { ?>
<form method="post" action="<?= base_url('its-sign/' . $ticket['id']) ?>" id="sigForm"><?= csrf_field() ?>
<label>Client Name</label><input name="signed_by_name" required>
<label><input type="checkbox" name="agree" value="1" required style="width:auto;"> I confirm the work is completed satisfactorily.</label><br><br>
<canvas id="cv" width="350" height="150" style="border:1px solid #cbd5e1;border-radius:10px;background:#fff;touch-action:none;max-width:100%;"></canvas>
<input type="hidden" name="signature_data" id="sd"><br>
<button type="button" class="btn" onclick="document.getElementById('sd').value=document.getElementById('cv').toDataURL('image/png');document.getElementById('sigForm').submit();">Save Signature</button></form>
<script>
var cv=document.getElementById('cv'),cx=cv.getContext('2d'),d=false;cx.lineWidth=2;cx.lineCap='round';cx.strokeStyle='#000';
function p(e){var r=cv.getBoundingClientRect(),s=e.touches?e.touches[0]:e;return{x:s.clientX-r.left,y:s.clientY-r.top};}
cv.addEventListener('mousedown',function(e){d=true;var q=p(e);cx.beginPath();cx.moveTo(q.x,q.y);e.preventDefault();});
cv.addEventListener('mousemove',function(e){if(!d)return;var q=p(e);cx.lineTo(q.x,q.y);cx.stroke();e.preventDefault();});
window.addEventListener('mouseup',function(){d=false;});
cv.addEventListener('touchstart',function(e){d=true;var q=p(e);cx.beginPath();cx.moveTo(q.x,q.y);e.preventDefault();});
cv.addEventListener('touchmove',function(e){if(!d)return;var q=p(e);cx.lineTo(q.x,q.y);cx.stroke();e.preventDefault();});
window.addEventListener('touchend',function(){d=false;});
</script>
<?php } ?></div>
<?= view('itsupport/_footer') ?>