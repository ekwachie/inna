# Directory Structure

Understanding the directory structure of an Inna application will help you navigate and organize your code effectively.

## Root Directory

The root directory contains the following structure:

```
inna/
├── app/                    # Application code
├── config/                 # Configuration files
├── docs/                   # Documentation
├── log/                    # Log files
├── migration/              # Database migrations
├── public/                 # Public web root
├── vendor/                 # Composer dependencies
├── .env                    # Environment variables (not in git)
├── env.example             # Environment template
├── composer.json           # Composer dependencies
├── index.php               # Application entry point
└── README.md               # Project readme
```

**Key Directories:**
- **app/** - Contains all application code (controllers, models, core classes)
- **config/** - Configuration files and data
- **docs/** - Documentation files
- **public/** - Web server document root (static assets, views)
- **vendor/** - Composer dependencies
- **log/** - Application log files
- **migration/** - Database migration files

## The `app` Directory

The `app` directory contains the core application code:

```
app/
├── Controllers/            # Application controllers
│   ├── HomeController.php
│   ├── AuthController.php
│   ├── ApiController.php
│   └── AjaxController.php
├── Core/                  # Framework core classes
│   ├── Application.php    # Main application class
│   ├── Controller.php     # Base controller
│   ├── Router.php         # Routing system
│   ├── Request.php        # HTTP request handling
│   ├── Response.php       # HTTP response handling
│   ├── Database.php       # Database connection
│   ├── DbModel.php        # Database model base
│   ├── Model.php          # Validation model
│   ├── DApiController.php # API controller base
│   ├── form/              # Form helpers
│   ├── Middlewares/       # Middleware classes
│   └── Utils/             # Utility classes
├── Models/                # Eloquent-style models
│   └── User.php
├── ext/                   # Extensions
│   ├── AppExtension.php   # Twig extensions
│   └── AppForm.php        # Form extensions
└── templates/             # Email templates
    └── email/
```

## The `config` Directory

Configuration files and data:

```
config/
├── config.php             # Main configuration file
├── GeoLite2-Country.mmdb  # GeoIP database
└── GeoLite2-ASN.mmdb      # ASN database
```

## The `public` Directory

The `public` directory is the document root for your web server:

```
public/
├── static/                # Static assets
│   ├── css/              # Stylesheets
│   └── js/               # JavaScript files
├── views/                 # Twig templates
│   ├── layouts/          # Layout templates
│   ├── partials/         # Partial templates
│   └── *.twig            # View templates
└── runtime/              # Twig cache
```

## The `migration` Directory

Database migration files:

```
migration/
├── m01012024_000_create_inna_database.php
├── m01012024_001_create_users_table.php
└── m01012024_002_create_otp_verifications_table.php
```

## The `log` Directory

Application logs:

```
log/
├── activity/             # Activity logs
├── audit/                # Audit logs
└── errors/               # Error logs
```

## Key Files

### `index.php`

The entry point of your application. This file:
- Loads Composer autoloader
- Loads configuration
- Defines routes
- Runs the application

### `composer.json`

Defines your project dependencies and autoloading rules.

### `.env`

Contains environment-specific configuration (not committed to git).

## Naming Conventions

### Controllers

- File: `PascalCaseController.php`
- Class: `PascalCaseController`
- Example: `HomeController.php` → `HomeController`

### Models

- File: `PascalCase.php`
- Class: `PascalCase`
- Example: `User.php` → `User`

### Views

- File: `kebab-case.twig`
- Example: `user-profile.twig`

### Migrations

- File: `m[date]_[description].php`
- Example: `m01012024_001_create_users_table.php`

## Best Practices

1. **Controllers**: Place all controllers in `app/Controllers/`
2. **Models**: Place models in `app/Models/`
3. **Views**: Place views in `public/views/`
4. **Utilities**: Place reusable utilities in `app/Core/Utils/`
5. **Middleware**: Place middleware in `app/Core/Middlewares/`

## Next Steps

- [Lifecycle](/getting-started/lifecycle.md) - Understand request lifecycle
- [Routing](/the-basics/routing.md) - Define your routes

