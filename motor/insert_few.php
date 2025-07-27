<?php   
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  
$num = isset($_REQUEST['num']) ? $_REQUEST['num'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$update_log = isset($_REQUEST['update_log']) ? $_REQUEST['update_log'] : '';
$outputdate = isset($_REQUEST['outputdate']) ? $_REQUEST['outputdate'] : '';

header("Content-Type: application/json");  // JSON 타입 헤더 설정

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();	

// Update log with current time and user session name
$update_log = date("Y-m-d H:i:s") . " - " . $_SESSION["name"] . " " . $update_log . "&#10";

try {
	$pdo->beginTransaction();
	$sql = "UPDATE " . $DB . ".motor SET 
			status=?, update_log=?, outputdate=? 
			WHERE num=? LIMIT 1";  // Only update status, update_log, and searchtag columns

	$stmh = $pdo->prepare($sql);

	// Bind the parameters
	$params = [$status, $update_log, $outputdate, $num];

	$stmh->execute($params);
	$pdo->commit();
} catch (PDOException $Exception) {
	$pdo->rollBack();
	print "오류: " . $Exception->getMessage();
}

$data = [   
    'num' => $num,
    'status' => $status,
    'outputdate' => $outputdate,
    'update_log' => $update_log
]; 
 
echo json_encode($data, JSON_UNESCAPED_UNICODE);

?>
