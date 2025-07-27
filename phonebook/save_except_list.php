<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect(); // 데이터베이스 연결

// PHP 오류를 로그 파일에 기록하도록 설정
ini_set('log_errors', 1);
ini_set('error_log', $_SERVER['DOCUMENT_ROOT'] . '/php_errors.log');
ini_set('display_errors', 0);

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$secondordnum = isset($data['secondordnum']) ? $data['secondordnum'] : null;
$exceptList = isset($data['exceptList']) ? $data['exceptList'] : null;

if ($secondordnum && $exceptList) {
    try {
        $sql = "SELECT * FROM delivery_excepts WHERE secondordnum = :secondordnum";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':secondordnum', $secondordnum, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $existingExcepts = json_decode($result['except_list'], true) ?? [];
            $existingKeys = [];

            // 기존 항목과 비교하여 중복 확인
            foreach ($existingExcepts as $key => $existingItem) {
                foreach ($exceptList as $newItem) {
                    if (
                        $existingItem['deliverymethod'] === $newItem['deliverymethod'] &&
                        $existingItem['address'] === $newItem['address'] &&
                        $existingItem['receiver'] === $newItem['receiver'] &&
                        $existingItem['tel'] === $newItem['tel']
                    ) {
                        $existingKeys[] = $key;
                        break;
                    }
                }
            }

            // 중복 항목 제거
            foreach ($existingKeys as $key) {
                unset($existingExcepts[$key]);
            }

            // 새 항목 추가
            $existingExcepts = array_merge($existingExcepts, $exceptList);
            $exceptListJson = json_encode(array_values($existingExcepts));

            $sql = "UPDATE delivery_excepts SET except_list = :except_list WHERE secondordnum = :secondordnum";
        } else {
            $exceptListJson = json_encode($exceptList);
            $sql = "INSERT INTO delivery_excepts (secondordnum, except_list) VALUES (:secondordnum, :except_list)";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':secondordnum', $secondordnum, PDO::PARAM_STR);
        $stmt->bindParam(':except_list', $exceptListJson, PDO::PARAM_STR);
        $stmt->execute();

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        error_log('Exception: ' . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'An error occurred while processing your request.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid input.']);
}
?>
