<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");

$pdo = db_connect();

// 테스트 파라미터
$test_employee = $_GET['employee'] ?? $_SESSION['user_name'];
$test_date = $_GET['date'] ?? date('Y-m-d');

echo "<!DOCTYPE html>
<html>
<head>
    <title>업무요청사항 조회 테스트</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
        .success { background: #d4edda; }
        .info { background: #d1ecf1; }
        .warning { background: #fff3cd; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        th { background: #f8f9fa; }
    </style>
</head>
<body>
    <h1>업무요청사항 조회 테스트</h1>
    
    <div class='test-section info'>
        <h3>테스트 정보</h3>
        <p><strong>담당자:</strong> {$test_employee}</p>
        <p><strong>기준 날짜:</strong> {$test_date}</p>
    </div>";

// 1. 기존 방식 (regist_day = ?) 테스트
echo "<div class='test-section warning'>
    <h3>1. 기존 방식 (regist_day = '{$test_date}')</h3>";

$old_sql = "SELECT num, subject, first_writer, chargedPerson, regist_day, dueDate, doneDate 
            FROM {$DB}.workprocess 
            WHERE chargedPerson = ? 
            AND regist_day = ?
            AND (doneDate IS NULL OR YEAR(doneDate) = 0)
            ORDER BY num DESC";

$stmt = $pdo->prepare($old_sql);
$stmt->execute([$test_employee, $test_date]);
$old_results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<p>결과: " . count($old_results) . "건</p>";
if (count($old_results) > 0) {
    echo "<table>
        <tr><th>번호</th><th>제목</th><th>요청자</th><th>등록일</th><th>처리기한</th></tr>";
    foreach ($old_results as $row) {
        echo "<tr>
            <td>{$row['num']}</td>
            <td>{$row['subject']}</td>
            <td>{$row['first_writer']}</td>
            <td>{$row['regist_day']}</td>
            <td>{$row['dueDate']}</td>
        </tr>";
    }
    echo "</table>";
}
echo "</div>";

// 2. 개선된 방식 (regist_day <= ?) 테스트
echo "<div class='test-section success'>
    <h3>2. 개선된 방식 (regist_day <= '{$test_date}')</h3>";

$new_sql = "SELECT num, subject, first_writer, chargedPerson, regist_day, dueDate, doneDate 
            FROM {$DB}.workprocess 
            WHERE chargedPerson = ? 
            AND regist_day <= ?
            AND (doneDate IS NULL OR YEAR(doneDate) = 0)
            ORDER BY num DESC";

$stmt = $pdo->prepare($new_sql);
$stmt->execute([$test_employee, $test_date]);
$new_results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<p>결과: " . count($new_results) . "건</p>";
if (count($new_results) > 0) {
    echo "<table>
        <tr><th>번호</th><th>제목</th><th>요청자</th><th>등록일</th><th>처리기한</th><th>경과일</th></tr>";
    foreach ($new_results as $row) {
        $regist_date = new DateTime($row['regist_day']);
        $current_date = new DateTime($test_date);
        $elapsed_days = $current_date->diff($regist_date)->days;
        
        echo "<tr>
            <td>{$row['num']}</td>
            <td>{$row['subject']}</td>
            <td>{$row['first_writer']}</td>
            <td>{$row['regist_day']}</td>
            <td>{$row['dueDate']}</td>
            <td><span style='color: " . ($elapsed_days > 0 ? 'red' : 'green') . "'>{$elapsed_days}일</span></td>
        </tr>";
    }
    echo "</table>";
}
echo "</div>";

// 3. 차이점 분석
$diff_count = count($new_results) - count($old_results);
if ($diff_count > 0) {
    echo "<div class='test-section success'>
        <h3>3. 개선 효과</h3>
        <p><strong>추가로 표시되는 업무요청건:</strong> {$diff_count}건</p>
        <p>이제 등록일 이후 완료되지 않은 모든 업무요청건이 계속 표시됩니다.</p>
    </div>";
}

echo "
    <div class='test-section'>
        <h3>테스트 방법</h3>
        <p>URL 파라미터로 다른 날짜 테스트: ?date=2025-01-01</p>
        <p>URL 파라미터로 다른 사용자 테스트: ?employee=홍길동</p>
    </div>
</body>
</html>";
?>