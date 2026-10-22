# Middleware

## Register middleware

```php
<?php
namespace app\Controllers;

use app\Core\Controller;
use app\Core\Middlewares\AuthMiddleware;
use app\Core\Middlewares\CsrfMiddleware;

final class AccountController extends Controller
{
    public function __construct()
    {
        $this->middleware(new AuthMiddleware());
        $this->middleware(new CsrfMiddleware());
    }
}
```

Middleware runs before the selected controller action, in registration order. A middleware extends BaseMiddleware and implements execute().

## Authentication and roles

AuthMiddleware optionally takes an array of protected action names. An empty list protects every action. AdminMiddleware has the same action-selection behavior and requires a signed-in actor with admin in roles (array) or role (comma-separated string). Guests receive 401; signed-in non-admins receive 403.

These session checks do not automatically query application user tables. Your application must refresh authorization after role changes and enforce resource ownership.

## CSRF

CsrfMiddleware skips GET, HEAD and OPTIONS. Other methods require a valid X-CSRF-Token header or csrf_token form value. DUtil::csrf_token() creates a token and expiry in the session; DUtil::csrf_verifier() checks it using hash_equals. Tokens expire after 15 minutes.

Use CSRF for cookie/session authentication. Server-to-server APIs use explicit credentials and webhook endpoints use signatures; do not attach CSRF middleware to a webhook receiver.

## Custom context

```php
$request = \app\Core\Application::$app->request;
$request->setAttribute('actor', $authorizedUser);
```

Read that value through getAttribute('actor') in the action. Keep sensitive authorization decisions on the server, and throw ApiException to stop a request with an appropriate public error.

