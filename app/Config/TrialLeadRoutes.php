<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Public free-trial lead capture. Kept in a separate route file so existing routes remain untouched.
$routes->get('start-free-trial', 'Public\TrialLeadController::index');
$routes->post('start-free-trial', 'Public\TrialLeadController::store');

// Existing landing-page Start Free Trial buttons already point to /register.
$routes->get('register', 'Public\TrialLeadController::index');

// Super-admin lead management.
$routes->get('super/trial-leads', 'Admin\TrialLeadController::index', ['filter' => 'auth:super_admin']);
$routes->post('super/trial-leads/status/(:num)', 'Admin\TrialLeadController::updateStatus/$1', ['filter' => 'auth:super_admin']);
$routes->post('super/trial-leads/notes/(:num)', 'Admin\TrialLeadController::updateNotes/$1', ['filter' => 'auth:super_admin']);
