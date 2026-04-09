<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home\HomeController::index');

$routes->get('/login', 'Auth\LoginController::index');
$routes->get('/logout', 'Auth\LoginController::index');

// Rutas protegidas
$routes->group('admin', function ($routes) {
    $routes->get('/', 'Admin\DashboardController::index');
    $routes->group('settings', function ($routes) {
        $routes->get('config', 'Settings\ConfigController::index', ['as' => 'settings.config']);
        $routes->post('bank/create', 'Settings\BankController::create', ['as' => 'settings.bank.create']);
    });
});