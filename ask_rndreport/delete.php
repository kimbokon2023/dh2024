<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/session.php';

header('Content-Type: application/json; charset=utf-8');

$tablename = 'eworks';
$num = $_REQUEST['num'] ?? '';

require_once($_SERVER['DOCUMENT_ROOT'] . '/lib/mydb.php');
$pdo = db_connect();

$response = [ 'status' => 'success', 'num' => $num ];

try {
    // 1) 관련 파일 레코드 삭제 (현재 모듈 스키마에 맞춤)
    $pdo->beginTransaction();
    $sql1 = "DELETE FROM {$DB}.fileuploads WHERE parentid = ? AND tablename = ?";
    $stmh1 = $pdo->prepare($sql1);
    $stmh1->bindValue(1, $num, PDO::PARAM_STR);
    $stmh1->bindValue(2, $tablename, PDO::PARAM_STR);
    $stmh1->execute();
    $pdo->commit();

    // 2) 문서 논리삭제 처리
    $pdo->beginTransaction();
    $sql2 = "UPDATE {$DB}.{$tablename} SET is_deleted = ? WHERE num = ? LIMIT 1";
    $stmh2 = $pdo->prepare($sql2);
    $stmh2->bindValue(1, true, PDO::PARAM_STR);
    $stmh2->bindValue(2, $num, PDO::PARAM_STR);
    $stmh2->execute();
    $pdo->commit();
} catch (Exception $ex) {
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    $response = [
        'status' => 'error',
        'message' => $ex->getMessage(),
    ];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>