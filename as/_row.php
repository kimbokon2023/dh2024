<?php
$num = isset($row['num']) ? $row['num'] : '';
$as_check = isset($row['as_check']) ? $row['as_check'] : 0;
$asday = isset($row['asday']) ? $row['asday'] : '';
$aswriter = isset($row['aswriter']) ? $row['aswriter'] : '';
$asorderman = isset($row['asorderman']) ? $row['asorderman'] : '';
$as_step = isset($row['as_step']) ? $row['as_step'] : '';
$asordermantel = isset($row['asordermantel']) ? $row['asordermantel'] : '';
$asfee = isset($row['asfee']) ? $row['asfee'] : '';
$asfee_estimate = isset($row['asfee_estimate']) ? $row['asfee_estimate'] : '';
$aslist = isset($row['aslist']) ? $row['aslist'] : '';
$as_refer = isset($row['as_refer']) ? $row['as_refer'] : '';
$asproday = isset($row['asproday']) ? $row['asproday'] : '';
$setdate = isset($row['setdate']) ? $row['setdate'] : '';
$asman = isset($row['asman']) ? $row['asman'] : '';
$asendday = isset($row['asendday']) ? $row['asendday'] : '';
$asresult = isset($row['asresult']) ? $row['asresult'] : '';
$ashistory = isset($row['ashistory']) ? $row['ashistory'] : '';
$note = isset($row['note']) ? $row['note'] : '';
$update_log = isset($row['update_log']) ? $row['update_log'] : '';
$secondord = isset($row['secondord']) ? $row['secondord'] : '';
$secondordman = isset($row['secondordman']) ? $row['secondordman'] : '';
$secondordmantel = isset($row['secondordmantel']) ? $row['secondordmantel'] : '';
$workplaceCode = isset($row['workplaceCode']) ? $row['workplaceCode'] : '';
$workplacename = isset($row['workplacename']) ? $row['workplacename'] : '';
$is_deleted = isset($row['is_deleted']) ? $row['is_deleted'] : NULL;
$searchtag = isset($row['searchtag']) ? $row['searchtag'] : '';
$address = isset($row['address']) ? $row['address'] : '';

// 추가된 변수들
$payment = isset($row['payment']) ? $row['payment'] : 'free'; // '유상' 또는 '무상' 기본값 '무상'
$demandDate = isset($row['demandDate']) ? $row['demandDate'] : NULL ; // 청구일
$spotman = isset($row['spotman']) ? $row['spotman'] : ''; // 현장 담당자
$spotmantel = isset($row['spotmantel']) ? $row['spotmantel'] : NULL; // 현장 담당자 연락처
$payman = isset($row['payman']) ? $row['payman'] : NULL; 
$paydate = isset($row['paydate']) ? $row['paydate'] : NULL;
$itemcheck = isset($row['itemcheck']) ? $row['itemcheck'] : NULL;
?>
