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

// SQL 조건 구성
$where_conditions = ["(is_deleted = 'N' OR is_deleted IS NULL)"];
$params = [];

if (!empty($search_date_start) && !empty($search_date_end)) {
    $where_conditions[] = "task_date BETWEEN :search_date_start AND :search_date_end";
    $params[':search_date_start'] = $search_date_start;
    $params[':search_date_end'] = $search_date_end;
}

$where_clause = implode(' AND ', $where_conditions);

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
        $all_task_rows[] = [
            'employee_name' => $task['employee_name'],
            'department' => $task['department'] ?? '',
            'memo' => $task['memo'] ?? '',
            'created_at' => $task['created_at'],
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
                <th class="text-center">생성일</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($all_task_rows)): ?>
                <tr>
                    <td colspan="10" class="text-center py-4">
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
                <tr class="task-row-clickable" data-task-num="<?= $row['num'] ?>" style="cursor: pointer;">                    
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
                    <td class="text-center"><?= date('Y-m-d H:i', strtotime($row['created_at'])) ?></td>                           
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</div>
