# Request Lifecycle

Understanding the request lifecycle helps you know where to place your code and how the framework processes each request.

## Overview

When a request enters your Inna application, it goes through several stages:

1. **Entry Point** - `index.php`
2. **Bootstrap** - Configuration and initialization
3. **Routing** - Route matching
4. **Middleware** - Request filtering
5. **Controller** - Business logic
6. **Response** - Output generation

## Lifecycle Flow

```
HTTP Request
    ↓
index.php (Entry Point)
    ↓
config/config.php (Bootstrap)
    ↓
Application::__construct() (Initialize)
    ↓
Router::resolve() (Route Matching)
    ↓
Middleware::execute() (Request Filtering)
    ↓
Controller::action() (Business Logic)
    ↓
Response (Output)
    ↓
HTTP Response
```

## Detailed Lifecycle

### 1. Entry Point (`index.php`)

Every request starts at `index.php`:

```php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';

// Define routes
$app->router->get('/', [HomeController::class, 'home']);

// Run the application
$app->run();
```

### 2. Bootstrap (`config/config.php`)

The configuration file:
- Loads environment variables
- Sets up error handling
- Configures security headers
- Creates the Application instance

```php
$dotenv = Dotenv\Dotenv::createImmutable($_SERVER['DOCUMENT_ROOT']);
$dotenv->load();

$config = [
    'db' => [...],
    'url' => $_ENV['DOMAIN']
];

$app = new Application($_SERVER['DOCUMENT_ROOT'], $config);
```

### 3. Application Initialization

The `Application` constructor:
- Initializes session
- Creates Request object
- Creates Response object
- Creates Router
- Connects to database
- Creates API controller instance

```php
public function __construct($rootPath, array $config)
{
    Session::init();
    self::$ROOT_DIR = $rootPath;
    self::$app = $this;
    $this->request = new Request();
    $this->response = new Response();
    $this->session = new Session();
    $this->controller = new Controller();
    $this->router = new Router($this->request, $this->response);
    $this->db = new Database($config['db']);
    $this->api = new DApiController();
}
```

### 4. Route Resolution

The router matches the request to a route:

```php
public function resolve()
{
    $method = $this->request->getMethod();
    $url = $this->request->getUrl();
    
    // Try exact match first
    $callback = $this->routeMap[$method][$url] ?? false;
    
    // Try pattern matching
    if (!$callback) {
        $callback = $this->getCallback();
    }
    
    // Handle 404
    if ($callback === false) {
        return $this->render('404');
    }
    
    // Execute callback
    return call_user_func($callback, $this->request, $this->response);
}
```

### 5. Middleware Execution

If the route uses a controller, middleware is executed:

```php
if (is_array($callback)) {
    $controller = new $callback[0];
    $controller->action = $callback[1];
    Application::$app->controller = $controller;
    
    // Execute middleware
    $middlewares = $controller->getMiddlewares();
    foreach ($middlewares as $middleware) {
        $middleware->execute();
    }
}
```

### 6. Controller Action

The controller method is called:

```php
return call_user_func($callback, $this->request, $this->response);
```

### 7. Response Generation

The controller returns a response:
- Rendered view (Twig template)
- JSON response (for APIs)
- Redirect
- Raw output

## Example Flow

Here's a complete example:

**Request:** `GET /about`

1. **index.php** loads and defines routes
2. **Router** matches `/about` to `HomeController::about`
3. **Middleware** (if any) executes
4. **HomeController::about()** is called:
   ```php
   public function about(Request $request, Response $response)
   {
       return $this->render('about', [
           'title' => 'About Us'
       ]);
   }
   ```
5. **Response** is rendered and sent to browser

## Service Container

The `Application` class acts as a service container. Access it via:

```php
use app\Core\Application;

// Available services
Application::$app->request;   // Request object
Application::$app->response;  // Response object
Application::$app->session;   // Session object
Application::$app->db;       // Database connection
Application::$app->router;   // Router instance
Application::$app->api;      // API controller
```

## Activity Logging

Requests are automatically logged by `ActivityLogService::logRequest()` in the config file, which logs:
- Request method
- Request URL
- IP address
- User agent
- Timestamp

## Error Handling

Errors are handled at multiple levels:

1. **PHP Errors** → Logged to `log/errors/`
2. **Application Errors** → Logged to `log/app_error_log_*.log`
3. **404 Errors** → Rendered via `404.twig` template
4. **Exceptions** → Can be caught and handled in controllers

## Next Steps

- [Routing](/the-basics/routing.md) - Learn about defining routes
- [Controllers](/the-basics/controllers.md) - Create your first controller

