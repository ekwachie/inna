<?php
namespace app\Services\Storage;

final class ImageProcessor
{
    public function optimize(string $bytes, int $quality = 80): string
    {
        if (!extension_loaded('gd')) throw new \RuntimeException('Image optimization requires ext-gd.');
        if ($quality < 1 || $quality > 100) throw new \InvalidArgumentException('Quality must be 1-100.');
        $info = @getimagesizefromstring($bytes);
        if (!$info || $info[0] * $info[1] > 20000000 || !in_array($info['mime'], ['image/jpeg', 'image/png', 'image/webp'], true)) throw new \InvalidArgumentException('Use a JPEG, PNG or WebP image up to 20 megapixels.');
        $image = @imagecreatefromstring($bytes);
        if (!$image) throw new \InvalidArgumentException('Cannot decode image.');
        ob_start();
        try {
            imagesavealpha($image, true);
            $ok = match ($info['mime']) {
                'image/jpeg' => imagejpeg($image, null, $quality),
                'image/png' => imagepng($image, null, (int) round((100 - $quality) * 9 / 100)),
                'image/webp' => imagewebp($image, null, $quality),
            };
            if (!$ok) throw new \RuntimeException('Cannot encode image.');
            return ob_get_contents();
        } finally { ob_end_clean(); imagedestroy($image); }
    }
}
