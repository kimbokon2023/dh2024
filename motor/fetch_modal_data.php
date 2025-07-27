<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

$type = $_POST['type'];
$date = $_POST['date'];
$month = $_POST['month'];
$year = $_POST['year'];

$response = array();
$response['data'] = array();

// Construct the date string for querying the database
$queryDate = "$year-$month-$date";

try {
    $query = "SELECT workplacename, orderlist, controllerlist, fabriclist, outputdate, deadline  
              FROM {$DB}.motor 
              WHERE is_deleted IS NULL 
              AND DATE(deadline) = :queryDate";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':queryDate', $queryDate, PDO::PARAM_STR);
    $stmt->execute();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $workplacename = $row['workplacename'];
        $orderlist = $row['orderlist'] ? json_decode($row['orderlist'], true) : [];
        $controllerlist = $row['controllerlist'] ? json_decode($row['controllerlist'], true) : [];
        $fabriclist = $row['fabriclist'] ? json_decode($row['fabriclist'], true) : [];
        
        if ($type == 'motor') {
            foreach ($orderlist as $item) {
                if (in_array($item['col5'], ['SET', '모터단품'])) {
                    $itemname = $item['col1'] . $item['col2'] . $item['col3'] . $item['col4'];
                    $quantity = $item['col8'];
                    $response['data'][] = array('workplacename' => $workplacename, 'itemname' => $itemname, 'quantity' => $quantity);
                }
            }
        } elseif ($type == 'bracket') {
            foreach ($orderlist as $item) {
                if (in_array($item['col5'], ['SET', '브라켓트'])) {
                    $itemname = $item['col6'] . $item['col7'];
                    $quantity = $item['col8'];
                    $response['data'][] = array('workplacename' => $workplacename, 'itemname' => $itemname, 'quantity' => $quantity);
                }
            }
        } elseif ($type == 'controller') {
            foreach ($controllerlist as $item) {
                $itemname = $item['col2'];
                $quantity = $item['col3'];
                $response['data'][] = array('workplacename' => $workplacename, 'itemname' => $itemname, 'quantity' => $quantity);
            }
        } elseif ($type == 'fabric') {
            foreach ($fabriclist as $item) {
                $itemname = $item['col1'];
                $quantity = $item['col5']; // 4는 수량, col5는 총m
                $response['data'][] = array('workplacename' => $workplacename, 'itemname' => $itemname, 'quantity' => $quantity);
            }
        } else {
            throw new Exception("Invalid type");
        }
    }

    echo json_encode($response);
} catch (Exception $e) {
    // Handle any errors
    echo json_encode(array("error" => $e->getMessage()));
}
?>
