# Changelog

## 2.0.0 - 2026-10-22

### Added

- Private authenticated encrypted uploads with dedicated keys, safe storage identifiers, metadata integrity, restrictive permissions and explicit key rotation.
- Public JPEG/PNG/WebP storage and a separate GD image optimizer.
- Payperlez payment, SMS and OTP clients with explicit idempotency, bounded TLS-verified transport and structured upstream failures.
- Webhook signature verification with timestamp, business and environment checks.
- Strict bounded JSON input, cached raw request bodies and middleware request attributes.
- Standard API envelopes and error statuses; PUT/PATCH/DELETE, HEAD and OPTIONS routing.
- Transaction callback helpers and lazy database construction using PHP 8.4.
- Landing page, searchable documentation and persistent system-aware light/dark themes.
- Framework, real HTTP/multipart and documentation regression tests.

### Changed

- PHP minimum is 8.4. JWT uses firebase/php-jwt 7 and requires a strong explicit key and valid standard claims.
- Admin middleware enforces an admin role; middleware action lists are honored.
- Migration execution stops on failure and records each successful migration immediately. Incompatible users tables are preserved.
- Optional GeoIP availability no longer blocks bootstrap. Mail configuration comes from the environment.
- Activity logs use request IDs and session fingerprints rather than raw session IDs.

### Removed

- Legacy Paystack and MSM Pusher SMS utilities.
- The old filename/destination FileUpload constructor and uploader/move/compressImage API.

Read docs/advanced/upgrading.md before adopting this release. Existing files, provider histories and schemas are not silently migrated.
