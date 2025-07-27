<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if(!isset($_SESSION["level"]) || $_SESSION["level"]>5) {
	sleep(1);
	header("Location:" . $WebSite . "login/login_form.php"); 
	exit;
}   

include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';

$title_message = 'DH모터 출고완료';
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
				

$nowday=date("Y-m-d");   // 현재일자 변수지정   
$counter=1;
 
$sum=array();   

$fromdate = isset($_REQUEST['fromdate']) ? $_REQUEST['fromdate'] : '';  
$todate = isset($_REQUEST['todate']) ? $_REQUEST['todate'] : '';     
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  

if($fromdate=="")
{
	$fromdate = date('Y-m-d', strtotime('-1 day'));
}
if($todate=="")
{
	$todate = date('Y-m-d'); // 오늘 날짜
	$Transtodate = strtotime($todate.'+1 days');
	$Transtodate = date("Y-m-d",$Transtodate);
}
    else
	{
	$Transtodate=strtotime($todate);
	$Transtodate=date("Y-m-d",$Transtodate);
	}

 
	// 검색 기간이 설정된 경우 해당 기간으로, 아니면 오늘부터 검색
	if($fromdate != "" && $todate != "") {
		$common = " WHERE outputdate >= '$fromdate' AND outputdate <= '$Transtodate' AND is_deleted IS NULL ORDER BY outputdate ASC ";
	} else {
		// 납기일(deadline)이 오늘 자정부터 미래인 경우를 조건으로 설정
		$common = " WHERE outputdate >= CURDATE() AND is_deleted IS NULL ORDER BY outputdate ASC ";
	}
	
	
	$sql = "select * from " . $DB . "." . $tablename .  " " . $common; 			
 
   $counter=0;
	// 배열 변수 초기화
	$num_arr = array();
	$deadline_arr = array();
	$outputdate_arr = array();
	$workplacename_arr = array();
	$secondord_arr = array();
	$contentslist_arr = array();
	$loadplace_arr = array();
	$address_arr = array();
	$deliverymethod_arr = array();
	$chargedman_arr = array();
	$chargedmantel_arr = array();
	$comment_arr = array();
	$statement_sent_at_arr = array();

		 	 
?>
		  
<div class="container mt-2">

<div class="card"> 	  
		<div class="card-body"> 	  
		<div class="d-flex align-items-center justify-content-center mt-2 mb-3 m-2">
			<h3 > DH 모터 출고완료 리스트 </h3>
		</div>			
				<div class="d-flex align-items-center justify-content-end mt-1 m-2">
					<button  type="button" class="btn btn-secondary btn-sm me-1" id="refresh"> <ion-icon name="refresh-outline"></ion-icon> 새로고침 </button>	 
					<button  type="button" class="btn btn-dark btn-sm me-1" onclick="generatePDF()"> PDF 저장 </button>
					<button  type="button" class="btn btn-secondary btn-sm" onclick="self.close();" > <i class="bi bi-x-lg"></i> 닫기 </button>&nbsp;	
				</div>
		
				<form method="GET" action="">
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
									<button type="button" class="btn btn-dark btn-sm me-1 change_dateRange" onclick='threeDaysAgo()'> 전전전일 </button>	
									<button type="button" class="btn btn-dark btn-sm me-1 change_dateRange" onclick='dayBeforeYesterday()'> 전전일 </button>	
									<button type="button" class="btn btn-dark btn-sm me-1 change_dateRange" onclick='yesterday()'> 전일 </button> 						
									<button type="button" class="btn btn-outline-danger btn-sm me-1 change_dateRange" onclick='this_today()'> 오늘 </button>
									<button type="button" class="btn btn-secondary btn-sm me-1 change_dateRange" onclick='this_month()'> 다음날 </button>
									<button type="button" class="btn btn-secondary btn-sm me-1 change_dateRange" onclick='twoDaysLater()'> 다다음날 </button> 
									<button type="button" class="btn btn-secondary btn-sm me-1 change_dateRange" onclick='threeDaysLater()'> 다다다음날 </button> 
								</div>

							</div>
						</div>		
					   <input type="date" id="fromdate" name="fromdate"   class="form-control"   style="width:100px;" value="<?=$fromdate?>" >  &nbsp;   ~ &nbsp;  
					   <input type="date" id="todate" name="todate"  class="form-control"   style="width:100px;" value="<?=$todate?>" >  &nbsp;     </span> 
					   &nbsp;&nbsp;		 			   		
					  <button type="submit" class="btn btn-dark  btn-sm" > <i class="bi bi-search"></i>  검색 </button> 		  		  
			</div>
			</form>
		</div>
	</div>
</div>
</form>


<div id="content-to-print">	
<br>
<div class="container-fluid mt-3">
<div class="d-flex align-items-center justify-content-center mb-1 m-2">		
	<table class="table table-hover table-sm" >
	<thead class="table-primary">
		<tr>
			<th class="text-center align-middle" style="width:80px;" >출고예정</th>
			<th class="text-center align-middle" style="width:80px;">출고일</th>
			<th class="text-center align-middle" style="width:100px;">발주처</th>
			<th class="text-center align-middle" style="width:150px;">현장명</th>
			<th class="text-center align-middle" style="width:150px;">내역</th>
			<th class="text-center align-middle" style="width:50px;" >상차</th>
			<th class="text-center align-middle" style="width:150px;">배송주소</th>
			<th class="text-center align-middle" style="width:70px;">배송방법</th>
			<th class="text-center align-middle" style="width:60px;">받는분</th>
			<th class="text-center align-middle" style="width:80px;">연락처</th>
			<th class="text-center align-middle" style="width:80px;" >전달사항</th>
			<th class="text-center align-middle" style="width:80px;" >명세표</th>
			<th class="text-center align-middle" style="width:80px;" >전송시간</th>
		</tr>
	</thead>
	<tbody>		
<?php
 try{
   $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh
   $rowNum = $stmh->rowCount();  

   while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {	

	  include $_SERVER['DOCUMENT_ROOT'] . '/motor/_contentload.php';  

	  if($orderdate!="0000-00-00" and $orderdate!="1970-01-01"  and $orderdate!="") $orderdate = date("Y-m-d", strtotime( $orderdate) );
			else $orderdate="";
	  if($deadline!="0000-00-00" and $deadline!="1970-01-01" and $deadline!="")  $deadline = date("Y-m-d", strtotime( $deadline) );
			else $deadline="";
	  if($outputdate!="0000-00-00" and $outputdate!="1970-01-01" and $outputdate!="")  $outputdate = date("Y-m-d", strtotime( $outputdate) );
			else $outputdate="";		      
	  if($demand!="0000-00-00" and $demand!="1970-01-01" and $demand!="")  $demand = date("Y-m-d", strtotime( $demand) );
			else $demand="";						
	   
	   $num_arr[$counter] = $num;
	   $deadline_arr[$counter] = $deadline;
	   $outputdate_arr[$counter] = $outputdate;
	   $workplacename_arr[$counter] = $workplacename;
	   $secondord_arr[$counter] =$secondord;
	   $loadplace_arr[$counter] =$loadplace ;
	   $address_arr[$counter]=$address ;
	   $deliverymethod_arr[$counter]=$deliverymethod ;
	   $chargedman_arr[$counter]=$chargedman ;
	   $chargedmantel_arr[$counter]=$chargedmantel ;
	   $comment_arr[$counter]=$comment ;	       		
	   // $statement_sent_at_arr[$counter] = $statement_sent_at ? date('H:i', strtotime($statement_sent_at)) : '';
	   $statement_sent_at_arr[$counter] = $statement_sent_at ;
			
		print '<tr>';
		print ' <td class="text-center align-middle" onclick="handleRowClick(' . $num . ')">' . iconv_substr($deadline, 5, 5, "utf-8") . ' </td>';
		print ' <td class="text-center align-middle" onclick="handleRowClick(' . $num . ')">' . iconv_substr($outputdate, 5, 5, "utf-8") . ' </td>';
		print ' <td class="text-center align-middle" onclick="handleRowClick(' . $num . ')">' . $secondord . ' </td>';
		print ' <td class="text-start align-middle fw-bold text-primary" onclick="handleRowClick(' . $num . ')">' . $workplacename_arr[$counter] . ' </td>';
		print ' <td class="text-start align-middle" onclick="handleRowClick(' . $num . ')">' . $contentslist_arr[$counter] . ' </td>';
		print ' <td class="text-center align-middle" onclick="handleRowClick(' . $num . ')">' . $loadplace_arr[$counter] . ' </td>';
		print ' <td class="text-start align-middle" onclick="handleRowClick(' . $num . ')">' . $address . ' </td>';
		print ' <td class="text-center align-middle" onclick="handleRowClick(' . $num . ')">' . $deliverymethod . ' </td>';
		print ' <td class="text-center align-middle" onclick="handleRowClick(' . $num . ')">' . $chargedman . ' </td>';
		print ' <td class="text-start align-middle" onclick="handleRowClick(' . $num . ')">' . $chargedmantel . ' </td>';
		print ' <td class="text-center align-middle" onclick="handleRowClick(' . $num . ')">' . $comment_arr[$counter] . ' </td>';
		print ' <td class="text-center align-middle"><a href="#" onclick="window.open(\'/motor/invoice_sales.php?num=' . $num . '\', \'_blank\', \'width=800,height=980\'); return false;"><i class="bi bi-eye"></i></a></td>';
		print ' <td class="text-center align-middle">' . $statement_sent_at_arr[$counter] . ' </td>';
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
        var result = 'DH모터 출고예정(' + workplace +')' + currentDate + currentTime + '.pdf';	
		
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

</script>
