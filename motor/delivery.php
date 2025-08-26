<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if(!isset($_SESSION["level"]) || $_SESSION["level"]>5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php"); 
    exit;
}   

include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';

$title_message = 'DH모터 화물택배';
$tablename = 'motor';

?>

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
if ($fromdate === "" || $fromdate === null || $todate === "" || $todate === null) {
    $fromdate = date("Y-m-d", strtotime($currentDate)); // 오늘날짜
    $todate = $currentDate; // 현재 날짜
	$Transtodate = $todate;
} else {
    // fromdate와 todate가 모두 설정된 경우 (기존 로직 유지)
    $Transtodate = $todate;
}
 
$now = date("Y-m-d"); // 현재일자 변수지정

$todate = date("Y-m-d"); // 현재일자 변수지정

$today_short = iconv_substr($todate, 5, 5, "utf-8");

// 납기일(deadline)이 오늘 자정부터 미래인 경우를 조건으로 설정
$common = " WHERE deadline BETWEEN '$fromdate' AND '$Transtodate' 
            AND (deliverymethod LIKE '%화물%' OR deliverymethod LIKE '%택배%') 
            AND is_deleted IS NULL 
            AND (outputdate IS NULL OR outputdate = '0000-00-00') 
            ORDER BY deadline ASC ";

$sql = "select * from " . $DB . "." . $tablename .  " " . $common;                            

$nowday=date("Y-m-d");   // 현재일자 변수지정   
$counter=1;
 
$sum=array();   
 
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
				<div class="d-flex align-items-center justify-content-end mt-1 m-2">
					<button  type="button" class="btn btn-secondary btn-sm me-1" id="refresh"> <i class="bi bi-arrow-clockwise"></i> 새로고침 </button>     
					<button  type="button" class="btn btn-danger btn-sm me-1" id="delivery_format"> 경동택배 요청 형식변환 </button>     
					<button type="button" id="submitFormBtn" class="btn btn-primary btn-sm me-1"> 선택된 항목 출력 </button>
					<!-- <button class="btn btn-dark btn-sm me-1" onclick="generatePDF()"> PDF 저장 </button>  -->
					<button id="labelBtn" class="btn btn-dark btn-sm me-1" > 택배 라벨 </button>
					<button class="btn btn-secondary btn-sm" onclick="self.close();" > <i class="bi bi-x-lg"></i> 닫기 </button>&nbsp;    
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
						   <input type="date" id="fromdate" name="fromdate"   class="form-control" style="width:100px;" value="<?=$fromdate?>" >  &nbsp;   ~ &nbsp;  
						   <input type="date" id="todate" name="todate"  class="form-control" style="width:100px;" value="<?=$todate?>" >  &nbsp;     </span> 
						   &nbsp;&nbsp;		 			   		
					  <button id="searchBtn" type="button" class="btn btn-dark  btn-sm" > <i class="bi bi-search"></i> 검색  </button> 		  		  
			</div>
		</div>
	</div>

<div id="content-to-print">    
<br>
<div class="container-fluid mt-2">
<div class="d-flex align-items-center justify-content-center">
	<div class="alert alert-primary fs-6" role="alert">
	     출고완료는 화면에 나오지 않게 수정했습니다. 출고증 변경사항을 기록한 후 반드시 '저장'해야 출력할때 나옵니다.
	</div>
</div>
<div class="d-flex align-items-center justify-content-center mt-2 mb-2">
    <h4 > DH 모터 금일(<?=$today_short?>) 출고(화물,택배) &nbsp; &nbsp; &nbsp;</h4>
	<button id="saveBtn" type="button" class="btn btn-dark  btn-sm mx-5"  > <i class="bi bi-floppy-fill"></i> 출고증 변경사항 저장  </button>
</div>
<div class="d-flex align-items-center justify-content-center mb-1 ">        
    <table class="table table-hover" id="myTable">
    <thead class="table-primary">
        <tr>
            <th class="text-center align-middle" style="width:50px;"> <input type="checkbox" id="selectAll"></th>
            <th class="text-center align-middle" style="width:100px;" >발주처</th>            
            <th class="text-center align-middle" style="width:100px;" >받는분</th>
            <th class="text-center align-middle" style="width:160px;">연락처</th>
            <th class="text-center align-middle" style="width:200px;">도착지</th>            
            <th class="text-center align-middle" style="width:200px;">제품명</th>
            <th class="text-center align-middle" style="width:50px;" >포장</th>
            <th class="text-center align-middle" style="width:50px;">화물<br>택배</th>
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
        print ' <td class="text-center align-middle"><input type="checkbox" class="row-checkbox" name="recordIds[]" value="' . $num . '"> </td>';
        print ' <td class="text-center align-middle"  onclick="handleRowClick(' . $num . ')" >' .  htmlspecialchars($secondord) . ' </td>';        
        print ' <td class="text-center align-middle"  onclick="handleRowClick(' . $num . ')">' .  htmlspecialchars($chargedman_arr[$counter]) . ' </td>';
        print ' <td class="text-start align-middle"  onclick="handleRowClick(' . $num . ')">' . htmlspecialchars(trim($chargedmantel)) . ' </td>';    
        print ' <td class="text-start align-middle"  onclick="handleRowClick(' . $num . ')">' .  htmlspecialchars($address_arr[$counter]) . ' </td>';        
        print ' <td class="text-start align-middle"  onclick="handleRowClick(' . $num . ')">' .  $contentslist . ' </td>';
				
		echo '<td class="text-center align-middle">' 
		   . '<select id="cargo_delwrapmethod" name="cargo_delwrapmethod" class="form-select form-select-sm w-auto">'
		   .   '<option value=""' . ($cargo_delwrapmethod === ''        ? ' selected' : '') . '></option>'
		   .   '<option value="박스"'   . ($cargo_delwrapmethod === '박스'   ? ' selected' : '') . '>박스</option>'
		   .   '<option value="파렛트"' . ($cargo_delwrapmethod === '파렛트' ? ' selected' : '') . '>파렛트</option>'
		   .   '<option value="묶음박스"' . ($cargo_delwrapmethod === '묶음박스' ? ' selected' : '') . '>묶음박스</option>'
		   . '</select>'
		   . '</td>';
						   

		if ($deliverymethod == '선/대신화물' || $deliverymethod == '착/대신화물' ) {
			echo '<td class="text-center align-middle"><span class="badge bg-danger">' . $deliverymethod . '</span></td>';
		} else if ($deliverymethod == '선/경동화물' || $deliverymethod == '착/경동화물') {
			echo '<td class="text-center align-middle"><span class="badge bg-primary">' . $deliverymethod . '</span></td>';
		} else if ($deliverymethod == '배차') {
			echo '<td class="text-center align-middle"><span class="badge bg-success">' . $deliverymethod . '[' . $delcompany . ']</span></td>';
		} else {
			echo '<td class="text-center align-middle">' . $deliverymethod . '</td>';
		}

				
		echo '<td>'
		   .   '<input type="text" id="cargo_delwrapsu" name="cargo_delwrapsu" autocomplete="off"'
		   .     ' class="form-control text-center"'
		   .     ' value="' . htmlspecialchars($cargo_delwrapsu) . '"'
		   .     ' onkeyup="inputNumberFormat(this)">'
		   . '</td>'		   
		   . '<td>'
		   .   '<input type="text" id="cargo_delwrapamount" name="cargo_delwrapamount" autocomplete="off"'
		   .     ' class="form-control text-end"'
		   .     ' value="' . htmlspecialchars($cargo_delwrapamount) . '"'
		   .     ' onkeyup="inputNumberFormat(this)">'
		   . '</td>';
		
		
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
        "order": [[1, 'desc']], // 발주처 기준 내림정렬
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

    $('#selectAll').click(function() {
        var isChecked = $(this).prop('checked');
        $('.row-checkbox').prop('checked', isChecked);
    });
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

$(document).ready(function(){
  $('#saveBtn').on('click', function(e){
    e.preventDefault();

    // 1) 테이블의 모든 행에 대해 업데이트 데이터 수집
    const updates = $('#myTable tbody tr').map(function(){
      const $tr = $(this);
      return {
        id:     $tr.find('.row-checkbox').val(),                         // num
        method: $tr.find('select[name="cargo_delwrapmethod"]').val(),    // 포장방식
        su:     $tr.find('input[name="cargo_delwrapsu"]').val(),         // 수량
        amount: $tr.find('input[name="cargo_delwrapamount"]').val()      // 금액
      };
    }).get();
	
	console.log(updates);

    if (updates.length === 0) {
      Toastify({
        text: "업데이트할 행이 없습니다.",
        duration: 2000, close:true,
        gravity:"top", position:"center",
        style:{ background:"linear-gradient(to right, #ff6b6b, #f06595)" }
      }).showToast();
      return;
    }

    // 2) AJAX 호출
    $.ajax({
      url: '/motor/insert_wrap.php',
      type: 'POST',
      dataType: 'json',
      data: { updates: JSON.stringify(updates) },
      success: function(res){
        if (res.success) {
          Toastify({
            text: "전체 행의 출고 포장 정보가 저장되었습니다.",
            duration: 2000, close:true,
            gravity:"top", position:"center",
            style:{ background:"linear-gradient(to right, #00b09b, #96c93d)" }
          }).showToast();
          setTimeout(function(){ location.reload(); }, 500);
        } else {
          Toastify({
            text: "저장 실패: " + res.error,
            duration: 2000, close:true,
            gravity:"top", position:"center",
            style:{ background:"linear-gradient(to right, #ff6b6b, #f06595)" }
          }).showToast();
        }
      },
		error: function(jqXHR, textStatus, errorThrown) {
		  // 1) 콘솔에 상세 오류 내용 출력
		  console.error("AJAX 요청 실패:", {
			status: jqXHR.status,
			statusText: jqXHR.statusText,
			responseText: jqXHR.responseText,
			textStatus: textStatus,
			errorThrown: errorThrown
		  });

		  // 2) 사용자에게는 간단히 알림
		  Toastify({
			text: "서버 오류가 발생했습니다: " + errorThrown,
			duration: 2000,
			close: true,
			gravity: "top",
			position: "center",
			style: { background: "linear-gradient(to right, #ff6b6b, #f06595)" }
		  }).showToast();
		}
	  });
	});
});

</script>
