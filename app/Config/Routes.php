<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->GET('/', 'Website::index');
$routes->match(['GET', 'POST'], 'its-setup', 'ItsSetup::index');
if (ENVIRONMENT !== 'production') {
    $routes->match(['GET', 'POST'], 'its-install', 'ItsAuth::install');
}
$routes->match(['GET', 'POST'], 'its-login', 'ItsAuth::login');
$routes->POST('its-logout', 'ItsAuth::logout');
$routes->match(['GET', 'POST'], 'its-2fa', 'ItsAuth::twoFactor');
$routes->POST('its-2fa-email', 'ItsAuth::twoFactorEmail');
$routes->GET('its-2fa-link/(:segment)', 'ItsAuth::twoFactorLink/$1');
$routes->GET('its-modules', 'ItsModules::index');
$routes->POST('its-modules-toggle', 'ItsModules::toggle');
$routes->GET('its-security', 'ItsSecurity::index');
$routes->POST('its-security-enable', 'ItsSecurity::enable');
$routes->POST('its-security-disable', 'ItsSecurity::disable');
$routes->POST('its-security-password', 'ItsSecurity::password');
$routes->POST('its-security-policy', 'ItsSecurity::policy');
$routes->POST('its-security-email2fa', 'ItsSecurity::emailTwoFactor');
$routes->POST('its-admin-reset2fa/(:num)', 'ItsSecurity::resetUser2fa/$1');
$routes->GET('its-dashboard', 'ItsDash::index');
$routes->GET('its-dashboard-stats', 'ItsDash::stats');
$routes->POST('its-dashboard-widgets', 'ItsDash::widgets');
$routes->GET('its-help', 'ItsDash::help');
$routes->GET('its-features', 'ItsDash::features');
$routes->GET('its-manifest', 'ItsMisc::manifest');

$routes->GET('its-tickets', 'ItsTickets::index');
$routes->POST('its-tickets-reply/(:num)', 'ItsTickets::reply/$1');
$routes->GET('its-portal', 'ItsPortal::index');
$routes->GET('its-portal-assets', 'ItsPortal::assets');
$routes->GET('its-portal-assets-report', 'ItsPortal::assetReport');
$routes->match(['GET', 'POST'], 'its-portal-new', 'ItsPortal::create');
$routes->GET('its-portal-ticket/(:num)', 'ItsPortal::view/$1');
$routes->POST('its-portal-reply/(:num)', 'ItsPortal::reply/$1');
$routes->POST('its-tickets', 'ItsTickets::store');
$routes->GET('its-tickets-view/(:num)', 'ItsTickets::view/$1');
$routes->POST('its-tasks/(:num)', 'ItsTickets::addTask/$1');

$routes->GET('its-clients', 'ItsClients::index');
$routes->GET('its-clients-view/(:num)', 'ItsClients::view/$1');
$routes->GET('its-clients-assets-report/(:num)', 'ItsClients::assetReport/$1');
$routes->GET('its-clients-report/(:num)', 'ItsClients::report/$1');
$routes->GET('its-assets-csv', 'ItsAssets::csv');
$routes->POST('its-admin-fields-save', 'ItsAdmin::saveField');
$routes->GET('its-clients-edit/(:num)', 'ItsClients::edit/$1');
$routes->POST('its-clients-save', 'ItsClients::save');
$routes->POST('its-clients-save/(:num)', 'ItsClients::save/$1');

$routes->GET('its-assets', 'ItsAssets::index');
$routes->GET('its-assets-edit/(:num)', 'ItsAssets::edit/$1');
$routes->POST('its-assets-save', 'ItsAssets::save');
$routes->POST('its-assets-save/(:num)', 'ItsAssets::save/$1');
$routes->GET('its-assets-view/(:num)', 'ItsAssets::view/$1');
$routes->POST('its-assets-log/(:num)', 'ItsAssets::log/$1');
$routes->GET('its-qr/(:num)', 'ItsMisc::qr/$1');

$routes->GET('its-network', 'ItsNetwork::index');
$routes->POST('its-network-devices', 'ItsNetwork::saveDevice');
$routes->POST('its-network-devices/(:num)', 'ItsNetwork::saveDevice/$1');
$routes->POST('its-network-check/(:num)', 'ItsNetwork::checkDevice/$1');
$routes->POST('its-network-action/(:num)', 'ItsNetwork::deviceAction/$1');
$routes->POST('its-network-devices-delete/(:num)', 'ItsNetwork::deleteDevice/$1');
$routes->POST('its-cameras', 'ItsNetwork::saveCamera');
$routes->POST('its-cameras/(:num)', 'ItsNetwork::saveCamera/$1');
$routes->POST('its-cameras-delete/(:num)', 'ItsNetwork::deleteCamera/$1');
$routes->POST('its-vault', 'ItsNetwork::saveCredential');
$routes->POST('its-vault/(:num)', 'ItsNetwork::saveCredential/$1');
$routes->POST('its-vault-reveal/(:num)', 'ItsNetwork::revealCredential/$1');
$routes->POST('its-vault-delete/(:num)', 'ItsNetwork::deleteCredential/$1');

$routes->GET('its-forms', 'ItsForms::index');
$routes->POST('its-forms', 'ItsForms::saveForm');
$routes->POST('its-forms-archive/(:num)', 'ItsForms::archiveForm/$1');
$routes->match(['GET', 'POST'], 'its-form/(:num)', 'ItsForms::submit/$1');
$routes->GET('its-form-reports', 'ItsForms::reports');
$routes->GET('its-form-file/(:num)/(:segment)', 'ItsForms::downloadFile/$1/$2');
$routes->GET('its-report-builder', 'ItsReportBuilder::index');
$routes->POST('its-report-builder-save', 'ItsReportBuilder::save');
$routes->GET('its-report-builder-load/(:segment)', 'ItsReportBuilder::load/$1');
$routes->POST('its-report-builder-delete/(:segment)', 'ItsReportBuilder::delete/$1');

$routes->GET('its-import', 'ItsImport::index');
$routes->GET('its-import-template/(:segment)', 'ItsImport::template/$1');
$routes->POST('its-import-run', 'ItsImport::run');
$routes->GET('its-training', 'ItsTraining::index');
$routes->GET('its-training-view/(:num)', 'ItsTraining::view/$1');
$routes->POST('its-training-save', 'ItsTraining::save');
$routes->POST('its-training-attendees/(:num)', 'ItsTraining::addAttendee/$1');
$routes->POST('its-training-mark/(:num)', 'ItsTraining::mark/$1');
$routes->POST('its-training-remove/(:num)/(:num)', 'ItsTraining::removeAttendee/$1/$2');
$routes->GET('its-training-csv/(:num)', 'ItsTraining::csv/$1');
$routes->match(['GET', 'POST'], 'its-attend/(:segment)', 'ItsTraining::attend/$1');
$routes->GET('its-quotes', 'ItsQuotes::index');
$routes->GET('its-quotes-view/(:num)', 'ItsQuotes::view/$1');
$routes->POST('its-quotes-save', 'ItsQuotes::save');
$routes->POST('its-quotes-save/(:num)', 'ItsQuotes::save/$1');
$routes->POST('its-quotes-send/(:num)', 'ItsQuotes::send/$1');
$routes->match(['GET', 'POST'], 'its-approve/(:num)/(:segment)', 'ItsQuotes::approve/$1/$2');

$routes->GET('its-report/(:num)', 'ItsReport::view/$1');
$routes->POST('its-sign/(:num)', 'ItsReport::sign/$1');
$routes->match(['GET', 'POST'], 'its-verify', 'ItsReport::verify');

$routes->GET('its-admin', 'ItsAdmin::panel');
$routes->POST('its-admin-users', 'ItsAdmin::addUser');
$routes->POST('its-admin-user-update/(:num)', 'ItsAdmin::updateUser/$1');
$routes->POST('its-admin-user-delete/(:num)', 'ItsAdmin::deleteUser/$1');
$routes->POST('its-admin-settings', 'ItsAdmin::saveSettings');
$routes->POST('its-admin-reports', 'ItsAdmin::saveReports');
$routes->POST('its-admin-mail', 'ItsAdmin::mailTest');
$routes->POST('its-admin-mail-settings', 'ItsAdmin::saveMail');
$routes->POST('its-admin-sec', 'ItsAdmin::saveSecSettings');
$routes->GET('its-admin-audit-export', 'ItsAdmin::exportAudit');
$routes->POST('its-admin-schedules', 'ItsAdmin::addSchedule');
$routes->GET('its-admin-backup', 'ItsAdmin::backup');
$routes->POST('its-admin-theme', 'ItsAdmin::saveTheme');
$routes->POST('its-admin-templates', 'ItsAdmin::saveTemplate');
$routes->POST('its-admin-templates-delete/(:num)', 'ItsAdmin::deleteTemplate/$1');

$routes->GET('its-board', 'ItsBoard::index');
$routes->POST('its-board-move', 'ItsBoard::move');
$routes->GET('its-updates', 'ItsUpdates::index');
$routes->POST('its-updates-url', 'ItsUpdates::saveUrl');
$routes->POST('its-updates-check', 'ItsUpdates::check');
$routes->POST('its-updates-baseline', 'ItsUpdates::baseline');
$routes->POST('its-updates-backup', 'ItsUpdates::backupNow');
$routes->POST('its-updates-heal', 'ItsUpdates::heal');
$routes->GET('its-updates-download', 'ItsUpdates::download');
$routes->POST('its-updates-apply', 'ItsUpdates::apply');
$routes->POST('its-updates-upload', 'ItsUpdates::upload');
$routes->POST('its-updates-auto', 'ItsUpdates::toggleAuto');
$routes->POST('its-updates-clearlog', 'ItsUpdates::clearLog');
$routes->POST('its-updates-folder-upload', 'ItsUpdates::folderUpload');
$routes->POST('its-updates-folder-install', 'ItsUpdates::folderInstall');
$routes->POST('its-updates-folder-delete', 'ItsUpdates::folderDelete');
$routes->GET('its-updates-progress', 'ItsUpdates::progress');
$routes->POST('its-updates-restore', 'ItsUpdates::restore');
$routes->POST('its-updates-integrity-check', 'ItsUpdates::integrityCheck');

$routes->GET('its-cron-monthly', 'ItsMisc::cronMonthly');
$routes->GET('its-cron-weekly', 'ItsMisc::cronWeekly');
$routes->GET('its-cron-maintenance', 'ItsMisc::cronMaintenance');
$routes->GET('its-cron-device-monitor', 'ItsMisc::cronDeviceMonitor');
$routes->POST('its-admin-permissions', 'ItsIntegrations::savePermissions');
$routes->POST('its-api-key-add', 'ItsIntegrations::addKey');
$routes->POST('its-api-key-revoke/(:num)', 'ItsIntegrations::revokeKey/$1');
$routes->POST('its-webhook-add', 'ItsIntegrations::addHook');
$routes->POST('its-webhook-delete/(:num)', 'ItsIntegrations::deleteHook/$1');
$routes->POST('its-webhook-test', 'ItsIntegrations::testHook');
$routes->GET('api/v1/tickets', 'ItsApi::tickets');
$routes->GET('api/v1/tickets/(:num)', 'ItsApi::ticket/$1');
$routes->POST('api/v1/tickets', 'ItsApi::createTicket');
$routes->GET('api/v1/clients', 'ItsApi::clients');
$routes->GET('api/v1/assets', 'ItsApi::assets');
$routes->POST('its-portal-rate/(:num)', 'ItsPortal::rate/$1');
$routes->POST('its-tickets-meta/(:num)', 'ItsTickets::meta/$1');
$routes->POST('its-tickets-time/(:num)', 'ItsTickets::addTime/$1');
$routes->POST('its-tickets-bulk', 'ItsTickets::bulk');
$routes->POST('its-tickets-watch/(:num)', 'ItsTickets::watch/$1');
$routes->POST('its-tickets-merge/(:num)', 'ItsTickets::merge/$1');
$routes->match(['GET', 'POST'], 'its-rate/(:segment)', 'ItsTickets::rate/$1');
$routes->GET('its-kb', 'ItsKb::index');
$routes->POST('its-kb-save', 'ItsKb::save');
$routes->POST('its-kb-delete/(:num)', 'ItsKb::delete/$1');
$routes->POST('its-canned-save', 'ItsKb::cannedSave');
$routes->POST('its-canned-delete/(:num)', 'ItsKb::cannedDelete/$1');
$routes->GET('its-portal-kb', 'ItsKb::portal');
$routes->GET('its-search', 'ItsSearch::index');
$routes->GET('its-health', 'ItsHealth::index');
$routes->POST('its-health-domains', 'ItsHealth::saveDomain');
$routes->POST('its-health-domains-delete/(:num)', 'ItsHealth::deleteDomain/$1');
$routes->GET('its-logs', 'ItsHealth::logs');
$routes->GET('its-logs-download', 'ItsHealth::downloadLogs');
$routes->POST('its-logs-clear', 'ItsHealth::clearLogs');
$routes->GET('its-signins', 'ItsHealth::signins');
$routes->GET('its-cleanup', 'ItsHealth::cleanup');
$routes->POST('its-cleanup-run', 'ItsHealth::runCleanup');
$routes->GET('its-billing', 'ItsBilling::index');
$routes->POST('its-billing-save/(:num)', 'ItsBilling::save/$1');
$routes->GET('its-billing-csv', 'ItsBilling::csv');
$routes->POST('its-calendar-token', 'ItsBilling::icalToken');
$routes->GET('its-calendar.ics', 'ItsBilling::ical');

// Extension portal
$routes->GET('its-extensions', 'ItsExtensions::index');
$routes->POST('its-extensions-upload', 'ItsExtensions::upload');
$routes->POST('its-extensions-approve/(:num)', 'ItsExtensions::approve/$1');
$routes->POST('its-extensions-reject/(:num)', 'ItsExtensions::reject/$1');
$routes->POST('its-extensions-delete/(:num)', 'ItsExtensions::delete/$1');
$routes->GET('its-extensions-download/(:num)', 'ItsExtensions::download/$1');
// Extension subscription management (admin)
$routes->GET('its-ext-subscriptions', 'ItsExtSubscriptions::index');
$routes->POST('its-ext-pkg-save', 'ItsExtSubscriptions::savePackage');
$routes->POST('its-ext-pkg-delete/(:num)', 'ItsExtSubscriptions::deletePackage/$1');
$routes->POST('its-ext-sub-approve/(:num)', 'ItsExtSubscriptions::approve/$1');
$routes->POST('its-ext-sub-suspend/(:num)', 'ItsExtSubscriptions::suspend/$1');
$routes->POST('its-ext-sub-reactivate/(:num)', 'ItsExtSubscriptions::reactivate/$1');
$routes->POST('its-ext-sub-delete/(:num)', 'ItsExtSubscriptions::deleteSub/$1');
$routes->POST('its-ext-sub-create', 'ItsExtSubscriptions::createDirect');

// Website CMS (staff)
$routes->GET('its-site', 'ItsSite::index');
$routes->POST('its-site-page', 'ItsSite::savePage');
$routes->POST('its-site-page-delete/(:num)', 'ItsSite::deletePage/$1');
$routes->POST('its-site-item', 'ItsSite::saveItem');
$routes->POST('its-site-item-delete/(:num)', 'ItsSite::deleteItem/$1');
$routes->POST('its-site-settings', 'ItsSite::saveSettings');
$routes->POST('its-site-lead/(:num)', 'ItsSite::lead/$1');
$routes->POST('its-site-preview', 'ItsSite::preview');
$routes->POST('its-site-upload', 'ItsSite::upload');
$routes->POST('its-site-design', 'ItsSite::saveDesign');
$routes->POST('its-site-menu', 'ItsSite::saveMenu');

// Public extension marketplace and API.
$routes->GET('extensions', 'Website::extensions');
$routes->GET('extensions-download/(:num)', 'Website::extensionDownload/$1');
$routes->GET('extensions-api', 'Website::extensionsApi');
$routes->POST('extensions-access-request', 'ItsExtSubscriptions::register');
// Products showcase
$routes->GET('products', 'Website::products');

// Public website. The catch-all for CMS pages must stay LAST so it never shadows the routes above.
$routes->GET('sitemap.xml', 'Website::sitemap');
$routes->GET('robots.txt', 'Website::robots');
$routes->POST('contact', 'Website::contact');
$routes->POST('support-request', 'Website::supportRequest');
$routes->GET('([a-z0-9-]+)', 'Website::page/$1');
