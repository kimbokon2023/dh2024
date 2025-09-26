<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");

// 권한 체크
if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

try {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
    $pdo = db_connect();

    // 중국발주 업체 목록 조회 - china_sort_order 순서로 정렬
    $sql = "SELECT num, vendor_name, image_base64, item
            FROM {$DB}.phonebook_buy
            WHERE is_china_vendor = 1 AND is_deleted IS NULL
            ORDER BY china_sort_order ASC, vendor_name ASC";

    $stmt = $pdo->query($sql);
    $vendors = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($vendors);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => '데이터베이스 오류: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>