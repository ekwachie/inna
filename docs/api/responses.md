# API Responses

## Success

```json
{
  "success": true,
  "data": {"id": "example"},
  "meta": {"request_id": "req_example"}
}
```

Use $this->success($data, 201) in DApiController, or ApiResponse::success($data, 201) elsewhere.

## Error

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Provide a name.",
    "fields": {"name": "Required."}
  },
  "meta": {"request_id": "req_example"}
}
```

Throw new ApiException('VALIDATION_ERROR', 'Provide a name.', 422, ['name' => 'Required.']) or call error(). Responses include X-Request-ID and Cache-Control: no-store. The request ID is server-generated and correlates with request activity logs.

## Status codes

Use 400 for malformed requests, 401 for missing/invalid authentication, 403 for denied access, 404 for missing resources, 405 for unsupported methods, 409 for conflicts, 413 for size limits, 415 for unsupported media types, 422 for validly encoded but invalid input, and 500 for unexpected failures.

A 202 response reports accepted/pending work, not completed payment or message delivery. Consult the operation's status field. ApiResponse methods terminate the request; return 204 explicitly with Response when no JSON body is needed.

