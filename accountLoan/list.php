<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");

if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php");
    exit;
}

// 에러 표시 설정
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';
$title_message = '대출 상환 현황';
?>

<link href="css/style.css" rel="stylesheet">
<title> <?=$title_message?> </title>

<style>
/* 테이블에 테두리 추가 */
#myTable, #myTable th, #myTable td {
	border: 1px solid black;
	border-collapse: collapse;
}

/* 테이블 셀 패딩 조정 */
#myTable th, #myTable td {
	padding: 8px;
	text-align: center;
}
</style>

</head>
<body>
<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php');
$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';  
$fromdate = isset($_REQUEST['fromdate']) ? $_REQUEST['fromdate'] : '';  
$todate = isset($_REQUEST['todate']) ? $_REQUEST['todate'] : '';  
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  

// 현재 날짜
$currentDate = date("Y-m-d");

// fromdate 또는 todate가 빈 문자열이거나 null인 경우
if ($fromdate === "" || $fromdate === null || $todate === "" || $todate === null) {
    // 현재 월의 1일을 fromdate로 설정
    $fromdate = date("2024-03-01");
    $todate = $currentDate;
    $Transtodate = $todate;
} else {
    $Transtodate = $todate;
}

function checkNull($strtmp) {
    return $strtmp !== null && trim($strtmp) !== '';
}

$tablenameAccount = 'account';
$tablename = 'accountLoan';

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

include $_SERVER['DOCUMENT_ROOT'] . "/account/fetch_options.php";
$options = array_merge(array_keys($incomeOptions), array_keys($expenseOptions));

$order = " ORDER BY loanStartDate ASC, num ASC ";

$sql_conditions = [];

// 삭제된 항목 제외
$sql_conditions[] = " is_deleted ='' ";

$sql_params = [];

// 테이블 컬럼 목록을 자동으로 가져오기
$columns = [];
$query = $pdo->query("SHOW COLUMNS FROM " . $tablename);
while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    $columns[] = $row['Field'];
}

// 검색 조건 추가
if (!empty($search)) {
    $search_conditions = [];
    $search_index = 1; // 숫자 인덱스 추가
    foreach ($columns as $column) {
        $placeholder = ":search" . $search_index; // 각 컬럼에 대한 고유한 플레이스홀더 생성
        $search_conditions[] = "$column LIKE $placeholder";
        $sql_params[$placeholder] = "%$search%";
        $search_index++; // 인덱스 증가
    }
    $sql_conditions[] = "(" . implode(" OR ", $search_conditions) . ")";
}


// 날짜 조건 추가
$sql_conditions[] = "loanStartDate BETWEEN :fromdate AND :todate";
$sql_params[':fromdate'] = $fromdate;
$sql_params[':todate'] = $todate;

// 최종 SQL 쿼리 조합
$sql = "SELECT * FROM  {$DB}.{$tablename} WHERE " . implode(" AND ", $sql_conditions) . $order;
 
$stmh = $pdo->prepare($sql);
    foreach ($sql_params as $param => $value) {
        $stmh->bindValue($param, $value);
    }
    $stmh->execute();
    $total_row = $stmh->rowCount();
    
// 수입, 지출을 기반으로 초기 잔액 계산
$initialBalanceSql = "SELECT 
    SUM(CASE WHEN inoutsep = '수입' THEN REPLACE(amount, ',', '') ELSE 0 END) -
    SUM(CASE WHEN inoutsep = '지출' THEN REPLACE(amount, ',', '') ELSE 0 END) AS balance
    FROM $tablenameAccount  
    WHERE is_deleted = '0' AND registDate < :fromdate";
$initialBalanceStmh = $pdo->prepare($initialBalanceSql);
$initialBalanceStmh->bindParam(':fromdate', $fromdate);
$initialBalanceStmh->execute();
$initialBalance = $initialBalanceStmh->fetch(PDO::FETCH_ASSOC)['balance'];

$totalIncomeSql = "SELECT SUM(REPLACE(amount, ',', '')) AS totalIncome 
    FROM $tablenameAccount  
    WHERE is_deleted = '0' AND inoutsep = '수입' 
    AND registDate BETWEEN :fromdate AND :todate";
$totalIncomeStmh = $pdo->prepare($totalIncomeSql);
$totalIncomeStmh->bindParam(':fromdate', $fromdate);
$totalIncomeStmh->bindParam(':todate', $todate);
$totalIncomeStmh->execute();
$totalIncome = $totalIncomeStmh->fetch(PDO::FETCH_ASSOC)['totalIncome'];

$totalExpenseSql = "SELECT SUM(REPLACE(amount, ',', '')) AS totalExpense 
    FROM $tablenameAccount 
    WHERE is_deleted = '0' AND inoutsep = '지출' 
    AND registDate BETWEEN :fromdate AND :todate";
$totalExpenseStmh = $pdo->prepare($totalExpenseSql);
$totalExpenseStmh->bindParam(':fromdate', $fromdate);
$totalExpenseStmh->bindParam(':todate', $todate);
$totalExpenseStmh->execute();
$totalExpense = $totalExpenseStmh->fetch(PDO::FETCH_ASSOC)['totalExpense'];

$finalBalance = $initialBalance + $totalIncome - $totalExpense;

// Bankbook options
$bankbookOptions = [];
$bankbookFilePath = $_SERVER['DOCUMENT_ROOT'] . "/account/bankbook.txt";
if (file_exists($bankbookFilePath)) {
    $bankbookOptions = file($bankbookFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
}

// echo '<pre>';
// print_r($sql );
// echo '</pre>';		

// 차입금 원급 상환 및 이자 비용 납부 내역을 가져옴
$loanRepaymentData = [];
$interestPaymentData = [];

// 기존 차입금 원급 상환 데이터 가져오기
$sql_repayment = "SELECT contentSub AS bank, SUM(REPLACE(amount, ',', '')) AS totalRepayment
                  FROM $tablenameAccount 
                  WHERE content = '차입금상환' AND (is_deleted = '0' or is_deleted IS NULL) 
                  GROUP BY contentSub";
$stmh_repayment = $pdo->query($sql_repayment);
while ($row = $stmh_repayment->fetch(PDO::FETCH_ASSOC)) {
    $loanRepaymentData[$row['bank']] = $row['totalRepayment'];
}

// 💡 **세금계산서(accountBill) 데이터를 차입금 상환에 포함**
$sql_accountBill = "SELECT contentSub AS bank, 
       SUM(REPLACE(amount, ',', '')) AS totalBillRepayment, 
       MAX(registDate) AS lastRegistDate
		FROM accountBill 
		WHERE is_deleted = '0' or is_deleted IS NULL 
		GROUP BY contentSub 
		ORDER BY lastRegistDate DESC ";
$stmh_accountBill = $pdo->query($sql_accountBill);
while ($row = $stmh_accountBill->fetch(PDO::FETCH_ASSOC)) {
    // 기존 차입금 상환에 추가
    if (isset($loanRepaymentData[$row['bank']])) {
        $loanRepaymentData[$row['bank']] += $row['totalBillReM 999	ayment'];
    } else {
        $loanRepaymentData[$row['bank']] = $row['totalBillRepayment'];
    }
}

// echo '<pre>';
// print_r($loanRepaymentData );
// echo '</pre>';	

$sql_interest = "SELECT contentSub AS bank, COUNT(*) AS interestPayments, SUM(REPLACE(amount, ',', '')) AS totalInterestPaid
                 FROM $tablenameAccount 
                 WHERE content = '이자비용' AND (is_deleted = '0' or is_deleted IS NULL) 
                 GROUP BY contentSub";
$stmh_interest = $pdo->query($sql_interest);
while ($row = $stmh_interest->fetch(PDO::FETCH_ASSOC)) {
    $interestPaymentData[$row['bank']] = [
        'interestPayments' => $row['interestPayments'],
        'totalInterestPaid' => $row['totalInterestPaid']
    ];
}
?>
<form id="board_form" name="board_form" method="post" enctype="multipart/form-data">
	<input type="hidden" id="mode" name="mode" value="<?= isset($mode) ? $mode : '' ?>">
	<input type="hidden" id="num" name="num" value="<?= isset($num) ? $num : '' ?>">
	<input type="hidden" id="tablename" name="tablename" value="<?= isset($tablename) ? $tablename : '' ?>">	
<div class="container-fluid">
	<!-- Modal -->
	<div id="myModal" class="modal">
		<div class="modal-content" style="width:800px;">
			<div class="modal-header">
				<span class="modal-title"> <?=$title_message?> </span>
				<span class="close">&times;</span>
			</div>
			<div class="modal-body">
				<div class="custom-card"></div>
			</div>
		</div>
	</div>
</div>
	
<div class="container-fluid">
	<div class="card justify-content-center text-center mt-5">
	<div class="card-header">
		<div class="d-flex justify-content-center align-items-center">
			<span class="text-center fs-5 mx-2">  <?=$title_message?> 						
			</span>
			<button type="button" class="btn btn-dark btn-sm mx-1" onclick='location.reload()'>   <i class="bi bi-arrow-clockwise" ></i> </button>      
			<button id="billRegistBtn" type="button" class="btn btn-dark btn-sm mx-1"> <i class="bi bi-pencil-square"></i> (주일,경동) 계산서 등록 </button>								
		</div>
	</div>
	<div class="card-body">
	<div class="row">
	<div class="col-sm-8">
	<div class="table-responsive">
		<table class="table table-bordered table-hover text-center" id="loanRepaymentTable">
			<thead class="table-secondary">
				<tr>
					<th class="text-center">차입계정</th>
					<th class="text-center">차입금</th>
					<th class="text-center">대출일</th>
					<th class="text-center">은행</th>
					<th class="text-center">용도</th>
					<th class="text-center">이자율%</th>
					<th class="text-center">이자납입일</th>
					<th class="text-center">차입금 원금상환</th>
					<th class="text-center">차입금 원금잔액</th>
					<th class="text-center">이자납입 회수</th>
					<th class="text-center">이자납입 금액합</th>
					<th class="text-center">비고</th>
				</tr>
			</thead>
			<tbody>
            <?php
						
			// 데이터를 차입계정($bank)별로 그룹화
			$groupedData = [];

			while ($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
				// _row.php 에서 필요한 변수들($bank, $loanStartDate, $content, $interestRate, $interestPaymentDate, $memo 등)을 설정한다고 가정합니다.
				include '_row.php';

				// 차입금 숫자 변환
				$loanAmount = floatval(str_replace(',', '', $row['loanAmount']));

				// 기존 데이터 저장
				$groupedData[$bank][] = [
					'loanAmount'          => $loanAmount,
					'loanStartDate'       => $loanStartDate,
					'content'             => $content,
					'interestRate'        => $interestRate,
					'interestPaymentDate' => $interestPaymentDate,
					'loanRepayment'       => 0, // 기본값
					'interestPayments'    => isset($interestPaymentData[$bank]) ? $interestPaymentData[$bank]['interestPayments'] : 0,
					'totalInterestPaid'   => isset($interestPaymentData[$bank]) ? $interestPaymentData[$bank]['totalInterestPaid'] : 0,
					'memo'                => $memo,
				];
			}

			// ✅ 세금계산서 발행 금액을 은행별 한 번만 추가하도록 수정
			foreach ($groupedData as $bank => &$records) {
				if (isset($loanRepaymentData[$bank])) {
					// 첫 번째 항목에만 상환금 추가
					$records[0]['loanRepayment'] = $loanRepaymentData[$bank];
				}
			}
			unset($records); // foreach에서 reference 사용 후 반드시 unset

// echo '<pre>';
// print_r($groupedData );
// echo '</pre>';	
            // 전체 합계 변수 초기화
            $totalLoanAmount       = 0;
            $totalRepayment        = 0;
            $totalBalance          = 0;
            $totalInterestPayments = 0;
            $totalInterestSum      = 0;

            // 그룹별 합계 계산 후 요약 행만 출력 (상세 내역은 숨김)
            foreach ($groupedData as $bankAccount => $rows) {
                $groupLoanAmount       = 0;
                $groupRepayment        = 0;
                $groupBalance          = 0;
                $groupInterestPayments = 0;
                $groupInterestSum      = 0;
                // 그룹 내 첫 번째 행의 값을 참조 (대출일, 용도 등)
                $firstRow = reset($rows);

                foreach ($rows as $data) {
                    $remainingBalance = $data['loanAmount'] - $data['loanRepayment'];
                    $groupLoanAmount       += $data['loanAmount'];
                    $groupRepayment        += $data['loanRepayment'];
                    $groupBalance          += $remainingBalance;
                    $groupInterestPayments += $data['interestPayments'];
                    $groupInterestSum      += $data['totalInterestPaid'];
                }

                // 전체 합계에 그룹별 합계 추가
                $totalLoanAmount       += $groupLoanAmount;
                $totalRepayment        += $groupRepayment;
                $totalBalance          += $groupBalance;
                $totalInterestPayments += $groupInterestPayments;
                $totalInterestSum      += $groupInterestSum;
			}
                ?>
				<tr style="background-color: greay;">
					<td class="text-center bg-light fw-bold">전체 합계</td>
					<td class="text-end bg-light fw-bold"><?= number_format($totalLoanAmount) ?></td>
					<td class="text-center bg-light"></td>
					<td class="text-center bg-light"></td>
					<td class="text-start bg-light"></td>
					<td class="text-end bg-light"></td>
					<td class="text-center bg-light"></td>
					<td class="text-end text-primary bg-light fw-bold"><?= number_format($totalRepayment) ?></td> <!-- 💡 수정됨 -->
					<td class="text-end text-danger bg-light fw-bold"><?= number_format($totalBalance) ?></td>
					<td class="text-center bg-light"><?= $totalInterestPayments ?></td>
					<td class="text-end fw-bold bg-light"><?= number_format($totalInterestSum) ?></td>
					<td class="text-start bg-light"></td>
				</tr>								
			<?php
			// 그룹별 합계 계산 후 요약 행만 출력
			foreach ($groupedData as $bankAccount => $rows) {
				$groupLoanAmount       = 0;
				$groupRepayment        = 0;
				$groupBalance          = 0;
				$groupInterestPayments = 0;
				$groupInterestSum      = 0;
				// 그룹 내 첫 번째 행의 값을 참조 (대출일, 용도 등)
				$firstRow = reset($rows);

				foreach ($rows as $data) {
					$remainingBalance = $data['loanAmount'] - $data['loanRepayment'];
					$groupLoanAmount       += $data['loanAmount'];
					$groupRepayment        += $data['loanRepayment'];
					$groupBalance          += $remainingBalance;
					$groupInterestPayments += $data['interestPayments'];
					$groupInterestSum      += $data['totalInterestPaid'];
				}
				?>
				<tr class="group-summary" data-bank="<?= htmlspecialchars($bankAccount) ?>" style="cursor:pointer;">
					<td class="text-center"><?= htmlspecialchars($bankAccount) ?></td>
					<td class="text-end"><?= number_format($groupLoanAmount) ?></td>
					<td class="text-center"><?= htmlspecialchars($firstRow['loanStartDate']) ?></td>
					<td class="text-center"><?= htmlspecialchars($bankAccount) ?></td>
					<td class="text-start"><?= htmlspecialchars($firstRow['content']) ?></td>
					<td class="text-end"><?= htmlspecialchars($firstRow['interestRate']) ?>%</td>
					<td class="text-center"><?= htmlspecialchars($firstRow['interestPaymentDate']) ?></td>
					<td class="text-end text-primary fw-bold"><?= number_format($groupRepayment) ?></td> <!-- 💡 수정됨 -->
					<td class="text-end text-danger fw-bold"><?= number_format($groupBalance) ?></td>
					<td class="text-center"><?= $groupInterestPayments ?></td>
					<td class="text-end fw-bold"><?= number_format($groupInterestSum) ?></td>
					<td class="text-start"><?= htmlspecialchars($firstRow['memo']) ?></td>
				</tr>
				<?php
			}
			?>
		</tbody>
    </table>
</div>
    </div>
	<div class="col-sm-4">
	<!--  세금계산서 등록부분에 대한 처리 --> 			
	<div class="d-flex justify-content-center align-items-center mt-3 mb-3 btn-alert">
		<span class="text-center fs-5"> (주일,경동) 세금계산서 차입금상환 </span>
	</div>

	<div class="d-flex justify-content-center align-items-center mt-3  mb-3 btn-alert">
		<table class="table table-hover table-bordered">
			<thead class="table-secondary">
				<tr>
					<th class="text-center" >번호</th>
					<th class="text-center" >계산서발행일</th>					
					<th class="text-center" >세부항목</th>						
					<th class="text-center" >금액</th>
					<th class="text-center" >적요</th>
				</tr>
				<!--
				<tr class="bg-light">
					<th class="text-end" colspan="3"> 합계 &nbsp; </th>
					<th class="text-end fw-bold text-primary" id="totalAmount_Bill"></th>
					<th class="text-end" colspan="1"></th>
				</tr>
				-->
			</thead>
			<tbody>
			<?php							
			$tablenameaccountBill = 'accountBill';
			try {
				$sqlaccountBill = "SELECT * FROM " . $tablenameaccountBill . " WHERE is_deleted=0 ORDER BY registDate DESC" ;
				$stmh = $pdo->prepare($sqlaccountBill);				
				$stmh->execute();

				$total_row = $stmh->rowCount();

				$start_num = $total_row;
				$counter = 1;
				$totalAmount_Bill = 0;

				while ($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
					include  $_SERVER['DOCUMENT_ROOT'] . "/account/_row.php";

					// 콤마 제거 후 숫자로 변환
					$amount = floatval(str_replace(',', '', $row['amount']));
					$totalAmount_Bill += $amount;
					?>
					<tr onclick="loadForm_registBill('update', '<?= $num ?>');">
						<td class="text-center"><?= $counter ?></td>
						<td class="text-center"><?= $registDate ?></td>													
						<td class="text-center"><?= $contentSub ?></td>							
						<td class="text-end fw-bold text-primary">
							<?= is_numeric($amount) ? number_format($amount) : htmlspecialchars($amount) ?>
						</td>
						<td class="text-start"><?= $memo ?></td>						
					</tr>
					<?php
					$start_num--;
					$counter++;
				}
			} catch (PDOException $Exception) {
				print "오류: " . $Exception->getMessage();
			}
			?>
			</tbody>
		</table>
	</div>
	<!--  세금계산서 등록부분에 대한 처리 끝... --> 		
	
    </div>
    </div>
<!-- 모달창 (Bootstrap 5 기준) -->
<div class="modal fade" id="loanDetailModal" tabindex="-1" aria-labelledby="loanDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-full">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="loanDetailModalLabel">차입계정 상세 내역</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="닫기"></button>
      </div>
      <div class="modal-body">
        <!-- 상세 내역은 AJAX 등을 통해 로드 -->
        <div id="modalContent">
          Loading...  
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 자바스크립트: 그룹 요약 행 클릭 시 모달창 띄우기  차입계정 상세 내역 -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    var summaryRows = document.querySelectorAll(".group-summary");
    summaryRows.forEach(function(row) {
        row.addEventListener("click", function() {
            var bankAccount = row.getAttribute("data-bank");            
            $.ajax({
                url: "getLoanDetails.php",
                method: "GET",
                data: { bank: bankAccount },
                success: function(data) {
                    document.getElementById("modalContent").innerHTML = data;
                }
            });
            // 임시로 모달 내용에 bankAccount 값을 표시
            document.getElementById("modalContent").innerHTML = "차입계정: " + bankAccount + " 의 상세 내역 로드 중...";
            var modal = new bootstrap.Modal(document.getElementById("loanDetailModal"));
            modal.show();
        });
    });
});
</script>

<div class="d-flex justify-content-center align-items-center mt-2">
<span>
	▷ <?= $total_row ?> &nbsp;
</span>
			
<!-- 기간부터 검색까지 연결 묶음 start -->                    
<span id="showdate" class="btn btn-dark btn-sm">기간</span>   &nbsp; 
			
<div id="showframe" class="card"> 
	<div class="card-header" style="padding:2px;">
		<div class="d-flex justify-content-center align-items-center">  
			기간 설정
		</div>
	</div> 
	<div class="card-body">                                        
		<div class="d-flex justify-content-center align-items-center">      
			<button type="button" class="btn btn-outline-success btn-sm me-1 change_dateRange" onclick='alldatesearch()'>전체</button>  
			<button type="button" class="btn btn-outline-primary btn-sm me-1 change_dateRange" onclick='pre_year()'>전년도</button>  
			<button type="button" class="btn btn-dark btn-sm me-1 change_dateRange" onclick='pre_month()'>전월</button>
			<button type="button" class="btn btn-dark btn-sm me-1 change_dateRange" onclick='dayBeforeYesterday()'>전전일</button>    
			<button type="button" class="btn btn-dark btn-sm me-1 change_dateRange" onclick='yesterday()'>전일</button>                         
			<button type="button" class="btn btn-outline-danger btn-sm me-1 change_dateRange" onclick='this_today()'>오늘</button>
			<button type="button" class="btn btn-dark btn-sm me-1 change_dateRange" onclick='this_month()'>당월</button>
			<button type="button" class="btn btn-dark btn-sm me-1 change_dateRange" onclick='this_year()'>당해년도</button> 
		</div>
	</div>
</div>      

<input type="date" id="fromdate" name="fromdate" class="form-control" style="width:110px;" value="<?=$fromdate?>">  &nbsp;   ~ &nbsp;  
<input type="date" id="todate" name="todate" class="form-control me-1" style="width:110px;" value="<?=$todate?>">  &nbsp;

<div class="inputWrap30">
<input type="text" id="search" class="form-control" style="width:200px;" name="search" value="<?=$search?>" autocomplete="off" onKeyPress="if (event.keyCode==13){ enter(); }">
<button class="btnClear"></button>
</div>
&nbsp;&nbsp;
<button class="btn btn-outline-dark btn-sm" type="button" id="searchBtn"> <i class="bi bi-search"></i> </button> &nbsp;&nbsp;&nbsp;&nbsp;				
<button id="newBtn" type="button" class="btn btn-dark btn-sm me-2"> <i class="bi bi-pencil-square"></i> 대출등록 </button>								
</div>
</div>
<div class="row w400px m-1 mt-2">
<table class="table table-bordered">
<thead class="table-secondary">
	<tr>
		<?php
		$tmp = '  ' . $bankbookOptions[0] . ' (계좌 잔액)  :  ';
		if (isset($finalBalance)) {
			$tmp_balance = number_format($finalBalance);
		}
		?>						
		<th class="text-center" style="width:200px;"> <?=$tmp?> </th>
		<th class="text-end text-primary fw-bold" style="width:100px;"> <?=$tmp_balance?> </th>
	</tr>
</thead>
</table>
</div>
	
<!-- 대한 대출현황 그룹 토글 스크립트 -->
<!-- 그룹 토글 스크립트 -->
<script>
function toggleGroup(groupId) {
    var rows = document.getElementsByClassName('group-' + groupId);
    for (var i = 0; i < rows.length; i++) {
        if (rows[i].style.display === "none") {
            rows[i].style.display = "";
            rows[i].style.backgroundColor = 'gray';
        } else {
            rows[i].style.display = "none";
        }
    }
    var btn = document.getElementById('toggle-btn-' + groupId);
    if (btn.innerHTML.trim() === '+') {
        btn.innerHTML = '-';
    } else {
        btn.innerHTML = '+';
    }
}
</script>

<div class="d-flex justify-content-center align-items-center mt-3 mb-3 btn-alert">
    <span class="text-center fs-5">(주) 대한 대출등록 현황</span>
</div>

<div class="d-flex justify-content-center p-2 align-items-center mt-3 mb-3 btn-alert">
    <table class="table table-hover" id="myTable">
        <thead class="table-secondary">
            <tr>
                <th class="text-center" style="width:100px;">번호</th>
                <th class="text-center" style="width:100px;">대출일</th>
                <th class="text-center" style="width:100px;">만기일</th>
                <th class="text-center" style="width:120px;">차입계정</th>
                <th class="text-center" style="width:120px;">차입금</th>
                <th class="text-center" style="width:150px;">용도</th>
                <th class="text-center" style="width:80px;">담당자</th>
                <th class="text-center" style="width:100px;">담당자 연락처</th>
                <th class="text-center" style="width:80px;">이자율(%)</th>
                <th class="text-center" style="width:100px;">이자납입일</th>
                <th class="text-center" style="width:250px;">대출계좌</th>
                <th class="text-center" style="width:250px;">이자계좌</th>
                <th class="text-center" style="width:200px;">비고</th>
            </tr>
            <tr style="background-color: #808080!important;">
                <th class="text-end" colspan="4"> 합계 &nbsp; </th>
                <th class="text-end fw-bold text-primary" id="totalLoanAmount">
                    <?php
                    // 전체 대출금 합계 (아래 그룹화 전에 계산한 값 사용)
                    echo isset($totalLoanAmount) ? number_format($totalLoanAmount) : '0';
                    ?>
                </th>
                <th class="text-end" colspan="9"></th>
            </tr>
        </thead>
        <?php
        try {
            // 기존 쿼리 실행 ($sql, $sql_params는 미리 정의되었다고 가정)
            $stmh = $pdo->prepare($sql);
            foreach ($sql_params as $param => $value) {
                $stmh->bindValue($param, $value);
            }
            $stmh->execute();
            
            // 그룹화 배열 생성 (차입계정 기준)
            $groupedRows = array();
            $totalLoanAmount = 0;
            while ($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
                // 그룹 기준은 'bank' 컬럼 (차입계정)
                $groupKey = $row['bank'];
                if (!isset($groupedRows[$groupKey])) {
                    $groupedRows[$groupKey] = array();
                }
                $groupedRows[$groupKey][] = $row;
                // 총 차입금(원금) 합계 계산 (콤마 제거 후)
                $totalLoanAmount += floatval(str_replace(',', '', $row['loanAmount']));
            }
            
            $groupIndex = 0;
            $counter = 1;
			
			// echo '<pre>';
			// print_r($groupedRows);
			// echo '</pre>';
			
            // 각 그룹별로 별도의 tbody로 감싸 출력 (요약 행 바로 아래에 상세 행이 위치)
            foreach ($groupedRows as $groupKey => $rows) {
                $groupIndex++;
                $groupCount = count($rows);
                echo "<tbody id='group-body-{$groupIndex}'>";
                if ($groupCount > 1) {
                    // 그룹 내 전체 합계 계산 (대출금)
                    $groupSum = 0;
                    foreach ($rows as $r) {
                        $groupSum += floatval(str_replace(',', '', $r['loanAmount']));
                    }
                    // 그룹 요약 행 (음영 처리)
                    echo '<tr style="background-color: #808080!important;">';
                    echo "<td class='text-center'> <div class='d-flex justify-content-center align-items-center'> <span class='mx-1'> " . $counter . " </span> <button type='button' class='btn btn-primary btn-sm mx-2' id='toggle-btn-{$groupIndex}' onclick=\"event.stopPropagation(); toggleGroup('{$groupIndex}');\">+</button> </div> </td>";
                    // 요약 행의 나머지 셀은 합계만 표시하고 나머지는 '-' 또는 '여러개 데이터'로 처리					
                    echo "<td class='text-center'>-</td>";
                    echo "<td class='text-center'>-</td>";
                    echo "<td class='text-center fw-bold text-primary'>" . htmlspecialchars($groupKey) . "</td>";
                    echo "<td class='text-end fw-bold '> <h6> <span class='badge bg-primary' > " . number_format($groupSum) . " </span> </h6> </td>";					
                    echo "<td class='text-start text-primary fw-bold'>여러개 데이터</td>";
                    echo "<td class='text-center'>-</td>";
                    echo "<td class='text-center'>-</td>";
                    echo "<td class='text-end'>-</td>";
                    echo "<td class='text-center'>-</td>";
                    echo "<td class='text-center'>-</td>";
                    echo "<td class='text-center'>-</td>";
                    echo "<td class='text-start'>-</td>";
                    echo "</tr>";
                    
                    $counter++;
                    
                    // 그룹 상세 내역: 요약 행 바로 아래에 출력 (숨김 처리, 토글 시 표시)
                    for ($i = 0; $i < $groupCount; $i++) {
                        $r = $rows[$i];
                        echo "<tr class='group-{$groupIndex}' style='display:none;' onclick=\"loadForm('update', '{$r['num']}');\">";
                        echo "<td class='text-center'></td>"; // 번호 셀은 비움
                        echo "<td class='text-center'>" . htmlspecialchars($r['loanStartDate']) . "</td>";
                        echo "<td class='text-center'>" . htmlspecialchars($r['maturityDate']) . "</td>";
                        echo "<td class='text-center'>" . htmlspecialchars($r['bank']) . "</td>";
                        echo "<td class='text-end fw-bold text-primary'>" . (is_numeric($r['loanAmount']) ? number_format($r['loanAmount']) : htmlspecialchars($r['loanAmount'])) . "</td>";
                        echo "<td class='text-start'>" . htmlspecialchars($r['content']) . "</td>";
                        echo "<td class='text-center'>" . htmlspecialchars($r['crew']) . "</td>";
                        echo "<td class='text-center'>" . htmlspecialchars($r['crewphone']) . "</td>";
                        echo "<td class='text-end'>" . htmlspecialchars($r['interestRate']) . "%</td>";
                        echo "<td class='text-center'>" . htmlspecialchars($r['interestPaymentDate']) . "</td>";
                        echo "<td class='text-center'>" . htmlspecialchars($r['loanAccount']) . "</td>";
                        echo "<td class='text-center'>" . htmlspecialchars($r['interestAccount']) . "</td>";
                        echo "<td class='text-start'>" . htmlspecialchars($r['memo']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    // 단일 데이터 그룹: 기존 단일 행 형태로 출력
                    $r = $rows[0];
                    echo "<tr onclick=\"loadForm('update', '{$r['num']}');\">";
                    echo "<td class='text-center'>" . $counter . "</td>";
                    echo "<td class='text-center'>" . htmlspecialchars($r['loanStartDate']) . "</td>";
                    echo "<td class='text-center'>" . htmlspecialchars($r['maturityDate']) . "</td>";
                    echo "<td class='text-center'>" . htmlspecialchars($r['bank']) . "</td>";
                    echo "<td class='text-end fw-bold text-primary'>" . (is_numeric($r['loanAmount']) ? number_format($r['loanAmount']) : htmlspecialchars($r['loanAmount'])) . "</td>";
                    echo "<td class='text-start'>" . htmlspecialchars($r['content']) . "</td>";
                    echo "<td class='text-center'>" . htmlspecialchars($r['crew']) . "</td>";
                    echo "<td class='text-center'>" . htmlspecialchars($r['crewphone']) . "</td>";
                    echo "<td class='text-end'>" . htmlspecialchars($r['interestRate']) . "%</td>";
                    echo "<td class='text-center'>" . htmlspecialchars($r['interestPaymentDate']) . "</td>";
                    echo "<td class='text-center'>" . htmlspecialchars($r['loanAccount']) . "</td>";
                    echo "<td class='text-center'>" . htmlspecialchars($r['interestAccount']) . "</td>";
                    echo "<td class='text-start'>" . htmlspecialchars($r['memo']) . "</td>";
                    echo "</tr>";
                    $counter++;
                }
                echo "</tbody>";
            }
        } catch (PDOException $Exception) {
            print "오류: " . $Exception->getMessage();
        }
        ?>
    </table>
</div>
</div>	
</form>

<script>
	// 총 차입금 합계 표시
	document.getElementById('totalLoanAmount').innerText = "<?= number_format($totalLoanAmount) ?>";
</script>

<script>
// 페이지 로딩
$(document).ready(function(){    
    var loader = document.getElementById('loadingOverlay');
    loader.style.display = 'none';
});

function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

$(document).ready(function() {	
	const initialBalance = <?= json_encode($initialBalance) ?>;
	const finalBalance = <?= json_encode($finalBalance) ?>;    	

	dataTable = $('#myTable').DataTable({
		"paging": false,
		"ordering": false,
		"searching": false,
		"pageLength": 20,
		"lengthMenu": [20,50,100],
		"language": {
			"lengthMenu": "Show _MENU_ entries",
			"search": "Live Search:"
		},
		"order": [[0, 'desc']],
		"dom": 't<"bottom"ip>',
		"columnDefs": [
			{
				"orderable": true, // 정렬 활성화
				"targets": [0, 1, 2, 3, 4, 5, 6] // 정렬 가능하도록 설정할 열 인덱스
			},
			{
				"orderable": false, // 정렬 비활성화
				"targets": [7, 8] // 나머지 열 (적요 열) 비활성화
			}
		],
		"footerCallback": function (row, data, start, end, display) {
			var api = this.api();

			var intVal = function (i) {
				return typeof i === 'string' ?
					i.replace(/[\$,]/g, '')*1 :
					typeof i === 'number' ?
						i : 0;
			};

			// Calculate the totals for Income, Expense, and Balance
			var totalIncomeAmount = api.column(5, { page: 'current' }).data().reduce(function (a, b) { return intVal(a) + intVal(b); }, 0);
			var totalExpenseAmount = api.column(6, { page: 'current' }).data().reduce(function (a, b) { return intVal(a) + intVal(b); }, 0);                

			// Update the header with the calculated totals
			$('#totalIncomeAmount').html(numberWithCommas(totalIncomeAmount));
			$('#totalExpenseAmount').html(numberWithCommas(totalExpenseAmount));
			$('#totalBalanceAmount').html(numberWithCommas(finalBalance));
		}
	});
});

</script>

<script>

let isSaving = false;
var ajaxRequest = null;
var ajaxRequest_SubOption = null;

document.addEventListener('DOMContentLoaded', function() {
    
    $("#newBtn").on("click", function() {
        loadForm('insert');
    });  
	
    $("#billRegistBtn").on("click", function() {
        loadForm_registBill('insert');
    });

    $("#searchBtn").on("click", function() {
        $("#board_form").submit();
    });


});

function loadForm_registBill(mode, num = null) { 
    if (num == null) {
        $("#mode").val('insert');
    } else {
        $("#mode").val('update');
        $("#num").val(num);
    }

    if (ajaxRequest !== null) {
        ajaxRequest.abort();
    }
    ajaxRequest = $.ajax({
        type: "POST",
        url: "fetch_modal_registBill.php",
        data: { mode: mode, num: num },
        dataType: "html",
        success: function(response) {
            document.querySelector(".modal-body .custom-card").innerHTML = response;			

            $("#myModal").show();
                        
			// PHP 데이터를 JSON으로 인코딩
			const incomeOptions = <?php echo json_encode($incomeOptions, JSON_UNESCAPED_UNICODE); ?>;
			const expenseOptions = <?php echo json_encode($expenseOptions, JSON_UNESCAPED_UNICODE); ?>;

			function updateContentOptions() {
				const contentSelect = document.getElementById('content');
				if (contentSelect) {
					contentSelect.innerHTML = '';

					const options = document.querySelector('input[name="inoutsep"]:checked').value === '수입' ? incomeOptions : expenseOptions;

					for (const [value, text] of Object.entries(options)) {
						const option = document.createElement('option');
						option.value = value;
						option.text = value;
						contentSelect.appendChild(option);
					}

				}
			}
           
			$(document).on("click", "#closeBtn", function() {
				$("#myModal").hide();
			});


			$(document).on("click", "#BillsaveBtn", function() {
				// if (isSaving) return;
				// isSaving = true;

					// AJAX 요청을 보냄
				if (ajaxRequest !== null) {
					ajaxRequest.abort();
				}
				ajaxRequest = $.ajax({
						url: "/accountLoan/insert_Bill.php",
						type: "post",
						data: {
							mode: $("#mode").length ? $("#mode").val() : '',
							num: $("#num").length ? $("#num").val() : '',
							update_log: $("#update_log").length ? $("#update_log").val() : '',
							registDate: $("#registDate").length ? $("#registDate").val() : '',
							inoutsep: $("input[name='inoutsep']:checked").length ? $("input[name='inoutsep']:checked").val() : '',
							content: $("#content").length ? $("#content").val() : '',
							amount: $("#amount").length ? $("#amount").val() : '',
							memo: $("#memo").length ? $("#memo").val() : '',
							first_writer: $("#first_writer").length ? $("#first_writer").val() : '',
							content_detail: $("#content_detail").length ? $("#content_detail").val() : '',
							contentSub: $("#contentSub").length ? $("#contentSub").val() : '',
							bankbook: $("#bankbook").length ? $("#bankbook").val() : '',
							secondordnum: $("#secondordnum").length ? $("#secondordnum").val() : ''
						},
						dataType: "json",
						success: function(response) {
							// console.log('call data',data);
							// console.log('response : ',response);
							
							Toastify({
								text: "저장 완료",
								duration: 3000,
								close: true,
								gravity: "top",
								position: "center",
								backgroundColor: "#4fbe87",
							}).showToast();
											
							setTimeout(function() {
								ajaxRequest = null ;
								$("#myModal").hide();
								location.reload();								
							}, 1500); // 1.5초 후 실행
							
						},
						error: function(jqxhr, status, error) {
							console.log("AJAX Error: ", status, error);
							ajaxRequest = null ;
							// isSaving = false;
						}
					});
				});

				$(document).on("click", "#BilldeleteBtn", function() {    
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

							$.ajax({
								url: "/accountLoan/insert_Bill.php",
								type: "post",
								data: formData,
								success: function(response) {
									console.log(response);
									Toastify({
										text: "파일 삭제완료",
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

				$(document).on("change", "input[name='inoutsep']", updateContentOptions);					

        },
        error: function(jqxhr, status, error) {
            console.log("AJAX error in loadForm:", status, error);
        }
    });
}

// function updateSubOptions() {
	// const contentSelect = document.getElementById('content');
	// const contentSubSelect = document.getElementById('contentSub');
	// const selectedValue = contentSelect.value;

	// if (ajaxRequest !== null) {
		// ajaxRequest.abort();
	// }

	// // AJAX 요청으로 세부항목 가져오기
	// ajaxRequest = $.ajax({
		// url: 'fetch_modal_registBill.php',
		// type: 'POST',
		// data: { action: 'getSubOptions', selectedKey: selectedValue },
		// dataType: 'json',
		// success: function(response) {
			// // 기존 옵션 초기화
			// contentSubSelect.innerHTML = '';

			// if (response.subOptions && response.subOptions.length > 0) {
				// // 새로운 옵션 추가
				// response.subOptions.forEach(item => {
					// for (const key in item) {
						// const option = document.createElement('option');
						// option.value = key;
						// option.text = key;
						// contentSubSelect.appendChild(option);
					// }
				// });
			// } else {
				// // 세부항목이 없는 경우 기본값 처리
				// const option = document.createElement('option');
				// option.value = '';
				// option.text = '세부항목 없음';
				// contentSubSelect.appendChild(option);
			// }
		// },
		// error: function(jqxhr, status, error) {
			// console.log("AJAX Error:", status, error);
		// }
	// });
// }
	
// content 선택 변경 이벤트
// $(document).on('change', '#content', updateSubOptions);

function loadForm(mode, num = null) {
    if (num == null) {
        $("#mode").val('insert');
    } else {
        $("#mode").val('update');
        $("#num").val(num);
    }

    if (ajaxRequest !== null) {
        ajaxRequest.abort();
    }
    ajaxRequest = $.ajax({
        type: "POST",
        url: "fetch_modal.php",
        data: { mode: mode, num: num },
        dataType: "html",
        success: function(response) {
			// console.log(response);
            document.querySelector(".modal-body .custom-card").innerHTML = response;			

            $("#myModal").show();
                       			           
			$(document).on("click", "#closeBtn", function() {
				$("#myModal").hide();
			});

			$(document).on("click", "#saveBtn", function() {
				// 기존 요청이 있으면 중단
				if (ajaxRequest !== null) {
					ajaxRequest.abort();
				}
				
				// AJAX 요청 실행
				ajaxRequest = $.ajax({
					url: "/accountLoan/insert.php",
					type: "post",
					data: {
						mode: $("#mode").val(),
						num: $("#num").val(),
						loanStartDate: $("#loanStartDate").val(),
						bank: $("#bank").val(),
						loanAmount: $("#loanAmount").val(),
						content: $("#content").val(),
						interestRate: $("#interestRate").val(),
						interestPaymentDate: $("#interestPaymentDate").val(),
						memo: $("#memo").val(),
						crew: $("#crew").val(),
						crewphone: $("#crewphone").val(),
						loanAccount: $("#loanAccount").val(),
						interestAccount: $("#interestAccount").val(),
						maturityDate: $("#maturityDate").val(),
						is_deleted: $("#is_deleted").val()
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
						}, 1500); // 1.5초 후 실행
					},
					error: function(jqxhr, status, error) {
						console.log("AJAX Error: ", status, error);
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

							$.ajax({
								url: "/accountLoan/insert.php",
								type: "post",
								data: formData,
								success: function(response) {
									Toastify({
										text: "파일 삭제완료",
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

function enter() {
    $("#board_form").submit();
}

</script>

<!-- 부트스트랩 툴팁 -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });  
  	// $("#order_form_write").modal("show");	  
});

$(document).ready(function(){
	saveLogData('대출금 상환현황'); 
	var totalAmount_Bill = '<?php echo $totalAmount_Bill; ?>';

	// 숫자로 변환 후 콤마 추가
	totalAmount_Bill = parseFloat(totalAmount_Bill).toLocaleString();

	$("#totalAmount_Bill").text(totalAmount_Bill);

});
</script>

</body>
</html>
