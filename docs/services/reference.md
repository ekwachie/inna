# Payperlez Service Reference

## Client

new Client(apiKey, baseUrl = 'https://api.payperlez.org/api/v1', transport = null) creates a client without sending a request. Client::fromEnvironment() reads PAYPERLEZ_API_KEY and PAYPERLEZ_BASE_URL. payments() and sms() construct service wrappers.

request(method, path, body = null, idempotencyKey = null, query = []) is the low-level JSON API method. It accepts GET/POST and safe API-relative paths; use the typed service methods for normal application code. Custom transports implement Transport::send(method, url, headers, body) returning status, lowercase headers and raw body.

## Payment endpoints

| Method | HTTP path relative to /api/v1 |
| --- | --- |
| checkout(input, key) | POST /checkout/sessions |
| checkoutStatus(id) | GET /checkout/sessions/{id} |
| expireCheckout(id) | POST /checkout/sessions/{id}/expire |
| collect(input, key) | POST /collections |
| disburse(input, key) | POST /disbursements |
| status(reference) | GET /status/{reference} |
| statusByClientReference(reference) | GET /status?client_reference=... |
| collections(query = []) | GET /collections |
| disbursements(query = []) | GET /disbursements |
| balance() | GET /account-balance |
| banks() | GET /banks |
| accountName(input) | POST /account-name |

Creation methods require a positive GHS decimal string or integer and an 8-100-character idempotency key using letters, digits, dot, underscore, colon or hyphen. The wrapper passes the remaining input to the API for authoritative validation.

## SMS endpoints

| Method | HTTP path relative to /api/v1 |
| --- | --- |
| send(senderId, recipient, message, key) | POST /sms/messages |
| bulk(senderId, recipients, message, key) | POST /sms/batches |
| otp(senderId, recipient, purpose, key) | POST /sms/otp/challenges |
| verifyOtp(reference, code) | POST /sms/otp/challenges/{reference}/verify |
| message(reference) | GET /sms/messages/{reference} |
| messages(query = []) | GET /sms/messages |
| batch(reference) | GET /sms/batches/{reference} |
| senders(query = []) | GET /sms/sender-ids |
| balance() | GET /sms/balance |

## Exceptions and pagination

ServiceException exposes errorCode, httpStatus (0 for no usable response), requestId, retryAfter, uncertain and fields. uncertain means a mutation's outcome may need reconciliation. Never treat an exception as proof the provider did nothing.

History responses use the API's data.items and data.pagination with limit, offset and has_more. Fetch subsequent pages explicitly; the SDK does not traverse pages automatically.

See [configuration](services/configuration.md) for transport limits and [testing](advanced/testing.md) for dependency injection.
