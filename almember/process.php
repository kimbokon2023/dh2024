<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

$tablename = isset($_REQUEST['tablename']) ? $_REQUEST['tablename'] : 'almember';  
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  
$num = isset($_REQUEST['num']) ? $_REQUEST['num'] : '';  

header("Content-Type: application/json");  // Use JSON content type

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

include '_request.php';

if ($mode == "update") {
    $update_log = date("Y-m-d H:i:s") . " - " . $_SESSION["name"] . " " . $update_log . "&#10";
    try {
        $pdo->beginTransaction();
        $sql = "UPDATE " . $DB . "." . $tablename . " SET ";
        $sql .= "name = ?, part = ?, dateofentry = ?, referencedate = ?, availableday = ?, comment = ?, update_log = ?, company=?  ";
        $sql .= "WHERE num = ? LIMIT 1";

        $stmh = $pdo->prepare($sql);

        $stmh->bindValue(1, $name, PDO::PARAM_STR);
        $stmh->bindValue(2, $part, PDO::PARAM_STR);
        $stmh->bindValue(3, $dateofentry, PDO::PARAM_STR);
        $stmh->bindValue(4, $referencedate, PDO::PARAM_STR);
        $stmh->bindValue(5, $availableday, PDO::PARAM_STR);
        $stmh->bindValue(6, $comment, PDO::PARAM_STR);        
        $stmh->bindValue(7, $update_log, PDO::PARAM_STR);        
        $stmh->bindValue(8, $company, PDO::PARAM_STR);        
        $stmh->bindValue(9, $num, PDO::PARAM_INT);

        $stmh->execute();
        $pdo->commit();
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        echo json_encode(["error" => $Exception->getMessage()]);
        exit;
    }
}

if ($mode == "insert" || $mode == '' || $mode == null) {
    $update_log = date("Y-m-d H:i:s") . " - " . $_SESSION["name"] . " " . $update_log . "&#10";
    try {
        $pdo->beginTransaction();

        $sql = "INSERT INTO " . $DB . "." . $tablename . " (";
        $sql .= "name, part, dateofentry, referencedate, availableday, comment, update_log, company ";
        $sql .= ") VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmh = $pdo->prepare($sql);

        $stmh->bindValue(1, $name, PDO::PARAM_STR);
        $stmh->bindValue(2, $part, PDO::PARAM_STR);
        $stmh->bindValue(3, $dateofentry, PDO::PARAM_STR);
        $stmh->bindValue(4, $referencedate, PDO::PARAM_STR);
        $stmh->bindValue(5, $availableday, PDO::PARAM_STR);
        $stmh->bindValue(6, $comment, PDO::PARAM_STR);
        $stmh->bindValue(7, $update_log, PDO::PARAM_STR);
        $stmh->bindValue(8, $company, PDO::PARAM_STR);

        $stmh->execute();
        $pdo->commit();
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        echo json_encode(["error" => $Exception->getMessage()]);
        exit;
    }
}

if ($mode == "delete") {
    try {
        $pdo->beginTransaction();
        $sql = "UPDATE " .  $DB . "." . $tablename . " SET is_deleted=1 WHERE num = ?";  
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $num, PDO::PARAM_INT);      
        $stmh->execute();
        $pdo->commit();
    } catch (PDOException $ex) {
        $pdo->rollBack();
        echo json_encode(["error" => $ex->getMessage()]);
        exit;
    }
}

$data = [   
 'num' => $num,
 'mode' => $mode
]; 

echo json_encode($data, JSON_UNESCAPED_UNICODE);

?>
