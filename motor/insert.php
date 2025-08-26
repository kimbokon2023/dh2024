 <?php   
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  
$num = isset($_REQUEST['num']) ? $_REQUEST['num'] : '';

header("Content-Type: application/json");  //json을 사용하기 위해 필요한 구문 받는측에서 필요한 정보임 ajax로 보내는 쪽에서 type : json
	 
include '_request.php';

$tablename = 'motor';
  
if (empty($price)) {
  $price = ''; // 빈 문자열인 경우 숫자 0으로 초기화
}

if (isset($_POST['orderlist'])) {
    $order_jsondata = json_decode($_POST['orderlist'], true);
} else {
    // Error handling or fallback
    $order_jsondata = null;
}

if (isset($_POST['accessorieslist'])) {
    $accessories_jsondata = json_decode($_POST['accessorieslist'], true);
} else {
    // Error handling or fallback
    $accessories_jsondata = null;
}

if (isset($_POST['controllerlist'])) {
    $controllerlist_jsondata = json_decode($_POST['controllerlist'], true);
} else {
    // Error handling or fallback
    $controllerlist_jsondata = null;
}

if (isset($_POST['fabriclist'])) {
    $fabriclist_jsondata = json_decode($_POST['fabriclist'], true);
} else {
    // Error handling or fallback
    $fabriclist_jsondata = null;
}

// 주소 따옴표 변환 '따옴표로
$address = str_replace('"', "'", $address);
$delbranchaddress = str_replace('"', "'", $delbranchaddress);
$delbranchtel = str_replace('"', "'", $delbranchtel);
$delbranchinvoice = str_replace('"', "'", $delbranchinvoice);
$delcarnumber = str_replace('"', "'", $delcarnumber);
$delcaritem = str_replace('"', "'", $delcaritem);
$delcartel = str_replace('"', "'", $delcartel);
$memo = str_replace('"', "'", $memo);
$comment = str_replace('"', "'", $comment);
$delmemo = str_replace('"', "'", $delmemo);
$secondordmemo = str_replace('"', "'", $secondordmemo);
$delwrappaymethod = str_replace('"', "'", $delwrappaymethod);
$cargo_delbranchinvoice = str_replace('"', "'", $cargo_delbranchinvoice);
$cargo_delwrapmethod = str_replace('"', "'", $cargo_delwrapmethod);
$cargo_delwrapsu = str_replace('"', "'", $cargo_delwrapsu);
$cargo_delwrapamount = str_replace('"', "'", $cargo_delwrapamount);
$cargo_delwrapweight = str_replace('"', "'", $cargo_delwrapweight);
$cargo_delwrappaymethod = str_replace('"', "'", $cargo_delwrappaymethod);
$original_num = str_replace('"', "'", $original_num);
$Deliverymanager = str_replace('"', "'", $Deliverymanager);	

// 검색 테그 초기화
// 모든 변수를 $searchtag에 추가
$searchtag = $workplacename . ' ' .              
	$secondord . ' ' .
	$secondordman . ' ' .
	$secondordmantel . ' ' .
	$chargedman . ' ' .
	$chargedmantel . ' ' .
	$address . ' ' .
	$delipay . ' ' .
	$deliverymethod . ' ' .
	$deliverypaymethod . ' ' .
	$delbranch . ' ' .
	$delbranchaddress . ' ' .
	$delbranchtel . ' ' .
	$delbranchinvoice . ' ' .
	$delcarnumber . ' ' .
	$delcaritem . ' ' .
	$delcartel . ' ' .
	$memo . ' ' .
	$comment . ' ' .
	$first_writer . ' ' .
	$update_log . ' ' .              
	$loadplace . ' ' . 
	$delwrapmethod . ' ' .
	$delwrapsu . ' ' .
	$delwrapamount . ' ' .
	$delwrapweight . ' ' .
	$status . ' ' .
	$delcompany . ' ' .
	$accessorieslist . ' ' .
	$controllerlist . ' ' .
	$orderlist . ' ' .
	$secondordmemo . ' ' .
	$delmemo . ' ' .
	$returncheck . ' ' .
	$cargo_delbranchinvoice . ' ' .
	$cargo_delwrapmethod . ' ' .
	$cargo_delwrapsu . ' ' .
	$cargo_delwrapamount . ' ' .
	$cargo_delwrapweight . ' ' .
	$cargo_delwrappaymethod .			  
	$delwrappaymethod;

// 마지막 쉼표와 공백 제거
$searchtag = rtrim($searchtag, ', ');

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();	

if ($mode == "modify") {
    $update_log = date("Y-m-d H:i:s") . " - " . $_SESSION["name"] . " " . $update_log . "&#10";

	// 등록일 자동등록
    if($registerdate==null or $registerdate == '0000-00-00'	)
		$registerdate = $orderdate;

	try {
		$pdo->beginTransaction();
		$sql = "UPDATE " . $DB . ".{$tablename}  SET 
				workplacename=?, status=?, order_total=?, screensu=?, steelsu=?, protectsu=?, smokesu=?, noendsu=?, 
				price=?, orderdate=?, deadline=?, outputdate=?, demand=?, secondord=?, secondordman=?, 
				secondordmantel=?, chargedman=?, chargedmantel=?, address=?, delipay=?, deliverymethod=?, 
				deliverypaymethod=?, memo=?, comment=?, orderlist=?, first_writer=?, update_log=?, searchtag=?, 
				is_deleted=?, accessorieslist=?, delbranch=?, delbranchaddress=?, delbranchtel=?, delbranchinvoice=?, 
				delcarnumber=?, delcaritem=?, delcartel=?, noscreensu=?, nosteelsu=?, noprotectsu=?, nosmokesu=?, loadplace=? , dcprice=?, dc_type=?, company_dc_value=? , site_dc_value=? , explosionsu=? , noexplosionsu=?, 
				realscreensu=? ,realsteelsu=? , realprotectsu=? , realsmokesu =?, realexplosionsu = ?, 	delwrapmethod=?,delwrapsu=?,delwrapamount=?,delwrapweight=?,delwrappaymethod=?, delcompany=?, secondordnum=?, registerdate=?, deltime=?,	
				totalprice=? ,screen_price=? , screen_dcprice=? , screen_dc_type=?, screen_company_dc_value= ?, screen_site_dc_value =?, dcadd=?, notdcprice=?, dctotal=?, secondordmemo=?, sendcheck=?, deldowntime=?, delmemo=?, del_status=?, del_writememo=?, controllerlist=?,  controller_price=?, controller_dcprice=?, controller_dc_type=?, controller_company_dc_value=?, controller_site_dc_value=?, returncheck=?, returndue=?, getdate=?,
				polesu=? , nopolesu=? , realpolesu=?, fabric_dc_type=? ,fabric_price=? ,fabric_company_dc_value=? ,fabric_site_dc_value=? ,fabric_dcprice=?, fabriclist=?, cargo_delbranchinvoice = ?, cargo_delwrapmethod = ?, cargo_delwrapsu = ?, cargo_delwrapamount = ?, cargo_delwrapweight = ?, cargo_delwrappaymethod = ?, original_num = ?, Deliverymanager = ?  ,
				custNote=? 
				WHERE num=? LIMIT 1";			

		$stmh = $pdo->prepare($sql);

		$params = [
			$workplacename, $status, $order_total, $screensu, $steelsu, $protectsu, $smokesu, $noendsu,
			$price, $orderdate, $deadline, $outputdate, $demand, $secondord, $secondordman,
			$secondordmantel, $chargedman, $chargedmantel, $address, $delipay, $deliverymethod,
			$deliverypaymethod, $memo, $comment, json_encode($order_jsondata), $first_writer, $update_log, $searchtag,
			$is_deleted,  json_encode($accessories_jsondata), $delbranch, $delbranchaddress, $delbranchtel, $delbranchinvoice, 
			$delcarnumber, $delcaritem, $delcartel, $noscreensu, $nosteelsu, $noprotectsu, $nosmokesu, $loadplace, $dcprice, $dc_type, $company_dc_value, $site_dc_value, $explosionsu , $noexplosionsu,
			$realscreensu  ,$realsteelsu  , $realprotectsu  , $realsmokesu  , $realexplosionsu ,$delwrapmethod,$delwrapsu,$delwrapamount,$delwrapweight,$delwrappaymethod, $delcompany, $secondordnum, $registerdate, $deltime,
			$totalprice , $screen_price , $screen_dcprice , $screen_dc_type  , $screen_company_dc_value , $screen_site_dc_value , $dcadd, $notdcprice, $dctotal, $secondordmemo, $sendcheck, $deldowntime, $delmemo, $del_status, $del_writememo, json_encode($controllerlist_jsondata), $controller_price, $controller_dcprice, $controller_dc_type, $controller_company_dc_value, $controller_site_dc_value, $returncheck, 
			$returndue, $getdate, $polesu , $nopolesu, $realpolesu,
			$fabric_dc_type, $fabric_price, $fabric_company_dc_value, $fabric_site_dc_value, $fabric_dcprice, json_encode($fabriclist_jsondata), $cargo_delbranchinvoice , $cargo_delwrapmethod , $cargo_delwrapsu , $cargo_delwrapamount , $cargo_delwrapweight , $cargo_delwrappaymethod, $original_num, $Deliverymanager, 
			$custNote,
			$num
		];

		$stmh->execute($params);
		$pdo->commit();
	} catch (PDOException $Exception) {
		$pdo->rollBack();
		print "오류: " . $Exception->getMessage();
	}

}

if($mode=="insert") {
    // 최초등록자 정보와 등록 시간 기록
    $first_writer = $_SESSION["name"] . " _" . date("Y-m-d H:i:s");
    
	$update_log = '';
	
	// 등록일 자동등록
    if($registerdate==null or $registerdate == '0000-00-00'	)
		$registerdate = $orderdate;

	try {
		$pdo->beginTransaction();

		// 삽입할 데이터의 SQL 쿼리 생성
		$sql = "INSERT INTO " . $DB . ".{$tablename}  (workplacename, status, order_total, screensu, steelsu, protectsu, smokesu, noendsu, 
				price, orderdate, deadline, outputdate, demand, secondord, secondordman, secondordmantel, chargedman, chargedmantel, 
				address, delipay, deliverymethod, deliverypaymethod, memo, comment, first_writer, update_log, searchtag, is_deleted, orderlist, accessorieslist,
				delbranch, delbranchaddress, delbranchtel, delbranchinvoice, delcarnumber, delcaritem, delcartel, nosteelsu, noprotectsu, nosmokesu, loadplace, dcprice, dc_type, company_dc_value, site_dc_value,explosionsu , noexplosionsu ,
				realscreensu  , realsteelsu  ,  realprotectsu  ,  realsmokesu , realexplosionsu, delwrapmethod,delwrapsu,delwrapamount,delwrapweight,delwrappaymethod, delcompany, secondordnum, registerdate, deltime ,totalprice , screen_price , screen_dcprice , screen_dc_type  , screen_company_dc_value, screen_site_dc_value, dcadd, notdcprice, dctotal , secondordmemo, sendcheck, deldowntime, delmemo, del_status, del_writememo, controllerlist, controller_price, controller_dcprice, controller_dc_type, controller_company_dc_value, controller_site_dc_value, returncheck, returndue, getdate, polesu , nopolesu, realpolesu,
				fabric_dc_type, fabric_price, fabric_company_dc_value, fabric_site_dc_value, fabric_dcprice, fabriclist, cargo_delbranchinvoice , cargo_delwrapmethod , cargo_delwrapsu , cargo_delwrapamount , cargo_delwrapweight , cargo_delwrappaymethod, original_num , Deliverymanager,
				custNote )
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
				?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
				?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
				?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";  

		$stmh = $pdo->prepare($sql);

		// 파라미터 바인딩
		$params = [
			$workplacename, $status, $order_total, $screensu, $steelsu, $protectsu, $smokesu, $noendsu,
			$price, $orderdate, $deadline, $outputdate, $demand, $secondord, $secondordman, $secondordmantel, $chargedman, $chargedmantel,
			$address, $delipay, $deliverymethod, $deliverypaymethod, $memo, $comment, $first_writer, $update_log, $searchtag, $is_deleted, json_encode($order_jsondata), json_encode($accessories_jsondata),
			$delbranch, $delbranchaddress, $delbranchtel, $delbranchinvoice, $delcarnumber, $delcaritem, $delcartel, $nosteelsu, $noprotectsu, $nosmokesu, $loadplace, $dcprice, $dc_type, $company_dc_value, $site_dc_value, $explosionsu , $noexplosionsu,
			$realscreensu  ,$realsteelsu  , $realprotectsu  , $realsmokesu  , $realexplosionsu, $delwrapmethod,$delwrapsu,$delwrapamount,$delwrapweight,$delwrappaymethod, $delcompany, $secondordnum, $registerdate, $deltime,
			$totalprice , $screen_price , $screen_dcprice , $screen_dc_type  , $screen_company_dc_value , $screen_site_dc_value, $dcadd, $notdcprice, $dctotal, $secondordmemo, $sendcheck, $deldowntime, $delmemo, $del_status, $del_writememo, json_encode($controllerlist_jsondata), $controller_price, $controller_dcprice, $controller_dc_type, $controller_company_dc_value, $controller_site_dc_value, $returncheck,
			$returndue, $getdate, $polesu, $nopolesu, $realpolesu, $fabric_dc_type, $fabric_price, $fabric_company_dc_value, $fabric_site_dc_value, $fabric_dcprice,json_encode($fabriclist_jsondata),
			$cargo_delbranchinvoice , $cargo_delwrapmethod , $cargo_delwrapsu , $cargo_delwrapamount , $cargo_delwrapweight , $cargo_delwrappaymethod, $original_num, $Deliverymanager,
			$custNote
			];

		$stmh->execute($params);
		$pdo->commit();
	} catch (PDOException $Exception) {
		$pdo->rollBack();
		print "오류: " . $Exception->getMessage();
	}

}
   
// 파일복사(분리복사)일 경우 사진데이터/엑셀데이터/랜더링 데이터를 함께 복사해주는 루틴제작
if($mode=="copy")
{
	
// 과거 DATA num   => $oldnum   
// 레코드의 최신것 하나

$sql = "select * from " . $DB . ".{$tablename}  ORDER BY num DESC";

try{  
		$stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh    
		$row = $stmh->fetch(PDO::FETCH_ASSOC); 
        $num = $row["num"];
  } catch (PDOException $Exception) {
  print "오류: ".$Exception->getMessage();
  }  		
}

if ($mode == "delete") {
    try {
        $pdo->beginTransaction();  // 트랜잭션 시작
        $query = "UPDATE " . $DB . ".{$tablename}  SET is_deleted=1 WHERE num=? LIMIT 1";
        $stmh = $pdo->prepare($query);
        $params = [$num];
        $stmh->execute($params);
        $pdo->commit();  // 데이터 변경 사항을 커밋
    } catch (PDOException $Exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();  // 오류 발생 시 롤백
        }
        print "오류: " . $Exception->getMessage();
    }
}

if ($mode == "insert" || $mode == "copy" ){
	try{
		$sql = "SELECT * FROM " . $DB . ".{$tablename}  ORDER BY num DESC LIMIT 1";
		$stmh = $pdo->prepare($sql);  
		$stmh->execute();                  
		$row = $stmh->fetch(PDO::FETCH_ASSOC);	 
		$num = $row["num"];	 
	}
	catch (PDOException $Exception) {
		error_log("오류: " . $Exception->getMessage());  // 오류 로깅
		echo "시스템 오류가 발생했습니다. 관리자에게 문의하세요.";  // 사용자 친화적 메시지
	} 
}
 
$data = [   
 'num' => $num,
 'mode' => $mode
  ]; 
 
 echo json_encode($data, JSON_UNESCAPED_UNICODE);
 ?>
