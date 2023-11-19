<?php
/**
 * @author      Evans Kwachie <evans.kwachie@ucc.edu.gh>
 * @copyright   Copyright (C), 2019 Evans Kwachie.
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

$app->router->get('/', [HomeController::class, 'home']);
$app->router->get('/contact', [HomeController::class, 'contact']);
$app->router->post('/contact', [HomeController::class, 'contact']);

$app->router->get('/login', [AuthController::class, 'login']);
$app->router->post('/login', [AuthController::class, 'login']);

$app->router->get('/register', [AuthController::class, 'register']);
$app->router->post('/register', [AuthController::class, 'register']);

$app->router->get('/login/{id}', [AuthController::class, 'login']);
$app->router->get('/contact/{id:\d+}/{username}', [AuthController::class, 'login']);

// Ajax calls
$app->router->post('/ajax/{follow}', [AjaxController::class, 'follow']);

$app->router->get('/logout', [AuthController::class, 'logout']);

$app->run();
