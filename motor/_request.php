<?php 
$num = isset($_REQUEST['num']) ? $_REQUEST['num'] : '';
$workplacename = isset($_REQUEST['workplacename']) ? $_REQUEST['workplacename'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$order_total = isset($_REQUEST['order_total']) ? $_REQUEST['order_total'] : '';
$screensu = isset($_REQUEST['screensu']) ? $_REQUEST['screensu'] : '';
$steelsu = isset($_REQUEST['steelsu']) ? $_REQUEST['steelsu'] : '';
$protectsu = isset($_REQUEST['protectsu']) ? $_REQUEST['protectsu'] : '';
$smokesu = isset($_REQUEST['smokesu']) ? $_REQUEST['smokesu'] : '';
$noendsu = isset($_REQUEST['noendsu']) ? $_REQUEST['noendsu'] : '';
$noscreensu = isset($_REQUEST['noscreensu']) ? $_REQUEST['noscreensu'] : '';
$nosteelsu = isset($_REQUEST['nosteelsu']) ? $_REQUEST['nosteelsu'] : '';
$noprotectsu = isset($_REQUEST['noprotectsu']) ? $_REQUEST['noprotectsu'] : '';
$nosmokesu = isset($_REQUEST['nosmokesu']) ? $_REQUEST['nosmokesu'] : '';
$orderdate = isset($_REQUEST['orderdate']) ? $_REQUEST['orderdate'] :  date("Y-m-d");
$deadline = isset($_REQUEST['deadline']) ? $_REQUEST['deadline'] : '';
$outputdate = isset($_REQUEST['outputdate']) ? $_REQUEST['outputdate'] : '';
$demand = isset($_REQUEST['demand']) ? $_REQUEST['demand'] : '';
$secondord = isset($_REQUEST['secondord']) ? $_REQUEST['secondord'] : '';
$secondordman = isset($_REQUEST['secondordman']) ? $_REQUEST['secondordman'] : '';
$secondordmantel = isset($_REQUEST['secondordmantel']) ? $_REQUEST['secondordmantel'] : '';
$chargedman = isset($_REQUEST['chargedman']) ? $_REQUEST['chargedman'] : '';
$chargedmantel = isset($_REQUEST['chargedmantel']) ? $_REQUEST['chargedmantel'] : '';
$address = isset($_REQUEST['address']) ? $_REQUEST['address'] : '';
$delipay = isset($_REQUEST['delipay']) ? $_REQUEST['delipay'] : '';
$deliverymethod = isset($_REQUEST['deliverymethod']) ? $_REQUEST['deliverymethod'] : '화물'; // '화물'을 기본값으로 설정
$deliverypaymethod = isset($_REQUEST['deliverypaymethod']) ? $_REQUEST['deliverypaymethod'] : '선불'; // '선불'을 기본값으로 설정
$delbranch = isset($_REQUEST['delbranch']) ? $_REQUEST['delbranch'] : '';
$delbranchaddress = isset($_REQUEST['delbranchaddress']) ? $_REQUEST['delbranchaddress'] : '';
$delbranchtel = isset($_REQUEST['delbranchtel']) ? $_REQUEST['delbranchtel'] : '';
$delbranchinvoice = isset($_REQUEST['delbranchinvoice']) ? $_REQUEST['delbranchinvoice'] : '';
$delcarnumber = isset($_REQUEST['delcarnumber']) ? $_REQUEST['delcarnumber'] : '';
$delcaritem = isset($_REQUEST['delcaritem']) ? $_REQUEST['delcaritem'] : '';
$delcartel = isset($_REQUEST['delcartel']) ? $_REQUEST['delcartel'] : '';
$memo = isset($_REQUEST['memo']) ? $_REQUEST['memo'] : '';
$comment = isset($_REQUEST['comment']) ? $_REQUEST['comment'] : '';
$first_writer = isset($_REQUEST['first_writer']) ? $_REQUEST['first_writer'] : '';
$update_log = isset($_REQUEST['update_log']) ? $_REQUEST['update_log'] : '';
$searchtag = isset($_REQUEST['searchtag']) ? $_REQUEST['searchtag'] : '';
$loadplace = isset($_REQUEST['loadplace']) ? $_REQUEST['loadplace'] : '';
$is_deleted = isset($_REQUEST['is_deleted']) ? $_REQUEST['is_deleted'] : NULL;	
$orderlist = isset($_REQUEST['orderlist']) ? $_REQUEST['orderlist'] : '{}';
$accessorieslist = isset($_REQUEST['accessorieslist']) ? $_REQUEST['accessorieslist'] : '{}';	
$controllerlist = isset($_REQUEST['controllerlist']) ? $_REQUEST['controllerlist'] : '{}';	
$explosionsu = isset($_REQUEST['explosionsu']) ? $_REQUEST['explosionsu'] : '';
$noexplosionsu = isset($_REQUEST['noexplosionsu']) ? $_REQUEST['noexplosionsu'] : '';
$realscreensu = isset($_REQUEST['realscreensu']) ? $_REQUEST['realscreensu'] : '';
$realsteelsu = isset($_REQUEST['realsteelsu']) ? $_REQUEST['realsteelsu'] : '';
$realprotectsu = isset($_REQUEST['realprotectsu']) ? $_REQUEST['realprotectsu'] : '';
$realsmokesu = isset($_REQUEST['realsmokesu']) ? $_REQUEST['realsmokesu'] : '';
$realexplosionsu = isset($_REQUEST['realexplosionsu']) ? $_REQUEST['realexplosionsu'] : '';

$polesu = isset($_REQUEST['polesu']) ? $_REQUEST['polesu'] : '';
$nopolesu = isset($_REQUEST['nopolesu']) ? $_REQUEST['nopolesu'] : '';
$realpolesu = isset($_REQUEST['realpolesu']) ? $_REQUEST['realpolesu'] : '';

$delwrapmethod = isset($_REQUEST['delwrapmethod']) ? $_REQUEST['delwrapmethod'] : '';
$delwrapsu = isset($_REQUEST['delwrapsu']) ? $_REQUEST['delwrapsu'] : '';
$delwrapamount = isset($_REQUEST['delwrapamount']) ? $_REQUEST['delwrapamount'] : '';
$delwrapweight = isset($_REQUEST['delwrapweight']) ? $_REQUEST['delwrapweight'] : '';
$delwrappaymethod = isset($_REQUEST['delwrappaymethod']) ? $_REQUEST['delwrappaymethod'] : '';

// // !!를 "로 변환
// $orderlist = str_replace('!!', '"', $orderlist);
// $accessorieslist = str_replace('!!', '"', $accessorieslist);

// 운송회사 추가
$delcompany = isset($_REQUEST['delcompany']) ? $_REQUEST['delcompany'] : '';
$secondordnum = isset($_REQUEST['secondordnum']) ? $_REQUEST['secondordnum'] : '';
$registerdate = isset($_REQUEST['registerdate']) ? $_REQUEST['registerdate'] : '';
$deltime = isset($_REQUEST['deltime']) ? $_REQUEST['deltime'] : null ;

$dc_type = isset($_REQUEST['dc_type']) ? $_REQUEST['dc_type'] : '';
$price = isset($_REQUEST['price']) ? $_REQUEST['price'] : '';
$dcprice = isset($_REQUEST['dcprice']) ? $_REQUEST['dcprice'] : '';
$company_dc_value = isset($_REQUEST['company_dc_value']) ? $_REQUEST['company_dc_value'] : '';
$site_dc_value = isset($_REQUEST['site_dc_value']) ? $_REQUEST['site_dc_value'] : '';

$totalprice = isset($_REQUEST['totalprice']) ? $_REQUEST['totalprice'] : '';
$screen_price = isset($_REQUEST['screen_price']) ? $_REQUEST['screen_price'] : '';
$screen_dcprice = isset($_REQUEST['screen_dcprice']) ? $_REQUEST['screen_dcprice'] : '';
$screen_dc_type = isset($_REQUEST['screen_dc_type']) ? $_REQUEST['screen_dc_type'] : '';
$screen_company_dc_value = isset($_REQUEST['screen_company_dc_value']) ? $_REQUEST['screen_company_dc_value'] : '';
$screen_site_dc_value = isset($_REQUEST['screen_site_dc_value']) ? $_REQUEST['screen_site_dc_value'] : '';
$dcadd = isset($_REQUEST['dcadd']) ? $_REQUEST['dcadd'] : '';
$notdcprice = isset($_REQUEST['notdcprice']) ? $_REQUEST['notdcprice'] : '';
$dctotal = isset($_REQUEST['dctotal']) ? $_REQUEST['dctotal'] : '';
// 5월19일자 추가
$secondordmemo = isset($_REQUEST['secondordmemo']) ? $_REQUEST['secondordmemo'] : '';  // 발주처 작성 메모
$sendcheck = isset($_REQUEST['sendcheck']) ? $_REQUEST['sendcheck'] : '';  // 배차회사 신호발송기록
$deldowntime = isset($_REQUEST['deldowntime']) ? $_REQUEST['deldowntime'] : null ;  // 배차회사 하차시간기록
$delmemo = isset($_REQUEST['delmemo']) ? $_REQUEST['delmemo'] : '';  // 배차회사 화물회사 전달사항
$del_status = isset($_REQUEST['del_status']) ? $_REQUEST['del_status'] : '';  // 배차회사 진행상태
$del_writememo = isset($_REQUEST['del_writememo']) ? $_REQUEST['del_writememo'] : '';  // 배차회사에서 남기는 메모
$controller_price = isset($_REQUEST['controller_price']) ? $_REQUEST['controller_price'] : '';  // 연동제어기 표준금액
$controller_dcprice = isset($_REQUEST['controller_dcprice']) ? $_REQUEST['controller_dcprice'] : '';  // 연동제어기 할인 적용금액
$controller_dc_type = isset($_REQUEST['controller_dc_type']) ? $_REQUEST['controller_dc_type'] : '';  
$controller_company_dc_value = isset($_REQUEST['controller_company_dc_value']) ? $_REQUEST['controller_company_dc_value'] : '';  
$controller_site_dc_value = isset($_REQUEST['controller_site_dc_value']) ? $_REQUEST['controller_site_dc_value'] : '';  
// 6월 3일차 
$returncheck = isset($_REQUEST['returncheck']) ? $_REQUEST['returncheck'] : '';
// 6월 7일차 받는날짜, 회수예정 추가
$returndue = isset($_REQUEST['returndue']) ? $_REQUEST['returndue'] : '';
$getdate = isset($_REQUEST['getdate']) ? $_REQUEST['getdate'] : '';

// 새로 추가된 fabric 컬럼들
$fabric_price = isset($_REQUEST['fabric_price']) ? $_REQUEST['fabric_price'] : '';  
$fabric_dcprice = isset($_REQUEST['fabric_dcprice']) ? $_REQUEST['fabric_dcprice'] : '';  
$fabric_dc_type = isset($_REQUEST['fabric_dc_type']) ? $_REQUEST['fabric_dc_type'] : '';  
$fabric_company_dc_value = isset($_REQUEST['fabric_company_dc_value']) ? $_REQUEST['fabric_company_dc_value'] : '';  
$fabric_site_dc_value = isset($_REQUEST['fabric_site_dc_value']) ? $_REQUEST['fabric_site_dc_value'] : '';  
$fabriclist = isset($_REQUEST['fabriclist']) ? $_REQUEST['fabriclist'] : '{}';  

// 화물운송에 대한 변수명 새로 만듬
$cargo_delbranchinvoice = isset($_REQUEST['cargo_delbranchinvoice']) ? $_REQUEST['cargo_delbranchinvoice'] : '';
$cargo_delwrapmethod = isset($_REQUEST['cargo_delwrapmethod']) ? $_REQUEST['cargo_delwrapmethod'] : '';
$cargo_delwrapsu = isset($_REQUEST['cargo_delwrapsu']) ? $_REQUEST['cargo_delwrapsu'] : '';
$cargo_delwrapamount = isset($_REQUEST['cargo_delwrapamount']) ? $_REQUEST['cargo_delwrapamount'] : '';
$cargo_delwrapweight = isset($_REQUEST['cargo_delwrapweight']) ? $_REQUEST['cargo_delwrapweight'] : '';
$cargo_delwrappaymethod = isset($_REQUEST['cargo_delwrappaymethod']) ? $_REQUEST['cargo_delwrappaymethod'] : '';
$original_num = isset($_REQUEST['original_num']) ? $_REQUEST['original_num'] : '';
$Deliverymanager = $_REQUEST['Deliverymanager'] ??  '';

// 거래명세표 전송시간 추가
$statement_sent_at = $_REQUEST['statement_sent_at'] ??  null;

// 발주처주소록 비고 가져오기
$custNote = isset($_REQUEST['custNote']) ? $_REQUEST['custNote'] : '';
// 인정업체 추가
$certified_company = isset($_REQUEST['certified_company']) ? $_REQUEST['certified_company'] : '';
?>
