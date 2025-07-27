<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");

$tablename = 'todos_monthly';  
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  
$num = isset($_REQUEST['num']) ? $_REQUEST['num'] : '';  

header("Content-Type: application/json");  // Use JSON content type

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

$registdate = isset($_POST['registdate']) ? $_POST['registdate'] : '';
$itemsep = isset($_POST['itemsep']) ? $_POST['itemsep'] : '';
$specialday = isset($_POST['specialday']) ? $_POST['specialday'] : '';
$title = isset($_POST['title']) ? $_POST['title'] : '';
$first_writer = isset($_POST['first_writer']) ? $_POST['first_writer'] : $_SESSION["name"];
$update_log = isset($_POST['update_log']) ? $_POST['update_log'] : '';
$yearlyspecialday = isset($_POST['yearlyspecialday']) ? $_POST['yearlyspecialday'] : '';  // Capture the yearlyspecialday
$searchtag = $title . ' ' . $itemsep . ' ' . $first_writer;

// Update operation
if ($mode == "modify" && $num) {
    $update_log .= date("Y-m-d H:i:s") . " - " . $_SESSION["name"] . " updated the record.\n";
    try {
        $pdo->beginTransaction();
        $sql = "UPDATE $tablename SET 
                registdate = ?, itemsep = ?, specialday = ?, yearlyspecialday = ?, title = ?, 
                first_writer = ?, update_log = ?, searchtag = ?
                WHERE num = ? AND (is_deleted = 0 or is_deleted IS NULL) ";

        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $registdate, PDO::PARAM_STR);
        $stmh->bindValue(2, $itemsep, PDO::PARAM_STR);
        $stmh->bindValue(3, $specialday, PDO::PARAM_STR);
        $stmh->bindValue(4, $yearlyspecialday, PDO::PARAM_STR);  // Bind yearlyspecialday to the SQL query
        $stmh->bindValue(5, $title, PDO::PARAM_STR);
        $stmh->bindValue(6, $first_writer, PDO::PARAM_STR);
        $stmh->bindValue(7, $update_log, PDO::PARAM_STR);
        $stmh->bindValue(8, $searchtag, PDO::PARAM_STR);
        $stmh->bindValue(9, $num, PDO::PARAM_INT);

        $stmh->execute();
        $pdo->commit();
        echo json_encode(["message" => "Update successful"], JSON_UNESCAPED_UNICODE);
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        echo json_encode(["error" => $Exception->getMessage()], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
// Insert operation
elseif ($mode == "insert" || empty($mode)) {
    $update_log .= date("Y-m-d H:i:s") . " - " . $_SESSION["name"] . " created the record.\n";
    try {
        $pdo->beginTransaction();
        $sql = "INSERT INTO $tablename (
                registdate, itemsep, specialday, yearlyspecialday, title, 
                first_writer, update_log, searchtag
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $registdate, PDO::PARAM_STR);
        $stmh->bindValue(2, $itemsep, PDO::PARAM_STR);
        $stmh->bindValue(3, $specialday, PDO::PARAM_STR);
        $stmh->bindValue(4, $yearlyspecialday, PDO::PARAM_STR);  // Bind yearlyspecialday to the SQL query
        $stmh->bindValue(5, $title, PDO::PARAM_STR);
        $stmh->bindValue(6, $first_writer, PDO::PARAM_STR);
        $stmh->bindValue(7, $update_log, PDO::PARAM_STR);
        $stmh->bindValue(8, $searchtag, PDO::PARAM_STR);

        $stmh->execute();
        $pdo->commit();
        echo json_encode(["message" => "Insert successful"], JSON_UNESCAPED_UNICODE);
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        echo json_encode(["error" => $Exception->getMessage()], JSON_UNESCAPED_UNICODE);
        exit;
    }
} elseif ($mode == "delete" && $num) {
    try {
        $pdo->beginTransaction();
        $sql = "UPDATE $tablename SET is_deleted = 1 WHERE num = ?";  
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $num, PDO::PARAM_INT);      
        $stmh->execute();
        $pdo->commit();
        echo json_encode(["message" => "Delete successful"], JSON_UNESCAPED_UNICODE);
    } catch (PDOException $ex) {
        $pdo->rollBack();
        echo json_encode(["error" => $ex->getMessage()], JSON_UNESCAPED_UNICODE);
        exit;
    }
} else {
    echo json_encode(["error" => "Invalid operation"], JSON_UNESCAPED_UNICODE);
}



?>
