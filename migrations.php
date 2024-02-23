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
 * $this->query('INSERT INTO tb (col1, col2, col3) VALUES(?,?,?)', $var1, $var2, $var3);
 *
 *
 */

require_once __DIR__ . '/vendor/autoload.php';

// error logging to file
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', './log/log_' . date("j.n.Y") . '.log');


use app\Core\Application;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
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

$app = new Application(__DIR__, $config);

//for the action
$action = $argv[1];

// for migration name 
$migration_name = $argv[2];

if (!empty($action) || !empty($migration_name)) {
    switch ($action) {
        case 'add':
            if (!empty($migration_name)) {
                $app->db->Add($migration_name);
            } else {
                echo "Migration name key not set";
            }
            break;
        case 'update':
            $app->db->applyMigrations();
            break;

        default:
            echo "\033[38;2;255;0;0m check command format ".$action;
            break;
    }
} else {
    echo "\033[38;2;255;0;0m Migration action or name key not set";
}

// $app->db->applyMigrations();
