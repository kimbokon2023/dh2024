<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

// 첫 화면 표시 문구
$title_message = "DH모터 부자재 (원가 및 단가)" ;
?>
<?php 
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
	 
<?php
$tablename = 'fee_sub'; 

$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';  
$fromdate = isset($_REQUEST['fromdate']) ? $_REQUEST['fromdate'] : '';  
$todate = isset($_REQUEST['todate']) ? $_REQUEST['todate'] : '';  
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  
// 메뉴를 표현할지 판단하는 header
$header = isset($_REQUEST['header']) ? $_REQUEST['header'] : '';  
if($header !== 'noheader')
	require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php');


require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

// 부자재에 대한 배열 가져오기
$sql = "select * from " . $DB . ".fee_sub order by basicdate desc limit 1";

try {
    $stmh = $pdo->query($sql);
    $rows = $stmh->fetchAll(PDO::FETCH_ASSOC); // 모든 데이터를 한 번에 가져옴
    $total_row = count($rows); // 가져온 행의 수를 계산
    $rows = array_reverse($rows); // 배열을 역순으로 정렬
    foreach ($rows as $row) {
        // 각 행에 대한 JSON 데이터를 디코드하고 필요에 따라 필터링
        $sub_item = array_filter(json_decode($row['item'], true) ?? [], function ($value) {
            return trim($value) !== '';
        });
    }
} catch (PDOException $Exception) {
    echo "오류: " . $Exception->getMessage();
}

array_unshift($sub_item, ''); // 배열의 맨 앞에 빈 문자열 추가

// 현재 날짜
$currentDate = date("Y-m-d");

// fromdate 또는 todate가 빈 문자열이거나 null인 경우
if ($fromdate === "" || $fromdate === null || $todate === "" || $todate === null) {
    $fromdate = date("Y-m-d", strtotime("2024-01-01"));
    $todate = $currentDate; // 현재 날짜
	$Transtodate = $todate;
} else {
    // fromdate와 todate가 모두 설정된 경우 (기존 로직 유지)
    $Transtodate = $todate;
}
		
$sql=" select * from " . $DB . "." . $tablename ;

$sum=array();
  	
$now = date("Y-m-d");	 // 현재 날짜와 크거나 같으면 출고예정으로 구분

$orderby="order by basicdate desc ";
 
$attached=''; 

$whereattached = '';
		
$SettingDate=" basicdate "; 
	 
$common= $SettingDate . " between '$fromdate' and '$Transtodate' and is_deleted IS NULL ";
		
$andPhrase= " and " . $common  . $orderby ;
$wherePhrase= " where " . $common  . $orderby ;


// 검색을 위해 모든 검색변수 공백제거
$search = str_replace(' ', '', $search);  

if($search==""){
	if($whereattached!=='')
		$sql="select * from " . $DB . "." . $tablename. $whereattached . $andPhrase; 					                 
	else
		$sql="select * from " . $DB . "." . $tablename. $wherePhrase ;					                 
	}	
	elseif($search!="" ) {
		$sql ="select * from " . $DB . "." . $tablename . " where (replace(searchtag,' ','') like '%$search%' ) " . $attached . $andPhrase; 					                       
	}


try {
    $stmh = $pdo->query($sql);
    $rows = $stmh->fetchAll(PDO::FETCH_ASSOC); // 모든 데이터를 한 번에 가져오기
    $total_row = count($rows); // 가져온 데이터의 행 수 계산
    // var_dump($total_row);
    $start_num = $total_row;
    
} catch (PDOException $Exception) {
    echo "오류: " . $Exception->getMessage();
}
		 
 ?>

<form id="board_form" name="board_form" method="post" action="list.php?mode=search">  		
	
<div class="container-fluid">  
	<div class="card mb-2 mt-2">  
	<div class="card-body">  	 
	<div class="d-flex  p-1 m-1 mt-1 justify-content-center align-items-center "> 		
		   <h5>  <?=$title_message?> </h5>				
		</div>	

	    <div class="d-flex  p-1 m-1 mt-1 mb-1 justify-content-center align-items-center"> 	  
			<ion-icon name="caret-forward-outline"></ion-icon> <?= $total_row ?> &nbsp; 		
			   <input type="date" id="fromdate" name="fromdate"   class="form-control"   style="width:100px;" value="<?=$fromdate?>" >  &nbsp;   ~ &nbsp;  
			   <input type="date" id="todate" name="todate"  class="form-control"   style="width:100px;" value="<?=$todate?>" >  &nbsp;     </span> 
			   &nbsp;&nbsp;		 
	
		<div class="inputWrap">
			<input type="text" id="search" name="search" value="<?=$search?>" onkeydown="JavaScript:SearchEnter();" autocomplete="off"  class="form-control" style="width:150px;" > &nbsp;			
			<button class="btnClear"></button>
		</div>				
		<div id="autocomplete-list">				
		</div>	
		  &nbsp;
		  <button id="searchBtn" type="button" class="btn btn-dark  btn-sm" > <i class="bi bi-search"></i> 검색 </button> 		  
		  &nbsp;&nbsp;&nbsp;		    
				 <button type="button" class="btn btn-dark  btn-sm me-1" id="writeBtn"> <i class="bi bi-pencil-fill"></i> 신규  </button> 
                 <button type="button" class="btn btn-info  btn-sm me-1" id="guideBtn"> <i class="bi bi-question-circle"></i> 새로 추가할 경우 간단 설명서  </button> 
				 <?php
				 if($header == 'noheader')
				 {
				 ?>
				  <button type="button" class="btn btn-dark  btn-sm me-1" id="reflectBtn"> <i class="bi bi-database-fill-down"></i> 적용하기  </button> 				
				 <?php } ?>					 
         </div> 	 
   </div> <!--card-body-->
   </div> <!--card -->   
</div> <!--container-fluid -->   
<div class="container-fluid">  
<div class="d-flex justify-content-center align-items-center"> 		
<table class="table table-hover" id="myTable">
  <thead class="table-primary">
    <tr>
		  <th class="text-center" style="width:50px;"> 번호</th>      
		  <th class="text-center" style="width:100px;"> 기준일</th>
		  <th class="text-center" style="width:600px;"> 내용</th>      
		  <th class="text-center" style="width:400px;"> 메모</th>      
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


	$contentlist ='부자재 원가 및 단가 지정';
	
    foreach ($rows as $row) {
        // HTML 출력 부분
		include '_row.php';		
		
        ?>
        <tr onclick="redirectToView('<?= $row['num'] ?>', '<?= $tablename ?>')">
            <td class="text-center"><?= $start_num ?></td>
            <td class="text-center"><?= $row['basicdate'] ?></td>            
            <td class="text-center"><?= $contentlist ?></td>            
            <td class="text-start "><?= $row['memo'] ?></td>
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
		<? 
		
		if($header !== 'noheader')	
			include '../footer_sub.php'; 
		
		?>
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
var feesubpageNumber; // 현재 페이지 번호 저장을 위한 전역 변수

$(document).ready(function() {			
    // DataTables 초기 설정
    dataTable = $('#myTable').DataTable({
        "paging": true,
        "ordering": true,
        "searching": true,
        "pageLength": 25,
        "lengthMenu": [25, 50, 100, 200, 500, 1000],
        "language": {
            "lengthMenu": "Show _MENU_ entries",
            "search": "Live Search:"
        },
        "order": [[0, 'desc']]
    });

    // 페이지 번호 복원 (초기 로드 시)
    var savedPageNumber = getCookie('feesubpageNumber');
    if (savedPageNumber) {
        dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
    }

    // 페이지 변경 이벤트 리스너
    dataTable.on('page.dt', function() {
        var feesubpageNumber = dataTable.page.info().page + 1;
        setCookie('feesubpageNumber', feesubpageNumber, 10); // 쿠키에 페이지 번호 저장
    });

    // 페이지 길이 셀렉트 박스 변경 이벤트 처리
    $('#myTable_length select').on('change', function() {
        var selectedValue = $(this).val();
        dataTable.page.len(selectedValue).draw(); // 페이지 길이 변경 (DataTable 파괴 및 재초기화 없이)

        // 변경 후 현재 페이지 번호 복원
        savedPageNumber = getCookie('feesubpageNumber');
        if (savedPageNumber) {
            dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
        }
    });
});

function restorePageNumber() {
    var savedPageNumber = getCookie('feesubpageNumber');
    if (savedPageNumber) {
        dataTable.page(parseInt(savedPageNumber) - 1).draw('page');
    }
	location.reload(true);
}

function redirectToView(num, tablename) {	
    var url = "write_form.php?mode=view&num=" + num + "&tablename=" + tablename;          
	customPopup(url, '', 1200, 900); 		    
}

$(document).ready(function(){	
	$("#writeBtn").click(function(){ 		
		var tablename = '<?php echo $tablename; ?>';		
		var url = "write_form.php?tablename=" + tablename; 				
		customPopup(url, '', 1200, 900); 	
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
		
		var feesubpageNumber = 1;
		setCookie('feesubpageNumber', feesubpageNumber, 10); // 쿠키에 페이지 번호 저장		
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

         $("#guideBtn").click(function(){ 		
            var url = "../fee_controller/guide.php"; 				
            customPopup(url, '', 800, 600); 	
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
  
  
<!-- 부모창의 select에 부자재 리스트 update 실행 -->  
<script>
$(document).ready(function() {
	$('#reflectBtn').click(function() {
		// #sub_dynamicTable 내의 col2[] 이름을 가진 select 요소만 타겟팅
		var parentSelect = window.opener.document.querySelector('#sub_dynamicTable [name="col2[]"]');

		// 예제 데이터, 실제로는 PHP에서 JSON으로 인코딩된 데이터를 사용해야 함
		var subItems = <?php echo json_encode($sub_item); ?>;

		// 기존 옵션을 클리어
		$(parentSelect).empty();

		// 새로운 옵션 추가
		subItems.forEach(function(item) {
			var option = $('<option></option>').val(item).text(item);
			$(parentSelect).append(option);
		});
		
		Toastify({
				text: "변경 내용이 적용되었습니다.",
				duration: 3000,
				close:true,
				gravity:"top",
				position: "center",
				style: {
					background: "linear-gradient(to right, #00b09b, #96c93d)"
				},
			}).showToast();	

		setTimeout(function(){									
					if (window.opener && !window.opener.closed) {
					  self.close();
					}								
		}, 1000);			
		
	});
});

</script>
