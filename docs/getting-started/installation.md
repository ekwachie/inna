# Installation

## Server Requirements

Before installing Inna, ensure your server meets the following requirements:

- **PHP** >= 8.0
- **Composer** (for dependency management)
- **MySQL** 5.7+ or **MariaDB** 10.2+ (or compatible database)
- **Web Server** (Apache with mod_rewrite or Nginx)
- **OpenSSL** PHP Extension
- **PDO** PHP Extension
- **Mbstring** PHP Extension
- **JSON** PHP Extension

## Installing Inna

### Via Composer (Recommended)

The easiest way to install Inna is using Composer's `create-project` command:

```bash
composer create-project payperlez/inna my-project -s stable
```

This will create a new `my-project` directory with a fresh Inna installation.

### Manual Installation

1. Clone the repository:
```bash
git clone https://github.com/payperlez/inna.git my-project
cd my-project
```

2. Install dependencies:
```bash
composer install
```

## Configuration

### Environment Setup

1. Copy the example environment file:
```bash
cp env.example .env
```

2. Generate an application key:
```bash
openssl rand -hex 32
```

3. Edit the `.env` file with your configuration:

```env
# Application
APP_ENV=development
DOMAIN=localhost

# Database
DB_DSN=mysql:host=localhost;dbname=inna_db
DB_USER=root
DB_PASSWORD=

# JWT (for API authentication)
JWT_SECRET=your_generated_secret_key_here
JWT_ISSUER=inna_api
JWT_TTL=3600
```

### Database Configuration

1. Create your database:
```sql
CREATE DATABASE inna_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Update your `.env` file with database credentials.

3. Run migrations:
```bash
./migrations update
```

## Web Server Configuration

### Apache Configuration

Ensure `mod_rewrite` is enabled and configure your `.htaccess` file:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>
```

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name localhost;
    root /path/to/inna/public;
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

## Verifying Installation

After installation, you can verify everything is working by:

1. Starting your web server
2. Visiting `http://localhost` in your browser
3. You should see the Inna welcome page

## Next Steps

- [Configuration](/getting-started/configuration.md) - Learn about framework configuration
- [Directory Structure](/getting-started/directory-structure.md) - Understand the project layout
- [Routing](the-basics/routing.md) - Define your first routes

