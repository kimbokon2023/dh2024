<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

$month = $_POST['month'];
$year = $_POST['year'];

$data_motor = array();

// 출고예정일 deadline 기준
try {
    $stmh = $pdo->query("SELECT workplacename, deadline, secondord, deliverymethod, num, outputdate, status, orderlist, controllerlist, fabriclist,  accessorieslist 
                         FROM {$DB}.motor 
                         WHERE is_deleted IS NULL 
                         AND MONTH(deadline) = $month 
                         AND YEAR(deadline) = $year");

    while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
        array_push($data_motor, $row);
    }

    $data_motor = array(
        "data_motor" => $data_motor,
    );

    echo(json_encode($data_motor, JSON_UNESCAPED_UNICODE));

} catch (PDOException $Exception) {
    print "오류: ".$Exception->getMessage();
}
?>
