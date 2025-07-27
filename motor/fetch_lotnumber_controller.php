<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

$item_code = $_POST['item_code'];
$item_qty = isset($_POST['item_qty']) ? $_POST['item_qty'] : 0;

// URL 인코딩된 JSON 데이터를 디코딩하는 함수
function decodeUrlEncodedJson($encodedJson) {
    return json_decode(urldecode($encodedJson), true);
}

try {
    
    $stock_sql = "SELECT lotnum, 
                   SUM(CASE WHEN CAST(surang AS SIGNED) <> 0 THEN CAST(surang AS SIGNED) ELSE 0 END) AS total_in  
            FROM material_reg 
            WHERE inout_item_code = :item_code AND is_deleted IS NULL
            GROUP BY lotnum";

    $stock_stmh = $pdo->prepare($stock_sql);
    $stock_stmh->bindValue(':item_code', $item_code, PDO::PARAM_STR);
    $stock_stmh->execute();
    $stock_rows = $stock_stmh->fetchAll(PDO::FETCH_ASSOC);

    $stock_data = [];

    foreach ($stock_rows as $stock_row) {
        $lotnum = strtoupper(trim($stock_row['lotnum']));
        $total_in = (int)$stock_row['total_in'];

        if (!isset($stock_data[$lotnum])) {
            $stock_data[$lotnum] = [
                'total_in' => 0,
                'total_out' => 0,
                'stock' => 0
            ];
        }

        $stock_data[$lotnum]['total_in'] += $total_in;
        $stock_data[$lotnum]['stock'] += $total_in;
    }


	// echo '<pre>';
	// print_r($stock_data);
	// echo '</pre>';

    // Fetch data from motor table
	$motor_out_data = [];
    $motor_sql = "SELECT * FROM motor WHERE is_deleted IS NULL";
    $stmh_motor = $pdo->prepare($motor_sql);
    $stmh_motor->execute();
    $motor_rows = $stmh_motor->fetchAll(PDO::FETCH_ASSOC);

    foreach ($motor_rows as $row) {
        $controllerlist = isset($row['controllerlist']) ? json_decode($row['controllerlist'], true) : [];
        foreach ($controllerlist as $order) {                    
            $applyQty = $order['col3'] ?? 0;
            $generated_item_code = $order['col2'] ?? 0;            
            $lotnum_controller = strtoupper(trim($order['col8'] ?? ''));              

            if ( $generated_item_code === $item_code ) {                        
                if (!empty($lotnum_controller)) {
                    if (!isset($motor_out_data[$lotnum_controller])) {
                        $motor_out_data[$lotnum_controller] = 0;
                    }
                    $motor_out_data[$lotnum_controller] += $applyQty;
                }
            }
        }	
    }
	
	// echo '<pre>';
	// // print_r($controllerlist);
	// // print_r($motor_out_data);
	// echo '</pre>';	

    $motor_output = '';
    $first = true; // 첫 번째 행 체크
	$row_count = 0;
	$tmp_lotnumber = '';	
    foreach ($stock_rows as $stock_row) {
        $lotnum = strtoupper(trim($stock_row['lotnum']));
        $total_in = (int)$stock_row['total_in'];
        $total_out = isset($motor_out_data[$lotnum]) ? (int)$motor_out_data[$lotnum] : 0;
        $stock = $total_in - $total_out;

        if ($stock != 0) {
            $qty_value = $first ? htmlspecialchars($item_qty) : '';
            $motor_output .= '<tr onclick="updateQty(this)">' .
                             '<td class="text-center">' . htmlspecialchars($item_code) . '</td>' .
                             '<td class="text-center">' . htmlspecialchars($lotnum) . '</td>' .
                             '<td class="text-center">' . htmlspecialchars($stock) . '</td>' .
                             '<td class="text-center"><input type="number" name="apply_qty[]" class="form-control text-center" value="' . $qty_value . '" /></td>' .
                             '</tr>';
            $first = false;
			$row_count++;
        }  		
		// $tmp_lotnumber = $lotnum;
    }
    // 재고가 0일때는 마지막 로트번호를 표시하게 하는 로직 추가		
	if ($row_count ==0 ) {
		$qty_value = 0 ;
		if($tmp_lotnumber == '')
			  $tmp_lotnumber = 'DH-C-임시';
		$motor_output .= '<tr onclick="updateQty(this)">' .
                             '<td class="text-center">' . htmlspecialchars($item_code) . '</td>' .
                             '<td class="text-center">' . htmlspecialchars($tmp_lotnumber) . '</td>' .
                             '<td class="text-center">' . htmlspecialchars($stock) . '</td>' .
                             '<td class="text-center"><input type="number" name="apply_qty[]" class="form-control text-center" value="' . $qty_value . '" /></td>' .
                             '</tr>';
	}		
	
        echo '
        <div class="d-flex justify-content-center align-items-center mb-2 mt-2">  
            <h5> <span class="badge bg-success"> 연동제어기 </span> 로트번호 </h5>
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
            <tbody id="controllerlotModalBody">' . $motor_output . '</tbody> </table>
        <div class="d-flex justify-content-center align-items-center mb-2 mt-2">  </div>';

} catch (PDOException $Exception) {
    echo "오류: " . $Exception->getMessage();
    exit;
}
?>


<script>
function updateQty(clickedRow) {
    const rows = document.querySelectorAll('tr');
    rows.forEach(row => {
        const input = row.querySelector('input[name="apply_qty[]"]');
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
