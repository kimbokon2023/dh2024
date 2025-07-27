<?php
$num = isset($_REQUEST['num']) && $_REQUEST['num'] !== '' ? $_REQUEST['num'] : NULL;
$as_check = isset($_REQUEST['as_check']) ? $_REQUEST['as_check'] : 0;
$asday = isset($_REQUEST['asday']) && $_REQUEST['asday'] !== '' ? $_REQUEST['asday'] : NULL;
$aswriter = isset($_REQUEST['aswriter']) && $_REQUEST['aswriter'] !== '' ? $_REQUEST['aswriter'] : NULL;
$asorderman = isset($_REQUEST['asorderman']) && $_REQUEST['asorderman'] !== '' ? $_REQUEST['asorderman'] : NULL;
$as_step = isset($_REQUEST['as_step']) && $_REQUEST['as_step'] !== '' ? $_REQUEST['as_step'] : NULL;
$asordermantel = isset($_REQUEST['asordermantel']) && $_REQUEST['asordermantel'] !== '' ? $_REQUEST['asordermantel'] : NULL;
$asfee = isset($_REQUEST['asfee']) && $_REQUEST['asfee'] !== '' ? $_REQUEST['asfee'] : '';
$asfee_estimate = isset($_REQUEST['asfee_estimate']) && $_REQUEST['asfee_estimate'] !== '' ? $_REQUEST['asfee_estimate'] :'';
$aslist = isset($_REQUEST['aslist']) && $_REQUEST['aslist'] !== '' ? $_REQUEST['aslist'] : NULL;
$as_refer = isset($_REQUEST['as_refer']) && $_REQUEST['as_refer'] !== '' ? $_REQUEST['as_refer'] : NULL;
$asproday = isset($_REQUEST['asproday']) && $_REQUEST['asproday'] !== '' ? $_REQUEST['asproday'] : NULL;
$setdate = isset($_REQUEST['setdate']) && $_REQUEST['setdate'] !== '' ? $_REQUEST['setdate'] : NULL;
$asman = isset($_REQUEST['asman']) && $_REQUEST['asman'] !== '' ? $_REQUEST['asman'] : NULL;
$asendday = isset($_REQUEST['asendday']) && $_REQUEST['asendday'] !== '' ? $_REQUEST['asendday'] : NULL;
$asresult = isset($_REQUEST['asresult']) && $_REQUEST['asresult'] !== '' ? $_REQUEST['asresult'] : NULL;
$ashistory = isset($_REQUEST['ashistory']) && $_REQUEST['ashistory'] !== '' ? $_REQUEST['ashistory'] : NULL;
$note = isset($_REQUEST['note']) && $_REQUEST['note'] !== '' ? $_REQUEST['note'] : NULL;
$update_log = isset($_REQUEST['update_log']) && $_REQUEST['update_log'] !== '' ? $_REQUEST['update_log'] : NULL;
$secondord = isset($_REQUEST['secondord']) && $_REQUEST['secondord'] !== '' ? $_REQUEST['secondord'] : NULL;
$secondordman = isset($_REQUEST['secondordman']) && $_REQUEST['secondordman'] !== '' ? $_REQUEST['secondordman'] : NULL;
$secondordmantel = isset($_REQUEST['secondordmantel']) && $_REQUEST['secondordmantel'] !== '' ? $_REQUEST['secondordmantel'] : NULL;
$workplaceCode = isset($_REQUEST['workplaceCode']) && $_REQUEST['workplaceCode'] !== '' ? $_REQUEST['workplaceCode'] : NULL;
$workplacename = isset($_REQUEST['workplacename']) && $_REQUEST['workplacename'] !== '' ? $_REQUEST['workplacename'] : NULL;
$is_deleted = isset($_REQUEST['is_deleted']) && $_REQUEST['is_deleted'] !== '' ? $_REQUEST['is_deleted'] : NULL;
$searchtag = isset($_REQUEST['searchtag']) && $_REQUEST['searchtag'] !== '' ? $_REQUEST['searchtag'] : NULL;
$address = isset($_REQUEST['address']) && $_REQUEST['address'] !== '' ? $_REQUEST['address'] : NULL;

// 추가된 변수들
$payment = isset($_REQUEST['payment']) && $_REQUEST['payment'] !== '' ? $_REQUEST['payment'] : 'free'; // 기본값 'free'
$demandDate = isset($_REQUEST['demandDate']) && $_REQUEST['demandDate'] !== '' ? $_REQUEST['demandDate'] : NULL;
$spotman = isset($_REQUEST['spotman']) && $_REQUEST['spotman'] !== '' ? $_REQUEST['spotman'] : NULL; // 현장 담당자
$spotmantel = isset($_REQUEST['spotmantel']) && $_REQUEST['spotmantel'] !== '' ? $_REQUEST['spotmantel'] : NULL;
$payman = isset($_REQUEST['payman']) && $_REQUEST['payman'] !== '' ? $_REQUEST['payman'] : NULL; 
$paydate = isset($_REQUEST['paydate']) && $_REQUEST['paydate'] !== '' ? $_REQUEST['paydate'] : NULL; 
$itemcheck = isset($_REQUEST['itemcheck']) && $_REQUEST['itemcheck'] !== '' ? $_REQUEST['itemcheck'] : NULL; 
?>
