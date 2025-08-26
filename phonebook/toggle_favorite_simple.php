<?php
header('Content-Type: application/json');

// 오류 표시 설정 (디버깅용)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', $_SERVER['DOCUMENT_ROOT'] . '/php_errors.log');

// 기본 정보 로깅
error_log('[toggle_favorite_simple.php] 스크립트 시작');

// JSON 파싱 - php://input 사용
$input = file_get_contents('php://input');
error_log('[toggle_favorite_simple.php] Raw input: ' . $input);

if (empty($input)) {
    error_log('[toggle_favorite_simple.php] 입력 데이터가 비어있습니다.');
    http_response_code(400);
    echo json_encode([
        "error" => "요청 데이터가 비어있습니다.", 
        "details" => "JSON 데이터가 전송되지 않았습니다."
    ]);
    exit;
}

$data = json_decode($input, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log('[toggle_favorite_simple.php] JSON decode error: ' . json_last_error_msg());
    http_response_code(400);
    echo json_encode([
        "error" => "잘못된 JSON 형식입니다.", 
        "details" => json_last_error_msg()
    ]);
    exit;
}

error_log('[toggle_favorite_simple.php] Parsed data: ' . print_r($data, true));

// 성공 응답
echo json_encode([
    "success" => true,
    "message" => "데이터를 성공적으로 받았습니다.",
    "received_data" => $data
]);
?>
