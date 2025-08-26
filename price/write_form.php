<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  
if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php");
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';

$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  
$num = isset($_REQUEST["num"]) ? $_REQUEST["num"] : "";
$tablename = isset($_REQUEST["tablename"]) ? $_REQUEST["tablename"] : "";

if ($mode === 'copy') {
    $title_message = "(데이터복사) 중국발주 원단가";
} else {
    $title_message = "중국발주 원단가";
}

?>
<title> <?=$title_message?> </title>

<style>
textarea {
    overflow: hidden;
    resize: none; /* 사용자 크기 조절을 방지 */
}  
input[type="checkbox"],
input[type="radio"] {
    transform: scale(1.5); /* 크기 확대 */
    margin: 3px; /* 여백 추가 */
}

.readonly-checkbox,
.readonly-radio {
    pointer-events: none; /* 사용자 상호작용 비활성화 */
    opacity: 1; /* 불투명도 설정 */
    color: red;
}

label {
    font-size: 1.5em; /* 글꼴 크기 확대 */
    display: inline-block;
    margin: 3px 0;
}
.readonly-select {
    pointer-events: none;
}
</style>

</head>

<?php   
include $_SERVER['DOCUMENT_ROOT'] . '/mymodal.php';
  
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();	
 
// 중국발주업체 목록 로드 (phonebook_buy)
$chinaVendors = array();
try {
    $sqlVendor = "SELECT vendor_name FROM $DB.phonebook_buy WHERE IFNULL(is_deleted,0)=0 AND is_china_vendor='1' ORDER BY vendor_name";
    $stVendor = $pdo->query($sqlVendor);
    $chinaVendors = $stVendor->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $Exception) {
    $chinaVendors = array();
}
 
// 중국구매처별 카테고리 맵 로드 (vendor_name => [category1, category2, ...])
$vendorCategoryMap = array();
try {
    $sqlVendorCat = "SELECT vendor_name, category FROM $DB.phonebook_buy WHERE IFNULL(is_deleted,0)=0 AND is_china_vendor='1'";
    $stVendorCat = $pdo->query($sqlVendorCat);
    $rowsVendorCat = $stVendorCat->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rowsVendorCat as $vcRow) {
        $vName = isset($vcRow['vendor_name']) ? (string)$vcRow['vendor_name'] : '';
        $catStr = isset($vcRow['category']) ? (string)$vcRow['category'] : '';
        $cats = array();
        if ($catStr !== '') {
            $parts = explode(',', $catStr);
            foreach ($parts as $p) {
                $p = trim($p);
                if ($p !== '') { $cats[] = $p; }
            }
        }
        $vendorCategoryMap[$vName] = array_values(array_unique($cats));
    }
} catch (PDOException $Exception) {
    $vendorCategoryMap = array();
}
 
$today = date("Y-m-d"); // 현재일자 변수지정
$registedate = date("Y-m-d"); // 현재일자 변수지정

include '_request.php';	 
	
if ($mode == "modify" || !empty($num)) {
    try {
        $sql = "select * from $DB.$tablename where num = ?";
        $stmh = $pdo->prepare($sql); 

        $stmh->bindValue(1, $num, PDO::PARAM_STR); 
        $stmh->execute();
        $count = $stmh->rowCount();            
        $row = $stmh->fetch(PDO::FETCH_ASSOC);  // $row 배열로 DB 정보를 불러온다.
        if ($count < 1) {  
            print "검색결과가 없습니다.<br>";
        } else {
            include '_row.php';	  
        }
    } catch (PDOException $Exception) {
        print "오류: ".$Exception->getMessage();
    }
}


if ($mode == "copy" || $mode == 'split') {
    try {
        $sql = "select * from " . $DB . ".{$tablename}  where num = ? ";
        $stmh = $pdo->prepare($sql); 
        $stmh->bindValue(1, $num, PDO::PARAM_STR); 
        $stmh->execute();
        $count = $stmh->rowCount();              
        if($count<1){  
            print "검색결과가 없습니다.<br>";
        } else {
            $row = $stmh->fetch(PDO::FETCH_ASSOC);
        }
        include '_row.php';
    } catch (PDOException $Exception) {
        print "오류: ".$Exception->getMessage();
    }
    // 자료번호 초기화 
    $num = 0;		 
	$registedate=date("Y-m-d");
	$mode = 'insert';
}  
    
if(empty($mode))
     $mode='insert';	

?>
  
<form id="board_form"  name="board_form" method="post" enctype="multipart/form-data">

<input type="hidden" id="mode" name="mode" value="<?= isset($mode) ? $mode : '' ?>">
<input type="hidden" id="num" name="num" value="<?= isset($num) ? $num : '' ?>">
<input type="hidden" id="level" name="level" value="<?= isset($level) ? $level : '' ?>">
<input type="hidden" id="user_name" name="user_name" value="<?= isset($user_name) ? $user_name : '' ?>">
<input type="hidden" id="update_log" name="update_log" value="<?= isset($update_log) ? $update_log : NULL ?>">
<input type="hidden" id="tablename" name="tablename" value="<?= isset($tablename) ? $tablename : '' ?>">
<input type="hidden" id="is_deleted" name="is_deleted" value="<?= isset($is_deleted) ? $is_deleted : '0' ?>">
<input type="hidden" id="itemList" name="itemList">

<div class="container"> 

<div class="row justify-content-center align-items-center ">	        
    <div class="card align-middle" style="width: 55rem;">	
        <div class="card-body text-center">							
            <div class="row d-flex justify-content-center align-items-center mb-3">					
                <div class="col-sm-1">			
                    <div class="d-flex p-1 mb-1 justify-content-start align-items-center">				
                       <?=$mode?>								
                   </div>
                </div>			
                <div class="col-sm-9">					
                    <div class="d-flex p-1 mb-1 justify-content-center align-items-center">	
                    <h5> <?=$title_message?> </h5> &nbsp; &nbsp; &nbsp; &nbsp; 	
                    <?php if ($mode == 'view') { ?>		
                        <button type="button" class="btn btn-dark btn-sm me-1" onclick="location.href='write_form.php?mode=modify&num=<?=$num?>&tablename=<?=$tablename?>';"> 수정 </button>
                        <button id="copyBtn" class="btn btn-primary btn-sm me-1" type="button"> 복사 </button>					
                        <button id="deleteBtn" class="btn btn-danger btn-sm me-1" type="button"> 삭제 </button>					
                    <?php } ?>		
                    <?php if ($mode !== 'view') { ?>
                        <button id="saveBtn" class="btn btn-dark btn-sm me-1" type="button">
                        <?php if ((int)$num > 0) print ' 저장';  else print ' 저장'; ?></button>		   			
                    <?php } ?>		
                    <button type="button" class="btn btn-outline-dark btn-sm me-2" id="showlogBtn"> H </button>					
                    </div>
                </div>			
                <div class="col-sm-2">					
                    <button type="button" class="btn btn-outline-dark btn-sm" onclick="self.close();"> &times; 닫기 </button>	&nbsp; 				
                </div>	
               </div>	
            </div>
        </div>		
    </div>		
</div>	
<div class="container-fluid mt-4">
      <div class="row ">    
        <div class="col-lg-2 col-sm-2">	  
		</div>
	<div class="col-lg-8 col-sm-8">	  		
		  <div class=" d-flex justify-content-center align-items-center"> 
				<table class="table table-bordered">
					<tbody>						
						<tr>
							<td class="text-center" style="width:10%;"> 등록일 </td>
							<td class="text-center"   style="width:10%;"  > 
								<input type="date" id="registedate" name="registedate" class="form-control"value="<?= isset($registedate) ? $registedate : '' ?>">
							</td>     
							<td class="text-center"   style="width:10%;"  >  메모 </td>
							<td > 
								<textarea id="memo" name="memo" class="form-control" rows="2"><?=$memo?></textarea>
							</td>          							
						</tr>								
					</tbody>
				</table>                    
			  </div>				                				
		  </div>				                				
			<div class="col-lg-2 col-sm-2">	  
		</div>		  
	  </div>	
	<div class='d-flex mt-2 mb-2 m-1'>
		<span class='badge bg-dark fs-6 me-3'>품목(item)</span>
		<button type='button' class='btn btn-primary btn-sm viewNoBtn add-row' data-table='myTable' style='margin-right: 5px;'>+</button>
		<!-- <button type='button' class='btn btn-danger btn-sm viewNoBtn remove-row' data-table='myTable' style='margin-right: 5px;'>-</button> -->
	</div>		  
    <div class="row justify-content-center">
        <div class="col-lg-12 col-sm-12">
            <div class="card">
                <div class="card-body">
                    <!-- 중국구매처 셀렉트 템플릿 (숨김) -->
                    <div id="vendorSelectTemplate" style="display:none;">
                        <select name="col1[]" class="form-select w-auto text-center col1" style="height: 32px; font-size: 0.7rem;">
                            <option value="">선택</option>
                            <?php foreach ($chinaVendors as $vn) { ?>
                                <option value="<?= htmlspecialchars($vn, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($vn, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <table id="myTable" class="table table-bordered table-hover">
					 <thead class="table-primary text-center">
							<tr>
								<th class="align-middle">기능</th>
								<th class="align-middle">NO.</th>
								<th class="align-middle">중국구매처</th>
								<th class="align-middle">카테고리</th>
								<th class="align-middle">품목코드</th>
								<th class="align-middle">품목명</th>
								<th class="align-middle">규격</th>
								<th class="align-middle">단위</th>
								<th class="align-middle">단가(위엔)</th>
								<th class="align-middle">비고</th>
							</tr>
						</thead>
                        <tbody>     
                            <!-- Additional Rows Go Here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

</form>
<script>
// 동적 행(add/copy/remove) 로직은 price/README.md(@README.md)를 참조합니다.
// 상세 스펙과 예시는 README의 addRow/removeRow/copyRow 절을 확인하세요.
 
var ajaxRequest = null;
var ajaxRequest_write = null;
// PHP에서 주입된 중국구매처별 카테고리 매핑
var vendorCategoryMap = <?php echo json_encode(isset($vendorCategoryMap) ? $vendorCategoryMap : [], JSON_UNESCAPED_UNICODE); ?>;
// 페이지 로딩
$(document).ready(function(){    
    var loader = document.getElementById('loadingOverlay');
    loader.style.display = 'none';

    var mode = '<?php echo $mode; ?>';

    $("#copyBtn").click(function() {
        location.href = 'write_form.php?mode=copy&num=' + $("#num").val() + "&tablename=" + $("#tablename").val();
    });

    $("#saveBtn").click(function() {
        saveData();
    });

    $("#deleteBtn").click(function() {
        deleteData();
    });

    // 초기 페이지 설정 및 로드
    initializePage();

    // 행 추가	
    $(document).on('click', '.add-row', function() {  // 전체 페이지에서 동작
        if (mode !== 'view') {
            var tableBody = $('#myTable tbody');
            addRow(tableBody);
        }
    });

    // 행 삭제
    $('#myTable').on('click', '.remove-row', function() {
        if (mode !== 'view') {
            var row = $(this).closest('tr');
            removeRow(row);
        }
    });

    // 행 복사
    $('#myTable').on('click', '.copy-row', function() {
        if (mode !== 'view') {
            var currentRow = $(this).closest('tr');
            var clonedRow = currentRow.clone(false);

            // select 값 보존
            currentRow.find('select').each(function(index) {
                var val = $(this).val();
                clonedRow.find('select').eq(index).val(val);
            });

            // 복사된 행의 일련번호는 초기화 후 삽입
            clonedRow.find('.row-index').text('');
            currentRow.after(clonedRow);

            // 숫자 포맷 입력 핸들러 바인딩 및 초기 계산
            clonedRow.find('.number-format').on('input', function () {
                calculateRow(clonedRow);
            });
            calculateRow(clonedRow);

            // 벤더에 맞춰 카테고리 옵션 재생성 및 선택값 유지
            var vendorValCloned = clonedRow.find('select.col1').val() || '';
            var selectedCatCloned = clonedRow.find('select.col2').val() || '';
            updateCategoryOptions(clonedRow, vendorValCloned, selectedCatCloned);

            // 일련번호 재계산
            updateRowIndices();
        }
    });

    // 벤더 변경 시 해당 행 카테고리 옵션 갱신
    $('#myTable').on('change', 'select.col1', function() {
        var row = $(this).closest('tr');
        var vendorVal = $(this).val() || '';
        var currentSelected = row.find('select.col2').val() || '';
        updateCategoryOptions(row, vendorVal, currentSelected);
    });
	
	// view 모드일 때, 버튼 및 입력 요소 비활성화 뒷쪽에 배치해야 동작한다. 주의요함
    if (mode === 'view') {
        $('input, textarea').prop('readonly', true); // Disable all input, textarea, and select elements
        $('input[type=hidden]').prop('readonly', false);
        $('select').prop('disabled', true).addClass('readonly-select');
        $('button.add-row, button.remove-row, button.copy-row').prop('disabled', true); // 버튼 비활성화
    }
});

function initializePage() {
    // PHP에서 넘어온 JSON 데이터를 JavaScript 객체로 변환
    var itemList = <?php echo isset($itemList) ? json_encode($itemList) : '[]'; ?>;

    // itemList가 올바른 배열인지 확인
    if (!Array.isArray(itemList)) {
        itemList = [];
    }

    var tableBody = $('#myTable tbody');  // 테이블의 tbody 선택

    // insert 모드에서 데이터가 없으면 첫 행 자동 추가
    var pageMode = '<?= $mode ?>';
    if (pageMode === 'insert' && itemList.length === 0) {
        addRow(tableBody, {});
        return;
    }

    // JSON 데이터를 순회하며 각 행을 추가하고, 값을 채워 넣음
    itemList.forEach(function(rowData) {
        addRow(tableBody, rowData);
    });
}

function inputNumberFormat(obj) {
    // 기존 값 유지
    let value = obj.value;

    // 숫자, 소수점 및 - 이외의 문자는 제거
    value = value.replace(/[^0-9.-]/g, '');

    // 콤마를 제거하고 숫자 포맷팅
    let rawValue = value.replace(/,/g, '');

    // 숫자인지 확인 (소수점 포함)
    if (!isNaN(rawValue) || rawValue === '.') {
        // 소수점이 마지막에 입력된 경우 처리
        if (rawValue.endsWith('.')) {
            obj.value = rawValue; // 포맷팅하지 않고 그대로 유지
            return;
        }

        // 음수일 때의 처리
        if (rawValue.startsWith('-')) {
            rawValue = '-' + formatNumberWithDecimals(rawValue.slice(1));
        } else {
            rawValue = formatNumberWithDecimals(rawValue);
        }

        obj.value = rawValue;
    }
}

// 숫자를 3자리마다 콤마와 소수점 포함 포맷팅
function formatNumberWithDecimals(value) {
    let parts = value.split('.');
    let integerPart = parts[0]; // 정수 부분
    let decimalPart = parts[1] || ''; // 소수 부분

    // 정수 부분에만 3자리마다 콤마 추가
    integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

    // 소수 부분이 있으면 결합
    return decimalPart ? integerPart + '.' + decimalPart : integerPart;
}

function addRow(tableBody, rowData = {}) {
    var newRow = $('<tr>');

    // + / - 버튼 추가 (첫 번째 열)
    newRow.append('<td class="text-center" style="width:80px;">' +
        '<div class="btn-group btn-group-sm" role="group">' + 
        '<button type="button" class="btn btn-primary viewNoBtn add-row" data-table="' + tableBody.closest('table').attr('id') + '">+</button>' +
        '<button type="button" class="btn btn-danger viewNoBtn remove-row">-</button>' +
        '<button type="button" class="btn btn-success viewNoBtn copy-row"><i class="bi bi-copy"></i></button>' +
        '</div></td>');

    // 일련번호 추가 (두 번째 열)
    var rowIndex = tableBody.find('tr').length + 1;
    newRow.append('<td class="text-center align-middle row-index">' + rowIndex + '</td>');

    // 3) 중국구매처 (select, col1)
    var vendorSelect = $('#vendorSelectTemplate').html();
    newRow.append('<td>' + vendorSelect + '</td>');

    // 4) 카테고리 (col2) - select 동적 옵션
    var col2 = rowData['col2'] || '';
    var categorySelect = '' +
        '<select name="col2[]" class="form-select w-auto text-center col2" style="height: 32px; font-size: 0.7rem;">' +
        '  <option value="">선택</option>' +
        '</select>';
    newRow.append('<td>' + categorySelect + '</td>');

    // 5) 품목코드 (col3)
    var col3 = rowData['col3'] || '';
    newRow.append('<td><input type="text" name="col3[]" value="' + col3 + '" class="form-control text-center col3" autocomplete="off"></td>');

    // 6) 품목명 (col4)
    var col4 = rowData['col4'] || '';
    newRow.append('<td><input type="text" name="col4[]" value="' + col4 + '" class="form-control text-center col4" autocomplete="off"></td>');

    // 7) 규격 (col5)
    var col5 = rowData['col5'] || '';
    newRow.append('<td><input type="text" name="col5[]" value="' + col5 + '" class="form-control text-center col5" autocomplete="off"></td>');

    // 8) 단위 (col6)
    var col6 = rowData['col6'] || '';
    var unitSelect = '' +
        '<select name="col6[]" class="form-select w-auto text-center col6" style="height: 32px; font-size: 0.7rem;">' +
        '  <option value="">선택</option>' +
        '  <option value="EA">EA</option>' +
        '  <option value="롤">롤</option>' +
        '  <option value="BOX">BOX</option>' +
        '</select>';
    newRow.append('<td>' + unitSelect + '</td>');

    // 9) 단가(위엔) (col7)
    var col7 = rowData['col7'] || '';
    if (!isNaN(col7) && col7 !== '') {
        col7 = parseFloat(col7).toLocaleString('en', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }
    newRow.append('<td><input type="text" name="col7[]" value="' + col7 + '" class="form-control text-center number-format col7" autocomplete="off" onkeyup="inputNumberFormat(this);"></td>');

    // 10) 비고 (col8)
    var col8 = rowData['col8'] || '';
    newRow.append('<td><input type="text" name="col8[]" value="' + col8 + '" class="form-control text-center col8" autocomplete="off"></td>');

    // 새 행을 테이블에 추가
    tableBody.append(newRow);

    // vendor 값 채우기 및 이에 따른 카테고리 옵션 반영
    if (rowData['col1']) {
        newRow.find('select.col1').val(rowData['col1']);
    }
    var vendorVal = newRow.find('select.col1').val() || '';
    updateCategoryOptions(newRow, vendorVal, col2);

    // 단위 값 채우기
    if (col6 !== '') {
        newRow.find('select.col6').val(col6);
    }

    // 숫자 필드에 3자리마다 콤마 유지/재계산(단순 포맷)
    newRow.find('.number-format').on('input', function () {
        calculateRow(newRow);
    });

    // 초기 포맷 적용
    calculateRow(newRow);
}

function calculateRow(row) {
    var el = row.find('.col7');
    if (el.length) {
        let raw = (el.val() || '').toString().replace(/[^0-9.-]/g, '');
        if (raw !== '' && !isNaN(raw)) {
            el.val(parseFloat(raw).toLocaleString('en', { minimumFractionDigits: 0, maximumFractionDigits: 2 }));
        }
    }
}


function removeRow(row) {
    row.remove(); // 행 삭제
    updateRowIndices(); // 일련번호 업데이트
}

function updateRowIndices() {
    $('#myTable tbody tr').each(function(index) {
        $(this).find('.row-index').text(index + 1); // 일련번호 업데이트
    });
}

// 벤더에 따른 카테고리 옵션을 해당 행의 select.col2에 채우기
function updateCategoryOptions(row, vendorName, selectedCategory) {
    var selectEl = row.find('select.col2');
    if (!selectEl.length) return;

    var optionsHtml = '<option value="">선택</option>';
    var categories = [];

    if (vendorName && vendorCategoryMap && vendorCategoryMap[vendorName]) {
        categories = vendorCategoryMap[vendorName];
    }

    // 옵션 생성
    if (Array.isArray(categories) && categories.length > 0) {
        categories.forEach(function(cat) {
            var safeVal = String(cat);
            optionsHtml += '<option value="' + safeVal.replace(/"/g, '&quot;') + '">' + safeVal.replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</option>';
        });
    }

    // 기존 선택값이 목록에 없으면 보존용 옵션 추가
    if (selectedCategory && categories.indexOf(selectedCategory) === -1) {
        var scSafe = String(selectedCategory);
        optionsHtml += '<option value="' + scSafe.replace(/"/g, '&quot;') + '">' + scSafe.replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</option>';
    }

    selectEl.html(optionsHtml);

    // 선택값 설정
    if (selectedCategory) {
        selectEl.val(selectedCategory);
    } else {
        // 없으면 첫 옵션 유지
        selectEl.val('');
    }
}

function saveData() {
	
    const formData = [];
    $('#myTable tbody tr').each(function() {
        let rowData = {};
        $(this).find('input, select').each(function() {
            let name = $(this).attr('name').replace('[]', '');
            let value = $(this).val();
            rowData[name] = value;
        });
        formData.push(rowData);
    });
    
    // JSON으로 인코딩된 데이터를 서버로 전송
    $('#itemList').val(JSON.stringify(formData));

    const form = $('#board_form')[0];
    const datasource = new FormData(form);

    if (ajaxRequest_write !== null) {
        ajaxRequest_write.abort();
    }

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
                Toastify({
                        text: "저장완료",
                        duration: 3000,
                        close:true,
                        gravity:"top",
                        position: "center",
                        style: {
                            background: "linear-gradient(to right, #00b09b, #96c93d)"
                        },
                    }).showToast();                

                    }, 1000);        
               
                    setTimeout(function() {
                        if (window.opener && !window.opener.closed) {                    
                            if (typeof window.opener.restorePageNumber === 'function') {
                                window.opener.restorePageNumber(); // 함수가 있으면 실행
                            }                    
                        }     
                        setTimeout(function() {
                           if (data && data.num) 
                            // 저장된 데이터 번호를 사용하여 새로운 페이지로 이동                        
                            window.location.href = 'write_form.php?mode=view&tablename=' + $('#tablename').val() + '&num=' + data.num;    
                        }, 1000);    
                   
                }, 1500);    

                hideOverlay();                               
           
    },
    error: function(jqxhr, status, error) {
        console.log(jqxhr, status, error);
        alert("An error occurred: " + error); // Display error message
    }                        
 });
}

function deleteData() {    
    var level = '<?php echo $level; ?>';

    if (level !== '1' && level !== '2') {
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
								window.opener.location.reload(); // 부모창 리로드
							}

							setTimeout(function() {
								self.close(); // 현재 창 닫기
							}, 1000);

						}, 1500);
  

						hideOverlay();                     
                    },
                    error: function(jqxhr, status, error) {
                        console.log(jqxhr, status, error);
                    }
                });
            }
        });
    }
}

$(document).ready(function () {
    $('#adaptCurrency').on('click', function () {
        // 환율 입력값 가져오기
        let currencyRate = parseFloat($('#currencyrate').val());

        // 입력값이 숫자인지 확인
        if (isNaN(currencyRate)) {
            Swal.fire({
                icon: 'error',
                title: '입력 오류',
                text: '유효한 환율 값을 입력하세요.'
            });
            return;
        }

		// 모든 col8 클래스의 input 값 설정
		$('.col8').each(function () {
			let originalValue = parseFloat($(this).val());

			// let updatedValue = (currencyRate).toFixed(2); // 소수점 2자리로 고정
			let updatedValue = currencyRate;
			$(this).val(updatedValue);

			// 현재 행 요소를 calculateRow에 전달
			const row = $(this).closest('tr'); // 해당 col8이 속한 <tr>을 가져옴
			calculateRow(row);
		});


        Swal.fire({
            icon: 'success',
            title: '적용 완료',
            text: '환율이 적용되었습니다.'
        });
    });
});


</script>

</body>
</html>
