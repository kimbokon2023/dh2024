<?php
/**
 * 개선된 fetch_bracket_all.php (rowspan 없이 각 행으로 출력)
 * - 브라켓 품목코드가 예를 들어 "380*180-2-6″" 인 경우, 
 *   "380*180" (베이스 코드)로 검색하여 해당 사이즈의 브라켓 전체의 재고를 보여줍니다.
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

$currentDate = date("Y-m-d");

// 품목코드 추출
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
    
    $upweight = str_replace(['k', 'K'], '', $upweight);

    if ($purpose === '무기둥모터') {
        $ecountcode = implode('-', array_filter([$volt, $wire, $purpose , $upweight]));
    } else {
        $ecountcode = implode('-', array_filter([$volt, $wire, $upweight]));
    }    
    // print ', 모터코드: ' . $ecountcode;
    return $ecountcode;
}

// 품목코드 수정 브라켓은 후렌지 크기까지 포함한것으로 수정 2024/07/25
function generateItemCodeForBracket($orderItem) {
    // print ',브라켓고유 코드 ' . strtolower(isset($orderItem['col6']) ? $orderItem['col6'] : '');
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

function decodeUrlEncodedJson($encodedJson) {
    return json_decode(urldecode($encodedJson), true);
}

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
		$num = $row['num'];

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
                // 모터 처리
				// print_r($item_code);				
                $item_code_motor = $lotnum_motor; // 모터의 아이템 코드 생성 방식 예시
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
                // 브라켓트 처리
				$bracket_code = generateItemCodeForBracket($order);   
				//print_r($bracket_code);				
                $item_code_bracket = $lotnum_bracket; // 브라켓트의 아이템 코드 생성 방식 예시
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
				// col3는 수량, col8은 로트번호를 의미
				$lotnum = strtoupper(trim($controller['col8']));
				$quantity = isset($controller['col3']) ? (int)str_replace(',', '', $controller['col3']) : 0;
				$item_code = $controller['col2']; // 이 부분은 품목코드

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
                // col5는 수량, col10은 로트번호를 의미
                $lotnum = strtoupper(trim($fabric['col10']));
                $quantity = isset($fabric['col5']) ? floatval(str_replace(',', '', $fabric['col5'])) : 0;
                $item_code = $fabric['col1']; // 이 부분은 각 원단의 고유 아이템 코드라고 가정합니다.

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

// 품목코드로 정렬  (브라켓트 후렌지 인치별로 구분됨)
ksort($stock_data);

$grouped_stock_data = [];
foreach ($stock_data as $item_code => $lots) {
    $item_code = isset($items[$item_code]) ? $items[$item_code] : $item_code;
    if (!isset($grouped_stock_data[$item_code])) {
        $grouped_stock_data[$item_code] = [];
    }
    foreach ($lots as $lotnum => $data) {
        $data['lotnum'] = $lotnum; // lotnum을 데이터에 추가
        $grouped_stock_data[$item_code][] = $data;
    }
}


// echo '<pre>';
// print_r($stock_data);
// echo '</pre>';

// 데이터가 '150K' , '1000K' 이렇게 있을때 정렬을 하면, 150K가 먼저 나오게 하고 싶다. 

// 사용자 정의 정렬 함수 정의
function customSortItemCode($a, $b) {
    // -로 분리하기 전에 문자열인지 확인
    if (is_string($a) && is_string($b)) {
        // -로 분리하여 각각 요소 추출
        $partsA = explode('-', $a);
        $partsB = explode('-', $b);

        // -가 두 개 포함된 경우에만 처리
        if (count($partsA) === 3 && count($partsB) === 3) {
            // 1차 정렬: 첫 번째 요소(숫자) 비교
            $numA = (int)$partsA[0];
            $numB = (int)$partsB[0];
            
            if ($numA !== $numB) {
                return $numA - $numB;
            }

            // 2차 정렬: 두 번째 요소(유선/무선) 비교 (한글 비교)
            $strA = $partsA[1];
            $strB = $partsB[1];

            return strcmp($strA, $strB);
        }
    }

    // -가 두 개가 아닌 경우 또는 데이터가 문자열이 아닌 경우: 그대로 반환하여 순서 유지
    return 0;
}

// 사용자 정의 정렬 함수로 정렬 수행
usort($items, 'customSortItemCode');

// 클라이언트에서 전달받은 변수 (예: 전체 브라켓 코드와 주문수량)
$full_itemCode_bracket = isset($_POST['itemCode_bracket']) ? $_POST['itemCode_bracket'] : '';
$item_qty = isset($_POST['item_qty']) ? $_POST['item_qty'] : 0;

// 브라켓 코드에서 베이스 코드 추출 (예: "380*180-2-6″" → "380*180")
$parts = explode('-', $full_itemCode_bracket);
$bracket_base = trim($parts[0]);

// echo '<pre>';
// print_r($grouped_stock_data);
// echo '</pre>';
// echo '<pre>';
// print_r($bracket_base);
// echo '</pre>';

// echo '<pre>';
// print_r($bracket_base);
// echo '</pre>';

// // 필터링: 임시 로트번호(예: 'DH-B-임시')는 제외하고, 재고(stock = total_in - total_out)가 0보다 큰 경우만 저장
// $filtered_rows = [];
// $total_overall_stock = 0;
// foreach ($items as $row) {
    // $lotnum = strtoupper(trim($row['lotnum']));
    // // 임시 로트번호 등 제외할 값을 필요에 따라 추가할 수 있음
    // if (in_array($lotnum, ['DH-B-임시'])) {
        // continue;
    // }
    // $total_in  = (int)$row['total_in'];
    // $total_out = 0 ;
    // $stock = $total_in - $total_out;
    // if ($stock > 0) {
        // $filtered_rows[] = [
            // 'item_code' => $full_itemCode_bracket,  // 베이스 코드로 표시
            // 'lotnum'    => $lotnum,
            // 'stock'     => $stock
        // ];
        // $total_overall_stock += $stock;
    // }
// }

// // HTML 테이블의 tbody 내부에 들어갈 각 tr 요소 생성 (각 행별 개별 출력, rowspan 없이)
 $output = "";
// if (count($filtered_rows) > 0) {
    // foreach ($filtered_rows as $data) {
        // $output .= '<tr onclick="updateQty2(this)" data-item-code="' . htmlspecialchars($data['item_code']) . '" data-lotnum="' . htmlspecialchars($data['lotnum']) . '">';
        // $output .= '<td class="text-center">' . htmlspecialchars($data['item_code']) . '</td>';
        // $output .= '<td class="text-center">' . htmlspecialchars($data['lotnum']) . '</td>';
        // $output .= '<td class="text-center">' . number_format($data['stock']) . '</td>';
        // $output .= '<td class="text-center"><input type="number" name="apply_qty2[]" class="form-control text-center" value="" /></td>';
        // $output .= '</tr>';
    // }
// } else {
    // $output .= '<tr><td colspan="4" class="text-center">재고가 있는 브라켓트 목록이 없습니다.</td></tr>';
// }
//echo $output;

  
           
	            $html_output = '';
	            $prefix = 'DH-B';
	            foreach ($grouped_stock_data as $item_code => $lots) {
	                $filtered_lots = array_values(array_filter($lots, function($data) use ($prefix) {
	                    $lotnum = $data['lotnum'] ?? '';
	                    // 로트번호가 "DH-M-임시", "DH-B-임시", "DH-M초기"인 경우는 제외
	                    if (in_array($lotnum, ['DH-M-임시', 'DH-B-임시', 'DH-M초기'])) {
	                        return false;
	                    }
	                    if ($prefix === '기타 부속') {
	                        return $data['stock'] != 0 && !preg_match('/^DH-/', $lotnum);
	                    } elseif ($prefix === 'DH-F') {
	                        return $data['stock'] != 0 && (
	                            preg_match('/^DH-F/', $lotnum) ||
	                            preg_match('/^DH-W/', $lotnum) ||
	                            preg_match('/^DH-AL/', $lotnum) ||
	                            preg_match('/^DH-버미글라스/', $lotnum)
	                        );
	                    } else {
	                        return $data['stock'] != 0 && preg_match("/^$prefix/", $lotnum);
	                    }
	                }));
	        
	                $first_row = true;
	                    foreach ($filtered_lots as $index => $data) {
	                        $html_output .= '<tr onclick="updateQty2(this)" data-item-code="' . htmlspecialchars($data['item_code']) . '" data-lotnum="' . htmlspecialchars($data['lotnum'] ?? '') . '">';
	                        if ($first_row) {
	                            $html_output .= '<td class="text-center" >' . htmlspecialchars($data['item_code']) . '</td>';
	                        }
	                        $html_output .= '<td class="text-start">' . htmlspecialchars($data['lotnum'] ?? '') . '</td>';
	                        $html_output .= '<td class="text-end">' . ($data['stock'] != 0 && $data['stock'] !== '' ? number_format($data['stock']) : htmlspecialchars($data['stock'])) . '</td>';	                        
							$html_output .= '<td class="text-center"> <input type="number" name="apply_qty2[]" class="form-control text-center" value="" /></td>';
	                        $html_output .= '</tr>';
	                    }
	                
	            }
	            
	            echo $html_output;  

?>
