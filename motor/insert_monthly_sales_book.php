<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");   
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

// AJAX 요청으로 전달된 데이터 받기
$uniqueNum = isset($_REQUEST['uniqueNum']) ? $_REQUEST['uniqueNum'] : '';
$book_issued = isset($_REQUEST['book_issued']) ? $_REQUEST['book_issued'] : '';

$response = [
    'status' => 'error',
    'message' => 'No data to save',
    'received_count' => 0
];

try{
    if (!empty($uniqueNum)) {
        // book_issued 값이 없으면 현재 시간으로 설정
        if (empty($book_issued)) {
            $book_issued = date('Y-m-d H:i:s');
        }
        
        // 기존 데이터가 있으면 업데이트
        $updateStmt = $pdo->prepare("
            UPDATE monthly_sales
            SET book_issued = :book_issued
            WHERE num = :num
        ");                    
        $updateStmt->bindValue(':book_issued', $book_issued, PDO::PARAM_STR);
        $updateStmt->bindValue(':num', $uniqueNum, PDO::PARAM_INT);
        $updateStmt->execute();
        
        $results[] = ['mode' => 'update', 'num' => $uniqueNum, 'book_issued' => $book_issued];

        $response = [
            'status' => 'success',
            'message' => '전송시간이 성공적으로 기록되었습니다.',
            'results' => $results,
            'received_count' => 1
        ];
    } else {
        $response = [
            'status' => 'error',
            'message' => 'uniqueNum이 전달되지 않았습니다.',
            'received_count' => 0
        ];
    }
} catch (PDOException $Exception) {
    $response = [
        'status' => 'error',
        'message' => $Exception->getMessage(),
        'received_count' => 0
    ];
}

// JSON 응답 헤더 설정
header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
