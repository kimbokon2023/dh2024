<?php
// 담당자 정보 저장 save_charge_person.php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");

// 권한 확인
if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    echo json_encode(['status' => 'error', 'message' => '권한이 없습니다.']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_POST['chargePerson']) || empty(trim($_POST['chargePerson']))) {
        echo json_encode(['status' => 'error', 'message' => '담당자명을 입력하세요.']);
        exit;
    }

    $chargePerson = trim($_POST['chargePerson']);

    // motor 폴더에 accountCharged.txt 파일로 저장
    $filePath = __DIR__ . '/accountCharged.txt';

    // 파일에 담당자 정보 저장
    $result = file_put_contents($filePath, $chargePerson);

    if ($result !== false) {
        echo json_encode(['status' => 'success', 'message' => '담당자가 저장되었습니다.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => '파일 저장 중 오류가 발생했습니다.']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => '오류가 발생했습니다: ' . $e->getMessage()]);
}
?> 