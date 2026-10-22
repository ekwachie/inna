# Encrypted Uploads

Use EncryptedStorage for documents that must remain private. It validates the upload, encrypts its bytes and metadata using Sodium secretbox, and writes an opaque .enc object outside the application and public web roots.

## Configure

Create an absolute directory owned by the PHP process with permissions 0700. Set STORAGE_PATH, STORAGE_KEY_ID=v1 and STORAGE_KEY in .env. Generate STORAGE_KEY with openssl rand -hex 32. It must be different from JWT_SECRET and any MFA key.

```php
use app\Services\Storage\EncryptedStorage;

$storage = EncryptedStorage::fromEnvironment();
```

An invalid directory or key fails before a write. Files use 0600 permissions, exclusive creation, random 64-character hexadecimal identifiers and authenticated encrypted metadata. The original filename is never used as the storage path.

## Upload

After authenticating the actor and checking permission to upload:

```php
$metadata = $storage->upload($_FILES['file'] ?? []);
```

The default policy allows non-empty PDF, JPEG and PNG files up to 2 MB. It checks PHP upload errors, is_uploaded_file, the actual byte count, detected MIME type and extension agreement. Images must also have valid dimensions no larger than 20 megapixels.

The result contains id, original_name, mime_type, size and sha256. Persist it in your application's file record alongside owner/resource IDs. The id is a storage identifier, not a public URL or authorization token. Keep identifiers and checksums out of public listings unless your application needs them.

## Authorized download

Look up the application's file record, authorize its owner/resource, then read its storage ID:

```php
$object = $storage->read($record['storage_id']);
$response->download(
    $object['bytes'],
    $object['metadata']['original_name'],
    $object['metadata']['mime_type']
);
```

read returns metadata, bytes and key_id. It authenticates the ciphertext, checks the embedded object ID and verifies the size/checksum. Corrupt, swapped, missing or symlinked objects fail rather than return partial content.

Your application must return an appropriate error and record access events. Inna does not automatically create file database tables or download routes.

## Application-generated files

write(bytes, filename) validates and stores generated content without pretending it came from an HTTP upload. Use upload() for multipart requests so uploaded-file checks cannot be bypassed.

To customize validation, construct the service explicitly:

```php
use app\Services\Storage\UploadPolicy;

$storage = new EncryptedStorage(
    '/srv/my-app-private/documents',
    ['v1' => $_ENV['STORAGE_KEY']],
    'v1',
    new UploadPolicy(5242880, ['application/pdf' => ['pdf']])
);
```

This implementation buffers bounded objects in memory. It is not a streaming service for multi-gigabyte uploads. MIME validation is not malware scanning; add scanning/quarantine when your product needs it.

## Delete and coordinate metadata

remove(id) deletes a stored object and tolerates an already-missing regular file. Authorize before calling it. Commit application metadata deletion first, then remove the file; record failures for cleanup.

For uploads, write the object and save its metadata. Remove the object on a known database rollback, but retain it for review when a commit outcome is uncertain. Database transactions cannot atomically commit filesystem changes. Avoid automated orphan deletion without a consistent metadata snapshot.

See [public images](storage/public-images.md) and [storage operations](storage/operations.md).
