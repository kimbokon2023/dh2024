<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

$month = $_POST['month'];
$year = $_POST['year'];

// Ensure month is always two digits (e.g., 01, 02, ... 12)
$plan_month = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);

$todo_data = array();
$leave_data = array();
$holiday_data = array();
$as_data = array();  // as 데이터를 저장할 배열

// Fetch data based on orderdate
try {    
	$stmh = $pdo->query("SELECT num, orderdate, towhom, reply, deadline, work_status, title, title_after, first_writer, update_log, searchtag 
                             FROM " . $DB . ".todos_work 
                             WHERE (is_deleted IS NULL or is_deleted = '0') 
                             AND MONTH(orderdate) = $month 
                             AND YEAR(orderdate) = $year");        

    while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
        array_push($todo_data, $row);
    }
} catch (PDOException $Exception) {
    print "오류: ".$Exception->getMessage();
}
// Fetch data based on orderdate
// try {
    // if($level == '1') { // 관리자이면 전부 다 보여주고,
        // $stmh = $pdo->query("SELECT num, orderdate, towhom, reply, deadline, work_status, title, title_after, first_writer, update_log, searchtag 
                             // FROM " . $DB . ".todos_work 
                             // WHERE (is_deleted IS NULL or is_deleted = '0') 
                             // AND MONTH(orderdate) = $month 
                             // AND YEAR(orderdate) = $year");    
    // } else {
        // $stmh = $pdo->query("SELECT num, orderdate, towhom, reply, deadline, work_status, title, title_after, first_writer, update_log, searchtag 
                             // FROM " . $DB . ".todos_work 
                             // WHERE (is_deleted IS NULL or is_deleted = '0') AND first_writer = '$user_name'  
                             // AND MONTH(orderdate) = $month 
                             // AND YEAR(orderdate) = $year");  
    // }

    // while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
        // array_push($todo_data, $row);
    // }
// } catch (PDOException $Exception) {
    // print "오류: ".$Exception->getMessage();
// }

// holiday 데이터 가져오기
$stmh = $pdo->query("SELECT num, startdate, enddate, comment 
                     FROM " . $DB . ".holiday 
                     WHERE is_deleted IS NULL 
                     AND ((MONTH(startdate) = $month AND YEAR(startdate) = $year) 
                     OR (MONTH(enddate) = $month AND YEAR(enddate) = $year))");

while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
    array_push($holiday_data, $row);
}

// as 데이터 가져오기
$stmh = $pdo->query("SELECT num, as_check, asday, aswriter, asorderman, as_step, asordermantel, asfee, asfee_estimate, aslist, as_refer, asproday, setdate, asman, asendday, asresult, ashistory, secondord, secondordman, secondordmantel, workplaceCode, workplacename, searchtag, update_log, note, address, payment, demandDate, spotman, spotmantel, payman, paydate
                     FROM " . $DB . ".as 
                     WHERE is_deleted IS NULL 
                     AND (MONTH(asday) = $month AND YEAR(asday) = $year)");

while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
    array_push($as_data, $row);
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
    "plan_month" => $plan_month
);

echo(json_encode($response, JSON_UNESCAPED_UNICODE));
?>
