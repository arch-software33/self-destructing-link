<?php

class FileHandler {
    private string $storageDir;
    private string $metadataDir;
    private int $tokenLength = 32;

    public function __construct(string $storageDir = 'storage') {
        $this->storageDir = rtrim($storageDir, '/');
        $this->metadataDir = $this->storageDir . '/.metadata';
        
        // Create storage and metadata directories if they don't exist
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
        if (!is_dir($this->metadataDir)) {
            mkdir($this->metadataDir, 0755, true);
        }
    }

    /**
     * Handle file upload and generate one-time download link
     * 
     * @param array $uploadedFile The $_FILES array element
     * @return string The generated download token/link
     * @throws Exception If upload fails
     */
    public function handleUpload(array $uploadedFile): string {
        // Validate upload
        if (!isset($uploadedFile['error']) || is_array($uploadedFile['error'])) {
            throw new Exception('Invalid file upload parameters');
        }

        // Check upload errors
        switch ($uploadedFile['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new Exception('File too large');
            case UPLOAD_ERR_PARTIAL:
                throw new Exception('File upload was partial');
            case UPLOAD_ERR_NO_FILE:
                throw new Exception('No file uploaded');
            default:
                throw new Exception('Unknown upload error');
        }

        // Generate unique token
        $token = bin2hex(random_bytes($this->tokenLength / 2));
        
        // Get file information
        $originalName = basename($uploadedFile['name']);
        $mimeType = $uploadedFile['type'];
        $fileSize = $uploadedFile['size'];

        // Create storage path
        $storagePath = $this->storageDir . '/' . $token;
        $metadataPath = $this->metadataDir . '/' . $token . '.json';

        // Store file metadata
        $metadata = [
            'originalName' => $originalName,
            'mimeType' => $mimeType,
            'size' => $fileSize,
            'created' => time()
        ];

        // Move uploaded file and save metadata
        if (!move_uploaded_file($uploadedFile['tmp_name'], $storagePath)) {
            throw new Exception('Failed to store uploaded file');
        }

        if (!file_put_contents($metadataPath, json_encode($metadata))) {
            unlink($storagePath);
            throw new Exception('Failed to store file metadata');
        }

        return $token;
    }

    /**
     * Handle file download and deletion
     * 
     * @param string $token The download token
     * @throws Exception If file not found or other errors
     */
    public function handleDownload(string $token): void {
        // Validate token format
        if (!preg_match('/^[a-f0-9]{' . $this->tokenLength . '}$/', $token)) {
            throw new Exception('Invalid download token');
        }

        $filePath = $this->storageDir . '/' . $token;
        $metadataPath = $this->metadataDir . '/' . $token . '.json';

        // Check if file exists
        if (!file_exists($filePath) || !file_exists($metadataPath)) {
            throw new Exception('File not found or already downloaded');
        }

        // Read metadata
        $metadata = json_decode(file_get_contents($metadataPath), true);
        if (!$metadata) {
            throw new Exception('Invalid file metadata');
        }

        // Send file headers
        header('Content-Type: ' . $metadata['mimeType']);
        header('Content-Disposition: attachment; filename="' . $metadata['originalName'] . '"');
        header('Content-Length: ' . $metadata['size']);
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Send file content and delete
        readfile($filePath);
        
        // Delete file and metadata
        unlink($filePath);
        unlink($metadataPath);
    }

    /**
     * Clean up expired files (optional, can be called via cron)
     * 
     * @param int $maxAge Maximum age in seconds before file is considered expired
     * @return int Number of files cleaned up
     */
    public function cleanupExpiredFiles(int $maxAge = 86400): int {
        $cleaned = 0;
        $now = time();

        foreach (glob($this->metadataDir . '/*.json') as $metadataFile) {
            $metadata = json_decode(file_get_contents($metadataFile), true);
            if (!$metadata) continue;

            if (($now - $metadata['created']) > $maxAge) {
                $token = basename($metadataFile, '.json');
                $filePath = $this->storageDir . '/' . $token;

                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                unlink($metadataFile);
                $cleaned++;
            }
        }

        return $cleaned;
    }
} 