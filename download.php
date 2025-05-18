<?php
require_once 'FileHandler.php';

try {
    if (!isset($_GET['token'])) {
        throw new Exception('No download token provided');
    }

    $token = $_GET['token'];
    $fileHandler = new FileHandler();
    $fileHandler->handleDownload($token);

} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 