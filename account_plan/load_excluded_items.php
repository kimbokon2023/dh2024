<?php
/**
 * 제외 항목 목록 로드 (수정)
 *
 * 이 파일은 excluded_items.json 파일에서 제외 항목 목록을 읽어와 HTML 테이블 코드로 반환합니다.
 * 복구 버튼이 추가되었습니다.
 */

$excludedItemsFile = $_SERVER['DOCUMENT_ROOT'] . "/account_plan/excluded_items.json";

$excludedItems = [];
if (file_exists($excludedItemsFile)) {
    $excludedItems = json_decode(file_get_contents($excludedItemsFile), true);
}

$tableRows = '';
if (!empty($excludedItems)) {
    foreach ($excludedItems as $item) {
        $tableRows .= "<tr>";
        $tableRows .= "<td>" . htmlspecialchars($item['yearMonth']) . "</td>";
        $tableRows .= "<td>" . htmlspecialchars($item['customerName']) . "</td>";
        $tableRows .= "<td>" . number_format($item['amount']) . "</td>";
        $tableRows .= "<td><button class='btn btn-secondary btn-sm restore-btn' data-year-month='" . htmlspecialchars($item['yearMonth']) . "' data-customer-name='" . htmlspecialchars($item['customerName']) . "' data-amount='" . htmlspecialchars($item['amount']) . "'>복구</button></td>";
        $tableRows .= "</tr>";
    }
} else {
    $tableRows = "<tr><td colspan='4'>제외된 항목이 없습니다.</td></tr>";
}

echo $tableRows;
?>