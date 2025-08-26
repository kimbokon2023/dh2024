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
function generateCustomerSheetHTML($workplace, $secondordnum, $fromdate, $todate) {
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

// Function to generate text content as fallback
function generateTextContent($workplace, $secondordnum, $fromdate, $todate) {
    $content = "==========================================\n";
    $content .= "           " . $workplace . " 관리대장\n";
    $content .= "           (거래명세서별)\n";
    $content .= "==========================================\n\n";
    
    $content .= "회사명 : 주식회사 대한 / 담당 : 최정인 과장\n";
    $content .= "기간 : " . $fromdate . " ~ " . $todate . "\n\n";
    
    $content .= "사업자등록번호: " . $secondordnum . "\n";
    $content .= "여신한도: 0\n";
    $content .= "대표자: \n";
    $content .= "전화: \n";
    $content .= "Email: \n";
    $content .= "Fax: \n";
    $content .= "주소: \n";
    $content .= "적요: \n\n";
    
    $content .= "==========================================\n";
    $content .= "              판매/수금내역\n";
    $content .= "==========================================\n";
    $content .= "일자\t\t적요\t\t판매\t\t수금\t\t잔액\n";
    $content .= "==========================================\n";
    $content .= "이월잔액\t\t\t\t\t\t0\n";
    $content .= "데이터를 불러오는 중...\n";
    
    return $content;
}

// Handle customer sheet PDF generation
if (isset($_POST['filename']) && isset($_POST['workplace']) && isset($_POST['secondordnum'])) {
    $filename = $_POST['filename'];
    $workplace = $_POST['workplace'];
    $secondordnum = $_POST['secondordnum'];
    $fromdate = $_POST['fromdate'] ?? '';
    $todate = $_POST['todate'] ?? '';
    
    // Clean filename
    $cleanFilename = cleanFilename($filename);
    
    // Create directory
    $dir = $_SERVER['DOCUMENT_ROOT'] . '/pdfs/';
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
    
    $pdfFile = $dir . $cleanFilename;
    
    // Generate HTML content for customer sheet
    $htmlContent = generateCustomerSheetHTML($workplace, $secondordnum, $fromdate, $todate);
    
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
                'success' => true,
                'filename' => $cleanFilename,
                'message' => 'PDF created successfully using TCPDF'
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'TCPDF error: ' . $e->getMessage()
            ]);
        }
    } else {
        // Create a simple text file as fallback
        $textContent = generateTextContent($workplace, $secondordnum, $fromdate, $todate);
        $textFile = $dir . str_replace('.pdf', '.txt', $cleanFilename);
        
        if (file_put_contents($textFile, $textContent) !== false) {
            echo json_encode([
                'success' => true,
                'filename' => str_replace('.pdf', '.txt', $cleanFilename),
                'message' => 'Text file created (TCPDF not available)'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to create text file'
            ]);
        }
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
}
?> 