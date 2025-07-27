<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

$item_code = $_POST['item_code'];
$from_date = $_POST['from_date'];
$to_date = $_POST['to_date'];
$search_secondord = $_POST['search_secondord'];
$search_workplacename = $_POST['search_workplacename'];

$orderby = "ORDER BY outputdate DESC";        
$SettingDate = "outputdate"; 

$common = "$SettingDate BETWEEN :from_date AND :to_date AND is_deleted IS NULL";

$conditions = [$common];
if (!empty($search_secondord) && !empty($search_workplacename)) {
    $conditions[] = "(secondord = :search_secondord AND workplacename LIKE :search_workplacename)";
} else {
    if (!empty($search_secondord)) {
        $conditions[] = "secondord = :search_secondord";
    }
    if (!empty($search_workplacename)) {
        $conditions[] = "workplacename LIKE :search_workplacename";
    }
}

$whereClause = implode(' AND ', $conditions);
$motor_sql = "SELECT * FROM motor WHERE " . $whereClause . " " . $orderby;

try {
    $stmh_motor = $pdo->prepare($motor_sql);
    $stmh_motor->bindValue(':from_date', $from_date, PDO::PARAM_STR);
    $stmh_motor->bindValue(':to_date', $to_date, PDO::PARAM_STR);
    if (!empty($search_secondord)) {
        $stmh_motor->bindValue(':search_secondord', $search_secondord, PDO::PARAM_STR);
    }
    if (!empty($search_workplacename)) {
        $stmh_motor->bindValue(':search_workplacename', "%$search_workplacename%", PDO::PARAM_STR);
    }
    $stmh_motor->execute();
    $motor_rows = $stmh_motor->fetchAll(PDO::FETCH_ASSOC);

    $stock_data = [];
    $total_out_sum = 0;
    $details = [];

// Function to generate item code
function generateItemCode($orderItem) {
    $volt = strtolower(isset($orderItem['col1']) ? $orderItem['col1'] : '');
    $wire = strtolower(isset($orderItem['col2']) ? $orderItem['col2'] : '');
    $purpose = strtolower(isset($orderItem['col3']) ? $orderItem['col3'] : '');
    $upweight = strtolower(isset($orderItem['col4']) ? $orderItem['col4'] : '');

    $upweight = str_replace(['k', 'K'], '', $upweight);

    if ($purpose === '무기둥모터') {
        $ecountcode = implode('-', array_filter([$volt, $wire, $purpose . $upweight]));
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

// Function to generate item code for accessory
function generateItemCodeForAccessory($accessoryItem) {
    $accessoryItem = array_map(function($value) {
        return is_string($value) ? str_replace('"', '″', $value) : $value;
    }, $accessoryItem);

    $code = strtolower(isset($accessoryItem['col1']) ? $accessoryItem['col1'] : '');

    if (empty($code)) {
        print 'Invalid accessory item: ';
        var_dump($accessoryItem);
    }
    return $code;
}

    foreach ($motor_rows as $row) {
        $orderlist = isset($row['orderlist']) ? json_decode($row['orderlist'], true) : [];
        $controllerlist = isset($row['controllerlist']) ? json_decode($row['controllerlist'], true) : [];
        $fabriclist = isset($row['fabriclist']) ? json_decode($row['fabriclist'], true) : [];
        $accessorieslist = isset($row['accessorieslist']) ? json_decode($row['accessorieslist'], true) : [];

        foreach ($orderlist as $order) {
            $item_code_gen = generateItemCode($order);
            $unit = strtolower($order['col5'] ?? '');
            $quantity = (int)($order['col8'] ?? 0);

            if ($unit === 'set' || $unit === '모터단품') {
                if (!isset($stock_data[$item_code_gen])) {
                    $stock_data[$item_code_gen] = ['total_out' => 0];
                }
                $stock_data[$item_code_gen]['total_out'] += $quantity;
                $details[] = [
                    'inoutdate' => $row['outputdate'],
                    'item_code' => $item_code_gen,
                    'quantity' => $quantity,
                    'workplacename' => $row['workplacename'],
                    'secondord' => $row['secondord']
                ];
            }

            if ($unit === 'set' || $unit === '브라켓트') {
                $bracket_code = generateItemCodeForBracket($order);
                if (!isset($stock_data[$bracket_code])) {
                    $stock_data[$bracket_code] = ['total_out' => 0];
                }
                $stock_data[$bracket_code]['total_out'] += $quantity;
                $details[] = [
                    'inoutdate' => $row['outputdate'],
                    'item_code' => $bracket_code,
                    'quantity' => $quantity,
                    'workplacename' => $row['workplacename'],
                    'secondord' => $row['secondord']
                ];
            }
        }

        foreach ($controllerlist as $controller) {
            $item_code_gen = strtolower($controller['col2'] ?? '');
            $quantity = (int)($controller['col3'] ?? 0);

            if (!isset($stock_data[$item_code_gen])) {
                $stock_data[$item_code_gen] = ['total_out' => 0];
            }
            $stock_data[$item_code_gen]['total_out'] += $quantity;
            $details[] = [
                'inoutdate' => $row['outputdate'],
                'item_code' => $item_code_gen,
                'quantity' => $quantity,
                'workplacename' => $row['workplacename'],
                'secondord' => $row['secondord']
            ];
        }

        foreach ($fabriclist as $fabric) {
            $item_code_gen = strtoupper($fabric['col1'] ?? '');
			$quantity_str = str_replace(',', '', $fabric['col5'] ?? '0'); // 쉼표 제거
			$quantity = (int)$quantity_str;

            if (!isset($stock_data[$item_code_gen])) {
                $stock_data[$item_code_gen] = ['total_out' => 0];
            }
            $stock_data[$item_code_gen]['total_out'] += $quantity;
            $details[] = [
                'inoutdate' => $row['outputdate'],
                'item_code' => $item_code_gen,
                'quantity' => $quantity,
                'workplacename' => $row['workplacename'],
                'secondord' => $row['secondord']
            ];
        }

        foreach ($accessorieslist as $accessory) {
            $item_code_gen = generateItemCodeForAccessory($accessory);
            $quantity = (int)($accessory['col2'] ?? 0);

            if (!isset($stock_data[$item_code_gen])) {
                $stock_data[$item_code_gen] = ['total_out' => 0];
            }
            $stock_data[$item_code_gen]['total_out'] += $quantity;
            $details[] = [
                'inoutdate' => $row['outputdate'],
                'item_code' => $item_code_gen,
                'quantity' => $quantity,
                'workplacename' => $row['workplacename'],
                'secondord' => $row['secondord']
            ];
        }
    }

    // Summing up total out quantities
    foreach ($stock_data as $code => $data) {
        if (str_replace(['-', 'k', 'K', '*'], '', $code) === str_replace(['-', 'k', 'K', '*'], '', $item_code)) {
            $total_out_sum += $data['total_out'];
        }
    }

    // Output the total outgoing quantity
    echo '<tr>';
    echo '<td class="text-center" colspan="2">총 출고수량</td>';
    echo '<td class="text-end" colspan="1">'.number_format($total_out_sum).'</td>';
    echo '<td class="text-center" colspan="2"> </td>';
    echo '</tr>';

    // Output detailed rows
    foreach ($details as $detail) {
        if (str_replace(['-', 'k', 'K', '*'], '', $detail['item_code']) === str_replace(['-', 'k', 'K', '*'], '', $item_code)) {
            echo '<tr>';
            echo '<td class="text-center">'.htmlspecialchars($detail['inoutdate']).'</td>';
            echo '<td class="text-start">'.htmlspecialchars($detail['item_code']).'</td>';
            echo '<td class="text-end">'.number_format($detail['quantity']).'</td>';
            echo '<td class="text-center">'.htmlspecialchars($detail['workplacename']).'</td>';
            echo '<td class="text-center">'.htmlspecialchars($detail['secondord']).'</td>';
            echo '</tr>';
        }
    }

} catch (PDOException $Exception) {
    echo "오류: " . $Exception->getMessage();
    exit;
}
?>
