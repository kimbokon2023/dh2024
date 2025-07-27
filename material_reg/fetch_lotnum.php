<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");

header("Content-Type: application/json"); // JSON 형식으로 응답하기 위해 설정

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

$sql = "SELECT lotnum FROM $DB.material_lot WHERE is_deleted IS NULL ORDER BY registedate DESC";

try {
    $stmh = $pdo->query($sql);
    $lotnums = [];

    while ($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
        $lotnums[] = $row['lotnum'];
    }
	 $lotnums[] = '초기로트' ;

    echo json_encode(['lotnums' => $lotnums], JSON_UNESCAPED_UNICODE);
} catch (PDOException $Exception) {
    echo json_encode(['error' => $Exception->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
