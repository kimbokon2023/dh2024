<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

$mode = isset($_POST['mode']) ? $_POST['mode'] : '';
$num = isset($_POST['num']) ? $_POST['num'] : '';
$plan_month = isset($_POST['plan_month']) ? $_POST['plan_month'] . '-01' : date("Y-m-01");
$seldate = isset($_POST['seldate']) ? $_POST['seldate'] :  date("Y-m-d", time());
$debug = isset($_POST['debug']) ? $_POST['debug'] : '';

// 디버깅을 위한 로그
error_log("fetch_modal.php - mode: $mode, num: $num, seldate: $seldate, plan_month: $plan_month, debug: $debug");

// echo $seldate; 

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php"); 
$pdo = db_connect();
 
// employee_tasks 테이블 관련 변수 초기화 
$tablename = 'employee_tasks';
$task_date = $seldate;
$employee_name = $_SESSION['user_name'] ?? $_SESSION['name'] ?? $_SESSION['username'] ?? '';
$department = $_SESSION['part'] ?? $_SESSION['department'] ?? $_SESSION['dept'] ?? $_SESSION['user_department'] ?? '';
$memo = '';
$tasks = [];
$tasksCount = 0;  

// 이전 날짜의 미완료 할일을 가져오는 함수
function getPendingTasks($pdo, $employee_name, $current_date) { 
    $pending_tasks = [];
    $task_content_map = []; // 중복 체크를 위한 맵
    
    try {
        // 현재 날짜보다 이전의 모든 할일을 조회
        $sql = "SELECT * FROM employee_tasks 
                WHERE employee_name = ? 
                AND task_date < ? 
                AND (is_deleted = 'N' OR is_deleted IS NULL)
                ORDER BY task_date ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(1, $employee_name, PDO::PARAM_STR); 
        $stmt->bindValue(2, $current_date, PDO::PARAM_STR);
        $stmt->execute(); 
         
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($results as $row) {
            $task_items = json_decode($row['tasks'], true) ?? [];
            
            foreach ($task_items as $index => $item) {
                // 완료되지 않은 할일이거나 completion_date가 없는 것만 가져오기
                if ((empty($item['is_completed']) || empty($item['completion_date'])) && !empty($item['task_content'])) {
                    $task_content = trim($item['task_content']);
                    // date_key 준비 (원본에 없을 수 있으므로 안정적인 해시로 임시 생성)
                    $existing_date_key = isset($item['date_key']) ? trim($item['date_key']) : '';
                    if ($existing_date_key === '') {
                        $existing_date_key = $row['task_date'] . '_' . substr(md5($row['num'] . '_' . $index . '_' . $task_content), 0, 10);
                    }
                    
                    // 중복 체크: 동일한 할일 내용이 이미 있는지 확인
                    if (isset($task_content_map[$task_content])) {
                        // 이미 존재하는 경우, 더 오래된 날짜의 할일을 우선적으로 유지
                        $existing_task = $task_content_map[$task_content];
                        $existing_date = new DateTime($existing_task['original_date']);
                        $current_item_date = new DateTime($item['original_date'] ?? $row['task_date']);
                        
                        // 현재 아이템이 더 오래된 경우에만 교체
                        if ($current_item_date < $existing_date) {
                            // 기존 항목 제거
                            $pending_tasks = array_filter($pending_tasks, function($task) use ($existing_task) {
                                return $task['unique_id'] !== $existing_task['unique_id'];
                            });
                            
                            // 새로운 항목 추가
                            $unique_id = $row['task_date'] . '_' . $row['num'] . '_' . $index;
                            $original_date = $item['original_date'] ?? $row['task_date'];
                            $original_date_obj = new DateTime($original_date);
                            $current_date_obj = new DateTime($current_date);
                            
                            // 경과일 계산
                            if ($original_date_obj > $current_date_obj) {
                                $elapsed_days = 0;
                            } else if (empty($item['is_completed']) || $item['is_completed'] === false) {
                                $elapsed_days = $current_date_obj->diff($original_date_obj)->days;
                            } else {
                                $completion_reference_date = !empty($item['completion_date']) ? $item['completion_date'] : $row['task_date'];
                                $completion_date_obj = new DateTime($completion_reference_date);
                                $elapsed_days = $completion_date_obj->diff($original_date_obj)->days;
                            }
                            
                            $new_task = [
                                'unique_id' => $unique_id,
                                'original_task_date' => $row['task_date'],
                                'original_task_num' => $row['num'],
                                'original_index' => $index,
                                'task_content' => $task_content,
                                'is_completed' => false,
                                'completion_date' => '',
                                'original_date' => $original_date,
                                'elapsed_days' => $elapsed_days,
                                'is_pending' => true,
                                'memo' => $row['memo'] ?? '',
                                'date_key' => $existing_date_key
                            ];
                            
                            $pending_tasks[] = $new_task;
                            $task_content_map[$task_content] = $new_task;
                        }
                    } else {
                        // 새로운 할일인 경우 추가
                        $unique_id = $row['task_date'] . '_' . $row['num'] . '_' . $index;
                        $original_date = $item['original_date'] ?? $row['task_date'];
                        $original_date_obj = new DateTime($original_date);
                        $current_date_obj = new DateTime($current_date);
                        
                        // 경과일 계산
                        if ($original_date_obj > $current_date_obj) {
                            $elapsed_days = 0;
                        } else if (empty($item['is_completed']) || $item['is_completed'] === false) {
                            $elapsed_days = $current_date_obj->diff($original_date_obj)->days;
                        } else {
                            $completion_reference_date = !empty($item['completion_date']) ? $item['completion_date'] : $row['task_date'];
                            $completion_date_obj = new DateTime($completion_reference_date);
                            $elapsed_days = $completion_date_obj->diff($original_date_obj)->days;
                        }
                        
                        $new_task = [
                            'unique_id' => $unique_id,
                            'original_task_date' => $row['task_date'],
                            'original_task_num' => $row['num'],
                            'original_index' => $index,
                            'task_content' => $task_content,
                            'is_completed' => false,
                            'completion_date' => '',
                            'original_date' => $original_date,
                            'elapsed_days' => $elapsed_days,
                            'is_pending' => true,
                            'memo' => $row['memo'] ?? '',
                            'date_key' => $existing_date_key
                        ];
                        
                        $pending_tasks[] = $new_task;
                        $task_content_map[$task_content] = $new_task;
                    }
                }
            }
        }
    } catch (PDOException $Exception) {
        error_log("Pending tasks fetch error: " . $Exception->getMessage());
    }
    
    return $pending_tasks;
}

// 업무요청사항을 가져오는 함수
function getWorkProcessTasks($pdo, $employee_name, $current_date, $debug, $excluded_nums = []) {
    global $DB;
    if(empty($DB)){
        $DB = $_SESSION['DB'];  
    } 
     
    $workprocess_tasks = []; 
    
    try {  
        // 디버그용 - 기본 데이터 존재 여부 확인
        if ($debug) {
            $check_sql = "SELECT num, chargedPerson, regist_day, doneDate, subject FROM {$DB}.workprocess 
                         WHERE chargedPerson = ? ORDER BY num DESC LIMIT 5";
            $check_stmt = $pdo->prepare($check_sql);
            $check_stmt->bindValue(1, $employee_name, PDO::PARAM_STR);
            $check_stmt->execute();
            $check_results = $check_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<div style='background: yellow; padding: 10px; margin: 10px;'>";
            echo "<strong>기본 데이터 확인:</strong> {$employee_name} 담당 업무요청사항 " . count($check_results) . "건<br>";
            foreach ($check_results as $row) {
                echo "#{$row['num']}: {$row['subject']} (등록: {$row['regist_day']}, 완료: " . ($row['doneDate'] ?: 'NULL') . ")<br>";
            }
            echo "</div>";
        }
        
        // 기존 업무요청사항 번호들을 제외하는 SQL 조건 생성
        $excluded_condition = '';
        if (!empty($excluded_nums)) {
            $placeholders = str_repeat('?,', count($excluded_nums) - 1) . '?';
            $excluded_condition = " AND num NOT IN ($placeholders)";
        }
        
        $sql = "SELECT * FROM {$DB}.workprocess 
                WHERE chargedPerson = ? 
                AND regist_day <= ?
                AND (doneDate IS NULL OR YEAR(doneDate) = 0)
                {$excluded_condition}
                ORDER BY num DESC";
        
        if ($debug) {
            echo "<div style='background: lightgreen; padding: 10px; margin: 10px;'>";
            echo "<strong>업무요청사항 연동:</strong><br>";
            echo "담당자: {$employee_name}, 현재 날짜: {$current_date}<br>";
            echo "제외할 번호들: " . implode(', ', $excluded_nums) . "<br>";
            echo "<strong>변경사항:</strong> regist_day <= '{$current_date}' 조건으로 등록일 이후 모든 미완료 업무요청건 조회<br>";
            // SQL 디버그 출력 - 순차적으로 치환
            $debug_sql = $sql;
            $debug_sql = preg_replace('/\?/', "'{$employee_name}'", $debug_sql, 1); // 첫 번째 ? 치환
            $debug_sql = preg_replace('/\?/', "'{$current_date}'", $debug_sql, 1); // 두 번째 ? 치환
            // 나머지 ? (excluded_nums)는 그대로 표시
            echo "SQL: {$debug_sql}<br>";
            echo "</div>";
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(1, $employee_name, PDO::PARAM_STR);
        $stmt->bindValue(2, $current_date, PDO::PARAM_STR);
        
        // 제외할 번호들 바인딩
        $param_index = 3;
        foreach ($excluded_nums as $excluded_num) {
            $stmt->bindValue($param_index, $excluded_num, PDO::PARAM_INT);
            $param_index++;
        }
        
        $execute_result = $stmt->execute();
        
        if ($debug) {
            echo "<div style='background: orange; padding: 10px; margin: 10px;'>";
            echo "<strong>SQL 실행:</strong><br>";
            echo "Execute 결과: " . ($execute_result ? '성공' : '실패') . "<br>";
            if (!$execute_result) {
                $error = $stmt->errorInfo();
                echo "SQL 오류: " . implode(' - ', $error) . "<br>";
            }
            echo "</div>";
        }
        
        if ($execute_result) {
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($debug) {
                echo "<div style='background: lightblue; padding: 10px; margin: 10px;'>";
                echo "<strong>결과:</strong> " . count($results) . "건의 새로운 업무요청사항 발견<br>";
                foreach ($results as $i => $row) {
                    echo "#{$row['num']}: {$row['subject']} (담당: {$row['chargedPerson']}, 등록: {$row['regist_day']}, 완료: " . ($row['doneDate'] ?: 'NULL') . ")<br>";
                }
                echo "</div>";
            }
        } else {
            $results = [];
        }
        
        foreach ($results as $row) {
            $workprocess_id = 'workprocess_' . $row['num'];
            $task_content = sprintf("(업무요청)%s, (처리기한)%s (제목)%s", 
                $row['first_writer'], 
                $row['dueDate'] ?: $current_date, 
                $row['subject']
            );
            
            // 경과일 계산: 등록일부터 현재까지
            $regist_date_obj = new DateTime($row['regist_day']);
            $current_date_obj = new DateTime($current_date);
            $elapsed_days = $current_date_obj->diff($regist_date_obj)->days;
            
            $workprocess_task = [
                'unique_id' => $workprocess_id,
                'task_content' => $task_content,
                'is_completed' => false,
                'completion_date' => '',
                'original_date' => $row['regist_day'],  // 등록일을 original_date로 설정
                'elapsed_days' => $elapsed_days,
                'is_pending' => true,  // 미완료 업무요청건은 추적 대상
                'is_workprocess' => true,
                'workprocess_num' => $row['num'],
                'workprocess_data' => $row
            ];
            
            $workprocess_tasks[] = $workprocess_task;
        }
        
    } catch (PDOException $Exception) {
        if ($debug == '1' || $debug === true || $debug === 1) {
            echo "<div style='background: red; color: white; padding: 10px; margin: 10px;'>";
            echo "<strong>SQL 예외 발생:</strong><br>";
            echo "오류 메시지: " . $Exception->getMessage() . "<br>";
            echo "오류 코드: " . $Exception->getCode() . "<br>";
            echo "</div>";
        }
        error_log("Work process tasks fetch error: " . $Exception->getMessage());
    }
    
    return $workprocess_tasks; 
}
 
// 기존 데이터 확인 (check_existing 모드일 때) 
if ($mode === 'check_existing') {
    try {
        $sql = "SELECT num FROM " . $DB . "." . $tablename . " WHERE task_date = ? AND employee_name = ? AND (is_deleted = 'N' OR is_deleted IS NULL)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(1, $seldate, PDO::PARAM_STR);
        $stmt->bindValue(2, $employee_name, PDO::PARAM_STR);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            // 기존 데이터가 있음
            echo json_encode([
                'exists' => true,
                'num' => $result['num']
            ]);
        } else {
            // 기존 데이터가 없음
            echo json_encode([
                'exists' => false,
                'num' => null
            ]);
        }
        exit;
    } catch (PDOException $Exception) {
        error_log("Check existing data error: " . $Exception->getMessage());
        echo json_encode([
            'exists' => false,
            'num' => null,
            'error' => $Exception->getMessage()
        ]);
        exit; 
    }
}

// 데이터 조회 (modify 모드일 때)
if ($mode === 'modify' && $num !== 'undefined') {
    try {
        if($level == '1') { // 관리자이면 전부 다 보여주고
            $sql = "SELECT * FROM " . $DB . "." . $tablename . " WHERE num = ?";
            $stmh = $pdo->prepare($sql);
            $stmh->bindValue(1, $num, PDO::PARAM_INT);      
        } else {
            $sql = "SELECT * FROM " . $DB . "." . $tablename . " WHERE num = ? AND employee_name = ?";
            $stmh = $pdo->prepare($sql);
            $stmh->bindValue(1, $num, PDO::PARAM_INT);      
            $stmh->bindValue(2, $employee_name, PDO::PARAM_STR);      
        }
        
        $stmh->execute();
        $row = $stmh->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $task_date = $row['task_date'];
            $employee_name = $row['employee_name'];
            $department = $row['department'];
            $memo = $row['memo'];
            
            // JSON 데이터 파싱
            if(!empty($row['tasks'])) {
                $tasks = json_decode($row['tasks'], true) ?? [];
                
                // 각 작업의 경과일 계산 (modify 모드용)
                foreach ($tasks as $index => &$task) {
                    if (!empty($task['task_content'])) {
                        $original_date = $task['original_date'] ?? $task_date;
                        $original_date_obj = new DateTime($original_date);
                        $today = new DateTime();
                        
                        // 미래 날짜인 경우 경과일을 0으로 설정
                        if ($original_date_obj > $today) {
                            $task['elapsed_days'] = 0;
                        }
                        // 미완료 작업인 경우: 현재 날짜 기준으로 계산
                        else if (empty($task['is_completed']) || $task['is_completed'] === false) {
                            $task['elapsed_days'] = $today->diff($original_date_obj)->days;
                        } 
                        // 완료된 작업인 경우: completion_date 또는 task_date 기준으로 계산
                        else {
                            $completion_reference_date = !empty($task['completion_date']) ? $task['completion_date'] : $task_date;
                            $completion_date_obj = new DateTime($completion_reference_date);
                            $task['elapsed_days'] = $completion_date_obj->diff($original_date_obj)->days;
                        }
                        
                        // original_date 설정 (없는 경우)
                        if (!isset($task['original_date'])) {
                            $task['original_date'] = $original_date;
                        }
                    }
                }
                unset($task); // 참조 해제
                
                $tasksCount = count($tasks);
            }
            
            // 수정 모드에서도 오늘 날짜인 경우 과거 미완료 할일 추가
            $today = new DateTime();
            $today->setTime(0, 0, 0);
            $task_date_obj = new DateTime($task_date);
            $task_date_obj->setTime(0, 0, 0);
            
            if ($task_date_obj == $today) {
                $pending_tasks = getPendingTasks($pdo, $employee_name, $task_date);
                if (!empty($pending_tasks)) {
                    // 중복 제거를 위한 맵 생성
                    $existing_task_map = [];
                    foreach ($tasks as $task) {
                        if (!empty($task['task_content'])) {
                            $task_content = trim($task['task_content']);
                            $existing_task_map[$task_content] = true;
                        }
                    }
                    
                    // 중복되지 않는 과거 미완료 할일만 추가
                    $unique_pending_tasks = [];
                    foreach ($pending_tasks as $pending_task) {
                        $pending_content = trim($pending_task['task_content']);
                        if (!isset($existing_task_map[$pending_content])) {
                            $unique_pending_tasks[] = $pending_task;
                            $existing_task_map[$pending_content] = true; // 추가된 할일도 맵에 기록
                        }
                    }
                    
                    if (!empty($unique_pending_tasks)) {
                        $tasks = array_merge($unique_pending_tasks, $tasks);
                        $tasksCount = count($tasks);
                    }
                }
            }
             
            // 업무요청사항 가져오기 (수정 모드에서도)
            // 현재 할일에 이미 있는 workprocess_num 추출
            $existing_workprocess_nums = [];
            $existing_workprocess_map = [];
            foreach ($tasks as $task) {
                if (!empty($task['workprocess_num'])) {
                    $existing_workprocess_nums[] = intval($task['workprocess_num']);
                    $existing_workprocess_map[intval($task['workprocess_num'])] = true;
                }
            }
            
            // 중복 제거
            $existing_workprocess_nums = array_unique($existing_workprocess_nums);
            
            $workprocess_tasks = getWorkProcessTasks($pdo, $employee_name, $task_date, $debug, $existing_workprocess_nums);
            if (!empty($workprocess_tasks)) {
                // 한번 더 중복 체크 (이미 추가된 것은 제외)
                $filtered_workprocess_tasks = [];
                foreach ($workprocess_tasks as $wp_task) {
                    if (!isset($existing_workprocess_map[$wp_task['workprocess_num']])) {
                        $filtered_workprocess_tasks[] = $wp_task;
                        $existing_workprocess_map[$wp_task['workprocess_num']] = true;
                    }
                }
                
                if (!empty($filtered_workprocess_tasks)) {
                    $tasks = array_merge($filtered_workprocess_tasks, $tasks);
                    $tasksCount = count($tasks);
                }
            }
        }
    } catch (PDOException $Exception) {
        echo "오류: " . $Exception->getMessage();
        exit;
    }
} else {
    $mode = 'insert';
    
    // 선택된 날짜가 오늘 이후인지 확인
    $today = new DateTime();
    $today->setTime(0, 0, 0);
    $selected_date = new DateTime($seldate);
    $selected_date->setTime(0, 0, 0);
    
    // 오늘 날짜일 때만 과거 미완료 할일 가져오기
    if ($selected_date == $today) {
        $pending_tasks = getPendingTasks($pdo, $employee_name, $seldate);
        
        // 기존 할일과 추적할일 합치기
        if (!empty($pending_tasks)) {
            $tasks = array_merge($pending_tasks, $tasks);
            $tasksCount = count($tasks);
        }
    }
    
    // 기존 employee_tasks 레코드에서 workprocess_nums 가져오기
    $existing_workprocess_nums = [];
    
    // 현재 날짜에 이미 저장된 workprocess_nums 조회
    try {
        $existing_sql = "SELECT workprocess_nums FROM {$DB}.{$tablename} 
                        WHERE employee_name = ? AND task_date = ? AND (is_deleted = 'N' OR is_deleted IS NULL)
                        ORDER BY num DESC LIMIT 1";
        $existing_stmt = $pdo->prepare($existing_sql);
        $existing_stmt->bindValue(1, $employee_name, PDO::PARAM_STR);
        $existing_stmt->bindValue(2, $seldate, PDO::PARAM_STR);
        $existing_stmt->execute();
        $existing_record = $existing_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing_record && !empty($existing_record['workprocess_nums']) && $existing_record['workprocess_nums'] !== 'null') {
            $stored_nums = json_decode($existing_record['workprocess_nums'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($stored_nums)) {
                $existing_workprocess_nums = $stored_nums;
            } else {
                // JSON 파싱 실패시 로그 기록
                error_log("JSON decode error for workprocess_nums: " . json_last_error_msg() . " - Data: " . $existing_record['workprocess_nums']);
            }
        }
    } catch (PDOException $e) {
        error_log("Error fetching existing workprocess_nums: " . $e->getMessage());
    }
    
    // 현재 할일에서도 workprocess_num 추출하여 추가
    $existing_workprocess_map = [];
    foreach ($tasks as $task) {
        if (!empty($task['workprocess_num'])) {
            $workprocess_num = intval($task['workprocess_num']);
            $existing_workprocess_nums[] = $workprocess_num;
            $existing_workprocess_map[$workprocess_num] = true;
        }
    }
    
    // 중복 제거
    $existing_workprocess_nums = array_unique($existing_workprocess_nums);
    
    // 업무요청사항 가져오기 (기존에 처리된 번호들은 제외)
    $workprocess_tasks = getWorkProcessTasks($pdo, $employee_name, $seldate, $debug, $existing_workprocess_nums);
    if (!empty($workprocess_tasks)) {
        // 한번 더 중복 체크 (이미 추가된 것은 제외)
        $filtered_workprocess_tasks = [];
        foreach ($workprocess_tasks as $wp_task) {
            $wp_num = intval($wp_task['workprocess_num']);
            if (!isset($existing_workprocess_map[$wp_num])) {
                $filtered_workprocess_tasks[] = $wp_task;
                $existing_workprocess_map[$wp_num] = true;
            }
        }
        
        if (!empty($filtered_workprocess_tasks)) {
            $tasks = array_merge($filtered_workprocess_tasks, $tasks);
            $tasksCount = count($tasks);
        }
    }    
}

$title_message = ($mode === 'modify') ? '할일 수정' : '할일 등록';
?>

	<div class="card justify-content-center">
		<div class="card-header text-center">
			<span class="text-center fs-5"><?= $title_message ?></span>
			<div class="d-flex justify-content-center align-items-center">
				<div class="card bg-primary text-white mx-1" style="width: 150px;">
					<div class="card-body text-center p-1">
						<small class="card-title mb-0">전체 할일</small>
						<h6 id="totalTasks" class="mb-0">0</h6>
					</div>
				</div>
				<div class="card bg-success text-white mx-1" style="width: 150px;">
					<div class="card-body text-center p-1">
						<small class="card-title mb-0">완료된 할일</small>
						<h6 id="completedTasks" class="mb-0">0</h6>
					</div>
				</div>
				<div class="card bg-warning text-white mx-1" style="width: 150px;">
					<div class="card-body text-center p-1">
						<small class="card-title mb-0">진행중인 할일</small>
						<h6 id="pendingTasks" class="mb-0">0</h6>
					</div>
				</div>
				<div class="card bg-info text-white mx-1" style="width: 150px;">
					<div class="card-body text-center p-1">
						<small class="card-title mb-0">완료율</small>
						<h6 id="completionRate" class="mb-0">0%</h6>
					</div>
				</div>
			</div>
		</div>
            <div class="card-body">
				<div class="row justify-content-center text-center">
                    <div class="d-flex align-items-center justify-content-center m-2">		
                        <div class="table-responsive">				
						<table class="table table-bordered table-sm">
							<tbody>
								<tr>
									<td class="text-center fs-6 " style="width:100px;">날짜</td>
									<td class="text-center" style="width:120px;">
										<input type="date" class="form-control fs-6" id="task_date" name="task_date" style="width:130px; border:none; box-shadow:none;" value="<?= $task_date ?>">
									</td>
                                    <td class="text-center fs-6" style="width:100px;">부서</td>
									<td class="text-center" style="width:180px;">
										<input type="text" class="form-control fs-6" id="department" name="department" value="<?= htmlspecialchars($department) ?>" autocomplete="off" style="border:none; box-shadow:none;" >			
                                    </td>									                                    
									<td class="text-center fs-6" style="width:100px;">성명</td>
									<td class="text-center">
										<input type="text" class="form-control fs-6 w100px" id="employee_name" name="employee_name" value="<?= htmlspecialchars($employee_name) ?>" autocomplete="off" style="border:none; box-shadow:none;">
									</td>
								</tr>																	
									<input type="hidden" class="form-control fs-6 text-start" id="memo" name="memo" value="<?= htmlspecialchars($memo) ?>" autocomplete="off">									
							</tbody>
                        </table>
                        </div>
                    </div>
                </div>
            <!-- 할일 목록 테이블과 통계 정보 -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered align-middle text-center table-sm" id="taskTable">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" style="width: 10%;">관리</th>
                                        <th scope="col" style="width: 60%;">할일</th>
                                        <th scope="col" style="width: 8%;">완료</th>
                                        <th scope="col" style="width: 8%;">완료일</th>
                                        <th scope="col" style="width: 8%;">경과</th>
                                    </tr>
                                </thead>
                                <tbody id="taskTableBody">
                                    <?php 
                                    $task_count = max(1, count($tasks));
                                    for($i = 0; $i < $task_count; $i++): 
                                        $task = $tasks[$i] ?? [];
                                        $is_pending = $task['is_pending'] ?? false;
                                        $elapsed_days = $task['elapsed_days'] ?? 0;
                                        $unique_id = $task['unique_id'] ?? '';
                                        $is_workprocess = $task['is_workprocess'] ?? false;
                                        $workprocess_num = $task['workprocess_num'] ?? '';
                                        
                                        // 저장된 tasks에서 workprocess_num이 있으면 workprocess로 처리
                                        if (!empty($workprocess_num) && !$is_workprocess) {
                                            $is_workprocess = true;
                                        }
                                        
                                        // 할일 날짜
                                        $original_task_date = $task['original_task_date'] ?? '';         
                                    ?>
                                    <tr class="task-row align-middle <?= $is_pending ? 'table-warning' : '' ?> <?= $is_workprocess ? 'table-info' : '' ?>" data-row="<?= $i ?>" data-unique-id="<?= $unique_id ?>" data-workprocess-num="<?= $workprocess_num ?>">
                                        <td class="text-center align-middle">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <div class="btn-group btn-group-sm" role="group" style="gap: 1px;">
                                                    <button type="button" class="btn btn-outline-primary btn-sm p-0" style="width: 20px; height: 20px; font-size: 12px;" onclick="addRowAfter(<?= $i ?>)" title="아래에 행 추가">
                                                        <i class="bi bi-plus"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-success btn-sm p-0" style="width: 20px; height: 20px; font-size: 12px;" onclick="copyRow(<?= $i ?>)" title="행 복사">
                                                        <i class="bi bi-files"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger btn-sm p-0" style="width: 20px; height: 20px; font-size: 12px;" onclick="deleteRow(<?= $i ?>)" title="행 삭제">
                                                        <i class="bi bi-dash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center align-middle">
                                            <div class="d-flex justify-content-start align-items-center w-100">
                                                <?php
                                                    // 오늘 이전 날짜면 readonly 처리
                                                    $task_original_date = $task['original_date'] ?? $task_date;
                                                    $readonly = '';
                                                    if (!empty($task_original_date)) {
                                                        $today = new DateTime();
                                                        $today->setTime(0, 0, 0);
                                                        $original_date_obj = new DateTime($task_original_date);
                                                        if ($original_date_obj < $today) {
                                                            $readonly = 'readonly';
                                                        }
                                                    }   
                                                ?>
                                                <?php if ($is_pending): ?>
                                                    <span> <?= date('m/d', strtotime(htmlspecialchars($original_task_date))) ?> </span>
                                                    <span class="badge bg-warning me-2" title="이전 날짜 미완료 할일">추적</span>
                                                <?php endif; ?>
                                                <?php if ($is_workprocess): ?>
                                                    <button type="button" class="btn btn-outline-dark btn-sm me-2" onclick="showWorkprocessDetail(<?= $workprocess_num ?>)" title="업무요청사항 상세보기" style="padding: 2px 6px; font-size: 11px;">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <span class="badge bg-danger me-2" title="업무요청사항">업무</span>
                                                <?php endif; ?>
                                                <textarea name="tasks[<?= $i ?>][task_content]" class="form-control form-control-sm task-content-input flex-grow-1 <?= $is_workprocess ? 'workprocess-task' : '' ?>" placeholder="할일을 입력하세요" rows="1" style="resize: none; overflow-y: hidden; line-height: 1.5; padding: 0.25rem 0.5rem; <?= $is_workprocess ? 'cursor: pointer; background-color: #e3f2fd;' : '' ?>" <?= ($is_pending || $is_workprocess) ? 'readonly' : $readonly ?> data-workprocess-num="<?= $workprocess_num ?>"><?= htmlspecialchars($task['task_content'] ?? '') ?></textarea>
                                                <input type="hidden" name="tasks[<?= $i ?>][unique_id]" value="<?= $unique_id ?>">
                                                <input type="hidden" name="tasks[<?= $i ?>][is_pending]" value="<?= $is_pending ? '1' : '0' ?>">
                                                <input type="hidden" name="tasks[<?= $i ?>][is_workprocess]" value="<?= $is_workprocess ? '1' : '0' ?>">
                                                <input type="hidden" name="tasks[<?= $i ?>][workprocess_num]" value="<?= htmlspecialchars($workprocess_num) ?>">
                                                <input type="hidden" name="tasks[<?= $i ?>][date_key]" value="<?= htmlspecialchars($task['date_key'] ?? '') ?>">
                                            </div>
                                        </td>
                                        <td class="text-center align-middle">
                                            <?php 
                                            // 오늘 이전 날짜에는 readonly/disabled 처리 (오늘은 비활성화 X)
                                            $is_past_date = false;
                                            
                                            if (!empty($task_date)) {
                                                $today = new DateTime();
                                                $today->setTime(0, 0, 0);
                                                $task_date_obj = new DateTime($task_date);
                                                // 오늘 날짜는 disabled 아님 (과거만)
                                                if ($task_date_obj < $today) {
                                                    $is_past_date = true;
                                                }
                                            }
                                            ?>
                                            <div class="form-check d-flex justify-content-center align-middle align-items-center">
                                                <input class="form-check-input task-checkbox" type="checkbox" name="tasks[<?= $i ?>][is_completed]" value="1" <?= ($task['is_completed'] ?? false) ? 'checked' : '' ?> onchange="updateCompletionDate(this)" <?= $is_past_date ? 'disabled' : '' ?>>
                                                <?php if ($is_past_date): ?>
                                                    <input type="hidden" name="tasks[<?= $i ?>][is_completed_hidden]" value="<?= ($task['is_completed'] ?? false) ? '1' : '0' ?>">
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="text-center align-middle">
                                            <input type="date" name="tasks[<?= $i ?>][completion_date]" class="form-control form-control-sm completion-date-input" value="<?= $task['completion_date'] ?? '' ?>" <?= ($task['is_completed'] ?? false) && !$is_past_date ? '' : 'readonly' ?>>
                                        </td>
                                        <td class="text-center align-middle">
                                            <?php 
                                            $task_original_date = $task['original_date'] ?? $task_date;
                                            if(!empty($task_original_date)) {
                                                $original_date = new DateTime($task_original_date);
                                                $today = new DateTime(); // 현재 날짜
                                                
                                                // elapsed_days 값이 있으면 우선 사용
                                                if (isset($task['elapsed_days']) && ($task['elapsed_days'] !== '')) {
                                                    $elapsed = $task['elapsed_days'];
                                                    
                                                    // 숫자가 아닌 경우 (예: "예정", "-" 등) 처리
                                                    if (is_numeric($elapsed)) {
                                                        if ($original_date > $today) {
                                                            echo '<span class="badge bg-info elapsed-days-display" data-original-date="' . $task_original_date . '">예정</span>';
                                                        } else if ($elapsed >= 0) {
                                                            $badge_class = ($task['is_completed'] ?? false) ? 'bg-success' : 'bg-secondary';
                                                            echo '<span class="badge ' . $badge_class . ' elapsed-days-display" data-original-date="' . $task_original_date . '">' . $elapsed . '일</span>';
                                                        } else {
                                                            echo '<span class="elapsed-days-display" data-original-date="' . $task_original_date . '">-</span>';
                                                        }
                                                    } else {
                                                        // 텍스트 값인 경우 그대로 표시
                                                        echo '<span class="badge bg-info elapsed-days-display" data-original-date="' . $task_original_date . '">' . $elapsed . '</span>';
                                                    }
                                                }
                                                // elapsed_days 값이 없으면 기존 로직 사용 (fallback)
                                                else {
                                                    // 미래 날짜인 경우 경과일 표시하지 않음
                                                    if ($original_date > $today) {
                                                        echo '<span class="badge bg-info elapsed-days-display" data-original-date="' . $task_original_date . '">예정</span>';
                                                    } else {
                                                        // 미완료 작업인 경우: 현재 날짜 기준으로 계산
                                                        if (empty($task['is_completed']) || $task['is_completed'] === false) {
                                                            $reference_date = new DateTime(); // 현재 날짜
                                                        } 
                                                        // 완료된 작업인 경우: completion_date 또는 task_date 기준으로 계산
                                                        else {
                                                            if (!empty($task['completion_date'])) {
                                                                $reference_date = new DateTime($task['completion_date']);
                                                            } else {
                                                                $reference_date = new DateTime($task_date); // task_date 사용
                                                            }
                                                        }
                                                        
                                                        $elapsed = $reference_date->diff($original_date)->days;
                                                        if($elapsed >= 0) {
                                                            $badge_class = ($task['is_completed'] ?? false) ? 'bg-success' : 'bg-secondary';
                                                            echo '<span class="badge ' . $badge_class . ' elapsed-days-display" data-original-date="' . $task_original_date . '">' . $elapsed . '일</span>';
                                                        } else {
                                                            echo '<span class="elapsed-days-display" data-original-date="' . $task_original_date . '">-</span>';
                                                        }
                                                    }
                                                }
                                            } else {
                                                echo '<span class="elapsed-days-display" data-original-date="' . $task_date . '">-</span>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endfor; ?>
                                </tbody>
                            </table>                
                        </div>
                    </div>
                </div> 
                <div class="d-flex justify-content-center">
                    <?php 
                    $is_past_date_for_save = false;
                    if ($mode === 'modify' && !empty($task_date)) {
                        $task_date_obj = new DateTime($task_date);
                        $today = new DateTime();
                        $today->setTime(0, 0, 0); // 시간을 00:00:00으로 설정
                        $is_past_date_for_save = $task_date_obj < $today;
                    }
                    ?>
                    <button type="button" id="saveBtn" class="btn btn-dark btn-sm me-3" <?= $is_past_date_for_save ? 'disabled' : '' ?> >
                        <i class="bi bi-floppy-fill"></i> 저장
                    </button>
                    <?php if ($mode !== 'insert') { ?>
                    <button type="button" id="deleteBtn" class="btn btn-danger btn-sm me-3" <?= $is_past_date_for_save ? 'disabled' : '' ?>>
                        <i class="bi bi-trash"></i> 삭제 
                    </button>						
                    <?php } ?>
										
                    <button type="button" id="closeBtn" class="btn btn-outline-dark btn-sm me-2" data-bs-dismiss="modal">
                        &times; 닫기
                    </button>
                </div>
            </div>
		</div>

<script>
// Textarea 자동 높이 조절 함수
function autoResizeTextarea(textarea) {
    // 높이를 auto로 재설정하여 scrollHeight를 정확히 계산
    textarea.style.height = 'auto';
    textarea.style.height = textarea.scrollHeight + 'px';
}

// 모든 textarea 초기화 함수
function initializeAllTextareas() {
    document.querySelectorAll('.task-content-input').forEach(function(textarea) {
        // 초기 높이 설정 - 내용이 있으면 그에 맞춰 조절
        autoResizeTextarea(textarea);
        
        // 입력 시 높이 자동 조절 (이벤트 중복 방지를 위해 먼저 제거)
        textarea.removeEventListener('input', handleTextareaInput);
        textarea.addEventListener('input', handleTextareaInput);
    });
}

// textarea input 이벤트 핸들러
function handleTextareaInput() {
    autoResizeTextarea(this);
}

// 페이지 로드 시 모든 textarea 초기화
document.addEventListener('DOMContentLoaded', function() {
    // 약간의 지연을 주어 DOM이 완전히 렌더링된 후 실행
    setTimeout(function() {
        initializeAllTextareas();
    }, 100);
});

// 모달이 표시될 때도 textarea 초기화 (Bootstrap 모달 이벤트 활용)
if (typeof bootstrap !== 'undefined') {
    document.addEventListener('shown.bs.modal', function(event) {
        if (event.target.id === 'taskModal') {
            setTimeout(function() {
                initializeAllTextareas();
            }, 100);
        }
    });
}

// 동적으로 추가되는 textarea를 위한 이벤트 위임
document.addEventListener('input', function(e) {
    if (e.target && e.target.classList.contains('task-content-input')) {
        autoResizeTextarea(e.target);
    }
});

// 업무요청사항 클릭 이벤트
document.addEventListener('click', function(e) {
    if (e.target && e.target.classList.contains('workprocess-task')) {
        const workprocessNum = e.target.getAttribute('data-workprocess-num');
        if (workprocessNum) {
            // workprocess 상세보기 팝업 열기
            const url = `/workprocess/view.php?num=${workprocessNum}&tablename=workprocess`;
            customPopup(url, '', 1400, 900);
        }
    }
}); 

// MutationObserver로 DOM 변경 감지하여 새로운 textarea 자동 초기화
const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
        if (mutation.addedNodes.length) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) { // Element node
                    const textareas = node.querySelectorAll ? node.querySelectorAll('.task-content-input') : [];
                    textareas.forEach(function(textarea) {
                        setTimeout(function() {
                            autoResizeTextarea(textarea);
                        }, 10);
                    });
                }
            });
        }
    });
});

// body 요소를 관찰
if (document.body) {
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
}
</script>

