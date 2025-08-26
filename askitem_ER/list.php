<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/session.php'; // 세션 파일 포함
require_once($_SERVER['DOCUMENT_ROOT'] . '/lib/mydb.php');

$title_message = '지출결의서';      
$mode = $_REQUEST["mode"] ?? '';
$search = $_REQUEST["search"] ?? '';
$category_filter = $_REQUEST["category_filter"] ?? ''; 

// 사용자 레벨과 이름 확인
$user_level = $_SESSION["level"] ?? '';
$user_name = $_SESSION["name"] ?? '';
$is_admin = ($user_level == "1" || $user_level == 1);

// echo 'user_level: ' . $user_level . '<br>';
// echo 'user_name: ' . $user_name . '<br>';
// echo 'is_admin: ' . $is_admin . '<br>';

// 카테고리 파일 경로
$categoryFile = $_SERVER['DOCUMENT_ROOT'] . '/askitem_ER/category.json';

// 카테고리 로드 함수
function loadCategories($file) {
    $defaultCategories = [
        '식비',
        '운반비', 
        '자재비',
        '차량유지비',
        '기타'
    ];
    
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $categories = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($categories)) {
            return $categories;
        }
    }
    
    return $defaultCategories;
}

// 카테고리 목록 가져오기
$categories = loadCategories($categoryFile);

?> 

<?php include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php'; ?> 
 
<title> <?=$title_message?>  </title>  

<style>
    #showextract {
        display: inline-block;
        position: relative;
    }
            
    .showextractframe {
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
		#category_filter {
            font-size: 22px;
            width: 150px !important;
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
    // 현재 달의 첫날
    $fromdate = date('Y-m-01');
    // 현재 달의 마지막 날
    $todate = date('Y-m-t');
    $Transtodate = $todate;
} else {
    // fromdate와 todate가 모두 설정된 경우 (기존 로직 유지)
    $Transtodate = $todate;
}
			  
// 법인카드 목록 가져오기
$jsonFile = $_SERVER['DOCUMENT_ROOT'] . '/account/cardlist.json';
$cards = [];
if (file_exists($jsonFile)) {
    $jsonContent = file_get_contents($jsonFile);
    $cards = json_decode($jsonContent, true);
    if (!is_array($cards)) {
        $cards = [];
    }
}

// 사용자 이름으로 카드번호 찾기 함수
function getUserCards($userName, $cardList) {
    $userCards = [];
    foreach ($cardList as $card) {
        if (isset($card['user']) && $card['user'] === $userName) {
            $userCards[] = $card['number'];
        }
    }
    return $userCards;
}

// 사용자 카드번호 목록 가져오기 (관리자가 아닌 경우)
$userCardNumbers = [];
if (!$is_admin && !empty($user_name)) {
    // 사용자 이름이 포함된 모든 카드 찾기
    $userCardNumbers = [];
    foreach ($cards as $card) {
        if (isset($card['user']) && strpos($card['user'], $user_name) !== false) {
            $userCardNumbers[] = $card['number'];
        }
    }
}

//echo 'userCardNumbers: ' . implode(', ', $userCardNumbers) . '<br>';

// 법인카드 정보를 포맷팅하는 함수
function formatCompanyCard($companyCard, $cardList) {
    if (empty($companyCard)) {
        return '';
    }
    
    // JSON 배열에서 해당 카드번호 찾기
    foreach ($cardList as $card) {
        if (isset($card['number']) && strpos($card['number'], $companyCard) !== false) {
            // 카드번호 끝 4자리 추출
            $lastThree = substr($card['number'], -8, 3);
            return "({$card['company']}) {$lastThree} ({$card['user']})";
        }
    }
    
    // 매칭되는 카드가 없으면 원본 반환
    return $companyCard;
}

$SettingDate = "indate";

$Andis_deleted = " AND (is_deleted IS NULL or is_deleted='0') AND eworks_item='지출결의서'  ";
$Whereis_deleted = " WHERE (is_deleted IS NULL or is_deleted='0') AND eworks_item='지출결의서'  ";

// 사용자 레벨에 따른 추가 조건
$userCondition = "";
if (!$is_admin && !empty($user_name)) {
    if (!empty($userCardNumbers)) {
        $cardConditions = [];
        foreach ($userCardNumbers as $cardNo) {
            $cardConditions[] = "companyCard LIKE '%" . trim($cardNo) . "%'";
        }
        if (!empty($cardConditions)) {
            $userCondition = " AND (" . implode(" OR ", $cardConditions) . ")";
        }
    } else {
        // 사용자 카드가 없으면 빈 결과
        $userCondition = " AND 1=0";
    }
}

$common = " WHERE " . $SettingDate . " BETWEEN '$fromdate' AND '$Transtodate' " . $Andis_deleted . $userCondition . " ORDER BY ";

$a = $common . " requestpaymentdate DESC, indate DESC "; // 지출요청일자 내림차순 전체

$sql="select * from ".$DB.".eworks " . $a; 	


// ——————————————————————————————
// (1) 카드번호별 사용 합계 계산
// ——————————————————————————————
$cardStats    = [];  // ['카드번호' => 총합]
$totalCardSum = 0;

try {
    // eworks 테이블에서 모든 지출결의서의 법인카드별 비용 합계 가져오기
    // $sql = "SELECT companyCard, suppliercost
    //           FROM {$DB}.eworks
    //          WHERE (is_deleted = '0' or is_deleted is null) 
    //            AND suppliercost IS NOT NULL
    //            AND suppliercost != '' AND eworks_item='지출결의서'
    //       ";
    $st = $pdo->query($sql);
    while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
        $cardNo = trim($r['companyCard']);
        // suppliercost 필드에 콤마가 들어 있을 수 있으니 제거 후 숫자로
        $amt = floatval(str_replace(',', '', $r['suppliercost']));
        if ($cardNo === '') continue;
        if (!isset($cardStats[$cardNo])) {
            $cardStats[$cardNo] = 0;
        }
        $cardStats[$cardNo] += $amt;
        $totalCardSum        += $amt;
    }
} catch (PDOException $e) {
    // 계산 중 오류 발생 시 무시
}

// 카드 목록(json)에 없는 카드번호가 쌓여 있을 수도 있으니, 정렬을 위해 키 가져오기
// 카드번호 목록만 뽑아낼 때
$allCardNumbers = array_map(
    function($c) {
        // PHP 7.0 이상부터 ?? 연산자는 사용 가능하므로 그대로 두셔도 됩니다.
        return isset($c['number']) ? $c['number'] : '';
    },
    $cards
);

// 관리자가 아닌 경우 사용자 카드만 필터링
if (!$is_admin) {
    $allCardNumbers = array_intersect($allCardNumbers, $userCardNumbers);
}

// 정렬
sort($allCardNumbers);


// 검색을 위해 모든 검색변수 공백제거
$search = str_replace(' ', '', $search);    
  
if($mode=="search"){
	  if($search=="" && $category_filter==""){			  
				$sql="select * from {$DB}.eworks " . $a; 										
			   }
		 elseif($search!="" || $category_filter!="") { 			    
			  $sql ="select * from {$DB}.eworks where (";
			  
			  // 검색어 조건
			  if($search!="") {
				  $sql .= "(outdate like '%$search%')  or (replace(outworkplace,' ','') like '%$search%' ) ";
				  $sql .="or (steel_item like '%$search%') or (spec like '%$spec%') or (JSON_EXTRACT(expense_data, '$[*].expense_item') like '%$search%')  or (first_writer like '%$search%') or (payment like '%$search%')   or (supplier like '%$search%') or (request_comment like '%$search%') ";
			  }
			  
			  // 카테고리 필터 조건
			  if($category_filter!="") {
				  if($search!="") {
					  $sql .= " AND ";
				  }
				  $sql .= "JSON_EXTRACT(expense_data, '$[*].expense_category') LIKE '%$category_filter%'";
			  }
			  
			  $sql .= ") " . $Andis_deleted . $userCondition . " order by num desc  ";										 								
			}
	   }
if($mode=="") {
   $sql="select * from {$DB}.eworks " . $a; 						                         
}						            
$nowday=date("Y-m-d");   // 현재일자 변수지정   
$dateCon =" AND between date('$fromdate') and date('$Transtodate') " ;
// mysql 8.0이라서 된다.
//(JSON_EXTRACT(expense_data, '$[*].expense_item') like '%$search%')   
try{  
	$stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh
	$total_row=$stmh->rowCount();	
//echo $sql;
?>

<form name="board_form" id="board_form"  method="post" action="list.php?mode=search">  
	<input type="hidden" id="tablename" name="tablename" value="<?=$tablename?>" >							
<div class="container-fluid">  	
		<div class="card mt-2">
			<div class="card-body">
				<div class="d-flex mb-3 mt-2 justify-content-center align-items-center">  
					<h4> <?=$title_message?> </h4>  
					<button type="button" class="btn btn-dark btn-sm mx-3"  onclick='location.reload();' title="새로고침"> <i class="bi bi-arrow-clockwise"></i> </button>  	 			
                    <small class="ms-5 text-muted">  카드사용 내역, 금액 기재하고 영수증 사진 첨부하여 저장 (사용카드번호 확인) </small>  
				</div>	                                
                <!-- ======================================= -->
                <!--  (2) 법인카드별 사용 합계 통계 테이블 -->
                <!-- ======================================= -->
                <div class="d-flex justify-content-center align-items-center mb-1"> 
                    <span class="text-success small mx-5"> ✔ 각 열을 누르면 상세내역이 나옵니다. </span>
                    <?php if (!$is_admin): ?>
                        <span class="text-warning small mx-2"> 🔒 본인 카드만 표시됩니다. </span>
                    <?php endif; ?>
                </div>
            
                <div class="d-flex justify-content-center align-items-center"> 
                    <table class="table table-bordered table-sm text-center w-75" id="cardStatsTable">
                    <thead class="table-light">
                    <tr>
                        <th>구분</th>
                        <th>카드 전체</th>
                        <?php foreach ($allCardNumbers as $cardNo): ?>
                            <?php if (isset($cardStats[$cardNo]) && $cardStats[$cardNo] > 0): ?>
                            <th class="card-header-clickable" data-card="<?= htmlspecialchars($cardNo) ?>" style="cursor: pointer;"><?= formatCompanyCard($cardNo, $cards) ?></th>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <th>합계</th>
                        <td><?= number_format($totalCardSum) ?></td>
                        <?php foreach ($allCardNumbers as $cardNo): ?>
                            <?php if (isset($cardStats[$cardNo]) && $cardStats[$cardNo] > 0): ?>
                            <td class="card-data-clickable" data-card="<?= htmlspecialchars($cardNo) ?>" style="cursor: pointer;">
                                <?= number_format($cardStats[$cardNo]) ?>
                            </td>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tr>
                    </tbody>
                </table>

					<div class="alert alert-primary ms-5 p-2" role="alert">
						지출결의서 10만원 미만은<br> 결재과정을 생략합니다.
					</div>		 
				</div>	
    
                <?php if (!empty($category_filter)): ?>
                <!-- 카테고리 필터 요약 테이블 -->
                <div class="d-flex justify-content-center align-items-center mb-3">
                    <table class="table table-bordered table-sm text-center" style="width: 400px;">
                        <thead class="table-info">
                            <tr>
                                <th>구분</th>
                                <th>내용</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>선택항목</strong></td>
                                <td><?= htmlspecialchars($category_filter) ?></td>
                            </tr>
                            <tr>
                                <td><strong>총 금액</strong></td>
                                <td class="text-end">
                                    <?php
                                    $filtered_total = 0;
                                    // 기존 쿼리를 다시 실행하여 총 금액 계산
                                    $total_sql = $sql;
                                    $total_stmh = $pdo->query($total_sql);
                                    while ($row = $total_stmh->fetch(PDO::FETCH_ASSOC)) {
                                        $expense_data = json_decode($row['expense_data'] ?? '[]', true);
                                        if (is_array($expense_data)) {
                                            foreach ($expense_data as $expense) {
                                                if (!empty($expense['expense_category']) && $expense['expense_category'] === $category_filter) {
                                                    if (!empty($expense['expense_amount'])) {
                                                        $filtered_total += intval(str_replace(',', '', $expense['expense_amount']));
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    echo number_format($filtered_total) . '원';
                                    ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>                			
            <div class="d-flex justify-content-start align-items-center mt-2">
                    <span>
                        ▷ <?= $total_row ?> &nbsp;
                    </span>
                                
                    <small class="d-block text-muted text-center mt-1 mx-4">
                        [기간]버튼에 커서를 올리면 전체, 전년도, 전월등 세부 내용을 검색 가능합니다.
                    </small>
                    <span id="showdate" class="btn btn-dark btn-sm mx-2">기간</span>  	
			<div id="showframe" class="card showextractframe" style="width:500px;">
				<div class="card-header " style="padding:2px;">
					<div class="d-flex justify-content-center align-items-center">  
						기간 설정
					</div>
				</div>
				<div class="card-body ">
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
            <div class="d-flex mb-1 mt-1 justify-content-center align-items-center">  													                  
			   
			   <!-- 분류 카테고리 선택 -->
			   <select id="category_filter" name="category_filter" class="form-select " style="width:120px; font-size: 0.8rem; height:30px; padding: 4px;">
				   <option value="">전체 분류</option>
				   <?php foreach ($categories as $category): ?>
				   <option value="<?= htmlspecialchars($category) ?>" <?= ($category_filter === $category) ? 'selected' : '' ?>>
					   <?= htmlspecialchars($category) ?>
				   </option>
				   <?php endforeach; ?>
			   </select>			   
			   &nbsp;&nbsp;
				   
				<?php if($chkMobile) { ?>
						</div>
					<div class="d-flex justify-content-center align-items-center">  	
				<?php } ?>	&nbsp;				
			<div class="inputWrap">
				<input type="text" id="search" name="search" value="<?=$search?>" autocomplete="off"  class="form-control w-auto mx-1" placeholder="<?= $category_filter ? '카테고리: ' . htmlspecialchars($category_filter) : '검색어 입력' ?>" > &nbsp;			
				<button class="btnClear"></button>
			</div>				
			<div id="autocomplete-list">
			</div>
			 &nbsp;												   			   
				<button type="button" id="searchBtn" class="btn btn-dark  btn-sm"> <i class="bi bi-search"></i>  </button>	&nbsp;&nbsp;
				<button type="button" class="btn btn-outline-secondary btn-sm me-1" id="categoryBtn" title="카테고리 설정"> <i class="bi bi-gear"></i> </button>
				<button type="button" class="btn btn-dark  btn-sm me-1" id="writeBtn"> <i class="bi bi-pencil-fill"></i> 신규  </button> 	    			 
		</div>
	</div>
  </div>	
<style>
th {
    white-space: nowrap;
}

/* 카드 통계 테이블 클릭 가능한 요소 스타일 */
.card-header-clickable:hover,
.card-data-clickable:hover {
    background-color: #e3f2fd !important;
    transition: background-color 0.2s ease;
}

.card-header-clickable,
.card-data-clickable {
    transition: background-color 0.2s ease;
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
        <th class="text-center" scope="col" style="width:120px;">지출요청일</th>		
        <th class="text-center" scope="col" style="width:120px;">결재일</th>		
        <th class="text-center" scope="col">기안자</th>        
        <th class="text-center" scope="col">분류</th>
        <th class="text-center" scope="col">적요</th>
        <th class="text-center" scope="col">금액</th>
        <th class="text-center" scope="col">비고</th>        
        <th class="text-center" scope="col">법인카드</th>
        <th class="text-center" scope="col">결재완료</th>
      </tr>
    </thead>	
    <tbody>
      <?php
      
      $start_num = $total_row; // 페이지당 표시되는 첫번째 글순번
      
      while ($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
       		 include $_SERVER['DOCUMENT_ROOT'] .'/eworks/_row.php';		
			 
        // expense_data JSON 파싱
        $expense_data = json_decode($expense_data ?? '[]', true);
        $items = [];
        $notes = [];
        $category = [];
        $total_amount = 0;

        if (is_array($expense_data)) {
            foreach ($expense_data as $expense) {
                if (!empty($expense['expense_item'])) {
                    $items[] = $expense['expense_item'];
                }
                if (!empty($expense['expense_note'])) {
                    $notes[] = $expense['expense_note'];
                }
                if (!empty($expense['expense_amount'])) {
                    $total_amount += intval(str_replace(',', '', $expense['expense_amount']));
                }
                if (!empty($expense['expense_category'])) {
                    $category[] = $expense['expense_category'];
                }
            }
        }

        // 제목 생성 (첫 번째 적요 + 외 N건)
        $title = '';
        if (!empty($items)) {
            if (count($items) > 1) {
                $title = $items[0] . ' 외 ' . (count($items) - 1) . '건';
            } else {
                $title = $items[0];
            }
        }

        // 분류,적요,비고를 콤마로 구분된 문자열로 변환
        $category_str = implode(', ', $category);
        $items_str = implode(', ', $items);
        $notes_str = implode(', ', $notes);
        
        // category_filter가 설정된 경우 해당 카테고리만 필터링
        if (!empty($category_filter)) {
            $filtered_items = [];
            $filtered_notes = [];
            $filtered_amount = 0;
            $filtered_category = [];
            
            if (is_array($expense_data)) {
                foreach ($expense_data as $expense) {
                    if (!empty($expense['expense_category']) && $expense['expense_category'] === $category_filter) {
                        if (!empty($expense['expense_item'])) {
                            $filtered_items[] = $expense['expense_item'];
                        }
                        if (!empty($expense['expense_note'])) {
                            $filtered_notes[] = $expense['expense_note'];
                        }
                        if (!empty($expense['expense_amount'])) {
                            $filtered_amount += intval(str_replace(',', '', $expense['expense_amount']));
                        }
                        $filtered_category[] = $expense['expense_category'];
                    }
                }
            }
            
            // 필터링된 데이터가 있는 경우에만 표시
            if (!empty($filtered_items)) {
                $category_str = implode(', ', $filtered_category);
                $items_str = implode(', ', $filtered_items);
                $notes_str = implode(', ', $filtered_notes);
                $total_amount = $filtered_amount;
            } else {
                // 해당 카테고리가 없으면 건너뛰기
                continue;
            }
        }
			 
		echo '<tr style="cursor:pointer;" data-id="'.  $num . '" onclick="redirectToView(' . $num . ')">';
		  ?>
			<td class="text-center"><?= $start_num ?></td>
			<td class="text-center" data-order="<?= $indate ?>"> <?=$indate?> </td>	  
			<td class="text-center" data-order="<?= $requestpaymentdate ?>"> <?= $requestpaymentdate ?> </td>	  
			<td class="text-center" data-order="<?= $paymentdate ?>"> <?= $paymentdate ?> </td>	  
			<td class="text-center"> <?= $author ?> </td>          			
			<td class="text-start"> <?= $category_str ?></td>
			<td class="text-start"><?= $items_str ?></td>
			<td class="text-end"><?= number_format($total_amount) ?></td>
			<td class="text-start"><?= $notes_str ?></td>
			<td class="text-start"><?= formatCompanyCard($companyCard, $cards) ?></td>
			<td class="text-center">
                <?= ( ($status === 'end' && !empty($e_confirm)) || empty($e_line_id) ) ? '✅' : '' ?>
            </td>
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
	// 자재현황 클릭시
	$("#rawmaterialBtn").click(function(){ 			
		 popupCenter('/ceiling/list_part_table.php?menu=no'  , '부자재현황보기', 1050, 950);	
	});	
});

$(document).ready(function() { 
	$("#writeBtn").click(function(){ 		
		var tablename = $("#tablename").val();			
		var url = "write_form.php?tablename=" + tablename ; 
		customPopup(url, '등록', 800, 800); 		
	 });	 
	$("#searchBtn").click(function() { 
		// 페이지 번호를 1로 설정
		currentpageNumber = 1;
		setCookie('currentpageNumber', currentpageNumber, 10); // 쿠키에 페이지 번호 저장

		// Set dateRange to '전체' and trigger the change event
		$('#dateRange').val('전체').change();
		document.getElementById('board_form').submit();
	});
	
	// 카테고리 설정 버튼 클릭 이벤트
	$("#categoryBtn").click(function(){ 		
		var url = "category_setting.php"; 
		var popup = customPopup(url, '카테고리 설정', 600, 700);
		
		// 팝업이 닫힐 때 페이지 새로고침
		var checkClosed = setInterval(function() {
			if (popup.closed) {
				clearInterval(checkClosed);
				location.reload();
			}
		}, 1000);
	 });
	 
	// 카테고리 필터 변경 시 자동 검색
	$("#category_filter").change(function() {
		$("#searchBtn").click();
	});
}); 

function redirectToView(num) {    
    var tablename = $("#tablename").val();    	
	
    var url = "write_form.php?mode=view&num=" + num         
        + "&tablename=" + tablename;   
	customPopup(url, '', 800, 800); 			
}

function restorePageNumber() {    
    location.reload();
}

// 서버에 작업 기록
$(document).ready(function(){
	saveLogData('<?=$title_message?>'); // 다른 페이지에 맞는 menuName을 전달
});

// 카드 통계 테이블 클릭 이벤트
$(document).ready(function() {
    // 카드 헤더 클릭 이벤트
    $(document).on('click', '.card-header-clickable', function() {
        var cardNo = $(this).data('card');
        openCardDetailPopup(cardNo);
    });
    
    // 카드 데이터 클릭 이벤트
    $(document).on('click', '.card-data-clickable', function() {
        var cardNo = $(this).data('card');
        openCardDetailPopup(cardNo);
    });
});

function openCardDetailPopup(cardNo) {
    var url = "card_detail.php?card=" + encodeURIComponent(cardNo) + "&fromdate=<?= $fromdate ?>&todate=<?= $todate ?>";
    popupCenter(url, '카드상세내역', 1200, 800);
}
</script> 

</body>
</html>