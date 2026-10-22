# Payperlez Payments

Configure a Payperlez client and an appropriately scoped test API key before calling these methods. Calls are server-to-server and return Payperlez's public response envelope.

## Hosted checkout

```php
$client = \app\Services\Payperlez\Client::fromEnvironment();
$result = $client->payments()->checkout([
    'amount' => '100.00',
    'currency' => 'GHS',
    'description' => 'Order 4821',
    'payment_methods' => ['mobile_money'],
    'success_url' => 'https://shop.example.com/payment/return',
    'cancel_url' => 'https://shop.example.com/payment/cancel',
], 'checkout-order-4821');
$checkoutUrl = $result['data']['checkout_url'];
```

Persist the checkout session ID and idempotency key with your order. Redirect deliberately in your controller after validating the returned URL is the expected Payperlez checkout destination. A browser return URL is not proof of payment.

Use checkoutStatus(sessionId) to inspect the session. expireCheckout(sessionId) expires an unused session; an initiated payment cannot simply be cancelled this way.

## Direct collection

```php
$result = $client->payments()->collect([
    'amount' => '100.00',
    'network' => 'mtn',
    'customer_msisdn' => '0244000000',
    'customer_name' => 'Ama Owusu',
    'client_reference' => 'order-4821',
    'narration' => 'Order 4821',
], 'collection-order-4821');
```

Amounts use GHS decimal strings or whole integer GHS values in this wrapper. Floats, zero, negative values, more than two decimal places and exponent notation are rejected. No fixed fee is added locally.

## Disbursement

```php
$result = $client->payments()->disburse([
    'amount' => '250.00',
    'channel' => 'mobile_money',
    'recipient_name' => 'Kojo Mensah',
    'recipient_msisdn' => '0209998888',
    'network' => 'telecel',
    'narration' => 'Supplier payout',
], 'payout-invoice-4821');
```

For disbursements, amount is the maximum total business debit budget including fees, not a guaranteed recipient principal. Use confirmed API values when reconciling. Do not calculate provider fees or balances in the framework client.

## Confirmation and retries

```php
$result = $client->payments()->status($payperlezReference);
$confirmed = ($result['data']['status'] ?? null) === 'succeeded';
```

Check the expected order reference, amount, currency and business/environment through your stored operation before fulfilling an order. success=true describes the API request, not necessarily the payment.

Operation states include processing, pending, succeeded, failed, reversed and unknown. A timeout or malformed response after a create may have an uncertain outcome. Poll status/statusByClientReference or retry the identical create with the original Idempotency-Key. Never generate another key simply because a request timed out.

## Other methods

collections(query) and disbursements(query) return paginated history. balance() reads the business balance; banks() lists supported banks; accountName(input) resolves an account name. Each needs its API permission. See [reference](services/reference.md) for endpoint mappings and [webhooks](services/webhooks.md) for signed status events.
