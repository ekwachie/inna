# JWT Authentication Usage Guide

This guide explains how to use JWT authentication in the Inna framework.

## Setup

1. **Environment Variables**: Add these to your `.env` file:
```env
JWT_SECRET=your_long_random_secret_key_here
JWT_ISSUER=inna_api
JWT_TTL=3600
```

2. **Create API Controller**: Extend `DApiController` for your API endpoints:
```php
use app\Core\DApiController;

class ApiController extends DApiController
{
    // Your endpoints here
}
```

## Usage Examples

### 1. Issue a JWT Token (Login Endpoint)

```php
public function login(Request $request)
{
    // Authenticate user (your logic here)
    $user = authenticateUser($username, $password);
    
    if ($user) {
        // Issue token with user claims
        $token = $this->issueToken([
            'sub' => $user['id'],           // User ID
            'username' => $user['username'],
            'email' => $user['email'],
            'roles' => ['user', 'admin'],   // User roles
        ], 3600); // Optional: TTL in seconds (default: 1 hour)
        
        return $this->message(true, [
            'token' => $token,
            'user' => $user
        ]);
    }
    
    return $this->message(false, 'Invalid credentials', 'ERR_AUTH');
}
```

### 2. Protect an Endpoint (Require JWT)

```php
public function profile(Request $request)
{
    // This validates the token automatically
    // Returns 401 if invalid/missing
    $payload = $this->requireJwt();
    
    // Access user data from token
    $userId = $payload['sub'];
    $username = $payload['username'];
    
    // Your logic here
    return $this->message(true, $userData);
}
```

### 3. Role-Based Authorization

```php
public function adminUsers(Request $request)
{
    // Require JWT AND admin role
    $payload = $this->requireJwt(['admin']);
    
    // Only users with 'admin' in their roles array can access this
    return $this->message(true, $adminData);
}
```

### 4. Multiple Roles (OR condition)

```php
// User must have at least one of these roles
$payload = $this->requireJwt(['admin', 'moderator']);
```

## API Endpoints

### POST `/api/login`
Authenticate and get a JWT token.

**Request:**
```json
{
  "username": "user@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "user": {
      "id": 1,
      "username": "user@example.com",
      "email": "user@example.com",
      "name": "John Doe",
      "roles": ["user"]
    }
  }
}
```

### GET `/api/profile`
Get user profile (requires JWT token).

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "username": "user@example.com",
      "email": "user@example.com",
      "name": "John Doe"
    },
    "token_issued_at": 1704067200,
    "token_expires_at": 1704070800
  }
}
```

### GET `/api/admin/users`
Get all users (requires JWT token AND admin role).

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "users": [...],
    "count": 10
  }
}
```

### POST `/api/refresh`
Refresh JWT token (requires valid token).

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
  }
}
```

## Client Usage Examples

### Using cURL

```bash
# Login
curl -X POST http://localhost:8080/api/login \
  -H "Content-Type: application/json" \
  -d '{"username":"user@example.com","password":"password123"}'

# Get Profile (with token)
curl -X GET http://localhost:8080/api/profile \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"

# Admin Endpoint
curl -X GET http://localhost:8080/api/admin/users \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Using JavaScript (Fetch API)

```javascript
// Login
const loginResponse = await fetch('http://localhost:8080/api/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    username: 'user@example.com',
    password: 'password123'
  })
});

const loginData = await loginResponse.json();
const token = loginData.data.token;

// Use token in subsequent requests
const profileResponse = await fetch('http://localhost:8080/api/profile', {
  method: 'GET',
  headers: {
    'Authorization': `Bearer ${token}`
  }
});

const profileData = await profileResponse.json();
```

### Using JavaScript (Axios)

```javascript
import axios from 'axios';

// Login
const { data } = await axios.post('http://localhost:8080/api/login', {
  username: 'user@example.com',
  password: 'password123'
});

const token = data.data.token;

// Set default authorization header
axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

// Now all requests will include the token
const profile = await axios.get('http://localhost:8080/api/profile');
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

### Invalid/Expired Token
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

## Key Methods Available

### `issueToken(array $claims, ?int $ttlSeconds = null): string`
Issues a new JWT token with the provided claims.

**Parameters:**
- `$claims`: Array of claims to include (e.g., `['sub' => 1, 'username' => 'user']`)
- `$ttlSeconds`: Optional TTL override (defaults to `JWT_TTL` from env)

**Returns:** JWT token string

### `requireJwt(array $requiredRoles = []): array`
Validates the incoming Bearer token and returns the decoded payload.

**Parameters:**
- `$requiredRoles`: Optional array of roles to require (e.g., `['admin']`)

**Returns:** Decoded token payload array

**Throws:** Automatically returns 401 JSON response if token is invalid/missing

### `getBearerToken(): ?string`
Extracts the Bearer token from the Authorization header.

**Returns:** Token string or null

### `message($status, $message, $errorCode = null)`
Standard API response formatter.

**Parameters:**
- `$status`: Boolean (true for success, false for error)
- `$message`: Response message/data
- `$errorCode`: Optional error code

## Token Claims

Standard JWT claims included automatically:
- `iss`: Issuer (from `JWT_ISSUER`)
- `iat`: Issued at (timestamp)
- `nbf`: Not before (timestamp)
- `exp`: Expiration (timestamp)

Custom claims you can add:
- `sub`: Subject (user ID) - recommended
- `username`: Username
- `email`: User email
- `roles`: Array of user roles

## Security Notes

1. **Secret Key**: Use a strong, random secret key in production
2. **HTTPS**: Always use HTTPS in production to protect tokens in transit
3. **Token Storage**: Store tokens securely on the client (e.g., httpOnly cookies or secure storage)
4. **Expiration**: Set appropriate TTL values based on your security requirements
5. **Role Validation**: Always validate roles on the server side

## Example Controller

See `app/Controllers/ApiController.php` for a complete working example with all endpoints.

