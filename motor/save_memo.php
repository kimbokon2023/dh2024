<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");

$pdo = db_connect();

$num = isset($_POST['num']) ? $_POST['num'] : '';
$comment = isset($_POST['comment']) ? $_POST['comment'] : '';
$primisedate = isset($_POST['primisedate']) ? $_POST['primisedate'] : '';

if ($num) {
    try {
        $sql = "UPDATE {$DB}.recordlist SET comment = ?, primisedate = ? WHERE num = ?";
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $comment, PDO::PARAM_STR);
        $stmh->bindValue(2, $primisedate, PDO::PARAM_STR);
        $stmh->bindValue(3, $num, PDO::PARAM_INT);
        $stmh->execute();
        echo 'success';
    } catch (PDOException $Exception) {
        echo "오류: " . $Exception->getMessage();
        exit;
    }
} else {
    echo 'fail';
}
?>
