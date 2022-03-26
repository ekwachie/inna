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
use Twig\Extra\String\StringExtension;

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
     * get callback 
     */
    public function getCallback()
    {
        $path = $this->request->getPath();
        $method = $this->request->method();

        // trim slashes
        $url = trim($path, characters: '/');

        // get ll routes from the current request method
        $routes = $this->routes[$method] ?? [];

        $routeParams = false;

        // start iterating registered routes
        foreach ($routes as $route => $callback) {
                $route = trim($route, characters: '/');
                $routeNames = [];

                if(!$route){
                    continue;
                }
                // /login/{id}
                // /
                // Find all route names from route and save in $routeNames
            if (preg_match_all('/\{(\w+[A-Za-z0-9-\/-:.\/_?&=#]+)?}/', $route, $matches)) {
                $routeNames = $matches[1];
            }

            // // Convert route name into regex pattern
            $routeRegex = "@^" . preg_replace_callback('/\{\w+[A-Za-z0-9-\/-:.\/_?&=#]+?}/', fn($m) => isset($m[2]) ? "({$m[2]})" : '(\w+)', $route) . "$@";
            

            // // Test and match current route against $routeRegex
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
        $path = $this->request->getPath();
        $method = $this->request->method();
        $callback = $this->routes[$method][$path] ?? false;

        if(!$callback){
            $callback = $this->getCallback();
            if ($callback == false) {
                return $this->render('404');
            }
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
