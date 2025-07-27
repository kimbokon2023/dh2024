<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

$tablename = isset($_REQUEST['tablename']) ? $_REQUEST['tablename'] : '';  
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  

header("Content-Type: application/json");  // Use JSON content type

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();
 
include $_SERVER['DOCUMENT_ROOT'] . "/material_reg/_request.php";
 
$searchtag = $registedate . ' ' .
              $inoutdate . ' ' .
              $secondord . ' ' .
              $inout_item_code . ' ' .
              $lotnum . ' ' .
              $item_name . ' ' .
              $unitprice . ' ' .
              $surang;

if ($mode == "update") {
    $update_log = date("Y-m-d H:i:s") . " - " . $_SESSION["name"] . " " . $update_log . "&#10";
    try {
        $pdo->beginTransaction();
        // Prepare the SQL query for updating the material lot information
        $sql = "UPDATE " . $DB . "." . $tablename . " SET ";
        $sql .= "registedate = ?, inoutdate = ?, secondord = ?, inout_item_code = ?, lotnum = ?, item_name = ?, unitprice = ?, surang = ?, comment = ?, searchtag = ?, update_log = ?, secondordnum = ? ";
        $sql .= "WHERE num = ? LIMIT 1"; // Update only one record matching the 'num'

        $stmh = $pdo->prepare($sql);

        // Bind the variables to the prepared statement as parameters
        $stmh->bindValue(1, $registedate, PDO::PARAM_STR);
        $stmh->bindValue(2, $inoutdate, PDO::PARAM_STR);
        $stmh->bindValue(3, $secondord, PDO::PARAM_STR);
        $stmh->bindValue(4, $inout_item_code, PDO::PARAM_STR);
        $stmh->bindValue(5, $lotnum, PDO::PARAM_STR);
        $stmh->bindValue(6, $item_name, PDO::PARAM_STR);
		$stmh->bindValue(7, str_replace(',', '', $unitprice), PDO::PARAM_STR);  // 숫자안에 콤마제거후 저장
		$stmh->bindValue(8, str_replace(',', '', $surang), PDO::PARAM_STR);
        $stmh->bindValue(9, $comment, PDO::PARAM_STR);
        $stmh->bindValue(10, $searchtag, PDO::PARAM_STR);
        $stmh->bindValue(11, $update_log, PDO::PARAM_STR);
        $stmh->bindValue(12, $secondordnum, PDO::PARAM_STR);
        $stmh->bindValue(13, $num, PDO::PARAM_INT);

        // Execute the statement
        $stmh->execute();
        $pdo->commit();
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        print "오류: " . $Exception->getMessage();
    }
}

if ($mode == "insert" || $mode == '' || $mode == null) {
    $update_log = date("Y-m-d H:i:s") . " - " . $_SESSION["name"] . " " . $update_log . "&#10";
    // Data insertion
    try {
        $pdo->beginTransaction();

        // Updated columns and values to be inserted
        $sql = "INSERT INTO " . $DB . "." . $tablename . " (";
        $sql .= "registedate, inoutdate, secondord, inout_item_code, lotnum, item_name, unitprice, surang, comment, searchtag, update_log, secondordnum ";
        $sql .= ") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmh = $pdo->prepare($sql);

        $stmh->bindValue(1, $registedate, PDO::PARAM_STR);
        $stmh->bindValue(2, $inoutdate, PDO::PARAM_STR);
        $stmh->bindValue(3, $secondord, PDO::PARAM_STR);
        $stmh->bindValue(4, $inout_item_code, PDO::PARAM_STR);
        $stmh->bindValue(5, $lotnum, PDO::PARAM_STR);
        $stmh->bindValue(6, $item_name, PDO::PARAM_STR);
		$stmh->bindValue(7, str_replace(',', '', $unitprice), PDO::PARAM_STR);
		$stmh->bindValue(8, str_replace(',', '', $surang), PDO::PARAM_STR);
        $stmh->bindValue(9, $comment, PDO::PARAM_STR);
        $stmh->bindValue(10, $searchtag, PDO::PARAM_STR);
        $stmh->bindValue(11, $update_log, PDO::PARAM_STR);
        $stmh->bindValue(12, $secondordnum, PDO::PARAM_STR);

        // Execute the statement
        $stmh->execute();
        $pdo->commit();
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        print "오류: " . $Exception->getMessage();
    }
}

if ($mode == "delete") { // Data deletion
    try {
        $pdo->beginTransaction();
        $sql = "UPDATE " .  $DB . "." . $tablename . " SET is_deleted=1 WHERE num = ?";  
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $num, PDO::PARAM_INT);      
        $stmh->execute();
        $pdo->commit();
    } catch (PDOException $ex) {
        $pdo->rollBack();
        print "오류: ".$ex->getMessage();
    }
}

$data = [   
 'num' => $num,
 'mode' => $mode
]; 

echo json_encode($data, JSON_UNESCAPED_UNICODE);

?>
