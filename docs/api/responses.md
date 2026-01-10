# API Responses

Inna provides standardized response formatting for APIs through the `DApiController` class.

## Standard Response Format

### Success Response

```php
return $this->message(true, $data);
```

Returns:

```json
{
    "success": true,
    "data": { ... }
}
```

### Error Response

```php
return $this->message(false, 'Error message', 'ERROR_CODE');
```

Returns:

```json
{
    "success": false,
    "data": "Error message",
    "error_code": "ERROR_CODE"
}
```

## Response Examples

### Simple Data

```php
return $this->message(true, [
    'id' => 1,
    'name' => 'John'
]);
```

### List Response

```php
return $this->message(true, [
    'items' => $items,
    'count' => count($items),
    'page' => 1
]);
```

### Error with Details

```php
return $this->message(false, [
    'message' => 'Validation failed',
    'errors' => [
        'email' => 'Email is required',
        'password' => 'Password must be at least 8 characters'
    ]
], 'VALIDATION_ERROR');
```

## Custom JSON Response

For custom response formats:

```php
return $this->json([
    'status' => 'ok',
    'result' => $data,
    'timestamp' => time()
]);
```

## HTTP Status Codes

Set status codes with the Response object:

```php
use app\Core\Application;

$response = Application::$app->response;
$response->setStatusCode(201); // Created
return $this->message(true, $data);
```

## Common Response Patterns

### Created Resource

```php
$id = $db->insert('users', $data);
$response->setStatusCode(201);
return $this->message(true, [
    'id' => $id,
    'message' => 'User created'
]);
```

### Not Found

```php
if (empty($user)) {
    $response->setStatusCode(404);
    return $this->message(false, 'User not found', 'NOT_FOUND');
}
```

### Validation Error

```php
$response->setStatusCode(422);
return $this->message(false, $errors, 'VALIDATION_ERROR');
```

### Unauthorized

```php
$response->setStatusCode(401);
return $this->message(false, 'Unauthorized', 'UNAUTHORIZED');
```

## Response Headers

Add custom headers:

```php
$this->header('X-Rate-Limit: 100');
$this->header('X-Rate-Limit-Remaining: 99');
return $this->message(true, $data);
```

## Best Practices

1. **Consistent format**: Always use `message()` for consistency
2. **Meaningful errors**: Provide clear error messages
3. **Error codes**: Use consistent error codes
4. **Status codes**: Set appropriate HTTP status codes
5. **Documentation**: Document your API response formats

## Next Steps

- [API Controllers](controllers.md) - Learn about API controllers
- [API Basics](basics.md) - Learn about API development

