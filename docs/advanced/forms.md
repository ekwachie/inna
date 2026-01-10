# Forms

Inna provides form helpers for building and validating forms.

## Form Helper

The framework includes a `Form` class for building forms. Check `app/Core/form/Form.php` and `app/ext/AppForm.php`.

### Basic Form

```php
use app\core\form\Form;

// Start form
$form = Form::begin('/contact', 'POST');

// Form fields
echo Form::field($model, 'name');
echo Form::field($model, 'email');
echo Form::field($model, 'message');

// End form
Form::end();
```

## Form Validation

Combine forms with the validation system:

```php
use app\Core\Model;
use app\core\form\Form;

$model = new Model();

// In controller
if ($request->isPost()) {
    $body = $request->getBody();
    
    $validation = new Model();
    $validation->name('name')
        ->value($body['name'] ?? '')
        ->required()
        ->min(3);
    
    if ($validation->isSuccess()) {
        // Process form
    }
}
```

## CSRF Protection

Implement CSRF protection:

### Generate Token

```php
use app\Core\Application;

$session = Application::$app->session;
$token = bin2hex(random_bytes(32));
$session->set('csrf_token', $token);
```

### In Form

```html
<input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
```

### Validate Token

```php
$session = Application::$app->session;
$body = $request->getBody();

if ($body['csrf_token'] !== $session->get('csrf_token')) {
    echo $this->setFlash('error', 'Invalid token');
    return $this->render('form');
}
```

## Complete Form Example

```php
<?php

namespace app\Controllers;

use app\Core\Controller;
use app\Core\Request;
use app\Core\Response;
use app\Core\Application;
use app\Core\Model;

class ContactController extends Controller
{
    public function index(Request $request, Response $response)
    {
        if ($request->isPost()) {
            $body = $request->getBody();
            $validation = new Model();
            
            // Validate
            $validation->name('name')
                ->value($body['name'] ?? '')
                ->required()
                ->min(3);
            
            $validation->name('email')
                ->value($body['email'] ?? '')
                ->required()
                ->pattern('email');
            
            $validation->name('message')
                ->value($body['message'] ?? '')
                ->required()
                ->min(10);
            
            if ($validation->isSuccess()) {
                // Send email or save to database
                echo $this->setFlash('success', 'Message sent!');
                return $response->redirect('/contact');
            } else {
                $errors = $validation->getErrors();
                echo $this->setFlash('error', implode(', ', $errors));
            }
        }
        
        // Generate CSRF token
        $session = Application::$app->session;
        $token = bin2hex(random_bytes(32));
        $session->set('csrf_token', $token);
        
        return $this->render('contact', ['csrf_token' => $token]);
    }
}
```

## View Template

```twig
<form action="/contact" method="POST">
    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
    
    <div>
        <label for="name">Name</label>
        <input type="text" name="name" id="name" value="{{ old.name|default('') }}" required>
    </div>
    
    <div>
        <label for="email">Email</label>
        <input type="email" name="email" id="email" value="{{ old.email|default('') }}" required>
    </div>
    
    <div>
        <label for="message">Message</label>
        <textarea name="message" id="message" required>{{ old.message|default('') }}</textarea>
    </div>
    
    <button type="submit">Send</button>
</form>
```

## Best Practices

1. **Always validate**: Validate all form input
2. **Use CSRF tokens**: Protect against CSRF attacks
3. **Sanitize output**: Always sanitize user input
4. **Provide feedback**: Show success/error messages
5. **Preserve input**: Preserve user input on validation errors

## Next Steps

- [Validation](/utilities/validation.md) - Learn about validation
- [Security](/security/authentication.md) - Learn about security

