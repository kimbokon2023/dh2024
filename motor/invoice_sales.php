<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

// 첫 화면 표시 문구
$title_message = 'DH모터 거래명세표'; 
$tablename = 'motor'; 
// 견적서, 거래명세서, 총거래원장등 설정 $item으로 설정하면 됨.
$item ='거래명세표';
   
include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';   
?>
<title> <?=$title_message?> </title>

<style>
	table, th, td {
		border: 1px solid black !important; /* Bold border */
		font-size: 10px !important;
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
			font-size: 9pt; /* Reduce font size for printing */
		}
		.table {
			width: 100%; /* Full width tables */
			/* table-layout: fixed; Uniform column sizing */
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
// 정가만 표시 여부: 기본은 체크된 상태(true)
$regularPrice = (!isset($_GET['regularPrice']) || $_GET['regularPrice'] == '1');
$dcPrice = $_GET['dcPrice'] ?? '';

function number_to_korean($number) {
    $korean_numbers = array(
        '', '일', '이', '삼', '사', '오', '육', '칠', '팔', '구'
    );
    $korean_units = array(
        '', '십', '백', '천', '만', '십만', '백만', '천만', '억', '십억', '백억', '천억', '조', '십조', '백조', '천조'
    );

    // 소수점 반올림 후 정수로 변환
    $number = round($number); 

    $result = '';

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

        if ($digit > 0) {
            $result .= $korean_numbers[$digit] . $korean_units[$unit_index];
        }
    }

    return removeAllButLastOccurrence($result, '만');
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
		// 정가만 표시 모드일 경우, $realamount를 col10에서 가져오고, unitprice를 재산출합니다.
		if ($regularPrice && $dcPrice) {
			$totalprice = (intval(str_replace(',', '', $totalprice)) + intval(str_replace(',', '', $dcadd)) + intval(str_replace(',', '', $dctotal))* -1 ) * 1.1;
			$dcadd = 0;
			// print '정가만 체크됨';
			// print 'totalprice' . $totalprice;
			// print 'dcadd' . $dcadd;
			// print 'dctotal' . $dctotal;
		} else if ($regularPrice) {
			$totalprice = (intval(str_replace(',', '', $totalprice)) + intval(str_replace(',', '', $dcadd)) + intval(str_replace(',', '', $dctotal))* -1 ) * 1.1;
			$dcadd = 0;
			// echo '정가만 표시 ' . $regularPrice . 'totalprice : ' . $totalprice . 'DC 토탈 ' . $dctotal ;
			}   
		else if ($dcPrice) {
			$totalprice = intval(str_replace(',', '', $totalprice)) * 1.1;
			$dcadd = intval(str_replace(',', '', $dcadd)); //추가할인
		}   
		else {
			$totalprice = intval(str_replace(',', '', $totalprice)) * 1.1;
			$dcadd = intval(str_replace(',', '', $dcadd)); //추가할인
		}   

		// Check if 할인합계 표시
		if ($dcPrice) {	
				$dctotal= intval(str_replace(',', '', $dctotal)) ;										
				$totalprice += $dctotal * 1.1 ;				
		}	
		
        $korean = number_to_korean($totalprice);
    }
} catch (PDOException $Exception) {
    print "오류: " . $Exception->getMessage();
}
?>

<div class="container mt-2">
	<div class="d-flex align-items-center justify-content-end mt-1 m-2">
		<!-- 기존 로트번호 보이기 체크박스 -->
		<input type="checkbox" id="lotNumberCheckbox" checked>
		<label for="lotNumberCheckbox" class="me-3">로트번호 보이기</label>
		
		<!-- 정가만 표시 체크박스 -->
		<input type="checkbox" id="regularPriceCheckbox" <?php if($regularPrice) echo 'checked'; ?>>
		<label for="regularPriceCheckbox" class="me-3">정가만 표시</label>		
		
		<!-- 할인율 표시 체크박스 -->
		<input type="checkbox" id="dcPriceCheckbox" <?php if($dcPrice) echo 'checked'; ?>>
		<label for="dcPriceCheckbox" class="me-3">할인합계 표시</label>
		
		<button class="btn btn-dark btn-sm me-1" onclick="generatePDF()"> PDF 저장 </button>
		<button class="btn btn-dark btn-sm me-1" onclick="sendmail(<?=$num?>);"> <i class="bi bi-envelope-arrow-up"></i> 전송 </button>
		<button class="btn btn-secondary btn-sm" onclick="self.close();"> <i class="bi bi-x-lg"></i> 닫기 </button>&nbsp;
	</div>

</div>

<div id="content-to-print">	
<br>
<div class="container mt-3">
<div class="d-flex align-items-center justify-content-center mb-3 m-3">
	<h2 > 거래명세표 </h2>
</div>
<div class="d-flex align-items-center justify-content-center m-2">
    <table class="table" style="border-collapse: collapse;">
        <tbody>
            <tr>
                <td class="text-center fw-bold" style="width:8%;" >출고일</td>
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
                <td class="text-center  align-middle fs-6 fw-bold" style="width:50px;">  금 </td>				
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
			$motorsu = intval(str_replace(',', '', $cols['col8']));

			// 정가만 표시 모드일 경우, $realamount를 col10에서 가져오고, unitprice를 재산출합니다.
			if ($regularPrice) {
				$realamount = intval(str_replace(',', '', $cols['col10']));
			} else {
				$realamount = intval(str_replace(',', '', $cols['col12']));
			}

			$unitprice = (!empty($motorsu) != 0) ? $realamount / $motorsu : 0;
			$unitamount = $motorsu * $unitprice;
			$vat = $unitamount / 10;
			 

			if($cols['col5']=='SET' || $cols['col5']=='모터단품'  )
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
			
			if( $cols['col5']=='브라켓트' )
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
				print ' <td  class="text-center lotview" data-original-content="' . $cols['col14'] . '"> ' . $cols['col14'] . ' </td>';						
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
						
				$controllersu = floatval(str_replace(',', '', $cols['col3']));
				// 만약 정가만 표시 모드라면, 예를 들어 col5를 정가용으로 사용한다고 가정 (원하시는 컬럼으로 변경)
				if ($regularPrice) {
					$realamount = floatval(str_replace(',', '', $cols['col5']));
				} else {
					$realamount = floatval(str_replace(',', '', $cols['col7']));
				}
				$unitprice = ($controllersu != 0) ? $realamount / $controllersu : 0;
				$unitamount = $controllersu * $unitprice;
				$vat = $unitamount / 10;			
				
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

				$fabricsu = intval(str_replace(',', '', $cols['col4'])) ;	
				// 만약 정가만 표시 모드라면, 예를 들어 col5를 정가용으로 사용한다고 가정 (원하시는 컬럼으로 변경)
				if ($regularPrice) {
					$realamount = intval(str_replace(',', '', $cols['col7']));
				} else {
					$realamount = intval(str_replace(',', '', $cols['col9']));
				}
				$unitprice = ($fabricsu != 0) ? $realamount / $fabricsu : 0;
				$displayunit = intval(str_replace(',', '', $cols['col6']));
				$unitamount = $fabricsu * $unitprice;
				$vat = $unitamount / 10;
				
				
				print '<tr>';
				print ' <td colspan="6" class="text-center"> ' .  $cols['col1'] .  ' </td>';							
				print ' <td  class="text-center"> ' . $cols['col3'] .  'M </td>';						
				print ' <td  class="text-center"> ' . number_format($fabricsu) . ' </td>';	// 수량		
				print ' <td class="text-end"> ' . ($unitprice != 0 ? number_format($displayunit) : '') . ' </td>';			
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
		
		// Check if 할인합계 표시
		if ($dcPrice) {	
				$dctotal= intval(str_replace(',', '', $dctotal)) ;			
				$unitprice= $dctotal ;				
				$unitamount = $unitprice ;
				$vat = $unitamount/ 10 ;
				
				print '<tr>';
				print ' <td colspan="6" class="text-center"> 할인금액 </td>';							
				print ' <td  class="text-center"> - </td>';						
				print ' <td  class="text-center"> - </td>';	
				print ' <td class="text-end"> ' . ($unitprice != 0 ? number_format($unitprice) : '') . ' </td>';			
				print ' <td class="text-end"> ' . ($unitamount != 0 ? number_format($unitamount) : '') . ' </td>';						
				print ' <td class="text-end"> ' . ($vat != 0 ? number_format($vat) : '') . ' </td>';						
				print ' <td  class="text-center lotview" data-original-content="">  </td>';						
				print ' <td  class="text-center"> </td>';																			
				print '</tr>';  				
				$totalvat += $vat;
				$totalunitamount += $unitamount;				
		}	
		
		if($motorsuSum != 0 || $controllersuSum != 0  || $fabricsuSum != 0  || $etcsuSum != 0 ) {
			print '<tr>';
			print ' <td  colspan="7" class="text-center calculate-row fw-bold" colspan="4"> 항목별 합계 </td>';	
			print ' <td  class="text-center calculate-row fw-bold"  > ' . ( $motorsuSum  + $controllersuSum + $fabricsuSum + $etcsuSum ). ' </td>';	
			print ' <td  class="text-center calculate-row "  >  </td>';					
			print ' <td  class="text-end calculate-row fw-bold" > ' . ($totalunitamount != 0 ? number_format($totalunitamount) : '') . ' </td>';	
			print ' <td  class="text-end calculate-row fw-bold" > ' . ($totalvat != 0 ? number_format($totalvat) : '') . ' </td>';	
			print ' <td  class="text-center calculate-row "  >  </td>';					
			print ' <td  class="text-center lotview calculate-row "  >  </td>';					
			print '</tr>';							
			if($dcadd != 0) {
				print '<tr>';
				print ' <td  colspan="7" class="text-center calculate-row fw-bold" colspan="4"> 추가할인 </td>';	
				print ' <td  class="text-center calculate-row "  >  </td>';	
				print ' <td  class="text-center calculate-row "  >  </td>';									
				print ' <td  class="text-end text-primary calculate-row fw-bold" > ' . ($dcadd != 0 ? number_format($dcadd*-1) : '') . ' </td>';	
				print ' <td  class="text-end text-primary calculate-row fw-bold " > ' . ($dcadd != 0 ? number_format($dcadd/10*-1) : '') . ' </td>';					
				print ' <td  class="text-center calculate-row "  >  </td>';	
				print ' <td  class="text-center lotview calculate-row "  >  </td>';	
				print '</tr>';	
			 }					
				print '<tr>';
				print ' <td  colspan="7" class="text-center calculate-row fw-bold" > 최종 합계 </td>';	
				print ' <td  class="text-end calculate-row fw-bold "  colspan="4" > ' . ($totalprice != 0 ? number_format($totalprice) : '') . ' </td>';	
				print ' <td  class="text-center calculate-row "  >  </td>';	
				print ' <td  class="text-center lotview calculate-row "  >  </td>';	
				print '</tr>';					
		}				
		print '<tr>';
		print ' <td  colspan="1" class="text-center  align-middle fw-bold" style="height:100px;" > 전달사항 </td>';	
		print ' <td  colspan="12" class="text-start  align-middle fw-bold"  > ' . $comment . ' </td>';
		print '</tr>';	
		?>		
        </tbody>
    </table>
</div>

</div> <!-- end of container -->

</div>

<!-- 페이지로딩 -->
<script>
// 페이지 로딩
$(document).ready(function(){	
    var loader = document.getElementById('loadingOverlay');
    loader.style.display = 'none';
});
</script>
<script>
function generatePDF() {
	var workplace = '<?php echo $workplacename; ?>';
	var deadline = '<?php echo $deadline; ?>';
	var deadlineDate = new Date(deadline);
	var formattedDate = "(" + String(deadlineDate.getFullYear()).slice(-2) + "." + ("0" + (deadlineDate.getMonth() + 1)).slice(-2) + "." + ("0" + deadlineDate.getDate()).slice(-2) + ")";
	var result = 'DH모터 거래명세서(' + workplace +')' + formattedDate + '.pdf';

	var element = document.getElementById('content-to-print');
	var opt = {
		margin:       0,
		filename:     result,
		image:        { type: 'jpeg', quality: 0.98 },
		html2canvas:  { scale: 2 },
		jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
	};
	html2pdf().from(element).set(opt).save();
}

function generatePDF_server(callback) {
	var workplace = '<?php echo $workplacename; ?>';
	var item = '<?php echo $item; ?>';
	var deadline = '<?php echo $deadline; ?>';
	var deadlineDate = new Date(deadline);
	var formattedDate = "(" + String(deadlineDate.getFullYear()).slice(-2) + "." + ("0" + (deadlineDate.getMonth() + 1)).slice(-2) + "." + ("0" + deadlineDate.getDate()).slice(-2) + ")";
	var result = cleanFilenameForJS('DH ' + item + '(' + workplace + ')' + formattedDate + '.pdf');
	
	// Function to clean filename in JavaScript
	function cleanFilenameForJS(filename) {
		// Remove or replace problematic characters
		filename = filename.replace(/[\/\\:*?"<>|]/g, '_');
		filename = filename.replace(/[^\w\s\-_\.]/g, '_'); // Only allow letters, numbers, spaces, hyphens, underscores, dots
		filename = filename.replace(/\s+/g, '_'); // Replace multiple spaces with single underscore
		filename = filename.replace(/_+/g, '_'); // Replace multiple underscores with single underscore
		filename = filename.replace(/^[._]+|[._]+$/g, ''); // Remove leading/trailing dots and underscores
		filename = filename.substring(0, 200); // Limit length to 200 characters
		
		// Ensure filename is not empty
		if (!filename) {
			filename = 'document_' + new Date().toISOString().slice(0, 19).replace(/:/g, '-') + '.pdf';
		}
		
		// Ensure it ends with .pdf
		if (!filename.toLowerCase().endsWith('.pdf')) {
			filename += '.pdf';
		}
		
		return filename;
	}

	console.log('[generatePDF_server] filename:', result);
	
	var element = document.getElementById('content-to-print');
	if (!element) {
		console.error('[generatePDF_server] content-to-print 요소를 찾을 수 없습니다.');
		Swal.fire('Error', 'PDF로 변환할 요소를 찾을 수 없습니다.', 'error');
		return;
	}

	// Show loading message and store the instance
	var loadingDialog = Swal.fire({
        title: '메일 전송중',
        text: '메일을 전송중입니다...',
        icon: 'info',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });    


	// Try server-side generation first (much smaller data transfer)
	generatePDFOnServer(element, result, callback);
}

// Alternative server-side PDF generation
function generatePDFOnServer(element, filename, callback) {
	console.log('[generatePDFOnServer] 서버에서 PDF 생성 시도');
	
	// Get HTML content
	var htmlContent = element.outerHTML;
	
	// Send HTML to server for PDF generation
	$.ajax({
		type: 'POST',
		url: 'generate_pdf_tcpdf.php',
		data: {
			html_content: htmlContent,
			filename: filename
		},
		success: function (response) {
			console.log('[generatePDFOnServer] 응답:', response);
			
			try {
				var res = JSON.parse(response);
				
				if (res.filename && res.pdf_created !== false) {
				console.log('[generatePDFOnServer] 성공 - PDF 생성됨');
				if (callback) {
					callback(res.filename);
				}
			} else if (res.filename && res.pdf_created === false) {
				console.log('[generatePDFOnServer] 서버 PDF 생성 실패, 클라이언트 사이드로 대체');
				generatePDFClientSide(element, filename, callback);
			} else {
				console.error('[generatePDFOnServer] 오류:', res.error);
				// Fallback to client-side generation
				console.log('[generatePDFOnServer] 클라이언트 사이드 생성으로 대체');
				generatePDFClientSide(element, filename, callback);
			}
			} catch (e) {
				console.error('[generatePDFOnServer] JSON 파싱 오류:', e);
				// Fallback to client-side generation
				console.log('[generatePDFOnServer] 클라이언트 사이드 생성으로 대체');
				generatePDFClientSide(element, filename, callback);
			}
		},
		error: function (xhr, status, error) {
			console.error('[generatePDFOnServer] 오류:', xhr.responseText);
			// Fallback to client-side generation
			console.log('[generatePDFOnServer] 클라이언트 사이드 생성으로 대체');
			generatePDFClientSide(element, filename, callback);
		}
	});
}

function generatePDFClientSide(element, filename, callback) {
	console.log('[generatePDFClientSide] 클라이언트에서 PDF 생성');
	
	var opt = {
		margin: [0.5, 0.5, 0.5, 0.5], // Add some margin for better layout
		filename: filename,
		image: { type: 'jpeg', quality: 0.98 }, // Very high quality
		html2canvas: { 
			scale: 1.5, // Higher scale for crisp text
			useCORS: true,
			allowTaint: true,
			backgroundColor: '#ffffff'
		},
		jsPDF: { 
			unit: 'in', 
			format: 'letter', 
			orientation: 'portrait',
			compress: false // Disable compression for better quality
		}
	};

	console.log('[generatePDFClientSide] html2pdf 옵션:', opt);

	html2pdf().from(element).set(opt).output('datauristring').then(function (pdfDataUri) {
		console.log('[generatePDFClientSide] PDF 생성 완료');

		var pdfBase64 = pdfDataUri.split(',')[1];
		
		// Try regular upload first
		uploadPDF(pdfBase64, filename, callback);
	});
}

function uploadPDF(pdfBase64, filename, callback) {
	var formData = new FormData();
	formData.append('pdf', pdfBase64);
	formData.append('filename', filename);

	console.log('[uploadPDF] PDF 데이터 전송 시작', {
		filename: filename,
		pdfDataLength: pdfBase64.length
	});

	$.ajax({
		type: 'POST',
		url: 'save_pdf.php',
		data: formData,
		processData: false,
		contentType: false,
		success: function (response) {
			console.log('[save_pdf.php 응답]', response);

			try {
				var res = JSON.parse(response);
				console.log('[save_pdf.php 파싱된 결과]', res);

				if (callback) {
					callback(res.filename);
				}
			} catch (e) {
				console.error('[JSON 파싱 오류] save_pdf.php 응답이 JSON이 아님:', response);
				Swal.fire('Error', 'PDF 저장 응답이 잘못되었습니다.', 'error');
			}
		},
		error: function (xhr, status, error) {
			console.error('[save_pdf.php 오류]', xhr.responseText);
			
			// If it's a 413 error (Request Entity Too Large), try chunked upload
			if (xhr.status === 413) {
				console.log('[uploadPDF] 413 오류 감지, 청크 업로드로 전환');
				uploadPDFChunked(pdfBase64, filename, callback);
			} else {
				Swal.fire('Error', 'PDF 저장에 실패했습니다.', 'error');
			}
		}
	});
}

function uploadPDFChunked(pdfBase64, filename, callback) {
	var chunkSize = 10 * 1024; // 10KB chunks (very small to avoid ModSecurity)
	var totalChunks = Math.ceil(pdfBase64.length / chunkSize);
	var currentChunk = 0;
	
	console.log('[uploadPDFChunked] 청크 업로드 시작', {
		filename: filename,
		totalChunks: totalChunks,
		chunkSize: chunkSize
	});
	
	function uploadNextChunk() {
		if (currentChunk >= totalChunks) {
			console.log('[uploadPDFChunked] 모든 청크 업로드 완료');
			return;
		}
		
		var start = currentChunk * chunkSize;
		var end = Math.min(start + chunkSize, pdfBase64.length);
		var chunk = pdfBase64.substring(start, end);
		
		var formData = new FormData();
		formData.append('chunk', chunk);
		formData.append('filename', filename);
		formData.append('chunkIndex', currentChunk);
		formData.append('totalChunks', totalChunks);
		
		console.log('[uploadPDFChunked] 청크 업로드 중', {
			chunkIndex: currentChunk,
			chunkSize: chunk.length
		});
		
		$.ajax({
			type: 'POST',
			url: 'save_pdf.php',
			data: formData,
			processData: false,
			contentType: false,
			success: function (response) {
				console.log('[uploadPDFChunked] 청크 응답:', response);
				
				try {
					var res = JSON.parse(response);
					
					if (res.status === 'complete') {
						console.log('[uploadPDFChunked] 업로드 완료');
						if (callback) {
							callback(res.filename);
						}
					} else {
						currentChunk++;
						uploadNextChunk();
					}
				} catch (e) {
					console.error('[uploadPDFChunked] JSON 파싱 오류:', e);
					Swal.fire('Error', '청크 업로드 중 오류가 발생했습니다.', 'error');
				}
			},
			error: function (xhr, status, error) {
				console.error('[uploadPDFChunked] 청크 업로드 오류:', xhr.responseText);
				
				// If chunked upload also fails, try tiny chunk method
				if (xhr.status === 413 || xhr.status === 0) {
					console.log('[uploadPDFChunked] 청크 업로드도 실패, 아주 작은 청크로 시도');
					uploadPDFTinyChunks(pdfBase64, filename, callback);
				} else {
					Swal.fire('Error', '청크 업로드에 실패했습니다.', 'error');
				}
			}
		});
	}
	
	uploadNextChunk();
}

function uploadPDFTinyChunks(pdfBase64, filename, callback) {
	var chunkSize = 5 * 1024; // 5KB chunks (very tiny to avoid ModSecurity)
	var totalChunks = Math.ceil(pdfBase64.length / chunkSize);
	var currentChunk = 0;
	
	console.log('[uploadPDFTinyChunks] 아주 작은 청크 업로드 시작', {
		filename: filename,
		totalChunks: totalChunks,
		chunkSize: chunkSize
	});
	
	function uploadNextTinyChunk() {
		if (currentChunk >= totalChunks) {
			console.log('[uploadPDFTinyChunks] 모든 청크 업로드 완료');
			return;
		}
		
		var start = currentChunk * chunkSize;
		var end = Math.min(start + chunkSize, pdfBase64.length);
		var chunk = pdfBase64.substring(start, end);
		
		var formData = new FormData();
		formData.append('tiny_chunk', chunk);
		formData.append('filename', filename);
		formData.append('chunkIndex', currentChunk);
		formData.append('totalChunks', totalChunks);
		
		console.log('[uploadPDFTinyChunks] 청크 업로드 중', {
			chunkIndex: currentChunk,
			chunkSize: chunk.length
		});
		
		$.ajax({
			type: 'POST',
			url: 'save_pdf_tiny.php',
			data: formData,
			processData: false,
			contentType: false,
			success: function (response) {
				console.log('[uploadPDFTinyChunks] 청크 응답:', response);
				
				try {
					var res = JSON.parse(response);
					
					if (res.status === 'complete') {
						console.log('[uploadPDFTinyChunks] 업로드 완료');
						if (callback) {
							callback(res.filename);
						}
					} else {
						currentChunk++;
						uploadNextTinyChunk();
					}
				} catch (e) {
					console.error('[uploadPDFTinyChunks] JSON 파싱 오류:', e);
					Swal.fire('Error', '아주 작은 청크 업로드 중 오류가 발생했습니다.', 'error');
				}
			},
			error: function (xhr, status, error) {
				console.error('[uploadPDFTinyChunks] 청크 업로드 오류:', xhr.responseText);
				
				// If tiny chunks also fail, try alternative method
				if (xhr.status === 413 || xhr.status === 0) {
					console.log('[uploadPDFTinyChunks] 아주 작은 청크도 실패, 대체 방법 시도');
					uploadPDFAlternative(pdfBase64, filename, callback);
				} else {
					Swal.fire('Error', '아주 작은 청크 업로드에 실패했습니다.', 'error');
				}
			}
		});
	}
	
	uploadNextTinyChunk();
}

function uploadPDFAlternative(pdfBase64, filename, callback) {
	console.log('[uploadPDFAlternative] 대체 방법으로 업로드 시도');
	
	// Try FormData method instead of JSON
	var formData = new FormData();
	formData.append('pdf', pdfBase64);
	formData.append('filename', filename);
	
	$.ajax({
		type: 'POST',
		url: 'save_pdf_tiny.php',
		data: formData,
		processData: false,
		contentType: false,
		success: function (response) {
			console.log('[uploadPDFAlternative] 응답:', response);
			
			try {
				var res = JSON.parse(response);
				
				if (res.filename) {
					console.log('[uploadPDFAlternative] 성공');
					if (callback) {
						callback(res.filename);
					}
				} else {
					console.error('[uploadPDFAlternative] 오류:', res.error);
					Swal.fire('Error', 'PDF 저장에 실패했습니다: ' + res.error, 'error');
				}
			} catch (e) {
				console.error('[uploadPDFAlternative] JSON 파싱 오류:', e);
				Swal.fire('Error', '응답 파싱에 실패했습니다.', 'error');
			}
		},
		error: function (xhr, status, error) {
			console.error('[uploadPDFAlternative] 오류:', xhr.responseText);
			
			// Last resort: try to save locally and provide download link
			console.log('[uploadPDFAlternative] 로컬 다운로드로 대체');
			downloadPDFLocally(pdfBase64, filename, callback);
		}
	});
}

function downloadPDFLocally(pdfBase64, filename, callback) {
	console.log('[downloadPDFLocally] 로컬 다운로드 생성');
	
	// Create a blob and download link
	var byteCharacters = atob(pdfBase64);
	var byteNumbers = new Array(byteCharacters.length);
	for (var i = 0; i < byteCharacters.length; i++) {
		byteNumbers[i] = byteCharacters.charCodeAt(i);
	}
	var byteArray = new Uint8Array(byteNumbers);
	var blob = new Blob([byteArray], {type: 'application/pdf'});
	
	// Create download link
	var link = document.createElement('a');
	link.href = window.URL.createObjectURL(blob);
	link.download = filename;
	document.body.appendChild(link);
	link.click();
	document.body.removeChild(link);
	
	// Show success message - PDF downloaded but email cannot be sent without server storage
	Swal.fire({
		title: 'PDF 다운로드 완료',
		text: 'PDF가 다운로드되었습니다. 서버 저장에 실패하여 이메일 전송이 불가능합니다. 수동으로 이메일을 보내주세요.',
		icon: 'warning',
		confirmButtonText: '확인'
	});
}

function sendmail(num) {
    var num = num;
    var item = '<?php echo preg_replace('/[\/\\\\:*?"<>|]/u', '_', $item); ?>';
	var secondordnum = '<?php echo $secondordnum; ?>';

    console.log('[sendmail] num:', num);

    if (typeof ajaxRequest !== 'undefined' && ajaxRequest !== null) {
        ajaxRequest.abort();
    }

    ajaxRequest = $.ajax({
        type: 'POST',
        url: 'get_companyCode.php',
        data: { secondordnum: secondordnum },
        dataType: 'json',
        success: function(response) {
            console.log('[get_companyCode.php 응답]', response);

            if (response.error) {
                Swal.fire('Error', response.error, 'error');
            } else {
                var email = response.email;
                var vendorName = response.vendor_name;

                console.log('[get_companyCode] email:', email, '| vendorName:', vendorName);

                Swal.fire({
                    title: 'E메일 보내기',
                    text: vendorName + ' 전송후 거래명세표 전송시간이 기록됩니다.',
                    icon: 'warning',
                    input: 'text',
                    inputLabel: 'Email 주소 수정 가능',
                    inputValue: email,
                    showCancelButton: true,
                    confirmButtonText: '보내기',
                    cancelButtonText: '취소',
                    reverseButtons: true,
                    inputValidator: (value) => {
                        if (!value) {
                            return '이메일 주소를 입력해주세요!';
                        }
                        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailPattern.test(value)) {
                            return '올바른 이메일 형식을 입력해주세요!';
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const updatedEmail = result.value;
                        console.log('[Swal Confirmed] updatedEmail:', updatedEmail);

                        generatePDF_server(function(filename) {
                            console.log('[generatePDF_server 완료] filename:', filename);
                            sendEmail(updatedEmail, vendorName, item, filename, num);
                        });
                    }
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('[get_companyCode.php 에러]', xhr.responseText);
            Swal.fire('Error', '전송중 오류가 발생했습니다.', 'error');
        }
    });
}

function sendEmail(recipientEmail, vendorName, item, filename, num) {
    var deadline = '<?php echo $deadline; ?>';
    var deadlineDate = new Date(deadline);
    var formattedDate = "(" + String(deadlineDate.getFullYear()).slice(-2) + "." + ("0" + (deadlineDate.getMonth() + 1)).slice(-2) + "." + ("0" + deadlineDate.getDate()).slice(-2) + ")";

    console.log('[sendEmail 호출]', {
        recipientEmail,
        vendorName,
        item,
        filename,
        formattedDate
    });

    if (typeof ajaxRequest !== 'undefined' && ajaxRequest !== null) {
        ajaxRequest.abort();
    }

    ajaxRequest = $.ajax({
        type: 'POST',
        url: 'send_email.php',
        data: {
            email: recipientEmail,
            vendorName: vendorName,
            filename: filename,
            item: item,
            formattedDate: formattedDate,
            num: num
        },
		dataType : 'json',
        success: function(response) {
            console.log('[send_email.php 응답]', response);

            if (typeof response === 'string') {
                try {
                    response = JSON.parse(response);
                } catch (e) {
                    console.error('[JSON 파싱 실패]', response);
                    Swal.fire('Error', '서버 응답이 올바르지 않습니다.', 'error');
                    return;
                }
            }

            if (response.error) {
				// Close loading dialog first
				Swal.close();
                Swal.fire('Error', response.error, 'error');
            } else {
				// Close loading dialog first
				Swal.close();
                Swal.fire('Success', '정상적으로 전송되었습니다.', 'success');
                setTimeout(function() {
                    window.opener.location.reload();
                }, 500);
            } 
        },
        error: function(xhr, status, error) {
            console.error('[send_email.php 에러]', xhr.responseText);
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

</script>

<script>
// 정가적용 표시
document.getElementById('regularPriceCheckbox').addEventListener('change', function() {
    var url = new URL(window.location.href);
    if (this.checked) {
         url.searchParams.set('regularPrice', '1');
    } else {
         url.searchParams.set('regularPrice', '0');  // 삭제하지 않고 '0'으로 설정
    }
    window.location.href = url.toString();
});

// 할인율 표시
document.getElementById('dcPriceCheckbox').addEventListener('change', function() {
    var url = new URL(window.location.href);
    if (this.checked) {
         url.searchParams.set('dcPrice', '1');
    } else {
         url.searchParams.set('dcPrice', '0');  // 삭제하지 않고 '0'으로 설정
    }
    window.location.href = url.toString();
});
</script>

</body>
</html>