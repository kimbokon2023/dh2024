<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");

header("Content-Type: application/json");

$num = $_REQUEST['num'] ?? '';
$status = $_REQUEST['status'] ?? '';
$update_log_input = $_REQUEST['update_log'] ?? '';
$outputdate = $_REQUEST['outputdate'] ?? '';

// 필수 값 누락 시
if (empty($num)) {
    http_response_code(400);
    echo json_encode(['error' => 'num 값이 없습니다.']);
    exit;
}

// 로그 문자열 조합
$update_log = date("Y-m-d H:i:s") . " - " . ($_SESSION["name"] ?? 'unknown') . " " . $update_log_input . "&#10";

$pdo = db_connect();

try {
    $pdo->beginTransaction();

    $sql = "UPDATE {$DB}.motor SET 
                status = ?, 
                update_log = ?, 
                outputdate = ?
            WHERE num = ?";

    $stmh = $pdo->prepare($sql);
    $stmh->execute([$status, $update_log, $outputdate, $num]);

    $pdo->commit();

    echo json_encode([
        'num' => $num,
        'status' => $status,
        'outputdate' => $outputdate,
        'update_log' => $update_log
    ], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode([
        'error' => '데이터베이스 오류',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
