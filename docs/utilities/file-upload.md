# File Upload

Inna provides utilities for handling file uploads securely.

## Basic File Upload

### HTML Form

```html
<form action="/upload" method="POST" enctype="multipart/form-data">
    <input type="file" name="file">
    <button type="submit">Upload</button>
</form>
```

### Controller Handler

```php
public function upload(Request $request, Response $response)
{
    if ($request->isPost() && isset($_FILES['file'])) {
        $file = $_FILES['file'];
        
        // Validate file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            echo $this->setFlash('error', 'Upload failed');
            return $this->render('upload');
        }
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            echo $this->setFlash('error', 'Invalid file type');
            return $this->render('upload');
        }
        
        // Validate file size (2MB)
        if ($file['size'] > 2097152) {
            echo $this->setFlash('error', 'File too large');
            return $this->render('upload');
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $destination = Application::$ROOT_DIR . '/public/uploads/' . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            echo $this->setFlash('success', 'File uploaded successfully');
            return $response->redirect('/upload');
        }
        
        echo $this->setFlash('error', 'Upload failed');
    }
    
    return $this->render('upload');
}
```

## Using FileUpload Utility

The framework includes a `FileUpload` utility class. Check `app/Core/Utils/FileUpload.php` for available methods.

## Validation

### Using Model Validation

```php
use app\Core\Model;

$validation = new Model();

$validation->name('avatar')
    ->file($_FILES['avatar'] ?? [])
    ->required()
    ->maxSize(2097152) // 2MB
    ->ext('jpg'); // or 'png', 'gif', etc.

if (!$validation->isSuccess()) {
    $errors = $validation->getErrors();
    // Handle errors
}
```

## Security Best Practices

1. **Validate file type**: Check MIME type, not just extension
2. **Limit file size**: Set maximum file size limits
3. **Unique filenames**: Generate unique filenames to prevent overwrites
4. **Store outside web root**: Store uploaded files outside public directory when possible
5. **Scan for viruses**: Consider virus scanning for user uploads
6. **Whitelist extensions**: Only allow specific file extensions

## Complete Example

```php
public function upload(Request $request, Response $response)
{
    if ($request->isPost() && isset($_FILES['file'])) {
        $file = $_FILES['file'];
        
        // Validation
        $validation = new Model();
        $validation->name('file')
            ->file($file)
            ->required()
            ->maxSize(5242880) // 5MB
            ->ext('pdf');
        
        if (!$validation->isSuccess()) {
            $errors = $validation->getErrors();
            echo $this->setFlash('error', implode(', ', $errors));
            return $this->render('upload');
        }
        
        // Additional MIME type check
        $allowedMimes = ['application/pdf'];
        if (!in_array($file['type'], $allowedMimes)) {
            echo $this->setFlash('error', 'Invalid file type');
            return $this->render('upload');
        }
        
        // Generate safe filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        
        // Create upload directory if it doesn't exist
        $uploadDir = Application::$ROOT_DIR . '/public/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $destination = $uploadDir . $filename;
        
        // Move file
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            // Save file info to database
            $db = Application::$app->db;
            $fileId = $db->insert('files', [
                'filename' => $filename,
                'original_name' => $file['name'],
                'size' => $file['size'],
                'type' => $file['type'],
                'uploaded_at' => date('Y-m-d H:i:s')
            ]);
            
            echo $this->setFlash('success', 'File uploaded successfully');
            return $response->redirect('/file/' . $fileId);
        }
        
        echo $this->setFlash('error', 'Upload failed');
    }
    
    return $this->render('upload');
}
```

## Multiple File Upload

```php
public function uploadMultiple(Request $request, Response $response)
{
    if ($request->isPost() && isset($_FILES['files'])) {
        $files = $_FILES['files'];
        $uploaded = [];
        $errors = [];
        
        foreach ($files['name'] as $key => $name) {
            if ($files['error'][$key] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $files['name'][$key],
                    'type' => $files['type'][$key],
                    'tmp_name' => $files['tmp_name'][$key],
                    'error' => $files['error'][$key],
                    'size' => $files['size'][$key]
                ];
                
                // Validate and upload
                $extension = pathinfo($name, PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $extension;
                $destination = Application::$ROOT_DIR . '/public/uploads/' . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $uploaded[] = $filename;
                } else {
                    $errors[] = $name;
                }
            }
        }
        
        if (!empty($uploaded)) {
            echo $this->setFlash('success', count($uploaded) . ' files uploaded');
        }
        
        if (!empty($errors)) {
            echo $this->setFlash('error', count($errors) . ' files failed');
        }
    }
    
    return $this->render('upload-multiple');
}
```

## Next Steps

- [Validation](validation.md) - Learn about validation
- [Security](/security/authentication.md) - Learn about security

