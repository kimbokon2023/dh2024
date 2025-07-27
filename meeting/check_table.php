<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");

$pdo = db_connect();

echo "<h2>회의록 시스템 데이터베이스 테이블 확인</h2>";

// meeting 테이블 확인
try {
    $sql = "SHOW TABLES LIKE 'meeting'";
    $stmh = $pdo->query($sql);
    $meeting_exists = $stmh->rowCount() > 0;
    
    if ($meeting_exists) {
        echo "<p style='color: green;'>✅ meeting 테이블이 존재합니다.</p>";
    } else {
        echo "<p style='color: red;'>❌ meeting 테이블이 존재하지 않습니다.</p>";
        
        // 테이블 생성
        $create_meeting = "
        CREATE TABLE IF NOT EXISTS meeting (
            num INT AUTO_INCREMENT PRIMARY KEY,
            registration_date DATE NOT NULL,
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
            $pdo->exec($create_meeting);
            echo "<p style='color: green;'>✅ meeting 테이블이 생성되었습니다.</p>";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>❌ meeting 테이블 생성 실패: " . $e->getMessage() . "</p>";
        }
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ meeting 테이블 확인 실패: " . $e->getMessage() . "</p>";
}

// meeting_ripple 테이블 확인
try {
    $sql = "SHOW TABLES LIKE 'meeting_ripple'";
    $stmh = $pdo->query($sql);
    $ripple_exists = $stmh->rowCount() > 0;
    
    if ($ripple_exists) {
        echo "<p style='color: green;'>✅ meeting_ripple 테이블이 존재합니다.</p>";
    } else {
        echo "<p style='color: red;'>❌ meeting_ripple 테이블이 존재하지 않습니다.</p>";
        
        // 테이블 생성
        $create_ripple = "
        CREATE TABLE IF NOT EXISTS meeting_ripple (
            num INT AUTO_INCREMENT PRIMARY KEY,
            parent INT NOT NULL,
            id VARCHAR(20) NOT NULL,
            name VARCHAR(20) NOT NULL,
            nick VARCHAR(20),
            content TEXT NOT NULL,
            regist_day DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (parent) REFERENCES meeting(num) ON DELETE CASCADE
        )";
        
        try {
            $pdo->exec($create_ripple);
            echo "<p style='color: green;'>✅ meeting_ripple 테이블이 생성되었습니다.</p>";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>❌ meeting_ripple 테이블 생성 실패: " . $e->getMessage() . "</p>";
        }
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ meeting_ripple 테이블 확인 실패: " . $e->getMessage() . "</p>";
}

// 테이블 구조 확인
if ($meeting_exists || $ripple_exists) {
    echo "<h3>테이블 구조 확인</h3>";
    
    if ($meeting_exists) {
        echo "<h4>meeting 테이블 구조:</h4>";
        try {
            $sql = "DESCRIBE meeting";
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
echo "<p><a href='list.php'>회의록 목록으로 이동</a></p>";
?> 