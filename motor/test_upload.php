<?php
header('Content-Type: application/json');

// Test different upload methods
$testData = str_repeat('A', 1024 * 1024); // 1MB test data

echo json_encode([
    'server_info' => [
        'post_max_size' => ini_get('post_max_size'),
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'max_execution_time' => ini_get('max_execution_time'),
        'memory_limit' => ini_get('memory_limit'),
        'max_input_time' => ini_get('max_input_time')
    ],
    'test_data_size' => strlen($testData),
    'test_data_size_mb' => round(strlen($testData) / (1024 * 1024), 2),
    'message' => 'Test script working. Check server limits above.'
]);
?> 