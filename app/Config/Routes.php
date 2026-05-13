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
	$routes->get('conges', 'Employe\CongeController::index');
	$routes->post('conges/store', 'Employe\CongeController::store');
	$routes->post('conges/(:num)/annuler', 'Employe\CongeController::annuler/$1');
});

// RH (protected)
$routes->group('rh', ['filter' => 'auth:rh'], static function ($routes) {
	$routes->get('dashboard', 'Rh\DashboardController::index');
	$routes->get('demandes', 'Rh\DemandeController::index');
	$routes->post('demandes/(:num)/approuver', 'Rh\DemandeController::approuver/$1');
	$routes->post('demandes/(:num)/refuser', 'Rh\DemandeController::refuser/$1');
});

// Admin (protected)
$routes->group('admin', ['filter' => 'auth:admin'], static function ($routes) {
	$routes->get('dashboard', 'Admin\DashboardController::index');
	$routes->get('employes', 'Admin\\EmployeController::index');
	$routes->post('employes/store', 'Admin\\EmployeController::store');
	$routes->post('employes/update/(:num)', 'Admin\\EmployeController::update/$1');
	$routes->post('employes/toggle-status/(:num)', 'Admin\\EmployeController::toggleStatus/$1');
	$routes->get('types-conge', 'Admin\\TypeCongeController::index');
	$routes->post('types-conge/store', 'Admin\\TypeCongeController::store');
});
