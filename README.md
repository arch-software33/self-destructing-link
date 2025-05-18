# Self-Destructing File Link Service

A simple, file-based self-destructing file sharing service written in PHP. This service allows users to upload files and generate one-time download links that automatically delete both the file and the link after the first access.

## Features

- File upload with one-time download link generation
- Automatic file deletion after download
- No database required - purely file-based storage
- Simple integration into existing PHP projects
- Secure file handling and random link generation

## Requirements

- PHP 7.4 or higher
- Apache/Nginx web server
- Write permissions for storage directory

## Installation

1. Clone this repository or copy the files to your project:
```bash
git clone https://github.com/archsoftware33/self-destructing-link.git
```

2. Create a storage directory and ensure it's writable:
```bash
mkdir storage
chmod 755 storage
```

3. Configure your web server to deny direct access to the storage directory.

### Apache Configuration
Add this to your `.htaccess` file:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^storage/ - [F,L]
</IfModule>
```

### Nginx Configuration
Add this to your server block:
```nginx
location /storage {
    deny all;
    return 403;
}
```

## Integration

1. Include the necessary files in your PHP project:
```php
require_once 'path/to/FileHandler.php';
```

2. Basic usage example:

```php
// Upload file and generate link
$fileHandler = new FileHandler();
$link = $fileHandler->handleUpload($_FILES['file']);

// Download file
$fileHandler->handleDownload($_GET['token']);
```

## Usage Example

### Upload Form
```html
<form action="upload.php" method="post" enctype="multipart/form-data">
    <input type="file" name="file" required>
    <button type="submit">Upload</button>
</form>
```

### Upload Handler (upload.php)
```php
<?php
require_once 'FileHandler.php';

$fileHandler = new FileHandler();

try {
    $link = $fileHandler->handleUpload($_FILES['file']);
    echo "Your one-time download link: " . $link;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

### Download Handler (download.php)
```php
<?php
require_once 'FileHandler.php';

$fileHandler = new FileHandler();

try {
    $fileHandler->handleDownload($_GET['token']);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

## Security Considerations

1. The storage directory must be outside the web root or properly protected
2. File types should be validated before upload
3. Implement rate limiting for production use
4. Consider adding password protection for sensitive files
5. Use HTTPS in production

## License

MIT License - See LICENSE file for details

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request 