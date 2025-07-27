<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

$tablename = isset($_REQUEST['tablename']) ? $_REQUEST['tablename'] : 'todos_work';  
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  
$num = isset($_REQUEST['num']) ? $_REQUEST['num'] : '';  
$first_writer = isset($_REQUEST['first_writer']) ? $_REQUEST['first_writer'] : $user_name;  
$monthlyPlan = isset($_REQUEST['monthlyPlan']) ? $_REQUEST['monthlyPlan'] : '';  
$plan_month = isset($_REQUEST['plan_month']) ? $_REQUEST['plan_month'] . '-01' : date("Y-m-01");

header("monthlyPlan-Type: application/json");  // Use JSON monthlyPlan type

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

// searchtag를 구성하는 문자열 결합 (필요한 필드 모두 포함)
$searchtag = $first_writer . ' ' . $update_log. ' ' . $monthlyPlan;

// 월간 일정에 대한 처리
if ($mode !== "delete") {
	try {
		// 해당 월의 계획이 있는지 먼저 확인
		$sql = "SELECT * FROM {$DB}.monthly_work WHERE plan_month = ? AND first_writer = ? ";
		$stmh = $pdo->prepare($sql);
		$stmh->bindValue(1, $plan_month, PDO::PARAM_STR);
		$stmh->bindValue(2, $first_writer, PDO::PARAM_STR);
		$stmh->execute();
		$row = $stmh->fetch(PDO::FETCH_ASSOC);

		// 데이터가 존재하면 UPDATE
		if ($row) {
			$pdo->beginTransaction();
			$sql = "UPDATE {$DB}.monthly_work SET 
					monthlyPlan = ?, searchtag = ?, plan_month =?, first_writer =? 
					WHERE plan_month = ? AND first_writer = ? LIMIT 1";
		} 
		// 데이터가 없으면 INSERT
		else {
			// 트랜잭션 시작
			$pdo->beginTransaction();
			$sql = "INSERT INTO {$DB}.monthly_work (plan_month, monthlyPlan, first_writer, searchtag)
					VALUES (?, ?, ?, ?)";
		}

		$stmh = $pdo->prepare($sql);

		// Bind parameters (INSERT와 UPDATE의 차이에 맞게 바인딩)
		if ($row) {
			// UPDATE용 바인딩
			$stmh->bindValue(1, $monthlyPlan, PDO::PARAM_STR);        
			$stmh->bindValue(2, $searchtag, PDO::PARAM_STR);
			$stmh->bindValue(3, $plan_month, PDO::PARAM_STR);
			$stmh->bindValue(4, $first_writer, PDO::PARAM_STR);
			$stmh->bindValue(5, $plan_month, PDO::PARAM_STR);
			$stmh->bindValue(6, $first_writer, PDO::PARAM_STR);
		} else {
			// INSERT용 바인딩
			$stmh->bindValue(1, $plan_month, PDO::PARAM_STR);
			$stmh->bindValue(2, $monthlyPlan, PDO::PARAM_STR);
			$stmh->bindValue(3, $first_writer, PDO::PARAM_STR);        
			$stmh->bindValue(4, $searchtag, PDO::PARAM_STR);
		}

		$stmh->execute();
		$pdo->commit(); // 트랜잭션 완료
		} catch (PDOException $Exception) {
			$pdo->rollBack();
			echo json_encode(["error" => $Exception->getMessage()], JSON_UNESCAPED_UNICODE);
			exit;
		}
}

include $_SERVER['DOCUMENT_ROOT'] . "/todo_work/_request.php"; // Ensure this file properly sets all needed variables

// searchtag를 구성하는 문자열 결합 (필요한 필드 모두 포함)
$searchtag = $orderdate . ' ' . $towhom . ' ' . $reply . ' ' . $deadline . ' ' . $work_status . ' ' . $title . ' ' . $title_after . ' ' . $first_writer . ' ' . $update_log;


if ($mode == "update") {
    $update_log = date("Y-m-d H:i:s") . " - " . $_SESSION["name"] . " " . $update_log . "&#10";
    try {
        $pdo->beginTransaction();
        $sql = "UPDATE " . $DB . "." . $tablename . " SET 
                orderdate = ?, towhom = ?, reply = ?, deadline = ?, work_status = ?, title = ?, title_after = ?, 
                first_writer = ?, update_log = ?, searchtag = ?
                WHERE num = ? LIMIT 1";

        $stmh = $pdo->prepare($sql);

        $stmh->bindValue(1, $orderdate, PDO::PARAM_STR);
        $stmh->bindValue(2, $towhom, PDO::PARAM_STR);
        $stmh->bindValue(3, $reply, PDO::PARAM_STR);
        $stmh->bindValue(4, $deadline, PDO::PARAM_STR);
        $stmh->bindValue(5, $work_status, PDO::PARAM_STR);
        $stmh->bindValue(6, $title, PDO::PARAM_STR);
        $stmh->bindValue(7, $title_after, PDO::PARAM_STR);
        $stmh->bindValue(8, $first_writer, PDO::PARAM_STR);
        $stmh->bindValue(9, $update_log, PDO::PARAM_STR);
        $stmh->bindValue(10, $searchtag, PDO::PARAM_STR);
        $stmh->bindValue(11, $num, PDO::PARAM_INT);

        $stmh->execute();
        $pdo->commit();
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        echo json_encode(["error" => $Exception->getMessage()], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if ($mode == "insert" || $mode == '' || $mode == null) {
    $update_log = date("Y-m-d H:i:s") . " - " . $_SESSION["name"] . " " . $update_log . "&#10";
    try {
        $pdo->beginTransaction();
	$sql = "INSERT INTO {$DB}.$tablename (
                orderdate, towhom, reply, deadline, work_status, title, title_after, first_writer, update_log, searchtag
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmh = $pdo->prepare($sql);

        $stmh->bindValue(1, $orderdate, PDO::PARAM_STR);
        $stmh->bindValue(2, $towhom, PDO::PARAM_STR);
        $stmh->bindValue(3, $reply, PDO::PARAM_STR);
        $stmh->bindValue(4, $deadline, PDO::PARAM_STR);
        $stmh->bindValue(5, $work_status, PDO::PARAM_STR);
        $stmh->bindValue(6, $title, PDO::PARAM_STR);
        $stmh->bindValue(7, $title_after, PDO::PARAM_STR);
        $stmh->bindValue(8, $first_writer, PDO::PARAM_STR);
        $stmh->bindValue(9, $update_log, PDO::PARAM_STR);
        $stmh->bindValue(10, $searchtag, PDO::PARAM_STR);

        $stmh->execute();
        $pdo->commit();
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        echo json_encode(["error" => $Exception->getMessage()], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if ($mode == "delete") {
    try {
        $pdo->beginTransaction();
        $sql = "UPDATE " .  $DB . "." . $tablename . " SET is_deleted=1 WHERE num = ?";  
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $num, PDO::PARAM_INT);      
        $stmh->execute();
        $pdo->commit();
    } catch (PDOException $ex) {
        $pdo->rollBack();
        echo json_encode(["error" => $ex->getMessage()], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$data = [   
    'num' => $num,
    'title' => $title,
    'title_after' => $title_after,
    'orderdate' => $orderdate,
    'mode' => $mode
];

echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>
