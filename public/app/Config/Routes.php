<?php
use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

// IT Support System routes
$routes->get('its-manifest', 'ItsMisc::manifest');
$routes->get('its-qr/(:num)', 'ItsMisc::qr/$1');
$routes->get('its-cron-monthly', 'ItsMisc::cronMonthly');
$routes->get('its-cron-weekly', 'ItsMisc::cronWeekly');
$routes->get('its-cron-maintenance', 'ItsMisc::cronMaintenance');
$routes->match(['get','post'], 'its-install', 'ItsAuth::install');
$routes->match(['get','post'], 'its-login', 'ItsAuth::login');
$routes->post('its-logout', 'ItsAuth::logout');
$routes->match(['get','post'], 'its-verify', 'ItsReport::verify');
$routes->match(['get','post'], 'its-approve/(:num)/(:segment)', 'ItsQuotes::approve/$1/$2');

$routes->get('its-dashboard', 'ItsDash::index');
$routes->get('its-help', 'ItsDash::help');
$routes->get('its-features', 'ItsDash::features');

$routes->get('its-clients', 'ItsClients::index');
$routes->get('its-clients-edit/(:num)', 'ItsClients::edit/$1');
$routes->post('its-clients-save/(:num)?', 'ItsClients::save/$1');

$routes->get('its-assets', 'ItsAssets::index');
$routes->get('its-assets-edit/(:num)', 'ItsAssets::edit/$1');
$routes->get('its-assets-view/(:num)', 'ItsAssets::view/$1');
$routes->post('its-assets-save/(:num)?', 'ItsAssets::save/$1');

$routes->get('its-tickets', 'ItsTickets::index');
$routes->post('its-tickets', 'ItsTickets::store');
$routes->get('its-tickets-view/(:num)', 'ItsTickets::view/$1');
$routes->post('its-tasks/(:num)', 'ItsTickets::addTask/$1');

$routes->get('its-report/(:num)', 'ItsReport::view/$1');
$routes->post('its-sign/(:num)', 'ItsReport::sign/$1');

$routes->get('its-quotes', 'ItsQuotes::index');
$routes->get('its-quotes-view/(:num)', 'ItsQuotes::view/$1');
$routes->post('its-quotes-save/(:num)?', 'ItsQuotes::save/$1');
$routes->post('its-quotes-send/(:num)', 'ItsQuotes::send/$1');

$routes->get('its-admin', 'ItsAdmin::panel');
$routes->post('its-admin-users', 'ItsAdmin::addUser');
$routes->post('its-admin-settings', 'ItsAdmin::saveSettings');
$routes->post('its-admin-reports', 'ItsAdmin::saveReports');
$routes->post('its-admin-mail', 'ItsAdmin::mailTest');
$routes->post('its-admin-schedules', 'ItsAdmin::addSchedule');
$routes->get('its-admin-backup', 'ItsAdmin::backup');