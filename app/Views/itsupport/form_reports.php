<?= view('itsupport/_header') ?>
<section class="reports-intro"><p class="eyebrow">ADMIN REPORT BUILDER</p><h2>Form submissions</h2><p class="muted">Filter responses by form and date, review the submitted values, or export a CSV.</p></section>
<?php foreach ($errors as $error) { ?><div class="flash flash-err" role="alert"><?= esc($error) ?></div><?php } ?>
<section class="card"><form method="get" action="<?= base_url('its-form-reports') ?>" class="report-filters">
    <label>Form<select name="form_id"><option value="">All forms</option><?php foreach ($forms as $form) { ?><option value="<?= esc($form['id']) ?>" <?= (int) $formId === (int) $form['id'] ? 'selected' : '' ?>><?= esc($form['title']) ?></option><?php } ?></select></label>
    <label>From<input type="date" name="from" value="<?= esc($from) ?>"></label>
    <label>To<input type="date" name="to" value="<?= esc($to) ?>"></label>
    <button class="btn">Apply filters</button>
    <?php if (!$errors) { ?><a class="button secondary" href="<?= base_url('its-form-reports?' . http_build_query(['form_id' => $formId ?: '', 'from' => $from, 'to' => $to, 'export' => 'csv'])) ?>">Export CSV</a><?php } ?>
</form></section>
<section class="card"><div class="section-heading"><h3>Responses</h3><span class="badge gray"><?= count($rows) ?> shown<?= count($rows) === 500 ? ' · limit reached' : '' ?></span></div>
    <?php if ($rows) { ?><div class="tbl"><table><thead><tr><th>ID</th><th>Form</th><th>Submitted</th><th>By</th><th>Answers</th></tr></thead><tbody><?php foreach ($rows as $row) { $answers = json_decode($row['answers_json'], true); $answers = is_array($answers) ? $answers : []; ?><tr><td><?= esc($row['id']) ?></td><td><?= esc($row['form_title']) ?></td><td><?= esc($row['submitted_at']) ?></td><td><?= esc($row['submitter_name'] ?: 'Unknown') ?></td><td><div class="answer-list"><?php foreach ($answers as $key => $value) { ?><div><strong><?= esc((string) $key) ?>:</strong> <?php if (is_array($value) && isset($value['stored_name'], $value['original_name'])) { ?><a href="<?= base_url('its-form-file/' . $row['id'] . '/' . rawurlencode((string) $key)) ?>"><?= esc($value['original_name']) ?></a><?php } else { ?><?= esc(is_scalar($value) ? (string) $value : '') ?><?php } ?></div><?php } ?></div></td></tr><?php } ?></tbody></table></div><?php } else { ?><p class="muted">No responses match these filters.</p><?php } ?>
</section>
<style>
.reports-intro{padding:7px 2px 14px}.reports-intro h2{margin:4px 0;font-size:28px}.reports-intro p{margin:0}.report-filters{display:flex;align-items:end;gap:12px;flex-wrap:wrap}.report-filters label{min-width:160px;flex:1}.report-filters input,.report-filters select{margin-bottom:0}.section-heading{display:flex;align-items:center;justify-content:space-between}.section-heading h3{margin:0}.answer-list{min-width:190px;max-width:480px;overflow-wrap:anywhere}.answer-list>div{margin-bottom:4px}@media(max-width:650px){.report-filters{align-items:stretch;flex-direction:column}.report-filters label{width:100%}}
</style>
<?= view('itsupport/_footer') ?>
