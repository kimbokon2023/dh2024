<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");

header("Content-Type: application/json");  // JSON을 사용하기 위해 필요한 구문

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

function getItems($pdo, $table) {
    $sql = "SELECT itemName FROM $table";
    try {
        $stmh = $pdo->query($sql);
        $items = $stmh->fetchAll(PDO::FETCH_COLUMN, 0);  // 첫 번째 열의 값을 배열로 가져옵니다.
        // Use array_filter to remove empty entries from the array
        return array_filter($items, function($value) {
            return !empty($value) && $value !== null && $value !== '';
        });
    } catch (PDOException $Exception) {
        print "오류: " . $Exception->getMessage();
        return [];
    }
}

// Hardcoded motor items
$motorItems = [
    '150KG',
    '300KG',
    '400KG',
    '500KG',
    '600KG',
    '800KG',
    '1000KG',
    '1500KG',
    '2000KG'
];

// Hardcoded wireless controller items
$wirelessControllerItems = [
    "흰색박스",
    "1500KG(콘트롤박스)"
];

// Hardcoded wire controller items
$wireControllerItems = [
    "흰색박스",
    "1500KG(콘트롤박스)"
];

// Hardcoded wireless link items
$wirelessLinkItems = [
    "노출형",
    "매립형"
];

// Hardcoded wire link items
$wireLinkItems = [
    "노출형",
    "매립형"
];
// Hardcoded wire link items
$bracketItems = [
    "380*180(스크린용)300-400KG",
    "530*320(철재용)300-400KG",
    "600*350(철재용)500-600KG",
	"690*390(철재용)800-1000KG",
	"910*600(철재용)1500KG",
	"650*270(철재용)2000KG"	
];

$data = [
    'motorItems' => $motorItems,
    'wirelessControllerItems' => $wirelessControllerItems,
    'wireControllerItems' => $wireControllerItems,
    'wirelessLinkItems' => $wirelessLinkItems,
    'wireLinkItems' => $wireLinkItems,
    'bracketItems' => $bracketItems
];

echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>