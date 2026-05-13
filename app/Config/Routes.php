<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// Auth
$routes->get('/', 'Auth\LoginController::index');
$routes->post('login', 'Auth\LoginController::login');
$routes->get('logout', 'Auth\LoginController::logout');

// Employe (protected)
$routes->group('employe', ['filter' => 'auth:employe'], static function ($routes) {
	$routes->get('dashboard', 'Employe\DashboardController::index');
});

// RH (protected)
$routes->group('rh', ['filter' => 'auth:rh'], static function ($routes) {
	$routes->get('dashboard', 'Rh\DashboardController::index');
});

// Admin (protected)
$routes->group('admin', ['filter' => 'auth:admin'], static function ($routes) {
	$routes->get('dashboard', 'Admin\DashboardController::index');
});
