# Logger

Inna includes logging utilities for application and activity logging.

## Activity Logging

Activity logs are automatically created for each request. Check `app/Core/Utils/ActivityLogService.php`.

### Log Location

Activity logs are stored in:
- `log/activity/log_[date].log`

## Audit Logging

Audit logs track important system events. Check `app/Core/Utils/AuditLogService.php`.

### Log Location

Audit logs are stored in:
- `log/audit/log_[date].log`

## Error Logging

PHP errors are automatically logged to:
- `log/errors/error_log_[date].log`

Application errors are logged to:
- `log/app_error_log_[date].log`

## Custom Logging

### Using PHP Error Log

```php
error_log("Custom log message", 3, Application::$ROOT_DIR . "/log/custom.log");
```

### Using Logger Utility

Check `app/Core/Utils/Logger.php` for available logging methods:

```php
use app\Core\Utils\Logger;

Logger::info('Information message');
Logger::error('Error message');
Logger::warning('Warning message');
```

## Best Practices

1. **Log important events**: Log user actions, errors, and important events
2. **Don't log sensitive data**: Never log passwords or sensitive information
3. **Rotate logs**: Implement log rotation to manage disk space
4. **Monitor logs**: Regularly monitor logs for issues
5. **Use appropriate levels**: Use appropriate log levels (info, error, warning)

## Next Steps

- [Error Handling](/advanced/error-handling.md) - Learn about error handling

