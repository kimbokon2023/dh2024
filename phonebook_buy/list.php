<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if(!isset($_SESSION["level"]) || $_SESSION["level"]>5) {
	sleep(1);
	header("Location:" . $WebSite . "login/login_form.php"); 
	exit;
}   
include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';
// 첫 화면 표시 문구
$title_message = '매입처 주소록'; 
?> 
<link href="css/style.css" rel="stylesheet" >   
<title> <?=$title_message?> </title>
</head>
<body>		  
<?php

// 메뉴를 표현할지 판단하는 header
$header = isset($_REQUEST['header']) ? $_REQUEST['header'] : '';  

if($header == 'header')
	require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php');

// 배송지 띄울폼 모달
include $_SERVER['DOCUMENT_ROOT'] . '/form/order_form.php'; 

function checkNull($strtmp) {
    if ($strtmp === null || trim($strtmp) === '') {
        return false;
    } else {
        return true;
    }
}

$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';  
$enterpress = isset($_REQUEST["enterpress"]) ? $_REQUEST["enterpress"] : '';    
$belong = isset($_REQUEST["belong"]) ? $_REQUEST["belong"] : '';    
$vendor_name = isset($_REQUEST["vendor_name"]) ? $_REQUEST["vendor_name"] : '';
$mode = isset($_REQUEST["mode"]) ? $_REQUEST["mode"] : '';

$tablename = 'phonebook_buy';

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();
 
// 중국발주업체 우선 정렬, 그 다음 번호 역순
$a=" order by is_china_vendor DESC, china_sort_order ASC, num desc";
	
if(checkNull($search))
{
	$sql = "SELECT * FROM ".$DB.".".$tablename." 
        WHERE searchtag LIKE '%$search%' AND is_deleted IS NULL " . $a;	
}
else
	{
		$sql ="select * from ".$DB.".".$tablename . " where is_deleted IS NULL "  . $a;	;	
	}

// print 'mode : ' . $mode;   
// print 'search : ' . $search;   
// print $sql;   
   
 try{  	  
	$stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh
	$total_row=$stmh->rowCount();			  
   
	
?>	

<form id="board_form" name="board_form" method="post" enctype="multipart/form-data"   >			 

	<input type="hidden" id="mode" name="mode" value="<?=$mode?>">             
	<input type="hidden" id="num" name="num"  > 
	<input type="hidden" id="tablename" name="tablename" value="<?=$tablename?>" > 				
	<input type="hidden" id="header" name="header" value="<?=$header?>" > 					
				
<?php if($header !== 'header') 
		{
			print '<div class="container-fluid" >	';
			print '<div class="card justify-content-center text-center mt-1" >';
		}
		else
		{
			print '<div class="container-fluid" >	';
			print '<div class="card justify-content-center text-center mt-5" >';
		}
?>	 
	<div class="card-header d-flex justify-content-center align-items-center">
		<span class="text-center fs-5" >  <?=$title_message?>   </span>								
		<button type="button" class="btn btn-dark btn-sm mx-3"  onclick='location.reload();' title="새로고침"> <i class="bi bi-arrow-clockwise"></i> </button>  
		<small class="ms-5 text-muted"> 대한에서 매입하는 거래처 등록 </small>  
	</div>
	<div class="card-body" >								
	<div class="d-flex  justify-content-center text-center align-items-center mb-2" >										
	▷ <?= $total_row ?> &nbsp; 
	<div class="inputWrap30">			
		<input type="text" id="search" class="form-control" style="width:150px;" name="search" value="<?=$search?>" onKeyPress="if (event.keyCode==13){ enter(); }" >
		<button class="btnClear">  </button>
	</div>							
	&nbsp;&nbsp;
	<button class="btn btn-dark btn-sm " type="button" id="searchBtn" > <i class="bi bi-search"></i> </button> </span> &nbsp;&nbsp;&nbsp;&nbsp;			
	<button id="uploadBtn" type="button" class="btn btn-dark btn-sm me-2"> <i class="bi bi-box-arrow-up"></i> 업로드 </button>	
	<button id="newBtn" type="button" class="btn btn-dark btn-sm me-2"> <i class="bi bi-pencil-square"></i> 신규 </button>	
	<?php if($header !== 'header') 
			print '<button id="closeBtn" type="button" class="btn btn-outline-dark btn-sm"> <i class="bi bi-x-lg"></i> 창닫기 </button>';
	?>			
	</div>		
		
	<div class="table-reponsive" >	
	 <table class="table table-hover" id="myTable">		 
			<thead class="table-primary">
				 <th class="text-center" >번호</th>
				 <th class="text-center" >회사</th>
				 <th class="text-center" >카테고리</th>
				 <th class="text-center" >중국발주업체 체크</th>
				 <th class="text-center" >중국업체 순서</th>
				 <th class="text-center" >이미지</th>
				 <th class="text-center" >대상품목</th>
				 <th class="text-center" >대표자</th>
				 <th class="text-center" >담당자</th>
				 <th class="text-center" >전화번호</th>
				 <th class="text-center" style="width:100px;" >수정/삭제</th>
			</thead>
			<tbody>		      	 
			<?php  		
			$start_num=$total_row;  			    
			while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
				include '_row.php';		
				if(empty($contact_info))
					$contact_info = $phone;	
			?>					 
			<tr onclick="maketext('<?=$vendor_name?>','<?=$num?>');">
				<td class="text-center" ><?= $start_num ?></td>
				<td title="<?=$vendor_name?>"><?= $vendor_name ?></td>
				<td class="text-center" title="<?=$category?>"><?= $category ?></td>
				<td class="text-center"><?= ((int)$is_china_vendor===1)?'해당':'' ?></td>
				<td class="text-center"><?= ((int)$is_china_vendor===1)?$china_sort_order:'' ?></td>
				<td class="text-center">
					<?php if(!empty($image_base64)) { 
						$src = (strpos($image_base64,'data:')===0)? $image_base64 : 'data:image/png;base64,'.$image_base64; ?>
						<img src="<?=$src?>" style="max-height:22px;width:auto;height:auto;object-fit:contain;border:1px solid #ddd;" />
					<?php } ?>
				</td>
				<td  class="text-center" title="<?=$item?>"><?= $item ?></td>
				<td  class="text-center" title="<?=$representative_name?>"><?= $representative_name ?></td>
				<td class="text-center" title="<?=$manager_name?>"><?= $manager_name ?></td>
				<td class="text-center" title="<?=$contact_info?>"><?= $contact_info ?></td>
				<td class="text-center" >										
					<button type="button" class="btn btn-primary btn-sm" onclick="updateFn('<?=$num?>'); event.stopPropagation();">
						 <i class="bi bi-pencil-square"></i>
					</button>
					<button type="button" class="btn btn-danger btn-sm" onclick="delFn('<?=$num?>'); event.stopPropagation();">
						<i class="bi bi-x-circle"></i>
					</button>
				</td>
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

<!-- 페이지로딩 -->
<script>
	// 페이지 로딩
	$(document).ready(function(){	
		var loader = document.getElementById('loadingOverlay');
		loader.style.display = 'none';
	});
</script>

<script>
var ajaxRequest_write = null;
var dataTable; // DataTables 인스턴스 전역 변수
var pbpageNumber; // 현재 페이지 번호 저장을 위한 전역 변수

$(document).ready(function() {			
	// DataTables 초기 설정
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

	// 페이지 번호 복원 (초기 로드 시)
	var savedPageNumber = getCookie('pbpageNumber');
	if (savedPageNumber) {
		dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
	}

	// 페이지 변경 이벤트 리스너
	dataTable.on('page.dt', function() {
		var pbpageNumber = dataTable.page.info().page + 1;
		setCookie('pbpageNumber', pbpageNumber, 10); // 쿠키에 페이지 번호 저장
	});

	// 페이지 길이 셀렉트 박스 변경 이벤트 처리
	$('#myTable_length select').on('change', function() {
		var selectedValue = $(this).val();
		dataTable.page.len(selectedValue).draw(); // 페이지 길이 변경 (DataTable 파괴 및 재초기화 없이)

		// 변경 후 현재 페이지 번호 복원
		savedPageNumber = getCookie('pbpageNumber');
		if (savedPageNumber) {
			dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
		}
	});
});

	function restorePageNumber() {
		var savedPageNumber = getCookie('pbpageNumber');
		// if (savedPageNumber) {
			// dataTable.page(parseInt(savedPageNumber) - 1).draw('page');
		// }
		location.reload(true);
	}

	// Enterkey 동작
	function enter()
	{
		$("#board_form").submit();	       		
	}

	/* ESC 키 누를시 팝업 닫기 */
	$(document).keydown(function(e){
		//keyCode 구 브라우저, which 현재 브라우저
		var code = e.keyCode || e.which;

		if (code == 27) { // 27은 ESC 키번호
			self.close();
		}
	});
		
	function maketext( vendorName,  num) {    
		var vendorFieldID = 'secondord'; // ID of the vendor input field in the parent document		
		var textmsg;
		var header = $("#header").val();
		
		if(header=='header'){			
			updateFn(num);
			return;
		}
		if(opener && opener.document) {
				$("#" + vendorFieldID, opener.document).val(vendorName); 				
								
			}
		self.close();

		}

		$("#searchBtn").on("click", function() {
			$("#board_form").submit();
		});	

		$("#search_directinput").on("click", function() {
			$("#custreg_search").hide();
		});	
		// upload
		$("#uploadBtn").on("click", function() {	
			  popupCenter('uploadgrid.php' , '업로드', 1800, 800);	
		});	
		// 신규 버튼
		$("#newBtn").on("click", function() {	
			  popupCenter('./write.php' , '매입처 신규등록', 800, 800);	
		});	
		// 창닫기 버튼
		$("#closeBtn").on("click", function() {
			self.close();
		});	

		
	function  updateFn(num) {	
		var header = $("#header").val();
		
		popupCenter('./write.php?num=' + num + '&header=' + header , '수정', 800, 800);	
	}
			
			
	function  delFn(delfirstitem) {
		console.log(delfirstitem);
		// console.log($("#board_form").serialize());
		$("#mode").val("delete");
		$("#num").val(delfirstitem);

		// DATA 삭제버튼 클릭시
			Swal.fire({ 
				   title: '해당 DATA 삭제', 
				   text: " DATA 삭제는 신중하셔야 합니다. '\n 정말 삭제 하시겠습니까?", 
				   icon: 'warning', 
				   showCancelButton: true, 
				   confirmButtonColor: '#3085d6', 
				   cancelButtonColor: '#d33', 
				   confirmButtonText: '삭제', 
				   cancelButtonText: '취소' })
				   .then((result) => { if (result.isConfirmed) { 
													
						if (ajaxRequest_write !== null) {
							ajaxRequest_write.abort();
						}		 
						ajaxRequest_write = $.ajax({
								url: "process.php",
								type: "post",		
								data: $("#board_form").serialize(),								
								success : function( data ){	

										  console.log(data);							
																			
										 Toastify({
												text: "파일 삭제 완료!",
												duration: 3000,
												close:true,
												gravity:"top",
												position: "center",
												backgroundColor: "#4fbe87",
											}).showToast();		
											
									  setTimeout(function() {
												location.reload();	
										   }, 1500);										 
													
									},
									error : function( jqxhr , status , error ){
										console.log( jqxhr , status , error );
								} 			      		
							   });												
				   } });	
	}
		
	// 자식창에서 돌아와서 이걸 실행한다
	function reloadlist() {
		const search = $("#search").val();
		$("#board_form").submit();				
	}	
</script>

