<?php
namespace app\Services\Storage;

/** Only use for intentionally public images. Private documents belong in EncryptedStorage. */
final class PublicImageStorage
{
    private string $root;
    private UploadPolicy $policy;
    public function __construct(string $directory, private readonly string $urlPrefix = '/public/uploads', int $maxBytes = 2097152)
    {
        if (!str_starts_with($directory, '/') || is_link($directory) || !preg_match('~^/[a-zA-Z0-9/_-]+$~D', $urlPrefix)) throw new \InvalidArgumentException('Configure an absolute image directory and local URL prefix.');
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) throw new \RuntimeException('Image directory unavailable.');
        $this->root = realpath($directory) ?: throw new \RuntimeException('Image directory unavailable.');
        $this->policy = new UploadPolicy($maxBytes, ['image/jpeg' => ['jpg','jpeg'], 'image/png' => ['png'], 'image/webp' => ['webp']]);
    }
    public function upload(array $file, int $quality = 80): array
    {
        $upload = $this->policy->uploaded($file);
        $bytes = (new ImageProcessor())->optimize($upload['bytes'], $quality);
        $metadata = $this->policy->validate($bytes, $upload['metadata']['original_name']);
        $extension = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$metadata['mime_type']];
        $id = bin2hex(random_bytes(32)) . '.' . $extension;
        $path = $this->root . '/' . $id;
        $stream = fopen($path, 'xb');
        if (!$stream) throw new \RuntimeException('Image write failed.');
        try {
            if (!chmod($path, 0644)) throw new \RuntimeException('Cannot set image permissions.');
            for ($offset = 0; $offset < strlen($bytes); $offset += $written) {
                $written = fwrite($stream, substr($bytes, $offset));
                if ($written === false || $written === 0) throw new \RuntimeException('Image write failed.');
            }
            if (!fflush($stream)) throw new \RuntimeException('Image write failed.');
        } catch (\Throwable $error) { fclose($stream); @unlink($path); throw $error; }
        fclose($stream);
        return $metadata + ['id' => $id, 'url' => rtrim($this->urlPrefix, '/') . '/' . $id];
    }
}
