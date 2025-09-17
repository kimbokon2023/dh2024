<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  
   
$title_message = '업무요청사항';   

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
$tablename = "workprocess";  
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();
	 
$mode=$_REQUEST["mode"] ?? '' ;
$search=$_REQUEST["search"] ?? '';
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
    // 해당 월의 마지막 날 계산
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

if($mode=="search"){
    if(!$search) {
        $sql ="select * from " . $DB . "." . $tablename . " 
            WHERE regist_day BETWEEN '$fromdate' AND '$todate' 
            order by num desc "; 				
    } else {
        $sql="select * from " . $DB . "." . $tablename . " 
            where (name like '%$search%' or subject like '%$search%' or regist_day like '%$search%' or searchtext like '%$search%') 
            AND regist_day BETWEEN '$fromdate' AND '$todate' 
            order by num desc ";              
    }
} else {
    $sql="select * from " . $DB . "." . $tablename . " 
        WHERE regist_day BETWEEN '$fromdate' AND '$todate' 
        order by num desc";              
}
	 	 
try{  
	$stmh = $pdo->query($sql); 
	$total_row=$stmh->rowCount();  	  
?>
	
<form name="board_form" id="board_form"  method="post">
<input type="hidden" id="previous_search_type" value="<?=$search_type?>">
   
<div class="container justify-content-center">  
<div class="card mt-2 mb-4">  
	<div class="card-body">  
		<div class="card-header">
			<div class="d-flex justify-content-center text-center align-items-center">										 
				<span class="text-center fs-5"> <?=$title_message?> </span>		
				<button type="button" class="btn btn-dark btn-sm mx-3" onclick='location.reload();' title="새로고침"> <i class="bi bi-arrow-clockwise"></i> </button>  	
				<small class="ms-5 text-muted">  업무요청시 담당자 지정, 요청내용 및 기간을 정확히 기재하여 저장합니다. </small>  	
			</div>
		</div>
		<div class="card-body">								
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

			<div class="d-flex justify-content-center text-center align-items-center mb-2">										 
				▷ <?= $total_row ?> &nbsp; 
				<div class="inputWrap30">			
					<input type="text" id="search" class="form-control" style="width:150px;" name="search" value="<?=$search?>" autocomplete="off" onKeyPress="if (event.keyCode==13){ enter(); }">
					<button class="btnClear"></button>
				</div>											
				<button class="btn btn-outline-dark btn-sm mx-1" type="button" id="searchBtn"> <i class="bi bi-search"></i> </button> 
				<button id="writeBtn" type="button" class="btn btn-dark btn-sm mx-1"> <i class="bi bi-pencil-square"></i> 신규 </button>				
			</div>		
			
			 <div class="row d-flex">
			 <table class="table table-hover" id="myTable">
			   <thead class="table-primary">
					<tr>
						 <th class="text-center" > 번호 </th>
						 <th class="text-center" > 요청사항 제목 </th>						 
						 <th class="text-center" > 작성자 </th>
						 <th class="text-center" > 업무담당자   </th>
						 <th class="text-center" > 작성일 </th>   
						 <th class="text-center" > 처리기한 </th>   
						 <th class="text-center" > 처리완료일 </th>   
						 <th class="text-center" > 조회수   </th>   
						 </tr>
				   </thead>
				<tbody>  			  
			<?php  			  
			  $start_num=$total_row;    // 페이지당 표시되는 첫번째 글순번
			  
			 while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
				include '_row.php';			   
				$subject=str_replace(" ", "&nbsp;", $row["subject"]);
				$sql="select * from " . $DB . ".workprocess_ripple where parent=$num";
				$stmh1 = $pdo->query($sql); 
				$num_ripple=$stmh1->rowCount(); 
			 ?>			 
			   <tr onclick="redirectToView('<?=$num?>', '<?=$tablename?>')">			  
				  <td class="text-center" >  <?= $start_num ?>      </td>
				  <td>  <?= $subject ?>                     
					<?php
					   if($num_ripple>0)
							echo '<span class="badge bg-primary "> '.$num_ripple.' </span> ';
					?>
				  </td>
				  <td class="text-center" >  <?= $first_writer ?>      </td>
				  <td class="text-center" >  <?= $chargedPerson ?>      </td>				  
				  <td class="text-center" >  <?= $regist_day ?>      </td>     				  
				  <td class="text-center">
				    <?= ($dueDate == '0000-00-00' || empty($dueDate)) ? '' : $dueDate ?>
			      </td>
				  <td class="text-center">
				    <?= ($doneDate == '0000-00-00' || empty($doneDate)) ? '' : $doneDate ?>
			      </td>
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
   </div> <!--card-body-->
   </div> <!--card -->
   </div> <!--container-->
	</form>   
</body> 
</html>   

<!-- 페이지로딩 -->
<script>
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
    var currentSearchType = $('input[name="search_type"]:checked').val();
    var previousSearchType = $('#previous_search_type').val();
    
    // 월별에서 기간별로 전환 시 날짜 설정
    if (previousSearchType === 'month' && currentSearchType === 'period') {
        var selectedMonth = $('#selected_month').val();
        if (selectedMonth) {
            // 선택된 월의 첫날과 마지막날 설정
            var firstDay = selectedMonth + '-01';
            var lastDay = new Date(selectedMonth + '-01');
            lastDay.setMonth(lastDay.getMonth() + 1);
            lastDay.setDate(0); // 이전 달의 마지막 날
            
            $('#fromdate').val(firstDay);
            $('#todate').val(lastDay.toISOString().split('T')[0]);
        }
    }
    
    // 연도별에서 기간별로 전환 시 날짜 설정
    if (previousSearchType === 'year' && currentSearchType === 'period') {
        var selectedYear = $('#selected_year').val();
        if (selectedYear) {
            $('#fromdate').val(selectedYear + '-01-01');
            $('#todate').val(selectedYear + '-12-31');
        }
    }
    
    // 현재 검색 타입 저장
    $('#previous_search_type').val(currentSearchType);
    
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

// 페이지 로딩
$(document).ready(function(){	
    var loader = document.getElementById('loadingOverlay');
	if(loader)
		loader.style.display = 'none';
		
    toggleSearchType(); // 초기 로드 시 검색 타입에 맞는 컨트롤 표시
});

var dataTable; // DataTables 인스턴스 전역 변수
var workprocesspageNumber; // 현재 페이지 번호 저장을 위한 전역 변수

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
    var savedPageNumber = getCookie('workprocesspageNumber');
    if (savedPageNumber) {
        dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
    }

    // 페이지 변경 이벤트 리스너
    dataTable.on('page.dt', function() {
        var workprocesspageNumber = dataTable.page.info().page + 1;
        setCookie('workprocesspageNumber', workprocesspageNumber, 10); // 쿠키에 페이지 번호 저장
    });

    // 페이지 길이 셀렉트 박스 변경 이벤트 처리
    $('#myTable_length select').on('change', function() {
        var selectedValue = $(this).val();
        dataTable.page.len(selectedValue).draw(); // 페이지 길이 변경 (DataTable 파괴 및 재초기화 없이)

        // 변경 후 현재 페이지 번호 복원
        savedPageNumber = getCookie('workprocesspageNumber');
        if (savedPageNumber) {
            dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
        }
    });
});

function restorePageNumber() {
    var savedPageNumber = getCookie('workprocesspageNumber');
    if (savedPageNumber) {
        dataTable.page(parseInt(savedPageNumber) - 1).draw('page');
    }
}


function redirectToView(num, tablename) {
    var page = workprocesspageNumber; // 현재 페이지 번호 (+1을 해서 1부터 시작하도록 조정)
    	
    var url = "view.php?num=" + num + "&tablename=" + tablename;          

	customPopup(url, '', 1400, 900); 		    
}

$(document).ready(function(){
	
	$("#writeBtn").click(function(){ 
		var page = workprocesspageNumber; // 현재 페이지 번호 (+1을 해서 1부터 시작하도록 조정)	
		var tablename = '<?php echo $tablename; ?>';		
		var url = "write_form.php?tablename=" + tablename; 				
		customPopup(url, '', 1400, 900); 	
	 });			 
		
});	

$(document).ready(function(){
	saveLogData('주요안건 처리현황'); 
});
</script>