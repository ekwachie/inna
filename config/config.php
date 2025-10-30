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

// error logging to file
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', $_SERVER['DOCUMENT_ROOT'] . '/log/errors/error_log_' . date("j.n.Y") . '.log');
// Set the default timezone to Africa/Accra
date_default_timezone_set('Africa/Accra');

use app\Core\Application;
use GeoIp2\Database\Reader;
use app\Core\Utils\ActivityLogService;



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

// Prevent framing from any domain except your own
header("Content-Security-Policy: frame-ancestors 'self'");
// Add X-Content-Type-Options header to prevent MIME sniffing
header('X-Content-Type-Options: nosniff');
// Remove the X-Powered-By header
header_remove('X-Powered-By');
// Set the HSTS header
header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");


// Setting a cookie with HttpOnly flag
setcookie('cookie_name', 'cookie_value', [
    'expires' => time() + 3600, // 1 hour expiration
    'path' => '/',
    'secure' => true, // Ensures the cookie is sent over HTTPS
    'httponly' => true, // HttpOnly flag to prevent client-side access
    'samesite' => 'Strict' // Optional: SameSite attribute for CSRF protection
]);

// This reader object should be reused across lookups as creation of it is
// expensive.
$reader = new Reader($_SERVER['DOCUMENT_ROOT'] .'/config/GeoLite2-Country.mmdb');

define('BASE_URL', 'http://' . $config['url'] . '/');
define('STATIC_URL', BASE_URL . 'public/static');
define('MEDIA_URL', BASE_URL . 'public/img');
define('GEO_RDR', $reader);


$app = new Application($_SERVER['DOCUMENT_ROOT'], $config);

ActivityLogService::logRequest($app->request);