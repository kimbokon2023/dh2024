<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if(!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php"); 
    exit;
}   
include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';
$title_message = '품목코드 (모터,브라켓트,연동제어기,콘트럴박스 단가설정 자료 추출)'; 

?>
 
<link href="css/style.css" rel="stylesheet">   
<title> <?=$title_message?> </title>

<style>
	.dataTables_filter {
		text-align: center !important;
	}
	.dataTables_filter label {
		display: flex;
		justify-content: center;
		align-items: center;
	}
	.dataTables_length {
		float: center;
	}
</style>
 

</head>
<body>         

<?php
// 메뉴를 표현할지 판단하는 header
$header = isset($_REQUEST['header']) ? $_REQUEST['header'] : '';  

if($header == 'header')
    require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php');

$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';  
$mode = isset($_REQUEST["mode"]) ? $_REQUEST["mode"] : '';

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

$items = [];

// Helper function to decode JSON safely
function safe_json_decode($json) {
    $decoded = json_decode($json, true);
    return is_array($decoded) ? $decoded : [];
}

// Helper function to generate ecountcode
function generate_ecountcode($volt, $wire, $upweight, $unit, $item) {
    if ($unit !== '브라켓트') {
        $ecountcode = '';
        if ($volt && (strpos($volt, '220') === 0 || strpos($volt, '380') === 0)) {
            $ecountcode .= $volt . '-';
        }
        if ($wire) {
            $ecountcode .= $wire . '-';
        }
        if ($item=='무기둥모터') {   // 무기둥모터 품목 추가
            $ecountcode .= $item . '-';
        }
        if ($upweight) {
            $ecountcode .= str_replace(['k', 'K'], '', $upweight) . '-';
        }
        // Remove the trailing '-' if it exists
        if (substr($ecountcode, -1) === '-') {
            $ecountcode = substr($ecountcode, 0, -1);
        }
        return '모터 ' . $ecountcode;
    } else {
        return '브라켓트 ' . $item;
    }
}

// fee 테이블에서 데이터 추출
$sql_fee = "SELECT ecountcode, item, volt, wire, upweight, unit FROM dbchandj.fee WHERE is_deleted IS NULL ORDER BY num DESC";
try {
    $stmh_fee = $pdo->query($sql_fee);
    while ($row = $stmh_fee->fetch(PDO::FETCH_ASSOC)) {
        $ecountcodes = safe_json_decode($row['ecountcode']);
        $items_array = safe_json_decode($row['item']);
        $volts = safe_json_decode($row['volt']);
        $wires = safe_json_decode($row['wire']);
        $upweights = safe_json_decode($row['upweight']);
        $units = safe_json_decode($row['unit']);
        foreach ($ecountcodes as $index => $item_code) {
            if (isset($items_array[$index]) && !empty($item_code) && !empty($items_array[$index])) {
                $generated_code = generate_ecountcode(
                    $volts[$index] ?? '',
                    $wires[$index] ?? '',
                    $upweights[$index] ?? '',
                    $units[$index] ?? '',
                    $items_array[$index]
                );
                // Exclude exact matches of '모터 ' and '브라켓트 '
                if ($generated_code !== '모터 ' && $generated_code !== '브라켓트 ') {
                    $items[] = [
                        'item_code' => $item_code,
                        'item_name' => $generated_code
                    ];
                }
            }
        }
    }
} catch (PDOException $Exception) {
    echo "오류: " . $Exception->getMessage();
    exit;
}

// fee_controller 테이블에서 데이터 추출
$sql_fee_controller = "SELECT item FROM dbchandj.fee_controller WHERE is_deleted IS NULL ORDER BY num DESC";
try {
    $stmh_fee_controller = $pdo->query($sql_fee_controller);
    while ($row = $stmh_fee_controller->fetch(PDO::FETCH_ASSOC)) {
        $item_codes = safe_json_decode($row['item']);
        foreach ($item_codes as $item_code) {
            if (!empty($item_code)) {
                $items[] = [
                    'item_code' => $item_code,
                    'item_name' => $item_code
                ];
            }
        }
    }
} catch (PDOException $Exception) {
    echo "오류: " . $Exception->getMessage();
    exit;
}

// fee_sub 테이블에서 데이터 추출
$sql_fee_sub = "SELECT itemcode, item FROM dbchandj.fee_sub WHERE is_deleted IS NULL ORDER BY num DESC";
try {
    $stmh_fee_sub = $pdo->query($sql_fee_sub);
    while ($row = $stmh_fee_sub->fetch(PDO::FETCH_ASSOC)) {
        $itemcodes = safe_json_decode($row['itemcode']);
        $items_array = safe_json_decode($row['item']);
        foreach ($itemcodes as $index => $item_code) {
            if (isset($items_array[$index]) && !empty($item_code) && !empty($items_array[$index])) {
                $items[] = [
                    'item_code' => $item_code,
                    'item_name' => $items_array[$index]
                ];
            }
        }
    }
} catch (PDOException $Exception) {
    echo "오류: " . $Exception->getMessage();
    exit;
}

// 중복 항목 제거
$unique_items = [];
foreach ($items as $item) {
    $unique_items[$item['item_code']] = $item;
}
$items = array_values($unique_items);

// 배열을 item_code 기준으로 오름차순 정렬
usort($items, function($a, $b) {
    return strcmp($a['item_code'], $b['item_code']);
});

$total_row = count($items);
?>    

<form id="board_form" name="board_form" method="post" enctype="multipart/form-data">             
    <input type="hidden" id="mode" name="mode" value="<?=$mode?>">             
    <input type="hidden" id="num" name="num"> 
    <input type="hidden" id="header" name="header" value="<?=$header?>">                 
                
<?php 
if($header !== 'header') {
    print '<div class="container-fluid">';
    print '<div class="card justify-content-center text-center mt-1">';
} else {
    print '<div class="container">';
    print '<div class="card justify-content-center text-center mt-5">';
}
?>     
    <div class="card-header">
        <span class="text-center fs-5">  <?=$title_message?>   </span>                                
    </div>
    <div class="card-body">                                
        
    <div class="table-reponsive">    
     <table class="table table-hover " id="myTable">         
            <thead class="table-primary">
                 <th class="text-center" style="width:10%;">번호</th>
                 <th class="text-center" style="width:30%;">품목코드</th>
                 <th class="text-center" style="width:60%;">품목명</th>               
            </thead>
            <tbody>                  
            <?php          
            $start_num = $total_row;                  
            foreach ($items as $item) {
            ?>                     
            <tr>
                <td class="text-center"><?= $start_num ?></td>
                <td class="text-center"><?= $item['item_code'] ?></td>
                <td class="text-center"><?= $item['item_name'] ?></td>
            </tr>
            <?php
            $start_num--;
            } 
            ?>         
      </tbody>
     </table>
        </div>
        </div>
        </div>        
</form>
</body>
</html>

<!-- 페이지로딩 -->
<script>    
$(document).ready(function(){    
    var loader = document.getElementById('loadingOverlay');
    loader.style.display = 'none';
});

var ajaxRequest_write = null;
var dataTable; // DataTables 인스턴스 전역 변수
var materialpageNumber; // 현재 페이지 번호 저장을 위한 전역 변수
   
   $(document).ready(function() {
            dataTable = $('#myTable').DataTable({
                "paging": true,
                "ordering": true,
                "searching": true,
                "pageLength": 500,
                "lengthMenu": [100, 200, 500, 1000],
                "language": {
                    "lengthMenu": "Show _MENU_ entries",
                    "search": "Live Search:"
                },
                "order": [[0, 'desc']],
                "dom": '<"top"lf><"center"t>rt<"bottom"ip><"clear">'
            });

            // 페이지 번호 복원 (초기 로드 시)
            var savedPageNumber = getCookie('materialpageNumber');
            if (savedPageNumber) {
                dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
            }

            // 페이지 변경 이벤트 리스너
            dataTable.on('page.dt', function() {
                var materialpageNumber = dataTable.page.info().page + 1;
                setCookie('materialpageNumber', materialpageNumber, 10); // 쿠키에 페이지 번호 저장
            });

            // 페이지 길이 셀렉트 박스 변경 이벤트 처리
            $('#myTable_length select').on('change', function() {
                var selectedValue = $(this).val();
                dataTable.page.len(selectedValue).draw(); // 페이지 길이 변경 (DataTable 파괴 및 재초기화 없이)

                // 변경 후 현재 페이지 번호 복원
                savedPageNumber = getCookie('materialpageNumber');
                if (savedPageNumber) {
                    dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
                }
            });               
});   

    function restorePageNumber() {
        var savedPageNumber = getCookie('materialpageNumber');
        // if (savedPageNumber) {
            // dataTable.page(parseInt(savedPageNumber) - 1).draw('page');
        // }
        location.reload(true);
    }


	
</script>
