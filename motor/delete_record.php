<?php
header('Content-Type: application/json');
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

$num = $_POST['num'];

// Record를 삭제(실제로는 is_deleted 필드를 업데이트)
$sql = "UPDATE recordlist SET is_deleted = 1 WHERE num = :num";
$stmt = $pdo->prepare($sql);
$stmt->execute(['num' => $num]);

// 삭제된 record의 정보를 반환합니다.
$sql = "SELECT secondordnum, primisedate, comment, recordTime FROM recordlist WHERE num = :num";
$stmt = $pdo->prepare($sql);
$stmt->execute(['num' => $num]);
$deletedRecord = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($deletedRecord, JSON_UNESCAPED_UNICODE);
?>
