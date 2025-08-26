<?php
header('Content-Type: application/json; charset=utf-8');
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

try {
    // POST 데이터 확인
    if (!isset($_POST['num']) || empty($_POST['num'])) {
        throw new Exception('할일 번호가 필요합니다.');
    }
    
    $taskNum = intval($_POST['num']);
    
    // 할일 상세 정보 조회
    $sql = "SELECT t.*, e.name as employee_name 
            FROM todo_tasks t 
            LEFT JOIN member e ON t.employee_id = e.id 
            WHERE t.num = :num";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':num', $taskNum, PDO::PARAM_INT);
    $stmt->execute();
    
    $task = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$task) {
        throw new Exception('해당 할일을 찾을 수 없습니다.');
    }
    
    // 할일 목록 파싱 (JSON 형태로 저장된 경우)
    if (!empty($task['tasks'])) {
        $tasks = json_decode($task['tasks'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // JSON 파싱 실패 시 기본 형식으로 변환
            $task['tasks'] = json_encode([['content' => $task['tasks'], 'is_completed' => 0]]);
        }
    } else {
        $task['tasks'] = json_encode([]);
    }
    
    // 날짜 형식 변환
    if (isset($task['task_date'])) {
        $task['task_date'] = date('Y-m-d', strtotime($task['task_date']));
    }
    
    // 응답 데이터 정리
    $response = [
        'num' => $task['num'],
        'employee_name' => $task['employee_name'] ?? '알 수 없음',
        'task_date' => $task['task_date'] ?? '',
        'tasks' => $task['tasks'],
        'status' => $task['status'] ?? 'pending',
        'created_at' => $task['created_at'] ?? '',
        'updated_at' => $task['updated_at'] ?? ''
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => '데이터베이스 오류가 발생했습니다.'
    ], JSON_UNESCAPED_UNICODE);
}
?>
