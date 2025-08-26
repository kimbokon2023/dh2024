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
<style>
.order-date-td {
    cursor: pointer;
    color: #007bff !important;
    text-decoration: underline;
    transition: all 0.2s ease;
}

.order-date-td:hover {
    background-color: #f8f9fa !important;
    color: #0056b3 !important;
    text-decoration: none;
}

.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.modal-xl {
    max-width: 90%;
}

@media (max-width: 768px) {
    .modal-xl {
        max-width: 95%;
    }
}

/* 검색 타입 선택 스타일 */
.search-type-container {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
}

.search-type-container input[type="radio"] {
    margin-right: 5px;
}

.search-type-container label {
    cursor: pointer;
    font-weight: 500;
}

/* 동적 검색 컨트롤 스타일 */
.year-select, .month-select, .period-select {
    display: none;
    min-width: 200px;
}

.year-select select, .month-select input, .period-select .d-flex {
    width: 100%;
}

/* 검색 결과 개수 스타일 */
.badge.bg-primary {
    font-size: 1rem !important;
    padding: 0.5rem 1rem;
}
</style>
</head>
<body>    
<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php'); ?>   

<?php
$tablename = 'm_order'; 

$search = $_REQUEST['search'] ?? '';  
$search_type = $_REQUEST['search_type'] ?? 'period'; // 기본값은 기간별
$selected_year = $_REQUEST['selected_year'] ?? date('Y');
$selected_month = $_REQUEST['selected_month'] ?? date('Y-m');
$fromdate = $_REQUEST['fromdate'] ?? '';  
$todate = $_REQUEST['todate'] ?? '';  
$mode = $_REQUEST['mode'] ?? '';  
 
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

// 현재 날짜
$currentDate = date("Y-m-d");

// 검색 타입에 따른 날짜 설정
if ($search_type === 'year') {
    // 연도별 검색
    $fromdate = $selected_year . "-01-01";
    $todate = $selected_year . "-12-31";
} elseif ($search_type === 'month') {
    // 월별 검색
    $fromdate = $selected_month . "-01";
    $todate = date("Y-m-t", strtotime($selected_month . "-01"));
} else {
    // 기간별 검색 (기본값)
    if ($fromdate === "" || $fromdate === null || $todate === "" || $todate === null) {
        $fromdate = date("2024-01-01"); 
        $todate = date("Y-m-d", strtotime("+2 months", strtotime($currentDate))); // 2개월 후 날짜
    }
}

$Transtodate = $todate;

// 연도 옵션 생성 (현재년도 + 과거 3년)
$current_year = date('Y');
$year_options = '';
for ($i = 0; $i < 4; $i++) {
    $year = $current_year - $i;
    $selected = ($year == $selected_year) ? 'selected' : '';
    $year_options .= "<option value='$year' $selected>" . $year . "년</option>";
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
<form id="board_form" name="board_form" method="post" >         
<div class="container">      
    <div class="card mb-2 mt-2 ">  
        <div class="card-body">       
            <div class="card-header d-flex justify-content-center align-items-center">   
                <span class="text-center fs-5">  <?=$title_message?>   </span>     
				<button type="button" class="btn btn-dark btn-sm mx-1" onclick='location.reload()'>  <i class="bi bi-arrow-clockwise"></i> </button>      						 
				<small class="ms-5 text-muted"> 중국 발주서별 송금내역과 통관내역을 등록합니다. </small>              
				<button type="button" class="btn btn-primary btn-sm mx-3"  onclick='location.href="list_input.php";' title="발주 입고창 이동" >  <i class="bi bi-list-columns"></i> </button>  	   		  						
				<?php if(intval($level) === 1) : ?>
					<button type="button" class="btn btn-danger btn-sm "  onclick='location.href="list_account.php";' title="송금액 이동" >  <i class="bi bi-currency-dollar"></i> </button>  	   		  										
				<?php endif; ?>					
            </div>    
            <div class="d-flex mt-1 mb-2 justify-content-center align-items-center">         
				<div class="alert alert-primary mx-3 w-50 text-center p-1" role="alert">
					단가, 금액은 (CNY) 위엔화 기준, 소수점 둘째자리까지 표시 
				</div>	
            </div>    			
			
			<!-- 발주일자별 요약 버튼 -->
			<div class="d-flex justify-content-center align-items-center mt-1 mb-3">
				<button type="button" class="btn btn-info btn-sm" id="summaryBtn">
					<i class="bi bi-calendar-check"></i> 발주일자별 요약보기
				</button>
			</div>	

            <!-- 검색 결과 개수 -->
            <div class="d-flex mt-1 mb-1 justify-content-center align-items-center">       
               <span class="text-center text-primary fs-6">▷ <?= $total_row ?> 건</span>
            </div>

            <!-- 검색 타입 선택 -->
            <div class="row justify-content-center mb-3">
                <div class="col-auto">
                    <div class="search-type-container">
                        <label class="me-3">
                            <input type="radio" name="search_type" value="year" <?= $search_type === 'year' ? 'checked' : '' ?> onchange="toggleSearchTypeAndSubmit()"> 연도별
                        </label>
                        <label class="me-3">
                            <input type="radio" name="search_type" value="month" <?= $search_type === 'month' ? 'checked' : '' ?> onchange="toggleSearchTypeAndSubmit()"> 월별
                        </label>
                        <label>
                            <input type="radio" name="search_type" value="period" <?= $search_type === 'period' ? 'checked' : '' ?> onchange="toggleSearchTypeAndSubmit()"> 기간별
                        </label>
                    </div>
                </div>
            </div>

            <!-- 동적 검색 컨트롤 -->
            <div class="row justify-content-center mb-3">
                <div class="col-auto">
                    <!-- 연도별 검색 -->
                    <div class="year-select">
                        <select id="selected_year" name="selected_year" class="form-select form-select-sm" onchange="autoSubmit()">
                            <?= $year_options ?>
                        </select>
                    </div>

                    <!-- 월별 검색 -->
                    <div class="month-select">
                        <input type="month" id="selected_month" name="selected_month" class="form-control" value="<?=$selected_month?>" onchange="autoSubmit()">
                    </div>

                    <!-- 기간별 검색 -->
                    <div class="period-select">
                        <div class="d-flex align-items-center">
                            <input type="date" id="fromdate" name="fromdate" class="form-control me-2" value="<?=$fromdate?>">
                            <span class="me-2">~</span>
                            <input type="date" id="todate" name="todate" class="form-control" value="<?=$todate?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 검색어 입력 및 검색 버튼 -->
            <div class="row justify-content-center mb-3">
                <div class="col-auto">
                    <div class="d-flex align-items-center">
                        <div class="inputWrap">
                            <input type="text" id="search" name="search" value="<?=$search?>" onkeydown="JavaScript:SearchEnter();" autocomplete="off" class="form-control" style="width:150px;" placeholder="검색어 입력">            
                            <button class="btnClear"></button>
                        </div>                
                        <div id="autocomplete-list"></div>    
                        &nbsp;
                        <button id="searchBtn" type="button" class="btn btn-dark btn-sm"> <i class="bi bi-search"></i> 검색 </button>                          
                    </div>
                </div>
            </div>
        </div> <!--end of card-body-->
    </div> <!--end of card -->   
</div> <!-- end of container -->   
</form>    

<div class="container-fluid mt-1 mb-3 w-90">    
<div class="table-responsive">     
<table class="table table-bordered table-hover" >
  <thead class="table-danger">
    <tr>
      <th class="text-center w110px">발주일자</th>
      <th class="text-end">발주금액(CNY)</th>
      <th class="text-end">1차송금(CNY)</th>
      <th class="text-end">2차송금(CNY)</th>
      <th class="text-end">3차송금(CNY)</th>
      <th class="text-end">4차송금(CNY)</th>
      <th class="text-end">5차송금(CNY)</th>
      <th class="text-end">6차송금(CNY)</th>
      <th class="text-end">7차송금(CNY)</th>
      <th class="text-end">잔액(CNY)</th>
      <th class="text-end">1차 통관비용(원)</th>
      <th class="text-end">2차 통관비용(원)</th>
      <th class="text-end">3차 통관비용(원)</th>
      <th class="text-end">4차 통관비용(원)</th>
      <th class="text-end">5차 통관비용(원)</th>        
      <th class="text-end">6차 통관비용(원)</th>
      <th class="text-end">7차 통관비용(원)</th>
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
        $input5      = 0;
        $input6      = 0;
        $input7      = 0;

        if (is_array($decoded)) {
            foreach ($decoded as $item) {
                // 1) 발주 금액(col6) 합계
                $amt = str_replace(',', '', ($item['col6'] ?? '0'));
                $amt_float = is_numeric($amt) ? (float)$amt : 0;
                $totalamount += $amt_float;                
            }

            // 디버그 정보 출력
            // echo $debug_output;
        }

        // 미입고 금액과 잔액 계산
        $미입고금액 = round($totalamount - $inputSum, 2);
        $send1 = (float)str_replace(',', '', ($sendMoney1 ?? 0));
        $send2 = (float)str_replace(',', '', ($sendMoney2 ?? 0));
        $send3 = (float)str_replace(',', '', ($sendMoney3 ?? 0));
        $send4 = (float)str_replace(',', '', ($sendMoney4 ?? 0));
        $send5 = (float)str_replace(',', '', ($sendMoney5 ?? 0));
        $send6 = (float)str_replace(',', '', ($sendMoney6 ?? 0));
        $send7 = (float)str_replace(',', '', ($sendMoney7 ?? 0));
        $잔액    = $totalamount - ($send1 + $send2 + $send3 + $send4 + $send5 + $send6 + $send7);

        // 각 차수별 통관비용 합계 
        // 부가세
        $customs_detail_total1 = (int)str_replace(',', '', ($customs_vat1 ?? 0));
        $customs_detail_total2 = (int)str_replace(',', '', ($customs_vat2 ?? 0));
        $customs_detail_total3 = (int)str_replace(',', '', ($customs_vat3 ?? 0));
        $customs_detail_total4 = (int)str_replace(',', '', ($customs_vat4 ?? 0));
        $customs_detail_total5 = (int)str_replace(',', '', ($customs_vat5 ?? 0));
        $customs_detail_total6 = (int)str_replace(',', '', ($customs_vat6 ?? 0));
        $customs_detail_total7 = (int)str_replace(',', '', ($customs_vat7 ?? 0));

        // 선임 및 부대비용
        $customs_detail_total1 += (int)str_replace(',', '', ($customs_miscellaneous_fee1 ?? 0));
        $customs_detail_total2 += (int)str_replace(',', '', ($customs_miscellaneous_fee2 ?? 0));
        $customs_detail_total3 += (int)str_replace(',', '', ($customs_miscellaneous_fee3 ?? 0));
        $customs_detail_total4 += (int)str_replace(',', '', ($customs_miscellaneous_fee4 ?? 0));
        $customs_detail_total5 += (int)str_replace(',', '', ($customs_miscellaneous_fee5 ?? 0));
        $customs_detail_total6 += (int)str_replace(',', '', ($customs_miscellaneous_fee6 ?? 0));
        $customs_detail_total7 += (int)str_replace(',', '', ($customs_miscellaneous_fee7 ?? 0));

        // 컨테이너운송비용
        $customs_detail_total1 += (int)str_replace(',', '', ($customs_container_fee1 ?? 0));
        $customs_detail_total2 += (int)str_replace(',', '', ($customs_container_fee2 ?? 0));
        $customs_detail_total3 += (int)str_replace(',', '', ($customs_container_fee3 ?? 0));
        $customs_detail_total4 += (int)str_replace(',', '', ($customs_container_fee4 ?? 0));
        $customs_detail_total5 += (int)str_replace(',', '', ($customs_container_fee5 ?? 0));
        $customs_detail_total6 += (int)str_replace(',', '', ($customs_container_fee6 ?? 0));
        $customs_detail_total7 += (int)str_replace(',', '', ($customs_container_fee7 ?? 0));

        // 통관수수료
        $customs_detail_total1 += (int)str_replace(',', '', ($customs_commission1 ?? 0));
        $customs_detail_total2 += (int)str_replace(',', '', ($customs_commission2 ?? 0));
        $customs_detail_total3 += (int)str_replace(',', '', ($customs_commission3 ?? 0));
        $customs_detail_total4 += (int)str_replace(',', '', ($customs_commission4 ?? 0));
        $customs_detail_total5 += (int)str_replace(',', '', ($customs_commission5 ?? 0));
        $customs_detail_total6 += (int)str_replace(',', '', ($customs_commission6 ?? 0));
        $customs_detail_total7 += (int)str_replace(',', '', ($customs_commission7 ?? 0));

        // 테이블 행 출력
        echo '<tr style="cursor:pointer;" data-num="' . $num . '">';
        echo '<td class="text-center order-date-td" data-num="' . $num . '" data-orderdate="' . htmlspecialchars($orderDate) . '">' . htmlspecialchars($orderDate) . '</td>';
        echo '<td class="text-end">' . number_format($totalamount,2) . '</td>';
        echo '<td class="text-end send-td" data-round="1" data-num="' . $num . '">' . (is_numeric($send1) && $send1 != 0 ? number_format($send1, 2) : '') . '</td>';
        echo '<td class="text-end send-td" data-round="2" data-num="' . $num . '">' . (is_numeric($send2) && $send2 != 0 ? number_format($send2, 2) : '') . '</td>';
        echo '<td class="text-end send-td" data-round="3" data-num="' . $num . '">' . (is_numeric($send3) && $send3 != 0 ? number_format($send3, 2) : '') . '</td>';
        echo '<td class="text-end send-td" data-round="4" data-num="' . $num . '">' . (is_numeric($send4) && $send4 != 0 ? number_format($send4, 2) : '') . '</td>';
        echo '<td class="text-end send-td" data-round="5" data-num="' . $num . '">' . (is_numeric($send5) && $send5 != 0 ? number_format($send5, 2) : '') . '</td>';
        echo '<td class="text-end send-td" data-round="6" data-num="' . $num . '">' . (is_numeric($send6) && $send6 != 0 ? number_format($send6, 2) : '') . '</td>';
        echo '<td class="text-end send-td" data-round="7" data-num="' . $num . '">' . (is_numeric($send7) && $send7 != 0 ? number_format($send7, 2) : '') . '</td>';
        echo '<td class="text-end">' . (is_numeric($잔액) && $잔액 != 0 ? number_format($잔액, 2) : '') . '</td>';
        echo '<td class="text-end customs-td" data-round="1" data-num="' . $num . '">' . (is_numeric($customs_detail_total1) && $customs_detail_total1 != 0 ? number_format($customs_detail_total1) : '') . '</td>';
        echo '<td class="text-end customs-td" data-round="2" data-num="' . $num . '">' . (is_numeric($customs_detail_total2) && $customs_detail_total2 != 0 ? number_format($customs_detail_total2) : '') . '</td>';
        echo '<td class="text-end customs-td" data-round="3" data-num="' . $num . '">' . (is_numeric($customs_detail_total3) && $customs_detail_total3 != 0 ? number_format($customs_detail_total3) : '') . '</td>';
        echo '<td class="text-end customs-td" data-round="4" data-num="' . $num . '">' . (is_numeric($customs_detail_total4) && $customs_detail_total4 != 0 ? number_format($customs_detail_total4) : '') . '</td>';
        echo '<td class="text-end customs-td" data-round="5" data-num="' . $num . '">' . (is_numeric($customs_detail_total5) && $customs_detail_total5 != 0 ? number_format($customs_detail_total5) : '') . '</td>';
        echo '<td class="text-end customs-td" data-round="6" data-num="' . $num . '">' . (is_numeric($customs_detail_total6) && $customs_detail_total6 != 0 ? number_format($customs_detail_total6) : '') . '</td>';
        echo '<td class="text-end customs-td" data-round="7" data-num="' . $num . '">' . (is_numeric($customs_detail_total7) && $customs_detail_total7 != 0 ? number_format($customs_detail_total7) : '') . '</td>';
        
        // 안전하게 합산 (is_numeric 체크)
        $fee1 = is_numeric($customs_detail_total1) ? $customs_detail_total1 : '';
        $fee2 = is_numeric($customs_detail_total2) ? $customs_detail_total2 : '';
        $fee3 = is_numeric($customs_detail_total3) ? $customs_detail_total3 : '';
        $fee4 = is_numeric($customs_detail_total4) ? $customs_detail_total4 : '';
        $fee5 = is_numeric($customs_detail_total5) ? $customs_detail_total5 : '';
        $fee6 = is_numeric($customs_detail_total6) ? $customs_detail_total6 : '';
        $fee7 = is_numeric($customs_detail_total7) ? $customs_detail_total7 : '';
        $fee_sum = $fee1 + $fee2 + $fee3 + $fee4 + $fee5 + $fee6 + $fee7;
        echo '<td class="text-end">' . ($fee_sum > 0 ? number_format($fee_sum) : '') . '</td>';
        // 콤마 제거 후 숫자 변환
        $customs_input_amount_cny1 = str_replace(',', '', $customs_input_amount_cny1);
        $customs_input_amount_cny2 = str_replace(',', '', $customs_input_amount_cny2); 
        $customs_input_amount_cny3 = str_replace(',', '', $customs_input_amount_cny3);
        $customs_input_amount_cny4 = str_replace(',', '', $customs_input_amount_cny4);
        $customs_input_amount_cny5 = str_replace(',', '', $customs_input_amount_cny5);
        $customs_input_amount_cny6 = str_replace(',', '', $customs_input_amount_cny6);
        $customs_input_amount_cny7 = str_replace(',', '', $customs_input_amount_cny7);

        // 숫자 체크 및 형변환
        $customs_input_amount_cny1 = is_numeric($customs_input_amount_cny1) ? (float)$customs_input_amount_cny1 : 0;
        $customs_input_amount_cny2 = is_numeric($customs_input_amount_cny2) ? (float)$customs_input_amount_cny2 : 0;
        $customs_input_amount_cny3 = is_numeric($customs_input_amount_cny3) ? (float)$customs_input_amount_cny3 : 0;
        $customs_input_amount_cny4 = is_numeric($customs_input_amount_cny4) ? (float)$customs_input_amount_cny4 : 0;
        $customs_input_amount_cny5 = is_numeric($customs_input_amount_cny5) ? (float)$customs_input_amount_cny5 : 0;
        $customs_input_amount_cny6 = is_numeric($customs_input_amount_cny6) ? (float)$customs_input_amount_cny6 : 0;
        $customs_input_amount_cny7 = is_numeric($customs_input_amount_cny7) ? (float)$customs_input_amount_cny7 : 0;

        $customs_input_amount_cny_sum = $customs_input_amount_cny1 + $customs_input_amount_cny2 + $customs_input_amount_cny3 + $customs_input_amount_cny4 + $customs_input_amount_cny5 + $customs_input_amount_cny6 + $customs_input_amount_cny7;
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
</div>+
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

<!-- 발주일자별 요약 모달 -->
<div class="modal fade" id="orderSummaryModal" tabindex="-1" aria-labelledby="orderSummaryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="orderSummaryModalLabel">발주일자별 요약</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="orderSummaryContent">
          <!-- 동적으로 내용이 로드됩니다 -->
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
      </div>
    </div>
  </div>
</div>

<div class="container-fluid mt-3 mb-3">
    <? include '../footer_sub.php'; ?>
</div>

<script>
// 페이지 로딩
$(document).ready(function(){    
    var loader = document.getElementById('loadingOverlay');
	if(loader)
		loader.style.display = 'none';
    
    // 검색 타입에 따른 초기 컨트롤 표시
    toggleSearchType();
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
        $("#board_form").submit();
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
        $("#board_form").submit(); 
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

// 송금내역 입력 모달 열기
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
    for(var i = 1; i <= 7; i++) {
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

// 송금내역 입력 모든 송금 정보를 한 번에 가져오기
function loadAllSendInfo(num) {
    sendInfoCache = {};
    var promises = [];
    
    for(var i = 1; i <= 7; i++) {
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
$(document).off('click', '.customs-td').on('click', '.customs-td', function() {
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

// 발주일자별 요약 모달 관련 함수들
$(document).ready(function(){
    // 발주일자별 요약 버튼 클릭 이벤트
    $('#summaryBtn').on('click', function() {
        loadOrderSummary();
    });
    
    // 발주일자 셀 클릭 이벤트
    $(document).on('click', '.order-date-td', function(e) {
        e.stopPropagation(); // 이벤트 버블링 방지
        var num = $(this).data('num');
        var orderDate = $(this).data('orderdate');
        loadOrderSummaryByDate(orderDate, num);
    });
});

// 발주일자별 요약 데이터 로드
function loadOrderSummary() {
    $('#orderSummaryModalLabel').text('발주일자별 요약');
    $('#orderSummaryContent').html('<div class="text-center"><i class="bi bi-arrow-clockwise spin"></i> 로딩중...</div>');
    
    var fromdate = $('#fromdate').val();
    var todate = $('#todate').val();
    
    $.get('get_order_summary.php', {
        fromdate: fromdate,
        todate: todate
    }, function(res) {
        if(res.success) {
            $('#orderSummaryContent').html(res.html);
        } else {
            $('#orderSummaryContent').html('<div class="alert alert-danger">데이터를 불러오는데 실패했습니다.</div>');
        }
        var modal = new bootstrap.Modal(document.getElementById('orderSummaryModal'));
        modal.show();
    }, 'json');
}

// 특정 발주일자 요약 데이터 로드
function loadOrderSummaryByDate(orderDate, num) {
    $('#orderSummaryModalLabel').text('발주일자: ' + orderDate + ' 요약');
    $('#orderSummaryContent').html('<div class="text-center"><i class="bi bi-arrow-clockwise spin"></i> 로딩중...</div>');
    
    $.get('get_order_summary.php', {
        orderDate: orderDate,
        num: num
    }, function(res) {
        if(res.success) {
            $('#orderSummaryContent').html(res.html);
        } else {
            $('#orderSummaryContent').html('<div class="alert alert-danger">데이터를 불러오는데 실패했습니다.</div>');
        }
        var modal = new bootstrap.Modal(document.getElementById('orderSummaryModal'));
        modal.show();
    }, 'json');
}

// 검색 타입에 따른 동적 컨트롤 표시/숨김
function toggleSearchType() {
    var searchType = $('input[name="search_type"]:checked').val();
    
    // 모든 검색 컨트롤 숨기기
    $('.year-select, .month-select, .period-select').hide();
    
    // 선택된 타입에 따라 해당 컨트롤만 표시
    if (searchType === 'year') {
        $('.year-select').show();
    } else if (searchType === 'month') {
        $('.month-select').show();
    } else if (searchType === 'period') {
        $('.period-select').show();
    }
}

// 검색 타입 변경 시 자동 제출
function toggleSearchTypeAndSubmit() {
    toggleSearchType();
    // 검색 타입 변경 시 자동으로 폼 제출
    setTimeout(function() {
        $("#board_form").submit();
    }, 100);
}

// 연도/월 변경 시 자동 제출
function autoSubmit() {
    setTimeout(function() {
        $("#board_form").submit();
    }, 100);
}

</script>
</body>
</html>