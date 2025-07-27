<?php   
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

header("Content-Type: application/json");  // Set content type for JSON response

$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';

include '_request.php';  

$tablename = 'fee';

$searchtag =  $volt . ' ' .
              $item . ' ' .
              $upweight . ' ' .
              $unit . ' ' .
              $originalcost . ' ' .
              $memo . ' ' .
              $price;
			  
$searchtag = trim($searchtag);

$wire = json_decode($_REQUEST['wire'] ?? '[]', true);
$volt = json_decode($_REQUEST['volt'] ?? '[]', true);
$unit = json_decode($_REQUEST['unit'] ?? '[]', true);
$upweight = json_decode($_REQUEST['upweight'] ?? '[]', true);
$price = json_decode($_REQUEST['price'] ?? '[]', true);
$originalcost = json_decode($_REQUEST['originalcost'] ?? '[]', true);
$item = json_decode($_REQUEST['item'] ?? '[]', true);
$ecountcode = json_decode($_REQUEST['ecountcode'] ?? '[]', true);
$yuan = json_decode($_REQUEST['yuan'] ?? '[]', true);

$fieldarr = [
    'wire', 'volt ', 'item ', 'upweight ', 'unit ', 
    'originalcost ', 'price ','ecountcode', 'yuan'
];

require_once("../lib/mydb.php");
$pdo = db_connect();


$strarr = [];

// Prepare values for modification
if ($mode == "modify") {
   $update_log = date("Y-m-d H:i:s") . " - " . $_SESSION["name"] . " " . $update_log . "&#10";

	try {
		$pdo->beginTransaction();

		// Prepare the SQL statement for update
		$sql = "UPDATE " . $DB . "." . $tablename . " SET "
			 . "basicdate = ? , wire = ?, volt = ?, item = ?, upweight = ?, unit = ?, originalcost = ?, price = ?, memo = ?, update_log = ?, searchtag = ?, ecountcode=?, yuan=?  "
			 . " WHERE num = ?";

		$stmh = $pdo->prepare($sql);

		// Bind values to the placeholders
		$stmh->bindValue(1, $basicdate, PDO::PARAM_STR);
		$stmh->bindValue(2, json_encode($wire, JSON_UNESCAPED_UNICODE), PDO::PARAM_STR);
		$stmh->bindValue(3, json_encode($volt, JSON_UNESCAPED_UNICODE), PDO::PARAM_STR);
		$stmh->bindValue(4, json_encode($item, JSON_UNESCAPED_UNICODE), PDO::PARAM_STR);
		$stmh->bindValue(5, json_encode($upweight, JSON_UNESCAPED_UNICODE), PDO::PARAM_STR);
		$stmh->bindValue(6, json_encode($unit, JSON_UNESCAPED_UNICODE), PDO::PARAM_STR);
		$stmh->bindValue(7, json_encode($originalcost, JSON_UNESCAPED_UNICODE), PDO::PARAM_STR);
		$stmh->bindValue(8, json_encode($price, JSON_UNESCAPED_UNICODE), PDO::PARAM_STR);		
		$stmh->bindValue(9, $memo, PDO::PARAM_STR);
		$stmh->bindValue(10, $update_log, PDO::PARAM_STR);
		$stmh->bindValue(11, $searchtag, PDO::PARAM_STR);
		$stmh->bindValue(12, json_encode($ecountcode, JSON_UNESCAPED_UNICODE), PDO::PARAM_STR);		
		$stmh->bindValue(13, json_encode($yuan, JSON_UNESCAPED_UNICODE), PDO::PARAM_STR);		
		$stmh->bindValue(14, $num, PDO::PARAM_INT);

		// Execute the statement
		$stmh->execute();
		$pdo->commit();
	} catch (PDOException $Exception) {
		$pdo->rollBack();
		print "오류: " . $Exception->getMessage();
	}

}

else if ($mode == "delete") {
   	$update_log = date("Y-m-d H:i:s") . " - " . $_SESSION["name"] . " " . $update_log . "&#10";
	
	try {
		$pdo->beginTransaction();   
		$sql = "UPDATE " . $DB . "." . $tablename . " SET update_log=?, is_deleted=? ";    		
		$sql .= "  WHERE num = ? LIMIT 1";

		$stmh = $pdo->prepare($sql);
		
		$is_deleted = 1;
		
		 $stmh->bindValue(1, $update_log, PDO::PARAM_STR);  
		 $stmh->bindValue(2, $is_deleted, PDO::PARAM_STR);  
		 $stmh->bindValue(3, $num, PDO::PARAM_STR);		

		$stmh->execute();
		$pdo->commit();
	} catch (PDOException $Exception) {
		$pdo->rollBack();
		print "오류: " . $Exception->getMessage();
	}	
	
}

else  {

	$update_log = date("Y-m-d H:i:s") . " - " . $_SESSION["name"] . " " . $update_log . "&#10";

	try {
		$pdo->beginTransaction();   
		$sql = "INSERT INTO " . $DB . "." . $tablename . " (basicdate, wire, volt, item, upweight, unit, originalcost, price, memo, update_log, searchtag, ecountcode, yuan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

		// Prepare the statement
		$stmh = $pdo->prepare($sql);

		// Bind values to the placeholders		
		$stmh->bindValue(1, $basicdate, PDO::PARAM_STR);
		$stmh->bindValue(2,  json_encode($wire), PDO::PARAM_STR);
		$stmh->bindValue(3,  json_encode($volt), PDO::PARAM_STR);
		$stmh->bindValue(4,  json_encode($item), PDO::PARAM_STR);
		$stmh->bindValue(5,  json_encode($upweight), PDO::PARAM_STR);
		$stmh->bindValue(6,  json_encode($unit), PDO::PARAM_STR);
		$stmh->bindValue(7,  json_encode($originalcost), PDO::PARAM_STR);
		$stmh->bindValue(8,  json_encode($price), PDO::PARAM_STR);
		$stmh->bindValue(9,  $memo, PDO::PARAM_STR);
		$stmh->bindValue(10, $update_log, PDO::PARAM_STR);		
		$stmh->bindValue(11, $searchtag, PDO::PARAM_STR);
		$stmh->bindValue(12, json_encode($ecountcode, JSON_UNESCAPED_UNICODE), PDO::PARAM_STR);			
		$stmh->bindValue(13, json_encode($yuan, JSON_UNESCAPED_UNICODE), PDO::PARAM_STR);			

		// Execute the statement
		$stmh->execute();
		$pdo->commit();		
	} catch (PDOException $Exception) {
		$pdo->rollBack();
		print "오류: " . $Exception->getMessage();
	}

}


$data = [
    'num' => $num,
    'mode' => $mode,
    'memo' => $memo,
    'basicdate' => $basicdate
];

echo json_encode($data, JSON_UNESCAPED_UNICODE);

?>
