# Routing

Define routes in index.php before $app->run(). Callback signatures receive Request and Response; closures may omit unused arguments.

## Methods

```php
$app->router->get('/orders', [OrderController::class, 'index']);
$app->router->post('/orders', [OrderController::class, 'create']);
$app->router->put('/orders/{id}', [OrderController::class, 'replace']);
$app->router->patch('/orders/{id}', [OrderController::class, 'update']);
$app->router->delete('/orders/{id}', [OrderController::class, 'remove']);
```

Import your controller class first. Inna does not automatically create CRUD actions, routes or database tables.

## Parameters

```php
$app->router->get('/orders/{id:[0-9]+}', function ($request) {
    return 'Order ' . $request->getRouteParam('id');
});
```

Unconstrained parameters match one non-empty path segment, including hyphens. Use getRouteParams() for all named values. Constraint patterns can use character classes and + or *; brace quantifiers are not supported in route placeholders.

## Matching and status

Trailing slashes normalize to the same route. Static route punctuation is literal. Exact matches take precedence. Put more-specific parameter routes ahead of broader patterns.

GET routes also accept HEAD. The router returns 405 with Allow for an unsupported method on a known path, and 404 for unknown paths. OPTIONS on a registered path returns 204 with Allow. This is method discovery, not automatic cross-origin authorization.

## Middleware

Middleware is registered in a controller constructor, not on the route object. See [middleware](security/middleware.md). Inna currently has no route-group or fluent route-middleware API.

