<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

$secondordnum = isset($_GET['secondordnum']) ? $_GET['secondordnum'] : null;

header('Content-Type: application/json');

if ($secondordnum) {
    try {
        $sql = "SELECT except_list FROM delivery_excepts WHERE secondordnum = :secondordnum";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':secondordnum', $secondordnum, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $excepts = $result ? json_decode($result['except_list'], true) : [];

        // 디버그용 로그 추가
        error_log('Secondordnum: ' . $secondordnum);
        error_log('Fetched result: ' . json_encode($result));
        error_log('Decoded excepts: ' . json_encode($excepts));

        echo json_encode(['results' => $excepts]);
    } catch (Exception $e) {
        error_log('Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
        echo json_encode(['error' => 'An error occurred while processing your request.']);
    }
} else {
    error_log('No secondordnum provided');
    echo json_encode(['error' => 'No secondordnum provided']);
}
?>
