# API Development

Inna provides a powerful foundation for building RESTful APIs with JWT authentication support.

## API Controllers

API controllers extend `DApiController` which provides:

- JSON response formatting
- JWT token management
- Standardized error handling
- HTTP header management

### Basic API Controller

```php
<?php

namespace app\Controllers;

use app\Core\DApiController;
use app\Core\Request;
use app\Core\Response;

class ApiController extends DApiController
{
    public function index(Request $request, Response $response)
    {
        return $this->message(true, [
            'message' => 'API is working',
            'version' => '1.0'
        ]);
    }
}
```

## API Response Format

### Success Response

```php
return $this->message(true, [
    'id' => 1,
    'name' => 'John'
]);
```

Returns:

```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "John"
    }
}
```

### Error Response

```php
return $this->message(false, 'User not found', 'USER_NOT_FOUND');
```

Returns:

```json
{
    "success": false,
    "data": "User not found",
    "error_code": "USER_NOT_FOUND"
}
```

## API Routes

Define API routes in `index.php`:

```php
// Public endpoints
$app->router->get('/api/public', [ApiController::class, 'public']);

// Protected endpoints
$app->router->get('/api/profile', [ApiController::class, 'profile']);
$app->router->get('/api/users', [ApiController::class, 'users']);
```

## Complete API Example

```php
<?php

namespace app\Controllers;

use app\Core\DApiController;
use app\Core\Request;
use app\Core\Response;
use app\Core\Application;

class ApiController extends DApiController
{
    // Public endpoint
    public function public(Request $request, Response $response)
    {
        return $this->message(true, [
            'message' => 'This is a public endpoint'
        ]);
    }
    
    // Protected endpoint
    public function profile(Request $request, Response $response)
    {
        $payload = $this->requireJwt();
        $userId = $payload['sub'];
        
        $db = Application::$app->db;
        $user = $db->select(
            "SELECT id, name, email FROM users WHERE id = :id",
            ['id' => $userId]
        );
        
        if (empty($user)) {
            return $this->message(false, 'User not found', 'USER_NOT_FOUND');
        }
        
        return $this->message(true, $user[0]);
    }
    
    // List users (admin only)
    public function users(Request $request, Response $response)
    {
        $payload = $this->requireJwt(['admin']);
        
        $db = Application::$app->db;
        $users = $db->select("SELECT id, name, email FROM users");
        
        return $this->message(true, [
            'users' => $users,
            'count' => count($users)
        ]);
    }
}
```

## Next Steps

- [API Controllers](controllers.md) - Learn more about API controllers
- [API Authentication](authentication.md) - Learn about JWT authentication
- [API Responses](responses.md) - Learn about response formatting

