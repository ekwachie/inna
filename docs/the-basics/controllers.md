# Controllers

Controllers are the heart of your Inna application. They handle HTTP requests, process business logic, and return responses.

## Creating Controllers

### Basic Controller

All controllers extend the base `Controller` class:

```php
<?php

namespace app\Controllers;

use app\Core\Controller;
use app\Core\Request;
use app\Core\Response;

class HomeController extends Controller
{
    public function index(Request $request, Response $response)
    {
        return $this->render('home', [
            'title' => 'Welcome'
        ]);
    }
}
```

## Controller Methods

### Rendering Views

Use the `render()` method to render Twig templates:

```php
public function about(Request $request, Response $response)
{
    return $this->render('about', [
        'title' => 'About Us',
        'description' => 'Learn more about our company'
    ]);
}
```

### Redirecting

Redirect to another URL:

```php
public function login(Request $request, Response $response)
{
    // After login logic
    return $this->redirect(); // Redirects to home
    
    // Or use Response object for custom redirects
    return $response->redirect('/dashboard');
}
```

### Flash Messages

Display flash messages to users:

```php
public function create(Request $request, Response $response)
{
    // After successful creation
    echo $this->setFlash('success', 'Item created successfully!');
    
    // Error message
    echo $this->setFlash('error', 'Something went wrong!');
    
    // Warning message
    echo $this->setFlash('warning', 'Please check your input.');
    
    // Info message
    echo $this->setFlash('info', 'New features available!');
}
```

Flash message types:
- `success` - Green (successful operations)
- `error` or `danger` - Red (errors)
- `warning` - Yellow (warnings)
- `info` - Blue (informational)

### API Responses

For API endpoints, use the `apiMessage()` method:

```php
public function apiEndpoint(Request $request, Response $response)
{
    // Success response
    return $this->apiMessage(true, [
        'data' => $someData
    ]);
    
    // Error response
    return $this->apiMessage(false, 'Error message', 'ERROR_CODE');
}
```

## Accessing Request Data

### GET Parameters

```php
public function search(Request $request, Response $response)
{
    $body = $request->getBody();
    $query = $body['q'] ?? '';
    $page = $body['page'] ?? 1;
}
```

### POST Data

```php
public function submit(Request $request, Response $response)
{
    $body = $request->getBody();
    $name = $body['name'] ?? '';
    $email = $body['email'] ?? '';
}
```

### Route Parameters

```php
// Route: /user/{id}
public function show(Request $request, Response $response)
{
    $id = $request->getRouteParam('id');
    $user = Application::$app->db->select(
        "SELECT * FROM users WHERE id = :id",
        ['id' => $id]
    );
}
```

### Request Method

```php
public function handle(Request $request, Response $response)
{
    if ($request->isGet()) {
        // Handle GET request
    }
    
    if ($request->isPost()) {
        // Handle POST request
    }
    
    $method = $request->getMethod(); // 'get', 'post', etc.
}
```

## Database Access

Access the database through the Application instance:

```php
use app\Core\Application;

public function index(Request $request, Response $response)
{
    $db = Application::$app->db;
    
    // Query database
    $users = $db->select("SELECT * FROM users");
    
    // Insert
    $id = $db->insert('users', [
        'name' => 'John',
        'email' => 'john@example.com'
    ]);
    
    // Update
    $db->update('users', 
        ['name' => 'Jane'],
        'id = :id',
        ['id' => 1]
    );
}
```

## Session Management

Access session data:

```php
use app\Core\Application;

public function login(Request $request, Response $response)
{
    $session = Application::$app->session;
    
    // Set session
    $session->set('user_id', 123);
    $session->set('username', 'john');
    
    // Get session
    $userId = $session->get('user_id');
    
    // Check if exists
    if ($session->issert('user_id')) {
        // User is logged in
    }
    
    // Unset
    $session->unsert('user_id');
}
```

## Middleware

Attach middleware to controllers:

```php
use app\Core\Middlewares\AuthMiddleware;

class DashboardController extends Controller
{
    public function __construct()
    {
        // Require authentication for all actions
        $this->middleware(new AuthMiddleware());
        
        // Or only for specific actions
        $this->middleware(new AuthMiddleware(['index', 'settings']));
    }
}
```

## Complete Example

```php
<?php

namespace app\Controllers;

use app\Core\Controller;
use app\Core\Request;
use app\Core\Response;
use app\Core\Application;
use app\Core\Middlewares\AuthMiddleware;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(new AuthMiddleware(['profile', 'settings']));
    }
    
    public function index(Request $request, Response $response)
    {
        $db = Application::$app->db;
        $users = $db->select("SELECT * FROM users");
        
        return $this->render('users/index', [
            'users' => $users
        ]);
    }
    
    public function show(Request $request, Response $response)
    {
        $id = $request->getRouteParam('id');
        $db = Application::$app->db;
        
        $user = $db->select(
            "SELECT * FROM users WHERE id = :id",
            ['id' => $id]
        );
        
        if (empty($user)) {
            return $this->render('404');
        }
        
        return $this->render('users/show', [
            'user' => $user[0]
        ]);
    }
    
    public function create(Request $request, Response $response)
    {
        if ($request->isPost()) {
            $body = $request->getBody();
            $db = Application::$app->db;
            
            $id = $db->insert('users', [
                'name' => $body['name'],
                'email' => $body['email']
            ]);
            
            echo $this->setFlash('success', 'User created successfully!');
            return $response->redirect('/user/' . $id);
        }
        
        return $this->render('users/create');
    }
}
```

## Best Practices

1. **Keep controllers thin**: Move business logic to models or service classes
2. **Use type hints**: Always type hint `Request` and `Response`
3. **Validate input**: Always validate and sanitize user input
4. **Handle errors**: Use try-catch blocks for database operations
5. **Return early**: Use early returns to reduce nesting

## Next Steps

- [Views & Templates](views.md) - Learn about rendering views
- [Database](/database/basics.md) - Learn about database operations
- [Middleware](/security/middleware.md) - Add middleware to controllers

