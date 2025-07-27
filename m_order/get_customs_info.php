<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
header('Content-Type: application/json; charset=utf-8');

$num = isset($_GET['num']) ? $_GET['num'] : '';
$round = isset($_GET['round']) ? intval($_GET['round']) : 0;

if (!$num || !$round || $round < 1 || $round > 4) {
    echo json_encode(['success' => false, 'message' => '필수 파라미터 누락 또는 차수 오류']);
    exit;
}

$pdo = db_connect();
$stmt = $pdo->prepare("SELECT * FROM m_order WHERE num=? LIMIT 1");
$stmt->execute([$num]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    $data = [
        'vat'       => $row["customs_vat{$round}"] ?? '',
        'etc'       => $row["customs_miscellaneous_fee{$round}"] ?? '',
        'container' => $row["customs_container_fee{$round}"] ?? '',
        'fee'       => $row["customs_commission{$round}"] ?? '',
        'total'     => $row["customs_detail_total{$round}"] ?? '',
        'date'      => $row["customs_date{$round}"] ?? '',
        'input_amount_cny' => $row["customs_input_amount_cny{$round}"] ?? '',
        // 날짜는 별도 컬럼이 없으므로 필요시 orderDate 또는 null
        'orderDate' => $row["orderDate"] ?? ''
    ];
    echo json_encode(['success' => true, 'data' => $data]);
} else {
    echo json_encode(['success' => false, 'data' => null]);
}