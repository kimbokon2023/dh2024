<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

// 첫 화면 표시 문구
$title_message = '(화물회사) (주)대한 배차 현황'; 
 ?>

<?php 

 if(!isset($_SESSION["level"]) || $_SESSION["level"] !== '10') {
		 sleep(1);
		  header("Location:" . $WebSite . "login/login_form.php"); 
         exit;
   }    
include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';
   
 ?>

<title> <?=$title_message?> </title>

<link href="css/style.css" rel="stylesheet" >   

<style>
@keyframes blink {
    0% { opacity: 1; }
    50% { opacity: 0; }
    100% { opacity: 1; }
}

.blink {
    animation: blink 1s infinite;
}
</style>


</head>

<body>	
	 
<?php  require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader_partner.php'); ?>   

<?php
$tablename = 'motor'; 

$check = isset($_REQUEST['check']) ? $_REQUEST['check'] : '';
$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';  
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  
$find = isset($_REQUEST['find']) ? $_REQUEST['find'] : '';  

// 사용자 입력 확인 및 기본값 설정
// 시간 개념이 들어가야 한다.
$fromdate = isset($_REQUEST['fromdate']) && !empty($_REQUEST['fromdate']) ? $_REQUEST['fromdate'] : date("Y-m-d 00:00:00", strtotime("-1 month"));
$todate = isset($_REQUEST['todate']) && !empty($_REQUEST['todate']) ? $_REQUEST['todate'] : date("Y-m-d 23:59:59");

// 입력된 날짜 형식을 SQL 쿼리에 적합하게 조정
$fromdate = date("Y-m-d 00:00:00", strtotime($fromdate));
$todate = date("Y-m-d 23:59:59", strtotime($todate));

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();


// /////////////////////////첨부파일 있는 것 불러오기 
$savefilename_arr=array(); 
$realname_arr=array(); 
$attach_arr=array(); 
$tablename='motor';
$item = 'motor';

$sql=" select * from " . $DB . ".fileuploads where tablename ='$tablename' and item ='$item' ";	

 try{  
   $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh   
   while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
			array_push($realname_arr, $row["realname"]);			
			array_push($savefilename_arr, $row["savename"]);			
			array_push($attach_arr, $row["parentid"]);			
        }		 
   } catch (PDOException $Exception) {
    print "오류: ".$Exception->getMessage();
  }   

$currentDate = date("Y-m-d");

if (empty($fromdate) || empty($todate)) {
    $fromdate = date("Y-m-d 00:00:00", strtotime("-1 month")); // 한 달 전, 일의 시작
    $todate = date("Y-m-d 23:59:59"); // 현재 날짜, 일의 마지막
}
$Transtodate = $todate;
		
$sql=" select * from " . $DB . ".motor " ;

$sum=array();
  	
$now = date("Y-m-d");	 // 현재 날짜와 크거나 같으면 출고예정으로 구분

$orderby="order by num desc ";
 
$attached=''; 

if ($check=='0' || $check==0)
	$whereattached = '';
		
$SettingDate=" deltime "; 
	 
$common= $SettingDate . " between '$fromdate' and '$Transtodate' and is_deleted IS NULL and sendcheck !=''  ";
		
$andPhrase= " and " . $common  . $orderby ;
$wherePhrase= " where " . $common  . $orderby ;


// 검색을 위해 모든 검색변수 공백제거
$search = str_replace(' ', '', $search);  

if($search==""){
	if($whereattached!=='')
		$sql="select * from " . $DB . ".motor " . $whereattached . $andPhrase; 					                 
	else
		$sql="select * from " . $DB . ".motor " . $wherePhrase ;					                 
	}
	elseif($search!="" && $find!="all")
	{
		$sql="select * from " . $DB . ".motor where ($find like '%$search%') " . $attached . $andPhrase;         			
	}     				 
	elseif($search!="" && $find=="all") { // 필드별 
		$sql ="select * from " . $DB . ".motor where (replace(searchtag,' ','') like '%$search%' ) " . $attached . $andPhrase; 					                       
	}
		   
 // print '$sql : ' . $sql;  
$current_condition = $check;

try {
    $stmh = $pdo->query($sql);
    $rows = $stmh->fetchAll(PDO::FETCH_ASSOC); // 모든 데이터를 한 번에 가져오기
    $total_row = count($rows); // 가져온 데이터의 행 수 계산
    // var_dump($total_row);
    $start_num = $total_row;
    
} catch (PDOException $Exception) {
    echo "오류: " . $Exception->getMessage();
}

$fromdateFormatted = date('Y-m-d', strtotime($fromdate));
$todateFormatted = date('Y-m-d', strtotime($todate));
		 
 ?>

<form id="board_form" name="board_form" method="post" action="del_list.php?mode=search">  
	<input type="hidden" id="voc_alert" name="voc_alert" value="<?=$voc_alert?>" size="5" > 	
	<input type="hidden" id="ma_alert" name="ma_alert" value="<?=$ma_alert?>" size="5" > 		
	<input type="hidden" id="check" name="check" value="<?=$check?>" size="5" > 	
	
	

<div class="container-fluid">  
	<div class="card mb-2 mt-2">  
	<div class="card-body">  	 
			 
	<div class="d-flex  p-1 m-1 mt-1 justify-content-center align-items-center "> 		
		    <h5>  <?=$title_message?> </h5>
				
		</div>	

	<div class="d-flex  p-1 m-1 mt-1 mb-1 justify-content-center align-items-center"> 	  
			<i class="bi bi-arrow-right"></i> <?= $total_row ?> &nbsp; 		

			<!-- 기간부터 검색까지 연결 묶음 start -->
				<span id="showdate" class="btn btn-dark btn-sm " > 기간 </span>	&nbsp; 

				<select name="dateRange" id="dateRange" class="form-control me-1" style="width:80px;">
					<?php
					$dateRangeArray = array('최근3개월','최근6개월', '최근1년', '최근2년','직접설정','전체');
					$savedDateRange = $_COOKIE['dateRange'] ?? ''; // 쿠키에서 dateRange 값 읽기

					foreach ($dateRangeArray as $range) {
						$selected = ($savedDateRange == $range) ? 'selected' : '';
						echo "<option $selected value='$range'>$range</option>";
					}
					?>
				</select>
				
				<div id="showframe" class="card">
					<div class="card-header " style="padding:2px;">
						<div class="d-flex justify-content-center align-items-center">  
							기간 설정
						</div>
					</div>
					<div class="card-body">
						<div class="d-flex justify-content-center align-items-center">  	
							<button type="button" class="btn btn-outline-success btn-sm me-1 change_dateRange"   onclick='alldatesearch()' > 전체 </button>  
							<button type="button" id="preyear" class="btn btn-outline-primary btn-sm me-1 change_dateRange"   onclick='pre_year()' > 전년도 </button>  
							<button type="button" id="three_month" class="btn btn-dark btn-sm me-1  change_dateRange"  onclick='three_month_ago()' > M-3월 </button>
							<button type="button" id="prepremonth" class="btn btn-dark btn-sm me-1  change_dateRange"  onclick='prepre_month()' > 전전월 </button>	
							<button type="button" id="premonth" class="btn btn-dark btn-sm me-1  change_dateRange"  onclick='pre_month()' > 전월 </button> 						
							<button type="button" class="btn btn-outline-danger btn-sm me-1  change_dateRange"  onclick='this_today()' > 오늘 </button>
							<button type="button" id="thismonth" class="btn btn-dark btn-sm me-1  change_dateRange"  onclick='this_month()' > 당월 </button>
							<button type="button" id="thisyear" class="btn btn-dark btn-sm me-1  change_dateRange"  onclick='this_year()' > 당해년도 </button> 
						</div>
					</div>
				</div>		

			   <input type="date" id="fromdate" name="fromdate"   class="form-control"   style="width:100px;" value="<?=$fromdateFormatted?>" >  &nbsp;   ~ &nbsp;  
			   <input type="date" id="todate" name="todate"  class="form-control"   style="width:100px;" value="<?=$todateFormatted?>" >  &nbsp;     </span> 
			   &nbsp;&nbsp;		 
				

		<div class="inputWrap">
			<input type="text" id="search" name="search" value="<?=$search?>" onkeydown="JavaScript:SearchEnter();" autocomplete="off"  class="form-control" style="width:200px;" > &nbsp;			
			<button class="btnClear"></button>
		</div>				
		<div id="autocomplete-list">				
		</div>	
		  &nbsp;
			<button id="searchBtn" type="button" class="btn btn-dark  btn-sm" > <i class="bi bi-search"></i> </button> 		  
         </div> 	 
		 
   </div> <!--card-body-->
   </div> <!--card -->   
</div> <!--container-fluid -->   
<div class="container-fluid">  
<div class="d-flex justify-content-center align-items-center"> 		
<table class="table table-hover" id="myTable">
  <thead class="table-primary">
    <tr>
      <th class="text-center " style="width:50px;"> 번호</th>      
      <th class="text-center " style="width:100px;">상차요청시간 </th>
      <th class="text-center " style="width:100px;">하차요청시간</th>
      <th class="text-center " style="width:80px;">진행상태</th>
      <th class="text-start " style="width:200px;">주소</th>
	  <th class="text-center " style="width:70px;">차량종류</th>    
	  <th class="text-center " style="width:80px;">차량번호</th>    
	  <th class="text-center " style="width:100px;"><i class="bi bi-telephone-forward-fill"></i> 기사 </th>
	  <th class="text-center " style="width:80px;">배송비 지급</th>    
	  <th class="text-end " style="width:60px;">운송료</th>	  
      <th class="text-start " style="width:120px;">전달사항</th>	  
    </tr>        
  </thead>	  
  <tbody>
<?php
try {
    $stmh = $pdo->query($sql);
    $rows = $stmh->fetchAll(PDO::FETCH_ASSOC); // 모든 데이터를 한 번에 가져오기
    $total_row = count($rows); // 가져온 데이터의 행 수 계산
    // var_dump($total_row);
    $start_num = $total_row;


    foreach ($rows as $row) {
        // HTML 출력 부분
		include '_row.php';		
	
		
        ?>
        <tr onclick="redirectToView('<?= $row['num'] ?>', '<?= $tablename ?>')">
            <td class="text-center"><?= $start_num ?></td>
            <td class="text-center"><?= $row['deltime'] ?></td>
            <td class="text-center"><?= $row['deldowntime'] ?></td>
			<td class="text-center <?php if (strpos($row['del_status'], '접수') !== false) echo 'blink text-primary'; ?>">
				<?= $row['del_status'] ?>
			</td>


            <td class="text-center"><?= $row['address'] ?></td>
            <td class="text-center"><?= $row['delcaritem'] ?></td>
            <td class="text-center"><?= $row['delcarnumber'] ?></td>
            <td class="text-center"><?= $row['delcartel'] ?></td>
            <td class="text-center"><?= $row['deliverypaymethod'] ?></td>
            <td class="text-center"><?= $row['delipay'] ?></td>
            <td class="text-center"><?= $row['delmemo'] ?></td>
        </tr>
        <?php
        $start_num--;
    }
} catch (PDOException $Exception) {
    echo "오류: " . $Exception->getMessage();
}
?>
  
  
     <!-- Table body 부분은 아래에 추가 -->
    </tbody>  
    </table>  
</div>
      
   </div> <!--container-->
</form>	
	<div class="container-fluid mt-3 mb-3">
		<? include '../footer_sub.php'; ?>
	</div>
</body>
</html>


<script>
// 페이지 로딩
$(document).ready(function(){	
    var loader = document.getElementById('loadingOverlay');
    loader.style.display = 'none';
});
</script>

<script> 
var dataTable; // DataTables 인스턴스 전역 변수
var motorpageNumber; // 현재 페이지 번호 저장을 위한 전역 변수

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
    var savedPageNumber = getCookie('motorpageNumber');
    if (savedPageNumber) {
        dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
    }

    // 페이지 변경 이벤트 리스너
    dataTable.on('page.dt', function() {
        var motorpageNumber = dataTable.page.info().page + 1;
        setCookie('motorpageNumber', motorpageNumber, 10); // 쿠키에 페이지 번호 저장
    });

    // 페이지 길이 셀렉트 박스 변경 이벤트 처리
    $('#myTable_length select').on('change', function() {
        var selectedValue = $(this).val();
        dataTable.page.len(selectedValue).draw(); // 페이지 길이 변경 (DataTable 파괴 및 재초기화 없이)

        // 변경 후 현재 페이지 번호 복원
        savedPageNumber = getCookie('motorpageNumber');
        if (savedPageNumber) {
            dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
        }
    });
});

function restorePageNumber() {
    var savedPageNumber = getCookie('motorpageNumber');
    // if (savedPageNumber) {
        // dataTable.page(parseInt(savedPageNumber) - 1).draw('page');
    // }
	location.reload(true);
}

function redirectToView(num, tablename) {	
    var url = "write_form_del.php?mode=view&num=" + num + "&tablename=" + tablename;          
	customPopup(url, '수주내역', 1850, 850); 		    
}

$(document).ready(function(){	
	$("#writeBtn").click(function(){ 		
		var tablename = '<?php echo $tablename; ?>';		
		var url = "write_form_del.php?tablename=" + tablename; 				
		customPopup(url, '수주내역', 1850, 850); 	
	 });			 
	 
	// 자재현황 클릭시
	$("#rawmaterialBtn").click(function(){ 			
		 popupCenter('/motor/list_part_table.php?menu=no'  , '부자재현황보기', 1050, 950);	
	});		 
			
	// 가공스케줄 클릭시
	$("#plan_cutandbending").click(function(){ 
		 popupCenter('/motor/plan_cutandbending.php?menu=no'  , '가공 스케줄', 1900, 950);	
	});		 
		
});	


$(document).ready(function() {	

    // 쿠키에서 dateRange 값을 읽어와 셀렉트 박스에 반영
    var savedDateRange = getCookie('dateRange');
    if (savedDateRange) {
        $('#dateRange').val(savedDateRange);
    }

    // dateRange 셀렉트 박스 변경 이벤트 처리
    $('#dateRange').on('change', function() {
        var selectedRange = $(this).val();
        var currentDate = new Date(); // 현재 날짜
        var fromDate, toDate;

        switch(selectedRange) {
            case '최근3개월':
                fromDate = new Date(currentDate.setMonth(currentDate.getMonth() - 3));
                break;
            case '최근6개월':
                fromDate = new Date(currentDate.setMonth(currentDate.getMonth() - 6));
                break;
            case '최근1년':
                fromDate = new Date(currentDate.setFullYear(currentDate.getFullYear() - 1));
                break;
            case '최근2년':
                fromDate = new Date(currentDate.setFullYear(currentDate.getFullYear() - 2));
                break;
            case '직접설정':
                fromDate = new Date(currentDate.setFullYear(currentDate.getFullYear() - 1));
                break;   
            case '전체':
                fromDate = new Date(currentDate.setFullYear(currentDate.getFullYear() - 20));
                break;            
            default:
                // 기본 값 또는 예외 처리
                break;
        }

        // 날짜 형식을 YYYY-MM-DD로 변환
        toDate = formatDate(new Date()); // 오늘 날짜
        fromDate = formatDate(fromDate); // 계산된 시작 날짜

        // input 필드 값 설정
        $('#fromdate').val(fromDate);
        $('#todate').val(toDate);		
		
		var selectedDateRange = $(this).val();
       // 쿠키에 저장된 값과 현재 선택된 값이 다른 경우에만 페이지 새로고침
        if (savedDateRange !== selectedDateRange) {
            setCookie('dateRange', selectedDateRange, 30); // 쿠키에 dateRange 저장
			document.getElementById('board_form').submit();      
        }		
		
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

    console.log('searchValue ' + searchValue);

    if (searchValue === "") {        
        document.getElementById('board_form').submit();
    } else {
        let now = new Date();
        let timestamp = now.toLocaleDateString() + ' ' + now.toLocaleTimeString();

        let searches = getSearches();
        // 기존에 동일한 검색어가 있는 경우 제거
        searches = searches.filter(search => search.keyword !== searchValue);
        // 새로운 검색 정보 추가
        searches.unshift({ keyword: searchValue, time: timestamp });
        searches = searches.slice(0, 50);

        document.cookie = "searches=" + JSON.stringify(searches) + "; max-age=31536000";
		
		var motorpageNumber = 1;
		setCookie('motorpageNumber', motorpageNumber, 10); // 쿠키에 페이지 번호 저장		
		// Set dateRange to '전체' and trigger the change event
        $('#dateRange').val('전체').change();
        document.getElementById('board_form').submit();
    }
}

// 검색창에 쿠키를 이용해서 저장하고 화면에 보여주는 코드 묶음
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
		}, 100); // Delay of 100 milliseconds
	});


    function hideAutocomplete() {
        document.getElementById('autocomplete-list').style.display = 'none';
    }

    function showAutocomplete() {
        document.getElementById('autocomplete-list').style.display = 'block';
    }


function renderAutocomplete(matches) {
    const autocompleteList = document.getElementById('autocomplete-list');    

    // Remove all .autocomplete-item elements
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
			console.log(match.keyword);
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
                // 배열이 50개 이상의 요소를 포함하는 경우 처음 50개만 반환
                if (searches.length > 50) {
                    return searches.slice(0, 50);
                }
                return searches;
            } catch (e) {
                console.error('Error parsing JSON from cookies', e);
                return []; // 오류가 발생하면 빈 배열 반환
            }
        }
    }
    return []; // 'searches' 쿠키가 없는 경우 빈 배열 반환
}


$(document).ready(function(){	

		$("#denkriModel").hover(function(){
			$("#customTooltip").show();
		}, function(){
			$("#customTooltip").hide();
		});

		$("#searchBtn").click(function(){ 	
			  saveSearch(); 
		 });		



});

$(document).ready(function() {
    $('.search-condition').change(function() {
        // 모든 체크박스의 선택을 해제합니다.
        $('.search-condition').not(this).prop('checked', false);

        // 선택된 체크박스의 값으로 `check` 필드를 업데이트합니다.
        var condition = $(this).is(":checked") ? $(this).val() : '';
        $("#check").val(condition);

        // 검색 입력란을 비우고 폼을 제출합니다.
        // $("#search").val('');                                                      
        $('#board_form').submit();  
    });
});


$(document).ready(function(){	
	
    // showstatus 요소와 showstatusframe 요소가 페이지에 존재하는지 확인
    var showstatus = document.getElementById('showstatus');
    var showstatusframe = document.getElementById('showstatusframe');
    
    // 요소가 존재하지 않으면 나머지 코드는 실행하지 않음
    if (!showstatus || !showstatusframe) {
        return;
    }

    var hideTimeoutstatus; // 프레임을 숨기기 위한 타이머 변수 

    // 요소가 존재한다면 이벤트 리스너를 추가
    showstatus.addEventListener('mouseenter', function(event) {
        clearTimeout(hideTimeoutstatus);  // 이미 설정된 타이머가 있다면 취소
        showstatusframe.style.top = (showstatus.offsetTop + showstatus.offsetHeight) + 'px';
        showstatusframe.style.left = showstatus.offsetLeft + 'px';
        showstatusframe.style.display = 'block';
    });

    showstatus.addEventListener('mouseleave', startstatusHideTimer);

    showstatusframe.addEventListener('mouseenter', function() {
        clearTimeout(hideTimeoutstatus);  // 이미 설정된 타이머가 있다면 취소
    });

    showstatusframe.addEventListener('mouseleave', startstatusHideTimer);

    // 타이머를 시작하는 함수
    function startstatusHideTimer() {
        hideTimeoutstatus = setTimeout(function() {
            showstatusframe.style.display = 'none';
        }, 50);  // 300ms 후에 프레임을 숨깁니다.
    }
	
});

</script>
  