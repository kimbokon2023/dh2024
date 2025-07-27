<?php   
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  
$num = isset($_REQUEST['num']) ? $_REQUEST['num'] : '';

print '기존의 json형태의 파일의 내용을 강제로 수정하기 위한 화면';

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
                'accessorieslist' => [],
                'controllerlist' => []
            ];
        }
        $dataByNum[$num]['accessorieslist'] = json_decode($row['accessorieslist'], true);
        $dataByNum[$num]['controllerlist'] = json_decode($row['controllerlist'], true) ?: [];
    }

    $updateData = [];

    // num별로 데이터 처리
    foreach ($dataByNum as $num => $lists) {
        $newAccessorieslist = [];
        $movedItems = [];

        foreach ($lists['accessorieslist'] as $item) {
            if (in_array($item['col2'], [
                '매립형-연동제어기-유선',
                '노출형-연동제어기-유선',
                '매립형-연동제어기-무선',
                '노출형-연동제어기-무선'
            ])) {
                $movedItems[] = [
                    'col1' => isset($item['col1']) ? $item['col1'] : '',
                    'col2' => isset($item['col2']) ? $item['col2'] : '',
                    'col3' => isset($item['col3']) ? $item['col3'] : '',
                    'col4' => isset($item['col4']) ? $item['col4'] : '',
                    'col5' => isset($item['col5']) ? $item['col5'] : '',
                    'col6' => isset($item['col6']) ? $item['col6'] : '',
                    'col7' => isset($item['col7']) ? $item['col7'] : '',
                    'col8' => isset($item['col8']) ? $item['col8'] : '',
                    'col9' => isset($item['col9']) ? $item['col9'] : ''
                ];
            } else {
                $newAccessorieslist[] = $item;
            }
        }

        if (!empty($movedItems)) {
            $lists['controllerlist'] = array_merge($lists['controllerlist'], $movedItems);
        }

        $updateData[$num] = [
            'accessorieslist' => json_encode($newAccessorieslist, JSON_UNESCAPED_UNICODE),
            'controllerlist' => json_encode($lists['controllerlist'], JSON_UNESCAPED_UNICODE)
        ];
    }

    // 데이터베이스 업데이트
    $pdo->beginTransaction();

    $sql = "UPDATE " . $DB . ".{$tablename} SET accessorieslist = ?, controllerlist = ? WHERE num = ?";
    $stmh = $pdo->prepare($sql);

    foreach ($updateData as $num => $data) {
        $stmh->execute([$data['accessorieslist'], $data['controllerlist'], $num]);
    }

    $pdo->commit();
} catch (PDOException $Exception) {
    $pdo->rollBack();
    print "오류: " . $Exception->getMessage();
}
?>
