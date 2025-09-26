<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
header('Content-Type: application/json; charset=utf-8');

$num = isset($_GET['num']) ? $_GET['num'] : '';
$round = isset($_GET['round']) ? intval($_GET['round']) : 0;

if (!$num || $round < 1 || $round > 7) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

$colDate = "sendDate$round";
$colRate = "exchange_rate$round";
$colKRW = "send_amount_krw$round";
$colCNY = "sendMoney$round";
$colRemittanceFee = "remittance_fee$round";

$pdo = db_connect();
$sql = "SELECT $colDate AS date, $colRate AS rate, $colKRW AS krw, $colCNY AS cny, $colRemittanceFee AS remittanceFee FROM m_order WHERE num = ? LIMIT 1";
$stmh = $pdo->prepare($sql);
$stmh->execute([$num]);
$row = $stmh->fetch(PDO::FETCH_ASSOC);

if ($row) {
    echo json_encode(['success' => true, 'data' => $row]);
} else {
    echo json_encode(['success' => false, 'message' => 'No data found']);
} 