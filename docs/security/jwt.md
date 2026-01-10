# JWT Authentication

Inna provides JWT (JSON Web Token) authentication for API endpoints. This is ideal for stateless authentication in REST APIs.

## Setup

### Environment Variables

Add to your `.env` file:

```env
JWT_SECRET=your_long_random_secret_key_here
JWT_ISSUER=inna_api
JWT_TTL=3600
```

Generate a secret key:

```bash
openssl rand -hex 32
```

## API Controllers

Extend `DApiController` for API endpoints:

```php
<?php

namespace app\Controllers;

use app\Core\DApiController;
use app\Core\Request;
use app\Core\Response;

class ApiController extends DApiController
{
    // Your API endpoints
}
```

## Issuing Tokens

### Login Endpoint

```php
public function login(Request $request, Response $response)
{
    $body = $request->getBody();
    
    // Authenticate user (your logic)
    $user = $this->authenticateUser($body['email'], $body['password']);
    
    if ($user) {
        // Issue token
        $token = $this->issueToken([
            'sub' => $user['id'],           // User ID
            'username' => $user['username'],
            'email' => $user['email'],
            'roles' => ['user', 'admin'],    // User roles
        ], 3600); // Optional: TTL in seconds
        
        return $this->message(true, [
            'token' => $token,
            'user' => $user
        ]);
    }
    
    return $this->message(false, 'Invalid credentials', 'ERR_AUTH');
}
```

## Protecting Endpoints

### Require JWT

```php
public function profile(Request $request, Response $response)
{
    // Validates token automatically
    // Returns 401 if invalid/missing
    $payload = $this->requireJwt();
    
    // Access user data from token
    $userId = $payload['sub'];
    $username = $payload['username'];
    
    // Your logic here
    return $this->message(true, $userData);
}
```

### Role-Based Authorization

```php
public function adminUsers(Request $request, Response $response)
{
    // Require JWT AND admin role
    $payload = $this->requireJwt(['admin']);
    
    // Only users with 'admin' in roles can access
    return $this->message(true, $adminData);
}
```

### Multiple Roles

```php
// User must have at least one of these roles
$payload = $this->requireJwt(['admin', 'moderator']);
```

## API Response Format

The framework uses a standard response format:

### Success Response

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

```json
{
    "success": false,
    "data": "Error message",
    "error_code": "ERROR_CODE"
}
```

## Complete Example

```php
<?php

namespace app\Controllers;

use app\Core\DApiController;
use app\Core\Request;
use app\Core\Response;
use app\Core\Application;

class ApiController extends DApiController
{
    public function login(Request $request, Response $response)
    {
        $body = $request->getBody();
        $db = Application::$app->db;
        
        $user = $db->select(
            "SELECT * FROM users WHERE email = :email",
            ['email' => $body['email']]
        );
        
        if (!empty($user) && password_verify($body['password'], $user[0]['password'])) {
            $token = $this->issueToken([
                'sub' => $user[0]['id'],
                'email' => $user[0]['email'],
                'roles' => explode(',', $user[0]['roles'] ?? 'user')
            ]);
            
            return $this->message(true, [
                'token' => $token,
                'user' => [
                    'id' => $user[0]['id'],
                    'email' => $user[0]['email'],
                    'name' => $user[0]['name']
                ]
            ]);
        }
        
        return $this->message(false, 'Invalid credentials', 'AUTH_FAILED');
    }
    
    public function profile(Request $request, Response $response)
    {
        $payload = $this->requireJwt();
        $userId = $payload['sub'];
        
        $db = Application::$app->db;
        $user = $db->select(
            "SELECT id, name, email FROM users WHERE id = :id",
            ['id' => $userId]
        );
        
        return $this->message(true, $user[0] ?? null);
    }
    
    public function adminUsers(Request $request, Response $response)
    {
        $payload = $this->requireJwt(['admin']);
        
        $db = Application::$app->db;
        $users = $db->select("SELECT id, name, email FROM users");
        
        return $this->message(true, ['users' => $users]);
    }
    
    public function refresh(Request $request, Response $response)
    {
        $payload = $this->requireJwt();
        
        // Issue new token with same claims
        $token = $this->issueToken([
            'sub' => $payload['sub'],
            'email' => $payload['email'],
            'roles' => $payload['roles'] ?? []
        ]);
        
        return $this->message(true, ['token' => $token]);
    }
}
```

## Client Usage

### cURL

```bash
# Login
curl -X POST http://localhost/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'

# Get Profile
curl -X GET http://localhost/api/profile \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### JavaScript (Fetch)

```javascript
// Login
const response = await fetch('/api/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    email: 'user@example.com',
    password: 'password'
  })
});

const data = await response.json();
const token = data.data.token;

// Use token
const profileResponse = await fetch('/api/profile', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});
```

## Error Responses

### Missing Token

```json
{
    "success": false,
    "data": "Missing Authorization Bearer token",
    "error_code": "AUTH_MISSING_BEARER"
}
```

### Invalid Token

```json
{
    "success": false,
    "data": "Unauthorized: Token expired",
    "error_code": "AUTH_INVALID_TOKEN"
}
```

### Insufficient Role

```json
{
    "success": false,
    "data": "Forbidden: insufficient role",
    "error_code": "AUTH_FORBIDDEN"
}
```

## Token Claims

Standard JWT claims are automatically included:
- `iss`: Issuer (from `JWT_ISSUER`)
- `iat`: Issued at (timestamp)
- `nbf`: Not before (timestamp)
- `exp`: Expiration (timestamp)

Custom claims you can add:
- `sub`: Subject (user ID) - recommended
- `username`: Username
- `email`: User email
- `roles`: Array of user roles

## Security Best Practices

1. **Use HTTPS**: Always use HTTPS in production
2. **Strong Secret**: Use a long, random secret key
3. **Token Storage**: Store tokens securely on client
4. **Expiration**: Set appropriate TTL values
5. **Validate Roles**: Always validate roles server-side

## Next Steps

- [API Development](/api/basics.md) - Learn more about API development
- [Authentication](authentication.md) - Learn about session-based auth

