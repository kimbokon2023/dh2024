<?php

// 기본 키(num) 호출
$num = isset($_REQUEST["num"]) ? $_REQUEST["num"] : '';

// 등록일자(registedate) 호출
$registedate = isset($_REQUEST['registedate']) ? $_REQUEST['registedate'] : date('Y-m-d') ;

// itemList JSON 데이터를 호출 후 파싱
$itemList = isset($_REQUEST['itemList']) ? json_decode($_REQUEST['itemList'], true) : [];

// 삭제 여부(is_deleted) 호출
$is_deleted = isset($_REQUEST['is_deleted']) ? $_REQUEST['is_deleted'] : 0;

// 업데이트 로그(update_log) 호출
$update_log = isset($_REQUEST['update_log']) ? $_REQUEST['update_log'] : '';

// 검색 태그(searchtag) 호출
$searchtag = isset($_REQUEST['searchtag']) ? $_REQUEST['searchtag'] : '';

// 생성일시(created_at) 호출
$created_at = isset($_REQUEST['created_at']) ? $_REQUEST['created_at'] : '';

$memo = isset($_REQUEST['memo']) ? $_REQUEST['memo'] : '';

?>
