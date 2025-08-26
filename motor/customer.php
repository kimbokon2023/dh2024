<?php
// 거래처 원장 customer.php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php"); 
    exit;
}

include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';

// 첫 화면 표시 문구
$title_message = '거래처 원장(VAT 포함)'; 

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

<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php'); ?>     

<?php
$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';  
$fromdate = isset($_REQUEST['fromdate']) ? $_REQUEST['fromdate'] : '';  
$todate = isset($_REQUEST['todate']) ? $_REQUEST['todate'] : '';  
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  

// 현재 날짜
$currentDate = date("Y-m-d");

// fromdate 또는 todate가 빈 문자열이거나 null인 경우
if ($fromdate === "" || $fromdate === null || $todate === "" || $todate === null) {
    // 현재 월의 1일을 fromdate로 설정
    $fromdate = date("Y-m-01");	
    // // fromdate를 이전 달의 1일로 설정
	// $fromdate = date("Y-m-01", strtotime("first day of -1 month"));	
    $todate = $currentDate;
    $Transtodate = $todate;
} else {
    $Transtodate = $todate;
}

// 시작일과 종료일을 "8월1일~9월1일" 형태로 포맷팅
$formatted_date_range = date("n월j일", strtotime($fromdate)) . '~' . date("n월j일", strtotime($todate));

function checkNull($strtmp) {
    return $strtmp !== null && trim($strtmp) !== '';
}

$tablename = 'phonebook';

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

// 이월잔액 계산을 위한 로직 (매출 - 수금)
$initialBalances = [];

// 이월 잔액을 계산할 기준 날짜
$lastMonthEnd = date("Y-m-d", strtotime($fromdate . " -1 day"));

// echo '<pre>';
// echo '이월잔액 기간 lastMonthEnd: ';
// print_r($lastMonthEnd);
// echo '</pre>';

$searchsecondordnum = '';
if(!empty($search))
{
	$sql = "SELECT secondordnum FROM ".$DB.".".$tablename." 
		WHERE (is_deleted IS NULL OR is_deleted = 0  or is_deleted ='' )  
	    AND represent='아이디부여' AND (vendor_name LIKE '%$search%')";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	$searchsecondordnum = $row['secondordnum'];
}

// 거래처별 매출 및 수금 데이터 계산
$salesBeforeSql = "
    SELECT secondordnum, SUM(CAST(REPLACE(totalprice, ',', '') AS SIGNED)) AS total_sales 
    FROM {$DB}.motor
    WHERE deadline <= :lastMonthEnd AND (is_deleted IS NULL or is_deleted=0 or is_deleted ='' )  
    GROUP BY secondordnum
";

$paymentBeforeSql = "
    SELECT secondordnum, SUM(CAST(REPLACE(amount, ',', '') AS SIGNED)) AS total_payment 
    FROM {$DB}.account
    WHERE registDate <= :lastMonthEnd AND (is_deleted IS NULL or is_deleted=0 or is_deleted ='' )  AND content = '거래처 수금'
    GROUP BY secondordnum
";

$salesBeforeStmt = $pdo->prepare($salesBeforeSql);
$salesBeforeStmt->execute([':lastMonthEnd' => $lastMonthEnd]);
$salesBeforeData = $salesBeforeStmt->fetchAll(PDO::FETCH_ASSOC);

$paymentBeforeStmt = $pdo->prepare($paymentBeforeSql);
$paymentBeforeStmt->execute([':lastMonthEnd' => $lastMonthEnd]);
$paymentBeforeData = $paymentBeforeStmt->fetchAll(PDO::FETCH_ASSOC);

// 거래처별로 매출과 수금을 비교해 이월 잔액을 계산
foreach ($salesBeforeData as $row) {
    $secondordnum = $row['secondordnum'];
    $total_sales = (float)$row['total_sales'] * 1.1;

    if (!isset($initialBalances[$secondordnum])) {
        $initialBalances[$secondordnum] = 0;
    }

    // 이월 잔액에 매출 더하기
    $initialBalances[$secondordnum] += $total_sales;
}

// echo '<pre>';
// print_r($salesBeforeData);
// echo '</pre>';

// 수금 데이터를 이용해 이월 잔액에서 수금을 차감
foreach ($paymentBeforeData as $row) {
    $secondordnum = $row['secondordnum'];
    $total_payment = (float)$row['total_payment'];

    if (!isset($initialBalances[$secondordnum])) {
        $initialBalances[$secondordnum] = 0;
    }

    // 이월 잔액에서 수금 차감
    $initialBalances[$secondordnum] -= $total_payment;
}

// echo '<pre>';
// print_r($initialBalances);
// echo '</pre>';

// 매출이 발생한 거래처 필터링 및 매출액 계산
$salesSql = "
    SELECT secondordnum, totalprice  
    FROM {$DB}.motor
    WHERE (deadline BETWEEN date('$fromdate') AND date('$Transtodate')) AND (is_deleted IS NULL OR is_deleted = 0  or is_deleted ='' ) 
";
$salesStmt = $pdo->prepare($salesSql);
$salesStmt->execute();
$salesData = $salesStmt->fetchAll(PDO::FETCH_ASSOC);

$salesResults = [];
foreach ($salesData as $row) {
    $secondordnum = $row['secondordnum'];
	$total_sales = 0;
    $total_sales = (float)str_replace(',', '', $row['totalprice']);  // 콤마 제거 후 매출액 가져옴

    if (!isset($salesResults[$secondordnum])) {
        $salesResults[$secondordnum] = 0;
    }
    $salesResults[$secondordnum] += round($total_sales * 1.1);
}

// echo '<pre>';
// echo '매출발생 시작 fromdate: ';
// print_r($fromdate);
// echo '</pre>';

// echo '<pre>';
// echo '매출발생 종료 Transtodate: ';
// print_r($Transtodate);
// echo '</pre>';

// echo '<pre>';
// echo '이월잔액배열 주일기업 추적 initialBalances: ';
// print_r(number_format(round($initialBalances['34'])));  // 주일기업 34번 
// echo '</pre>';


// 모든 거래처 목록을 처리하기 전에 거래처 이름을 저장하는 배열 초기화
$vendorNames = []; 
// 모든 거래처 목록을 생성 (매출, 기초채권)
$allResults = array_unique(array_merge(array_keys($salesResults), array_keys($initialBalances)));

// 매출 금액 기준으로 역순으로 정렬
usort($allResults, function($a, $b) use ($salesResults) {
    $salesA = isset($salesResults[$a]) ? $salesResults[$a] : 0;
    $salesB = isset($salesResults[$b]) ? $salesResults[$b] : 0;
    return round($salesA - $salesB);
});

// 합계를 저장할 변수들
$totalInitialReceivable = 0;
$totalSalesAmount = 0;
$totalPaymentAmount = 0;
$totalBalanceDue = 0;

try {	
    $start_num = 1;                
    foreach ($allResults as $ordnum) {
        // 이월 잔액 설정
        $initialReceivable = isset($initialBalances[$ordnum]) ? $initialBalances[$ordnum] : 0;

		// 수금 내역 가져오기
		$paymentSql = "SELECT SUM(CAST(REPLACE(amount, ',', '') AS SIGNED)) as total_payment 
					   FROM ".$DB.".account 
					   WHERE secondordnum = '$ordnum'
					   AND registDate BETWEEN '1970-01-01' AND date('$Transtodate') 
					   AND (is_deleted IS NULL OR is_deleted = 0  or is_deleted ='' ) 
					   AND content = '거래처 수금'";

        $paymentStmt = $pdo->prepare($paymentSql);
        $paymentStmt->execute();
        $paymentData = $paymentStmt->fetch(PDO::FETCH_ASSOC);
        $total_payment = isset($paymentData['total_payment']) ? (int)str_replace(',', '', $paymentData['total_payment']) : 0;

        $total_sales = isset($salesResults[$ordnum]) ? $salesResults[$ordnum] : 0;

        // 조건: 기초채권이 있거나 매출이 있는 경우만 표시
        if ($initialReceivable != 0 || $total_sales != 0 ) {		   // 매출이 있는 것을 추출
            $sql = "SELECT * FROM ".$DB.".".$tablename." 
                    WHERE secondordnum = '$ordnum'
                      AND (is_deleted IS NULL OR is_deleted = 0  or is_deleted ='' )  
                      AND represent='아이디부여'";    

            $stmh = $pdo->query($sql); 
            while ($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
                include $_SERVER['DOCUMENT_ROOT'] . '/phonebook/_row.php';        

                if (intval($ordnum) > 0)
				{
                    $savenum = $ordnum;									
					// 거래처 이름 저장
					$vendorNames[$ordnum] =$vendor_name ; 
				}
                else
                    $savenum = $num;

                $total_amount = round($total_sales ) ;

                // 잔액 계산
                $balance_due = round($initialReceivable) + $total_amount - round($total_payment);

				$totalInitialReceivable += $initialReceivable;
				$totalSalesAmount += $total_amount;
				$totalPaymentAmount += $total_payment;
				$totalBalanceDue += $balance_due;

            } 
        }
    }
} catch (PDOException $Exception) {
    print "오류: ".$Exception->getMessage();
}

// echo '<pre>';
// print_r($vendorNames);
// echo '</pre>';

// 거래처 이름으로 정렬
usort($allResults, function($a, $b) use ($vendorNames) {
    // 거래처 이름이 없는 경우 빈 문자열로 처리
    $nameA = $vendorNames[$a] ?? '';
    $nameB = $vendorNames[$b] ?? '';
    return strcmp($nameA, $nameB);
});

// echo $sql;
// echo '<pre>';
// print_r($allResults);
// echo '</pre>';	
  
// $search값이 있다면 // 특정 숫자 67만 남기고 필터링
// echo 'searchsecondordnum : ' . $searchsecondordnum;
if (!empty($search)) {
    $allResults = array_filter($allResults, function ($value) use ($searchsecondordnum) {
        return $value === intval($searchsecondordnum);
    });

    // array_values로 인덱스 재정렬
    $allResults = array_values($allResults);
}

// 중복 제거
$allResults = array_unique($allResults);


// 중복 제거
$allResults = array_unique($allResults);

// 빈 값 제거
$allResults = array_filter($allResults, function ($value) {
    return $value !== null && $value !== '';
});

// 배열 키 재정렬
$allResults = array_values($allResults);

// echo '<pre>';
// print_r($allResults);
// echo '</pre>';	

?>

<form id="board_form" name="board_form" method="post" enctype="multipart/form-data">             
<div class="container mb-5"> 
    <input type="hidden" id="mode" name="mode" value="<?=$mode?>">             
    <input type="hidden" id="num" name="num"> 
    <input type="hidden" id="tablename" name="tablename" value="<?=$tablename?>">                 
    <input type="hidden" id="header" name="header" value="<?=$header?>">                 
    <input type="hidden" id="secondordnum" name="secondordnum" value="<?=$secondordnum?>">                 
    <div class="d-flex p-1 m-1 mt-1 mb-1 justify-content-center align-items-center">       
        <span class="text-center fs-5 me-4"><?=$title_message?></span>    
        <button type="button" class="btn btn-dark btn-sm me-4" onclick='location.href="customer.php"'> 
            <i class="bi bi-arrow-clockwise"></i> 
        </button>        
		   <!-- <span class="badge bg-primary"> (한빛에스티) 매월25일 마감 </span> -->
        <small class="ms-5 text-muted"> 기간별 설정해서 거래처별 원장을 검색합니다. </small>  
    </div>     
	
    <div class="card-body">                               
        <div class="d-flex p-1 m-1 mt-1 mb-1 justify-content-start align-items-center">       
        <!-- 우측을 가리키는 삼각형 아이콘 -->
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-caret-right-fill me-2" viewBox="0 0 16 16" style="vertical-align:middle;">
            <path d="M12.14 8.753l-6.482 4.796A.5.5 0 0 1 5 13.5V2.5a.5.5 0 0 1 .658-.478l6.482 4.796a.5.5 0 0 1 0 .935z"/>
        </svg>
        <span id="total"> </span> &nbsp;

            <!-- 기간부터 검색까지 연결 묶음 start -->                    
            <small class="d-block text-muted text-center mt-1 mx-2">
                [기간]버튼에 커서를 올리면 전체, 전년도, 전월등 세부 내용을 검색 가능합니다.
            </small>
            <span id="showdate" class="btn btn-dark btn-sm mx-2">기간</span>            
            
            <div id="showframe" class="card" style="width:300px;"> 
                <div class="card-header" style="padding:2px;">
                    <div class="d-flex justify-content-center align-items-center">  
                        기간 설정
                    </div>
                </div> 
                <div class="card-body">										
                    <div class="d-flex justify-content-center align-items-center">                                                              
                        <button type="button" class="btn btn-dark btn-sm me-1 change_dateRange" onclick='prepre_month()'>전전월</button>                            
                        <button type="button" class="btn btn-dark btn-sm me-1 change_dateRange" onclick='pre_month()'>전월</button>                            
                        <button type="button" class="btn btn-dark btn-sm me-1 change_dateRange" onclick='this_month()'>당월</button>
                        <button type="button" class="btn btn-dark btn-sm me-1 change_dateRange" onclick='this_year()'>당해년도</button> 
                    </div>
                </div>
            </div>      

        <input type="date" id="fromdate" name="fromdate" class="form-control" style="width:100px;" value="<?=$fromdate?>">  &nbsp;   ~ &nbsp;  
        <input type="date" id="todate" name="todate" class="form-control me-1" style="width:100px;" value="<?=$todate?>">  &nbsp;     </span> 
        </div>
        <div class="d-flex p-1 m-1 mt-1 mb-1 justify-content-center align-items-center">       
        
        <div class="inputWrap">
                <input type="text" id="search" name="search" value="<?=$search?>" onkeydown="if(event.key === 'Enter') submitForm();" autocomplete="off" class="form-control" style="width:150px;"> &nbsp;           
                <button class="btnClear"></button>
        </div>              
          
        <div id="autocomplete-list">                       
        </div>  
          &nbsp; 
            <button id="searchBtn" type="button" class="btn btn-dark btn-sm me-2" onclick="submitForm()"> <i class="bi bi-search"></i> </button>                              
			* 출고예정일 기준입니다. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<button type="button" class="btn btn-dark btn-sm me-2" onclick="location.href='../getmoney/list.php?header=header'"><i class="bi bi-journal-x"></i> 수금 </button>    
			<button type="button" class="btn btn-dark btn-sm me-1" onclick="location.href='month_sales.php?header=header'"> <i class="bi bi-file-earmark-ruled"></i> 판매일괄회계</button>
			<button type="button" class="btn btn-primary btn-sm me-1" onclick="saveBalance();"> <i class="bi bi-floppy"></i> 차기월 이월금확정</button>
			<button type="button" class="btn btn-danger btn-sm me-2" onclick="location.href='../motor/receivable.php?header=header'"> <i class="bi bi-journal-x"></i> 미수금 </button>    
			<button type="button" class="btn btn-dark btn-sm me-2" onclick="generateExcel();" > <i class="bi bi-file-earmark-spreadsheet"></i> </button>
         </div> 
		
    <div class="d-flex p-1 m-1 mb-1 justify-content-center align-items-center">     
     <table class="table table-hover" id="myTable">        
            <thead class="table-info"> 
                 <th class="text-center w80px">번호</th>                 
                 <th class="text-center w200px">거래처명</th>                  
                 <th class="text-center w140px">이월잔액</th>
                 <th class="text-center w140px"> 매출</th>
                 <th class="text-center w140px">수금</th>
                 <th class="text-center w140px">잔액</th>
                 <th class="text-center w60px">결제일</th>
                 <th class="text-center w140px">적요</th>
				 <?php if($user_id == 'pro') 
					 print '<th class="text-center w50px">거래처 Code</th>';
				 ?>
                 <th style="display:none;" ></th>
            </thead>
         <tbody>                			
<?php  
try {	
	$start_num = 1;	
		
	foreach ($allResults as $initnum) {
		
		// echo 'second ord num '. $initnum . '<br>';
        // 이월잔액 설정
        $initialReceivable = isset($initialBalances[$initnum]) ? intval($initialBalances[$initnum]) : 0;
		// 마지막 자릿수가 1인지 확인
		if (floatval($initialReceivable) % 10 === 1) {
			// 마지막 자릿수를 제거 (정수로 처리)
			$initialReceivable = floor($initialReceivable / 10)*10;
		}
		
		// echo '거래처 $initnum' . $initnum . ' : ', $initialReceivable  . ' <br> ' ;

		// 수금 내역 가져오기
		$paymentSql = "SELECT SUM(CAST(REPLACE(amount, ',', '') AS SIGNED)) as total_payment 
					   FROM ".$DB.".account 
					   WHERE secondordnum = '$initnum'
					   AND registDate BETWEEN date('$fromdate') AND date('$Transtodate') 
					   AND (is_deleted IS NULL OR is_deleted = 0  or is_deleted ='' )
					   AND content = '거래처 수금'";

        $paymentStmt = $pdo->prepare($paymentSql);
        $paymentStmt->execute();
        $paymentData = $paymentStmt->fetch(PDO::FETCH_ASSOC);
        $total_payment = isset($paymentData['total_payment']) ? (int)str_replace(',', '', $paymentData['total_payment']) : 0;

        $total_sales = isset($salesResults[$initnum]) ? $salesResults[$initnum] : 0;
		// print $total_sales;
        // 조건: 이월잔액이 있거나 매출이 있는 경우만 표시
		// 검색어가 있는 경우 검색어 있는 것만 나오게 함
		// echo 'searchsecondordnum : ' . $searchsecondordnum;
		
	if ( ($initialReceivable != 0 or $total_sales != 0) and intval($initnum) > 1 ) {
		
		$sql = "SELECT * FROM ".$DB.".".$tablename." 
				WHERE secondordnum = '$initnum'
				  AND (is_deleted IS NULL OR is_deleted = 0 or is_deleted ='' ) 
				  AND represent='아이디부여'";
               
        $total_amount = round($total_sales,2);

        // 잔액 계산
        $balance_due = $initialReceivable + $total_amount - $total_payment;
	    $memo = '';
		// if($balance_due>0 && $initialReceivable>0 )
		
		// (주)한빛에스티는 25일 마감 예외처리해야함.	
		// if($vendor_name === '㈜ 한빛에스티')
			// $vendor_name = '㈜ 한빛에스티(월마감25일)';
						
		// 마지막 단위 원단위 중 1은 제거하는 로직
		// 당월매출 마지막 1원 삭제
		// 마지막 자리가 1로 끝나는 경우, 0으로 변경
		// print_r($total_payment);
		// $totalSalesAmount -= 5; 
		if (round($total_amount,2) % 10 === 1) {
			$total_amount -= 1;  // 1을 빼서 마지막 자리를 0으로 만듭니다.
		}

		if ($balance_due % 10 === 1) {
			$balance_due -= 1;  // 1을 빼서 마지막 자리를 0으로 만듭니다.
		}
		// print_r($balance_due);
		
		if (intval($balance_due) !== 0 || $total_sales > 0 ) {
		?>                     
		<tr onclick="redirectToView('<?= $initnum ?>')">
			<td class="text-center"><?= $start_num ?></td>                
			<td class="text-start text-primary"><?= $vendorNames[$initnum] ?></td>    
			 <td class="text-end text-primary fw-bold"><?= number_format($initialReceivable) ?></td>    
			<td class="text-end text-secondary fw-bold"><?= number_format($total_amount) ?></td>    
			<td class="text-end fw-bold"><?= number_format($total_payment) ?></td>
			<td class="text-end fw-bold"><?= number_format($balance_due) ?></td>
            <td class="text-end text-primary fw-bold">
                <?php if (!empty($paydate)) : ?>
                    <?= htmlspecialchars($paydate) ?>
                <?php endif; ?>
            </td>
			<td class="text-end"><?= $memo ?></td>
			<?php if($user_id == 'pro') 
					 echo '<td class="text-center w50px"> ' . $initnum . ' </td>';
			 ?>
			<td style="display:none;"><?= $initnum ?></td>
		</tr>
		<?php			
		$start_num++;		
    } 
  }
}
?>         
       
      </tbody>
      <tfoot class="table-secondary">
            <tr>
                <th class="text-end w80px" colspan="2"> 합계 &nbsp; </th>
                <th class="text-end"><?= number_format($totalInitialReceivable) ?></th>
                <th class="text-end"><?= number_format($totalSalesAmount) ?></th>
                <th class="text-end"><?= number_format($totalPaymentAmount) ?></th>
                <th class="text-end"><?= number_format($totalBalanceDue) ?></th>
                <th class="text-end">&nbsp;  </th>
                <th class="text-end w150px">&nbsp;  </th>
            </tr>
        </tfoot>
     </table>
    </div>
</div>
</form>
<?php
} catch (PDOException $Exception) {
    print "오류: ".$Exception->getMessage();
}
?>
<!-- 페이지로딩 -->
<script>
    // 페이지 로딩
    $(document).ready(function(){    
        var loader = document.getElementById('loadingOverlay');
        loader.style.display = 'none';
    });

    function submitForm() {
        $('#board_form').submit();
    }
</script>

<script>
var dataTable; // DataTables 인스턴스 전역 변수
var bookpageNumber; // 현재 페이지 번호 저장을 위한 전역 변수

$(document).ready(function() {            
    // DataTables 초기 설정
    dataTable = $('#myTable').DataTable({
        "paging": true,
        "ordering": true,
        "searching": true,
        "pageLength": 100,
        "lengthMenu": [100, 200, 500, 1000],
        "language": {
            "lengthMenu": "Show _MENU_ entries",
            "search": "Live Search:"
        },
        // "order": [[8, 'desc']], // 잔액기준 내림차순 정렬
        "dom": 't<"bottom"ip>', // search 창과 lengthMenu 숨기기
        "footerCallback": function ( row, data, start, end, display ) {
            var api = this.api(), data;
            
            // 합계를 계산하는 함수
            var intVal = function (i) {
                return typeof i === 'string' ?
                    i.replace(/[\$,]/g, '')*1 :
                    typeof i === 'number' ?
                        i : 0;
            };
            
            // 합계 계산
            totalInitialReceivable = api.column(2).data().reduce(function (a, b) { return intVal(a) + intVal(b); }, 0);
            totalSalesAmount = api.column(3).data().reduce(function (a, b) { return intVal(a) + intVal(b); }, 0);
            totalPaymentAmount = api.column(4).data().reduce(function (a, b) { return intVal(a) + intVal(b); }, 0);
            totalBalanceDue = api.column(5).data().reduce(function (a, b) { return intVal(a) + intVal(b); }, 0);

            // 합계 출력
            $(api.column(2).footer()).html(numberWithCommas(totalInitialReceivable));
            $(api.column(3).footer()).html(numberWithCommas(totalSalesAmount));
            $(api.column(4).footer()).html(numberWithCommas(totalPaymentAmount));
            $(api.column(5).footer()).html(numberWithCommas(totalBalanceDue));
        }
    });

    // 페이지 번호 복원 (초기 로드 시)
    var savedPageNumber = getCookie('bookpageNumber');
    if (savedPageNumber) {
        dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
    }

    // 페이지 변경 이벤트 리스너
    dataTable.on('page.dt', function() {
        var bookpageNumber = dataTable.page.info().page + 1;
        setCookie('bookpageNumber', bookpageNumber, 10); // 쿠키에 페이지 번호 저장
    });

    // 페이지 길이 셀렉트 박스 변경 이벤트 처리
    $('#myTable_length select').on('change', function() {
        var selectedValue = $(this).val();
        dataTable.page.len(selectedValue).draw(); // 페이지 길이 변경 (DataTable 파괴 및 재초기화 없이)

        // 변경 후 현재 페이지 번호 복원
        savedPageNumber = getCookie('bookpageNumber');
        if (savedPageNumber) {
            dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
        }
    });
	
	var total = '<?php echo $start_num; ?>';
	$("#total").text(Number(total)-1);
	
});

function redirectToView(num) {    
    var fromdate = document.getElementById('fromdate').value;
    var todate = document.getElementById('todate').value;
    var url = "customer_sheet.php?num=" + num + "&fromdate=" + fromdate + "&todate=" + todate;
    customPopup(url, '거래원장', 1000, 850);             
}

function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function saveBalance() {
    // 테이블 데이터를 수집
    let data = [];
    let closure_date = $('#todate').val();
    let year = closure_date.split('-')[0];
    let month = closure_date.split('-')[1];
    let dayOfMonth = parseInt(closure_date.split('-')[2]);

    // 해당 월의 마지막 날 계산
    let lastDayOfMonth = new Date(year, month, 0).getDate();

    // 날짜가 말일 또는 25일이 아닌 경우 경고 메시지 출력
    if (dayOfMonth !== lastDayOfMonth && dayOfMonth !== 25) {
        alert('해당일자로 이월 마감할 수 없습니다.');
        return; // 함수 종료
    }

    $('#myTable tbody tr').each(function() {
        let row = $(this);
        let balance = parseFloat(row.find('td:nth-child(6)').text().replace(/,/g, ''));
        let secondordnum = row.find('td:nth-child(9)').text().trim();

        // 매월 말일인 경우
        if (dayOfMonth === lastDayOfMonth) {
            // '66'이 아닌 데이터만 처리
            if (secondordnum !== '66' && balance !== 0) {
                data.push({
                    mode: row.attr('data-mode') || 'insert',  // insert or update mode
                    num: row.attr('data-num'),
                    secondordnum: secondordnum,
                    customer_name: row.find('td:nth-child(2)').text().trim(),
                    balance: balance,
                    closure_date: closure_date,
                    memo: row.find('td:nth-child(8)').text().trim()
                });
            }
        }
        // 25일인 경우
        else if (dayOfMonth === 25) {
            // '66' 데이터만 처리
            if (secondordnum === '66' && balance !== 0) {
                data.push({
                    mode: row.attr('data-mode') || 'insert',  // insert or update mode
                    num: row.attr('data-num'),
                    secondordnum: secondordnum,
                    customer_name: row.find('td:nth-child(2)').text().trim(),
                    balance: balance,
                    closure_date: closure_date,
                    memo: row.find('td:nth-child(8)').text().trim()
                });
            }
        }
    });

    // Ajax로 데이터 전송
    if (data.length > 0) {
        $.ajax({
            url: 'insert_monthly_balance.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            success: function(response) {
                let result = JSON.parse(response);
                if (result.status === 'success') {
                    alert('이월금액이 성공적으로 저장되었습니다.');
                } else {
                    alert('오류 발생: ' + result.message);
                }
            },
            error: function(xhr, status, error) {
                alert('오류 발생: ' + error);
            }
        });
    } else {
        alert('저장할 데이터가 없습니다.');
    }
}

function detail() {
    // 년도, 시작 월, 종료 월 값을 가져옴
    const year = document.getElementById('year').value;
    const startMonth = document.getElementById('startMonth').value;
    const endMonth = document.getElementById('endMonth').value;

    // detail.php로 이동할 URL 생성
    const url = `detail.php?year=${year}&startMonth=${startMonth}&endMonth=${endMonth}`;

    // customPopup을 사용하여 detail.php를 팝업으로 열기
    customPopup(url, '상세 내역', 900, 700);
}


function generateExcel() {
    var table = document.getElementById('myTable');
    var rows = table.getElementsByTagName('tr');
    var data = [];

    // 각 행을 반복하여 데이터 수집
    for (var i = 1; i < rows.length; i++) { // 헤더 행을 건너뜀
        var cells = rows[i].getElementsByTagName('td');
        var rowData = {};
        rowData['number'] = cells[0]?.innerText || '';
        rowData['secondord'] = cells[1]?.innerText || '';
        rowData['lastbalance'] = cells[2]?.innerText || '';
        rowData['monthsales'] = cells[3]?.innerText || '';
        rowData['income'] = cells[4]?.innerText || '';
        rowData['balances'] = cells[5]?.innerText || '';
        rowData['payday'] = cells[6]?.innerText || '';
        rowData['memo'] = cells[7]?.innerText || '';
        rowData['secondordnum'] = cells[8]?.innerText || '';
        
        data.push(rowData);
    }

    // saveExcel.php에 데이터 전송
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "customer_saveExcel.php", true);
    xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4) {
            if (xhr.status == 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        console.log('Excel file generated successfully.');
                        // 다운로드 스크립트로 리디렉션
                        window.location.href = 'downloadExcel.php?filename=' + encodeURIComponent(response.filename.split('/').pop());
                    } else {
                        console.log('Failed to generate Excel file: ' + response.message);
                    }
                } catch (e) {
                    console.log('Error parsing response: ' + e.message + '\nResponse text: ' + xhr.responseText);
                }
            } else {
                console.log('Failed to generate Excel file: Server returned status ' + xhr.status);
            }
        }
    };
    xhr.send(JSON.stringify(data));
}

$(document).ready(function(){
	saveLogData('거래처 원장'); 
});
</script>
</body>
</html>
