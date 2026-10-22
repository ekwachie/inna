# Testing

## Framework suite

```bash
composer test
php tests/http.php
```

composer test runs tests/framework.php and tests/documentation.php. It verifies route behavior, authorization, JWT, encrypted objects, key rotation, transport outcomes, signatures, database transactions and rendered documentation links.

The HTTP suite starts a temporary loopback server with a disposable SQLite-backed/test configuration and private storage. It exercises JSON errors, HTTP methods, actual multipart uploads and access-controlled downloads, then removes test files and stops the server.

Install pdo_sqlite to run the regression suite. No production database credentials, live payment or SMS calls are needed.

## Integration doubles

Inject a Transport implementation into the Payperlez client:

```php
use app\Services\Payperlez\Transport;

final class TestTransport implements Transport
{
    public function send(string $method, string $url, array $headers, ?string $body): array
    {
        return [
            'status' => 202,
            'headers' => [],
            'body' => '{"success":true,"data":{"status":"pending"},"meta":{"request_id":"req_test"}}',
        ];
    }
}
```

Create new Client('test-key', 'https://api.payperlez.org/api/v1', new TestTransport()). Assert endpoint, payload, headers and failure behavior in your own tests. A fake transport must never log API keys.

## Application tests

Test ownership denial before storage reads; invalid MIME and oversized uploads; deduplication and out-of-order webhooks; and retrying payment/SMS operations with the original key. Test actual MySQL migrations against a disposable MySQL/MariaDB database before deployment; SQLite unit tests do not prove MySQL DDL behavior.

The local suite verifies client contracts and safe failure handling. A test-key smoke against the real Payperlez API is a separate deployment check, not a claim the mocked suite can make.
