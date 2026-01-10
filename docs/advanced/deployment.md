# Deployment

This guide covers deploying your Inna application to production.

## Pre-Deployment Checklist

- [ ] Set `APP_ENV=production` in `.env`
- [ ] Generate strong `JWT_SECRET`
- [ ] Configure database credentials
- [ ] Set up error logging
- [ ] Enable HTTPS
- [ ] Configure web server
- [ ] Set proper file permissions
- [ ] Run migrations
- [ ] Clear Twig cache

## Environment Configuration

### Production `.env`

```env
APP_ENV=production
DOMAIN=yourdomain.com

DB_DSN=mysql:host=localhost;dbname=production_db
DB_USER=production_user
DB_PASSWORD=strong_password

JWT_SECRET=your_production_secret_key
JWT_ISSUER=yourdomain_api
JWT_TTL=3600
```

## Web Server Configuration

### Apache

Ensure `.htaccess` is configured:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>
```

### Nginx

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/inna/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## File Permissions

Set proper permissions:

```bash
# Directories
find . -type d -exec chmod 755 {} \;

# Files
find . -type f -exec chmod 644 {} \;

# Writable directories
chmod -R 775 log/
chmod -R 775 public/runtime/
```

## Database Setup

### Run Migrations

```bash
./migrations update
```

### Backup Database

Always backup before deployment:

```bash
mysqldump -u user -p database_name > backup.sql
```

## Security Checklist

- [ ] Use HTTPS
- [ ] Strong database passwords
- [ ] Secure JWT secret
- [ ] Disable error display
- [ ] Set secure session cookies
- [ ] Configure security headers
- [ ] Restrict file permissions
- [ ] Use environment variables

## Performance Optimization

### Enable OpCache

In `php.ini`:

```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
```

### Clear Cache

Clear Twig cache:

```bash
rm -rf public/runtime/*
```

## Monitoring

### Error Monitoring

Monitor error logs:
- `log/errors/error_log_*.log`
- `log/app_error_log_*.log`

### Activity Logs

Monitor activity:
- `log/activity/log_*.log`

## SSL/HTTPS

### Let's Encrypt

```bash
sudo certbot --nginx -d yourdomain.com
```

### Force HTTPS

In your application, ensure HTTPS is used in production (automatically handled by the framework).

## Backup Strategy

### Database Backups

Set up automated database backups:

```bash
# Daily backup script
mysqldump -u user -p database_name > /backups/db_$(date +%Y%m%d).sql
```

### File Backups

Backup important files:

```bash
tar -czf backup_$(date +%Y%m%d).tar.gz app/ config/ migration/
```

## Troubleshooting

### Check Logs

```bash
tail -f log/errors/error_log_*.log
tail -f log/app_error_log_*.log
```

### Check Permissions

```bash
ls -la
```

### Test Database Connection

```php
// Test script
$db = new Database($config['db']);
echo "Connected!";
```

## Next Steps

- [Configuration](/getting-started/configuration.md) - Review configuration
- [Security](/security/authentication.md) - Review security settings

