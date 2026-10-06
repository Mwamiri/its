<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->GET('/', 'Home::index');
$routes->match(['GET', 'POST'], 'its-install', 'ItsAuth::install');
$routes->match(['GET', 'POST'], 'its-login', 'ItsAuth::login');
$routes->POST('its-logout', 'ItsAuth::logout');
$routes->match(['GET', 'POST'], 'its-2fa', 'ItsAuth::twoFactor');
$routes->GET('its-security', 'ItsSecurity::index');
$routes->POST('its-security-enable', 'ItsSecurity::enable');
$routes->POST('its-security-disable', 'ItsSecurity::disable');
$routes->POST('its-security-password', 'ItsSecurity::password');
$routes->POST('its-security-policy', 'ItsSecurity::policy');
$routes->POST('its-admin-reset2fa/(:num)', 'ItsSecurity::resetUser2fa/$1');
$routes->GET('its-dashboard', 'ItsDash::index');
$routes->GET('its-dashboard-stats', 'ItsDash::stats');
$routes->GET('its-help', 'ItsDash::help');
$routes->GET('its-features', 'ItsDash::features');
$routes->GET('its-manifest', 'ItsMisc::manifest');

$routes->GET('its-tickets', 'ItsTickets::index');
$routes->POST('its-tickets-reply/(:num)', 'ItsTickets::reply/$1');
$routes->GET('its-portal', 'ItsPortal::index');
$routes->match(['GET', 'POST'], 'its-portal-new', 'ItsPortal::create');
$routes->GET('its-portal-ticket/(:num)', 'ItsPortal::view/$1');
$routes->POST('its-portal-reply/(:num)', 'ItsPortal::reply/$1');
$routes->POST('its-tickets', 'ItsTickets::store');
$routes->GET('its-tickets-view/(:num)', 'ItsTickets::view/$1');
$routes->POST('its-tasks/(:num)', 'ItsTickets::addTask/$1');

$routes->GET('its-clients', 'ItsClients::index');
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
$routes->POST('its-admin-settings', 'ItsAdmin::saveSettings');
$routes->POST('its-admin-reports', 'ItsAdmin::saveReports');
$routes->POST('its-admin-mail', 'ItsAdmin::mailTest');
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
$routes->GET('its-kb', 'ItsKb::index');
$routes->POST('its-kb-save', 'ItsKb::save');
$routes->POST('its-kb-delete/(:num)', 'ItsKb::delete/$1');
$routes->POST('its-canned-save', 'ItsKb::cannedSave');
$routes->POST('its-canned-delete/(:num)', 'ItsKb::cannedDelete/$1');
$routes->GET('its-portal-kb', 'ItsKb::portal');
$routes->GET('its-search', 'ItsSearch::index');
$routes->GET('its-health', 'ItsHealth::index');
$routes->GET('its-billing', 'ItsBilling::index');
$routes->POST('its-billing-save/(:num)', 'ItsBilling::save/$1');
$routes->GET('its-billing-csv', 'ItsBilling::csv');
$routes->POST('its-calendar-token', 'ItsBilling::icalToken');
$routes->GET('its-calendar.ics', 'ItsBilling::ical');
