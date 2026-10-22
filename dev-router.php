<?php
if (PHP_SAPI !== 'cli-server') { http_response_code(404); exit; }
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');
$file = realpath(__DIR__ . $path);
foreach (['/public/static/', '/public/uploads/'] as $prefix) {
    if (str_starts_with($path, $prefix) && $file && str_starts_with($file, __DIR__ . $prefix) && is_file($file)
        && in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['css','js','map','png','jpg','jpeg','webp','gif','svg','ico','woff','woff2','ttf'], true)) return false;
}
require __DIR__ . '/index.php';
