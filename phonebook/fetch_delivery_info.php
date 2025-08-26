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
        // 즐겨찾기 정보를 먼저 가져옴
        $sql = "SELECT favorites_list FROM $DB.delivery_favorites WHERE secondordnum = :secondordnum";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':secondordnum', $secondordnum, PDO::PARAM_INT);
        $stmt->execute();
        $favorites_result = $stmt->fetch(PDO::FETCH_ASSOC);
        $favorites = $favorites_result ? json_decode($favorites_result['favorites_list'], true) : [];

        // 기본 쿼리 작성
        $sql = "SELECT deliverymethod, delcompany, address, delbranch, delbranchaddress, delcaritem, delcartel, loadplace,  chargedmantel, chargedman ,cargo_delbranchinvoice, cargo_delwrapmethod, cargo_delwrapsu, cargo_delwrapamount ,cargo_delwrapweight,cargo_delwrappaymethod 
                FROM $DB.motor 
                WHERE secondordnum = :secondordnum AND is_deleted IS NULL AND (chargedman IS NOT NULL or chargedman !='' ) ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':secondordnum', $secondordnum, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 즐겨찾기 목록과 결과 목록 비교 및 제외
        $filtered_results = array_filter($results, function($result) use ($favorites) {
            foreach ($favorites as $favorite) {
                $match = true;
                foreach ($favorite as $key => $value) {
                    // 공백이나 null 값을 제외하고 비교
                    if (!empty($value) && isset($result[$key]) && $result[$key] !== $value) {
                        $match = false;
                        break;
                    }
                }
                if ($match) {
                    return false;
                }
            }
            return true;
        });

        // $favorites 값을 함께 반환
        echo json_encode(['results' => array_values($filtered_results), 'favorites' => $favorites]);
    } catch (Exception $e) {
        error_log('Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
        error_log($e->getTraceAsString());
        echo json_encode(['error' => 'An error occurred while processing your request.']);
    }
} else {
    echo json_encode(['error' => 'No secondordnum provided']);
}
?>
