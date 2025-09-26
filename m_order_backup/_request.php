<?php 
$num = isset($_REQUEST['num']) ? $_REQUEST['num'] : '';
$orderDate = isset($_REQUEST['orderDate']) ? $_REQUEST['orderDate'] : '';
$orderlist = isset($_REQUEST['orderlist']) ? $_REQUEST['orderlist'] : [];
$memo = isset($_REQUEST['memo']) ? $_REQUEST['memo'] : '';
$is_deleted = isset($_REQUEST['is_deleted']) ? $_REQUEST['is_deleted'] : null;
$first_writer = isset($_REQUEST['first_writer']) ? $_REQUEST['first_writer'] : null;
$update_log = isset($_REQUEST['update_log']) ? $_REQUEST['update_log'] : null;
$totalsurang = $_REQUEST['totalsurang'] ?? '';
$totalamount = $_REQUEST['totalamount'] ?? '';

// 차수는 7차까지 확대
$inputSum1 = $_REQUEST['inputSum1'] ?? '';
$inputSum2 = $_REQUEST['inputSum2'] ?? '';
$inputSum3 = $_REQUEST['inputSum3'] ?? '';
$inputSum4 = $_REQUEST['inputSum4'] ?? '';
$inputSum5 = $_REQUEST['inputSum5'] ?? '';
$inputSum6 = $_REQUEST['inputSum6'] ?? '';
$inputSum7 = $_REQUEST['inputSum7'] ?? '';


$sendMoney1 = $_REQUEST['sendMoney1'] ?? '';
$sendMoney2 = $_REQUEST['sendMoney2'] ?? '';
$sendMoney3 = $_REQUEST['sendMoney3'] ?? '';
$sendMoney4 = $_REQUEST['sendMoney4'] ?? '';
$sendMoney5 = $_REQUEST['sendMoney5'] ?? '';
$sendMoney6 = $_REQUEST['sendMoney6'] ?? '';
$sendMoney7 = $_REQUEST['sendMoney7'] ?? '';

// 추가된 송금일자 변수 4개
$sendDate1 = $_REQUEST['sendDate1'] ?? '';
$sendDate2 = $_REQUEST['sendDate2'] ?? '';
$sendDate3 = $_REQUEST['sendDate3'] ?? '';
$sendDate4 = $_REQUEST['sendDate4'] ?? '';
$sendDate5 = $_REQUEST['sendDate5'] ?? '';
$sendDate6 = $_REQUEST['sendDate6'] ?? '';
$sendDate7 = $_REQUEST['sendDate7'] ?? '';

$customs_fee1 = $_REQUEST['customs_fee1'] ?? '';
$customs_fee2 = $_REQUEST['customs_fee2'] ?? '';
$customs_fee3 = $_REQUEST['customs_fee3'] ?? '';
$customs_fee4 = $_REQUEST['customs_fee4'] ?? '';
$customs_fee5 = $_REQUEST['customs_fee5'] ?? '';
$customs_fee6 = $_REQUEST['customs_fee6'] ?? '';
$customs_fee7 = $_REQUEST['customs_fee7'] ?? '';


$customs_fee_total1 = $_REQUEST['customs_fee_total1'] ?? '';
$customs_fee_total2 = $_REQUEST['customs_fee_total2'] ?? '';
$customs_fee_total3 = $_REQUEST['customs_fee_total3'] ?? '';
$customs_fee_total4 = $_REQUEST['customs_fee_total4'] ?? '';
$customs_fee_total5 = $_REQUEST['customs_fee_total5'] ?? '';
$customs_fee_total6 = $_REQUEST['customs_fee_total6'] ?? '';
$customs_fee_total7 = $_REQUEST['customs_fee_total7'] ?? '';

$customs_vat1 = $_REQUEST['customs_vat1'] ?? '';
$customs_vat2 = $_REQUEST['customs_vat2'] ?? '';
$customs_vat3 = $_REQUEST['customs_vat3'] ?? '';
$customs_vat4 = $_REQUEST['customs_vat4'] ?? '';
$customs_vat5 = $_REQUEST['customs_vat5'] ?? '';
$customs_vat6 = $_REQUEST['customs_vat6'] ?? '';
$customs_vat7 = $_REQUEST['customs_vat7'] ?? '';

$customs_miscellaneous_fee1 = $_REQUEST['customs_miscellaneous_fee1'] ?? '';
$customs_miscellaneous_fee2 = $_REQUEST['customs_miscellaneous_fee2'] ?? '';
$customs_miscellaneous_fee3 = $_REQUEST['customs_miscellaneous_fee3'] ?? '';
$customs_miscellaneous_fee4 = $_REQUEST['customs_miscellaneous_fee4'] ?? '';
$customs_miscellaneous_fee5 = $_REQUEST['customs_miscellaneous_fee5'] ?? '';
$customs_miscellaneous_fee6 = $_REQUEST['customs_miscellaneous_fee6'] ?? '';
$customs_miscellaneous_fee7 = $_REQUEST['customs_miscellaneous_fee7'] ?? '';


$customs_container_fee1 = $_REQUEST['customs_container_fee1'] ?? '';
$customs_container_fee2 = $_REQUEST['customs_container_fee2'] ?? '';
$customs_container_fee3 = $_REQUEST['customs_container_fee3'] ?? '';
$customs_container_fee4 = $_REQUEST['customs_container_fee4'] ?? '';
$customs_container_fee5 = $_REQUEST['customs_container_fee5'] ?? '';
$customs_container_fee6 = $_REQUEST['customs_container_fee6'] ?? '';
$customs_container_fee7 = $_REQUEST['customs_container_fee7'] ?? '';

$customs_commission1 = $_REQUEST['customs_commission1'] ?? '';
$customs_commission2 = $_REQUEST['customs_commission2'] ?? '';
$customs_commission3 = $_REQUEST['customs_commission3'] ?? '';
$customs_commission4 = $_REQUEST['customs_commission4'] ?? '';
$customs_commission5 = $_REQUEST['customs_commission5'] ?? '';
$customs_commission6 = $_REQUEST['customs_commission6'] ?? '';
$customs_commission7 = $_REQUEST['customs_commission7'] ?? '';

$customs_detail_total1 = $_REQUEST['customs_detail_total1'] ?? '';
$customs_detail_total2 = $_REQUEST['customs_detail_total2'] ?? '';
$customs_detail_total3 = $_REQUEST['customs_detail_total3'] ?? '';
$customs_detail_total4 = $_REQUEST['customs_detail_total4'] ?? '';
$customs_detail_total5 = $_REQUEST['customs_detail_total5'] ?? '';
$customs_detail_total6 = $_REQUEST['customs_detail_total6'] ?? '';
$customs_detail_total7 = $_REQUEST['customs_detail_total7'] ?? '';

$customs_input_amount_cny1 = $_REQUEST['customs_input_amount_cny1'] ?? '';
$customs_input_amount_cny2 = $_REQUEST['customs_input_amount_cny2'] ?? '';
$customs_input_amount_cny3 = $_REQUEST['customs_input_amount_cny3'] ?? '';
$customs_input_amount_cny4 = $_REQUEST['customs_input_amount_cny4'] ?? '';
$customs_input_amount_cny5 = $_REQUEST['customs_input_amount_cny5'] ?? '';
$customs_input_amount_cny6 = $_REQUEST['customs_input_amount_cny6'] ?? '';
$customs_input_amount_cny7 = $_REQUEST['customs_input_amount_cny7'] ?? '';

$exchange_rate1 = $_REQUEST['exchange_rate1'] ?? '';
$exchange_rate2 = $_REQUEST['exchange_rate2'] ?? '';
$exchange_rate3 = $_REQUEST['exchange_rate3'] ?? '';
$exchange_rate4 = $_REQUEST['exchange_rate4'] ?? '';
$exchange_rate5 = $_REQUEST['exchange_rate5'] ?? '';
$exchange_rate6 = $_REQUEST['exchange_rate6'] ?? '';
$exchange_rate7 = $_REQUEST['exchange_rate7'] ?? '';

$send_amount_krw1 = $_REQUEST['send_amount_krw1'] ?? '';
$send_amount_krw2 = $_REQUEST['send_amount_krw2'] ?? '';
$send_amount_krw3 = $_REQUEST['send_amount_krw3'] ?? '';
$send_amount_krw4 = $_REQUEST['send_amount_krw4'] ?? '';
$send_amount_krw5 = $_REQUEST['send_amount_krw5'] ?? '';
$send_amount_krw6 = $_REQUEST['send_amount_krw6'] ?? '';
$send_amount_krw7 = $_REQUEST['send_amount_krw7'] ?? '';

$customs_date1 = $_REQUEST['customs_date1'] ?? '';
$customs_date2 = $_REQUEST['customs_date2'] ?? '';
$customs_date3 = $_REQUEST['customs_date3'] ?? '';
$customs_date4 = $_REQUEST['customs_date4'] ?? '';
$customs_date5 = $_REQUEST['customs_date5'] ?? '';
$customs_date6 = $_REQUEST['customs_date6'] ?? '';
$customs_date7 = $_REQUEST['customs_date7'] ?? '';

// 차수별 송금수수료 추가
$remittance_fee1 = $_REQUEST['remittance_fee1'] ?? '';
$remittance_fee2 = $_REQUEST['remittance_fee2'] ?? '';
$remittance_fee3 = $_REQUEST['remittance_fee3'] ?? '';
$remittance_fee4 = $_REQUEST['remittance_fee4'] ?? '';
$remittance_fee5 = $_REQUEST['remittance_fee5'] ?? '';
$remittance_fee6 = $_REQUEST['remittance_fee6'] ?? '';
$remittance_fee7 = $_REQUEST['remittance_fee7'] ?? '';

?>
