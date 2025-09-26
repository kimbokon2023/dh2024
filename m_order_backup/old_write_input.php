<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php"); 
    exit;
}

include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';

$mode = $_REQUEST['mode'] ?? '';  
$num =  $_REQUEST["num"] ?? '';

$first_writer = '';

if ($mode === 'copy') {
    $title_message = "(데이터복사) 발주 차수별 입고  ";
} else {
    $title_message = "(과거 4차수형태 자료) 발주 차수별 입고  ";
}

$tablename = 'm_order';
?> 

<link href="css/style.css" rel="stylesheet">    
<title> <?=$title_message?> </title>

<style>
.hidden {
    display: none;
}
.scrollable-modal-body {
    max-height: 500px;
    overflow-y: auto;
}

.hidden {
    display: none;
}
.expanded {
    /* 예시: 남은 공간을 꽉 채우도록 처리 (필요에 따라 수정) */
    width: auto;
}

/* 1~4차 입고일 컬럼 화면에서만 숨기기 */
#orderlistTable th:nth-child(8),
#orderlistTable td:nth-child(8),
#orderlistTable th:nth-child(11),
#orderlistTable td:nth-child(11),
#orderlistTable th:nth-child(14),
#orderlistTable td:nth-child(14),
#orderlistTable th:nth-child(17),
#orderlistTable td:nth-child(17) {
  display: none;
}

/* 1~4차 입고금액, 송금액 컬럼 숨기기 (회계용 데이터) */
#orderlistTable th:nth-child(23),
#orderlistTable td:nth-child(23),
#orderlistTable th:nth-child(24),
#orderlistTable td:nth-child(24),
#orderlistTable th:nth-child(25),
#orderlistTable td:nth-child(25),
#orderlistTable th:nth-child(26),
#orderlistTable td:nth-child(26),
#orderlistTable th:nth-child(27),
#orderlistTable td:nth-child(27),
#orderlistTable th:nth-child(28),
#orderlistTable td:nth-child(28),
#orderlistTable th:nth-child(29),
#orderlistTable td:nth-child(29),
#orderlistTable th:nth-child(30),
#orderlistTable td:nth-child(30) {
  display: none;
}

</style>
</head>
<body>
<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

$todate = date("Y-m-d"); // 현재일자 변수지정
$todayDate = date("Y-m-d"); // 현재일자 

if ($mode == "modify" || $mode == "view") {
    try {
        $sql = "select * from m_order where num = ?";
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $num, PDO::PARAM_STR);
        $stmh->execute();
        $count = $stmh->rowCount();
        if ($count < 1) {
            print "검색결과가 없습니다.<br>";
        } else {
            $row = $stmh->fetch(PDO::FETCH_ASSOC);
            include "_row.php";
        }
    } catch (PDOException $Exception) {
        print "오류: " . $Exception->getMessage();
    }
}

if ($mode !== "modify" && $mode !== "copy" && $mode !== "split" && $mode !== "view") {
    include '_request.php';
    $first_writer = $user_name;
    $orderDate = $todate;
	// 초기값 빈 문자열로 설정
    $inputDate1 = $inputDate2 = $inputDate3 = $inputDate4 = '';
}
else
	{
		// 초기화
	$inputDate1 = '';
	$inputDate2 = '';
	$inputDate3 = '';
	$inputDate4 = '';

	// JSON 디코딩
	$orderlist = json_decode($row['orderlist'] ?? '[]', true);

	foreach ($orderlist as $item) {
		// 1차 입고일 (col7)
		if (empty($inputDate1) 
			&& !empty($item['col7']) 
			&& $item['col7'] !== '0000-00-00'
		) {
			$inputDate1 = $item['col7'];
		}
		// 2차 입고일 (col10)
		if (empty($inputDate2) 
			&& !empty($item['col10']) 
			&& $item['col10'] !== '0000-00-00'
		) {
			$inputDate2 = $item['col10'];
		}
		// 3차 입고일 (col13)
		if (empty($inputDate3) 
			&& !empty($item['col13']) 
			&& $item['col13'] !== '0000-00-00'
		) {
			$inputDate3 = $item['col13'];
		}
		// 4차 입고일 (col16)
		if (empty($inputDate4) 
			&& !empty($item['col16']) 
			&& $item['col16'] !== '0000-00-00'
		) {
			$inputDate4 = $item['col16'];
		}

		// 네 개 모두 채워졌으면 더 이상 반복할 필요 없음
		if ($inputDate1 && $inputDate2 && $inputDate3 && $inputDate4) {
			break;
		}
	}	
}

if ($mode == "copy" || $mode == 'split') {
    try {
        $sql = "select * from m_order where num = ?";
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $num, PDO::PARAM_STR);
        $stmh->execute();
        $count = $stmh->rowCount();
        if ($count < 1) {
            print "검색결과가 없습니다.<br>";
        } else {
            $row = $stmh->fetch(PDO::FETCH_ASSOC);
        }
        include '_row.php';
    } catch (PDOException $Exception) {
        print "오류: " . $Exception->getMessage();
    }
    $num = 0;
    $orderDate = $todate;
    $mode = "insert";
}

?>

<form id="board_form" name="board_form" method="post" enctype="multipart/form-data" onkeydown="return captureReturnKey(event)">
    <input type="hidden" id="first_writer" name="first_writer" value="<?= isset($first_writer) ? $first_writer : '' ?>">
    <input type="hidden" id="update_log" name="update_log" value="<?= isset($update_log) ? $update_log : '' ?>">
    <input type="hidden" id="num" name="num" value="<?= isset($num) ? $num : '' ?>">
    <input type="hidden" id="tablename" name="tablename" value="<?= isset($tablename) ? $tablename : '' ?>">
    <input type="hidden" id="mode" name="mode" value="<?= isset($mode) ? $mode : '' ?>">
    <input type="hidden" id="totalsurang" name="totalsurang" value="<?= isset($totalsurang) ? $totalsurang : '' ?>">
    <input type="hidden" id="totalamount" name="totalamount" value="<?= isset($totalamount) ? $totalamount : '' ?>">
    <input type="hidden" id="inputSum1" name="inputSum1" value="<?= $inputSum1 ?? '' ?>">
    <input type="hidden" id="inputSum2" name="inputSum2" value="<?= $inputSum2 ?? '' ?>">
    <input type="hidden" id="inputSum3" name="inputSum3" value="<?= $inputSum3 ?? '' ?>">
    <input type="hidden" id="inputSum4" name="inputSum4" value="<?= $inputSum4 ?? '' ?>">	
	
    <input type="hidden" id="orderlist" name="orderlist">	
    
	<?php include $_SERVER['DOCUMENT_ROOT'] . '/m_order/modal.php'; ?>
	
    <div class="container">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-center align-items-center mt-3 mb-5">
                    <span class="fs-5 me-5"> <?=$title_message?> (<?=$mode?>) </span>
                    <?php if ($mode !== 'view') { ?>
                        <button type="button" class="btn btn-dark btn-sm me-2 saveBtn">  <i class="bi bi-floppy-fill"></i> 저장 </button>
                    <?php } else { ?>
						<button type="button" class="btn btn-dark btn-sm mx-2"  onclick='location.reload();' > <i class="bi bi-arrow-clockwise"></i> </button>  
                        <button type="button" class="btn btn-dark btn-sm me-1" onclick="location.href='write_input.php?mode=modify&num=<?=$num?>';">  <i class="bi bi-pencil-square"></i>  수정  </button>                        
                        <button type="button" class="btn btn-secondary btn-sm me-1" onclick="generateExcel();"> Excel 저장 </button>
                    <?php } ?>
                    &nbsp;&nbsp;
                    최초 : <?=$first_writer?>
                    <br>
                    <?php $update_log_extract = substr($update_log, 0, 31); ?>
                    &nbsp;&nbsp; 수정 : <?=$update_log_extract?> &nbsp;&nbsp;&nbsp;
                    <span class="text-end" style="width:10%;">
                        <button type="button" class="btn btn-outline-dark btn-sm me-2" id="showlogBtn"> H </button>
                        <button class="btn btn-secondary btn-sm" onclick="self.close();"> &times; 닫기 </button> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    </span>
                </div>                                    
                    <div class="d-flex justify-content-center">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td class="text-end" style="width:80px;"> 구매발주일 </td>
                                    <td class="w150px">
                                        <div class="d-flex align-items-center justify-content-start">
                                            <input type="date" name="orderDate" id="orderDate" value="<?=$orderDate?>" class="form-control" style="width:100px;">
                                        </div>
                                    </td>
									<td class="text-end" style="width:80px;"> 비고 </td>
                                    <td>                                       
                                        <textarea name="memo" id="memo" class="form-control text-start" style="height:20px;"><?=$memo?></textarea>                                        
                                    </td>
                                </tr>
                            </tbody>
                        </table>
					<div class="alert alert-primary mx-3 w-25" role="alert">
						단가, 금액은 위엔화 기준입니다.
					</div>							
				</div>   
				
				<div class="d-flex justify-content-center align-items-center flex-wrap gap-2 my-2">
					<!-- 전체 제어 버튼 -->
					<button type="button" class="btn btn-outline-primary btn-sm me-2 passView" id="checkAllPhases">전체 체크</button>
					<button type="button" class="btn btn-outline-secondary btn-sm me-4 passView" id="uncheckAllPhases">전체 해제</button>
				
					<!-- 차수별 표시 제어용 체크박스 -->
					<div class="form-check form-check-inline ms-2">
						<input class="form-check-input phase-toggle passView" type="checkbox" id="chkPhase1" data-phase="1" checked>
						<label class="form-check-label" for="chkPhase1">1차</label>
					</div>
					<div class="form-check form-check-inline">
						<input class="form-check-input phase-toggle passView" type="checkbox" id="chkPhase2" data-phase="2" checked>
						<label class="form-check-label" for="chkPhase2">2차</label>
					</div>
					<div class="form-check form-check-inline">
						<input class="form-check-input phase-toggle passView" type="checkbox" id="chkPhase3" data-phase="3" checked>
						<label class="form-check-label" for="chkPhase3">3차</label>
					</div>
					<div class="form-check form-check-inline">
						<input class="form-check-input phase-toggle passView" type="checkbox" id="chkPhase4" data-phase="4" checked>
						<label class="form-check-label" for="chkPhase4">4차</label>
					</div>								
				
				<?php if ($mode !== 'view') : ?>				
					<select id="selectDate" name="selectDate" class="form-select d-block w-auto ms-5 me-1">
						<option value="1차">1차</option>
						<option value="2차">2차</option>
						<option value="3차">3차</option>
						<option value="4차">4차</option>
					</select>
					<h6> <span class="text-end mx-1"> 입고일 </span> </h6>
					<input type="date" name="todayDate" id="todayDate" value="<?=$todayDate?>" class="form-control mx-1" style="width:100px;">
					<button type="button" class="btn btn-primary btn-sm mx-2" id="updateDate"> 실행 </button>			 
				<?php endif; ?>	
				</div>				
            </div> <!-- end of card body -->
        </div>
    </div>
<div class="container-fluid">	
 <?php
 
 
	function generateTableSection($id, $title, $badgeClass = 'bg-primary', $inputDate1, $inputDate2, $inputDate3, $inputDate4) {
		echo "		
			<div class='d-flex justify-content-center  align-items-center  mt-4 mb-2'>
				<span class='badge $badgeClass fs-6 me-3'>$title</span>
				<span class=' fs-6 ms-5 me-2'> 총 발주수량 </span>
				<span id='totalsurangDisplay' class='form-control w60px text-end text-primary fw-bold ms-1 me-5 fs-6'> </span>		
				<span class=' fs-6 ms-5 me-2'> 1차 입고 </span>
				<span id='inputSumDisplay1' class='form-control w60px text-end text-secondary ms-1 me-5 fs-6'> </span>		
				<span class=' fs-6 ms-5 me-2'> 2차 입고 </span>
				<span id='inputSumDisplay2' class='form-control w60px text-end text-secondary ms-1 me-5 fs-6'> </span>		
				<span class=' fs-6 ms-5 me-2'> 3차 입고 </span>
				<span id='inputSumDisplay3' class='form-control w60px text-end text-secondary ms-1 me-5 fs-6'> </span>		
				<span class=' fs-6 ms-5 me-2'> 4차 입고 </span>
				<span id='inputSumDisplay4' class='form-control w60px text-end text-secondary ms-1 me-5 fs-6'> </span>		
			</div>		";							

			// … orderlist 디코딩 및 foreach로 inputDate 계산한 직후에 추가 …

			// undefined variable 방지용 초기화
			// if (t($inputDate1)) $inputDate1 = '';
			   // else
				   // echo $inputDate1;
			// if (!isset($inputDate2)) $inputDate2 = '';
			// if (!isset($inputDate3)) $inputDate3 = '';
			// if (!isset($inputDate4)) $inputDate4 = '';

	// 폼 출력
    // (화면에서 불러온) 레코드 고유번호   

    echo "<div class='d-flex justify-content-center align-items-center mt-4 mb-2'>";
        // 1차
        echo "<span class='fs-6 ms-5 me-2'>1차 입고일</span>";
        echo "<input 
                type='date' 
                id='inputDate1' 
                name='inputDate1' 
                class='form-control w-auto text-center text-secondary ms-1' 
                value='{$inputDate1}'>";
        echo "<button 
                type='button' 
                id='btnComplete1'
                class='btn btn-success btn-sm ms-2 me-5'
                onclick='completeInStock(1)'>
                전산 입고처리
              </button>";

        // 2차
        echo "<span class='fs-6 ms-5 me-2'>2차 입고일</span>";
        echo "<input 
                type='date' 
                id='inputDate2' 
                name='inputDate2' 
                class='form-control w-auto text-center text-secondary ms-1' 
                value='{$inputDate2}'>";
        echo "<button 
                type='button' 
                id='btnComplete2'
                class='btn btn-success btn-sm ms-2 me-5'
                onclick='completeInStock(2)'>
                전산 입고처리
              </button>";

        // 3차
        echo "<span class='fs-6 ms-5 me-2'>3차 입고일</span>";
        echo "<input 
                type='date' 
                id='inputDate3' 
                name='inputDate3' 
                class='form-control w-auto text-center text-secondary ms-1' 
                value='{$inputDate3}'>";
        echo "<button 
                type='button' 
                id='btnComplete3'
                class='btn btn-success btn-sm ms-2 me-5'
                onclick='completeInStock(3)'>
                전산 입고처리
              </button>";

        // 4차
        echo "<span class='fs-6 ms-5 me-2'>4차 입고일</span>";
        echo "<input 
                type='date' 
                id='inputDate4' 
                name='inputDate4' 
                class='form-control w-auto text-center text-secondary ms-1' 
                value='{$inputDate4}'>";
        echo "<button 
                type='button' 
                id='btnComplete4'
                class='btn btn-success btn-sm ms-2'
                onclick='completeInStock(4)'>
                전산 입고처리
              </button>";
    echo "</div>";
		
			echo "
			<div class='table-responsive'>
				<table class='table table-bordered table-hover' id='{$id}Table' style='min-width:2200px;'>
					<thead id='thead_$id'>
						<tr>
							<th class='text-center' style='width:20px;'>NO</th>
							<th class='text-center' style='width:60px;'>카테고리</th>
							<th class='text-center' style='width:150px;'>품목코드</th>
							<th class='text-center' style='width:150px;'>품목명</th>
							<th class='text-center' style='width:60px;'>구매수량</th>
							<th class='text-center' style='width:60px;display:none;'>단가<br>(위엔)</th>
							<th class='text-center' style='width:200px;'>비고</th>
							<th class='text-center' style='width:60px;display:none;'>금액<br>(위엔)</th>

							<!-- 1차 -->
							<th class='text-center' style='width:80px;'>1차 입고일</th>
							<th class='text-center' style='width:80px;'>1차 입고수량</th>
							<th class='text-center' style='width:80px;'>1차 로트번호</th>

							<!-- 2차 -->
							<th class='text-center' style='width:60px;'>2차 입고일</th>
							<th class='text-center' style='width:80px;'>2차 입고수량</th>
							<th class='text-center' style='width:80px;'>2차 로트번호</th>

							<!-- 3차 -->
							<th class='text-center' style='width:80px;'>3차 입고일</th>
							<th class='text-center' style='width:80px;'>3차 입고수량</th>
							<th class='text-center' style='width:80px;'>3차 로트번호</th>

							<!-- 4차 -->
							<th class='text-center' style='width:80px;'>4차 입고일</th>
							<th class='text-center' style='width:80px;'>4차 입고수량</th>
							<th class='text-center' style='width:80px;'>4차 로트번호</th>

							<!-- 23~30: 1~4차 입고금액 + 송금액 (숨김) -->
							<th class='text-center' style='width:80px;display:none;'>1차 입고금액</th>
							<th class='text-center' style='width:80px;display:none;'>1차 송금액</th>
							<th class='text-center' style='width:80px;display:none;'>2차 입고금액</th>
							<th class='text-center' style='width:80px;display:none;'>2차 송금액</th>
							<th class='text-center' style='width:80px;display:none;'>3차 입고금액</th>
							<th class='text-center' style='width:80px;display:none;'>3차 송금액</th>
							<th class='text-center' style='width:80px;display:none;'>4차 입고금액</th>
							<th class='text-center' style='width:80px;display:none;'>4차 송금액</th>

							<!-- 계산 및 상태 -->
							<th class='text-center' style='width:80px;'>구매수량합</th>
							<th class='text-center' style='width:80px;'>입고합</th>
							<th class='text-center' style='width:80px;'>구매입고차이</th>
							<th class='text-center' style='width:80px;'>상태</th>
						</tr>
					</thead>
					<tbody id='{$id}Group'>
						<!-- 동적으로 행 추가 -->
					</tbody>
				</table>
			</div> 
		</div>
		";
	}
	
	generateTableSection('orderlist', '차수별 입고 입력 ', 'bg-primary', $inputDate1, $inputDate2, $inputDate3, $inputDate4);
	?>
</div>

</form>

<script>
var ajaxRequest = null;
var ajaxRequest_write = null;

// 전역 변수에 품목 데이터 저장 (material_reg 폴더의 fetch_itemcode.php 재활용)
var itemData = [];

// 품목 데이터를 Ajax로 로드하는 함수
function loadItemData() {
    $.ajax({
        url: "/material_reg/fetch_itemcode.php", // 재활용: material_reg 폴더의 fetch_itemcode.php
        type: "GET",
        dataType: "json",
        success: function(data) {
			console.log('fetch_itemcode : ',data);
            if(data.items) {
                itemData = data.items;
            }
        },
        error: function(xhr, status, error) {
            console.error("품목 데이터 로드 에러:", error);
        }
    });
}

// initializeAutocomplete 함수: 입력 필드에 자동완성 기능을 설정
function initializeAutocomplete($input) {
    $input.autocomplete({
        source: function(request, response) {
            var term = request.term.toLowerCase();
			var filteredOptions = $.grep(itemData, function(item) {
				var code = item.item_code ? item.item_code.toLowerCase() : "";
				var name = item.item_name ? item.item_name.toLowerCase() : "";
				return code.indexOf(term) !== -1 || name.indexOf(term) !== -1;
			}).map(function(item) {
				return {
					label: item.item_code + " - " + item.item_name,
					value: $input.hasClass("item-code") ? item.item_code : item.item_name,
					item_code: item.item_code,
					item_name: item.item_name,
					item_yuan: item.item_yuan
				};
			});

            response(filteredOptions);
        },
       select: function(event, ui) {
			var $this = $(this);
			    console.log("선택된 항목:", ui.item);

			if ($this.hasClass("item-code")) {
				$this.val(ui.item.item_code);
				$this.closest("tr").find("input.item-name").val(ui.item.item_name);
				$this.closest("tr").find("input.unit-price").val(ui.item.item_yuan);
			} else if ($this.hasClass("item-name")) {
				$this.val(ui.item.item_name);
				$this.closest("tr").find("input.item-code").val(ui.item.item_code);
				$this.closest("tr").find("input.unit-price").val(ui.item.item_yuan);
			}
			return false;
		},
		
        minLength: 0,
        open: function() {
            $(this).autocomplete("widget").css({
                "max-height": "200px",
                "overflow-y": "auto",
                "overflow-x": "hidden"
            });
        }
    }).on("keydown", function(event) {
        if (event.keyCode === $.ui.keyCode.ENTER && $(this).autocomplete("instance").menu.active) {
            event.preventDefault();
        }
    });
}

// 문서 로드 시 품목 데이터를 먼저 로드합니다.

$(document).ready(function() {		

	loadItemData();  		
	initializePage();
	bindEventHandlers();			
    updateTotalSummary(); // 페이지 로드 후 최초 계산	

	$("#showlogBtn").click(function() {
		var num = '<?= $num ?>';
		var workitem = 'm_order';
		var btn = $(this);
		popupCenter("../Showlog.php?num=" + num + "&workitem=" + workitem, '로그기록 보기', 500, 500);
		btn.prop('disabled', false);
	});

	$(".saveBtn").click(function() {
		saveData();
	});

	$(".deleteBtn").click(function() {
		deleteData();
	});		

	document.querySelectorAll('input').forEach(function(input) {
		input.setAttribute('autocomplete', 'off');
	});
		
});

function initializePage() {
    try {
        var loader = document.getElementById('loadingOverlay');
        if(loader)
            loader.style.display = 'none';

        var orderlist = <?= json_encode($orderlist ?? []) ?>;
        console.log('write_input initializePage - orderlist:', orderlist);
        console.log('write_input initializePage - orderlist type:', typeof orderlist);
        console.log('write_input initializePage - orderlist length:', orderlist ? orderlist.length : 'N/A');

        // orderlist가 null이나 undefined인 경우 빈 배열로 초기화
        if (!orderlist || !Array.isArray(orderlist)) {
            console.warn('write_input orderlist is invalid, using empty array:', orderlist);
            orderlist = [];
        }

        // orderlist의 첫 번째 항목을 자세히 로그로 출력
        if (orderlist.length > 0) {
            console.log('write_input First item in orderlist:', orderlist[0]);
            console.log('write_input First item keys:', Object.keys(orderlist[0]));
        }

        loadTableData('#orderlistTable', orderlist, 'orderlistTable');

        if ('<?= $mode ?>' === 'view') {
            disableInputsForViewMode();
        }
        
        console.log('write_input Page initialized successfully');
    } catch (error) {
        console.error('Error in write_input initializePage:', error);
    }
}

function bindEventHandlers() {
	$(document).on("click", ".remove-row", function() {
		$(this).closest("tr").remove();
		updateTotalSummary(); // 삭제 후 합계 재계산
	});
		
	$(document).on('click', '.add-row', function() {
		var table = $(this).closest('div').siblings('div').find('table'); // 현재 버튼과 가까운 테이블 찾기
		var tableBody = table.find('tbody');
		var tableId = table.attr('id') || ''; // 테이블 ID가 존재할 경우만 사용

		addRow(tableBody, {}, tableId);
	});
		
	$(document).on('click', '.add-row_new', function() {
		var table = $(this).closest('table'); // 버튼이 속한 테이블 찾기
		var tableBody = table.find('tbody');
		var tableId = table.attr('id') || ''; // id 존재 시 사용

		addRow(tableBody, {}, tableId);
	});

}
function addRow(tableBody, rowData = {}, typebutton = '') {
    try {
        console.log('write_input addRow called with:', { tableBody, rowData, typebutton });
        
        // rowData가 null이나 undefined인 경우 빈 객체로 초기화
        if (!rowData || typeof rowData !== 'object') {
            console.warn('rowData is invalid, using empty object:', rowData);
            rowData = {};
        }
        
        // rowData의 모든 키를 로그로 출력하여 디버깅
        console.log('write_input rowData keys:', Object.keys(rowData));
        console.log('write_input rowData values:', rowData);
        
        var showInout = true; // 입고 열 항상 표시
        var table = tableBody.closest('table');
        var thead = table.find('thead');
        if (thead.length !== 0) {
            thead.css('display', 'table-header-group');
        }

        var newRow = $('<tr>');

        // 0. NO
        newRow.append(`
            <td class="text-center">
                <div class="d-flex justify-content-center align-items-center">
                    <span class="serial-number me-4"></span>
                </div>
            </td>
        `);

        // 1. 카테고리 셀렉트박스 (readonly/disabled)
        var categoryValue = rowData.col0 || rowData.category || '모터';
        var categorySelect = '<select name="col0[]" class="form-select w-auto item-category" style="font-size: 0.7rem;" disabled readonly>' +
            '<option value="모터"' + (categoryValue === '모터' ? ' selected' : '') + '>모터</option>' +
            '<option value="연동제어기"' + (categoryValue === '연동제어기' ? ' selected' : '') + '>연동제어기</option>' +
            '<option value="운송비"' + (categoryValue === '운송비' ? ' selected' : '') + '>운송비</option>' +
            '<option value="부속자재"' + (categoryValue === '부속자재' ? ' selected' : '') + '>부속자재</option>' +
            '</select>';
        newRow.append('<td class="text-center">' + categorySelect + '</td>');

        // 2. 품목코드
        newRow.append(`
            <td class="text-center"><input type="text" name="col1[]" class="form-control item-code" readonly value="${rowData.col1 || rowData.item_code || ''}"></td>
        `);

        // 3. 품목명
        newRow.append(`
            <td class="text-center"><input type="text" name="col2[]" class="form-control item-name" readonly value="${rowData.col2 || rowData.item_name || ''}"></td>
        `);

        // 4. 구매수량
        newRow.append(`
            <td class="text-center"><input type="number" name="col3[]" class="form-control text-end purchase-qty" readonly value="${rowData.col3 || rowData.purchase_qty || ''}" onkeyup="updateRowCalculation(this);"></td>
        `);

        // 5. 단가
        newRow.append(`
            <td class="text-center" style="display:none;"><input type="number" name="col4[]" class="form-control text-end unit-price" readonly value="${rowData.col4 || rowData.unit_price || ''}" onkeyup="updateRowCalculation(this);"></td>
        `);

        // 6. 비고
        newRow.append(`
            <td class="text-center"><input type="text" name="col5[]" class="form-control" readonly value="${rowData.col5 || rowData.remark || ''}"></td>
        `);

        // 7. 금액
        newRow.append(`
            <td class="text-center" style="display:none;"><input type="text" name="col6[]" class="form-control text-end amount" readonly value="${(rowData.col6 || rowData.amount) ? Number(rowData.col6 || rowData.amount).toLocaleString() : ''}"></td>
        `);

        // 8~16: 1~4차 입고일, 입고수량, 로트번호
        for (let i = 1; i <= 4; i++) {
            // 입고일 (col7, col10, col13, col16)
            var inDateKey = 'col' + (6 + i * 3 - 2);
            var inDateValue = rowData[inDateKey] || rowData[`in_date${i}`] || '';
            newRow.append(`
                <td class="text-center"><input type="date" name="${inDateKey}[]" class="form-control" value="${inDateValue}"></td>
            `);
            
            // 입고수량 (col8, col11, col14, col17)
            var inQtyKey = 'col' + (6 + i * 3 - 1);
            var inQtyValue = rowData[inQtyKey] || rowData[`in_qty${i}`] || '';
            newRow.append(`
                <td class="text-center"><input type="number" name="${inQtyKey}[]" class="form-control text-end inqty${i}" value="${inQtyValue}" onkeyup="updateRowCalculation(this);"></td>
            `);
            
            // 로트번호 (col9, col12, col15, col18)
            var lotNumKey = 'col' + (6 + i * 3);
            var lotNumValue = rowData[lotNumKey] || rowData[`lot_num${i}`] || '';
            newRow.append(`
                <td class="text-center"><input type="text" name="${lotNumKey}[]" class="form-control lotnum${i}" value="${lotNumValue}"></td>
            `);
        }

        // 19. 구매수량합
        newRow.append(`
            <td class="text-center"><input type="text" name="col19[]" class="form-control text-end total-purchase" readonly value="${(rowData.col19 || rowData.total_purchase) ? Number(rowData.col19 || rowData.total_purchase).toLocaleString() : ''}"></td>
        `);

        // 20. 입고합
        newRow.append(`
            <td class="text-center"><input type="text" name="col20[]" class="form-control text-end total-inqty" readonly value="${(rowData.col20 || rowData.total_inqty) ? Number(rowData.col20 || rowData.total_inqty).toLocaleString() : ''}"></td>
        `);

        // 21. 구매입고차이
        newRow.append(`
            <td class="text-center"><input type="text" name="col21[]" class="form-control text-end diff" readonly value="${(rowData.col21 || rowData.diff) ? Number(rowData.col21 || rowData.diff).toLocaleString() : ''}"></td>
        `);

        // 22. 상태
        newRow.append(`
            <td class="text-center"><input type="text" name="col22[]" class="form-control fw-bold text-center status" readonly value="${rowData.col22 || rowData.status || ''}"></td>
        `);

        // 23~30: 1~4차 입고금액 + 송금액 (숨김) - 회계용 데이터
        for (let i = 1; i <= 4; i++) {
            // 입고금액 (col23, col25, col27, col29)
            var amountInKey = 'col' + (22 + i * 2 - 1);
            var amountInValue = rowData[amountInKey] || rowData[`amount_in${i}`] || '';
            newRow.append(`
                <td style="display:none;"><input type="text" name="${amountInKey}[]" class="form-control text-end amountIn${i}" value="${amountInValue}"></td>
            `);
            
            // 송금액 (col24, col26, col28, col30)
            var sendAmountKey = 'col' + (22 + i * 2);
            var sendAmountValue = rowData[sendAmountKey] || rowData[`send_amount${i}`] || '';
            newRow.append(`
                <td style="display:none;"><input type="text" name="${sendAmountKey}[]" class="form-control text-end " value="${sendAmountValue}"></td>
            `);
        }

        tableBody.append(newRow);

        // 일련번호 업데이트
        updateSerialNumbers(tableBody);

        // 계산 실행
        updateRowCalculation(newRow.find('input.purchase-qty')[0]);
        
        console.log('write_input Row added successfully with data:', rowData);
    } catch (error) {
        console.error('Error in write_input addRow function:', error);
        console.error('rowData:', rowData);
        console.error('typebutton:', typebutton);
    }
}

// NO 일련번호 자동 부여
function updateSerialNumbers(tableBody) {
    tableBody.find('tr').each(function(index) {
        $(this).find('.serial-number').text(index + 1);
    });
}

function updateRowCalculation(input) {
    const $row = $(input).closest("tr");

    const purchaseQty = parseFloat($row.find("input.purchase-qty").val().replace(/,/g, '')) || 0;
    const unitPrice = parseFloat($row.find("input.unit-price").val().replace(/,/g, '')) || 0;
    const amount = purchaseQty * unitPrice;
    $row.find("input.amount").val(amount.toLocaleString());

    let totalInQty = 0;

    // 1~4차 입고수량에 따른 입고금액 계산 및 숨겨진 컬럼에 저장
    for (let i = 1; i <= 4; i++) {
        const inQty = parseFloat($row.find(`.inqty${i}`).val().replace(/,/g, '')) || 0;
        const inAmount = inQty * unitPrice;
        // 숨겨진 입고금액 컬럼에 저장 (col23, col25, col27, col29)
        $row.find(`.amountIn${i}`).val(inAmount.toLocaleString());
        totalInQty += inQty;
    }

    $row.find("input.total-inqty").val(totalInQty.toLocaleString());
    $row.find("input.total-purchase").val(purchaseQty.toLocaleString());

    const diff = purchaseQty - totalInQty;
    $row.find("input.diff").val(diff.toLocaleString());

    if (diff === 0) {
        $row.find("input.diff").css("background-color", "#000").css("color", "#fff");
    } else if (diff > 0) {
        $row.find("input.diff").css("background-color", "#007bff").css("color", "#fff");
    } else {
        $row.find("input.diff").css("background-color", "#dc3545").css("color", "#fff");
    }

    const status = (diff === 0 && purchaseQty > 0) ? '완료' : '';
    $row.find("input.status").val(status);

    updateTotalSummary();
}

function updateTotalSummary() {
    let totalQty = 0;
    let totalAmount = 0;

    let sum1 = 0;
    let sum2 = 0;
    let sum3 = 0;
    let sum4 = 0;

    $("input.purchase-qty").each(function () {
        const qty = parseFloat($(this).val().replace(/,/g, "")) || 0;
        totalQty += qty;
    });

    $("input.amount").each(function () {
        const amount = parseFloat($(this).val().replace(/,/g, "")) || 0;
        totalAmount += amount;
    });

    $("input.inqty1").each(function () {
        const val = parseFloat($(this).val().replace(/,/g, "")) || 0;
        sum1 += val;
    });

    $("input.inqty2").each(function () {
        const val = parseFloat($(this).val().replace(/,/g, "")) || 0;
        sum2 += val;
    });

    $("input.inqty3").each(function () {
        const val = parseFloat($(this).val().replace(/,/g, "")) || 0;
        sum3 += val;
    });

    $("input.inqty4").each(function () {
        const val = parseFloat($(this).val().replace(/,/g, "")) || 0;
        sum4 += val;
    });

    // 총 구매 수량 표시
    $("#totalsurangDisplay").text(totalQty.toLocaleString());

    // 각 차수 입고 합계를 hidden input에 반영
    $("#inputSum1").val(sum1);
    $("#inputSum2").val(sum2);
    $("#inputSum3").val(sum3);
    $("#inputSum4").val(sum4);

    // 각 차수 입고 합계를 span에도 표시
    $("#inputSumDisplay1").text(sum1.toLocaleString());
    $("#inputSumDisplay2").text(sum2.toLocaleString());
    $("#inputSumDisplay3").text(sum3.toLocaleString());
    $("#inputSumDisplay4").text(sum4.toLocaleString());
}

function loadTableData(tableId, dataList, typebutton) {
    try {
        var tableBody = $(tableId).find('tbody');
        var theadId;

        console.log('write_input loadTableData called with:', { tableId, dataList, typebutton });

        switch (tableId) {
            case '#orderlistTable':
                theadId = '#thead_orderlist';
                break;
            default:
                theadId = null;
        }

        if (typeof dataList === 'string') {
            try {
                dataList = JSON.parse(dataList);
                console.log('write_input Parsed dataList:', dataList);
            } catch (e) {
                console.error('Failed to parse dataList:', e);
                dataList = [];
            }
        }

        if (theadId) {
            if (dataList.length === 0) {
                $(theadId).hide();
            } else {
                $(theadId).show();
            }
        }

        if (!Array.isArray(dataList)) {
            console.warn('write_input dataList is not an array:', dataList);
            dataList = [];
        }

        if (dataList.length === 0) {
            console.log('write_input no record');
        } else {
            console.log('write_input Adding', dataList.length, 'rows');
            console.log('write_input dataList structure:', dataList);
            
            dataList.forEach(function(item, index) {
                console.log('write_input Processing row', index + 1, ':', item);
                console.log('write_input Row', index + 1, 'keys:', Object.keys(item));
                console.log('write_input Row', index + 1, 'values:', item);
                
                try {
                    addRow(tableBody, item, typebutton);
                    console.log('write_input Successfully added row', index + 1);
                } catch (error) {
                    console.error('write_input Error adding row', index + 1, ':', error, item);
                }
            });
        }
    } catch (error) {
        console.error('Error in write_input loadTableData:', error);
    }
}

function formatNumber(input) {
    input.value = input.value.replace(/\D/g, '');
    input.value = input.value.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function saveData() {
    const myform = document.getElementById('board_form');
    const inputs = myform.querySelectorAll('input[required]');
    let allValid = true;

    // console.log(inputs);	
    inputs.forEach(input => {
        if (!input.value) {
            allValid = false;
            Toastify({
                text: "수량 등 필수입력 부분을 확인해 주세요.",
                duration: 2000,
                close: true,
                gravity: "top",
                position: "center",
                style: {
                    background: "linear-gradient(to right, #00b09b, #96c93d)"
                },
            }).showToast();
            return;
        }
    });

    if (!allValid) return;

    var num = $("#num").val();    
    $("button").prop("disabled", true);
  
    if (Number(num) < 1) {
        $("#mode").val('insert');
    } else {
        $("#mode").val('modify');
    }

    let formData = [];
    $('#orderlistTable tbody tr').each(function() {
        let rowData = {};
        $(this).find('input, select').each(function() {
            let name = $(this).attr('name').replace('[]', '');
            let value = $(this).val();
            rowData[name] = value;
        });
        formData.push(rowData);
    });
    let jsonString = JSON.stringify(formData);
    $('#orderlist').val(jsonString);

    var form = $('#board_form')[0];
    var datasource = new FormData(form);

    if (ajaxRequest_write !== null) {
        ajaxRequest_write.abort();
    }
	
	showMsgModal(2); // 1 이미지 저장, 2 파일저장
	
    ajaxRequest_write = $.ajax({
        enctype: 'multipart/form-data',
        processData: false,
        contentType: false,
        cache: false,
        timeout: 600000,
        url: "insert_input.php", // insert.php 분리 각 요소에 맞게 수정함
        type: "post",
        data: datasource,
        dataType: "json",
        success: function(data) {
			console.log(data);
            setTimeout(function() {
                if (window.opener && !window.opener.closed) {
					hideMsgModal();
                    if (typeof window.opener.restorePageNumber === 'function') {
                        window.opener.restorePageNumber();
                    }
                }
            }, 1000);
            ajaxRequest_write = null;
            setTimeout(function() {
                hideMsgModal();
                self.close();
            }, 1000);
        },
        error: function(jqxhr, status, error) {
            console.log(jqxhr, status, error);
			ajaxRequest_write = null;
			hideMsgModal();
        }
    });
}

function deleteData() {
    var first_writer = '<?= $first_writer ?>';
    var level = '<?= $level ?>';

    if (!first_writer.includes(first_writer) && level !== '1') {
        Swal.fire({
            title: '삭제불가',
            text: "작성자와 관리자만 삭제가능합니다.",
            icon: 'error',
            confirmButtonText: '확인'
        });
    } else {
        Swal.fire({
            title: '자료 삭제',
            text: "삭제는 신중! 정말 삭제하시겠습니까?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: '삭제',
            cancelButtonText: '취소'
        }).then((result) => {
            if (result.isConfirmed) {
                $("#mode").val('delete');
                var form = $('#board_form')[0];
                var formData = new FormData(form);

                formData.set('mode', $("#mode").val());
                formData.set('num', $("#num").val());

                if (ajaxRequest_write !== null) {
                    ajaxRequest_write.abort();
                }

                ajaxRequest_write = $.ajax({
                    enctype: 'multipart/form-data',
                    processData: false,
                    contentType: false,
                    cache: false,
                    timeout: 1000000,
                    url: "insert_input.php",
                    type: "post",
                    data: formData,
                    dataType: "json",
                    success: function(data) {
                        Toastify({
                            text: "파일 삭제완료 ",
                            duration: 2000,
                            close: true,
                            gravity: "top",
                            position: "center",
                            style: {
                                background: "linear-gradient(to right, #00b09b, #96c93d)"
                            },
                        }).showToast();
                        setTimeout(function() {
                            if (window.opener && !window.opener.closed) {
                                if (typeof window.opener.restorePageNumber === 'function') {
                                    window.opener.restorePageNumber();
                                }
                                window.opener.location.reload();
                                window.close();
                            }
                        }, 1000);
                    },
                    error: function(jqxhr, status, error) {
                        console.log(jqxhr, status, error);
                    }
                });
            }
        });
    }
}

function disableInputsForViewMode() {
    // readonly 처리 (passView 제외)
    $('input, textarea').not('.passView').prop('readonly', true);

    // select, 버튼 등 (passView 제외)
    $('select, .restrictbtn, .sub_add, .add').not('.passView').prop('disabled', true);

    // 파일 업로드는 항상 readonly false 유지
    $('input[type=file]').prop('readonly', false);

    // 체크박스는 passView 제외
    $('input[type=checkbox]').not('.passView').prop('disabled', true);

    // 기타 버튼
    $('.viewNoBtn').not('.passView').prop('disabled', true);
    $('.specialbtnClear').not('.passView').prop('disabled', true);
}

function captureReturnKey(e) {
    if (e.keyCode == 13 && e.srcElement.type != 'textarea') {
        return false;
    }
}

function closePopup() {
    if (popupWindow && !popupWindow.closed) {
        popupWindow.close();
        isWindowOpen = false;
    }
}

function showWarningModal() {
    Swal.fire({
        title: '등록 오류 알림',
        text: '필수입력 요소를 확인바랍니다.',
        icon: 'warning',
    }).then(result => {
        if (result.isConfirmed) {
            return;
        }
    });
}

function showlotError() {
    Swal.fire({
        title: '등록 오류 알림',
        text: '입력 항목들을 점검해주세요.',
        icon: 'warning',
    }).then(result => {
        if (result.isConfirmed) {
            return;
        }
    });
}

function inputNumber(input) {
    const cursorPosition = input.selectionStart;
    const value = input.value.replace(/,/g, '');
    const formattedValue = Number(value).toLocaleString();
    input.value = formattedValue;
    input.setSelectionRange(cursorPosition, cursorPosition);
}
</script>

<script>
function generateExcel() {
    // 엑셀로 저장할 테이블 ID 목록
    var tableIds = [
        'orderlistTable' 
    ];
    
   var data = [];
   
    tableIds.forEach(function(tableId) {
        var table = document.getElementById(tableId);
        if (!table) return;  // 해당 테이블이 없으면 건너뜀
        
        // tbody 내의 모든 행을 순회
        var tbody = table.getElementsByTagName('tbody')[0];
        var rows = tbody.getElementsByTagName('tr');
        for (var i = 0; i < rows.length; i++) {
            var cells = rows[i].getElementsByTagName('td');
            var rowData = {};
            
            // // 첫 번째 셀: select 요소가 있다면 선택된 옵션의 텍스트만 추출
            // if (cells[0]) {
                // var selectElem = cells[0].querySelector('select');
                // if (selectElem) {
                    // rowData['model'] = selectElem.options[selectElem.selectedIndex].text.trim();
                // } else {
                    // rowData['model'] = cells[0].innerText.trim();
                // }
            // } else {
                // rowData['model'] = '';
            // }
					
		// 셀에서 값 추출: input, select, textarea가 있으면 그 값, 없으면 innerText 사용
		function getCellValue(cell) {
			if (!cell) return '';
			var formElem = cell.querySelector('input, select, textarea');
			if (formElem) {
				return formElem.value.trim();
			} else {
				return cell.innerText.trim();
			}
		}
		
		// 예시: 각 셀에서 값 추출하여 rowData에 저장
		rowData['model_code']  	 = cells[0] ? getCellValue(cells[0]) : '';
		rowData['model']   		 = cells[1] ? getCellValue(cells[1]) : '';
		rowData['purchaseQty']   = cells[2] ? getCellValue(cells[2]) : '';
		rowData['unitPrice']     = cells[3] ? getCellValue(cells[3]) : '';
		rowData['note']          = cells[4] ? getCellValue(cells[4]) : '';
		rowData['amount']        = cells[5] ? getCellValue(cells[5]) : '';
		
		data.push(rowData);
        }
    });
    
    console.log('엑셀 data', data);
    
    
    // saveExcel.php에 데이터 전송
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "order_saveExcel.php", true);
    xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
    xhr.onreadystatechange = function () {
         if (xhr.readyState === 4) {
             if (xhr.status === 200) {
                 try {
                     var response = JSON.parse(xhr.responseText);
                     if (response.success) {
                         console.log('Excel file generated successfully.');
                         window.location.href = 'downloadExcel.php?filename=' + encodeURIComponent(response.filename.split('/').pop());
                     } else {
                         console.log('Failed to generate Excel file: ' + response.message);
                     }
                 } catch (e) {
                     console.log('Error parsing response: ' + e.message + '\nResponse text: ' + xhr.responseText);
                 }
             } else {
                 console.log('Failed to generate Excel file: Server returned status ' + xhr.status);
             }
         }
    };
    xhr.send(JSON.stringify(data));
}

$(document).ready(function() {
	$('#showAllTable').click(function(){
		// 각 테이블 ID 목록 (select 요소를 포함하는 테이블)
		var tableIds = [
			'orderlistTable'
		];
		
		tableIds.forEach(function(tableId) {
			var tableBody = $('#' + tableId).find('tbody');
			
			// 각 테이블에 해당하는 옵션 배열 가져오기
			var optionsList = [];
			switch(tableId) {
				case 'orderlistTable':
					optionsList = orderlistOptions;
					break;
				default:
					optionsList = [];
					break;
			}
			
			// 각 옵션마다 행 추가 (rowData.col1 에 해당 옵션을 설정)
			 optionsList.forEach(function(option) {
            var exists = false;
            tableBody.find('tr').each(function(){                
                var currentOption = $(this).find('input[name="col1[]"]').val();
                if (currentOption === option) {
                    exists = true;
                    return false; // 반복문 탈출
                }
            });
            if (!exists) {
                // 해당 옵션이 없으면 행 추가 (첫 열에 option 값 설정)
                addRow(tableBody, { col1: option }, tableId);
				}
			});
		});
	});

	$('#removeNoitem').click(function(){
		// 대상 테이블 ID 목록
		var tableIds = [
			'orderlistTable'
		];
		
		tableIds.forEach(function(tableId) {
			$('#' + tableId + ' tbody tr').each(function(){
				// 구매수량은 두 번째 열의 input (name="col2[]")
				var purchaseQty = parseFloat($(this).find('input[name="col2[]"]').val()) || 0;
				if (purchaseQty <= 0) {
					$(this).remove();
				}
			});
		});
	});

	$('#updateDate').click(function(){
		// 선택된 차수 ("1차", "2차", "3차", "4차")
		var selectedPhase = $('#selectDate').val();
		// 새 날짜 값
		var newDate = $('#todayDate').val();

        console.log('updateDate clicked:', selectedPhase, newDate);
		
		// 각 차수에 해당하는 name 속성 매핑
		var colName;
		
		switch(selectedPhase) {
			case '1차':
				colName = 'col7'; // 1차 입고일
				break;
			case '2차':
				colName = 'col10'; // 2차 입고일
				break;
			case '3차':
				colName = 'col13'; // 3차 입고일
				break;			
			case '4차':
				colName = 'col16'; // 4차 입고일
				break;
			default:
				colName = 'col7'; // 1차 입고일
		}
		
		// 대상 테이블 ID 목록 (모든 품목 테이블)
		var tableIds = [
			'orderlistTable'
		];
		
		// 각 테이블의 tbody를 순회하여, 해당 name 속성을 가진 date input 값을 새 날짜로 업데이트
		tableIds.forEach(function(tableId) {
			var selector = '#' + tableId + ' tbody tr input[name="' + colName + '[]"]';
			console.log('Updating selector:', selector);
			
			$(selector).each(function(){
				console.log('Updating date input:', this, 'to value:', newDate);
				$(this).val(newDate);
			});
		});
		
		// 상단의 차수별 입고일 input도 동기화
		var phaseNumber = selectedPhase.replace('차', '');
		$('#inputDate' + phaseNumber).val(newDate);
		
		console.log('Date update completed for phase:', selectedPhase);
	});
	
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });	
});
</script>

<script>
function updateTableMinWidth() {
	const checkedCount = $('.phase-toggle:checked').length;

	let minWidth;
	switch (checkedCount) {
		case 4:
			minWidth = 2800;
			break;
		case 3:
			minWidth = 2500;
			break;
		case 2:
			minWidth = 2300;
			break;
		case 1:
			minWidth = 1800;
			break;
		default:
			minWidth = 1800;
	}

	$('#orderlistTable').css('min-width', `${minWidth}px`);
}

// 차수별 열 토글 함수
function togglePhaseColumns(phase, show) {
    updateTableMinWidth();    
    const columnMap = {
        1: [8, 9, 10],   // 1차 입고일, 입고수량, 로트번호
        2: [11, 12, 13], // 2차 입고일, 입고수량, 로트번호
        3: [14, 15, 16], // 3차 입고일, 입고수량, 로트번호
        4: [17, 18, 19], // 4차 입고일, 입고수량, 로트번호
    };

    const indexes = columnMap[phase];
    if (!indexes) return;

    const $table = $('#orderlistTable');

    $table.find('tr').each(function () {
        indexes.forEach(function (idx) {
            $(this).find('th, td').eq(idx).toggle(show);
        }.bind(this));
    });
    
    console.log('Phase', phase, 'columns toggled:', show ? 'shown' : 'hidden');
}

// 체크박스 단일 제어
$(document).on('change', '.phase-toggle', function () {
    const phase = $(this).data('phase');
    const isChecked = $(this).is(':checked');
    togglePhaseColumns(phase, isChecked);
});

// 전체 체크
$('#checkAllPhases').on('click', function () {
    $('.phase-toggle').each(function () {
        $(this).prop('checked', true);
        const phase = $(this).data('phase');
        togglePhaseColumns(phase, true);
    });
    updateTableMinWidth();
});

// 전체 해제
$('#uncheckAllPhases').on('click', function () {
    $('.phase-toggle').each(function () {
        $(this).prop('checked', false);
        const phase = $(this).data('phase');
        togglePhaseColumns(phase, false);
    });
    updateTableMinWidth();
});

$(document).ready(function() {
    // 1~4차 입고일 동기화 처리
    [1, 2, 3, 4].forEach(function(i) {
        $('#inputDate' + i).on('change', function() {
            var date = $(this).val();
            var colName = 'col' + (6 + i * 3 - 2);
            var selector = 'input[name="' + colName + '[]"]';
            console.log('Syncing date from inputDate' + i + ' to table:', date, 'selector:', selector);
            
            $('#orderlistTable tbody tr').each(function() {
                $(this).find(selector).val(date);
            });
        });
    });

    // ★ 체크박스 상태에 따라 열 표시 초기화
    [1, 2, 3, 4].forEach(function(phase) {
        var checked = $('#chkPhase' + phase).is(':checked');
        togglePhaseColumns(phase, checked);
    });
    
    // 초기 테이블 너비 설정
    updateTableMinWidth();
    
    // 페이지 로드 시 상단 입고일 input과 테이블 내 날짜 input 동기화
    setTimeout(function() {
        [1, 2, 3, 4].forEach(function(i) {
            var topDate = $('#inputDate' + i).val();
            if (topDate) {
                var colName = 'col' + (6 + i * 3 - 2);
                var selector = 'input[name="' + colName + '[]"]';
                $(selector).val(topDate);
            }
        });
    }, 100);
});

/**
 * stage 차수(1~4)를 받아,
 * #inputDate{stage} 의 날짜와
 * #orderlistTable tbody 각 행에서 lotnum{stage}가 비어있지 않은 로우만 골라
 * material_reg 테이블에 insert 요청을 보냅니다.
 */
function completeInStock(stage) {
    const inoutDate = $('#inputDate' + stage).val();
    if (!inoutDate) {
        alert(stage + '차 입고일을 선택해주세요.');
        return;
    }

    // 저장할 항목들 모으기
    const items = [];
    $('#orderlistTable tbody tr').each(function() {
        const $tr = $(this);
        const lotnum = $tr.find('.lotnum' + stage).val().trim();
        if (!lotnum) return;  // 로트번호가 빈 문자열이면 건너뜀

        const item_code  = $tr.find('input[name="col1[]"]').val().trim();
        const item_name  = $tr.find('input[name="col2[]"]').val().trim();
        const surang     = $tr.find('.inqty' + stage).val().trim() || '0';
        const comment    = $tr.find('input[name="col5[]"]').val().trim();

        items.push({
            inout_item_code: item_code,
            item_name:      item_name,
            surang:         surang,
            lotnum:         lotnum,
            comment:        comment
        });
    });

    if (items.length === 0) {
        alert(stage + '차에 저장할 로트번호가 없습니다.');
        return;
    }

    // AJAX 요청
    $.ajax({
        url: '/m_order/process_instock.php',
        method: 'POST',
        dataType: 'json',
        data: {
            mode:       'insert',
            tablename:  'material_reg',
            inoutdate:  inoutDate,
            secondord:  '안린',                 // 거래처
            items:      JSON.stringify(items)  // 실제 저장할 데이터 배열
        },
		success(res) {
			if (res.success) {
				Swal.fire({
					icon: 'success',
					title: `${stage}차 전산 입고처리 완료`,
					showConfirmButton: false,
					timer: 1500
				});
				$('#btnComplete' + stage).prop('disabled', true);
			} else {
				Swal.fire({
					icon: 'error',
					title: '저장 실패',
					text: res.message
				});
			}
		},
        error() {
            alert('서버 통신 오류가 발생했습니다.');
        }
    });
}

</script>

</body>
</html>