# Routing

Routing in Inna allows you to map URLs to controller actions or closures. All routes are defined in `index.php`.

## Basic Routing

### GET Routes

```php
$app->router->get('/about', [HomeController::class, 'about']);
```

### POST Routes

```php
$app->router->post('/contact', [ContactController::class, 'submit']);
```

## Route Parameters

### Named Parameters

Capture URL segments as parameters:

```php
// Route: /user/{id}
$app->router->get('/user/{id}', [UserController::class, 'show']);

// In controller:
public function show(Request $request, Response $response)
{
    $id = $request->getRouteParam('id');
    // $id contains the value from URL
}
```

### Parameter Constraints

You can add regex constraints to parameters:

```php
// Only numeric IDs
$app->router->get('/user/{id:[\d]+}', [UserController::class, 'show']);

// Alphanumeric with hyphens
$app->router->get('/{category}/{slug:[\w\d-]+}', [PostController::class, 'show']);
```

### Multiple Parameters

```php
$app->router->get('/post/{category}/{id}', [PostController::class, 'show']);

// In controller:
public function show(Request $request, Response $response)
{
    $category = $request->getRouteParam('category');
    $id = $request->getRouteParam('id');
}
```

## Route Examples

### Simple Route

```php
$app->router->get('/', [HomeController::class, 'index']);
```

### Route with Parameters

```php
$app->router->get('/user/{id}', [UserController::class, 'profile']);
```

### Route with Constraint

```php
$app->router->get('/post/{id:[\d]+}', [PostController::class, 'show']);
```

### Route with Multiple Segments

```php
$app->router->get('/blog/{year}/{month}/{slug}', [BlogController::class, 'post']);
```

### Complex Pattern

```php
// Matches: /api/v1/users/123
$app->router->get('/api/v1/users/{id:[\d]+}', [ApiController::class, 'getUser']);
```

## Accessing Route Parameters

In your controller, access parameters via the Request object:

```php
use app\Core\Request;
use app\Core\Response;

class UserController extends Controller
{
    public function show(Request $request, Response $response)
    {
        // Get single parameter
        $id = $request->getRouteParam('id');
        
        // Get all parameters
        $params = $request->getRouteParams();
        
        // Get parameter with default
        $page = $request->getRouteParam('page', 1);
    }
}
```

## Route Matching

Routes are matched in this order:

1. **Exact Match**: Direct URL match
2. **Pattern Match**: Regex pattern matching with parameters
3. **404**: If no match found, renders `404.twig`

### Example Matching

```php
// Route definition
$app->router->get('/user/{id}', [UserController::class, 'show']);

// Matches:
// ✅ /user/123
// ✅ /user/456
// ❌ /user/123/posts (doesn't match)
```

## Route Patterns

### Common Patterns

```php
// Numeric only
{id:[\d]+}

// Alphanumeric with hyphens
{slug:[\w\d-]+}

// Letters only
{name:[\w]+}

// Any character (be careful!)
{any:.*}
```

## Route Organization

### Grouping by Controller

```php
// Home routes
$app->router->get('/', [HomeController::class, 'index']);
$app->router->get('/about', [HomeController::class, 'about']);
$app->router->get('/contact', [HomeController::class, 'contact']);

// User routes
$app->router->get('/user/{id}', [UserController::class, 'show']);
$app->router->post('/user', [UserController::class, 'create']);

// API routes
$app->router->post('/api/login', [ApiController::class, 'login']);
$app->router->get('/api/user', [ApiController::class, 'user']);
```

## 404 Handling

If no route matches, the framework automatically renders the `404.twig` template located in `public/views/404.twig`.

You can customize this by editing the template file.

## Best Practices

1. **Use descriptive URLs**: `/user/profile` instead of `/u/p`
2. **Use constraints**: Always validate parameter patterns
3. **Organize routes**: Group related routes together
4. **Use RESTful conventions**: 
   - GET for reading
   - POST for creating
   - PUT/PATCH for updating (if supported)
   - DELETE for deleting (if supported)

## Common Patterns

### RESTful Routes

```php
// List all
$app->router->get('/posts', [PostController::class, 'index']);

// Show one
$app->router->get('/post/{id}', [PostController::class, 'show']);

// Create
$app->router->post('/post', [PostController::class, 'create']);

// Update
$app->router->post('/post/{id}', [PostController::class, 'update']);

// Delete
$app->router->post('/post/{id}/delete', [PostController::class, 'delete']);
```

### API Routes

```php
$app->router->post('/api/login', [ApiController::class, 'login']);
$app->router->get('/api/user', [ApiController::class, 'user']);
$app->router->get('/api/posts', [ApiController::class, 'posts']);
```

## Next Steps

- [Controllers](the-basics/controllers.md) - Learn about controllers
- [Middleware](/security/middleware.md) - Add middleware to routes

