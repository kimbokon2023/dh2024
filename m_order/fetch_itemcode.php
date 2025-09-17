<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");

header("Content-Type: application/json");

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

$items = [];
$item_codes_set = [];

// Helper function to decode JSON safely
function safe_json_decode($json) {
    $decoded = json_decode($json, true);
    return is_array($decoded) ? $decoded : [];
}

// Helper function to generate ecountcode
function generate_ecountcode($volt, $wire, $upweight, $unit, $item) {
    if(!empty($item)) {
        if ($unit !== '브라켓트') {
            $ecountcode = '';
            if ($volt && (strpos($volt, '220') === 0 || strpos($volt, '380') === 0)) {
                $ecountcode .= $volt . 'V-';
            }
            if ($wire) {
                $ecountcode .= $wire . '-';
            }
            if ($item=='무기둥모터') {   // 무기둥모터 품목 추가
                $ecountcode .= $item . '-';
            }
            if ($upweight) {
                $ecountcode .= str_replace(['k', 'K'], 'K', $upweight) . '-';
            }
            if ($item=='방범') {        // 방범인 경우 - 방범 형태로 수정
                $ecountcode .=  $item ;
            }
            // Remove the trailing '-' if it exists
            if (substr($ecountcode, -1) === '-') {
                $ecountcode = substr($ecountcode, 0, -1);
            }
            return '모터 ' . $ecountcode;
        } else {
            return '브라켓트 ' . $item;
        }
    }
}

// fee 테이블에서 데이터 추출
$tablename = 'fee';
$sql_fee = "SELECT ecountcode, item, volt, wire, upweight, unit, yuan FROM {$DB}.{$tablename} WHERE is_deleted IS NULL ORDER BY num DESC  limit 1";
try {
    $stmh_fee = $pdo->query($sql_fee);
    while ($row = $stmh_fee->fetch(PDO::FETCH_ASSOC)) {
        $ecountcodes = safe_json_decode($row['ecountcode']);
        $items_array = safe_json_decode($row['item']);
        $volts = safe_json_decode($row['volt']);
        $wires = safe_json_decode($row['wire']);
        $upweights = safe_json_decode($row['upweight']);
        $units = safe_json_decode($row['unit']);
        $yuans = safe_json_decode($row['yuan']);
        foreach ($ecountcodes as $index => $item_code) {
            if (isset($items_array[$index])) {
                $unitValue = $units[$index] ?? '';

                // unit이 '모터단품'인 것만 포함
                if ($unitValue === '모터단품') {
                    $generated_code = generate_ecountcode(
                        $volts[$index] ?? '',
                        $wires[$index] ?? '',
                        $upweights[$index] ?? '',
                        $unitValue,
                        $items_array[$index]
                    );

                    if ($generated_code === '' || $generated_code === null) {
                        $generated_code = '모터단품';
                    }

                    if (!in_array($item_code, $item_codes_set)) {
                        $items[] = [
                            'item_code' => $item_code,
                            'item_name' => $generated_code,
                            'item_yuan' => $yuans[$index] ?? '',
                            'item_unit' => $unitValue
                        ];
                        $item_codes_set[] = $item_code;
                    }
                }
            }
        }
    }
} catch (PDOException $Exception) {
    echo json_encode(['error' => $Exception->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}

// fee_controller 테이블에서 데이터 추출
$tablename = 'fee_controller';
$sql_fee_controller = "SELECT item, yuan FROM {$DB}.{$tablename}  WHERE is_deleted IS NULL ORDER BY num DESC  limit 1";
try {
    $stmh_fee_controller = $pdo->query($sql_fee_controller);
    while ($row = $stmh_fee_controller->fetch(PDO::FETCH_ASSOC)) {
        $item_codes = safe_json_decode($row['item']);
        $yuans = safe_json_decode($row['yuan']);
        foreach ($item_codes as $index => $item_code) {
            if (!in_array($item_code, $item_codes_set)) {
                $items[] = [
                    'item_code' => $item_code,
                    'item_name' => $item_code,
                    'item_yuan' => $yuans[$index] ?? '',
                    'item_unit' => ''
                ];
                $item_codes_set[] = $item_code;
            }
        }
    }
} catch (PDOException $Exception) {
    echo json_encode(['error' => $Exception->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}

// fee_sub 테이블에서 데이터 추출
function generateItemCodeForAccessory($accessoryItem) {
    $col1 = isset($accessoryItem['col1']) ? $accessoryItem['col1'] : '';
    $volt = '';
    $wire = '';
    $range = '';

    if (strpos($col1, '콘트롤박스') !== false) {
        $ecountcode = '';

        if (preg_match('/(\d+)V/', $col1, $matches) && (strpos($matches[1], '220') === 0 || strpos($matches[1], '380') === 0)) {
            $volt = $matches[1];  // '220V'에서 '220'만 추출
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
        return $col1 ;
    }
}

// fee_sub 테이블에서 데이터 추출
$tablename = 'fee_sub';
$sql_fee_sub = "SELECT itemcode, item, yuan FROM {$DB}.{$tablename}  WHERE is_deleted IS NULL ORDER BY num DESC limit 1";
try {
    $stmh_fee_sub = $pdo->query($sql_fee_sub);
    while ($row = $stmh_fee_sub->fetch(PDO::FETCH_ASSOC)) {
        $itemcodes = safe_json_decode($row['itemcode']);
        $items_array = safe_json_decode($row['item']);
        $yuans = safe_json_decode($row['yuan']);
        foreach ($itemcodes as $index => $item_code) {
            if (isset($items_array[$index])) {
                $accessoryItem = ['col1' => $items_array[$index]];
                $generated_code = generateItemCodeForAccessory($accessoryItem);

                if (!empty($generated_code) && !in_array($generated_code, $item_codes_set)) {
                    $items[] = [
                        'item_code' => $generated_code,
                        'item_name' => $generated_code,
                        'item_yuan' => $yuans[$index] ?? '',
                        'item_unit' => ''
                    ];
                    $item_codes_set[] = $generated_code;
                } elseif (!in_array($item_code, $item_codes_set)) {
                    $items[] = [
                        'item_code' => $item_code,
                        'item_name' => $item_code,
                        'item_yuan' => $yuans[$index] ?? '',
                        'item_unit' => ''
                    ];
                    $item_codes_set[] = $item_code;
                }
            }
        }
    }
} catch (PDOException $Exception) {
    echo json_encode(['error' => $Exception->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}

// fee_fabric 테이블에서 데이터 추출
$tablename = 'fee_fabric';
$sql_fee_sub = "SELECT itemcode, item, yuan FROM {$DB}.{$tablename}  WHERE is_deleted IS NULL ORDER BY num DESC limit 1";
try {
    $stmh_fee_sub = $pdo->query($sql_fee_sub);
    while ($row = $stmh_fee_sub->fetch(PDO::FETCH_ASSOC)) {
        $itemcodes = safe_json_decode($row['itemcode']);
        $items_array = safe_json_decode($row['item']);
        $yuans = safe_json_decode($row['yuan']);
        foreach ($itemcodes as $index => $item_code) {
            if (isset($items_array[$index])) {
                $accessoryItem = ['col1' => $items_array[$index]];
                if (!empty($item_code)) {
                    $items[] = [
                        'item_code' => $item_code,
                        'item_name' => $item_code,
                        'item_yuan' => $yuans[$index] ?? '',
                        'item_unit' => ''
                    ];
                    $item_codes_set[] = $item_code;
                }
            }
        }
    }
} catch (PDOException $Exception) {
    echo json_encode(['error' => $Exception->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}


// 배열을 item_code 기준으로 오름차순 정렬
usort($items, function($a, $b) {
    return strcmp($a['item_code'], $b['item_code']);
});

echo json_encode(['items' => $items], JSON_UNESCAPED_UNICODE);
?>


