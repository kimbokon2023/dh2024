<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/session.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/lib/mydb.php');

$pdo = db_connect();
$response = ['result' => 'error', 'message' => ''];

try {
    $mode = $_POST['mode'] ?? '';
    $num = $_POST['num'] ?? '';
    $tablename = $_POST['tablename'] ?? 'employee_tasks';
    
    // 기본 정보
    $task_date = $_POST['task_date'] ?? date('Y-m-d');
    $employee_name = $_POST['employee_name'] ?? '';
    $department = $_POST['department'] ?? '';
    $memo = $_POST['memo'] ?? '';
    
    // 세션에서 사용자 정보 가져오기 (직원명이나 부서가 비어있을 경우)
    if (empty($employee_name)) {
        $employee_name = $_SESSION['user_name'] ?? $_SESSION['name'] ?? $_SESSION['username'] ?? '';
    }
    if (empty($department)) {
        $department = $_SESSION['part'] ?? $_SESSION['department'] ?? $_SESSION['dept'] ?? $_SESSION['user_department'] ?? '';
    }
    
    // JSON 데이터
    $tasks_json = $_POST['tasks_json'] ?? '[]';
    
    // 추적 시스템 관련 필드
    $tracking_enabled = $_POST['tracking_enabled'] ?? 'Y';
    $last_tracking_date = $_POST['last_tracking_date'] ?? null;
    $tracking_count = $_POST['tracking_count'] ?? 0;
    
    if ($mode === 'insert' || $mode === 'copy') {
        // 새 할일 등록
        $sql = "INSERT INTO {$DB}.employee_tasks 
                (task_date, employee_name, department, memo, tasks, tracking_enabled, last_tracking_date, tracking_count, created_at, created_by) 
                VALUES (:task_date, :employee_name, :department, :memo, :tasks, :tracking_enabled, :last_tracking_date, :tracking_count, NOW(), :created_by)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':task_date', $task_date, PDO::PARAM_STR);
        $stmt->bindParam(':employee_name', $employee_name, PDO::PARAM_STR);
        $stmt->bindParam(':department', $department, PDO::PARAM_STR);
        $stmt->bindParam(':memo', $memo, PDO::PARAM_STR);
        $stmt->bindParam(':tasks', $tasks_json, PDO::PARAM_STR);
        $stmt->bindParam(':tracking_enabled', $tracking_enabled, PDO::PARAM_STR);
        $stmt->bindParam(':last_tracking_date', $last_tracking_date, PDO::PARAM_STR);
        $stmt->bindParam(':tracking_count', $tracking_count, PDO::PARAM_INT);
        $stmt->bindParam(':created_by', $user_name, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            $new_num = $pdo->lastInsertId();
            
            // 추적 시스템: 완료된 추적 할일을 원본 데이터에 반영
            $tasks = json_decode($tasks_json, true) ?? [];
            updateOriginalTasksFromTracking($pdo, $tasks, $employee_name);
            
            $response = [
                'result' => 'success',
                'message' => '할일이 성공적으로 등록되었습니다.',
                'num' => $new_num
            ];
        } else {
            $response = [
                'result' => 'error',
                'message' => '할일 등록 중 오류가 발생했습니다.'
            ];
        }
        
    } elseif ($mode === 'modify') {
        // 할일 수정
        $sql = "UPDATE {$DB}.employee_tasks 
                SET task_date = :task_date, 
                    employee_name = :employee_name, 
                    department = :department, 
                    memo = :memo, 
                    tasks = :tasks, 
                    tracking_enabled = :tracking_enabled,
                    last_tracking_date = :last_tracking_date,
                    tracking_count = :tracking_count,
                    updated_at = NOW(), 
                    updated_by = :updated_by 
                WHERE num = :num";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':task_date', $task_date, PDO::PARAM_STR);
        $stmt->bindParam(':employee_name', $employee_name, PDO::PARAM_STR);
        $stmt->bindParam(':department', $department, PDO::PARAM_STR);
        $stmt->bindParam(':memo', $memo, PDO::PARAM_STR);
        $stmt->bindParam(':tasks', $tasks_json, PDO::PARAM_STR);
        $stmt->bindParam(':tracking_enabled', $tracking_enabled, PDO::PARAM_STR);
        $stmt->bindParam(':last_tracking_date', $last_tracking_date, PDO::PARAM_STR);
        $stmt->bindParam(':tracking_count', $tracking_count, PDO::PARAM_INT);
        $stmt->bindParam(':updated_by', $user_name, PDO::PARAM_STR);
        $stmt->bindParam(':num', $num, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            // 추적 시스템: 완료된 추적 할일을 원본 데이터에 반영
            $tasks = json_decode($tasks_json, true) ?? [];
            updateOriginalTasksFromTracking($pdo, $tasks, $employee_name);
            
            $response = [
                'result' => 'success',
                'message' => '할일이 성공적으로 수정되었습니다.',
                'num' => $num
            ];
        } else {
            $response = [
                'result' => 'error',
                'message' => '할일 수정 중 오류가 발생했습니다.'
            ];
        }
        
    } elseif ($mode === 'delete') {
        // 할일 삭제 (논리 삭제)
        $sql = "UPDATE {$DB}.employee_tasks 
                SET is_deleted = 'Y', 
                    deleted_at = NOW(), 
                    deleted_by = :deleted_by 
                WHERE num = :num";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':deleted_by', $user_name, PDO::PARAM_STR);
        $stmt->bindParam(':num', $num, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $response = [
                'result' => 'success',
                'message' => '할일이 성공적으로 삭제되었습니다.'
            ];
        } else {
            $response = [
                'result' => 'error',
                'message' => '할일 삭제 중 오류가 발생했습니다.'
            ];
        }
        
    } else {
        $response = [
            'result' => 'error',
            'message' => '잘못된 모드입니다.'
        ];
    }
    
} catch (PDOException $e) {
    $response = [
        'result' => 'error',
        'message' => '데이터베이스 오류: ' . $e->getMessage()
    ];
} catch (Exception $e) {
    $response = [
        'result' => 'error',
        'message' => '오류: ' . $e->getMessage()
    ];
}

// 추적 시스템: 완료된 추적 할일을 원본 데이터에 반영하는 함수
function updateOriginalTasksFromTracking($pdo, $tasks, $employee_name) {
    try {
        foreach ($tasks as $task) {
            if (($task['is_pending'] ?? false) && ($task['is_completed'] ?? false)) {
                $unique_id = $task['unique_id'] ?? '';
                if (!empty($unique_id)) {
                    $unique_parts = explode('_', $unique_id);
                    if (count($unique_parts) >= 3) {
                        $original_date = $unique_parts[0];
                        $original_num = $unique_parts[1];
                        $original_index = $unique_parts[2];
                        
                        // 원본 데이터 조회 및 업데이트
                        $sql = "SELECT * FROM employee_tasks WHERE num = ? AND task_date = ?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindValue(1, $original_num, PDO::PARAM_INT);
                        $stmt->bindValue(2, $original_date, PDO::PARAM_STR);
                        $stmt->execute();
                        $original_row = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($original_row) {
                            $original_tasks = json_decode($original_row['tasks'], true) ?? [];
                            
                            // 해당 인덱스의 할일 완료 상태 업데이트
                            if (isset($original_tasks[$original_index])) {
                                $original_tasks[$original_index]['is_completed'] = true;
                                $original_tasks[$original_index]['completion_date'] = $task['completion_date'] ?? date('Y-m-d');
                                
                                // 원본 데이터 업데이트
                                $update_sql = "UPDATE employee_tasks SET tasks = ?, updated_at = NOW(), updated_by = ? WHERE num = ?";
                                $update_stmt = $pdo->prepare($update_sql);
                                $update_stmt->bindValue(1, json_encode($original_tasks), PDO::PARAM_STR);
                                $update_stmt->bindValue(2, $employee_name, PDO::PARAM_STR);
                                $update_stmt->bindValue(3, $original_num, PDO::PARAM_INT);
                                $update_stmt->execute();
                                
                                // 추적 이력 테이블에 기록
                                insertTrackingHistory($pdo, $original_num, $original_date, $original_index, $task, $employee_name);
                                
                                // 완료 이력 테이블에 기록
                                insertCompletionHistory($pdo, $original_num, $original_date, $original_index, $task, $employee_name);
                            }
                        }
                    }
                }
            }
        }
    } catch (Exception $e) {
        error_log("Tracking update error: " . $e->getMessage());
    }
}

// 추적 이력 테이블에 기록하는 함수
function insertTrackingHistory($pdo, $original_num, $original_date, $original_index, $task, $employee_name) {
    try {
        $sql = "INSERT INTO employee_task_tracking 
                (original_task_num, original_task_date, original_index, tracked_date, employee_name, task_content, is_completed, completion_date, elapsed_days) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(1, $original_num, PDO::PARAM_INT);
        $stmt->bindValue(2, $original_date, PDO::PARAM_STR);
        $stmt->bindValue(3, $original_index, PDO::PARAM_INT);
        $stmt->bindValue(4, date('Y-m-d'), PDO::PARAM_STR);
        $stmt->bindValue(5, $employee_name, PDO::PARAM_STR);
        $stmt->bindValue(6, $task['task_content'], PDO::PARAM_STR);
        $stmt->bindValue(7, $task['is_completed'] ? 'Y' : 'N', PDO::PARAM_STR);
        $stmt->bindValue(8, $task['completion_date'] ?? null, PDO::PARAM_STR);
        $stmt->bindValue(9, $task['elapsed_days'] ?? 0, PDO::PARAM_INT);
        $stmt->execute();
    } catch (Exception $e) {
        error_log("Tracking history insert error: " . $e->getMessage());
    }
}

// 완료 이력 테이블에 기록하는 함수
function insertCompletionHistory($pdo, $original_num, $original_date, $original_index, $task, $employee_name) {
    try {
        $sql = "INSERT INTO employee_task_completions 
                (task_num, task_date, employee_name, task_index, task_content, completion_date, elapsed_days, is_tracked_completion, original_task_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(1, $original_num, PDO::PARAM_INT);
        $stmt->bindValue(2, $original_date, PDO::PARAM_STR);
        $stmt->bindValue(3, $employee_name, PDO::PARAM_STR);
        $stmt->bindValue(4, $original_index, PDO::PARAM_INT);
        $stmt->bindValue(5, $task['task_content'], PDO::PARAM_STR);
        $stmt->bindValue(6, $task['completion_date'] ?? date('Y-m-d'), PDO::PARAM_STR);
        $stmt->bindValue(7, $task['elapsed_days'] ?? 0, PDO::PARAM_INT);
        $stmt->bindValue(8, 'Y', PDO::PARAM_STR);
        $stmt->bindValue(9, $original_date, PDO::PARAM_STR);
        $stmt->execute();
    } catch (Exception $e) {
        error_log("Completion history insert error: " . $e->getMessage());
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?> 