# Application Logging and Audit

## Correlate requests

ApiResponse::requestId() provides the same server-generated identifier used by standard API responses and activity logs.

```php
\app\Core\Utils\Logger::write('payments', [
    'request_id' => \app\Core\ApiResponse::requestId(),
    'operation_id' => $operationId,
    'event' => 'reconciliation_scheduled',
]);
```

Log operation identifiers and safe state transitions. Do not log payment API credentials, webhook secrets, OTPs, raw documents or full provider payloads.

## Audit events

Controller::auditEvent(request, event, meta = [], status = 'success', resource = null) is a protected convenience helper. It infers session actor information and delegates to AuditEvent/AuditLogService. Audit logging is best-effort and does not provide a durable transactional audit table.

For actions that require guaranteed audit persistence, write an application audit/outbox record inside the same database transaction as the change. A filesystem log cannot replace transactional accounting.

## Retention and recovery

Use private APP_LOG_DIR, restricted access, retention limits and monitoring for failed writes. Audit log encryption uses existing utility behavior; do not assume it is the new authenticated file-storage format. Existing encrypted logs must retain their original keys/readers until migrated deliberately.

A service request ID belongs to the remote Payperlez call and is different from your local request ID. Store both when diagnosing a failed integration without storing its secret payload.

