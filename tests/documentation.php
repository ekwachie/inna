<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use app\Core\Utils\Documentation;

function check(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$documentation = new Documentation();
$groups = $documentation->catalogue();
check($groups[0]['pages'][0]['path'] === 'getting-started/quickstart', 'Quickstart must lead navigation.');
$count = 0;
foreach ($groups as $group) {
    foreach ($group['pages'] as $page) {
        $rendered = $documentation->page($page['path']);
        $source = file_get_contents(dirname(__DIR__) . '/docs/' . $page['path'] . '.md');
        preg_match_all('/```php\s*\n(.*?)```/s', $source, $examples);
        foreach ($examples[1] as $example) {
            token_get_all(str_starts_with(ltrim($example), '<?php') ? $example : '<?php ' . $example, TOKEN_PARSE);
        }
        check($rendered !== null && str_contains($rendered['content'], '<h1'), 'Guide did not render: ' . $page['path']);
        preg_match_all('~href="(/documentation/[^"#]+)~', $rendered['content'], $links);
        foreach ($links[1] as $link) {
            check($documentation->page(substr($link, strlen('/documentation/'))) !== null, 'Broken guide link: ' . $link);
        }
        $count++;
    }
}
check($documentation->page('../README') === null, 'Traversal must be rejected.');
check($documentation->page('getting-started/missing') === null, 'Missing guides must be rejected.');
check($documentation->page('README') !== null, 'Legacy overview must remain available.');
$quickstart = $documentation->page('getting-started/quickstart');
check(str_contains($quickstart['content'], 'href="/documentation/the-basics/routing"'), 'Markdown links must resolve to framework routes.');
check(count($quickstart['toc']) === 4, 'Quickstart contents must contain its four sections.');

$fixture = sys_get_temp_dir() . '/inna-docs-' . bin2hex(random_bytes(8));
mkdir($fixture);
mkdir($fixture . '/testing');
$markdown = <<<'MD'
# Renderer test

## Same heading

```php
# This is code, not a heading
$value = '**literal** [link](file.md)';
```

## Same heading

- Parent
  - Child

| Key | Value |
| --- | --- |
| a | b |

<script>alert('unsafe')</script>

[Unsafe](javascript:alert%281%29)
MD;
try {
    file_put_contents($fixture . '/testing/renderer.md', $markdown);
    $page = (new Documentation($fixture))->page('testing/renderer');
    check(str_contains($page['content'], '**literal** [link](file.md)'), 'Code examples must remain literal.');
    check(!str_contains($page['content'], '<script'), 'Raw HTML must be stripped.');
    check(!str_contains($page['content'], 'href="javascript:'), 'Unsafe links must be rejected.');
    check(str_contains($page['content'], '<table>'), 'Markdown tables must render.');
    check(substr_count($page['content'], '<ul>') === 2, 'Nested lists must remain nested.');
    check($page['toc'][0]['id'] === 'same-heading' && $page['toc'][1]['id'] === 'same-heading-2', 'Heading IDs must be unique.');
} finally {
    unlink($fixture . '/testing/renderer.md');
    rmdir($fixture . '/testing');
    rmdir($fixture);
}

echo "Documentation checks passed; {$count} guides rendered.\n";

if (isset($argv[1])) {
    $base = rtrim($argv[1], '/');
    foreach (['/' => 200, '/documentation' => 200, '/docs' => 200,
        '/documentation/getting-started/quickstart' => 200,
        '/documentation/the-basics/routing' => 200,
        '/documentation/README' => 200,
        '/documentation/getting-started/missing' => 404,
        '/public/static/css/site.css' => 200,
        '/public/static/js/site.js' => 200] as $path => $status) {
        $body = file_get_contents($base . $path, false, stream_context_create(['http' => ['ignore_errors' => true]]));
        check($body !== false && str_contains($http_response_header[0], (string) $status), 'Unexpected HTTP response: ' . $path);
        if ($path === '/') {
            check(str_contains($body, 'A little framework.'), 'Homepage must show the landing page.');
            check(!str_contains($body, 'built with Inna'), 'Homepage must not disclose product implementation.');
        }
        if ($path === '/documentation') {
            check(str_contains($body, '<h1 id="build-your-first-inna-app">'), 'Docs must render without JavaScript.');
            check(!preg_match('~(?:href|src)=["\'][^"\']*design-preview~', $body), 'Integrated pages must not depend on preview files.');
        }
    }
    echo "HTTP route checks passed.\n";
}
