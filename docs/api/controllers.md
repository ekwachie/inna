# API Controllers

DApiController extends Controller and provides standard JSON responses, protected JWT helpers and middleware registration.

## Methods

| Method | Behavior |
| --- | --- |
| success(data, status = 200) | Send a standard success response and exit |
| error(code, message, status = 400, fields = []) | Send a standard error response and exit |
| requireJwt(roles = []) | Validate Bearer token and optional roles; protected |
| issueToken(claims, ttl = null) | Issue a signed JWT; protected |
| json(value, options = null, depth = 512) | Send raw JSON and exit |
| message(status, message, errorCode = null) | Legacy envelope; retained for compatibility |

Prefer success/error for new endpoints. message does not provide the newer error/status contract and is not used by the new starter endpoints.

## Services and errors

Construct Payperlez or storage services inside actions/application services that need them. Catch ServiceException to handle upstream outcomes deliberately; otherwise it becomes a generic 500 through the application error handler. Never echo provider payloads or arbitrary exceptions.

See [responses](api/responses.md), [Payperlez configuration](services/configuration.md) and [webhooks](services/webhooks.md).

