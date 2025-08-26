<?php
// Set more generous limits for PDF uploads
ini_set('post_max_size', '50M');
ini_set('upload_max_filesize', '50M');
ini_set('max_execution_time', 600);
ini_set('memory_limit', '256M');
ini_set('max_input_time', 600);

// Function to get server upload limits
function getServerLimits() {
    return [
        'post_max_size' => ini_get('post_max_size'),
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'max_execution_time' => ini_get('max_execution_time'),
        'memory_limit' => ini_get('memory_limit'),
        'max_input_time' => ini_get('max_input_time'),
        'max_file_uploads' => ini_get('max_file_uploads')
    ];
}

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

// Handle chunked upload for very large PDFs
if (isset($_POST['chunk']) && isset($_POST['filename'])) {
    $chunk = $_POST['chunk'];
    $filename = $_POST['filename'];
    $chunkIndex = isset($_POST['chunkIndex']) ? (int)$_POST['chunkIndex'] : 0;
    $totalChunks = isset($_POST['totalChunks']) ? (int)$_POST['totalChunks'] : 1;
    
    // Clean filename - more comprehensive cleaning
    $cleanFilename = cleanFilename($filename);
    $dir = $_SERVER['DOCUMENT_ROOT'] . '/pdfs/';
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
    
    $tempFile = $dir . $cleanFilename . '.tmp';
    
    // Append chunk to temporary file
    $chunkData = base64_decode($chunk);
    file_put_contents($tempFile, $chunkData, FILE_APPEND | LOCK_EX);
    
    // If this is the last chunk, rename temp file to final file
    if ($chunkIndex == $totalChunks - 1) {
        $finalFile = $dir . $cleanFilename;
        if (rename($tempFile, $finalFile)) {
            echo json_encode(['filename' => $cleanFilename, 'status' => 'complete']);
        } else {
            echo json_encode(['error' => '파일 저장에 실패했습니다.']);
        }
    } else {
        echo json_encode(['status' => 'chunk_received', 'chunkIndex' => $chunkIndex]);
    }
    exit;
}

// Handle regular PDF upload (for smaller files)
if (isset($_POST['pdf']) && isset($_POST['filename'])) {
    $pdf = $_POST['pdf'];
    $filename = $_POST['filename'];

    // Check if PDF data is too large for single upload
    $pdfSize = strlen($pdf);
    $maxSize = 10 * 1024 * 1024; // 10MB limit for single upload
    
    // Log detailed size information for debugging
    error_log("PDF Upload Debug - Base64 size: " . $pdfSize . " bytes (" . round($pdfSize/1024, 2) . " KB)");
    error_log("PDF Upload Debug - Estimated actual size: " . round(($pdfSize * 3) / 4) . " bytes (" . round(($pdfSize * 3) / 4 / 1024, 2) . " KB)");
    error_log("PDF Upload Debug - Server limits - POST: " . ini_get('post_max_size') . ", Upload: " . ini_get('upload_max_filesize'));
    
    if ($pdfSize > $maxSize) {
        echo json_encode(['error' => 'PDF가 너무 큽니다. 청크 업로드를 사용하세요.', 'size' => $pdfSize]);
        exit;
    }

    // Clean filename
    $cleanFilename = cleanFilename($filename);
    
    // Create directory if it doesn't exist
    $dir = $_SERVER['DOCUMENT_ROOT'] . '/pdfs/';
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }

    $filePath = $dir . $cleanFilename;

    // Decode and save PDF
    $pdfData = base64_decode($pdf);
    if ($pdfData === false) {
        echo json_encode(['error' => 'PDF 데이터 디코딩에 실패했습니다.']);
        exit;
    }

    // Save PDF file
    if (file_put_contents($filePath, $pdfData) !== false) {
        error_log("PDF Upload Success - File saved: " . $cleanFilename . " (" . filesize($filePath) . " bytes)");
        echo json_encode(['filename' => $cleanFilename, 'status' => 'success']);
    } else {
        error_log("PDF Upload Error - Failed to save file: " . $cleanFilename);
        echo json_encode(['error' => '파일 저장에 실패했습니다.']);
    }
} else {
    // Check if this is a status request
    if (isset($_GET['status']) && $_GET['status'] === 'check') {
        $limits = getServerLimits();
        echo json_encode([
            'status' => 'server_info',
            'limits' => $limits,
            'current_settings' => [
                'post_max_size_set' => ini_get('post_max_size'),
                'upload_max_filesize_set' => ini_get('upload_max_filesize'),
                'max_execution_time_set' => ini_get('max_execution_time'),
                'memory_limit_set' => ini_get('memory_limit'),
                'max_input_time_set' => ini_get('max_input_time')
            ]
        ]);
        exit;
    }
    
    echo json_encode(['error' => 'Invalid request']);
}
?>