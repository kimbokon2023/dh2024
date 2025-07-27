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
    $title_message = "(데이터복사) 구매 발주서";
} else {
    $title_message = "구매 발주서";
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
    <input type="hidden" id="orderlist" name="orderlist">	
	
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/m_order/modal.php'; ?>
	
    <div class="container">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-center align-items-center mt-3 mb-5">
                    <span class="fs-5 me-5"> <?=$title_message?> (<?=$mode?>) </span>
                    <?php if ($mode !== 'view') { ?>
                        <button type="button" class="btn btn-dark btn-sm me-2 saveBtn"> <ion-icon name="save-outline"></ion-icon> 저장 </button>
                    <?php } else { ?>
						<button type="button" class="btn btn-dark btn-sm mx-2"  onclick='location.reload();' > <i class="bi bi-arrow-clockwise"></i> </button>  
                        <button type="button" class="btn btn-dark btn-sm me-1" onclick="location.href='write_form.php?mode=modify&num=<?=$num?>';">  <i class="bi bi-pencil-square"></i>  수정  </button>
                        <button type="button" class="btn btn-danger btn-sm me-1 deleteBtn"><i class="bi bi-trash"></i>  삭제  </button>
                        <button type="button" class="btn btn-dark btn-sm me-1" onclick="location.href='write_form.php';"> <i class="bi bi-pencil"></i>  신규 </button>
                        <button type="button" class="btn btn-primary btn-sm me-1" onclick="location.href='write_form.php?mode=copy&num=<?=$num?>';"> <i class="bi bi-copy"></i> 복사</button>
                        <button type="button" class="btn btn-secondary btn-sm me-1" onclick="generateExcel();"> Excel 구매 발주서 </button>
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
            </div>
        </div>
      </div>
	<div class="container-fluid">	
 <?php
	function generateTableSection($id, $title, $badgeClass = 'bg-secondary') {
		echo "
		<div class='d-flex row justify-content-center  align-items-center m-1 p-2 rounded' style='border: 1px solid #392f31;'>
			<div class='d-flex justify-content-start  align-items-center  mb-2'>
				<span class='badge $badgeClass fs-6 me-3'>$title</span>
				<button type='button' class='btn btn-dark btn-sm viewNoBtn add-row' data-table='{$id}Table'>+</button>
				
				<span class=' fs-6 ms-5 me-2'>총수량 </span>
				<input type='text' id='totalsurang' name='totalsurang' readonly class='form-control text-end w120px ms-1 me-5 fs-6'> 
				<span class=' fs-6 mx-2'>총금액합 </span>
				<input type='text' id='totalamount' name='totalamount' readonly class='form-control text-end w120px ms-1 me-5 fs-6'> 
			</div>
			<div class='d-flex row'>
				<table class='table table-bordered table-hover' id='{$id}Table'>
					<thead id='thead_$id'>
						<tr>
							<th class='text-center' style='width:8%;'> NO (+/-) </th>
							<th class='text-center' style='width:5%;'> 카테고리</th>
							<th class='text-center' style='width:15%;'> 품목코드</th>
							<th class='text-center' style='width:20%;'> 품목명</th>
							<th class='text-center' style='width:5%;'> 구매수량</th>
							<th class='text-center' style='width:5%;'> 단가(위엔)</th>
							<th class='text-center' style='width:25%;'> 비고</th>
							<th class='text-center' style='width:5%;'> 금액(위엔)</th>							
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
	
	generateTableSection('orderlist', '중국 발주 리스트');
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
        console.log('initializePage - orderlist:', orderlist);

        // orderlist가 null이나 undefined인 경우 빈 배열로 초기화
        if (!orderlist || !Array.isArray(orderlist)) {
            console.warn('orderlist is invalid, using empty array:', orderlist);
            orderlist = [];
        }

        loadTableData('#orderlistTable', orderlist, 'orderlistTable');

        if ('<?= $mode ?>' === 'view') {
            disableInputsForViewMode();
        }
        
        console.log('Page initialized successfully');
    } catch (error) {
        console.error('Error in initializePage:', error);
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

function updateRowCalculation(element) {
    var row = $(element).closest('tr');
    
    // 구매수량과 단가
    var purchaseQty = parseFloat(row.find('.purchase-qty').val()) || 0;
    var unitPrice = parseFloat(row.find('.unit-price').val()) || 0;
    
    // 각 차수 입고수량
    var inQty1 = parseFloat(row.find('.inqty1').val()) || 0;
    var inQty2 = parseFloat(row.find('.inqty2').val()) || 0;
    var inQty3 = parseFloat(row.find('.inqty3').val()) || 0;
    var inQty4 = parseFloat(row.find('.inqty4').val()) || 0;
    
    // 금액 계산
    var amount = purchaseQty * unitPrice;
    row.find('.amount').val(amount.toLocaleString());
    
    // 구매수량합: 구매수량 그대로 표시
    row.find('.total-purchase').val(purchaseQty.toLocaleString());
    
    // 입고합: 1차+2차+3차+4차 입고수량의 합
    var totalInQty = inQty1 + inQty2 + inQty3 + inQty4;
    row.find('.total-inqty').val(totalInQty.toLocaleString());
    
    // 구매입고차이 계산
    var diff = purchaseQty - totalInQty;
    row.find('.diff').val(diff.toLocaleString());
    
    // 상태: 차이가 0이고 구매수량이 0보다 크면 "완료"
    var status = (diff === 0 && purchaseQty > 0) ? '완료' : '';
    row.find('.status').val(status);
}
showInoutColumns = false; // 구매입력시 안보이게...

function addRow(tableBody, rowData, typebutton) {
    try {
        var showInout = showInoutColumns || false;
        console.log('addRow called with:', { tableBody, rowData, typebutton });

        // rowData가 null이나 undefined인 경우 빈 객체로 초기화
        if (!rowData || typeof rowData !== 'object') {
            console.warn('rowData is invalid, using empty object:', rowData);
            rowData = {};
        }

        var table = tableBody.closest('table');
        var thead = table.find('thead');
        if (thead.length !== 0) {
            thead.css('display', 'table-header-group');
        }

        var newRow = $('<tr>');

        newRow.append('<td class="text-center" >' +
            '<div class="d-flex justify-content-center align-items-center">' +
            '<span class="serial-number me-4"></span>' +
            '<button type="button" class="btn btn-dark btn-sm viewNoBtn add-row_new me-2" data-table="orderlistTable" >+</button> <button type="button" class="btn btn-danger btn-sm remove-row viewNoBtn">-</button>' +
            '</div></td>');

        // 카테고리 선택 필드 추가
        var categoryValue = rowData.col0 || '모터'; // 기본값은 '모터'
        var categorySelect = '<select name="col0[]" class="form-select w-auto item-category" style="font-size: 0.7rem;">' +
            '<option value="모터" ' + (categoryValue === '모터' ? 'selected' : '') + '>모터</option>' +
            '<option value="연동제어기" ' + (categoryValue === '연동제어기' ? 'selected' : '') + '>연동제어기</option>' +
            '<option value="운송비" ' + (categoryValue === '운송비' ? 'selected' : '') + '>운송비</option>' +
            '<option value="부속자재" ' + (categoryValue === '부속자재' ? 'selected' : '') + '>부속자재</option>' +
            '</select>';
        newRow.append('<td class="text-center ' + (showInout ? '' : 'expanded') + '">' + categorySelect + '</td>');

        var codeInput = '<div class="specialinputWrap"> <input type="text" name="col1[]" class="form-control item-code" placeholder="품목코드" autocomplete="off" value="' + (rowData.col1 || '') + '"> <button class="specialbtnClear"></button> </div>';
        newRow.append('<td class="text-center ' + (showInout ? '' : 'expanded') + '">' + codeInput + '</td>');

        var nameInput = '<div class="specialinputWrap">  <input type="text" name="col2[]" class="form-control item-name" placeholder="품목명" autocomplete="off" value="' + (rowData.col2 || '') + '"> <button class="specialbtnClear"></button> </div>';
        newRow.append('<td class="text-center ' + (showInout ? '' : 'expanded') + '">' + nameInput + '</td>');

        newRow.append('<td class="text-center ' + (showInout ? '' : 'expanded') + '"><input type="number" name="col3[]" class="form-control text-end purchase-qty" autocomplete="off" value="' + (rowData.col3 || '') + '" required onkeyup="updateRowCalculation(this);"></td>');
        newRow.append('<td class="text-center ' + (showInout ? '' : 'expanded') + '"><input type="number" name="col4[]" class="form-control text-end unit-price" autocomplete="off" value="' + (rowData.col4 || '') + '" required onkeyup="updateRowCalculation(this);"></td>');
        newRow.append('<td class="text-center ' + (showInout ? '' : 'expanded') + '"><input type="text" name="col5[]" class="form-control" autocomplete="off" value="' + (rowData.col5 || '') + '"></td>');
        newRow.append('<td class="text-center ' + (showInout ? '' : 'expanded') + '"><input type="text" name="col6[]" class="form-control text-end amount" autocomplete="off" value="' + (rowData.col6 ? Number(rowData.col6).toLocaleString() : '') + '" readonly></td>');

        for (let i = 1; i <= 4; i++) {
            let dateCol = 6 + i * 3 - 2;
            let qtyCol = 6 + i * 3 - 1 ;
            let lotCol = 6 + i * 3 ;

            newRow.append('<td class="text-center ' + (showInout ? '' : 'hidden') + '"><input type="date" name="col' + dateCol + '[]" class="form-control" value="' + (rowData['col' + dateCol] || '') + '"></td>');
            newRow.append('<td class="text-center ' + (showInout ? '' : 'hidden') + '"><input type="number" name="col' + qtyCol + '[]" class="form-control text-end inqty' + i + '" value="' + (rowData['col' + qtyCol] || '') + '" onkeyup="updateRowCalculation(this);"></td>');
            newRow.append('<td class="text-center ' + (showInout ? '' : 'hidden') + '"><input type="text" name="col' + lotCol + '[]" class="form-control lotnum' + i + '" value="' + (rowData['col' + lotCol] || '') + '"></td>');
        }

        newRow.append('<td class="text-center ' + (showInout ? '' : 'hidden') + '"><input type="text" name="col19[]" class="form-control text-end total-purchase" autocomplete="off" value="" readonly></td>');
        newRow.append('<td class="text-center ' + (showInout ? '' : 'hidden') + '"><input type="text" name="col20[]" class="form-control text-end total-inqty" autocomplete="off" value="" readonly></td>');
        newRow.append('<td class="text-center ' + (showInout ? '' : 'hidden') + '"><input type="text" name="col21[]" class="form-control text-end diff" autocomplete="off" value="" readonly></td>');
        newRow.append('<td class="text-center ' + (showInout ? '' : 'hidden') + '"><input type="text" name="col22[]" class="form-control fw-bold text-center status" autocomplete="off" value="" readonly></td>');

        // 23~30: 1~4차 입고금액 + 송금액
        for (let i = 1; i <= 4; i++) {
            newRow.append(`
                <td style="display:none;"><input type="text" name="col${22 + i * 2 - 1}[]" class="form-control text-end " value="${rowData[`col${22 + i * 2 - 1}`] || ''}"></td>
            `);
            newRow.append(`
                <td style="display:none;"><input type="text" name="col${22 + i * 2}[]" class="form-control text-end " value="${rowData[`col${22 + i * 2 }`] || ''}"></td>
            `);
        }

        tableBody.append(newRow);

        updateSerialNumbers(tableBody);

        function updateSerialNumbers(tableBody) {
            tableBody.find('tr').each(function(index) {
                $(this).find('.serial-number').text(index + 1);
            });
        }

        initializeAutocomplete(newRow.find('input.item-code'));
        initializeAutocomplete(newRow.find('input.item-name'));
        updateRowCalculation(newRow.find('input.purchase-qty')[0]);
        
        console.log('Row added successfully');
    } catch (error) {
        console.error('Error in addRow function:', error);
        console.error('rowData:', rowData);
        console.error('typebutton:', typebutton);
    }
}

function updateRowCalculation(input) {
    const $row = $(input).closest("tr");

    const purchaseQty = parseFloat($row.find("input.purchase-qty").val().replace(/,/g, '')) || 0;
    const unitPrice = parseFloat($row.find("input.unit-price").val().replace(/,/g, '')) || 0;
    const amount = purchaseQty * unitPrice;
    $row.find("input.amount").val(amount.toLocaleString());

    let totalInQty = 0;

    for (let i = 1; i <= 4; i++) {
        const inQty = parseFloat($row.find(`.inqty${i}`).val().replace(/,/g, '')) || 0;
        const inAmount = inQty * unitPrice;
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

    // 모든 .purchase-qty 요소를 순회하며 합산
    $("input.purchase-qty").each(function() {
        const qty = parseFloat($(this).val().replace(/,/g, "")) || 0;
        totalQty += qty;
    });

    // 모든 .amount 요소를 순회하며 합산
    $("input.amount").each(function() {
        const amount = parseFloat($(this).val().replace(/,/g, "")) || 0;
        totalAmount += amount;
    });

    // 결과 입력
    $("#totalsurang").val(totalQty.toLocaleString());
    $("#totalamount").val(totalAmount.toLocaleString());
}

function loadTableData(tableId, dataList, typebutton) {
    var tableBody = $(tableId).find('tbody');
    var theadId;

    console.log('loadTableData called with:', { tableId, dataList, typebutton });

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
            console.log('Parsed dataList:', dataList);
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
        console.warn('dataList is not an array:', dataList);
        dataList = [];
    }

    if (dataList.length === 0) {
        console.log('no record');
    } else {
        console.log('Adding', dataList.length, 'rows');
        dataList.forEach(function(item, index) {
            console.log('Adding row', index + 1, ':', item);
            try {
                addRow(tableBody, item, typebutton);
            } catch (error) {
                console.error('Error adding row', index + 1, ':', error, item);
            }
        });
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
        url: "insert.php",
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
                    url: "insert.php",
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
    $('input, textarea').prop('readonly', true);
    $('select, .restrictbtn, .sub_add, .add').prop('disabled', true);
    $('input[type=file]').prop('readonly', false);
    $('input[type=checkbox]').prop('disabled', true);
    $('.viewNoBtn').prop('disabled', true);
    $('.specialbtnClear').prop('disabled', true);
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

$(document).ready(function() {
	$('#removeNoitem').click(function(){
		// 대상 테이블 ID 목록
		var tableIds = [
			'orderlistTable'
		];
		
		tableIds.forEach(function(tableId) {
			$('#' + tableId + ' tbody tr').each(function(){
				// 구매수량은 네 번째 열의 input (name="col3[]") - 카테고리 추가로 인한 위치 변경
				var purchaseQty = parseFloat($(this).find('input[name="col3[]"]').val()) || 0;
				if (purchaseQty <= 0) {
					$(this).remove();
				}
			});
		});
	});

	
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });	
});

function generateExcel() {
    // 엑셀로 저장할 테이블 ID 목록
    var tableIds = ['orderlistTable'];
    var data = [];

    // ✅ 구매발주일 값 읽기
    var orderDate = document.getElementById('orderDate')?.value || '';

    tableIds.forEach(function(tableId) {
        var table = document.getElementById(tableId);
        if (!table) return;

        var tbody = table.getElementsByTagName('tbody')[0];
        var rows = tbody.getElementsByTagName('tr');
        for (var i = 0; i < rows.length; i++) {
            var cells = rows[i].getElementsByTagName('td');
            var rowData = {};

            function getCellValue(cell) {
                if (!cell) return '';
                var formElem = cell.querySelector('input, select, textarea');
                if (formElem) {
                    return formElem.value.trim();
                } else {
                    return cell.innerText.trim();
                }
            }

            rowData['category']     = cells[1] ? getCellValue(cells[1]) : '';
            rowData['model_code']   = cells[2] ? getCellValue(cells[2]) : '';
            rowData['model']        = cells[3] ? getCellValue(cells[3]) : '';
            rowData['purchaseQty']  = cells[4] ? getCellValue(cells[4]) : '';
            rowData['unitPrice']    = cells[5] ? getCellValue(cells[5]) : '';
            rowData['note']         = cells[6] ? getCellValue(cells[6]) : '';
            rowData['amount']       = cells[7] ? getCellValue(cells[7]) : '';

            data.push(rowData);
        }
    });

    console.log('엑셀 data', data);

    // ✅ 서버에 보낼 최종 payload
    var payload = {
        orderDate: orderDate,
        items: data
    };

    // 전송
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
    xhr.send(JSON.stringify(payload));
}

</script>
</body>
</html>