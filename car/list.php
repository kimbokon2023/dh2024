<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php"); 
    exit;
}   

include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';

// 첫 화면 표시 문구
$title_message = '차량 관리';
?>
<link href="css/style.css" rel="stylesheet">   
<title><?=$title_message?></title>

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

$tablename = 'car'; // 차량 테이블

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();
 
$order_by = "ORDER BY manufacturing_date ASC, purchase_date ASC ";
	
if (checkNull($search)) {
	$sql = "SELECT * FROM ".$DB.".".$tablename." 
        WHERE searchtag LIKE '%$search%' AND is_deleted IS NULL " . $order_by;	
} else {
	$sql = "SELECT * FROM ".$DB.".".$tablename." WHERE is_deleted IS NULL " . $order_by;
}

try {  	  	  
	$stmh = $pdo->query($sql); 
	$total_row = $stmh->rowCount();			  
?>	

<form id="board_form" name="board_form" method="post" enctype="multipart/form-data">			 
	<input type="hidden" id="num" name="num">             	
	<input type="hidden" id="mode" name="mode" value="<?=$mode?>">             	
	<input type="hidden" id="tablename" name="tablename" value="<?= isset($tablename) ? $tablename : '' ?>">				

<div class="container-fluid">
<div class="card justify-content-center text-center mt-5">

	<div class="card-header">
		<div class="d-flex justify-content-center text-center align-items-center">										 
			<span class="text-center fs-5"><?=$title_message?></span>		
			<button type="button" class="btn btn-dark btn-sm mx-3" onclick='location.reload();' title="새로고침"> <i class="bi bi-arrow-clockwise"></i> </button>  		
			<small class="mx-3 text-muted"> 차량을 신규 등록하거나 보유중인 차량을 클릭하여 수정 (정비내역 및 특이사항 기재) </small>  
		</div>
	</div>
	<div class="card-body">								
		<div class="d-flex justify-content-center text-center align-items-center mb-2">										 
			▷ <?= $total_row ?> &nbsp; 
			<div class="inputWrap30">			
				<input type="text" id="search" class="form-control" style="width:150px;" name="search" value="<?=$search?>" onKeyPress="if (event.keyCode==13){ enter(); }">
				<button class="btnClear"></button>
			</div>							
			&nbsp;&nbsp;
			<button class="btn btn-outline-dark btn-sm" type="button" id="searchBtn"> <i class="bi bi-search"></i> 검색 </button> &nbsp;&nbsp;&nbsp;&nbsp;			
			<button id="newBtn" type="button" class="btn btn-dark btn-sm me-2"> <i class="bi bi-pencil-square"></i> 신규 </button>				
		</div>		
		
		<div class="table-responsive">	
		<table class="table table-hover" id="myTable">		 
			
		<thead class="table-primary">
			<th class="text-center w50px">번호</th>
			<th class="text-center w180px">차종/차량번호</th>				
			<th class="text-center w50px">담당자</th>					
			<th class="text-start w120px">보험사</th>
			<th class="text-start w100px">보험사 연락처</th>
			<th class="text-start w70px">최초등록일</th>
			<th class="text-start w150px">정기 적성검사 기간</th>
			<th class="text-start w80px">검사 수검일</th>			
			<th class="text-start w100px">엔진오일 교환일</th>   
			<th class="text-start w100px">정비 이력일</th>   
		</thead>
		<tbody>		      	 
		<?php  		
		$start_num = $total_row;  			    
		while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
			// 엔진오일 교환일 JSON 디코딩
			$engine_oil_data = json_decode($row['engine_oil_change_data'], true);
			$maintenance_data = json_decode($row['maintenance_data'], true);

			// "정기" 점검이 들어간 레코드만 골라서 날짜 추출
			$last_inspection_date = '';    // 기본값: 빈 문자열
			if (!empty($maintenance_data) && is_array($maintenance_data)) {
				$dates = [];
				foreach ($maintenance_data as $m) {
					if (!empty($m['maintenance_record'])
						&& mb_strpos($m['maintenance_record'], '정기') !== false
					) {
						$dates[] = $m['maintenance_date'];
					}
				}
				if (!empty($dates)) {
					// 날짜 문자열(YYYY-MM-DD) 배열을 내림차순 정렬
					rsort($dates);
					$last_inspection_date = $dates[0];
				}
			}

			// 엔진오일 최근 교환일 추출
			$last_engineOilDate = '';
			if (!empty($engine_oil_data) && is_array($engine_oil_data)) {
				$oilDates = [];
				foreach ($engine_oil_data as $oil) {
					if (!empty($oil['engine_oil_change_date'])) {
						$oilDates[] = $oil['engine_oil_change_date'];
					}
				}
				if (!empty($oilDates)) {
					rsort($oilDates);
					$last_engineOilDate = $oilDates[0];
				}
			}			

			// 마지막 정비이력일 추출
			$last_maintenanceDate = '';
			if (!empty($maintenance_data) && is_array($maintenance_data)) {
				$oilDates = [];
				foreach ($maintenance_data as $oil) {
					if (!empty($oil['maintenance_date'])) {
						$oilDates[] = $oil['maintenance_date'];
					}
				}
				if (!empty($oilDates)) {
					rsort($oilDates);
					$last_maintenanceDate = $oilDates[0];
				}
			}			
		?>					 
		<tr onclick="redirectToView('<?=$row['num']?>')">  
			<td class="text-center"><?= $start_num ?></td>
			<td class="text-start text-primary fw-bold "> <?= htmlspecialchars($row['vehicle_type'], ENT_QUOTES, 'UTF-8') ?> / 
				<?= htmlspecialchars($row['vehicle_number'], ENT_QUOTES, 'UTF-8') ?>
			</td>
			<td class="text-center fw-bold"><?= htmlspecialchars($row['responsible_person'], ENT_QUOTES, 'UTF-8') ?></td>
			<td class="text-start"><?= htmlspecialchars($row['insurance'], ENT_QUOTES, 'UTF-8') ?></td>
			<td class="text-start"><?= htmlspecialchars($row['insurance_contact'], ENT_QUOTES, 'UTF-8') ?></td>
			<td class="text-start"><?= $row['manufacturing_date'] ?></td>									
			<td class="text-start"><?= $row['inspectionDate'] ?> ~ <?= $row['inspectionDateTo'] ?> </td>									
			<td class="text-start fw-bold text-danger"><?= $last_inspection_date ?> </td>									
			<td class="text-start"><?= $last_engineOilDate ?> </td>									
			<td class="text-start"><?= $last_maintenanceDate ?> </td>									
		
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

<!-- 페이지 로딩 -->
<script>
$(document).ready(function(){	
	var loader = document.getElementById('loadingOverlay');
	if(loader)
		loader.style.display = 'none';
});

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

	var savedPageNumber = getCookie('carpageNumber');
	if (savedPageNumber) {
		dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
	}

	dataTable.on('page.dt', function() {
		var carpageNumber = dataTable.page.info().page + 1;
		setCookie('carpageNumber', carpageNumber, 10);
	});

	$('#myTable_length select').on('change', function() {
		var selectedValue = $(this).val();
		dataTable.page.len(selectedValue).draw();
		savedPageNumber = getCookie('carpageNumber');
		if (savedPageNumber) {
			dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
		}
	});
});

function restorePageNumber() {
	var savedPageNumber = getCookie('carpageNumber');
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
	var tablename = $("#tablename").val();
	popupCenter('write_form.php?mode=modify&num=' + num + '&tablename=' + tablename,  title , 1100, 900);    
}

$("#searchBtn").on("click", function() {
	$("#board_form").submit();
});	

$("#newBtn").on("click", function() {		
	var title = '<?php echo $title_message; ?>';
	var tablename = $("#tablename").val();
	popupCenter('write_form.php?tablename=' + tablename,  title +'신규등록' , 1100, 900);   	
	
});

$("#closeBtn").on("click", function() {
	self.close();
});	
		
function reloadlist() {
	$("#board_form").submit();				
}	

$(document).ready(function(){
	saveLogData('차량관리 목록'); 
});
</script>

</body>
</html>