<?php
require dirname(__DIR__) . '/vendor/autoload.php';

function expect(bool $ok, string $message): void { if (!$ok) throw new RuntimeException($message); }
$root = sys_get_temp_dir() . '/inna-http-' . bin2hex(random_bytes(8));
mkdir($root, 0700);
$listener = stream_socket_server('tcp://127.0.0.1:0', $number, $message);
if (!$listener) throw new RuntimeException('Cannot reserve test port.');
$address = stream_socket_get_name($listener, false);
fclose($listener);
$env = array_merge(getenv(), ['INNA_TEST_STORAGE' => $root, 'INNA_TEST_KEY' => bin2hex(random_bytes(32))]);
$process = proc_open([PHP_BINARY, '-S', $address, __DIR__ . '/http-router.php'], [['pipe', 'r'], ['file', $root . '/server.log', 'a'], ['file', $root . '/server.log', 'a']], $pipes, dirname(__DIR__), $env);
if (!is_resource($process)) throw new RuntimeException('Cannot start HTTP test server.');
$request = static function(string $method, string $path, mixed $body = null, array $headers = []) use ($address): array {
    $curl = curl_init('http://' . $address . $path);
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_CUSTOMREQUEST => $method, CURLOPT_TIMEOUT => 5, CURLOPT_HTTPHEADER => $headers]);
    if ($body !== null) curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
    $content = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    curl_close($curl);
    return [$status, $content];
};
try {
    for ($attempt=0; $attempt<40; $attempt++) {
        $socket = @stream_socket_client('tcp://' . $address, $number, $message, .1);
        if ($socket) { fclose($socket); break; }
        usleep(50000);
    }
    expect($request('GET', '/')[1] === 'No database connection required.', 'Database must initialize lazily.');
    expect($request('GET', '/api/item/abc-def')[0] === 200, 'Hyphenated route failed.');
    expect($request('POST', '/api/item/abc')[0] === 405, 'Wrong method must produce 405.');
    expect($request('GET', '/api/missing')[0] === 404, 'Missing API route must produce 404.');
    expect($request('OPTIONS', '/api/item/abc')[0] === 204, 'OPTIONS failed.');
    foreach (['PATCH', 'PUT'] as $method) {
        $result = $request($method, '/api/item/abc', '{"name":"Example"}', ['Content-Type: application/json']);
        expect($result[0] === 200 && json_decode($result[1], true)['data']['name'] === 'Example', $method . ' JSON parsing failed.');
    }
    expect($request('PATCH', '/api/item/abc', '{broken', ['Content-Type: application/json'])[0] === 400, 'Malformed JSON must produce 400.');
    expect($request('PATCH', '/api/item/abc', '[]', ['Content-Type: application/json'])[0] === 400, 'JSON arrays must not be treated as objects.');
    expect($request('PATCH', '/api/item/abc', '{}', ['Content-Type: text/plain'])[0] === 415, 'Wrong content type must produce 415.');
    expect($request('PATCH', '/api/item/abc', str_repeat('x',65537), ['Content-Type: application/json'])[0] === 413, 'Body limit failed.');
    expect($request('DELETE', '/api/item/abc')[0] === 200, 'DELETE failed.');
    $pdf = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF\n";
    file_put_contents($root . '/upload.pdf', $pdf);
    $result = $request('POST', '/api/upload', ['file' => new CURLFile($root . '/upload.pdf', 'application/pdf', 'receipt.pdf')]);
    expect($result[0] === 201, 'Multipart upload failed: ' . $result[1]);
    $id = json_decode($result[1], true)['data']['id'];
    expect(is_file($root . '/' . $id), 'Encrypted object was not persisted.');
    expect($request('GET', '/api/download/' . $id)[0] === 403, 'Anonymous download must fail.');
    $result = $request('GET', '/api/download/' . $id, headers: ['Authorization: Bearer test-owner']);
    expect($result[0] === 200 && $result[1] === $pdf, 'Authorized encrypted download failed.');
    $result = $request('POST', '/api/upload', ['file' => new CURLFile($root . '/upload.pdf', 'image/jpeg', 'receipt.jpg')]);
    expect($result[0] === 415, 'Spoofed extension/client MIME must be rejected.');
    file_put_contents($root . '/large.pdf', $pdf . str_repeat(' ', 2097152));
    expect($request('POST', '/api/upload', ['file' => new CURLFile($root . '/large.pdf', 'application/pdf', 'large.pdf')])[0] === 413, 'Oversized multipart upload must fail.');
    if (extension_loaded('gd')) {
        $image = imagecreatetruecolor(2, 2);
        imagepng($image, $root . '/image.png');
        imagedestroy($image);
        $result = $request('POST', '/api/image', ['file' => new CURLFile($root . '/image.png', 'image/png', 'avatar.png')]);
        expect($result[0] === 201, 'Public image upload failed: ' . $result[1]);
        $metadata = json_decode($result[1], true)['data'];
        expect(str_starts_with($metadata['url'], '/images/') && is_file($root . '/images/' . $metadata['id']), 'Public image persistence failed.');
        expect((fileperms($root . '/images/' . $metadata['id']) & 0777) === 0644, 'Public image permissions failed.');
    }
    echo "HTTP checks passed: lazy bootstrap, routing, JSON, multipart uploads and authorized downloads.\n";
} finally {
    proc_terminate($process);
    proc_close($process);
    foreach ($pipes as $pipe) if (is_resource($pipe)) fclose($pipe);
    foreach (glob($root . '/images/*') as $file) unlink($file);
    if (is_dir($root . '/images')) rmdir($root . '/images');
    foreach (glob($root . '/*') as $file) unlink($file);
    rmdir($root);
}
