# Building APIs

Use DApiController, strict JSON input, explicit authentication and standard response envelopes.

## A complete endpoint

Create app/Controllers/EchoController.php:

```php
<?php
namespace app\Controllers;

use app\Core\ApiException;
use app\Core\DApiController;
use app\Core\Request;

final class EchoController extends DApiController
{
    public function create(Request $request)
    {
        $body = $request->getJsonBody();
        if (!is_string($body['name'] ?? null) || trim($body['name']) === '') {
            throw new ApiException('VALIDATION_ERROR', 'Provide a name.', 422);
        }
        $this->success(['name' => $body['name']], 201);
    }
}
```

Register it before $app->run():

```php
$app->router->post('/api/echo', [\app\Controllers\EchoController::class, 'create']);
```

Test it:

```bash
curl -i http://127.0.0.1:8080/api/echo \
  -H 'Content-Type: application/json' \
  -d '{"name":"Ama"}'
```

Expect HTTP 201 with success=true, data.name=Ama and meta.request_id.

## API design

Use GET for reads, POST for creation, PATCH for partial updates, PUT for replacement and DELETE for deletion. Register each explicitly. Validate inputs before passing them to models/services.

Inna does not enable wildcard CORS. Keep APIs same-origin or configure explicit trusted origins and preflight handling for your application. API credentials belong on servers.

