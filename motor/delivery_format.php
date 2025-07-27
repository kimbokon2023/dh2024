<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';

$title_message = 'DH 화물택배';
$tablename = 'motor';

?>

<style>
.smallfont {
    border: 0.5px solid #ccc !important; /* 가늘고 옅은 회색 테두리 */
    font-size: 11px !important;
    padding: 1px;
}

table, th {
    border: 1px solid #ccc !important; /* 가늘고 옅은 회색 테두리 */
    font-size: 13px !important;
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
    font-size: 13px !important;
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
$common = " WHERE deadline between '$fromdate' and '$Transtodate' and (deliverymethod like '%화물%' OR deliverymethod like '%택배%') AND is_deleted IS NULL ORDER BY deadline ASC ";

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
		 


<form id="board_form" name="board_form" method="post" action="delivery_format.php">  

<div class="container mt-2">
	
	<div class="card"> 	  
		<div class="card-body"> 	  
				<div class="d-flex align-items-center justify-content-end mt-1 m-2">
					<button  type="button" class="btn btn-secondary btn-sm me-1" id="refresh"> <i class="bi bi-arrow-clockwise"></i> 새로고침 </button>	 				
					<button  type="button" class="btn btn-dark btn-sm me-1" onclick="generateExcel();"> Excel 저장 </button>
					<button  type="button" class="btn btn-dark btn-sm me-1" onclick="generatePDF();"> PDF 저장 </button>
					<button  type="button" class="btn btn-secondary btn-sm" onclick="self.close();" > <i class="bi bi-x-lg"></i> 닫기 </button>
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
						   <input type="date" id="fromdate" name="fromdate"   class="form-control"   style="width:100px;" value="<?=$fromdate?>" >  &nbsp;   ~ &nbsp;  
						   <input type="date" id="todate" name="todate"  class="form-control"   style="width:100px;" value="<?=$todate?>" >  &nbsp;     </span> 
   
					  <button id="searchBtn" type="button" class="btn btn-dark  btn-sm" > <i class="bi bi-search"></i> 검색 </button> 		  		  
			</div>
		</div>
	</div>
</div>
</form>

<div id="content-to-print">	
<div class="container-fluid mt-2">
<div class="d-flex align-items-center justify-content-center mt-2 mb-2 ">
    <h3 > DH 모터 금일(<?=$today_short?>) 출고(화물,택배) &nbsp; &nbsp; &nbsp; 발송처 : (주)대한  010-3966-2024</h3>
</div>
<div class="d-flex align-items-center justify-content-center mb-1 ">        
    <table class="table table-hover" id="myTable">
    <thead class="table-primary">
        <tr>
            <th class="text-center align-middle" style="width:50px;"> <input type="checkbox" id="selectAll"></th>
            <th class="text-center align-middle" style="width:150px;" >경동/대신</th>
            <th class="text-center align-middle" style="width:150px;" >우편번호</th>
            <th class="text-center align-middle" style="width:180px;" >도착영업소</th>
            <th class="text-center align-middle" style="width:150px;">받는분</th>
            <th class="text-center align-middle" style="width:150px;">전화번호</th>            
            <th class="text-center align-middle" style="width:150px;">기타<br>전화번호 </th>
            <th class="text-center align-middle" style="width:300px;">주소 </th>
            <th class="text-center align-middle" style="width:100px;">상세주소 </th>
            <th class="text-center align-middle" style="width:100px;"> 품목명 </th>
            <th class="text-center align-middle" style="width:100px;">수량</th>
            <th class="text-center align-middle" style="width:150px;">포장상태</th> 
            <th class="text-center align-middle" style="width:150px;">개별단가(만원)</th>                                
            <th class="text-center align-middle" style="width:150px;" > 배송구분</th>
            <th class="text-center align-middle" style="width:100px;" > 운임</th>            
            <th class="text-center align-middle" style="display:none;"> 별도운임</th>            
            <th class="text-center align-middle" style="display:none;"> 기타운임</th>            
        </tr> 
    </thead>
    <tbody>
     
<?php

 try {  
 
   $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh
   $rowNum = $stmh->rowCount();  

   while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {    
        include '_contentload.php';
            
		$deliverymethod = str_replace('경동','',$deliverymethod);
		print '<tr>';
        print ' <td class="text-center align-middle"> <input type="checkbox" class="row-checkbox" name="recordIds[]" value="' . $num . '"></td>';
        if($deliverymethod  == '대신화물')
            print ' <td class="text-center align-middle" style="white-space: nowrap;"> <span class="badge bg-primary"> ' . htmlspecialchars($deliverymethod) . ' </span> </td>';    
        else        
            print ' <td class="text-center align-middle" style="white-space: nowrap;">' .  htmlspecialchars($deliverymethod) . ' </td>';        		
		
		print ' <td class="text-center align-middle" style="white-space: nowrap;">  </td>';        		
        print ' <td class="text-start align-middle"  style="white-space: nowrap;" onclick="handleRowClick(' . $num . ')">' .  htmlspecialchars($address_arr[$counter]) . ' </td>';        
        print ' <td class="text-center align-middle" style="white-space: nowrap;"  onclick="handleRowClick(' . $num . ')">' .  htmlspecialchars($chargedman_arr[$counter]) . ' </td>';
        print ' <td class="text-start align-middle"  style="white-space: nowrap;" onclick="handleRowClick(' . $num . ')">' . htmlspecialchars(trim($chargedmantel)) . ' </td>';    
		print ' <td class="text-center align-middle" style="white-space: nowrap;">  </td>';        				
        print ' <td class="text-start align-middle"  style="white-space: nowrap;" onclick="handleRowClick(' . $num . ')">' .  htmlspecialchars($address_arr[$counter]) . ' </td>';        
        print ' <td class="text-start align-middle"  style="white-space: nowrap;" > </td>';        
        print ' <td class="text-start align-middle"  style="white-space: nowrap;" onclick="handleRowClick(' . $num . ')">' .  '모터 및 부속자재' . ' </td>';        
        print ' <td class="text-center align-middle" style="white-space: nowrap;" >' . htmlspecialchars($delwrapsu) . ' </td>';                		
        print ' <td class="text-center align-middle" style="white-space: nowrap;">' .  htmlspecialchars($delwrapmethod) . ' </td>';                    		
        print ' <td class="text-center align-middle" style="white-space: nowrap;" >' . htmlspecialchars($delwrapamount) . ' </td>';
        print ' <td class="text-center align-middle" style="white-space: nowrap;">  </td>';        		
        print ' <td class="text-center align-middle" style="white-space: nowrap;" >' . htmlspecialchars($delipay) . ' </td>';        
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
        var result = 'DH모터 화물택배(' + workplace +')' + currentDate + currentTime + '.pdf';	
		
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


function SearchEnter(){

    if(event.keyCode == 13){		
		saveSearch();
    }
}

function saveSearch() {
	
    document.getElementById('board_form').submit();
    
}

$(document).ready(function(){    
    $("#submitFormBtn").click(function(){  document.getElementById('board_form').submit();     });              // refresh
    $("#refresh").click(function(){  location.reload();   });              // refresh
    $("#labelBtn").click(function(){  
        var url = "label_tag.php";                 
        customPopup(url, '라벨인쇄', 1400, 900); 
    });              // refresh
    $('#selectAll').click(function() {
        var isChecked = $(this).prop('checked');
        $('.row-checkbox').prop('checked', isChecked);
    });
});
</script>

<script>
function generateExcel() {
    var table = document.getElementById('myTable');
    var rows = table.getElementsByTagName('tr');
    var data = [];

    // 각 행을 반복하여 데이터 수집
    for (var i = 1; i < rows.length; i++) { // 헤더 행을 건너뜀
        var cells = rows[i].getElementsByTagName('td');
        var checkbox = cells[0].querySelector('input');

        if (checkbox && checkbox.checked) { // 체크박스가 체크된 경우에만 데이터 수집
            var rowData = {};
            rowData['checkbox'] = checkbox.checked;
            rowData['delivery'] = cells[1]?.innerText || '';
            rowData['postalCode'] = cells[2]?.innerText || '';
            rowData['office'] = cells[3]?.innerText || '';
            rowData['receiver'] = cells[4]?.innerText || '';
            rowData['phone'] = cells[5]?.innerText || '';
            rowData['otherPhone'] = cells[6]?.innerText || '';
            rowData['address'] = cells[7]?.innerText || '';
            rowData['address1'] = cells[8]?.innerText || '';
            rowData['item'] = cells[9]?.innerText || '';
            rowData['quantity'] = cells[10]?.innerText || '';
            rowData['packaging'] = cells[11]?.innerText || '';
            rowData['unitPrice'] = cells[12]?.innerText || '';
            rowData['shippingType'] = cells[13]?.innerText || '';
            rowData['freight'] = cells[14]?.innerText || '';
            rowData['freight1'] = cells[15]?.innerText || '';
            rowData['freight2'] = cells[16]?.innerText || '';

            data.push(rowData);
        }
    }

    // saveExcel.php에 데이터 전송
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "delivery_saveExcel.php", true);
    xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4) {
            if (xhr.status == 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        console.log('Excel file generated successfully.');
                        // 다운로드 스크립트로 리디렉션
                        window.location.href = 'downloadExcel.php?filename=' + encodeURIComponent(response.filename.split('/').pop());
                    } else {
                        console.log('Failed to generate Excel file: ' + response.message);
                    }
                } catch (e) {
                    console.log('Error parsing response: ' + e.message + '\nResponse text: ' + xhr.responseText);
                }
            } else {
                console.log('Failed to generate Excel file: Server returned status ' + xhr.status);
            }
        }
    };
    xhr.send(JSON.stringify(data));
}

</script>
