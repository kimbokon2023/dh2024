<?php
// 에러 표시 설정
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 모든 요청을 로그에 기록
error_log("=== Excel generation request started ===");
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

        // JSON 오류 확인
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON input: ' . json_last_error_msg());
        }

        // 데이터가 비어있는지 확인
        if (empty($data)) {
            throw new Exception('No data received');
        }

        error_log("Data received: " . count($data) . " rows");

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

        // 헤더 설정
        $headers = [
            'number' => '번호',
            'registDate' => '등록일자',
            'content' => '항목',
            'contentSub' => '세부항목',
            'contentDetail' => '상세내용',
            'income' => '수입',
            'expense' => '지출',
            'balance' => '잔액',
            'memo' => '적요'
        ];

        // 헤더를 엑셀에 추가
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            // 셀의 글씨를 굵게 하고 음영을 추가
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getStyle($col . '1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $sheet->getStyle($col . '1')->getFill()->getStartColor()->setRGB('D9D9D9');
            $col++;
        }

        // 데이터 채우기
        $rowNumber = 2;
        foreach ($data as $row) {
            $col = 'A';
            foreach ($headers as $key => $header) {
                $value = isset($row[$key]) ? $row[$key] : ''; // 데이터가 있으면 채우고 없으면 공백
                $sheet->setCellValue($col . $rowNumber, $value);
                $col++;
            }
            $rowNumber++;
        }

        // 특정 열의 기본 폭 설정
        $sheet->getColumnDimension('E')->setWidth(45);
        $sheet->getColumnDimension('H')->setWidth(25);
        $sheet->getColumnDimension('I')->setWidth(60);

        // 나머지 열의 폭을 글씨에 맞추기
        foreach (range('A', 'I') as $columnID) {
            if (!in_array($columnID, ['E', 'H','I'])) {
                $sheet->getColumnDimension($columnID)->setWidth(20);
            }
        }

        // 테두리 설정
        $styleArray = [
            'borders' => [
                'allborders' => [
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                ],
            ],
        ];

        $sheet->getStyle('A1:I' . ($rowNumber - 1))->applyFromArray($styleArray);

        // 열별 정렬 설정
        $sheet->getStyle('F2:F' . ($rowNumber - 1))
              ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('G2:G' . ($rowNumber - 1))
              ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('H2:H' . ($rowNumber - 1))
              ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('I2:I' . ($rowNumber - 1))
              ->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        // 파일 저장
        $filename = 'DH모터(금전출납부)_' . date('YmdHis') . '.xlsx';
        $filePath = '../excelsave/' . $filename; // 파일 경로 확인 필요
        
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
    error_log("Excel generation error: " . $e->getMessage()); // 오류 로그 기록
    $response = ['success' => false, 'message' => $e->getMessage()];
}

// JSON 응답 반환
echo json_encode($response);
?>
