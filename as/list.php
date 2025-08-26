<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
	sleep(1);
	header("Location:" . $WebSite . "login/login_form.php"); 
	exit;
}   

include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';

// 첫 화면 표시 문구
$title_message = '결선/AS 진행중'; 
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
 
$order_by = "ORDER BY asendday ASC, asproday ASC ";
	
if (checkNull($search)) {
	$sql = "SELECT * FROM ".$DB.".".$tablename." 
        WHERE searchtag LIKE '%$search%' AND is_deleted IS NULL and asendday IS NULL 
        AND asday BETWEEN '$fromdate' AND '$todate' " . $order_by;	
} else {
	$sql = "SELECT * FROM ".$DB.".".$tablename." 
        WHERE is_deleted IS NULL and asendday IS NULL 
        AND asday BETWEEN '$fromdate' AND '$todate' " . $order_by;
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
		<small class="ms-5 text-muted">현재 진행되는 결선 및 AS 업체, 현장위치, 일정, 담당자 확인 (신규현장 등록시 "신규"버튼 클릭 후 작성란에 맞게 기재)</small>  
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

				<!-- 검색어 입력 및 검색 버튼 -->
				<div class="row justify-content-center mb-3">
					<div class="col-auto">
						<div class="d-flex align-items-center">
							<div class="inputWrap30 me-2">			
								<input type="text" id="search" class="form-control" style="width:150px;" name="search" value="<?=$search?>" onKeyPress="if (event.keyCode==13){ enter(); }" placeholder="검색어 입력">
								<button class="btnClear"></button>
							</div>							
							<button class="btn btn-outline-dark btn-sm me-2" type="button" id="searchBtn"> <i class="bi bi-search"></i> 검색 </button> &nbsp;&nbsp;&nbsp;&nbsp;			
							<button id="newBtn" type="button" class="btn btn-dark btn-sm me-2"> <i class="bi bi-pencil-square"></i> 신규 </button>				
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="table-responsive">	
			<table class="table table-hover" id="myTable">		 
				<thead class="table-primary">
					<th class="text-center w50px">번호</th>
					<th class="text-center w50px">구분</th>					
					<th class="text-center w130px">처리예정일</th>					
					<th class="text-center w200px">현장주소</th>
					<th class="text-center w80px">접수일</th>
					<th class="text-center w80px">최종완료일</th>					
					<th class="text-center w120px">요청업체</th>
					<th class="text-center w120px">요청인</th>
					<th class="text-center w200px">AS 구체적 증상 및 <br> 추가 메모</th>
					<th class="text-center w90px">처리담당자</th>					
					<th class="text-center w100px">처리내용</th>					
					<th class="text-center w50px">무상/유상</th>					
					<th class="text-center w80px">비용</th>					
					<th class="text-start w200px">처리결과(구체적)</th>					
				</thead>
				<tbody>		      	 
				<?php  		
				$start_num = $total_row;  			    
				while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
					include '_row.php';		
				?>					 
				<tr onclick="redirectToView('<?=$num?>')">  
					<td class="text-center"><?= $start_num ?></td>
					<td class="text-start fw-bold"><?= $itemcheck ?></td>
				<td class="text-start fw-bold text-danger">
					<?php 
					if ($asproday != '0000-00-00' && !empty($asproday)) {
						$date = new DateTime($asproday);
						echo $date->format('Y-m-d');
					} else {
						echo ''; // 잘못된 날짜는 빈 문자열로 출력
					}
					
					if ($asendday != '0000-00-00' && !empty($asendday)) 
						echo '<span class="badge bg-dark"> (완료) </span>';
					?>
				</td>					
				<td class="text-start "><?= htmlspecialchars($address, ENT_QUOTES, 'UTF-8') ?></td>
				<td class="text-start">
					<?php 
					if ($asday != '0000-00-00' && !empty($asday)) {
						$date = new DateTime($asday);
						echo $date->format('m-d');
					} else {
						echo ''; // 잘못된 날짜는 빈 문자열로 출력
					}
					?>
				</td>				
				<td class="text-start">
					<?php 
					if ($asendday != '0000-00-00' && !empty($asendday)) {
						$date = new DateTime($asendday);
						echo $date->format('m-d');
					} else {
						echo ''; // 잘못된 날짜는 빈 문자열로 출력
					}
					?>
				</td>
					<td class="text-center"><?= $as_step ?></td>
					<td class="text-center"><?= $asorderman ?></td>
					<td class="text-start text-primary "><?= $aslist ?></td> <!-- 구체적 증상 -->
					<td class="text-center"><?= $asman ?></td>					
					<td class="text-start"><?= $asresult ?></td> <!-- 처리 결과 -->
					<td class="text-center">
						<?php 
						if ($payment == 'free') {
							echo '무상';
						} elseif ($payment == 'paid') {
							echo '<span class="badge bg-danger"> 유상 </span>';
						} else {
							echo '알 수 없음'; // 'free'나 'paid'가 아닌 경우에 표시될 값
						}
						?>
					</td>
					<td class="text-end fw-bold text-danger">
						<?= (strpos($asfee, ',') !== false || $asfee === null || $asfee === '') ? $asfee : number_format($asfee) ?>
					</td> 					
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
		"order": []
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

$(document).ready(function(){
	saveLogData('결선 진행중'); 
});
</script> 
  