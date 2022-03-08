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
namespace app\Core;
use app\Core\Middlewares\BaseMiddleware;

class Router
{
    public Request $request;
    public Response $response;
    protected array $routes = [];

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = new Response();
    }

    /**
     * get request
     */
    public function get(string $path, $callback)
    {
        $this->routes['get'][$path] = $callback;
    }

    /**
     * post request
     */
    public function post($path, $callback)
    {
        $this->routes['post'][$path] = $callback;
    }

    /**
     * resolving request
     */
    public function resolve()
    {
        $path = $this->request->getPath();
        $method = $this->request->method();
        $callback = $this->routes[$method][$path] ?? false;

        if ($callback == false) {
            return $this->render('404');
            // exit;
        }

        if (is_string($callback)) {
            return $this->render($callback);
        }

        if (is_array($callback)) {
            $controller = new $callback[0]();
            Application::$app->controller =  $controller;
            $controller->action = $callback[1];
            $callback[0] = $controller;

            foreach ($controller->getMiddlewares() as $middleware) {
                $middleware->execute();
            }
           
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
            Application::$ROOT_DIR . '/views/'
        );

        // Instantiate our Twig
        $twig = new \Twig\Environment($loader, [
            'cache' => Application::$ROOT_DIR . '/public/runtime/',
            'auto_reload' => true,
        ]);

        return $twig->render("$view.twig", $data);
    }
}
