# Storage Operations

## Backups

Back up application metadata, encrypted files and their key ring together using a coordinated snapshot or a maintenance window. A database backup alone does not contain uploaded files. A storage backup without its encryption keys cannot be decrypted.

Restrict key access, keep recovery copies outside the deployment checkout and verify a restore into an isolated environment. Do not remove private storage during deployments.

## Key rotation

The environment factory loads a single key. For rotation, construct a service with both old and new keys and select the new key for writes:

```php
use app\Services\Storage\EncryptedStorage;

$storage = new EncryptedStorage('/srv/my-app-private/documents', [
    'v1' => $oldKeyHex,
    'v2' => $newKeyHex,
], 'v2');
$storage->rotate($storageId);
```

rotate authenticates the existing object, writes a new encrypted temporary object and renames it over the old one while preserving its ID. The new key ID is stored in the versioned envelope.

Coordinate rotation with application writes/deletes. This API does not implement a distributed lock or automatic background migration. Walk identifiers from your application metadata, record progress, verify a read afterward, and resume failed batches deliberately. Keep old keys until every active object and retained backup that needs them has an appropriate recovery plan.

## Failure handling

Read/write failures throw RuntimeException with a generic message. Missing old keys, tampered content, wrong object IDs, invalid private roots and unreadable files fail closed. Do not loosen permissions or generate a replacement key to repair a failed read.

Failed writes remove known partial output. A filesystem/database commit ambiguity can leave private orphans; review them against application metadata before deletion. Rotation uses an atomic rename on the same filesystem but is not a coordinated database/filesystem transaction.

## Limits and temporary data

Validation reads only the configured maximum plus one byte. PHP initially receives multipart files in its configured upload temporary directory; encryption protects the persistent storage object, not PHP's initial temporary upload. Protect the host and temporary directory, and set upload_max_filesize and post_max_size appropriately.

The private root is explicitly configured and trusted. Keep its parents controlled by the service account/administrator so another local account cannot replace directories or race file operations.
