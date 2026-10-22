# Controllers

Controllers coordinate input, authorization, services and responses. Keep business persistence in models and external integrations in services.

## A complete action

Create app/Controllers/GreetingController.php:

```php
<?php
namespace app\Controllers;

use app\Core\Controller;
use app\Core\Request;

final class GreetingController extends Controller
{
    public function show(Request $request)
    {
        return $this->render('greeting', [
            'name' => $request->getRouteParam('name', 'friend'),
        ]);
    }
}
```

Create public/views/greeting.twig containing:

```twig
<h1>Hello, {{ name }}</h1>
```

Register it in index.php:

```php
$app->router->get('/greet/{name}', [\app\Controllers\GreetingController::class, 'show']);
```

## Return values

render() returns HTML. Returning a string from an action writes it to the response. Response::redirect() and JSON/download methods terminate the request, so do not continue processing afterward.

Controller has middleware(), getMiddlewares() and setMiddlewares(). DApiController extends Controller and adds success(), error() and protected JWT helpers.

## Errors and boundaries

Throw ApiException for a public validation or access error. Do not echo raw exceptions, database queries or service credentials. A controller must authorize an object before calling its storage service. See [API responses](api/responses.md).

