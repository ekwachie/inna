# Views and Templates

Inna renders Twig templates from public/views. Templates use automatic HTML escaping, so print ordinary variables normally.

## Render a view

```php
return $this->render('orders/index', ['orders' => $orders]);
```

This loads public/views/orders/index.twig. The built-in site layout is layouts/site.twig; the older layouts/layout.twig remains for existing pages.

## Template basics

```twig
{% extends 'layouts/site.twig' %}
{% block title %}Orders{% endblock %}
{% block content %}
<h1>Orders</h1>
<ul>
{% for order in orders %}
    <li>{{ order.name }}</li>
{% else %}
    <li>No orders yet.</li>
{% endfor %}
</ul>
{% endblock %}
```

Use include for shared fragments. Never apply raw to user input. The documentation page uses raw only after its Markdown renderer strips raw HTML and unsafe links.

## Globals and assets

The router supplies base_url and current_path. Your controller can supply static when using older templates. New site assets use root-relative /public/static URLs. Twig includes the string-extra extension and app/ext/AppExtension.php, which defines the price filter and area function.

## Theme and cache

The site layout supports light/dark themes through data-theme on html. The toggle saves in localStorage under inna-theme and otherwise follows the system preference. The choice persists between the landing page and documentation.

Compiled templates are cached in public/runtime with auto_reload enabled. Give the PHP process write access there; do not put secrets in the cache directory.

