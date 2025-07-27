<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");

header("Content-Type: application/json");

$pdo = db_connect();

$num = $_POST['num'] ?? '';
$tablename = $_POST['tablename'] ?? '';
$person = $_POST['person'] ?? '';
$type = $_POST['type'] ?? '';
$date = $_POST['date'] ?? '';

$response = ['success' => false, 'message' => ''];

if (!$num || !$tablename || !$person || !$type) {
    $response['message'] = '필수 파라미터가 누락되었습니다.';
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// 현재 사용자가 해당 담당자인지 확인
if ($person !== $_SESSION['name']) {
    $response['message'] = '본인만 상태를 업데이트할 수 있습니다.';
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // 기존 상태 데이터 가져오기
    $sql = "SELECT chargedPersonStatus FROM {$DB}.{$tablename} WHERE num = ?";
    $stmh = $pdo->prepare($sql);
    $stmh->bindValue(1, $num, PDO::PARAM_INT);
    $stmh->execute();
    $row = $stmh->fetch(PDO::FETCH_ASSOC);
    
    if (!$row) {
        throw new Exception('해당 게시글을 찾을 수 없습니다.');
    }
    
    $statusData = $row['chargedPersonStatus'] ? json_decode($row['chargedPersonStatus'], true) : [];
    
    // 해당 담당자의 상태 업데이트
    if (!isset($statusData[$person])) {
        $statusData[$person] = ['checked' => '', 'done' => ''];
    }
    
    $statusData[$person][$type] = $date;
    
    // JSON으로 인코딩하여 저장
    $newStatusJson = json_encode($statusData, JSON_UNESCAPED_UNICODE);
    
    $sql = "UPDATE {$DB}.{$tablename} SET chargedPersonStatus = ? WHERE num = ?";
    $stmh = $pdo->prepare($sql);
    $stmh->bindValue(1, $newStatusJson, PDO::PARAM_STR);
    $stmh->bindValue(2, $num, PDO::PARAM_INT);
    $stmh->execute();
    
    $pdo->commit();
    $response['success'] = true;
    $response['message'] = '상태가 성공적으로 업데이트되었습니다.';
    
} catch (Exception $e) {
    $pdo->rollBack();
    $response['message'] = '오류가 발생했습니다: ' . $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?> 