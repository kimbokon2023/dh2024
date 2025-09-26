<?php
// 담당자 정보 불러오기 load_charge_person.php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");

// 권한 확인
if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    echo json_encode(['status' => 'error', 'message' => '권한이 없습니다.']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

try {
    // motor 폴더의 accountCharged.txt 파일 확인
    $filePath = __DIR__ . '/accountCharged.txt';

    if (file_exists($filePath)) {
        $chargePerson = file_get_contents($filePath);
        $chargePerson = trim($chargePerson);

        if (!empty($chargePerson)) {
            echo json_encode(['status' => 'success', 'chargePerson' => $chargePerson]);
        } else {
            echo json_encode(['status' => 'success', 'chargePerson' => '']);
        }
    } else {
        echo json_encode(['status' => 'success', 'chargePerson' => '']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => '오류가 발생했습니다: ' . $e->getMessage()]);
}
?> 