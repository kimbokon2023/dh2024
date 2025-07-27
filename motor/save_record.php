<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

$num = $_POST['recordNum'];
$secondordnum = $_POST['secondordID'];
$primisedate = $_POST['primisedate'];
$comment = $_POST['comment'];
$recordTime = $_POST['recordTime'];

if($num) {
    $sql = "UPDATE {$DB}.recordlist SET secondordnum = :secondordnum, primisedate = :primisedate, comment = :comment , recordTime = :recordTime WHERE num = :num";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['secondordnum' => $secondordnum,'primisedate' => $primisedate, 'comment' => $comment, 'recordTime' => $recordTime, 'num' => $num]);
} else {
    $sql = "INSERT INTO {$DB}.recordlist (secondordnum, primisedate, comment, recordTime, registedate ) VALUES(:secondordnum, :primisedate, :comment, :recordTime, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['secondordnum' => $secondordnum, 'primisedate' => $primisedate, 'comment' => $comment, 'recordTime' => $recordTime]);
    $num = $pdo->lastInsertId();
}

$data = [
    'num' => $num,
    'secondordnum' => $secondordnum,
    'primisedate' => $primisedate,
    'comment' => $comment,
    'recordTime' => $recordTime
];

echo json_encode($data, JSON_UNESCAPED_UNICODE);


?>
