# Configuration

## Environment Configuration

Inna uses environment variables for configuration, which are loaded from a `.env` file in your project root.

### Environment File

The `.env` file contains sensitive configuration values. Never commit this file to version control. Instead, commit the `env.example` file which serves as a template.

### Available Configuration Options

#### Application Configuration

```env
# Application Environment
# Options: development, production, prod, dev
APP_ENV=development

# Application Domain
DOMAIN=localhost
```

#### Database Configuration

```env
# Database DSN (Data Source Name)
# Format: mysql:host=HOST;dbname=DATABASE
DB_DSN=mysql:host=localhost;dbname=inna_db

# Database Username
DB_USER=root

# Database Password
DB_PASSWORD=your_password
```

#### JWT Configuration (for API)

```env
# JWT Secret Key (generate with: openssl rand -hex 32)
JWT_SECRET=your_long_random_secret_key_here

# JWT Issuer
JWT_ISSUER=inna_api

# JWT Time To Live (in seconds, default: 3600 = 1 hour)
JWT_TTL=3600
```

## Configuration File

The main configuration file is located at `config/config.php`. This file:

- Loads environment variables using Dotenv
- Sets up error logging
- Configures timezone
- Sets security headers
- Initializes the Application instance

### Security Headers

The framework automatically sets the following security headers:

- **Content-Security-Policy**: Prevents framing from unauthorized domains
- **X-Content-Type-Options**: Prevents MIME type sniffing
- **Strict-Transport-Security**: Enforces HTTPS (in production)
- **X-Powered-By**: Removed for security

### Error Logging

Errors are automatically logged to:
- `log/errors/error_log_[date].log` - PHP errors
- `log/app_error_log_[date].log` - Application errors

### Constants

The framework defines several useful constants:

```php
BASE_URL      // Base URL of your application
STATIC_URL    // URL for static assets
MEDIA_URL     // URL for media files
GEO_RDR       // GeoIP2 database reader instance
```

## Accessing Configuration

### Environment Variables

Access environment variables using PHP's `$_ENV` superglobal:

```php
$dbHost = $_ENV['DB_HOST'];
$appEnv = $_ENV['APP_ENV'];
```

### Application Instance

Access configuration through the Application instance:

```php
use app\Core\Application;

// Database connection
$db = Application::$app->db;

// Request object
$request = Application::$app->request;

// Response object
$response = Application::$app->response;

// Session
$session = Application::$app->session;
```

## Environment Detection

The framework automatically detects the environment:

1. Checks `APP_ENV` in `.env`
2. Falls back to HTTPS detection if not set
3. Sets appropriate security headers based on environment

### Production vs Development

**Production Mode:**
- Secure cookies enabled
- HTTPS enforced
- Error display disabled
- Error logging enabled

**Development Mode:**
- Error display enabled (for debugging)
- Less strict security (for local development)

## Custom Configuration

You can add custom configuration by:

1. Adding variables to your `.env` file
2. Accessing them via `$_ENV['YOUR_VAR']`
3. Or creating a custom config array in `config/config.php`

Example:

```php
// config/config.php
$config = [
    'db' => [...],
    'url' => $_ENV['DOMAIN'],
    'custom' => [
        'api_key' => $_ENV['API_KEY'],
        'cache_ttl' => 3600,
    ]
];
```

## Next Steps

- [Directory Structure](/getting-started/directory-structure.md) - Learn about project organization
- [Lifecycle](/getting-started/lifecycle.md) - Understand how requests are processed

