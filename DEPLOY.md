# CI4 IT Support System - Deployment Instructions

## Step 1: Import Database
1. Open phpMyAdmin in cPanel
2. Select your CI4 database
3. Click Import tab
4. Choose file: `itsupport_schema.sql`
5. Click Go

## Step 2: Upload Files
1. In cPanel File Manager, navigate to your CI4 installation folder (e.g., `public_html/sup/`)
2. Upload the zip file and extract it here
3. This will overwrite/merge with your existing CI4 files

## Step 3: Set Permissions
1. Right-click `public/uploads` -> Change Permissions -> 755
2. Right-click `writable` -> Change Permissions -> 755

## Step 4: Configure .env
1. Open `.env` file (enable "Show Hidden Files" in cPanel Settings)
2. Ensure `app.baseURL = 'https://yourdomain.com/sup/public/'` (with trailing slash)
3. Database settings should already be configured

## Step 5: Clear Cache
1. Delete all files in `writable/cache/`
2. Delete all files in `writable/session/`

## Step 6: Run Installer
Visit: `https://yourdomain.com/sup/public/its-install`
Fill out the form and click Install.

## Step 7: Configure Mail & Cron
1. Login at `/its-login`
2. Go to Admin -> Mail to configure SMTP
3. Set up cron jobs (tokens shown in Admin -> Reports/Maintenance):
   - Monthly: `0 17 28-31 * * [ "$(date -d tomorrow +\%d)" = 01 ] && curl -s "https://yourdomain.com/sup/public/its-cron-monthly?token=TOKEN"`
   - Weekly: `0 8 * * 1 curl -s "https://yourdomain.com/sup/public/its-cron-weekly?token=TOKEN"`
   - Maintenance: `0 * * * * curl -s "https://yourdomain.com/sup/public/its-cron-maintenance?token=TOKEN"`

## Updating an Existing Installation
Before updating, make a database backup. Run the CodeIgniter migrations for additive changes (`php spark migrate`) with the PHP CLI configured with the same database credentials and MySQLi extension as the web server. The supplied `itsupport_schema.sql` is also useful for creating missing tables on a fresh schema, but `CREATE TABLE IF NOT EXISTS` does not alter tables that already exist.

Network availability checks only test one configured TCP port per device. To schedule checks, enable monitoring for selected devices and call `/its-cron-device-monitor?token=TOKEN` from a trusted scheduler every few minutes. Keep the cron token private and configure devices only on networks the system is authorized to probe.

Camera RTSP/RTSPS URLs are optional and editable by administrators in the camera register. They are encrypted with the application encryption key, are not shown in camera listings, and cannot be recovered if that key is lost. The RTSP URL field stores connection details only; it does not proxy or display live video.

### Client portal
Run `php spark migrate` to add `users.client_id` and `ticket_updates`. Create a user with the `client` role and link it to a client in Admin > Users; that user lands on the portal and only sees that client's tickets.

### System updates (Admin > Updates)
- Record an integrity baseline first; updates are refused if files differ from it. Back up with "Create backup now" before changing anything.
- Upload a ZIP for an application update (only `app/`, `public/`, `system/`, `composer.json`, `spark`, `VERSION`; never `.env`, `writable/` or uploads) or the official CodeIgniter release ZIP (only its `system/` folder is applied). Major jumps require typing `UPDATE MAJOR`.
- Online checks use HTTPS. If PHP has no CA bundle (typical on WAMP), place a `cacert.pem` in `writable/`; certificate verification stays on.
- Activity is recorded in `writable/update.log`; backups live in `writable/backups/`.

### Front-end CSS (Tailwind)
Styles are compiled into `public/assets/tailwind.css`, which is committed/uploaded with the app, so production servers do not need Node. After adding `tw-` classes to a view, run `npm install` once and `npm run build:css` on your development machine.

### Credential vault encryption key
The administrator-only credential vault requires a secret application key. Run `php spark key:generate` or set `encryption.key` in the private `.env` file to a CodeIgniter-compatible key before storing credentials. Do not commit or share this key. Back it up securely and separately from database backups: vault secrets cannot be decrypted if the key is lost or changed. Restrict filesystem access to `.env`; database backups contain encrypted ciphertext, not plaintext credentials. Do not put secrets in the vault notes field.

### Installable app and offline behavior
The app manifest and service worker are available from the application base URL on `localhost` or an HTTPS origin. The service worker caches only the generic offline fallback page; authenticated pages, client records, submissions, and entered form data are never cached. The offline page is informational and cannot submit work until connectivity returns.

## Troubleshooting
- **404 errors**: Check `.env` baseURL has trailing slash, check `public/.htaccess` exists
- **500 errors**: Change `CI_ENVIRONMENT = production` to `development` in `.env` to see errors
- **Uploads not working**: Check `public/uploads` permissions (755 or 775)
## SNMP monitoring
Set a device's monitor port to **161** to use a built-in SNMPv2c check (sysUpTime). No PHP snmp extension is needed. The community string comes from the `snmp_community` setting (default `public`). SSH checks are not provided.

## Publishing app updates
Run `php spark release:build 2.2.0 https://your-host/itsupport "notes"`. It writes `writable/releases/itsupport-2.2.0.zip` and `manifest.json` (with SHA-256). Host both over HTTPS, then paste the manifest URL on the Updates page.


## Enterprise features (v2.1)

- **Security**: per-account lockout (5 failures = 15 min), TOTP two-factor with recovery codes (`Security` page), optional "require 2FA for admins", idle session timeout. Policies live on the Security page (admin).
- **Permissions**: Admin > Permissions matrix sets none/view/edit per role and module. Admins always have full access.
- **SLA**: response/resolve targets per priority (Security page, optional business hours). Tickets show SLA state; the bell in the menu counts overdue tickets and offline devices.
- **Tickets**: type, assignee, time tracking, canned replies, client 1-5 star rating after resolution.
- **Knowledge base**: staff manage articles at `its-kb`; clients search published articles at `its-portal-kb`.
- **Search**: Ctrl+K searches tickets, clients, assets and articles.
- **API**: `Authorization: Bearer itk_...` (create keys in Admin > API & Webhooks). `GET /api/v1/tickets|clients|assets`, `POST /api/v1/tickets`. 60 requests/minute per key.
- **Webhooks**: HTTPS endpoints only, HMAC-signed JSON, events `ticket.created` and `ticket.status_changed`.
- **Health**: `its-health` (admin) shows PHP, disk, DB, SLA, 2FA coverage and recent log lines.
- Run `php spark migrate` after updating. Not included: SSO, email-to-ticket, SMS, payment links (need external accounts).
### Scheduled backups, billing, calendar, tests

- `php spark backup:run [keep]` writes a gzip backup to `writable/backups/` (default keeps 14). Schedule it daily: Windows Task Scheduler running `C:\wamp\bin\php\php8.3.28\php.exe spark backup:run` in the project folder, or cron `0 2 * * * php /path/spark backup:run`. The Health page shows the last backup.
- Admin > Billing: per-client retainer hours and hourly rate; amount due = billable hours over the retainer. CSV download per month. The same page creates the private iCal link (`its-calendar.ics?token=...`) for open ticket due dates.
- Tests: `tests/unit/EnterpriseTest.php` (TOTP, recovery codes, SLA). Run with `composer install` then `vendor/bin/phpunit`.
## Machine service log
Open Assets, then the machine (or scan its QR), then Log work. Record repairs, new builds, and RAM/SSD/CPU/OS upgrades with before/after specs, costs, and an optional ticket link. Upgrades update the machine specs automatically.
