<?php
// 에러 표시 설정
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 모든 요청을 로그에 기록
error_log("=== Motor order Excel generation request started ===");
error_log("Script: " . __FILE__);
error_log("Time: " . date('Y-m-d H:i:s'));

header('Content-Type: application/json'); // JSON 응답 설정

$response = ['success' => false, 'message' => 'Unknown error'];

try {
    // 요청 메서드 로깅
    error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
    error_log("Content-Type: " . (isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : 'not set'));
    error_log("Content-Length: " . (isset($_SERVER['CONTENT_LENGTH']) ? $_SERVER['CONTENT_LENGTH'] : 'not set'));
    error_log("HTTP_USER_AGENT: " . (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'not set'));
    error_log("REQUEST_URI: " . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'not set'));
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // 요청에서 JSON 데이터 가져오기
        if (isset($_POST['excelData'])) {
            $rawInput = $_POST['excelData'];
            error_log("Data from POST: " . $rawInput);
            $data = json_decode($rawInput, true);
        } else {
            $rawInput = file_get_contents('php://input');
            error_log("Raw input: " . $rawInput);
            $data = json_decode($rawInput, true);
        }
				
		$orderDate = isset($data['orderDate']) ? $data['orderDate'] : '';
		$items = isset($data['items']) ? $data['items'] : [];		

        // JSON 오류 확인
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON input: ' . json_last_error_msg());
        }

        // 데이터가 비어있는지 확인
        if (empty($items)) {
            throw new Exception('No data received');
        }

        error_log("Data received: " . count($items) . " items");

        // PHPExcel 라이브러리 포함
        $phpExcelPath = '../PHPExcel_1.8.0/Classes/PHPExcel.php';
        if (!file_exists($phpExcelPath)) {
            throw new Exception('PHPExcel library not found at: ' . $phpExcelPath);
        }
        require $phpExcelPath;
		
        // 새로운 PHPExcel 객체 생성
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();

        // 헤더 설정 (컬럼명에 맞게)
		$headers = [
			'model_code'    => "品名\n(품명)",
			'model'         => "型号\n(모델)",
			'purchaseQty'   => "数量\n（수량）",
			'unitPrice'     => "单价\n（단가）",
			'note'          => "配置（비고）",
			'amount'        => "合计 (합계）"
		];


// A1~F1 병합 및 제목 설정
// $orderDate = date('Y-m-d');

$sheet->mergeCells('A1:F1');
$sheet->setCellValue('A1', '모터발주서');
$styleTitle = $sheet->getStyle('A1');
$styleTitle->getFont()->setBold(true)->setSize(20); // 글자 크기 2배 정도 (기본이 11~12)
$styleTitle->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$styleTitle->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

// A2~F5 병합 및 내용 설정
$infoText = "NO." . $orderDate . "\n（甲）需货方(갑)：Dae Han co.,LTD\n（乙）供货方(을)：福建安麟智能科技股份有限公司";
$sheet->mergeCells('A2:F5');
$sheet->setCellValue('A2', $infoText);
$styleInfo = $sheet->getStyle('A2');
$styleInfo->getAlignment()->setWrapText(true); // 줄바꿈
$styleInfo->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$styleInfo->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
$styleInfo->getFont()->setSize(12);

// 헤더는 A6부터 시작하도록 조정
$headerStartRow = 6;
$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . $headerStartRow, $header);

    // 스타일 설정
    $style = $sheet->getStyle($col . $headerStartRow);
    $style->getFont()->setBold(true);
    $style->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
    $style->getFill()->getStartColor()->setRGB('D9D9D9');
    $style->getAlignment()->setWrapText(true);
    $style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $style->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    // 열 너비 계산
    // $textLength = strlen($header);
    // $width = $textLength * 1.2;
    // if ($width < 30) $width = 30;
    // elseif ($width > 70) $width = 70;
    // $sheet->getColumnDimension($col)->setWidth($width);
	
$columnWidths = [
    'A' => 35,
    'B' => 35,
    'C' => 10,
    'D' => 10,
    'E' => 40,
    'F' => 13
];

    // 열 폭 수동 지정
    if (isset($columnWidths[$col])) {
        $sheet->getColumnDimension($col)->setWidth($columnWidths[$col]);
    }	

    $col++;
}
$rowNumber = $headerStartRow + 1; // 7행부터 데이터 시작
foreach ($items as $row) {
    $col = 'A';
    $maxLineCount = 1; // 각 행에서 가장 긴 셀 줄 수 저장용

    foreach ($headers as $key => $header) {
        $value = isset($row[$key]) ? $row[$key] : '';
        $cell = $col . $rowNumber;

        // E열이면 줄바꿈 적용
        if ($col === 'E') {
            $value = preg_replace('/寸(?!$)\s*/u', "寸\n", $value);
        }

        $sheet->setCellValue($cell, $value);

        // 줄 수 계산 (셀 내용 기준)
        $lineCount = substr_count($value, "\n") + 1;
        if ($lineCount > $maxLineCount) {
            $maxLineCount = $lineCount;
        }

        $style = $sheet->getStyle($cell);
        $style->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $style->getAlignment()->setWrapText(true);

        if (in_array($col, ['A', 'B', 'E'])) {
            $style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        } else {
            $style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        }

        $col++;
    }

    // 행 높이: 줄 수 * 13.5(기본행높이) * 1.1(여유)
    $height = $maxLineCount * 17.5 * 1.1;
    $sheet->getRowDimension($rowNumber)->setRowHeight($height);

    $rowNumber++;
}

// 1. 마지막 금액 합계 계산
$totalAmount = 0;
foreach ($items as $row) {
    $amountStr = isset($row['amount']) ? $row['amount'] : '0';
    $amountNum = floatval(str_replace(',', '', $amountStr)); // 쉼표 제거 후 숫자 변환
    $totalAmount += $amountNum;
}


// 2. '합계' 행: B~F 병합, 오른쪽 정렬
$sheet->setCellValue('B' . $rowNumber, '合计（합계）: ' . number_format($totalAmount, 2));
$sheet->mergeCells("B{$rowNumber}:F{$rowNumber}");
$style = $sheet->getStyle("B{$rowNumber}");
$style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
$style->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
$sheet->getRowDimension($rowNumber)->setRowHeight(20);
$rowNumber++;

// 3. 'CIF:INCHON' 행: A~F 병합, 왼쪽 정렬
$sheet->setCellValue('A' . $rowNumber, 'CIF:INCHON');
$sheet->mergeCells("A{$rowNumber}:F{$rowNumber}");
$style = $sheet->getStyle("A{$rowNumber}");
$style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$style->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
$sheet->getRowDimension($rowNumber)->setRowHeight(20);
$rowNumber++;

// 4. 마지막 주소/비고 3줄 행 (적색 포함)
$richText = new PHPExcel_RichText();
$richText->createText("1.주소：45-22,Ongjeong-ro,Tongjin-eup,Gimpo-si,Gyeonggi-do,Korea。Zip Code:10029   Kyung Dong co.,LTD\n");
$richText->createText("2.CNF\n");

// 빨간색 텍스트만 별도 처리
$redText = $richText->createTextRun("3.송금후40일");
$redText->getFont()->getColor()->setRGB('FF0000'); // 빨간색

$sheet->setCellValue('A' . $rowNumber, $richText);
$sheet->mergeCells("A{$rowNumber}:F{$rowNumber}");
$style = $sheet->getStyle("A{$rowNumber}");
$style->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$style->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
$style->getAlignment()->setWrapText(true);
$sheet->getRowDimension($rowNumber)->setRowHeight(60);



        // // 기본 열 너비 설정
        // foreach (range('A', 'F') as $columnID) { // A부터 F까지 (6열)
            // $sheet->getColumnDimension($columnID)->setAutoSize(true);
        // }
        
        // 테두리 설정
        $styleArray = [
            'borders' => [
                'allborders' => [
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A1:F' . ($rowNumber - 1))->applyFromArray($styleArray); // A부터 F까지 (6열)

        // 파일 저장
        $filename = 'DH(중국발주서)_' . date('YmdHis') . '.xlsx';
        $filePath = '../excelsave/' . $filename; // 파일 저장 경로
        
        // excelsave 디렉토리가 존재하는지 확인하고 없으면 생성
        $dirPath = '../excelsave/';
        if (!is_dir($dirPath)) {
            if (!mkdir($dirPath, 0755, true)) {
                throw new Exception('Failed to create directory: ' . $dirPath);
            }
        }
        
        // 디렉토리에 쓰기 권한이 있는지 확인
        if (!is_writable($dirPath)) {
            throw new Exception('Directory is not writable: ' . $dirPath);
        }
        
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save($filePath);

        // 파일이 생성되었는지 확인
        if (file_exists($filePath)) {
            $response = ['success' => true, 'filename' => $filePath];
            error_log("Excel file created successfully: " . $filePath);
        } else {
            throw new Exception('Failed to save the Excel file');
        }
    } else {
        throw new Exception('Invalid request method: ' . $_SERVER['REQUEST_METHOD']);
    }
} catch (Exception $e) {
    error_log("Motor order Excel generation error: " . $e->getMessage()); // 오류 로그 기록
    $response = ['success' => false, 'message' => $e->getMessage()];
}

// JSON 응답 반환
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
