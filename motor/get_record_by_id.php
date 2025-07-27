<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

if (isset($_POST['recordNum'])) {
    $recordNum = $_POST['recordNum'];
    
    $sql = "SELECT * FROM {$DB}.recordlist WHERE num = :num";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':num', $recordNum, PDO::PARAM_INT);
    $stmt->execute();
    
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($record) {
        echo json_encode($record);
    } else {
        echo json_encode(null);
    }
} else {
    echo json_encode(null);
}
?>
