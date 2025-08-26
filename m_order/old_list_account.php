<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

$title_message = '구매 발주분(중국) 송금내역 '; 

if(!isset($_SESSION["level"]) || $_SESSION["level"]>5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php"); 
    exit;
}    
include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';   
?>
<title> <?=$title_message?> </title>
<link href="css/style.css" rel="stylesheet" >   
</head>
<body>    
<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php'); ?>   

<?php
$tablename = 'm_order'; 

$search = $_REQUEST['search'] ?? '';  
$fromdate = $_REQUEST['fromdate'] ?? '';  
$todate = $_REQUEST['todate'] ?? '';  
$mode = $_REQUEST['mode'] ?? '';  
   
function decodeList($jsonData) {
    $decoded = json_decode($jsonData, true);
    if (is_array($decoded)) {
        $totalOrder = 0;
        $totalInput = 0;
        $validRows = 0;
        $hasDifference = false;
        
        // 먼저 전체 발주수량과 입고수량 계산
        foreach ($decoded as $item) {

            $col3 = $item['col3'] ?? '';
            $col20 = $item['col20'] ?? '';
            $col21 = $item['col21'] ?? '';
            
            // 콤마 제거 후 숫자 변환
            $col3_clean = str_replace(',', '', $col3);
            $col20_clean = str_replace(',', '', $col20);
            $col21_clean = str_replace(',', '', $col21);
            
            // 숫자가 아닌 값은 0으로 처리
            $col3_numeric = is_numeric($col3_clean) ? (float)$col3_clean : 0;
            $col20_numeric = is_numeric($col20_clean) ? (float)$col20_clean : 0;
            
            $totalOrder += $col3_numeric;
            $totalInput += $col20_numeric;
            
            // 차이가 있는 항목이 있는지 확인 (숫자 비교)
            $col21_numeric = is_numeric($col21_clean) ? (float)$col21_clean : 0;
            if ($col21_numeric != 0) {
                $hasDifference = true;
            }
        }
        
        // 전체 발주수량과 입고수량이 같으면 완료 메시지 표시
        // 부동소수점 비교를 위해 반올림 처리
        $totalOrder_rounded = round($totalOrder, 2);
        $totalInput_rounded = round($totalInput, 2);
        
        // 디버깅용 로그 (나중에 제거)
        if ($totalOrder > 0) {
            error_log("Debug - totalOrder: $totalOrder, totalInput: $totalInput, rounded: $totalOrder_rounded vs $totalInput_rounded, hasDifference: " . ($hasDifference ? 'true' : 'false'));
        }
        
        if ($totalOrder_rounded == $totalInput_rounded && $totalOrder > 0) {
            return '<div class="text-success fw-bold text-center" style="font-size: 12px; padding: 5px; background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px;">전체 발주분 입고완료</div>';
        }
        
        // 차이가 있는 항목만 테이블로 표시
        if ($hasDifference) {
            $table = '<table class="table table-sm table-bordered mb-0" style="font-size: 11px;">';
            $table .= '<thead class="table-light">';
            $table .= '<tr>';
            $table .= '<th class="text-center" style="width: 40%;">품명</th>';
            $table .= '<th class="text-center" style="width: 30%;">발주</th>';
            $table .= '<th class="text-center" style="width: 30%;">입고</th>';
            $table .= '</tr>';
            $table .= '</thead>';
            $table .= '<tbody>';
            
            foreach ($decoded as $item) {
                $col1 = isset($item['col1']) ? trim($item['col1']) : '';
                $col3 = isset($item['col3']) ? trim($item['col3']) : '';
                $col20 = isset($item['col20']) ? trim($item['col20']) : '';
                $col21 = isset($item['col21']) ? trim($item['col21']) : '';
                
                // 콤마 제거 후 숫자 변환
                $col3_clean = str_replace(',', '', $col3);
                $col20_clean = str_replace(',', '', $col20);
                $col21_clean = str_replace(',', '', $col21);
                
                // 차이가 0이 아닌 경우만 표시 (숫자 비교)
                $col21_numeric = is_numeric($col21_clean) ? (float)$col21_clean : 0;
                if ($col21_numeric != 0) {
                    $table .= '<tr>';
                    $table .= '<td class="text-start">' . htmlspecialchars($col1) . '</td>';
                    $table .= '<td class="text-end">' . (is_numeric($col3_clean) ? number_format($col3_clean) : $col3) . '</td>';
                    $table .= '<td class="text-end">' . (is_numeric($col20_clean) ? number_format($col20_clean) : $col20) . '</td>';
                    $table .= '</tr>';
                    $validRows++;
                }
            }
            
            // 합계 행 추가 (유효한 행이 있는 경우에만)
            if ($validRows > 0) {
                $table .= '<tr class="table-warning fw-bold">';
                $table .= '<td class="text-center">합계</td>';
                $table .= '<td class="text-end">' . number_format($totalOrder) . '</td>';
                $table .= '<td class="text-end">' . number_format($totalInput) . '</td>';
                $table .= '</tr>';
            }
            
            $table .= '</tbody>';
            $table .= '</table>';
            
            return $table;
        }
        
        // 차이가 없는 경우 빈 문자열 반환
        return '';
    }
    return '';
}

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

// 현재 날짜
$currentDate = date("Y-m-d");

// fromdate 또는 todate가 빈 문자열이거나 null인 경우
if ($fromdate === "" || $fromdate === null || $todate === "" || $todate === null) {
    // $fromdate = date("Y-m-d", strtotime("-4 weeks", strtotime($currentDate))); // 4주 전 날짜
	$fromdate = date("2024-01-01"); 
    $todate = date("Y-m-d", strtotime("+2 months", strtotime($currentDate))); // 2개월 후 날짜
    $Transtodate = $todate;
} else {
    // fromdate와 todate가 모두 설정된 경우 (기존 로직 유지)
    $Transtodate = $todate;
}

$sql="SELECT * FROM {$tablename}";

$sum=array();
$now = date("Y-m-d");     // 현재 날짜와 크거나 같으면 출하예정으로 구분

$orderby=" ORDER BY orderDate DESC, num DESC";
$attached=''; 
$whereattached = '';
$titletag = '';
        
$SettingDate=" orderDate "; 
$common= $SettingDate . " BETWEEN '$fromdate' AND '$Transtodate' AND (is_deleted IS NULL OR is_deleted = '') ";
$andPhrase= " AND " . $common  . $orderby ;
$wherePhrase= " WHERE " . $common  . $orderby ;

// 검색을 위해 모든 검색변수 공백제거
$search = str_replace(' ', '', $search);  

if($search==""){
    if($whereattached!=='')
        $sql="SELECT * FROM " . $tablename . " " .  $whereattached . $andPhrase;                                          
    else
        $sql="SELECT * FROM  " . $tablename . " " .  $wherePhrase ;                                         
}                 
else {
    $sql ="SELECT * FROM  " . $tablename . " WHERE (REPLACE(searchtag,' ','') LIKE '%$search%' ) " . $attached . " AND is_deleted IS NULL " . $orderby;                       
}


try {
    $stmh = $pdo->query($sql);
    $rows = $stmh->fetchAll(PDO::FETCH_ASSOC); // 모든 데이터를 한 번에 가져오기
    $total_row = count($rows); // 가져온 데이터의 행 수 계산
    $start_num = $total_row;
} catch (PDOException $Exception) {
    echo "오류: " . $Exception->getMessage();
}
?>
<div class="container-fluid">  
<form id="board_form" name="board_form" method="post" >         
    <div class="d-flex justify-content-center align-items-center ">
    <div class="card mb-2 mt-2 ">  
        <div class="card-body">       
            <div class="d-flex mt-1 mb-4 justify-content-center align-items-center">         
                <h5 class="mx-1">  <?=$title_message?>  <?=$titletag?> </h5>  &nbsp;&nbsp;
				<button type="button" class="btn btn-dark btn-sm mx-2"  onclick='location.reload();' > <i class="bi bi-arrow-clockwise"></i> </button>  	 
				<button type="button" class="btn btn-success btn-sm ms-1"  onclick='location.href="list.php";' title="발주서 이동" >  <i class="bi bi-list-ol"></i> </button>  	   		  										
				<button type="button" class="btn btn-primary btn-sm mx-3"  onclick='location.href="list_input.php";' title="발주 입고창 이동" >  <i class="bi bi-list-columns"></i> </button>  	   		  						
            </div>    			
			<div class="d-flex mt-1 mb-1 justify-content-center align-items-center">         
				<div class="alert alert-primary mx-3 w-50 text-center p-1" role="alert">
					단가, 금액은 (CNY) 위엔화 기준, 소수점 둘째자리까지 표시 
				</div>	
			</div>
			
			<!-- 발주일자별 요약 테이블 -->
			<div class="d-flex flex-wrap justify-content-center align-items-start mt-2 mb-3">
				<?php
				try {
					// 발주일자별 요약 테이블용 쿼리 (오래된 순서)
					$summaryOrderby = " ORDER BY orderDate ASC, num ASC";
					$summaryWherePhrase = " WHERE " . $common . $summaryOrderby;
					$summarySql = "SELECT * FROM " . $tablename . " " . $summaryWherePhrase;
					
					$stmh = $pdo->query($summarySql);
					$rows = $stmh->fetchAll(PDO::FETCH_ASSOC);
					foreach ($rows as $row) {
						include $_SERVER['DOCUMENT_ROOT'] . '/m_order/_row.php';
						$orderDate = $orderDate ?? '';

						// JSON 디코딩
						$decoded = json_decode($row['orderlist'], true);

						// 합계 초기화
						$totalamount = 0;
						$inputSum = 0;

						if (is_array($decoded)) {
							foreach ($decoded as $item) {
								// 발주 금액(col6) 합계
								$amt = str_replace(',', '', ($item['col6'] ?? '0'));
								$amt_float = is_numeric($amt) ? (float)$amt : 0;
								$totalamount += $amt_float;

								// 입력 항목(col23,25,27,29) 합계
								foreach (['col23','col25','col27','col29'] as $c) {
									$v = str_replace(',', '', trim(isset($item[$c]) ? $item[$c] : '0'));
									$v_float = is_numeric($v) ? (float)$v : 0;
									$inputSum += $v_float;
								}
							}
						}

						// 원래 테이블과 동일한 로직으로 누적입고물량대금합(CNY) 계산
						$customs_input_amount_cny1 = str_replace(',', '', ($customs_input_amount_cny1 ?? '0'));
						$customs_input_amount_cny2 = str_replace(',', '', ($customs_input_amount_cny2 ?? '0')); 
						$customs_input_amount_cny3 = str_replace(',', '', ($customs_input_amount_cny3 ?? '0'));
						$customs_input_amount_cny4 = str_replace(',', '', ($customs_input_amount_cny4 ?? '0'));

						// 숫자 체크 및 형변환
						$customs_input_amount_cny1 = is_numeric($customs_input_amount_cny1) ? (float)$customs_input_amount_cny1 : 0;
						$customs_input_amount_cny2 = is_numeric($customs_input_amount_cny2) ? (float)$customs_input_amount_cny2 : 0;
						$customs_input_amount_cny3 = is_numeric($customs_input_amount_cny3) ? (float)$customs_input_amount_cny3 : 0;
						$customs_input_amount_cny4 = is_numeric($customs_input_amount_cny4) ? (float)$customs_input_amount_cny4 : 0;

						$customs_input_amount_cny_sum = $customs_input_amount_cny1 + $customs_input_amount_cny2 + $customs_input_amount_cny3 + $customs_input_amount_cny4;

						// 차액 계산
						$difference = round($totalamount - $customs_input_amount_cny_sum, 2);
						?>
						<div class="card mx-2 mb-2" style="min-width: 200px; padding: 0px;">
							<div class="card-header p-2">
								<h6 class="mb-0 text-center"> 발주일 : <?= htmlspecialchars($orderDate) ?></h6>
							</div>
							<div class="card-body p-0">
								<table class="table table-sm table-bordered mb-0" style="font-size: 10px; padding: 0px;">
									<thead class="table-light">
										<tr>
											<th class="text-center" style="width: 60%;">구분</th>
											<th class="text-center" style="width: 40%;">금액(CNY)</th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td class="text-start">발주금액(CNY)</td>
											<td class="text-end"><?= number_format($totalamount, 2) ?></td>
										</tr>
										<tr>
											<td class="text-start">누적입고물량대금합(CNY)</td>
											<td class="text-end"><?= number_format($customs_input_amount_cny_sum, 2) ?></td>
										</tr>
										<tr class="<?= $difference > 0 ? 'table-warning' : 'table-success' ?>">
											<td class="text-start fw-bold">차액</td>
											<td class="text-end fw-bold"><?= number_format($difference, 2) ?></td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
						<?php
					}
				} catch (PDOException $Exception) {
					echo "오류: " . $Exception->getMessage();
				}
				?>
			</div>	
            <div class="d-flex mt-1 mb-1 justify-content-center align-items-center">       
               ▷   <?= $total_row ?> &nbsp;                           
                <input type="date" id="fromdate" name="fromdate" class="form-control" style="width:100px;" value="<?=$fromdate?>">  &nbsp;   ~ &nbsp;  
                <input type="date" id="todate" name="todate" class="form-control me-1" style="width:100px;" value="<?=$todate?>">  &nbsp;  
            
                <div class="inputWrap">
                    <input type="text" id="search" name="search" value="<?=$search?>" onkeydown="JavaScript:SearchEnter();" autocomplete="off" class="form-control" style="width:150px;"> &nbsp;            
                    <button class="btnClear"></button>
                </div>                
                <div id="autocomplete-list"></div>    
                &nbsp;
                <button id="searchBtn" type="button" class="btn btn-dark  btn-sm"> <i class="bi bi-search"></i> 검색 </button>                          
            </div>               
        </div> <!--end of card-body-->
    </div> <!--end of card -->   
    </div> <!--end of d-flex-->    
</form>    
</div> <!-- end of container -->   

<div class="container-fluid mt-1 mb-3 w-90">    
<div class="table-responsive">     
<table class="table table-bordered table-hover" >
  <thead class="table-danger">
    <tr>
      <th class="text-center">발주일자</th>
      <th class="text-end">발주금액(CNY)</th>
      <th class="text-end">1차송금(CNY)</th>
      <th class="text-end">2차송금(CNY)</th>
      <th class="text-end">3차송금(CNY)</th>
      <th class="text-end">4차송금(CNY)</th>
      <th class="text-end">잔액(CNY)</th>
      <th class="text-end">1차 통관비용(원)</th>
      <th class="text-end">2차 통관비용(원)</th>
      <th class="text-end">3차 통관비용(원)</th>
      <th class="text-end">4차 통관비용(원)</th>
      <th class="text-end">통관비용합계(원)</th>
      <th class="text-end">누적입고물량대금합(CNY)</th>
      <th class="text-center">전체보기</th>
    </tr>
  </thead>
  <tbody>
    <?php
   
   try {
    $stmh = $pdo->query($sql);
    $rows = $stmh->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        include $_SERVER['DOCUMENT_ROOT'] . '/m_order/_row.php';
        $orderDate = $orderDate ?? '';

        // JSON 디코딩
        $decoded = json_decode($row['orderlist'], true);

        // 합계 초기화
        $totalamount = 0;
        $inputSum    = 0;
        $input1      = 0;
        $input2      = 0;
        $input3      = 0;
        $input4      = 0;

        if (is_array($decoded)) {
            // 디버그용 변수 초기화
            $debug_output = '';
            $debug_output .= '<div style="font-size:12px;margin:10px;padding:10px;border:1px solid #ccc;">';
            $debug_output .= '<h5>디버그 정보</h5>';

            foreach ($decoded as $item) {
                // 1) 발주 금액(col6) 합계
                $amt = str_replace(',', '', ($item['col6'] ?? '0'));
                $amt_float = is_numeric($amt) ? (float)$amt : 0;
                $totalamount += $amt_float;

                // 디버그 출력 - 발주금액
                $debug_output .= sprintf('발주금액(col6): %s -> %s<br>', $item['col6'] ?? '0', number_format($amt_float));

                // 2) 입력 항목(col23,25,27,29) 합계
                foreach (['col23','col25','col27','col29'] as $c) {
                    $v = str_replace(',', '', trim(isset($item[$c]) ? $item[$c] : '0'));
                    $v_float = is_numeric($v) ? (float)$v : 0;
                    $inputSum += $v_float;
                    if($c == 'col23' && $item['col23'] != '') $input1 += $v_float;
                    if($c == 'col25' && $item['col25'] != '') $input2 += $v_float;
                    if($c == 'col27' && $item['col27'] != '') $input3 += $v_float;
                    if($c == 'col29' && $item['col29'] != '') $input4 += $v_float;

                    // 디버그 출력 - 입력항목
                    $debug_output .= sprintf('%s: %s -> %s<br>', 
                        $c,
                        isset($item[$c]) ? $item[$c] : '0',
                        number_format($v_float)
                    );
                }
                $debug_output .= '<hr>';
            }

            $debug_output .= sprintf('최종 발주금액 합계: %s<br>', number_format($totalamount));
            $debug_output .= sprintf('최종 입력항목 합계: %s<br>', number_format($inputSum));
            $debug_output .= '</div>';

            // 디버그 정보 출력
            // echo $debug_output;
        }

        // 미입고 금액과 잔액 계산
        $미입고금액 = round($totalamount - $inputSum, 2);
        $send1 = (float)str_replace(',', '', ($sendMoney1 ?? 0));
        $send2 = (float)str_replace(',', '', ($sendMoney2 ?? 0));
        $send3 = (float)str_replace(',', '', ($sendMoney3 ?? 0));
        $send4 = (float)str_replace(',', '', ($sendMoney4 ?? 0));
        $잔액    = $totalamount - ($send1 + $send2 + $send3 + $send4);

        // 각 차수별 통관비용 합계 
        // 부가세
        $customs_detail_total1 = (int)str_replace(',', '', ($customs_vat1 ?? 0));
        $customs_detail_total2 = (int)str_replace(',', '', ($customs_vat2 ?? 0));
        $customs_detail_total3 = (int)str_replace(',', '', ($customs_vat3 ?? 0));
        $customs_detail_total4 = (int)str_replace(',', '', ($customs_vat4 ?? 0));

        // 선임 및 부대비용
        $customs_detail_total1 += (int)str_replace(',', '', ($customs_miscellaneous_fee1 ?? 0));
        $customs_detail_total2 += (int)str_replace(',', '', ($customs_miscellaneous_fee2 ?? 0));
        $customs_detail_total3 += (int)str_replace(',', '', ($customs_miscellaneous_fee3 ?? 0));
        $customs_detail_total4 += (int)str_replace(',', '', ($customs_miscellaneous_fee4 ?? 0));

        // 컨테이너운송비용
        $customs_detail_total1 += (int)str_replace(',', '', ($customs_container_fee1 ?? 0));
        $customs_detail_total2 += (int)str_replace(',', '', ($customs_container_fee2 ?? 0));
        $customs_detail_total3 += (int)str_replace(',', '', ($customs_container_fee3 ?? 0));
        $customs_detail_total4 += (int)str_replace(',', '', ($customs_container_fee4 ?? 0));

        // 통관수수료
        $customs_detail_total1 += (int)str_replace(',', '', ($customs_commission1 ?? 0));
        $customs_detail_total2 += (int)str_replace(',', '', ($customs_commission2 ?? 0));
        $customs_detail_total3 += (int)str_replace(',', '', ($customs_commission3 ?? 0));
        $customs_detail_total4 += (int)str_replace(',', '', ($customs_commission4 ?? 0));

        // 테이블 행 출력
        echo '<tr style="cursor:pointer;" data-num="' . $num . '">';
        echo '<td class="text-center">' . htmlspecialchars($orderDate) . '</td>';
        echo '<td class="text-end">' . number_format($totalamount,2) . '</td>';
        echo '<td class="text-end send-td" data-round="1" data-num="' . $num . '">' . (is_numeric($send1) && $send1 != 0 ? number_format($send1, 2) : '') . '</td>';
        echo '<td class="text-end send-td" data-round="2" data-num="' . $num . '">' . (is_numeric($send2) && $send2 != 0 ? number_format($send2, 2) : '') . '</td>';
        echo '<td class="text-end send-td" data-round="3" data-num="' . $num . '">' . (is_numeric($send3) && $send3 != 0 ? number_format($send3, 2) : '') . '</td>';
        echo '<td class="text-end send-td" data-round="4" data-num="' . $num . '">' . (is_numeric($send4) && $send4 != 0 ? number_format($send4, 2) : '') . '</td>';
        echo '<td class="text-end">' . (is_numeric($잔액) && $잔액 != 0 ? number_format($잔액, 2) : '') . '</td>';
        echo '<td class="text-end customs-td" data-round="1" data-num="' . $num . '">' . (is_numeric($customs_detail_total1) && $customs_detail_total1 != 0 ? number_format($customs_detail_total1) : '') . '</td>';
        echo '<td class="text-end customs-td" data-round="2" data-num="' . $num . '">' . (is_numeric($customs_detail_total2) && $customs_detail_total2 != 0 ? number_format($customs_detail_total2) : '') . '</td>';
        echo '<td class="text-end customs-td" data-round="3" data-num="' . $num . '">' . (is_numeric($customs_detail_total3) && $customs_detail_total3 != 0 ? number_format($customs_detail_total3) : '') . '</td>';
        echo '<td class="text-end customs-td" data-round="4" data-num="' . $num . '">' . (is_numeric($customs_detail_total4) && $customs_detail_total4 != 0 ? number_format($customs_detail_total4) : '') . '</td>';
        
        // 안전하게 합산 (is_numeric 체크)
        $fee1 = is_numeric($customs_detail_total1) ? $customs_detail_total1 : '';
        $fee2 = is_numeric($customs_detail_total2) ? $customs_detail_total2 : '';
        $fee3 = is_numeric($customs_detail_total3) ? $customs_detail_total3 : '';
        $fee4 = is_numeric($customs_detail_total4) ? $customs_detail_total4 : '';
        $fee_sum = $fee1 + $fee2 + $fee3 + $fee4;
        echo '<td class="text-end">' . ($fee_sum > 0 ? number_format($fee_sum) : '') . '</td>';
        // 콤마 제거 후 숫자 변환
        $customs_input_amount_cny1 = str_replace(',', '', $customs_input_amount_cny1);
        $customs_input_amount_cny2 = str_replace(',', '', $customs_input_amount_cny2); 
        $customs_input_amount_cny3 = str_replace(',', '', $customs_input_amount_cny3);
        $customs_input_amount_cny4 = str_replace(',', '', $customs_input_amount_cny4);

        // 숫자 체크 및 형변환
        $customs_input_amount_cny1 = is_numeric($customs_input_amount_cny1) ? (float)$customs_input_amount_cny1 : 0;
        $customs_input_amount_cny2 = is_numeric($customs_input_amount_cny2) ? (float)$customs_input_amount_cny2 : 0;
        $customs_input_amount_cny3 = is_numeric($customs_input_amount_cny3) ? (float)$customs_input_amount_cny3 : 0;
        $customs_input_amount_cny4 = is_numeric($customs_input_amount_cny4) ? (float)$customs_input_amount_cny4 : 0;

        $customs_input_amount_cny_sum = $customs_input_amount_cny1 + $customs_input_amount_cny2 + $customs_input_amount_cny3 + $customs_input_amount_cny4;
        echo '<td class="text-end">' . ($customs_input_amount_cny_sum > 0 ? number_format($customs_input_amount_cny_sum, 2) : '') . '</td>';
        echo '<td class="text-center" onclick="redirectToView(' . $num . ',\'' . $tablename . '\')"> <i class="bi bi-eye"></i> </td>';
        echo '</tr>';
    }
} catch (PDOException $Exception) {
    echo "오류: " . $Exception->getMessage();
}
    ?>
  </tbody>
</table>
</div>
</div>
<!-- 송금내역 입력 모달 -->
<div class="modal" id="sendModal" tabindex="-1" style="display:none;">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="sendForm">
        <div class="modal-header">
          <h5 class="modal-title" id="sendModalTitle">송금내역 입력</h5>
        </div>
        <div class="modal-body">
          <input type="hidden" id="modalNum" name="num">
          <input type="hidden" id="modalRound" name="round">
          <table class="table table-bordered mb-0 table-sm w-75 mx-auto">
            <tr>
              <th style="width:55%;">송금일자</th>
              <td><input type="date" class="form-control" id="modalDate" name="date" ></td>
            </tr>
            <tr>
              <th>환율</th>
              <td><input type="text" class="form-control text-end" id="modalRate" name="rate" autocomplete="off" ></td>
            </tr>
            <tr>
              <th>원화 금액</th>
              <td><input type="text" class="form-control number-comma text-end" id="modalKRW" name="krw" required autocomplete="off"></td>
            </tr>
            <tr>
              <th>송금수수료(원)</th>
              <td><input type="text" class="form-control number-comma text-end" id="modalfeeKRW" name="remittanceFee" autocomplete="off"></td>
            </tr>
            <tr>
              <th>원화 누적 금액 <br> <span class="text-danger">(송금수수료 제외)</span></th>
              <td><input type="text" class="form-control number-comma text-end" id="modalKRWTotal" name="krwTotal" required autocomplete="off" readonly></td>
            </tr>            
            <tr>
              <th>송금액(CNY)</th>
              <td><input type="text" class="form-control number-comma text-end" id="modalCNY" name="cny" autocomplete="off"></td>
            </tr>
          </table>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" id="modalCloseBtn">닫기</button>
          <button type="submit" class="btn btn-primary" id="modalSaveBtn">저장</button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- 통관비용 입력 모달 -->
<div class="modal" id="customsModal" tabindex="-1" style="display:none;">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="customsForm">
        <div class="modal-header">
          <h5 class="modal-title" id="customsModalTitle">통관비용 상세입력</h5>
        </div>
        <div class="modal-body">
          <input type="hidden" id="customsNum" name="num">
          <input type="hidden" id="customsRound" name="round">
          <table class="table table-bordered mb-0 w-75 mx-auto">
            <tr>
              <th class="text-end" style="width:50%;">통관일자</th>
              <td><input type="date" class="form-control" id="customsDate" name="date" autocomplete="off"></td>
            </tr>
            <tr>
              <th class="text-end">부가세(원)</th>
              <td><input type="text" class="form-control number-comma text-end" id="customsVat" name="vat" autocomplete="off"></td>
            </tr>
            <tr>
              <th class="text-end">선임및부대비(원)</th>
              <td><input type="text" class="form-control number-comma text-end" id="customsEtc" name="etc" autocomplete="off"></td>
            </tr>
            <tr>
              <th class="text-end">컨테이너운송</th>
              <td><input type="text" class="form-control number-comma text-end" id="customsContainer" name="container" autocomplete="off"></td>
            </tr>
            <tr>
              <th class="text-end">통관수수료</th>
              <td><input type="text" class="form-control number-comma text-end" id="customsFee" name="fee" autocomplete="off"></td>
            </tr>
            <tr>
              <th class="fw-bold text-primary text-end"> 원화 합계</th>
              <td><input type="text" class="form-control number-comma text-end" id="customsTotal" name="total" readonly autocomplete="off"></td>
            </tr>
            <tr>
              <th class="fw-bold text-danger text-end">입고물량대금(CNY)</th>
              <td><input type="text" class="form-control number-comma text-end" id="customsInputAmountCNY" name="input_amount_cny" autocomplete="off"></td>
            </tr>            
          </table>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" id="customsModalCloseBtn">닫기</button>
          <button type="submit" class="btn btn-primary" id="customsModalSaveBtn">저장</button>
        </div>
      </form>
    </div>
  </div>
</div> <!-- end of customsModal -->

<div class="container-fluid mt-3 mb-3">
    <? include '../footer_sub.php'; ?>
</div>

<script>
// 페이지 로딩
$(document).ready(function(){    
    var loader = document.getElementById('loadingOverlay');
	if(loader)
		loader.style.display = 'none';
});

var dataTable; // DataTables 인스턴스 전역 변수
var orderpageNumber; // 현재 페이지 번호 저장을 위한 전역 변수

$(document).ready(function() {            
    // DataTables 초기 설정
    dataTable = $('#myTable').DataTable({
        "paging": true,
        "ordering": true,
        "searching": true,
        "pageLength": 50,
        "lengthMenu": [50, 100, 200, 500, 1000],
        "language": {
            "lengthMenu": "Show _MENU_ entries",
            "search": "Live Search:"
        },
        "order": [[0, 'desc']] 
    });

    // 페이지 번호 복원 (초기 로드 시)
    var savedPageNumber = getCookie('orderpageNumber');
    if (savedPageNumber) {
        dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
    }

    // 페이지 변경 이벤트 리스너
    dataTable.on('page.dt', function() {
        var orderpageNumber = dataTable.page.info().page + 1;
        setCookie('orderpageNumber', orderpageNumber, 10); // 쿠키에 페이지 번호 저장
    });

    // 페이지 길이 셀렉트 박스 변경 이벤트 처리
    $('#myTable_length select').on('change', function() {
        var selectedValue = $(this).val();
        dataTable.page.len(selectedValue).draw(); // 페이지 길이 변경 (DataTable 파괴 및 재초기화 없이)

        // 변경 후 현재 페이지 번호 복원
        savedPageNumber = getCookie('orderpageNumber');
        if (savedPageNumber) {
            dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
        }
    });
});

function restorePageNumber() {
    var savedPageNumber = getCookie('orderpageNumber');
    location.reload(true);
}

function redirectToView(num, tablename) {    
    var url = "write_account.php?mode=view&num=" + num + "&tablename=" + tablename;          
    customPopup(url, '', 1850, 900);             
}

$(document).ready(function(){    
    $("#writeBtn").click(function(){         
        var tablename = '<?php echo $tablename; ?>';        
        var url = "write_account.php?tablename=" + tablename;                 
        customPopup(url, '', 1850, 900);       
     });             
});    

function SearchEnter(){
    if(event.keyCode == 13){        
        saveSearch();
    }
}

function saveSearch() {
    let searchInput = document.getElementById('search');
    let searchValue = searchInput.value;

    if (searchValue === "") {        
        document.getElementById('board_form').submit();
    } else {
        let now = new Date();
        let timestamp = now.toLocaleDateString() + ' ' + now.toLocaleTimeString();

        let searches = getSearches();
        searches = searches.filter(search => search.keyword !== searchValue);
        searches.unshift({ keyword: searchValue, time: timestamp });
        searches = searches.slice(0, 15);

        document.cookie = "searches=" + JSON.stringify(searches) + "; max-age=31536000";
        
        var orderpageNumber = 1;
        setCookie('orderpageNumber', orderpageNumber, 10);         
        $('#dateRange').val('전체').change();
        document.getElementById('board_form').submit();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const autocompleteList = document.getElementById('autocomplete-list');  

    searchInput.addEventListener('input', function() {
        const val = this.value;
        let searches = getSearches();
        let matches = searches.filter(s => {
            if (typeof s.keyword === 'string') {
                return s.keyword.toLowerCase().includes(val.toLowerCase());
            }
            return false;
        });            
        renderAutocomplete(matches);               
    });
    
    searchInput.addEventListener('focus', function() {
        let searches = getSearches();
        renderAutocomplete(searches);   

        console.log(searches);                
    });
});

var isMouseOverSearch = false;
var isMouseOverAutocomplete = false;

document.getElementById('search').addEventListener('focus', function() {
    isMouseOverSearch = true;
    showAutocomplete();
});

document.getElementById('search').addEventListener('blur', function() {        
    setTimeout(function() {
        if (!isMouseOverAutocomplete) {
            hideAutocomplete();
        }
    }, 100); 
});

function hideAutocomplete() {
    document.getElementById('autocomplete-list').style.display = 'none';
}

function showAutocomplete() {
    document.getElementById('autocomplete-list').style.display = 'block';
}

function renderAutocomplete(matches) {
    const autocompleteList = document.getElementById('autocomplete-list');    

    const items = autocompleteList.getElementsByClassName('autocomplete-item');
    while(items.length > 0){
        items[0].parentNode.removeChild(items[0]);
    }

    matches.forEach(function(match) {
        let div = document.createElement('div');
        div.className = 'autocomplete-item';
        div.innerHTML =  '<span class="text-primary">' + match.keyword + ' </span>';
        div.addEventListener('click', function() {
            document.getElementById('search').value = match.keyword;
            autocompleteList.innerHTML = '';            
            document.getElementById('board_form').submit();    
        });
        autocompleteList.appendChild(div);
    });
}

function getSearches() {
    let cookies = document.cookie.split('; ');
    for (let cookie of cookies) {
        if (cookie.startsWith('searches=')) {
            try {
                let searches = JSON.parse(cookie.substring(9));
                if (searches.length > 15) {
                    return searches.slice(0, 15);
                }
                return searches;
            } catch (e) {
                console.error('Error parsing JSON from cookies', e);
                return []; 
            }
        }
    }
    return []; 
}

$(document).ready(function(){    

    $("#searchBtn").click(function(){     
        saveSearch(); 
    });        

});

$(document).ready(function(){    
    var showstatus = document.getElementById('showstatus');
    var showstatusframe = document.getElementById('showstatusframe');
    
    if (!showstatus || !showstatusframe) {
        return;
    }

    var hideTimeoutstatus; 

    showstatus.addEventListener('mouseenter', function(event) {
        clearTimeout(hideTimeoutstatus);  
        showstatusframe.style.top = (showstatus.offsetTop + showstatus.offsetHeight) + 'px';
        showstatusframe.style.left = showstatus.offsetLeft + 'px';
        showstatusframe.style.display = 'block';
    });

    showstatus.addEventListener('mouseleave', startstatusHideTimer);

    showstatusframe.addEventListener('mouseenter', function() {
        clearTimeout(hideTimeoutstatus);  
    });

    showstatusframe.addEventListener('mouseleave', startstatusHideTimer);

    function startstatusHideTimer() {
        hideTimeoutstatus = setTimeout(function() {
            showstatusframe.style.display = 'none';
        }, 50);  
    }
});

// 숫자를 콤마 형식으로 변환하는 함수
function formatNumber(num) {
    if (isNaN(num) || num === '') return '';
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

$(document).ready(function(){  
    // 모든 필요한 셀을 선택하여 콤마 형식으로 변환
    document.querySelectorAll('.number-format').forEach(function(element) {
        var value = element.innerText;
        element.innerText = formatNumber(value);
    });
});

$(document).ready(function(){
	saveLogData('중국 구매서 (입고) 리스트 조회'); 
});

// 모달 열기
$(document).on('click', '.send-td', function() {
    var num = $(this).data('num');
    var round = $(this).data('round');
    $('#modalNum').val(num);
    $('#modalRound').val(round);
    $('#sendModalTitle').text(round + '차 송금내역 입력');
    // 기존 값 불러오기
    $.get('get_send_info.php', {num, round}, function(res){
        console.log('모달창 송금액 차수별 ',res);
        if(res.success && res.data){
            var rate = res.data.rate;
            if(rate === undefined || rate === null || rate === '' ||  rate == '0' || rate == '0.0') rate = '190.00';
            else rate = parseFloat(rate);
            $('#modalDate').val(res.data.date || '');
            $('#modalRate').val(res.data.rate ? rate : '190.00');    // 기본값은 190원 환율 적용
            $('#modalKRW').val(res.data.krw ? res.data.krw.replace(/[^0-9]/g, '').toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") : '');
            $('#modalCNY').val(res.data.cny ? res.data.cny.replace(/[^0-9.]/g, '').toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") : '');
            $('#modalfeeKRW').val(res.data.remittanceFee ? res.data.remittanceFee.replace(/[^0-9.]/g, '').toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") : '');
        } else {
            $('#modalDate').val('');
            $('#modalRate').val('190.00');
            $('#modalKRW').val('');
            $('#modalCNY').val('');
            $('#modalfeeKRW').val('');
            $('#modalKRWTotal').val('');
        }
        $('#sendModal').show();
        // 모달이 열린 후 모든 송금 정보를 가져와서 합계 계산
        loadAllSendInfo(num);
    }, 'json');
});
// 환율 입력란: 소수점 없이 정수만 입력
// $(document).on('input', '#modalRate', function() {
//     this.value = this.value.replace(/[^0-9]/g, '');
// });
// 숫자 입력 input에 자동 콤마
$(document).on('input', '.number-comma', function() {
    // 숫자와 소수점만 허용
    var val = this.value.replace(/[^0-9.]/g, '');
    
    // 소수점이 두 개 이상 들어가는 것을 방지
    val = val.replace(/(\..*)\./g, '$1');
    
    if(val) {
        // 소수점 기준으로 나누기
        var parts = val.split('.');
        
        // 정수 부분에만 콤마 추가
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        
        // 다시 합치기
        this.value = parts.join('.');
    } else {
        this.value = '';
    }
});
// 닫기
$('#modalCloseBtn').on('click', function() {
    $('#sendModal').hide();
});
// 원화 금액 입력 시 합계 자동 계산
$('#modalKRW, #modalfeeKRW').on('input', function() {
    calculateKRWTotal();
});

// 송금 정보 캐시
var sendInfoCache = {};

// 원화 누적금액 합계 (송금수수료 제외) 계산 함수
function calculateKRWTotal() {
    var currentKRW = parseInt($('#modalKRW').val().replace(/[^0-9.]/g, '')) || 0;    
    var round = parseInt($('#modalRound').val()) || 1;
    var num = $('#modalNum').val();
    
    var totalKRW = 0;
    
    // 1차~4차 송금액을 순회하며 합계 계산
    for(var i = 1; i <= 4; i++) {
        if(i == round) {
            // 현재 입력 중인 차수는 현재 입력값 사용
            totalKRW += currentKRW;
        } else {
            // 다른 차수는 캐시된 값 사용
            var existingKRW = getExistingKRWAmount(num, i);
            totalKRW += existingKRW;
        }
    }
    
    $('#modalKRWTotal').val(numberWithCommas(totalKRW));
}

// 기존 저장된 원화 금액 가져오기 (캐시 사용)
function getExistingKRWAmount(num, round) {
    var cacheKey = num + '_' + round;
    if(sendInfoCache[cacheKey] && sendInfoCache[cacheKey].krw) {
        return parseInt(sendInfoCache[cacheKey].krw.replace(/[^0-9]/g, '')) || 0;
    }
    return 0;
}

// 모든 송금 정보를 한 번에 가져오기
function loadAllSendInfo(num) {
    sendInfoCache = {};
    var promises = [];
    
    for(var i = 1; i <= 4; i++) {
        promises.push(
            $.get('get_send_info.php', {num: num, round: i})
        );
    }
    
    $.when.apply($, promises).done(function() {
        for(var i = 0; i < arguments.length; i++) {
            var res = arguments[i][0];
            var round = i + 1;
            var cacheKey = num + '_' + round;
            
            if(res.success && res.data) {
                sendInfoCache[cacheKey] = res.data;
            }
        }
        
        // 모든 정보를 가져온 후 합계 계산
        calculateKRWTotal();
    });

    calculateKRWTotal();
}

// 저장
$('#sendForm').on('submit', function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
    $.post('save_send_info.php', formData, function(res) {
        if(res.success) {
            alert('저장되었습니다.');
            $('#sendModal').hide();
            location.reload(); // 또는 해당 row만 갱신
        } else {
            alert('저장 실패: ' + res.message);
        }
    }, 'json');
});

// 통관비용 td 클릭 이벤트
$(document).on('click', '.customs-td', function() {
    var num = $(this).data('num');
    var round = $(this).data('round');
    $('#customsNum').val(num);
    $('#customsRound').val(round);
    $('#customsModalTitle').text(round + '차 통관비용 입력');
    // 기존 값 불러오기
    $.get('get_customs_info.php', {num, round}, function(res){
        if(res.success && res.data){
            // 통관일자가 없으면 오늘 날짜로
            var dateVal = res.data.date;
            if(!dateVal) {
                var today = new Date();
                var yyyy = today.getFullYear();
                var mm = String(today.getMonth() + 1).padStart(2, '0');
                var dd = String(today.getDate()).padStart(2, '0');
                dateVal = yyyy + '-' + mm + '-' + dd;
            }
            $('#customsDate').val(dateVal);
            $('#customsVat').val(res.data.vat ? numberWithCommas(res.data.vat) : '');
            $('#customsEtc').val(res.data.etc ? numberWithCommas(res.data.etc) : '');
            $('#customsContainer').val(res.data.container ? numberWithCommas(res.data.container) : '');
            $('#customsFee').val(res.data.fee ? numberWithCommas(res.data.fee) : '');
            $('#customsTotal').val(res.data.total ? numberWithCommas(res.data.total) : '');
            $('#customsInputAmountCNY').val(res.data.input_amount_cny ? numberWithCommas(res.data.input_amount_cny) : '');
        } else {
            // 통관일자가 없으면 오늘 날짜로
            var today = new Date();
            var yyyy = today.getFullYear();
            var mm = String(today.getMonth() + 1).padStart(2, '0');
            var dd = String(today.getDate()).padStart(2, '0');
            var dateVal = yyyy + '-' + mm + '-' + dd;
            $('#customsDate').val(dateVal);
            $('#customsVat').val('');
            $('#customsEtc').val('');
            $('#customsContainer').val('');
            $('#customsFee').val('');
            $('#customsTotal').val('');
            $('#customsInputAmountCNY').val('');
        }
        $('#customsModal').show();
    }, 'json');
});

// 입력값이 바뀔 때 합계 자동 계산
$('#customsVat, #customsEtc, #customsContainer, #customsFee').on('input', function() {
    var vat = parseInt($('#customsVat').val().replace(/[^0-9]/g, '')) || 0;
    var etc = parseInt($('#customsEtc').val().replace(/[^0-9]/g, '')) || 0;
    var container = parseInt($('#customsContainer').val().replace(/[^0-9]/g, '')) || 0;
    var fee = parseInt($('#customsFee').val().replace(/[^0-9]/g, '')) || 0;
    var total = vat + etc + container + fee;
    $('#customsTotal').val(numberWithCommas(total));
});

// 닫기
$('#customsModalCloseBtn').on('click', function() {
    $('#customsModal').hide();
});

// 저장
$('#customsForm').on('submit', function(e) {
    e.preventDefault();

    // 저장중 UI 표시
    Swal.fire({
        title: '저장중입니다...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    var formData = $(this).serialize();
    $.post('save_customs_info.php', formData, function(res) {
        if(res.success) {
            Swal.fire({
                icon: 'success',
                title: '저장되었습니다.',
                showConfirmButton: false,
                timer: 1200
            });
            $('#customsModal').hide();
            setTimeout(function() {
                location.reload();
            }, 1200);
        } else {
            Swal.fire({
                icon: 'error',
                title: '저장 실패',
                text: res.message || '저장에 실패했습니다.'
            });
        }
    }, 'json');
});

// 콤마 함수
function numberWithCommas(x) {
    if(!x) return '';
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

</script>
</body>
</html>