<?php   
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");

$mode = $_REQUEST['mode'] ?? '';  
$num = $_REQUEST['num'] ?? '';
$tablename = 'm_order';

header("Content-Type: application/json");

include '_request.php';

// Fetch JSON data from POST request
$orderlist_jsondata = isset($_POST['orderlist']) ? json_decode($_POST['orderlist'], true) : null;

// 콤마 제거 관련 정리
$unitprice = isset($unitprice) ? floatval(str_replace(',', '', $unitprice)) : '';
$amount = isset($amount) ? floatval(str_replace(',', '', $amount)) : '';
$invoiceAmount = isset($invoiceAmount) ? floatval(str_replace(',', '', $invoiceAmount)) : '';

// DB 연결
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

if ($mode == "modify") {
    $update_log = date("Y-m-d H:i:s") . " - " . $_SESSION["name"] . " " . $update_log . "&#10";

    try {
        $pdo->beginTransaction();
        $sql = "UPDATE m_order SET 
            orderDate=?, memo=?, orderlist=?, update_log=?, first_writer=?, 
            totalsurang=?, totalamount=?
            WHERE num=? LIMIT 1";

        $stmh = $pdo->prepare($sql);

        $params = [
            $orderDate, $memo, json_encode($orderlist_jsondata), $update_log, $first_writer,
            $totalsurang, $totalamount,
            $num
        ];

        $stmh->execute($params);
        $pdo->commit();
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        print "오류: " . $Exception->getMessage();
    }
}

if ($mode == "insert" || $mode == "copy") {
    $update_log = '';
    $first_writer = $_SESSION["name"];

    try {
        $pdo->beginTransaction();

        $sql = "INSERT INTO m_order (
            orderDate, memo, orderlist, update_log, first_writer, totalsurang, totalamount            
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?
        )";  // 7개 컬럼

        $stmh = $pdo->prepare($sql);

        $params = [
            $orderDate, $memo, json_encode($orderlist_jsondata), $update_log, $first_writer, $totalsurang, $totalamount
        ];

        $stmh->execute($params);
        $pdo->commit();
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        print "오류: " . $Exception->getMessage();
    }
}

if ($mode == "copy") {
    try {  
        $stmh = $pdo->query("SELECT * FROM m_order ORDER BY num DESC");    
        $row = $stmh->fetch(PDO::FETCH_ASSOC); 
        $num = $row["num"];
    } catch (PDOException $Exception) {
        print "오류: " . $Exception->getMessage();
    }
}

if ($mode == "delete") {
    try {
        $pdo->beginTransaction();
        $query = "UPDATE m_order SET is_deleted=1 WHERE num=? LIMIT 1";
        $stmh = $pdo->prepare($query);
        $stmh->execute([$num]);
        $pdo->commit();
    } catch (PDOException $Exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        print "오류: " . $Exception->getMessage();
    }
}

if ($mode == "insert" || $mode == "copy") {
    try {
        $stmh = $pdo->query("SELECT * FROM m_order ORDER BY num DESC LIMIT 1");
        $row = $stmh->fetch(PDO::FETCH_ASSOC); 	 
        $num = $row["num"]; 	
    } catch (PDOException $Exception) {
        error_log("오류: " . $Exception->getMessage());
        echo "시스템 오류가 발생했습니다. 관리자에게 문의하세요.";
    }
}

$data = [   
    'num' => $num,
    'mode' => $mode,
    'totalsurang' => $totalsurang,
    'totalamount' => $totalamount
];

echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>
