# Middleware

Middleware provides a convenient mechanism for filtering HTTP requests entering your application. Middleware can perform tasks like authentication, logging, and more.

## Creating Middleware

### Basic Middleware

All middleware extends the `BaseMiddleware` class:

```php
<?php

namespace app\Core\Middlewares;

use app\Core\Middlewares\BaseMiddleware;

class CustomMiddleware extends BaseMiddleware
{
    public function execute()
    {
        // Your middleware logic here
        // If validation fails, throw an exception or redirect
    }
}
```

## Built-in Middleware

### AuthMiddleware

Requires user authentication:

```php
<?php

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
    
    public function execute()
    {
        if (empty(Session::issert('user'))) {
            if (empty($this->action) || in_array(Application::$app->controller->action, $this->actions)) {
                throw new \Exception('APPx001 - You do not have access');
            }
        }
    }
}
```

### AdminMiddleware

Requires admin role:

```php
<?php

namespace app\Core\Middlewares;

use app\Core\Application;
use app\Core\Utils\Session;

class AdminMiddleware extends BaseMiddleware
{
    public function execute()
    {
        $session = Application::$app->session;
        
        if (!$session->issert('user_id')) {
            throw new \Exception('Authentication required');
        }
        
        $userId = $session->get('user_id');
        $db = Application::$app->db;
        
        $user = $db->select(
            "SELECT role FROM users WHERE id = :id",
            ['id' => $userId]
        );
        
        if (empty($user) || $user[0]['role'] !== 'admin') {
            throw new \Exception('Admin access required');
        }
    }
}
```

## Using Middleware

### In Controllers

Attach middleware in the controller constructor:

```php
use app\Core\Middlewares\AuthMiddleware;
use app\Core\Middlewares\AdminMiddleware;

class DashboardController extends Controller
{
    public function __construct()
    {
        // Require auth for all actions
        $this->middleware(new AuthMiddleware());
    }
    
    public function index(Request $request, Response $response)
    {
        // This action requires authentication
    }
}
```

### Specific Actions

Apply middleware only to specific actions:

```php
public function __construct()
{
    // Only require auth for 'settings' and 'profile' actions
    $this->middleware(new AuthMiddleware(['settings', 'profile']));
}
```

### Multiple Middleware

Apply multiple middleware:

```php
public function __construct()
{
    $this->middleware(new AuthMiddleware());
    $this->middleware(new AdminMiddleware());
}
```

## Custom Middleware Examples

### Logging Middleware

```php
<?php

namespace app\Core\Middlewares;

use app\Core\Middlewares\BaseMiddleware;
use app\Core\Application;
use app\Core\Utils\Logger;

class LoggingMiddleware extends BaseMiddleware
{
    public function execute()
    {
        $request = Application::$app->request;
        Logger::info('Request: ' . $request->getMethod() . ' ' . $request->getUrl());
    }
}
```

### CSRF Protection Middleware

```php
<?php

namespace app\Core\Middlewares;

use app\Core\Middlewares\BaseMiddleware;
use app\Core\Application;
use app\Core\Utils\Session;

class CsrfMiddleware extends BaseMiddleware
{
    public function execute()
    {
        $request = Application::$app->request;
        
        if ($request->isPost()) {
            $session = Application::$app->session;
            $token = $session->get('csrf_token');
            $body = $request->getBody();
            
            if (empty($body['csrf_token']) || $body['csrf_token'] !== $token) {
                throw new \Exception('Invalid CSRF token');
            }
        }
    }
}
```

### Rate Limiting Middleware

```php
<?php

namespace app\Core\Middlewares;

use app\Core\Middlewares\BaseMiddleware;
use app\Core\Application;
use app\Core\Utils\Session;

class RateLimitMiddleware extends BaseMiddleware
{
    private $maxRequests = 10;
    private $timeWindow = 60; // seconds
    
    public function execute()
    {
        $session = Application::$app->session;
        $ip = $_SERVER['REMOTE_ADDR'];
        $key = 'rate_limit_' . $ip;
        
        $requests = $session->get($key) ?? [];
        $now = time();
        
        // Remove old requests
        $requests = array_filter($requests, function($timestamp) use ($now) {
            return ($now - $timestamp) < $this->timeWindow;
        });
        
        if (count($requests) >= $this->maxRequests) {
            throw new \Exception('Rate limit exceeded');
        }
        
        // Add current request
        $requests[] = $now;
        $session->set($key, $requests);
    }
}
```

### Guest Middleware

Redirect authenticated users away from guest-only pages:

```php
<?php

namespace app\Core\Middlewares;

use app\Core\Middlewares\BaseMiddleware;
use app\Core\Application;
use app\Core\Utils\Session;

class GuestMiddleware extends BaseMiddleware
{
    public function execute()
    {
        $session = Application::$app->session;
        
        if ($session->issert('user_id')) {
            Application::$app->response->redirect('/dashboard');
            exit;
        }
    }
}
```

## Middleware Execution Order

Middleware executes in the order it's added:

```php
public function __construct()
{
    // Executes first
    $this->middleware(new LoggingMiddleware());
    
    // Executes second
    $this->middleware(new AuthMiddleware());
    
    // Executes third
    $this->middleware(new AdminMiddleware());
}
```

## Best Practices

1. **Fail fast**: Throw exceptions early if validation fails
2. **Keep it simple**: Each middleware should do one thing
3. **Reusable**: Make middleware reusable across controllers
4. **Document**: Document what each middleware does
5. **Test**: Test middleware in isolation

## Complete Example

```php
<?php

namespace app\Controllers;

use app\Core\Controller;
use app\Core\Request;
use app\Core\Response;
use app\Core\Middlewares\AuthMiddleware;
use app\Core\Middlewares\AdminMiddleware;

class AdminController extends Controller
{
    public function __construct()
    {
        // Require authentication
        $this->middleware(new AuthMiddleware());
        
        // Require admin role
        $this->middleware(new AdminMiddleware());
    }
    
    public function index(Request $request, Response $response)
    {
        // Only accessible to authenticated admins
        return $this->render('admin/index');
    }
    
    public function users(Request $request, Response $response)
    {
        // Only accessible to authenticated admins
        $db = Application::$app->db;
        $users = $db->select("SELECT * FROM users");
        
        return $this->render('admin/users', ['users' => $users]);
    }
}
```

## Next Steps

- [Authentication](authentication.md) - Learn about authentication
- [Sessions](sessions.md) - Learn about session management

