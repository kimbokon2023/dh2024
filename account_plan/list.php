<?php
/**
 * 월별 수입/지출 예상 내역서
 *
 * 이 파일은 대한주식회사의 월별 수입/지출 예상 내역서를 생성하고 표시합니다.
 * 사용자 세션을 확인하고 권한에 따라 페이지 접근을 제어합니다.
 * 데이터베이스에서 데이터를 조회하고 계산하여 HTML 테이블 형태로 출력합니다.
 */

require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");

// 세션 확인 및 사용자 권한 검사
if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php");
    exit;
}

// 에러 표시 설정
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// HTML 헤더 로드
include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';
$title_message = '월별 수입/지출 예상내역서';

// 데이터베이스 연결 설정 파일 로드
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");

?>

<link href="css/style.css" rel="stylesheet">
<title><?= $title_message ?></title>
<style>
    /* 테이블에 테두리 추가 */
    #detailTable,
    #detailTable th,
    #detailTable td {
        border: 1px solid black;
        border-collapse: collapse;
    }

    /* 테이블 셀 패딩 조정 */
    #detailTable th,
    #detailTable td {
        padding: 10px;
        text-align: center;
    }
	
	.custom-modal-body {
		max-height: 600px;
		overflow-y: auto;	
	}
</style>
</head>

<body>

<?php
// 사용자 정의 헤더 로드
require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php');

// 미수금 데이터 가져오기
require_once($_SERVER['DOCUMENT_ROOT'] . "/motor/fetch_balance.php");

// 파라미터에서 검색어, 연월 가져오기, 없으면 기본값 설정
$searchKeyword = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
$selectedYearMonth = isset($_REQUEST['yearMonth']) ? $_REQUEST['yearMonth'] : date("Ym");

// 현재 날짜가 속한 연월
$currentYearMonth = date("Ym");

// 선택된 연월의 이전 달 계산
$previousMonth = date("Ym", strtotime($selectedYearMonth . '01 -1 month'));
$previousMonthDisplay = date("m", strtotime($previousMonth . '01')) . "월"; // 이전 달 표시 (예: 03월)

// 기준 연월에 따른 fromdate, todate 설정 (검색 기간)
$startDate = $selectedYearMonth . '01'; // Ym01
$endDate = date("Y-m-t", strtotime($startDate)); // Y-m-마지막날

// 검색 기간 표시를 위해 fromdate와 todate를 Y-m-d 형식으로 변환
$formattedStartDate = date("Y-m-d", strtotime($startDate)); // Y-m-d
$formattedEndDate = date("Y-m-d", strtotime($endDate)); // Y-m-d

// 파라미터에서 모드 가져오기, 없으면 기본값 설정
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';

// 테이블 이름 정의
$phonebookTable = 'phonebook';
$accountTable = 'account';
$accountPlanTable = 'account_plan';
$monthlySalesTable = 'monthly_sales';
$motorTable = 'motor';

/**
 * 데이터베이스 쿼리를 실행하고 결과를 반환합니다.
 *
 * @param PDO $pdo PDO 객체
 * @param string $sql 실행할 SQL 쿼리
 * @param array $params 쿼리에 바인딩할 파라미터 배열 (선택 사항)
 * @return array|false 쿼리 결과 (연관 배열) 또는 실패 시 false
 * @throws PDOException 쿼리 실행 중 오류가 발생한 경우
 */
function executeQuery(PDO $pdo, string $sql, array $params = [])
{
	try {
		$stmt = $pdo->prepare($sql);
		$stmt->execute($params);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	} catch (PDOException $e) {
		// 에러 로깅
		error_log("쿼리 실행 오류: " . $e->getMessage() . "\n쿼리: " . $sql . "\n파라미터: " . print_r($params, true));
		throw $e; // 예외를 다시 던져서 호출자에서 처리하도록 함
	}
}

// 데이터베이스 연결
$pdo = db_connect();
// 이월 잔액을 동적으로 계산하기 위한 로직
$previousMonthStartDate = date("Y-m-01", strtotime($previousMonth . '01')); // 이전 달의 시작일 (YYYY-mm-01)

// 전전달의 29일 구하기
// $previousMonthFromDate = date("Y-m-28", strtotime("-1 month", strtotime($previousMonthFromDate)));
$previousMonthEndDate = date("Y-m-t", strtotime($previousMonth . '01'));

// 전월 매출 데이터 가져오기
$previousMonthSalesSql = "
	SELECT secondordnum, totalprice
	FROM {$motorTable}
	WHERE (deadline BETWEEN :previousMonthStartDate AND :previousMonthEndDate)
	AND (is_deleted IS NULL OR is_deleted = 0)
";
$previousMonthSalesParams = [
	':previousMonthStartDate' => $previousMonthStartDate,
	':previousMonthEndDate' => $previousMonthEndDate
];
$previousMonthSalesData = executeQuery($pdo, $previousMonthSalesSql, $previousMonthSalesParams);

// 전월 매출을 secondordnum 별로 합산
$previousMonthSales = [];
foreach ($previousMonthSalesData as $row) {
	$secondordnum = $row['secondordnum'];
	$totalSalesPrev = (float)str_replace(',', '', $row['totalprice']); // 콤마 제거 후 매출액 가져옴

	if (!isset($previousMonthSales[$secondordnum])) {
		$previousMonthSales[$secondordnum] = 0;
	}
	$previousMonthSales[$secondordnum] += round($totalSalesPrev * 1.1, 2); // 부가세 포함
}


// 당월 매출 데이터 가져오기
$currentMonthSalesSql = "
	SELECT secondordnum, totalprice
	FROM {$motorTable}
	WHERE (deadline BETWEEN :startDate AND :endDate)
	AND (is_deleted IS NULL OR is_deleted = 0)
";
$currentMonthSalesParams = [
	':startDate' => $startDate,
	':endDate' => $endDate
];
$currentMonthSalesData = executeQuery($pdo, $currentMonthSalesSql, $currentMonthSalesParams);

// 당월 매출을 secondordnum 별로 합산
$currentMonthSales = [];
foreach ($currentMonthSalesData as $row) {
	$secondordnum = $row['secondordnum'];
	$totalSales = (float)str_replace(',', '', $row['totalprice']); // 콤마 제거 후 매출액 가져옴

	if (!isset($currentMonthSales[$secondordnum])) {
		$currentMonthSales[$secondordnum] = 0;
	}
	$currentMonthSales[$secondordnum] += round($totalSales * 1.1, 2); // 부가세 포함
}

// 전월까지의 누적 매출 및 수금 데이터를 기반으로 이월 잔액 계산
$initialBalances = [];
$lastMonthEnd = date("Y-m-t", strtotime($startDate . " -1 month")); // 전월 말일

$salesBeforeSql = "
	SELECT secondordnum, SUM(CAST(REPLACE(totalprice, ',', '') AS SIGNED)) AS total_sales
	FROM {$motorTable}
	WHERE deadline <= :lastMonthEnd
	AND (is_deleted IS NULL OR is_deleted = 0)
	GROUP BY secondordnum
";
$salesBeforeParams = [':lastMonthEnd' => $lastMonthEnd];
$salesBeforeData = executeQuery($pdo, $salesBeforeSql, $salesBeforeParams);

$paymentEnddate = date("Y-m-t", strtotime($endDate));
$paymentBeforeSql = "
	SELECT secondordnum, SUM(CAST(REPLACE(amount, ',', '') AS SIGNED)) AS total_payment
	FROM {$accountTable}
	WHERE registDate <= :paymentEnddate
	AND (is_deleted IS NULL OR is_deleted = 0)
	AND content = '거래처 수금'
	GROUP BY secondordnum
";
$paymentBeforeParams = [':paymentEnddate' => $paymentEnddate];
$paymentBeforeData = executeQuery($pdo, $paymentBeforeSql, $paymentBeforeParams);

// 누적 매출 합산
foreach ($salesBeforeData as $row) {
	$secondordnum = $row['secondordnum'];
	$totalSalesBefore = round((float)$row['total_sales'] * 1.1, 2);

	if (!isset($initialBalances[$secondordnum])) {
		$initialBalances[$secondordnum] = 0;
	}

	$initialBalances[$secondordnum] += $totalSalesBefore;
}

// 누적 수금 합산
foreach ($paymentBeforeData as $row) {
	$secondordnum = $row['secondordnum'];
	$totalPaymentBefore = (float)$row['total_payment'];

	if (!isset($initialBalances[$secondordnum])) {
		$initialBalances[$secondordnum] = 0;
	}

	$initialBalances[$secondordnum] -= $totalPaymentBefore;
}

// 거래처별 잔액 계산
$balances = fetch_balances($DB, $startDate, $endDate);

// 모든 거래처 목록 생성 (이월 잔액, 전월 매출, 당월 매출)
$allVendors = array_unique(array_merge(array_keys($initialBalances), array_keys($currentMonthSales), array_keys($previousMonthSales), array_keys($balances)));

// 미수금 계산
$receivables = []; // 미수금 저장 배열
$vendorNames = []; // 거래처 이름 저장 배열

foreach ($allVendors as $secondordnum) {
	// 미수금 계산
	$previousMonthSale = isset($previousMonthSales[$secondordnum]) ? $previousMonthSales[$secondordnum] : 0;
	$currentMonthSale = isset($currentMonthSales[$secondordnum]) ? $currentMonthSales[$secondordnum] : 0;
	$balance = isset($balances[$secondordnum]) ? $balances[$secondordnum] : 0;

	// 미수금은 해당 거래처의 잔액에서 전월 매출과 당월 매출을 뺀 값으로 계산
	$receivableAmount = ($balance > 0) ? $balance - $currentMonthSale - $previousMonthSale : 0;
	$receivables[$secondordnum] = $receivableAmount;

	// 거래처 이름 가져오기
	$vendorNameSql = "SELECT vendor_name FROM {$phonebookTable} WHERE secondordnum = :secondordnum AND (is_deleted IS NULL OR is_deleted = 0)";
	$vendorNameParams = [':secondordnum' => $secondordnum];
	$vendorNameResult = executeQuery($pdo, $vendorNameSql, $vendorNameParams);
	$vendorNames[$secondordnum] = $vendorNameResult[0]['vendor_name'] ?? '';
}

// 현재 잔액 계산서
$currentDate = date("Y-m-d"); // 현재 날짜

// fromdate 또는 todate가 빈 문자열이거나 null인 경우
if (empty($formattedStartDate) || empty($formattedEndDate)) {
	// 현재 월의 1일을 fromdate로 설정
	$formattedStartDate = date("Y-m-01");
	// 전달의 29일 구하기
	// $formattedStartDate = date("Y-m-29", strtotime("-1 month", strtotime($formattedStartDate)));
	$formattedEndDate = $currentDate;
	$Transtodate = $formattedEndDate;
} else {		
	$Transtodate = $formattedEndDate;
}

/**
 * 문자열이 null이 아니고 빈 문자열이 아닌지 확인합니다.
 *
 * @param string|null $str 확인할 문자열
 * @return bool 문자열이 null이 아니고 비어 있지 않으면 true, 그렇지 않으면 false
 */
function checkNull(?string $str): bool
{
	return $str !== null && trim($str) !== '';
}

// account 테이블 쿼리 조건 및 파라미터 설정
$accountQueryConditions = [];
$accountQueryParams = [];

if (checkNull($searchKeyword)) {
	$accountQueryConditions[] = "searchtag LIKE :searchKeyword";
	$accountQueryParams[':searchKeyword'] = "%$searchKeyword%";
}

$accountQueryConditions[] = "registDate BETWEEN :formattedStartDate AND :formattedEndDate";
$accountQueryParams[':formattedStartDate'] = $formattedStartDate;
$accountQueryParams[':formattedEndDate'] = $formattedEndDate;

$accountQueryConditions[] = "(is_deleted = 0 OR is_deleted IS NULL)";

// 수입/지출 구분
$inoutType = isset($_REQUEST['inoutsep_select']) ? $_REQUEST['inoutsep_select'] : '';
if (checkNull($inoutType)) {
	$accountQueryConditions[] = "inoutsep = :inoutType";
	$accountQueryParams[':inoutType'] = $inoutType;
}

// 내용
$contentType = isset($_REQUEST['content_select']) ? $_REQUEST['content_select'] : '';
if (checkNull($contentType)) {
	$accountQueryConditions[] = "content = :contentType";
	$accountQueryParams[':contentType'] = $contentType;
}

// account 테이블에서 데이터 조회
$accountOrder = " ORDER BY registDate ASC, num ASC ";
$accountSql = "SELECT * FROM {$accountTable} WHERE " . implode(' AND ', $accountQueryConditions) . $accountOrder;
$accountData = executeQuery($pdo, $accountSql, $accountQueryParams);

// 수입, 지출을 기반으로 초기 잔액 계산
$initialBalanceSql = "
	SELECT
		SUM(CASE WHEN inoutsep = '수입' THEN REPLACE(amount, ',', '') ELSE 0 END) -
		SUM(CASE WHEN inoutsep = '지출' THEN REPLACE(amount, ',', '') ELSE 0 END) AS balance
	FROM {$accountTable}
	WHERE is_deleted = '0' AND registDate < :formattedStartDate
";
$initialBalanceParams = [':formattedStartDate' => $formattedStartDate];
$initialBalanceResult = executeQuery($pdo, $initialBalanceSql, $initialBalanceParams);
$initialBalance = $initialBalanceResult[0]['balance'];

// 총 수입 계산
$totalIncomeSql = "
	SELECT SUM(REPLACE(amount, ',', '')) AS totalIncome
	FROM {$accountTable}
	WHERE is_deleted = '0' AND inoutsep = '수입'
	AND registDate BETWEEN :formattedStartDate AND :formattedEndDate
";
$totalIncomeParams = [
	':formattedStartDate' => $formattedStartDate,
	':formattedEndDate' => $formattedEndDate
];
$totalIncomeResult = executeQuery($pdo, $totalIncomeSql, $totalIncomeParams);
$totalIncome = $totalIncomeResult[0]['totalIncome'];

// 총 지출 계산
$totalExpenseSql = "
	SELECT SUM(REPLACE(amount, ',', '')) AS totalExpense
	FROM {$accountTable}
	WHERE is_deleted = '0' AND inoutsep = '지출'
	AND registDate BETWEEN :formattedStartDate AND :formattedEndDate
";
$totalExpenseParams = [
	':formattedStartDate' => $formattedStartDate,
	':formattedEndDate' => $formattedEndDate
];
$totalExpenseResult = executeQuery($pdo, $totalExpenseSql, $totalExpenseParams);
$totalExpense = $totalExpenseResult[0]['totalExpense'];

// 최종 잔액 계산
$finalBalance = $initialBalance + $totalIncome - $totalExpense;

// Bankbook options (은행 계좌 정보)
$bankbookOptions = [];
$bankbookFilePath = $_SERVER['DOCUMENT_ROOT'] . "/account/bankbook.txt";
if (file_exists($bankbookFilePath)) {
	$bankbookOptions = file($bankbookFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
}

// 수입/지출 내역 조회를 위한 연도 설정
$year = isset($_REQUEST['year']) ? $_REQUEST['year'] : date('Y');

// 조회할 월 설정 (기본값: 현재 월)
$selectedMonth = isset($_REQUEST['startMonth']) ? $_REQUEST['startMonth'] : date('m');

// 계산서 발행 조회할 전달(이전 월) 계산
$prevMonth = $selectedMonth - 1;
if ($prevMonth == 0) {
	$prevMonth = 12;
	$year = $year - 1; // 연도가 바뀌는 경우 처리
}

// 이전달의 시작일과 종료일 설정
$prevStartDate = "$year-$prevMonth-01";
// $prevStartDate = date("Y-m-29", strtotime("-1 month", strtotime($prevStartDate)));
$prevEndDate = date("Y-m-t", strtotime($prevStartDate));

// 수입 내역 조회
$incomeSql = "
	SELECT customer_name, invoice_issued, sales as amount, secondordnum, SUM(sales) as totalAmount
	FROM {$monthlySalesTable}
	WHERE invoice_issued = '발행'
	AND closure_date BETWEEN :prevStartDate AND :prevEndDate
	AND (is_deleted = '0' or is_deleted IS NULL)
	GROUP BY customer_name
";
$incomeParams = [
	':prevStartDate' => $prevStartDate,
	':prevEndDate' => $prevEndDate
];
$incomeData = executeQuery($pdo, $incomeSql, $incomeParams);

// 미수금을 수입 내역에 추가
foreach ($receivables as $secondordnum => $receivableAmount) {
	if ($receivableAmount > 0) {
		$incomeData[] = [
			'customer_name' => $vendorNames[$secondordnum],
			'receivableAmount' => $receivableAmount,
			'totalAmount' => $receivableAmount,
			'secondordnum' => $secondordnum
		];
	}
}

// 미수금 누계 처리
$accumulatedIncomeData = [];
foreach ($incomeData as $item) {
	$secondordnum = $item['secondordnum'];

	// 누적 데이터에 secondordnum이 이미 존재하는지 확인
	if (isset($accumulatedIncomeData[$secondordnum])) {
		// 존재하면 금액을 누계
		$accumulatedIncomeData[$secondordnum]['amount'] += isset($item['amount']) ? $item['amount'] : 0;
		$accumulatedIncomeData[$secondordnum]['receivableAmount'] += isset($item['receivableAmount']) ? $item['receivableAmount'] : 0;
		$accumulatedIncomeData[$secondordnum]['totalAmount'] += isset($item['totalAmount']) ? $item['totalAmount'] : 0;
	} else {
		// 존재하지 않으면 새로운 항목을 추가, 없는 필드 초기화
		$accumulatedIncomeData[$secondordnum] = [
			'customer_name' => $item['customer_name'],
			'amount' => isset($item['amount']) ? $item['amount'] : 0,
			'receivableAmount' => isset($item['receivableAmount']) ? $item['receivableAmount'] : 0,
			'totalAmount' => isset($item['totalAmount']) ? $item['totalAmount'] : 0,
			'secondordnum' => $secondordnum
		];
	}
}

// 배열을 다시 숫자 인덱스로 변환
$incomeData = array_values($accumulatedIncomeData);

// 현재 기준월의 1일부터 현재 날짜까지 수금된 데이터 조회
$fromdate = date("Y-m-01");
// 전전달의 29일 구하기
// $fromdate = date("Y-m-29", strtotime("-1 month", strtotime($fromdate)));
// $todate = date("Y-m-d");
// $lastmonthEnddate = date("Y-m-t", strtotime("first day of last month")); //전달의 **말일(마지막 날)**을 
$lastmonthEnddate = date("Y-m-t");

// print_r($lastmonthEnddate);

// account 테이블에서 해당 거래처의 수금된 항목 조회
$paymentSql = "
	SELECT secondordnum, SUM(CAST(REPLACE(amount, ',', '') AS SIGNED)) AS total_payment
	FROM {$accountTable}
	WHERE inoutsep = '수입'
	AND registDate BETWEEN :fromdate AND :lastmonthEnddate
	AND (is_deleted IS NULL OR is_deleted = 0)
	GROUP BY secondordnum
";
$paymentParams = [
	':fromdate' => $fromdate,
	':lastmonthEnddate' => $lastmonthEnddate
];
$paymentData = executeQuery($pdo, $paymentSql, $paymentParams);

// 이미 수금된 secondordnum을 배열로 저장
$receivedPayments = [];
foreach ($paymentData as $row) {
	$secondordnum = $row['secondordnum'];
	$totalPayment = (float)$row['total_payment'];
	// $totalPayment = 0 ; // 이미 수금된것 제외 코드

	// 수금 내역을 기록 (이미 수금된 금액이 있으면 배열에 추가) 수금이 계산서 금액과 일치하면 제외되는 알고리즘 추가한다.
	if (intval($totalPayment) > 0) {
		$receivedPayments[$secondordnum] = $totalPayment;
	}
}

// echo '<pre>';
// print_r($paymentData);
// echo '</pre>';	

// 수금된 금액을 차감한 나머지 미수금 계산
$filteredIncomeData = [];
foreach ($incomeData as $incomeRow) {
	$secondordnum = $incomeRow['secondordnum'] ?? '';

	// 수금된 금액이 있는지 확인
	if (isset($receivedPayments[$secondordnum])) {
		
		// echo ' , ' . $incomeRow['receivableAmount'] . '   미수금 : ' . $receivedPayments[$secondordnum] ;
		
		if( $incomeRow['receivableAmount'] ==  $receivedPayments[$secondordnum]) {
		// 수금된 금액을 차감한 나머지 미수금을 계산		
		$remainingAmount = $incomeRow['receivableAmount'] - $receivedPayments[$secondordnum];  // 현재월의 수금내역 차감공식 수정 250415 찾음
		} 
		else
		{
		   $remainingAmount = $incomeRow['receivableAmount'] ;
		}
		
		// 나머지 미수금이 0보다 크거나 같으면 배열에 추가
		if ($remainingAmount >= 0) {
			$incomeRow['receivableAmount'] = $remainingAmount;
			$incomeRow['totalAmount'] = $incomeRow['totalAmount']; // totalAmount는 원래의 매출 금액으로 유지
			$filteredIncomeData[] = $incomeRow;
		}
	} else {
		// 수금된 금액이 없으면 그대로 추가
		$filteredIncomeData[] = $incomeRow;
	}
}

// vendorNames 기준으로 $filteredIncomeData 정렬
usort($filteredIncomeData, function ($a, $b) use ($vendorNames) {
	return strcmp($vendorNames[$a['secondordnum']], $vendorNames[$b['secondordnum']]);
});

// 제외된 항목 파일 경로
$excludedItemsFile = $_SERVER['DOCUMENT_ROOT'] . "/account_plan/excluded_items.json";

// 제외된 항목 로드
$excludedItems = [];
if (file_exists($excludedItemsFile)) {
	$excludedItems = json_decode(file_get_contents($excludedItemsFile), true);
}

// 디버깅을 위한 코드 추가
// echo '<pre>--- 제외된 항목 ($excludedItems) ---';
// print_r($excludedItems);
// echo '</pre>';

// $filteredIncomeData에서 제외된 항목 필터링
$filteredIncomeDataForDebugging = $filteredIncomeData; // 디버깅을 위해 원본 배열 복사
if (!empty($excludedItems)) {
	$filteredIncomeData = array_filter($filteredIncomeData, function ($item) use ($excludedItems, $selectedYearMonth) {
		// 디버깅을 위한 코드 추가
		// echo '<pre>--- 현재 비교 중인 항목 ($item) ---';
		// print_r($item);
		// echo '</pre>';

		$isExcluded = false; // 제외 여부 플래그
		foreach ($excludedItems as $excludedItem) {
			// 디버깅을 위한 코드 추가
			// echo '<pre>--- 제외된 항목과 비교 ($excludedItem) ---';
			// print_r($excludedItem);
			// echo '--- 비교 결과 ---';
			// echo '$excludedItem[\'yearMonth\'] === $selectedYearMonth : ' . ($excludedItem['yearMonth'] === $selectedYearMonth ? 'true' : 'false') . '<br>';
			// echo '$excludedItem[\'customerName\'] === $item[\'customer_name\'] : ' . ($excludedItem['customerName'] === $item['customer_name'] ? 'true' : 'false') . '<br>';
			// echo 'intval($excludedItem[\'amount\']) === intval($item[\'totalAmount\']) : ' . (intval($excludedItem['amount']) === intval($item['totalAmount']) ? 'true' : 'false') . '<br>';
			// echo '</pre>';

			if (
				$excludedItem['yearMonth'] === $selectedYearMonth &&
				$excludedItem['customerName'] === $item['customer_name'] &&
				intval($excludedItem['amount']) === intval($item['totalAmount'])
			) {
				$isExcluded = true; // 제외 플래그 설정
				break;
			}
		}

		// 디버깅을 위한 코드 추가
		// echo '<pre>--- 최종 제외 여부: ' . ($isExcluded ? '제외됨' : '유지됨') . ' ---</pre>';

		return !$isExcluded; // 제외 여부에 따라 반환
	});
}

// 디버깅을 위한 코드 추가: 필터링 전후 비교
// echo '<pre>--- 필터링 전 ($filteredIncomeDataForDebugging) ---';
// print_r($filteredIncomeDataForDebugging);
// echo '</pre>';

// echo '<pre>--- 필터링 후 ($filteredIncomeData) ---';
// print_r($filteredIncomeData);
// echo '</pre>';

// ... (나머지 코드는 동일) ...

// 이번 달로 설정, 계산서는 지난달
$startMonth = isset($_REQUEST['startMonth']) ? $_REQUEST['startMonth'] : date('m');
$endMonth = isset($_REQUEST['endMonth']) ? $_REQUEST['endMonth'] : date('m');

$startDate = "$year-$startMonth-01";
$endDate = date("Y-m-t", strtotime("$year-$endMonth-01"));

// 지출 내역 조회 (월별 누적)
$expenseSql = "
	SELECT content, SUM(amount) as totalAmount
	FROM {$accountPlanTable}
	WHERE inoutsep = '지출'
	AND (is_deleted = '0' or is_deleted IS NULL)
	GROUP BY content
";

$expenseData = executeQuery($pdo, $expenseSql);

// 지출 상세 내역 조회
$expenseDetailSql = "
	SELECT num, content, amount, registDate, memo, ForeDate
	FROM {$accountPlanTable}
	WHERE inoutsep = '지출'
	AND (is_deleted = '0' or is_deleted IS NULL)
";
$expenseDetailData = executeQuery($pdo, $expenseDetailSql);

// 수입/지출 합계 및 순이익 계산 (필터링된 데이터 사용)
$filteredTotalIncome = array_sum(array_column($filteredIncomeData, 'totalAmount'));
$filteredTotalExpense = array_sum(array_column($expenseData, 'totalAmount'));
$filteredNetIncome = $filteredTotalIncome - $filteredTotalExpense + $finalBalance;

// receivableAmount 또는 totalAmount 값이 0보다 큰 항목만 필터링
$filteredIncomeData = array_filter($filteredIncomeData, function ($item) {
	return (isset($item['receivableAmount']) && intval($item['receivableAmount']) > 0) ||
		(isset($item['totalAmount']) && intval($item['totalAmount']) > 0);
});

// vendorNames 기준으로 $filteredIncomeData 정렬
usort($filteredIncomeData, function ($a, $b) {
	return strcmp($a['customer_name'], $b['customer_name']);
});

// 수금을 바로 하는 업체를 처리하기 위한 코드
// 현재 날짜로부터 2개월 전의 1일을 시작일로 설정
$specialStartDate = (new DateTime('first day of -2 months'))->format('Y-m-d');
$specialEndDate = date("Y-m-d");

// account 테이블에서 해당 거래처의 수금된 항목 조회
$specialPaymentSql = "
SELECT secondordnum, SUM(CAST(REPLACE(amount, ',', '') AS SIGNED)) AS total_payment
FROM {$accountTable}
WHERE inoutsep = '수입'
AND registDate BETWEEN :specialStartDate AND :specialEndDate
AND (is_deleted IS NULL OR is_deleted = 0)
GROUP BY secondordnum
";
$specialPaymentParams = [
	':specialStartDate' => $specialStartDate,
	':specialEndDate' => $specialEndDate
];
$specialPaymentData = executeQuery($pdo, $specialPaymentSql, $specialPaymentParams);

// paymentDataSpecial 배열을 secondordnum을 키로 하는 형태로 재구성
$specialPaymentMap = [];
foreach ($specialPaymentData as $payment) {
	$specialPaymentMap[$payment['secondordnum']] = intval($payment['total_payment']);
}

// filteredIncomeData에서 secondordnum과 total_payment가 일치하는 항목 제거
$filteredIncomeData = array_filter($filteredIncomeData, function ($item) use ($specialPaymentMap) {
	// receivableAmount 또는 totalAmount 값이 있으면 intval로 변환
	$item['receivableAmount'] = isset($item['receivableAmount']) ? intval($item['receivableAmount']) : 0;
	$item['totalAmount'] = isset($item['totalAmount']) ? intval($item['totalAmount']) : 0;

	// paymentDataSpecial에 secondordnum이 있고, totalAmount와 total_payment가 일치하면 해당 항목을 제거
	if (isset($specialPaymentMap[$item['secondordnum']]) && $specialPaymentMap[$item['secondordnum']] === $item['totalAmount']) {
		return false; // 제거
	}

	// 제거되지 않은 항목만 반환
	return true;
});

// vendorNames 기준으로 $filteredIncomeData 정렬
usort($filteredIncomeData, function ($a, $b) {
	return strcmp($a['customer_name'], $b['customer_name']);
});

?>
<form id="board_form" name="board_form" method="post" enctype="multipart/form-data">

	<input type="hidden" id="mode" name="mode" value="<?= isset($mode) ? $mode : '' ?>">
	<input type="hidden" id="num" name="num" value="<?= isset($num) ? $num : '' ?>">
	<input type="hidden" id="tablename" name="tablename" value="<?= isset($tablename) ? $tablename : '' ?>">
	<input type="hidden" id="startMonth" name="startMonth" value="<?= isset($startMonth) ? $startMonth : '' ?>">
	<input type="hidden" id="endMonth" name="endMonth" value="<?= isset($endMonth) ? $endMonth : '' ?>">

	<div class="container-fluid">
		<div id="myModal" class="modal">
			<div class="modal-content" style="width:800px;">
				<div class="modal-header">
					<span class="modal-title"><?= $title_message ?></span>
					<span class="close">&times;</span>
				</div>
				<div class="modal-body">
					<div class="custom-card"></div>
				</div>
			</div>
		</div>
	</div>
	
	<div class="container mt-5">
		<div class="card justify-content-center text-center mt-5">
			<div class="card-header">
				<span class="text-center fs-5"> <?= $title_message ?>
					<button type="button" class="btn btn-dark btn-sm me-1" onclick='location.reload()'> <i class="bi bi-arrow-clockwise"></i> </button>
				</span>
			</div>
			<div class="card-body">
				<div class="row mb-3">
					<div class="d-flex justify-content-center align-items-center">
						<select id="year" name="year" class="form-select w-auto me-2" style="font-size:0.8rem; height:32px;" onchange="loadDetails()">
							<?php for ($i = date('Y'); $i >= 2024; $i--) : ?>
								<option value="<?= $i ?>" <?= ($year == $i) ? 'selected' : '' ?>><?= $i ?>년</option>
							<?php endfor; ?>
						</select>

						<button id="newInBtn" type="button" class="btn btn-dark btn-sm mx-1"> <i class="bi bi-pencil-square"></i> 수입등록 </button>
						<button id="newBtn" type="button" class="btn btn-dark btn-sm mx-1"> <i class="bi bi-pencil-square"></i> 지출등록 </button>
						<button id="excludedListBtn" type="button" class="btn btn-primary btn-sm mx-1"> <i class="bi bi-card-checklist"></i> 수입제외목록 </button>
						<button type="button" class="btn btn-dark btn-sm mx-1" id="csvDownload"> <i class="bi bi-floppy-fill"></i>  CSV </button>
					</div>
				</div>

				<div class="row w400px m-1 mt-4" style="padding:2px;margin:2px;">
					<table class="table table-bordered" style="padding:2px;">
						<thead class="table-secondary" style="padding:2px;">
							<tr>
								<?php
								$bankbookTitle = ' ' . $bankbookOptions[0] . ' (계좌 잔액) : ';
								$formattedFinalBalance = isset($finalBalance) ? number_format($finalBalance) : '';
								?>
								<th class="text-center" style="width:200px;"><?= $bankbookTitle ?></th>
								<th class="text-end text-primary fw-bold" style="width:100px;"><?= $formattedFinalBalance ?></th>
							</tr>
						</thead>
					</table>
				</div>
				<div class="row mb-4">
					<table class="table table-hover" id="detailTable">
						<thead class="table-info">
							<tr>
								<th colspan="2" class="text-center">수입</th>
								<th colspan="4" class="text-center">지출</th>
							</tr>
							<tr>
								<th class="text-center">항목</th>
								<th class="text-center">금액</th>
								<th class="text-center">항목</th>
								<th class="text-center">금액</th>
								<th class="text-center">예상지급일</th>
								<th class="text-center">비고</th>
							</tr>
						</thead>
						<tbody>
							<?php
							// 미수금, 계산서 업체를 분할 후 다시 합치는 로직 구현
							$receivablesData = [];
							$invoicesData = [];

							// 이번 달의 시작일과 종료일 계산
							$currentMonthStartDate = date('Y-m-01');
							$currentMonthEndDate = date('Y-m-t');

							// 기존 데이터에서 미수금 및 계산서 발행 금액을 분리
							foreach ($filteredIncomeData as $incomeRow) {
								// 미수금이 있는 경우 receivablesData 배열에 추가
								if ($incomeRow['receivableAmount'] > 0) {
									$receivablesData[] = [
										'customer_name' => $incomeRow['customer_name'],
										'receivableAmount' => $incomeRow['receivableAmount'],
										'secondordnum' => $incomeRow['secondordnum']
									];
								}

								// 계산서 발행 금액이 있는 경우 invoicesData 배열에 추가
								if ($incomeRow['amount'] > 0) {
									$invoicesData[] = [
										'customer_name' => $incomeRow['customer_name'],
										'amount' => $incomeRow['amount'],
										'secondordnum' => $incomeRow['secondordnum']
									];
								}
							}

							// 데이터베이스에서 수동으로 입력된 자료 가져오기
							$manualInputSql = "
								SELECT content AS customer_name, amount, secondordnum, num
								FROM {$accountPlanTable}_in
								WHERE registDate BETWEEN :currentMonthStartDate AND :currentMonthEndDate
								AND (is_deleted = '0' or is_deleted IS NULL)
							";
							$manualInputParams =	[
								':currentMonthStartDate' => $currentMonthStartDate,
								':currentMonthEndDate' => $currentMonthEndDate
							];
							$manualEntries = executeQuery($pdo, $manualInputSql, $manualInputParams);

							// 수동으로 입력된 자료를 기존 데이터에 추가
							foreach ($manualEntries as $entry) {
								// 계산서 발행 금액이 있는 경우 invoicesData 배열에 추가
								if ($entry['amount'] > 0) {
									$invoicesData[] = [
										'customer_name' => '(수기) ' . $entry['customer_name'],
										'amount' => $entry['amount'],
										'secondordnum' => $entry['secondordnum'],
										'num' => $entry['num']
									];
								}
							}

							// 최종적으로 두 배열을 다시 합칩니다.
							$finalIncomeData = array_merge($receivablesData, $invoicesData);

							// 월별 수입/지출 합계 초기화
							$monthlyTotalIncome = 0;
							$monthlyTotalExpense = 0;

							$maxRows = max(count($finalIncomeData), count($expenseDetailData));
							for ($i = 0; $i < $maxRows; $i++) :
								// 수입/미수금
								$incomeContent = isset($finalIncomeData[$i]['customer_name']) ? $finalIncomeData[$i]['customer_name'] . (isset($finalIncomeData[$i]['receivableAmount']) ? ' (미수금)' : ' (계산서발행)') : '';
								$incomeAmount = isset($finalIncomeData[$i]['receivableAmount']) && intval($finalIncomeData[$i]['receivableAmount']) > 0 ? $finalIncomeData[$i]['receivableAmount'] : (isset($finalIncomeData[$i]['amount']) ? $finalIncomeData[$i]['amount'] : 0);

								// Income details
								$incomeNum = isset($finalIncomeData[$i]['num']) ? $finalIncomeData[$i]['num'] : '';

								// Expense details
								$expenseNum = isset($expenseDetailData[$i]['num']) ? $expenseDetailData[$i]['num'] : '';
								$expenseContent = isset($expenseDetailData[$i]['content']) ? $expenseDetailData[$i]['content'] : '';
								$expenseAmount = isset($expenseDetailData[$i]['amount']) ? $expenseDetailData[$i]['amount'] : 0;
								$expenseMemo = isset($expenseDetailData[$i]['memo']) ? $expenseDetailData[$i]['memo'] : '';
								$expenseForeDate = isset($expenseDetailData[$i]['ForeDate']) && $expenseDetailData[$i]['ForeDate'] !== '0000-00-00' ? $expenseDetailData[$i]['ForeDate'] : '';

								// 월별 수입/지출 합계
								$monthlyTotalIncome += floatval($incomeAmount);
								$monthlyTotalExpense += floatval($expenseAmount);

								// 미수금 항목에 'text-danger' 클래스 추가
								$incomeClass = (strpos($incomeContent, '미수금') !== false) ? 'text-danger' : '';

								echo '<tr >';
								echo '<td class="text-center fw-bold ' . $incomeClass . ' income-row" data-year-month="' . $selectedYearMonth . '" data-customer-name="' . htmlspecialchars($incomeContent) . '" data-amount="' . $incomeAmount . '">' . $incomeContent . '</td>';                                    
								echo '<td class="text-end text-primary fw-bold income-row" data-year-month="' . $selectedYearMonth . '" data-customer-name="' . htmlspecialchars($incomeContent) . '" data-amount="' . $incomeAmount . '" >' . number_format($incomeAmount) . '</td>';
							?>
								<td class="text-center" onclick="loadForm('update', '<?= $expenseNum ?>', 'account_plan');"><?= $expenseContent ?></td>
								<td class="text-end text-danger fw-bold" onclick="loadForm('update', '<?= $expenseNum ?>', 'account_plan');">
									<?= floatval($expenseAmount) > 0 ? number_format((float)$expenseAmount) : '' ?>
								</td>
								<td class="text-center text-secondary fw-bold" onclick="loadForm('update', '<?= $expenseNum ?>', 'account_plan');"><?= $expenseForeDate ?></td>
								<td class="text-start text-secondary fw-bold" onclick="loadForm('update', '<?= $expenseNum ?>', 'account_plan');"><?= $expenseMemo ?></td>

								</tr>
							<?php endfor; ?>
						</tbody>
						<tfoot class="table-secondary">
							<tr>
								<th class="text-end"> 수입 합계 &nbsp; </th>
								<th class="text-end text-primary"><?= number_format($monthlyTotalIncome) ?></th>
								<th class="text-end"> 지출 합계 &nbsp; </th>
								<th class="text-end text-danger"><?= number_format($monthlyTotalExpense) ?></th>
								<th class="text-end"></th>
								<th class="text-end"></th>
							</tr>
							<tr>
								<th class="text-end" colspan="3"> (계좌 잔액) </th>
								<th class="text-end"><?= number_format($finalBalance) ?></th>
								<th class="text-end"></th>
								<th class="text-end"></th>
							</tr>
							<tr>
								<th class="text-end" colspan="3"> 최종 차액 &nbsp; </th>
								<th class="text-end"><?= number_format($filteredNetIncome) ?></th>
								<th class="text-end"></th>
								<th class="text-end"></th>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		</div>
	</div>
	
<div class="container-fluid">
<div id="excludeModal" class="modal">
	<div class="modal-content" style="width:400px;">
		<div class="modal-header">
			<span class="modal-title">항목 제외</span>
			<span class="close">&times;</span>
		</div>
		<div class="modal-body">
			<p><H4> 이 항목을 제외하시겠습니까? </H4></p>
			<button id="excludeConfirmBtn" class="btn btn-danger">제외</button>
		</div>
	</div>
</div>
</div>
	
<div id="excludedListModal" class="modal">
<div class="modal-content" style="width: 600px;">
	<div class="modal-header">
		<span class="modal-title">수입제외목록</span>
		<span class="close" onclick="$('#excludedListModal').hide();">&times;</span>
	</div>
	<div class="modal-body custom-modal-body">
		<table class="table table-striped" id="excludedListTable">
			<thead>
				<tr>
					<th>연월</th>
					<th>거래처명</th>
					<th>제외금액</th>
					<th>복구</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>
</div>
</div>	
	
</form>

<script>
	let isSaving = false;
	var ajaxRequest = null;

	// 페이지 로딩
	$(document).ready(function() {
		var loader = document.getElementById('loadingOverlay');
		loader.style.display = 'none';

		$("#newBtn").on("click", function() {
			loadForm('insert', null, '<?= $accountPlanTable ?>');
		});
		$("#newInBtn").on("click", function() {
			loadForm('insert', null, '<?= $accountPlanTable ?>_in'); // 수입부분 등록
		});
	});

	/**
	 * 폼을 로드하여 모달 창에 표시합니다.
	 *
	 * @param {string} mode 'insert' 또는 'update' 모드
	 * @param {number|null} num 수정할 레코드의 번호 (mode가 'update'일 때 사용)
	 * @param {string} tablename 데이터베이스 테이블 이름
	 */
	function loadForm(mode, num = null, tablename = '') {
		console.log(num);

		if (mode == 'update' && (num == '' || num == null))
			return // 번호가 없고 수정인 경우는 리턴

		if (num == null) {
			$("#mode").val('insert');
		} else {
			$("#mode").val('update');
			$("#num").val(num);
		}

		if (ajaxRequest !== null) {
			ajaxRequest.abort();
		}

		// 수입, 지출 테이블 구분
		var url = (tablename == '<?= $accountPlanTable ?>') ? "fetch_modal.php" : "fetch_in.php";

		ajaxRequest = $.ajax({
			type: "POST",
			url: url,
			data: {
				mode: mode,
				num: num,
				tablename: tablename // tablename 추가
			},
			dataType: "html",
			success: function(response) {
				document.querySelector(".modal-body .custom-card").innerHTML = response;

				$("#myModal").show();

				$(document).on("click", "#closeBtn", function() {
					$("#myModal").hide();
				});

				$(document).on("click", "#saveBtn", function() {
					if (isSaving) return; // 중복 저장 방지
					isSaving = true;

					if (ajaxRequest !== null) {
						ajaxRequest.abort();
					}
					ajaxRequest = $.ajax({
						url: "/account_plan/insert.php",
						type: "post",
						data: {
							mode: $("#mode").val(),
							num: $("#num").val(),
							update_log: $("#update_log").val(),
							registDate: $("#registDate").val(),
							inoutsep: $("input[name='inoutsep']:checked").val(),
							content: $("#content").val(),
							amount: $("#amount").val(),
							memo: $("#memo").val(),
							first_writer: $("#first_writer").val(),
							content_detail: $("#content_detail").val(),
							ForeDate: $("#ForeDate").val(),
							secondordnum: $("#secondordnum").val(),
							tablename: tablename // tablename 추가
						},
						dataType: "json",
						success: function(response) {
							Toastify({
								text: "저장 완료",
								duration: 3000,
								close: true,
								gravity: "top",
								position: "center",
								backgroundColor: "#4fbe87",
							}).showToast();
							setTimeout(function() {
								$("#myModal").hide();
								location.reload();
							}, 1500);
						},
						error: function(jqxhr, status, error) {
							console.log("AJAX Error: ", status, error);
						},
						complete: function() {
							isSaving = false; // 저장 완료 후 중복 방지 해제
						}
					});
				});

				$(document).on("click", "#deleteBtn", function() {
					var level = '<?= $_SESSION["level"] ?>';

					if (level !== '1') {
						Swal.fire({
							title: '삭제불가',
							text: "관리자만 삭제 가능합니다.",
							icon: 'error',
							confirmButtonText: '확인'
						});
						return;
					}

					Swal.fire({
						title: '자료 삭제',
						text: "삭제는 신중! 정말 삭제하시겠습니까?",
						icon: 'warning',
						showCancelButton: true,
						confirmButtonColor: '#3085d6',
						cancelButtonColor: '#d33',
						confirmButtonText: '삭제',
						cancelButtonText: '취소'
					}).then((result) => {
						if (result.isConfirmed) {
							$("#mode").val('delete');
							var formData = $("#board_form").serialize();
							formData += '&tablename=' + tablename; // tablename 추가

							$.ajax({
								url: "/account_plan/insert.php",
								type: "post",
								data: formData,
								success: function(response) {
									Toastify({
										text: "삭제 완료",
										duration: 2000,
										close: true,
										gravity: "top",
										position: "center",
										backgroundColor: "#4fbe87",
									}).showToast();

									$("#myModal").hide();
									location.reload();
								},
								error: function(jqxhr, status, error) {
									console.log(jqxhr, status, error);
								}
							});
						}
					});
				});

				$(".close").on("click", function() {
					$("#myModal").hide();
				});

			},
			error: function(jqxhr, status, error) {
				console.log("AJAX error in loadForm:", status, error);
			}
		});
	}

	/**
	 * 전화번호부 팝업 창을 엽니다.
	 *
	 * @param {string} searchfield 검색 필드 ID
	 */
	function phonebookBtn(searchfield) {
		var search = $("#" + searchfield).val();
		href = '../phonebook/list.php?returnID=수입등록&search=' + search;
		popupCenter(href, '전화번호 검색', 1600, 800);
	}

	// 기존 loadDetails 함수 유지
	function loadDetails() {
		const year = document.getElementById('year').value;
		const startMonth = document.getElementById('startMonth').value;
		const endMonth = document.getElementById('endMonth').value;

		window.location.href = `list.php?year=${year}&startMonth=${startMonth}&endMonth=${endMonth}`;
	}

	/**
	 * 테이블 데이터를 CSV 파일로 다운로드합니다.
	 */
	document.getElementById("csvDownload").addEventListener("click", function() {
		const table = document.getElementById("detailTable");
		const theadRow = table.querySelector("thead tr");
		const rows = table.querySelectorAll("tbody tr");
		const tfootRow = table.querySelectorAll("tfoot tr");

		const csvRows = [];

		// Include the header row
		const headerData = [];
		theadRow.querySelectorAll("th").forEach(function(cell) {
			headerData.push(cell.textContent);
		});
		csvRows.push(headerData.join(","));

		// Include the data rows
		rows.forEach(function(row) {
			const rowData = [];
			row.querySelectorAll("td").forEach(function(cell) {
				// Remove commas from numeric values
				const cleanValue = cell.textContent.replace(/,/g, "");
				rowData.push(cleanValue);
			});
			csvRows.push(rowData.join(","));
		});

		// Include the data rows
		tfootRow.forEach(function(row) {
			const rowData = [];
			row.querySelectorAll("th").forEach(function(cell) {
				// Remove commas from numeric values
				const cleanValue = cell.textContent.replace(/,/g, "");
				rowData.push(cleanValue);
			});
			csvRows.push(rowData.join(","));
		});

		const csvContent = csvRows.join("\n");
		// Add BOM for UTF-8 encoding to fix Korean characters
		const blob = new Blob(['\ufeff' + csvContent], {
			type: "text/csv;charset=utf-8;"
		});
		const link = document.createElement("a");
		link.href = URL.createObjectURL(blob);
		link.setAttribute("download", "월별_수입지출_예상내역서.csv");
		document.body.appendChild(link);
		link.click();
	});
	
	

// tr 클릭 이벤트 핸들러
$(document).on("click", ".income-row", function() {
let yearMonth = $(this).data("year-month");
let customerName = $(this).data("customer-name");
let amount = $(this).data("amount");

// data- 속성에서 값을 가져올 때, 해당 속성이 없으면 undefined가 반환되므로,
// 이 경우를 대비하여 기본값을 설정해주는 것이 좋습니다.
yearMonth = yearMonth || ''; // 빈 문자열 또는 원하는 기본값
customerName = customerName || ''; // 빈 문자열 또는 원하는 기본값
amount = amount || 0; // 0 또는 원하는 기본값

$("#excludeConfirmBtn").data("year-month", yearMonth);
$("#excludeConfirmBtn").data("customer-name", customerName);
$("#excludeConfirmBtn").data("amount", amount);

$("#excludeModal").show();
});

// 제외 버튼 클릭 이벤트 핸들러
$(document).on("click", "#excludeConfirmBtn", function() {
let yearMonth = $(this).data("year-month");
let customerName = $(this).data("customer-name");
let amount = $(this).data("amount");

$.ajax({
	url: "/account_plan/exclude_item.php",
	type: "post",
	data: {
		yearMonth: yearMonth,
		customerName: customerName,
		amount: amount
	},
	dataType: "json",
	success: function(response) {
		if (response.success) {
			Toastify({
				text: "항목이 제외되었습니다.",
				duration: 3000,
				close: true,
				gravity: "top",
				position: "center",
				backgroundColor: "#4fbe87",
			}).showToast();

			// 화면에서 해당 tr 요소 제거
			// $(`.income-row[data-year-month='${yearMonth}'][data-customer-name='${customerName}'][data-amount='${amount}']`).remove();
			
			location.reload();

			$("#excludeModal").hide();
		} else {
			Toastify({
				text: "오류가 발생했습니다.",
				duration: 3000,
				close: true,
				gravity: "top",
				position: "center",
				backgroundColor: "#ff0000",
			}).showToast();
		}
	},
	error: function(jqxhr, status, error) {
		console.log("AJAX Error: ", status, error);
		Toastify({
			text: "서버와의 통신 중 오류가 발생했습니다.",
			duration: 3000,
			close: true,
			gravity: "top",
			position: "center",
			backgroundColor: "#ff0000",
		}).showToast();
	}
});
});

// 모달 닫기 버튼 이벤트
$(".close").on("click", function() {
$("#excludeModal").hide();
});
	

// "수입제외목록" 버튼 클릭 이벤트 핸들러
$(document).on("click", "#excludedListBtn", function() {
loadExcludedItems();
$("#excludedListModal").show();
});


function loadExcludedItems() {
$.ajax({
	url: "/account_plan/load_excluded_items.php",
	type: "get",
	dataType: "html",
	success: function(response) {
		$("#excludedListTable tbody").html(response);

		// 복구 버튼 이벤트 핸들러 추가 (loadExcludedItems 함수 내부)
		$(".restore-btn").on("click", function() {
			let yearMonth = $(this).data("year-month");
			let customerName = $(this).data("customer-name");
			let amount = $(this).data("amount");

			if (confirm("이 항목을 복구하시겠습니까?")) {
				$.ajax({
					url: "/account_plan/restore_item.php",
					type: "post",
					data: {
						yearMonth: yearMonth,
						customerName: customerName,
						amount: amount
					},
					dataType: "json",
					success: function(response) {
						if (response.success) {
							Toastify({
								text: "항목이 복구되었습니다.",
								duration: 3000,
								close: true,
								gravity: "top",
								position: "center",
								backgroundColor: "#4fbe87",
							}).showToast();

							// 복구 후 목록 다시 로드
							loadExcludedItems();
							// 복구 후에 페이지 새로 고침
							location.reload();
						} else {
							Toastify({
								text: "오류가 발생했습니다.",
								duration: 3000,
								close: true,
								gravity: "top",
								position: "center",
								backgroundColor: "#ff0000",
							}).showToast();
						}
					},
					error: function(jqxhr, status, error) {
						console.log("AJAX Error: ", status, error);
						Toastify({
							text: "서버와의 통신 중 오류가 발생했습니다.",
							duration: 3000,
							close: true,
							gravity: "top",
							position: "center",
							backgroundColor: "#ff0000",
						}).showToast();
					}
				});
			}
		});
	},
	error: function(jqxhr, status, error) {
		console.log("AJAX Error: ", status, error);
		$("#excludedListTable tbody").html("<tr><td colspan='4'>목록을 불러오는 데 실패했습니다.</td></tr>");
	}
});
}

$(document).ready(function(){
	saveLogData('월별 수입지출 내역서'); 
});
</script>
</body>
</html>