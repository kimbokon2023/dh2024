<?php
$num = isset($row['num']) ? $row['num'] : '';
$workplacename = isset($row['workplacename']) ? $row['workplacename'] : '';
$status = isset($row['status']) ? $row['status'] : '';
$order_total = isset($row['order_total']) ? $row['order_total'] : '';
$screensu = isset($row['screensu']) ? $row['screensu'] : '';
$steelsu = isset($row['steelsu']) ? $row['steelsu'] : '';
$protectsu = isset($row['protectsu']) ? $row['protectsu'] : '';
$smokesu = isset($row['smokesu']) ? $row['smokesu'] : '';
$noendsu = isset($row['noendsu']) ? $row['noendsu'] : '';
$noscreensu = isset($row['noscreensu']) ? $row['noscreensu'] : '';
$nosteelsu = isset($row['nosteelsu']) ? $row['nosteelsu'] : '';
$noprotectsu = isset($row['noprotectsu']) ? $row['noprotectsu'] : '';
$nosmokesu = isset($row['nosmokesu']) ? $row['nosmokesu'] : '';
$orderdate = isset($row['orderdate']) ? $row['orderdate'] :  date("Y-m-d");
$deadline = isset($row['deadline']) ? $row['deadline'] : '';
$outputdate = isset($row['outputdate']) ? $row['outputdate'] : '';
$demand = isset($row['demand']) ? $row['demand'] : '';
$secondord = isset($row['secondord']) ? $row['secondord'] : '';
$secondordman = isset($row['secondordman']) ? $row['secondordman'] : '';
$secondordmantel = isset($row['secondordmantel']) ? $row['secondordmantel'] : '';
$chargedman = isset($row['chargedman']) ? $row['chargedman'] : '';
$chargedmantel = isset($row['chargedmantel']) ? $row['chargedmantel'] : '';
$address = isset($row['address']) ? $row['address'] : '';
$delipay = isset($row['delipay']) ? $row['delipay'] : '';
$deliverymethod = isset($row['deliverymethod']) ? $row['deliverymethod'] : '화물'; // '화물'을 기본값으로 설정
$deliverypaymethod = isset($row['deliverypaymethod']) ? $row['deliverypaymethod'] : '선불'; // '선불'을 기본값으로 설정
$delbranch = isset($row['delbranch']) ? $row['delbranch'] : '';
$delbranchaddress = isset($row['delbranchaddress']) ? $row['delbranchaddress'] : '';
$delbranchtel = isset($row['delbranchtel']) ? $row['delbranchtel'] : '';
$delbranchinvoice = isset($row['delbranchinvoice']) ? $row['delbranchinvoice'] : '';
$delcarnumber = isset($row['delcarnumber']) ? $row['delcarnumber'] : '';
$delcaritem = isset($row['delcaritem']) ? $row['delcaritem'] : '';
$delcartel = isset($row['delcartel']) ? $row['delcartel'] : '';
$memo = isset($row['memo']) ? $row['memo'] : '';
$comment = isset($row['comment']) ? $row['comment'] : '';
$first_writer = isset($row['first_writer']) ? $row['first_writer'] : '';
$update_log = isset($row['update_log']) ? $row['update_log'] : '';
$searchtag = isset($row['searchtag']) ? $row['searchtag'] : '';
$loadplace = isset($row['loadplace']) ? $row['loadplace'] : '';
$is_deleted = isset($row['is_deleted']) ? $row['is_deleted'] : NULL;
$explosionsu = isset($row['explosionsu']) ? $row['explosionsu'] : '';
$noexplosionsu = isset($row['noexplosionsu']) ? $row['noexplosionsu'] : '';
$realscreensu = isset($row['realscreensu']) ? $row['realscreensu'] : '';
$realsteelsu = isset($row['realsteelsu']) ? $row['realsteelsu'] : '';
$realprotectsu = isset($row['realprotectsu']) ? $row['realprotectsu'] : '';
$realsmokesu = isset($row['realsmokesu']) ? $row['realsmokesu'] : '';
$realexplosionsu = isset($row['realexplosionsu']) ? $row['realexplosionsu'] : '';
$delwrapmethod = isset($row['delwrapmethod']) ? $row['delwrapmethod'] : '';
$delwrapsu = isset($row['delwrapsu']) ? $row['delwrapsu'] : '';
$delwrapamount = isset($row['delwrapamount']) ? $row['delwrapamount'] : '';
$delwrapweight = isset($row['delwrapweight']) ? $row['delwrapweight'] : '';
$delwrappaymethod = isset($row['delwrappaymethod']) ? $row['delwrappaymethod'] : '';

$polesu = isset($row['polesu']) ? $row['polesu'] : '';
$nopolesu = isset($row['nopolesu']) ? $row['nopolesu'] : '';
$realpolesu = isset($row['realpolesu']) ? $row['realpolesu'] : '';

$orderlist = isset($row['orderlist']) ? $row['orderlist'] : '{}';
$accessorieslist = isset($row['accessorieslist']) ? $row['accessorieslist'] : '{}';
$controllerlist = isset($row['controllerlist']) ? $row['controllerlist'] : '{}';

// !!를 "로 변환
//$orderlist = str_replace('!!', '"', $orderlist);
//$accessorieslist = str_replace('!!', '"', $accessorieslist);
// 운송회사 추가
$delcompany = isset($row['delcompany']) ? $row['delcompany'] : '';
$secondordnum = isset($row['secondordnum']) ? $row['secondordnum'] : '';
$registerdate = isset($row['registerdate']) ? $row['registerdate'] : '';
$deltime = isset($row['deltime']) ? $row['deltime'] : '';

$price = isset($row['price']) ? $row['price'] : '';
$dcprice = isset($row['dcprice']) ? $row['dcprice'] : '';
$dc_type = isset($row['dc_type']) ? $row['dc_type'] : '';
$company_dc_value = isset($row['company_dc_value']) ? $row['company_dc_value'] : '';
$site_dc_value = isset($row['site_dc_value']) ? $row['site_dc_value'] : '';
$totalprice = isset($row['totalprice']) ? $row['totalprice'] : '';
$screen_price = isset($row['screen_price']) ? $row['screen_price'] : '';
$screen_dcprice = isset($row['screen_dcprice']) ? $row['screen_dcprice'] : '';
$screen_dc_type = isset($row['screen_dc_type']) ? $row['screen_dc_type'] : '';
$screen_company_dc_value = isset($row['screen_company_dc_value']) ? $row['screen_company_dc_value'] : '';
$screen_site_dc_value = isset($row['screen_site_dc_value']) ? $row['screen_site_dc_value'] : '';
$dcadd = isset($row['dcadd']) ? $row['dcadd'] : '';
$notdcprice = isset($row['notdcprice']) ? $row['notdcprice'] : '';
$dctotal = isset($row['dctotal']) ? $row['dctotal'] : '';

// 5월19일자 추가
$secondordmemo = isset($row['secondordmemo']) ? $row['secondordmemo'] : '';  // 발주처 작성 메모
$sendcheck = isset($row['sendcheck']) ? $row['sendcheck'] : '';  // 배차회사 신호발송기록
$deldowntime = isset($row['deldowntime']) ? $row['deldowntime'] : null ;  // 배차회사 하차시간기록
$delmemo = isset($row['delmemo']) ? $row['delmemo'] : '';  // 배차회사 화물회사 전달사항
$del_status = isset($row['del_status']) ? $row['del_status'] : '';  // 배차회사 화물회사 진행상태
$del_writememo = isset($row['del_writememo']) ? $row['del_writememo'] : '';  
$controller_price = isset($row['controller_price']) ? $row['controller_price'] : '';  // 연동제어기 표준금액
$controller_dcprice = isset($row['controller_dcprice']) ? $row['controller_dcprice'] : '';  // 연동제어기 할인 적용금액
$controller_dc_type = isset($row['controller_dc_type']) ? $row['controller_dc_type'] : '';  
$controller_company_dc_value = isset($row['controller_company_dc_value']) ? $row['controller_company_dc_value'] : '';  
$controller_site_dc_value = isset($row['controller_site_dc_value']) ? $row['controller_site_dc_value'] : ''; 

// 6월 3일차 
$returncheck = isset($row['returncheck']) ? $row['returncheck'] : '';
$returndue = isset($row['returndue']) ? $row['returndue'] : '';
$getdate = isset($row['getdate']) ? $row['getdate'] : '';

// 새로 추가된 fabric 컬럼들
$fabric_price = isset($row['fabric_price']) ? $row['fabric_price'] : '';  
$fabric_dcprice = isset($row['fabric_dcprice']) ? $row['fabric_dcprice'] : '';  
$fabric_dc_type = isset($row['fabric_dc_type']) ? $row['fabric_dc_type'] : '';  
$fabric_company_dc_value = isset($row['fabric_company_dc_value']) ? $row['fabric_company_dc_value'] : '';  
$fabric_site_dc_value = isset($row['fabric_site_dc_value']) ? $row['fabric_site_dc_value'] : '';  
$fabriclist = isset($row['fabriclist']) ? $row['fabriclist'] : '{}';  

// 화물운송에 대한 변수명 새로 만듬
$cargo_delbranchinvoice = isset($row['cargo_delbranchinvoice']) ? $row['cargo_delbranchinvoice'] : '';
$cargo_delwrapmethod = isset($row['cargo_delwrapmethod']) ? $row['cargo_delwrapmethod'] : '';
$cargo_delwrapsu = isset($row['cargo_delwrapsu']) ? $row['cargo_delwrapsu'] : '';
$cargo_delwrapamount = isset($row['cargo_delwrapamount']) ? $row['cargo_delwrapamount'] : '';
$cargo_delwrapweight = isset($row['cargo_delwrapweight']) ? $row['cargo_delwrapweight'] : '';
$cargo_delwrappaymethod = isset($row['cargo_delwrappaymethod']) ? $row['cargo_delwrappaymethod'] : '';
$original_num = isset($row['original_num']) ? $row['original_num'] : '';
$Deliverymanager = $row['Deliverymanager'] ??  ''; 

// 거래명세표 전송시간 추가
$statement_sent_at = $row['statement_sent_at'] ??  null;

// 발주처주소록 비고 가져오기
$custNote = isset($row['custNote']) ? $row['custNote'] : '';
// 인정업체 추가
$certified_company = isset($row['certified_company']) ? $row['certified_company'] : '';
?>
