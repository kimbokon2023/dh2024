<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect(); // 데이터베이스 연결

// PHP 오류를 로그 파일에 기록하도록 설정
ini_set('log_errors', 1);
ini_set('error_log', $_SERVER['DOCUMENT_ROOT'] . '/php_errors.log');
ini_set('display_errors', 0);

$secondordnum = isset($_GET['secondordnum']) ? $_GET['secondordnum'] : null;

header('Content-Type: application/json'); // JSON 형식으로 응답

if ($secondordnum) {
    try {
        // 즐겨찾기 정보를 가져옴
        $sql = "SELECT favorites_list FROM $DB.delivery_favorites WHERE secondordnum = :secondordnum";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':secondordnum', $secondordnum, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $favorites = json_decode($result['favorites_list'], true);
            echo json_encode(['results' => $favorites]);
        } else {
            echo json_encode(['results' => []]);
        }
    } catch (Exception $e) {
        error_log('Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
        error_log($e->getTraceAsString());
        echo json_encode(['error' => 'An error occurred while processing your request.']);
    }
} else {
    echo json_encode(['error' => 'No secondordnum provided']);
}
?>
