# Request & Response

Understanding how to work with HTTP requests and responses is essential for building web applications.

## Request Object

The `Request` object provides access to HTTP request data.

### Getting Request Method

```php
use app\Core\Request;

$method = $request->getMethod(); // 'get', 'post', 'put', etc.

if ($request->isGet()) {
    // Handle GET request
}

if ($request->isPost()) {
    // Handle POST request
}
```

### Getting Request URL

```php
$url = $request->getUrl(); // '/about', '/user/123', etc.
```

### Getting Request Body

Get all GET/POST parameters:

```php
$body = $request->getBody();

// Access individual parameters
$name = $body['name'] ?? '';
$email = $body['email'] ?? '';
```

The `getBody()` method automatically sanitizes input using `FILTER_SANITIZE_SPECIAL_CHARS`.

### Route Parameters

Access route parameters:

```php
// Route: /user/{id}/post/{slug}
$id = $request->getRouteParam('id');
$slug = $request->getRouteParam('slug');

// Get all route parameters
$params = $request->getRouteParams();

// Get with default value
$page = $request->getRouteParam('page', 1);
```

## Response Object

The `Response` object handles HTTP responses.

### Setting Status Code

```php
use app\Core\Response;

$response->setStatusCode(200);  // OK
$response->setStatusCode(404);  // Not Found
$response->setStatusCode(500);  // Internal Server Error
```

### Redirecting

```php
// Simple redirect
$response->redirect('/dashboard');

// Redirect with status code
$response->redirect('/login', 301);

// Redirect to external URL
$response->redirect('https://example.com');
```

### JSON Response

Return JSON data:

```php
$response->json([
    'success' => true,
    'data' => [
        'id' => 1,
        'name' => 'John'
    ]
]);

// With options
$response->json($data, JSON_PRETTY_PRINT);
```

### Setting Headers

```php
// Single header
$response->header('Content-Type: application/xml');

// Multiple headers
$response->headers([
    'Content-Type: application/json',
    'X-Custom-Header: value'
]);
```

### HTTP Authentication

```php
$response->auth('Protected Area');
// Sets WWW-Authenticate header and 401 status
```

### Caching

```php
$response->cache('etag-value', time());
// Sets cache headers for browser caching
```

## Complete Examples

### Handling Form Submission

```php
public function submit(Request $request, Response $response)
{
    if ($request->isPost()) {
        $body = $request->getBody();
        
        // Validate
        if (empty($body['name']) || empty($body['email'])) {
            echo $this->setFlash('error', 'All fields are required');
            return $this->render('contact');
        }
        
        // Process form
        // ...
        
        echo $this->setFlash('success', 'Message sent!');
        return $response->redirect('/contact');
    }
    
    return $this->render('contact');
}
```

### API Endpoint

```php
public function api(Request $request, Response $response)
{
    $body = $request->getBody();
    $id = $request->getRouteParam('id');
    
    $data = [
        'id' => $id,
        'name' => 'John',
        'timestamp' => time()
    ];
    
    return $response->json([
        'success' => true,
        'data' => $data
    ]);
}
```

### Conditional Response

```php
public function show(Request $request, Response $response)
{
    $id = $request->getRouteParam('id');
    $user = $this->getUser($id);
    
    if (!$user) {
        $response->setStatusCode(404);
        return $this->render('404');
    }
    
    return $this->render('user/show', ['user' => $user]);
}
```

## Accessing Raw PHP Superglobals

While the framework provides clean abstractions, you can still access PHP superglobals if needed:

```php
// $_GET
$query = $_GET['q'] ?? '';

// $_POST
$email = $_POST['email'] ?? '';

// $_SERVER
$ip = $_SERVER['REMOTE_ADDR'];
$userAgent = $_SERVER['HTTP_USER_AGENT'];

// $_FILES
$file = $_FILES['upload'] ?? null;
```

**Note:** Always sanitize and validate data from superglobals directly.

## Best Practices

1. **Use Request methods**: Prefer `$request->getBody()` over direct superglobal access
2. **Sanitize input**: The framework sanitizes `getBody()`, but validate as well
3. **Set appropriate status codes**: Use proper HTTP status codes
4. **Return early**: Use early returns for error cases
5. **Type hint**: Always type hint `Request` and `Response` in controller methods

## Next Steps

- [Controllers](controllers.md) - Learn about controllers
- [Database](/database/basics.md) - Learn about database operations

