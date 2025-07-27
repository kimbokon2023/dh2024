<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

// PHP 오류를 로그 파일에 기록하도록 설정
ini_set('log_errors', 1);
ini_set('error_log', $_SERVER['DOCUMENT_ROOT'] . '/php_errors.log');
ini_set('display_errors', 0);

header('Content-Type: application/json'); // JSON 형식으로 응답

try {
    // Get all records from delivery_favorites
    $sql = "SELECT num, favorites_list FROM delivery_favorites";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $result) {
        $num = $result['num'];
        $favorites = json_decode($result['favorites_list'], true) ?? [];

        // Update deliverymethod values
        $updated = false;
        foreach ($favorites as &$favorite) {
            if (isset($favorite['deliverymethod'])) {
                if ($favorite['deliverymethod'] === '택배') {
                    $favorite['deliverymethod'] = '선/택배';
                    $updated = true;
                } elseif ($favorite['deliverymethod'] === '경동화물') {
                    $favorite['deliverymethod'] = '선/경동화물';
                    $updated = true;
                }
            }
        }

        if ($updated) {
            $favorites_list = json_encode($favorites);

            // Update the favorites_list in the database
            $sql_update = "UPDATE delivery_favorites SET favorites_list = :favorites_list WHERE num = :num";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->bindParam(':favorites_list', $favorites_list, PDO::PARAM_STR);
            $stmt_update->bindParam(':num', $num, PDO::PARAM_INT);
            $stmt_update->execute();
        }
    }

    echo json_encode(["success" => true, "message" => "Favorites list updated successfully for all records."]);
} catch (Exception $e) {
    error_log('Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
    error_log($e->getTraceAsString());
    echo json_encode(['error' => 'An error occurred while updating the favorites list.']);
}
?>
