# Request Lifecycle

## Bootstrap

The web server forwards a route to index.php. Composer loads classes, config/config.php reads .env, configures headers, creates the application and records request activity. The database is lazy; optional GeoIP failure is tolerated.

## Routing and middleware

The router matches the normalized path and HTTP method. Exact routes take precedence over parameter routes. Named parameters are added to Request. For a controller callback, Inna creates the controller, records its action and executes its middleware in registration order.

If middleware rejects a request, the action never runs. Authorization must happen before reading private files or initiating external operations.

## Producing a response

An action can return an HTML string, render a Twig template, redirect, send a download, or call ApiResponse. JSON response helpers send headers and terminate the request.

HEAD follows GET routing but suppresses normal returned content. Automatic OPTIONS responses advertise registered methods. A missing route is 404; a known path with another method is 405.

## Failure handling

Application::run catches exceptions raised during routing/actions. ApiException carries a public error code, status and optional field errors. Unexpected exceptions produce a generic 500 response and a log entry containing a request ID and exception class, without exposing the exception message.

API paths and requests accepting application/json receive the standard JSON error envelope. Ordinary web requests receive an HTML error. Failures before Application::run, such as a malformed environment file, are bootstrap failures and must be diagnosed from server logs.

