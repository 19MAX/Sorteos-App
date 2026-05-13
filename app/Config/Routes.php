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
    $routes->get('tickets/(:num)', 'Admin\TicketsController::show/$1', ['as' => 'admin.tickets.show', 'filter' => 'adminauth']);

    // Transactions
    $routes->get('transactions', 'Admin\TransactionController::index', ['as' => 'admin.transactions.index']);
    $routes->get('transactions/(:num)/tickets', 'Admin\TransactionController::tickets/$1', ['as' => 'admin.transactions.tickets']);
    $routes->get('payphone-transactions', 'Admin\PayphoneTransactionController::index', ['as' => 'admin.payphoneTransactions.index']);
    $routes->post('transactions/mark-as-paid', 'Admin\TransactionController::markAsPaid', ['as' => 'admin.transactions.markAsPaid']);
    $routes->post('transactions/reject', 'Admin\TransactionController::reject', ['as' => 'admin.transactions.reject']);
    $routes->post('transactions/expire-expired', 'Admin\TransactionController::expireExpired', ['as' => 'admin.transactions.expireExpired']);
    $routes->post('transactions/delete-old', 'Admin\TransactionController::deleteOld', ['as' => 'admin.transactions.deleteOld']);

    // Participants
    $routes->get('participants', 'Admin\ParticipantController::index', ['as' => 'admin.participants.index']);
    $routes->get('participants/data', 'Admin\ParticipantController::data', ['as' => 'admin.participants.data']);
    $routes->get('participants/buscar', 'Admin\ParticipantController::buscar', ['as' => 'admin.participants.buscar']);
    $routes->get('participants/create', 'Admin\ParticipantController::create', ['as' => 'admin.participants.create']);
    $routes->post('participants/store', 'Admin\ParticipantController::store', ['as' => 'admin.participants.store']);
    $routes->get('participants/edit/(:num)', 'Admin\ParticipantController::edit/$1', ['as' => 'admin.participants.edit']);
    $routes->post('participants/update/(:num)', 'Admin\ParticipantController::update/$1', ['as' => 'admin.participants.update']);
    $routes->post('participants/delete/(:num)', 'Admin\ParticipantController::delete/$1', ['as' => 'admin.participants.delete']);

    // Physical Sales
    $routes->get('physical-sales', 'Admin\PhysicalSaleController::index', ['as' => 'admin.physicalSales.index']);
    $routes->post('physical-sales/buscar-cedula', 'Admin\PhysicalSaleController::buscarCedula', ['as' => 'admin.physicalSales.buscarCedula']);
    $routes->post('physical-sales/guardar-participante', 'Admin\PhysicalSaleController::guardarParticipante', ['as' => 'admin.physicalSales.guardarParticipante']);
    $routes->post('physical-sales/vender-boletos', 'Admin\PhysicalSaleController::venderBoletos', ['as' => 'admin.physicalSales.venderBoletos']);

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