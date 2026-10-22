# Error Handling

## Public errors

Throw ApiException(errorCode, message, status = 400, fields = []) for errors a caller may understand:

```php
throw new \app\Core\ApiException(
    'PROJECT_NOT_FOUND',
    'Project not found.',
    404
);
```

Use stable codes for client logic. Messages should help recovery without exposing SQL, infrastructure paths or credentials.

## Unexpected errors

Application::run catches routing/action exceptions. Unexpected failures return a generic 500 and log a request ID with the exception class. API requests receive the standard envelope; ordinary web requests receive HTML. Bootstrap failures before run require server-log diagnosis.

Do not catch every exception and return HTTP 200. Preserve an exception's failure state, and do not treat a payment transport error as a confirmed rejection.

## Service errors

Catch Payperlez ServiceException when your application needs to schedule reconciliation or return an upstream-specific response. Inspect errorCode, httpStatus, requestId, retryAfter and uncertain. Store the original idempotency key before the request. Never print raw response bodies.

Storage read errors can indicate missing files, missing keys or authentication failures; report a generic unavailable response and record operational context without file contents.

## Troubleshooting

Check the server PHP version/extensions, .env parsing, private directory ownership, key availability, PDO driver and API key permissions. A 401 is authentication, a 403 is authorization, a 409 is conflicting operation state, and a 413 is a size limit. Keep the request ID when reporting an API failure.

