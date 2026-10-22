# Payperlez Services

Inna supplies a server-to-server client for the public Payperlez Biz API. It does not embed provider credentials, provider balances or the Biz application's settlement implementation.

## Configure

```dotenv
PAYPERLEZ_BASE_URL=https://api.payperlez.org/api/v1
PAYPERLEZ_API_KEY=
```

Obtain a test key through Payperlez Biz and grant only the permissions your application needs. The key selects test/live; changing a local environment label does not change a key's environment.

```php
use app\Services\Payperlez\Client;

$client = Client::fromEnvironment();
$payments = $client->payments();
$sms = $client->sms();
```

Constructing the client sends no request. Use separate key configuration per deployment. Never put the key in browser JavaScript or a checkout redirect.

## HTTP behavior

The default transport verifies TLS, permits HTTPS only and does not follow redirects. It has a 5-second connection timeout, 30-second total timeout and 2 MB response limit. Requests use X-API-Key and JSON.

Override transport limits explicitly if needed:

```php
use app\Services\Payperlez\CurlTransport;

$client = new Client(
    $_ENV['PAYPERLEZ_API_KEY'],
    'https://api.payperlez.org/api/v1',
    new CurlTransport(5, 45, 2097152)
);
```

The base URL is trusted server configuration. Never derive it from a user-supplied URL. Request paths are relative to that configured base.

## Results and failures

Methods return the full response envelope: success, data and meta.request_id. They do not redirect the browser, change sessions, update your database, calculate fees or mark an order paid.

ServiceException contains errorCode, httpStatus, requestId, retryAfter, uncertain and fields. The exception message uses a safe error code rather than raw upstream response text. Avoid logging fields/payloads because they may contain personal data.

There are no automatic retries. Persist the operation and idempotency key before sending; reuse the same key and identical payload when retrying an uncertain create. See [payments](services/payments.md), [SMS](services/sms.md) and [webhooks](services/webhooks.md).
