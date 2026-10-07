<?php

namespace Config;

/**
 * Every release, newest first. Shown on the Updates page ("What's new" and "Version history").
 * Each item is [tag, text]; tags are feature, security, fix, performance or change.
 * Releases before 2.1.1 were reconstructed from the database migrations, commit history and deployment notes.
 */
class ReleaseNotes
{
    public array $releases = [
        '3.5.0' => [
            'date'  => '2026-10-08',
            'title' => 'Company website as the front page, page builder, email sign-in codes and module manager',
            'items' => [
                ['feature', 'The company website is now the front page of the system, like Joomla or WordPress: visitors see the site, and the header has Login (staff) and Client portal buttons that lead to the back end.'],
                ['feature', 'Drag-and-drop page builder (System → Website → Pages): arrange blocks (hero, text, image + text, services, solutions, products, projects, testimonials, values, stats, packages, FAQs, partner logos, call-to-action, contact form, spacer), edit each block\'s text, images, buttons and background, and see a live preview that uses the same renderer as the published page.'],
                ['feature', 'Design settings for the whole website: header style (light, dark, brand), sticky header, top contact bar, menu position, logo size, header button; font, text size, corner roundness, button shape and section spacing; footer style, footer text and columns; custom CSS.'],
                ['feature', 'Website pages: Home, About, Services, Solutions, Products, Projects, Support and Contact, plus any pages you add, with built-in vector illustrations and branded image placeholders.'],
                ['feature', 'Website content manager for services, solutions, products, case studies, testimonials, values, packages, FAQs and partner logos, with image uploads.'],
                ['feature', 'Website SEO: per-page titles and descriptions with a search-result preview, canonical and social-share tags, LocalBusiness, FAQ and breadcrumb structured data, sitemap.xml and robots.txt, optional Google Analytics and Search Console verification.'],
                ['feature', 'Website contact form with spam protection; enquiries land in a Leads inbox, are emailed to support@mwamiri.co.ke (or your website email), and can be turned into a client in one click.'],
                ['feature', 'Email two-factor sign-in: after the password, users can receive a 6-digit code and a one-click sign-in link by email that opens their dashboard or client portal (Security page). Works on its own or as a backup to an authenticator app.'],
                ['feature', 'Module manager (System → Modules): switch the website, client portal, board, quotes, network, forms, knowledge base, training, billing, import, report builder or API off and on. Switched-off modules disappear from menus and their pages are blocked; data is kept.'],
                ['feature', 'Update packages folder (System → Updates): add ZIP packages, or copy ZIPs or unzipped package folders into writable/updates by FTP, review the version and type, then install with one click.'],
                ['feature', 'Version history on the Updates page: every release from 1.0.0 to the latest with its changes, filterable by new features, security, fixes and reliability.'],
                ['security', 'Email sign-in codes and links expire after 10 minutes, work once, are rate-limited, and count toward the account lockout; the "require 2FA for administrators" policy accepts either method.'],
                ['security', 'Switched-off modules are refused for every URL and API call, not just hidden in menus.'],
                ['performance', 'Existing website pages are converted to builder blocks automatically during the upgrade, so they look the same until you change them.'],
            ],
        ],
        '3.0.0' => [
            'date'  => '2026-10-07',
            'title' => 'Client dashboard, branded reports, custom fields and a safer updater',
            'items' => [
                ['feature', 'Client portal dashboard: what needs the client\'s attention, quotes to approve, upcoming visits, latest replies, warranty alerts and forms to fill.'],
                ['feature', 'Clients can see their equipment in the portal, print their own asset register, and choose the affected device when reporting an issue.'],
                ['feature', 'Branded documents: service reports, client asset registers and client service summaries carry your logo, address, contact details and a custom footer (Admin → Branding).'],
                ['feature', 'Client overview page with details, equipment, tickets, quotes, portal users and one-click reports.'],
                ['feature', 'Custom fields for clients, assets and tickets (text, dropdown, date, yes/no and more), shown on forms, lists, detail pages, exports, reports and optionally in the portal (Admin → Custom fields).'],
                ['feature', 'Assets: filter by client, status and warranty, search by serial/hostname/model, CSV export, "Save and add another", notes field, asset types list.'],
                ['feature', 'Tickets: quick views (Open, Assigned to me, Overdue, Unassigned, All), filters and search, priority/assignee/due columns, link a ticket to a device, assign on creation.'],
                ['feature', 'Staff dashboard: My work, due within 24 hours, recent activity, and per-user show/hide of dashboard sections.'],
                ['feature', 'Form builder: add, remove, duplicate and reorder fields; section headings; help text and placeholders; half-width fields; conditional "show only if" questions; up to 40 fields.'],
                ['feature', 'Forms can be published to staff, clients (portal) or both, with a custom confirmation message and email alerts on each response; single-form CSV exports have one column per question.'],
                ['feature', 'Bulk import of clients, assets and training attendees from CSV or Excel, with downloadable templates.'],
                ['feature', 'Training sessions with attendance registers, QR self sign-in and CSV export.'],
                ['change', 'Menu reorganised into Overview, Service desk, Customers, Operations, Insights, System and Help & account, with icons, a count of your open tickets, and items shown according to each role\'s permissions. Mobile tab bars for staff and clients.'],
                ['security', 'Cost figures on the dashboard and in its live data are shown only to roles with the "Costs & prices" permission.'],
                ['security', 'Client portal users can no longer read company-wide dashboard figures.'],
                ['security', 'Old source code and the SQL schema that earlier installs left in public/ are moved out of the web root automatically during the upgrade (to writable/legacy-public/, nothing is deleted).'],
                ['fix', 'Emails no longer fail with an error after sending: the missing mail log model was added and logging can no longer break delivery.'],
                ['fix', 'Dashboard figures refresh once a minute as stated (they were refreshing every 5 seconds).'],
                ['fix', 'Client and asset forms validate email addresses, statuses, lengths and logo uploads, and keep what you typed when something needs correcting.'],
                ['performance', 'Updater: pre-flight checks (PHP version, upgrade path, PHP syntax of every file, writable folders) run before anything is changed.'],
                ['performance', 'Updater: a maintenance page is shown while files are replaced; on failure, database changes from the update are rolled back, old files restored and newly added files removed.'],
                ['performance', 'Updater: after installing, the site is test-loaded; if it returns a server error the update is rolled back automatically.'],
                ['performance', 'Updater, backups and integrity checks are no longer cut off by PHP\'s time limit or by closing the browser tab mid-update.'],
                ['performance', 'Emergency restore from the command line when the website is down: php spark its:restore.'],
                ['performance', 'Release packages never include per-site files (index.php, .htaccess, database, URL, email, encryption and session settings).'],
            ],
        ],
        '2.1.1' => [
            'date'  => '2026-10-07',
            'title' => 'Security and reliability fixes',
            'note'  => 'Prepared as a separate release and shipped as part of 3.0.0.',
            'items' => [
                ['security', 'Removed old source code, the database schema and deployment notes that were downloadable from the public web folder.'],
                ['security', 'Bulk import has its own permission (off for technicians by default) and also requires edit rights on the clients, assets or training data being imported.'],
                ['security', 'Training is a permission module that can be set per role in Admin.'],
                ['security', 'All cron URLs use a constant-time token check and refuse requests when no cron token is configured.'],
                ['security', 'Quote approval links are signed with their own secret instead of the cron token, and a quote can be approved or rejected only once.'],
                ['security', 'Two-factor codes cannot be reused: each code is accepted once, including the code used during setup.'],
                ['security', 'Security headers are sent on every page (X-Frame-Options, X-Content-Type-Options, Referrer-Policy).'],
                ['security', 'Signature captures are checked to be genuine PNG/JPEG images under 1 MB before they are saved.'],
                ['fix', 'Ticket and quote numbers can no longer be duplicated when two are created at the same moment.'],
                ['fix', 'The installer requires an 8-character password, matching password changes and new users.'],
                ['fix', 'Signed-out visitors go straight to sign-in, a fresh install goes to setup, and the site root no longer shows the framework welcome page.'],
                ['fix', 'Monthly and weekly report cron URLs reject malformed dates.'],
                ['performance', 'Settings are loaded with one query per request instead of one query per value.'],
                ['performance', 'Storage folders are prepared once instead of being checked on every request.'],
            ],
        ],
        '2.1.0' => [
            'date'  => '2026-10-06',
            'title' => 'Enterprise security, client portal upgrades, board, API and new interface',
            'note'  => 'Reconstructed from the project history and deployment notes.',
            'items' => [
                ['feature', 'Kanban ticket board with drag-and-drop status changes.'],
                ['feature', 'Roles and permissions matrix (none / view / edit per module) for managers and technicians.'],
                ['feature', 'SLA targets per priority with business hours, overdue alerts and a notification bell.'],
                ['feature', 'Tickets: type, assignee, time tracking, canned replies and 1-5 star client rating.'],
                ['feature', 'Knowledge base for staff, with published articles searchable in the client portal.'],
                ['feature', 'Global search (Ctrl+K) across tickets, clients, assets and articles.'],
                ['feature', 'REST API with bearer keys (tickets, clients, assets) and HMAC-signed webhooks.'],
                ['feature', 'Retainers and billing (billable hours over retainer, monthly CSV) and a private iCal calendar feed.'],
                ['feature', 'Machine service log: repairs and upgrades per asset with before/after specs and costs.'],
                ['feature', 'Health page, scheduled backups (spark backup:run) and the online update manager with integrity checks.'],
                ['feature', 'New interface: side menu, app bar, dark mode, text size controls and six interface languages.'],
                ['security', 'Account lockout after repeated failures, authenticator-app two-factor with recovery codes, optional 2FA for administrators and idle session timeout.'],
                ['fix', 'Required-field validation on save, guards for missing records, null-safe audit logging and the billing CSV export deprecation.'],
                ['change', 'Help Center rebuilt as a searchable, role-aware guide; forms modernised with responsive layout and validation states.'],
            ],
        ],
        '2.0.0' => [
            'date'  => '2026-10-05',
            'title' => 'Client portal',
            'note'  => 'Reconstructed from the database migrations.',
            'items' => [
                ['feature', 'Client portal: client users sign in, report issues, follow progress and reply on their tickets.'],
                ['feature', 'Ticket conversation (public replies and internal notes).'],
            ],
        ],
        '1.2.0' => [
            'date'  => '2026-10-04',
            'title' => 'Network monitoring',
            'note'  => 'Reconstructed from the database migrations.',
            'items' => [
                ['feature', 'Network device availability monitoring (TCP port and SNMP checks) with a cron endpoint.'],
                ['security', 'Camera RTSP/RTSPS URLs are stored encrypted with the application key.'],
            ],
        ],
        '1.1.0' => [
            'date'  => '2026-10-02',
            'title' => 'Network management, credential vault, templates and dynamic forms',
            'note'  => 'Reconstructed from the database migrations.',
            'items' => [
                ['feature', 'Network device register (MikroTik, Hikvision, Ruijie, TP-Link, Tenda) with remote actions.'],
                ['feature', 'Camera (CCTV) register.'],
                ['feature', 'Credential vault for device passwords, encrypted with the application key (administrators only).'],
                ['feature', 'Document templates for tickets, quotes and reports.'],
                ['feature', 'Dynamic forms with responses and reports, and the report builder.'],
                ['security', 'Login attempt tracking and rate limiting per IP address.'],
            ],
        ],
        '1.0.0' => [
            'date'  => '2026-10-02',
            'title' => 'First release',
            'note'  => 'Reconstructed from the original installation schema.',
            'items' => [
                ['feature', 'Clients, assets with QR labels, tickets with tasks, photos and costs.'],
                ['feature', 'Quotes with emailed approval links.'],
                ['feature', 'Service reports with client signature capture and public verification codes.'],
                ['feature', 'Maintenance schedules that create tickets automatically, and monthly/weekly email reports.'],
                ['feature', 'Mail settings with SMTP, mail log, audit log, branding settings and an installer.'],
            ],
        ],
    ];
}
