# Public Images

Private files and public images have different access rules. Use PublicImageStorage only when anyone with the resulting URL may read the image.

## Upload an image

Create an intentionally public directory such as public/uploads, mapped to /public/uploads by your server. The directory and URL prefix must describe the same location.

```php
use app\Services\Storage\PublicImageStorage;

$images = new PublicImageStorage(
    \app\Core\Application::$ROOT_DIR . '/public/uploads',
    '/public/uploads'
);
$metadata = $images->upload($_FILES['image'] ?? [], 80);
```

The service requires ext-gd. It accepts JPEG, PNG and WebP, checks actual MIME/extension agreement and dimensions, decodes/re-encodes the image, and uses a random filename with an extension derived from MIME. Returned metadata includes id and url. Public files use 0644 permissions.

The original image must be non-empty and within the configured size limit (default 2 MB); the encoded output is validated against that limit too. GIF and SVG uploads are deliberately not accepted by this service. Image re-encoding can remove metadata; do not use it for evidence whose original bytes must be preserved.

## Optimization alone

```php
use app\Services\Storage\ImageProcessor;

$optimized = (new ImageProcessor())->optimize($imageBytes, 80);
```

Quality must be 1-100. JPEG/WebP use the encoder's quality setting; PNG maps it to compression effort. Dimensions are checked before allocating a decoded image, with a 20-megapixel ceiling.

## Ownership and cleanup

Authenticate uploads and enforce resource ownership, quotas and retention in your application. Save the returned URL only after the image write succeeds. Replacing an avatar does not automatically delete its previous image; implement cleanup against your own metadata.

Never place executable files in this directory. Configure the web server to serve it as static assets and deny PHP execution. Documents containing private information belong in [encrypted storage](storage/encrypted-uploads.md), not public images.
