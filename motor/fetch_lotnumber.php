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
    // Step 1: Calculate the total stock for each lot number associated with the given item code

    // Fetch data from material_reg table
    $stock_sql = "SELECT lotnum, 
                   SUM(CASE WHEN CAST(surang AS SIGNED) > 0 THEN CAST(surang AS SIGNED) ELSE 0 END) AS total_in 
            FROM material_reg 
            WHERE inout_item_code = :item_code AND is_deleted IS NULL
            GROUP BY lotnum";

    $stock_stmh = $pdo->prepare($stock_sql);
    $stock_stmh->bindValue(':item_code', $item_code, PDO::PARAM_STR);
    $stock_stmh->execute();
    $stock_rows = $stock_stmh->fetchAll(PDO::FETCH_ASSOC);

    // // 디버그용 출력
    // echo "<pre>material_reg 데이터:\n";
    // print_r($stock_rows);
    // echo "</pre>";

    $stock_data = [];

    foreach ($stock_rows as $stock_row) {
        $lotnum = strtoupper(trim($stock_row['lotnum']));
        $total_in = (int)$stock_row['total_in'];
        $total_out = (int)$stock_row['total_out'];
        $stock = $total_in - $total_out;

        if (!isset($stock_data[$lotnum])) {
            $stock_data[$lotnum] = [
                'total_in' => 0,
                'total_out' => 0,
                'stock' => 0
            ];
        }

        $stock_data[$lotnum]['total_in'] += $total_in;
        $stock_data[$lotnum]['total_out'] += $total_out;
        $stock_data[$lotnum]['stock'] += $stock;
    }

    // Fetch data from motor table
    $motor_sql = "SELECT * FROM motor WHERE is_deleted IS NULL";
    $stmh_motor = $pdo->prepare($motor_sql);
    $stmh_motor->execute();
    $motor_rows = $stmh_motor->fetchAll(PDO::FETCH_ASSOC);

    // // 디버그용 출력
    // echo "<pre>motor 데이터:\n";
    // print_r($motor_rows);
    // echo "</pre>";

    foreach ($motor_rows as $row) {        
        $controllerlist = isset($row['controllerlist']) ? json_decode($row['controllerlist'], true) : [];
        $accessorieslist = isset($row['accessorieslist']) ? json_decode($row['accessorieslist'], true) : [];
        $fabriclist = isset($row['fabriclist']) ? json_decode($row['fabriclist'], true) : [];

        // // 디버그용 출력
        // echo "<pre>현재 motor row 데이터:\n";
        // print_r($row);
        // echo "</pre>";

        foreach ($controllerlist as $controller) {
            $col10_data = decodeUrlEncodedJson($controller['col10'] ?? '[]');
            if (is_array($col10_data)) {
                foreach ($col10_data as $key => $value) {
                    if ($key !== $item_code) continue;
                    $lotnum = strtoupper(trim($value['로트번호']));
                    if (!isset($stock_data[$lotnum])) {
                        $stock_data[$lotnum] = [
                            'total_in' => 0,
                            'total_out' => 0,
                            'stock' => 0
                        ];
                    }
                    $stock_data[$lotnum]['total_out'] += $value['수량'];
                    $stock_data[$lotnum]['stock'] -= $value['수량'];
                }
            }
        }

        foreach ($accessorieslist as $accessory) {
            if (strpos($accessory['col1'], '콘트롤박스') === false) {
                continue; // '콘트롤박스'가 아닌 경우 제외
            }
            $col6_data = decodeUrlEncodedJson($accessory['col6'] ?? '[]');
            if (is_array($col6_data)) {
                foreach ($col6_data as $key => $value) {
                    if ($key !== $item_code) continue;
                    $lotnum = strtoupper(trim($value['로트번호']));
                    if (!isset($stock_data[$lotnum])) {
                        $stock_data[$lotnum] = [
                            'total_in' => 0,
                            'total_out' => 0,
                            'stock' => 0
                        ];
                    }
                    $stock_data[$lotnum]['total_out'] += $value['수량'];
                    $stock_data[$lotnum]['stock'] -= $value['수량'];
                }
            }
        }

        foreach ($fabriclist as $accessory) {            
            $col6_data = decodeUrlEncodedJson($accessory['col12'] ?? '[]');
            if (is_array($col6_data)) {
                foreach ($col6_data as $key => $value) {
                    if ($key !== $item_code) continue;
                    $lotnum = strtoupper(trim($value['로트번호']));
                    if (!isset($stock_data[$lotnum])) {
                        $stock_data[$lotnum] = [
                            'total_in' => 0,
                            'total_out' => 0,
                            'stock' => 0
                        ];
                    }
                    $stock_data[$lotnum]['total_out'] += $value['수량'];
                    $stock_data[$lotnum]['stock'] -= $value['수량'];
                }
            }
        }
    }

    // Step 3: Filter out lot numbers with stock > 0
    $result = [];
    foreach ($stock_data as $lotnum => $data) {
        if ($data['stock'] > 0) {
            $result[] = [
                'lotnum' => $lotnum,
                'stock' => $data['stock']
            ];
        }
    }

    // // 디버그용 출력
    // echo "<pre>최종 stock 데이터:\n";
    // print_r($stock_data);
    // echo "필터링된 결과:\n";
    // print_r($result);
    // echo "</pre>";

    // Step 4: Output the results
    if (!empty($result)) {
        $resultCount = count($result); // $result 배열의 길이
        $index = 0; // 현재 반복의 인덱스

        foreach ($result as $res) {
            $index++;
            echo '<tr onclick="updateQty(this)">';
            echo '<td class="text-center">'.htmlspecialchars($item_code).'</td>';            
            echo '<td class="text-center">'.htmlspecialchars($res['lotnum']).'</td>';
            echo '<td class="text-center">'.htmlspecialchars($res['stock']).'</td>';

            if ($index === $resultCount) {
                // 마지막 요소인 경우
                echo '<td class="text-center"><input type="number" name="apply_qty[]" class="form-control text-center" value="' . $item_qty . '" /></td>';
            } else {
                // 마지막 요소가 아닌 경우
                echo '<td class="text-center"><input type="number" name="apply_qty[]" class="form-control text-center" value="" /></td>';
            }

            echo '</tr>';
        }
    } else {        
        echo '<tr><td colspan="5" class="text-center">No available stock</td></tr>';
    }

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
