<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  
$title_message = 'DH모터 화물업체/화물/택배';
$tablename = 'motor';

if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php"); 
    exit;
}       
include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';   
?>

<title> <?=$title_message?> </title>
<link href="css/style.css" rel="stylesheet" >   

<style>
	#viewTable th, td{
		border : 1px #aaaaaa solid ;
	}

</style>
</head>

<body>    
<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php'); ?>   


<style>
.smallfont {
    border: 1px solid #ccc !important; /* 가늘고 옅은 회색 테두리 */
    font-size: 11px !important;
    padding: 1px;
}

table, th {
    border: 1px solid #ccc !important; /* 가늘고 옅은 회색 테두리 */
    font-size: 15px !important;
    padding : 0px;
}

@media print {
    body {
        width: 210mm; /* Approx width of A4 paper */
        height: 297mm; /* Height of A4 paper */
        margin: 5mm; /* Provide a margin */
        font-size: 10pt; /* Reduce font size for printing */
    }   
    .table th, .table td {
        padding: 1px; /* Reduce padding */               
    }
    .text-center {
        text-align: center; /* Maintain center alignment */
    }
}

td {
    padding-top: 1px;
    padding-bottom: 1px;
    border: 1px solid #ccc !important; /* 가늘고 옅은 회색 테두리 */
    font-size: 14px !important;
    padding-left: 1px; /* 좌측 여백 */
    padding-right: 1px; /* 우측 여백 */
}
  
input[type="checkbox"] {
    transform: scale(1.2); /* 크기를 1.5배로 확대 */    
}

.pagebreak { page-break-before: always; }
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
// 시작일은 전달 1일, 종료일은 전달의 마지막 일자로 자동 설정
if ($fromdate === "" || $fromdate === null || $todate === "" || $todate === null) {
    // 현재 날짜에서 전달의 1일 구하기
    $firstDayPrevMonth = date("Y-m-01", strtotime("first day of last month"));
    // 현재 날짜에서 전달의 마지막 일자 구하기
    $lastDayPrevMonth = date("Y-m-t", strtotime("last day of last month"));
    $fromdate = $firstDayPrevMonth;
    $todate = $lastDayPrevMonth;
    $Transtodate = $todate;
} else {
    // fromdate와 todate가 모두 설정된 경우 (기존 로직 유지)
    $Transtodate = $todate;
}


$today_short = iconv_substr($todate, 5, 5, "utf-8");

// 납기일(deadline)이 지정된 기간 내이고, 화물/택배 운송이며, 금액이 입력된 항목들 또는 배차 항목들을 포함
$common = " WHERE deadline BETWEEN '$fromdate' AND '$Transtodate' 
            AND (deliverymethod LIKE '%화물%' OR deliverymethod LIKE '%택배%' OR deliverymethod = '배차') 
            AND is_deleted IS NULL 
            AND (
                (outputdate IS NULL OR outputdate = '0000-00-00') 
                OR (cargo_delwrapamount IS NOT NULL AND cargo_delwrapamount != '' AND cargo_delwrapamount != '0')
                OR (delipay IS NOT NULL AND delipay != '' AND delipay != '0')
                OR (delcompany = '25시콜')
                OR (deliverymethod = '배차')
            )
            ORDER BY deadline ASC ";

$sql = "select * from " . $DB . "." . $tablename .  " " . $common;                            

$nowday=date("Y-m-d");   // 현재일자 변수지정   
$counter=1;
 
$sum=array();   
 
// $counter=1로 유지 (일련번호는 1부터 시작)
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
$delipay_arr = array();
$delwrapmethod_arr = array();  // 포장방식 배열
$delwrapsu_arr = array();      // 포장수량 배열
$delwrapamount_arr = array();  // 금액(만원) 배열
$delwrapweight_arr = array();  // 무게(kg) 배열
$delwrappaymethod_arr = array();  // 결제방식 배열

?>

<form id="board_form" name="board_form" method="post"  >
         
<div class="container mt-2">

	<div class="card"> 	  
		<div class="card-body"> 	  				
				<div class="d-flex align-items-center justify-content-center mt-1 m-2">
                    <h5>  <?=$title_message?> </h5>  &nbsp;&nbsp;&nbsp;   
                    <small class="ms-5 text-muted"> 화물업체/화물/택배 통계 </small>  
					<button  type="button" class="btn btn-secondary btn-sm ms-5 me-1 " id="refresh"> <i class="bi bi-arrow-clockwise"></i> 새로고침 </button>     					
					<button class="btn btn-secondary btn-sm m-1" onclick="self.close();" > <i class="bi bi-x-lg"></i> 닫기 </button>&nbsp;    
				</div>					
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
										<button type="button" class="btn btn-dark btn-sm me-1 change_dateRange" onclick='twoMonthsAgo()'> 전전달 </button>	
										<button type="button" class="btn btn-dark btn-sm me-1 change_dateRange" onclick='lastMonth()'> 전달 </button>	
										<button type="button" class="btn btn-outline-danger btn-sm me-1 change_dateRange" onclick='thisMonth()'> 이번달 </button>
									</div>
								</div>
							</div>		
						   <input type="date" id="fromdate" name="fromdate"   class="form-control" style="width:100px;" value="<?=$fromdate?>" >  &nbsp;   ~ &nbsp;  
						   <input type="date" id="todate" name="todate"  class="form-control" style="width:100px;" value="<?=$todate?>" >  &nbsp;     </span> 
						   &nbsp;&nbsp;		 			   		
					  <button id="searchBtn" type="button" class="btn btn-dark  btn-sm" > <i class="bi bi-search"></i> 검색  </button>
					  <button id="monthlyStatsBtn" type="button" class="btn btn-info  btn-sm ms-2" > <i class="bi bi-bar-chart-fill"></i> 월별 통계  </button> 		  		  
			</div>
		</div>
	</div>

<div id="content-to-print">    
<br>
<div class="container-fluid mt-2">
<div class="d-flex align-items-center justify-content-center mb-1 ">        
    <table class="table table-hover" id="myTable">
    <thead class="table-primary">
        <tr>
            <th class="text-center align-middle" style="width:50px;">번호</th>
            <th class="text-center align-middle" style="width:100px;" >출고일</th>            
            <th class="text-center align-middle" style="width:100px;" >발주처</th>
            <th class="text-center align-middle" style="width:100px;" >받는분</th>
            <th class="text-center align-middle" style="width:160px;">연락처</th>
            <th class="text-center align-middle" style="width:200px;">도착지</th>            
            <th class="text-center align-middle" style="width:200px;">제품명</th>
            <th class="text-center align-middle" style="width:50px;" >포장</th>
            <th class="text-center align-middle" style="width:50px;">화물<br>택배</th>
            <th class="text-center align-middle" style="width:80px;">화물업체</th>
            <th class="text-center align-middle" style="width:50px;">수량</th>            
            <th class="text-center align-middle"  style="width:150px;" >운임</th>            
        </tr>
    </thead>
    <tbody>
        
<?php

 try {  
 
   $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh
   $rowNum = $stmh->rowCount();  

   while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {    
        include '_contentload.php';
            
		// $deliverymethod = str_replace('경동','',$deliverymethod);
        print '<tr>';
        print ' <td class="text-center align-middle">' . $counter . '</td>';
        print ' <td class="text-center align-middle">' . htmlspecialchars($deadline) . '</td>';
        print ' <td class="text-center align-middle"  onclick="handleRowClick(' . $num . ')" >' .  htmlspecialchars($secondord) . ' </td>';        
        print ' <td class="text-center align-middle"  onclick="handleRowClick(' . $num . ')">' .  htmlspecialchars($chargedman_arr[$counter]) . ' </td>';
        print ' <td class="text-start align-middle"  onclick="handleRowClick(' . $num . ')">' . htmlspecialchars(trim($chargedmantel)) . ' </td>';    
        print ' <td class="text-start align-middle"  onclick="handleRowClick(' . $num . ')">' .  htmlspecialchars($address_arr[$counter]) . ' </td>';        
        print ' <td class="text-start align-middle"  onclick="handleRowClick(' . $num . ')">' .  $contentslist . ' </td>';
				
		// 포장 방식 표시 (운송방법에 따라 다른 컬럼 사용)
		$wrap_display = '-';
		if ($deliverymethod == '배차') {
			// 배차인 경우 포장 방식 표시 안함
			$wrap_display = '-';
		} else if (strpos($deliverymethod, '화물') !== false) {
			// 화물인 경우 cargo_delwrapmethod 사용
			$wrap_display = !empty($cargo_delwrapmethod) ? $cargo_delwrapmethod : '-';
		} else if (strpos($deliverymethod, '택배') !== false || $deliverymethod == '선/택배' || $deliverymethod == '착/택배') {
			// 택배인 경우 delwrapmethod 사용 (선/택배, 착/택배 포함)
			$wrap_display = !empty($delwrapmethod) ? $delwrapmethod : '-';
		}
		echo '<td class="text-center align-middle">' . htmlspecialchars($wrap_display) . '</td>';
						   

		if ($deliverymethod == '선/대신화물' || $deliverymethod == '착/대신화물' ) {
			echo '<td class="text-center align-middle"><span class="badge bg-danger">' . $deliverymethod . '</span></td>';
		} else if ($deliverymethod == '선/경동화물' || $deliverymethod == '착/경동화물') {
			echo '<td class="text-center align-middle"><span class="badge bg-primary">' . $deliverymethod . '</span></td>';
		} else if ($deliverymethod == '배차') {
			echo '<td class="text-center align-middle"><span class="badge bg-success">' . $deliverymethod . '[' . $delcompany . ']</span></td>';
		} else {
			echo '<td class="text-center align-middle">' . $deliverymethod . '</td>';
		}

		// 화물업체 정보 표시 - 배차일 때만 화물업체 표시
		if ($deliverymethod == '배차') {
			$company_display = !empty($delcompany) ? $delcompany : '미지정';
			echo '<td class="text-center align-middle"><small>' . htmlspecialchars($company_display) . '</small></td>';
		} else {
			echo '<td class="text-center align-middle">-</td>';
		}

		// 수량 표시 (운송방법에 따라 다른 컬럼 사용)
		$quantity_display = '-';
		if ($deliverymethod == '배차') {
			// 배차인 경우 수량 표시 안함 (또는 다른 로직 필요시 추가)
			$quantity_display = '-';
		} else if (strpos($deliverymethod, '화물') !== false) {
			// 화물인 경우 cargo_delwrapsu 사용
			if (!empty($cargo_delwrapsu)) {
				$cargo_su_numeric = floatval(str_replace(',', '', $cargo_delwrapsu));
				$quantity_display = is_numeric($cargo_su_numeric) && $cargo_su_numeric > 0 ? number_format($cargo_su_numeric) : '-';
			}
		} else if (strpos($deliverymethod, '택배') !== false || $deliverymethod == '선/택배' || $deliverymethod == '착/택배') {
			// 택배인 경우 delwrapsu 사용 (선/택배, 착/택배 포함)
			if (!empty($delwrapsu)) {
				$delwrap_su_numeric = floatval(str_replace(',', '', $delwrapsu));
				$quantity_display = is_numeric($delwrap_su_numeric) && $delwrap_su_numeric > 0 ? number_format($delwrap_su_numeric) : '-';
			}
		}
		echo '<td class="text-center align-middle">' . htmlspecialchars($quantity_display) . '</td>';
		
		// 운임 표시 (운송방법에 따라 다른 컬럼 사용)
		$amount_display = '-';
		if ($deliverymethod == '배차') {
			// 배차인 경우 delipay 사용
			if (!empty($delipay)) {
				$delipay_numeric = floatval(str_replace(',', '', $delipay));
				$amount_display = is_numeric($delipay_numeric) && $delipay_numeric > 0 ? number_format($delipay_numeric) . '원' : '-';
			}
		} else if (strpos($deliverymethod, '화물') !== false) {
			// 화물인 경우 cargo_delwrapamount 사용
			if (!empty($cargo_delwrapamount)) {
				$cargo_amount_numeric = floatval(str_replace(',', '', $cargo_delwrapamount));
				$amount_display = is_numeric($cargo_amount_numeric) && $cargo_amount_numeric > 0 ? number_format($cargo_amount_numeric) . '원' : '-';
			}
		} else if (strpos($deliverymethod, '택배') !== false || $deliverymethod == '선/택배' || $deliverymethod == '착/택배') {
			// 택배인 경우 delwrapamount 사용 (선/택배, 착/택배 포함)
			if (!empty($delwrapamount)) {
				$delwrap_amount_numeric = floatval(str_replace(',', '', $delwrapamount));
				$amount_display = is_numeric($delwrap_amount_numeric) && $delwrap_amount_numeric > 0 ? number_format($delwrap_amount_numeric) . '원' : '-';
			}
		}
		echo '<td class="text-end align-middle">' . htmlspecialchars($amount_display) . '</td>';
		
		
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

var dataTable; // DataTables 인스턴스 전역 변수
var delpageNumber; // 현재 페이지 번호 저장을 위한 전역 변수

$(document).ready(function() {            
    // DataTables 초기 설정
    dataTable = $('#myTable').DataTable({
        "paging": true,
        "ordering": true,
        "searching": true,
        "pageLength": 500,
        "lengthMenu": [25, 50, 100, 200, 500, 1000],
        "language": {
            "lengthMenu": "Show _MENU_ entries",
            "search": "Live Search:"
        },
        "order": [[0, 'desc']], 
        "dom": 'tip' // 't' = table, 'i' = information, 'p' = pagination
    });

    // 페이지 길이 셀렉트 박스 변경 이벤트 처리
    $('#myTable_length select').on('change', function() {
        var selectedValue = $(this).val();
        dataTable.page.len(selectedValue).draw(); // 페이지 길이 변경 (DataTable 파괴 및 재초기화 없이)
    });
});

function restorePageNumber() {
    var savedPageNumber = getCookie('delpageNumber');
    // if (savedPageNumber) {
        // dataTable.page(parseInt(savedPageNumber) - 1).draw('page');
    // }
    location.reload(true);
}

$(document).ready(function(){    
  $("#submitFormBtn").click(function(){  
        var form = document.getElementById('board_form');
        form.action = '/motor/invoice_delivery.php'; // dynamically set the action
        form.target = 'customPopup1';
        var popup = window.open('', 'customPopup1', 'width=1600,height=900');
        form.submit();
        popup.focus();
    });        

    $("#refresh").click(function(){  
        location.reload();  
    }); // refresh

    $("#monthlyStatsBtn").click(function(){  
        var link = 'delivery_monthly_stats.php';
        customPopup(link, '월별 운임 통계', 1600, 600);
    }); // monthly stats

    $("#delivery_format").click(function(){  
        location.href = 'delivery_format.php';
    }); 

    $("#labelBtn").click(function(){  
        var form = document.getElementById('board_form');
        form.action = 'label_tag.php';
        form.target = 'customPopup2';
        form.method = 'post';
        var popup = window.open('', 'customPopup2', 'width=1400,height=900');
        form.submit();
        popup.focus();
    }); // refresh

    // 체크박스 기능 제거됨 - 일련번호로 변경
});
    
    function generatePDF() {
        var workplace = '택배화물';
        var d = new Date();
        var currentDate = ( d.getMonth() + 1 ) + "-" + d.getDate()  + "_" ;
        var currentTime = d.getHours()  + "_" + d.getMinutes() +"_" + d.getSeconds() ;
        var result = 'DH모터 (' + workplace +')' + currentDate + currentTime + '.pdf';    
        
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
// 저장 기능 제거됨 - 포장/수량/운임이 일반 텍스트로 표시되므로 편집 불가
</script>