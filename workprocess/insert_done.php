<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");

header("Content-Type: application/json");

$pdo = db_connect();

$num = $_POST['num'] ?? '';
$tablename = $_POST['tablename'] ?? '';
$action = $_POST['action'] ?? '';
$selectedDate = $_POST['selectedDate'] ?? '';

$response = ['success' => false, 'message' => '', 'doneDate' => ''];

if (!$num || !$tablename || !$action) {
    $response['message'] = '잘못된 요청입니다.';
    echo json_encode($response);
    exit;
}

try {
    $pdo->beginTransaction();
if ($action === 'set') {
    $dateToSave = $selectedDate ?: date('Y-m-d'); // 선택된 날짜 또는 오늘
    $sql = "UPDATE {$DB}.{$tablename} SET doneDate = ? WHERE num = ?";
    $stmh = $pdo->prepare($sql);
    $stmh->execute([$dateToSave, $num]);
    $response['doneDate'] = $dateToSave;
    $response['message'] = '완료일이 저장되었습니다.';
} elseif ($action === 'clear') {
        $sql = "UPDATE {$DB}.{$tablename} SET doneDate = NULL WHERE num = ?";
        $stmh = $pdo->prepare($sql);
        $stmh->execute([$num]);
        $response['doneDate'] = '';
        $response['message'] = '완료일이 삭제되었습니다.';
    } else {
        throw new Exception('알 수 없는 액션입니다.');
    }

    $pdo->commit();
    $response['success'] = true;
} catch (PDOException $e) {
    $pdo->rollBack();
    $response['message'] = 'DB 오류: ' . $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>