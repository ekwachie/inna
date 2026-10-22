<?php

/**
 * @author      Payperlez Team <inna@payperlez.org>
 * @copyright   Copyright (C), 2019. Payperlez Inc.
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

namespace app\Core;

use app\Core\Utils\DUtil;
use app\Core\Utils\Session;

class Application
{
    public static string $ROOT_DIR;
    public Router $router;
    public Request $request;
    public Response $response;
    public Session $session;
    public Database $db;
    public DApiController $api;
    public Controller $controller;
    public static Application $app;



    public function __construct($rootPath, array $config)
    {
        Session::init();
        self::$ROOT_DIR = $rootPath;
        self::$app = $this;
        $this->request = new Request();
        $this->response = new Response();
        $this->session = new Session();
        $this->controller = new Controller();
        $this->router = new Router($this->request, $this->response);
        $this->db = (new \ReflectionClass(Database::class))->newLazyProxy(
            fn () => new Database($config['db'] ?? [])
        );
        $this->api = new DApiController();
    }
    public function run()
    {
        try {
            $content = $this->router->resolve();
            if ($this->request->getMethod() !== 'head') echo $content;
        } catch (\Throwable $error) {
            $known = $error instanceof ApiException;
            $status = $known ? $error->status : 500;
            if (!$known) error_log('Request ' . ApiResponse::requestId() . ' failed [' . get_class($error) . ']');
            if (str_starts_with($this->request->getUrl(), '/api/') || str_contains($this->request->getHeader('Accept', ''), 'application/json')) {
                ApiResponse::error($known ? $error->errorCode : 'INTERNAL_ERROR', $known ? $error->getMessage() : 'An internal error occurred.', $status, $known ? $error->fields : []);
            }
            http_response_code($status);
            header('Content-Type: text/html; charset=utf-8');
            if ($this->request->getMethod() !== 'head') {
                echo $status === 404 ? $this->router->render('404', ['static' => defined('STATIC_URL') ? STATIC_URL : '/public/static']) : '<h1>' . $status . '</h1><p>' . htmlspecialchars($known ? $error->getMessage() : 'An internal error occurred.', ENT_QUOTES, 'UTF-8') . '</p>';
            }
        }
    }
}
