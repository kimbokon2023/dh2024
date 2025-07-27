<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

$title_message = '구매 발주(중국) 리스트'; 

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
$tablename = 'material_order'; 

$check = isset($_REQUEST['check']) ? $_REQUEST['check'] : '';
$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';  
$fromdate = isset($_REQUEST['fromdate']) ? $_REQUEST['fromdate'] : '';  
$todate = isset($_REQUEST['todate']) ? $_REQUEST['todate'] : '';  
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  
   
function decodeList($jsonData) {
    $decoded = json_decode($jsonData, true);
    if (is_array($decoded)) {
        $result = array_map(function($item) {
            $col1  = isset($item['col1']) ? $item['col1'] : '';
            $col2  = isset($item['col2']) ? $item['col2'] : '';
            $col3  = isset($item['col3']) ? $item['col3'] : '';
            $col4  = isset($item['col4']) ? $item['col4'] : '';
            $col5  = isset($item['col5']) ? $item['col5'] : '';
            $col6  = isset($item['col6']) ? $item['col6'] : '';
            $col7  = isset($item['col7']) ? $item['col7'] : '';
            $col8  = isset($item['col8']) ? $item['col8'] : '';
            $col9  = isset($item['col9']) ? $item['col9'] : '';
            $col10 = isset($item['col10']) ? $item['col10'] : '';
            $col11 = isset($item['col11']) ? $item['col11'] : '';
            $col12 = isset($item['col12']) ? $item['col12'] : '';
            $col13 = isset($item['col13']) ? $item['col13'] : '';
            $col14 = isset($item['col14']) ? $item['col14'] : '';
            $col15 = isset($item['col15']) ? $item['col15'] : '';
            // return "모델: $col1, 구매수량: $col2, 단가: $col3, 금액: $col4, 비고: $col5, 1차 입고일: $col6, 1차 입고수량: $col7, 2차 입고일: $col8, 2차 입고수량: $col9, 3차 입고일: $col10, 3차 입고수량: $col11, 구매수량합: $col12, 입고합: $col13, 구매입고차이: $col14, 상태: $col15";
            return "$col1, 구매량: $col12, 입고량: $col13, 차이: $col14 ";
        }, $decoded);
        return implode('<br>', $result);
    }
    return '';
}

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

// 현재 날짜
$currentDate = date("Y-m-d");

// fromdate 또는 todate가 빈 문자열이거나 null인 경우
if ($fromdate === "" || $fromdate === null || $todate === "" || $todate === null) {
    $fromdate = date("Y-m-d", strtotime("-4 weeks", strtotime($currentDate))); // 4주 전 날짜
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
$common= $SettingDate . " BETWEEN '$fromdate' AND '$Transtodate' AND is_deleted IS NULL ";
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

// print '$sql : ' . $sql;  
$current_condition = $check;

try {
    $stmh = $pdo->query($sql);
    $rows = $stmh->fetchAll(PDO::FETCH_ASSOC); // 모든 데이터를 한 번에 가져오기
    $total_row = count($rows); // 가져온 데이터의 행 수 계산
    $start_num = $total_row;
} catch (PDOException $Exception) {
    echo "오류: " . $Exception->getMessage();
}
?>
<form id="board_form" name="board_form" method="post" action="list.php?mode=search">     
<input type="hidden" id="check" name="check" value="<?=$check?>" size="5">     
<div class="container-fluid">  
    <div class="card mb-2 mt-2">  
        <div class="card-body">       
            <div class="d-flex mt-1 mb-2 justify-content-center align-items-center">         
                <h5 class="mx-1">  <?=$title_message?>  <?=$titletag?> </h5>  &nbsp;&nbsp;
				<button type="button" class="btn btn-dark btn-sm mx-2"  onclick='location.reload();' > <i class="bi bi-arrow-clockwise"></i> </button>  	   		  						
            </div>    
            <div class="d-flex mt-1 mb-1 justify-content-center align-items-center">       
                <ion-icon name="caret-forward-outline"></ion-icon> <?= $total_row ?> &nbsp;                           
                <input type="date" id="fromdate" name="fromdate" class="form-control" style="width:100px;" value="<?=$fromdate?>">  &nbsp;   ~ &nbsp;  
                <input type="date" id="todate" name="todate" class="form-control me-1" style="width:100px;" value="<?=$todate?>">  &nbsp;  
            
                <div class="inputWrap">
                    <input type="text" id="search" name="search" value="<?=$search?>" onkeydown="JavaScript:SearchEnter();" autocomplete="off" class="form-control" style="width:150px;"> &nbsp;            
                    <button class="btnClear"></button>
                </div>                
                <div id="autocomplete-list"></div>    
                &nbsp;
                <button id="searchBtn" type="button" class="btn btn-dark  btn-sm"> <i class="bi bi-search"></i> 검색 </button>          
                &nbsp;&nbsp;&nbsp;                        
                <button type="button" class="btn btn-dark  btn-sm me-1" id="writeBtn"> <i class="bi bi-pencil-fill"></i> 신규 </button> 
            </div>               
        </div> <!--card-body-->
    </div> <!--card -->   
</div> <!--container-fluid -->   
</form>    

<div class="container-fluid mt-1 mb-3">    
<div class="table-responsive">     
<table class="table table-hover" id="myTable">
  <thead class="table-info">
    <tr>
      <th class="text-center w50px">번호</th>      
      <th class="text-center w80px" >구매발주일</th>
      <th class="text-center w200px" >모터</th>   
      <th class="text-center w200px" >무선콘트롤러</th>   
      <th class="text-center w200px" >유선콘트롤러</th>   
      <th class="text-center w200px" >무선제어기</th>   
      <th class="text-center w200px" >유선제어기</th>   
      <th class="text-center w200px" >브라켓트</th>   
      <th class="text-center w80px"> 완료여부</th>
      <th class="text-center w200px"> 비고</th>
    </tr>        
  </thead>      
  <tbody>
<?php
try {
    $stmh = $pdo->query($sql);
    $rows = $stmh->fetchAll(PDO::FETCH_ASSOC); // 모든 데이터를 한 번에 가져오기
    $total_row = count($rows); // 가져온 데이터의 행 수 계산
    $start_num = $total_row;
	
	$judgement = '';
			
	function checkAllCompleted($jsonData) {
		// JSON 데이터를 배열로 디코딩
		$decoded = json_decode($jsonData, true);
		if (!is_array($decoded)) {
			return false;
		}
		// 각 행을 순회하면서 col15 값이 "완료"인지 확인
		foreach ($decoded as $item) {
			$col15 = isset($item['col15']) ? trim($item['col15']) : '';
			if ($col15 !== '완료') {
				return false;
			}
		}
		return true;
	}

    
    foreach ($rows as $row) {
        
        include $_SERVER['DOCUMENT_ROOT'] . '/material_order/_row.php';  

		$motorlist = isset($row['motorlist']) ? decodeList($row['motorlist']) : '';
		$wirelessClist = isset($row['wirelessClist']) ? decodeList($row['wirelessClist']) : '';
		$wireClist = isset($row['wireClist']) ? decodeList($row['wireClist']) : '';
		$wirelessLinklist = isset($row['wirelessLinklist']) ? decodeList($row['wirelessLinklist']) : '';
		$wireLinklist = isset($row['wireLinklist']) ? decodeList($row['wireLinklist']) : '';
		$bracketlist = isset($row['bracketlist']) ? decodeList($row['bracketlist']) : '';
		$memo = isset($row['memo']) ? $row['memo'] : '';
		
		$judgement = '완료'; // 기본적으로 완료로 가정
		if (
			!checkAllCompleted($row['motorlist']) ||
			!checkAllCompleted($row['wirelessClist']) ||
			!checkAllCompleted($row['wireClist']) ||
			!checkAllCompleted($row['wirelessLinklist']) ||
			!checkAllCompleted($row['wireLinklist']) ||
			!checkAllCompleted($row['bracketlist'])
		) {
			$judgement = '';
		}
		?>
		<tr onclick="redirectToView('<?= $num ?>', '<?= $tablename ?>')">
			<td class="text-center"><?= $start_num ?></td>
			<td class="text-center"><?= $orderDate ?></td>
			<td class="text-start"><?= $motorlist ?></td>
			<td class="text-start"><?= $wirelessClist ?></td>
			<td class="text-start"><?= $wireClist ?></td>
			<td class="text-start"><?= $wirelessLinklist ?></td>
			<td class="text-start"><?= $wireLinklist ?></td>
			<td class="text-start"><?= $bracketlist ?></td>
			<td class="text-center fw-bold"><?= $judgement ?></td>
			<td class="text-start"><?= $memo ?></td>
		</tr>

        <?php
        $start_num--;
    }
} catch (PDOException $Exception) {
    echo "오류: " . $Exception->getMessage();
}
?>
    </tbody>  
</table>
</div>
</div>
<div class="container-fluid mt-3 mb-3">
    <? include '../footer_sub.php'; ?>
</div>

<script>
// 페이지 로딩
$(document).ready(function(){    
    var loader = document.getElementById('loadingOverlay');
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
        "order": [[2, 'desc']] // 출하예정기준 내림정렬
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
    var url = "write_form.php?mode=view&num=" + num + "&tablename=" + tablename;          
    customPopup(url, '', 1850, 900);             
}

$(document).ready(function(){    
    $("#writeBtn").click(function(){         
        var tablename = '<?php echo $tablename; ?>';        
        var url = "write_form.php?tablename=" + tablename;                 
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
	saveLogData('구매서 작성 리스트'); 
});

</script>
</body>
</html>