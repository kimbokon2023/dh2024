<?php
// 에러 표시 설정
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json'); // JSON 응답 설정

$response = ['success' => false, 'message' => 'Unknown error'];

try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // 요청에서 JSON 데이터 가져오기
        $data = json_decode(file_get_contents('php://input'), true);

        // JSON 오류 확인
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON input: ' . json_last_error_msg());
        }

        // 데이터가 비어있는지 확인
        if (empty($data)) {
            throw new Exception('No data received');
        }

        // PHPExcel 라이브러리 포함
        require '../PHPExcel_1.8.0/Classes/PHPExcel.php';

        // 새로운 PHPExcel 객체 생성
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();

        // 헤더 설정 (한글로)
        $headers = [
            'delivery' => '배송사',
            'postalCode' => '우편번호',
            'office' => '도착영업소',
            'receiver' => '받는분',
            'phone' => '전화번호',
            'otherPhone' => '기타 전화번호',
            'address' => '주소',
            'address1' => '상세주소',
            'item' => '품목',
            'quantity' => '수량',
            'packaging' => '포장 상태',
            'unitPrice' => '개별단가(만원)',
            'shippingType' => '배송구분',
            'freight' => '운임',
            'freight1' => '별도운임',
            'freight2' => '기타운임'
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
		
		// 셀박스 범위가 되는 행수
		$boxrowNumber = $rowNumber;
        
        // 특정 열의 기본 폭 설정
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(35);

        // 나머지 열의 폭을 글씨에 맞추기
        foreach (range('A', 'P') as $columnID) {
            if (!in_array($columnID, ['G','D'])) {
                // $sheet->getColumnDimension($columnID)->setAutoSize(true);
                $sheet->getColumnDimension($columnID)->setWidth(15);
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
		
		$col = "P";
				
        $sheet->getStyle('A1:' . $col . ($boxrowNumber - 1))->applyFromArray($styleArray);

        // 파일 저장
        $filename = 'DH모터(경동)_' . date('YmdHis') . '.xlsx';
        $filePath = '../excelsave/' . $filename; // 파일 경로 확인 필요
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save($filePath);

        // 파일이 생성되었는지 확인
        if (file_exists($filePath)) {
            $response = ['success' => true, 'filename' => $filePath];
        } else {
            throw new Exception('Failed to save the Excel file');
        }
    } else {
        throw new Exception('Invalid request method');
    }
} catch (Exception $e) {
    error_log($e->getMessage()); // 오류 로그 기록
    $response = ['success' => false, 'message' => $e->getMessage()];
}

// JSON 응답 반환
echo json_encode($response);
?>
