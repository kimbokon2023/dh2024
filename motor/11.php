<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");

$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';
$num = isset($_REQUEST['num']) ? $_REQUEST['num'] : '';

print 'controllerlist의 col8 요소를 deadline 컬럼 기준 6월 6일까지의 데이터에 한해 "DH-C초기"로 변경하는 화면 ';

include '_request.php';

$tablename = 'motor';
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

// PDO에서 버퍼링된 쿼리를 사용하도록 설정
$pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);

// 이 스크립트의 메모리 제한 증가
ini_set('memory_limit', '256M');

$sql = "SELECT * FROM " . $DB . "." . $tablename . " WHERE deadline <= '2024-06-06'";   // 6월9일까지 브라켓 로트번호 삽입

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
                'controllerlist' => []
            ];
        }
        $dataByNum[$num]['controllerlist'] = json_decode($row['controllerlist'], true);
    }

    $updateData = [];

    // num별로 데이터 처리
    foreach ($dataByNum as $num => $lists) {
        $newControllerlist = [];

        foreach ($lists['controllerlist'] as $item) {
            $newItem = $item;

            // deadline이 6월 10일 이전인 데이터의 col8에 'DH-C초기' 값을 넣음
            if (strtotime($row['deadline']) <= strtotime('2024-06-06')) {
                $newItem['col8'] = 'DH-C초기';
            }

            $newControllerlist[] = $newItem;
			
			echo '<pre>';
			print_r($newControllerlist);
			echo '</pre>';
        }

        $updateData[$num] = [
            'controllerlist' => json_encode($newControllerlist, JSON_UNESCAPED_UNICODE)
        ];
    }

    // 데이터베이스 업데이트
    $pdo->beginTransaction();

    $sql = "UPDATE " . $DB . ".{$tablename} SET controllerlist = ? WHERE num = ?";
    $stmh = $pdo->prepare($sql);

    foreach ($updateData as $num => $data) {
        $stmh->execute([$data['controllerlist'], $num]);
    }

    $pdo->commit();
} catch (PDOException $Exception) {
    $pdo->rollBack();
    print "오류: " . $Exception->getMessage();
}
?>
