<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

$month = $_POST['month'];
$year = $_POST['year'];
$member = isset($_POST['member']) ? $_POST['member'] : (isset($_GET['member']) ? $_GET['member'] : null);

// Ensure month is always two digits (e.g., 01, 02, ... 12)
$plan_month = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);

$todo_data = array();
$leave_data = array();
$holiday_data = array();
$as_data = array();  // as 데이터를 저장할 배열

// $member가 있으면 해당 사용자의 할일 데이터만 가져오기
try {
    if($member && !empty($member)) {
        $sql = "SELECT num, task_date, employee_name, department, memo, tasks, created_at, created_by, updated_at, updated_by 
                FROM " . $DB . ".employee_tasks 
                WHERE (is_deleted IS NULL or is_deleted = 'N') 
                AND MONTH(task_date) = :month 
                AND YEAR(task_date) = :year
                AND employee_name = :member";
        $stmh = $pdo->prepare($sql);
        $stmh->bindParam(':month', $month, PDO::PARAM_INT);
        $stmh->bindParam(':year', $year, PDO::PARAM_INT);
        $stmh->bindParam(':member', $member, PDO::PARAM_STR);
    } else {    
        $sql = "SELECT num, task_date, employee_name, department, memo, tasks, created_at, created_by, updated_at, updated_by 
                FROM " . $DB . ".employee_tasks 
                WHERE (is_deleted IS NULL or is_deleted = 'N') 
                AND MONTH(task_date) = :month 
                AND YEAR(task_date) = :year";
        $stmh = $pdo->prepare($sql);
        $stmh->bindParam(':month', $month, PDO::PARAM_INT);
        $stmh->bindParam(':year', $year, PDO::PARAM_INT);
    }
    
    $stmh->execute();
    while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
        // tasks JSON 데이터 필터링
        if (!empty($row['tasks'])) {
            $tasks = json_decode($row['tasks'], true);
            if (is_array($tasks)) {
                $filtered_tasks = array();
                $task_date = $row['task_date']; // 현재 행의 task_date
                
                foreach ($tasks as $task) {
                    $should_include = true;
                    
                    // is_completed가 true인 경우 추가 검사
                    if (isset($task['is_completed']) && $task['is_completed'] === true) {
                        // completion_date가 있고 task_date보다 이전이면 제외
                        if (isset($task['completion_date']) && !empty($task['completion_date'])) {
                            $completion_date = $task['completion_date'];
                            if ($completion_date < $task_date) {
                                $should_include = false; // 과거에 완료된 작업은 제외
                            }
                        } else {
                            // completion_date가 없는 완료된 작업은 제외
                            $should_include = false;
                        }
                    }
                    
                    if ($should_include) {
                        array_push($filtered_tasks, $task);
                    }
                }
                // 필터링된 tasks를 다시 JSON으로 인코딩
                $row['tasks'] = json_encode($filtered_tasks, JSON_UNESCAPED_UNICODE);
            }
        }
        array_push($todo_data, $row);
    }
} catch (PDOException $Exception) {
    print "오류: ".$Exception->getMessage();
}

// holiday 데이터 가져오기
$stmh = $pdo->query("SELECT num, startdate, enddate, comment 
                     FROM " . $DB . ".holiday 
                     WHERE is_deleted IS NULL 
                     AND ((MONTH(startdate) = $month AND YEAR(startdate) = $year) 
                     OR (MONTH(enddate) = $month AND YEAR(enddate) = $year))");

while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
    array_push($holiday_data, $row);
}

// Combine all the data into a single response
$response = array(
    "todo_data" => $todo_data,
    "leave_data" => [],    
    "holiday_data" => $holiday_data,
    "as_data" => $as_data,  // as 데이터를 추가
    "level" => $level,
    "month" => $month,
    "year" => $year,
    "plan_month" => $plan_month,
    "debug" => array(
        "member" => $member,
        "member_filtered" => !empty($member),
        "todo_count" => count($todo_data)
    )
);

echo(json_encode($response, JSON_UNESCAPED_UNICODE));
?>
