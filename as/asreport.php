<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
	sleep(1);
	header("Location:" . $WebSite . "login/login_form.php"); 
	exit;
}   

include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';

// 첫 화면 표시 문구
$title_message = 'AS 처리 보고서'; 
?>
 
<link href="css/style.css" rel="stylesheet">   
<title> <?=$title_message?> </title>

<style>
	/* 검색 타입 선택 스타일 */
	.search-type-container {
		display: flex;
		align-items: center;
		justify-content: center;
		gap: 15px;
	}

	.search-type-container input[type="radio"] {
		margin-right: 5px;
	}

	.search-type-container label {
		cursor: pointer;
		font-weight: 500;
	}

	/* 동적 검색 컨트롤 스타일 */
	.year-select, .month-select, .period-select {
		display: none;
		min-width: 200px;
	}

	.year-select select, .month-select input, .period-select .d-flex {
		width: 100%;
	}

	/* 검색 결과 개수 스타일 */
	.badge.bg-primary {
		font-size: 1rem !important;
		padding: 0.5rem 1rem;
	}
</style>

</head>
<body>		 
	<?php include $_SERVER['DOCUMENT_ROOT'] . '/myheader.php'; ?>   
<?php

$header = isset($_REQUEST['header']) ? $_REQUEST['header'] : '';  

function checkNull($strtmp) {
    return !($strtmp === null || trim($strtmp) === '');
}

$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';  
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';
$search_type = isset($_REQUEST['search_type']) ? $_REQUEST['search_type'] : 'period'; // 기본값은 기간별
$selected_year = isset($_REQUEST['selected_year']) ? $_REQUEST['selected_year'] : date('Y');
$selected_month = isset($_REQUEST['selected_month']) ? $_REQUEST['selected_month'] : date('Y-m');
$fromdate = isset($_REQUEST['fromdate']) ? $_REQUEST['fromdate'] : '';  
$todate = isset($_REQUEST['todate']) ? $_REQUEST['todate'] : '';  

// 현재 날짜
$currentDate = date("Y-m-d");

// 검색 타입에 따른 날짜 설정
if ($search_type === 'year') {
    // 연도별 검색
    $fromdate = $selected_year . "-01-01";
    $todate = $selected_year . "-12-31";
} elseif ($search_type === 'month') {
    // 월별 검색
    $fromdate = $selected_month . "-01";
    $todate = date("Y-m-t", strtotime($selected_month . "-01"));
} else {
    // 기간별 검색 (기본값)
    if ($fromdate === "" || $fromdate === null || $todate === "" || $todate === null) {
        $fromdate = date("Y-m-01", strtotime("-1 month"));	
        $todate = $currentDate;
    }
}

$tablename = 'as';

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();
 
$order_by = "ORDER BY num DESC";
	
// AS 자료만 필터링 (itemcheck가 'AS'인 경우만)
if (checkNull($search)) {
	$sql = "SELECT * FROM ".$DB.".".$tablename." 
        WHERE searchtag LIKE '%$search%' AND is_deleted IS NULL AND itemcheck='AS' 
        AND ((payment='paid' And paydate IS NOT NULL) or (payment='free' And asendday IS NOT NULL))
        AND asendday BETWEEN '$fromdate' AND '$todate' " . $order_by;	
} else {
	$sql = "SELECT * FROM ".$DB.".".$tablename." 
        WHERE is_deleted IS NULL AND itemcheck='AS' 
        AND ((payment='paid' And paydate IS NOT NULL) or (payment='free' And asendday IS NOT NULL))
        AND asendday BETWEEN '$fromdate' AND '$todate' " . $order_by;
}

// 연도 옵션 생성 (현재년도 + 과거 3년)
$current_year = date('Y');
$year_options = '';
for ($i = 0; $i < 4; $i++) {
    $year = $current_year - $i;
    $selected = ($year == $selected_year) ? 'selected' : '';
    $year_options .= "<option value='$year' $selected>" . $year . "년</option>";
}

// print $sql;

try {  	  
	$stmh = $pdo->query($sql); 
	$total_row = $stmh->rowCount();			  
?>	

<form id="board_form" name="board_form" method="post" enctype="multipart/form-data">	
	<input type="hidden" id="num" name="num" >             	
	<input type="hidden" id="mode" name="mode" value="<?=$mode?>">             	
	<input type="hidden" id="tablename" name="tablename" value="<?=$tablename?>"> 					
	
<div class="container-fluid">
<div class="card justify-content-center text-center mt-5">

	<div class="card-header d-flex justify-content-center align-items-center">
		<span class="text-center fs-5"> <?=$title_message?> </span>	
		<button type="button" class="btn btn-dark btn-sm mx-3"  onclick='location.reload();' title="새로고침"> <i class="bi bi-arrow-clockwise"></i> </button>  
		<small class="mx-3 text-muted"> 결선이나 A/S 처리 후 상황별 상세내역입니다. </small>  		
		<button class="btn btn-success btn-sm me-2" type="button" id="excelBtn"> <i class="bi bi-file-earmark-excel"></i> 엑셀저장 </button> &nbsp;&nbsp;
		<button class="btn btn-info btn-sm me-2" type="button" id="aiPromptBtn"> <i class="bi bi-clipboard"></i> AI prompt 복사 </button> &nbsp;&nbsp;
		<button class="btn btn-warning btn-sm" type="button" id="asReportBtn"> <i class="bi bi-file-text"></i> AS보고서 </button>						
	</div>
	<div class="card-body">								
		<div class="row justify-content-center">
			<div class="col-md-8">
				<!-- 검색 결과 개수 -->
				<div class="text-center mb-3">
					<span class="text-center text-primary fs-6">▷ <?= $total_row ?> 건</span>
				</div>

				<!-- 검색 타입 선택 -->
				<div class="row justify-content-center mb-3">
					<div class="col-auto">
						<div class="search-type-container">
							<label class="me-3">
								<input type="radio" name="search_type" value="year" <?= $search_type === 'year' ? 'checked' : '' ?> onchange="toggleSearchTypeAndSubmit()"> 연도별
							</label>
							<label class="me-3">
								<input type="radio" name="search_type" value="month" <?= $search_type === 'month' ? 'checked' : '' ?> onchange="toggleSearchTypeAndSubmit()"> 월별
							</label>
							<label>
								<input type="radio" name="search_type" value="period" <?= $search_type === 'period' ? 'checked' : '' ?> onchange="toggleSearchTypeAndSubmit()"> 기간별
							</label>
						</div>
					</div>
				</div>

				<!-- 동적 검색 컨트롤 -->
				<div class="row justify-content-center mb-3">
					<div class="col-auto">
						<!-- 연도별 검색 -->
						<div class="year-select">
							<select id="selected_year" name="selected_year" class="form-select form-select-sm" onchange="autoSubmit()">
								<?= $year_options ?>
							</select>
						</div>

						<!-- 월별 검색 -->
						<div class="month-select">
							<input type="month" id="selected_month" name="selected_month" class="form-control" value="<?=$selected_month?>" onchange="autoSubmit()">
						</div>

						<!-- 기간별 검색 -->
						<div class="period-select">
							<div class="d-flex align-items-center">
								<input type="date" id="fromdate" name="fromdate" class="form-control me-2" value="<?=$fromdate?>" onchange="autoSubmit()">
								<span class="me-2">~</span>
								<input type="date" id="todate" name="todate" class="form-control" value="<?=$todate?>" onchange="autoSubmit()">
							</div>
						</div>
					</div>
				</div>

				<!-- 검색어 입력 및 버튼들 -->
				<div class="row justify-content-center mb-3">
					<div class="col-auto">
						<div class="d-flex align-items-center">
							<div class="inputWrap30 me-2">			
								<input type="text" id="search" class="form-control" style="width:150px;" name="search" value="<?=$search?>" onKeyPress="if (event.keyCode==13){ enter(); }" placeholder="검색어 입력">
								<button class="btnClear"></button>
							</div>							
							<button class="btn btn-outline-dark btn-sm me-2" type="button" id="searchBtn"> <i class="bi bi-search"></i> 검색 </button> &nbsp;&nbsp;							
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="table-responsive">	
			<table class="table table-hover" id="myTable">		 
				<thead class="table-secondary">
					<th class="text-center w50px">번호</th>
					<th class="text-center w100px">처리예정일</th>					
					<th class="text-center w100px">AS완료일</th>					
					<th class="text-center w100px">요청업체</th>
					<th class="text-center w100px">요청인</th>
					<th class="text-center w100px">비용부담업체</th>
					<th class="text-center w100px">비용</th>
					<th class="text-center w100px">청구일자</th>
					<th class="text-center w300px">현장주소</th>					
					<th class="text-center w300px">증상</th>					
					<th class="text-center w300px">유상처리결과</th>					
					<th class="text-center w300px">처리방법 및 결과(구체적)</th>					
				</thead>
				<tbody>		      	 
				<?php  		
				$start_num = $total_row;  			    
				while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
					include '_row.php';		
				?>					 
				<tr onclick="redirectToView('<?=$num?>')">  
					<td class="text-center"><?= $start_num ?></td>
					<td class="text-center fw-bold text-danger">
						<?php 
						if ($asproday != '0000-00-00' && !empty($asproday)) {
							$date = new DateTime($asproday);
							echo $date->format('Y-m-d');
						} else {
							echo ''; // 잘못된 날짜는 빈 문자열로 출력
						}
						
						if ($asfee !== '' && $asfee !== null && strpos($asfee, ',') === false) {
							$asfee = number_format($asfee);
						}
						if ($asendday != '0000-00-00' && !empty($asendday)) 
							echo '<span class="badge bg-dark"> (완료) </span>';
						?>
					</td>
					<td class="text-center"><?= $asendday == '0000-00-00' ? '' : $asendday ?></td> <!-- 최종 완료일 -->
					<td class="text-start"><?= $as_step ?></td>
					<td class="text-start"><?= $asorderman ?></td>
					<td class="text-start text-primary"><?= $asfee_estimate ?></td>
					<td class="text-end fw-bold text-danger">
						<?= (strpos($asfee, ',') !== false || $asfee === null || $asfee === '') ? $asfee : number_format($asfee) ?>
					</td> 
					<td class="text-start"><?= $demandDate?></td>
					<td class="text-start"><?= $address ?></td> <!-- 현장주소 -->					
					<td class="text-start"><?= $aslist ?></td> <!-- 증상 -->					
					<td class="text-start"><?= $asresult ?></td> <!-- 처리 결과 -->
					<td class="text-start"><?= $note ?></td> <!-- 메모 -->
				</tr>
				<?php
					$start_num--;
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
</form>
</body>
</html>

<!-- 페이지 로딩 -->
<script>
	$(document).ready(function(){	
		var loader = document.getElementById('loadingOverlay');
		loader.style.display = 'none';
		
		toggleSearchType(); // 초기 로드 시 검색 타입에 맞는 컨트롤 표시
	});
</script>

<script>
var ajaxRequest_write = null;
var dataTable;

$(document).ready(function() {			
	dataTable = $('#myTable').DataTable({
		"paging": true,
		"ordering": true,
		"searching": true,
		"pageLength": 50,
		"lengthMenu": [25, 50, 100, 200, 500, 1000],
		"language": {
			"lengthMenu": "Show _MENU_ entries",
			"search": "Live Search:"
		},
		"order": [[0, 'desc']]
	});

	var savedPageNumber = getCookie('pbpageNumber');
	if (savedPageNumber) {
		dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
	}

	dataTable.on('page.dt', function() {
		var pbpageNumber = dataTable.page.info().page + 1;
		setCookie('pbpageNumber', pbpageNumber, 10);
	});

	$('#myTable_length select').on('change', function() {
		var selectedValue = $(this).val();
		dataTable.page.len(selectedValue).draw();
		savedPageNumber = getCookie('pbpageNumber');
		if (savedPageNumber) {
			dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
		}
	});
});

// 검색 타입에 따른 동적 컨트롤 표시/숨김
function toggleSearchType() {
    var searchType = $('input[name="search_type"]:checked').val();
    
    // 모든 검색 컨트롤 숨기기
    $('.year-select, .month-select, .period-select').hide();
    
    // 선택된 타입에 따라 해당 컨트롤만 표시
    if (searchType === 'year') {
        $('.year-select').show();
    } else if (searchType === 'month') {
        $('.month-select').show();
    } else if (searchType === 'period') {
        $('.period-select').show();
    }
}

// 검색 타입 변경 시 자동 검색 실행
function toggleSearchTypeAndSubmit() {
    toggleSearchType();
    
    // 약간의 지연 후 폼 제출 (UI 업데이트를 위해)
    setTimeout(function() {
        $("#board_form").submit();
    }, 100);
}

// 검색 조건 변경 시 자동 검색 실행
function autoSubmit() {
    // 약간의 지연 후 폼 제출 (사용자 입력 완료를 위해)
    setTimeout(function() {
        $("#board_form").submit();
    }, 300);
}

function restorePageNumber() {
	var savedPageNumber = getCookie('pbpageNumber');
	location.reload(true);
}

function enter() {
	$("#board_form").submit();	       		
}

$(document).keydown(function(e){
	var code = e.keyCode || e.which;
	if (code == 27) { 
		self.close();
	}
});

function redirectToView(num) {
	var title = '<?php echo $title_message; ?>';
	popupCenter('write.php?mode=modify&num=' + num,  title , 1000, 980);    
}

$("#searchBtn").on("click", function() {
	$("#board_form").submit();
});	

$("#excelBtn").on("click", function() {
	exportToExcel();
});

$("#aiPromptBtn").on("click", function() {
	exportAIPrompt();
});

$("#asReportBtn").on("click", function() {
	openASReport();
});

$("#uploadBtn").on("click", function() {	
	popupCenter('uploadgrid.php' , '업로드', 1800, 800);	
});	

$("#newBtn").on("click", function() {	
	popupCenter('./write.php' , '결선/AS 신규등록', 1000, 980);	
});

$("#closeBtn").on("click", function() {
	self.close();
});	
		
function reloadlist() {
	$("#board_form").submit();				
}	

function exportToExcel() {
	// 현재 검색 조건과 필터링된 데이터를 가져오기 위해 AJAX 요청
	var searchValue = $('#search').val();
	var searchType = $('input[name="search_type"]:checked').val();
	var selectedYear = $('#selected_year').val();
	var selectedMonth = $('#selected_month').val();
	var fromDate = $('#fromdate').val();
	var toDate = $('#todate').val();
	
	$.ajax({
		url: 'export_as_excel.php',
		type: 'POST',
		data: {
			search: searchValue,
			search_type: searchType,
			selected_year: selectedYear,
			selected_month: selectedMonth,
			fromdate: fromDate,
			todate: toDate
		},
		success: function(response) {
			if (response.success) {
				// 파일 다운로드 링크 생성
				var link = document.createElement('a');
				link.href = response.file;
				link.download = 'AS_완료_리포트_' + new Date().toISOString().slice(0,10) + '.csv';
				document.body.appendChild(link);
				link.click();
				document.body.removeChild(link);
			} else {
				alert('CSV 파일 생성 중 오류가 발생했습니다.');
			}
		},
		error: function() {
			alert('CSV 파일 생성 중 오류가 발생했습니다.');
		}
	});
}

function exportAIPrompt() {
	// 현재 검색 조건과 필터링된 데이터를 가져오기 위해 AJAX 요청
	var searchValue = $('#search').val();
	var searchType = $('input[name="search_type"]:checked').val();
	var selectedYear = $('#selected_year').val();
	var selectedMonth = $('#selected_month').val();
	var fromDate = $('#fromdate').val();
	var toDate = $('#todate').val();
	
	$.ajax({
		url: 'export_ai_prompt.php',
		type: 'POST',
		data: {
			search: searchValue,
			search_type: searchType,
			selected_year: selectedYear,
			selected_month: selectedMonth,
			fromdate: fromDate,
			todate: toDate
		},
		success: function(response) {
			if (response.success) {
				// 파일 다운로드 링크 생성
				var link = document.createElement('a');
				link.href = response.file;
				link.download = 'as_report.txt';
				document.body.appendChild(link);
				link.click();
				document.body.removeChild(link);
			} else {
				alert('AI 프롬프트 파일 생성 중 오류가 발생했습니다.');
			}
		},
		error: function() {
			alert('AI 프롬프트 파일 생성 중 오류가 발생했습니다.');
		}
	});
}

function openASReport() {
	// 현재 검색 조건을 URL 파라미터로 전달
	var searchValue = $('#search').val();
	var searchType = $('input[name="search_type"]:checked').val();
	var selectedYear = $('#selected_year').val();
	var selectedMonth = $('#selected_month').val();
	var fromDate = $('#fromdate').val();
	var toDate = $('#todate').val();
	
	var url = 'asreport_detail.php';
	var params = [];
	
	if (searchValue) {
		params.push('search=' + encodeURIComponent(searchValue));
	}
	if (searchType) {
		params.push('search_type=' + encodeURIComponent(searchType));
	}
	if (selectedYear) {
		params.push('selected_year=' + encodeURIComponent(selectedYear));
	}
	if (selectedMonth) {
		params.push('selected_month=' + encodeURIComponent(selectedMonth));
	}
	if (fromDate) {
		params.push('fromdate=' + encodeURIComponent(fromDate));
	}
	if (toDate) {
		params.push('todate=' + encodeURIComponent(toDate));
	}
	
	if (params.length > 0) {
		url += '?' + params.join('&');
	}
	
	popupCenter(url, 'AS 상세 보고서', 1500, 800);
}

$(document).ready(function(){
	saveLogData('AS 보고서 리포트'); 
});
</script> 