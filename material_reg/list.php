<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php");
    exit;
}   
include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';
$title_message = '자재 입고'; 
?>
 
<link href="css/style.css?v=<?=time()?>" rel="stylesheet">   
<title> <?=$title_message?> </title> 

<style>
	/* 테이블에 테두리 추가 */
	#myTable, #myTable th, #myTable td {
		border: 1px solid black;
		border-collapse: collapse;
	}

	/* 테이블 셀 패딩 조정 */
	#myTable th, #myTable td {
		padding: 8px;
		text-align: center;
	}

	/* 검색 타입 선택 스타일 */
	.search-type-container {
		display: flex;
		align-items: center;
		justify-content: center;
		gap: 15px;
	}

	.search-type-container input[type="radio"] {
		margin-right: 5px;
	}

	.search-type-container label {
		cursor: pointer;
		font-weight: 500;
	}

	/* 동적 검색 컨트롤 스타일 */
	.year-select, .month-select, .period-select {
		display: none;
		min-width: 200px;
	}

	.year-select select, .month-select input, .period-select .d-flex {
		width: 100%;
	}

	/* 검색 결과 개수 스타일 */
	.badge.bg-primary {
		font-size: 1rem !important;
		padding: 0.5rem 1rem;
	}
</style>
</head> 

<body>         
<?php
$header = isset($_REQUEST['header']) ? $_REQUEST['header'] : '';  

if ($header == 'header')
    require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php');

function checkNull($strtmp) {
    return ($strtmp !== null && trim($strtmp) !== '');
}

$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';  
$mode = isset($_REQUEST["mode"]) ? $_REQUEST["mode"] : '';
$search_type = isset($_REQUEST['search_type']) ? $_REQUEST['search_type'] : 'period'; // 기본값은 기간별
$selected_year = isset($_REQUEST['selected_year']) ? $_REQUEST['selected_year'] : date('Y');
$selected_month = isset($_REQUEST['selected_month']) ? $_REQUEST['selected_month'] : date('Y-m');
$fromdate = isset($_REQUEST['fromdate']) ? $_REQUEST['fromdate'] : '';  
$todate = isset($_REQUEST['todate']) ? $_REQUEST['todate'] : '';  

// 현재 날짜
$currentDate = date("Y-m-d");

// 검색 타입에 따른 날짜 설정
if ($search_type === 'year') {
    // 연도별 검색
    $fromdate = $selected_year . "-01-01";
    $todate = $selected_year . "-12-31";
} elseif ($search_type === 'month') {
    // 월별 검색
    $fromdate = $selected_month . "-01";
    $todate = date("Y-m-t", strtotime($selected_month . "-01"));
} else {
    // 기간별 검색 (기본값)
    if ($fromdate === "" || $fromdate === null || $todate === "" || $todate === null) {
        $fromdate = date("Y-01-01");	
        $todate = $currentDate;
    }
}

// 연도 옵션 생성 (현재년도 + 과거 3년)
$current_year = date('Y');
$year_options = '';
for ($i = 0; $i < 4; $i++) {
    $year = $current_year - $i;
    $selected = ($year == $selected_year) ? 'selected' : '';
    $year_options .= "<option value='$year' $selected>" . $year . "년</option>";
}

$tablename = 'material_reg';
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

$order = " ORDER BY inoutdate DESC, registedate desc, inout_item_code asc ";

// 검색 조건에 따른 SQL 쿼리 구성
if (checkNull($search)) {
    $sql = "SELECT * FROM ".$DB.".".$tablename." 
        WHERE searchtag LIKE '%$search%' AND is_deleted IS NULL 
        AND inoutdate BETWEEN '$fromdate' AND '$todate' " . $order;	
} else {
    $sql = "SELECT * FROM ".$DB.".".$tablename." 
        WHERE is_deleted IS NULL 
        AND inoutdate BETWEEN '$fromdate' AND '$todate' " . $order;
}

try {      
    $stmh = $pdo->query($sql);
    $total_row = $stmh->rowCount();        
?>   

<form id="board_form" name="board_form" method="post" enctype="multipart/form-data">             
    <input type="hidden" id="mode" name="mode" value="<?=$mode?>">             
    <input type="hidden" id="num" name="num"> 
    <input type="hidden" id="tablename" name="tablename" value="<?=$tablename?>">                 
    <input type="hidden" id="header" name="header" value="<?=$header?>">      

	<div class="container-fluid">
		<!-- Modal -->
		<div id="myModal" class="modal">
			<div class="modal-content"  style="width:1100px;">
				<div class="modal-header">
					<span class="modal-title">자재 입고</span>
					<span class="close">&times;</span>
				</div>
				<div class="modal-body">
					<div class="custom-card"></div>
				</div>
			</div>
		</div>
	</div>
	
    <div class="container-fluid">
    <div class="card justify-content-center text-center mt-1">
    <div class="card-header d-flex justify-content-center align-items-center">
        <span class="text-center fs-5 me-4"><?=$title_message?></span>   
        <button type="button" class="btn btn-dark btn-sm me-1" onclick='location.href="list.php?header=header"'> <i class="bi bi-arrow-clockwise"></i> </button> 
        <small class="ms-5 text-muted"> 중국에서 입고된 품목들을 일자별 등록하고 저장 (1~7차까지 등록가능) </small>  
    </div>
    <div class="card-body">
        <div class="container mt-2 mb-2">   
        ▷ <?= $total_row ?> &nbsp; 
        
        <!-- 검색 타입 선택 -->
        <div class="row justify-content-center mb-3">
            <div class="col-auto">
                <div class="search-type-container">
                    <label class="me-3">
                        <input type="radio" name="search_type" value="year" <?= $search_type === 'year' ? 'checked' : '' ?> onchange="toggleSearchTypeAndSubmit()"> 연도별
                    </label>
                    <label class="me-3">
                        <input type="radio" name="search_type" value="month" <?= $search_type === 'month' ? 'checked' : '' ?> onchange="toggleSearchTypeAndSubmit()"> 월별
                    </label>
                    <label>
                        <input type="radio" name="search_type" value="period" <?= $search_type === 'period' ? 'checked' : '' ?> onchange="toggleSearchTypeAndSubmit()"> 기간별
                    </label>
                </div>
            </div>
        </div>

        <!-- 동적 검색 컨트롤 -->
        <div class="row justify-content-center mb-3">
            <div class="col-auto">
                <!-- 연도별 검색 -->
                <div class="year-select">
                    <select id="selected_year" name="selected_year" class="form-select form-select-sm" onchange="autoSubmit()">
                        <?= $year_options ?>
                    </select>
                </div>

                <!-- 월별 검색 -->
                <div class="month-select">
                    <input type="month" id="selected_month" name="selected_month" class="form-control" value="<?=$selected_month?>" onchange="autoSubmit()">
                </div>

                <!-- 기간별 검색 -->
                <div class="period-select">
                    <div class="d-flex align-items-center">
                        <input type="date" id="fromdate" name="fromdate" class="form-control me-2" value="<?=$fromdate?>" onchange="autoSubmit()">
                        <span class="me-2">~</span>
                        <input type="date" id="todate" name="todate" class="form-control" value="<?=$todate?>" onchange="autoSubmit()">
                    </div>
                </div>
            </div>
        </div>

        <!-- 검색어 입력 및 검색 버튼 (항상 유지) -->
        <div class="row justify-content-center mb-3">
            <div class="col-auto">
                <div class="d-flex align-items-center">
                    <div class="inputWrap30 me-2">			
                        <input type="text" id="search" class="form-control" style="width:150px;" name="search" value="<?=$search?>" autocomplete="off" onKeyPress="if (event.keyCode==13){ enter(); }" placeholder="검색어 입력">
                        <button class="btnClear"></button>
                    </div>							
                    <button class="btn btn-outline-dark btn-sm me-2" type="button" id="searchBtn"> <i class="bi bi-search"></i> 검색 </button> &nbsp;&nbsp;&nbsp;&nbsp;			
                    <button id="newBtn" type="button" class="btn btn-dark btn-sm me-2"> <i class="bi bi-pencil-square"></i> 신규 </button>				
                </div>
            </div>
        </div>
    
    <?php if($header !== 'header') 
            print '<button id="closeBtn" type="button" class="btn btn-outline-dark btn-sm"> <i class="bi bi-x-lg"></i> 창닫기 </button>';
    ?>            
    </div>	
    </div>	
    </div>	
    <div class="table-responsive">   
       <table class="table table-hover" id="myTable">         
            <thead class="table-primary">
                 <th class="text-center" style="width:60px;" >번호</th>
                 <th class="text-center" style="width:80px;" >등록일자</th>
                 <th class="text-center" style="width:80px;" >입고 일자</th>
                 <th class="text-center" style="width:80px;" >거래처</th>                 
			     <th class="text-center" style="width:150px;" >품목코드</th>
                 <th class="text-center" style="width:200px;" >품목명</th>
                 <th class="text-center" style="width:60px;" >수량</th>
				 <th class="text-center" style="width:100px;" >단가</th>
                 <th class="text-center" style="width:150px;" >로트번호</th>
                 <th class="text-center" style="width:100px;" >비고</th>
            </thead>
            <tbody>                  
            <?php          
            $start_num = $total_row;                  
            while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
                include '_row.php';                                        
            ?>                     
            <tr onclick="loadForm('update', '<?=$num?>');">
                <td class="text-center"><?= $start_num ?></td>
                <td class="text-center"><?= $registedate ?></td>
                <td class="text-center fw-bold"><?= $inoutdate ?></td>
                <td class="text-center"><?= $secondord?></td>                
                <td class="text-start fw-bold text-secondary"><?= $inout_item_code ?></td>				
                <td class="text-start fw-bold text-primary"><?= $item_name ?></td>
				<td class="text-center">
					<?php if (is_numeric($surang)) : ?>
						<?= number_format($surang, strpos($surang, '.') !== false ? 2 : 0) ?>
					<?php else : ?>
						<?= htmlspecialchars($surang) ?>
					<?php endif; ?>
				</td>
				<td class="text-center"><?= $unitprice ?></td>
                <td class="text-center fw-bold text-success"><?= $lotnum ?></td>
                <td class="text-center"><?= $comment ?></td>
            </tr>
        <?php
            $start_num--;
                 } 
              } catch (PDOException $Exception) {
                  print "오류: ".$Exception->getMessage();
              }  
        ?>         
      </tbody>
     </table>
    </div>
    </div>
    </div>        
</form>

<script>
var ajaxRequest = null;
var itemData = [];
var lotnumData = [];

// 검색 타입에 따른 동적 컨트롤 표시/숨김
function toggleSearchType() {
    var searchType = $('input[name="search_type"]:checked').val();
    
    // 모든 검색 컨트롤 숨기기
    $('.year-select, .month-select, .period-select').hide();
    
    // 선택된 타입에 따라 해당 컨트롤만 표시
    if (searchType === 'year') {
        $('.year-select').show();
    } else if (searchType === 'month') {
        $('.month-select').show();
    } else if (searchType === 'period') {
        $('.period-select').show();
    }
}

// 검색 타입 변경 시 자동 검색 실행
function toggleSearchTypeAndSubmit() {
    toggleSearchType();
    
    // 약간의 지연 후 폼 제출 (UI 업데이트를 위해)
    setTimeout(function() {
        $("#board_form").submit();
    }, 100);
}

// 검색 조건 변경 시 자동 검색 실행
function autoSubmit() {
    // 약간의 지연 후 폼 제출 (사용자 입력 완료를 위해)
    setTimeout(function() {
        $("#board_form").submit();
    }, 300);
}

function fetchItemData() {
    $.ajax({
        url: "fetch_itemcode.php",
        type: "post",
        dataType: "json",
        success: function(data) {
            itemData = data["items"];
            // console.log(data);  // 데이터 로드 확인
            initializeAutocomplete('#inout_item_code'); // 데이터 로드 후 자동 완성 초기화
            initializeAutocomplete('#item_name'); // 품목명에 대한 자동 완성 초기화
        },
        error: function(jqxhr, status, error) {
            console.log(jqxhr, status, error);
        }
    });
}

function fetchLotnumData() {
    $.ajax({
        url: "fetch_lotnum.php",
        type: "post",
        dataType: "json",
        success: function(data) {
            lotnumData = data["lotnums"];
            // console.log(data);  // 데이터 로드 확인
            initializeAutocomplete('#lotnum'); // 로트번호에 대한 자동 완성 초기화
        },
        error: function(jqxhr, status, error) {
            console.log(jqxhr, status, error);
        }
    });
}

function initializeAutocomplete(input) {
    $(input).autocomplete({
        source: function(request, response) {
            try {
                var term = request.term.toLowerCase();
                var filteredOptions;
                
                if (input.id === 'lotnum') {
                    filteredOptions = $.grep(lotnumData, function(lotnum) {
                        return lotnum.toLowerCase().indexOf(term) !== -1;
                    }).map(function(lotnum) {
                        return { label: lotnum, value: lotnum };
                    });
                } else {
                    filteredOptions = $.grep(itemData, function(item) {
                        return (item.item_code && item.item_code.toLowerCase().indexOf(term) !== -1) ||
                               (item.item_name && item.item_name.toLowerCase().indexOf(term) !== -1);
                    }).map(function(item) {
                        return {
                            label: item.item_code + ' - ' + item.item_name,
                            value: input.id === 'inout_item_code' ? item.item_code : item.item_name,
                            item_code: item.item_code,
                            item_name: item.item_name
                        };
                    });
                }
                response(filteredOptions);
            } catch (e) {
                console.error("Error in autocomplete source function: ", e);
                response([]);
            }
        },
        select: function(event, ui) {
            if (input.id === 'inout_item_code' || input.id === 'item_name') {
                if (input.id === 'inout_item_code') {
                    $(input).val(ui.item.item_code);
                    $('#item_name').val(ui.item.item_name);
                } else {
                    $(input).val(ui.item.item_name);
                    $('#inout_item_code').val(ui.item.item_code);
                }
            } else {
                $(input).val(ui.item.value);
            }
            return false;
        },
        focus: function(event, ui) {
            if (input.id === 'inout_item_code' || input.id === 'item_name') {
                if (input.id === 'inout_item_code') {
                    $(input).val(ui.item.item_code);
                    $('#item_name').val(ui.item.item_name);
                } else {
                    $(input).val(ui.item.item_name);
                    $('#inout_item_code').val(ui.item.item_code);
                }
            } else {
                $(input).val(ui.item.value);
            }
            return false;
        },
        open: function() {
            $(this).autocomplete("widget").css({
                "max-height": "200px",
                "overflow-y": "auto",
                "overflow-x": "hidden"
            });
        },
        close: function(event, ui) {
            // Do not close the autocomplete menu on input focus loss
            if (event.originalEvent && event.originalEvent.type === 'blur') {
                event.preventDefault();
            }
        }
    }).on('keydown', function(event) {
        if (event.keyCode === $.ui.keyCode.ENTER && $(this).autocomplete("instance").menu.active) {
            event.preventDefault();
        }
    });
}

$(document).ready(function() {
    fetchItemData();
    fetchLotnumData();
});

function loadForm(mode, num = null) {
    if (num == null) {
        $("#mode").val('insert');
    } else {
        $("#mode").val('update');
        $("#num").val(num);
    }       

    $.ajax({
        type: "POST",
        url: "fetch_modal.php",
        data: { mode: mode, num: num },
        dataType: "html",
        success: function(response) {
            document.querySelector(".modal-body .custom-card").innerHTML = response;
            $("#myModal").show();

            $("#closeBtn").on("click", function() {
                $("#myModal").hide();
            });

            initializeAutocomplete('#inout_item_code');
            initializeAutocomplete('#item_name');
            initializeAutocomplete('#lotnum');

            $(document).on('click', '.specialbtnClear', function(e) {
                e.preventDefault(); // 기본 동작을 방지합니다.
                $(this).siblings('input').val('').focus();
                $('#item_name').val('');
                $('#inout_item_code').val('');                
            });

            $(document).on('click', '.btnClear_lot', function(e) {
                e.preventDefault(); // 기본 동작을 방지합니다.
                $(this).siblings('input').val('').focus();                
                $('#lotnum').val('');
            });

            $(document).on('input', '.inputcode ', function() {
                initializeAutocomplete(this);
            });     
			
            $(document).on('input', '.inputitemname ', function() {
                initializeAutocomplete(this);
            });       
			
            $(document).on('input', '.inputlot ', function() {
                initializeAutocomplete(this);
            });       
			
            let isSaving = false;
            
            // 저장 버튼
            $("#saveBtn").on("click", function() {
                if (isSaving) return;
                isSaving = true;

                var header = $("#header").val();
                var formData = $("#board_form").serialize();
                let continuousRegistration = $("#continuous_registration").is(':checked');

                $.ajax({
                    url: "process.php",
                    type: "post",
                    data: formData,
                    success: function(response) {
                        Toastify({
                            text: "저장완료",
                            duration: 3000,
                            close: true,
                            gravity: "top",
                            position: "center",
                            backgroundColor: "#4fbe87",
                        }).showToast();

                        if (!continuousRegistration) {
                            $("#myModal").hide();
                            location.reload();
                        } else {
                            // 입력 필드 초기화
                            // $("#inout_item_code").val('');
                            // $("#item_name").val('');
                            // $("#secondord").val('');
                            // $("#unitprice").val('');
                            // $("#surang").val('');
                            // $("#lotnum").val('');
                            // $("#comment").val('');
							$("#mode").val('insert');
							$("#num").val('');							
							
                            isSaving = false;
                        }
                    },
                    error: function(jqxhr, status, error) {
                        console.log(jqxhr, status, error);
                        isSaving = false;
                    }
                });
            });

            // 삭제 버튼
            $("#deleteBtn").on("click", function() {
                var level = '<?= $_SESSION["level"] ?>';

                if (level !== '1') {
                    Swal.fire({
                        title: '삭제불가',
                        text: "관리자만 삭제 가능합니다.",
                        icon: 'error',
                        confirmButtonText: '확인'
                    });
                    return;
                }

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
                        var formData = $("#board_form").serialize();

                        $.ajax({
                            url: "process.php",
                            type: "post",
                            data: formData,
                            success: function(response) {
                                Toastify({
                                    text: "파일 삭제완료",
                                    duration: 2000,
                                    close: true,
                                    gravity: "top",
                                    position: "center",
                                    style: {
                                        background: "linear-gradient(to right, #00b09b, #96c93d)"
                                    },
                                }).showToast();

                                $("#myModal").hide();
                                location.reload();
                            },
                            error: function(jqxhr, status, error) {
                                console.log(jqxhr, status, error);
                            }
                        });
                    }
                });
            });
        },
        error: function(jqxhr, status, error) {
            console.log("AJAX Error: ", status, error);
        }
    });
}
</script>

<!-- 데이터 테이블 및 기타 기능을 위한 스크립트 -->
<script>
var ajaxRequest_write = null;
var dataTable; // DataTables 인스턴스 전역 변수
var material_regpageNumber; // 현재 페이지 번호 저장을 위한 전역 변수

$(document).ready(function() {            
    // DataTables 초기 설정
    dataTable = $('#myTable').DataTable({
        "paging": true,
        "ordering": true,
        "searching": true,
        "pageLength": 200,
        "lengthMenu": [ 100, 200, 500, 1000],
        "language": {
            "lengthMenu": "Show _MENU_ entries",
            "search": "Live Search:"
        },
        "order": [[1, 'desc']],
        "dom": 't<"bottom"ip>' // search 창과 lengthMenu 숨기기		
    });

    // 페이지 번호 복원 (초기 로드 시)
    var savedPageNumber = getCookie('material_regpageNumber');
    if (savedPageNumber) {
        dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
    }

    // 페이지 변경 이벤트 리스너
    dataTable.on('page.dt', function() {
        var material_regpageNumber = dataTable.page.info().page + 1;
        setCookie('material_regpageNumber', material_regpageNumber, 10); // 쿠키에 페이지 번호 저장
    });

    // 페이지 길이 셀렉트 박스 변경 이벤트 처리
    $('#myTable_length select').on('change', function() {
        var selectedValue = $(this).val();
        dataTable.page.len(selectedValue).draw(); // 페이지 길이 변경 (DataTable 파괴 및 재초기화 없이)

        // 변경 후 현재 페이지 번호 복원
        savedPageNumber = getCookie('material_regpageNumber');
        if (savedPageNumber) {
            dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
        }
    });

    $(document).on('click', '.specialbtnClear', function(e) {
        e.preventDefault(); // 기본 동작을 방지합니다.
        $(this).siblings('input').val('').focus();
    });
	
    $(document).on('click', '.btnClear_lot', function(e) {
        e.preventDefault(); // 기본 동작을 방지합니다.
        $(this).siblings('input').val('').focus();
    });
});

function restorePageNumber() {
    var savedPageNumber = getCookie('material_regpageNumber');
    location.reload(true);
}
</script>

<!-- 페이지로딩 및 Modal Script -->
<script>
$(document).ready(function(){    
    var loader = document.getElementById('loadingOverlay');
    if (loader) {
        loader.style.display = 'none';
    }
    
    toggleSearchType(); // 초기 로드 시 검색 타입에 맞는 컨트롤 표시

    var modal = document.getElementById("myModal");
    var span = document.getElementsByClassName("close")[0];

    span.onclick = function() {
        modal.style.display = "none";
    }

    $(".close").on("click", function() {		
        $("#myModal").hide();
    });
	
    $("#newBtn").on("click", function() {		
        loadForm('insert');
    });

    $("#searchBtn").on("click", function() {
        $("#board_form").submit();
    });
	
    $("#closeBtn").on("click", function() {
         var modal = document.getElementById("myModal");
		 modal.style.display = "none";
    });
});

function enter() {
    $("#board_form").submit();
}

function phonebookBtn(searchfield)
{	    
    var search = $("#" + searchfield).val();				
    href = '../phonebook_buy/list.php?search=' + search ;				
	popupCenter(href, '매입처 검색', 1600, 800);
}

function inputNumberFormat(obj) {
    // 숫자와 소수점 이외의 문자는 제거
    obj.value = obj.value.replace(/[^0-9.]/g, '');
    
    // 소수점이 두 개 이상 들어가는 것을 방지
    obj.value = obj.value.replace(/(\..*)\./g, '$1');
    
    // 콤마를 제거하고 숫자를 포맷팅 (소수점 이하 부분은 콤마 제외)
    let parts = obj.value.split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    
    obj.value = parts.join('.');
}


// Prevent form submission on Enter key
$(document).on("keypress", "input", function(event) {
	return event.keyCode != 13;
});		

$(document).ready(function(){
	saveLogData('자재 입고'); 
});
</script>

</body>
</html>