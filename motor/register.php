<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if(!isset($_SESSION["level"]) || $_SESSION["level"]>5) {
	sleep(1);
	header("Location:" . $WebSite . "login/login_form.php"); 
	exit;
}   

include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';

$title_message = 'DH모터 일일접수 리스트';
$tablename = 'motor';
?>

<style>
table, th, td {
	border: 1px solid black !important; /* 굵은 테두리 적용 */
	font-size: 13px !important;
}
	@media print {
		body {
			width: 210mm; /* Approx width of A4 paper */
			height: 297mm; /* Height of A4 paper */
			margin: 5mm; /* Provide a margin */
			font-size: 10pt; /* Reduce font size for printing */
		}
		.table {
			width: 100%; /* Full width tables */
			table-layout: fixed; /* Uniform column sizing */
		}
		.table th, .table td {
			padding: 2px; /* Reduce padding */
			border: 1px solid #ddd; /* Ensure borders are visible */
		}
		.text-center {
			text-align: center; /* Maintain center alignment */
		}
		.fw-bold {
			font-weight: bold; /* Ensure bold text is printed */
		}
	}
</style>
	
</head>

<title> <?=$title_message?> </title> 

<body>	

<html lang="ko">

 
<?php 

$fromdate = isset($_REQUEST['fromdate']) ? $_REQUEST['fromdate'] : '';  
$todate = isset($_REQUEST['todate']) ? $_REQUEST['todate'] : '';  

// 현재 날짜
$currentDate = date("Y-m-d");

// fromdate 또는 todate가 빈 문자열이거나 null인 경우
if ($fromdate === "" || $fromdate === null || $todate === "" || $todate === null) {
    $fromdate = date("Y-m-d", strtotime($currentDate)); // 오늘날짜
    $todate = $currentDate; // 현재 날짜
	$Transtodate = $todate;
} else {
    // fromdate와 todate가 모두 설정된 경우 (기존 로직 유지)
    $Transtodate = $todate;
}
		
 
$now = date("Y-m-d"); // 현재일자 변수지정

// 납기일(deadline)이 오늘 자정부터 미래인 경우를 조건으로 설정
$common = " WHERE orderdate between '$fromdate' and '$Transtodate' and is_deleted IS NULL ORDER BY orderdate ASC ";


$sql = "select * from " . $DB . "." . $tablename .  " " . $common; 							

$nowday=date("Y-m-d");   // 현재일자 변수지정   
$counter=1;
 
$sum=array();   
  
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  
		 	 
?>
		 
<div class="container mt-2">

<form id="board_form" name="board_form" method="post" action="register.php">  
	
	<div class="card"> 	  
		<div class="card-body"> 	  
				<div class="d-flex align-items-center justify-content-end mt-1 m-2">
					<button  type="button" class="btn btn-secondary btn-sm me-1" id="refresh"> <i class="bi bi-arrow-clockwise"></i> 새로고침 </button>	 
					<button  type="button" class="btn btn-dark btn-sm me-1" onclick="generatePDF()"> <i class="bi bi-floppy-fill"></i> PDF 저장 </button>
					<button  type="button" class="btn btn-secondary btn-sm" onclick="self.close();" > <i class="bi bi-x-lg"></i> 닫기 </button>&nbsp;	
				</div>
		
				<div class="d-flex  p-1 m-1 mt-1 mb-1 justify-content-center align-items-center"> 	  
						<!-- 기간부터 검색까지 연결 묶음 start -->
							<span id="showdate" class="btn btn-dark btn-sm " > 기간 </span>	&nbsp; 
							<div id="showframe" class="card">
								<div class="card-header " style="padding:2px;">
									<div class="d-flex justify-content-center align-items-center">  
										기간 설정
									</div>
								</div>
								<div class="card-body">
									<div class="d-flex justify-content-center align-items-center">  	
										<button type="button" class="btn btn-outline-success btn-sm me-1 change_dateRange"   onclick='alldatesearch()' > 전체 </button>  
										<button type="button" id="preyear" class="btn btn-outline-primary btn-sm me-1 change_dateRange"   onclick='pre_year()' > 전년도 </button>  
										<button type="button" id="three_month" class="btn btn-dark btn-sm me-1  change_dateRange"  onclick='pre_month()' > 전월 </button>
										<button type="button" id="prepremonth" class="btn btn-dark btn-sm me-1  change_dateRange"  onclick='dayBeforeYesterday()' > 전전일 </button>	
										<button type="button" id="premonth" class="btn btn-dark btn-sm me-1  change_dateRange"  onclick='yesterday()' > 전일 </button> 						
										<button type="button" class="btn btn-outline-danger btn-sm me-1  change_dateRange"  onclick='this_today()' > 오늘 </button>
										<button type="button" id="thismonth" class="btn btn-dark btn-sm me-1  change_dateRange"  onclick='this_month()' > 당월 </button>
										<button type="button" id="thisyear" class="btn btn-dark btn-sm me-1  change_dateRange"  onclick='this_year()' > 당해년도 </button> 
									</div>
								</div>
							</div>		
						   <input type="date" id="fromdate" name="fromdate"   class="form-control"   style="width:100px;" value="<?=$fromdate?>" >  &nbsp;   ~ &nbsp;  
						   <input type="date" id="todate" name="todate"  class="form-control"   style="width:100px;" value="<?=$todate?>" >  &nbsp;     </span> 
						   &nbsp;&nbsp;		 			   		
					  <button id="searchBtn" type="button" class="btn btn-dark  btn-sm" > <i class="bi bi-search"></i> 검색 </button> 		  		  
			</div>
		</div>
	</div>
</form>

<div id="content-to-print">	
<br>
<div class="container-fluid mt-3">
<div class="d-flex align-items-center justify-content-center mt-2 mb-3 m-2">
	<h3 > <?=$title_message?> </h3>
</div>

<div class="d-flex align-items-center justify-content-center mb-1 m-2">	
	
        <table class="table table-borderd table-hover" >
        <thead class="table-primary">
            <tr>
                <th class="text-center align-middle" style="width:50px;" >접수일</th>
                <th class="text-center align-middle" style="width:50px;" >출고예정</th>
                <th class="text-center align-middle" style="width:50px;">출고일</th>
                <th class="text-center align-middle" style="width:100px;">발주처</th>
                <th class="text-center align-middle" style="width:150px;">현장명</th>
                <th class="text-center align-middle" style="width:150px;">내역</th>
                <th class="text-center align-middle"  style="width:80px;" >전달사항</th>
            </tr>
        </thead>
        <tbody>
		
<?php

 try{  
 
   $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh
   $rowNum = $stmh->rowCount();  

   while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {	
		 include '_contentload.php';
			
		print '<tr onclick="handleRowClick(' . $num . ')">';
		print ' <td class="text-center align-middle">' . iconv_substr($orderdate, 5, 5, "utf-8") . ' </td>';
		print ' <td class="text-center align-middle">' . iconv_substr($deadline, 5, 5, "utf-8") . ' </td>';
		print ' <td class="text-center align-middle">' . iconv_substr($outputdate, 5, 5, "utf-8") . ' </td>';
		print ' <td class="text-center align-middle">' . $secondord . ' </td>';
		print ' <td class="text-start align-middle fw-bold text-primary">' . $workplacename_arr[$counter] . ' </td>';
		print ' <td class="text-start align-middle">' . $contentslist_arr[$counter] . ' </td>';
		print ' <td class="text-center align-middle">' . $comment_arr[$counter] . ' </td>';
		print '</tr>';
	
	   						
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
 
</div>    <!-- end of content-to-print --> 

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

$(document).ready(function(){	
	$("#refresh").click(function(){  location.reload();   });	          // refresh
});
	
    function generatePDF() {
		var workplace = '<?php echo $workplacename; ?>';
        var d = new Date();
        var currentDate = ( d.getMonth() + 1 ) + "-" + d.getDate()  + "_" ; 
        var currentTime = d.getHours()  + "_" + d.getMinutes() +"_" + d.getSeconds() ;
        var result = 'DH모터 일일접수(' + workplace +')' + currentDate + currentTime + '.pdf';	
		
        var element = document.getElementById('content-to-print');
        var opt = {
            margin:       0,
            filename:     result,
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2 },
//            jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
			jsPDF: { unit: 'in', format: 'letter', orientation: 'landscape' }  // 가로방향
	
        };
        html2pdf().from(element).set(opt).save();
    }
    
function handleRowClick(row) {
    
    if (row !== '') {
        var link = 'write_form.php?mode=view&num=' + row;
        customPopup(link, '수주내역', 1850, 900);
    }
}


function SearchEnter(){

    if(event.keyCode == 13){		
		saveSearch();
    }
}

function saveSearch() {
	
    document.getElementById('board_form').submit();
    
}


</script>
