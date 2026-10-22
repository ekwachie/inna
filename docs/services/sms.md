# Payperlez SMS and OTP

Use an approved sender UUID belonging to the same business and test/live environment as your API key. Sending requires available SMS credits and the appropriate key permissions.

## Send a message

```php
$client = \app\Services\Payperlez\Client::fromEnvironment();
$result = $client->sms()->send(
    $senderUuid,
    '0244000000',
    'Your order is ready.',
    'sms-order-4821'
);
```

The sender value is the approved sender UUID, not a free-form brand name. The response reports acceptance/queue state. It does not guarantee handset delivery.

## Bulk messages

```php
$result = $client->sms()->bulk(
    $senderUuid,
    ['0244000000', '0209998888'],
    'Your update is available.',
    'sms-campaign-4821'
);
```

The API currently accepts 1-500 recipients per batch, normalizes/deduplicates them, and accounts for message encoding/segments. Query batch(reference) for batch state. Do not hard-code a price per message in Inna.

## OTP

```php
$challenge = $client->sms()->otp(
    $senderUuid,
    '0244000000',
    'login',
    'otp-login-attempt-4821'
);
```

Read the API's challenge reference from the response and persist it against your application's login attempt. After the user submits a code:

```php
$result = $client->sms()->verifyOtp($challengeReference, '001234');
```

Codes must remain six-digit strings so leading zeros are retained. The API creates and verifies OTPs; your application decides what a successfully verified challenge authorizes. Bind it to the expected actor, recipient and purpose. Never log codes or trust a client-provided verified flag. Test environments may return a test code; do not expose that behavior in live flows.

## Query and retry

message(reference), messages(query), batch(reference), senders(query) and balance() query state. Sending uses required idempotency keys. Reuse the original key and payload for an uncertain retry, and inspect message state rather than sending another message with a fresh key.

A 429 ServiceException may include retryAfter seconds. Your scheduler chooses when to retry. The client does not sleep or retry automatically.
