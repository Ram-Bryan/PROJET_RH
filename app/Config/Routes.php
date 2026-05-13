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
	$routes->get('conges/create', 'Employe\CongeController::create');
	$routes->post('conges', 'Employe\CongeController::store');
	$routes->post('conges/annuler/(:num)', 'Employe\CongeController::cancel/$1');
	$routes->get('profil', 'Employe\\ProfilController::index');
	$routes->post('profil', 'Employe\\ProfilController::update');
});
