# Validation

Inna includes a powerful validation system based on the `Model` class for validating user input.

## Basic Validation

### Creating a Validator

```php
use app\Core\Model;

$validation = new Model();

// Validate name
$validation->name('name')
    ->value($request->getBody()['name'] ?? '')
    ->required()
    ->min(3)
    ->max(100);

// Validate email
$validation->name('email')
    ->value($request->getBody()['email'] ?? '')
    ->required()
    ->pattern('email');

// Check if valid
if ($validation->isSuccess()) {
    // Validation passed
} else {
    $errors = $validation->getErrors();
    // Handle errors
}
```

## Validation Rules

### required()

Field must not be empty:

```php
$validation->name('email')->value($email)->required();
```

### min()

Minimum length or value:

```php
// String length
$validation->name('password')->value($password)->min(8);

// Numeric value
$validation->name('age')->value($age)->min(18);
```

### max()

Maximum length or value:

```php
// String length
$validation->name('title')->value($title)->max(255);

// Numeric value
$validation->name('quantity')->value($quantity)->max(100);
```

### pattern()

Validate against predefined patterns:

```php
// Email
$validation->name('email')->value($email)->pattern('email');

// URL
$validation->name('website')->value($website)->pattern('url');

// Integer
$validation->name('age')->value($age)->pattern('int');

// Alpha only
$validation->name('name')->value($name)->pattern('alpha');

// Alphanumeric
$validation->name('username')->value($username)->pattern('alphanum');
```

Available patterns:
- `email` - Email address
- `url` - URL
- `int` - Integer
- `float` - Float number
- `alpha` - Letters only
- `alphanum` - Letters and numbers
- `text` - Text with common characters
- `date_dmy` - Date (DD-MM-YYYY)
- `date_ymd` - Date (YYYY-MM-DD)
- `tel` - Telephone number

### customPattern()

Custom regex pattern:

```php
$validation->name('phone')
    ->value($phone)
    ->customPattern('^\+?[1-9]\d{1,14}$');
```

### equal()

Value must match:

```php
$validation->name('password_confirm')
    ->value($confirmPassword)
    ->equal($password);
```

## File Validation

### File Size

```php
$validation->name('avatar')
    ->file($_FILES['avatar'] ?? [])
    ->maxSize(2097152); // 2MB in bytes
```

### File Extension

```php
$validation->name('document')
    ->file($_FILES['document'] ?? [])
    ->ext('pdf');
```

## Complete Example

```php
use app\Core\Model;

public function register(Request $request, Response $response)
{
    if ($request->isPost()) {
        $body = $request->getBody();
        $validation = new Model();
        
        // Name validation
        $validation->name('name')
            ->value($body['name'] ?? '')
            ->required()
            ->min(3)
            ->max(100);
        
        // Email validation
        $validation->name('email')
            ->value($body['email'] ?? '')
            ->required()
            ->pattern('email');
        
        // Password validation
        $validation->name('password')
            ->value($body['password'] ?? '')
            ->required()
            ->min(8);
        
        // Confirm password
        $validation->name('password_confirm')
            ->value($body['password_confirm'] ?? '')
            ->required()
            ->equal($body['password'] ?? '');
        
        // Check validation
        if (!$validation->isSuccess()) {
            $errors = $validation->getErrors();
            echo $this->setFlash('error', implode(', ', $errors));
            return $this->render('auth/register');
        }
        
        // Validation passed, create user
        // ...
    }
    
    return $this->render('auth/register');
}
```

## Getting Errors

### All Errors

```php
$errors = $validation->getErrors();
// Returns: ['Field name required.', 'Field email invalid.']
```

### Display Errors

```php
echo $validation->displayErrors();
// Returns HTML formatted errors
```

### Check Success

```php
if ($validation->isSuccess()) {
    // No errors
} else {
    // Has errors
}
```

## Sanitization

### purify()

Sanitize strings to prevent XSS:

```php
$clean = $validation->purify($userInput);
// Returns HTML-escaped string
```

## Static Validation Methods

### Quick Checks

```php
use app\Core\Model;

if (Model::is_email($email)) {
    // Valid email
}

if (Model::is_int($value)) {
    // Valid integer
}

if (Model::is_url($url)) {
    // Valid URL
}

if (Model::is_alpha($text)) {
    // Letters only
}
```

## Best Practices

1. **Validate early**: Validate input as soon as possible
2. **Use appropriate rules**: Choose the right validation rule
3. **Provide clear errors**: Give users helpful error messages
4. **Sanitize output**: Always sanitize user input before displaying
5. **Server-side validation**: Never rely only on client-side validation

## Next Steps

- [Forms](/advanced/forms.md) - Learn about form handling
- [Security](/security/authentication.md) - Learn about security

