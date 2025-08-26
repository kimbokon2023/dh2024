<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/session.php');  
$title_message = '직원 할일 관리'; 
$title_message_sub = '오늘의 할일 등록 및 체크' ; 
$tablename = 'employee_tasks'; 
$item ='직원 할일';   
$emailTitle ='할일';   
$subTitle = '직원 할일 관리';
?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php'; ?> 

<title> <?=$title_message?>  </title>

</head>		 
<body>

<?php
$pdo = db_connect();

// GET 파라미터 처리
$mode = $_REQUEST["mode"] ?? 'insert';
$num = $_REQUEST["num"] ?? '';
$tablename = $_REQUEST["tablename"] ?? 'employee_tasks';

// 복사 모드일 때는 num을 초기화 (새로운 할일로 저장하기 위해)
if($mode == 'copy') {
    $original_num = $num; // 원본 num 보관
    $num = ''; // 새로운 할일을 위해 num 초기화
}

// 데이터 조회
$task_data = null;
if(($mode == 'view' || $mode == 'modify' || $mode == 'copy')) {
    $query_num = ($mode == 'copy') ? $original_num : $num;
    if(!empty($query_num)) {
        try {
            $sql = "SELECT * FROM {$DB}.employee_tasks WHERE num = :num AND (is_deleted IS NULL OR is_deleted = 'N')";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':num', $query_num, PDO::PARAM_INT);
            $stmt->execute();
            $task_data = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "오류: " . $e->getMessage();
        }
    }
}

// 기본값 설정
$task_date = $task_data['task_date'] ?? date('Y-m-d');

// session.php에서 사용자 정보 가져오기
$session_employee_name = $_SESSION['user_name'] ?? $_SESSION['name'] ?? $_SESSION['username'] ?? '';
$session_department = $_SESSION['part'] ?? $_SESSION['department'] ?? $_SESSION['dept'] ?? $_SESSION['user_department'] ?? '';

// 저장된 데이터가 있으면 그 값을 사용, 없으면 세션에서 가져온 값 사용
$employee_name = $task_data['employee_name'] ?? $session_employee_name;
$department = $task_data['department'] ?? $session_department;
$memo = $task_data['memo'] ?? '';

// JSON 데이터 파싱
$tasks = [];

if($task_data) {
    if(!empty($task_data['tasks'])) {
        $tasks = json_decode($task_data['tasks'], true) ?? [];
    }
}

// insert 모드일 때 과거 미완료 할일 가져오기 (가장 오래된 날짜부터)
if($mode == 'insert' && empty($tasks)) {
    $today = date('Y-m-d');
    
    // 오늘 이전의 모든 날짜에서 같은 직원의 미완료 할일 조회 (가장 오래된 날짜부터)
    $sql = "SELECT tasks, task_date FROM {$DB}.employee_tasks 
            WHERE employee_name = :employee_name 
            AND task_date < :today 
            AND (is_deleted IS NULL OR is_deleted = 'N')
            ORDER BY task_date ASC, created_at ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':employee_name', $employee_name);
    $stmt->bindParam(':today', $today);
    $stmt->execute();
    $past_tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 이미 처리된 할일 내용을 추적하기 위한 배열
    $processed_contents = [];
    
    foreach($past_tasks as $past_task) {
        if(!empty($past_task['tasks'])) {
            $task_items = json_decode($past_task['tasks'], true) ?? [];
            
            foreach($task_items as $task) {
                // 미완료이고 아직 처리되지 않은 내용인 경우만 추가
                if(!($task['is_completed'] ?? false) && !in_array($task['task_content'], $processed_contents)) {
                    // original_date가 없으면 해당 날짜를 설정
                    if(empty($task['original_date'])) {
                        $task['original_date'] = $past_task['task_date'];
                    }
                    // 자동으로 가져온 할일임을 표시
                    $task['is_imported'] = true;
                    $tasks[] = $task;
                    
                    // 처리된 내용으로 표시
                    $processed_contents[] = $task['task_content'];
                }
            }
        }
    }
}
?>

<form method="post" id="taskForm">
    <input type="hidden" id="mode" name="mode" value="<?= $mode ?>">
    <input type="hidden" id="tablename" name="tablename" value="<?= $tablename ?>">    
    <input type="hidden" id="num" name="num" value="<?= $num ?>">
    <input type="hidden" id="user_name" name="user_name" value="<?= $user_name ?>">
    <input type="hidden" id="level" name="level" value="<?= $level ?>">

<div class="container-fluid my-3">
    <div class="card shadow-sm mb-4 ">
        <div class="card-body p-4">
            <?php if($mode == 'insert' || $mode == 'modify' || $mode == 'copy'): ?>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>
                    <?php 
                    if($mode == 'insert') echo '직원 할일 등록';
                    elseif($mode == 'modify') echo '직원 할일 수정';
                    elseif($mode == 'copy') echo '직원 할일 복사';
                    ?>
                </h4>
                <div>
                    <button type="button" id="saveBtn" class="btn btn-primary me-2">저장</button>                    
                    <button type="button" class="btn btn-secondary" onclick="window.close()">닫기</button>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if($mode == 'view'): ?>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>직원 할일 보기</h4>
                <div>
                    <!-- <button type="button" class="btn btn-dark me-2" onclick="editTask()">수정</button>
                    <button type="button" class="btn btn-primary me-2" onclick="copyTask()">복사</button>
                    <button type="button" class="btn btn-danger me-2" onclick="deleteTask(<?= $num ?>, '<?= $employee_name ?>')">삭제</button> -->
                    <button type="button" class="btn btn-secondary" onclick="window.close()">닫기</button>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container-fluid my-3">
<div class="card shadow-sm mb-4 ">
<div class="card-body p-4">    
    <!-- 기본 정보 -->
    <div class="row mb-4">
        <div class="col-md-2 mb-1 mx-2">
            <label for="task_date" class="form-label fw-bold">날짜</label>
            <?php if($mode == 'insert' || $mode == 'modify' || $mode == 'copy'): ?>
                <input type="date" id="task_date" name="task_date" value="<?= $task_date ?>" class="form-control">
            <?php else: ?>
                <div class="form-control-plaintext"><?= date('Y년 m월 d일', strtotime($task_date)) ?></div>
            <?php endif; ?>
        </div>
        <div class="col-md-2 mb-1 mx-2">
            <label for="employee_name" class="form-label fw-bold">직원명</label>
            <?php if($mode == 'insert' || $mode == 'modify' || $mode == 'copy'): ?>
                <input type="text" id="employee_name" name="employee_name" value="<?= htmlspecialchars($employee_name) ?>" class="form-control" placeholder="직원명">
            <?php else: ?>
                <div class="form-control-plaintext"><?= htmlspecialchars($employee_name) ?></div>
            <?php endif; ?>
        </div>
                 <div class="col-md-2 mb-1 mx-2">
             <label for="department" class="form-label fw-bold">부서</label>
             <?php if($mode == 'insert' || $mode == 'modify' || $mode == 'copy'): ?>
                 <input type="text" id="department" name="department" value="<?= htmlspecialchars($department) ?>" class="form-control" placeholder="부서">
             <?php else: ?>
                 <div class="form-control-plaintext"><?= htmlspecialchars($department) ?></div>
             <?php endif; ?>
         </div>
        <div class="col-md-5 mb-1 mx-2">
            <label for="memo" class="form-label fw-bold">메모</label>
            <?php if($mode == 'insert' || $mode == 'modify' || $mode == 'copy'): ?>
                <input type="text" id="memo" name="memo" value="<?= htmlspecialchars($memo) ?>" class="form-control" placeholder="메모">
            <?php else: ?>
                <div class="form-control-plaintext"><?= htmlspecialchars($memo) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 할일 목록 테이블 -->
    <?php 
    $has_imported_tasks = false;
    if($mode == 'insert' && !empty($tasks)) {
        foreach($tasks as $task) {
            if($task['is_imported'] ?? false) {
                $has_imported_tasks = true;
                break;
            }
        }
    }
    ?>
    <?php if($has_imported_tasks): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle"></i>
        <strong>알림:</strong> 과거 미완료된 할일이 자동으로 가져왔습니다. 회색 배경의 할일은 수정할 수 없지만 체크박스는 사용 가능합니다.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <div class="table-responsive mb-4">
        <table class="table table-bordered align-middle text-center" id="taskTable">
            <thead class="table-light">
                <tr>
                    <th scope="col" style="width: 8%;">관리</th>
                    <th scope="col" style="width: 50%;">오늘의 할일</th>
                    <th scope="col" style="width: 12%;">완료</th>
                    <th scope="col" style="width: 15%;">완료일</th>
                    <th scope="col" style="width: 15%;">경과일</th>
                </tr>
            </thead>
            <tbody id="taskTableBody">
                <?php 
                if($mode == 'insert' || $mode == 'modify' || $mode == 'copy'): 
                    // 저장된 데이터가 있으면 모든 행을 생성, 없으면 첫 번째 행만 생성
                    $task_count = max(1, count($tasks));
                    for($i = 0; $i < $task_count; $i++): ?>
                    <tr class="task-row" data-row="<?= $i ?>">
                        <td>
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
                        <td style="text-align: left;">
                            <input type="text" name="tasks[<?= $i ?>][task_content]" class="form-control form-control-sm task-content-input" placeholder="할일을 입력하세요" value="<?= htmlspecialchars($tasks[$i]['task_content'] ?? '') ?>" <?= ($tasks[$i]['is_imported'] ?? false) ? 'readonly style="background-color: #f8f9fa;"' : '' ?>>
                        </td>
                        <td>
                            <div class="form-check d-flex justify-content-center">
                                <input class="form-check-input task-checkbox" type="checkbox" name="tasks[<?= $i ?>][is_completed]" value="1" <?= ($tasks[$i]['is_completed'] ?? false) ? 'checked' : '' ?> onchange="updateCompletionDate(this)">
                            </div>
                        </td>
                        <td>
                            <input type="date" name="tasks[<?= $i ?>][completion_date]" class="form-control form-control-sm completion-date-input" value="<?= $tasks[$i]['completion_date'] ?? '' ?>" <?= ($tasks[$i]['is_completed'] ?? false) ? '' : 'readonly' ?>>
                        </td>
                        <td class="text-center align-middle">
                                    <?php 
                                    if(!empty($tasks[$i]['original_date'])) {
                                        $original_date = new DateTime($tasks[$i]['original_date']);
                                        $today = new DateTime();
                                        $elapsed = $today->diff($original_date)->days;
                                        if($elapsed > 0) {
                                            echo '<span class="badge bg-secondary elapsed-days-display" data-original-date="' . $tasks[$i]['original_date'] . '">' . $elapsed . '일</span>';
                                        } else {
                                            echo '<span class="elapsed-days-display" data-original-date="' . $tasks[$i]['original_date'] . '">-</span>';
                                        }
                                    } else {
                                        echo '<span class="elapsed-days-display" data-original-date="">-</span>';
                                    }
                                    ?>
                    </td>
                    </tr>
                    <?php endfor; ?>
                <?php else:
                // view 모드에서 할일 목록 표시
                foreach($tasks as $task): ?>
                <tr class="task-row-view">
                    <td></td>
                    <td class="text-start"><?= htmlspecialchars($task['task_content'] ?? '') ?></td>
                    <td>
                        <div class="form-check d-flex justify-content-center">
                            <input class="form-check-input" type="checkbox" <?= ($task['is_completed'] ?? false) ? 'checked' : '' ?> disabled>
                        </div>
                    </td>
                    <td class="text-center"><?= $task['completion_date'] ? date('Y-m-d', strtotime($task['completion_date'])) : '' ?></td>
                    <td class="text-center align-middle">
                                    <?php 
                                    if(!empty($tasks[$i]['original_date'])) {
                                        $original_date = new DateTime($tasks[$i]['original_date']);
                                        $today = new DateTime();
                                        $elapsed = $today->diff($original_date)->days;
                                        if($elapsed > 0) {
                                            echo '<span class="badge bg-secondary elapsed-days-display" data-original-date="' . $tasks[$i]['original_date'] . '">' . $elapsed . '일</span>';
                                        } else {
                                            echo '<span class="elapsed-days-display" data-original-date="' . $tasks[$i]['original_date'] . '">-</span>';
                                        }
                                    } else {
                                        echo '<span class="elapsed-days-display" data-original-date="">-</span>';
                                    }
                                    ?>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>                
    </div>

    <!-- 통계 정보 -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h5 class="card-title">전체 할일</h5>
                    <h3 id="totalTasks">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h5 class="card-title">완료된 할일</h5>
                    <h3 id="completedTasks">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h5 class="card-title">진행중인 할일</h5>
                    <h3 id="pendingTasks">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h5 class="card-title">완료율</h5>
                    <h3 id="completionRate">0%</h3>
                </div>
            </div>
        </div>
    </div>
            
        </div>
    </div>
</div>

</form>

<script>
// 페이지 로딩
$(document).ready(function(){	
    var loader = document.getElementById('loadingOverlay');
    if(loader) {
        loader.style.display = 'none';
    }
});
</script>

<script>
$(document).ready(function() {
    // 초기 통계 업데이트
    updateStatistics();
    
    // 저장 버튼 이벤트 리스너
    $('#saveBtn').click(function() {
        saveTask();
    });
    
    // 할일 내용 입력 시 통계 업데이트
    $(document).on('input', '.task-content-input', function() {
        updateStatistics();
    });
    
    // 체크박스 변경 시 통계 업데이트
    $(document).on('change', '.task-checkbox', function() {
        updateStatistics();
    });
});

// 전역 변수
let taskRowCount = <?= max(1, count($tasks)) ?>;

// 저장 함수
function saveTask() {
    console.log('=== 저장 함수 호출 ===');
   
    try {
        // JSON 데이터 생성
        const tasks = [];
        $('.task-row').each(function() {
            const taskContent = $(this).find('.task-content-input').val();
            const isCompleted = $(this).find('.task-checkbox').is(':checked');
            const completionDate = $(this).find('.completion-date-input').val();
            const originalDate = $(this).find('.elapsed-days-display').data('original-date') || '';
            const isImported = $(this).find('.task-content-input').prop('readonly');

            if (taskContent.trim()) { // 빈 내용이 아닌 경우만 추가
                tasks.push({
                    task_content: taskContent,
                    is_completed: isCompleted,
                    completion_date: completionDate,
                    original_date: originalDate,
                    is_imported: isImported
                });
            }
        });
        
        // 폼 데이터 수집
        const formData = new FormData($('#taskForm')[0]);
        formData.append('tasks_json', JSON.stringify(tasks));
        
        // 디버그: 전송할 데이터 확인
        console.log('=== AJAX 전송 데이터 ===');
        console.log('mode:', '<?= $mode ?>');
        console.log('num:', '<?= $num ?>');
        console.log('tablename:', '<?= $tablename ?>');
        console.log('tasks:', tasks);
        
        // AJAX 호출
        $.ajax({
            url: 'task_process.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            timeout: 30000,
            beforeSend: function() {
                console.log('=== AJAX 요청 시작 ===');
            },
            success: function(response) {
                console.log('=== AJAX 성공 응답 ===');
                console.log('Response:', response);
                
                if (response.result === 'success') {
                    console.log('저장 성공 - mode:', '<?= $mode ?>', 'num:', response.num);
                    
                    Swal.fire({
                        icon: 'success',
                        title: '성공', 
                        text: response.message,
                        confirmButtonColor: '#3085d6',
                        timer: 1500,
                        showConfirmButton: false
                    });
                
                    setTimeout(function() {                    
                        // 성공 시 view 모드로 이동
                        const mode = '<?= $mode ?>';
                        const num = response.num;
                        console.log('리다이렉트 - mode:', mode, 'num:', num);                        
                        // 부모창 새로고침
                        if(window.opener) {
                            window.opener.location.reload();
                        }
                        window.location.href = 'write_form.php?mode=view&num=' + num + '&tablename=employee_tasks';                        
                    }, 1500);
                } else {
                    console.log('저장 실패 - message:', response.message);
                    alert('저장 중 오류가 발생했습니다: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.log('=== AJAX 오류 ===');
                console.log('Status:', status);
                console.log('Error:', error);
                console.log('Response Text:', xhr.responseText);
                
                let errorMessage = '저장 중 오류가 발생했습니다.';
                if (status === 'timeout') {
                    errorMessage = '요청 시간이 초과되었습니다.';
                } else if (xhr.status === 0) {
                    errorMessage = '네트워크 연결을 확인해주세요.';
                } else if (xhr.responseText) {
                    try {
                        const errorResponse = JSON.parse(xhr.responseText);
                        errorMessage = errorResponse.message || errorMessage;
                    } catch (e) {
                        errorMessage = error || errorMessage;
                    }
                }
                
                alert(errorMessage);
            }
        });
    } catch (error) {
        console.log('=== JavaScript 오류 ===');
        console.log('Error:', error);
        alert('JavaScript 오류가 발생했습니다: ' + error.message);
    }
}

// 통계 업데이트 함수
function updateStatistics() {
    let totalTasks = 0;
    let completedTasks = 0;
    
    // 일반 모드 (insert, modify, copy)와 view 모드를 모두 처리
    $('.task-row, .task-row-view').each(function() {
        let taskContent = '';
        let isCompleted = false;
        
        // 일반 모드인 경우
        if ($(this).hasClass('task-row')) {
            taskContent = $(this).find('.task-content-input').val();
            isCompleted = $(this).find('.task-checkbox').is(':checked');
        }
        // view 모드인 경우
        else if ($(this).hasClass('task-row-view')) {
            taskContent = $(this).find('td:nth-child(2)').text().trim();
            isCompleted = $(this).find('input[type="checkbox"]').is(':checked');
        }
        
        if (taskContent.trim()) {
            totalTasks++;
            if (isCompleted) {
                completedTasks++;
            }
        }
    });
    
    const pendingTasks = totalTasks - completedTasks;
    const completionRate = totalTasks > 0 ? Math.round((completedTasks / totalTasks) * 100) : 0;
    
    $('#totalTasks').text(totalTasks);
    $('#completedTasks').text(completedTasks);
    $('#pendingTasks').text(pendingTasks);
    $('#completionRate').text(completionRate + '%');
}

// 완료일 업데이트 함수
function updateCompletionDate(checkbox) {
    const row = $(checkbox).closest('.task-row');
    const completionDateInput = row.find('.completion-date-input');
    
    if (checkbox.checked) {
        // 체크된 경우 오늘 날짜로 설정
        const today = new Date().toISOString().split('T')[0];
        completionDateInput.val(today);
        completionDateInput.prop('readonly', false);
    } else {
        // 체크 해제된 경우 날짜 초기화
        completionDateInput.val('');
        completionDateInput.prop('readonly', true);
    }
}

// 특정 행 뒤에 새 행 추가
function addRowAfter(rowIndex) {
    const newRowIndex = taskRowCount;
    const newRow = `
        <tr class="task-row" data-row="${newRowIndex}">
            <td>
                <div class="d-flex align-items-center justify-content-center">
                    <div class="btn-group btn-group-sm" role="group" style="gap: 1px;">
                        <button type="button" class="btn btn-outline-primary btn-sm p-0" style="width: 20px; height: 20px; font-size: 12px;" onclick="addRowAfter(${newRowIndex})" title="아래에 행 추가">
                            <i class="bi bi-plus"></i>
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm p-0" style="width: 20px; height: 20px; font-size: 12px;" onclick="copyRow(${newRowIndex})" title="행 복사">
                            <i class="bi bi-files"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm p-0" style="width: 20px; height: 20px; font-size: 12px;" onclick="deleteRow(${newRowIndex})" title="행 삭제">
                            <i class="bi bi-dash"></i>
                        </button>
                    </div>
                </div>
            </td>
            <td style="text-align: left;">
                <input type="text" name="tasks[${newRowIndex}][task_content]" class="form-control form-control-sm task-content-input" placeholder="할일을 입력하세요">
            </td>
            <td>
                <div class="form-check d-flex justify-content-center">
                    <input class="form-check-input task-checkbox" type="checkbox" name="tasks[${newRowIndex}][is_completed]" value="1" onchange="updateCompletionDate(this)">
                </div>
            </td>
            <td>
                <input type="date" name="tasks[${newRowIndex}][completion_date]" class="form-control form-control-sm completion-date-input" readonly>
            </td>
            <td>
                <span class="badge bg-secondary elapsed-days-display" data-original-date="<?= date('Y-m-d') ?>">-</span>
            </td>
        </tr>
    `;
    
    // 지정된 행 뒤에 새 행 삽입
    const targetRow = $(`.task-row[data-row="${rowIndex}"]`);
    targetRow.after(newRow);
    
    taskRowCount++;
    updateRowNumbers();
    updateStatistics();
}

// 행 복사 함수
function copyRow(rowIndex) {
    const sourceRow = $(`.task-row[data-row="${rowIndex}"]`);
    const newRowIndex = taskRowCount;
    
    // 소스 행의 데이터 복사
    const taskContent = sourceRow.find('.task-content-input').val();
    const isCompleted = sourceRow.find('.task-checkbox').is(':checked');
    const completionDate = sourceRow.find('.completion-date-input').val();
    
    const newRow = `
        <tr class="task-row" data-row="${newRowIndex}">
            <td>
                <div class="d-flex align-items-center justify-content-center">
                    <div class="btn-group btn-group-sm" role="group" style="gap: 1px;">
                        <button type="button" class="btn btn-outline-primary btn-sm p-0" style="width: 20px; height: 20px; font-size: 12px;" onclick="addRowAfter(${newRowIndex})" title="아래에 행 추가">
                            <i class="bi bi-plus"></i>
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm p-0" style="width: 20px; height: 20px; font-size: 12px;" onclick="copyRow(${newRowIndex})" title="행 복사">
                            <i class="bi bi-files"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm p-0" style="width: 20px; height: 20px; font-size: 12px;" onclick="deleteRow(${newRowIndex})" title="행 삭제">
                            <i class="bi bi-dash"></i>
                        </button>
                    </div>
                </div>
            </td>
            <td style="text-align: left;">
                <input type="text" name="tasks[${newRowIndex}][task_content]" class="form-control form-control-sm task-content-input" placeholder="할일을 입력하세요" value="${taskContent}">
            </td>
            <td>
                <div class="form-check d-flex justify-content-center">
                    <input class="form-check-input task-checkbox" type="checkbox" name="tasks[${newRowIndex}][is_completed]" value="1" ${isCompleted ? 'checked' : ''} onchange="updateCompletionDate(this)">
                </div>
            </td>
            <td>
                <input type="date" name="tasks[${newRowIndex}][completion_date]" class="form-control form-control-sm completion-date-input" value="${completionDate}" ${isCompleted ? '' : 'readonly'}>
            </td>
            <td>
                <span class="badge bg-secondary elapsed-days-display" data-original-date="<?= date('Y-m-d') ?>">-</span>
            </td>
        </tr>
    `;
    
    // 소스 행 뒤에 새 행 삽입
    sourceRow.after(newRow);
    
    taskRowCount++;
    updateRowNumbers();
    updateStatistics();
}

// 행 삭제 함수
function deleteRow(rowIndex) {
    const row = $(`.task-row[data-row="${rowIndex}"]`);
    if ($('.task-row').length > 1) {
        row.remove();
        updateRowNumbers();
        updateStatistics();
    } else {
        alert('최소 1개의 행은 유지해야 합니다.');
    }
}

// 행 번호 업데이트 함수
function updateRowNumbers() {
    $('.task-row').each(function(index) {
        $(this).attr('data-row', index);
        
        // 버튼의 onclick 속성 업데이트
        const buttons = $(this).find('.btn-group button');
        buttons.eq(0).attr('onclick', `addRowAfter(${index})`);
        buttons.eq(1).attr('onclick', `copyRow(${index})`);
        buttons.eq(2).attr('onclick', `deleteRow(${index})`);
    });
}

// 수정 함수
function editTask() {
    const num = '<?= $num ?>';
    window.location.href = 'write_form.php?mode=modify&num=' + num + '&tablename=employee_tasks';
}

// 복사 함수
function copyTask() {
    const num = '<?= $num ?>';
    window.location.href = 'write_form.php?mode=copy&num=' + num + '&tablename=employee_tasks';
}

// 삭제 함수
function deleteTask(num, employee_name) {
    
    if ($('#level').val() !== '1' && $('#user_name').val() !== employee_name) {
        Swal.fire({
            icon: 'error',
            title: '권한 없음',
            text: '관리자 또는 작성자만 삭제할 수 있습니다.',
            confirmButtonText: '확인'
        });
        return;
    }
    
    Swal.fire({
        title: '삭제 확인',
        text: '정말로 이 할일을 삭제하시겠습니까?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: '삭제',
        cancelButtonText: '취소'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'task_process.php',
                type: 'POST',
                data: {
                    mode: 'delete',
                    num: num,
                    tablename: 'employee_tasks'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.result === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: '삭제 완료',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            if(window.opener) {
                                window.opener.location.reload();
                            }
                            window.close();
                        });
                    } else {
                        alert('삭제 중 오류가 발생했습니다: ' + response.message);
                    }
                },
                error: function() {
                    alert('삭제 중 오류가 발생했습니다.');
                }
            });
        }
    });
}
</script>
</body>
</html>
