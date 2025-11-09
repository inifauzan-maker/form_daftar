<?php

use App\Controllers\ActivityLogController;
use App\Controllers\Api\ProgramController;
use App\Controllers\Api\SchoolController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\InvoiceController;
use App\Controllers\PermissionController;
use App\Controllers\ProgramDetailController;
use App\Controllers\ProductController;
use App\Controllers\RegistrationController;
use App\Controllers\RoleController;
use App\Controllers\UserController;
use App\Core\Router;

/** @var Router $router */
$router->get('/login', [AuthController::class, 'showLoginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/', [RegistrationController::class, 'index']);
$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/api/dashboard/metrics', [DashboardController::class, 'metrics']);
$router->get('/api/dashboard/geography', [DashboardController::class, 'geography']);
$router->get('/api/registrations', [DashboardController::class, 'list']);
$router->get('/dashboard/export', [DashboardController::class, 'export']);
$router->get('/dashboard/invoice', [InvoiceController::class, 'show']);
$router->get('/products', [ProductController::class, 'index']);
$router->get('/api/schools', [SchoolController::class, 'search']);
$router->get('/api/programs', [ProgramController::class, 'index']);
$router->post('/api/registrations', [RegistrationController::class, 'store']);
$router->post('/api/registrations/status', [DashboardController::class, 'updateStatus']);
$router->get('/programs', [ProgramDetailController::class, 'index']);
$router->post('/programs/save', [ProgramDetailController::class, 'save']);
$router->post('/programs/delete', [ProgramDetailController::class, 'delete']);
$router->get('/users', [UserController::class, 'index']);
$router->get('/api/users', [UserController::class, 'list']);
$router->post('/api/users', [UserController::class, 'store']);
$router->post('/api/users/update', [UserController::class, 'update']);
$router->post('/api/users/delete', [UserController::class, 'delete']);
$router->get('/activity-logs', [ActivityLogController::class, 'index']);
$router->get('/api/activity-logs', [ActivityLogController::class, 'list']);
$router->get('/api/roles', [RoleController::class, 'list']);
$router->post('/api/roles', [RoleController::class, 'store']);
$router->post('/api/roles/update', [RoleController::class, 'update']);
$router->post('/api/roles/delete', [RoleController::class, 'delete']);
$router->get('/api/permissions', [PermissionController::class, 'list']);
$router->post('/api/permissions', [PermissionController::class, 'store']);
$router->post('/api/permissions/update', [PermissionController::class, 'update']);
$router->post('/api/permissions/delete', [PermissionController::class, 'delete']);
