<?php
/**
 * 제외 항목 저장
 *
 * 이 파일은 클라이언트에서 요청한 제외 항목 정보를 JSON 파일로 저장합니다.
 */

// 에러 표시 설정
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$yearMonth = $_POST['yearMonth'];
$customerName = $_POST['customerName'];
$amount = $_POST['amount'];

// "(주)"는 유지하고 "(계산서발행)" 등 나머지 괄호 내용 제거
$cleanCustomerName = preg_replace('/\s*\((?!주\)).*?\)\s*/', '', $customerName);

// 데이터 유효성 검사 (필요한 경우 추가)
$excludedItem = [
    'yearMonth' => $yearMonth,
    'customerName' => $cleanCustomerName,
    'amount' => $amount
];

$excludedItemsFile = $_SERVER['DOCUMENT_ROOT'] . "/account_plan/excluded_items.json";

$excludedItems = [];
if (file_exists($excludedItemsFile)) {
    $excludedItems = json_decode(file_get_contents($excludedItemsFile), true);
}

$excludedItems[] = $excludedItem;

file_put_contents($excludedItemsFile, json_encode($excludedItems));

echo json_encode(['success' => true]);
?>
