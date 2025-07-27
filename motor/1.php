<?php   

require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  
$num = isset($_REQUEST['num']) ? $_REQUEST['num'] : '';

print 'orderlist의 col15값을 col16으로, col14값을 col15로 이동시키는 json형태의 파일 내용을 강제로 수정하기 위한 화면 ';

include '_request.php';

$tablename = 'motor';
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

// PDO에서 버퍼링된 쿼리를 사용하도록 설정
$pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);

// 이 스크립트의 메모리 제한 증가
ini_set('memory_limit', '256M');

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
                'orderlist' => []
            ];
        }
        $dataByNum[$num]['orderlist'] = json_decode($row['orderlist'], true);
    }

    $updateData = [];

    // num별로 데이터 처리
    foreach ($dataByNum as $num => $lists) {
        $newOrderlist = [];

        foreach ($lists['orderlist'] as $item) {
            $newItem = $item;
            $tempCol14 = isset($item['col14']) ? $item['col14'] : '';
            $tempCol15 = isset($item['col15']) ? $item['col15'] : '';

            $newItem['col15'] = $tempCol14;
            $newItem['col16'] = $tempCol15;

            $newOrderlist[] = $newItem;
        }

        $updateData[$num] = [
            'orderlist' => json_encode($newOrderlist, JSON_UNESCAPED_UNICODE)
        ];
    }

    // 데이터베이스 업데이트
    $pdo->beginTransaction();

    $sql = "UPDATE " . $DB . ".{$tablename} SET orderlist = ? WHERE num = ?";
    $stmh = $pdo->prepare($sql);

    foreach ($updateData as $num => $data) {
        $stmh->execute([$data['orderlist'], $num]);
    }

    $pdo->commit();
} catch (PDOException $Exception) {
    $pdo->rollBack();
    print "오류: " . $Exception->getMessage();
}
?>
