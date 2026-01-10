# API Controllers

API controllers in Inna extend `DApiController` and provide specialized methods for building RESTful APIs.

## Base API Controller

The `DApiController` class provides:

- `message()` - Standardized response formatting
- `json()` - JSON response with headers
- `requireJwt()` - JWT token validation
- `issueToken()` - JWT token generation
- `getBearerToken()` - Extract Bearer token from headers

## Creating API Controllers

```php
<?php

namespace app\Controllers;

use app\Core\DApiController;
use app\Core\Request;
use app\Core\Response;

class UserApiController extends DApiController
{
    // Your API endpoints
}
```

## Standard Methods

### message()

Format API responses:

```php
// Success
return $this->message(true, $data);

// Error
return $this->message(false, 'Error message', 'ERROR_CODE');
```

### json()

Return raw JSON:

```php
return $this->json([
    'custom' => 'response',
    'format' => true
]);
```

### requireJwt()

Validate JWT token:

```php
// Require any valid token
$payload = $this->requireJwt();

// Require specific role
$payload = $this->requireJwt(['admin']);

// Require one of multiple roles
$payload = $this->requireJwt(['admin', 'moderator']);
```

### issueToken()

Generate JWT token:

```php
$token = $this->issueToken([
    'sub' => $userId,
    'email' => $email,
    'roles' => ['user']
], 3600); // Optional TTL
```

## RESTful API Example

```php
<?php

namespace app\Controllers;

use app\Core\DApiController;
use app\Core\Request;
use app\Core\Response;
use app\Core\Application;

class PostApiController extends DApiController
{
    // GET /api/posts - List all
    public function index(Request $request, Response $response)
    {
        $db = Application::$app->db;
        $posts = $db->select("SELECT * FROM posts ORDER BY created_at DESC");
        
        return $this->message(true, [
            'posts' => $posts,
            'count' => count($posts)
        ]);
    }
    
    // GET /api/posts/{id} - Show one
    public function show(Request $request, Response $response)
    {
        $id = $request->getRouteParam('id');
        $db = Application::$app->db;
        
        $post = $db->select(
            "SELECT * FROM posts WHERE id = :id",
            ['id' => $id]
        );
        
        if (empty($post)) {
            return $this->message(false, 'Post not found', 'POST_NOT_FOUND');
        }
        
        return $this->message(true, $post[0]);
    }
    
    // POST /api/posts - Create
    public function create(Request $request, Response $response)
    {
        $payload = $this->requireJwt();
        $body = $request->getBody();
        $db = Application::$app->db;
        
        // Validate
        if (empty($body['title']) || empty($body['content'])) {
            return $this->message(false, 'Title and content required', 'VALIDATION_ERROR');
        }
        
        $id = $db->insert('posts', [
            'title' => $body['title'],
            'content' => $body['content'],
            'user_id' => $payload['sub'],
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        return $this->message(true, [
            'id' => $id,
            'message' => 'Post created'
        ]);
    }
    
    // POST /api/posts/{id} - Update
    public function update(Request $request, Response $response)
    {
        $payload = $this->requireJwt();
        $id = $request->getRouteParam('id');
        $body = $request->getBody();
        $db = Application::$app->db;
        
        // Check ownership or admin
        $post = $db->select(
            "SELECT user_id FROM posts WHERE id = :id",
            ['id' => $id]
        );
        
        if (empty($post)) {
            return $this->message(false, 'Post not found', 'POST_NOT_FOUND');
        }
        
        $isOwner = $post[0]['user_id'] == $payload['sub'];
        $isAdmin = in_array('admin', $payload['roles'] ?? []);
        
        if (!$isOwner && !$isAdmin) {
            return $this->message(false, 'Unauthorized', 'AUTH_FORBIDDEN');
        }
        
        $db->update(
            'posts',
            [
                'title' => $body['title'],
                'content' => $body['content']
            ],
            'id = :id',
            ['id' => $id]
        );
        
        return $this->message(true, ['message' => 'Post updated']);
    }
    
    // POST /api/posts/{id}/delete - Delete
    public function delete(Request $request, Response $response)
    {
        $payload = $this->requireJwt(['admin']);
        $id = $request->getRouteParam('id');
        $db = Application::$app->db;
        
        $deleted = $db->delete('posts', 'id = :id', ['id' => $id]);
        
        if ($deleted) {
            return $this->message(true, ['message' => 'Post deleted']);
        }
        
        return $this->message(false, 'Post not found', 'POST_NOT_FOUND');
    }
}
```

## Error Handling

### Validation Errors

```php
public function create(Request $request, Response $response)
{
    $body = $request->getBody();
    
    $errors = [];
    
    if (empty($body['email'])) {
        $errors[] = 'Email is required';
    }
    
    if (empty($body['password'])) {
        $errors[] = 'Password is required';
    }
    
    if (!empty($errors)) {
        return $this->message(false, $errors, 'VALIDATION_ERROR');
    }
    
    // Continue with creation
}
```

### Database Errors

```php
try {
    $id = $db->insert('users', $data);
    return $this->message(true, ['id' => $id]);
} catch (\Exception $e) {
    return $this->message(false, 'Database error', 'DB_ERROR');
}
```

## Response Headers

Set custom headers:

```php
$this->header('X-Custom-Header: value');
return $this->message(true, $data);
```

## Best Practices

1. **Use JWT for authentication**: Protect endpoints with `requireJwt()`
2. **Validate input**: Always validate and sanitize input
3. **Handle errors gracefully**: Return meaningful error messages
4. **Use status codes**: Set appropriate HTTP status codes
5. **Document your API**: Document endpoints and expected responses

## Next Steps

- [API Authentication](authentication.md) - Learn about JWT authentication
- [API Responses](responses.md) - Learn about response formatting

