<?php
header('Content-Type: application/json');

// Set very conservative limits
ini_set('post_max_size', '1M');
ini_set('upload_max_filesize', '1M');
ini_set('max_execution_time', 30);
ini_set('memory_limit', '16M');

// Log all incoming data for debugging
error_log("Test Upload - Raw POST data size: " . strlen(file_get_contents('php://input')));
error_log("Test Upload - POST array size: " . count($_POST));

if (isset($_POST['pdf'])) {
    $pdf = $_POST['pdf'];
    $filename = isset($_POST['filename']) ? $_POST['filename'] : 'test.pdf';
    
    error_log("Test Upload - PDF data received, length: " . strlen($pdf));
    
    // Create test directory
    $dir = $_SERVER['DOCUMENT_ROOT'] . '/pdfs/';
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
    
    $filePath = $dir . 'test_' . date('Y-m-d_H-i-s') . '.pdf';
    
    // Save test file
    $pdfData = base64_decode($pdf);
    if ($pdfData !== false) {
        $result = file_put_contents($filePath, $pdfData);
        if ($result !== false) {
            echo json_encode([
                'success' => true,
                'filename' => basename($filePath),
                'size' => $result,
                'message' => 'Test upload successful'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to write file'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Invalid base64 data'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'No PDF data received',
        'post_data' => array_keys($_POST)
    ]);
}
?> 