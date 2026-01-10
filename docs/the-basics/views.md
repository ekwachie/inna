# Views & Templates

Inna uses [Twig](https://twig.symfony.com/) as its templating engine. Twig provides a clean, secure, and powerful way to create templates.

## Creating Views

Views are stored in `public/views/` and use the `.twig` extension.

### Basic View

```twig
{# public/views/home.twig #}
<!DOCTYPE html>
<html>
<head>
    <title>{{ title }}</title>
</head>
<body>
    <h1>{{ title }}</h1>
    <p>{{ description }}</p>
</body>
</html>
```

### Rendering a View

In your controller:

```php
public function home(Request $request, Response $response)
{
    return $this->render('home', [
        'title' => 'Welcome',
        'description' => 'Welcome to Inna Framework'
    ]);
}
```

## Layouts

Use layouts to avoid repeating common HTML structure.

### Base Layout

```twig
{# public/views/layouts/layout.twig #}
<!DOCTYPE html>
<html>
<head>
    <title>{% block title %}Default Title{% endblock %}</title>
    {% block styles %}{% endblock %}
</head>
<body>
    {% block header %}
        {% include 'partials/header.twig' %}
    {% endblock %}
    
    <main>
        {% block content %}{% endblock %}
    </main>
    
    {% block footer %}
        {% include 'partials/footer.twig' %}
    {% endblock %}
    
    {% block scripts %}{% endblock %}
</body>
</html>
```

### Extending Layouts

```twig
{# public/views/home.twig #}
{% extends 'layouts/layout.twig' %}

{% block title %}Home - My Site{% endblock %}

{% block content %}
    <h1>Welcome</h1>
    <p>This is the home page.</p>
{% endblock %}
```

## Partials

Include reusable partials:

```twig
{# public/views/partials/header.twig #}
<header>
    <nav>
        <a href="/">Home</a>
        <a href="/about">About</a>
        <a href="/contact">Contact</a>
    </nav>
</header>
```

```twig
{# In your view #}
{% include 'partials/header.twig' %}
```

## Variables

### Displaying Variables

```twig
{{ variable }}
{{ user.name }}
{{ items[0].title }}
```

### Escaping

Twig automatically escapes output for security:

```twig
{{ user_input }} {# Automatically escaped #}
{{ user_input|raw }} {# Unescaped (use with caution!) #}
```

## Control Structures

### If Statements

```twig
{% if user %}
    <p>Welcome, {{ user.name }}!</p>
{% else %}
    <p>Please log in.</p>
{% endif %}

{% if user.role == 'admin' %}
    <a href="/admin">Admin Panel</a>
{% endif %}
```

### Loops

```twig
{% for user in users %}
    <div>
        <h3>{{ user.name }}</h3>
        <p>{{ user.email }}</p>
    </div>
{% endfor %}

{% for key, value in items %}
    <p>{{ key }}: {{ value }}</p>
{% endfor %}
```

### Loop Variables

```twig
{% for user in users %}
    {% if loop.first %}
        <p>First user: {{ user.name }}</p>
    {% endif %}
    
    {% if loop.last %}
        <p>Last user: {{ user.name }}</p>
    {% endif %}
    
    <p>User {{ loop.index }} of {{ loop.length }}</p>
{% endfor %}
```

## Filters

Twig provides many useful filters:

```twig
{{ name|upper }}           {# Uppercase #}
{{ name|lower }}           {# Lowercase #}
{{ name|capitalize }}      {# Capitalize #}
{{ text|length }}          {# String length #}
{{ date|date('Y-m-d') }}   {# Format date #}
{{ text|default('N/A') }}  {# Default value #}
{{ text|trim }}            {# Trim whitespace #}
{{ array|join(', ') }}     {# Join array #}
```

## Functions

### URL Generation

```twig
{{ url('/about') }}
{{ static_url('css/style.css') }}
{{ media_url('images/logo.png') }}
```

### Constants

```twig
{{ BASE_URL }}
{{ STATIC_URL }}
{{ MEDIA_URL }}
```

## Custom Extensions

The framework includes custom Twig extensions. Check `app/ext/AppExtension.php` for available functions and filters.

## Forms

### Basic Form

```twig
<form action="/contact" method="POST">
    <input type="text" name="name" value="{{ old.name|default('') }}">
    <input type="email" name="email" value="{{ old.email|default('') }}">
    <textarea name="message">{{ old.message|default('') }}</textarea>
    <button type="submit">Send</button>
</form>
```

### CSRF Protection

For CSRF protection, you'll need to implement tokens in your forms and validate them in controllers.

## Error Handling

### 404 Page

Create `public/views/404.twig`:

```twig
{% extends 'layouts/layout.twig' %}

{% block content %}
    <h1>404 - Page Not Found</h1>
    <p>The page you're looking for doesn't exist.</p>
    <a href="/">Go Home</a>
{% endblock %}
```

## Best Practices

1. **Use layouts**: Avoid repeating HTML structure
2. **Extract partials**: Reuse common components
3. **Escape output**: Let Twig handle escaping automatically
4. **Keep logic minimal**: Move complex logic to controllers
5. **Organize views**: Group related views in subdirectories

## Example: Complete Page

```twig
{# public/views/users/show.twig #}
{% extends 'layouts/layout.twig' %}

{% block title %}{{ user.name }} - User Profile{% endblock %}

{% block content %}
    <div class="user-profile">
        <h1>{{ user.name }}</h1>
        <p>Email: {{ user.email }}</p>
        <p>Joined: {{ user.created_at|date('F j, Y') }}</p>
        
        {% if user.posts %}
            <h2>Posts</h2>
            <ul>
                {% for post in user.posts %}
                    <li>
                        <a href="/post/{{ post.id }}">{{ post.title }}</a>
                    </li>
                {% endfor %}
            </ul>
        {% else %}
            <p>No posts yet.</p>
        {% endif %}
    </div>
{% endblock %}
```

## Next Steps

- [Request & Response](request-response.md) - Learn about HTTP handling
- [Forms](/advanced/forms.md) - Learn about form handling

