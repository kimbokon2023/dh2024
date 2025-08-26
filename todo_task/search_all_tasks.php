<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

// 디버깅을 위한 로그
error_log("Search request received: " . print_r($_POST, true));

$keyword = isset($_POST['keyword']) ? $_POST['keyword'] : '';

// 할일 전체에서 검색 (기간 무관)
try {
    // 먼저 모든 할일 데이터를 가져옴
    $sql = "SELECT num, task_date, employee_name, department, memo, tasks, created_at, created_by, updated_at, updated_by 
            FROM " . $DB . ".employee_tasks 
            WHERE (is_deleted IS NULL OR is_deleted = 'N') 
            ORDER BY task_date DESC, created_at DESC";
    
    $stmh = $pdo->query($sql);
    
    $results = array();
    while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
        $match_found = false;
        
        // 기본 필드에서 검색
        if (stripos($row['employee_name'], $keyword) !== false || 
            stripos($row['task_date'], $keyword) !== false ||
            stripos($row['department'], $keyword) !== false ||
            stripos($row['memo'], $keyword) !== false) {
            $match_found = true;
        }
        
        // tasks JSON 데이터에서 검색
        if (!empty($row['tasks'])) {
            $tasks = json_decode($row['tasks'], true);
            if (is_array($tasks)) {
                $filtered_tasks = array();
                
                foreach ($tasks as $task) {
                    $content = isset($task['task_content']) ? $task['task_content'] : '';                    
                    
                    // JSON 내부의 content나 title에서 검색어 찾기
                    if (stripos($content, $keyword) !== false ) {
                        $filtered_tasks[] = $task;
                        $match_found = true;
                    }
                }
                
                // 매칭되는 할일이 있으면 필터링된 tasks로 업데이트
                if (!empty($filtered_tasks)) {
                    $row['tasks'] = json_encode($filtered_tasks, JSON_UNESCAPED_UNICODE);
                }
            }
        }
        
        // 검색어와 매칭되는 경우만 결과에 포함
        if ($match_found) {
            array_push($results, $row);
        }
    }
    
    echo json_encode($results, JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $Exception) {
    print "오류: ".$Exception->getMessage();
}
?>
