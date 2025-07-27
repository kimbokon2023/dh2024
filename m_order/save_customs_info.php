<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
header('Content-Type: application/json; charset=utf-8');

$num = isset($_POST['num']) ? $_POST['num'] : '';
$round = isset($_POST['round']) ? intval($_POST['round']) : 0;
$vat = isset($_POST['vat']) ? str_replace(',', '', $_POST['vat']) : '';
$etc = isset($_POST['etc']) ? str_replace(',', '', $_POST['etc']) : '';
$container = isset($_POST['container']) ? str_replace(',', '', $_POST['container']) : '';
$fee = isset($_POST['fee']) ? str_replace(',', '', $_POST['fee']) : '';
$total = isset($_POST['total']) ? str_replace(',', '', $_POST['total']) : '';
$date = isset($_POST['date']) ? $_POST['date'] : '';
$input_amount_cny = isset($_POST['input_amount_cny']) ? str_replace(',', '', $_POST['input_amount_cny']) : '';

if (!$num || !$round || $round < 1 || $round > 4) {
    echo json_encode(['success' => false, 'message' => '필수 파라미터 누락 또는 차수 오류']);
    exit;
}

$pdo = db_connect();
$sql = "UPDATE m_order SET
    customs_vat{$round}=?,
    customs_miscellaneous_fee{$round}=?,
    customs_container_fee{$round}=?,
    customs_commission{$round}=?,
    customs_detail_total{$round}=?,
    customs_date{$round}=?,
    customs_input_amount_cny{$round}=?
    WHERE num=?";
$stmt = $pdo->prepare($sql);
$success = $stmt->execute([
    $vat, $etc, $container, $fee, $total, $date, $input_amount_cny, $num
]);

if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'DB 저장 실패']);
} 