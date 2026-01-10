# Pagination

Inna includes a `Paginator` utility for paginating database results.

## Basic Usage

### In Controller

```php
use app\Core\Utils\Paginator;
use app\Core\Application;

public function index(Request $request, Response $response)
{
    $db = Application::$app->db;
    $page = (int)($request->getBody()['page'] ?? 1);
    $perPage = 10;
    
    // Get total count
    $total = $db->select("SELECT COUNT(*) as total FROM posts")[0]['total'];
    
    // Calculate offset
    $offset = ($page - 1) * $perPage;
    
    // Get paginated results
    $posts = $db->select(
        "SELECT * FROM posts ORDER BY created_at DESC LIMIT :limit OFFSET :offset",
        [
            'limit' => $perPage,
            'offset' => $offset
        ]
    );
    
    // Create paginator
    $paginator = new Paginator($total, $perPage, $page);
    
    return $this->render('posts/index', [
        'posts' => $posts,
        'paginator' => $paginator
    ]);
}
```

## Using Paginator Class

Check `app/Core/Utils/Paginator.php` for available methods and usage.

## View Template

### Display Pagination Links

```twig
{% if paginator.hasPages() %}
    <nav>
        {% if paginator.hasPrevious() %}
            <a href="?page={{ paginator.previousPage() }}">Previous</a>
        {% endif %}
        
        {% for page in paginator.pages() %}
            {% if page == paginator.currentPage() %}
                <strong>{{ page }}</strong>
            {% else %}
                <a href="?page={{ page }}">{{ page }}</a>
            {% endif %}
        {% endfor %}
        
        {% if paginator.hasNext() %}
            <a href="?page={{ paginator.nextPage() }}">Next</a>
        {% endif %}
    </nav>
{% endif %}
```

## Custom Implementation

### Simple Pagination Helper

```php
function paginate($query, $params, $page, $perPage = 10)
{
    $db = Application::$app->db;
    
    // Get total
    $countQuery = "SELECT COUNT(*) as total FROM (" . $query . ") as count_query";
    $total = $db->select($countQuery, $params)[0]['total'];
    
    // Calculate pagination
    $totalPages = ceil($total / $perPage);
    $offset = ($page - 1) * $perPage;
    
    // Get results
    $results = $db->select(
        $query . " LIMIT :limit OFFSET :offset",
        array_merge($params, [
            'limit' => $perPage,
            'offset' => $offset
        ])
    );
    
    return [
        'data' => $results,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => $totalPages,
            'has_previous' => $page > 1,
            'has_next' => $page < $totalPages,
            'previous_page' => $page > 1 ? $page - 1 : null,
            'next_page' => $page < $totalPages ? $page + 1 : null
        ]
    ];
}
```

### Usage

```php
$result = paginate(
    "SELECT * FROM posts WHERE status = :status",
    ['status' => 'published'],
    $page,
    10
);

return $this->render('posts/index', [
    'posts' => $result['data'],
    'pagination' => $result['pagination']
]);
```

## Next Steps

- [Database](/database/basics.md) - Learn about database operations
- [Views](/the-basics/views.md) - Learn about views

