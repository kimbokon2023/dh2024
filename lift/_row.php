<?php
// $row에서 가져오는 데이터
$num = isset($row['num']) ? $row['num'] : '';
$vehicle_number = isset($row['vehicle_number']) ? $row['vehicle_number'] : '';
$vehicle_type = isset($row['vehicle_type']) ? $row['vehicle_type'] : '';
$responsible_person = isset($row['responsible_person']) ? $row['responsible_person'] : '';
$assistant = isset($row['assistant']) ? $row['assistant'] : '';
$insurance = isset($row['insurance']) ? $row['insurance'] : '';
$insurance_contact = isset($row['insurance_contact']) ? $row['insurance_contact'] : ''; // 추가: 보험사 연락처
$total_distance_km = isset($row['total_distance_km']) ? $row['total_distance_km'] : '';
$manufacturing_date = isset($row['manufacturing_date']) ? $row['manufacturing_date'] : '';
$purchase_date = isset($row['purchase_date']) ? $row['purchase_date'] : '';
$purchase_type = isset($row['purchase_type']) ? $row['purchase_type'] : ''; // 추가: 구매 유형
$engine_oil_change_data = isset($row['engine_oil_change_data']) ? $row['engine_oil_change_data'] : '';
$engine_oil_change_cycle = isset($row['engine_oil_change_cycle']) ? $row['engine_oil_change_cycle'] : ''; // 추가: 엔진오일 교환주기
$maintenance_data = isset($row['maintenance_data']) ? $row['maintenance_data'] : '';
$is_deleted = isset($row['is_deleted']) ? $row['is_deleted'] : '';
$searchtag = isset($row['searchtag']) ? $row['searchtag'] : '';
$update_log = isset($row['update_log']) ? $row['update_log'] : '';
$note = isset($row['note']) ? $row['note'] : '';
$KMrecordDate = isset($row['KMrecordDate']) ? $row['KMrecordDate'] : '';
?>
