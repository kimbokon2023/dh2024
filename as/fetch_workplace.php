<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect(); // 데이터베이스 연결

// 검색어를 GET 파라미터에서 받아옴
$searchQuery = isset($_GET['query']) ? $_GET['query'] : null;

if ($searchQuery) {
    // SQL 쿼리 작성, `workplacename`을 검색어로 사용
    $sql = "SELECT workplacename, num 
            FROM motor 
            WHERE workplacename LIKE :searchQuery 
            AND is_deleted IS NULL
            LIMIT 10";  // 검색 결과 제한

    // 준비된 명령문 준비
    $stmt = $pdo->prepare($sql);

    // 검색어 파라미터 바인딩
    $stmt->bindValue(':searchQuery', '%' . $searchQuery . '%', PDO::PARAM_STR);

    // 쿼리 실행
    $stmt->execute();

    // 결과를 HTML 테이블 행으로 변환
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($results) {
        foreach ($results as $row) {
            echo '<tr onclick="selectWorkplace(\'' . htmlspecialchars($row['workplacename'], ENT_QUOTES) . '\', \'' . $row['num'] . '\')">';
            echo '<td>' . htmlspecialchars($row['workplacename'], ENT_QUOTES) . '</td>';
            echo '<td>' . htmlspecialchars($row['num'], ENT_QUOTES) . '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="2">No results found.</td></tr>';
    }
} else {
    echo '<tr><td colspan="2">Please enter a search query.</td></tr>';
}
?>
