<?php

// 기본 키(num) 처리
$num = isset($row["num"]) ? $row["num"] : '';

// 등록일자(registedate) 처리
$registedate = isset($row['registedate']) ? $row['registedate'] : '';

// itemList JSON 데이터를 처리
$itemList = isset($row['itemList']) ? json_decode($row['itemList'], true) : [];

// 삭제 여부(is_deleted) 처리
$is_deleted = isset($row['is_deleted']) ? $row['is_deleted'] : 0;

// 업데이트 로그(update_log) 처리
$update_log = isset($row['update_log']) ? $row['update_log'] : '';

// 검색 태그(searchtag) 처리
$searchtag = isset($row['searchtag']) ? $row['searchtag'] : '';

// 생성일시(created_at) 처리
$created_at = isset($row['created_at']) ? $row['created_at'] : '';
$memo = isset($row['memo']) ? $row['memo'] : '';

?>
 