# Logging

## Structured entries

```php
use app\Core\Utils\Logger;

Logger::write('orders', [
    'event' => 'order_created',
    'order_id' => $orderId,
]);
```

Logger writes one JSON line containing ts, channel and data. Channels allow letters, digits, underscore and hyphen. Invalid channel names are ignored. Writes append under a file lock.

## Destinations

Configure APP_LOG_DIR to a private writable directory. The legacy fallback order includes project/log, a sibling logs directory and a temporary inna-log directory. Each channel has a subdirectory and daily log file. Logger tries writable candidates and reports unavailability through PHP error_log once per channel.

Use an explicit private APP_LOG_DIR in production, ship logs to your monitoring system and apply retention/rotation. Do not rely on shared temporary fallback storage for production audit retention.

## Request activity

ActivityLogService records request metadata with a server-generated request ID, actor details and a session fingerprint rather than the session ID itself. GeoIP is optional; logging failure must not block the application.

Keep request URLs, referrers, usernames and email addresses in mind when setting retention and access controls. Do not add raw request bodies, API keys, authorization headers, OTPs or private file bytes to log payloads.

See [application logging](advanced/logging.md) for audit events and operational correlation.

