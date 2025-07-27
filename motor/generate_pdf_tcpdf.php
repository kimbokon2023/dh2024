<?php
header('Content-Type: application/json');

// Try to use TCPDF if available
$tcpdfAvailable = false;

// Check if TCPDF is available
if (class_exists('TCPDF')) {
    $tcpdfAvailable = true;
} else {
    // Try to include TCPDF manually
    $tcpdfPaths = [
        $_SERVER['DOCUMENT_ROOT'] . '/vendor/tecnickcom/tcpdf/tcpdf.php',
        $_SERVER['DOCUMENT_ROOT'] . '/tcpdf/tcpdf.php',
        '/usr/share/php/tcpdf/tcpdf.php'
    ];
    
    foreach ($tcpdfPaths as $path) {
        if (file_exists($path)) {
            require_once($path);
            $tcpdfAvailable = true;
            break;
        }
    }
}

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
    
    $pdfFile = $dir . $cleanFilename;
    
    if ($tcpdfAvailable) {
        try {
            // Create new PDF document
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            
            // Set document information
            $pdf->SetCreator('DH Motor System');
            $pdf->SetAuthor('DH Motor');
            $pdf->SetTitle($cleanFilename);
            
            // Set default header data
            $pdf->SetHeaderData('', 0, '', '', array(0,0,0), array(255,255,255));
            $pdf->setFooterData(array(0,0,0), array(255,255,255));
            
            // Set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
            
            // Set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            
            // Set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
            
            // Set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            
            // Set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            
            // Add a page
            $pdf->AddPage();
            
            // Set font
            $pdf->SetFont('dejavusans', '', 10);
            
            // Write HTML content
            $pdf->writeHTML($htmlContent, true, false, true, false, '');
            
            // Save PDF
            $pdf->Output($pdfFile, 'F');
            
            echo json_encode([
                'filename' => $cleanFilename,
                'message' => 'PDF created successfully using TCPDF'
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'error' => 'TCPDF error: ' . $e->getMessage(),
                'pdf_created' => false
            ]);
        }
    } else {
        echo json_encode([
            'error' => 'TCPDF not available',
            'pdf_created' => false
        ]);
    }
} else {
    echo json_encode(['error' => 'Missing required parameters']);
}
?> 