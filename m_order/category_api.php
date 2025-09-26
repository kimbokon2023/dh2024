<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");

// 권한 체크 - 관리자만 접근 가능
if (!isset($_SESSION["level"]) || $_SESSION["level"] > 1) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

$jsonFilePath = __DIR__ . '/vendor_categories.json';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // JSON 파일 읽기
        if (!file_exists($jsonFilePath)) {
            // 파일이 없으면 기본 데이터 생성
            $defaultData = [
                "default" => [
                    "name" => "기본",
                    "categories" => ["모터", "연동제어기", "원단", "부속자재", "운송비"]
                ]
            ];

            file_put_contents($jsonFilePath, json_encode($defaultData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            echo json_encode($defaultData);
        } else {
            $content = file_get_contents($jsonFilePath);
            $data = json_decode($content, true);

            if ($data === null) {
                throw new Exception('JSON 파일 파싱 오류');
            }

            echo json_encode($data);
        }

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // JSON 파일 쓰기
        $action = $_POST['action'] ?? '';

        if ($action === 'save') {
            $jsonData = $_POST['data'] ?? '';

            if (empty($jsonData)) {
                throw new Exception('저장할 데이터가 없습니다.');
            }

            $data = json_decode($jsonData, true);
            if ($data === null) {
                throw new Exception('잘못된 JSON 데이터입니다.');
            }

            // 데이터 유효성 검증
            if (!isset($data['default'])) {
                throw new Exception('기본 카테고리는 필수입니다.');
            }

            // JSON 파일 저장 (백업 파일도 생성)
            $backupPath = $jsonFilePath . '.backup.' . date('Y-m-d-H-i-s');
            if (file_exists($jsonFilePath)) {
                copy($jsonFilePath, $backupPath);
            }

            $result = file_put_contents($jsonFilePath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            if ($result === false) {
                throw new Exception('파일 저장에 실패했습니다.');
            }

            echo json_encode([
                'success' => true,
                'message' => '카테고리 매핑이 성공적으로 저장되었습니다.',
                'backup_file' => basename($backupPath)
            ]);

        } else {
            throw new Exception('잘못된 요청입니다.');
        }

    } else {
        throw new Exception('지원하지 않는 HTTP 메소드입니다.');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>