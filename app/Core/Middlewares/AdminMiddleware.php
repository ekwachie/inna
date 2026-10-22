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
        if ($this->actions && !in_array(Application::$app->controller->action, $this->actions, true)) return;
        (new AuthMiddleware())->execute();
        $user = Session::get('user');
        $user = $user[0] ?? $user;
        $roles = $user['roles'] ?? $user['role'] ?? [];
        $roles = is_string($roles) ? array_map('trim', explode(',', $roles)) : $roles;
        if (!is_array($roles) || !in_array('admin', $roles, true)) {
            throw new \app\Core\ApiException('FORBIDDEN', 'Administrator access is required.', 403);
        }
    }
}
