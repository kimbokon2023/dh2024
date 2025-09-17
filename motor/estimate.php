<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

// 첫 화면 표시 문구
$title_message = 'DH모터 견적서'; 
$tablename = 'motor'; 
// 견적서, 거래명세서, 총거래원장등 설정 $item으로 설정하면 됨.
$item ='견적서';
   
include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';   
?>
<title> <?=$title_message?> </title>

<style>
        table, th, td {
            border: 1px solid black !important; /* Bold border */
            font-size: 12px !important;
			white-space: nowrap;
        }
		.calculate-row {
			background-color: #f0f0f0!important; /* Light gray background */		
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
                padding: 1px; /* Reduce padding */
                border: 1px solid #ddd; /* Ensure borders are visible */
            }
            .text-center {
                text-align: center; /* Maintain center alignment */
            }
            .fw-bold {
                font-weight: bold; /* Ensure bold text is printed */
            }
			
            /* Handle borders for rowspan */
            .table-bordered tr:nth-child(2) td:nth-child(3),
            .table-bordered tr:nth-child(3) td:nth-child(3),
            .table-bordered tr:nth-child(4) td:nth-child(3),
            .table-bordered tr:nth-child(5) td:nth-child(3) {
                border-top: none; /* Remove top border */
            }
            .table-bordered tr:nth-child(6) td:nth-child(3) {
                border-bottom: 1px solid #ddd; /* Add bottom border */
            }			
        }
    </style>
		
</head>

<body>	

<html lang="ko"> 

<?php

$num = isset($_REQUEST['num']) ? $_REQUEST['num'] : '';  

function number_to_korean($number) {
    $korean_numbers = array(
        '', '일', '이', '삼', '사', '오', '육', '칠', '팔', '구'
    );
    $korean_units = array(
        '', '십', '백', '천', '만', '십만', '백만', '천만', '억', '십억', '백억', '천억', '조', '십조', '백조', '천조'
    );
    
    $result = ' ';
    
    // 숫자를 문자열로 변환하여 한 글자씩 처리
    $number_str = strval($number);
    $length = strlen($number_str);
    for ($i = 0; $i < $length; $i++) {
        $digit = (int)$number_str[$i];
        $unit_index = $length - $i - 1;
        
        // 단위가 '만' 이상인 경우, 단위를 적용하여 처리
        if ($unit_index > 3) {
            $unit_index = $unit_index % 4 + 4 * ((int)($unit_index / 4));
        }
        
        // 숫자와 단위를 조합하여 한글로 변환
        if ($digit > 0) {
            $result .= $korean_numbers[$digit] . $korean_units[$unit_index];
        }
    }
    
    return removeAllButLastOccurrence($result,'만') ;
}

function removeAllButLastOccurrence($string, $target) {
    // 마지막 '만'의 위치를 찾습니다
    $lastPos = strrpos($string, $target);

    // 마지막 '만'이 없으면 원래 문자열을 반환합니다
    if ($lastPos === false) {
        return $string;
    }

    // 마지막 '만'을 제외한 모든 '만'을 제거합니다
    $beforeLastPos = substr($string, 0, $lastPos);
    $afterLastPos = substr($string, $lastPos);

    // '만'을 빈 문자열로 대체합니다
    $result = str_replace($target, '', $beforeLastPos) . $afterLastPos;

    return $result;
}

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();
try {
    $sql = "select * from " . $DB . ".motor where num = ? ";
    $stmh = $pdo->prepare($sql);
    $stmh->bindValue(1, $num, PDO::PARAM_STR);
    $stmh->execute();
    $count = $stmh->rowCount();
    if ($count < 1) {
        print "검색결과가 없습니다.<br>";
    } else {
        $row = $stmh->fetch(PDO::FETCH_ASSOC);
        include "_row.php";

        if ($deliverymethod == '화물' || $deliverymethod == '택배') {
            // 상차지에 화물지점표기함
            $address = $delbranch . (!empty($delbranchaddress) ? ' (' . $delbranchaddress . ')' : '');
            $loadplace = '(주)대한 본사';
        }
        if ($deliverymethod == '직배송') {
            // 상차지에 화물지점표기함
            $loadplace = '(주)대한 본사';
        }

        $totalprice = intval(str_replace(',', '', $totalprice)) * 1.1;
        $dcadd = intval(str_replace(',', '', $dcadd)); //추가할인

        $korean = number_to_korean($totalprice);
    }
} catch (PDOException $Exception) {
    print "오류: " . $Exception->getMessage();
}
?>

<div class="container mt-2">
    <div class="d-flex align-items-center justify-content-end mt-1 m-2">
        <select id="accountSelect" class="form-select me-3 w-auto" style="font-size: 0.8rem; height: 32px;">
            <option value="">계좌번호 선택</option>
        </select>
        <input type="checkbox" id="lotNumberCheckbox" checked>
        <label for="lotNumberCheckbox" class="me-3">로트번호 보이기</label>
        <button class="btn btn-dark btn-sm me-1" onclick="generatePDF();"> PDF 저장 </button>
        <button class="btn btn-dark btn-sm me-1" onclick="sendmail();"> <i class="bi bi-envelope-arrow-up"></i> 전송 </button>
        <button class="btn btn-secondary btn-sm" onclick="self.close();"> <i class="bi bi-x-lg"></i> 닫기 </button>&nbsp;
    </div>
</div>

<div id="content-to-print">	
<br>
<div class="container mt-3">
<div class="d-flex align-items-center justify-content-center mb-3 m-3">
	<h2 > <?=$item?>  </h2>
</div>
<div class="d-flex align-items-center justify-content-center m-2">
    <table class="table" style="border-collapse: collapse;">
        <tbody>
            <tr>
                <td class="text-center fw-bold" style="width:8%;" >출고예정일</td>
                <td class="text-center" style="width:30%;">  <?=$deadline?> </td>				
                <td rowspan="5" class="text-center align-middle fw-bold" style="width:2%; border-top:none; border-bottom:none;" >공 급 자</td>
             
                <td class="text-center fw-bold"  style="width:10%;" >등록번호</td>
                <td colspan="3" class="text-center" style="width:30%;"> 883-86-03143 </td>
            </tr>
            <tr>
                <td class="text-center fw-bold">업체명</td>
                <td class="text-center" > <?=$secondord?></td>
                <td class="text-center fw-bold" > 법인명 </td>
                <td class="text-center" >㈜ 대한 </td>
                <td class="text-center fw-bold" >성명</td>
                <td class="text-center fw-bold">
					<div class="d-flex align-items-center justify-content-center ">
						신 지 환 &nbsp;
						<img src="../img/daehanstamp.png" alt="도장" style="width:45px; height:45px;">
					</div>
                </td>				
			<tr>
				<td class="text-center fw-bold">담당자</td>
                <td class="text-center"><?=$secondordman?></td>
                <td class="text-center fw-bold" > 사업장주소 </td>
                <td colspan="3" class="text-center"> 경기도 김포시 통진읍 월하로 485</td>				
            </tr>
			<tr>				
                <td  class="text-center fw-bold">연락처</td>
                <td  class="text-center"><?=$secondordmantel?></td>
                <td class="text-center fw-bold" > 업 태 </td>
                <td class="text-center" > 제조업 </td>
                <td class="text-center fw-bold" >종목</td>
                <td class="text-center" > 전동기제조 </td>				
                
            </tr>
			<tr>
                <td class="text-center fw-bold">현장명</td>
                <td class="text-center fw-bold" > <?=$workplacename?></td>
                <td class="text-center fw-bold" > 전화번호 </td>
                <td class="text-center" > 010-3966-2024</td>
                <td class="text-center fw-bold" > 메일 </td>
                <td class="text-center" > dhm2024@naver.com </td>		                
            </tr>
            
        </tbody>
    </table>   
</div>
<div class="d-flex align-items-center justify-content-center m-2">	
	<table class="table" style="border-collapse: collapse;">
        <tbody>
            <tr>
                <td class="text-center fw-bold" style="width:250px;" >
				금액(VAT 포함)  <br>
				아래와 같이 계산합니다				
				</td>
                <td class="text-center  align-middle fs-6 fw-bold " style="width:50px;">  금 </td>				
                <td rowspan="5" class="text-end align-middle fw-bold fs-6" style="width:500px;" > <?=$korean?> </td>                
                <td class="text-center fw-bold  align-middle fs-6"  style="width:50px;" > 원 </td>
                <td class="align-middle text-end fs-6 fw-bold" style="width:250px;"> ( ￦ <?=number_format($totalprice)?>  )</td>
            </tr>
        </tbody>
    </table>
</div>
  <div class="d-flex align-items-center justify-content-center mb-1 m-2">	

    <table class="table" style="border-collapse: collapse;">
        <thead>
            <tr id="lotNumberHeaderRow">
                <th colspan="6" class="text-center">품명</th>
				<th rowspan="2" class="text-center align-middle ">단위</th>
                <th rowspan="2" class="text-center align-middle ">수량</th>
                <th rowspan="2" class="text-center align-middle ">단가</th>
                <th rowspan="2" class="text-center align-middle ">공급가액</th>
                <th rowspan="2" class="text-center align-middle ">세액</th>
                <th rowspan="2" class="text-center align-middle lotview">로트번호</th>
                <th rowspan="2" class="text-center align-middle ">비고</th>
            </tr>
            <tr>
                <th class="text-center">전원</th>
                <th class="text-center">유무선</th>                
                <th class="text-center">구분</th>
                <th class="text-center">종류</th>
                <th class="text-center">브라켓트</th>                
                <th class="text-center">후렌지</th>                
            </tr>
        </thead>
        <tbody id="lotNumberTableBody">
		
		<?php

		$motor = json_decode($orderlist, true);		

		$motorsu = 0;
		$motorsuSum = 0;
		$unitamount = 0;	
		$vat = 0;
		$totalvat = 0;  // 단가표상 합계 내고, 추가할인 부분 처리해야 함.
		$totalunitamount = 0;  // 단가표상 합계 내고, 추가할인 부분 처리해야 함.

		foreach ($motor as $cols) {
			$motorsu = intval(str_replace(',', '', $cols['col8'])) ;			
			$unitprice= intval(str_replace(',', '', $cols['col9'])) ;			
			$realamount= intval(str_replace(',', '', $cols['col12'])) ;			
			$unitprice= $realamount / $motorsu;
			$unitamount = $motorsu * $unitprice ;
			$vat = $unitamount/ 10 ;
			 

			if($cols['col5']=='SET' || $cols['col5']=='모터단품' || $cols['col5']=='브라켓트' )
			{
				print '<tr>';
				print ' <td  class="text-center"> ' . $cols['col1'] . ' </td>';			
				print ' <td  class="text-center"> ' . $cols['col2'] . ' </td>';			
				print ' <td  class="text-center"> ' . $cols['col3'] . 'M </td>';			
				print ' <td  class="text-center"> ' . $cols['col4'] . ' </td>';			
				print ' <td  class="text-center"> ' . $cols['col6'] . ' </td>';							
				print ' <td  class="text-center"> ' . $cols['col7'] . ' </td>';			
				print ' <td  class="text-center"> ' . $cols['col5'] . ' </td>';	// 단위
				print ' <td  class="text-center"> ' . number_format($motorsu) . ' </td>';	// 수량		
				print ' <td class="text-end"> ' . ($unitprice != 0 ? number_format($unitprice) : '') . ' </td>';			
				print ' <td class="text-end"> ' . ($unitamount != 0 ? number_format($unitamount) : '') . ' </td>';		
				print ' <td class="text-end"> ' . ($vat != 0 ? number_format($vat) : '') . ' </td>';		
				print ' <td  class="text-center lotview" data-original-content="' . $cols['col13'] . '"> ' . $cols['col13'] . ' </td>';						
				print ' <td  class="text-center"> ' . $cols['col15'] . '</td>';										
				print '</tr>';					
				$motorsuSum += $motorsu;
				$totalvat += $vat;
				$totalunitamount += $unitamount;
			}
		}
				
		// Decode the JSON string into an associative array
		$controller = json_decode($controllerlist, true);

		// Check if $controller is an array
		if (is_array($controller)) {
			$firstitem = true;
			$controllersu = 0;
			$controllersuSum = 0;				

			foreach ($controller as $cols) {
				
				$controllersu = intval(str_replace(',', '', $cols['col3'])) ;			
				$unitprice= intval(str_replace(',', '', $cols['col4'])) ;
				$realamount= intval(str_replace(',', '', $cols['col7'])) ;			
				$unitprice= $realamount / $controllersu;				
				$unitamount = $controllersu * $unitprice ;
				$vat = $unitamount/ 10 ;				
				
				print '<tr>';
				print ' <td colspan="6" class="text-center"> ' .  $cols['col2'] .  ' </td>';							
				print ' <td  class="text-center"> ' . 'EA' . ' </td>';						
				print ' <td  class="text-center"> ' . number_format($controllersu) . ' </td>';	// 수량		
				print ' <td class="text-end"> ' . ($unitprice != 0 ? number_format($unitprice) : '') . ' </td>';			
				print ' <td class="text-end"> ' . ($unitamount != 0 ? number_format($unitamount) : '') . ' </td>';						
				print ' <td class="text-end"> ' . ($vat != 0 ? number_format($vat) : '') . ' </td>';						
				print ' <td  class="text-center lotview" data-original-content="' . $cols['col8'] . '"> ' . $cols['col8'] . ' </td>';						
				print ' <td  class="text-center"> ' . $cols['col9'] . ' </td>';										
				print '</tr>';  
				$controllersuSum += $controllersu;  
				$totalvat += $vat;
				$totalunitamount += $unitamount;				
			}
		}					
		
		
		$fabric = json_decode($fabriclist, true);

		// Check if $fabric is an array
		if (is_array($fabric)) {
			$firstitem = true;
			$fabricsu = 0;
			$fabricsuSum = 0;				

			foreach ($fabric as $cols) {				
				
				$fabricsu = intval(str_replace(',', '', $cols['col5'])) ;			
				$unitprice= intval(str_replace(',', '', $cols['col6'])) ;
				$realamount= intval(str_replace(',', '', $cols['col9'])) ;			
				$unitprice= $realamount / $fabricsu;				
				$unitamount = $fabricsu * $unitprice ;
				$vat = $unitamount/ 10 ;				
				
				print '<tr>';
				print ' <td colspan="6" class="text-center"> ' .  $cols['col1'] .  ' </td>';							
				print ' <td  class="text-center"> ' . 'EA' . ' </td>';						
				print ' <td  class="text-center"> ' . number_format($fabricsu) . ' </td>';	// 수량		
				print ' <td class="text-end"> ' . ($unitprice != 0 ? number_format($unitprice) : '') . ' </td>';			
				print ' <td class="text-end"> ' . ($unitamount != 0 ? number_format($unitamount) : '') . ' </td>';						
				print ' <td class="text-end"> ' . ($vat != 0 ? number_format($vat) : '') . ' </td>';						
				print ' <td  class="text-center lotview" data-original-content="' . $cols['col10'] . '"> ' . $cols['col10'] . ' </td>';						
				print ' <td  class="text-center"> ' . $cols['col11'] . ' </td>';										
				print '</tr>';  
				$fabricsuSum += $fabricsu;  
				$totalvat += $vat;
				$totalunitamount += $unitamount;				
			}
		}				
		
		// Decode the JSON string into an associative array
		$accessories = json_decode($accessorieslist, true);

		// Check if $accessories is an array
		if (is_array($accessories)) {
			$firstitem = true;
			$etcsu = 0;
			$etcsuSum = 0;				

			foreach ($accessories as $cols) {				
				
				$etcsu = intval(str_replace(',', '', $cols['col2'])) ;			
				$unitprice= intval(str_replace(',', '', $cols['col3'])) ;
				$realamount= intval(str_replace(',', '', $cols['col4'])) ;			
				$unitprice= $realamount / $etcsu;				
				$unitamount = $etcsu * $unitprice ;
				$vat = $unitamount/ 10 ;
				
				print '<tr>';
				print ' <td colspan="6" class="text-center"> ' . $cols['col1'] .  ' </td>';							
				print ' <td  class="text-center"> ' . 'EA' . ' </td>';						
				print ' <td  class="text-center"> ' . number_format($etcsu) . ' </td>';	// 수량		
				print ' <td class="text-end"> ' . ($unitprice != 0 ? number_format($unitprice) : '') . ' </td>';			
				print ' <td class="text-end"> ' . ($unitamount != 0 ? number_format($unitamount) : '') . ' </td>';						
				print ' <td class="text-end"> ' . ($vat != 0 ? number_format($vat) : '') . ' </td>';						
				print ' <td  class="text-center lotview" data-original-content="' . $cols['col5'] . '"> ' . $cols['col5'] . ' </td>';						
				print ' <td  class="text-center"> ' . $cols['col5'] . '</td>';																			
				print '</tr>';  
				$etcsuSum += $etcsu;  
				$totalvat += $vat;
				$totalunitamount += $unitamount;				
			}
		}	
		
			if($motorsuSum > 0 || $controllersuSum > 0  || $fabricsuSum > 0  || $etcsuSum > 0 ) {
				print '<tr>';
				print ' <td  colspan="7" class="text-center calculate-row  fw-bold " colspan="4"> 항목별 합계 </td>';	
				print ' <td  class="text-center calculate-row  fw-bold "  > ' . ( $motorsuSum  + $controllersuSum + $fabricsuSum + $etcsuSum ). ' </td>';	
				print ' <td  class="text-center calculate-row  fw-bold "  >  </td>';					
				print ' <td  class="text-end calculate-row  fw-bold " > ' . ($totalunitamount != 0 ? number_format($totalunitamount) : '') . ' </td>';	
				print ' <td  class="text-end calculate-row  fw-bold " > ' . ($totalvat != 0 ? number_format($totalvat) : '') . ' </td>';	
				print ' <td  class="text-center calculate-row "  >  </td>';					
				print ' <td  class="text-center lotview calculate-row "  >  </td>';					
				print '</tr>';							
				if($dcadd > 0) {
					print '<tr>';
					print ' <td  colspan="7" class="text-center calculate-row  fw-bold " colspan="4"> 추가할인 </td>';	
					print ' <td  class="text-center calculate-row "  >  </td>';	
					print ' <td  class="text-center calculate-row "  >  </td>';									
					print ' <td  class="text-end text-primary calculate-row  fw-bold " > ' . ($dcadd != 0 ? number_format($dcadd*-1) : '') . ' </td>';	
					print ' <td  class="text-end text-primary calculate-row  fw-bold " > ' . ($dcadd != 0 ? number_format($dcadd/10*-1) : '') . ' </td>';					
					print ' <td  class="text-center calculate-row "  >  </td>';	
					print ' <td  class="text-center lotview calculate-row "  >  </td>';	
					print '</tr>';	
				 }					
					print '<tr>';
					print ' <td  colspan="7" class="text-center fw-bold calculate-row " > 최종 합계 </td>';	
					print ' <td  class="text-end calculate-row fw-bold"  colspan="4" > ' . ($totalprice != 0 ? number_format($totalprice) : '') . ' </td>';	
					print ' <td  class="text-center calculate-row "  >  </td>';	
					print ' <td  class="text-center lotview calculate-row "  >  </td>';	
					print '</tr>';					
			}		
		
		print '<tr>';
		print ' <td  colspan="1" class="text-center  align-middle fw-bold" style="height:80px;" > 비고 </td>';	
		print ' <td  colspan="12" class="text-start  align-middle fw-bold"  > 계좌번호 : <span id="accountDisplay">국민은행 253401-04-381605 주식회사 대한</span> <br><br> 위와 같이 견적합니다.  </td>';
		print '</tr>';	
		?>		
        </tbody>
    </table>
</div>

</div> <!-- end of container -->

</div>
</div>
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

ajaxRequest = null;
function generatePDF() {
	var workplace = '<?php echo $workplacename; ?>';
	var item = '<?php echo $item; ?>';	
	var deadline = '<?php echo $deadline; ?>';
	var deadlineDate = new Date(deadline);
	var formattedDate = "(" + String(deadlineDate.getFullYear()).slice(-2) + "." + ("0" + (deadlineDate.getMonth() + 1)).slice(-2) + "." + ("0" + deadlineDate.getDate()).slice(-2) + ")";
	var result = 'DH ' + item +'(' + workplace +')' + formattedDate + '.pdf';	
	
	var element = document.getElementById('content-to-print');
	var opt = {
		margin:       0,
		filename:     result,
		image:        { type: 'jpeg', quality: 0.98 },
		html2canvas:  { scale: 2 },
		jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
	};
	html2pdf().from(element).set(opt).save();
	
	return result;
}

function generatePDF_server(callback) {
	var workplace = '<?php echo $workplacename; ?>';
	var item = '<?php echo $item; ?>';
	var deadline = '<?php echo $deadline; ?>';
	var deadlineDate = new Date(deadline);
	var formattedDate = "(" + String(deadlineDate.getFullYear()).slice(-2) + "." + ("0" + (deadlineDate.getMonth() + 1)).slice(-2) + "." + ("0" + deadlineDate.getDate()).slice(-2) + ")";
	var result = 'DH ' + item +'(' + workplace + ')' + formattedDate + '.pdf';

    var element = document.getElementById('content-to-print');
    var opt = {
        margin: 0,
        filename: result,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
    };

    html2pdf().from(element).set(opt).output('datauristring').then(function (pdfDataUri) {
        var pdfBase64 = pdfDataUri.split(',')[1]; // Base64 인코딩된 PDF 데이터 추출
        var formData = new FormData();
        formData.append('pdf', pdfBase64);
        formData.append('filename', result);

        $.ajax({
            type: 'POST',
            url: 'save_pdf.php', // PDF 파일을 저장하는 PHP 파일
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                var res = JSON.parse(response);
                if (callback) {
                    callback(res.filename); // 서버에 저장된 파일 경로를 콜백으로 전달
                }
            },
            error: function (xhr, status, error) {
                Swal.fire('Error', 'PDF 저장에 실패했습니다.', 'error');
            }
        });
    });
}

function sendmail() {
    var secondordnum = '<?php echo $secondordnum; ?>'; // 서버에서 가져온 값
    var item = '<?php echo $item; ?>'; 
    console.log('secondordnum', secondordnum);
    
    if (typeof ajaxRequest !== 'undefined' && ajaxRequest !== null) {
        ajaxRequest.abort();
    }
    
    ajaxRequest = $.ajax({
        type: 'POST',
        url: 'get_companyCode.php', // 파일 이름 수정
        data: { secondordnum: secondordnum },
        dataType: 'json',
        success: function(response) {
			console.log('response : ', response);
            if (response.error) {
                Swal.fire('Error', response.error, 'error');
            } else {
                var email = response.email;
                var vendorName = response.vendor_name;

                Swal.fire({
                    title: '이메일 보내기',
                    text: '거래처(' + vendorName + ') Email : (' + email + ') 이메일 전송 하시겠습니까?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '보내기',
                    cancelButtonText: '취소',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        generatePDF_server(function(filename) {
                            sendEmail(email, vendorName, item, filename);
                        });
                    }
                });
            }
        },
        error: function(xhr, status, error) {
            Swal.fire('Error', '전송중 오류가 발생했습니다.', 'error');
        }
    });
}

function sendEmail(recipientEmail,vendorName, item, filename) {
    // 이메일 전송 코드 작성 (예: PHP를 호출하여 이메일 전송)
    if (typeof ajaxRequest !== 'undefined' && ajaxRequest !== null) {
        ajaxRequest.abort();
    }
	var deadline = '<?php echo $deadline; ?>';
	var deadlineDate = new Date(deadline);
	var formattedDate = "(" + String(deadlineDate.getFullYear()).slice(-2) + "." + ("0" + (deadlineDate.getMonth() + 1)).slice(-2) + "." + ("0" + deadlineDate.getDate()).slice(-2) + ")";
	
    ajaxRequest = $.ajax({
        type: 'POST',
        url: 'send_email.php', // 이메일 전송을 처리하는 PHP 파일
        data: { email: recipientEmail, vendorName : vendorName, filename: filename, item : item, formattedDate :formattedDate },
        success: function(response) {
			console.log(response);
            Swal.fire('Success', '정상적으로 전송되었습니다.', 'success'); 
        },
        error: function(xhr, status, error) {
            Swal.fire('Error', '전송에 실패했습니다. 확인바랍니다.', 'error'); 
        }
    });
}

document.getElementById('lotNumberCheckbox').addEventListener('change', function() {
    var lotviewThs = document.querySelectorAll('.lotview');
    var lotviewTds = document.querySelectorAll('td.lotview');

    if (this.checked) {
        // lotview 클래스의 요소를 다시 표시
        lotviewThs.forEach(function(th) {
            th.style.display = '';
        });
        lotviewTds.forEach(function(td) {
            td.style.display = '';
            td.textContent = td.dataset.originalContent;
        });
    } else {
        // lotview 클래스의 요소를 숨김
        lotviewThs.forEach(function(th) {
            th.style.display = 'none';
        });
        lotviewTds.forEach(function(td) {
            td.style.display = 'none';
        });
    }
});

// 계좌번호 로드 및 선택 기능 통합 스크립트
document.addEventListener('DOMContentLoaded', function() {
    // 계좌 셀렉트 박스와 표시 영역
    var select = document.getElementById('accountSelect');
    var accountDisplay = document.getElementById('accountDisplay');

    // 계좌 목록 불러오기 및 셀렉트 옵션 생성
    fetch('/account/accountlist.json')
        .then(response => response.json())
        .then(function(accounts) {
            select.innerHTML = '<option value="">계좌번호 선택</option>';
            let defaultIndex = 0;
            accounts.forEach(function(account, idx) {
                var accountText = account.company + ' ' + account.number + ' 주식회사 대한';
                var option = document.createElement('option');
                option.value = accountText;
                option.textContent = accountText;
                select.appendChild(option);
                if(account.company.indexOf('우리은행') !== -1) {
                    defaultIndex = idx + 1; // "계좌번호 선택" 옵션이 0번
                }
            });
            // 기본값을 우리은행으로 설정
            select.selectedIndex = defaultIndex;
            // 표시 영역에도 기본값 반영
            if (accountDisplay) {
                accountDisplay.textContent = select.value || '우리은행 1005-204-801516 주식회사 대한';
            }
        });

    // 계좌 선택 시 계좌번호 표시 업데이트
    select.addEventListener('change', function() {
        if (accountDisplay) {
            if (this.value) {
                accountDisplay.textContent = this.value;
            } else {
                accountDisplay.textContent = '우리은행 1005-204-801516 주식회사 대한'; // 기본값
            }
        }
    });
});

</script>