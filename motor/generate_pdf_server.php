<?php
header('Content-Type: application/json');

// Set limits for PDF generation
ini_set('max_execution_time', 300);
ini_set('memory_limit', '256M');

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

// Function to generate customer sheet HTML content
function generateCustomerSheetHTML($workplace, $secondordnum, $fromdate, $todate, $deadline) {
    // This is a simplified version - in a real implementation, you would fetch data from database
    $html = '<div style="text-align: center; margin-bottom: 20px;">
        <h2>' . htmlspecialchars($workplace) . ' 관리대장</h2>
        <h5>(거래명세서별)</h5>
    </div>';
    
    $html .= '<div style="margin-bottom: 20px;">
        <div style="text-align: left; float: left;">회사명 : 주식회사 대한 / 담당 : 최정인 과장</div>
        <div style="text-align: right; float: right;">' . htmlspecialchars($fromdate) . ' ~ ' . htmlspecialchars($todate) . '</div>
        <div style="clear: both;"></div>
    </div>';
    
    $html .= '<table style="width: 100%; margin-bottom: 20px;">
        <tr>
            <th style="width: 20%;">사업자등록번호</th>
            <th style="width: 30%; color: blue;">' . htmlspecialchars($secondordnum) . '</th>
            <th style="width: 20%;">대표자</th>
            <th style="width: 30%;"></th>
        </tr>
        <tr>
            <th>여신한도</th>
            <th>0</th>
            <th>전화</th>
            <th></th>
        </tr>
        <tr>
            <th>Email</th>
            <th></th>
            <th>Fax</th>
            <th></th>
        </tr>
        <tr>
            <th>주소</th>
            <th colspan="3"></th>
        </tr>
        <tr>
            <th>적요</th>
            <th colspan="3"></th>
        </tr>
    </table>';
    
    $html .= '<table style="width: 100%;">
        <thead>
            <tr>
                <th colspan="5" style="text-align: center;">판매/수금내역</th>
            </tr>
            <tr>
                <th style="text-align: center;">일자</th>
                <th style="text-align: center;">적요</th>
                <th style="text-align: center;">판매</th>
                <th style="text-align: center;">수금</th>
                <th style="text-align: center;">잔액</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="2" style="text-align: center; font-weight: bold; color: blue;">이월잔액</td>
                <td style="text-align: center;"></td>
                <td style="text-align: center;"></td>
                <td style="text-align: right; font-weight: bold; color: blue;">0</td>
            </tr>
            <tr>
                <td colspan="5" style="text-align: center; font-weight: bold;">데이터를 불러오는 중...</td>
            </tr>
        </tbody>
    </table>';
    
    return $html;
}

// Handle customer sheet PDF generation
if (isset($_POST['filename']) && isset($_POST['workplace']) && isset($_POST['secondordnum'])) {
    $filename = $_POST['filename'];
    $workplace = $_POST['workplace'];
    $secondordnum = $_POST['secondordnum'];
    $fromdate = $_POST['fromdate'] ?? '';
    $todate = $_POST['todate'] ?? '';
    $deadline = $_POST['deadline'] ?? '';
    
    // Clean filename
    $cleanFilename = cleanFilename($filename);
    
    // Create directory
    $dir = $_SERVER['DOCUMENT_ROOT'] . '/pdfs/';
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
    
    $htmlFile = $dir . str_replace('.pdf', '.html', $cleanFilename);
    $pdfFile = $dir . $cleanFilename;
    
    // Generate HTML content for customer sheet
    $htmlContent = generateCustomerSheetHTML($workplace, $secondordnum, $fromdate, $todate, $deadline);
    
    // Create a complete HTML document
    $fullHtml = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>' . $cleanFilename . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; font-size: 12px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 4px; text-align: left; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .date-row { background-color: #f0f0f0; }
        .date-row-date { background-color: #f0f0f0; color: blue; }
        @media print {
            body { margin: 0; font-size: 10pt; }
            table { width: 100%; table-layout: fixed; }
            th, td { padding: 1px; border: 1px solid #ddd; }
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
                'success' => true,
                'filename' => $cleanFilename,
                'message' => 'PDF created successfully on server'
            ]);
        } else {
            // If PDF creation fails, return HTML file info for fallback
            echo json_encode([
                'success' => false,
                'filename' => $cleanFilename,
                'html_file' => str_replace('.pdf', '.html', $cleanFilename),
                'message' => 'HTML file saved. PDF conversion failed.',
                'error' => 'PDF conversion tools not available on server'
            ]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to save HTML file']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
}
?> 