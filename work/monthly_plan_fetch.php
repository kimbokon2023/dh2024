<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");    
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

$month = isset($_POST['month']) ? $_POST['month'] : date('m'); // 받은 month
$year = isset($_POST['year']) ? $_POST['year'] : date('Y');   // 받은 year

// 해당 월의 첫날 계산
$plan_month = "$year-$month-01"; 

// 멤버 테이블에서 dailyworkcheck가 '작성'인 멤버들을 가져오기
$sql = "SELECT name, numorder FROM member WHERE dailyworkcheck = '작성' ORDER BY numorder ASC";
$stmh = $pdo->prepare($sql);
$stmh->execute();
$members = $stmh->fetchAll(PDO::FETCH_ASSOC);

$response = '';

foreach ($members as $member) {
    $name = $member['name'];
	if($name!=='개발자') {
		$response .= '<div class="d-flex">';
		$response .= '<div class="col-sm-1">' . htmlspecialchars($name) . '</div>';
		$response .= '<div class="col-sm-11">';

		// 각 멤버의 월별 계획 가져오기
		$sql_plan = "SELECT monthlyPlan FROM monthly_work WHERE plan_month = ? AND first_writer = ? AND (is_deleted = 0 OR is_deleted IS NULL) ORDER BY num DESC LIMIT 1";
		$stmh_plan = $pdo->prepare($sql_plan);
		$stmh_plan->bindValue(1, $plan_month, PDO::PARAM_STR);
		$stmh_plan->bindValue(2, $name, PDO::PARAM_STR); // 멤버 이름으로 월간 계획 검색
		$stmh_plan->execute();
		$row_plan = $stmh_plan->fetch(PDO::FETCH_ASSOC);

		if ($row_plan) {
			$monthlyPlan = htmlspecialchars($row_plan['monthlyPlan']); // 계획이 존재하면 가져오기
		} else {
			$monthlyPlan = '이번 달에 등록된 계획이 없습니다.';
		}

		// ul/li로 리스트 출력
		$response .= '<ul><li>' . nl2br($monthlyPlan) . '</li></ul>';
		$response .= '</div>';
		$response .= '</div>';
	}
}

echo $response;
