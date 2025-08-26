<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php"); 
    exit;
}   

include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';

// 첫 화면 표시 문구
$title_message = '인정업체별 사용제품';
?>
<link href="../css/style.css" rel="stylesheet">   
<title><?=$title_message?></title>

<style>
/* 버튼 그룹 스타일 */
.btn-group-sm .btn {
    border-radius: 0.2rem;
    font-size: 0.875rem;
    line-height: 1.5;
    padding: 0.25rem 0.5rem;
}

.btn-group .btn:not(:last-child):not(.dropdown-toggle) {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}

.btn-group .btn:not(:first-child) {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}

/* 작은 버튼 스타일 */
.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    border-radius: 0.2rem;
}

/* 아이콘 버튼 스타일 */
.btn i {
    font-size: 12px;
}

/* 테이블 스타일 */
.table-sm td, .table-sm th {
    padding: 0.3rem;
}

.task-row {
    transition: background-color 0.2s ease;
}

.task-row:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.form-control-sm {
    height: calc(1.5em + 0.5rem + 2px);
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    line-height: 1.5;
    border-radius: 0.2rem;
}
</style>

</head>
<body>		 

<?php include $_SERVER['DOCUMENT_ROOT'] . '/myheader.php'; ?>   

<?php
$mode = isset($_REQUEST["mode"]) ? $_REQUEST["mode"] : 'view'; // view 또는 edit
$tablename = 'CertProd'; // 인정제품 테이블

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

// num 1번 데이터 확인 (1개 레코드만 존재하는 구조)
$existing_data = null;
try {
    $sql = "SELECT * FROM ". $DB . "." . $tablename . " WHERE num = 1";
    $stmh = $pdo->query($sql);            
    $existing_data = $stmh->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $Exception) {
    // 테이블이 없거나 오류 발생 시 무시
}

// 모드 결정: 데이터가 없으면 신규, 있으면 수정 가능
$is_new = !$existing_data;
$can_edit = $mode === 'edit' && !$is_new;
?>

<form id="board_form" name="board_form" method="post" enctype="multipart/form-data">			 
	<input type="hidden" id="num" name="num" value="<?= $existing_data ? $existing_data['num'] : '' ?>">
	<input type="hidden" id="tablename" name="tablename" value="<?= $tablename ?>">
	<input type="hidden" id="mylist" name="mylist" value="">

<div class="container-fluid">
<div class="card justify-content-center text-center mt-5">

	<div class="card-header">
		<div class="d-flex justify-content-center text-center align-items-center">										 
			<span class="text-center fs-5"><?=$title_message?></span>		
			<button type="button" class="btn btn-dark btn-sm mx-3" onclick='location.reload();' title="새로고침"> <i class="bi bi-arrow-clockwise"></i> </button>  		
			<small class="mx-3 text-muted"> 인정업체별 성적서를 등록합니다.(발주시 필수 검색) </small>  
		</div>
	</div>
	<div class="card-body">								
		<div class="d-flex justify-content-center text-center align-items-center mb-3">										 
			<?php if($is_new): ?>
				<button type="button" class="btn btn-success btn-sm me-2" onclick="switchToEditMode()"> <i class="bi bi-plus-circle"></i> 신규등록 </button>
			<?php elseif($can_edit): ?>
				<button type="button" class="btn btn-primary btn-sm me-2" onclick="saveData()"> <i class="bi bi-check-circle"></i> 저장 </button>
			<?php else: ?>
				<button type="button" class="btn btn-warning btn-sm me-2" onclick="switchToEditMode()"> <i class="bi bi-pencil-square"></i> 수정 </button>
			<?php endif; ?>
			<button type="button" class="btn btn-secondary btn-sm" onclick="history.back()"> <i class="bi bi-arrow-left"></i> 뒤로 </button>
		</div>		
		
		<div class="table-responsive">	
		<table class="table table-bordered table-sm" id="productTable">		 
			
		<thead class="table-primary">
			<th class="text-center w80px">작업</th>
			<th class="text-center w80px">업체명</th>				
			<th class="text-center w250px">원단</th>					
			<th class="text-center w250px">내화실</th>
			<th class="text-center w150px">가스켓</th>
			<th class="text-center w150px">비고</th>
		</thead>
		<tbody id="productTableBody">		      	 
		</tbody>			
			
		</table>

		</div>
	</div>
</div>		
</form>

<script>
var productData = [];
var currentMode = '<?= $mode ?>';
var isNew = <?= $is_new ? 'true' : 'false' ?>;

// 페이지 로딩 시 기존 데이터 표시
$(document).ready(function(){	
	var loader = document.getElementById('loadingOverlay');
	if(loader)
		loader.style.display = 'none';

	// 기존 데이터가 있으면 로드
	<?php if($existing_data && $existing_data['mylist']): ?>
		try {
			productData = JSON.parse('<?= addslashes($existing_data['mylist']) ?>');
			if(Array.isArray(productData)) {
				productData.forEach(function(item) {
					addRow(item);
				});
			}
		} catch(e) {
			console.error('JSON 파싱 오류:', e);
			productData = [];
		}
	<?php endif; ?>

	// 초기 행이 없으면 빈 행 추가
	if(productData.length === 0) {
		addRow();
	}
	
	// 현재 모드에 따라 UI 상태 설정
	updateUIMode();
});

// UI 모드 업데이트
function updateUIMode() {
	var isEditMode = currentMode === 'edit';
	
	// 입력 필드 readonly 설정
	$('#productTableBody input').prop('readonly', !isEditMode);
	
	// 버튼 활성화/비활성화
	$('#productTableBody .btn').prop('disabled', !isEditMode);
	
	// 테이블 스타일 변경
	if(isEditMode) {
		$('#productTable').removeClass('table-secondary').addClass('table-bordered');
	} else {
		$('#productTable').removeClass('table-bordered').addClass('table-secondary');
	}
}

// 수정 모드로 전환
function switchToEditMode() {
	currentMode = 'edit';
	updateUIMode();
	location.href = '?mode=edit';
}

// 조회 모드로 전환
function switchToViewMode() {
	currentMode = 'view';
	updateUIMode();
	location.href = '?mode=view';
}

// 행 추가 함수
function addRow(data = null, afterRow = null) {
	var newRow = $('<tr>');
	
	// 기본 데이터 설정
	var rowData = data || {
		company_name: '',
		원단: '',
		내화실: '',
		가스켓: '',
		비고: ''
	};

	// 작업 버튼 열
	newRow.append('<td class="text-center">' +
		'<div class="d-flex align-items-center justify-content-center">' +
			'<div class="btn-group btn-group-sm" role="group" style="gap: 1px;">' +
				'<button type="button" class="btn btn-outline-primary btn-sm p-0" style="width: 20px; height: 20px; font-size: 12px;" onclick="addRow(null, this.closest(\'tr\'))" title="아래에 행 추가">' +
					'<i class="bi bi-plus"></i>' +
				'</button>' +
				'<button type="button" class="btn btn-outline-success btn-sm p-0" style="width: 20px; height: 20px; font-size: 12px;" onclick="copyRow(this)" title="행 복사">' +
					'<i class="bi bi-files"></i>' +
				'</button>' +
				'<button type="button" class="btn btn-outline-danger btn-sm p-0" style="width: 20px; height: 20px; font-size: 12px;" onclick="removeRow(this)" title="행 삭제">' +
					'<i class="bi bi-dash"></i>' +
				'</button>' +
			'</div>' +
		'</div>' +
	'</td>');

	// 업체명
	newRow.append('<td class="text-center"><input type="text" name="company_name[]" class="form-control form-control-sm" autocomplete="off" value="' + (rowData.company_name || '') + '"></td>');
	
	// 원단
	newRow.append('<td class="text-center"><input type="text" name="원단[]" class="form-control form-control-sm" autocomplete="off" value="' + (rowData.원단 || '') + '"></td>');
	
	// 내화실
	newRow.append('<td class="text-center"><input type="text" name="내화실[]" class="form-control form-control-sm" autocomplete="off" value="' + (rowData.내화실 || '') + '"></td>');
	
	// 가스켓
	newRow.append('<td class="text-center"><input type="text" name="가스켓[]" class="form-control form-control-sm" autocomplete="off" value="' + (rowData.가스켓 || '') + '"></td>');
	
	// 비고
	newRow.append('<td class="text-center"><input type="text" name="비고[]" class="form-control form-control-sm" autocomplete="off" value="' + (rowData.비고 || '') + '"></td>');
	// 행을 테이블에 추가
	if(afterRow) {
		$(afterRow).after(newRow);
	} else {
		$('#productTableBody').append(newRow);
	}
	
	// UI 모드 업데이트
	updateUIMode();
}

// 행 삭제 함수
function removeRow(button) {
	var row = $(button).closest('tr');
	if($('#productTableBody tr').length > 1) {
		row.remove();
	} else {
		alert('최소 하나의 행은 유지해야 합니다.');
	}
}

// 행 복사 함수
function copyRow(button) {
	var row = $(button).closest('tr');
	var rowData = {
		company_name: row.find('input[name="company_name[]"]').val(),
		원단: row.find('input[name="원단[]"]').val(),
		내화실: row.find('input[name="내화실[]"]').val(),
		가스켓: row.find('input[name="가스켓[]"]').val(),
		비고: row.find('input[name="비고[]"]').val()
	};
	addRow(rowData, row);
}

// AJAX로 데이터 저장
function saveData() {
	// 테이블의 모든 데이터를 수집
	var data = [];
	$('#productTableBody tr').each(function() {
		var row = $(this);
		var rowData = {
			company_name: row.find('input[name="company_name[]"]').val(),
			원단: row.find('input[name="원단[]"]').val(),
			내화실: row.find('input[name="내화실[]"]').val(),
			가스켓: row.find('input[name="가스켓[]"]').val(),
			비고: row.find('input[name="비고[]"]').val()
		};
		data.push(rowData);
	});

	// JSON 데이터를 hidden 필드에 설정
	$('#mylist').val(JSON.stringify(data));

	// AJAX로 저장
	$.ajax({
		url: 'process.php',
		type: 'POST',
		data: {
			num: $('#num').val(),
			tablename: $('#tablename').val(),
			mylist: $('#mylist').val()
		},
		success: function(response) {
			try {
				var result = JSON.parse(response);
				if(result.success) {
					swal.fire({
						icon: 'success',
						title: '저장 완료',
						text: result.message
					}).then(function() {
						// 저장 성공 시 조회 모드로 전환
						location.href = '?mode=view';
					});
				} else {
					swal.fire({
						icon: 'error',
						title: '저장 실패',
						text: '저장 실패: ' + result.message
					});
				}
			} catch(e) {
				// JSON 파싱 실패 시 일반 응답으로 처리
				swal.fire({
					icon: 'error',
					title: '오류',
					text: response
				});
			}
		},
		error: function() {
			swal.fire({
				icon: 'error',
				title: '오류',
				text: '저장 중 오류가 발생했습니다.'
			});
		}
	});
}

// 키보드 단축키
$(document).keydown(function(e){
	var code = e.keyCode || e.which;
	if (code == 27) { // ESC
		if(currentMode === 'edit') {
			switchToViewMode();
		} else {
			history.back();
		}
	}
});


$(document).ready(function(){
	saveLogData('인정업체별 사용제품'); 
});

</script>

</body>
</html>
