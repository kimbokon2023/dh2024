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
    $title_message = "(데이터복사) 모터,브라켓,콘트롤박스 등 단가 마진 추정";
} else {
    $title_message = "모터,브라켓,콘트롤박스 등 단가 마진 추정";
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
</style>

</head>

<?php   
include $_SERVER['DOCUMENT_ROOT'] . '/mymodal.php';
  
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();	
 
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
								<textarea id="memo" name="memo" class="form-control" rows="4"><?=$memo?></textarea>
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
				<div class='d-flex justify-content-center mt-1 mb-1 m-1'>
                    <h5 class="card-title text-center mx-5">모터 단가표</h5> 
                    <input type="text" id="currencyrate" name="currencyrate" class="form-control w60px text-end" value="200"  >
                    <button type="button" id="adaptCurrency" class="btn btn-primary btn-sm mx-2" > 환율 전체행 적용 </button>	
				</div>
                    <table id="myTable" class="table table-bordered table-hover">
					 <thead class="table-primary text-center">
							<tr>
								<th rowspan="2" class="align-middle">+-</th>
								<th rowspan="2" class="align-middle">NO.</th>
								<th rowspan="2" class="align-middle">구분</th>								
								<th colspan="5" class="align-middle">단가표</th>
								<th rowspan="2" class="align-middle">합계(위엔)</th>
								<th rowspan="2" class="align-middle">위엔화 환율</th>
								<th rowspan="2" class="align-middle">원화 환산금액</th>								
								<th rowspan="1" class="align-middle">통관+운송</th>								
								<th rowspan="1" class="align-middle  bg-danger text-white">판매가</th>
								<th rowspan="2" class="align-middle">판매마진</th>
								<th rowspan="1" class="align-middle">매출대비</th>
							</tr>
							<tr>
								<!-- Second row headers for the "단가 표" section -->
								<th class="align-middle">품목</th>
								<th class="align-middle">모터</th>
								<th class="align-middle">브라켓</th>
								<th class="align-middle">콘트롤박스</th>
								<th class="align-middle">기타(금액)</th>																
								<th class="align-middle">20% UP</th>
								<th class="align-middle bg-danger text-white">(모터+브라켓)</th>								
								<th class="align-middle">마진율(%)</th>
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

var ajaxRequest = null;
var ajaxRequest_write = null;
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
            var clonedRow = currentRow.clone();
            
            // 복사된 행의 일련번호는 복사하지 않음, 새 행 추가 후 일련번호 재정렬
            clonedRow.find('.row-index').text('');
            currentRow.after(clonedRow);

            // 일련번호 재계산
            updateRowIndices();
        }
    });
	
	// view 모드일 때, 버튼 및 입력 요소 비활성화 뒷쪽에 배치해야 동작한다. 주의요함
    if (mode === 'view') {
        $('input, textarea').prop('readonly', true); // Disable all input, textarea, and select elements
        $('input[type=hidden]').prop('readonly', false);
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
        '<div class="d-flex justify-content-center mt-1">' + 
        '<button type="button" class="btn btn-primary btn-sm viewNoBtn add-row me-1" data-table="' + tableBody.closest('table').attr('id') + '">+</button>' +
        '<button type="button" class="btn btn-danger btn-sm viewNoBtn remove-row ms-2 me-2">-</button>' +
        '<button type="button" class="btn btn-success btn-sm viewNoBtn copy-row"><i class="bi bi-copy"></i></button>' +
        '</div></td>');

    // 일련번호 추가 (두 번째 열)
    var rowIndex = tableBody.find('tr').length + 1;
    newRow.append('<td class="text-center align-middle row-index">' + rowIndex + '</td>');

    // col1부터 col15까지 채우기 (세 번째 열부터 시작)
    for (let i = 1; i <= 15; i++) {
		let colValue = rowData['col' + i] || ''; // 값이 없으면 빈 문자열 사용

		if (!isNaN(colValue) && colValue !== '') {
			// 값이 숫자일 경우 콤마와 소수점 포맷 적용
			colValue = parseFloat(colValue).toLocaleString('en', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
		}
        // 특정 열만 readonly 속성 부여
        if ([ 11, 12, 14, 15].includes(i)) {
            newRow.append('<td><input type="text" name="col' + i + '[]" value="' + colValue + '" class="form-control text-center number-format calculate col' + i + '" autocomplete="off" readonly></td>');
        } 
		else if  ([2].includes(i)) 
		{
            newRow.append('<td><input type="text" name="col' + i + '[]" value="' + colValue + '" style="width:130px;" class="form-control text-center  calculate  number-format  col' + i + '" autocomplete="off" ></td>');
        } 
		else if ([ 1, 3, 4, 5, 6, 7, 8, 13 ].includes(i)) {
            newRow.append('<td><input type="text" name="col' + i + '[]" value="' + colValue + '" class="form-control text-center number-format   calculate  col' + i + '" autocomplete="off"  onkeyup="inputNumberFormat(this);" ></td>');
        }
    }

    // 새 행을 테이블에 추가
    tableBody.append(newRow);

	// 숫자 필드에 3자리마다 콤마 추가
	newRow.find('.number-format').on('input', function () {
		calculateRow(newRow);
	});

    // 처음 로드될 때도 자동 계산 적용
    calculateRow(newRow);
}

function calculateRow(row) {
    // col3 모터가격, 브라켓, 콘트롤박스, 리미트, 
    const col3 = parseFloat(row.find('.col3').val().replace(/,/g, '')) || 0;
    const col4 = parseFloat(row.find('.col4').val().replace(/,/g, '')) || 0;
    const col5 = parseFloat(row.find('.col5').val().replace(/,/g, '')) || 0;
    const col6 = parseFloat(row.find('.col6').val().replace(/,/g, '')) || 0;
    const col7 = col3 + col4 + col5 + col6; // col7은 col3, col4, col5, col6을 합산 (4가지 품목을 더한값)

    // col7 계산 (readonly)
    if (!isNaN(col7)) {
        row.find('.col7').val(col7.toLocaleString('en'));
    }

    // col8은 환율 입력
    const col8 = parseFloat(row.find('.col8').val().replace(/,/g, '')) || 0;
	
	// 9,10은 제외시킴 프로그램 수정
    // const col9 = parseFloat(row.find('.col9').val().replace(/,/g, '')) || 0;
    // const col10 = parseFloat(row.find('.col10').val().replace(/,/g, '')) || 0;
	
    const col11 = parseFloat((col7 * col8).toFixed(1)); // 소수점 첫째자리까지 계산

    if (!isNaN(col11)) {
        row.find('.col11').val(col11.toLocaleString('en'));
    }

    // col12 계산 (col11 * 1.2)
    const col12 = parseFloat((col11 * 1.2).toFixed(1)); // 소수점 첫째자리까지 계산
    if (!isNaN(col12)) {
        row.find('.col12').val(col12.toLocaleString('en'));
    }

    // col13을 가져와서 col14 계산 (col13 - col11)
    const col13 = parseFloat(row.find('.col13').val().replace(/,/g, '')) || 0;
    const col14 = col13 - col12;

    if (!isNaN(col14)) {
        row.find('.col14').val(col14.toLocaleString('en'));
    }

    // col15 계산 (마진율 = col14 / col13 * 100)
    let col15 = 0;
    if (col13 !== 0) {
        col15 = parseFloat(((col14 / col13) * 100).toFixed(1)); // 소수점 첫째자리까지 계산
    }

    if (!isNaN(col15)) {
        row.find('.col15').val(col15.toLocaleString('en'));
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

    if (level !== '1') {
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
