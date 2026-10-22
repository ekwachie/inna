# Installation

## Requirements

Use PHP >=8.4 and Composer 2. Required extensions are curl, fileinfo, mbstring, openssl, PDO and sodium, plus the requirements of Composer dependencies. Add pdo_mysql for MySQL/MariaDB and gd for optional public-image optimization. SQLite is used by the framework test suite.

```bash
php -v
composer check-platform-reqs
```

## Install from source

Clone the repository into a new directory:

```bash
git clone https://github.com/ekwachie/inna.git my-app
cd my-app
composer install
cp env.example .env
```

To install the 2.0 release into a new, empty directory:

```bash
composer create-project payperlez/inna my-app '^2.0' -s stable
```

The first-install script copies env.example to .env if it does not already exist. Generate your JWT secret and configure optional features before using them.

## Configure optional features

The homepage does not require a database. Set a real DB_DSN, DB_USER and DB_PASSWORD before calling model methods or running migrations. Generate JWT_SECRET with openssl rand -hex 32 before issuing tokens. Generate a different key for encrypted storage.

See [configuration](getting-started/configuration.md) for all settings and [deployment](advanced/deployment.md) for web-server setup.

## Verify

```bash
composer test
php tests/http.php
php -S 127.0.0.1:8080 -t . dev-router.php
```

The HTTP test starts its own isolated loopback server and removes its temporary storage afterward. It does not call live payment or SMS services.

## Common problems

A non-empty create-project directory must be preserved or replaced deliberately; Composer will not merge projects. Missing PHP extensions are reported by check-platform-reqs. For database connection errors, verify the PDO driver and credentials. Do not run migrations on production simply to test connectivity.

