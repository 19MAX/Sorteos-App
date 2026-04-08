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
});