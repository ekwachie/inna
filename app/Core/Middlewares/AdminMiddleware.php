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
namespace app\Core\Middlewares;

use app\Core\Application;
use app\Core\Utils\Session;

class AdminMiddleware extends BaseMiddleware
{
    public array $actions = [];

    public function __construct(array $actions = [])
    {
        $this->actions = $actions;
    }
    /**
     *
     * @return mixed
     */
    public function execute()
    {
        if (empty(Session::issert('user'))) {
            if (empty($this->action) || in_array(Application::$app->controller->action, $this->actions)) {
                throw new \Exception('ADMIN001 - You do not have access');
                // Application::$app->response->redirect('/');
            }
        }
    }
}