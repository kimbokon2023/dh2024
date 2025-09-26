<?php
require_once('../session.php');
require_once('../lib/mydb.php');
$pdo = db_connect();

echo "<h3>unitprice 테이블 구조</h3>";

try {
    $stmt = $pdo->query("DESCRIBE {$DB}.unitprice");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

    foreach($columns as $column) {
        echo "<tr>";
        echo "<td>" . ($column['Field'] ?? '') . "</td>";
        echo "<td>" . ($column['Type'] ?? '') . "</td>";
        echo "<td>" . ($column['Null'] ?? '') . "</td>";
        echo "<td>" . ($column['Key'] ?? '') . "</td>";
        echo "<td>" . ($column['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . ($column['Extra'] ?? '') . "</td>";
        echo "</tr>";
    }
    echo "</table>";

} catch (Exception $e) {
    echo "에러: " . $e->getMessage();
}
?>