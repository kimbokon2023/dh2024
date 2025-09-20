<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/session.php'; // 세션 파일 포함
require_once($_SERVER['DOCUMENT_ROOT'] . '/lib/mydb.php');
 
$title_message = '연구노트';      
?> 
 
<?php include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php'; ?> 
 
<title> <?=$title_message?>  </title>  
 
<style>

#showextract {
	display: inline-block;
	position: relative;
}
		
#showextractframe {
    display: none;
    position: absolute;
    width: 800px;
    z-index: 1000;
    left: 50%; /* 화면 가로축의 중앙에 위치 */
    top: 110px; /* Y축은 절대 좌표에 따라 설정 */
    transform: translateX(-50%); /* 자신의 너비의 반만큼 왼쪽으로 이동 */
}
#autocomplete-list {
	border: 1px solid #d4d4d4;
	border-bottom: none;
	border-top: none;
	position: absolute;
	top: 87%;
	left: 65%;
	right: 30%;
	width : 10%;
	z-index: 99;
}
.autocomplete-item {
	padding: 10px;
	cursor: pointer;
	background-color: #fff;
	border-bottom: 1px solid #d4d4d4;
}
.autocomplete-item:hover {
	background-color: #e9e9e9;
}
</style>   
</head>		 
<body>

<?php
$tablename = 'eworks';
 if(!$chkMobile) 
{ 	
	require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php'); 
}

 // 모바일이면 특정 CSS 적용
if ($chkMobile) {
    echo '<style>
        table th, table td, h4, .form-control, span {
            font-size: 22px;
        }
         h4 {
            font-size: 40px; 
        }
		.btn-sm {
        font-size: 30px;
		}
    </style>';
}
 
 
include $_SERVER['DOCUMENT_ROOT'] .'/eworks/_request.php'; 
	  
$pdo = db_connect();

// 현재 날짜
$currentDate = date("Y-m-d");

$fromdate = $_REQUEST['fromdate'] ?? '';
$todate = $_REQUEST['todate'] ?? '';

// fromdate 또는 todate가 빈 문자열이거나 null인 경우
if ($fromdate === "" || $fromdate === null || $todate === "" || $todate === null) {
    // $fromdate = date("Y-m-d", strtotime("-3 months", strtotime($currentDate))); // 6개월 이전 날짜
    $fromdate = date("2024-01-01");
    $todate = $currentDate; // 현재 날짜
	$Transtodate = $todate;
} else {
    // fromdate와 todate가 모두 설정된 경우 (기존 로직 유지)
    $Transtodate = $todate;
}
			  
$SettingDate = "indate";

$Andis_deleted = " AND (is_deleted IS NULL or is_deleted='0') AND eworks_item='" . $title_message . "' ";       // 해당 아이템만 불러오기
$Whereis_deleted = " WHERE (is_deleted IS NULL or is_deleted='0') AND eworks_item='" . $title_message . "' ";

$common = " WHERE " . $SettingDate . " BETWEEN '$fromdate' AND '$Transtodate' " . $Andis_deleted . " ORDER BY ";

$a = $common . " num DESC "; // 내림차순 전체

// 검색 변수 초기화
$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';

// 페이징 변수 초기화
$page = isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;
$scale = isset($_REQUEST['scale']) ? (int)$_REQUEST['scale'] : 50;

$sql="select * from ".$DB.".eworks " . $a;

// 검색을 위해 모든 검색변수 공백제거
$search = str_replace(' ', '', $search);

if($mode=="search"){
	  if($search==""){			  
				$sql="select * from {$DB}.eworks " . $a; 										
			   }
		 elseif($search!="") { 			    
			  $sql ="select * from {$DB}.eworks where ((outdate like '%$search%')  or (replace(outworkplace,' ','') like '%$search%' ) ";
			  $sql .="or (steel_item like '%$search%') or (spec like '%$search%') or (company like '%$search%')  or (first_writer like '%$search%') or (payment like '%$search%')   or (supplier like '%$search%') or (request_comment like '%$search%') )  " . $Andis_deleted . " order by num desc  ";										 								
			}
	   }
if($mode=="") {
				$sql="select * from {$DB}.eworks " . $a; 						                         
			}		
				            
$nowday=date("Y-m-d");   // 현재일자 변수지정   

$dateCon =" AND between date('$fromdate') and date('$Transtodate') " ;
   
try{  
	  $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh
      $total_row=$stmh->rowCount();	

?>

<form name="board_form" id="board_form"  method="post" action="list.php?mode=search">  
	<input type="hidden" id="tablename" name="tablename" value="<?=$tablename?>" >							
<div class="container">  	
		<div class="card mt-2">
			<div class="card-body">
				<div class="d-flex mb-3 mt-2 justify-content-center align-items-center">  
					<h4> <?=$title_message?> </h4>  
					<button type="button" class="btn btn-dark btn-sm mx-3"  onclick='location.reload();' title="새로고침"> <i class="bi bi-arrow-clockwise"></i> </button>  	 			
                    <small class="ms-5 text-muted">  연구노트를 작성하면 결재시스템으로 넘어갑니다. </small>  
				</div>	
			<div class="d-flex mb-1 mt-1 justify-content-center align-items-center">  													   
			<!-- 기간부터 검색까지 연결 묶음 start -->
            <small class="ms-5 text-muted">  
                [기간]버튼에 커서를 올리면 전체, 전년도, 전월등 세부 검색 가능
            </small>

			<span id="showdate" class="btn btn-dark btn-sm mx-2" > 기간 </span>
			<div id="showframe" class="card">
				<div class="card-header " style="padding:2px;">
					<div class="d-flex justify-content-center align-items-center">  
						기간 설정
					</div>
				</div>
				<div class="card-body">
					<div class="d-flex justify-content-center align-items-center">  	
						<button type="button" class="btn btn-outline-success btn-sm me-1 change_dateRange"   onclick='alldatesearch()' > 전체 </button>  
						<button type="button" id="preyear" class="btn btn-outline-primary btn-sm me-1 change_dateRange" onclick='pre_year()'> 전년도 </button>  
						<button type="button" id="three_month" class="btn btn-dark btn-sm me-1 change_dateRange "  onclick='three_month_ago()'> M-3월 </button>
						<button type="button" id="prepremonth" class="btn btn-dark btn-sm me-1 change_dateRange "  onclick='prepre_month()'> 전전월 </button>	
						<button type="button" id="premonth" class="btn btn-dark btn-sm me-1 change_dateRange "  onclick='pre_month()'> 전월 </button> 						
						<button type="button" class="btn btn-outline-danger btn-sm me-1 change_dateRange "  onclick='this_today()'> 오늘 </button>
						<button type="button" id="thismonth" class="btn btn-dark btn-sm me-1 change_dateRange "  onclick='this_month()'> 당월 </button>
						<button type="button" id="thisyear" class="btn btn-dark btn-sm me-1 change_dateRange "  onclick='this_year()'> 당해년도 </button> 
					</div>
				</div>
			</div>		
			   <input type="date" id="fromdate" name="fromdate" size="12"  class="form-control"   style="width:100px;" value="<?=$fromdate?>" placeholder="기간 시작일">  &nbsp;   ~ &nbsp;  
			   <input type="date" id="todate" name="todate" size="12"   class="form-control"   style="width:100px;" value="<?=$todate?>" placeholder="기간 끝">  &nbsp;     </span> 
			   &nbsp;&nbsp;
				   
				<?php if($chkMobile) { ?>
						</div>
					<div class="d-flex justify-content-center align-items-center">  	
				<?php } ?>		
	 		   
	   				   
				   &nbsp;				
			<div class="inputWrap">
				<input type="text" id="search" name="search" value="<?=$search?>" autocomplete="off"  class="form-control w-auto mx-1" > &nbsp;			
				<button class="btnClear"></button>
			</div>				
			<div id="autocomplete-list">
			</div>
			 &nbsp;												   			   
				<button type="button" id="searchBtn" class="btn btn-dark  btn-sm"> <i class="bi bi-search"></i>  </button>	&nbsp;&nbsp;
				<button type="button" class="btn btn-dark  btn-sm me-1" id="writeBtn"> <i class="bi bi-pencil-fill"></i> 신규  </button> 	    			 
		</div>
	</div>
  </div>	
<style>
th {
    white-space: nowrap;
}
</style>		  
<div class="card mb-2">
<div class="card-body">	  	  
   <div class="table-responsive"> 	
   <table class="table table-hover " id="myTable">
    <thead class="table-primary">
      <tr>
        <th class="text-center" scope="col" style="width:5%;">번호</th>
        <th class="text-center" scope="col" style="width:120px;">작성일</th>		
        <th class="text-center" scope="col"> 작성자 </th>
		<th class="text-center w-35" scope="col"> 제목 </th>          
        <th class="text-center" scope="col"> 결재라인 </th>        
        <th class="text-center" scope="col"> 결재내역 </th>        
      </tr>
    </thead>	
    <tbody>
      <?php
      if ($page <= 1)
        $start_num = $total_row; // 페이지당 표시되는 첫번째 글순번
      else
        $start_num = $total_row - ($page - 1) * $scale;
	
      while ($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
       		 include $_SERVER['DOCUMENT_ROOT'] .'/eworks/_row.php';		
			 
		echo '<tr style="cursor:pointer;" data-id="'.  $num . '" onclick="redirectToView(' . $num . ')">';
      ?>
		 <td class="text-center"><?= $start_num ?></td>           
		 <td class="text-center" data-order="<?= $indate ?>"> <?=$indate?> </td>	  
         <td class="text-center"> <?= $author ?> </td>          
         <td class="text-start"><?= $outworkplace ?></td>     		 
         <td class="text-start"><?= str_replace('!', ' → ', $e_line) ?></td>
         <td class="text-start"><?= str_replace('!', ' → ', $e_confirm) ?></td>
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
</div>  

</form>	 
      
<div class="container-fluid">
<? include '../footer_sub.php'; ?>
</div>

<script>
var dataTable; // DataTables 인스턴스 전역 변수
var requestetcpageNumber; // 현재 페이지 번호 저장을 위한 전역 변수

// 페이지 로딩
$(document).ready(function(){	
    var loader = document.getElementById('loadingOverlay');
	if(loader)
		loader.style.display = 'none';	        
});

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
    var savedPageNumber = getCookie('requestetcpageNumber');
    if (savedPageNumber) {
        dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
    }

    // 페이지 변경 이벤트 리스너
    dataTable.on('page.dt', function() {
        var requestetcpageNumber = dataTable.page.info().page + 1;
        setCookie('requestetcpageNumber', requestetcpageNumber, 10); // 쿠키에 페이지 번호 저장
    });

    // 페이지 길이 셀렉트 박스 변경 이벤트 처리
    $('#myTable_length select').on('change', function() {
        var selectedValue = $(this).val();
        dataTable.page.len(selectedValue).draw(); // 페이지 길이 변경 (DataTable 파괴 및 재초기화 없이)

        // 변경 후 현재 페이지 번호 복원
        savedPageNumber = getCookie('requestetcpageNumber');
        if (savedPageNumber) {
            dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
        }
    });
});

function restorePageNumber() {
    var savedPageNumber = getCookie('requestetcpageNumber');
    if (savedPageNumber) {
        dataTable.page(parseInt(savedPageNumber) - 1).draw('page');
    }
}

function blinker() {
	$('.blinking').fadeOut(500);
	$('.blinking').fadeIn(500);
}
setInterval(blinker, 1000);

$(document).ready(function() {
    // Event listener for keydown on #search
    $("#search").keydown(function(event) {
        // Check if the pressed key is 'Enter'
        if (event.key === "Enter" || event.keyCode === 13) {
            // Prevent the default action to stop form submission
            event.preventDefault();
            // Trigger click event on #searchBtn
            $("#searchBtn").click();
        }
    });
});


$(document).ready(function() { 

	$("#writeBtn").click(function(){ 		
		var tablename = $("#tablename").val();			
		var url = "write_form.php?tablename=" + tablename ; 

		customPopup(url, '등록', 1000, 800); 		
	 });	 


$("#searchBtn").click(function() { 
    // 페이지 번호를 1로 설정
    currentpageNumber = 1;
    setCookie('currentpageNumber', currentpageNumber, 10); // 쿠키에 페이지 번호 저장

	// Set dateRange to '전체' and trigger the change event
	$('#dateRange').val('전체').change();
    document.getElementById('board_form').submit();
});

}); 

function redirectToView(num) {    
    var tablename = $("#tablename").val();
    	
    var url = "write_form.php?mode=view&num=" + num         
        + "&tablename=" + tablename;   
	customPopup(url, '', 1000, 800); 			
}

function restorePageNumber() {    
    location.reload();
}

// 서버에 작업 기록
$(document).ready(function(){
	saveLogData('<?=$title_message?>'); // 다른 페이지에 맞는 menuName을 전달
});
</script> 
</body>
</html>