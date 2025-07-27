<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

$month = $_POST['month'];
$year = $_POST['year'];

$todo_data = array();
$leave_data = array();
$monthly_data = array();
$holiday_data = array();

// Fetch data based on orderdate
try {
    $stmh = $pdo->query("SELECT num, orderdate, towhom, reply, deadline, work_status, title, first_writer, update_log, searchtag 
                         FROM " . $DB . ".todos_account 
                         WHERE is_deleted IS NULL 
                         AND MONTH(orderdate) = $month 
                         AND YEAR(orderdate) = $year");

    while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
        array_push($todo_data, $row);
    }
} catch (PDOException $Exception) {
    print "오류: ".$Exception->getMessage();
}

// Fetch monthly schedule data
try {
    $sql = "SELECT num, specialday, yearlyspecialday, title FROM todos_monthly WHERE is_deleted IS NULL ORDER BY specialday ASC";
    $stmh = $pdo->prepare($sql);
    $stmh->execute();
    $monthly_data = $stmh->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $Exception) {
    echo "오류: " . $Exception->getMessage();
    exit;
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
    "monthly_data" => $monthly_data, 
	"holiday_data" => $holiday_data 
);

echo(json_encode($response, JSON_UNESCAPED_UNICODE));
?>



