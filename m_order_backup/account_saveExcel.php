<?php
header('Content-Type: application/json; charset=UTF-8');

$response = ['success' => false, 'message' => 'Unknown error'];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // 1) JSON 파싱
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input: ' . json_last_error_msg());
    }

    // 2) 필수 데이터
    $orderDate = isset($data['orderDate']) ? $data['orderDate'] : '';
    $headers   = isset($data['headers']) && is_array($data['headers']) ? $data['headers'] : [];
    $items     = isset($data['items'])   && is_array($data['items'])   ? $data['items']   : [];

    if (empty($headers) || empty($items)) {
        throw new Exception('No headers or items received');
    }

    // 3) '비고' 컬럼 제거
    // $headers = array_values(array_filter($headers, function($h) {
        // return mb_strpos($h, '비고') === false;
    // }));

    // 4) PHPExcel 초기화
    require '../PHPExcel_1.8.0/Classes/PHPExcel.php';
    $objPHPExcel = new PHPExcel();
    $sheet = $objPHPExcel->setActiveSheetIndex(0);

    // 5) 컬럼 및 마지막 열 계산
    $colCount = count($headers);
    $lastCol  = chr(ord('A') + $colCount -1 );

    // 6) 제목 (1행)
    $sheet->mergeCells("A1:{$lastCol}1");
    $sheet->setCellValue('A1', '중국 모터 발주서');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(30);
    $sheet->getStyle('A1')->getAlignment()
        ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
        ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

    // 7) 헤더 (2행)
    $headerRow = 2;
    for ($i = 0; $i < $colCount; $i++) {
        $col = chr(ord('A') + $i);
        $sheet->setCellValue($col . $headerRow, $headers[$i]);
        $sheet->getStyle($col . $headerRow)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'type'       => PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor' => ['rgb' => 'D9D9D9']
            ],
            'alignment' => [
                'wrap'       => true,
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getColumnDimension($col)->setAutoSize(true)->setWidth(30);
    }

    // 8) 데이터 출력 (3행부터), col0~colN 키 사용
    $rowNum = $headerRow + 1;
    foreach ($items as $row) {
        for ($i = 0; $i < $colCount; $i++) {
            $col = chr(ord('A') + $i);
            $key = 'col' . $i;
            $value = isset($row[$key]) ? $row[$key] : '';
            $sheet->setCellValue($col . $rowNum, $value);
            $align = $sheet->getStyle($col . $rowNum)->getAlignment();
            // 우측 정렬: 수량, 단가, 금액
            if (mb_strpos($headers[$i], '수량') !== false || mb_strpos($headers[$i], '단가') !== false || mb_strpos($headers[$i], '금액') !== false) {
                $align->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            } else {
                $align->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            }
            $align->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
        }
        $sheet->getRowDimension($rowNum)->setRowHeight(-1);
        $rowNum++;
    }

    // 9) 금액 컬럼 합계
    $sumCols = [];
    for ($i = 0; $i < $colCount; $i++) {
        if (mb_strpos($headers[$i], '금액') !== false) {
            $sumCols[$i] = 0.0;
        }
    }
    foreach ($items as $row) {
        foreach ($sumCols as $i => &$total) {
            $key = 'col' . $i;
            $val = isset($row[$key]) ? $row[$key] : '0';
            $total += floatval(str_replace(',', '', $val));
        }
    }
    unset($total);

    // 10) 합계 행
    $sumRow = $rowNum;
    $sheet->getStyle("A{$sumRow}:{$lastCol}{$sumRow}")->getFill()
        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
        ->getStartColor()->setRGB('EEEEEE');
    foreach ($sumCols as $i => $total) {
        $col = chr(ord('A') + $i);
        $sheet->setCellValue($col . $sumRow, number_format($total, 2));
        $sheet->getStyle($col . $sumRow)->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT)
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    }

    // 11) 테두리 적용
    $sheet->getStyle("A1:{$lastCol}{$sumRow}")->applyFromArray([
        'borders' => [
            'allborders' => [
                'style' => PHPExcel_Style_Border::BORDER_THIN,
            ],
        ],
    ]);

    // 12) 파일 저장
    $filename = 'DH(중국발주서_송금액)_' . date('YmdHis') . '.xlsx';
    $filePath = '../excelsave/' . $filename;
    PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007')->save($filePath);
    if (!file_exists($filePath)) {
        throw new Exception('Failed to save the Excel file');
    }

    $response = ['success' => true, 'filename' => $filePath];

} catch (Exception $e) {
    error_log($e->getMessage());
    $response = ['success' => false, 'message' => $e->getMessage()];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
