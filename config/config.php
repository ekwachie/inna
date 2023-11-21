<?php

/**
 * @author      Evans Kwachie <evans.kwachie@ucc.edu.gh>
 * @copyright   Copyright (C), 2019 Evans Kwachie.
 * @license     MIT LICENSE (https://opensource.org/licenses/MIT)
 *              Refer to the LICENSE file distributed within the package.
 *
 * @todo PDO exception and error handling
 * @category    Database
 * @example
 *
 * All configurations can be found in here.
 */

use app\Core\Application;

$dotenv = Dotenv\Dotenv::createImmutable($_SERVER['DOCUMENT_ROOT']);
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
define('STATIC_URL', BASE_URL . 'public/static');
define('MEDIA_URL', BASE_URL . 'public/img');

$app = new Application($_SERVER['DOCUMENT_ROOT'], $config);
