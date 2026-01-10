# Error Handling

Inna provides multiple layers of error handling for your application.

## Error Logging

### Automatic Error Logging

PHP errors are automatically logged to:
- `log/errors/error_log_[date].log`

Application errors are logged to:
- `log/app_error_log_[date].log`

### Configuration

Error logging is configured in `config/config.php`:

```php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', $_SERVER['DOCUMENT_ROOT'] . '/log/errors/error_log_' . date("j.n.Y") . '.log');
```

## 404 Errors

### Custom 404 Page

Create `public/views/404.twig`:

```twig
{% extends 'layouts/layout.twig' %}

{% block content %}
    <h1>404 - Page Not Found</h1>
    <p>The page you're looking for doesn't exist.</p>
    <a href="/">Go Home</a>
{% endblock %}
```

The router automatically renders this when no route matches.

## Exception Handling

### Try-Catch Blocks

```php
try {
    $db->insert('users', $data);
} catch (\Exception $e) {
    error_log($e->getMessage());
    echo $this->setFlash('error', 'An error occurred');
}
```

### Database Errors

```php
try {
    $result = $db->select("SELECT * FROM users WHERE id = :id", ['id' => $id]);
} catch (\PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    return $this->render('error', ['message' => 'Database error']);
}
```

## Error Pages

### Custom Error Page

Create error view:

```twig
{# public/views/error.twig #}
{% extends 'layouts/layout.twig' %}

{% block content %}
    <h1>Error</h1>
    <p>{{ message }}</p>
{% endblock %}
```

### In Controller

```php
public function show(Request $request, Response $response)
{
    try {
        $user = $this->getUser($id);
        if (!$user) {
            $response->setStatusCode(404);
            return $this->render('error', ['message' => 'User not found']);
        }
        return $this->render('user/show', ['user' => $user]);
    } catch (\Exception $e) {
        error_log($e->getMessage());
        $response->setStatusCode(500);
        return $this->render('error', ['message' => 'An error occurred']);
    }
}
```

## API Error Responses

For API endpoints, return JSON errors:

```php
public function apiEndpoint(Request $request, Response $response)
{
    try {
        $data = $this->getData();
        return $this->apiMessage(true, $data);
    } catch (\Exception $e) {
        error_log($e->getMessage());
        $response->setStatusCode(500);
        return $this->apiMessage(false, 'Internal server error', 'SERVER_ERROR');
    }
}
```

## Best Practices

1. **Log errors**: Always log errors for debugging
2. **Don't expose details**: Don't expose sensitive error details to users
3. **Use try-catch**: Wrap risky operations in try-catch blocks
4. **Set status codes**: Set appropriate HTTP status codes
5. **Handle gracefully**: Provide user-friendly error messages

## Next Steps

- [Logger](/utilities/logger.md) - Learn about logging
- [Security](/security/authentication.md) - Learn about security

