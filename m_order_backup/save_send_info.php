<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
header('Content-Type: application/json; charset=utf-8');

$num = isset($_POST['num']) ? $_POST['num'] : '';
$round = isset($_POST['round']) ? intval($_POST['round']) : 0;
$date = isset($_POST['date']) ? $_POST['date'] : '';
$rate = isset($_POST['rate']) ? $_POST['rate'] : '';
$krw = isset($_POST['krw']) ? str_replace(',', '', $_POST['krw']) : '';
$cny = isset($_POST['cny']) ? str_replace(',', '', $_POST['cny']) : '';
$remittanceFee = isset($_POST['remittanceFee']) ? str_replace(',', '', $_POST['remittanceFee']) : '';

if (!$num || $round < 1 || $round > 4) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

$colDate = "sendDate$round";
$colRate = "exchange_rate$round";
$colKRW = "send_amount_krw$round";
$colCNY = "sendMoney$round";
$colRemittanceFee = "remittance_fee$round";

$pdo = db_connect();
$sql = "UPDATE m_order SET $colDate=?, $colRate=?, $colKRW=?, $colCNY=?, $colRemittanceFee=? WHERE num=?";
$stmh = $pdo->prepare($sql);
$success = $stmh->execute([$date, $rate, $krw, $cny, $remittanceFee, $num]);

if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'DB update failed']);
} 