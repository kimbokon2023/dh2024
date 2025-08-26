<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php"); 
    exit;
}   
include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';
// 첫 화면 표시 문구
$title_message = '지게차/이동식에어컨 관리'; 
?>

<link href="css/style.css" rel="stylesheet">   
<title> <?=$title_message?> </title>

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

$tablename = 'lift'; // 'lift' 테이블 사용

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
	<input type="hidden" id="num" name="num" value="<?= isset($row['num']) ? $row['num'] : '' ?>">
	<input type="hidden" id="mode" name="mode" value="<?= isset($mode) ? $mode : '' ?>">         	
	<input type="hidden" id="tablename" name="tablename" value="<?= isset($tablename) ? $tablename : '' ?>">			

<div class="container">
<div class="card justify-content-center text-center mt-5">

	<div class="card-header d-flex justify-content-center align-items-center">
		<span class="text-center fs-5"> <?=$title_message?> </span>	
		<button type="button" class="btn btn-dark btn-sm mx-3" onclick='location.reload();' title="새로고침"> <i class="bi bi-arrow-clockwise"></i> </button>  		
		<small class="mx-3 text-muted"> 지게차를 신규 등록하거나 보유중인 지게차를 클릭하여 수정 (정비내역 및 특이사항 기재) ※이동식 에어컨 내역도 같이 관리중
		</small>  
	</div>
	<div class="card-body">								
		<div class="d-flex justify-content-center text-center align-items-center mb-2">										 
			▷ <?= $total_row ?> &nbsp; 
			<div class="inputWrap30">			
				<input type="text" id="search" class="form-control" style="width:150px;" name="search" value="<?=$search?>" onKeyPress="if (event.keyCode==13){ enter(); }">
				<button class="btnClear"></button>
			</div>							
			&nbsp;&nbsp;
			<button class="btn btn-outline-dark btn-sm" type="button" id="searchBtn"> <i class="bi bi-search"></i> </button> &nbsp;&nbsp;&nbsp;&nbsp;			
			<button id="newBtn" type="button" class="btn btn-dark btn-sm me-2"> <i class="bi bi-pencil-square"></i> 신규 </button>				
		</div>		
		
		<div class="table-responsive">	
		<table class="table table-hover" id="myTable">		 
			
		<thead class="table-primary">
			<th class="text-center w50px">번호</th>
			<th class="text-center w150px">중량 / 차종</th>				
			<th class="text-center w50px">담당자</th>					
			<th class="text-center w100px">최초등록일</th>
			<th class="text-center w120px">구입업체</th>
			<th class="text-center w100px">구입업체 연락처</th> 			
			<th class="text-center w100px">정비 이력일</th>
		</thead>
		<tbody>		      	 
		<?php  		
		$start_num = $total_row;  			    
		while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
			// 엔진오일 교환일 JSON 디코딩
			$engine_oil_data = json_decode($row['engine_oil_change_data'], true);
			$maintenance_data = json_decode($row['maintenance_data'], true);

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
			<td class="text-center"><?= $row['manufacturing_date'] ?></td>
			<td class="text-center"><?= htmlspecialchars($row['insurance'], ENT_QUOTES, 'UTF-8') ?></td>
			<td class="text-center"><?= htmlspecialchars($row['insurance_contact'], ENT_QUOTES, 'UTF-8') ?></td> <!-- 추가: 보험사 연락처 -->			
			<td class="text-center"><?= $last_maintenanceDate ?> </td>
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
		"lengthMenu": [50, 100, 200, 500, 1000],
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
	saveLogData('지게차 관리목록'); 
});
</script>

</body>
</html>