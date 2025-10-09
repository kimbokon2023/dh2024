<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");

if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php"); 
    exit;
}

// PHPExcel 라이브러리 로드
$excel_lib_path = '../PHPExcel_1.8.0/Classes/PHPExcel.php';
if (!file_exists($excel_lib_path)) {
    die('PHPExcel 라이브러리를 찾을 수 없습니다.');
}

require_once $excel_lib_path;

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();
 
// 품목코드 추출 (기존 로직과 동일)
$items = [];

function safe_json_decode($json) {
    $decoded = json_decode($json, true);
    return is_array($decoded) ? $decoded : [];
}

function generateItemCode($orderItem) {
    $volt = strtolower(isset($orderItem['col1']) ? $orderItem['col1'] : '');
    $wire = strtolower(isset($orderItem['col2']) ? $orderItem['col2'] : '');
    $purpose = strtolower(isset($orderItem['col3']) ? $orderItem['col3'] : '');
    $upweight = strtolower(isset($orderItem['col4']) ? $orderItem['col4'] : '');
    $checkLotNum = strtolower(isset($orderItem['col16']) ? $orderItem['col16'] : '');
    
    $decodedStr = urldecode($checkLotNum);
    $containsBangbum = (strpos($decodedStr, '방범') !== false);    
    $upweight = str_replace(['k', 'K'], '', $upweight);

    if ($purpose === '무기둥모터') {
        $ecountcode = implode('-', array_filter([$volt, $wire, $purpose , $upweight]));
    } else if ($purpose === '방범' && $containsBangbum) {
        $ecountcode = implode('-', array_filter([$volt, $wire, $upweight, $purpose]));
    } else {
        $ecountcode = implode('-', array_filter([$volt, $wire, $upweight]));
    }    
    return $ecountcode;
}

function generateItemCodeForBracket($orderItem) {
    $bracket = isset($orderItem['col6']) ? $orderItem['col6'] : '';
    $flange = isset($orderItem['col7']) ? $orderItem['col7'] : '';
    $ecountcode = $bracket . '-' . $flange;
    return strtolower($ecountcode);
}

function generateItemCodeForAccessory($accessoryItem) {
    $col1 = isset($accessoryItem['col1']) ? $accessoryItem['col1'] : '';
    $volt = '';
    $wire = '';
    $range = '';

    if (strpos($col1, '콘트롤박스') !== false) {
        $ecountcode = '';

        if (preg_match('/(\d+V)/', $col1, $matches) && (strpos($matches[1], '220') === 0 || strpos($matches[1], '380') === 0)) {
            $volt = $matches[1];
            $ecountcode .= $volt . '-';
        }

        if (preg_match('/\[(.*?)\]/', $col1, $matches)) {
            $wire = $matches[1];
            $ecountcode .= $wire . '-';
        }

        $ecountcode .= '콘트롤박스-';

        if (preg_match('/\((.*?)\)/', $col1, $matches)) {
            $range = str_replace(['k', 'K'], '', $matches[1]);
            $range = str_replace('~', '-', $range);
            $ecountcode .= $range . '-';
        }

        if (substr($ecountcode, -1) === '-') {
            $ecountcode = substr($ecountcode, 0, -1);
        }
        return $ecountcode;
    } else {
        return '';
    }
}

// 품목코드 데이터 로드
$sql_fee = "SELECT ecountcode, item, volt, wire, upweight, unit FROM dbchandj.fee WHERE is_deleted IS NULL ORDER BY num DESC";
$sql_fee_controller = "SELECT item FROM dbchandj.fee_controller WHERE is_deleted IS NULL ORDER BY num DESC";
$sql_fee_sub = "SELECT itemcode, item FROM dbchandj.fee_sub WHERE is_deleted IS NULL ORDER BY num DESC";
$sql_fee_fabric = "SELECT itemcode, item FROM dbchandj.fee_fabric WHERE is_deleted IS NULL ORDER BY num DESC";

try {
    $stmh_fee = $pdo->query($sql_fee);
    while ($row = $stmh_fee->fetch(PDO::FETCH_ASSOC)) {
        $item_codes = safe_json_decode($row['ecountcode']);
        foreach ($item_codes as $item_code) {
            if (!empty($item_code)) {
                $items[$item_code] = $item_code;
            }
        }        
    }

    $stmh_fee_controller = $pdo->query($sql_fee_controller);
    while ($row = $stmh_fee_controller->fetch(PDO::FETCH_ASSOC)) {
        $item_codes = safe_json_decode($row['item']);
        foreach ($item_codes as $item_code) {
            if (!empty($item_code)) {
                $items[$item_code] = $item_code;
            }
        }
    }
    
    $stmh_fee_fabric = $pdo->query($sql_fee_fabric);
    while ($row = $stmh_fee_fabric->fetch(PDO::FETCH_ASSOC)) {
        $item_codes = safe_json_decode($row['itemcode']);
        foreach ($item_codes as $item_code) {
            if (!empty($item_code)) {
                $items[$item_code] = $item_code;
            }
        }
    }

    $stmh_fee_sub = $pdo->query($sql_fee_sub);
    while ($row = $stmh_fee_sub->fetch(PDO::FETCH_ASSOC)) {
        $itemcodes = safe_json_decode($row['itemcode']);
        $items_array = safe_json_decode($row['item']);
        foreach ($itemcodes as $index => $item_code) {
            if (isset($items_array[$index]) && !empty($item_code) && !empty($items_array[$index])) {
                if (strpos($items_array[$index], '콘트롤박스') !== false) {
                    $generated_code = generateItemCodeForAccessory([
                        'col1' => $items_array[$index]
                    ]);
                    if (!empty($generated_code)) {
                        $items[$item_code] = $generated_code;
                    }
                }
            }
        }
    }

} catch (PDOException $Exception) {
    echo "오류: " . $Exception->getMessage();
    exit;
}

// 재고 데이터 계산 (기존 로직과 동일)
$stock_data = [];

// material_reg 테이블에서 입출고 데이터 계산
$stock_sql = "SELECT inout_item_code, lotnum, num,  SUM(CASE WHEN CAST(surang AS SIGNED) > 0 THEN CAST(surang AS SIGNED) ELSE 0 END) AS total_in, 
              SUM(CASE WHEN CAST(surang AS SIGNED) < 0 THEN ABS(CAST(surang AS SIGNED)) ELSE 0 END) AS total_out 
              FROM material_reg 
              WHERE (is_deleted IS NULL or is_deleted = '') 
              GROUP BY inout_item_code, lotnum";

try {
    $stock_stmh = $pdo->prepare($stock_sql);    
    $stock_stmh->execute();
    $stock_rows = $stock_stmh->fetchAll(PDO::FETCH_ASSOC);

    foreach ($stock_rows as $stock_row) {
        $item_code = $stock_row['inout_item_code'];        
        $lotnum = strtoupper(trim($stock_row['lotnum']));
        $num = $stock_row['num'];

        if (!isset($stock_data[$item_code])) {
            $stock_data[$item_code] = [];
        }

        if (!isset($stock_data[$item_code][$lotnum])) {
            $stock_data[$item_code][$lotnum] = [
                'item_code' => $item_code,
                'total_in' => 0,
                'total_out' => 0,
                'stock' => 0,
                'num' => $num
            ];
        }

        $stock_data[$item_code][$lotnum]['total_in'] += $stock_row['total_in'];
        $stock_data[$item_code][$lotnum]['total_out'] += $stock_row['total_out'];
        $stock_data[$item_code][$lotnum]['stock'] += $stock_row['total_in'] - $stock_row['total_out'];
    }

} catch (PDOException $Exception) {
    echo "오류: " . $Exception->getMessage();
    exit;
}

// motor 테이블에서 출고 데이터 계산
$motor_sql = "SELECT * FROM motor where (is_deleted IS NULL or is_deleted='') ";
try {
    $stmh = $pdo->prepare($motor_sql);
    $stmh->execute();
    $rows = $stmh->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        $orderlist = isset($row['orderlist']) ? safe_json_decode($row['orderlist']) : [];
        $controllerlist = isset($row['controllerlist']) ? safe_json_decode($row['controllerlist']) : [];
        $fabriclist = isset($row['fabriclist']) ? safe_json_decode($row['fabriclist']) : [];
        $accessorieslist = isset($row['accessorieslist']) ? safe_json_decode($row['accessorieslist']) : [];
        $num = $row['num'];

        foreach ($orderlist as $order) {
            $unit = $order['col5'] ?? '';
            $applyQty = $order['col8'] ?? 0;
            $item_code = generateItemCode($order);            
            $lotnum_motor = strtoupper(trim($order['col13'] ?? ''));
            $lotnum_bracket = strtoupper(trim($order['col14'] ?? ''));

            if ($unit === 'SET' || $unit === '모터단품') {
                $item_code_motor = $lotnum_motor;
                if (!isset($stock_data[$item_code][$item_code_motor])) {
                    $stock_data[$item_code][$item_code_motor] = [
                        'item_code' => $item_code,
                        'total_in' => 0,
                        'total_out' => 0,
                        'stock' => 0,
                        'num' => $num
                    ];
                }
                $stock_data[$item_code][$item_code_motor]['total_out'] += $applyQty;
                $stock_data[$item_code][$item_code_motor]['stock'] -= $applyQty;
            }

            if ($unit === 'SET' || $unit === '브라켓트') {
                $bracket_code = generateItemCodeForBracket($order);   
                $item_code_bracket = $lotnum_bracket;
                if (!isset($stock_data[$bracket_code][$item_code_bracket])) {
                    $stock_data[$bracket_code][$item_code_bracket] = [
                        'item_code' => $bracket_code,
                        'total_in' => 0,
                        'total_out' => 0,
                        'stock' => 0,
                        'num' => $num
                    ];
                }
                $stock_data[$bracket_code][$item_code_bracket]['total_out'] += $applyQty;
                $stock_data[$bracket_code][$item_code_bracket]['stock'] -= $applyQty;
            }
        }

        if (is_array($controllerlist)) {
            foreach ($controllerlist as $controller) {
                $lotnum = strtoupper(trim($controller['col8']));
                $quantity = isset($controller['col3']) ? (int)str_replace(',', '', $controller['col3']) : 0;
                $item_code = $controller['col2'];

                if (!isset($stock_data[$item_code])) {
                    $stock_data[$item_code] = [];
                }
                if (!isset($stock_data[$item_code][$lotnum])) {
                    $stock_data[$item_code][$lotnum] = [
                        'item_code' => $item_code,
                        'total_in' => 0,
                        'total_out' => 0,
                        'stock' => 0,
                        'num' => $num
                    ];
                }
                $stock_data[$item_code][$lotnum]['total_out'] += $quantity;
                $stock_data[$item_code][$lotnum]['stock'] -= $quantity;
            }
        }

        if (is_array($fabriclist)) {           
            foreach ($fabriclist as $fabric) {
                $lotnum = strtoupper(trim($fabric['col10']));
                $quantity = isset($fabric['col5']) ? floatval(str_replace(',', '', $fabric['col5'])) : 0;
                $item_code = $fabric['col1'];

                if (!isset($stock_data[$item_code])) {
                    $stock_data[$item_code] = [];
                }
                if (!isset($stock_data[$item_code][$lotnum])) {
                    $stock_data[$item_code][$lotnum] = [
                        'item_code' => $item_code,
                        'total_in' => 0,
                        'total_out' => 0,
                        'stock' => 0,
                        'num' => $num
                    ];
                }
                $stock_data[$item_code][$lotnum]['total_out'] += $quantity;
                $stock_data[$item_code][$lotnum]['stock'] -= $quantity;
            }
        }
    }

} catch (PDOException $Exception) {
    echo "오류: " . $Exception->getMessage();
    exit;
}

// 품목코드로 정렬
ksort($stock_data);

$grouped_stock_data = [];
foreach ($stock_data as $item_code => $lots) {
    $item_code = isset($items[$item_code]) ? $items[$item_code] : $item_code;
    if (!isset($grouped_stock_data[$item_code])) {
        $grouped_stock_data[$item_code] = [];
    }
    foreach ($lots as $lotnum => $data) {
        $data['lotnum'] = $lotnum;
        $grouped_stock_data[$item_code][] = $data;
    }
}

// Excel 파일 생성
try {
    $objPHPExcel = new PHPExcel();
    
    // 문서 속성 설정
    $objPHPExcel->getProperties()
        ->setCreator("J-TECH Elevator")
        ->setLastModifiedBy($_SESSION['name'] ?? 'User')
        ->setTitle("재고현황 - " . date('Y-m-d'))
        ->setSubject("Material Stock Status")
        ->setDescription("Material stock status exported from J-TECH system")
        ->setKeywords("material stock inventory excel export")
        ->setCategory("Inventory Data");

    // 카테고리별 시트 생성
    $table_categories = [
        'DH-M' => '모터',
        'DH-B' => '브라켓트',
        'DH-C' => '연동제어기',
        'DH-F' => '가스켓, 화이버 원단',
        'DH-W' => '와이어 원단',
        'DH-버미글라스' => '버미글라스',
        '기타 부속' => '부속 자재'
    ];

    $sheet_index = 0;
    $first_sheet = true;

    foreach ($table_categories as $prefix => $category_name) {
        if ($first_sheet) {
            $sheet = $objPHPExcel->getActiveSheet();
            $first_sheet = false;
        } else {
            $sheet = $objPHPExcel->createSheet();
        }
        
        $sheet->setTitle($category_name);
        
        // 헤더 설정
        $headers = [
            'A1' => '품목코드',
            'B1' => '로트번호', 
            'C1' => '재고수량',
            'D1' => '재고합'
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // 헤더 스타일 적용
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => '4472C4']
            ],
            'alignment' => [
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allborders' => [
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                ]
            ]
        ];

        $sheet->getStyle('A1:D1')->applyFromArray($headerStyle);

        // 데이터 입력
        $row = 2;
        $merge_ranges = [];

        if ($prefix === 'DH-M') {
            // 모터의 경우 무선/유선/방범으로 세분화
            $sections = [
                '무선' => [],
                '유선' => [],
                '방범' => []
            ];

            foreach ($grouped_stock_data as $item_code => $lots) {
                // DH-M 관련 로트번호 필터링
                $filtered_lots = array_values(array_filter($lots, function($data) use ($prefix) {
                    $lotnum = $data['lotnum'] ?? '';
                    if (in_array($lotnum, ['DH-M-임시', 'DH-B-임시', 'DH-M초기'])) {
                        return false;
                    }
                    return $data['stock'] != 0 && preg_match("/^$prefix/", $lotnum);
                }));

                if (count($filtered_lots) > 0) {
                    // item_code에 따라 분류
                    if (strpos($item_code, '무선') !== false && strpos($item_code, '방범') === false) {
                        $sections['무선'][$item_code] = $filtered_lots;
                    } elseif (strpos($item_code, '유선') !== false && strpos($item_code, '방범') === false) {
                        $sections['유선'][$item_code] = $filtered_lots;
                    } elseif (strpos($item_code, '방범') !== false) {
                        $sections['방범'][$item_code] = $filtered_lots;
                    }
                }
            }

            // 각 섹션별로 데이터 출력
            foreach ($sections as $section_name => $section_data) {
                if (!empty($section_data)) {
                    // 섹션 헤더 추가
                    $sheet->setCellValue('A' . $row, $section_name);
                    $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray([
                        'font' => ['bold' => true, 'size' => 12],
                        'fill' => [
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => ['rgb' => 'E7E6E6']
                        ],
                        'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER]
                    ]);
                    $sheet->mergeCells('A' . $row . ':D' . $row);
                    $row++;

                    foreach ($section_data as $item_code => $filtered_lots) {
                        $total_stock = array_sum(array_column($filtered_lots, 'stock'));
                        $lot_count = count($filtered_lots);
                        
                        // 첫 번째 행에 품목코드와 재고합 입력
                        $sheet->setCellValue('A' . $row, $item_code);
                        $sheet->setCellValue('D' . $row, $total_stock);
                        
                        // 품목코드와 재고합에 rowspan 적용 (mergeCells)
                        if ($lot_count > 1) {
                            $merge_ranges[] = [
                                'range' => 'A' . $row . ':A' . ($row + $lot_count - 1),
                                'value' => $item_code
                            ];
                            $merge_ranges[] = [
                                'range' => 'D' . $row . ':D' . ($row + $lot_count - 1),
                                'value' => $total_stock
                            ];
                        }
                        
                        // 각 로트번호별 데이터 입력
                        foreach ($filtered_lots as $index => $data) {
                            $sheet->setCellValue('B' . $row, $data['lotnum'] ?? '');
                            $sheet->setCellValue('C' . $row, $data['stock']);
                            $row++;
                        }
                    }
                    $row++; // 섹션 간 공백
                }
            }
        } else {
            // 다른 카테고리들의 기존 로직
            foreach ($grouped_stock_data as $item_code => $lots) {
                // 카테고리별 필터링
                $filtered_lots = array_values(array_filter($lots, function($data) use ($prefix) {
                    $lotnum = $data['lotnum'] ?? '';
                    $item_code = $data['item_code'] ?? '';
                    
                    if (in_array($lotnum, ['DH-M-임시', 'DH-B-임시', 'DH-M초기'])) {
                        return false;
                    }
                    
                    if ($prefix === '기타 부속') {
                        return $data['stock'] != 0 && !preg_match('/^DH-/', $lotnum);
                    } elseif ($prefix === 'DH-F' && $item_code == '가스켓원단-DH-ALF-045(W50)') {
                        return $data['stock'] != 0;
                    } else {
                        return $data['stock'] != 0 && preg_match("/^$prefix/", $lotnum);
                    }
                }));

                if (count($filtered_lots) > 0) {
                    $total_stock = array_sum(array_column($filtered_lots, 'stock'));
                    $lot_count = count($filtered_lots);
                    
                    // 첫 번째 행에 품목코드와 재고합 입력
                    $sheet->setCellValue('A' . $row, $item_code);
                    $sheet->setCellValue('D' . $row, $total_stock);
                    
                    // 품목코드와 재고합에 rowspan 적용 (mergeCells)
                    if ($lot_count > 1) {
                        $merge_ranges[] = [
                            'range' => 'A' . $row . ':A' . ($row + $lot_count - 1),
                            'value' => $item_code
                        ];
                        $merge_ranges[] = [
                            'range' => 'D' . $row . ':D' . ($row + $lot_count - 1),
                            'value' => $total_stock
                        ];
                    }
                    
                    // 각 로트번호별 데이터 입력
                    foreach ($filtered_lots as $index => $data) {
                        $sheet->setCellValue('B' . $row, $data['lotnum'] ?? '');
                        $sheet->setCellValue('C' . $row, $data['stock']);
                        $row++;
                    }
                }
            }
        }

        // mergeCells 적용
        foreach ($merge_ranges as $merge) {
            $sheet->mergeCells($merge['range']);
        }

        // 컬럼 너비 설정
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);

        // 데이터 스타일 적용
        $dataStyle = [
            'borders' => [
                'allborders' => [
                    'style' => PHPExcel_Style_Border::BORDER_THIN
                ]
            ],
            'alignment' => [
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ]
        ];

        if ($row > 2) {
            $sheet->getStyle('A2:D' . ($row - 1))->applyFromArray($dataStyle);
        }

        $sheet_index++;
    }

    // 첫 번째 시트를 활성화
    $objPHPExcel->setActiveSheetIndex(0);

    // 파일명 생성
    $filename = '재고현황_' . date('Y-m-d') . '.xlsx';
    $filename = preg_replace('/[^가-힣a-zA-Z0-9._-]/', '_', $filename);

    // Excel 파일 출력
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Cache-Control: max-age=1');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: cache, must-revalidate');
    header('Pragma: public');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    exit;

} catch (Exception $e) {
    error_log("Excel export error: " . $e->getMessage());
    die("Excel 파일 생성 중 오류가 발생했습니다: " . $e->getMessage());
}
?>
