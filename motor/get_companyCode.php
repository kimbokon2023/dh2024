<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

$secondordnum = $_POST['secondordnum'];

try {
    $sql = "SELECT email, vendor_name FROM {$DB}.phonebook WHERE secondordnum = ?";
    $stmh = $pdo->prepare($sql);
    $stmh->bindValue(1, $secondordnum, PDO::PARAM_STR);
    $stmh->execute();
    $row = $stmh->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $response = array('email' => $row['email'], 'vendor_name' => $row['vendor_name']);
    } else {
        $response = array('error' => 'No data found', 'secondordnum' => $secondordnum );
    }

    echo json_encode($response);
} catch (PDOException $Exception) {
    echo json_encode(array('error' => $Exception->getMessage()));
}
?>
