<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect(); // 데이터베이스 연결

// `secondordnum` 값을 GET 파라미터에서 받아옴
$secondordnum = isset($_GET['secondordnum']) ? $_GET['secondordnum'] : null;

if ($secondordnum) {
    // SQL 쿼리 작성, `secondordnum`을 조건으로 추가
    $sql = "SELECT deliverymethod, delcompany, address, delbranch, delbranchaddress, delcaritem, delcartel, loadplace, chargedmantel, chargedman 
            FROM motor 
            WHERE secondordnum = :secondordnum and is_deleted IS NULL ";

    // 준비된 명령문 준비
    $stmt = $pdo->prepare($sql);

    // `secondordnum` 파라미터 바인딩
    $stmt->bindParam(':secondordnum', $secondordnum, PDO::PARAM_INT);

    // 쿼리 실행
    $stmt->execute();

    // 결과를 JSON 형태로 변환
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($results);
} else {
    // `secondordnum`이 제공되지 않은 경우
    echo json_encode(array("error" => "No secondordnum provided"));
}
?>
