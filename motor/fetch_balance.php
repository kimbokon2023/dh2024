<?php
// fetch_balance.php
function fetch_balances($DB, $fromdate, $todate) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
    $pdo = db_connect();

    // 현재 날짜
    $currentDate = date("Y-m-d");

    // fromdate 또는 todate가 빈 문자열이거나 null인 경우 처리
    if ($fromdate === "" || $fromdate === null || $todate === "" || $todate === null) {
        $fromdate = date("Y-m-01");
        $todate = $currentDate;
    }

    $initialBalances = [];

    // 이월 잔액을 직접 계산하는 로직
    $lastMonthEnd = date("Y-m-t", strtotime($fromdate . " -1 month"));

    // 매출 내역 조회
    $salesBeforeSql = "
        SELECT secondordnum, SUM(CAST(REPLACE(totalprice, ',', '') AS SIGNED)) AS total_sales 
        FROM {$DB}.motor
        WHERE deadline <= :lastMonthEnd AND (is_deleted IS NULL or is_deleted = 0)  
        GROUP BY secondordnum
    ";
    $paymentBeforeSql = "
        SELECT secondordnum, SUM(CAST(REPLACE(amount, ',', '') AS SIGNED)) AS total_payment 
        FROM {$DB}.account
        WHERE registDate <= :lastMonthEnd AND (is_deleted IS NULL or is_deleted = 0)   AND content = '거래처 수금'
        GROUP BY secondordnum
    ";

    // 전월까지의 매출과 수금 데이터 가져오기
    $salesBeforeStmt = $pdo->prepare($salesBeforeSql);
    $salesBeforeStmt->execute([':lastMonthEnd' => $lastMonthEnd]);
    $salesBeforeData = $salesBeforeStmt->fetchAll(PDO::FETCH_ASSOC);

    $paymentBeforeStmt = $pdo->prepare($paymentBeforeSql);
    $paymentBeforeStmt->execute([':lastMonthEnd' => $lastMonthEnd]);
    $paymentBeforeData = $paymentBeforeStmt->fetchAll(PDO::FETCH_ASSOC);

    // 이월 잔액 계산
    foreach ($salesBeforeData as $row) {
        $secondordnum = $row['secondordnum'];
        $total_sales_before = round((float)$row['total_sales'] * 1.1,2); // 부가세 포함

        if (!isset($initialBalances[$secondordnum])) {
            $initialBalances[$secondordnum] = 0;
        }

        $initialBalances[$secondordnum] += $total_sales_before;
    }

    foreach ($paymentBeforeData as $row) {
        $secondordnum = $row['secondordnum'];
        $total_payment_before = (float)$row['total_payment'];

        if (!isset($initialBalances[$secondordnum])) {
            $initialBalances[$secondordnum] = 0;
        }

        $initialBalances[$secondordnum] -= $total_payment_before;
    }

	// 당월 매출 내역 가져오기
	$salesSql = "
		SELECT secondordnum, totalprice 
		FROM {$DB}.motor
		WHERE (deadline BETWEEN date('$fromdate') AND date('$todate')) AND  (is_deleted IS NULL or is_deleted = 0)  
	";

	$salesStmt = $pdo->prepare($salesSql);
	$salesStmt->execute();
	$salesData = $salesStmt->fetchAll(PDO::FETCH_ASSOC);

	$salesResults = [];
	foreach ($salesData as $row) {
		$secondordnum = $row['secondordnum'];
		$total_sales = (float)str_replace(',', '', $row['totalprice']);  // 콤마 제거 후 매출액 가져옴

		if (!isset($salesResults[$secondordnum])) {
			$salesResults[$secondordnum] = 0;
		}
		$salesResults[$secondordnum] += round($total_sales * 1.1,2) ;  // 부가세 포함
	}

    // 당월 수금 내역 가져오기 (account 테이블)
    $paymentSql = "
        SELECT secondordnum, SUM(CAST(REPLACE(amount, ',', '') AS SIGNED)) AS total_payment
        FROM {$DB}.account
        WHERE registDate BETWEEN date('$fromdate')  AND date('$todate') 
        AND (is_deleted IS NULL or is_deleted = 0)  AND content = '거래처 수금'
        GROUP BY secondordnum
    ";

    $paymentStmt = $pdo->prepare($paymentSql);
    $paymentStmt->execute();
    $paymentData = $paymentStmt->fetchAll(PDO::FETCH_ASSOC);

    $paymentResults = [];
    foreach ($paymentData as $row) {
        $secondordnum = $row['secondordnum'];
        $total_payment = (float)$row['total_payment'];

        if (!isset($paymentResults[$secondordnum])) {
            $paymentResults[$secondordnum] = 0;
        }
        $paymentResults[$secondordnum] += $total_payment;
    }

    // 모든 거래처 목록을 생성 (매출, 기초채권)
    $allResults = array_unique(array_merge(array_keys($salesResults), array_keys($initialBalances)));

    // 최종 잔액 계산
    $balances = [];
    foreach ($allResults as $secondordnum) {
        $initialReceivable = isset($initialBalances[$secondordnum]) ? $initialBalances[$secondordnum] : 0;
        $total_sales = isset($salesResults[$secondordnum]) ? $salesResults[$secondordnum]  : 0; // 부가세 포함
        $total_payment = isset($paymentResults[$secondordnum]) ? $paymentResults[$secondordnum] : 0;

        // 최종 잔액
        $balances[$secondordnum] = $initialReceivable + $total_sales - $total_payment;
    }

    return $balances;
}

?>
