# Mwamiri IT Support

A self-hosted IT support and field-service platform built on **CodeIgniter 4.7** (PHP 8.2+, MySQL/MariaDB). It manages clients, equipment, tickets and quotes, with a client portal, a Kanban board, network monitoring, billing and enterprise security. Current version: **2.1.0**.

## Features

| Area | What it does |
|---|---|
| **Tickets** | Create and track tickets with priority, type, assignee and SLA due dates. Add tasks (diagnosis, action, parts and labour cost), photos, time entries and canned replies. Produces a signed service report. |
| **Kanban board** | Drag tickets between status columns. Overdue tickets are flagged. |
| **Clients and quotes** | Client records with logos and retainer hours. Quotes with line items, emailed to the client for approval through a secure link. |
| **Assets and machine service log** | Registers machines with CPU, RAM, storage, OS, purchase and warranty dates. Log repairs, new builds, and RAM/SSD/CPU/OS upgrades with before/after specs, costs and an optional ticket link. Specs update automatically. QR code per asset. |
| **Client portal** | Clients see only their own tickets, report issues, reply, rate the service and read help articles. |
| **Network and credentials** | Device and camera inventory with TCP availability checks, and an AES-encrypted credential vault with an audit trail on reveals. |
| **Reports and forms** | Report builder with CSV export, custom forms with submissions, and a knowledge base (internal and public articles). |
| **Billing** | Monthly retainer vs billable hours, overage amount, CSV export and an iCal feed. |
| **Security** | Role-based permissions (admin, manager, technician, client), TOTP two-factor authentication with recovery codes, login lockout, password policy, audit log and CSRF protection. |
| **Integrations** | REST API (`/api/v1/tickets`, `clients`, `assets`) with API keys, and webhooks. |
| **Operations** | Scheduled backups, system health page, integrity-checked updater, mail log and cron endpoints. |
| **Interface** | Left sidebar with top bar, notification bell (overdue tickets and offline devices), Ctrl+K search, dark mode, text-size controls, mobile bottom tab bar, and six languages (English, French, Spanish, Kiswahili, Arabic, Chinese). |

## System at a glance

- 23 controllers, 122 routes, 12 migrations and 30 database tables.
- Main tables: `tickets`, `tasks`, `clients`, `assets`, `asset_events`, `quotes`, `network_devices`, `vault_credentials`, `kb_articles`, `users`, `role_permissions`, `api_keys`, `webhooks`, `audit_log`.
- Libraries: `Perm`, `Sla`, `Totp`, `TicketService`, `Webhooks`, `Backup`, `SystemUpdater`, `NetworkMonitor`, `CredentialVault`, `MailLib`, `ReportLib`, `Qr`.
- Tailwind CSS (prefix `tw-`) compiled to `public/assets/tailwind.css`.

## Requirements

PHP 8.2+ with `intl`, `mbstring`, `mysqli`, `curl`, `zip`; MySQL 5.7+ or MariaDB 10.4+; Apache or Nginx; Node.js only if you change styles.

## Install

```bash
git clone https://github.com/Mwamiri/its.git
cd its
cp env .env                 # then edit .env
```

In `.env` set `app.baseURL`, the `database.default.*` values, and `encryption.key` (generate with `php spark key:generate`). Then:

```bash
php spark migrate
```

The framework is bundled in `system/`, so no Composer step is needed. Import `itsupport_schema.sql` first on a fresh database if migrations alone are not enough, then open `/its-install` once to create the first admin account. Point the web root at `public/`. See [DEPLOY.md](DEPLOY.md) for cPanel and cron setup.

## Commands

```bash
php spark migrate           # apply database migrations
php spark backup:run [keep] # database backup, keeping the last N
npm install && npm run build:css   # rebuild Tailwind after adding classes
```

Cron endpoints (use your cron token, shown in Admin): `its-cron-maintenance`, `its-cron-weekly`, `its-cron-monthly`, `its-cron-device-monitor`.

## Security notes

Set `CI_ENVIRONMENT = production` in `.env` on live servers. Keep `.env`, `writable/` and the cron token private. After manual file changes, record a new integrity baseline in **Updates** before using the updater. See [SECURITY.md](SECURITY.md).

## Help

Open **Help** inside the app for a searchable guide, or **Features** for a feature overview.

## License

MIT. Built on [CodeIgniter 4](https://codeigniter.com).