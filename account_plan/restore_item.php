<?php
/**
 * 제외 항목 복구
 *
 * 이 파일은 클라이언트에서 요청한 제외 항목을 excluded_items.json 파일에서 삭제합니다.
 */

// 에러 표시 설정
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$yearMonth = $_POST['yearMonth'];
$customerName = $_POST['customerName'];
$amount = $_POST['amount'];

$excludedItemsFile = $_SERVER['DOCUMENT_ROOT'] . "/account_plan/excluded_items.json";

$excludedItems = [];
if (file_exists($excludedItemsFile)) {
    $excludedItems = json_decode(file_get_contents($excludedItemsFile), true);
}

// 제외 항목 배열에서 해당 항목 제거
$updatedExcludedItems = array_filter($excludedItems, function ($item) use ($yearMonth, $customerName, $amount) {
    return !(
        $item['yearMonth'] === $yearMonth &&
        $item['customerName'] === $customerName &&
        intval($item['amount']) === intval($amount)
    );
});

// 배열 인덱스 재정렬
$updatedExcludedItems = array_values($updatedExcludedItems);

// 업데이트된 제외 항목 배열을 JSON 파일에 저장
file_put_contents($excludedItemsFile, json_encode($updatedExcludedItems));

echo json_encode(['success' => true]);
?>