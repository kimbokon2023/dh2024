<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");

header("Content-Type: application/json");

$pdo = db_connect();

$num = $_POST['num'] ?? '';
$tablename = $_POST['tablename'] ?? '';

$response = ['success' => false, 'message' => '', 'chargedPersonStatus' => ''];

if (!$num || !$tablename) {
    $response['message'] = '필수 파라미터가 누락되었습니다.';
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // 최신 상태 데이터 가져오기
    $sql = "SELECT chargedPersonStatus FROM {$DB}.{$tablename} WHERE num = ?";
    $stmh = $pdo->prepare($sql);
    $stmh->bindValue(1, $num, PDO::PARAM_INT);
    $stmh->execute();
    $row = $stmh->fetch(PDO::FETCH_ASSOC);
    
    if (!$row) {
        throw new Exception('해당 게시글을 찾을 수 없습니다.');
    }
    
    $response['success'] = true;
    $response['chargedPersonStatus'] = $row['chargedPersonStatus'] ?? '';
    $response['message'] = '상태 데이터를 성공적으로 가져왔습니다.';
    
} catch (Exception $e) {
    $response['message'] = '오류가 발생했습니다: ' . $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?> 