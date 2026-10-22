# Forms and CSRF

Use explicit Twig forms. The legacy PHP form field renderer is incomplete and should not be used as a generic form builder.

## Controller

A controller serving and accepting a form should register CsrfMiddleware:

```php
<?php
namespace app\Controllers;

use app\Core\Controller;
use app\Core\Request;
use app\Core\Middlewares\CsrfMiddleware;
use app\Core\Utils\DUtil;

final class MessageController extends Controller
{
    public function __construct()
    {
        $this->middleware(new CsrfMiddleware());
    }

    public function form(Request $request)
    {
        return $this->render('message', ['csrf_token' => DUtil::csrf_token()]);
    }

    public function submit(Request $request)
    {
        $name = $_POST['name'] ?? null;
        if (!is_string($name) || trim($name) === '') {
            throw new \app\Core\ApiException('VALIDATION_ERROR', 'Provide a name.', 422);
        }
        return $this->render('message', [
            'csrf_token' => DUtil::csrf_token(),
            'notice' => 'Thanks, ' . $name,
        ]);
    }
}
```

Register GET /message to form and POST /message to submit.

## Template

Create public/views/message.twig:

```twig
<form action="/message" method="post">
    <input type="hidden" name="csrf_token" value="{{ csrf_token }}">
    <label for="name">Name</label>
    <input id="name" name="name" required maxlength="100">
    <button type="submit">Send</button>
</form>
{% if notice is defined %}<p>{{ notice }}</p>{% endif %}
```

Twig escapes the notice. Validate length and business rules server-side even when HTML fields have constraints. Add AuthMiddleware when the form needs a signed-in actor.

## Upload forms

Use enctype="multipart/form-data" and a file input. Check authorization, then pass $_FILES['file'] to a storage service. PHP upload limits and the service's byte limit both apply. See [encrypted uploads](storage/encrypted-uploads.md).

