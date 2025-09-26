<?php
require_once('../session.php');
require_once('../lib/mydb.php');
$pdo = db_connect();

echo "<h3>판매가(원화) 컬럼 추가</h3>";

try {
    // 컬럼이 이미 존재하는지 확인
    $checkStmt = $pdo->query("SHOW COLUMNS FROM {$DB}.unitprice LIKE 'sell_price_krw'");
    if ($checkStmt->rowCount() > 0) {
        echo "<p style='color: orange;'>sell_price_krw 컬럼이 이미 존재합니다.</p>";
    } else {
        // 컬럼 추가 실행
        $sql = "ALTER TABLE {$DB}.unitprice ADD COLUMN sell_price_krw DECIMAL(15,2) NULL COMMENT '판매가(원화)' AFTER unit_price_cny";

        $pdo->exec($sql);
        echo "<p style='color: green;'>✅ sell_price_krw 컬럼이 성공적으로 추가되었습니다.</p>";
    }

    // 결과 확인
    echo "<h4>변경된 테이블 구조 (관련 컬럼들):</h4>";
    $stmt = $pdo->query("DESCRIBE {$DB}.unitprice");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Comment</th></tr>";

    foreach($columns as $column) {
        // 가격 관련 컬럼만 표시
        if (strpos($column['Field'], 'price') !== false || $column['Field'] === 'exchange_rate') {
            echo "<tr>";
            echo "<td><strong>" . $column['Field'] . "</strong></td>";
            echo "<td>" . $column['Type'] . "</td>";
            echo "<td>" . $column['Null'] . "</td>";
            echo "<td>" . ($column['Comment'] ?? '') . "</td>";
            echo "</tr>";
        }
    }
    echo "</table>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ 에러: " . $e->getMessage() . "</p>";
}
?>

<p><a href="unitprice_list.php">단가 관리 페이지로 돌아가기</a></p>