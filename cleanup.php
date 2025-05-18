<?php
require_once 'FileHandler.php';

// Set script execution time limit to 5 minutes
set_time_limit(300);

try {
    $fileHandler = new FileHandler();
    
    // Clean up files older than 24 hours (86400 seconds)
    $cleanedCount = $fileHandler->cleanupExpiredFiles(86400);
    
    echo json_encode([
        'success' => true,
        'message' => "Cleanup completed successfully. Removed {$cleanedCount} expired files.",
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} 