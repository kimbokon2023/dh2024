<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php");
    exit;
}   
include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';
$title_message = '로트번호 생성관리'; 
?>
 
<link href="css/style.css" rel="stylesheet">   
<title> <?=$title_message?> </title>

<style>
.container-move {
    font-family: Arial, sans-serif;
    display: flex;
    font-size: 1.4rem;
    justify-content: center;
    align-items: center;
    overflow: hidden;
    width: 100%;
}
.description-move {
    font-family: Arial, sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: hidden;
    width: 100%;
}
.container-move .part, .description-move .desc-part {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.5s ease-in-out, transform 0.5s ease-in-out;
}
.container-move .part.show, .description-move .desc-part.show {
    opacity: 1;
    transform: translateY(0);
}

.description-move {
    font-size: 1rem;
    color: #666;
}
/* 체크박스 크기 크게 */
input[type="checkbox"] {
    transform: scale(1.6); /* 크기를 1.5배로 확대 */
    margin-right: 10px;   /* 확대 후의 여백 조정 */
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

$tablename = 'material_lot';

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();
 
$order = " ORDER BY registedate DESC";
    
if (checkNull($search)) {
    $sql = "SELECT * FROM ".$DB.".".$tablename." 
            WHERE searchtag LIKE '%$search%' AND is_deleted IS NULL 
            AND registedate BETWEEN '$fromdate' AND '$todate' " . $order;    
} else {
    $sql = "SELECT * FROM ".$DB.".".$tablename . " WHERE is_deleted IS NULL 
            AND registedate BETWEEN '$fromdate' AND '$todate' " . $order;   
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
        <div class="modal-content"  style="width:1000px;">
            <div class="modal-header">
                <span class="modal-title">로트번호 관리(생성/수정)</span>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <div class="custom-card"></div>
            </div>
        </div>
    </div>
</div>

<?php 
if ($header !== 'header') {
    print '<div class="container-fluid">';
    print '<div class="card justify-content-center text-center mt-1">';
} else {
    print '<div class="container">';
    print '<div class="card justify-content-center text-center mt-5">';
}
?>     
    <div class="card-header d-flex justify-content-center align-items-center">   
        <span class="text-center fs-5">  <?=$title_message?>   </span>     
		<button type="button" class="btn btn-dark btn-sm mx-1" onclick='location.reload()'>  <i class="bi bi-arrow-clockwise"></i> </button>      						 
		<small class="ms-5 text-muted"> 제품별 로트번호 확인 및 검색 (신규생성시 "신규"버튼 클릭 후 품목에 맞게 기재) ex.DH-M-0825 </small>  
    </div>
		 <?php if($chkMobile==false) { ?>
			<div class="container">     
		 <?php } else { ?>
			<div class="container-fluid">     
			<?php } ?>
		 
		<?php
			// Get current date in MMDD format
			$now_tmp = date('md');
		?>
		<div class="card mt-2 mb-2">
		<div class="card-body">
		<div class="row">	

		  <div class="container-move">
				<div id="part1" class="part">(주) 대한 DH모터 로트번호 생성규칙 : </div>
				<div id="part2" class="part">DH</div>
				<div id="part3" class="part">-</div>
				<div id="part4" class="part">M</div>
				<div id="part5" class="part">-</div>
				<div id="part6" class="part">0424</div>				
			</div>
			<div class="description-move">
				<div id="desc1" class="desc-part">DH</div>
				<div id="desc2" class="desc-part">-</div>
				<div id="desc3" class="desc-part">M , M(방범), C , B , T , F </div>
				<div id="desc4" class="desc-part">-</div>
				<div id="desc5" class="desc-part">0424(중국제조번호)</div>				
				<div id="desc6" class="desc-part"> 참고바랍니다. M-모터, M(방범) - 방범용 모터, C-연동제어기, B-브라켓트, T-콘트롤박스, F-원단, 수동입력 </div>
			</div>
		</div>
		</div>
		</div>
		</div>

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
        
    <div class="table-responsive">   
       <table class="table table-hover" id="myTable">         
            <thead class="table-primary">
                 <th class="text-center">번호</th>
                 <th class="text-center">등록일자</th>
                 <th class="text-center w-25" >로트번호</th>                 
                 <th class="text-center">업데이트 로그</th>
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
                <td class="text-center fs-6 fw-bold"><?= $lotnum ?></td>                
                <td class="text-center"><?= $update_log ?></td>
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

</body>
</html>

<script>
var ajaxRequest = null;

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

			document.getElementById('select-all').addEventListener('change', function() {
				var checkboxes = document.querySelectorAll('.record-checkbox');
				for (var checkbox of checkboxes) {
					checkbox.checked = this.checked;
				}
			});			

			var manualInputCheckbox = document.getElementById('manual_input_checkbox');
			var selectAllCheckbox = document.getElementById('select-all');

            $("#closeBtn").on("click", function() {
                $("#myModal").hide();
            });
            
			// 저장 버튼
			$("#saveBtn").on("click", function() {
				var header = $("#header").val();
				var china_num = $("#china_num").val();
				var manual_input_checkbox = $("#manual_input_checkbox").is(':checked') ? 'checked' : '';
				var manual_input = $("#manual_input").val();
				var lot_types = [];

				$("input[name='lot_type[]']:checked").each(function() {
					lot_types.push($(this).val());
				});

				if (lot_types.length === 0 && manual_input_checkbox === '') {
					alert('하나 이상 필드를 선택하거나 수동 입력을 체크해주세요.');
					return;
				}

				let msg = '저장완료';
				let lotnum;
				let requests = [];

				if (lot_types.length > 0 && manual_input_checkbox === '') {
					requests = lot_types.map(type => {
						if (china_num == '') {
							lotnum = `${type}`;
						} else {
							lotnum = `DH-${type}-${china_num}`;
						}

						console.log('Request Data:', {
							mode: $("#mode").val(),
							num: $("#num").val(),
							tablename: $("#tablename").val(),
							header: $("#header").val(),
							update_log: $("#update_log").val(),
							registedate: $("#registedate").val(),
							lotnum: lotnum,
							manual_input_checkbox: manual_input_checkbox,
							manual_input: manual_input
						});

						return $.ajax({
							url: "process.php",
							type: "post",
							data: {
								mode: $("#mode").val(),
								num: $("#num").val(),
								tablename: $("#tablename").val(),
								header: $("#header").val(),
								update_log: $("#update_log").val(),
								registedate: $("#registedate").val(),
								lotnum: lotnum,
								manual_input_checkbox: manual_input_checkbox,
								manual_input: manual_input
							},
							dataType: "json"
						});
					});
				} else if (manual_input_checkbox === 'checked') {
					lotnum = `DH-${manual_input}`;

					console.log('Request Data:', {
						mode: $("#mode").val(),
						num: $("#num").val(),
						tablename: $("#tablename").val(),
						header: $("#header").val(),
						update_log: $("#update_log").val(),
						registedate: $("#registedate").val(),
						lotnum: lotnum,
						manual_input_checkbox: manual_input_checkbox,
						manual_input: manual_input
					});

					requests.push($.ajax({
						url: "process.php",
						type: "post",
						data: {
							mode: $("#mode").val(),
							num: $("#num").val(),
							tablename: $("#tablename").val(),
							header: $("#header").val(),
							update_log: $("#update_log").val(),
							registedate: $("#registedate").val(),
							lotnum: lotnum,
							manual_input_checkbox: manual_input_checkbox,
							manual_input: manual_input
						},
						dataType: "json"
					}));
				}

				$.when.apply($, requests).then(function() {
					Toastify({
						text: msg,
						duration: 3000,
						close: true,
						gravity: "top",
						position: "center"
					}).showToast();

					$("#myModal").hide();
					location.reload();
				}).fail(function(jqxhr, status, error) {
					console.log('AJAX Error:', jqxhr, status, error);
				});
			});

            // 삭제 버튼
            $("#deleteBtn").click(function() {
                var level = '<?= $_SESSION["level"] ?>';

                if (level !== '1') {
                    Swal.fire({
                        title: '삭제불가',
                        text: "관리자만 삭제 가능합니다.",
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

                            $.ajax({
                                enctype: 'multipart/form-data',
                                processData: false,
                                contentType: false,
                                cache: false,
                                timeout: 1000000,
                                url: "process.php",
                                type: "post",
                                data: formData,
                                dataType: "json",
                                success: function(data) {
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
                }
            });

            $("#closeBtn").on("click", function() {
                var modal = document.getElementById("myModal");
                modal.style.display = "none";
            });
			
		
				manualInputCheckbox.addEventListener('change', () => {
					if (manualInputCheckbox.checked) {
						selectAllCheckbox.checked = false;
						const checkboxes = document.querySelectorAll('.record-checkbox');
						checkboxes.forEach(checkbox => {
							checkbox.checked = false;
						});
					}
				});

				selectAllCheckbox.addEventListener('change', () => {
					if (selectAllCheckbox.checked) {
						manualInputCheckbox.checked = false;
					}
				});
        },
        error: function(jqxhr, status, error) {
            console.log("AJAX Error: ", status, error);
        }
    });
}

function restorePageNumber() {
    var savedPageNumber = getCookie('material_lotpageNumber');
    location.reload(true);
}

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
	
		const parts = document.querySelectorAll('.part');
				const descParts = document.querySelectorAll('.desc-part');
				let index = 0;

				function showNextPart() {
					if (index < parts.length) {
						parts[index].classList.add('show');
						index++;
						setTimeout(showNextPart, 500); // Adjust the delay for each part to show up
					} else if (index - parts.length < descParts.length) {
						descParts[index - parts.length].classList.add('show');
						index++;
						setTimeout(showNextPart, 500); // Adjust the delay for each desc part to show up
					}
				}

				showNextPart();

	
	
});

function enter() {
    $("#board_form").submit();
}

$(document).ready(function(){
	saveLogData('로트번호 생성관리'); 
});

</script>
