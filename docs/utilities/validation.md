# Validation

Model contains a small fluent validator. It is not a schema engine: validate input types before applying rules, and choose exact business rules in your controller/service.

## Validate text

```php
use app\Core\Model;
use app\Core\ApiException;

$body = $request->getJsonBody();
$name = $body['name'] ?? null;
if (!is_string($name)) throw new ApiException('VALIDATION_ERROR', 'Name must be text.', 422);

$validation = new Model();
$validation->name('name')->value($name)->required()->min(2)->max(100);
if (!$validation->isSuccess()) {
    throw new ApiException('VALIDATION_ERROR', 'Check the supplied values.', 422, ['name' => implode(' ', $validation->getErrors())]);
}
```

required rejects null, the empty string and an empty array; integer zero is valid. min/max measure bytes for strings and compare values for numeric inputs. Trim text deliberately when your business rule requires it.

## Available rules

pattern(name) checks one of Model::$patterns, such as alpha, alphanum, int or date_ymd. customPattern(pattern) wraps your expression in full-string anchors. equal(value) checks equality. isSuccess returns whether the error list is empty, and getErrors returns its messages.

The legacy pattern collection is basic format checking, not full validation of calendar dates, URLs or all email address forms. Prefer filter_var($email, FILTER_VALIDATE_EMAIL) for email and domain-specific parsers where appropriate. Static Model::is_email/is_int/is_float are boolean checks; they do not append errors to a fluent validator.

## Files

The legacy file/ext/maxSize helpers do not establish MIME authenticity or trusted upload origin. Use UploadPolicy through EncryptedStorage or PublicImageStorage for uploads. See [file upload](utilities/file-upload.md).

## Output and persistence

Validate before persistence; bind SQL values rather than concatenating them. Escape when rendering into HTML. purify is an HTML-escaping helper, not malware scanning, SQL protection or universal sanitization. Never escape/normalize passwords before password verification.

