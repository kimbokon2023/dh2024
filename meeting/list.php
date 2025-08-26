<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  
   
   // 첫 화면 표시 문구
$title_message = '회의록';
   

 if(!isset($_SESSION["level"]) || $_SESSION["level"]>5) {
		 sleep(1);
		  header("Location:" . $WebSite . "login/login_form.php"); 
         exit;
   }   

include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';   

 ?>
  
<title>  <?=$title_message?>  </title> 

    <style>
        .table-hover tbody tr:hover {
            cursor: pointer;
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
    </style> 
 
 </head> 
 
<body>

  <?php require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php'); ?>   

<?php

$tablename = "meeting";
  
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();
	 
// 검색 관련 변수 초기화
$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';  
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';
$search_type = isset($_REQUEST['search_type']) ? $_REQUEST['search_type'] : 'period'; // 기본값은 기간별
$selected_year = isset($_REQUEST['selected_year']) ? $_REQUEST['selected_year'] : date('Y');
$selected_month = isset($_REQUEST['selected_month']) ? $_REQUEST['selected_month'] : date('Y-m');
$fromdate = isset($_REQUEST['fromdate']) ? $_REQUEST['fromdate'] : '';  
$todate = isset($_REQUEST['todate']) ? $_REQUEST['todate'] : '';  

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
        $fromdate = date("Y-m-01", strtotime("-1 month"));	
        $todate = $currentDate;
    }
}

// 연도 옵션 생성 (현재년도 + 과거 3년)
$current_year = date('Y');
$year_options = '';
for ($i = 0; $i < 4; $i++) {
    $year = $current_year - $i;
    $selected = ($year == $selected_year) ? 'selected' : '';
    $year_options .= "<option value='$year' $selected>" . $year . "년</option>";
}
	 
if(isset($_REQUEST["mode"]))
     $mode=$_REQUEST["mode"];
  else 
     $mode="";

// 검색 조건에 따른 SQL 쿼리 구성
if (!empty($search)) {
    $sql = "SELECT * FROM ".$DB.".".$tablename." 
        WHERE (name LIKE '%$search%' OR subject LIKE '%$search%' OR regist_day LIKE '%$search%' OR registration_date LIKE '%$search%' OR searchtext LIKE '%$search%') 
        AND is_deleted != 'Y' 
        AND registration_date BETWEEN '$fromdate' AND '$todate' 
        ORDER BY num DESC";	
} else {
    $sql = "SELECT * FROM ".$DB.".".$tablename." 
        WHERE is_deleted != 'Y' 
        AND registration_date BETWEEN '$fromdate' AND '$todate' 
        ORDER BY num DESC";
}

try{  
    $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh
    $total_row=$stmh->rowCount();  

   } catch (PDOException $Exception) {
    print "오류: ".$Exception->getMessage();
}  	 	 
try{  
	$stmh = $pdo->query($sql); 
	  
?>
<form name="board_form" id="board_form"  method="post" action="list.php?mode=search">
   
<div class="container">  
	<div class="card mt-1">
		<div class="card-body">
			<div class="d-flex mb-3 mt-2 justify-content-center align-items-center">  
				<h4> <?=$title_message?> </h4>  
				<button type="button" class="btn btn-dark btn-sm mx-3"  onclick='location.reload();' title="새로고침"> <i class="bi bi-arrow-clockwise"></i> </button>  
                <small class="ms-5 text-muted"> 일자, 시간, 참석자, 회의내용, 결과 작성 후 저장 </small>  
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
							<input type="date" id="fromdate" name="fromdate" class="form-control me-2" value="<?=$fromdate?>" onchange="autoSubmit()">
							<span class="me-2">~</span>
							<input type="date" id="todate" name="todate" class="form-control" value="<?=$todate?>" onchange="autoSubmit()">
						</div>
					</div>
				</div>
			</div>

			<!-- 검색어 입력 및 검색 버튼 (항상 유지) -->
			<div class="row justify-content-center mb-3">
				<div class="col-auto">
					<div class="d-flex align-items-center">
						<div class="inputWrap30 me-2">			
							<input type="text" id="search" class="form-control" style="width:150px;" name="search" value="<?=$search?>" autocomplete="off" onKeyPress="if (event.keyCode==13){ enter(); }" placeholder="검색어 입력">
							<button class="btnClear"></button>
						</div>							
						<button class="btn btn-outline-dark btn-sm me-2" type="button" id="searchBtn"> <i class="bi bi-search"></i> 검색 </button> &nbsp;&nbsp;&nbsp;&nbsp;			
						<button id="newBtn" type="button" class="btn btn-dark btn-sm me-2"> <i class="bi bi-pencil-square"></i> 신규 </button>				
					</div>
				</div>
			</div>
		</div>
	</div>
	   
<div class="card mb-2">
<div class="card-body">	  	  
   <div class="table-responsive"> 	
   <table class="table table-hover " id="myTable">
    <thead class="table-primary">
      <tr>
        <th class="text-center" scope="col" style="width:8%;">번호</th>
        <th class="text-center" scope="col" style="width:10%;">회의일자</th>        
        <th class="text-center" scope="col" style="width:50%;">글제목</th>        
        <th class="text-center" scope="col" style="width:12%;">작성자</th>
        <th class="text-center" scope="col" style="width:15%;">등록일자</th>   
        <th class="text-center" scope="col" style="width:10%;">조회수</th>           
      </tr>
    </thead>	
    <tbody>
      <?php
  
  $start_num=$total_row;    // 페이지당 표시되는 첫번째 글순번
  			 
 while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
   include '_row.php';
   
  $subject=str_replace(" ", "&nbsp;", $row["subject"]);
   
  $sql="select * from " . $DB . ".meeting_ripple where parent=$num";
  $stmh1 = $pdo->query($sql); 
  $num_ripple=$stmh1->rowCount(); 
  
  // 중요회의 여부 확인
  $is_important = ($row["suggestioncheck"] == "y") ? true : false;
  
  // 회의일자 표시
  $meeting_date = $row["registration_date"];
  $display_subject =  $subject;
 ?>
 
   <tr onclick="redirectToView('<?=$num?>', '<?=$tablename?>')">  
        <td class="text-center" >  <?= $start_num ?>      </td>
        <td class="text-center" >  <?= $meeting_date ?>      </td>
        <td>  
        <?php if($is_important): ?>
            <span class="badge bg-danger me-1">중요</span>
        <?php endif; ?>
        <?= $display_subject ?>                     
        <?php
            if($num_ripple>0)
                echo '<span class="badge bg-primary ms-1"> '.$num_ripple.' </span> ';
        ?>
        </td>
        <td class="text-center" >  <?= $name ?>      </td>
        <td class="text-center" >  <?= $regist_day ?>      </td>     
        <td class="text-center" >  <?= $hit ?>       </td>    
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
   </div> <!--card-body-->
   </div> <!--card -->
   </div> <!--container-fluid-->
	</form>   
</body> 
</html>   

<!-- 페이지로딩 -->
<script>
// 페이지 로딩
$(document).ready(function(){	
    var loader = document.getElementById('loadingOverlay');
    if(loader) {
        loader.style.display = 'none';
    }
    
    toggleSearchType(); // 초기 로드 시 검색 타입에 맞는 컨트롤 표시
});

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

// 검색 타입 변경 시 자동 검색 실행
function toggleSearchTypeAndSubmit() {
    toggleSearchType();
    
    // 약간의 지연 후 폼 제출 (UI 업데이트를 위해)
    setTimeout(function() {
        $("#board_form").submit();
    }, 100);
}

// 검색 조건 변경 시 자동 검색 실행
function autoSubmit() {
    // 약간의 지연 후 폼 제출 (사용자 입력 완료를 위해)
    setTimeout(function() {
        $("#board_form").submit();
    }, 300);
}

// 엔터키 입력 시 검색 실행
function enter() {
    $("#board_form").submit();
}
</script>
<script>

var dataTable; // DataTables 인스턴스 전역 변수
var meetingpageNumber; // 현재 페이지 번호 저장을 위한 전역 변수

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
    var savedPageNumber = getCookie('meetingpageNumber');
    if (savedPageNumber) {
        dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
    }

    // 페이지 변경 이벤트 리스너
    dataTable.on('page.dt', function() {
        var meetingpageNumber = dataTable.page.info().page + 1;
        setCookie('meetingpageNumber', meetingpageNumber, 10); // 쿠키에 페이지 번호 저장
    });

    // 페이지 길이 셀렉트 박스 변경 이벤트 처리
    $('#myTable_length select').on('change', function() {
        var selectedValue = $(this).val();
        dataTable.page.len(selectedValue).draw(); // 페이지 길이 변경 (DataTable 파괴 및 재초기화 없이)

        // 변경 후 현재 페이지 번호 복원
        savedPageNumber = getCookie('meetingpageNumber');
        if (savedPageNumber) {
            dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
        }
    });
});

function restorePageNumber() {
    var savedPageNumber = getCookie('meetingpageNumber');
    if (savedPageNumber) {
        dataTable.page(parseInt(savedPageNumber) - 1).draw('page');
    }
}


function redirectToView(num, tablename) {
    var page = meetingpageNumber; // 현재 페이지 번호 (+1을 해서 1부터 시작하도록 조정)
    	
    var url = "view.php?num=" + num + "&tablename=" + tablename;          

	customPopup(url, '회의록', 1000, 800); 		    
}

$(document).ready(function(){
	
	$("#newBtn").click(function(){ 
		var page = meetingpageNumber; // 현재 페이지 번호 (+1을 해서 1부터 시작하도록 조정)	
		var tablename = '<?php echo $tablename; ?>';		
		var url = "write_form.php?tablename=" + tablename; 				
		customPopup(url, '회의록', 1000, 800); 	
	 });			 
		
});	
</script> 