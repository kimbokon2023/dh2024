<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

$title_message = ' 4차수형태 구매 발주(중국) 입고 리스트'; 

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

$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';  
$fromdate = isset($_REQUEST['fromdate']) ? $_REQUEST['fromdate'] : '';  
$todate = isset($_REQUEST['todate']) ? $_REQUEST['todate'] : '';  
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  

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
<div class="container w-75">  
    <div class="d-flex justify-content-center">
    <div class="card mb-2 mt-2 w-75">  
        <div class="card-body">       
            <div class="d-flex mt-1 mb-4 justify-content-center align-items-center">         
                <h5 class="mx-1">  <?=$title_message?>  <?=$titletag?> </h5>  &nbsp;&nbsp;
				<button type="button" class="btn btn-dark btn-sm mx-2"  onclick='location.reload();' > <i class="bi bi-arrow-clockwise"></i> </button>  	 
				<button type="button" class="btn btn-success btn-sm mx-3"  onclick='location.href="list.php";' title="발주서 이동" >  <i class="bi bi-list-ol"></i> </button>  	   		  										
				<?php if(intval($level) === 1) : ?>
					<button type="button" class="btn btn-danger btn-sm "  onclick='location.href="list_account.php";' title="송금액 이동" >  <i class="bi bi-currency-dollar"></i> </button>  	   		  										
				<?php endif; ?>					
            </div>    
            <div class="d-flex mt-1 mb-1 justify-content-center align-items-center">       
                ▷  <?= $total_row ?> &nbsp;                           
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
                <!-- <button type="button" class="btn btn-dark  btn-sm me-1" id="writeBtn"> <i class="bi bi-pencil-fill"></i> 신규 </button>  -->
            </div>               
        </div> <!--card-body-->
    </div> <!--card -->   
    </div> <!--d-flex justify-content-center-->
</div> <!--container w-75 -->   
</form>    

<div class="container w-75 mt-1 mb-3">    
<div class="table-responsive">     
<table class="table table-bordered table-hover w-75" id="myTable" style="border-collapse:collapse;">
  <thead class="table-success">
    <tr>
      <th class="text-center">발주일자</th>
      <th class="text-center">품목</th>
      <th class="text-center">발주 수량</th>
      <th class="text-center">입고 수량</th>
      <th class="text-center">발주금액(CNY)</th>
      <th class="text-center">입고금액(CNY)</th>
      <th class="text-center">미입고금액(CNY)</th>
    </tr>
  </thead>
  <tbody>
<?php
$categoryList = ['모터', '연동제어기', '운송비', '부속자재'];

// echo '<pre>';
// print_r($rows);
// echo '</pre>';

foreach ($rows as $row) {
    $orderDate = $row['orderDate'];
    $orderlist = json_decode($row['orderlist'], true);
    $num = $row['num'];
    $catSum = [];
    foreach ($categoryList as $cat) {
        $catSum[$cat] = [
            'qty' => 0,
            'input_qty' => 0,
            'order_amt' => 0,
            'input_amt' => 0,
            'remain_amt' => 0
        ];
    }
    if (is_array($orderlist)) {
        foreach ($orderlist as $item) {
            $cat = isset($item['col0']) ? $item['col0'] : '부속자재';
            $qty = isset($item['col3']) ? floatval(str_replace(',', '', $item['col3'])) : 0;
            $input_qty = isset($item['col20']) ? floatval(str_replace(',', '', $item['col20'])) : 0;
            $order_amt = isset($item['col6']) ? floatval(str_replace(',', '', $item['col6'])) : 0;
            // 입고금액: 1~4차 모두 합산
            $input_amt = 0;
            foreach ([23, 25, 27, 29] as $col) {
                $input_amt += isset($item['col'.$col]) ? floatval(str_replace(',', '', $item['col'.$col])) : 0;
            }
            $remain_amt = $order_amt - $input_amt;
            if (!isset($catSum[$cat])) $cat = '부속자재';
            $catSum[$cat]['qty'] += $qty;
            $catSum[$cat]['input_qty'] += $input_qty;
            $catSum[$cat]['order_amt'] += $order_amt;
            $catSum[$cat]['input_amt'] += $input_amt;
            $catSum[$cat]['remain_amt'] += $remain_amt;
        }
    }
    $first = true;
    foreach ($categoryList as $cat) {
        echo '<tr onclick="redirectToView(\'' . $num . '\', \'$tablename\')">';
        if ($first) {
            echo '<td rowspan="4" class="text-center" style="vertical-align:middle;">' . htmlspecialchars($orderDate) . '</td>';
            $first = false;
        }
        echo '<td class="text-center">' . $cat . '</td>';
        echo '<td class="text-end">' . ($catSum[$cat]['qty'] ? number_format($catSum[$cat]['qty']) : '') . '</td>';
        echo '<td class="text-end text-primary">' . ($catSum[$cat]['input_qty'] ? number_format($catSum[$cat]['input_qty']) : '') . '</td>';
        echo '<td class="text-end ">' . ($catSum[$cat]['order_amt'] ? number_format($catSum[$cat]['order_amt'], 2) : '') . '</td>';
        echo '<td class="text-end fw-bold text-primary">' . ($catSum[$cat]['input_amt'] ? number_format($catSum[$cat]['input_amt'], 2) : '') . '</td>';
        echo '<td class="text-end fw-bold">' . ($catSum[$cat]['remain_amt'] ? number_format($catSum[$cat]['remain_amt'], 2) : '') . '</td>';
        echo '</tr>';
    }
}
?>
  </tbody>
</table>
</div>
</div>
<div class="container w-75 mt-3 mb-3">
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
    var url = "write_input.php?mode=view&num=" + num + "&tablename=" + tablename;          
    customPopup(url, '', 1650, 900);             
}

$(document).ready(function(){    
    $("#writeBtn").click(function(){         
        var tablename = '<?php echo $tablename; ?>';        
        var url = "write_input.php?tablename=" + tablename;                 
        customPopup(url, '', 1650, 900);       
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

</script>
</body>
</html>