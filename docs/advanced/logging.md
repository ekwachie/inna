# Activity & Audit Logging

Inna provides comprehensive logging capabilities for tracking application activity and auditing system events.

## Activity Logging

Activity logs automatically track HTTP requests. This is configured in `config/config.php`:

```php
ActivityLogService::logRequest($app->request);
```

### Log Location

Activity logs are stored in:
- `log/activity/log_[date].log`

### What's Logged

- Request method (GET, POST, etc.)
- Request URL
- IP address
- User agent
- Timestamp

## Audit Logging

Audit logs track important system events and user actions.

### Using AuditLogService

Check `app/Core/Utils/AuditLogService.php` for available methods:

```php
use app\Core\Utils\AuditLogService;

// Log user action
AuditLogService::log([
    'action' => 'user_login',
    'user_id' => $userId,
    'ip' => $_SERVER['REMOTE_ADDR'],
    'details' => 'User logged in successfully'
]);

// Log data change
AuditLogService::log([
    'action' => 'user_update',
    'user_id' => $userId,
    'target_id' => $targetUserId,
    'changes' => ['email' => 'old@example.com', 'new_email' => 'new@example.com']
]);
```

### Log Location

Audit logs are stored in:
- `log/audit/log_[date].log`

## Custom Logging

### Application-Specific Logs

```php
function logActivity($message, $data = [])
{
    $logFile = Application::$ROOT_DIR . '/log/activity/custom_' . date('Y-m-d') . '.log';
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'message' => $message,
        'data' => $data
    ];
    
    file_put_contents($logFile, json_encode($logEntry) . PHP_EOL, FILE_APPEND);
}

// Usage
logActivity('User created post', ['post_id' => 123, 'user_id' => 456]);
```

## Log Rotation

### Manual Rotation

```bash
# Archive old logs
tar -czf logs_archive_$(date +%Y%m).tar.gz log/
rm log/activity/*.log
rm log/audit/*.log
```

### Automated Rotation

Set up a cron job for log rotation:

```bash
# Add to crontab
0 0 1 * * /path/to/rotate_logs.sh
```

## Best Practices

1. **Log important events**: Log user actions, data changes, and system events
2. **Don't log sensitive data**: Never log passwords, tokens, or sensitive information
3. **Use structured logging**: Use JSON or structured format for easier parsing
4. **Rotate logs**: Implement log rotation to manage disk space
5. **Monitor logs**: Regularly monitor logs for security and performance issues
6. **Set retention**: Define how long to keep logs

## Log Analysis

### View Recent Activity

```bash
tail -f log/activity/log_*.log
```

### Search Logs

```bash
grep "user_login" log/audit/log_*.log
```

### Count Events

```bash
grep -c "user_login" log/audit/log_*.log
```

## Security Considerations

1. **Protect log files**: Set proper file permissions (644)
2. **Don't expose logs**: Never expose log files via web server
3. **Encrypt sensitive logs**: Consider encrypting logs containing sensitive data
4. **Access control**: Restrict access to log files
5. **Regular review**: Regularly review logs for security issues

## Next Steps

- [Logger](/utilities/logger.md) - Learn about general logging
- [Error Handling](error-handling.md) - Learn about error logging

