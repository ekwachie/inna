# Session Management

Inna provides secure session management through the `Session` class.

## Basic Usage

### Setting Session Values

```php
use app\Core\Application;

$session = Application::$app->session;

$session->set('user_id', 123);
$session->set('username', 'john');
$session->set('email', 'john@example.com');
```

### Getting Session Values

```php
$userId = $session->get('user_id');
$username = $session->get('username');
```

### Checking Session

```php
if ($session->issert('user_id')) {
    // User is logged in
}
```

### Removing Session Values

```php
// Remove single value
$session->unsert('user_id');

// Destroy entire session
$session->destroy();
```

## Session Configuration

The framework automatically configures secure session settings:

- **HttpOnly**: Prevents JavaScript access
- **Secure**: Only sent over HTTPS (in production)
- **SameSite**: CSRF protection (Strict)
- **Timeout**: 1 hour (3600 seconds)

These settings are configured in `app/Core/Utils/Session.php`.

## Session Lifecycle

### Initialization

Sessions are automatically initialized when the Application is created:

```php
// In Application::__construct()
Session::init();
```

### Session Data

Access session data throughout your application:

```php
$session = Application::$app->session;
$userId = $session->get('user_id');
```

## Common Patterns

### Login

```php
public function login(Request $request, Response $response)
{
    if ($request->isPost()) {
        $body = $request->getBody();
        // Authenticate user...
        
        $session = Application::$app->session;
        $session->set('user_id', $user['id']);
        $session->set('username', $user['name']);
        
        return $response->redirect('/dashboard');
    }
}
```

### Logout

```php
public function logout(Request $request, Response $response)
{
    $session = Application::$app->session;
    $session->destroy();
    
    return $response->redirect('/');
}
```

### Check Authentication

```php
public function dashboard(Request $request, Response $response)
{
    $session = Application::$app->session;
    
    if (!$session->issert('user_id')) {
        return $response->redirect('/login');
    }
    
    $userId = $session->get('user_id');
    // Load user data...
}
```

## Flash Messages

While not part of the Session class, flash messages can be implemented using sessions:

```php
// Set flash message
$session->set('flash_message', 'Operation successful');
$session->set('flash_type', 'success');

// Get and clear flash message
$message = $session->get('flash_message');
$type = $session->get('flash_type');
$session->unsert('flash_message');
$session->unsert('flash_type');
```

## Security Considerations

1. **Never store sensitive data**: Don't store passwords or sensitive info in sessions
2. **Use HTTPS**: Always use HTTPS in production
3. **Regenerate session ID**: Consider regenerating session ID after login
4. **Set timeouts**: Sessions automatically timeout after 1 hour
5. **Validate session data**: Always validate session data before use

## Best Practices

1. **Minimal data**: Store only necessary data in sessions
2. **Clear on logout**: Always destroy sessions on logout
3. **Check before use**: Always check if session value exists before using
4. **Use constants**: Define constants for session keys
5. **Document usage**: Document what session keys you use

## Next Steps

- [Authentication](authentication.md) - Learn about authentication
- [Middleware](middleware.md) - Learn about middleware

