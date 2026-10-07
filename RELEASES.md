# IT Support release notes

The complete list of changes for every version, from 1.0.0 to the latest, is shown in the app under **Updates → Version history** (source: `app/Config/ReleaseNotes.php`). Upgrade instructions for each release are in `writable/releases/UPGRADE-x.y.z.md`.

## 3.5.0 (2026-10-08)
The company website is the front page, with Login and Client portal buttons; a drag-and-drop page builder with live preview; header, body and footer design settings; email two-factor sign-in (code or one-click link); a module manager; an update packages folder; and the full version history on the Updates page.

## 3.0.0 (2026-10-07)
Client portal dashboard, branded reports (asset register, service summary), client overview, custom fields, assets and tickets upgrades, staff dashboard, form builder, reorganised menu, import and training, and a safer updater (pre-flight checks, maintenance page, health check, automatic rollback, `php spark its:restore`).

## 2.1.1 (2026-10-07): Security and reliability fixes

### Upgrade steps
1. Run `php spark migrate`. This adds `users.totp_last_step`, which blocks reuse of a two-factor code.
2. In **Updates → System integrity**, record a new baseline. Otherwise the changed files show as modified and online updates stay disabled.
3. Review **Admin → Permissions** for the new **Bulk import** and **Training** modules. By default, import is off for technicians and training is on.
4. Optional, if the site is served over HTTPS: set `app.forceGlobalSecureRequests = true` in `.env`.

Quote approval links emailed before this release still work until the quote is approved or rejected. Links sent from now on use the new signing secret.

### Security
- Removed old source code, the database schema and deployment notes that were downloadable from `public/` (`public/app`, `public/public`, `public/itsupport_schema.sql`, `public/DEPLOY.md`).
- Bulk import has its own permission and also needs edit rights on the clients, assets or training data being imported.
- Training is now a permission module that can be set per role.
- Client portal users can no longer read company-wide dashboard figures (`its-dashboard-stats`). The dashboard permission is enforced.
- Cron URLs use a constant-time token check and are refused when no cron token is set.
- Quote approval links are HMAC-signed with a dedicated secret, and a quote can be decided only once.
- Each two-factor code is accepted only once, including the code used during setup.
- The `secureheaders` filter is on (X-Frame-Options, X-Content-Type-Options, Referrer-Policy).
- Signature captures must be genuine PNG/JPEG images of 1 MB or less.

### Fixes
- Ticket and quote numbers are generated atomically, so there are no duplicates under concurrent requests.
- The installer requires an 8-character password, matching the other password rules.
- Signed-out visitors are sent to sign-in, a fresh install goes to setup, and the site root no longer shows the framework welcome page.
- The monthly and weekly report cron URLs validate their date parameters.

### Performance
- Settings are loaded with one query per request instead of one query per value.
- Storage folders are prepared once instead of being checked on every request.

### Not in this release (planned)
- Signed update manifests (Ed25519), so a compromised update server cannot push code.
- Moving access checks into route filters, and running cron jobs as `spark` commands.
- Feature tests for authentication, role permissions, portal isolation and quote approval.
- Streaming database backups and caching dashboard counts.
