<?php
$num = isset($_REQUEST['num']) && $_REQUEST['num'] !== '' ? $_REQUEST['num'] : NULL;
$vehicle_number = isset($_REQUEST['vehicle_number']) && $_REQUEST['vehicle_number'] !== '' ? $_REQUEST['vehicle_number'] : NULL;
$vehicle_type = isset($_REQUEST['vehicle_type']) && $_REQUEST['vehicle_type'] !== '' ? $_REQUEST['vehicle_type'] : NULL;
$responsible_person = isset($_REQUEST['responsible_person']) && $_REQUEST['responsible_person'] !== '' ? $_REQUEST['responsible_person'] : NULL;
$assistant = isset($_REQUEST['assistant']) && $_REQUEST['assistant'] !== '' ? $_REQUEST['assistant'] : NULL;
$insurance = isset($_REQUEST['insurance']) && $_REQUEST['insurance'] !== '' ? $_REQUEST['insurance'] : NULL;
$insurance_contact = isset($_REQUEST['insurance_contact']) && $_REQUEST['insurance_contact'] !== '' ? $_REQUEST['insurance_contact'] : NULL; // 추가: 보험사 연락처
$total_distance_km = isset($_REQUEST['total_distance_km']) && $_REQUEST['total_distance_km'] !== '' ? $_REQUEST['total_distance_km'] : NULL;
$manufacturing_date = isset($_REQUEST['manufacturing_date']) && $_REQUEST['manufacturing_date'] !== '' ? $_REQUEST['manufacturing_date'] : NULL;
$purchase_date = isset($_REQUEST['purchase_date']) && $_REQUEST['purchase_date'] !== '' ? $_REQUEST['purchase_date'] : NULL;
$purchase_type = isset($_REQUEST['purchase_type']) && $_REQUEST['purchase_type'] !== '' ? $_REQUEST['purchase_type'] : NULL; // 추가: 구매 유형
$engine_oil_change_data = isset($_REQUEST['engine_oil_change_data']) && $_REQUEST['engine_oil_change_data'] !== '' ? $_REQUEST['engine_oil_change_data'] : NULL;
$engine_oil_change_cycle = isset($_REQUEST['engine_oil_change_cycle']) && $_REQUEST['engine_oil_change_cycle'] !== '' ? $_REQUEST['engine_oil_change_cycle'] : NULL; // 추가: 엔진오일 교환주기
$maintenance_data = isset($_REQUEST['maintenance_data']) && $_REQUEST['maintenance_data'] !== '' ? $_REQUEST['maintenance_data'] : NULL;
$note = isset($_REQUEST['note']) && $_REQUEST['note'] !== '' ? $_REQUEST['note'] : NULL;
$update_log = isset($_REQUEST['update_log']) && $_REQUEST['update_log'] !== '' ? $_REQUEST['update_log'] : NULL;
$is_deleted = isset($_REQUEST['is_deleted']) && $_REQUEST['is_deleted'] !== '' ? $_REQUEST['is_deleted'] : NULL;
$searchtag = isset($_REQUEST['searchtag']) && $_REQUEST['searchtag'] !== '' ? $_REQUEST['searchtag'] : NULL;
$KMrecordDate = isset($_REQUEST['KMrecordDate']) && $_REQUEST['KMrecordDate'] !== '' ? $_REQUEST['KMrecordDate'] : NULL;
?>
