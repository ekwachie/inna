# Inna Framework Documentation

Welcome to the Inna Framework documentation! Inna is a minimal PHP framework that gives you full control over every aspect of your application.

## Table of Contents

### Getting Started
- [Installation](getting-started/installation.md)
- [Configuration](getting-started/configuration.md)
- [Directory Structure](getting-started/directory-structure.md)
- [Lifecycle](getting-started/lifecycle.md)

### The Basics
- [Routing](the-basics/routing.md)
- [Controllers](the-basics/controllers.md)
- [Views & Templates](the-basics/views.md)
- [Request & Response](the-basics/request-response.md)

### Database
- [Database Basics](database/basics.md)
- [Query Builder](database/query-builder.md)
- [Migrations](database/migrations.md)
- [Models](database/models.md)

### Security
- [Authentication](security/authentication.md)
- [Middleware](security/middleware.md)
- [Session Management](security/sessions.md)
- [JWT Authentication](security/jwt.md)

### API Development
- [API Basics](api/basics.md)
- [API Controllers](api/controllers.md)
- [API Authentication](api/authentication.md)
- [API Responses](api/responses.md)

### Utilities
- [Validation](utilities/validation.md)
- [File Upload](utilities/file-upload.md)
- [Mailer](utilities/mailer.md)
- [SMS](utilities/sms.md)
- [Logger](utilities/logger.md)
- [Paginator](utilities/paginator.md)

### Advanced Topics
- [Forms](advanced/forms.md)
- [Error Handling](advanced/error-handling.md)
- [Activity & Audit Logging](advanced/logging.md)
- [Deployment](advanced/deployment.md)

## Quick Start

```bash
# Install via Composer
composer create-project payperlez/inna my-project -s stable

# Generate application key
openssl rand -hex 32

# Configure your .env file
cp env.example .env

# Run migrations
./migrations update
```

## Framework Philosophy

Inna is designed to be:
- **Minimal**: Only what you need, nothing more
- **Flexible**: Full control over your application
- **Fast**: Lightweight and performant
- **Secure**: Built-in security features

## Requirements

- PHP >= 8.0
- Composer
- MySQL/MariaDB (or compatible database)
- Web server (Apache/Nginx)

## Support

For issues, questions, or contributions, please visit:
- GitHub: [ekwachie/inna](https://github.com/ekwachie/inna)
- Email: inna@payperlez.org

## License

The Inna framework is open-sourced software licensed under the [MIT license](LICENSE).

