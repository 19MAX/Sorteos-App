<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home\HomeController::index');
$routes->get('/comprar', 'Home\HomeController::comprar');
$routes->get('/mis-boletos', 'Home\HomeController::misBoletos');

$routes->get('/login', 'Auth\LoginController::index');
$routes->get('/logout', 'Auth\LoginController::index');

// Rutas protegidas
$routes->group('admin', function ($routes) {
    $routes->get('/', 'Admin\DashboardController::index');

    // Tickets
    $routes->get('tickets/generate', 'Admin\TicketsController::index', ['as' => 'admin.tickets.generate']);
    $routes->post('tickets/generate-process', 'Admin\TicketsController::generate', ['as' => 'admin.tickets.generate.process']);
    $routes->get('tickets/data', 'Admin\TicketsController::data', ['as' => 'admin.tickets.data']);

    $routes->group('settings', function ($routes) {
        $routes->get('config', 'Settings\ConfigController::index', ['as' => 'settings.config']);
        $routes->post('bank/create', 'Settings\BankController::create', ['as' => 'settings.bank.create']);
        $routes->post('bank/update/(:num)', 'Settings\BankController::update/$1', ['as' => 'settings.bank.update']);
        $routes->post('bank/delete/(:num)', 'Settings\BankController::delete/$1', ['as' => 'settings.bank.delete']);
        $routes->post('tickets/settings', 'Settings\ConfigController::save', ['as' => 'settings.tickets.save']);
    });
});

// Rutas API
$routes->group('api', function ($routes) {
    $routes->post('cedula', 'Home\HomeController::cedula');
});