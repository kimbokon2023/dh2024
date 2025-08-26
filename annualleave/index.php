<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/session.php');

// if (!isset($_SESSION["name"])) {
    // $_SESSION["url"] = 'https://dh2024.co.kr/annualleave/index.php?user_name=' . $user_name;
    // sleep(1);
    // header("Location:https://dh2024.co.kr/login/logout.php");
    // exit;
// }

$title_message = '직원 연차';

?>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php' ?>

<link href="css/style.css" rel="stylesheet">   

<title> <?=$title_message?> </title>
</head>

<body>

<? require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php'); ?>

<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

// 배열로 기본정보 불러옴
require_once($_SERVER['DOCUMENT_ROOT'] . "/almember/load_DB.php");

if ($level === '1')
    $admin = 1;

$tablename = "eworks";

// 변수 초기화
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';
$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';

$AndisDeleted = " AND is_deleted IS NULL ";
$WhereisDeleted = " where is_deleted IS NULL ";

if ($mode == "search" || $mode == "") {
    if ($search == "") {
        if ($admin == 1) {
            $sql = "SELECT * FROM " . $DB . "." . $tablename . $WhereisDeleted . " ORDER BY al_askdatefrom DESC, registdate DESC";
        } else {
            $sql = "SELECT * FROM " . $DB . "." . $tablename . " WHERE author LIKE '%$user_name%' " . $AndisDeleted . " ORDER BY al_askdatefrom DESC, registdate DESC";
        }
    } elseif ($search != "") {
        if ($admin == 1) {
            $sql = "SELECT * FROM " . $DB . "." . $tablename . " WHERE (author LIKE '%$search%') " . $AndisDeleted . " ORDER BY al_askdatefrom DESC, registdate DESC";
        } else {
            $sql = "SELECT * FROM " . $DB . "." . $tablename . " WHERE (author = '$user_name') AND (author LIKE '%$search%') " . $AndisDeleted . " ORDER BY al_askdatefrom DESC, registdate DESC";
        }
    }
}

// var_dump($sql);

try {
    $stmh = $pdo->query($sql);
    $total_row = $stmh->rowCount();
?>

<form name="board_form" id="board_form" method="post" >

<input type="hidden" id="username" name="username" value="<?= isset($user_name) ? $user_name : '' ?>">

    <?php if ($chkMobile == false) { ?>
        <div class="container">
        <?php } else { ?>
            <div class="container-fluid">
            <?php } ?>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-center align-items-center mt-3 mb-3">
                        <span class=" fs-5"> <?=$title_message?> </span>
                        <button type="button" class="btn btn-dark btn-sm mx-3"  onclick='location.reload();' title="새로고침"> <i class="bi bi-arrow-clockwise"></i> </button>  	 			
                        &nbsp;&nbsp;&nbsp;&nbsp;
                        <? if ($admin == 1) { ?>
                            <button type="button" id="openAlmemberBtn" class="btn btn-success btn-sm me-2">
                                <i class="bi bi-pencil-square"></i>
                                직원 정보
                                <button type="button" class="btn btn-primary btn-sm" onclick="location.href='./admin.php'">
                                    <i class="bi bi-pencil-square"></i>
                                    관리자모드
                                </button>
                            <? } ?>
                            <small class="ms-5 text-muted"> 연차 기간을 넣고 결재요청 버튼을 누르시면 등록됩니다. </small>    
                    </div>
					<div class="d-flex justify-content-center align-items-center mt-3 mb-4">
						<h6>
							<select id="yearSelect" class="form-select form-select-sm d-inline w-auto">
								<?php
								$currentYear = date("Y");
								for ($year = $currentYear; $year >= $currentYear - 3; $year--) {
									echo "<option value='{$year}'" . ($year == $currentYear ? " selected" : "") . ">{$year}</option>";
								}
								?>
							</select>
							년도 연차 &nbsp; <?=$user_name?>님 &nbsp;
							<span class="badge bg-success fs-6"> 발생일수 </span>
							<span id="totalDays" class="text-success"> <?=$total?> &nbsp; </span>
							<span class="badge bg-primary fs-6"> 사용일수 </span>
							<span id="usedDays" class="text-primary"> <?=$thisyeartotalusedday?> &nbsp; </span>
							<span class="badge bg-secondary fs-6"> 잔여일수 </span>
							<span id="remainingDays" class="text-dark"> <?=$thisyeartotalremainday?> &nbsp; </span>
						</h6>
					</div>


                    <div class="d-flex justify-content-center mt-1 mb-1">
                        <h6 style="background-color:#a5a5a5;"> 결재완료 후 삭제 불가 </h6>
                    </div>

                    <div class="d-flex justify-content-center align-items-center mt-2 mb-2">

                        &nbsp;&nbsp;&nbsp; ▷ <?= $total_row ?> &nbsp;&nbsp;&nbsp;

                        <input type="text" name="search" id="search" class="form-control me-1" style="width:150px;" value="<?=$search?>" onkeydown="JavaScript:SearchEnter();" placeholder="검색어">
                        <button type="button" id="searchBtn" class="btn btn-outline-dark btn-sm mx-2"> <i class="bi bi-search"></i> 검색</button>
                        <button type="button" id="writeBtn" class="btn btn-dark btn-sm me-1"> <i class="bi bi-pencil-square"></i> 신청 </button>
                    </div>

                    <div class="row d-flex justify-content-center">
                        <table class="table table-hover" id="myTable">
                            <thead class="table-primary">
                                <tr>
                                    <th class="text-center">번호</th>
                                    <th class="text-center">접수일</th>
                                    <th class="text-center">시작일</th>
                                    <th class="text-center">종료일</th>
                                    <th class="text-center">사용일수</th>
                                    <th class="text-center">성명</th>
                                    <th class="text-center">사유</th>
                                    <th class="text-center">결재상태</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $start_num = $total_row;
                                while ($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
                                    include "rowDBask.php";
                                    switch ($status) {
                                        case 'send':
                                            $statusstr = '결재요청';
                                            $statusClass = 'text-primary fw-bold';
                                            break;
                                        case 'ing':
                                            $statusstr = '결재중';
                                            $statusClass = '';
                                            break;
                                        case 'end':
                                            $statusstr = '결재완료';
                                            $statusClass = '';
                                            break;
                                        default:
                                            $statusstr = '';
                                            $statusClass = '';
                                            break;
                                    }
                                ?>
                                    <tr onclick="loadForm('update','<?=$num?>');">
                                        <td class="text-center"><?=$start_num?></td>
                                        <td class="text-center"><?=iconv_substr($registdate, 5, 5, "utf-8")?></td>
                                        <td class="text-center"><?=iconv_substr($al_askdatefrom, 5, 5, "utf-8")?></td>
                                        <td class="text-center"><?=iconv_substr($al_askdateto, 5, 5, "utf-8")?></td>
                                        <td class="text-center"><?=$al_usedday?></td>
                                        <td class="text-center"><?=$author?></td>
                                        <td class="text-center"><?=$al_content?></td>
                                        <td class="text-center <?=$statusClass?>"><?=$statusstr?></td>
                                    </tr>
                                <?php
                                    $start_num--;
                                }
                                ?>
                                <?php
                                    $start_num--;                           
                            } catch (PDOException $Exception) {
                                print "오류: " . $Exception->getMessage();
                            }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-5 mb-5">
                    </div>
                </div>
            </div>
            </div>
        </div>
		
 <!-- Modal -->
 <div id="myModal" class="modal" >
	<div class="modal-content" style="width:600px;">
		<div class="modal-header">
			<span class="modal-title">연차</span>
			<span class="close closeBtn">&times;</span>
		</div>
		<div class="modal-body">
			<div class="custom-card"></div>
		</div>
	</div>
</div>		
		
</form>
<div class="container-fluid mt-3 mb-3">
    <? include '../footer_sub.php'; ?>
</div>

<script>
var ajaxRequest_write = null;
var ajaxRequest = null;

var dataTable; // DataTables 인스턴스 전역 변수
var annualleavepageNumber; // 현재 페이지 번호 저장을 위한 전역 변수

$(document).ready(function() {
	// DataTables 초기 설정
	dataTable = $('#myTable').DataTable({
		"paging": true,
		"ordering": true,
		"searching": true,
		"pageLength": 50,
		"lengthMenu": [50, 100, 200, 500, 1000],
		"language": {
			"lengthMenu": "Show _MENU_ entries",
			"search": "Live Search:"
		},
		"order": [
			[0, 'desc']
		]
	});

	// 페이지 번호 복원 (초기 로드 시)
	var savedPageNumber = getCookie('annualleavepageNumber');
	if (savedPageNumber) {
		dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
	}

	// 페이지 변경 이벤트 리스너
	dataTable.on('page.dt', function() {
		var annualleavepageNumber = dataTable.page.info().page + 1;
		setCookie('annualleavepageNumber', annualleavepageNumber, 10); // 쿠키에 페이지 번호 저장
	});

	// 페이지 길이 셀렉트 박스 변경 이벤트 처리
	$('#myTable_length select').on('change', function() {
		var selectedValue = $(this).val();
		dataTable.page.len(selectedValue).draw(); // 페이지 길이 변경 (DataTable 파괴 및 재초기화 없이)

		// 변경 후 현재 페이지 번호 복원
		savedPageNumber = getCookie('annualleavepageNumber');
		if (savedPageNumber) {
			dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
		}
	});

});
	
	

function SearchEnter() {
	if (event.keyCode == 13) {
		document.getElementById('board_form').submit();
	}
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

            $("#closeBtn").on("click", function() {
                $("#myModal").hide();
            });
			
            $(".closeBtn").on("click", function() {
                $("#myModal").hide();
            });
			
			// PHP에서 생성된 JSON 데이터를 JavaScript 변수로 변환
			var employeeData = <?= $employee_json ?>;
			
			console.log('employeeData', employeeData);

			// 동적으로 추가된 select 요소에 이벤트 바인딩
			$(document).on('change', '#author', function() {
				var selectedName = $(this).val(); // 선택된 성명
				var partInput = $("#al_part"); // 부서 입력 필드
				partInput.val(employeeData[selectedName] || ''); // 해당 성명의 부서 값 설정
			});

            // 저장 버튼
            $("#saveBtn").on("click", function() {
                var num = $("#num").val();  
                var part = $("#part").val();  
                var status = $("#status").val();  
                var user_name = $("#user_name").val();     
                var admin = '<?= $admin ?>';

                if(status=='send' || admin === '1') {  
                    if(Number(num)>0) 
                        $("#mode").val('modify');     
                    else
                        $("#mode").val('insert');   

                    // savetext div의 HTML 내용을 가져옴
                    var htmlContent = document.getElementById('savetext').innerHTML;

                    $("#htmltext").val(encodeURIComponent(htmlContent));  

                    $.ajax({
                        url: "insert_ask.php",
                        type: "post",       
                        data: $("#board_form").serialize(),
                        dataType:"json",
                        success : function(data) {
                            console.log(data);
                                Toastify({
                                    text: "파일 저장완료",
                                    duration: 2000,
                                    close: true,
                                    gravity: "top",
                                    position: "center",
                                    style: {
                                        background: "linear-gradient(to right, #00b09b, #96c93d)"
                                    },
                                }).showToast();                            
							setTimeout(function() {			 
								 $("#myModal").modal('hide');
								 location.reload();           
							}, 1000);								
						   
                        },
                        error : function(jqxhr, status, error) {
                            console.log(jqxhr, status, error);
                        }                   
                    });     
                } else {            
                    Toastify({
                        text: "본인과 관리자만 수정 가능",
                        duration: 2000,
                        close: true,
                        gravity: "top",
                        position: "center",
                        style: {
                            background: "linear-gradient(to right, #00b09b, #96c93d)"
                        },
                    }).showToast();    
                }       
            });

            // 삭제 버튼
            $("#deleteBtn").click(function() {
                var level = '<?= $_SESSION["level"] ?>';                
                var user_id = '<?php echo $user_id; ?>';
                var author_id = '<?php echo $author_id; ?>';

                if (level !== '1' && user_id !== author_id) {
                    Swal.fire({
                        title: '삭제불가',
                        text: "작성자와 관리자만 삭제 가능합니다.",
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
                                url: "insert_ask.php",
                                type: "post",
                                data: formData,
                                dataType: "json",
                                success: function(data) {
									console.log(data);
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

                                    $("#myModal").modal('hide');
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
          
		  				
			// 신청일 변경시 종료일도 변경함
			$("#al_askdatefrom").change(function(){
			   var radioVal = $('input[name="al_item"]:checked').val();	
			   console.log(radioVal);
			   $('#al_askdateto').val($("#al_askdatefrom").val());      
			   
			   const result = getDateDiff($("#al_askdatefrom").val(), $("#al_askdateto").val()) + 1;
			   
			   switch(radioVal)
			   {
				  case '연차' :
					 $('#al_usedday').val(result);
					 break;
				  case '오전반차' :	 
				  case '오후반차' :	 	   
					 $('#al_usedday').val(result/2);
					 break;
				  case '오전반반차' :	 
				  case '오후반반차' :	 	   
					 $('#al_usedday').val(result/4);
					 break;
				  case '경조사' :	 	   
					 $('#al_usedday').val(0);
					 break;		 
				  case '예비군훈련' :	 	   
					 $('#al_usedday').val(0);
					 break;		 
				  case '공가' :	 	   
					 $('#al_usedday').val(0);
					 break;		 
			   }
					 
			});	
				
			$('input[name="al_item"]').change(function(){
			   var radioVal = $('input[name="al_item"]:checked').val();	
			   console.log(radioVal);
			   // $('#al_askdateto').val($("#al_askdatefrom").val());      
			   
			   const result = getDateDiff($("#al_askdatefrom").val(), $("#al_askdateto").val()) + 1;
			   
			   switch(radioVal)
			   {
				  case '연차' :
					 $('#al_usedday').val(result);
					 break;
				  case '오전반차' :	 
				  case '오후반차' :	 	   
					 $('#al_usedday').val(result/2);
					 break;
				  case '오전반반차' :	 
				  case '오후반반차' :	 	   
					 $('#al_usedday').val(result/4);
					 break;
				  case '경조사' :	 	   
					 $('#al_usedday').val(0);
					 break;
                     case '예비군훈련' :	 	   
					 $('#al_usedday').val(0);
					 break;		 
				  case '공가' :	 	   
					 $('#al_usedday').val(0);
					 break;	                     
			   }
			});	

			// 종료일을 변경해도 자동계산해 주기	
			$("#al_askdateto").change(function(){
			   var radioVal = $('input[name="al_item"]:checked').val();	
			   console.log(radioVal);
			   // $('#al_askdateto').val($("#al_askdatefrom").val());      
			   
			   const result = getDateDiff($("#al_askdatefrom").val(), $("#al_askdateto").val()) + 1;
			   
			   switch(radioVal)
			   {
				  case '연차' :
					 $('#al_usedday').val(result);
					 break;
				  case '오전반차' :	 
				  case '오후반차' :	 	   
					 $('#al_usedday').val(result/2);
					 break;
				  case '오전반반차' :	 
				  case '오후반반차' :	 	   
					 $('#al_usedday').val(result/4);
					 break;
				  case '경조사' :	 	   
					 $('#al_usedday').val(0);
					 break;		 
                     case '예비군훈련' :	 	   
					 $('#al_usedday').val(0);
					 break;		 
				  case '공가' :	 	   
					 $('#al_usedday').val(0);
					 break;	                     
			   }
			});			  
		  
		  
        },
        error: function(jqxhr, status, error) {
            console.log("AJAX Error: ", status, error);
        }
    });
}

$(document).ready(function() {
    $("#writeBtn").on("click", function() {
        loadForm('insert');
    });

    $("#closeModalBtn").click(function() {
        $('#myModal').modal('hide');
    });

    $(".close").click(function() {
        $('#myModal').modal('hide');
    });

    $("#openAlmemberBtn").click(function() {
        popupCenter('../almember/list.php', '직원 연차 등록', 900, 800);
    });

    $("#searchBtn").click(function() {
        document.getElementById('board_form').submit();
    });        

    function restorePageNumber() {
        var savedPageNumber = getCookie('annualleavepageNumber');
        location.reload(true);
    }        
});


$(document).ready(function(){    
    var loader = document.getElementById('loadingOverlay');
    loader.style.display = 'none';

    var modal = document.getElementById("myModal");
    var span = document.getElementsByClassName("close")[0];

    span.onclick = function() {
        modal.style.display = "none";
    }
});


// 두날짜 사이 일자 구하기 
const getDateDiff = (d1, d2) => {
const date1 = new Date(d1);
const date2 = new Date(d2);

let count = 0;
const oneDay = 24 * 60 * 60 * 1000; // 하루의 밀리세컨드 수

while (date1 < date2) {
const dayOfWeek = date1.getDay(); // 요일 (0:일, 1:월, ..., 6:토)

// 토요일(6)이나 일요일(0)이 아닌 경우에만 count 증가
if (dayOfWeek !== 0 && dayOfWeek !== 6) {
  count++;
}

date1.setTime(date1.getTime() + oneDay); // 다음 날짜로 이동
}

return count;
}

$(document).ready(function () {
    $('#yearSelect').change(function () {
        const selectedYear = $(this).val();
        $.ajax({
            url: '/almember/update_annual_leave.php', // 데이터 업데이트를 처리할 PHP 파일
            method: 'POST',
            data: { year: selectedYear, user_name: "<?=$user_name?>" },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    $('#totalDays').text(response.total);
                    $('#usedDays').text(response.usedDays);
                    $('#remainingDays').text(response.remainingDays);
                } else {
                    alert('데이터를 업데이트하는 동안 오류가 발생했습니다.');
                }
            },
            error: function(jqxhr, status, error) {
                console.log(jqxhr, status, error);				
                alert('서버 요청 중 오류가 발생했습니다.');
            }
        });
    });
});

$(document).ready(function(){
	saveLogData('직원 연차'); 
});
	
</script>
</body>
</html>
