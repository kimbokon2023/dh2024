<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");

$pdo = db_connect();

echo "<h2>건의사항 시스템 데이터베이스 테이블 확인</h2>";

// suggestion 테이블 확인
try {
    $sql = "SHOW TABLES LIKE 'suggestion'";
    $stmh = $pdo->query($sql);
    $suggestion_exists = $stmh->rowCount() > 0;
    
    if ($suggestion_exists) {
        echo "<p style='color: green;'>✅ suggestion 테이블이 존재합니다.</p>";
    } else {
        echo "<p style='color: red;'>❌ suggestion 테이블이 존재하지 않습니다.</p>";
        
        // 테이블 생성
        $create_suggestion = "
        CREATE TABLE IF NOT EXISTS suggestion (
            num INT AUTO_INCREMENT PRIMARY KEY,
            id VARCHAR(20) NOT NULL,
            name VARCHAR(20) NOT NULL,
            subject VARCHAR(200) NOT NULL,
            content LONGTEXT,
            regist_day DATETIME DEFAULT CURRENT_TIMESTAMP,
            hit INT DEFAULT 0,
            is_html CHAR(1) DEFAULT 'n',
            suggestioncheck CHAR(1) DEFAULT 'n',
            searchtext LONGTEXT
        )";
        
        try {
            $pdo->exec($create_suggestion);
            echo "<p style='color: green;'>✅ suggestion 테이블이 생성되었습니다.</p>";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>❌ suggestion 테이블 생성 실패: " . $e->getMessage() . "</p>";
        }
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ suggestion 테이블 확인 실패: " . $e->getMessage() . "</p>";
}

// suggestion_ripple 테이블 확인
try {
    $sql = "SHOW TABLES LIKE 'suggestion_ripple'";
    $stmh = $pdo->query($sql);
    $ripple_exists = $stmh->rowCount() > 0;
    
    if ($ripple_exists) {
        echo "<p style='color: green;'>✅ suggestion_ripple 테이블이 존재합니다.</p>";
    } else {
        echo "<p style='color: red;'>❌ suggestion_ripple 테이블이 존재하지 않습니다.</p>";
        
        // 테이블 생성
        $create_ripple = "
        CREATE TABLE IF NOT EXISTS suggestion_ripple (
            num INT AUTO_INCREMENT PRIMARY KEY,
            parent INT NOT NULL,
            id VARCHAR(20) NOT NULL,
            name VARCHAR(20) NOT NULL,
            nick VARCHAR(20),
            content TEXT NOT NULL,
            regist_day DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (parent) REFERENCES suggestion(num) ON DELETE CASCADE
        )";
        
        try {
            $pdo->exec($create_ripple);
            echo "<p style='color: green;'>✅ suggestion_ripple 테이블이 생성되었습니다.</p>";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>❌ suggestion_ripple 테이블 생성 실패: " . $e->getMessage() . "</p>";
        }
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ suggestion_ripple 테이블 확인 실패: " . $e->getMessage() . "</p>";
}

// 테이블 구조 확인
if ($suggestion_exists || $ripple_exists) {
    echo "<h3>테이블 구조 확인</h3>";
    
    if ($suggestion_exists) {
        echo "<h4>suggestion 테이블 구조:</h4>";
        try {
            $sql = "DESCRIBE suggestion";
            $stmh = $pdo->query($sql);
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>필드</th><th>타입</th><th>NULL</th><th>키</th><th>기본값</th></tr>";
            while ($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>";
                echo "<td>" . $row['Field'] . "</td>";
                echo "<td>" . $row['Type'] . "</td>";
                echo "<td>" . $row['Null'] . "</td>";
                echo "<td>" . $row['Key'] . "</td>";
                echo "<td>" . $row['Default'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>테이블 구조 확인 실패: " . $e->getMessage() . "</p>";
        }
    }
}

echo "<hr>";
echo "<p><a href='list.php'>건의사항 목록으로 이동</a></p>";
?> 