# API Authentication

This guide covers JWT authentication for API endpoints. For detailed JWT usage, see the [JWT Authentication](/security/jwt.md) guide.

## Quick Start

### 1. Setup Environment

```env
JWT_SECRET=your_secret_key_here
JWT_ISSUER=inna_api
JWT_TTL=3600
```

### 2. Login Endpoint

```php
public function login(Request $request, Response $response)
{
    $body = $request->getBody();
    
    // Authenticate user
    $user = authenticateUser($body['email'], $body['password']);
    
    if ($user) {
        $token = $this->issueToken([
            'sub' => $user['id'],
            'email' => $user['email'],
            'roles' => $user['roles']
        ]);
        
        return $this->message(true, [
            'token' => $token,
            'user' => $user
        ]);
    }
    
    return $this->message(false, 'Invalid credentials', 'AUTH_FAILED');
}
```

### 3. Protect Endpoints

```php
public function profile(Request $request, Response $response)
{
    $payload = $this->requireJwt();
    // Token validated, access user data
    $userId = $payload['sub'];
    
    return $this->message(true, $userData);
}
```

## Role-Based Access

```php
// Require admin role
public function adminEndpoint(Request $request, Response $response)
{
    $payload = $this->requireJwt(['admin']);
    // Only admins can access
}

// Require one of multiple roles
public function moderatorEndpoint(Request $request, Response $response)
{
    $payload = $this->requireJwt(['admin', 'moderator']);
    // Admins or moderators can access
}
```

## Client Implementation

### JavaScript Example

```javascript
// Login
const login = async (email, password) => {
  const response = await fetch('/api/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ email, password })
  });
  
  const data = await response.json();
  
  if (data.success) {
    localStorage.setItem('token', data.data.token);
    return data.data;
  }
  
  throw new Error(data.data);
};

// Authenticated request
const getProfile = async () => {
  const token = localStorage.getItem('token');
  
  const response = await fetch('/api/profile', {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  
  return response.json();
};
```

## Token Refresh

```php
public function refresh(Request $request, Response $response)
{
    $payload = $this->requireJwt();
    
    // Issue new token
    $token = $this->issueToken([
        'sub' => $payload['sub'],
        'email' => $payload['email'],
        'roles' => $payload['roles'] ?? []
    ]);
    
    return $this->message(true, ['token' => $token]);
}
```

## Next Steps

- [JWT Authentication](/security/jwt.md) - Complete JWT guide
- [API Controllers](controllers.md) - Learn about API controllers

