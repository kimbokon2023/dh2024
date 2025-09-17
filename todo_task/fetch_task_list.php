<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");
// 검색 조건
$getDate = $_REQUEST['sendDate'] ?? '';
$search_date_start = $getDate ?? '';
$search_date_end = $getDate ?? '';

// echo '$getDate : ' . $getDate;

// 페이지네이션
$page = $_GET['page'] ?? 1;
$limit = 100;
$offset = ($page - 1) * $limit;

// 직원 명단 조회 (task_list.php와 동일한 조건)
$member_sql = "SELECT name FROM {$DB}.member WHERE dailyworkcheck = '작성' AND (quitDate IS NULL OR quitDate = '0000-00-00')";
$member_stmt = $pdo->prepare($member_sql);
$member_stmt->execute();
$all_members = $member_stmt->fetchAll(PDO::FETCH_COLUMN);

// 미완료 작업을 포함한 모든 작업 데이터를 수집할 배열
$all_tasks = [];

if (!empty($search_date_start) && !empty($search_date_end)) {
    // 1. 먼저 해당 날짜의 작업들을 조회
    $where_conditions = ["(is_deleted = 'N' OR is_deleted IS NULL)"];
    $where_conditions[] = "task_date BETWEEN :search_date_start AND :search_date_end";
    $params = [
        ':search_date_start' => $search_date_start,
        ':search_date_end' => $search_date_end
    ];

    $where_clause = implode(' AND ', $where_conditions);
    $sql = "SELECT * FROM {$DB}.employee_tasks WHERE {$where_clause} ORDER BY task_date DESC, created_at DESC";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $today_tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 해당 날짜에 작업이 있는 직원들 목록
    $employees_with_today_tasks = [];
    foreach ($today_tasks as $task) {
        $employees_with_today_tasks[] = $task['employee_name'];
    }
    $employees_with_today_tasks = array_unique($employees_with_today_tasks);

    // 모든 작업에 추가
    $all_tasks = array_merge($all_tasks, $today_tasks);

    // 2. 해당 날짜에 작업이 없는 직원들의 이전 미완료 작업 조회
    $employees_without_today_tasks = array_diff($all_members, $employees_with_today_tasks);

    foreach ($employees_without_today_tasks as $employee_name) {
        // 해당 직원의 이전 날짜 작업들을 날짜 역순으로 조회
        $prev_sql = "SELECT * FROM {$DB}.employee_tasks
                     WHERE employee_name = :employee_name
                     AND task_date < :search_date
                     AND (is_deleted = 'N' OR is_deleted IS NULL)
                     ORDER BY task_date DESC
                     LIMIT 200"; // 최근 200개 작업만 확인

        $prev_stmt = $pdo->prepare($prev_sql);
        $prev_stmt->bindValue(':employee_name', $employee_name);
        $prev_stmt->bindValue(':search_date', $search_date_start);
        $prev_stmt->execute();
        $prev_tasks = $prev_stmt->fetchAll(PDO::FETCH_ASSOC);

        // 각 이전 작업에서 미완료 항목이 있는지 확인
        foreach ($prev_tasks as $prev_task) {
            $task_items = json_decode($prev_task['tasks'], true) ?? [];
            $has_incomplete = false;

            foreach ($task_items as $item) {
                if (!($item['is_completed'] ?? false)) {
                    $has_incomplete = true;
                    break;
                }
            }

            // 미완료 작업이 있으면 해당 작업을 추가하고 이 직원의 검색 중단
            if ($has_incomplete) {
                $all_tasks[] = $prev_task;
                break;
            }
        }
    }
} else {
    // 날짜 조건이 없는 경우 기존 로직 사용
    $where_conditions = ["(is_deleted = 'N' OR is_deleted IS NULL)"];
    $where_clause = implode(' AND ', $where_conditions);

    $sql = "SELECT * FROM {$DB}.employee_tasks WHERE {$where_clause} ORDER BY task_date DESC, created_at DESC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $all_tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$tasks = $all_tasks;

// 모든 할일 항목을 하나의 배열로 만들기
$all_task_rows = [];
foreach ($tasks as $task) {
    // JSON 데이터 파싱
    $task_items = json_decode($task['tasks'], true) ?? [];
    
    // 할일이 없으면 기본 행 하나 표시
    if (empty($task_items)) {
        $task_items = [['task_content' => '', 'is_completed' => false, 'completion_date' => '', 'original_date' => '']];
    }
    
    // 각 할일 항목을 개별 행으로 만들기
    foreach ($task_items as $index => $item) {
        $task_date = $task['task_date'] ?? '';
        $is_completed = $item['is_completed'] ?? false;

        // 이전 날짜의 완료된 작업은 제외
        if (!empty($search_date_start) && $task_date < $search_date_start && $is_completed) {
            continue;
        }

        $all_task_rows[] = [
            'employee_name' => $task['employee_name'],
            'department' => $task['department'] ?? '',
            'memo' => $task['memo'] ?? '',
            'created_at' => $task['created_at'],
            'task_date' => $task['task_date'] ?? '',
            'num' => $task['num'],
            'task_item' => $item
        ];
    }
}
?>


<!-- 할일 목록 테이블 -->
<div class="row d-flex justify-content-center w-75">
<div class="table-responsive">
    <h4 class="mx-1 text-center">  <?=$search_date_end ?> (<?php
        $day = date('w', strtotime($search_date_end));
        $days = array('일', '월', '화', '수', '목', '금', '토');
        echo $days[$day];
    ?>) </h4> <br>
    <table class="table table-striped table-hover">
        <thead class="table-danger">
            <tr>                
                <th class="text-center">성명</th>
                <th class="text-center">부서</th>
                <th class="text-center">할일 내용</th>
                <th class="text-center">완료 여부</th>
                <th class="text-center">완료일</th>
                <th class="text-center">경과일</th>
                <th class="text-center">메모</th>
                <th class="text-center">작업일</th>
                <th class="text-center">생성일</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($all_task_rows)): ?>
                <tr>
                    <td colspan="11" class="text-center py-4">
                        <i class="bi bi-inbox text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2">검색 결과가 없습니다.</p>
                    </td>
                </tr>
            <?php else: ?>
                <?php 
                $prev_employee = '';
                $prev_department = '';
                
                foreach ($all_task_rows as $index => $row): 
                    $item = $row['task_item'];
                    $is_completed = $item['is_completed'] ?? false;
                    $completion_date = $item['completion_date'] ?? '';
                    $original_date = $item['original_date'] ?? '';

                    // 작업일 확인 (이전 날짜의 미완료 작업인지 판단)
                    $task_date = $row['task_date'] ?? '';

                    $is_previous_incomplete = false;
                    if (!empty($search_date_start) && $task_date < $search_date_start && !$is_completed) {
                        $is_previous_incomplete = true;
                    }

                    // 경과일 계산
                    $elapsed_days = '';
                    if (!empty($original_date)) {
                        $original_date_obj = new DateTime($original_date);
                        $today = new DateTime();
                        $elapsed = $today->diff($original_date_obj)->days;
                        if ($elapsed > 0) {
                            $elapsed_days = $elapsed . '일';
                        }
                    }

                    // 같은 이름과 부서인지 확인
                    $current_employee = $row['employee_name'];
                    $current_department = $row['department'];
                    $show_name = ($current_employee !== $prev_employee || $current_department !== $prev_department);

                    // 이전 값 업데이트
                    $prev_employee = $current_employee;
                    $prev_department = $current_department;
                ?>
                <tr class="task-row-clickable <?= $is_previous_incomplete ? 'table-warning' : '' ?>" data-task-num="<?= $row['num'] ?>" style="cursor: pointer;" <?= $is_previous_incomplete ? 'title="이전 날짜의 미완료 작업"' : '' ?>>
                    <td class="text-center">
                        <?php if ($show_name): ?>
                            <?= htmlspecialchars($row['employee_name']) ?>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if ($show_name): ?>
                            <span class="text-secondary"><?= htmlspecialchars($row['department'] ?? '-') ?></span>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-start">
                        <?php if (!empty($item['task_content'])): ?>
                            <span class="text-truncate d-inline-block" style="max-width: 200px;" title="<?= htmlspecialchars($item['task_content']) ?>">
                                <?= htmlspecialchars($item['task_content']) ?>
                            </span>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <div class="form-check d-flex justify-content-center">
                            <input class="form-check-input" type="checkbox" <?= $is_completed ? 'checked' : '' ?> disabled>
                        </div>
                    </td>
                    <td class="text-center">
                        <?php if (!empty($completion_date)): ?>
                            <?= date('Y-m-d', strtotime($completion_date)) ?>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if (!empty($elapsed_days)): ?>
                            <span class="badge bg-warning"><?= $elapsed_days ?></span>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-start">
                        <?php if (!empty($row['memo'])): ?>
                            <span class="text-truncate d-inline-block" style="max-width: 150px;" title="<?= htmlspecialchars($row['memo']) ?>">
                                <?= htmlspecialchars($row['memo']) ?>
                            </span>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if ($is_previous_incomplete): ?>
                            <span class="badge bg-warning text-dark"><?= $task_date ?></span>
                        <?php else: ?>
                            <?= $task_date ?>
                        <?php endif; ?>
                    </td>
                    <td class="text-center"><?= date('Y-m-d H:i', strtotime($row['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</div>
