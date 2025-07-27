 <?php   
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  
$num = isset($_REQUEST['num']) ? $_REQUEST['num'] : '';

header("Content-Type: application/json");  //json을 사용하기 위해 필요한 구문 받는측에서 필요한 정보임 ajax로 보내는 쪽에서 type : json
	 
include '_request.php';
  
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();	

if ($mode == "modify") {
    $update_log = date("Y-m-d H:i:s") . " - " . $_SESSION["name"] . " " . $update_log . "&#10";

	// 등록일 자동등록
    if($registerdate==null or $registerdate == '0000-00-00'	)
		$registerdate = $orderdate;

	try {
		$pdo->beginTransaction();
		$sql = "UPDATE " . $DB . ".motor SET delcarnumber=?, delcaritem=?, delcartel=?, delipay=?, del_status=?, del_writememo=?  
				WHERE num=? LIMIT 1";			

		$stmh = $pdo->prepare($sql);

		$params = [ $delcarnumber, $delcaritem, $delcartel,$delipay,  $del_status, $del_writememo, $num ];

		$stmh->execute($params);
		$pdo->commit();
	} catch (PDOException $Exception) {
		$pdo->rollBack();
		print "오류: " . $Exception->getMessage();
	}

}

 
$data = [   
 'num' => $num,
 'mode' => $mode
 
 ]; 
 
 echo json_encode($data, JSON_UNESCAPED_UNICODE);

 ?>
