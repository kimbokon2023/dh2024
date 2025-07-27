<?php
// 에러 표시
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // JSON으로 데이터 받기
    $data = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() === JSON_ERROR_NONE && !empty($data)) {
        // PHPExcel 라이브러리 포함
        require '../PHPExcel_1.8.0/Classes/PHPExcel.php';

        // 새로운 PHPExcel 객체 생성
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();

        // 헤더 설정
        $headers = ['입출고일자', '품목코드', '로트번호', '입고수량', '출고수량', '재고', '현장명', '발주처'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $col++;
        }

        // 데이터 채우기
        $rowNumber = 2;
        foreach ($data as $row) {
            $col = 'A';
            foreach ($row as $value) {
                $sheet->setCellValue($col . $rowNumber, $value);
                $col++;
            }
            $rowNumber++;
        }

        // 엑셀 파일 저장
        $filename = 'stock_data_' . date('YmdHis') . '.xlsx';
        $filePath = '../excelsave/' . $filename;
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save($filePath);

        if (file_exists($filePath)) {
            echo json_encode(['success' => true, 'filename' => $filePath]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save the Excel file.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid data format.']);
    }
}
