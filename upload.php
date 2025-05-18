<?php
require_once 'FileHandler.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method is allowed');
    }

    if (empty($_FILES['file'])) {
        throw new Exception('No file uploaded');
    }

    $fileHandler = new FileHandler();
    $token = $fileHandler->handleUpload($_FILES['file']);
    
    // Generate the full download URL
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $downloadUrl = $protocol . '://' . $host . dirname($_SERVER['PHP_SELF']) . '/download.php?token=' . $token;

    echo json_encode([
        'success' => true,
        'message' => 'File uploaded successfully',
        'token' => $token,
        'downloadUrl' => $downloadUrl
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 