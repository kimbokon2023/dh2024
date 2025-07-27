<?php   
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  
$num = isset($_REQUEST['num']) ? $_REQUEST['num'] : '';

print '기존의 json형태의 파일의 내용을 강제로 수정하기 위한 화면 <br>';
print '로트번호를 부여하고 ';
include '_request.php';
$tablename = 'motor';

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();
$sql = "SELECT * FROM " . $DB . "." . $tablename . " ";

try {
    $stmh = $pdo->prepare($sql);
    $stmh->execute();
    $rows = $stmh->fetchAll(PDO::FETCH_ASSOC);

    $dataByNum = [];

    // 데이터를 num별로 그룹화
    foreach ($rows as $row) {
        $num = $row['num'];
        if (!isset($dataByNum[$num])) {
            $dataByNum[$num] = [
                'orderlist' => [],
                'accessorieslist' => [],
                'controllerlist' => []
            ];
        }
        $dataByNum[$num]['orderlist'] = json_decode($row['orderlist'], true) ?: [];
        $dataByNum[$num]['accessorieslist'] = json_decode($row['accessorieslist'], true) ?: [];
        $dataByNum[$num]['controllerlist'] = json_decode($row['controllerlist'], true) ?: [];
    }

    $updateData = [];

    // num별로 데이터 처리
    foreach ($dataByNum as $num => $lists) {
        $newControllerlist = [];
		foreach ($lists['controllerlist'] as &$item) {
			if (in_array($item['col2'], [
				'매립형-연동제어기-유선',
				'노출형-연동제어기-유선',
				'매립형-연동제어기-무선',
				'노출형-연동제어기-무선'
			])) {
				// 연동제어기의 경우 품목코드 생성
				$item['col10'] = urlencode(json_encode([
					$item['col2'] => ['로트번호' => '초기로트', '수량' => (int)$item['col3']] // 수량은 col3를 사용
				], JSON_UNESCAPED_UNICODE));
			} else {
				$item['col10'] = urlencode(json_encode([
					generateItemCodeForAccessory($item) => ['로트번호' => '초기로트', '수량' => (int)$item['col3']]
				], JSON_UNESCAPED_UNICODE));
			}
			// $item['col9'] = ""; // col9에 빈 문자열 할당
			$newControllerlist[] = $item;
		}


        $newAccessorieslist = [];
        foreach ($lists['accessorieslist'] as &$item) {
            $item['col6'] = urlencode(json_encode([
                generateItemCodeForAccessory($item) => ['로트번호' => '초기로트', '수량' => (int)$item['col2']]
            ], JSON_UNESCAPED_UNICODE));
            $newAccessorieslist[] = $item;
        }

        // orderlist에 col15 추가
        foreach ($lists['orderlist'] as &$orderItem) {
            if (strcasecmp($orderItem['col5'], 'set') === 0) {
                $col15_data = [
                    generateItemCode($orderItem) => ['로트번호' => '초기로트', '수량' => (int)$orderItem['col8']],
                    generateBracketCode($orderItem) => ['로트번호' => '초기로트', '수량' => (int)$orderItem['col8']]
                ];
            } else {
                $col15_data = [
                    generateItemCode($orderItem) => ['로트번호' => '초기로트', '수량' => (int)$orderItem['col8']]
                ];
            }

            // JSON 형식으로 변환하여 col15에 저장
            $orderItem['col15'] = urlencode(json_encode($col15_data, JSON_UNESCAPED_UNICODE));
        }

        $updateData[$num] = [
            'orderlist' => json_encode($lists['orderlist'], JSON_UNESCAPED_UNICODE),
            'accessorieslist' => json_encode($newAccessorieslist, JSON_UNESCAPED_UNICODE),
            'controllerlist' => json_encode($newControllerlist, JSON_UNESCAPED_UNICODE)
        ];
    }

    // 데이터베이스 업데이트
    $pdo->beginTransaction();

    $sql = "UPDATE " . $DB . ".{$tablename} SET orderlist = ?, accessorieslist = ?, controllerlist = ? WHERE num = ?";
    $stmh = $pdo->prepare($sql);

    foreach ($updateData as $num => $data) {
        $stmh->execute([$data['orderlist'], $data['accessorieslist'], $data['controllerlist'], $num]);
    }

    $pdo->commit();
} catch (PDOException $Exception) {
    $pdo->rollBack();
    print "오류: " . $Exception->getMessage();
}


// Helper function to generate item code based on given columns
function generateItemCode($orderItem) {
    $volt = isset($orderItem['col1']) ? $orderItem['col1'] : '';
    $wire = isset($orderItem['col2']) ? $orderItem['col2'] : '';
    $item = isset($orderItem['col3']) ? $orderItem['col3'] : '';
    $upweight = isset($orderItem['col4']) ? $orderItem['col4'] : '';
    $unit = isset($orderItem['col5']) ? $orderItem['col5'] : '';

    if ($unit !== '브라켓트') {
        $ecountcode = '';
        if ($volt && (strpos($volt, '220') === 0 || strpos($volt, '380') === 0)) {
            $ecountcode .= $volt . '-';
        }
        if ($wire) {
            $ecountcode .= $wire . '-';
        }
        if ($item == '무기둥모터') { // 무기둥모터 품목 추가
            $ecountcode .= $item . '-';
        }       
        if ($upweight) {
            $ecountcode .= str_replace(['k', 'K'], '', $upweight) . '-';
        }
        // Remove the trailing '-' if it exists
        if (substr($ecountcode, -1) === '-') {
            $ecountcode = substr($ecountcode, 0, -1);
        }
        return $ecountcode;
    } else {
		// 브라켓트 품명을 만든다.
         $bracketitem = isset($orderItem['col6']) ? $orderItem['col6'] : '';
		return $bracketitem;
    }
}

// Helper function to generate the bracket item code based on given columns
function generateBracketCode($orderItem) {
    $item = isset($orderItem['col6']) ? $orderItem['col6'] : '';
    return $item;
}


// Helper function to generate item code for accessories based on given columns
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
        return '';
    }
}
?>
