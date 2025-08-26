<?php 
$num = isset($row['num']) ? $row['num'] : '';
$orderDate = isset($row['orderDate']) ? $row['orderDate'] : '';
$orderlist = isset($row['orderlist']) ? json_decode($row['orderlist'], true) : [];
$memo = isset($row['memo']) ? $row['memo'] : '';
$is_deleted = isset($row['is_deleted']) ? $row['is_deleted'] : null;
$first_writer = isset($row['first_writer']) ? $row['first_writer'] : null;
$update_log = isset($row['update_log']) ? $row['update_log'] : null;
$totalsurang = $row['totalsurang'] ?? '';
$totalamount = $row['totalamount'] ?? '';

// 추가된 8개의 컬럼
$inputSum1 = $row['inputSum1'] ?? '';
$inputSum2 = $row['inputSum2'] ?? '';
$inputSum3 = $row['inputSum3'] ?? '';
$inputSum4 = $row['inputSum4'] ?? '';
$inputSum5 = $row['inputSum5'] ?? '';
$inputSum6 = $row['inputSum6'] ?? '';
$inputSum7 = $row['inputSum7'] ?? '';

$sendMoney1 = $row['sendMoney1'] ?? '';
$sendMoney2 = $row['sendMoney2'] ?? '';
$sendMoney3 = $row['sendMoney3'] ?? '';
$sendMoney4 = $row['sendMoney4'] ?? '';
$sendMoney5 = $row['sendMoney5'] ?? '';
$sendMoney6 = $row['sendMoney6'] ?? '';
$sendMoney7 = $row['sendMoney7'] ?? '';

// 추가된 송금일자 컬럼 4개
$sendDate1 = $row['sendDate1'] ?? '';
$sendDate2 = $row['sendDate2'] ?? '';
$sendDate3 = $row['sendDate3'] ?? '';
$sendDate4 = $row['sendDate4'] ?? '';
$sendDate5 = $row['sendDate5'] ?? '';
$sendDate6 = $row['sendDate6'] ?? '';
$sendDate7 = $row['sendDate7'] ?? '';

$customs_fee1 = $row['customs_fee1'] ?? '';
$customs_fee2 = $row['customs_fee2'] ?? '';
$customs_fee3 = $row['customs_fee3'] ?? '';
$customs_fee4 = $row['customs_fee4'] ?? '';
$customs_fee5 = $row['customs_fee5'] ?? '';
$customs_fee6 = $row['customs_fee6'] ?? '';
$customs_fee7 = $row['customs_fee7'] ?? '';

$customs_fee_total1 = $row['customs_fee_total1'] ?? '';
$customs_fee_total2 = $row['customs_fee_total2'] ?? '';
$customs_fee_total3 = $row['customs_fee_total3'] ?? '';
$customs_fee_total4 = $row['customs_fee_total4'] ?? '';
$customs_fee_total5 = $row['customs_fee_total5'] ?? '';
$customs_fee_total6 = $row['customs_fee_total6'] ?? '';
$customs_fee_total7 = $row['customs_fee_total7'] ?? '';

$customs_vat1 = $row['customs_vat1'] ?? '';
$customs_vat2 = $row['customs_vat2'] ?? '';
$customs_vat3 = $row['customs_vat3'] ?? '';
$customs_vat4 = $row['customs_vat4'] ?? '';
$customs_vat5 = $row['customs_vat5'] ?? '';
$customs_vat6 = $row['customs_vat6'] ?? '';
$customs_vat7 = $row['customs_vat7'] ?? '';

$customs_miscellaneous_fee1 = $row['customs_miscellaneous_fee1'] ?? '';
$customs_miscellaneous_fee2 = $row['customs_miscellaneous_fee2'] ?? '';
$customs_miscellaneous_fee3 = $row['customs_miscellaneous_fee3'] ?? '';
$customs_miscellaneous_fee4 = $row['customs_miscellaneous_fee4'] ?? '';
$customs_miscellaneous_fee5 = $row['customs_miscellaneous_fee5'] ?? '';
$customs_miscellaneous_fee6 = $row['customs_miscellaneous_fee6'] ?? '';
$customs_miscellaneous_fee7 = $row['customs_miscellaneous_fee7'] ?? '';

$customs_container_fee1 = $row['customs_container_fee1'] ?? '';
$customs_container_fee2 = $row['customs_container_fee2'] ?? '';
$customs_container_fee3 = $row['customs_container_fee3'] ?? '';
$customs_container_fee4 = $row['customs_container_fee4'] ?? '';
$customs_container_fee5 = $row['customs_container_fee5'] ?? '';
$customs_container_fee6 = $row['customs_container_fee6'] ?? '';
$customs_container_fee7 = $row['customs_container_fee7'] ?? '';

$customs_commission1 = $row['customs_commission1'] ?? '';
$customs_commission2 = $row['customs_commission2'] ?? '';
$customs_commission3 = $row['customs_commission3'] ?? '';
$customs_commission4 = $row['customs_commission4'] ?? '';
$customs_commission5 = $row['customs_commission5'] ?? '';
$customs_commission6 = $row['customs_commission6'] ?? '';
$customs_commission7 = $row['customs_commission7'] ?? '';

$customs_detail_total1 = $row['customs_detail_total1'] ?? '';
$customs_detail_total2 = $row['customs_detail_total2'] ?? '';
$customs_detail_total3 = $row['customs_detail_total3'] ?? '';
$customs_detail_total4 = $row['customs_detail_total4'] ?? '';
$customs_detail_total5 = $row['customs_detail_total5'] ?? '';
$customs_detail_total6 = $row['customs_detail_total6'] ?? '';
$customs_detail_total7 = $row['customs_detail_total7'] ?? '';

$customs_input_amount_cny1 = $row['customs_input_amount_cny1'] ?? '';
$customs_input_amount_cny2 = $row['customs_input_amount_cny2'] ?? '';
$customs_input_amount_cny3 = $row['customs_input_amount_cny3'] ?? '';
$customs_input_amount_cny4 = $row['customs_input_amount_cny4'] ?? '';
$customs_input_amount_cny5 = $row['customs_input_amount_cny5'] ?? '';
$customs_input_amount_cny6 = $row['customs_input_amount_cny6'] ?? '';
$customs_input_amount_cny7 = $row['customs_input_amount_cny7'] ?? '';

$exchange_rate1 = $row['exchange_rate1'] ?? '';
$exchange_rate2 = $row['exchange_rate2'] ?? '';
$exchange_rate3 = $row['exchange_rate3'] ?? '';
$exchange_rate4 = $row['exchange_rate4'] ?? '';
$exchange_rate5 = $row['exchange_rate5'] ?? '';
$exchange_rate6 = $row['exchange_rate6'] ?? '';
$exchange_rate7 = $row['exchange_rate7'] ?? '';

$send_amount_krw1 = $row['send_amount_krw1'] ?? '';
$send_amount_krw2 = $row['send_amount_krw2'] ?? '';
$send_amount_krw3 = $row['send_amount_krw3'] ?? '';
$send_amount_krw4 = $row['send_amount_krw4'] ?? '';
$send_amount_krw5 = $row['send_amount_krw5'] ?? '';
$send_amount_krw6 = $row['send_amount_krw6'] ?? '';
$send_amount_krw7 = $row['send_amount_krw7'] ?? '';

// 차수별 통관일 컬럼 추가
$customs_date1 = $row['customs_date1'] ?? '';
$customs_date2 = $row['customs_date2'] ?? '';
$customs_date3 = $row['customs_date3'] ?? '';
$customs_date4 = $row['customs_date4'] ?? '';
$customs_date5 = $row['customs_date5'] ?? '';
$customs_date6 = $row['customs_date6'] ?? '';
$customs_date7 = $row['customs_date7'] ?? '';

// 차수별 송금수수료 추가
$remittance_fee1 = $row['remittance_fee1'] ?? '';
$remittance_fee2 = $row['remittance_fee2'] ?? '';
$remittance_fee3 = $row['remittance_fee3'] ?? '';
$remittance_fee4 = $row['remittance_fee4'] ?? '';
$remittance_fee5 = $row['remittance_fee5'] ?? '';
$remittance_fee6 = $row['remittance_fee6'] ?? '';
$remittance_fee7 = $row['remittance_fee7'] ?? '';
?>
