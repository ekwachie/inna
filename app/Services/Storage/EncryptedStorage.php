<?php
namespace app\Services\Storage;

/** Bounded, authenticated private objects. The application owns access checks and metadata persistence. */
final class EncryptedStorage
{
    private string $root;
    private array $keys;

    public function __construct(string $directory, array $keys, private readonly string $activeKey, private readonly UploadPolicy $policy = new UploadPolicy())
    {
        if (!extension_loaded('sodium')) throw new \RuntimeException('Encrypted storage requires ext-sodium.');
        if (!$keys || !isset($keys[$activeKey])) throw new \InvalidArgumentException('An active storage key is required.');
        foreach ($keys as $id => $hex) {
            if (!is_string($id) || !preg_match('/^[a-zA-Z0-9_-]{1,32}$/D', $id) || !is_string($hex) || !preg_match('/^[a-f0-9]{64}$/Di', $hex)) {
                throw new \InvalidArgumentException('Storage keys require a safe key ID and 64 hexadecimal characters.');
            }
            $this->keys[$id] = hex2bin($hex);
        }
        if (!str_starts_with($directory, '/') || $directory === '/') throw new \InvalidArgumentException('Use an absolute private storage directory.');
        if (!is_dir($directory) && !@mkdir($directory, 0700, true) && !is_dir($directory)) $this->unavailable();
        $root = realpath($directory);
        if (!$root || $root === '/' || !is_writable($root)) $this->unavailable();
        foreach ([dirname(__DIR__, 3), $_SERVER['DOCUMENT_ROOT'] ?? '', $_SERVER['CONTEXT_DOCUMENT_ROOT'] ?? ''] as $public) {
            $public = $public !== '' ? realpath($public) : false;
            if ($public && ($root === $public || str_starts_with($root, $public . '/'))) throw new \InvalidArgumentException('Private storage must be outside application and web roots.');
        }
        if (preg_match('~/(?:public_html|htdocs)(?:/|$)~', $root) || (fileperms($root) & 0077)) throw new \InvalidArgumentException('Storage directory must be private (0700).');
        $this->root = $root;
    }

    public static function fromEnvironment(): self
    {
        return new self($_ENV['STORAGE_PATH'] ?? '', [$_ENV['STORAGE_KEY_ID'] ?? 'v1' => $_ENV['STORAGE_KEY'] ?? ''], $_ENV['STORAGE_KEY_ID'] ?? 'v1');
    }

    public function upload(array $file): array
    {
        $upload = $this->policy->uploaded($file);
        return $this->store($upload['bytes'], $upload['metadata']);
    }

    /** For application-generated content; HTTP uploads must use upload(). */
    public function write(string $bytes, string $filename): array
    {
        return $this->store($bytes, $this->policy->validate($bytes, $filename));
    }

    private function store(string $bytes, array $metadata): array
    {
        $id = bin2hex(random_bytes(32)) . '.enc';
        $metadata['id'] = $id;
        $this->persist($this->path($id), $this->encrypt($bytes, $metadata));
        return $metadata;
    }

    private function encrypt(string $bytes, array $metadata): string
    {
        $json = json_encode($metadata, JSON_THROW_ON_ERROR);
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        return 'INNA1' . chr(strlen($this->activeKey)) . $this->activeKey . $nonce
            . sodium_crypto_secretbox(pack('N', strlen($json)) . $json . $bytes, $nonce, $this->keys[$this->activeKey]);
    }

    private function persist(string $path, string $bytes): void
    {
        $file = @fopen($path, 'xb');
        if (!$file) $this->unavailable();
        try {
            if (!chmod($path, 0600)) $this->unavailable();
            for ($offset = 0, $length = strlen($bytes); $offset < $length; $offset += $written) {
                $written = fwrite($file, substr($bytes, $offset));
                if ($written === false || $written === 0) $this->unavailable();
            }
            if (!fflush($file) || !fsync($file)) $this->unavailable();
        } catch (\Throwable $error) {
            fclose($file);
            @unlink($path);
            throw $error;
        }
        fclose($file);
    }

    public function read(string $id): array
    {
        $path = $this->path($id);
        $stat = @lstat($path);
        $file = @fopen($path, 'rb');
        if (!$file || !$stat) { if ($file) fclose($file); $this->unavailable(); }
        try {
            $opened = fstat($file);
            if (!$opened || $opened['ino'] !== $stat['ino'] || $opened['dev'] !== $stat['dev'] || ($opened['mode'] & 0170000) !== 0100000) $this->unavailable();
            $raw = stream_get_contents($file, $this->policy->maxBytes + 8193);
        } finally { fclose($file); }
        if ($raw === false || strlen($raw) > $this->policy->maxBytes + 8192 || substr($raw, 0, 5) !== 'INNA1' || strlen($raw) < 48) $this->unavailable();
        $length = ord($raw[5]);
        $keyId = substr($raw, 6, $length);
        if (!isset($this->keys[$keyId]) || strlen($raw) < 6 + $length + 40) $this->unavailable();
        $nonce = substr($raw, 6 + $length, 24);
        $plain = sodium_crypto_secretbox_open(substr($raw, 30 + $length), $nonce, $this->keys[$keyId]);
        if ($plain === false || strlen($plain) < 4) $this->unavailable();
        $metadataLength = unpack('Nlength', substr($plain, 0, 4))['length'];
        if ($metadataLength > 4096 || $metadataLength > strlen($plain) - 4) $this->unavailable();
        try { $metadata = json_decode(substr($plain, 4, $metadataLength), true, 16, JSON_THROW_ON_ERROR); }
        catch (\JsonException) { $this->unavailable(); }
        $bytes = substr($plain, 4 + $metadataLength);
        if (!is_array($metadata) || ($metadata['id'] ?? null) !== $id || ($metadata['size'] ?? null) !== strlen($bytes)
            || !is_string($metadata['sha256'] ?? null) || !hash_equals($metadata['sha256'], hash('sha256', $bytes))) $this->unavailable();
        return ['metadata' => $metadata, 'bytes' => $bytes, 'key_id' => $keyId];
    }

    /** Re-encrypt in place, keeping the opaque ID. Coordinate rotation with application writes. */
    public function rotate(string $id): void
    {
        $object = $this->read($id);
        $temporary = $this->path(bin2hex(random_bytes(32)) . '.enc');
        $this->persist($temporary, $this->encrypt($object['bytes'], $object['metadata']));
        try {
            $target = $this->path($id);
            if (!rename($temporary, $target)) $this->unavailable();
        } finally { if (is_file($temporary)) @unlink($temporary); }
    }

    public function remove(string $id): void
    {
        $path = $this->path($id);
        if (is_file($path) && !unlink($path)) $this->unavailable();
    }

    private function path(string $id): string
    {
        if (!preg_match('/^[a-f0-9]{64}\.enc$/D', $id)) throw new \InvalidArgumentException('Invalid storage identifier.');
        $path = $this->root . '/' . $id;
        if (is_link($path)) $this->unavailable();
        return $path;
    }

    private function unavailable(): never { throw new \RuntimeException('Private file storage is unavailable or the object failed authentication.'); }
}
