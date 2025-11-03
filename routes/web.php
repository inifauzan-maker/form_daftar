<?php

use App\Controllers\Api\ProgramController;
use App\Controllers\Api\SchoolController;
use App\Controllers\DashboardController;
use App\Controllers\InvoiceController;
use App\Controllers\RegistrationController;
use App\Core\Router;

/** @var Router $router */
$router->get('/', [RegistrationController::class, 'index']);
$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/api/registrations', [DashboardController::class, 'list']);
$router->get('/dashboard/export', [DashboardController::class, 'export']);
$router->get('/dashboard/invoice', [InvoiceController::class, 'show']);
$router->get('/api/schools', [SchoolController::class, 'search']);
$router->get('/api/programs', [ProgramController::class, 'index']);
$router->post('/api/registrations', [RegistrationController::class, 'store']);
$router->post('/api/registrations/status', [DashboardController::class, 'updateStatus']);
