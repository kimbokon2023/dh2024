<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

$month = $_POST['month'];
$year = $_POST['year'];

$todo_data = array();
$leave_data = array();
$holiday_data = array();
$work_data = array();  // 업무일지
$as_data = array();  // as 데이터를 저장할 배열
$meeting_data = array();  // 회의록 데이터를 저장할 배열

// 출고일 outputdate 기준
try {
    // todos 데이터 가져오기
    $stmh = $pdo->query("SELECT num, orderdate, towhom, reply, deadline, work_status, title, first_writer, update_log, searchtag 
                         FROM " . $DB . ".todos 
                         WHERE is_deleted IS NULL 
                         AND MONTH(orderdate) = $month 
                         AND YEAR(orderdate) = $year");

    while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
        array_push($todo_data, $row);
    }

    // eworks 데이터 가져오기
    $stmh = $pdo->query("SELECT author, al_askdatefrom, al_askdateto, al_item, al_content  
                         FROM " . $DB . ".eworks 
                         WHERE is_deleted IS NULL 
                         AND ((MONTH(al_askdatefrom) = $month AND YEAR(al_askdatefrom) = $year) 
                         OR (MONTH(al_askdateto) = $month AND YEAR(al_askdateto) = $year))");

    while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
        array_push($leave_data, $row);
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


// Fetch data based on orderdate
try {
	
		if($level == '1') // 관리자이면 전부 다 보여주고,
		{
			$stmh = $pdo->query("SELECT num, orderdate, towhom, reply, deadline, work_status, title, title_after, first_writer, update_log, searchtag 
                         FROM " . $DB . ".todos_work 
                         WHERE (is_deleted IS NULL or is_deleted = '0') 
                         AND MONTH(orderdate) = $month 
                         AND YEAR(orderdate) = $year");    
		}
		else
		{
				$stmh = $pdo->query("SELECT num, orderdate, towhom, reply, deadline, work_status, title, title_after, first_writer, update_log, searchtag 
                         FROM " . $DB . ".todos_work 
                         WHERE (is_deleted IS NULL or is_deleted = '0') AND first_writer = '$user_name'  
                         AND MONTH(orderdate) = $month 
                         AND YEAR(orderdate) = $year");  
		}	

    while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
        array_push($work_data, $row);
    }
} catch (PDOException $Exception) {
    print "오류: ".$Exception->getMessage();
}

// as 데이터 가져오기
$stmh = $pdo->query("SELECT num, as_check, asday, aswriter, asorderman, as_step, asordermantel, asfee, asfee_estimate, aslist, as_refer, asproday, setdate, asman, asendday, asresult, ashistory, secondord, secondordman, secondordmantel, workplaceCode, workplacename, searchtag, update_log, note, address, payment, demandDate, spotman, spotmantel, payman, paydate
                     FROM " . $DB . ".as 
                     WHERE is_deleted IS NULL 
                     AND (MONTH(asday) = $month AND YEAR(asday) = $year)");

while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
    array_push($as_data, $row);
}

// 회의록 데이터 가져오기
$stmh = $pdo->query("SELECT num, registration_date, subject, name, regist_day, hit
                     FROM " . $DB . ".meeting 
                     WHERE (MONTH(registration_date) = $month AND YEAR(registration_date) = $year)");

while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
    array_push($meeting_data, $row);
}

    // 응답 데이터 구성
    $response = array(
        "todo_data" => $todo_data,
        "leave_data" => $leave_data,
		"as_data" => $as_data,  // as 데이터를 추가	
		"meeting_data" => $meeting_data,  // 회의록 데이터를 추가	
        "holiday_data" => $holiday_data,  // holiday 데이터 추가
        "work_data" => $work_data,  // work데이터 추가
    );

    echo(json_encode($response, JSON_UNESCAPED_UNICODE));

} catch (PDOException $Exception) {
    print "오류: ".$Exception->getMessage();
}
?>
