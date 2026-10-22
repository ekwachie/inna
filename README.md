# Inna

A minimal PHP MVC framework by Payperlez. PHP **>=8.4** is required.

This is **Inna 2.0**. Read the upgrade guide before applying framework changes to an existing application.

## Start from this checkout

```bash
composer install
cp env.example .env
php -S 127.0.0.1:8080 -t . dev-router.php
```

Do not overwrite an existing .env. Set DOMAIN=127.0.0.1:8080. Open the landing page at http://127.0.0.1:8080 and developer guides at http://127.0.0.1:8080/documentation.

The homepage and documentation do not require a database connection, Payperlez credentials or private-storage keys. Configure those features when you use them.

## Developer guide

- [Quickstart](docs/getting-started/quickstart.md) and [configuration](docs/getting-started/configuration.md)
- [Routing](docs/the-basics/routing.md), [controllers](docs/the-basics/controllers.md) and [views](docs/the-basics/views.md)
- [Database](docs/database/basics.md) and [migrations](docs/database/migrations.md)
- [Authentication](docs/security/authentication.md) and [JWT](docs/security/jwt.md)
- [Private encrypted uploads](docs/storage/encrypted-uploads.md) and [public images](docs/storage/public-images.md)
- [Payperlez payments](docs/services/payments.md), [SMS/OTP](docs/services/sms.md) and [webhooks](docs/services/webhooks.md)
- [Deployment](docs/advanced/deployment.md), [testing](docs/advanced/testing.md) and [upgrading to 2.0](docs/advanced/upgrading.md)

## Verify

```bash
composer check-platform-reqs
composer validate --strict
composer test
php tests/http.php
```

The HTTP suite starts its own isolated localhost server. Tests use simulated Payperlez responses; no real payment or SMS is initiated.

## License

MIT. Maintained by the Payperlez team.
