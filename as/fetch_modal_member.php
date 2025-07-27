<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

$sql = "SELECT id, name, position FROM member WHERE company='대한' AND position != '대표이사' and (dailyworkcheck != '' or dailyworkcheck IS NULL ) ORDER BY numorder ASC";
$stmh = $pdo->prepare($sql);
$stmh->execute();

$members = [];
while ($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
    $members[] = [
        'id' => $row['id'],
        'name' => htmlspecialchars($row['name']),
        'position' => htmlspecialchars($row['position'])
    ];
}

echo json_encode($members);
?>
