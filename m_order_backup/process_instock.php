<?php
header('Content-Type: application/json; charset=utf-8');
require_once($_SERVER['DOCUMENT_ROOT'].'/session.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/lib/mydb.php');
$pdo = db_connect();

// POST 데이터 읽기
$mode      = $_POST['mode']      ?? '';
$tablename = $_POST['tablename'] ?? 'material_reg';
$inoutdate = $_POST['inoutdate'] ?? '';
$secondord = $_POST['secondord'] ?? '';
$items     = json_decode($_POST['items'] ?? '[]', true);

if ($mode !== 'insert' || empty($items)) {
    echo json_encode(['success'=>false, 'message'=>'잘못된 요청입니다.']);
    exit;
}

try {
    $pdo->beginTransaction();

    $registedate = date('Y-m-d');
    $user_name   = $_SESSION['name'] ?? '';

    $sql = "INSERT INTO {$tablename}
        (registedate, inoutdate, secondord,
         inout_item_code, item_name, surang,
         lotnum, comment, searchtag, update_log)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmh = $pdo->prepare($sql);

    foreach ($items as $it) {
        // 수량(surang) 필드 읽어오기
        $surang     = isset($it['surang']) ? str_replace(',', '', $it['surang']) : '0';

        // 태그·로그 생성
        $searchtag  = "{$registedate} {$inoutdate} {$secondord} {$it['inout_item_code']} {$it['lotnum']} {$surang}";
        $update_log = date('Y-m-d H:i:s') . " - {$user_name} 전산입고\r\n";

        $stmh->execute([
            $registedate,
            $inoutdate,
            $secondord,
            $it['inout_item_code'],
            $it['item_name'],
            $surang,             // ← 여기서 DB의 `surang` 컬럼에 수량 바인딩
            $it['lotnum'],
            $it['comment'],
            $searchtag,
            $update_log
        ]);
    }

    $pdo->commit();
    echo json_encode(['success'=>true]);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success'=>false, 'message'=>$e->getMessage()]);
}
