<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");

function fetchMotorDataByDate($pdo, $month, $year, $currentDate = null) {
	require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");
	if(!isset($DB))
		  $DB = 'dbchandj';
    $data_motor = array();
    try {		
        // 기본 SQL 쿼리
        $sql = "SELECT workplacename, deadline,  secondord, num, outputdate, status, orderlist, controllerlist, fabriclist, accessorieslist  
                FROM {$DB}.motor 
                WHERE is_deleted IS NULL 
                AND MONTH(deadline) = :month 
                AND YEAR(deadline) = :year" ;

        // 현재의 달이 선택된 경우, 오늘 날짜까지의 데이터만 가져오도록 조건 추가
        if ($currentDate) {
            $sql .= " AND deadline <= :currentDate";
        }

        $stmh = $pdo->prepare($sql);
        $stmh->bindParam(':month', $month, PDO::PARAM_INT);
        $stmh->bindParam(':year', $year, PDO::PARAM_INT);
        
        if ($currentDate) {
            $stmh->bindParam(':currentDate', $currentDate);
        }

        $stmh->execute();

        while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
            array_push($data_motor, $row);
        }

        return array("data_motor" => $data_motor);

    } catch (PDOException $Exception) {
        // 에러를 로그로 기록
        error_log("Database Error: " . $Exception->getMessage());
        return array("error" => "Data retrieval failed. Please try again later.");
    }
}

$month = filter_input(INPUT_POST, 'month', FILTER_VALIDATE_INT);
$year = filter_input(INPUT_POST, 'year', FILTER_VALIDATE_INT);

// 현재 날짜를 가져옴
$today = new DateTime();
$currentMonth = $today->format('n'); // 1부터 시작하는 월 번호
$currentYear = $today->format('Y');
$currentDate = null;

// 현재의 달과 년도가 요청된 달과 년도와 동일한 경우
if ($month && $year) {
    if ($month == $currentMonth && $year == $currentYear) {
        $currentDate = $today->format('Y-m-t'); // 오늘의 날짜
    }

    $pdo = db_connect();
    $data_motor = fetchMotorDataByDate($pdo, $month, $year, $currentDate);
    echo(json_encode($data_motor, JSON_UNESCAPED_UNICODE));
} else {
    echo(json_encode(array("error" => "Invalid input data."), JSON_UNESCAPED_UNICODE));
}
?>
