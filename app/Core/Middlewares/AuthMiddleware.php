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

class AuthMiddleware extends BaseMiddleware
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
        if ($this->actions && !in_array(Application::$app->controller->action, $this->actions, true)) return;
        $user = Session::get('user');
        $user = is_array($user) ? ($user[0] ?? $user) : [];
        if (!is_array($user) || (empty($user['id']) && empty($user['username']))) {
            throw new \app\Core\ApiException('UNAUTHENTICATED', 'Sign in to continue.', 401);
        }
    }
}
