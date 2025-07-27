<?php
// insert_wrap.php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");
header("Content-Type: application/json; charset=utf-8");

if(empty($DB))
	$DB = 'chandj';

// DB 연결
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

// 업데이트할 테이블
$tablename = 'motor';

// AJAX 로 넘어온 JSON 배열 디코딩
$updates = isset($_POST['updates'])
         ? json_decode($_POST['updates'], true)
         : null;

if (!is_array($updates) || count($updates) === 0) {
    echo json_encode([
        'success' => false,
        'error'   => '업데이트할 데이터가 없습니다.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo->beginTransaction();

    $sql  = "UPDATE {$DB}.{$tablename}
             SET cargo_delwrapmethod = ?,
                 cargo_delwrapsu     = ?,
                 cargo_delwrapamount = ?,
				 delwrapmethod = ?,
                 delwrapsu     = ?,
                 delwrapamount = ? 
             WHERE num = ?";
    $stmt = $pdo->prepare($sql);

    foreach ($updates as $row) {
        if (!isset($row['id'], $row['method'], $row['su'], $row['amount'])) {
            continue;
        }
        $stmt->execute([
            $row['method'],
            $row['su'],
            $row['amount'],
            $row['method'],
            $row['su'],
            $row['amount'],
            intval($row['id'])
        ]);
    }

    $pdo->commit();
    echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'error'   => 'DB 오류: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
