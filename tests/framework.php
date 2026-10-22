<?php
require dirname(__DIR__) . '/vendor/autoload.php';

use app\Core\ApiException;
use app\Core\Application;
use app\Core\Middlewares\AdminMiddleware;
use app\Core\Utils\Jwt;
use app\Services\Payperlez\Client;
use app\Services\Payperlez\ServiceException;
use app\Services\Payperlez\Transport;
use app\Services\Payperlez\WebhookVerifier;
use app\Services\Storage\EncryptedStorage;
use app\Services\Storage\UploadPolicy;

function ensure(bool $value, string $message): void { if (!$value) throw new RuntimeException($message); }
function fails(callable $action, string $class): Throwable {
    try { $action(); } catch (Throwable $error) { ensure($error instanceof $class, 'Wrong error: ' . get_class($error) . ': ' . $error->getMessage()); return $error; }
    throw new RuntimeException('Expected failure: ' . $class);
}

$app = new Application(dirname(__DIR__), ['db' => ['dsn' => 'sqlite::memory:']]);
$app->router->get('/items/{id}', static fn($r) => $r->getRouteParam('id'));
$app->router->patch('/items/{id}', static fn($r) => $r->getRouteParam('id'));
$app->router->get('/literal.html', static fn() => 'literal');
$_SERVER['REQUEST_METHOD'] = 'GET'; $_SERVER['REQUEST_URI'] = '/items/a-b/';
ensure($app->router->resolve() === 'a-b', 'Named route and trailing slash failed.');
$_SERVER['REQUEST_METHOD'] = 'PATCH';
ensure($app->router->resolve() === 'a-b', 'PATCH route failed.');
$_SERVER['REQUEST_METHOD'] = 'POST';
ensure(fails(fn() => $app->router->resolve(), ApiException::class)->status === 405, 'Method mismatch must return 405.');
$_SERVER['REQUEST_METHOD'] = 'GET'; $_SERVER['REQUEST_URI'] = '/literalXhtml';
ensure(fails(fn() => $app->router->resolve(), ApiException::class)->status === 404, 'Static route regex characters must be literal.');
$_SESSION['user'] = ['id' => 1, 'role' => 'user'];
ensure(fails(fn() => (new AdminMiddleware())->execute(), ApiException::class)->status === 403, 'Non-admin session must be rejected.');
$_SESSION['user'] = [['id' => 1, 'role' => 'admin']]; (new AdminMiddleware())->execute();
$_SESSION['user'] = []; ensure(fails(fn() => (new AdminMiddleware())->execute(), ApiException::class)->status === 401, 'Empty user must be rejected.');

fails(fn() => new Jwt('change_this_secret'), RuntimeException::class);
$jwt = new Jwt(str_repeat('a', 64), 'test-issuer');
$token = $jwt->encode(['sub' => '1', 'roles' => ['admin']]);
ensure($jwt->decode($token)['sub'] === '1', 'JWT roundtrip failed.');
fails(fn() => (new Jwt(str_repeat('b', 64), 'test-issuer'))->decode($token), Throwable::class);
fails(fn() => (new Jwt(str_repeat('a', 64), 'other-issuer'))->decode($token), Throwable::class);
$expired = Firebase\JWT\JWT::encode(['iss' => 'test-issuer', 'iat' => time()-100, 'nbf' => time()-100, 'exp' => time()-1], str_repeat('a',64), 'HS256');
fails(fn() => $jwt->decode($expired), Throwable::class);

$root = sys_get_temp_dir() . '/inna-storage-' . bin2hex(random_bytes(8));
mkdir($root, 0700);
$keys = ['old' => bin2hex(random_bytes(32)), 'new' => bin2hex(random_bytes(32))];
$pdf = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF\n";
try {
    $storage = new EncryptedStorage($root, $keys, 'old');
    $meta = $storage->write($pdf, '../../document.pdf');
    ensure($meta['original_name'] === 'document.pdf', 'Original name must be normalized.');
    ensure($storage->read($meta['id'])['bytes'] === $pdf, 'Storage roundtrip failed.');
    ensure(!str_contains(file_get_contents($root . '/' . $meta['id']), '%PDF'), 'File must not contain plaintext.');
    ensure((fileperms($root . '/' . $meta['id']) & 0777) === 0600, 'Private file permissions failed.');
    fails(fn() => $storage->read('../document.pdf'), InvalidArgumentException::class);
    fails(fn() => $storage->upload(['error' => UPLOAD_ERR_OK, 'tmp_name' => __FILE__, 'name' => 'test.pdf']), ApiException::class);
    fails(fn() => $storage->write($pdf, 'document.jpg'), ApiException::class);
    fails(fn() => (new UploadPolicy(10))->validate($pdf, 'x.pdf'), ApiException::class);
    $other = $storage->write($pdf, 'other.pdf');
    copy($root . '/' . $meta['id'], $root . '/' . $other['id']);
    fails(fn() => $storage->read($other['id']), RuntimeException::class);
    $storage->remove($other['id']);
    (new EncryptedStorage($root, $keys, 'new'))->rotate($meta['id']);
    $newOnly = new EncryptedStorage($root, ['new' => $keys['new']], 'new');
    ensure($newOnly->read($meta['id'])['key_id'] === 'new', 'Key rotation failed.');
    $raw = file_get_contents($root . '/' . $meta['id']);
    $raw[strlen($raw)-1] = chr(ord($raw[strlen($raw)-1]) ^ 1);
    file_put_contents($root . '/' . $meta['id'], $raw);
    fails(fn() => $newOnly->read($meta['id']), RuntimeException::class);
    $link = str_repeat('a',64) . '.enc';
    symlink(__FILE__, $root . '/' . $link);
    fails(fn() => $storage->read($link), RuntimeException::class);
    unlink($root . '/' . $link);
    $storage->remove($meta['id']);
} finally { foreach (glob($root . '/*') as $file) unlink($file); rmdir($root); }

final class FakeTransport implements Transport
{
    public array $calls = [];
    public array $response = ['status' => 202, 'headers' => [], 'body' => '{"success":true,"data":{"status":"pending","reference":"ref-1"},"meta":{"request_id":"req_test"}}'];
    public bool $timeout = false;
    public function send(string $method, string $url, array $headers, ?string $body): array {
        $this->calls[] = compact('method', 'url', 'headers', 'body');
        if ($this->timeout) throw new ServiceException('TRANSPORT_ERROR', uncertain: true);
        return $this->response;
    }
}
$transport = new FakeTransport();
$client = new Client('ppz_test_example', transport: $transport);
$result = $client->payments()->collect(['amount' => '1000.01', 'network' => 'mtn'], 'order-0001');
ensure($result['data']['status'] === 'pending', 'Pending must remain pending.');
ensure(json_decode($transport->calls[0]['body'], true)['amount'] === '1000.01', 'Amount precision lost.');
ensure(in_array('Idempotency-Key: order-0001', $transport->calls[0]['headers'], true), 'Idempotency header missing.');
fails(fn() => $client->payments()->collect(['amount' => 1.23], 'order-0002'), InvalidArgumentException::class);
$client->sms()->send('sender-uuid', '0244000000', 'Hello', 'sms-0001');
ensure(str_ends_with($transport->calls[1]['url'], '/sms/messages'), 'SMS path mismatch.');
$client->sms()->otp('sender-uuid', '0244000000', 'login', 'otp-0001');
$client->sms()->verifyOtp('challenge-1', '001234');
ensure(json_decode($transport->calls[3]['body'], true)['code'] === '001234', 'OTP leading zeros lost.');
$contractCalls = [
    [fn() => $client->payments()->checkout(['amount' => '10.00'], 'checkout-0001'), 'POST', '/checkout/sessions'],
    [fn() => $client->payments()->checkoutStatus('session-1'), 'GET', '/checkout/sessions/session-1'],
    [fn() => $client->payments()->expireCheckout('session-1'), 'POST', '/checkout/sessions/session-1/expire'],
    [fn() => $client->payments()->disburse(['amount' => '10.00'], 'payout-0001'), 'POST', '/disbursements'],
    [fn() => $client->payments()->status('ref-1'), 'GET', '/status/ref-1'],
    [fn() => $client->payments()->collections(), 'GET', '/collections'],
    [fn() => $client->payments()->disbursements(), 'GET', '/disbursements'],
    [fn() => $client->payments()->balance(), 'GET', '/account-balance'],
    [fn() => $client->payments()->banks(), 'GET', '/banks'],
    [fn() => $client->payments()->accountName(['channel' => 'mobile_money']), 'POST', '/account-name'],
    [fn() => $client->sms()->bulk('sender-uuid', ['0244000000'], 'Hello', 'bulk-0001'), 'POST', '/sms/batches'],
    [fn() => $client->sms()->message('ref-1'), 'GET', '/sms/messages/ref-1'],
    [fn() => $client->sms()->batch('ref-1'), 'GET', '/sms/batches/ref-1'],
    [fn() => $client->sms()->messages(), 'GET', '/sms/messages'],
    [fn() => $client->sms()->senders(), 'GET', '/sms/sender-ids'],
    [fn() => $client->sms()->balance(), 'GET', '/sms/balance'],
];
foreach ($contractCalls as [$call, $method, $path]) {
    $call(); $last = $transport->calls[array_key_last($transport->calls)];
    ensure($last['method'] === $method && str_ends_with($last['url'], $path), 'Service endpoint mismatch: ' . $path);
}
$client->payments()->statusByClientReference('order:4821');
ensure(str_ends_with($transport->calls[array_key_last($transport->calls)]['url'], '/status?client_reference=order%3A4821'), 'Client-reference query encoding failed.');
$transport->timeout = true;
$before = count($transport->calls);
ensure(fails(fn() => $client->payments()->collect(['amount' => '1.00'], 'order-0003'), ServiceException::class)->uncertain, 'Timeout outcome must be uncertain.');
ensure(count($transport->calls) === $before + 1, 'Client must not retry automatically.');
$transport->timeout = false;
$transport->response = ['status' => 429, 'headers' => ['retry-after' => '60'], 'body' => '{"success":false,"error":{"code":"RATE_LIMITED"},"meta":{"request_id":"req_limit"}}'];
$error = fails(fn() => $client->sms()->balance(), ServiceException::class);
ensure($error->httpStatus === 429 && $error->retryAfter === 60 && $error->requestId === 'req_limit', 'Error metadata lost.');
$transport->response = ['status' => 502, 'headers' => [], 'body' => '<html>upstream error</html>'];
ensure(fails(fn() => $client->sms()->balance(), ServiceException::class)->errorCode === 'INVALID_RESPONSE', 'Malformed provider response must fail safely.');
fails(fn() => new Client('secret', 'http://example.com'), InvalidArgumentException::class);

$secret = 'whsec_' . str_repeat('a', 64);
$event = ['id' => 'event-1', 'type' => 'collection.succeeded', 'business_id' => 'business-1', 'environment' => 'test', 'data' => ['reference' => 'ref-1']];
$raw = json_encode($event);
$now = time();
$signature = 't=' . $now . ',v1=' . hash_hmac('sha256', $now . '.' . $raw, $secret);
$verifier = new WebhookVerifier([$secret], 'business-1', 'test');
ensure($verifier->verify($raw, $signature)['id'] === 'event-1', 'Webhook verification failed.');
fails(fn() => $verifier->verify($raw . ' ', $signature), UnexpectedValueException::class);
fails(fn() => $verifier->verify($raw, $signature, $now+301), UnexpectedValueException::class);
fails(fn() => (new WebhookVerifier([$secret], 'other-business', 'test'))->verify($raw, $signature), UnexpectedValueException::class);

$app->db->pdo->exec('CREATE TABLE examples (id INTEGER PRIMARY KEY, name TEXT)');
$app->db->transaction(fn($db) => $db->insert('examples', ['name' => 'saved']));
fails(fn() => $app->db->transaction(function($db) { $db->insert('examples', ['name' => 'rolled-back']); throw new RuntimeException('rollback'); }), RuntimeException::class);
ensure(count($app->db->select('SELECT * FROM examples')) === 1, 'Transaction rollback failed.');
$originalRoot = Application::$ROOT_DIR;
$migrationRoot = sys_get_temp_dir() . '/inna-migrations-' . bin2hex(random_bytes(8));
mkdir($migrationRoot, 0700); mkdir($migrationRoot . '/migration', 0700);
try {
    Application::$ROOT_DIR = $migrationRoot;
    file_put_contents($migrationRoot . '/migration/m20261022000001_first.php', '<?php namespace app\\migrations; class m20261022000001_first { public function up() { \\app\\Core\\Application::$app->db->pdo->exec("CREATE TABLE migrated_example (id INTEGER)"); } }');
    file_put_contents($migrationRoot . '/migration/m20261022000002_fail.php', '<?php namespace app\\migrations; class m20261022000002_fail { public function up() { throw new \\RuntimeException("expected migration failure"); } }');
    ob_start();
    try { fails(fn() => $app->db->applyMigrations(), RuntimeException::class); } finally { ob_end_clean(); }
    ensure(count($app->db->getAppliedMigrations()) === 1, 'A completed migration must be recorded before a later failure.');
    fails(fn() => $app->db->addMigration('unsafe;name', ['name' => 'TEXT']), InvalidArgumentException::class);
} finally {
    Application::$ROOT_DIR = $originalRoot;
    foreach (glob($migrationRoot . '/migration/*') as $file) unlink($file);
    rmdir($migrationRoot . '/migration'); rmdir($migrationRoot);
}
echo "Framework checks passed: routing, authorization, JWT, storage, Payperlez services, webhooks and transactions.\n";
