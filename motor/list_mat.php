<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  
$title_message = 'DH 모터 (로트번호) 재고현황'; 

if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php"); 
    exit;
}       
include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';   
?>

<title> <?=$title_message?> </title>
<link href="css/style.css" rel="stylesheet" >   

<style>
	#viewTable th, td{
		border : 1px #aaaaaa solid ;
	}

</style>
</head>

<body>    
<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php'); ?>   

<?php
$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';  

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
    $checkLotNum = strtolower(isset($orderItem['col16']) ? $orderItem['col16'] : ''); // 모터의 품목코드가 저장된 것입니다. 220-유선-400-방범 형태
	
	// 1단계: URL 디코딩
	// $decodedStr = urldecode($checkLotNum);
	
	// "방범25"와 "방범"을 구분하여 확인
	// $containsBangbum25 = (strpos($decodedStr, '방범25') !== false);
	// $containsBangbum = (strpos($decodedStr, '방범') !== false);

    $containsBangbum25 = '';
    $containsBangbum = '';

    if($purpose === '방범25') {
        $containsBangbum25 = $purpose;
    } else if ($purpose === '방범') {
        $containsBangbum = $purpose;
    } 
	
    $upweight = str_replace(['k', 'K'], '', $upweight);

    if ($purpose === '무기둥모터') {
        $ecountcode = implode('-', array_filter([$volt, $wire, $purpose , $upweight]));
    } else if ($containsBangbum25) {
        // 방범25 모터: checkLotNum(col16)에 '방범25'가 포함된 경우
        // 220-유선-300-방범25 형태로 생성
        $ecountcode = implode('-', array_filter([$volt, $wire, $upweight, '방범25']));
    } else if ($containsBangbum) {
        // 방범 모터: checkLotNum(col16)에 '방범'이 포함된 경우 (방범25 제외)
        // 220-유선-300-방범 형태로 생성
        $ecountcode = implode('-', array_filter([$volt, $wire, $upweight, '방범']));
    } else {
        $ecountcode = implode('-', array_filter([$volt, $wire, $upweight]));
    }    
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
$empty_lotnum_orders = []; // 로트번호가 비어있는 주문 목록

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
        $orderlist = isset($row['orderlist']) ? safe_json_decode($row['orderlist']) : []; // 모터 출고 데이터
        $controllerlist = isset($row['controllerlist']) ? safe_json_decode($row['controllerlist']) : []; // 연동제어기 출고 데이터
        $fabriclist = isset($row['fabriclist']) ? safe_json_decode($row['fabriclist']) : []; // 원단 출고 데이터
        $accessorieslist = isset($row['accessorieslist']) ? safe_json_decode($row['accessorieslist']) : []; // 부속 자재 출고 데이터
		$num = $row['num'];

        foreach ($orderlist as $order) {
            $unit = $order['col5'] ?? '';
            $applyQty = $order['col8'] ?? 0;
            $item_code = generateItemCode($order);
            $lotnum_motor = strtoupper(trim($order['col13'] ?? ''));
            $lotnum_bracket = strtoupper(trim($order['col14'] ?? ''));

            if ($unit === 'SET' || $unit === '모터단품') {
                // 모터 처리
                $item_code_motor = $lotnum_motor;
                
                // lotnum이 비어있는 경우 경고 목록에 추가
                if (empty($item_code_motor) || trim($item_code_motor) === '') {
                    $empty_lotnum_orders[] = [
                        'num' => $num,
                        'item_code' => $item_code,
                        'quantity' => $applyQty,
                        'unit' => $unit,
                        'workplacename' => $row['workplacename'] ?? '',
                        'secondord' => $row['secondord'] ?? '',
                        'deadline' => $row['deadline'] ?? ''
                    ];
                    continue;
                }
                
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

?>

<form id="board_form" name="board_form" method="post" action="list_mat.php?mode=search">      

<input type="hidden" id="itemCode" name="itemCode" value="<?= isset($itemCode) ? $itemCode : '' ?>">
<input type="hidden" id="lotnum" name="lotnum" value="<?= isset($lotnum) ? $lotnum : '' ?>">

<div class="container-fluid">     
 <div class="d-flex  p-1 justify-content-center align-items-center ">     
  <div class="card mb-2 mt-1 text-center justify-content-center  w-75">  
    <div class="card-body">
    <div class="d-flex  p-1 justify-content-center align-items-center ">         
	   <h5>  <?=$title_message?> </h5>  &nbsp;&nbsp;&nbsp;   
       <small class="ms-5 text-muted"> 품목별 현재 재고현황 (해당품목 클릭시 입고일자, 수량 및 출고현장 확인 가능) </small>  
		 <button type="button" class="btn btn-dark btn-sm mx-2" onclick="location.reload();"> <i class="bi bi-arrow-clockwise"></i> </button>                     
           <button type="button" class="btn btn-dark btn-sm mx-2" id="showlist" > <i class="bi bi-bar-chart-line-fill"></i> 출고통계 </button>
           <button type="button" class="btn btn-success btn-sm" id="exportExcel" > <i class="bi bi-file-earmark-excel"></i> Excel 변환 </button>                               
   </div>
   </div>
</div> 
</div>
</div>

<?php if (!empty($empty_lotnum_orders)): ?>
<div class="container-fluid mb-3">
    <div class="alert alert-danger" role="alert">
        <h5 class="alert-heading"><i class="bi bi-exclamation-triangle-fill"></i> 로트번호 미입력 주문 (재고 처리 불가)</h5>
        <p>아래 주문들은 로트번호(col13)가 입력되지 않아 재고 처리가 되지 않습니다. 수주 내역에서 로트번호를 입력해주세요.</p>
        <hr>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead class="table-warning">
                    <tr>
                        <th class="text-center">주문번호</th>
                        <th class="text-center">현장명</th>
                        <th class="text-center">발주처</th>
                        <th class="text-center">납기일</th>
                        <th class="text-center">품목코드</th>
                        <th class="text-center">단위</th>
                        <th class="text-center">수량</th>
                        <th class="text-center">작업</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($empty_lotnum_orders as $empty_order): ?>
                    <tr>
                        <td class="text-center"><?= htmlspecialchars($empty_order['num']) ?></td>
                        <td class="text-center"><?= htmlspecialchars($empty_order['workplacename']) ?></td>
                        <td class="text-center"><?= htmlspecialchars($empty_order['secondord']) ?></td>
                        <td class="text-center"><?= htmlspecialchars($empty_order['deadline']) ?></td>
                        <td class="text-center"><?= htmlspecialchars($empty_order['item_code']) ?></td>
                        <td class="text-center"><?= htmlspecialchars($empty_order['unit']) ?></td>
                        <td class="text-center"><?= htmlspecialchars($empty_order['quantity']) ?></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-primary" onclick="handleRowClick(<?= $empty_order['num'] ?>)">
                                <i class="bi bi-pencil-fill"></i> 수정
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="container-fluid">   
    <div class="row justify-content-center text-center">
<div class="container-fluid">   
    <div class="row justify-content-center text-center">
<?php	
	// echo '<pre>';
	// print_r($grouped_stock_data);
	// echo '</pre>';
	
	$table_categories = [
	    'DH-M' => '모터',
	    'DH-B' => '브라켓트',
	    'DH-C' => '연동제어기',
	    'DH-F' => '가스켓, 화이버 원단',
	    'DH-W' => '와이어 원단',
	    'DH-버미글라스' => '버미글라스',
	    '기타 부속' => '부속 자재'
	];
	$columns = [
	    ['DH-M'],
	    ['DH-B'],
	    ['DH-C', 'DH-F', 'DH-W', 'DH-버미글라스', '기타 부속']
	];
	
	foreach ($columns as $column) {
	    echo '<div class="col-sm-4">';
	    foreach ($column as $prefix) {
	        $table_title = $table_categories[$prefix];
	        echo '<div class="card mb-1 mt-1 justify-content-center w-80">';
	        echo '<div class="card-body">';
	        echo '<h4>' . $table_title . '</h4>';
	        
	        // 특별히 DH-M인 경우, 품목코드 내 "무선"과 "유선", "방범" 에 따라 별도 테이블 출력
	        if ($prefix === 'DH-M') {
	            // 무선, 유선, 방범 데이터를 각각 누적할 변수
	            $wireless_html = '';
	            $wired_html    = '';
	            $prevent_html    = ''; // 방범
	            
	            foreach ($grouped_stock_data as $item_code => $lots) {
	                // DH-M 관련 로트번호 필터링
	                $filtered_lots = array_values(array_filter($lots, function($data) use ($prefix) {
	                    $lotnum = $data['lotnum'] ?? '';
	                    // 제외할 로트번호
	                    if (in_array($lotnum, ['DH-M-임시', 'DH-B-임시', 'DH-M초기'])) {
	                        return false;
	                    }
	                    return $data['stock'] != 0 && preg_match("/^$prefix/", $lotnum);
	                }));
	                
	                $total_stock = array_sum(array_column($filtered_lots, 'stock'));
	                $lot_count   = count($filtered_lots);
	                
	                if ($lot_count > 0) {
	                    // item_code에 "무선" 또는 "유선"이 포함되었는지 검사하여 분리
	                    if (strpos($item_code, '무선') !== false && strpos($item_code, '방범') == false ) {
	                        $first_row = true;
	                        foreach ($filtered_lots as $index => $data) {
	                            $wireless_html .= '<tr data-item-code="' . htmlspecialchars($data['item_code']) . '" data-lotnum="' . htmlspecialchars($data['lotnum'] ?? '') . '">';
	                            if ($first_row) {
	                                $wireless_html .= '<td class="text-center" rowspan="' . $lot_count . '">' . htmlspecialchars($data['item_code']) . '</td>';
	                                $first_row = false;
	                            }
	                            $wireless_html .= '<td class="text-start">' . htmlspecialchars($data['lotnum'] ?? '') . '</td>';
	                            $wireless_html .= '<td class="text-end">' . ($data['stock'] != 0 && $data['stock'] !== '' ? number_format($data['stock']) : htmlspecialchars($data['stock'])) . '</td>';
	                            if ($index == 0) {
	                                $wireless_html .= '<td class="text-end fw-bold text-primary" rowspan="' . $lot_count . '">' . ($total_stock != 0 && $total_stock !== '' ? number_format($total_stock) : htmlspecialchars($total_stock)) . '</td>';
	                            }
	                            $wireless_html .= '</tr>';
	                        }
	                    } elseif (strpos($item_code, '유선') !== false  && strpos($item_code, '방범') == false ) {
	                        $first_row = true;
	                        foreach ($filtered_lots as $index => $data) {
	                            $wired_html .= '<tr data-item-code="' . htmlspecialchars($data['item_code']) . '" data-lotnum="' . htmlspecialchars($data['lotnum'] ?? '') . '">';
	                            if ($first_row) {
	                                $wired_html .= '<td class="text-center" rowspan="' . $lot_count . '">' . htmlspecialchars($data['item_code']) . '</td>';
	                                $first_row = false;
	                            }
	                            $wired_html .= '<td class="text-start">' . htmlspecialchars($data['lotnum'] ?? '') . '</td>';
	                            $wired_html .= '<td class="text-end">' . ($data['stock'] != 0 && $data['stock'] !== '' ? number_format($data['stock']) : htmlspecialchars($data['stock'])) . '</td>';
	                            if ($index == 0) {
	                                $wired_html .= '<td class="text-end fw-bold text-primary" rowspan="' . $lot_count . '">' . ($total_stock != 0 && $total_stock !== '' ? number_format($total_stock) : htmlspecialchars($total_stock)) . '</td>';
	                            }
	                            $wired_html .= '</tr>';
	                        }	                    
	                    } elseif (strpos($item_code, '방범') !== false) {
	                        $first_row = true;
	                        foreach ($filtered_lots as $index => $data) {
	                            $prevent_html .= '<tr data-item-code="' . htmlspecialchars($data['item_code']) . '" data-lotnum="' . htmlspecialchars($data['lotnum'] ?? '') . '">';
	                            if ($first_row) {
	                                $prevent_html .= '<td class="text-center" rowspan="' . $lot_count . '">' . htmlspecialchars($data['item_code']) . '</td>';
	                                $first_row = false;
	                            }
	                            $prevent_html .= '<td class="text-start">' . htmlspecialchars($data['lotnum'] ?? '') . '</td>';
	                            $prevent_html .= '<td class="text-end">' . ($data['stock'] != 0 && $data['stock'] !== '' ? number_format($data['stock']) : htmlspecialchars($data['stock'])) . '</td>';
	                            if ($index == 0) {
	                                $prevent_html .= '<td class="text-end fw-bold text-primary" rowspan="' . $lot_count . '">' . ($total_stock != 0 && $total_stock !== '' ? number_format($total_stock) : htmlspecialchars($total_stock)) . '</td>';
	                            }
	                            $prevent_html .= '</tr>';
								}
							}
						}
					}
					
					// 무선 테이블 출력 (데이터가 있는 경우)
					if (!empty($wireless_html)) {
						echo '<span class="badge bg-primary fs-6 mt-3 mb-1">무선</span>';
						echo '<table class="table table-hover" id="viewTable">';
						echo '<thead class="table-primary">';
						echo '<tr>';
						echo '<th class="text-center" style="width:300px;"> 품목코드 </th>';
						echo '<th class="text-center" style="width:200px;"> 로트번호 </th>';
						echo '<th class="text-end" style="width:100px;"> 재고수량 </th>';
						echo '<th class="text-end" style="width:100px;"> 재고합 </th>';     
						echo '</tr>';        
						echo '</thead>';   
						echo '<tbody>';
						echo $wireless_html;
						echo '</tbody></table>';
					}
					
					// 유선 테이블 출력 (데이터가 있는 경우)
					if (!empty($wired_html)) {
						echo '<span class="badge bg-success mt-3 mb-1 fs-6">유선</span>';
						echo '<table class="table table-hover" id="viewTable">';
						echo '<thead class="table-primary">';
						echo '<tr>';
						echo '<th class="text-center" style="width:300px;"> 품목코드 </th>';
						echo '<th class="text-center" style="width:200px;"> 로트번호 </th>';
						echo '<th class="text-end" style="width:100px;"> 재고수량 </th>';
						echo '<th class="text-end" style="width:100px;"> 재고합 </th>';     
						echo '</tr>';        
						echo '</thead>';   
						echo '<tbody>';
						echo $wired_html;
						echo '</tbody></table>';
					}
					// 방범 테이블 출력 (데이터가 있는 경우)
					if (!empty($prevent_html)) {
						echo '<span class="badge bg-info mt-3 mb-1 fs-6">방범</span>';
						echo '<table class="table table-hover" id="viewTable">';
						echo '<thead class="table-primary">';
						echo '<tr>';
						echo '<th class="text-center" style="width:300px;"> 품목코드 </th>';
						echo '<th class="text-center" style="width:200px;"> 로트번호 </th>';
						echo '<th class="text-end" style="width:100px;"> 재고수량 </th>';
						echo '<th class="text-end" style="width:100px;"> 재고합 </th>';     
						echo '</tr>';        
						echo '</thead>';   
						echo '<tbody>';
						echo $prevent_html;
						echo '</tbody></table>';
					}
					
				} 
				
	        else if ($prefix !== 'DH-M') {
					// DH-M 이외의 경우 기존 로직 그대로 진행 (브라켓, 기타부속, 원단 표현)
					echo '<table class="table table-hover" id="viewTable">';
					echo '<thead class="table-primary">';
					echo '<tr>';
					echo '<th class="text-center" style="width:300px;"> 품목코드 </th>';
					echo '<th class="text-center" style="width:200px;"> 로트번호 </th>';
					echo '<th class="text-end" style="width:100px;"> 재고수량 </th>';
					echo '<th class="text-end" style="width:100px;"> 재고합 </th>';     
					echo '</tr>';        
					echo '</thead>';   
					echo '<tbody>';
					
					$html_output = '';
	            
					foreach ($grouped_stock_data as $item_code => $lots) {
						$filtered_lots = array_values(array_filter($lots, function($data) use ($prefix) {
							$lotnum = $data['lotnum'] ?? '';
							$item_code = $data['item_code'] ?? '';
							
						// echo '<pre>';
						// print_r($itemcode);
						// echo '</pre>';
						
	                    // 로트번호가 "DH-M-임시", "DH-B-임시", "DH-M초기"인 경우는 제외
	                    if (in_array($lotnum, ['DH-M-임시', 'DH-B-임시', 'DH-M초기'])) {
	                        return false;
	                    }
	                    if ($prefix === '기타 부속') {
	                        return $data['stock'] != 0 && !preg_match('/^DH-/', $lotnum);
	                    } elseif ( $prefix === 'DH-F' && $item_code == '가스켓원단-DH-ALF-045(W50)'  ) {
	                        return $data['stock'] != 0 ;
	                    // } elseif ( $prefix === 'DH-ALW' || $prefix === 'DH-AW' ||  $prefix === 'DH-ALV' || $prefix === 'DH-버미글라스' ) {
	                        // return $data['stock'] != 0 && (	                            
	                            // preg_match('/^DH-ALW/', $lotnum) ||	                            
	                            // preg_match('/^DH-AL/', $lotnum) ||
	                            // preg_match('/^DH-ALV/', $lotnum) ||
	                            // preg_match('/^DH-버미글라스/', $lotnum)
	                        // );
	                    } else {
	                        return $data['stock'] != 0 && preg_match("/^$prefix/", $lotnum);
	                    }
	                }));
	        
	                $total_stock = array_sum(array_column($filtered_lots, 'stock'));
	                $lot_count   = count($filtered_lots);
	        
	                if ($lot_count > 0) {
	                    $first_row = true;
	                    foreach ($filtered_lots as $index => $data) {
	                        $html_output .= '<tr data-item-code="' . htmlspecialchars($data['item_code']) . '" data-lotnum="' . htmlspecialchars($data['lotnum'] ?? '') . '">';
	                        if ($first_row) {
	                            $html_output .= '<td class="text-center" rowspan="' . $lot_count . '">' . htmlspecialchars($data['item_code']) . '</td>';
	                            $first_row = false;
	                        }
	                        $html_output .= '<td class="text-start">' . htmlspecialchars($data['lotnum'] ?? '') . '</td>';
	                        $html_output .= '<td class="text-end">' . ($data['stock'] != 0 && $data['stock'] !== '' ? number_format($data['stock']) : htmlspecialchars($data['stock'])) . '</td>';
	                        if ($index == 0) { // 첫번째 행에 총 재고량 표시
	                            $html_output .= '<td class="text-end fw-bold text-primary" rowspan="' . $lot_count . '">' . ($total_stock != 0 && $total_stock !== '' ? number_format($total_stock) : htmlspecialchars($total_stock)) . '</td>';
	                        }
	                        $html_output .= '</tr>';
	                    }
	                }
	            }
	            
	            echo $html_output;
	            echo '</tbody></table>';
	        }
	        
	        echo '</div></div>';
	    }
	    echo '</div>';
	}
?>

    </div>
</div>

</div>

<!-- 품목코드 Modal -->
<div class="container-fluid justify-content-center align-items-center">  
    <div id="detailModal" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-full" role="document">
            <div class="modal-content">
                <div class="modal-header">          
                    <h2 class="modal-title">품목 코드 및 로트 번호 상세 내역</h2>                    					
                    <button type="button" class="btn btn-success downloadExcel">
                       엑셀 다운로드
                    </button>
					<button type="button" class="btn btn-outline-dark detailModalClose">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-hover" id="myTable">
                        <thead class="table-primary">
                            <tr>
                                <th class="text-center">입출고일자</th>
                                <th class="text-center">품목코드</th>
                                <th class="text-center">로트번호</th>
                                <th class="text-center">입고수량</th>
                                <th class="text-center">출고수량</th>
                                <th class="text-center">재고</th>
                                <th class="text-center">현장명</th>
                                <th class="text-center">발주처</th>
                            </tr>
                        </thead>
                        <tbody id="detailModalBody">
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">                    
                    <button type="button" class="btn btn-outline-dark btn-sm detailModalClose" data-dismiss="modal">
                        <i class="bi bi-x-lg"></i> 닫기
                    </button>
                </div>            
            </div>
        </div>
    </div>
</div>

<!-- 재고조정 Modal -->  
<div id="stockAdjustmentModal" class="modal fade" tabindex="-1">
	<div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">          
                <h2 class="modal-title">재고 조정</h2>
                <button type="button" class="btn btn-outline-dark modalClose">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="modal-body" id="stockAdjustmentBody">
            </div>
            <div class="modal-footer">                    
                <button type="button" class="btn btn-dark btn-sm saveBtn" data-dismiss="modal">
                    <i class="bi bi-floppy2-fill"></i> 저장
                </button>                 
                <button type="button" class="btn btn-outline-dark btn-sm modalClose" data-dismiss="modal">
                    <i class="bi bi-x-lg"></i> 닫기
                </button>
            </div>            
        </div>  
    </div>
</div>


<div class="container-fluid mt-3 mb-3">
    <? include '../footer_sub.php'; ?>
</div>

<script>
// 페이지 로딩
$(document).ready(function(){    
    var loader = document.getElementById('loadingOverlay');
    loader.style.display = 'none';
});
</script>

<script> 

function SearchEnter(){
    if(event.keyCode == 13){        
        document.getElementById('board_form').submit();
    }
}

function handleRowClick(num) {
    if (num !== '') {
        var link = 'write_form.php?mode=view&num=' + num;
        customPopup(link, '수주내역', 1850, 900);
    }
}

$(document).ready(function(){    
    $("#searchBtn").click(function(){     
        document.getElementById('board_form').submit();
     });       
    $("#showlist").click(function(){        
        var url = "outputstat.php";    
        customPopup(url, '출고통계', 1900, 850);            
     });
     
    $("#exportExcel").click(function(){        
        window.location.href = 'download_excel_stock.php';            
     });        
});
</script>

<script>
$(document).ready(function() {
    // 상세 모달 (detailModal) 열기
    $('#myTable tbody tr, #viewTable tbody tr').on('click', function() {
        var itemCode = $(this).data('item-code');
        var lotnum = $(this).data('lotnum');
		
		$("#itemCode").val(itemCode);
		$("#lotnum").val(lotnum);

        $.ajax({
            url: 'fetch_detail.php',
            method: 'POST',
            data: { item_code: itemCode, lotnum: lotnum },
            success: function(response) {
                $('#detailModalBody').html(response);
                $('#detailModal').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
            }
        });
    });

    // 재고조정 모달 (stockAdjustmentModal) 열기
    $('#detailModalBody').on('click', 'tr', function() {
        var inoutType = $(this).data('inout-type'); // 입고 or 출고 구분

        if (inoutType === 'in') {
            var num = $(this).data('num');
            
            // 기존 데이터 로드
            $.ajax({
                url: '/material_reg/fetch_inout_data.php',
                method: 'POST',
                data: { num: num },
				success: function(response) {
                $('#stockAdjustmentBody').html(response);                
                $('#stockAdjustmentModal').modal('show');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error("Error fetching data: ", textStatus, errorThrown);
                }
            });
        }
    });


    // 모달 닫기 처리
    $('.modalClose').on('click', function() {
        $('#stockAdjustmentModal').modal('hide');
    });

    $('.detailModalClose').on('click', function() {
        $('#detailModal').modal('hide');
    });
	
	
});

function openStockAdjustment() {
    document.getElementById('stockAdjustmentModal').style.display = 'block';
}

// 모달 닫기 함수
function closeStockAdjustment() {
    document.getElementById('stockAdjustmentModal').style.display = 'none';
}
</script>


<script>

$(document).on('click', '.saveBtn', function() {
    var formData = {
        mode: 'update', // Always update since you're modifying existing data
        tablename: 'material_reg', 
        num: $('#num').val(), 
        registedate: $('#registedate').val(),
        inoutdate: $('#inoutdate').val(),
        secondord: $('#secondord').val(),
        inout_item_code: $('#inout_item_code').val(),
        lotnum: $('#lotnum').val(),
        item_name: $('#item_name').val(),
        unitprice: $('#unitprice').val(),
        surang: $('#surang').val(),
        comment: $('#comment').val()
    };

	$.ajax({
		url: '/material_reg/process.php',
		method: 'POST',
		data: formData,
		dataType: 'json',
		success: function(response) {
			console.log(response);			
			Toastify({
				text: "수정완료 ",
				duration: 2000,
				close:true,
				gravity:"top",
				position: "center",
				style: {
					background: "linear-gradient(to right, #00b09b, #96c93d)"
				},
			}).showToast();				
	
		
        $.ajax({
            url: 'fetch_detail.php',
            method: 'POST',
            data: { item_code: $("#itemCode").val() , lotnum: $("#lotnum").val() },
            success: function(response) {
                $('#detailModalBody').html(response);
				$('#stockAdjustmentModal').modal('hide');
                $('#detailModal').modal('show');
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
            }
        });			
			
		},
		error: function(jqXHR, textStatus, errorThrown) {
			console.error("Error saving data: ", textStatus, errorThrown);
			alert('오류가 발생했습니다. 다시 시도해주세요.');
		}
	});
});


$(document).ready(function() {
    // 엑셀 다운로드 버튼 클릭 이벤트
    $('.downloadExcel').on('click', function() {
        var data = [];
        var rows = $('#myTable tbody tr');  // modal-body의 테이블

        // 각 행의 데이터를 수집
        rows.each(function() {
            var rowData = {};
            rowData['입출고일자'] = $(this).find('td').eq(0).text();
            rowData['품목코드'] = $(this).find('td').eq(1).text();
            rowData['로트번호'] = $(this).find('td').eq(2).text();
            rowData['입고수량'] = $(this).find('td').eq(3).text();
            rowData['출고수량'] = $(this).find('td').eq(4).text();
            rowData['재고'] = $(this).find('td').eq(5).text();
            rowData['현장명'] = $(this).find('td').eq(6).text();
            rowData['발주처'] = $(this).find('td').eq(7).text();

            data.push(rowData);
        });

        // 엑셀 파일 생성 요청
        $.ajax({
            url: 'download_excel.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                var res = JSON.parse(response);
                if (res.success) {
                    window.location.href = 'downloadExcel.php?filename=' + encodeURIComponent(res.filename.split('/').pop());
                } else {
                    alert('엑셀 파일 생성에 실패했습니다: ' + res.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("Error: ", textStatus, errorThrown);
                alert('엑셀 파일 생성 중 오류가 발생했습니다.');
            }
        });
    });
});

$(document).ready(function(){
	saveLogData('재고 현황'); 
});

</script>

</body>
</html>