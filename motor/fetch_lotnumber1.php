<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

$item_code = $_POST['item_code'] ?? '';
$unitsection = $_POST['unit'] ?? '';
$item_qty = isset($_POST['item_qty']) ? $_POST['item_qty'] : 0;
$itemCode_bracket = isset($_POST['itemCode_bracket']) ? $_POST['itemCode_bracket'] : 0;

echo '검색 item_code : ' . $item_code;

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
    $bracket = $orderItem['col6'] ?? '';
    $flange = $orderItem['col7'] ?? '';
    return strtolower($bracket . '-' . $flange);
}

try {
  // Fetch data for motor
    $motor_sql = "SELECT lotnum, 
                   SUM(CASE WHEN CAST(surang AS SIGNED) <> 0 THEN CAST(surang AS SIGNED) ELSE 0 END) AS total_in
            FROM material_reg 
            WHERE inout_item_code = :item_code AND is_deleted IS NULL
            GROUP BY lotnum";

    $stock_stmh = $pdo->prepare($motor_sql);
    $stock_stmh->bindValue(':item_code', $item_code, PDO::PARAM_STR);
    $stock_stmh->execute();
    $stock_rows = $stock_stmh->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch motor data for out quantity
    $motor_out_sql = "SELECT orderlist FROM motor WHERE is_deleted IS NULL";
    $motor_out_stmh = $pdo->prepare($motor_out_sql);
    $motor_out_stmh->execute();
    $motor_out_rows = $motor_out_stmh->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total out quantity for each lotnum
    $motor_out_data = [];
    $bracket_out_data = [];
    foreach ($motor_out_rows as $row) {
        $orderlist = isset($row['orderlist']) ? json_decode($row['orderlist'], true) : [];
        foreach ($orderlist as $order) {  

            $unit = $order['col5'] ?? '';
            $applyQty = $order['col8'] ?? 0;
            $generated_item_code = generateItemCode($order);            
            $bracket_code = generateItemCodeForBracket($order); 
			// echo '$generated_item_code : ' . $generated_item_code . '<br>' ;			
            $lotnum_motor_motor = strtoupper(trim($order['col13'] ?? ''));           		
            $lotnum_motor_bracket = strtoupper(trim($order['col14'] ?? ''));           

            if (($unit == 'SET' || $unit == '모터단품') && $generated_item_code == $item_code ) {                        
                if (!empty($lotnum_motor_motor)) {
					// echo '<br> $lotnum_motor_motor : ' . $lotnum_motor_motor . '<br>' ;	
                    if (!isset($motor_out_data[$lotnum_motor_motor])) {
                        $motor_out_data[$lotnum_motor_motor] = 0;
                    }
                    $motor_out_data[$lotnum_motor_motor] += $applyQty;
                }
            }
            if (($unit == 'SET' || $unit == '브라켓트') && $bracket_code == $itemCode_bracket ) {                        
                if (!empty($lotnum_motor_bracket)) {
                    if (!isset($bracket_out_data[$lotnum_motor_bracket])) {
                        $bracket_out_data[$lotnum_motor_bracket] = 0;
                    }
                    $bracket_out_data[$lotnum_motor_bracket] += $applyQty;
                }
            }
        }
    }
    
    // echo '<pre>';
    // print_r($item_code);
    // echo '</pre>';
    // echo '<pre>';
    // print_r($bracket_out_data);
    // echo '</pre>';

    $motor_output = '';
    $first = true; // 첫 번째 행 체크
	$row_count = 0;
	$tmp_lotnumber = '';	
    foreach ($stock_rows as $stock_row) {
        $lotnum = strtoupper(trim($stock_row['lotnum']));
        $total_in = (int)$stock_row['total_in'];
        // $total_out = (int)$stock_row['total_out'];
        $total_out = isset($motor_out_data[$lotnum]) ? (int)$motor_out_data[$lotnum] : 0;
        $stock = $total_in - $total_out;

        if ($stock != 0) {
            $qty_value = $first ? htmlspecialchars($item_qty) : '';
            $motor_output .= '<tr onclick="updateQty1(this)">' .
                             '<td class="text-center">' . htmlspecialchars($item_code) . '</td>' .
                             '<td class="text-center">' . htmlspecialchars($lotnum) . '</td>' .
                             '<td class="text-center">' . htmlspecialchars($stock) . '</td>' .
                             '<td class="text-center"><input type="number" name="apply_qty1[]" class="form-control text-center" value="' . $qty_value . '" /></td>' .
                             '</tr>';
            $first = false;
			$row_count++;
        }  		
		$tmp_lotnumber = $lotnum;
    }
    // 재고가 0일때는 마지막 로트번호를 표시하게 하는 로직 추가		
	if ($row_count ==0 ) {
		$qty_value = 0 ;
		$stock = 0;
		if($tmp_lotnumber == '')
			  $tmp_lotnumber = 'DH-M-임시';
		$motor_output .= '<tr onclick="updateQty1(this)">' .
						 '<td class="text-center">' . htmlspecialchars($item_code) . '</td>' .
						 '<td class="text-center">' . htmlspecialchars($tmp_lotnumber) . '</td>' .
						 '<td class="text-center">' . htmlspecialchars($stock) . '</td>' .
						 '<td class="text-center"><input type="number" name="apply_qty1[]" class="form-control text-center" value="' . $qty_value . '" /></td>' .
						 '</tr>';
	}		

    // Fetch data for bracket
    $bracket_sql = "SELECT lotnum, 
                   SUM(CASE WHEN CAST(surang AS SIGNED) > 0 THEN CAST(surang AS SIGNED) ELSE 0 END) AS total_in 
            FROM material_reg 
            WHERE inout_item_code = :item_code_bracket AND is_deleted IS NULL
            GROUP BY lotnum";

    $bracket_stmh = $pdo->prepare($bracket_sql);
    $bracket_stmh->bindValue(':item_code_bracket', $itemCode_bracket, PDO::PARAM_STR);
    $bracket_stmh->execute();
    $bracket_rows = $bracket_stmh->fetchAll(PDO::FETCH_ASSOC);

    $bracket_output = '';
	
	// echo '<pre>';
	// print_r($bracket_rows);
	// echo '</pre>';	
	
    $first = true; // 첫 번째 행 체크
	$row_count = 0;
	$tmp_lotnumber = '';
    foreach ($bracket_rows as $bracket_row) {
        $lotnum = strtoupper(trim($bracket_row['lotnum']));
        $total_in = (int)$bracket_row['total_in'];
	    $total_out = isset($bracket_out_data[$lotnum]) ? (int)$bracket_out_data[$lotnum] : 0;        
        $stock = $total_in - $total_out;

        if ($stock !=0 ) {
            $qty_value = $first ? htmlspecialchars($item_qty) : '';
            $bracket_output .= '<tr onclick="updateQty2(this)">' .
                               '<td class="text-center">' . htmlspecialchars($itemCode_bracket) . '</td>' .
                               '<td class="text-center">' . htmlspecialchars($lotnum) . '</td>' .
                               '<td class="text-center">' . htmlspecialchars($stock) . '</td>' .
                               '<td class="text-center"><input type="number" name="apply_qty2[]" class="form-control text-center" value="' . $qty_value . '" /></td>' .
                               '</tr>';
            $first = false;
			$row_count++;
        }  		
		$tmp_lotnumber = $lotnum;
    }
	
    // 재고가 0일때는 마지막 로트번호를 표시하게 하는 로직 추가		
	if ($row_count ==0 ) {
		$qty_value = 0 ;
		if($tmp_lotnumber == '')
			  $tmp_lotnumber = 'DH-B-임시';
		$bracket_output .= '<tr onclick="updateQty2(this)">' .
						   '<td class="text-center">' . htmlspecialchars($itemCode_bracket) . '</td>' .
						   '<td class="text-center">' . htmlspecialchars($tmp_lotnumber) . '</td>' .
						   '<td class="text-center"> 0 </td>' .
						   '<td class="text-center"><input type="number" name="apply_qty2[]" class="form-control text-center" value="' . $qty_value . '" /></td>' .
						   '</tr>';
	}	
    
    if ($unitsection === 'SET') {
        echo '
        <div class="d-flex justify-content-center align-items-center mb-2 mt-2">  
            <h5> <span class="badge bg-primary"> 모터 </span> 로트번호 </h5>			
        </div>            
        <table class="table table-hover">
            <thead class="table-primary">
                <tr>                            
                    <th class="text-center" style="width:35%;">품목코드</th>                            
                    <th class="text-center" style="width:35%;">로트번호</th>
                    <th class="text-center" style="width:15%;">재고</th>
                    <th class="text-center" style="width:15%;">주문수량</th>
                </tr>
            </thead>
            <tbody id="lotModalBody">' .
            $motor_output .
            '</tbody>
        </table>
        <div class="d-flex justify-content-center align-items-center mb-2 mt-2">  
        </div>
        <div class="d-flex justify-content-center align-items-center mb-2">  
            <h5> <span class="badge bg-danger"> 브라켓트 </span>   로트번호 </h5>
			<div class="form-check form-switch mx-3">
				<input class="form-check-input mx-2 anotherBKStockChk" type="checkbox" id="anotherBKStockChk">
				<label class="form-check-label mx-2 text-success fw-bold" for="anotherBKStockChk">재고 전체</label>
			</div>		
        </div>                            
        <table class="table table-hover">
            <thead class="table-primary">
                <tr>                            
                    <th class="text-center" style="width:35%;">품목코드</th>                            
                    <th class="text-center" style="width:35%;">로트번호</th>
                    <th class="text-center" style="width:15%;">재고</th>
                    <th class="text-center" style="width:15%;">주문수량</th>
                </tr>
            </thead>
            <tbody id="lotModalBody_bracket">' .
            $bracket_output .
            '</tbody>
        </table>';
    } elseif ($unitsection === '모터단품') {
        echo '
        <div class="d-flex justify-content-center align-items-center mb-2 mt-2">  
            <h5> 모터 로트번호 </h5>
        </div>            
        <table class="table table-hover">
            <thead class="table-primary">
                <tr>                            
                    <th class="text-center" style="width:35%;">품목코드</th>                            
                    <th class="text-center" style="width:35%;">로트번호</th>
                    <th class="text-center" style="width:15%;">재고</th>
                    <th class="text-center" style="width:15%;">주문수량</th>
                </tr>
            </thead>
            <tbody id="lotModalBody">' .
            $motor_output .
            '</tbody>
        </table>';
    } elseif ($unitsection === '브라켓트') {
        echo '
        <div class="d-flex justify-content-center align-items-center mb-2 mt-2">  
            <h5> 브라켓트 로트번호 </h5>			
			<div class="form-check form-switch mx-3">
				<input class="form-check-input mx-2 anotherBKStockChk" type="checkbox" id="anotherBKStockChk">
				<label class="form-check-label mx-2 text-success fw-bold" for="anotherBKStockChk">재고 전체</label>
			</div>		
        </div>                            
        <table class="table table-hover">
            <thead class="table-primary">
                <tr>                            
                    <th class="text-center" style="width:35%;">품목코드</th>                            
                    <th class="text-center" style="width:35%;">로트번호</th>
                    <th class="text-center" style="width:15%;">재고</th>
                    <th class="text-center" style="width:15%;">주문수량</th>
                </tr>
            </thead>
            <tbody id="lotModalBody_bracket">' .
            $bracket_output .
            '</tbody>
        </table>';
    }

} catch (PDOException $Exception) {
    ob_end_clean();
    echo '<div class="alert alert-danger" role="alert">오류: ' . htmlspecialchars($Exception->getMessage()) . '</div>';
    exit;
}
?>

<script>
function updateQty1(clickedRow) {
    const rows = document.querySelectorAll('tr');
    rows.forEach(row => {
        const input = row.querySelector('input[name="apply_qty1[]"]');
        if (input) {
            if (row === clickedRow) {
                input.value = <?= json_encode($item_qty) ?>;
            } else {
                input.value = '';
            }
        }
    });
}

function updateQty2(clickedRow) {
    const rows = document.querySelectorAll('tr');
    rows.forEach(row => {
        const input = row.querySelector('input[name="apply_qty2[]"]');
        if (input) {
            if (row === clickedRow) {
                input.value = <?= json_encode($item_qty) ?>;
            } else {
                input.value = '';
            }
        }
    });
}
</script>
