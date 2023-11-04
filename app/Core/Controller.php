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

class Controller
{
    /**
     * @var \app\Core\Middlewares\BaseMiddleware[];
     */
    protected array $middlewares = [];
    public string $action = '';

    /** 
     * render page view
     * */
    public function render($view, $params = [])
    {
        return Application::$app->router->render($view, $params);
    }
    /**
     * redirect to url
     */
    public function redirect()
    {
        return Application::$app->response->redirect('/');
    }

    public function setFlash($type, $msg)
    {
        return '
        <script>
            swal("' . $msg . '", {
                icon: "' . $type . '",
            });
        </script>
        ';

    }


    public function middleware(BaseMiddleware $middleware)
    {
        $this->middlewares[] = $middleware;
    }

    /**
     * 
     * @return array
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * 
     * @param array $middlewares 
     * @return Controller
     */
    public function setMiddlewares(array $middlewares): self
    {
        $this->middlewares = $middlewares;
        return $this;
    }

    /**
     * for APi resources
     */
    public function apiMessage($status, $message, $errorCode = null)
    {
        return Application::$app->api->message($status, $message, $errorCode);
    }
}
