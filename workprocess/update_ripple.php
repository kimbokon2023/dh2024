<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");

$pdo = db_connect();

$ripple_num = $_POST["ripple_num"];
$content = $_POST["content"];
$tablename = $_POST["tablename"];
$num = $_POST["num"];

try {
    $sql = "UPDATE " . $DB . ".workprocess_ripple SET content = ? WHERE num = ?";
    $stmh = $pdo->prepare($sql);
    $stmh->bindValue(1, $content, PDO::PARAM_STR);
    $stmh->bindValue(2, $ripple_num, PDO::PARAM_INT);
    $stmh->execute();

    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
