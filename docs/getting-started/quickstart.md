# Build your first Inna app

This guide targets Inna 2.0 and PHP 8.4+.

## Prepare the project

For an existing checkout:

```bash
composer install
cp env.example .env
php -v
```

Do not overwrite an existing configured .env. A new project can be created with:

```bash
composer create-project payperlez/inna my-app -s stable
cd my-app
```

Composer requires the target directory to be empty. The first-install script creates .env from env.example without overwriting an existing file.

## Start the local server

Run from the project root:

```bash
php -S 127.0.0.1:8080 -t . dev-router.php
```

Set DOMAIN=127.0.0.1:8080 in .env. Open http://127.0.0.1:8080 for the landing page or http://127.0.0.1:8080/documentation for these guides. This loopback server is for development only; the router serves static files only from public/static and public/uploads.

## Define your first route

In index.php, insert this before the final $app->run():

```php
$app->router->get('/hello/{name}', function ($request) {
    return 'Hello, ' . htmlspecialchars(
        $request->getRouteParam('name'),
        ENT_QUOTES,
        'UTF-8'
    );
});
```

Visit /hello/Ama. The response is Hello, Ama. You do not need a database for this route.

## Keep building

Learn [routing](the-basics/routing.md), move route behavior into [controllers](the-basics/controllers.md), render [views](the-basics/views.md), and configure a [database](database/basics.md) when your application needs persistence. Run composer test before changing framework behavior.
