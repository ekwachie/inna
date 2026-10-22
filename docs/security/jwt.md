# JSON Web Tokens

Inna's Jwt wrapper uses firebase/php-jwt 7 with HS256. It requires a configured secret and issuer, validates signatures and time claims, and rejects tokens without integer exp, iat and nbf or the expected iss.

## Configure and issue

Generate JWT_SECRET with openssl rand -hex 32. Set JWT_ISSUER to a stable identifier for your application.

```php
use app\Core\Utils\Jwt;

$jwt = new Jwt();
$token = $jwt->encode([
    'sub' => (string) $userId,
    'roles' => ['user'],
], 3600);
$claims = $jwt->decode($token);
```

encode overwrites iss, iat, nbf and exp using the configured issuer and a positive TTL. Other claims remain yours. decode throws for malformed, expired, wrongly signed or incorrectly issued tokens.

## Controller helpers

In a DApiController action:

```php
$claims = $this->requireJwt(['admin']);
$this->success(['actor' => $claims['sub']]);
```

requireJwt checks Bearer authentication and requires at least one of the supplied roles. Missing/invalid tokens return 401; missing roles return 403. It uses signed claims; recheck database state for sensitive actions.

## Lifetime and rotation

The starter refresh endpoint requires a still-valid access token and an active database account. It is not a separate long-lived refresh-token system. Expired tokens require authentication again.

Changing JWT_SECRET invalidates existing tokens. Plan rotation and logout/revocation behavior for your application. Do not reuse the JWT secret for file encryption or webhook verification. Tokens are signed, not encrypted: their claims can be read by anyone holding them.

