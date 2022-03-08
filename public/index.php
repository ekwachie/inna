<?php
/**
 * @author      Desmond Evans Kwachie Jr <iamdesmondjr@gmail.com>
 * @copyright   Copyright (C), 2019 Desmond Evans Kwachie Jr.
 * @license     MIT LICENSE (https://opensource.org/licenses/MIT)
 *              Refer to the LICENSE file distributed within the package.
 *
 * @todo PDO exception and error handling
 * @category    Database
 * @example
 * $this->query('INSERT INTO tb (col1, col2, col3) VALUES(?,?,?)', $var1, $var2, $var3);
 *
 *
 */

use app\controllers\HomeController;
use app\controllers\AuthController;
use app\core\Application;

require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$config = [
    'db' => [
        'dsn' => $_ENV['DB_DSN'],
        'user' => $_ENV['DB_USER'],
        'password' => $_ENV['DB_PASSWORD'],
       
    ],
    'url' => $_ENV['DOMAIN']
];

define('BASE_URL', 'http://' . $config['url'] . '/');
define('STATIC_URL', BASE_URL. 'static');
define('MEDIA_URL', BASE_URL. 'img');

$app = new Application(dirname(__DIR__), $config);
$GLOBALS['db'] = $app->db;

$app->router->get('/', [HomeController::class, 'home']);
$app->router->get('/contact', [HomeController::class, 'contact']);
$app->router->post('/contact', [HomeController::class, 'contact']);

$app->router->get('/login', [AuthController::class, 'login']);
$app->router->post('/login', [AuthController::class, 'login']);

$app->router->get('/register', [AuthController::class, 'register']);
$app->router->post('/register', [AuthController::class, 'register']);

$app->router->get('/login/{id}', [AuthController::class, 'login']);
$app->router->get('/contact/{id:\d+}/{username}', [AuthController::class, 'login']);

$app->router->get('/logout', [AuthController::class, 'logout']);

$app->run();
