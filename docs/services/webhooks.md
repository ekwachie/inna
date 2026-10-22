# Payperlez Webhooks

Use WebhookVerifier to authenticate Payperlez collection, disbursement and settlement events. Obtain the complete whsec_ signing secret from Payperlez webhook configuration and keep it on your backend.

## Verify before decoding/trusting

```php
use app\Core\ApiException;
use app\Services\Payperlez\WebhookVerifier;

$raw = $request->getRawBody();
$verifier = new WebhookVerifier(
    [$_ENV['PAYPERLEZ_WEBHOOK_SECRET']],
    $_ENV['PAYPERLEZ_BUSINESS_ID'],
    $_ENV['PAYPERLEZ_ENVIRONMENT']
);
try {
    $event = $verifier->verify(
        $raw,
        $request->getHeader('X-Payperlez-Signature', '')
    );
} catch (\UnexpectedValueException | \JsonException $error) {
    throw new ApiException('INVALID_WEBHOOK', 'Invalid webhook.', 401);
}
```

The verifier checks the exact raw body, HMAC-SHA256 signature, timestamp within 300 seconds, business ID, environment and basic event shape. It supports an array of current/previous signing secrets during rotation. Keep host clocks synchronized. Changing JSON whitespace before verification invalidates a valid signature.

## Accept durably

After verification, persist event.id with a UNIQUE constraint and record the event in your database/outbox transaction. Duplicate already-accepted events should return 2xx. Acknowledge only after durable acceptance.

Inna verifies authenticity; it does not create your application's deduplication table or mark orders paid. Implement that persistence against your own schema. Return 204 explicitly after commit:

```php
$response->setStatusCode(204);
return '';
```

## Ordering and state

Deliveries may repeat or arrive out of order. Do not let a late pending event overwrite a confirmed result. Retrieve payment status when events conflict and use your application's state-transition rules. Handle reversals explicitly.

Use event.id from the signed payload for deduplication. Event headers are useful diagnostics but not a substitute for the authenticated body.

## Rotation and deployment

Retain the old signing secret while outstanding events signed with it may still arrive. Accept only configured event types, expected business and expected environment. Use a public HTTPS endpoint when configuring Payperlez deliveries; no live webhook is sent by the framework tests.
