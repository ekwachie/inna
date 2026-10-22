# Deployment

## Prepare a release

Require PHP >=8.4 and run composer check-platform-reqs, composer test and php tests/http.php. Install locked dependencies with composer install --no-dev --prefer-dist --optimize-autoloader. Do not update dependencies on production.

Set APP_ENV=production, the real DOMAIN and feature-specific credentials. PHP error display remains disabled. Use HTTPS and configure trusted proxies at the web server.

## Apache

Serve the repository entry point with DirectoryIndex index.php and mod_rewrite enabled. The supplied .htaccess routes requests through index.php and denies direct access to application/config/vendor/migration/log/test directories and sensitive root files. Allow the required override directives in the virtual host.

Public uploads must be static-only. Disable PHP execution there in the virtual host; do not rely on uploaded filenames to control execution. Test the actual host rules after deployment.

## Nginx

The project entry point is at the checkout root, not public/index.php. A starting configuration:

```nginx
server {
    listen 443 ssl;
    server_name app.example.com;
    root /srv/my-app;
    index index.php;

    location ~ /\.(?!well-known) { deny all; }
    location ~ ^/(app|config|vendor|migration|log|tests|design-preview)(/|$) { deny all; }
    location ~ ^/(composer\.(json|lock)|env\.example|dev-router\.php|migrations)$ { deny all; }
    location / { try_files $uri $uri/ /index.php?$query_string; }

    location = /index.php {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root/index.php;
        fastcgi_param HTTPS on;
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
    }

    location ~ \.php$ { deny all; }
}
```

Add your TLS certificate configuration, upload/body limits and access logging. Validate with nginx -t and use paths appropriate to your host. This template is a starting point, not an automatic provisioner.

## Persistent data

Keep .env, private storage, encryption keys, database data and protected logs outside disposable release assets or preserve them explicitly. Give PHP write access only where needed: Twig runtime, intentional public uploads and private storage/log directories. Never make the entire checkout writable.

Back up encrypted files and their keys with database metadata. Migrate data in a planned window; a database-only restore cannot recover private uploaded files.

## Payments and workers

Use a test key in non-production environments. Persist operations and idempotency keys, verify signed webhooks and run application-specific reconciliation/outbox workers. Inna's client does not run the Payperlez Biz provider/settlement workers inside your application.

## Health and rollout

Verify the landing page, documentation, a real database read, a private upload/download and your webhook receiver in an appropriate test environment. Keep a rollback plan that includes schema compatibility; reverting application files does not reverse database changes or key rotation.

