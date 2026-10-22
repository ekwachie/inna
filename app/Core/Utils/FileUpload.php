<?php
namespace app\Core\Utils;

use app\Services\Storage\EncryptedStorage;

/** Private uploads by default. Public images use PublicImageStorage explicitly. */
final class FileUpload
{
    public function __construct(private readonly EncryptedStorage $storage) {}
    public function upload(array $file): array { return $this->storage->upload($file); }
}
