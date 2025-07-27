<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

$tablename = isset($_REQUEST['tablename']) ? $_REQUEST['tablename'] : '';  
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  

header("Content-Type: application/json");  // Use JSON content type

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

include "_request.php";

$searchtag = $registedate . ' ' . $lotnum;

if ($mode == "update") {
    $update_log = date("Y-m-d H:i:s") . " - " . $_SESSION["name"] . " " . $update_log . "&#10";
    try {
        $pdo->beginTransaction();
        $sql = "UPDATE " . $DB . "." . $tablename . " SET ";
        $sql .= "registedate = ?, lotnum = ?, searchtag = ?, update_log = ?, manual_input_checkbox = ?, manual_input = ? ";
        $sql .= "WHERE num = ? LIMIT 1";

        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $registedate, PDO::PARAM_STR);
        $stmh->bindValue(2, $lotnum, PDO::PARAM_STR);
        $stmh->bindValue(3, $searchtag, PDO::PARAM_STR);
        $stmh->bindValue(4, $update_log, PDO::PARAM_STR);
        $stmh->bindValue(5, $manual_input_checkbox, PDO::PARAM_STR);        
        $stmh->bindValue(6, $_REQUEST['manual_input'], PDO::PARAM_STR);
        $stmh->bindValue(7, $num, PDO::PARAM_INT);

        $stmh->execute();
        $pdo->commit();
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        print "오류: " . $Exception->getMessage();
    }
}

if ($mode == "insert") {
    $update_log = date("Y-m-d H:i:s") . " - " . $_SESSION["name"] . " " . $update_log . "&#10";
    try {
        $pdo->beginTransaction();
        $sql = "INSERT INTO " . $DB . "." . $tablename . " (";
        $sql .= "registedate, lotnum, searchtag, update_log, manual_input_checkbox, manual_input";
        $sql .= ") VALUES (?, ?, ?, ?, ?, ?)";

        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $registedate, PDO::PARAM_STR);
        $stmh->bindValue(2, $lotnum, PDO::PARAM_STR);
        $stmh->bindValue(3, $searchtag, PDO::PARAM_STR);
        $stmh->bindValue(4, $update_log, PDO::PARAM_STR);
        $stmh->bindValue(5, $manual_input_checkbox, PDO::PARAM_STR);        
        $stmh->bindValue(6, $_REQUEST['manual_input'], PDO::PARAM_STR);

        $stmh->execute();
        $pdo->commit();
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        print "오류: " . $Exception->getMessage();
    }
}

if ($mode == "delete") {
    try {
        $pdo->beginTransaction();
        $sql = "UPDATE " . $DB . "." . $tablename . " SET is_deleted=1 WHERE num = ?";
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $num, PDO::PARAM_INT);
        $stmh->execute();
        $pdo->commit();
    } catch (PDOException $ex) {
        $pdo->rollBack();
        print "오류: " . $ex->getMessage();
    }
}

$data = [   
 'num' => $num,
 'mode' => $mode
]; 

echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>
