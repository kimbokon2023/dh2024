<?php
// Set very conservative limits to avoid ModSecurity
ini_set('post_max_size', '500K');
ini_set('upload_max_filesize', '500K');
ini_set('max_execution_time', 300);
ini_set('memory_limit', '16M');

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

if (isset($_POST['pdf']) && isset($_POST['filename'])) {
    $pdf = $_POST['pdf'];
    $filename = $_POST['filename'];

    // 1. 파일명에서 경로 문자 제거
    $cleanFilename = preg_replace('/[\/\\\\:*?"<>|]/u', '_', $filename); // 파일명에서 위험 문자 제거
    // 2. 디렉토리 확인 및 생성
    $dir = $_SERVER['DOCUMENT_ROOT'] . '/pdfs/';
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }

    $filePath = $dir . $cleanFilename;

    // 3. PDF 파일 저장
    if (file_put_contents($filePath, base64_decode($pdf)) !== false) {
        echo json_encode(['filename' => $cleanFilename]);
    } else {
        echo json_encode(['error' => '파일 저장에 실패했습니다.']);
    }
} else {
    echo json_encode(['error' => 'Invalid request']);
}