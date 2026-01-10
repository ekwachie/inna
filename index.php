<?php
/**
 * @author      Payperlez Team <inna@payperlez.org>
 * @copyright   Copyright (C), 2019. Payperlez Inc.
 * @license     MIT LICENSE (https://opensource.org/licenses/MIT)
 *              Refer to the LICENSE file distributed within the package.
 *
 * @todo PDO exception and error handling
 * @category    index
 * @example
 *
 *
 */
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';

use app\Controllers\AjaxController;
use app\Controllers\HomeController;
use app\Controllers\AuthController;
use app\Controllers\ApiController;
use app\Controllers\DocsController;

$app->router->get('/', [DocsController::class, 'index']);

$app->router->get('/about', [HomeController::class, 'about']);

// Documentation routes - must be before catch-all route
$app->router->get('/documentation/{path:[\w\/-]+}', [DocsController::class, 'show']);

$app->router->get('/contact', [HomeController::class, 'contact']);

// Ajax calls
$app->router->post('/ajax/{follow}', [AjaxController::class, 'follow']);

$app->router->get('/logout', [AuthController::class, 'logout']);

// API Routes with JWT Authentication
$app->router->post('/api/login', [ApiController::class, 'login']);
$app->router->get('/api/profile', [ApiController::class, 'profile']);
$app->router->get('/api/admin/users', [ApiController::class, 'adminUsers']);
$app->router->post('/api/refresh', [ApiController::class, 'refresh']);
$app->router->get('/api/public', [ApiController::class, 'public']);

$app->run();
