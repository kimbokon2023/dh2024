<?php
// 로트번호 등록여부
$registlotnum='';	
// 1) JSON으로 저장된 리스트 디코딩
$orderItems      = json_decode($row['orderlist']      ?? '[]', true);
$controllerItems = json_decode($row['controllerlist'] ?? '[]', true);
$fabricItems     = json_decode($row['fabriclist']     ?? '[]', true);

// 2) 기본값 세팅
$isMissingLot = false;

// 3) orderlist 내역 검사 (col5 / col13,col14 체크)
if (is_array($orderItems)) {
	foreach ($orderItems as $item) {
		if (!isset($item['col5'])) continue;
		switch ($item['col5']) {
			case 'SET':
				if (empty($item['col13']) || empty($item['col14'])) {
					$isMissingLot = true;
				}
				break;
			case '모터단품':
				if (empty($item['col13'])) {
					$isMissingLot = true;
				}
				break;
			case '브라켓트':
				if (empty($item['col14'])) {
					$isMissingLot = true;
				}
				break;
		}
		if ($isMissingLot) break;
	}
}

// 4) controllerlist 검사 (col8 체크)
if (!$isMissingLot && is_array($controllerItems)) {
	foreach ($controllerItems as $item) {
		if (empty($item['col8'])) {
			$isMissingLot = true;
			break;
		}
	}
}

// 5) fabriclist 검사 (col10 체크)
if (!$isMissingLot && is_array($fabricItems)) {
	foreach ($fabricItems as $item) {
		if (empty($item['col10'])) {
			$isMissingLot = true;
			break;
		}
	}
}

// 6) 결과 설정
$registlotnum = $isMissingLot ? '미등록' : '';		
?>