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

## Troubleshooting
- **404 errors**: Check `.env` baseURL has trailing slash, check `public/.htaccess` exists
- **500 errors**: Change `CI_ENVIRONMENT = production` to `development` in `.env` to see errors
- **Uploads not working**: Check `public/uploads` permissions (755 or 775)