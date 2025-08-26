<?php
error_reporting(0); // 오류 리포팅 비활성화

if (!isset($_SESSION)) {
    session_start();
}

header("Content-Type: application/json");

// POST 데이터 수신
$savedName = $_POST['savedName'] ?? '';

$filePath = './Company_approvalLine_.json';

if(file_exists($filePath)) {
    $data = json_decode(file_get_contents($filePath), true);
    
    if (is_array($data)) {
        foreach ($data as $approvalLine) {
            if (isset($approvalLine['savedName']) && $approvalLine['savedName'] === $savedName) {
                echo json_encode($approvalLine, JSON_UNESCAPED_UNICODE);
                exit;
            }
        }
    }

    // 일치 항목이 없거나 데이터가 비정상이어도 항상 유효한 JSON 반환
    echo json_encode(array("approvalOrder" => array()), JSON_UNESCAPED_UNICODE);
    exit;
} else {
    // 파일이 없더라도 파서 에러가 발생하지 않도록 빈 구조 반환
    echo json_encode(array("approvalOrder" => array()), JSON_UNESCAPED_UNICODE);
    exit;
}

?>
