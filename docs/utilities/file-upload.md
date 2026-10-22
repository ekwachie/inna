# File Upload

Inna 2.0 replaces the old filename/destination uploader with explicit storage services.

## Private default

```php
use app\Core\Utils\FileUpload;
use app\Services\Storage\EncryptedStorage;

$uploads = new FileUpload(EncryptedStorage::fromEnvironment());
$metadata = $uploads->upload($_FILES['file'] ?? []);
```

FileUpload is a small facade over the private encrypted storage service. It has no uploader(), move() or compressImage() legacy methods. Its constructor accepts an EncryptedStorage instance, and upload(file) returns metadata or throws an exception.

Authorize before uploading and persist metadata using your application schema. The service does not create database rows or access-control routes.

## Choose the right service

Use [encrypted uploads](storage/encrypted-uploads.md) for private documents and [public images](storage/public-images.md) for intentionally public JPEG/PNG/WebP images. ImageProcessor handles optional optimization separately.

See [storage operations](storage/operations.md) for key rotation, backup and cleanup, and [upgrading](advanced/upgrading.md) before migrating existing uploads. Existing plaintext files are not encrypted automatically.

