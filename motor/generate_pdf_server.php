<?php
header('Content-Type: application/json');

// Simple HTML to PDF conversion using wkhtmltopdf or similar
// For now, we'll create a PDF using a different approach

if (isset($_POST['html_content']) && isset($_POST['filename'])) {
    $htmlContent = $_POST['html_content'];
    $filename = $_POST['filename'];
    
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
    
    $htmlFile = $dir . str_replace('.pdf', '.html', $cleanFilename);
    $pdfFile = $dir . $cleanFilename;
    
    // Create a complete HTML document
    $fullHtml = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>' . $cleanFilename . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        @media print {
            body { margin: 0; }
        }
    </style>
</head>
<body>
' . $htmlContent . '
</body>
</html>';
    
    // Save HTML file first
    if (file_put_contents($htmlFile, $fullHtml) !== false) {
        
        // Try to convert HTML to PDF using different methods
        $pdfCreated = false;
        
        // Method 1: Try wkhtmltopdf if available
        if (function_exists('shell_exec')) {
            $command = "wkhtmltopdf --quiet --encoding utf-8 '$htmlFile' '$pdfFile' 2>&1";
            $output = shell_exec($command);
            if (file_exists($pdfFile)) {
                $pdfCreated = true;
            }
        }
        
        // Method 2: Try using Chrome/Chromium headless
        if (!$pdfCreated && function_exists('shell_exec')) {
            $command = "google-chrome --headless --disable-gpu --print-to-pdf='$pdfFile' '$htmlFile' 2>&1";
            $output = shell_exec($command);
            if (file_exists($pdfFile)) {
                $pdfCreated = true;
            }
        }
        
        // Method 3: Try using Chromium
        if (!$pdfCreated && function_exists('shell_exec')) {
            $command = "chromium-browser --headless --disable-gpu --print-to-pdf='$pdfFile' '$htmlFile' 2>&1";
            $output = shell_exec($command);
            if (file_exists($pdfFile)) {
                $pdfCreated = true;
            }
        }
        
        if ($pdfCreated) {
            // Clean up HTML file
            unlink($htmlFile);
            
            echo json_encode([
                'filename' => $cleanFilename,
                'message' => 'PDF created successfully on server'
            ]);
        } else {
            // If PDF creation fails, return HTML file info for fallback
            echo json_encode([
                'filename' => $cleanFilename,
                'html_file' => str_replace('.pdf', '.html', $cleanFilename),
                'message' => 'HTML file saved. PDF conversion failed. Will use client-side generation.',
                'pdf_created' => false
            ]);
        }
    } else {
        echo json_encode(['error' => 'Failed to save HTML file']);
    }
} else {
    echo json_encode(['error' => 'Missing required parameters']);
}
?> 