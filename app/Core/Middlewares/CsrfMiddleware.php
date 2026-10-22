<?php
namespace app\Core\Middlewares;

use app\Core\ApiException;
use app\Core\Application;
use app\Core\Utils\DUtil;

final class CsrfMiddleware extends BaseMiddleware
{
    public function execute(): void
    {
        $request = Application::$app->request;
        if (in_array($request->getMethod(), ['get', 'head', 'options'], true)) return;
        $token = $request->getHeader('X-CSRF-Token') ?? ($_POST['csrf_token'] ?? '');
        if (!is_string($token) || !DUtil::csrf_verifier($token)['valid']) throw new ApiException('CSRF_INVALID', 'Refresh the form and try again.', 403);
    }
}
