<?php
error_reporting(0); // 오류 리포팅 비활성화

if (!isset($_SESSION)) {
    session_start();
}

header("Content-Type: application/json");

$filePath = './Company_approvalLine_.json';

if(file_exists($filePath)) {
    $data = json_decode(file_get_contents($filePath), true);
    if (!is_array($data)) {
        $data = array();
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([]);
}

?>
