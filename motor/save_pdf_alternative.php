<?php
// Alternative PDF save method with different approach
header('Content-Type: application/json');

// Set very conservative limits to avoid ModSecurity
ini_set('post_max_size', '20M');
ini_set('upload_max_filesize', '20M');
ini_set('max_execution_time', 120);
ini_set('memory_limit', '16M');

// Get raw POST data
$rawData = file_get_contents('php://input');

if (empty($rawData)) {
    // Try regular POST data
    if (isset($_POST['pdf']) && isset($_POST['filename'])) {
        $pdf = $_POST['pdf'];
        $filename = $_POST['filename'];
    } else {
        echo json_encode(['error' => 'No data received']);
        exit;
    }
} else {
    // Parse JSON data
    $data = json_decode($rawData, true);
    if ($data && isset($data['pdf']) && isset($data['filename'])) {
        $pdf = $data['pdf'];
        $filename = $data['filename'];
    } else {
        // Try to get data from $_POST if JSON parsing fails
        if (isset($_POST['pdf']) && isset($_POST['filename'])) {
            $pdf = $_POST['pdf'];
            $filename = $_POST['filename'];
        } else {
            echo json_encode(['error' => 'Invalid data format']);
            exit;
        }
    }
}

// Log the data received for debugging
error_log("Alternative PDF Upload - Data received, PDF length: " . strlen($pdf));

    // Clean filename - more comprehensive cleaning
    $cleanFilename = cleanFilename($filename);
    
    // Function to clean filename
    function cleanFilename($filename) {
        // Remove or replace problematic characters
        $filename = preg_replace('/[\/\\\\:*?"<>|]/u', '_', $filename);
        $filename = preg_replace('/[^\p{L}\p{N}\s\-_\.]/u', '_', $filename); // Only allow letters, numbers, spaces, hyphens, underscores, dots
        $filename = preg_replace('/\s+/', '_', $filename); // Replace multiple spaces with single underscore
        $filename = preg_replace('/_+/', '_', $filename); // Replace multiple underscores with single underscore
        $filename = trim($filename, '._'); // Remove leading/trailing dots and underscores
        $filename = substr($filename, 0, 200); // Limit length to 200 characters
        
        // Ensure filename is not empty
        if (empty($filename)) {
            $filename = 'document_' . date('Y-m-d_H-i-s') . '.pdf';
        }
        
        // Ensure it ends with .pdf
        if (!preg_match('/\.pdf$/i', $filename)) {
            $filename .= '.pdf';
        }
        
        return $filename;
    }

// Create directory
$dir = $_SERVER['DOCUMENT_ROOT'] . '/pdfs/';
if (!file_exists($dir)) {
    mkdir($dir, 0755, true);
}

$filePath = $dir . $cleanFilename;

// Save PDF
try {
    $pdfData = base64_decode($pdf);
    if ($pdfData === false) {
        echo json_encode(['error' => 'Invalid base64 data']);
        exit;
    }
    
    $result = file_put_contents($filePath, $pdfData);
    if ($result !== false) {
        echo json_encode(['filename' => $cleanFilename, 'size' => $result]);
    } else {
        echo json_encode(['error' => 'Failed to save file']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Exception: ' . $e->getMessage()]);
}
?> 