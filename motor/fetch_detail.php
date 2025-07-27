<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

$item_code = $_POST['item_code'];
$lotnum = strtoupper(trim($_POST['lotnum']));

// URL 인코딩된 JSON 데이터를 디코딩하는 함수
function decodeUrlEncodedJson($encodedJson) {
    return json_decode(urldecode($encodedJson), true);
}

function safe_json_decode($json) {
    $decoded = json_decode($json, true);
    return is_array($decoded) ? $decoded : [];
}

function generateItemCode($orderItem) {
    $volt = strtolower(isset($orderItem['col1']) ? $orderItem['col1'] : '');
    $wire = strtolower(isset($orderItem['col2']) ? $orderItem['col2'] : '');
    $purpose = strtolower(isset($orderItem['col3']) ? $orderItem['col3'] : '');
    $upweight = strtolower(isset($orderItem['col4']) ? $orderItem['col4'] : '');
    $checkLotNum = strtolower(isset($orderItem['col16']) ? $orderItem['col16'] : ''); // 모터의 품목코드가 저장된 것입니다. 220-유선-400-방범 형태
	
	// 1단계: URL 디코딩
	$decodedStr = urldecode($checkLotNum);
	// echo '<br>';
	// echo $checkLotNum;
	
	// "방범"이라는 단어가 포함되어 있는지 확인
	// 품목코드가 없으니, 용도에서 찾는다.
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

// 품목코드 수정 브라켓은 후렌지 크기까지 포함한것으로 수정 2024/07/25
function generateItemCodeForBracket($orderItem) {
    $bracket = isset($orderItem['col6']) ? $orderItem['col6'] : '';
    $flange = isset($orderItem['col7']) ? $orderItem['col7'] : '';
    $ecountcode = $bracket . '-' . $flange;
    return strtolower($ecountcode);
}

// material_reg 테이블에서 데이터 추출
$sql = "SELECT inoutdate, inout_item_code, item_name, lotnum, 
        SUM(CASE WHEN CAST(surang AS SIGNED) > 0 THEN CAST(surang AS SIGNED) ELSE 0 END) AS total_in, 
        SUM(CASE WHEN CAST(surang AS SIGNED) < 0 THEN ABS(CAST(surang AS SIGNED)) ELSE 0 END) AS total_out, 
        (SUM(CASE WHEN CAST(surang AS SIGNED) > 0 THEN CAST(surang AS SIGNED) ELSE 0 END) - 
        SUM(CASE WHEN CAST(surang AS SIGNED) < 0 THEN ABS(CAST(surang AS SIGNED)) ELSE 0 END)) AS stock,
        '' AS workplacename, '' AS secondord, num
        FROM material_reg 
        WHERE inout_item_code = :item_code AND lotnum = :lotnum AND is_deleted IS NULL
        GROUP BY inoutdate, inout_item_code, item_name, lotnum
        ORDER BY inoutdate ASC"; // 오래된 순으로 정렬

try {
    $stmh = $pdo->prepare($sql);
    $stmh->bindValue(':item_code', $item_code, PDO::PARAM_STR);
    $stmh->bindValue(':lotnum', $lotnum, PDO::PARAM_STR);
    $stmh->execute();
    $rows = $stmh->fetchAll(PDO::FETCH_ASSOC);

    // motor 테이블에서 추가 데이터를 처리
    $motor_sql = "SELECT * FROM motor where is_deleted IS NULL ";
    $stmh_motor = $pdo->prepare($motor_sql);
    $stmh_motor->execute();
    $motor_rows = $stmh_motor->fetchAll(PDO::FETCH_ASSOC);

    $extra_rows = [];

    foreach ($motor_rows as $row) {
        $orderlist = isset($row['orderlist']) ? json_decode($row['orderlist'], true) : [];
        $controllerlist = isset($row['controllerlist']) ? json_decode($row['controllerlist'], true) : [];
        $fabriclist = isset($row['fabriclist']) ? json_decode($row['fabriclist'], true) : [];
        $accessorieslist = isset($row['accessorieslist']) ? json_decode($row['accessorieslist'], true) : [];

        foreach ($orderlist as $order) {
            $unit = $order['col5'] ?? '';
            $applyQty = $order['col8'] ?? 0;
            $generated_item_code = generateItemCode($order);            
            $bracket_code = generateItemCodeForBracket($order);            
            $lotnum_motor = strtoupper(trim($order['col13'] ?? ''));
            $lotnum_bracket = strtoupper(trim($order['col14'] ?? ''));

            if (($unit === 'SET' || $unit === '모터단품') && $generated_item_code === $item_code && $lotnum_motor === $lotnum) {
                // 모터 처리
                $item_code_motor = $lotnum_motor; // 모터의 아이템 코드 생성 방식 예시
                if (!isset($stock_data[$item_code][$item_code_motor])) {
                    $stock_data[$item_code][$item_code_motor] = [
                        'item_code' => $item_code,
                        'total_in' => 0,
                        'total_out' => 0,
                        'stock' => 0
                    ];
                }
                $stock_data[$item_code][$item_code_motor]['total_out'] += $applyQty;
                $stock_data[$item_code][$item_code_motor]['stock'] -= $applyQty;

                // extra_rows 배열에 추가
                $extra_rows[] = [
                    'inoutdate' => $row['deadline'],
                    'inout_item_code' => $item_code,
                    'item_name' => $order['col1'], // Assuming col1 is item_name
                    'lotnum' => $lotnum_motor,
                    'total_in' => 0,
                    'total_out' => $applyQty,
                    'stock' => -$applyQty,
                    'workplacename' => $row['workplacename'],
                    'secondord' => $row['secondord'],
                    'num' => $row['num'] // num 값을 추가
                ];
            }

            if (($unit === 'SET' || $unit === '브라켓트') && $bracket_code === $item_code && $lotnum_bracket === $lotnum) {
                $item_code_bracket = $lotnum_bracket; // 브라켓트의 아이템 코드 생성 방식 예시
                if (!isset($stock_data[$bracket_code][$item_code_bracket])) {
                    $stock_data[$bracket_code][$item_code_bracket] = [
                        'item_code' => $bracket_code,
                        'total_in' => 0,
                        'total_out' => 0,
                        'stock' => 0
                    ];
                }
                $stock_data[$bracket_code][$item_code_bracket]['total_out'] += $applyQty;
                $stock_data[$bracket_code][$item_code_bracket]['stock'] -= $applyQty;

                // extra_rows 배열에 추가
                $extra_rows[] = [
                    'inoutdate' => $row['deadline'],
                    'inout_item_code' => $bracket_code,
                    'item_name' => $order['col1'], // Assuming col1 is item_name
                    'lotnum' => $lotnum_bracket,
                    'total_in' => 0,
                    'total_out' => $applyQty,
                    'stock' => -$applyQty,
                    'workplacename' => $row['workplacename'],
                    'secondord' => $row['secondord'],
                    'num' => $row['num'] // num 값을 추가
                ];
            }
        }

        if (isset($controllerlist) && is_array($controllerlist) && !empty($controllerlist)) {
            foreach ($controllerlist as $controller) {
                $controller_lotnum = strtoupper(trim($controller['col8']));
                $quantity = isset($controller['col3']) ? (int)str_replace(',', '', $controller['col3']) : 0;
                $controller_item_code = $controller['col2']; // 각 컨트롤러의 고유 아이템 코드라고 가정합니다.

                // 조건을 확인하고 추가 행을 구성
                if ($item_code === $controller_item_code && $lotnum === $controller_lotnum) {
                    $extra_rows[] = [
                        'inoutdate' => $row['deadline'],
                        'inout_item_code' => $item_code,
                        'item_name' => $controller['col2'], // col2가 item_name이라고 가정
                        'lotnum' => $lotnum,
                        'total_in' => 0,
                        'total_out' => $quantity,
                        'stock' => -$quantity,
                        'workplacename' => $row['workplacename'],
                        'secondord' => $row['secondord'],
                        'num' => $row['num'] // num 값을 추가
                    ];
                }
            }
        } else {
            continue;
        }

        if (isset($fabriclist) && is_array($fabriclist) && !empty($fabriclist)) {
            foreach ($fabriclist as $fabric) {
                $fabric_lotnum = strtoupper(trim($fabric['col10']));
                $quantity = isset($fabric['col5']) ? (int)str_replace(',', '', $fabric['col5']) : 0;
                $fabric_item_code = $fabric['col1']; // 각 원단의 고유 아이템 코드라고 가정합니다.

                // 조건을 확인하고 추가 행을 구성
                if ($item_code === $fabric_item_code && $lotnum === $fabric_lotnum) {
                    $extra_rows[] = [
                        'inoutdate' => $row['deadline'],
                        'inout_item_code' => $item_code,
                        'item_name' => $fabric['col1'], // Assuming col2 is item_name
                        'lotnum' => $lotnum,
                        'total_in' => 0,
                        'total_out' => $quantity,
                        'stock' => -$quantity,
                        'workplacename' => $row['workplacename'],
                        'secondord' => $row['secondord'],
                        'num' => $row['num'] // num 값을 추가
                    ];
                }
            }
        } else {
            continue;
        }

        foreach ($accessorieslist as $accessory) {
            if (strpos($accessory['col1'], '콘트롤박스') === false) {
                continue; // '콘트롤박스'가 아닌 경우 제외
            }
            $col6_data = decodeUrlEncodedJson($accessory['col6'] ?? '[]');
            if (is_array($col6_data)) {
                foreach ($col6_data as $key => $value) {
                    $accessory_lotnum = strtoupper(trim($value['로트번호']));
                    if ($key === $item_code && $accessory_lotnum === $lotnum) {
                        $extra_rows[] = [
                            'inoutdate' => $row['deadline'],
                            'inout_item_code' => $key,
                            'item_name' => $accessory['col1'], // Assuming col1 is item_name
                            'lotnum' => $accessory_lotnum,
                            'total_in' => 0,
                            'total_out' => $value['수량'],
                            'stock' => -$value['수량'],
                            'workplacename' => $row['workplacename'],
                            'secondord' => $row['secondord'],
                            'num' => $row['num'] // num 값을 추가
                        ];
                    }
                }
            }
        }
    }

    // 기존 rows와 extra_rows를 병합하고 날짜 순으로 정렬
    $all_rows = array_merge($rows, $extra_rows);
    usort($all_rows, function($a, $b) {
        return strtotime($a['inoutdate']) - strtotime($b['inoutdate']); // 오래된 순으로 정렬
    });

    // 재고 계산을 위해 초기 재고 값 설정
    $current_stock = 0;
    foreach ($all_rows as &$row) {
        $current_stock += $row['total_in'] - $row['total_out'];
        $row['stock'] = $current_stock; // 현재 재고 값 업데이트
    }
    unset($row); // 참조 해제

	// 정렬된 데이터 출력 (최신 데이터가 위로 오도록 역순 출력)
	foreach (array_reverse($all_rows) as $row) {
		$totalIn = $row['total_in'] == 0 ? '' : htmlspecialchars($row['total_in']);
		$totalOut = $row['total_out'] == 0 ? '' : htmlspecialchars($row['total_out']);

        // 입고 데이터를 클릭 시 모달 창을 통해 수정 가능하도록 설정
		if ($totalIn !== '') {
			// For incoming stock data, include 'num' as the unique identifier and display the modal for editing
			echo '<tr data-num="' . htmlspecialchars($row['num']) . '" data-inout-type="in">';
		} else {
            // 출고 데이터를 클릭 시 수주 내역 팝업
            $onclickAttribute = $totalOut !== '' ? ' onclick="redirectToView(' . htmlspecialchars($row['num']) . ', \'motor\');"' : '';
            echo '<tr data-item-code="' . htmlspecialchars($row['inout_item_code']) . '" data-lotnum="' . htmlspecialchars($row['lotnum']) . '"' . $onclickAttribute . '>';
        }

        $rowClass = $totalIn !== '' ? 'text-primary' : '';

		echo '<td class="text-center ' . $rowClass. '">'.htmlspecialchars($row['inoutdate']).'</td>';
		echo '<td class="text-center ' . $rowClass. '">'.htmlspecialchars($row['inout_item_code']).'</td>';        
		echo '<td class="text-center ' . $rowClass. '">'.htmlspecialchars($row['lotnum']).'</td>';
		echo '<td class="text-center ' . $rowClass . ' fw-bold">' . ($totalIn != 0 && $totalIn !== '' ? number_format($totalIn) : htmlspecialchars($totalIn)) . '</td>';
		echo '<td class="text-center ' . $rowClass . '">' . ($totalOut != 0 && $totalOut !== '' ? number_format($totalOut) : htmlspecialchars($totalOut)) . '</td>';
		echo '<td class="text-center ' . $rowClass . '">' . ($row['stock'] != 0 && $row['stock'] !== '' ? number_format($row['stock']) : htmlspecialchars($row['stock'])) . '</td>';
		echo '<td class="text-center ' . $rowClass. '">'.htmlspecialchars($row['workplacename']).'</td>';
		echo '<td class="text-center ' . $rowClass. '">'.htmlspecialchars($row['secondord']).'</td>';
		echo '</tr>';
	}
} catch (PDOException $Exception) {
    echo "오류: " . $Exception->getMessage();
    exit;
}
?>

<script>
function redirectToView(num, tablename) {
    var url = "write_form.php?mode=view&num=" + num + "&tablename=" + tablename;
    customPopup(url, '수주내역', 1850, 900);
}

</script>
