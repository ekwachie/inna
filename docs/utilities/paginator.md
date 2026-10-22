# Pagination

The existing Paginator helper computes SQL offsets and optional HTML links. It does not execute your query or count rows automatically.

## Use offsets

```php
use app\Core\Utils\Paginator;

$paginator = new Paginator(20, 'page');
$paginator->set_total($totalRows);
$limits = $paginator->get_limit_keys();
$rows = $model->select(
    'SELECT id, name FROM projects ORDER BY id LIMIT :limit OFFSET :offset',
    ['limit' => $limits['limit'], 'offset' => $limits['offset']]
);
```

Choose a positive integer page size in application code. The helper reads the named page query parameter and clamps invalid negative/zero pages to 1. get_start returns the offset, get_limit returns MySQL-style LIMIT SQL, and get_limit_keys returns integer offset/limit values.

## HTML links

page_links(path = '?', extra = null) returns legacy HTML markup. Only supply trusted URL fragments and render its output deliberately; it is not an escaping or URL-building service. New APIs should return structured pagination instead of HTML.

## API history

Payperlez history uses limit/offset/has_more in its own API response. Pass query parameters to the Payperlez client and follow that contract rather than using this local SQL helper to paginate remote results.

