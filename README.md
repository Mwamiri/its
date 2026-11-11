# Mwamiri IT Support

A self-hosted IT service desk, field-service system, client portal, and company website built with CodeIgniter 4 and PHP. It brings ticketing, client and asset management, monitoring, billing, public website content, and an extension marketplace into one application.

**Current release: 5.3.6** — see [release history](RELEASES.md).

## Features

### Service desk

- Ticket queues with priorities, types, assignments, due dates, SLA targets, status filters, and a drag-and-drop Kanban board.
- Ticket tasks for diagnosis, work performed, parts, labour, time entries, attachments, canned replies, and signed service reports.
- Bulk ticket actions, ticket watchers with email notifications, ticket merging, and client satisfaction surveys.
- Quotes with line items and secure client approval links.
- A searchable knowledge base for staff and clients.

### Clients, assets, and client portal

- Client records, contacts, retainer hours, and client-linked users.
- Asset register with machine specifications, warranty and purchase dates, QR codes, CSV export, and service history for repairs and upgrades.
- Client portal for tickets, quotes, assets, service reports, help articles, and forms. Portal users are restricted to their own client data.
- Custom fields for clients, assets, and tickets.

### Monitoring and operations

- Network device and camera registers, TCP availability checks, scheduled device monitoring, and SNMPv2c uptime checks.
- SSL certificate and domain expiry monitoring.
- System health checks for server, storage, database, mail, security, and required writable directories.
- Scheduled database backups, error-log viewer, sign-in history, system cleanup tools, and an integrity-checked updater with backups and restore support.
- Admin tools for mail configuration, user management, permissions, audit logs, security settings, and module controls.

### Website and extensions

- Public website CMS with a drag-and-drop page builder, reusable content blocks, configurable navigation and footer, support/contact forms, and SEO controls.
- Public products showcase and extension marketplace with platform filters, extension metadata, screenshots, changelogs, and download statistics.
- Staff extension portal for uploads, review/approval, and platform-specific distribution. Subscription keys and access packages can control downloads.
- Public extensions API for approved catalogue entries.

### Security and administration

- Role-based permissions for administrators, managers, technicians, and client users.
- TOTP and email-based two-factor authentication, optional staff-wide 2FA enforcement, login lockout, session controls, and IP allow/block lists.
- Security headers, CSRF protection, rate limiting, audit trails, and encrypted credential storage.
- API keys and signed webhooks for integrations.
- Global command palette/search, dashboard shortcuts, notifications, dark mode, mobile navigation, and multiple language options.

### Reporting and productivity

- Billing for retainer and billable hours, CSV exports, and private iCal feeds.
- Report builder, custom forms, CSV/Excel imports, training attendance, and branded client documents.
- Dashboard alerts and reports for operational and service activity.

## Requirements

- PHP 8.2+ with `intl`, `mbstring`, `mysqli`, `curl`, and `zip`.
- MySQL 5.7+ or MariaDB 10.4+.
- Apache or Nginx configured to serve the `public/` directory and allow the application's rewrite rules.
- HTTPS is strongly recommended for production.
- Node.js is needed only when rebuilding front-end styles. The compiled CSS is included.

The CodeIgniter framework is bundled in `system/`; a production installation does not need Composer. Composer dependencies are needed for development tools and tests.

## Fresh installation

For cPanel or other packaged installations, use the install ZIP and follow [INSTALL.txt](INSTALL.txt). In summary:

1. Extract the package into the application directory and point the domain document root to its `public/` folder.
2. Create an empty MySQL/MariaDB database and import `itsupport_schema.sql`.
3. Copy `env` to `.env`; configure `CI_ENVIRONMENT`, `app.baseURL`, database access, and a strong `encryption.key`.
4. Set a private random `its.setupKey` in `.env`, then open `/its-setup` to run the remaining migrations and create the first administrator.
5. Remove `its.setupKey` from `.env` after setup. Keep `.env`, encryption keys, cron tokens, database backups, and uploaded files private.

For local development, clone the repository:

```bash
git clone https://github.com/Mwamiri/its.git
cd its
```

Configure `.env` and the database as above, then apply migrations:

```bash
php spark migrate
```

Keep the web root pointed at `public/`; do not expose the repository root. See [DEPLOY.md](DEPLOY.md) for deployment, cron, update, and troubleshooting details.

## Updating an existing installation

Back up the database and application files before every update. Use the update ZIP through **System → Updates**; do not use the fresh-install ZIP on an existing site. Retain the site's `.env`, database, writable data, and uploads. After an update, run migrations when instructed by the release.

The updater checks file integrity, creates backups, validates packages, and supports restoring previous code backups. See [DEPLOY.md](DEPLOY.md) for the full update procedure.

## Useful commands

```bash
php spark migrate
php spark backup:run [keep]
npm install
npm run build:css
composer test
```

Run the CSS build only after changing front-end styles. Tests use the PHPUnit configuration in [phpunit.dist.xml](phpunit.dist.xml); code coverage is opt-in and requires a PHP coverage driver.

Scheduled tasks use the private cron token configured for the site. Configure maintenance, weekly/monthly reports, and device monitoring in a trusted scheduler; see [DEPLOY.md](DEPLOY.md) for endpoint details.

## Documentation

- [Installation instructions](INSTALL.txt)
- [Deployment and operations guide](DEPLOY.md)
- [Security guidance](SECURITY.md)
- [Release history](RELEASES.md)

## License

MIT. Built on [CodeIgniter 4](https://codeigniter.com).
