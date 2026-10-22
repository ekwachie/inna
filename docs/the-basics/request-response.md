# Request and Response

## Read input

| Method | Result |
| --- | --- |
| getMethod() | Lowercase HTTP method |
| getUrl() | Path without query string |
| getRouteParam(name, default) | One named route parameter |
| getRouteParams() | All route parameters |
| getQueryParams() | Raw query array; validate before use |
| getHeader(name, default) | Case-insensitive header lookup through server variables |
| getJsonBody(maxBytes = 65536) | Strict JSON object as an array |
| getRawBody(maxBytes = 1048576) | Cached raw body |
| setAttribute(name, value) / getAttribute(name, default) | Per-request middleware context |

getJsonBody() requires application/json, accepts a charset parameter, rejects malformed JSON and top-level arrays/scalars, and limits depth to 32. Wrong media type is 415, invalid JSON is 400 and oversized bodies are 413. Reading raw input first still permits a later JSON read from the cached bytes.

getBody() is a legacy GET/form helper that HTML-escapes values. Use getJsonBody for APIs and validated raw query/form values for new code; escape when rendering rather than before password verification or storage.

## Respond

```php
$response->setStatusCode(201);
return $this->render('saved', ['name' => $name]);
```

Response::redirect('/orders', 303) sends Location and exits. Response::json(array) sends JSON and exits; ApiResponse adds the standard API envelope. Response::header(value) sets a raw header.

## Downloads

After checking authorization:

```php
$object = $storage->read($storageId);
$response->download(
    $object['bytes'],
    $object['metadata']['original_name'],
    $object['metadata']['mime_type']
);
```

download sends attachment disposition, Content-Length, nosniff and private/no-store headers. It buffers bounded file bytes and exits. It is not a large-file streaming API.

