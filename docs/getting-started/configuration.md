# Configuration

Copy env.example to .env once. Inna loads it through phpdotenv in config/config.php. Never commit credentials. PHP error display is disabled in both environments; inspect protected logs during development.

## Application

| Variable | Meaning |
| --- | --- |
| APP_ENV | development or production |
| DOMAIN | Host and optional port, without a scheme or trailing slash |
| APP_NAME | Application name |
| APP_LOG_DIR | Preferred log directory, ideally outside the web root |
| TRUSTED_PROXY_IPS | Comma-separated exact proxy IP addresses trusted for client-IP forwarding |

BASE_URL, STATIC_URL and MEDIA_URL are defined during bootstrap. GEO_RDR is a GeoIP reader or null when its database is missing/unreadable. GeoIP failure does not prevent serving a page.

## Database

```dotenv
DB_DSN=mysql:host=127.0.0.1;port=3306;dbname=inna;charset=utf8mb4
DB_USER=inna_app
DB_PASSWORD=replace-with-your-password
```

The PDO connection initializes on first database use via PHP 8.4 lazy objects. Database errors still fail the operation that needs it.

## Authentication

```dotenv
JWT_SECRET=
JWT_ISSUER=inna_api
JWT_TTL=3600
```

Generate a secret with openssl rand -hex 32 and paste it into JWT_SECRET. The signing key must contain at least 32 bytes; default placeholder keys are rejected. Keep issuer stable for tokens you intend to accept.

## Storage and services

STORAGE_PATH is an absolute private directory outside the checkout and every public web root. STORAGE_KEY is a dedicated 64-character hexadecimal key and STORAGE_KEY_ID names it. New private directories use 0700.

PAYPERLEZ_API_KEY selects the test or live environment. PAYPERLEZ_BASE_URL defaults to https://api.payperlez.org/api/v1. API keys belong only on your server.

PAYPERLEZ_WEBHOOK_SECRET, PAYPERLEZ_BUSINESS_ID and PAYPERLEZ_ENVIRONMENT configure your application's webhook verifier. Do not use a payment API key as a signing secret.

## SMTP

SMTP_HOST, SMTP_PORT (587), SMTP_USERNAME, SMTP_PASSWORD, SMTP_FROM_ADDRESS and SMTP_FROM_NAME configure the mail helper. The helper uses STARTTLS. Blank SMTP configuration is harmless until you send mail.

## Operations

Set HTTPS correctly at the web server. Session Secure cookies follow the HTTPS server flag; configure a trusted reverse proxy to set that flag correctly. Never trust arbitrary forwarded headers. Back up private storage, its keys and application metadata together.

