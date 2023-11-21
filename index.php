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

$app->router->get('/about', [HomeController::class, 'about']);

$app->router->get('/docs', [HomeController::class, 'documentation']);

$app->router->get('/contact', [HomeController::class, 'contact']);

// Ajax calls
$app->router->post('/ajax/{follow}', [AjaxController::class, 'follow']);

$app->router->get('/logout', [AuthController::class, 'logout']);

$app->run();
