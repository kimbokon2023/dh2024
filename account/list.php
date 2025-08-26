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
$title_message = '금전 출납부';
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
$lastUpdateFile = $_SERVER['DOCUMENT_ROOT'] . "/account/last_update.txt";
$lastUpdatedAt = file_exists($lastUpdateFile) ? trim(file_get_contents($lastUpdateFile)) : '';
$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';  
$fromdate = isset($_REQUEST['fromdate']) ? $_REQUEST['fromdate'] : '';  
$todate = isset($_REQUEST['todate']) ? $_REQUEST['todate'] : '';  
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  

// 현재 날짜
$currentDate = date("Y-m-d");

// fromdate 또는 todate가 빈 문자열이거나 null인 경우
if ($fromdate === "" || $fromdate === null || $todate === "" || $todate === null) {
    // 현재 월의 1일을 fromdate로 설정
    // $fromdate = date("Y-m-01");
    // 전달(이전 달)의 1일을 fromdate로 설정
    $fromdate = date("Y-m-01", strtotime("-1 month"));	
    $todate = $currentDate;
    $Transtodate = $todate;
} else {
    $Transtodate = $todate;
}

function checkNull($strtmp) {
    return $strtmp !== null && trim($strtmp) !== '';
}

$tablename = 'account';

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

$inoutsep_select = isset($_REQUEST['inoutsep_select']) ? $_REQUEST['inoutsep_select'] : '';  
$content_select = isset($_REQUEST['content_select']) ? $_REQUEST['content_select'] : '';

$order = " ORDER BY registDate ASC, num ASC ";

$sql_conditions = [];
$sql_params = [];

if (checkNull($search)) {
    $sql_conditions[] = "searchtag LIKE :search";
    $sql_params[':search'] = "%$search%";
}

$sql_conditions[] = "registDate BETWEEN :fromdate AND :todate";
$sql_params[':fromdate'] = $fromdate;
$sql_params[':todate'] = $todate;

$sql_conditions[] = " (is_deleted IS NULL or is_deleted = 0  or is_deleted ='' ) ";

if (checkNull($inoutsep_select)) {
    $sql_conditions[] = "inoutsep = :inoutsep";
    $sql_params[':inoutsep'] = $inoutsep_select;
}

if (checkNull($content_select)) {
    $sql_conditions[] = "content = :content";
    $sql_params[':content'] = $content_select;
}

$sql = "SELECT * FROM " . $tablename . " WHERE " . implode(' AND ', $sql_conditions) . $order;

try {
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
    FROM $tablename 
    WHERE (is_deleted IS NULL or is_deleted = 0  or is_deleted ='' ) AND registDate < :fromdate";
$initialBalanceStmh = $pdo->prepare($initialBalanceSql);
$initialBalanceStmh->bindParam(':fromdate', $fromdate);
$initialBalanceStmh->execute();
$initialBalance = $initialBalanceStmh->fetch(PDO::FETCH_ASSOC)['balance'];

$totalIncomeSql = "SELECT SUM(REPLACE(amount, ',', '')) AS totalIncome 
    FROM $tablename 
    WHERE (is_deleted IS NULL or is_deleted = 0  or is_deleted ='' ) AND inoutsep = '수입' 
    AND registDate BETWEEN :fromdate AND :todate";
$totalIncomeStmh = $pdo->prepare($totalIncomeSql);
$totalIncomeStmh->bindParam(':fromdate', $fromdate);
$totalIncomeStmh->bindParam(':todate', $todate);
$totalIncomeStmh->execute();
$totalIncome = $totalIncomeStmh->fetch(PDO::FETCH_ASSOC)['totalIncome'];

$totalExpenseSql = "SELECT SUM(REPLACE(amount, ',', '')) AS totalExpense 
    FROM $tablename 
    WHERE (is_deleted IS NULL or is_deleted = 0  or is_deleted ='' ) AND inoutsep = '지출' 
    AND registDate BETWEEN :fromdate AND :todate";
$totalExpenseStmh = $pdo->prepare($totalExpenseSql);
$totalExpenseStmh->bindParam(':fromdate', $fromdate);
$totalExpenseStmh->bindParam(':todate', $todate);
$totalExpenseStmh->execute();
$totalExpense = $totalExpenseStmh->fetch(PDO::FETCH_ASSOC)['totalExpense'];

$finalBalance = $initialBalance + $totalIncome - $totalExpense;

// 멀티 계좌정보(잔액) 가져오기
include $_SERVER['DOCUMENT_ROOT'] . "/account/bankbook.php";

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

    <div class="container">
        <div class="card justify-content-center text-center mt-5">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-center fs-5">  <?=$title_message?> 
						<button type="button" class="btn btn-dark btn-sm me-1" onclick='location.reload()'>   <i class="bi bi-arrow-clockwise" ></i> </button>      
					</span>
                    <small class="ms-5 text-muted"> 지출과 수입을 등록합니다.(비고란에 상세하게 기입하기) </small>  
                </div>
                <div class="text-muted" style="font-size:0.85rem;">
                    <?php if ($lastUpdatedAt !== '') { echo '최종 업데이트 일시: ' . htmlspecialchars($lastUpdatedAt); } ?>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-start align-items-center mt-2">
                    <span>
                        ▷ <?= $total_row ?> &nbsp;
                    </span>
                                
                    <!-- 기간부터 검색까지 연결 묶음 start -->                    
                    <small class="d-block text-muted text-center mt-1 mx-2">
                       [기간]버튼에 커서를 올리면 전체, 전년도, 전월등 세부 내용을 검색 가능합니다.
                    </small>
                    <span id="showdate" class="btn btn-dark btn-sm mx-2">기간</span>
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
                </div>
                <div class="d-flex justify-content-center align-items-center mt-2">
                    <!-- 기간검색 끝 -->

                    <!-- 첫 번째 select 문: 수입/지출 구분 -->
                    <select id="inoutsep_select" name="inoutsep_select"  class="form-select form-select-sm mx-1 d-block w-auto mx-1" > 
                        <option value="">전체계정</option>
                        <option value="수입" <?= $inoutsep_select === '수입' ? 'selected' : '' ?>>수입</option>
                        <option value="지출" <?= $inoutsep_select === '지출' ? 'selected' : '' ?>>지출</option>
                    </select>

				<!-- 두 번째 select 문: 항목 선택 -->
				<select id="content_select" name="content_select"  class="form-select form-select-sm mx-1 d-block w-auto mx-1" >
					<option value="">전체항목</option>
					<?php
						include 'fetch_options.php';						
                        $options = array_merge(array_keys($incomeOptions), array_keys($expenseOptions));
                        foreach ($options as $option) {
                            $selected = ($content_select === $option) ? 'selected' : '';
                            echo "<option value=\"$option\" $selected>$option</option>";
                        }
                        ?>
                    </select>
                
                <div class="inputWrap30">
                    <input type="text" id="search" class="form-control" style="width:150px;" name="search" value="<?=$search?>" onKeyPress="if (event.keyCode==13){ enter(); }">
                    <button class="btnClear"></button>
                </div>
                &nbsp;&nbsp;
                <button class="btn btn-outline-dark btn-sm" type="button" id="searchBtn"> <i class="bi bi-search"></i> </button> &nbsp;&nbsp;&nbsp;&nbsp;
				<button type="button" class="btn btn-secondary btn-sm ms-2 me-2" onclick="settings();"  data-bs-toggle="tooltip" data-bs-placement="bottom" title="계정 관리(추가/수정/삭제)" > <i class="bi bi-gear-fill"></i> </button>
                <button id="newBtn" type="button" class="btn btn-dark btn-sm me-2"> <i class="bi bi-pencil-square"></i> 신규 </button>				
				<button type="button" class="btn btn-dark btn-sm me-2" onclick="generateExcel();" > <i class="bi bi-file-earmark-spreadsheet"></i> 엑셀다운로드 </button>
				<button type="button" class="btn btn-primary btn-sm me-2" onclick="detail();" > <i class="bi bi-ticket-detailed"></i> 상세내역 </button>
            </div>
            </div>
            <!-- 멀티 계좌별 잔액 표시 -->
            <div class="d-flex flex-wrap justify-content-start align-items-center mt-2 p-2 border rounded" style="gap: 10px;">
                <strong class="me-2">계좌 잔액:</strong>
                    <?php foreach ($accountFinalBalances as $summary): ?>
                        <?php if ($summary['balance'] > 0): ?>
                            <?php 
                            // USD가 포함된 경우 금액 앞에 $ 추가
                            $isUSD = stripos($summary['name'], 'USD') !== false;
                            $formattedBalance = $isUSD ? '$' . number_format($summary['balance']) : number_format($summary['balance']);
                            ?>
                            <div class="border rounded p-1" style="font-size: 0.8em;">
                                <span class="text-secondary"><?= htmlspecialchars($summary['name']) ?>:</span>
                                <span class="fw-bold ms-1"><?= $formattedBalance ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
            </div>            				 
            <div class="table-responsive">
                <table class="table table-hover" id="myTable">
                    <thead class="table-secondary">
                        <tr>
                            <th class="text-center" style="width:50px;">번호</th>
                            <th class="text-center" style="width:80px;">등록일자</th>
                            <th class="text-center" style="width:80px;">항목</th>
                            <th class="text-center" style="width:80px;">세부항목</th>
                            <th class="text-center" style="width:150px;">상세내용</th>
                            <th class="text-center" style="width:100px;">수입</th>
                            <th class="text-center" style="width:100px;">지출</th>
                            <th class="text-center" style="width:100px;">잔액</th>       
                            <th class="text-center" style="width:160px;">계좌</th>                     
                            <th class="text-center" style="width:200px;">적요</th>
                        </tr>
						<tr style="background-color: #808080!important;">
							<th class="text-end" colspan="5"> 합계 &nbsp; </th>
							<th class="text-end" id="totalIncomeAmount"></th>
							<th class="text-end" id="totalExpenseAmount"></th>
							<th class="text-end" id="totalBalanceAmount"></th>
							<th class="text-end"></th>							
							<th class="text-end"></th>							
						</tr>						
                    </thead>
                    <tbody>
                        <?php
                        // 1) 계좌별 기초 잔액(기간 시작 이전)을 계산하여 러닝 잔액 테이블 생성
                        $runningBalances = [];
                        if (is_array($accounts)) {
                            foreach ($accounts as $account) {
                                // dh2024는 bankbook 저장 시 memo 미포함 케이스가 있어 memo는 제외
                                $accountDisplay = $account['company'] . ' ' . $account['number'];

                                $initialBalanceSqlByAccount = "SELECT 
                                    (SUM(CASE WHEN inoutsep = '수입' OR inoutsep = '최초전월이월' THEN REPLACE(amount, ',', '') ELSE 0 END) -
                                     SUM(CASE WHEN inoutsep = '지출' THEN REPLACE(amount, ',', '') ELSE 0 END)) AS balance
                                    FROM $tablename 
                                    WHERE (is_deleted IS NULL or is_deleted = 0 or is_deleted = '')
                                      AND registDate < :fromdate AND bankbook = :bankbook";
                                $stmtInit = $pdo->prepare($initialBalanceSqlByAccount);
                                $stmtInit->bindParam(':fromdate', $fromdate);
                                $stmtInit->bindParam(':bankbook', $accountDisplay);
                                $stmtInit->execute();
                                $resInit = $stmtInit->fetch(PDO::FETCH_ASSOC);
                                $runningBalances[$accountDisplay] = $resInit['balance'] ?? 0;
                            }
                        }

                        $start_num = $total_row;
						$counter = 1;
                        $balance = $initialBalance; // 전체 기준 잔액(표시는 계좌별 러닝 잔액 사용)
						while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
                            
							include '_row.php';
							
							// 콤마 제거 후 숫자로 변환
							$amount = floatval(str_replace(',', '', $row['amount']));

                            // JSON에 없는 계좌가 등장하는 경우 0으로 초기화
                            if (!isset($runningBalances[$bankbook])) {
                                $runningBalances[$bankbook] = 0;
                            }

                            // 계좌별 러닝 잔액 가감 (수입/최초전월이월 +, 지출 -)
                            if ($inoutsep === '수입' || $inoutsep === '최초전월이월') {
                                $runningBalances[$bankbook] += $amount;
                            } else {
                                $runningBalances[$bankbook] -= $amount;
                            }

                        ?>
                        <tr onclick="loadForm('update', '<?=$num?>');">
                            <td class="text-center"><?= $counter ?></td>
                            <td class="text-center"><?= $registDate ?></td>                            
							<td class="text-center fw-bold "> <?= $content ?> </td>
							<td class="text-center fw-bold "> <?= $contentSub ?> </td>
                            
                            <td class="text-start"><?= $content_detail ?></td>
                            <?php if ($inoutsep === '수입') : ?>
                                <td class="text-end fw-bold text-primary">
                                    <?= is_numeric($amount) ? number_format($amount) : htmlspecialchars($amount) ?>
                                </td>
                                <td class="text-end"></td>
                            <?php else : ?>
                                <td class="text-end"></td>
                                <td class="text-end fw-bold text-danger">
                                    <?= is_numeric($amount) ? number_format($amount) : htmlspecialchars($amount) ?>
                                </td>
                            <?php endif; ?>
                            <td class="text-end fw-bold"><?= number_format($runningBalances[$bankbook]) ?></td>     
                            <td class="text-start"><?= $bankbook ?></td>                       
                            <td class="text-start"><?= $memo ?></td>
                        </tr>
                        <?php
                            $start_num--;
                            $counter++;
                        }
                        } catch (PDOException $Exception) {
                            print "오류: ".$Exception->getMessage();
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</form>

<script>
// 페이지 로딩
$(document).ready(function(){    
    var loader = document.getElementById('loadingOverlay');
    if (loader) {   
        loader.style.display = 'none';
    }
});

function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}


$(document).ready(function() {
	
    const initialBalance = <?= json_encode($initialBalance) ?>;
    const finalBalance = <?= json_encode($finalBalance) ?>;    	
	
	dataTable = $('#myTable').DataTable({
		"paging": true,
		"ordering": true,
		"searching": true,
		"pageLength": 1000,
		"lengthMenu": [1000],
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
				"targets": [7, 8, 9] // 나머지 열 (적요 열) 비활성화
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

    $("#searchBtn").on("click", function() {
        $("#board_form").submit();
    });


});


//  모달창에 내용을 넣는 구조임 모달을 부르고 내용을 동적으로 넣는다.
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
            document.querySelector(".modal-body .custom-card").innerHTML = response;			

            $("#myModal").show();
                        
			// PHP 데이터를 JSON으로 인코딩
			const incomeOptions = <?php echo json_encode($incomeOptions, JSON_UNESCAPED_UNICODE); ?>;
			const expenseOptions = <?php echo json_encode($expenseOptions, JSON_UNESCAPED_UNICODE); ?>;

			// console.log('Income Options:', incomeOptions);
			// console.log('Expense Options:', expenseOptions);		
			
			function updateDescription() {
				const contentSelect = document.getElementById('content');
				const descriptionDiv = document.getElementById('content_description');
				const selectedValue = contentSelect.value;
				
				// 수입인지 지출인지에 따라 설명 변경
				const descriptions = document.querySelector('input[name="inoutsep"]:checked').value === '수입' ? incomeOptions : expenseOptions;
				descriptionDiv.innerText = descriptions[selectedValue] || '';

				// '거래처 수금'이 선택되었을 때 전화번호 검색 화면을 띄움
				if (selectedValue === '거래처 수금') {
					phonebookBtn('');
				}
			}

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

					updateDescription();
				}
			}
			
			function phonebookBtn(search)
			{					
				returnID = '수금등록';
				href = '/phonebook/list.php?search=' + search + '&returnID=' + returnID;
				popupCenter(href, '전화번호 검색', 1400, 700);

			}
			
           
			$(document).on("click", "#closeBtn", function() {
				$("#myModal").hide();
			});


			$(document).on("click", "#saveBtn", function() {
				// if (isSaving) return;
				// isSaving = true;

					// AJAX 요청을 보냄
				if (ajaxRequest !== null) {
					ajaxRequest.abort();
				}
				ajaxRequest = $.ajax({
						url: "/account/insert.php",
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
							contentSub: $("#contentSub").val(),
							bankbook: $("#bankbook").val(),
							secondordnum: $("#secondordnum").val()
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
							// isSaving = false;
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
								url: "/account/insert.php",
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

				// 항목 선택 변경 시 설명 업데이트
				$(document).on("change", "#content", updateDescription);

				$(document).on("change", "input[name='inoutsep']", updateContentOptions);					

        },
        error: function(jqxhr, status, error) {
            console.log("AJAX error in loadForm:", status, error);
        }
    });
}

function updateSubOptions() {
	const contentSelect = document.getElementById('content');
	const contentSubSelect = document.getElementById('contentSub');
	const selectedValue = contentSelect.value;

	if (ajaxRequest !== null) {
		ajaxRequest.abort();
	}

	// AJAX 요청으로 세부항목 가져오기
	ajaxRequest = $.ajax({
		url: 'fetch_modal.php',
		type: 'POST',
		data: { action: 'getSubOptions', selectedKey: selectedValue },
		dataType: 'json',
		success: function(response) {
			// 기존 옵션 초기화
			contentSubSelect.innerHTML = '';

			if (response.subOptions && response.subOptions.length > 0) {
				// 새로운 옵션 추가
				response.subOptions.forEach(item => {
					for (const key in item) {
						const option = document.createElement('option');
						option.value = key;
						option.text = key;
						contentSubSelect.appendChild(option);
					}
				});
			} else {
				// 세부항목이 없는 경우 기본값 처리
				const option = document.createElement('option');
				option.value = '';
				option.text = '세부항목 없음';
				contentSubSelect.appendChild(option);
			}
		},
		error: function(jqxhr, status, error) {
			console.log("AJAX Error:", status, error);
		}
	});
}
	

// content 선택 변경 이벤트
$(document).on('change', '#content', updateSubOptions);


</script>

<script>
function generateExcel() {
    var table = document.getElementById('myTable');
    var rows = table.getElementsByTagName('tr');
    var data = [];

    // 각 행을 반복하여 데이터 수집
    for (var i = 1; i < rows.length; i++) { // 헤더 행을 건너뜀
        var cells = rows[i].getElementsByTagName('td');
        var rowData = {};
        rowData['number'] = cells[0]?.innerText || '';
        rowData['registDate'] = cells[1]?.innerText || '';
        rowData['content'] = cells[2]?.innerText || '';
        rowData['contentSub'] = cells[3]?.innerText || '';
        rowData['contentDetail'] = cells[4]?.innerText || '';
        rowData['income'] = cells[5]?.innerText || '';
        rowData['expense'] = cells[6]?.innerText || '';
        rowData['balance'] = cells[7]?.innerText || '';
        rowData['memo'] = cells[8]?.innerText || '';
        
        data.push(rowData);
    }

    // saveExcel.php에 데이터 전송
    console.log('Sending data:', data);
    $.ajax({
        type: "POST",
        url: "order_saveExcel.php",
        data: { excelData: JSON.stringify(data) },
        dataType: "json",
        success: function(response) {
            try {
                if (response.success) {
                    console.log('Excel file generated successfully.');
                    window.location.href = 'downloadExcel.php?filename=' + encodeURIComponent(response.filename.split('/').pop());
                } else {
                    console.log('Failed to generate Excel file: ' + response.message);
                }
            } catch (e) {
                console.log('Error parsing response: ' + e.message + '\nResponse text: ' + response);
            }
        },
        error: function(xhr, status, error) {
            console.log('Failed to generate Excel file: ' + error);
            console.log('Status: ' + status);
            console.log('Response Text: ' + xhr.responseText);
            console.log('Status Code: ' + xhr.status);
        }
    });
}

function settings() {    
    // 계정설정
    const url = `settings.php`;
    customPopup(url, '계정 관리', 600, 850);
}

function detail() {    
    // detail.php로 이동할 URL 생성
    const url = `detail.php`;

    // customPopup을 사용하여 detail.php를 팝업으로 열기
    customPopup(url, '상세 내역', 800, 900);
}


function openPopup(url, title, width, height) {
    // 화면 중앙에 팝업을 띄우도록 좌표 계산
    const left = (window.screen.width / 2) - (width / 2);
    const top = (window.screen.height / 2) - (height / 2);

    // 팝업 창 생성
    const popupWindow = window.open(
        url,
        title,
        `width=${width},height=${height},top=${top},left=${left},scrollbars=yes,resizable=yes`
    );

    // 오버레이 생성
    const overlay = document.createElement('div');
    overlay.id = 'overlay';
    overlay.style.position = 'fixed';
    overlay.style.top = 0;
    overlay.style.left = 0;
    overlay.style.width = '100%';
    overlay.style.height = '100%';
    overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.5)'; // 반투명 검은색
    overlay.style.zIndex = 10000; // 최상위 레이어
    overlay.style.cursor = 'not-allowed'; // 사용자가 클릭하지 못하도록 마우스 커서를 변경
    document.body.appendChild(overlay);

    // 팝업이 닫히면 오버레이 제거
    const interval = setInterval(() => {
        if (popupWindow.closed) {
            clearInterval(interval);
            document.body.removeChild(overlay);
        }
    }, 500);

    // 팝업 창에 포커스 이동
    if (window.focus) {
        popupWindow.focus();
    }
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
	saveLogData('금전 출납부'); 
});
</script>

</body>
</html>
