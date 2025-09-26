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
<style>
.accordion-button:not(.collapsed) {
    background-color: #e7f1ff;
    color: #0c63e4;
}
.accordion-button:focus {
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}
.accordion-item {
    border: 1px solid rgba(0, 0, 0, 0.125);
    margin-bottom: 0.5rem;
}
.accordion-body {
    padding: 0;
}
.badge {
    font-size: 0.75em;
}
#myTable th, #myTable td {
  border: 1px solid #333 !important;
}
</style>
</head>
<body>    

<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php'); ?>   

<?php
$tablename = 'm_order'; 

$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';  
$fromdate = isset($_REQUEST['fromdate']) ? $_REQUEST['fromdate'] : '';  
$todate = isset($_REQUEST['todate']) ? $_REQUEST['todate'] : '';  
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  
   
function decodeList($jsonData) {
    $decoded = json_decode($jsonData, true);
    if (is_array($decoded)) {
        // 카테고리별 데이터 그룹화
        $categories = [
            '모터' => ['items' => [], 'totalQty' => 0, 'totalAmount' => 0],
            '연동제어기' => ['items' => [], 'totalQty' => 0, 'totalAmount' => 0],
            '운송비' => ['items' => [], 'totalQty' => 0, 'totalAmount' => 0],
            '부속자재' => ['items' => [], 'totalQty' => 0, 'totalAmount' => 0]
        ];
        
        $totalQuantity = 0;
        $totalAmount = 0;
        
        // 데이터를 카테고리별로 분류
        foreach ($decoded as $item) {
            $category = isset($item['col0']) ? trim($item['col0']) : '모터';
            $col1 = isset($item['col1']) ? trim($item['col1']) : '';
            $col2 = isset($item['col2']) ? trim($item['col2']) : '';
            $col3 = isset($item['col3']) ? trim($item['col3']) : '';
            $col4 = isset($item['col4']) ? trim($item['col4']) : '';
            $col5 = isset($item['col5']) ? trim($item['col5']) : '';
            $col6 = isset($item['col6']) ? trim($item['col6']) : '';
            
            // 콤마 제거 후 숫자 변환
            $col3_clean = str_replace(',', '', $col3);
            $col4_clean = str_replace(',', '', $col4);
            $col6_clean = str_replace(',', '', $col6);
            
            $quantity = is_numeric($col3_clean) ? (float)$col3_clean : 0;
            $amount = is_numeric($col6_clean) ? (float)$col6_clean : 0;
            
            // 카테고리가 정의되지 않은 경우 '부속자재'로 분류
            if (!isset($categories[$category])) {
                $category = '부속자재';
            }
            
            $categories[$category]['items'][] = [
                'col1' => $col1,
                'col2' => $col2,
                'col3' => $col3,
                'col4' => $col4,
                'col5' => $col5,
                'col6' => $col6,
                'quantity' => $quantity,
                'amount' => $amount
            ];
            
            $categories[$category]['totalQty'] += $quantity;
            $categories[$category]['totalAmount'] += $amount;
            
            $totalQuantity += $quantity;
            $totalAmount += $amount;
        }
        
        $table = '<div class="accordion" id="categoryAccordion">';
        
        foreach ($categories as $categoryName => $categoryData) {
            if (count($categoryData['items']) > 0) {
                $categoryId = str_replace([' ', '-'], '', $categoryName);
                $table .= '<div class="accordion-item">';
                $table .= '<h2 class="accordion-header" id="heading' . $categoryId . '">';
                $table .= '<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse' . $categoryId . '" aria-expanded="false" aria-controls="collapse' . $categoryId . '">';
                $table .= '<strong>' . $categoryName . '</strong> ';
                $table .= '<span class="badge bg-primary ms-2">수량: ' . number_format($categoryData['totalQty']) . '</span>';
                $table .= '<span class="badge bg-success ms-2">금액: ' . number_format($categoryData['totalAmount']) . ' 위엔</span>';
                $table .= '</button>';
                $table .= '</h2>';
                $table .= '<div id="collapse' . $categoryId . '" class="accordion-collapse collapse" aria-labelledby="heading' . $categoryId . '" data-bs-parent="#categoryAccordion">';
                $table .= '<div class="accordion-body p-0">';
                $table .= '<table class="table table-sm table-bordered mb-0" style="font-size: 11px;">';
                $table .= '<thead class="table-light">';
                $table .= '<tr>';
                $table .= '<th class="text-center" style="width: 20%;">품명</th>';
                $table .= '<th class="text-center" style="width: 20%;">품목코드</th>';
                $table .= '<th class="text-center" style="width: 5%;">수량</th>';
                $table .= '<th class="text-center" style="width: 5%;">단가(위엔)</th>';
                $table .= '<th class="text-center" style="width: 5%;">금액(위엔)</th>';
                $table .= '<th class="text-center" style="width: 30%;">비고</th>';
                $table .= '</tr>';
                $table .= '</thead>';
                $table .= '<tbody>';
                
                foreach ($categoryData['items'] as $item) {
                    $table .= '<tr>';
                    $table .= '<td class="text-start">' . htmlspecialchars($item['col1']) . '</td>';
                    $table .= '<td class="text-center">' . htmlspecialchars($item['col2']) . '</td>';
                    $table .= '<td class="text-end">' . (is_numeric($item['col3']) ? number_format($item['col3']) : $item['col3']) . '</td>';
                    $table .= '<td class="text-end">' . (is_numeric($item['col4']) ? number_format($item['col4']) : $item['col4']) . '</td>';
                    $table .= '<td class="text-end">' . (is_numeric($item['col6']) ? number_format($item['col6']) : $item['col6']) . '</td>';
                    $table .= '<td class="text-start">' . htmlspecialchars($item['col5']) . '</td>';
                    $table .= '</tr>';
                }
                
                // 카테고리별 합계 행
                $table .= '<tr class="table-warning fw-bold">';
                $table .= '<td class="text-center" colspan="2">' . $categoryName . ' 합계</td>';
                $table .= '<td class="text-end">' . number_format($categoryData['totalQty']) . '</td>';
                $table .= '<td class="text-end">-</td>';
                $table .= '<td class="text-end">' . number_format($categoryData['totalAmount']) . '</td>';
                $table .= '<td class="text-center">-</td>';
                $table .= '</tr>';
                
                $table .= '</tbody>';
                $table .= '</table>';
                $table .= '</div>';
                $table .= '</div>';
                $table .= '</div>';
            }
        }
        
        // 전체 합계 섹션
        if (count($decoded) > 0) {
            $table .= '<div class="mt-2 p-2 bg-light border rounded">';
            $table .= '<div class="d-flex justify-content-between align-items-center">';
            $table .= '<strong>전체 합계</strong>';
            $table .= '<div>';
            $table .= '<span class="badge bg-primary me-2">총 수량: ' . number_format($totalQuantity) . '</span>';
            $table .= '<span class="badge bg-success">총 금액: ' . number_format($totalAmount) . ' 위엔</span>';
            $table .= '</div>';
            $table .= '</div>';
            $table .= '</div>';
        }
        
        $table .= '</div>';
        
        return $table;
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
    $fromdate = date("2025-01-01"); 
    $todate = date("Y-m-d", strtotime("+2 months", strtotime($currentDate))); // 2개월 후 날짜
    $Transtodate = $todate;
} else {
    // fromdate와 todate가 모두 설정된 경우 (기존 로직 유지)
    $Transtodate = $todate;
}

$sql="SELECT * FROM {$tablename}";

$sum=array();
$now = date("Y-m-d");     // 현재 날짜와 크거나 같으면 출하예정으로 구분

$orderby=" ORDER BY num DESC";
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
<div class="container">  
    <div class="d-flex justify-content-center">
    <div class="card mb-2 mt-2 w-75">
        <div class="card-body">
            <div class="card-header d-flex justify-content-center align-items-center mb-2">   
                <span class="text-center fs-5">  <?=$title_message?>   </span>     
				<button type="button" class="btn btn-dark btn-sm mx-1" onclick='location.reload()'>  <i class="bi bi-arrow-clockwise"></i> </button>      						 
				<small class="ms-5 text-muted"> 중국측 구매를 위한 발주서 (발주서 작성 후 중국측 발송)</small>              
				<button type="button" class="btn btn-primary btn-sm mx-3"  onclick='location.href="list_input.php";' title="발주 입고창 이동" >  <i class="bi bi-list-columns"></i> </button>  	   		  						
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
                <button type="button" class="btn btn-dark  btn-sm me-1" id="writeBtn"> <i class="bi bi-pencil-fill"></i> 신규 </button> 
            </div>               
        </div> <!--card-body-->
    </div> <!--card -->   
    </div> <!--d-flex justify-content-center-->
</div> <!--container-fluid -->   
</form>    

<div class="container mt-1 mb-3">    
<div class="table-responsive">     
<table class="table table-bordered table-hover w-75" id="myTable" style="border-collapse:collapse;">
    <thead class="table-info">
    <tr>
        <th class="text-center">발주일자</th>
        <th class="text-center">품목</th>
        <th class="text-center">발주수량</th>
        <th class="text-center">발주금액(CNY)</th>
        <th class="text-center">누적금액(CNY)</th>
    </tr>
    </thead>
    <tbody>
<?php
$categoryList = ['모터', '연동제어기', '운송비', '부속자재'];
foreach ($rows as $row) {
    $orderDate = $row['orderDate'];
    $orderlist = json_decode($row['orderlist'], true);
    $num = $row['num'];
    $catSum = [];
    foreach ($categoryList as $cat) {
        $catSum[$cat] = ['qty' => 0, 'amount' => 0];
    }
    if (is_array($orderlist)) {
        foreach ($orderlist as $item) {
            $cat = isset($item['col0']) ? $item['col0'] : '부속자재';
            $qty = isset($item['col3']) ? floatval(str_replace(',', '', $item['col3'])) : 0;
            $amt = isset($item['col6']) ? floatval(str_replace(',', '', $item['col6'])) : 0;
            if (!isset($catSum[$cat])) $cat = '부속자재';
            $catSum[$cat]['qty'] += $qty;
            $catSum[$cat]['amount'] += $amt;
        }
    }
    // 발주일자별 누적합
    $rowCumulative = 0;
    $first = true;
    foreach ($categoryList as $cat) {
        $rowCumulative += $catSum[$cat]['amount'];
        echo '<tr onclick="redirectToView(\'' . $num . '\', \'$tablename\')">';
        if ($first) {
            echo '<td rowspan="4" class="text-center" style="vertical-align:middle;">' . htmlspecialchars($orderDate) . '</td>';
            $first = false;
        }
        echo '<td class="text-center">' . $cat . '</td>';
        echo '<td class="text-end">' . ($catSum[$cat]['qty'] ? number_format($catSum[$cat]['qty']) : '') . '</td>';
        echo '<td class="text-end">' . ($catSum[$cat]['amount'] ? number_format($catSum[$cat]['amount'], 2) : '') . '</td>';
        echo '<td class="text-end fw-bold">' . ($rowCumulative ? number_format($rowCumulative, 2) : '') . '</td>';
        echo '</tr>';
    }
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