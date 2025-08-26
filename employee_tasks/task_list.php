<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/session.php');  
$title_message = '직원 할일 목록'; 
$title_message_sub = '할일 관리 시스템' ; 
?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php'; ?> 

<title> <?=$title_message?>  </title>

</head>		 
<body>
<?php 
$header = $_GET['header'] ?? 'yes';
if($header == 'yes') {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php'); 
}
?>   
<?php
$pdo = db_connect();

// 검색 조건
$search_date_start = $_GET['search_date_start'] ?? date('Y-m-d', strtotime('-1 month'));
$search_date_end = $_GET['search_date_end'] ?? date('Y-m-d');
$search_employee = $_GET['search_employee'] ?? '';
$search_department = $_GET['search_department'] ?? '';
$search_status = $_GET['search_status'] ?? '';

// 세션에서 사용자 정보 가져오기
$current_user = $_SESSION['user_name'] ?? $_SESSION['name'] ?? $_SESSION['username'] ?? '';
$current_department = $_SESSION['part'] ?? $_SESSION['department'] ?? $_SESSION['dept'] ?? $_SESSION['user_department'] ?? '';

// 페이지네이션
$page = $_GET['page'] ?? 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// SQL 조건 구성
$where_conditions = ["(is_deleted = 'N' OR is_deleted IS NULL)"];
$params = [];

if (!empty($search_date_start) && !empty($search_date_end)) {
    $where_conditions[] = "task_date BETWEEN :search_date_start AND :search_date_end";
    $params[':search_date_start'] = $search_date_start;
    $params[':search_date_end'] = $search_date_end;
}

if (!empty($search_employee) && trim($search_employee) !== '') {
    $where_conditions[] = "employee_name LIKE :search_employee";
    $params[':search_employee'] = '%' . trim($search_employee) . '%';
}

if (!empty($search_department) && trim($search_department) !== '') {
    $where_conditions[] = "department = :search_department";
    $params[':search_department'] = $search_department;
}

// 현재 사용자가 특정 부서에 속해있다면 해당 부서의 할일만 표시
// if (!empty($current_department)) {
//     $where_conditions[] = "department = :current_department";
//     $params[':current_department'] = $current_department;
// }

$where_clause = implode(' AND ', $where_conditions);

// 전체 개수 조회
$count_sql = "SELECT COUNT(*) as total FROM {$DB}.employee_tasks WHERE {$where_clause}";
$count_stmt = $pdo->prepare($count_sql);
foreach ($params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_count / $limit);

// 데이터 조회
$sql = "SELECT * FROM {$DB}.employee_tasks WHERE {$where_clause} ORDER BY task_date DESC, created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 디버그용 SQL 출력 (개발 완료 후 주석 처리)
// echo $sql ;

// 부서 목록 조회 (필터용)
$dept_sql = "SELECT DISTINCT part FROM {$DB}.member WHERE company = '대한' ORDER BY part";
$dept_stmt = $pdo->prepare($dept_sql);
$dept_stmt->execute();
$departments = $dept_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<input type="hidden" id="header" name="header" value="<?= $header ?>">  
<input type="hidden" id="level" value="<?= $level ?>">
<input type="hidden" id="user_name" value="<?= $user_name ?>">  

<div class="container-fluid my-3">
    <div class="card shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>직원 할일 목록</h4>
                <div>
                    <button type="button" class="btn btn-primary" onclick="openNewTask()">
                        <i class="bi bi-plus-circle"></i> 새 할일 등록
                    </button>
                </div>
            </div>
            
            <!-- 현재 사용자 정보 표시 -->
            <?php if (!empty($current_user)): ?>
            <div class="alert alert-info">
                <strong>현재 사용자:</strong> <?= htmlspecialchars($current_user) ?>
                <?php if (!empty($current_department)): ?>
                    (<?= htmlspecialchars($current_department) ?>)
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container-fluid my-3">
    <div class="card shadow-sm mb-4">
        <div class="card-body p-4">
            <!-- 검색 폼 -->
            <form method="GET" class="row g-2 mb-4">
                <div class="d-flex justify-content-center align-items-center">
                    <div class="col-md-2 mx-2">
                        <label for="search_date_start" class="form-label">기간</label>
                        <div class="d-flex align-items-center">
                            <input type="date" class="form-control me-2 w100px" id="search_date_start" name="search_date_start" value="<?= htmlspecialchars($search_date_start) ?>">
                            <span class="mx-1">~</span>
                            <input type="date" class="form-control ms-2 w100px" id="search_date_end" name="search_date_end" value="<?= htmlspecialchars($search_date_end) ?>">
                        </div>
                    </div>
                    <div class="col-md-1 mx-2">
                        <label for="search_department" class="form-label">부서</label>
                        <select class="form-select w-auto" id="search_department" name="search_department" style="font-size: 0.7rem; height: 30px;">
                            <option value="">전체</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= htmlspecialchars($dept) ?>" <?= ($search_department == $dept) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dept) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-1 mx-2">
                            <label for="search_status" class="form-label">상태</label>
                            <select class="form-select w-auto" id="search_status" name="search_status" style="font-size: 0.7rem; height: 30px;">
                                <option value="">전체</option>
                                <option value="completed" <?= ($search_status == 'completed') ? 'selected' : '' ?>>완료</option>
                                <option value="pending" <?= ($search_status == 'pending') ? 'selected' : '' ?>>진행중</option>
                            </select>
                        </div>
                        <div class="col-md-1 mx-2">
                            <label for="search_employee" class="form-label">직원명</label>
                            <input type="text" class="form-control" id="search_employee" name="search_employee" value="<?= htmlspecialchars($search_employee) ?>" placeholder="직원명">
                        </div>
                        <div class="col-md-2 mx-2">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary btn-sm me-2">
                                    <i class="bi bi-search"></i> 검색
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="resetSearch()">
                                    <i class="bi bi-arrow-clockwise"></i> 초기화
                                </button>
                            </div>
                        </div>
                </div>
            </form>

            <!-- 결과 요약 -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <strong>검색 결과:</strong> 총 <?= number_format($total_count) ?>건 
                        (<?= $page ?>페이지 / <?= $total_pages ?>페이지)
                    </div>
                </div>
            </div>

            <!-- 할일 목록 테이블 -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                             <th>번호</th>
                             <th>날짜</th>
                             <th>직원명</th>
                             <th>부서</th>
                             <th>할일 개수</th>
                             <th>완료율</th>
                             <th>지연 할일</th>
                             <th>메모</th>
                             <th>생성일</th>
                             <th>관리</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tasks)): ?>
                                                 <tr>
                             <td colspan="10" class="text-center py-4">
                                <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                                <p class="text-muted mt-2">검색 결과가 없습니다.</p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($tasks as $task): 
                            // JSON 데이터 파싱
                            $task_items = json_decode($task['tasks'], true) ?? [];
                            $total_tasks = count($task_items);
                            $completed_tasks = 0;
                            
                                                         $elapsed_tasks = 0;
                             foreach ($task_items as $item) {
                                 if ($item['is_completed'] ?? false) {
                                     $completed_tasks++;
                                 }
                                 // 경과일이 있는 할일 카운트
                                 if (!empty($item['original_date'])) {
                                     $original_date = new DateTime($item['original_date']);
                                     $today = new DateTime();
                                     $elapsed = $today->diff($original_date)->days;
                                     if ($elapsed > 0) {
                                         $elapsed_tasks++;
                                     }
                                 }
                             }
                            
                            $completion_rate = $total_tasks > 0 ? round(($completed_tasks / $total_tasks) * 100) : 0;
                        ?>
                        <tr class="task-row-clickable" data-task-num="<?= $task['num'] ?>" style="cursor: pointer;">
                            <td><?= $task['num'] ?></td>
                            <td><?= date('Y-m-d', strtotime($task['task_date'])) ?></td>
                            <td><?= htmlspecialchars($task['employee_name']) ?></td>
                            <td>
                                <span class="badge bg-secondary"><?= htmlspecialchars($task['department'] ?? '-') ?></span>
                            </td>
                            <td>
                                <span class="badge bg-primary"><?= $total_tasks ?>개</span>
                            </td>
                                                         <td>
                                 <?php if ($completion_rate == 100): ?>
                                     <span class="badge bg-success"><?= $completion_rate ?>%</span>
                                 <?php elseif ($completion_rate >= 50): ?>
                                     <span class="badge bg-warning"><?= $completion_rate ?>%</span>
                                 <?php else: ?>
                                     <span class="badge bg-danger"><?= $completion_rate ?>%</span>
                                 <?php endif; ?>
                             </td>
                             <td>
                                 <?php if ($elapsed_tasks > 0): ?>
                                     <span class="badge bg-danger"><?= $elapsed_tasks ?>개</span>
                                 <?php else: ?>
                                     <span class="badge bg-success">0개</span>
                                 <?php endif; ?>
                             </td>
                            <td>
                                <?php if (!empty($task['memo'])): ?>
                                    <span class="text-truncate d-inline-block" style="max-width: 150px;" title="<?= htmlspecialchars($task['memo']) ?>">
                                        <?= htmlspecialchars($task['memo']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('Y-m-d H:i', strtotime($task['created_at'])) ?></td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">                                    
                                    <button type="button" class="btn btn-outline-dark" onclick="editTask(<?= $task['num'] ?>)" title="수정">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-success" onclick="copyTask(<?= $task['num'] ?>)" title="복사">
                                        <i class="bi bi-files"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" onclick="deleteTask(<?= $task['num'] ?>, '<?= $task['employee_name'] ?>')" title="삭제">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- 페이지네이션 -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="페이지 네비게이션">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    if ($start_page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>">1</a>
                    </li>
                    <?php if ($start_page > 2): ?>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                    <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                    <?php endif; ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $total_pages])) ?>"><?= $total_pages ?></a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// 페이지 로딩 반드시 있어야 화면이 나온다.
$(document).ready(function(){	
    var loader = document.getElementById('loadingOverlay');
    if(loader) {
        loader.style.display = 'none';
    }
});
</script>

<script>
// 새 할일 등록
function openNewTask() {
    window.open('write_form.php?mode=insert&tablename=employee_tasks', 'newTask', 'width=1200,height=800,scrollbars=yes,resizable=yes');
}

// 할일 보기
function viewTask(num) {
    window.open('write_form.php?mode=view&num=' + num + '&tablename=employee_tasks', 'viewTask', 'width=1200,height=800,scrollbars=yes,resizable=yes');
}

// 할일 수정
function editTask(num) {
    window.open('write_form.php?mode=modify&num=' + num + '&tablename=employee_tasks', 'editTask', 'width=1200,height=800,scrollbars=yes,resizable=yes');
}

// 할일 복사
function copyTask(num) {
    window.open('write_form.php?mode=copy&num=' + num + '&tablename=employee_tasks', 'copyTask', 'width=1200,height=800,scrollbars=yes,resizable=yes');
}

// 할일 삭제
function deleteTask(num, employee_name) {
    // 관리자 레벨이거나 자신이 작성한 할일만 삭제 가능
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
                            location.reload();
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

// 검색 초기화
function resetSearch() {
    const today = new Date().toISOString().split('T')[0];
    const oneMonthAgo = new Date();
    oneMonthAgo.setMonth(oneMonthAgo.getMonth() - 1);
    const oneMonthAgoStr = oneMonthAgo.toISOString().split('T')[0];
    
    window.location.href = 'task_list.php?search_date_start=' + oneMonthAgoStr + '&search_date_end=' + today;
}

// 페이지 로드 시 팝업 창 닫기 감지
window.addEventListener('beforeunload', function() {
    // 팝업 창이 닫힐 때 부모 창 새로고침
    if (window.opener && !window.opener.closed) {
        window.opener.location.reload();
    }
});

// 테이블 행 클릭 이벤트 처리
$(document).ready(function() {
    // 행 클릭 시 view 모드로 이동
    $(document).on('click', '.task-row-clickable', function(e) {
        // 버튼 클릭 시에는 행 클릭 이벤트를 무시
        if ($(e.target).closest('.btn-group, button').length > 0) {
            return;
        }
        
        const taskNum = $(this).data('task-num');
        if (taskNum) {
            viewTask(taskNum);
        }
    });
    
    // 행에 호버 효과 추가
    $('.task-row-clickable').hover(
        function() {
            $(this).addClass('table-hover');
        },
        function() {
            $(this).removeClass('table-hover');
        }
    );
});
</script>
</body>
</html> 