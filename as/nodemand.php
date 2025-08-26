<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  
if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
	sleep(1);
	header("Location:" . $WebSite . "login/login_form.php"); 
	exit;
}   
include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';

// 첫 화면 표시 문구
$title_message = '결선/AS 비용 미청구'; 

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

$tablename = 'as';

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();
 
$order_by = "ORDER BY num DESC";
	
// 청구일이 IS NULL 인경우
if (checkNull($search)) {
	$sql = "SELECT * FROM ".$DB.".".$tablename." 
        WHERE searchtag LIKE '%$search%' AND is_deleted IS NULL AND payment='paid' And demandDate IS NULL " . $order_by;	
} else {
	$sql = "SELECT * FROM ".$DB.".".$tablename." WHERE is_deleted IS NULL AND payment='paid' And demandDate IS NULL " . $order_by;
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
		<small class="ms-5 text-muted"> 결선/AS 완료현장 (유상건) 미청구 업체 내역 확인</small>  
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
		</div>		
		
		<div class="table-responsive">	
			<table class="table table-hover" id="myTable">		 
				<thead class="table-warning">
					<th class="text-center">번호</th>
					<th class="text-center w50px">구분</th>						
					<th class="text-center">처리예정일</th>					
					<th class="text-center">최종완료일</th>					
					<th class="text-center">요청업체</th> 
					<th class="text-center">요청인</th>
					<th class="text-center">비용부담업체</th>
					<th class="text-center">비용</th>
					<th class="text-center">청구일자</th>
					<th class="text-center">현장주소</th>					
					<th class="text-center">처리내용</th>					
					<th class="text-center">메모</th>					
				</thead>
				<tbody>		      	 
				<?php  		
				$start_num = $total_row;  			    
				while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
					include '_row.php';		
				?>					 
				<tr onclick="redirectToView('<?=$num?>')">  
					<td class="text-center"><?= $start_num ?></td>
					<td class="text-center fw-bold"><?= $itemcheck ?></td>					
				<td class="text-center fw-bold text-danger">
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
					<td class="text-center"><?= $asendday == '0000-00-00' ? '' : $asendday ?></td> <!-- 최종 완료일 -->
					<td class="text-center"><?= $as_step ?></td>
					<td class="text-center"><?= $asorderman ?></td>
					<td class="text-center text-primary"><?= $asfee_estimate ?></td>
						<td class="text-end fw-bold text-danger">
							<?= (strpos($asfee, ',') !== false || $asfee === null || $asfee === '') ? $asfee : number_format($asfee) ?>
						</td> 
					<td class="text-center"><?= $demandDate?></td>
					<td class="text-start"><?= $address ?></td> <!-- 구체적 증상 -->					
					<td class="text-center"><?= $asresult ?></td> <!-- 처리 결과 -->
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
	saveLogData('결선 미청구'); 
});
</script>
