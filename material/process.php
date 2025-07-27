<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

$tablename = isset($_REQUEST['tablename']) ? $_REQUEST['tablename'] : '';  
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  

header("Content-Type: application/json");  // Use JSON content type

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

include "_request.php";

$searchtag = $item_code . ' ' .
              $item_name . ' ' .
              $spec . ' ' .
              $spec_info . ' ' .
              $unit . ' ' .
              $item_type . ' ' .
              $set_flag . ' ' .
              $stock . ' ' .
              $prod_proc . ' ' .
              $in_price . ' ' .
              $in_price_vat . ' ' .
              $out_price . ' ' .
              $out_price_vat . ' ' .
              $is_deleted;

if ($mode == "update") {
    $update_log = date("Y-m-d H:i:s") . " - " . $_SESSION["name"] . " " . $update_log . "&#10";
    try {
        $pdo->beginTransaction();
        // Prepare the SQL query for updating the material information
        $sql = "UPDATE " . $DB . "." . $tablename . " SET ";
        $sql .= "item_code = ?, item_name = ?, spec = ?, spec_info = ?, ";
        $sql .= "unit = ?, item_type = ?, set_flag = ?, stock = ?, prod_proc = ?, ";
        $sql .= "in_price = ?, in_price_vat = ?, out_price = ?, out_price_vat = ?, searchtag = ?, update_log = ?, is_deleted = ? ";
        $sql .= "WHERE num = ? LIMIT 1"; // Update only one record matching the 'num'

        $stmh = $pdo->prepare($sql);

        // Bind the variables to the prepared statement as parameters
        $stmh->bindValue(1, $item_code, PDO::PARAM_STR);
        $stmh->bindValue(2, $item_name, PDO::PARAM_STR);
        $stmh->bindValue(3, $spec, PDO::PARAM_STR);
        $stmh->bindValue(4, $spec_info, PDO::PARAM_STR);
        $stmh->bindValue(5, $unit, PDO::PARAM_STR);
        $stmh->bindValue(6, $item_type, PDO::PARAM_STR);
        $stmh->bindValue(7, $set_flag, PDO::PARAM_STR);
        $stmh->bindValue(8, $stock, PDO::PARAM_STR);
        $stmh->bindValue(9, $prod_proc, PDO::PARAM_STR);
        $stmh->bindValue(10, $in_price, PDO::PARAM_STR);
        $stmh->bindValue(11, $in_price_vat, PDO::PARAM_STR);
        $stmh->bindValue(12, $out_price, PDO::PARAM_STR);
        $stmh->bindValue(13, $out_price_vat, PDO::PARAM_STR);
        $stmh->bindValue(14, $searchtag, PDO::PARAM_STR);
        $stmh->bindValue(15, $update_log, PDO::PARAM_STR);
        $stmh->bindValue(16, $is_deleted, PDO::PARAM_INT);
        $stmh->bindValue(17, $num, PDO::PARAM_INT);

        // Execute the statement
        $stmh->execute();
        $pdo->commit();
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        print "오류: " . $Exception->getMessage();
    }
}

if ($mode == "insert") {
    $update_log = date("Y-m-d H:i:s") . " - " . $_SESSION["name"] . " " . $update_log . "&#10";
    // Data insertion
    try {
        $pdo->beginTransaction();

        // Updated columns and values to be inserted
        $sql = "INSERT INTO " . $DB . "." . $tablename . " (";
        $sql .= "item_code, item_name, spec, spec_info, unit, item_type, set_flag, stock, prod_proc, ";
        $sql .= "in_price, in_price_vat, out_price, out_price_vat, searchtag, update_log, is_deleted";
        $sql .= ") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmh = $pdo->prepare($sql);

        $stmh->bindValue(1, $item_code, PDO::PARAM_STR);
        $stmh->bindValue(2, $item_name, PDO::PARAM_STR);
        $stmh->bindValue(3, $spec, PDO::PARAM_STR);
        $stmh->bindValue(4, $spec_info, PDO::PARAM_STR);
        $stmh->bindValue(5, $unit, PDO::PARAM_STR);
        $stmh->bindValue(6, $item_type, PDO::PARAM_STR);
        $stmh->bindValue(7, $set_flag, PDO::PARAM_STR);
        $stmh->bindValue(8, $stock, PDO::PARAM_STR);
        $stmh->bindValue(9, $prod_proc, PDO::PARAM_STR);
        $stmh->bindValue(10, $in_price, PDO::PARAM_STR);
        $stmh->bindValue(11, $in_price_vat, PDO::PARAM_STR);
        $stmh->bindValue(12, $out_price, PDO::PARAM_STR);
        $stmh->bindValue(13, $out_price_vat, PDO::PARAM_STR);
        $stmh->bindValue(14, $searchtag, PDO::PARAM_STR);
        $stmh->bindValue(15, $update_log, PDO::PARAM_STR);
        $stmh->bindValue(16, $is_deleted, PDO::PARAM_INT);

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
