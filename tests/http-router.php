<?php
require dirname(__DIR__) . '/vendor/autoload.php';

use app\Core\Application;
use app\Core\ApiException;
use app\Core\ApiResponse;
use app\Services\Storage\EncryptedStorage;

$app = new Application(dirname(__DIR__), ['db' => ['dsn' => 'invalid:unused']]);
$storage = static fn() => new EncryptedStorage(getenv('INNA_TEST_STORAGE'), ['test' => getenv('INNA_TEST_KEY')], 'test');
$app->router->get('/', static fn() => 'No database connection required.');
$app->router->get('/api/item/{id}', static fn($r) => ApiResponse::success(['id' => $r->getRouteParam('id')]));
$app->router->patch('/api/item/{id}', static fn($r) => ApiResponse::success($r->getJsonBody()));
$app->router->put('/api/item/{id}', static fn($r) => ApiResponse::success($r->getJsonBody()));
$app->router->delete('/api/item/{id}', static fn() => ApiResponse::success(['deleted' => true]));
$app->router->post('/api/upload', static fn() => ApiResponse::success($storage()->upload($_FILES['file'] ?? []), 201));
$app->router->post('/api/image', static fn() => ApiResponse::success((new \app\Services\Storage\PublicImageStorage(getenv('INNA_TEST_STORAGE') . '/images', '/images'))->upload($_FILES['file'] ?? []), 201));
$app->router->get('/api/download/{id}', static function($request, $response) use ($storage) {
    if ($request->getHeader('Authorization') !== 'Bearer test-owner') throw new ApiException('FORBIDDEN', 'Not authorized.', 403);
    $file = $storage()->read($request->getRouteParam('id'));
    $response->download($file['bytes'], $file['metadata']['original_name'], $file['metadata']['mime_type']);
});
$app->run();
