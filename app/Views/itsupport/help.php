<?= view('itsupport/_header') ?>
<?php $role = (string) (session('its_user')['role'] ?? ''); $isClient = $role === 'client'; $isAdmin = $role === 'admin';
$sections = [
 ['start', '🚀 Getting started', false, [
  ['First-time setup', '<ol><li>Import <code>itsupport_schema.sql</code> in phpMyAdmin, then run <code>php spark migrate</code>.</li><li>Open <code>/its-install</code> and create the first admin.</li><li>Go to <strong>Admin → Branding &amp; Settings</strong> to set the company name, logo and colours.</li><li>Set <code>encryption.key</code> in <code>.env</code> (needed for the credential vault).</li><li>Add your clients, then assets, then start logging tickets.</li></ol>'],
  ['Finding your way around', '<p>The left sidebar groups everything: <strong>Work</strong> (tickets, board, quotes), <strong>Customers</strong>, <strong>IT Operations</strong> and <strong>Resources</strong>. The top bar has search, the notification bell and your user menu. On a phone, use the bottom tab bar.</p>'],
  ['Keyboard shortcuts', '<p><kbd>Ctrl</kbd>+<kbd>K</kbd> opens quick search across clients, tickets, assets and articles. <kbd>Esc</kbd> closes it. Text size (A− to A++), language and dark mode are in the toolbar and user menu.</p>'],
 ]],
 ['tickets', '🎫 Tickets and the board', false, [
  ['Create and work a ticket', '<p><strong>Tickets → New ticket</strong>: pick the client, a subject and priority. Open the ticket to add tasks (what you found, what you did, parts and labour cost), reply to the client, upload photos and get a signed service report.</p>'],
  ['Kanban board', '<p><strong>Board</strong> shows tickets as cards by status. Drag a card to change its status. Overdue cards are flagged.</p>'],
  ['SLA and overdue alerts', '<p>Each ticket gets a due date from its priority. Overdue tickets appear in the 🔔 notification bell and on the dashboard.</p>'],
  ['Time tracking and canned replies', '<p>Log time on a ticket for billing. Use canned replies for common answers.</p>'],
 ]],
 ['assets', '💻 Assets and machine service log', false, [
  ['Register a machine', '<p><strong>Assets → add</strong>: name, brand, serial, plus specs (hostname, CPU, RAM, storage, OS, purchase and warranty dates). A "new build" entry is logged automatically.</p>'],
  ['Log repairs and upgrades', '<p>Open the machine (or scan its QR code) and use <strong>Log work</strong>. Choose repair, upgrade, replacement or install; pick the component (RAM, SSD, HDD, CPU, OS); enter the old and new spec, costs and notes; optionally link a ticket. For RAM, storage, CPU and OS the machine\'s specs update automatically.</p>'],
  ['Warranty', '<p>Expired warranties show a red badge on the machine page.</p>'],
 ]],
 ['customers', '👥 Clients, quotes and billing', false, [
  ['Clients', '<p>Store contacts, logos and notes. A client can have portal logins: <strong>Admin → Users → role client</strong>.</p>'],
  ['Quotes', '<p>Build a quote with line items, email it, and the client approves from a secure link.</p>'],
  ['Billing and retainers', '<p><strong>Billing</strong> shows billable hours against each client\'s retainer for the month, the overage amount, and a CSV export.</p>'],
 ]],
 ['portal', '🌐 Client portal', false, [
  ['What clients see', '<p>Client logins only see their own tickets, can report an issue, reply, rate the service and read help articles.</p>'],
 ]],
 ['network', '📡 Network and credentials', false, [
  ['Devices and cameras', '<p>Add devices with an IP. Monitoring marks them online or offline; offline devices raise a notification. Set a cron on <code>its-cron-device-monitor</code>.</p>'],
  ['Credential vault', '<p>Passwords are encrypted. Revealing one is logged in the audit trail.</p>'],
 ]],
 ['reports', '📊 Reports, forms and knowledge base', false, [
  ['Report builder', '<p>Pick a module, columns and filters, then save and export to CSV.</p>'],
  ['Custom forms', '<p>Create forms with your own fields, share the link, and review submissions under Form reports.</p>'],
  ['Knowledge base', '<p>Write articles for your team; mark them public to show them in the client portal.</p>'],
 ]],
 ['admin', '🛡 Admin and security', true, [
  ['Users and permissions', '<p><strong>Admin → Users</strong> adds people. <strong>Permissions</strong> sets None, View or Edit per role and module.</p>'],
  ['Two-factor authentication', '<p><strong>Security</strong> lets each user turn on authenticator-app 2FA and download recovery codes. Admins can reset a user\'s 2FA. Repeated wrong passwords lock the account for a short time.</p>'],
  ['API and webhooks', '<p><strong>Admin → API &amp; Webhooks</strong>: create an API key for <code>/api/v1/tickets</code>, <code>clients</code> and <code>assets</code>, and add webhooks to be notified of new tickets.</p>'],
  ['Backups', '<p><strong>Admin → Backup</strong> downloads a database dump. Schedule it with <code>php spark backup:run</code>.</p>'],
  ['Updates', '<p><strong>Updates</strong> installs releases after checking file integrity. Record a baseline after any manual change.</p>'],
  ['System health', '<p><strong>Health</strong> checks PHP, database, folders, backups and cron.</p>'],
 ]],
 ['trouble', '🔧 Troubleshooting', false, [
  ['Emails not sending', '<ol><li>Check the SMTP settings in <strong>Admin → Mail</strong>.</li><li>Send a test message.</li><li>Look at <strong>Mail Log</strong> for the error.</li></ol>'],
  ['Cron jobs', '<p>Call the <code>its-cron-*</code> URLs on a schedule with your token (shown in Admin → Maintenance).</p>'],
  ['Something shows an error', '<p>Check <code>writable/logs</code> for the newest log file. In <code>.env</code> set <code>CI_ENVIRONMENT = development</code> temporarily to see details, then return it to <code>production</code>.</p>'],
  ['Locked out', '<p>Wait a few minutes for the lockout to end, or ask an admin to reset your password or 2FA.</p>'],
 ]],
];
if ($isClient) { $sections = [['portal', '🌐 Using the support portal', false, [
  ['Report an issue', '<p>Choose <strong>Report an Issue</strong>, describe the problem and send it. You will get updates by email.</p>'],
  ['Follow a ticket', '<p>Open <strong>My Tickets</strong> to read replies, add information, and rate the service when it is completed.</p>'],
  ['Find answers', '<p><strong>Help articles</strong> has guides you can read without raising a ticket.</p>'],
]]]; } ?>
<style>.help-wrap{display:grid;gap:18px}.help-toc{display:flex;flex-wrap:wrap;gap:8px}.help-toc a{padding:6px 12px;border-radius:999px;background:var(--brand-soft);color:var(--brand-dark);font-size:13px;font-weight:600;text-decoration:none}.help-sec h3{margin:0 0 8px}.help-sec details{border-top:1px solid var(--line);padding:10px 2px}.help-sec summary{font-weight:600}.help-sec details p,.help-sec details ol{margin:8px 0 2px;color:var(--muted)}kbd{border:1px solid var(--line);border-radius:5px;padding:1px 6px;font:12px monospace}@media(min-width:1000px){.help-wrap{grid-template-columns:240px 1fr;align-items:start}.help-toc{position:sticky;top:76px;flex-direction:column}.help-toc a{border-radius:10px}}</style>
<div class="card"><div class="section-heading"><h2>Help Center</h2></div>
<label for="help-q" class="muted">Search this guide</label><input id="help-q" type="search" placeholder="e.g. RAM upgrade, backup, 2FA, SLA" autocomplete="off">
<p class="muted" id="help-empty" hidden>No matching help topics. Try another word, or press Ctrl+K to search your data.</p></div>
<div class="help-wrap">
<nav class="help-toc" aria-label="Help topics"><?php foreach ($sections as $s) { if ($s[2] && !$isAdmin) continue; ?><a href="#h-<?= $s[0] ?>"><?= esc($s[1]) ?></a><?php } ?></nav>
<div style="display:grid;gap:14px"><?php foreach ($sections as $s) { if ($s[2] && !$isAdmin) continue; ?>
<section class="card help-sec" id="h-<?= $s[0] ?>"><h3><?= esc($s[1]) ?></h3>
<?php foreach ($s[3] as $i => $t) { ?><details <?= $i === 0 ? 'open' : '' ?>><summary><?= esc($t[0]) ?></summary><?= $t[1] ?></details><?php } ?></section><?php } ?></div></div>
<script>(function(){var q=document.getElementById('help-q'),secs=[].slice.call(document.querySelectorAll('.help-sec')),e=document.getElementById('help-empty');q.addEventListener('input',function(){var v=q.value.trim().toLowerCase(),any=false;secs.forEach(function(s){var hit=false;s.querySelectorAll('details').forEach(function(d){var m=!v||d.textContent.toLowerCase().indexOf(v)>-1;d.hidden=!m;if(m){hit=true;if(v)d.open=true}});s.hidden=!hit;if(hit)any=true});e.hidden=any||!v})})();</script>
<?= view('itsupport/_footer') ?>