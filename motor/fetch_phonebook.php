<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

try {
    $query = "SELECT vendor_name FROM phonebook WHERE is_deleted IS NULL";
    $stmh = $pdo->prepare($query);
    $stmh->execute();
    $result = $stmh->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode($result);
} catch (PDOException $Exception) {
    echo "오류: " . $Exception->getMessage();
    exit;
}
?>
