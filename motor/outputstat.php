<?php 
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  
$title_message = '출고 통계 (출고완료일 기준) '; 

if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
	sleep(1);
	header("Location:" . $WebSite . "login/login_form.php"); 
	exit;
}
include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';   
?>

<title> <?=$title_message?> </title>
<link href="css/style.css" rel="stylesheet" > 

<style>
#viewTable th, td{
    border : 1px #aaaaaa solid ;
}
</style>

</head>
<body>
<?php
$header = isset($_REQUEST['header']) ? $_REQUEST['header'] : '';  
if(!empty($header))
    require_once($_SERVER['DOCUMENT_ROOT'] . "/myheader.php");

$tablename = 'motor'; 

$check = isset($_REQUEST['check']) ? $_REQUEST['check'] : '';
$fromdate = isset($_REQUEST['fromdate']) ? $_REQUEST['fromdate'] : '';  
$todate = isset($_REQUEST['todate']) ? $_REQUEST['todate'] : '';  
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  
$search_secondord = isset($_REQUEST['search_secondord']) ? $_REQUEST['search_secondord'] : '';  
$search_workplacename = isset($_REQUEST['search_workplacename']) ? $_REQUEST['search_workplacename'] : '';

$search = $search_secondord . $search_workplacename ;
// 현재 날짜
$currentDate = date("Y-m-d");

// fromdate 또는 todate가 빈 문자열이거나 null인 경우
if ($fromdate === "" || $fromdate === null || $todate === "" || $todate === null) {
    // fromdate가 없으면 매월 1일자로 설정
    if ($fromdate === "" || $fromdate === null) {
        $fromdate = date("Y-m-01", strtotime($currentDate));
    } else {
        $fromdate = date("Y-m-d", strtotime("-2 weeks", strtotime($currentDate))); // 2주 전 날짜
    }

    // todate가 없으면 해당 월의 마지막 날로 설정
    if ($todate === "" || $todate === null) {
        $todate = date("Y-m-t", strtotime($currentDate)); // 해당 월의 마지막 날
    }	
    $Transtodate = $todate;
} else {
    // fromdate와 todate가 모두 설정된 경우 (기존 로직 유지)
    $Transtodate = $todate;
}

$orderby="order by outputdate desc ";        
        
$SettingDate=" outputdate "; 

$common= $SettingDate . " between '$fromdate' and '$Transtodate' and is_deleted IS NULL ";

$conditions = [$common];
if (!empty($search_secondord) && !empty($search_workplacename)) {
    $conditions[] = "(secondord = '$search_secondord' AND workplacename LIKE '%$search_workplacename%')";
} else {
    if (!empty($search_secondord)) {
        $conditions[] = "secondord = '$search_secondord' ";
    }
    if (!empty($search_workplacename)) {
        $conditions[] = "workplacename LIKE '%$search_workplacename%'";
    }
}

$whereClause = implode(' AND ', $conditions);
$wherePhrase = " WHERE " . $whereClause . " " . $orderby;
$motor_sql="SELECT * FROM " . $DB . ".motor " . $wherePhrase;

// print $motor_sql;

// 품목코드 추출
$items = [];

function safe_json_decode($json) {
    $decoded = json_decode($json, true);
    return is_array($decoded) ? $decoded : [];
}

function generateItemCode($orderItem) {
    $volt = strtolower(isset($orderItem['col1']) ? $orderItem['col1'] : '');
    $wire = strtolower(isset($orderItem['col2']) ? $orderItem['col2'] : '');
    $purpose = strtolower(isset($orderItem['col3']) ? $orderItem['col3'] : '');
    $upweight = strtolower(isset($orderItem['col4']) ? $orderItem['col4'] : '');
    $checkLotNum = strtolower(isset($orderItem['col13']) ? $orderItem['col13'] : '');
	
	// "방범"이라는 단어가 포함되어 있는지 확인
	$containsBangbum = (strpos($checkLotNum, '방범') !== false);
    
    $upweight = str_replace(['k', 'K'], '', $upweight);

    if ($purpose === '무기둥모터') {
        $ecountcode = implode('-', array_filter([$volt, $wire, $purpose , $upweight]));
    } else if ($purpose === '방범' && $containsBangbum) {
        $ecountcode = implode('-', array_filter([$volt, $wire, $upweight, $purpose]));
    } else {
        $ecountcode = implode('-', array_filter([$volt, $wire, $upweight]));
    }    
    return $ecountcode;
}

// 품목코드 수정 브라켓은 후렌지 크기까지 포함한것으로 수정 2024/07/25
function generateItemCodeForBracket($orderItem) {
    // print ',브라켓고유 코드 ' . strtolower(isset($orderItem['col6']) ? $orderItem['col6'] : '');
    $bracket = isset($orderItem['col6']) ? $orderItem['col6'] : '';
    $flange = isset($orderItem['col7']) ? $orderItem['col7'] : '';
	$ecountcode = $bracket . '-' . $flange;
    return 'BRKT' . strtolower($ecountcode);
}

function generateItemCodeForAccessory($accessoryItem) {
    // 모든 문자열 값에서 "을 ″로 변환
    $accessoryItem = array_map(function($value) {
        return is_string($value) ? str_replace('"', '″', $value) : $value;
    }, $accessoryItem);

    $code = strtolower(isset($accessoryItem['col1']) ? $accessoryItem['col1'] : '');
    
    // 디버그 코드 추가
    if (empty($code)) {
        print 'Invalid accessory item: ';
        var_dump($accessoryItem);
    }
    return $code;
}


// URL 인코딩된 JSON 데이터를 디코딩하는 함수
function decodeUrlEncodedJson($encodedJson) {
    return json_decode(urldecode($encodedJson), true);
}

$sql_fee = "SELECT ecountcode FROM dbchandj.fee WHERE is_deleted IS NULL ORDER BY num DESC";
$sql_fee_controller = "SELECT item FROM dbchandj.fee_controller WHERE is_deleted IS NULL ORDER BY num DESC";
$sql_fee_sub = "SELECT item FROM dbchandj.fee_sub WHERE is_deleted IS NULL ORDER BY num DESC";
$sql_fee_fabric = "SELECT itemcode FROM dbchandj.fee_fabric WHERE is_deleted IS NULL ORDER BY num DESC";

try {
    $stmh_fee = $pdo->query($sql_fee);
    while ($row = $stmh_fee->fetch(PDO::FETCH_ASSOC)) {
        $ecountcodes = safe_json_decode($row['ecountcode']);
        foreach ($ecountcodes as $item_code) {
            if (!empty($item_code)) {
                $items[$item_code] = strtolower($item_code);
            }
        }
    }

    $stmh_fee_controller = $pdo->query($sql_fee_controller);
    while ($row = $stmh_fee_controller->fetch(PDO::FETCH_ASSOC)) {
        $item_codes = safe_json_decode($row['item']);
        foreach ($item_codes as $item_code) {
            if (!empty($item_code)) {
                $items[$item_code] = strtolower($item_code);
            }
        }
    }

    $stmh_fee_sub = $pdo->query($sql_fee_sub);
    while ($row = $stmh_fee_sub->fetch(PDO::FETCH_ASSOC)) {
        $items_array = array_filter(safe_json_decode($row['item']), 'strlen'); // 빈 값 제거
        
        foreach ($items_array as $item) {
            // "를 ″로 변환
            $item = str_replace('"', '″', $item);
            
            $generated_code = generateItemCodeForAccessory([
                'col1' => $item
            ]);
            if (!empty($generated_code)) {
                $items[$generated_code] = $generated_code;
            }
        }
    }
	
    $stmh_fee_fabric = $pdo->query($sql_fee_fabric);
    while ($row = $stmh_fee_fabric->fetch(PDO::FETCH_ASSOC)) {
        $item_codes = safe_json_decode($row['itemcode']);
        foreach ($item_codes as $item_code) {
            if (!empty($item_code)) {
                $items[$item_code] = $item_code;
            }
        }
    }

} catch (PDOException $Exception) {
    echo "오류: " . $Exception->getMessage();
    exit;
}

$stock_data = [];

// motor 테이블에서 출고 데이터 계산
try {
    $stmh = $pdo->prepare($motor_sql);
    $stmh->execute();
    $rows = $stmh->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
        $orderlist = isset($row['orderlist']) ? json_decode($row['orderlist'], true) : [];
        $controllerlist = isset($row['controllerlist']) ? json_decode($row['controllerlist'], true) : [];
        $fabriclist = isset($row['fabriclist']) ? json_decode($row['fabriclist'], true) : [];
        $accessorieslist = isset($row['accessorieslist']) ? json_decode($row['accessorieslist'], true) : [];

        foreach ($orderlist as $order) {
            $item_code = generateItemCode($order);
            $unit = strtolower($order['col5'] ?? '');
            $quantity = (int)($order['col8'] ?? 0);

            if ($unit === 'set' || $unit === '모터단품') {
                if (!isset($stock_data[$item_code])) {
                    $stock_data[$item_code] = ['total_out' => 0];
                }
                $stock_data[$item_code]['total_out'] += $quantity;
            }

            if ($unit === 'set' || $unit === '브라켓트') {
                $bracket_code = generateItemCodeForBracket($order); 
			    // print 'br code ' . $bracket_code;
                if (!isset($stock_data[$bracket_code])) {
                    $stock_data[$bracket_code] = ['total_out' => 0];
                }
                $stock_data[$bracket_code]['total_out'] += $quantity;
            }
        }

        foreach ($controllerlist as $controller) {
            $item_code = strtolower($controller['col2'] ?? '');
            $quantity = (int)($controller['col3'] ?? 0);

            if (!isset($stock_data[$item_code])) {
                $stock_data[$item_code] = ['total_out' => 0];
            }
            $stock_data[$item_code]['total_out'] += $quantity;
        }

		foreach ($fabriclist as $fabric) {
			$item_code = strtoupper($fabric['col1'] ?? '');
			$quantity_str = str_replace(',', '', $fabric['col5'] ?? '0'); // 쉼표 제거
			$quantity = (int)$quantity_str;

			if (!isset($stock_data[$item_code])) {
				$stock_data[$item_code] = ['total_out' => 0];
			}
			$stock_data[$item_code]['total_out'] += $quantity;
		}

        foreach ($accessorieslist as $accessory) {
            $item_code = generateItemCodeForAccessory($accessory);
            $quantity = (int)($accessory['col2'] ?? 0);

            if (!isset($stock_data[$item_code])) {
                $stock_data[$item_code] = ['total_out' => 0];
            }
            $stock_data[$item_code]['total_out'] += $quantity;
        }
    }

} catch (PDOException $Exception) {
    echo "오류: " . $Exception->getMessage();
    exit;
}

// 품목코드로 정렬
ksort($stock_data);
// print_r($stock_data);


// 품목명별 출고량 합계를 계산하여 새로운 테이블 데이터 생성
$grouped_stock_data = [
    '모터' => [],
    '브라켓트' => [],
    '연동제어기' => [],
    '원단' => [],
    '부속자재' => []
];

foreach ($stock_data as $item_code => $data) {
    $bracketgroup = ["380*180", "530*320", "600*320", "600*350", "690*390", "910*600"];    
    $item_name = isset($items[$item_code]) ? $items[$item_code] : $item_code;
    
    // "를 ″로 변환
    $item_name = str_replace('"', '″', $item_name);
	
	// echo $item_name;
	$group = '';      
    if (strpos($item_name, '와이어원단') === 0 || strpos($item_name, '가스켓원단') === 0 || strpos($item_name, '화이버글라스원단') === 0 || strpos($item_name, '내화실-') === 0 ) {  
        $group = '원단';      
    }
    elseif (strpos($item_name, '220-') === 0 || strpos($item_name, '380-') === 0) {
        $group = '모터';
    } 	
	elseif (strpos($item_name, '노출형-') === 0 || strpos($item_name, '매립형-') === 0) {
        $group = '연동제어기';
    }
	else 	
	{
        $group = '부속자재';
		// foreach ($bracketgroup as $bracket) {
			// $tempName = 
            // if (strpos($item_name, $bracket) !== false) {
                // $group = '브라켓트';
                // break;
            // }
		// }		
		if (strpos($item_name, 'BRKT') !== false) {
                $group = '브라켓트';                    
		}
    }

    if (!isset($grouped_stock_data[$group][$item_name])) {
        $grouped_stock_data[$group][$item_name] = [
            'item_name' => $item_name,
            'total_out' => 0
        ];
    }    
    $grouped_stock_data[$group][$item_name]['total_out'] += $data['total_out'];
}

// var_dump($grouped_stock_data);  // 이 부분은 생략합니다.
// print_r($grouped_stock_data);  // 이 부분은 생략합니다.


// 각 그룹 내에서 품목명으로 정렬
foreach ($grouped_stock_data as &$group) {
    usort($group, function($a, $b) {
        return strcmp($a['item_name'], $b['item_name']);
    });
}

// 이 부분에서 레퍼런스 할당을 해제해야 합니다.
unset($group);
// var_dump($motor_sql);
?>

<form id="board_form" name="board_form" method="post" action="outputstat.php?mode=search&header=<?= isset($header) ? $header : '' ?>">     
<input type="hidden" id="header" name="header" value="<?= isset($header) ? $header : '' ?>">

<div class="container-fluid">     
<div class="card mb-1 mt-1 justify-content-center">
<div class="card-body">
<div class="container">     

    <div class="d-flex  p-1 justify-content-center align-items-center ">     
    <div class="card mb-2 mt-1 text-center justify-content-center  w-75">  
    <div class="card-body">       
             
    <div class="d-flex  p-1 justify-content-center align-items-center ">         
		<h5>  <?=$title_message?> </h5>  &nbsp;&nbsp;&nbsp;   
		<button type="button" class="btn btn-dark btn-sm mx-3"  onclick='location.reload();' title="새로고침"> <i class="bi bi-arrow-clockwise"></i> </button>  		   
   </div>
   
    <div class="d-flex  p-1 justify-content-center align-items-center ">                    
	     <h5>  로트번호와 상관없이 출고한 수량 </h5>  &nbsp;&nbsp;&nbsp;             
   </div>
    <div class="d-flex  p-1 m-1 mt-1 mb-1 justify-content-center align-items-center">     
            
            <!-- 기간부터 검색까지 연결 묶음 start -->
                <span id="showdate" class="btn btn-dark btn-sm " > 기간 </span>   &nbsp; 
				
				<div id="showframe" class="card"> 
					<div class="card-header" style="padding:2px;">
						<div class="d-flex justify-content-center align-items-center">  
							기간 설정
						</div>
					</div> 
					<div class="card-body">                                        
						<div class="d-flex justify-content-center align-items-center">      
							<button type="button" class="btn btn-outline-success btn-sm me-1 change_dateRange" onclick='alldatesearch()'>전체</button>  
							<button type="button" class="btn btn-outline-primary btn-sm me-1 change_dateRange" onclick='pre_year()'>전년도</button>  
							<button type="button" class="btn btn-dark btn-sm me-1 change_dateRange" onclick='pre_month()'>전월</button>
							<button type="button" class="btn btn-dark btn-sm me-1 change_dateRange" onclick='dayBeforeYesterday()'>전전일</button>    
							<button type="button" class="btn btn-dark btn-sm me-1 change_dateRange" onclick='yesterday()'>전일</button>                         
							<button type="button" class="btn btn-outline-danger btn-sm me-1 change_dateRange" onclick='this_today()'>오늘</button>
							<button type="button" class="btn btn-dark btn-sm me-1 change_dateRange" onclick='this_month()'>당월</button>
							<button type="button" class="btn btn-dark btn-sm me-1 change_dateRange" onclick='this_year()'>당해년도</button> 
						</div>
					</div>
				</div>   				
			
                <input  type="date" id="fromdate" name="fromdate" class="form-control" style="width:100px;" value="<?=$fromdate?>"> &nbsp; ~ &nbsp;
                <input  type="date" id="todate" name="todate" class="form-control me-1" style="width:100px;" value="<?=$todate?>"> &nbsp;
				발주처 &nbsp;
                <input  type="text" id="search_secondord" name="search_secondord" class="form-control me-1 text-start" style="width:200px;" value="<?=$search_secondord?>"> &nbsp;				
				현장명 &nbsp;
                <input  type="text" id="search_workplacename" name="search_workplacename" class="form-control text-start me-1" style="width:150px;" onkeydown="JavaScript:SearchEnter();" autocomplete="off" value="<?=$search_workplacename?>"> &nbsp;				
                <button type="button" id="searchBtn" class="btn btn-dark btn-sm me-2"><i class="bi bi-search"></i> 검색 </button>
                <?php if(empty($header)) { ?>
                    <button type="button" onclick="self.close();" class="btn btn-outline-dark btn-sm"> &times; 닫기 </button>
                <?php } ?>                 
        </div>    
   
   </div>
</div> 
</div>
</div>

<div class="container-fluid ">   
<div class="row  justify-content-center text-center">
    <?php
    $table_categories = [
        '모터' => '모터',
        '브라켓트' => '브라켓트',
        '연동제어기' => '연동제어기',
        '원단' => '원단',
        '부속자재' => '부속자재'
    ];

    $group_order = [
        ['모터'],
        ['브라켓트', '연동제어기', '원단'],
        ['부속자재']
    ]; 

    foreach ($group_order as $col_groups) {
        echo '<div class="col-sm-4">';
        foreach ($col_groups as $group) {
            $table_title = $table_categories[$group];
            echo '<h3> '. $table_title . '</h3>';
            echo '<div class="card mb-1 mt-1 justify-content-center w-80">';
            echo '<div class="card-body">';
            echo '<table class="table table-hover" id="viewTable">';
            echo '<thead class="table-primary">';
            echo '<tr>';
            echo '<th class="text-center" style="width:250px;"> 품목코드 </th>';
            echo '<th class="text-center" style="width:100px;"> 출고수량 </th>';    
            echo '</tr>';        
            echo '</thead>';   
            echo '<tbody>';

            $html_output = '';
            $total_out_sum = 0;

            if (!empty($grouped_stock_data[$group])) {
                foreach ($grouped_stock_data[$group] as $data) {
                    $html_output .= '<tr data-item-code="' . htmlspecialchars(str_replace('BRKT', '', $data['item_name'])) . '">';
                    $html_output .= '<td class="text-center">' . htmlspecialchars(str_replace('BRKT', '', $data['item_name'])) . '</td>';
                    $html_output .= '<td class="text-end">' . number_format($data['total_out']) . '</td>';
                    $html_output .= '</tr>';
                    $total_out_sum += $data['total_out'];
                }

                // Add the total sum row
                $html_output .= '<tr>';
                $html_output .= '<td class="text-center"><strong>합계</strong></td>';
                $html_output .= '<td class="text-end"><strong>' . number_format($total_out_sum) . '</strong></td>';
                $html_output .= '</tr>';
            } else {
                $html_output .= '<tr>';
                $html_output .= '<td class="text-center" colspan="2">데이터가 없습니다.</td>';
                $html_output .= '</tr>';
            }

            echo $html_output;

            echo '</tbody>';  
            echo '</table>';  
            echo '</div>';  
            echo '</div>';
        }
        echo '</div>';
    }
    ?>
</div>
</div>


</div>
</div>
</div>
</div>

<!-- Modal -->
<div class="container-fluid justify-content-center align-items-center">  
<div id="detailModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-full" role="document" >
        <div class="modal-content ">
            <div class="modal-header">          
                <h2 class="modal-title">입출고 내역</h2>
                <button type="button" class="btn btn-outline-dark detailModalclose">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th class="text-center">출고일자</th>
                            <th class="text-center">품목코드</th>                                                        
                            <th class="text-center">수량 or M</th>                            
                            <th class="text-center">현장명</th>
                            <th class="text-center">발주처</th>
                        </tr>
                    </thead>
                    <tbody id="detailModalBody">
                    </tbody>
                </table>
            </div>
          <div class="modal-footer">                    
            <button type="button" class="btn btn-outline-dark btn-sm detailModalclose" data-dismiss="modal"><i class="bi bi-x-lg"></i> 닫기</button>
          </div>            
        </div>
    </div>
</div>
</div>   

</form>    

</body>
</html>

<script>
// 페이지 로딩
$(document).ready(function(){    
    var loader = document.getElementById('loadingOverlay');
	if(loader)
		loader.style.display = 'none';
});
</script>

<script> 
function SearchEnter(){
    if(event.keyCode == 13){        
        document.getElementById('board_form').submit();
    }
}

$(document).ready(function(){    
    $("#searchBtn").click(function(){     
        document.getElementById('board_form').submit();
     });       
});

document.addEventListener('DOMContentLoaded', function() {
	var fromDateInput = document.getElementById('fromdate');
	var toDateInput = document.getElementById('todate');
	var searchBtn = document.getElementById('searchBtn');

	function autoSearch() {
		searchBtn.click();
	}

	fromDateInput.addEventListener('change', autoSearch);
	toDateInput.addEventListener('change', autoSearch);
});

$(document).ready(function() {
    $('#viewTable tbody tr').on('click', function() {
        var itemCode = $(this).data('item-code');

        if (!itemCode) {
            return; // itemCode가 없는 경우 modal 창을 띄우지 않음
        }
  
        var fromDate = $('#fromdate').val();
        var toDate = $('#todate').val();
        var search_secondord = $('#search_secondord').val();
        var search_workplacename = $('#search_workplacename').val();
        
        $.ajax({
            url: 'fetch_stocks.php',
            method: 'POST',
            data: { 
                item_code: itemCode,
                from_date: fromDate,
                search_secondord: search_secondord,
                search_workplacename: search_workplacename,
                to_date: toDate
            },
            success: function(response) {
                // console.log(response);
                $('#detailModalBody').html(response);
                $('#detailModal').modal('show');
                
                $(".detailModalclose").click(function(){     
                    $('#detailModal').modal('hide');
                }); 
            }
        });
    });
});

var vendorOptions = [];
var ajaxRequest = null;

function fetchVendorOptions() {
    if (ajaxRequest !== null) {
        ajaxRequest.abort();
    }
    ajaxRequest = $.ajax({
        url: "fetch_phonebook.php",
        type: "post",
        dataType: "json",
        success: function(data) {
            console.log("Fetched vendor options: ", data); // 디버그 코드
            vendorOptions = data || [];
            initializeAutocomplete($('#search_secondord'), vendorOptions);
            ajaxRequest = null;
        },
        error: function(jqxhr, status, error) {
            console.log(jqxhr, status, error);
        }
    });
}

function initializeAutocomplete(input, options) {
    console.log("Initializing autocomplete with options: ", options); // 디버그 코드
    $(input).autocomplete({
        source: function(request, response) {
            try {
                var filteredOptions = $.grep(options, function(option) {
                    return option.toLowerCase().indexOf(request.term.toLowerCase()) !== -1;
                });
                console.log("Filtered options: ", filteredOptions); // 디버그 코드
                response(filteredOptions);
            } catch (e) {
                console.error("Error in autocomplete source function: ", e);
                response([]);
            }
        },
        select: function(event, ui) {
            console.log("Selected: ", ui.item.value); // 디버그 코드
            $(this).val(ui.item.value);
            return false;
        },
        focus: function(event, ui) {
            $(this).val(ui.item.value);
            return false;
        }
    });
}

$(document).ready(function() {
    fetchVendorOptions();
});

$(document).ready(function(){
	saveLogData('모터 출고통계'); 
});
</script>
