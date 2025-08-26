<?php   
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");

$mode = $_REQUEST['mode'] ?? '';  
$num = $_REQUEST['num'] ?? '';
$tablename = 'm_order';

header("Content-Type: application/json");
$tablename = 'm_order';
include '_request.php';

$exchange_rate1 = isset($_REQUEST['exchange_rate1']) ? str_replace(',', '', $_REQUEST['exchange_rate1']) : '';
$exchange_rate2 = isset($_REQUEST['exchange_rate2']) ? str_replace(',', '', $_REQUEST['exchange_rate2']) : '';
$exchange_rate3 = isset($_REQUEST['exchange_rate3']) ? str_replace(',', '', $_REQUEST['exchange_rate3']) : '';
$exchange_rate4 = isset($_REQUEST['exchange_rate4']) ? str_replace(',', '', $_REQUEST['exchange_rate4']) : '';
$exchange_rate5 = isset($_REQUEST['exchange_rate5']) ? str_replace(',', '', $_REQUEST['exchange_rate5']) : '';
$exchange_rate6 = isset($_REQUEST['exchange_rate6']) ? str_replace(',', '', $_REQUEST['exchange_rate6']) : '';
$exchange_rate7 = isset($_REQUEST['exchange_rate7']) ? str_replace(',', '', $_REQUEST['exchange_rate7']) : '';

// DB 연결
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

if ($mode == "modify") {
    $update_log = date("Y-m-d H:i:s") . " - " . $_SESSION["name"] . " " . $update_log . "&#10";

    try {
        $pdo->beginTransaction();
        $sql = "UPDATE $DB.$tablename SET 
            memo=?, update_log=?,             
            sendDate1=?, sendDate2=?, sendDate3=?, sendDate4=?, sendDate5=?, sendDate6=?, sendDate7=?,
            customs_fee1=?, customs_fee2=?, customs_fee3=?, customs_fee4=?, customs_fee5=?, customs_fee6=?, customs_fee7=?,
            customs_fee_total1=?, customs_fee_total2=?, customs_fee_total3=?, customs_fee_total4=?, customs_fee_total5=?, customs_fee_total6=?, customs_fee_total7=?,
            customs_vat1=?, customs_vat2=?, customs_vat3=?, customs_vat4=?, customs_vat5=?, customs_vat6=?, customs_vat7=?,
            customs_miscellaneous_fee1=?, customs_miscellaneous_fee2=?, customs_miscellaneous_fee3=?, customs_miscellaneous_fee4=?, customs_miscellaneous_fee5=?, customs_miscellaneous_fee6=?, customs_miscellaneous_fee7=?,
            customs_container_fee1=?, customs_container_fee2=?, customs_container_fee3=?, customs_container_fee4=?, customs_container_fee5=?, customs_container_fee6=?, customs_container_fee7=?,
            customs_commission1=?, customs_commission2=?, customs_commission3=?, customs_commission4=?, customs_commission5=?, customs_commission6=?, customs_commission7=?,
            customs_detail_total1=?, customs_detail_total2=?, customs_detail_total3=?, customs_detail_total4=?, customs_detail_total5=?, customs_detail_total6=?, customs_detail_total7=?,
            customs_input_amount_cny1=?, customs_input_amount_cny2=?, customs_input_amount_cny3=?, customs_input_amount_cny4=?, customs_input_amount_cny5=?, customs_input_amount_cny6=?, customs_input_amount_cny7=?,
            customs_date1=?, customs_date2=?, customs_date3=?, customs_date4=?, customs_date5=?, customs_date6=?, customs_date7=?,
            exchange_rate1=?, exchange_rate2=?, exchange_rate3=?, exchange_rate4=?, exchange_rate5=?, exchange_rate6=?, exchange_rate7=?,
            send_amount_krw1=?, send_amount_krw2=?, send_amount_krw3=?, send_amount_krw4=?, send_amount_krw5=?, send_amount_krw6=?, send_amount_krw7=?,
            remittance_fee1=?, remittance_fee2=?, remittance_fee3=?, remittance_fee4=?, remittance_fee5=?, remittance_fee6=?, remittance_fee7=?
            WHERE num=? LIMIT 1";

        $stmh = $pdo->prepare($sql);

        $params = [
            $memo, $update_log,
            $sendDate1, $sendDate2, $sendDate3, $sendDate4, $sendDate5, $sendDate6, $sendDate7,
            $customs_fee1, $customs_fee2, $customs_fee3, $customs_fee4, $customs_fee5, $customs_fee6, $customs_fee7,
            $customs_fee_total1, $customs_fee_total2, $customs_fee_total3, $customs_fee_total4, $customs_fee_total5, $customs_fee_total6, $customs_fee_total7,
            $customs_vat1, $customs_vat2, $customs_vat3, $customs_vat4, $customs_vat5, $customs_vat6, $customs_vat7,
            $customs_miscellaneous_fee1, $customs_miscellaneous_fee2, $customs_miscellaneous_fee3, $customs_miscellaneous_fee4, $customs_miscellaneous_fee5, $customs_miscellaneous_fee6, $customs_miscellaneous_fee7,
            $customs_container_fee1, $customs_container_fee2, $customs_container_fee3, $customs_container_fee4, $customs_container_fee5, $customs_container_fee6, $customs_container_fee7,
            $customs_commission1, $customs_commission2, $customs_commission3, $customs_commission4, $customs_commission5, $customs_commission6, $customs_commission7,
            $customs_detail_total1, $customs_detail_total2, $customs_detail_total3, $customs_detail_total4, $customs_detail_total5, $customs_detail_total6, $customs_detail_total7,
            $customs_input_amount_cny1, $customs_input_amount_cny2, $customs_input_amount_cny3, $customs_input_amount_cny4, $customs_input_amount_cny5, $customs_input_amount_cny6, $customs_input_amount_cny7,
            $customs_date1, $customs_date2, $customs_date3, $customs_date4, $customs_date5, $customs_date6, $customs_date7,
            $exchange_rate1, $exchange_rate2, $exchange_rate3, $exchange_rate4, $exchange_rate5, $exchange_rate6, $exchange_rate7,
            $send_amount_krw1, $send_amount_krw2, $send_amount_krw3, $send_amount_krw4, $send_amount_krw5, $send_amount_krw6, $send_amount_krw7,
            $remittance_fee1, $remittance_fee2, $remittance_fee3, $remittance_fee4, $remittance_fee5, $remittance_fee6, $remittance_fee7,
            $num
        ];

        $stmh->execute($params);
        $pdo->commit();
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        print "오류: " . $Exception->getMessage();
    }
}
$data = [   
    'num' => $num,
    'mode' => $mode,
    'sendDate1' => $sendDate1,
    'sendDate2' => $sendDate2, 
    'sendDate3' => $sendDate3,
    'sendDate4' => $sendDate4,
    'sendDate5' => $sendDate5,
    'sendDate6' => $sendDate6,
    'sendDate7' => $sendDate7,
    'customs_fee1' => $customs_fee1,
    'customs_fee2' => $customs_fee2,
    'customs_fee3' => $customs_fee3, 
    'customs_fee4' => $customs_fee4,
    'customs_fee5' => $customs_fee5,
    'customs_fee6' => $customs_fee6,
    'customs_fee7' => $customs_fee7,
    'customs_fee_total1' => $customs_fee_total1,
    'customs_fee_total2' => $customs_fee_total2,
    'customs_fee_total3' => $customs_fee_total3,
    'customs_fee_total4' => $customs_fee_total4,
    'customs_fee_total5' => $customs_fee_total5,
    'customs_fee_total6' => $customs_fee_total6,
    'customs_fee_total7' => $customs_fee_total7,
    'customs_vat1' => $customs_vat1,
    'customs_vat2' => $customs_vat2,
    'customs_vat3' => $customs_vat3,
    'customs_vat4' => $customs_vat4,
    'customs_vat5' => $customs_vat5,
    'customs_vat6' => $customs_vat6,
    'customs_vat7' => $customs_vat7,
    'customs_miscellaneous_fee1' => $customs_miscellaneous_fee1,
    'customs_miscellaneous_fee2' => $customs_miscellaneous_fee2,
    'customs_miscellaneous_fee3' => $customs_miscellaneous_fee3,
    'customs_miscellaneous_fee4' => $customs_miscellaneous_fee4,
    'customs_miscellaneous_fee5' => $customs_miscellaneous_fee5,
    'customs_miscellaneous_fee6' => $customs_miscellaneous_fee6,
    'customs_miscellaneous_fee7' => $customs_miscellaneous_fee7,
    'customs_container_fee1' => $customs_container_fee1,
    'customs_container_fee2' => $customs_container_fee2,
    'customs_container_fee3' => $customs_container_fee3,
    'customs_container_fee4' => $customs_container_fee4,
    'customs_container_fee5' => $customs_container_fee5,
    'customs_container_fee6' => $customs_container_fee6,
    'customs_container_fee7' => $customs_container_fee7,
    'customs_commission1' => $customs_commission1,
    'customs_commission2' => $customs_commission2,
    'customs_commission3' => $customs_commission3,
    'customs_commission4' => $customs_commission4,
    'customs_commission5' => $customs_commission5,
    'customs_commission6' => $customs_commission6,
    'customs_commission7' => $customs_commission7,
    'customs_detail_total1' => $customs_detail_total1,
    'customs_detail_total2' => $customs_detail_total2,
    'customs_detail_total3' => $customs_detail_total3,
    'customs_detail_total4' => $customs_detail_total4,
    'customs_detail_total5' => $customs_detail_total5,
    'customs_detail_total6' => $customs_detail_total6,
    'customs_detail_total7' => $customs_detail_total7,
    'customs_input_amount_cny1' => $customs_input_amount_cny1,
    'customs_input_amount_cny2' => $customs_input_amount_cny2,
    'customs_input_amount_cny3' => $customs_input_amount_cny3,
    'customs_input_amount_cny4' => $customs_input_amount_cny4,
    'customs_input_amount_cny5' => $customs_input_amount_cny5,
    'customs_input_amount_cny6' => $customs_input_amount_cny6,
    'customs_input_amount_cny7' => $customs_input_amount_cny7,
    'customs_date1' => $customs_date1,
    'customs_date2' => $customs_date2,
    'customs_date3' => $customs_date3,
    'customs_date4' => $customs_date4,
    'customs_date5' => $customs_date5,
    'customs_date6' => $customs_date6,
    'customs_date7' => $customs_date7,
    'exchange_rate1' => $exchange_rate1,
    'exchange_rate2' => $exchange_rate2,
    'exchange_rate3' => $exchange_rate3,
    'exchange_rate4' => $exchange_rate4,
    'exchange_rate5' => $exchange_rate5,
    'exchange_rate6' => $exchange_rate6,
    'exchange_rate7' => $exchange_rate7,
    'send_amount_krw1' => $send_amount_krw1,
    'send_amount_krw2' => $send_amount_krw2,
    'send_amount_krw3' => $send_amount_krw3,
    'send_amount_krw4' => $send_amount_krw4,
    'send_amount_krw5' => $send_amount_krw5,
    'send_amount_krw6' => $send_amount_krw6,
    'send_amount_krw7' => $send_amount_krw7,
    'remittance_fee1' => $remittance_fee1,
    'remittance_fee2' => $remittance_fee2,
    'remittance_fee3' => $remittance_fee3,
    'remittance_fee4' => $remittance_fee4,
    'remittance_fee5' => $remittance_fee5,
    'remittance_fee6' => $remittance_fee6,
    'remittance_fee7' => $remittance_fee7
];

echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>
