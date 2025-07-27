<?php

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

        // 헤더 설정 (컬럼명에 맞게)
        $headers = [
            'model'         => '모델',
            'purchaseQty'   => '구매수량',
            'unitPrice'     => '단가',
            'amount'        => '금액',
            'note'          => '비고',
            'inDate1'       => '1차 입고일',
            'inQty1'        => '1차 입고수량',
            'inDate2'       => '2차 입고일',
            'inQty2'        => '2차 입고수량',
            'inDate3'       => '3차 입고일',
            'inQty3'        => '3차 입고수량',
            'totalPurchase' => '구매수량합',
            'totalInQty'    => '입고합',
            'diff'          => '구매입고차이',
            'status'        => '상태'
        ];

        // 헤더를 엑셀에 추가 및 열 너비 설정 (헤더 문자열 길이에 기반)
		$col = 'A';
		foreach ($headers as $header) {
			$sheet->setCellValue($col . '1', $header);
			// 헤더 스타일 설정
			$sheet->getStyle($col . '1')->getFont()->setBold(true);
			$sheet->getStyle($col . '1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$sheet->getStyle($col . '1')->getFill()->getStartColor()->setRGB('D9D9D9');
			
			// 헤더 문자열 길이에 따른 폭 계산
			$textLength = strlen($header);
			$width = $textLength * 1.2;
			if ($width < 30) {
				$width = 30;
			} elseif ($width > 80) {
				$width = 80;
			}
			$sheet->getColumnDimension($col)->setWidth($width);
			
			$col++;
		}


        // 데이터 채우기 (행 번호 2부터 시작)
        $rowNumber = 2;
        foreach ($data as $row) {
            $col = 'A';
            foreach ($headers as $key => $header) {
                $value = isset($row[$key]) ? $row[$key] : ''; // 값이 있으면 채우고 없으면 빈 문자열
                $sheet->setCellValue($col . $rowNumber, $value);
                $col++;
            }
            $rowNumber++;
        }
		
        // 기본 열 너비 설정
        foreach (range('A', 'O') as $columnID) { // A부터 O까지 (15열)
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        
        // 테두리 설정
        $styleArray = [
            'borders' => [
                'allborders' => [
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A1:O' . ($rowNumber - 1))->applyFromArray($styleArray);

        // 파일 저장
        $filename = 'DH(중국발주서)_' . date('YmdHis') . '.xlsx';
        $filePath = '../excelsave/' . $filename; // 파일 저장 경로
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

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
