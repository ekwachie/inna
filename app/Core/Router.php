<?php

/**
 * @author      Evans Kwachie <evans.kwachie@ucc.edu.gh>
 * @copyright   Copyright (C), 2019 Evans Kwachie.
 * @license     MIT LICENSE (https://opensource.org/licenses/MIT)
 *              Refer to the LICENSE file distributed within the package.
 *
 *
 */

namespace app\Core;

use Twig\Extra\String\StringExtension;

class Router
{
    public Request $request;
    public Response $response;
    private array $routeMap = [];

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function get(string $url, $callback)
    {
        $this->routeMap['get'][$url] = $callback;
    }

    public function post(string $url, $callback)
    {
        $this->routeMap['post'][$url] = $callback;
    }

    /**
     * @return array
     */

    public function getRouteMap($method): array
    {
        return $this->routeMap[$method] ?? [];
    }


    /**
     * get callback 
     */
    public function getCallback()
    {
        $method = $this->request->getMethod();
        $url = $this->request->getUrl();

        $url = trim($url, '/');

        $routes = $this->getRouteMap($method);

        $routeParams = false;

        foreach ($routes as $route => $callback) {

            $route = trim($route, '/');
            $routeNames = [];

            if (!$route) {
                continue;
            }

            if (preg_match_all('/\{(\w+)(:[^}]+)?}/', $route, $matches)) {
                $routeNames = $matches[1];
            }

            $routeRegex = "@^" . preg_replace_callback('/\{\w+(:([^}]+))?}/', fn ($m) => isset($m[2]) ? "({$m[2]})" : '(\w+)', $route) . "$@";

            if (preg_match_all($routeRegex, $url, $valueMatches)) {
                $values = [];
                for ($i = 1; $i < count($valueMatches); $i++) {
                    $values[] = $valueMatches[$i][0];
                }
                $routeParams = array_combine($routeNames, $values);

                $this->request->setRouteParams($routeParams);
                return $callback;
            }
        }

        return false;
    }

    /**
     * resolving request
     */
    public function resolve()
    {
        $method = $this->request->getMethod();
        $url = $this->request->getUrl();
        $callback = $this->routeMap[$method][$url] ?? false;
        if (!$callback) {

            $callback = $this->getCallback();

            if ($callback === false) {
                return $this->render('404');
            }
        }
        if (is_string($callback)) {
            return $this->render($callback);
        }
        if (is_array($callback)) {
            $controller = new $callback[0];
            $controller->action = $callback[1];
            Application::$app->controller = $controller;
            $middlewares = $controller->getMiddlewares();
            foreach ($middlewares as $middleware) {
                $middleware->execute();
            }
            $callback[0] = $controller;
        }
        return call_user_func($callback, $this->request, $this->response);
    }

    /**
     * rending view using twig
     */
    public function render($view, $data = [])
    {
        //   var_dump($data);
        $loader = new \Twig\Loader\FilesystemLoader(
            Application::$ROOT_DIR . '/public/views/'
        );

        // Instantiate our Twig
        $twig = new \Twig\Environment($loader, [
            'cache' => Application::$ROOT_DIR . '/public/runtime/',
            'auto_reload' => true,
        ]);
        $twig->addExtension(new StringExtension());

        return $twig->render("$view.twig", $data);
    }
}
