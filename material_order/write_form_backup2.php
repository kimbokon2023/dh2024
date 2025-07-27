<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php"); 
    exit;
}

include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';

$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  
$num = isset($_REQUEST["num"]) ? $_REQUEST["num"] : "";

$first_writer = '';

if ($mode === 'copy') {
    $title_message = "(데이터복사) 구매 발주서";
} else {
    $title_message = "구매 발주서";
}

$tablename = 'material_order';
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
        $sql = "select * from material_order where num = ?";
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
        $sql = "select * from material_order where num = ?";
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
    <input type="hidden" id="motorlist" name="motorlist">
    <input type="hidden" id="wirelessClist" name="wirelessClist">
    <input type="hidden" id="wireClist" name="wireClist">
    <input type="hidden" id="wirelessLinklist" name="wirelessLinklist">
    <input type="hidden" id="wireLinklist" name="wireLinklist">
    <input type="hidden" id="bracketlist" name="bracketlist">

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/material_order/modal.php'; ?>

    <div class="container">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-center align-items-center mt-3 mb-5">
                    <span class="fs-5 me-5"> <?=$title_message?> (<?=$mode?>) </span>
                    <?php if ($mode !== 'view') { ?>
                        <button type="button" class="btn btn-dark btn-sm me-2 saveBtn"> <ion-icon name="save-outline"></ion-icon> 저장 </button>
                    <?php } else { ?>
						<button type="button" class="btn btn-dark btn-sm mx-2"  onclick='location.reload();' > <i class="bi bi-arrow-clockwise"></i> </button>  
                        <button type="button" class="btn btn-dark btn-sm me-1" onclick="location.href='write_form.php?mode=modify&num=<?=$num?>';"> <ion-icon name="color-wand-outline"></ion-icon> 수정 </button>
                        <button type="button" class="btn btn-danger btn-sm me-1 deleteBtn"> <ion-icon name="trash-outline"></ion-icon> 삭제 </button>
                        <button type="button" class="btn btn-dark btn-sm me-1" onclick="location.href='write_form.php';"> <ion-icon name="create-outline"></ion-icon> 신규 </button>
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
                        <button class="btn btn-secondary btn-sm" onclick="self.close();"> <i class="bi bi-x-lg"></i> 닫기 </button> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
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
				<?php if ($mode !== 'view') : ?>				
					<div class="d-flex justify-content-center align-items-center">
						<button type="button" class="btn btn-primary btn-sm mx-2" id="showAllTable"> 전체항목 펼치기 </button>				
						<button type="button" class="btn btn-danger btn-sm mx-2" id="removeNoitem"> 구매수량 '0' 전체 제거 </button>			
						<select id="selectDate" name="selectDate" class="form-select d-block w-auto ms-5 me-1">
							<option value="1차">1차</option>
							<option value="2차">2차</option>
							<option value="3차">3차</option>
						</select>
						<h6> <span class="text-end mx-1"> 입고일 </span> </h6>
						<input type="date" name="todayDate" id="todayDate" value="<?=$todayDate?>" class="form-control mx-1" style="width:100px;">
						<button type="button" class="btn btn-primary btn-sm mx-2" id="updateDate"> 실행 </button>			 
					</div>
				<?php endif; ?>	
            </div>
        </div>
      </div>
	<div class="container-fluid">
 <?php
	function generateTableSection($id, $title, $badgeClass = 'bg-dark') {
		echo "
		<div class='d-flex row justify-content-center m-1 p-2 rounded' style='border: 1px solid #392f31;'>
			<div class='d-flex mb-2'>
				<span class='badge $badgeClass fs-6 me-3'>$title</span>
				<button type='button' class='btn btn-dark btn-sm viewNoBtn add-row' data-table='{$id}Table' style='margin-right: 5px;'>+</button>
			</div>
			<div class='d-flex row'>
				<table class='table table-bordered' id='{$id}Table'>
					<thead id='thead_$id'>
						<tr>
							<th class='text-center'>모델</th>
							<th class='text-center'>구매수량</th>
							<th class='text-center'>단가</th>
							<th class='text-center'>금액</th>
							<th class='text-center'>비고</th>
							<th class='text-center'>1차 입고일</th>
							<th class='text-center'>1차 입고수량</th>
							<th class='text-center'>2차 입고일</th>
							<th class='text-center'>2차 입고수량</th>
							<th class='text-center'>3차 입고일</th>
							<th class='text-center'>3차 입고수량</th>
							<th class='text-center'>구매수량합</th>
							<th class='text-center'>입고합</th>
							<th class='text-center'>구매입고차이</th>
							<th class='text-center'>상태</th>
							<th class='text-center w50px'>삭제</th>
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

	generateTableSection('motorlist', '电机 (모터)');
	generateTableSection('wirelessClist', '无线分控 (무선 콘트롤러)');
	generateTableSection('wireClist', '有线分控 (유선 콘트롤러)');
	generateTableSection('wirelessLinklist', '无线主控 (무선 제어기)');
	generateTableSection('wireLinklist', '有线主控 (유선 제어기)');
	generateTableSection('bracketlist', '支架板 (브라켓트)');
	?>
</div>
</form>

<script>
var ajaxRequest = null;
var ajaxRequest_write = null;
var motorlistOptions = [] ;
var wirelessClistOptions =  [];
var wireClistOptions = [];
var wirelessLinklistOptions = [];
var wireLinklistOptions = [];
var bracketlistOptions = [];

$(document).ready(function() {		
    // AJAX 요청을 통해 데이터 가져오기
	if (ajaxRequest_write !== null) {
		ajaxRequest_write.abort();
	}		 
	ajaxRequest_write = $.ajax({
        url: 'fetch_item.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            // 전역 변수에 옵션 데이터 설정
            motorlistOptions = data.motorItems || [];
            wirelessClistOptions = data.wirelessControllerItems || [];
            wireClistOptions = data.wireControllerItems || [];
            wirelessLinklistOptions = data.wirelessLinkItems || [];
            wireLinklistOptions = data.wireLinkItems || [];
            bracketlistOptions = data.bracketItems || [];
			
			console.log('motorlistOptions',motorlistOptions);
			console.log('bracketlistOptions',bracketlistOptions);
				
			initializePage();
			bindEventHandlers();			

			$("#showlogBtn").click(function() {
				var num = '<?= $num ?>';
				var workitem = 'material_order';
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

			ajaxRequest_write = null;
        },
        error: function(xhr, status, error) {
            console.log("Error fetching item options:", error);
			ajaxRequest_write = null;
        }
    });    
	
});

function initializePage() {
    var loader = document.getElementById('loadingOverlay');
    loader.style.display = 'none';

    var motorlist = <?= json_encode($motorlist ?? []) ?>;
    var wirelessClist = <?= json_encode($wirelessClist ?? []) ?>;
    var wireClist = <?= json_encode($wireClist ?? []) ?>;
    var wirelessLinklist = <?= json_encode($wirelessLinklist ?? []) ?>;
    var wireLinklist = <?= json_encode($wireLinklist ?? []) ?>;
    var bracketlist = <?= json_encode($bracketlist ?? []) ?>;

    loadTableData('#motorlistTable', motorlist, 'motorlistTable');
    loadTableData('#wirelessClistTable', wirelessClist, 'wirelessClistTable');
    loadTableData('#wireClistTable', wireClist, 'wireClistTable');
    loadTableData('#wirelessLinklistTable', wirelessLinklist, 'wirelessLinklistTable');
    loadTableData('#wireLinklistTable', wireLinklist, 'wireLinklistTable');
    loadTableData('#bracketlistTable', bracketlist, 'bracketlistTable');

    if ('<?= $mode ?>' === 'view') {
        disableInputsForViewMode();
    }
}

function bindEventHandlers() {
    $(document).on('click', '.remove-row', function() {
        $(this).closest('tr').remove();
    });
	
    $(document).on('click', '.add-row', function() {
        var tableId = $(this).data('table');
        var tableBody = $('#' + tableId).find('tbody');
        addRow(tableBody, {}, tableId);
    });
}

function updateRowCalculation(element) {
    var row = $(element).closest('tr');
    
    // 구매수량과 단가 가져오기
    var purchaseQty = parseFloat(row.find('.purchase-qty').val()) || 0;
    var unitPrice = parseFloat(row.find('.unit-price').val()) || 0;
    
    // 각 차수 입고수량 가져오기
    var inQty1 = parseFloat(row.find('.inqty1').val()) || 0;
    var inQty2 = parseFloat(row.find('.inqty2').val()) || 0;
    var inQty3 = parseFloat(row.find('.inqty3').val()) || 0;
    
    // 금액 계산 (구매수량 * 단가) 및 3자리 콤마 추가
    var amount = purchaseQty * unitPrice;
    row.find('.amount').val(amount.toLocaleString());
    
    // 구매수량합 = 구매수량 그대로 표시 (3자리 콤마 적용)
    row.find('.total-purchase').val(purchaseQty.toLocaleString());
    
    // 입고합 = 1차 + 2차 + 3차 입고수량의 합 (3자리 콤마 적용)
    var totalInQty = inQty1 + inQty2 + inQty3;
    row.find('.total-inqty').val(totalInQty.toLocaleString());
    
    // 구매입고차이 = 구매수량합 - 입고합 (3자리 콤마 적용)
    var diff = purchaseQty - totalInQty;
    row.find('.diff').val(diff.toLocaleString());
    
    // 상태: diff가 0이고 구매수량이 0보다 크면 "완료", 아니면 빈 문자열
    var status = (diff === 0 && purchaseQty > 0) ? '완료' : '';
    row.find('.status').val(status);
}

function addRow(tableBody, rowData, typebutton) {
	
	console.log('tableBody',tableBody);
	console.log('rowData',rowData);
	console.log('typebutton',typebutton);
	
    var table = tableBody.closest('table');
    // 테이블에 이미 thead가 있는지 확인 (있다면 추가하지 않음)
    // 테이블에 thead가 존재하면 보이도록, 없으면 생성하여 추가
    var thead = table.find('thead');
    if (thead.length !== 0) {
        // 존재하지만 display:none; 상태라면 보이게 설정
        thead.css('display', 'table-header-group');
    }
  
    // 새 행 생성
    var newRow = $('<tr>');
		
	var optionsList = [];
	switch (typebutton) {
		case 'motorlistTable':
			optionsList = (typeof motorlistOptions !== 'undefined') ? motorlistOptions : [];
			break;
		case 'wirelessClistTable':
			optionsList = (typeof wirelessClistOptions !== 'undefined') ? wirelessClistOptions : [];
			break;
		case 'wireClistTable':
			optionsList = (typeof wireClistOptions !== 'undefined') ? wireClistOptions : [];
			break;
		case 'wirelessLinklistTable':
			optionsList = (typeof wirelessLinklistOptions !== 'undefined') ? wirelessLinklistOptions : [];
			break;
		case 'wireLinklistTable':
			optionsList = (typeof wireLinklistOptions !== 'undefined') ? wireLinklistOptions : [];
			break;
		case 'bracketlistTable':
			optionsList = (typeof bracketlistOptions !== 'undefined') ? bracketlistOptions : [];
			break;
		default:
			optionsList = [];
			break;
	}
		
	var selectHtml = '<select name="col1[]" class="form-select d-block w-auto" style="font-size:0.7rem;height:32px;" required>';
	// 기본 옵션 (빈 값)
	selectHtml += '<option value=""></option>';
	
	console.log('typebutton',typebutton);
	console.log('optionsList',optionsList);

	// 옵션 목록을 생성
	selectHtml += optionsList.map(function(item) {
		return '<option value="' + item + '" ' + (rowData.col1 === item ? 'selected' : '') + '>' + item + '</option>';
	}).join('');

	// 기존 값이 옵션 목록에 없으면 마지막에 추가하고 선택 상태로 설정
	if (rowData.col1 && optionsList.indexOf(rowData.col1) === -1) {
		selectHtml += '<option value="' + rowData.col1 + '" selected>' + rowData.col1 + '</option>';
	}

	selectHtml += '</select>';
	newRow.append('<td class="text-center">' + selectHtml + '</td>');
    
    // 2. 구매수량 (오른쪽 정렬)
    newRow.append('<td class="text-center"><input type="number" name="col2[]" class="form-control text-end purchase-qty" autocomplete="off" value="' + (rowData.col2 || '') + '" required onkeyup="updateRowCalculation(this);"></td>');
    
    // 3. 단가 (오른쪽 정렬)
    newRow.append('<td class="text-center"><input type="number" name="col3[]" class="form-control text-end unit-price" autocomplete="off" value="' + (rowData.col3 || '') + '" required onkeyup="updateRowCalculation(this);"></td>');
    
    // 4. 금액 (자동계산, 오른쪽 정렬) - 3자리마다 콤마 추가
	newRow.append('<td class="text-center"><input type="text" name="col4[]" class="form-control text-end amount" autocomplete="off" value="' + (rowData.col4 ? Number(rowData.col4).toLocaleString() : '') + '" readonly></td>');

    
    // 5. 비고
    newRow.append('<td class="text-center"><input type="text" name="col5[]" class="form-control"  autocomplete="off" value="' + (rowData.col5 || '') + '"></td>');
    
    // 6. 1차 입고일
    newRow.append('<td class="text-center"><input type="date" name="col6[]" class="form-control" value="' + (rowData.col6 || '') + '"></td>');
    
    // 7. 1차 입고수량 (오른쪽 정렬)
    newRow.append('<td class="text-center"><input type="number" name="col7[]" class="form-control text-end inqty1"  autocomplete="off" value="' + (rowData.col7 || '') + '" onkeyup="updateRowCalculation(this);"></td>');
    
    // 8. 2차 입고일
    newRow.append('<td class="text-center"><input type="date" name="col8[]" class="form-control"  autocomplete="off" value="' + (rowData.col8 || '') + '"></td>');
    
    // 9. 2차 입고수량 (오른쪽 정렬)
    newRow.append('<td class="text-center"><input type="number" name="col9[]" class="form-control text-end inqty2"  autocomplete="off" value="' + (rowData.col9 || '') + '" onkeyup="updateRowCalculation(this);"></td>');
    
    // 10. 3차 입고일
    newRow.append('<td class="text-center"><input type="date" name="col10[]" class="form-control"  autocomplete="off" value="' + (rowData.col10 || '') + '"></td>');
    
    // 11. 3차 입고수량 (오른쪽 정렬)
    newRow.append('<td class="text-center"><input type="number" name="col11[]" class="form-control text-end inqty3" autocomplete="off"  value="' + (rowData.col11 || '') + '" onkeyup="updateRowCalculation(this);"></td>');
    
    // 12. 구매수량합 (구매수량 그대로, 오른쪽 정렬)
    newRow.append('<td class="text-center"><input type="text" name="col12[]" class="form-control text-end total-purchase"  autocomplete="off" value="" readonly></td>');
    
    // 13. 입고합 (1차+2차+3차 입고수량의 합, 오른쪽 정렬)
    newRow.append('<td class="text-center"><input type="text" name="col13[]" class="form-control text-end total-inqty"  autocomplete="off" value="" readonly></td>');
    
    // 14. 구매입고차이 (구매수량 - 입고합, 오른쪽 정렬)
    newRow.append('<td class="text-center"><input type="text" name="col14[]" class="form-control text-end diff"  autocomplete="off" value="" readonly></td>');
    
    // 15. 상태 (입고합과 구매수량이 일치하면 "완료")
    newRow.append('<td class="text-center"><input type="text" name="col15[]" class="form-control status"  autocomplete="off" value="" readonly></td>');
    
    // 16. 삭제 버튼
    newRow.append('<td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-row viewNoBtn">-</button></td>');
    
    tableBody.append(newRow);
    
    // 행 추가 후 계산 실행
    updateRowCalculation(newRow.find('input.purchase-qty')[0]);
}
function loadTableData(tableId, dataList, typebutton) {
    var tableBody = $(tableId).find('tbody');
    var theadId;

    switch (tableId) {
        case '#motorlistTable':
            theadId = '#thead_motorlist';
            break;
        case '#wirelessClistTable':
            theadId = '#thead_wirelessClist';
            break;
        case '#wireClistTable':
            theadId = '#thead_wireClist';
            break;
        case '#wirelessLinklistTable':
            theadId = '#thead_wirelessLinklist';
            break;
        case '#wireLinklistTable':
            theadId = '#thead_wireLinklist';
            break;
        case '#bracketlistTable':
            theadId = '#thead_bracketlist';
            break;
        default:
            theadId = null;
    }

    if (typeof dataList === 'string') {
        try {
            dataList = JSON.parse(dataList);
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
        dataList = [];
    }

    if (dataList.length === 0) {
        console.log('no record');
    } else {
        dataList.forEach(function(item) {
            addRow(tableBody, item, typebutton);
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
    $('#motorlistTable tbody tr').each(function() {
        let rowData = {};
        $(this).find('input, select').each(function() {
            let name = $(this).attr('name').replace('[]', '');
            let value = $(this).val();
            rowData[name] = value;
        });
        formData.push(rowData);
    });
    let jsonString = JSON.stringify(formData);
    $('#motorlist').val(jsonString);

    formData = [];
    $('#wirelessClistTable tbody tr').each(function() {
        let rowData = {};
        $(this).find('input, select').each(function() {
            let name = $(this).attr('name').replace('[]', '');
            let value = $(this).val();
            rowData[name] = value;
        });
        formData.push(rowData);
    });
    jsonString = JSON.stringify(formData);
    $('#wirelessClist').val(jsonString);

    formData = [];
    $('#wireClistTable tbody tr').each(function() {
        let rowData = {};
        $(this).find('input, select').each(function() {
            let name = $(this).attr('name').replace('[]', '');
            let value = $(this).val();
            rowData[name] = value;
        });
        formData.push(rowData);
    });
    jsonString = JSON.stringify(formData);
    $('#wireClist').val(jsonString);

    formData = [];
    $('#wirelessLinklistTable tbody tr').each(function() {
        let rowData = {};
        $(this).find('input, select').each(function() {
            let name = $(this).attr('name').replace('[]', '');
            let value = $(this).val();
            rowData[name] = value;
        });
        formData.push(rowData);
    });
    jsonString = JSON.stringify(formData);
    $('#wirelessLinklist').val(jsonString);

    formData = [];
    $('#wireLinklistTable tbody tr').each(function() {
        let rowData = {};
        $(this).find('input, select').each(function() {
            let name = $(this).attr('name').replace('[]', '');
            let value = $(this).val();
            rowData[name] = value;
        });
        formData.push(rowData);
    });
    jsonString = JSON.stringify(formData);
    $('#wireLinklist').val(jsonString);

    formData = [];
    $('#bracketlistTable tbody tr').each(function() {
        let rowData = {};
        $(this).find('input, select').each(function() {
            let name = $(this).attr('name').replace('[]', '');
            let value = $(this).val();
            rowData[name] = value;
        });
        formData.push(rowData);
    });
    jsonString = JSON.stringify(formData);
    $('#bracketlist').val(jsonString);

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
</script>

<script>
function generateExcel() {
    // 엑셀로 저장할 테이블 ID 목록
    var tableIds = [
        'motorlistTable', 
        'wirelessClistTable', 
        'wireClistTable', 
        'wirelessLinklistTable', 
        'wireLinklistTable', 
        'bracketlistTable'
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
            
            // 첫 번째 셀: select 요소가 있다면 선택된 옵션의 텍스트만 추출
            if (cells[0]) {
                var selectElem = cells[0].querySelector('select');
                if (selectElem) {
                    rowData['model'] = selectElem.options[selectElem.selectedIndex].text.trim();
                } else {
                    rowData['model'] = cells[0].innerText.trim();
                }
            } else {
                rowData['model'] = '';
            }
					
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
		rowData['purchaseQty']   = cells[1] ? getCellValue(cells[1]) : '';
		rowData['unitPrice']     = cells[2] ? getCellValue(cells[2]) : '';
		rowData['amount']        = cells[3] ? getCellValue(cells[3]) : '';
		rowData['note']          = cells[4] ? getCellValue(cells[4]) : '';
		rowData['inDate1']       = cells[5] ? getCellValue(cells[5]) : '';
		rowData['inQty1']        = cells[6] ? getCellValue(cells[6]) : '';
		rowData['inDate2']       = cells[7] ? getCellValue(cells[7]) : '';
		rowData['inQty2']        = cells[8] ? getCellValue(cells[8]) : '';
		rowData['inDate3']       = cells[9] ? getCellValue(cells[9]) : '';
		rowData['inQty3']        = cells[10] ? getCellValue(cells[10]) : '';
		rowData['totalPurchase'] = cells[11] ? getCellValue(cells[11]) : '';
		rowData['totalInQty']    = cells[12] ? getCellValue(cells[12]) : '';
		rowData['diff']          = cells[13] ? getCellValue(cells[13]) : '';
		rowData['status']        = cells[14] ? getCellValue(cells[14]) : '';

            
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
			'motorlistTable', 
			'wirelessClistTable', 
			'wireClistTable', 
			'wirelessLinklistTable', 
			'wireLinklistTable', 
			'bracketlistTable'
		];
		
		tableIds.forEach(function(tableId) {
			var tableBody = $('#' + tableId).find('tbody');
			
			// 각 테이블에 해당하는 옵션 배열 가져오기
			var optionsList = [];
			switch(tableId) {
				case 'motorlistTable':
					optionsList = motorlistOptions;
					break;
				case 'wirelessClistTable':
					optionsList = wirelessClistOptions;
					break;
				case 'wireClistTable':
					optionsList = wireClistOptions;
					break;
				case 'wirelessLinklistTable':
					optionsList = wirelessLinklistOptions;
					break;
				case 'wireLinklistTable':
					optionsList = wireLinklistOptions;
					break;
				case 'bracketlistTable':
					optionsList = bracketlistOptions;
					break;
				default:
					optionsList = [];
					break;
			}
			
			// 각 옵션마다 행 추가 (rowData.col1 에 해당 옵션을 설정)
			 optionsList.forEach(function(option) {
            var exists = false;
            tableBody.find('tr').each(function(){
                // select[name="col1[]"]의 값이 이미 option과 일치하면 exists 플래그 설정
                var currentOption = $(this).find('select[name="col1[]"]').val();
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
			'motorlistTable', 
			'wirelessClistTable', 
			'wireClistTable', 
			'wirelessLinklistTable', 
			'wireLinklistTable', 
			'bracketlistTable'
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
		// 선택된 차수 ("1차", "2차", "3차")
		var selectedPhase = $('#selectDate').val();
		// 새 날짜 값
		var newDate = $('#todayDate').val();
		
		// 각 차수에 해당하는 열 인덱스 (0-based 인덱스)
		// 테이블 구조 예시:
		// 0: 모델, 1: 구매수량, 2: 단가, 3: 금액, 4: 비고,
		// 5: 1차 입고일, 6: 1차 입고수량,
		// 7: 2차 입고일, 8: 2차 입고수량,
		// 9: 3차 입고일, 10: 3차 입고수량,
		// 11: 구매수량합, 12: 입고합, 13: 구매입고차이, 14: 상태, 15: 삭제
		var colIndex;
		switch(selectedPhase) {
			case '1차':
				colIndex = 5;
				break;
			case '2차':
				colIndex = 7;
				break;
			case '3차':
				colIndex = 9;
				break;
			default:
				colIndex = 5;
		}
		
		// 대상 테이블 ID 목록 (모든 품목 테이블)
		var tableIds = [
			'motorlistTable', 
			'wirelessClistTable', 
			'wireClistTable', 
			'wirelessLinklistTable', 
			'wireLinklistTable', 
			'bracketlistTable'
		];
		
		// 각 테이블의 tbody를 순회하여, 해당 열의 날짜 input 값을 새 날짜로 업데이트
		tableIds.forEach(function(tableId) {
			$('#' + tableId + ' tbody tr').each(function(){
				var cells = $(this).find('td');
				if(cells.length > colIndex) {
					// 해당 셀 내의 date input 값을 업데이트
					$(cells[colIndex]).find('input[type="date"]').val(newDate);
				}
			});
		});
	});
	
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });	
});
</script>

</body>
</html>