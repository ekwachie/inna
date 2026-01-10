# Authentication

Inna provides session-based authentication out of the box. This guide covers implementing authentication in your application.

## Session Management

The framework includes a `Session` class for managing user sessions.

### Setting Session Data

```php
use app\Core\Application;

$session = Application::$app->session;

// Set session values
$session->set('user_id', 123);
$session->set('username', 'john');
$session->set('email', 'john@example.com');
```

### Getting Session Data

```php
// Get session value
$userId = $session->get('user_id');

// Check if session exists
if ($session->issert('user_id')) {
    // User is logged in
}
```

### Removing Session Data

```php
// Remove single value
$session->unsert('user_id');

// Destroy entire session
$session->destroy();
```

## Login Implementation

### Login Controller

```php
<?php

namespace app\Controllers;

use app\Core\Controller;
use app\Core\Request;
use app\Core\Response;
use app\Core\Application;

class AuthController extends Controller
{
    public function login(Request $request, Response $response)
    {
        if ($request->isPost()) {
            $body = $request->getBody();
            $db = Application::$app->db;
            $session = Application::$app->session;
            
            // Find user
            $user = $db->select(
                "SELECT * FROM users WHERE email = :email",
                ['email' => $body['email']]
            );
            
            if (!empty($user) && password_verify($body['password'], $user[0]['password'])) {
                // Set session
                $session->set('user_id', $user[0]['id']);
                $session->set('username', $user[0]['name']);
                $session->set('email', $user[0]['email']);
                
                echo $this->setFlash('success', 'Welcome back!');
                return $response->redirect('/dashboard');
            }
            
            echo $this->setFlash('error', 'Invalid credentials');
        }
        
        return $this->render('auth/login');
    }
    
    public function logout(Request $request, Response $response)
    {
        $session = Application::$app->session;
        $session->destroy();
        
        echo $this->setFlash('success', 'Logged out successfully');
        return $response->redirect('/');
    }
}
```

## Registration

### Registration Controller

```php
public function register(Request $request, Response $response)
{
    if ($request->isPost()) {
        $body = $request->getBody();
        $db = Application::$app->db;
        $session = Application::$app->session;
        
        // Validate
        if (empty($body['name']) || empty($body['email']) || empty($body['password'])) {
            echo $this->setFlash('error', 'All fields are required');
            return $this->render('auth/register');
        }
        
        // Check if email exists
        $existing = $db->select(
            "SELECT id FROM users WHERE email = :email",
            ['email' => $body['email']]
        );
        
        if (!empty($existing)) {
            echo $this->setFlash('error', 'Email already registered');
            return $this->render('auth/register');
        }
        
        // Create user
        $id = $db->insert('users', [
            'name' => $body['name'],
            'email' => $body['email'],
            'password' => password_hash($body['password'], PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        // Auto-login
        $session->set('user_id', $id);
        $session->set('username', $body['name']);
        $session->set('email', $body['email']);
        
        echo $this->setFlash('success', 'Registration successful!');
        return $response->redirect('/dashboard');
    }
    
    return $this->render('auth/register');
}
```

## Checking Authentication

### Helper Function

Create a helper function to check if user is logged in:

```php
// In a helper file or Application class
function isAuthenticated()
{
    $session = Application::$app->session;
    return $session->issert('user_id');
}

function getCurrentUser()
{
    if (!isAuthenticated()) {
        return null;
    }
    
    $session = Application::$app->session;
    $db = Application::$app->db;
    
    return $db->select(
        "SELECT * FROM users WHERE id = :id",
        ['id' => $session->get('user_id')]
    )[0] ?? null;
}
```

### In Controllers

```php
public function dashboard(Request $request, Response $response)
{
    $session = Application::$app->session;
    
    if (!$session->issert('user_id')) {
        return $response->redirect('/login');
    }
    
    $userId = $session->get('user_id');
    // Load user data and render dashboard
}
```

## Password Hashing

Always hash passwords using PHP's `password_hash()`:

```php
// When creating user
$password = password_hash($plainPassword, PASSWORD_DEFAULT);

// When verifying
if (password_verify($inputPassword, $hashedPassword)) {
    // Password is correct
}
```

## Using Auth Middleware

The framework includes `AuthMiddleware` for protecting routes:

```php
use app\Core\Middlewares\AuthMiddleware;

class DashboardController extends Controller
{
    public function __construct()
    {
        // Require auth for all actions
        $this->middleware(new AuthMiddleware());
        
        // Or only specific actions
        $this->middleware(new AuthMiddleware(['index', 'settings']));
    }
}
```

## Session Security

The framework automatically configures secure session settings:

- **HttpOnly**: Prevents JavaScript access
- **Secure**: Only sent over HTTPS (in production)
- **SameSite**: CSRF protection
- **Timeout**: 1 hour (configurable)

## Complete Example

```php
<?php

namespace app\Controllers;

use app\Core\Controller;
use app\Core\Request;
use app\Core\Response;
use app\Core\Application;
use app\Core\Middlewares\AuthMiddleware;

class AuthController extends Controller
{
    public function login(Request $request, Response $response)
    {
        if ($request->isPost()) {
            $body = $request->getBody();
            $db = Application::$app->db;
            $session = Application::$app->session;
            
            $user = $db->select(
                "SELECT * FROM users WHERE email = :email",
                ['email' => $body['email']]
            );
            
            if (!empty($user) && password_verify($body['password'], $user[0]['password'])) {
                $session->set('user_id', $user[0]['id']);
                $session->set('username', $user[0]['name']);
                $session->set('email', $user[0]['email']);
                
                echo $this->setFlash('success', 'Welcome!');
                return $response->redirect('/dashboard');
            }
            
            echo $this->setFlash('error', 'Invalid credentials');
        }
        
        return $this->render('auth/login');
    }
    
    public function logout(Request $request, Response $response)
    {
        Application::$app->session->destroy();
        echo $this->setFlash('success', 'Logged out');
        return $response->redirect('/');
    }
}

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(new AuthMiddleware());
    }
    
    public function index(Request $request, Response $response)
    {
        $session = Application::$app->session;
        $userId = $session->get('user_id');
        
        // Load user data
        $db = Application::$app->db;
        $user = $db->select(
            "SELECT * FROM users WHERE id = :id",
            ['id' => $userId]
        )[0];
        
        return $this->render('dashboard', ['user' => $user]);
    }
}
```

## Next Steps

- [Middleware](middleware.md) - Learn about middleware
- [JWT Authentication](jwt.md) - Learn about API authentication
- [Sessions](sessions.md) - Learn more about session management

