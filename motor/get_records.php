<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

if (isset($_GET['secondordnum'])) {
    $secondordnum = $_GET['secondordnum'];

    $sql = "SELECT num, primisedate, comment, recordTime FROM {$DB}.recordlist WHERE secondordnum = :secondordnum AND is_deleted IS NULL ORDER BY registedate DESC ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['secondordnum' => $secondordnum]);

    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$records) {
        $records = [];
    }

    echo json_encode($records);
} else {
    echo json_encode(['error' => 'secondordnum이 전달되지 않았습니다.']);
    error_log("secondordnum이 전달되지 않았습니다.");
}
?>
