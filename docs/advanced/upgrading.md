# Upgrading to Inna 2.0

Inna 2.0 changes several APIs and requirements. Do not overwrite an existing application with create-project. Back up source, database, uploaded files and keys, then port the changes in a separate checkout.

## PHP and dependencies

PHP >=8.4 is required. Install required extensions and update locked dependencies deliberately. JWT uses firebase/php-jwt 7. New storage requires sodium/fileinfo; public image optimization additionally needs gd.

## Uploads

The six-argument FileUpload constructor and uploader/move/compressImage methods are removed. Replace them with new FileUpload($encryptedStorage)->upload($_FILES['file']) or call EncryptedStorage directly.

Private storage requires an absolute directory and a dedicated key. Existing plaintext files are not moved or encrypted automatically. Migrate from known application metadata, validate/read each source, write and verify its encrypted replacement, then update metadata. Retain originals until the migration and restore plan are verified. Never delete originals after an ambiguous database commit.

Intentionally public images use PublicImageStorage, not encrypted storage. The new encrypted object format is not directly compatible with Payperlez Biz's KYB envelope; migrate through authorized decrypt-and-reencrypt tooling if needed.

## Payments and SMS

app\\Core\\Utils\\Paystack and app\\Core\\Utils\\Sms are removed. There is no silent forwarding from Paystack/MSM Pusher to Payperlez.

Replace payment calls with Client::fromEnvironment()->payments(). Store operation IDs and idempotency keys. Handle status and redirects explicitly; the new service adds no local fee and writes no session state. Migrating historic Paystack transactions requires keeping the application's provider history and reconciliation strategy.

Replace SMS calls with client->sms()->send/bulk/otp using approved sender UUIDs and Payperlez API credentials. Return values are envelopes, not boolean delivery guarantees.

## Authentication and HTTP

JWT_SECRET must be strong/configured. Missing exp/iat/nbf/iss claims are rejected. Weak/default tokens stop working. Admin middleware enforces an admin role and respects action lists. New API endpoints use success/error envelopes and real HTTP statuses; update clients that expected HTTP 200 for every failure.

Use strict getJsonBody for APIs. New methods support PUT/PATCH/DELETE, HEAD and OPTIONS. GET/form getBody remains a legacy escaping helper. Existing code that relied on malformed JSON being silently accepted needs an explicit migration.

## Database and operations

Database construction is lazy. Migration runs stop on failure and record each successful migration immediately; inspect partial MySQL DDL before retrying. Existing incompatible users tables are not dropped automatically.

Session IDs regenerate on the bundled User login path and password hashes are not stored in sessions. Audit/activity logs use request IDs and session fingerprints. Review your own authentication flows and role-refresh policy.

## Documentation and site

The landing page is at / and docs at /documentation, with /docs as an alias. New guides include storage and Payperlez service reference pages. Both surfaces support persistent light/dark themes. Preserve your application's routes/templates when adopting these changes.
