<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");

$pdo = db_connect();
$DB = $_SESSION["DB"];

try {
    // m_order 테이블의 컬럼 구조 확인
    $sql = "SHOW COLUMNS FROM {$DB}.m_order";
    $stmt = $pdo->query($sql);
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>m_order 테이블 컬럼 구조:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

    $china_item_exists = false;
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Default']) . "</td>";
        echo "<td>" . htmlspecialchars($column['Extra']) . "</td>";
        echo "</tr>";

        if ($column['Field'] === 'china_item') {
            $china_item_exists = true;
        }
    }
    echo "</table>";

    if ($china_item_exists) {
        echo "<p style='color: green;'><strong>✅ china_item 컬럼이 존재합니다.</strong></p>";
    } else {
        echo "<p style='color: red;'><strong>❌ china_item 컬럼이 존재하지 않습니다. SQL 스크립트를 실행해야 합니다.</strong></p>";
        echo "<p>실행해야 할 SQL:</p>";
        echo "<pre>ALTER TABLE `{$DB}`.`m_order` ADD COLUMN `china_item` VARCHAR(200) DEFAULT NULL COMMENT '중국발주처 품목명' AFTER `vendor_name`;</pre>";
    }

} catch (PDOException $e) {
    echo "오류: " . $e->getMessage();
}
?>