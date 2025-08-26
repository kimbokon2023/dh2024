<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");

$pdo = db_connect();

$mode = $_POST['mode'] ?? '';
$response = ['result' => 'error', 'message' => ''];

try {
    if ($mode === 'insert' || $mode === 'modify') {
        $tablename = $_POST['tablename'] ?? 'employee_tasks';
        $task_date = $_POST['task_date'] ?? '';
        $employee_name = $_POST['employee_name'] ?? '';
        $department = $_POST['department'] ?? '';
        $memo = $_POST['memo'] ?? '';
        $tasks_json = $_POST['tasks_json'] ?? '[]';
        
        // JSON 데이터 파싱
        $tasks = json_decode($tasks_json, true) ?? [];

        // date_key 생성 유틸리티 (원본 날짜 + 안정적 난수)
        $generateDateKey = function($originalDate, $content, $index) use ($task_date) {
            $seed = $originalDate . '|' . $content . '|' . $index;
            return ($originalDate ?: $task_date) . '_' . substr(md5($seed), 0, 10);
        };
        
        // 추적된 할일의 완료 상태를 원본 데이터에 반영
        $tracked_completions = [];
        $debug_info = []; // 디버그 정보 저장용
        
        foreach ($tasks as $index => $task) {
            // date_key 보강 (없으면 생성)
            if (empty($task['date_key'])) {
                $tasks[$index]['date_key'] = $generateDateKey($task['original_date'] ?? $task_date, $task['task_content'] ?? '', $index);
            }
            $debug_info[] = [
                'task_content' => $task['task_content'] ?? '',
                'is_completed' => $task['is_completed'] ?? false,
                'is_pending' => $task['is_pending'] ?? false,
                'original_date' => $task['original_date'] ?? '',
                'completion_date' => $task['completion_date'] ?? '',
                'date_key' => $tasks[$index]['date_key']
            ];
            
            if (!empty($task['is_completed'])) {
                $tracked_completions[] = [
                    'original_date' => $task['original_date'] ?? '',
                    'task_content' => $task['task_content'] ?? '',
                    'date_key' => $tasks[$index]['date_key'] ?? '',
                    'completion_date' => $task['completion_date'] ?? date('Y-m-d'),
                    'current_task_date' => $task_date
                ];
            }
        }
        
        // 디버그: 추적 대상 할일 정보
        $debug_info[] = ['message' => '추적 대상 할일 수: ' . count($tracked_completions)];
        $debug_info[] = ['tracked_completions' => $tracked_completions];
        
        // 원본 데이터 업데이트 - date_key 기반으로 일괄 완료 처리
        foreach ($tracked_completions as $completion) {
            $debug_info[] = ['processing_completion' => $completion];
            
            if (!empty($completion['date_key'])) {
                // date_key 기준으로 해당 직원의 모든 행을 조회하고, 같은 date_key를 가진 항목을 완료 처리
                $sql = "SELECT * FROM {$DB}.{$tablename} WHERE employee_name = ? AND (is_deleted = 'N' OR is_deleted IS NULL) AND task_date >= ?";
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(1, $employee_name, PDO::PARAM_STR);
                $stmt->bindValue(2, $completion['original_date'] ?: '0000-00-00', PDO::PARAM_STR);
                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($rows as $row) {
                    $original_tasks = json_decode($row['tasks'], true) ?? [];
                    $row_updated = false;
                    foreach ($original_tasks as $i => &$original_task) {
                        // date_key 없으면 생성해 비교 안정성 확보
                        $taskDateKey = isset($original_task['date_key']) ? $original_task['date_key'] : $generateDateKey($original_task['original_date'] ?? $row['task_date'], $original_task['task_content'] ?? '', $i);
                        if ($taskDateKey === $completion['date_key']) {
                            $original_task['date_key'] = $taskDateKey; // 누락시 보강
                            $original_task['is_completed'] = true;
                            $original_task['completion_date'] = $completion['current_task_date'];
                            $row_updated = true;
                        }
                    }
                    unset($original_task);
                    if ($row_updated) {
                        $update_sql = "UPDATE {$DB}.{$tablename} SET tasks = ?, updated_at = NOW(), updated_by = ? WHERE num = ?";
                        $update_stmt = $pdo->prepare($update_sql);
                        $update_stmt->bindValue(1, json_encode($original_tasks), PDO::PARAM_STR);
                        $update_stmt->bindValue(2, $employee_name, PDO::PARAM_STR);
                        $update_stmt->bindValue(3, $row['num'], PDO::PARAM_INT);
                        $update_stmt->execute();
                    }
                }
            } else {
                $debug_info[] = ['completion_skipped' => 'date_key가 비어있음'];
            }
        }
        
        // 현재 할일 데이터 구성: 완료된 추적 항목도 오늘 레코드에 남긴다(증빙 목적)
        $current_tasks = [];
        foreach ($tasks as $task) {
            // 추적 관련 임시 필드는 저장 시 제거 (elapsed_days는 유지)
            unset($task['unique_id']);
            unset($task['is_pending']);
            unset($task['original_task_date']);
            unset($task['original_task_num']);
            unset($task['original_index']);
            
            $current_tasks[] = $task;
        }
        
        if ($mode === 'insert') {
            $sql = "INSERT INTO {$DB}.{$tablename} (task_date, employee_name, department, memo, tasks, created_by) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(1, $task_date, PDO::PARAM_STR);
            $stmt->bindValue(2, $employee_name, PDO::PARAM_STR);
            $stmt->bindValue(3, $department, PDO::PARAM_STR);
            $stmt->bindValue(4, $memo, PDO::PARAM_STR);
            $stmt->bindValue(5, json_encode($current_tasks), PDO::PARAM_STR);
            $stmt->bindValue(6, $employee_name, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                $response = ['result' => 'success', 'message' => '할일이 성공적으로 등록되었습니다.', 'debug_info' => $debug_info];
            } else {
                $response = ['result' => 'error', 'message' => '할일 등록에 실패했습니다.', 'debug_info' => $debug_info];
            }
        } else {
            $num = $_POST['num'] ?? '';
            $sql = "UPDATE {$DB}.{$tablename} SET task_date = ?, employee_name = ?, department = ?, memo = ?, tasks = ?, updated_at = NOW(), updated_by = ? WHERE num = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(1, $task_date, PDO::PARAM_STR);
            $stmt->bindValue(2, $employee_name, PDO::PARAM_STR);
            $stmt->bindValue(3, $department, PDO::PARAM_STR);
            $stmt->bindValue(4, $memo, PDO::PARAM_STR);
            $stmt->bindValue(5, json_encode($current_tasks), PDO::PARAM_STR);
            $stmt->bindValue(6, $employee_name, PDO::PARAM_STR);
            $stmt->bindValue(7, $num, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $response = ['result' => 'success', 'message' => '할일이 성공적으로 수정되었습니다.', 'debug_info' => $debug_info];
            } else {
                $response = ['result' => 'error', 'message' => '할일 수정에 실패했습니다.', 'debug_info' => $debug_info];
            }
        }
    } elseif ($mode === 'delete') {
        $num = $_POST['num'] ?? '';
        $tablename = $_POST['tablename'] ?? 'employee_tasks';
        
        $sql = "UPDATE {$DB}.{$tablename} SET is_deleted = 'Y', deleted_at = NOW(), deleted_by = ? WHERE num = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(1, $_SESSION['user_name'] ?? '', PDO::PARAM_STR);
        $stmt->bindValue(2, $num, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $response = ['result' => 'success', 'message' => '할일이 성공적으로 삭제되었습니다.'];
        } else {
            $response = ['result' => 'error', 'message' => '할일 삭제에 실패했습니다.'];
        }
    } else {
        $response = ['result' => 'error', 'message' => '잘못된 요청입니다.'];
    }
} catch (PDOException $Exception) {
    $response = ['result' => 'error', 'message' => '데이터베이스 오류: ' . $Exception->getMessage()];
} catch (Exception $Exception) {
    $response = ['result' => 'error', 'message' => '오류: ' . $Exception->getMessage()];
}

header('Content-Type: application/json');
echo json_encode($response);
?> 