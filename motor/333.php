<?php   

// 부속자재의 1컬럼부터 4칼럼까지  한칸씩 오른쪽으로 이동

require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  
$num = isset($_REQUEST['num']) ? $_REQUEST['num'] : '';

print 'accessorieslist의 컬럼 위치를 바꾸는 기존의 json형태의 파일의 내용을 강제로 수정하기 위한 화면 ';

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
                'accessorieslist' => []
            ];
        }
        $dataByNum[$num]['accessorieslist'] = json_decode($row['accessorieslist'], true);
    }

    $updateData = [];

    // num별로 데이터 처리
    foreach ($dataByNum as $num => $lists) {
        $newAccessorieslist = [];

        foreach ($lists['accessorieslist'] as $item) {
            $newItem = $item;
            $tempCol2 = isset($item['col2']) ? $item['col2'] : '';
            $tempCol3 = isset($item['col3']) ? $item['col3'] : '';
            $tempCol5 = isset($item['col5']) ? $item['col5'] : '';

            $newItem['col1'] = $tempCol2;
            $newItem['col2'] = $tempCol3;
            $newItem['col3'] = $tempCol5;

            $newAccessorieslist[] = $newItem;
        }

        $updateData[$num] = [
            'accessorieslist' => json_encode($newAccessorieslist, JSON_UNESCAPED_UNICODE)
        ];
    }

    // 데이터베이스 업데이트
    $pdo->beginTransaction();

    $sql = "UPDATE " . $DB . ".{$tablename} SET accessorieslist = ? WHERE num = ?";
    $stmh = $pdo->prepare($sql);

    foreach ($updateData as $num => $data) {
        $stmh->execute([$data['accessorieslist'], $num]);
    }

    $pdo->commit();
} catch (PDOException $Exception) {
    $pdo->rollBack();
    print "오류: " . $Exception->getMessage();
}
?>
