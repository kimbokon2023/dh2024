<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php"); 
    exit;
}   
include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';
$title_message = '차량관리'; 
?> 
<title> <?=$title_message?> </title>
<?php if($chkMobile==true) { ?>
<style>
  /* 모바일 화면에서 폰트 크기를 20px로 설정 */
  @media (max-width: 1000px) {
    body {
      font-size: 25px;
    }

    .form-control, .fw-bold, .table td, .table th {
      font-size: 25px; /* 테이블, 입력 필드 등의 폰트 크기 조정 */
    }

    button {
      font-size: 30px; /* 버튼의 폰트 크기 조정 */
    }

    .modal-body, .modal-title {
      font-size: 30px; /* 모달 창 내부 폰트 크기 조정 */
    }
	
  }
</style>
<?php } ?>
</head>
<body>		 
<?php
$option = isset($_REQUEST['option']) ? $_REQUEST['option'] : '';  
$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';  
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  
$header = isset($_REQUEST['header']) ? $_REQUEST['header'] : '';  


$num = isset($_REQUEST["num"]) ? $_REQUEST["num"] : '';
isset($_REQUEST["item"])  ? $item=$_REQUEST["item"] :   $item=''; 
isset($_REQUEST["tablename"])  ? $tablename=$_REQUEST["tablename"] :  $tablename=''; 
    	  
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();
    
if($num > 0) {	
    try {
        $sql = "SELECT * FROM ". $DB . "." . $tablename . " WHERE num = ?";
        $stmh = $pdo->prepare($sql);  
        $stmh->bindValue(1, $num, PDO::PARAM_INT);      
        $stmh->execute();            
        $row = $stmh->fetch(PDO::FETCH_ASSOC); 	 
        include '_row.php';
    } catch (PDOException $Exception) {
        print "오류: ".$Exception->getMessage();
    }           
		$mode = 'modify';
 } else {
    include '_request.php';
    $mode = 'insert';
	// 현재 날짜를 'Y-m-d' 형식으로 기록	
}

?>

<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/common/modal.php'); ?>   

<form id="board_form" name="board_form" method="post" enctype="multipart/form-data">			 
	<!-- 숨김 필드 (화면에 보이지 않음) -->
	<input type="hidden" id="num" name="num" value="<?= isset($row['num']) ? $row['num'] : '' ?>">
	<input type="hidden" id="mode" name="mode" value="<?= isset($mode) ? $mode : '' ?>">
	<input type="hidden" id="is_deleted" name="is_deleted" value="<?= isset($row['is_deleted']) ? $row['is_deleted'] : '' ?>">
	<input type="hidden" id="searchtag" name="searchtag" value="<?= isset($row['searchtag']) ? $row['searchtag'] : '' ?>">
	<input type="hidden" id="update_log" name="update_log" value="<?= isset($row['update_log']) ? $row['update_log'] : '' ?>">
	<input type="hidden" id="tablename" name="tablename" value="<?= isset($tablename) ? $tablename : '' ?>">		

<div class="container-fluid">    
	<div class="card justify-content-center mt-2 mb-5">
		<div class="card-header text-center">
			<span class="text-center fs-5"><?=$title_message?></span>
		</div>
<div class="card-body">
<table class="table table-bordered">
    <tbody>
        <tr>
            <td><label for="vehicle_number">차량번호</label></td>
            <td colspan="7" >
				<div class="d-flex justify-content-start align-items-center">
                <input type="text" id="vehicle_number" name="vehicle_number" class="form-control w110px me-2" value="<?= isset($row['vehicle_number']) ? $row['vehicle_number'] : '' ?>">            
				<span class="ms-4 me-1" > 차종 </span>
                <input type="text" id="vehicle_type" name="vehicle_type" class="form-control w150px mx-1" value="<?= isset($row['vehicle_type']) ? $row['vehicle_type'] : '' ?>">				
				<span class="ms-4 me-1" > 구매유형 </span>
				<select id="purchase_type" name="purchase_type" class="form-select w-auto mx-1"  style="font-size: 0.8rem; height: 32px;">
					<option value="lease" <?= (isset($row['purchase_type']) && $row['purchase_type'] == 'lease') ? 'selected' : '' ?>>리스</option>
					<option value="rent" <?= (isset($row['purchase_type']) && $row['purchase_type'] == 'rent') ? 'selected' : '' ?>>렌트</option>
					<option value="company_own" <?= (isset($row['purchase_type']) && $row['purchase_type'] == 'company_own') ? 'selected' : '' ?>>회사 소유</option>
				</select>
				</div>
			</td>
        </tr>
        <tr>
            <td><label for="responsible_person">담당자(정)</label></td>
            <td colspan="1" >
                <input type="text" id="responsible_person" name="responsible_person" class="form-control w100px" value="<?= isset($row['responsible_person']) ? $row['responsible_person'] : '' ?>">
            </td>
            <td> <label for="assistant">담당자(부)</label></td>            
			<td colspan="1" > 
                <input type="text" id="assistant" name="assistant" class="form-control w100px" value="<?= isset($row['assistant']) ? $row['assistant'] : '' ?>">
            </td>         
            <td><label for="total_distance_km">총 주행거리 (km)</label></td>
            <td>
                <input type="text" id="total_distance_km" name="total_distance_km" class="form-control w60px" value="<?= isset($row['total_distance_km']) ? $row['total_distance_km'] : '' ?>">
            </td>
            <td><label for="KMrecordDate">기록일 </label></td>
            <td>
                <input type="date" id="KMrecordDate" name="KMrecordDate" class="form-control" value="<?= isset($row['KMrecordDate']) ? $row['KMrecordDate'] : '' ?>">
            </td>			
            </tr>
        <tr>
            <td><label for="insurance">보험회사,<br> 증서번호 </label></td>
            <td class="w230px">
                <input type="text" id="insurance" name="insurance" class="form-control" value="<?= isset($row['insurance']) ? $row['insurance'] : '' ?>">
            </td>
            <td class="w100px" > <label for="insurance_contact">보험사 <br> 연락처 </label></td>
            <td class="w150px" > 
                <input type="text" id="insurance_contact" name="insurance_contact" class="form-control" value="<?= isset($row['insurance_contact']) ? $row['insurance_contact'] : '' ?>">
            </td>
            <td colspan="4" class="text-danger text-end" > 
				<div class="d-flex justify-content-center align-items-center">
					<label for="fromInsuDate" class="text-success text-end fw-bold"> 보험기간 </label> &nbsp; &nbsp;			
					<input type="date" id="fromInsuDate" name="fromInsuDate" class="form-control w100px" value="<?= isset($row['fromInsuDate']) ? $row['fromInsuDate'] : '' ?>">
					&nbsp; ~ &nbsp;
					<input type="date" id="toInsuDate" name="toInsuDate" class="form-control w100px" value="<?= isset($row['toInsuDate']) ? $row['toInsuDate'] : '' ?>">
				</div>
            </td>  			
        </tr>
        <tr>
            <td><label for="manufacturing_date">최초등록일</label></td>
            <td>
                <input type="date" id="manufacturing_date" name="manufacturing_date" class="form-control" value="<?= isset($row['manufacturing_date']) ? $row['manufacturing_date'] : '' ?>">
            </td>
            <td><label for="purchase_date">구매일자</label></td>
            <td>
                <input type="date" id="purchase_date" name="purchase_date" class="form-control" value="<?= isset($row['purchase_date']) ? $row['purchase_date'] : '' ?>">
            </td>
            <td colspan="4" class="text-danger text-end" > 
				<div class="d-flex justify-content-center align-items-center">
					<label for="inspectionDate" class="text-danger text-end fw-bold"> 검사주기 </label> &nbsp; &nbsp;			
					<input type="date" id="inspectionDate" name="inspectionDate" class="form-control w100px" value="<?= isset($row['inspectionDate']) ? $row['inspectionDate'] : '' ?>">
					&nbsp; ~ &nbsp;
					<input type="date" id="inspectionDateTo" name="inspectionDateTo" class="form-control w100px" value="<?= isset($row['inspectionDateTo']) ? $row['inspectionDateTo'] : '' ?>">
				</div>
            </td>            			
        </tr>
        <tr>
            <td><label for="engine_oil_change_data">엔진오일 교환일</label></td>
            <td colspan="7">
			<div class="d-flex align-items-center">
				<label class="me-2 ms-2 text-primary fw-bold" for="engine_oil_change_cycle"> 엔진오일 교환주기(Km) </label> &nbsp;
				<input type="number" id="engine_oil_change_cycle" name="engine_oil_change_cycle" class="form-control w100px fw-bold" value="<?= isset($row['engine_oil_change_cycle']) ? $row['engine_oil_change_cycle'] : '' ?>">
				&nbsp;&nbsp;&nbsp;&nbsp;
				<button type="button" class="btn btn-outline-primary btn-sm add-row-engineOil mb-1 "> <i class="bi bi-plus"> </i> 엔진오일 교환일 추가</button>							
			</div>

			<table class="table table-bordered" id="engineOilListTable">
				<thead>
					<tr>
						<th class="text-center" >#</th>
						<th class="text-center" >교환일</th>
						<th class="text-center" >주행거리</th>
						<th class="text-center" >비용</th>
						<th class="text-center" >행 추가/삭제</th>
					</tr>
				</thead>
				<tbody id="engineOilListBody">
					<!-- 엔진오일 교환일 행이 추가될 위치 -->
				</tbody>
			</table>			
            </td>
        </tr>
		<tr>
			<td><label for="maintenance_data">정비내역</label></td>
			<td colspan="7">
				<button type="button" class="btn btn-outline-success btn-sm add-row-maintenance mb-1"> <i class="bi bi-plus"> </i> 정비내역 추가</button>
				<table class="table table-bordered" id="maintenanceListTable">
					<thead>
						<tr>
							<th class="text-center" >#</th>
							<th class="text-center" >정비일자</th>
							<th class="text-center" >정비내역 기록</th>
							<th class="text-center" >비용</th>
							<th class="text-center" >정비업체</th>
							<th class="text-center" >행 추가/삭제</th>
						</tr>
					</thead>
					<tbody id="maintenanceListBody">
						<!-- 정비내역 행이 추가될 위치 -->
					</tbody>
				</table>
			</td>
		</tr>
        <tr>
            <td><label for="note">비고</label></td>
            <td colspan="7">
                <textarea id="note" name="note" class="form-control" rows="3"><?= isset($row['note']) ? $row['note'] : '' ?></textarea>
            </td>
        </tr>
    </tbody>
</table>
  </div>		
			<div class="row mt-2 mb-3">
				<div class="d-flex justify-content-center">
					<button type="button" id="saveBtn" class="btn btn-dark btn-sm me-3">
						<i class="bi bi-floppy-fill"></i> 저장
					</button>	
					<button type="button" id="deleteBtn" class="btn btn-danger btn-sm me-3">
						<i class="bi bi-trash"></i>  삭제 
					</button>	
					<button type="button" id="closeBtn" class="btn btn-outline-dark btn-sm me-2">
						&times; 닫기
					</button>
				</div>
			</div>			
    </div>

</div>
</div>
</form>

<!-- 페이지 로딩 -->
<script>
$(document).ready(function(){	
    var loader = document.getElementById('loadingOverlay');
	if(loader)
		loader.style.display = 'none';
});

ajaxRequest_write = null;

$(document).ready(function(){	  
    $("#closeBtn").on("click", function() {
        self.close();
    });	

$(document).ready(function(){	  
    $("#saveBtn").on("click", function() {
        let msg = '저장완료';

        var form = $('#board_form')[0];           
        var data = new FormData(form); 			
						
        // 저장 시 데이터를 JSON으로 처리하는 부분
        let engineOilList = [];
        $('#engineOilListTable tbody tr').each(function() {
            let rowData = {};
            $(this).find('input, select').each(function() {
                let name = $(this).attr('name').replace('[]', '');
                rowData[name] = $(this).val();
            });
            engineOilList.push(rowData);
        });

        // 데이터를 JSON으로 설정
        data.set('engine_oil_change_data', JSON.stringify(engineOilList));
		
        // 정비내역 데이터를 JSON으로 처리하는 부분
        let maintenanceList = [];
        $('#maintenanceListTable tbody tr').each(function() {
            let rowData = {};
            $(this).find('input, textarea').each(function() {
                let name = $(this).attr('name').replace('[]', '');
                rowData[name] = $(this).val();
            });
            maintenanceList.push(rowData);
        });

        // 데이터를 JSON으로 설정
        data.set('maintenance_data', JSON.stringify(maintenanceList));		

        if (ajaxRequest_write !== null) {
            ajaxRequest_write.abort();
        }		 

        ajaxRequest_write = $.ajax({
            url: "insert.php",
            type: "post",
            data: data,  // FormData 객체 사용
            processData: false, // jQuery가 data를 자동으로 처리하지 않도록 설정
            contentType: false, // contentType을 false로 설정하여 multipart/form-data로 전송
            success: function(data) {	
                console.log(data);			
                Toastify({
                    text: msg,
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "center",
                    backgroundColor: "#4fbe87",
                }).showToast();			
                
                $(opener.location).attr("href", "javascript:reloadlist();");	

                setTimeout(function() {
                   self.close();								   
                }, 1000);				
            },
            error: function(jqxhr, status, error) {
                console.log(jqxhr, status, error);
            } 			      		
        });												 
    });			
});

});	 

$(document).ready(function() {
    // 삭제 버튼 클릭 이벤트
    $("#deleteBtn").on("click", function() {
        const delfirstitem = $("#num").val(); // 삭제할 항목의 num 값을 가져옴
        delFn(delfirstitem);
    });
});

function delFn(delfirstitem) {
    $("#mode").val("delete");
    $("#num").val(delfirstitem);

    Swal.fire({
        title: '해당 DATA 삭제',
        text: "DATA 삭제는 신중하셔야 합니다. 정말 삭제 하시겠습니까?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: '삭제',
        cancelButtonText: '취소'
    }).then((result) => {
        if (result.isConfirmed) {
            if (ajaxRequest_write !== null) {
                ajaxRequest_write.abort();
            }		 
            ajaxRequest_write = $.ajax({
                url: "insert.php",
                type: "post",
                data: $("#board_form").serialize(),
                success: function(data) {
                    console.log(data);
                    Toastify({
                        text: "파일 삭제 완료!",
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "center",
                        backgroundColor: "#4fbe87"
                    }).showToast();
                    
                    // 페이지를 1.5초 후에 새로고침
                    setTimeout(function() {
					    $(opener.location).attr("href", "javascript:reloadlist();");
                        self.close();
                    }, 1000);										 
                },
                error: function(jqxhr, status, error) {
                    console.log(jqxhr, status, error);
                }
            });												
        } 
    });	
}


function inputNumberFormat(obj) {
    // 숫자, 소수점 및 - 이외의 문자는 제거
    obj.value = obj.value.replace(/[^0-9.-]/g, '');

    // 콤마를 제거하고 숫자를 포맷팅
    let value = obj.value.replace(/,/g, '');

    // 부호가 앞에 오도록 하고 소수점을 포함한 포맷팅 처리
    if (value.startsWith('-')) {
        // 음수일 때의 처리
        value = '-' + formatNumber(value.slice(1));
    } else {
        // 양수일 때의 처리
        value = formatNumber(value);
    }

    obj.value = value;
}

// 3자리마다 콤마를 추가하는 함수
function formatNumber(value) {
    if (!value) return ''; // 값이 없으면 빈 문자열 반환
    let parts = value.split('.');
    // 정수 부분에만 콤마 추가
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    // 소수점이 있으면 정수 부분 + 소수점 부분을 반환
    return parts.length > 1 ? parts.join('.') : parts[0];
}

function deleteLastchar(str)
// 마지막 문자 제거하는 함수
{
  return str = str.substr(0, str.length - 1);		
}
  		

$(document).ready(function() {
    // 서버에서 전달된 engine_oil_change_data를 PHP에서 JSON으로 변환
    var engineOilList = <?php echo json_encode($engine_oil_change_data ?? []); ?>;

    // 엔진오일 교환일 데이터가 문자열 형태로 넘어올 경우 JSON으로 파싱
    if (typeof engineOilList === 'string') {
        try {
            engineOilList = JSON.parse(engineOilList);
        } catch (e) {
            console.error('JSON 파싱 오류:', e);
            engineOilList = [];
        }
    }

    // 배열인지 확인하고 배열이 아니면 빈 배열로 초기화
    if (!Array.isArray(engineOilList)) {
        engineOilList = [];
    }

    // 기존 데이터를 테이블에 추가
    engineOilList.forEach(function(rowData, index) {
        addRow_engineOil($('#engineOilListBody'), rowData);
    });

});

// 초기 행 추가 버튼 클릭 시
$(document).on('click', '.add-row-engineOil', function() {
	addRow_engineOil($('#engineOilListBody'));
});


$('#engineOilListBody').on('click', '.remove-row-engineOil', function() {
	$(this).closest('tr').remove();
	updateSerialNumbers($('#engineOilListBody'));
});

// 엔진오일 행 추가 함수
function addRow_engineOil(tableBody, rowData = {}) {
	var newRow = $('<tr>');

	// 일련번호
	newRow.append('<td class="text-center serial-number"></td>');

	// 엔진오일 교환일 (Date 필드)
	var engineOilChangeDate = rowData.engine_oil_change_date || new Date().toISOString().split('T')[0]; // 현재 날짜 기본값으로 설정		
	newRow.append('<td class="text-center"><input type="date" name="engine_oil_change_date[]" class="form-control text-center" value="' + engineOilChangeDate + '"></td>');

	// 주행거리 (Mileage 필드)
	var mileage = rowData.mileage || ''; // 기존 데이터 설정
	newRow.append('<td class="text-center"><input type="number" name="mileage[]" class="form-control text-center" value="' + mileage + '"></td>');
	var engineOilFee = rowData.engine_oil_fee || '' ;
	newRow.append('<td class="text-center"><input type="text" name="engine_oil_fee[]" class="form-control"  value="' + engineOilFee + '"> </td>');		

	// 추가/삭제 버튼
	newRow.append('<td class="text-center">' +
		'<button type="button" class="btn btn-outline-dark btn-sm add-row-engineOil" style="border:0px;">+</button>' +
		'<button type="button" class="btn btn-outline-danger btn-sm remove-row-engineOil" style="border:0px;">-</button>' +
	'</td>');

	// 행을 테이블에 추가
	tableBody.append(newRow);

	// 일련번호 갱신
	updateSerialNumbers(tableBody);
}

// 정비내역 행 추가 함수
function addRow_maintenance(tableBody, rowData = {}) {
    var newRow = $('<tr>');

    // 일련번호
    newRow.append('<td class="text-center serial-number"></td>');

    // 정비일자 (Date 필드)			
    var maintenanceDate = rowData.maintenance_date || new Date().toISOString().split('T')[0]; // 현재 날짜 기본값으로 설정
    newRow.append('<td class="text-center"><input type="date" name="maintenance_date[]" class="form-control text-center" value="' + maintenanceDate + '"></td>');

    // 정비내역 기록 (Text 필드)
    var maintenanceRecord = rowData.maintenance_record || ''; // 기존 데이터 설정
    var maintenanceFee = rowData.maintenance_fee || ''; // 기존 데이터 설정
    var maintenancecorp = rowData.maintenance_corp || ''; // 기존 데이터 설정
    
    newRow.append('<td class="text-center"><input type="text" name="maintenance_record[]" class="form-control"  value="' + maintenanceRecord + '"> </td>');
    newRow.append('<td class="text-center"><input type="text" name="maintenance_fee[]" class="form-control"  value="' + maintenanceFee + '"> </td>');
    newRow.append('<td class="text-center"><input type="text" name="maintenance_corp[]" class="form-control"  value="' + maintenancecorp + '"> </td>');

    // 추가/삭제 버튼
    newRow.append('<td class="text-center">' +
        '<button type="button" class="btn btn-outline-dark btn-sm add-row-maintenance" style="border:0px;">+</button>' +
        '<button type="button" class="btn btn-outline-danger btn-sm remove-row-maintenance" style="border:0px;">-</button>' +
    '</td>');

    // 행을 테이블에 추가
    tableBody.append(newRow);

    // 일련번호 갱신
    updateSerialNumbers(tableBody);
}

// 일련번호 갱신 함수
function updateSerialNumbers(tableBody) {
    tableBody.find('tr').each(function(index) {
        $(this).find('.serial-number').text(index + 1);
    });
}

$(document).ready(function() {
    // 서버에서 전달된 maintenance_data를 PHP에서 JSON으로 변환
    var maintenanceList = <?php echo json_encode($maintenance_data ?? []); ?>;

    // 정비내역 데이터가 문자열 형태로 넘어올 경우 JSON으로 파싱
    if (typeof maintenanceList === 'string') {
        try {
            maintenanceList = JSON.parse(maintenanceList);
        } catch (e) {
            console.error('JSON 파싱 오류:', e);
            maintenanceList = [];
        }
    }

    // 배열인지 확인하고 배열이 아니면 빈 배열로 초기화
    if (!Array.isArray(maintenanceList)) {
        maintenanceList = [];
    }

    // 기존 데이터를 테이블에 추가
    maintenanceList.forEach(function(rowData) {
        addRow_maintenance($('#maintenanceListBody'), rowData);
    });

});

// 초기 행 추가 버튼 클릭 시
$(document).on('click', '.add-row-maintenance', function() {
	addRow_maintenance($('#maintenanceListBody'));
});


$('#maintenanceListBody').on('click', '.remove-row-maintenance', function() {
	$(this).closest('tr').remove();
	updateSerialNumbers($('#maintenanceListBody'));
});

</script>



</body>
</html>