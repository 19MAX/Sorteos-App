<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home\HomeController::index');
$routes->get('/comprar', 'Home\HomeController::comprar');
$routes->get('/mis-boletos', 'Home\HomeController::misBoletos');
$routes->post('/buscar-boletos', 'Home\HomeController::buscarBoletos');

$routes->get('/login', 'Auth\LoginController::index');
$routes->post('/login', 'Auth\LoginController::login');
$routes->get('/logout', 'Auth\LoginController::logout');

// Rutas protegidas
$routes->group('admin', ['filter' => 'adminauth'], function ($routes) {
    $routes->get('/', 'Admin\DashboardController::index');

    // Tickets
    $routes->get('tickets/generate', 'Admin\TicketsController::index', ['as' => 'admin.tickets.generate']);
    $routes->post('tickets/generate-process', 'Admin\TicketsController::generate', ['as' => 'admin.tickets.generate.process']);
    $routes->get('tickets/data', 'Admin\TicketsController::data', ['as' => 'admin.tickets.data', 'filter' => 'adminauth']);
    $routes->get('tickets/export', 'Admin\TicketsController::export', ['as' => 'admin.tickets.export', 'filter' => 'adminauth']);
    $routes->get('tickets/:id', 'Admin\TicketsController::show', ['as' => 'admin.tickets.show', 'filter' => 'adminauth']);

    // Transactions
    $routes->get('transactions', 'Admin\TransactionController::index', ['as' => 'admin.transactions.index']);
    $routes->post('transactions/mark-as-paid', 'Admin\TransactionController::markAsPaid', ['as' => 'admin.transactions.markAsPaid']);
    $routes->post('transactions/reject', 'Admin\TransactionController::reject', ['as' => 'admin.transactions.reject']);
    $routes->post('transactions/expire-expired', 'Admin\TransactionController::expireExpired', ['as' => 'admin.transactions.expireExpired']);

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
    $routes->post('orden/crear', 'Api\OrdenController::crear');
    $routes->get('orden/verificar', 'Api\OrdenController::verificar');
    $routes->get('tickets/disponibles', 'Api\OrdenController::disponibles');
});

// Payphone
$routes->post('payphone/pagar', 'PayphoneController::pagar');
$routes->get('payphone/respuesta', 'Payphone\RespuestaController::respuesta');