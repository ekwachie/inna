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

class Request
{
    private array $routeParams = [];

    public function getMethod()
    {
        return strtolower((string)($_SERVER['REQUEST_METHOD'] ?? 'get'));
    }

    public function getUrl()
    {
        $path = (string)($_SERVER['REQUEST_URI'] ?? '/');
        $position = strpos($path, '?');
        if ($position !== false) {
            $path = substr($path, 0, $position);
        }
        return $path;
    }

    public function isGet()
    {
        return $this->getMethod() === 'get';
    }

    public function isPost()
    {
        return $this->getMethod() === 'post';
    }

    public function getBody()
    {
        $data = [];
        if ($this->isGet()) {
            foreach ($_GET as $key => $value) {
                $data[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }
        if ($this->isPost()) {
            $contentType = (string)($_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '');
            if (stripos($contentType, 'application/json') !== false) {
                $json = file_get_contents('php://input');
                $decoded = json_decode($json ?: '', true);
                $data = is_array($decoded) ? $decoded : [];
            } else {
                foreach ($_POST as $key => $value) {
                    $data[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
                }
            }
        }
        return $data;
    }

    /**
     * @param $params
     * @return self
     */
    public function setRouteParams($params)
    {
        $this->routeParams = $params;
        return $this;
    }

    public function getRouteParams()
    {
        return $this->routeParams;
    }

    public function getRouteParam($param, $default = null)
    {
        return $this->routeParams[$param] ?? $default;
    }
}
