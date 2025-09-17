<?php
// workprocess 테이블의 regist_day 컬럼을 CHAR(20)에서 DATE로 안전하게 변환
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");

// 관리자 권한 확인 (레벨 1-2만 실행 가능)
if (!isset($_SESSION["level"]) || intval($_SESSION["level"]) > 2) {
    die("권한이 없습니다. 관리자만 실행 가능합니다.");
}

$pdo = db_connect();

echo "<h3>workprocess 테이블 regist_day 컬럼 변환</h3>";

try {
    $pdo->beginTransaction(); 
    
    // 1단계: 임시 컬럼 추가
    echo "<p>1단계: 임시 DATE 컬럼 추가...</p>";
    $sql1 = "ALTER TABLE {$DB}.workprocess ADD COLUMN regist_day_new DATE NULL AFTER regist_day";
    $pdo->exec($sql1);
    echo "✓ 임시 컬럼 regist_day_new 추가 완료<br>";
    
    // 2단계: 기존 데이터를 새 컬럼으로 변환하여 복사
    echo "<p>2단계: 기존 데이터를 DATE 형식으로 변환...</p>";
    $sql2 = "UPDATE {$DB}.workprocess 
             SET regist_day_new = DATE(regist_day) 
             WHERE regist_day IS NOT NULL AND regist_day != ''";
    $result = $pdo->exec($sql2);
    echo "✓ {$result}개 레코드 변환 완료<br>";
    
    // 3단계: 변환 결과 확인
    echo "<p>3단계: 변환 결과 확인...</p>";
    $sql3 = "SELECT 
                COUNT(*) as total,
                COUNT(regist_day_new) as converted,
                COUNT(*) - COUNT(regist_day_new) as null_count
             FROM {$DB}.workprocess";
    $stmt = $pdo->prepare($sql3);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "전체 레코드: {$result['total']}<br>";
    echo "변환된 레코드: {$result['converted']}<br>";
    echo "NULL 레코드: {$result['null_count']}<br>";
    
    // 4단계: 샘플 데이터 확인
    echo "<p>4단계: 변환된 샘플 데이터 확인...</p>";
    $sql4 = "SELECT num, regist_day as original, regist_day_new as converted 
             FROM {$DB}.workprocess 
             ORDER BY num DESC 
             LIMIT 5";
    $stmt = $pdo->prepare($sql4);
    $stmt->execute();
    $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>번호</th><th>원본 (regist_day)</th><th>변환후 (regist_day_new)</th></tr>";
    foreach($samples as $sample) {
        echo "<tr>";
        echo "<td>{$sample['num']}</td>";
        echo "<td>{$sample['original']}</td>";
        echo "<td>{$sample['converted']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p><strong>변환이 올바른지 확인하세요.</strong></p>";
    echo "<p><a href='?step=2'>변환이 올바르면 여기를 클릭하여 다음 단계 진행</a></p>";
    echo "<p><a href='?rollback=1'>문제가 있으면 여기를 클릭하여 롤백</a></p>";
    
    $pdo->commit();
    
} catch (Exception $e) {
    $pdo->rollback();
    echo "<p style='color: red;'>오류 발생: " . $e->getMessage() . "</p>";
}

// 2단계: 컬럼 교체
if (isset($_GET['step']) && $_GET['step'] == '2') {
    try {
        $pdo->beginTransaction();
        
        echo "<h4>5단계: 기존 컬럼 삭제 및 새 컬럼 이름 변경</h4>";
        
        // 기존 regist_day 컬럼 삭제
        $sql5 = "ALTER TABLE {$DB}.workprocess DROP COLUMN regist_day";
        $pdo->exec($sql5);
        echo "✓ 기존 regist_day 컬럼 삭제<br>";
        
        // 새 컬럼을 regist_day로 이름 변경
        $sql6 = "ALTER TABLE {$DB}.workprocess CHANGE regist_day_new regist_day DATE NULL";
        $pdo->exec($sql6);
        echo "✓ 새 컬럼을 regist_day로 이름 변경<br>";
        
        echo "<h4>변환 완료!</h4>";
        echo "<p>workprocess 테이블의 regist_day 컬럼이 성공적으로 DATE 타입으로 변환되었습니다.</p>";
        
        $pdo->commit();
        
    } catch (Exception $e) {
        $pdo->rollback();
        echo "<p style='color: red;'>오류 발생: " . $e->getMessage() . "</p>";
    }
}

// 롤백
if (isset($_GET['rollback']) && $_GET['rollback'] == '1') {
    try {
        $pdo->beginTransaction();
        
        echo "<h4>롤백: 임시 컬럼 삭제</h4>";
        $sql_rollback = "ALTER TABLE {$DB}.workprocess DROP COLUMN regist_day_new";
        $pdo->exec($sql_rollback);
        echo "✓ 임시 컬럼 삭제 완료<br>";
        
        $pdo->commit();
        
    } catch (Exception $e) {
        $pdo->rollback();
        echo "<p style='color: red;'>롤백 오류: " . $e->getMessage() . "</p>";
    }
}
?>