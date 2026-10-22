<?php
namespace app\Services\Storage;

use app\Core\ApiException;

final class UploadPolicy
{
    public function __construct(
        public readonly int $maxBytes = 2097152,
        public readonly array $types = ['application/pdf' => ['pdf'], 'image/jpeg' => ['jpg', 'jpeg'], 'image/png' => ['png']]
    ) {
        if ($maxBytes < 1 || !$types) throw new \InvalidArgumentException('Configure upload size and allowed MIME types.');
    }

    public function validate(string $bytes, string $name): array
    {
        $size = strlen($bytes);
        if ($size === 0 || $size > $this->maxBytes) throw new ApiException('UPLOAD_SIZE', 'Upload a non-empty file within the configured size limit.', 413);
        $name = basename(str_replace('\\', '/', $name));
        if ($name === '' || strlen($name) > 200 || !mb_check_encoding($name, 'UTF-8') || preg_match('/[\x00-\x1f\x7f]/', $name)) {
            throw new ApiException('UPLOAD_NAME', 'Invalid filename.', 422);
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($bytes);
        if (!isset($this->types[$mime]) || !in_array(strtolower(pathinfo($name, PATHINFO_EXTENSION)), $this->types[$mime], true)) {
            throw new ApiException('UPLOAD_TYPE', 'File type is not allowed.', 415);
        }
        if (str_starts_with($mime, 'image/')) {
            $image = @getimagesizefromstring($bytes);
            if (!$image || $image[0] < 1 || $image[1] < 1 || $image[0] * $image[1] > 20000000) throw new ApiException('UPLOAD_IMAGE', 'Invalid or oversized image dimensions.', 422);
        }
        return ['original_name' => $name, 'mime_type' => $mime, 'size' => $size, 'sha256' => hash('sha256', $bytes)];
    }

    public function uploaded(array $file): array
    {
        if (in_array($file['error'] ?? null, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) throw new ApiException('UPLOAD_SIZE', 'Upload exceeds the PHP upload limit.', 413);
        if (($file['error'] ?? null) !== UPLOAD_ERR_OK || !is_string($file['tmp_name'] ?? null) || !is_string($file['name'] ?? null)
            || !is_uploaded_file($file['tmp_name'])) throw new ApiException('INVALID_UPLOAD', 'Upload did not complete.', 422);
        $bytes = file_get_contents($file['tmp_name'], false, null, 0, $this->maxBytes + 1);
        if ($bytes === false) throw new ApiException('INVALID_UPLOAD', 'Cannot read upload.', 422);
        return ['bytes' => $bytes, 'metadata' => $this->validate($bytes, $file['name'])];
    }
}
