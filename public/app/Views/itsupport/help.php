<?= view('itsupport/_header') ?>
<div class="card"><h2>Help Center</h2>
<details open><summary>Getting Started</summary><ol><li>Import itsupport_schema.sql in phpMyAdmin</li><li>Visit /its-install to create admin</li><li>Admin - Settings and Mail test</li><li>Add clients, assets, tickets; sign reports</li></ol></details>
<details><summary>Emails Not Sending</summary><ol><li>Check .env or MailLib SMTP settings</li><li>Admin - Mail - send test</li><li>Admin - Mail Log for errors</li></ol></details>
<details><summary>Cron</summary><p>Add cPanel cron hitting the its-cron-* URLs with your token (shown in Admin - Reports / Maintenance).</p></details>
</div>
<?= view('itsupport/_footer') ?>