<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");

if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php");
    exit;
}

// 에러 표시 설정
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';
$title_message = '월별 수입/지출 예상내역서';
?>

<link href="css/style.css" rel="stylesheet">
<title> <?=$title_message?> </title>
<style>
    /* 테이블에 테두리 추가 */
    #detailTable, #detailTable th, #detailTable td {
        border: 1px solid black;
        border-collapse: collapse;
    }

    /* 테이블 셀 패딩 조정 */
    #detailTable th, #detailTable td {
        padding: 8px;
        text-align: center;
    }
</style>

</head>

<body>
<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php');
$year = isset($_REQUEST['year']) ? $_REQUEST['year'] : date('Y');
$startMonth = isset($_REQUEST['startMonth']) ? $_REQUEST['startMonth'] : 1;
$endMonth = isset($_REQUEST['endMonth']) ? $_REQUEST['endMonth'] : date('m');

$startDate = "$year-$startMonth-01";
$endDate = date("Y-m-t", strtotime("$year-$endMonth-01"));

$tablename = 'account';

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

// 수입 내역 조회
$incomeSql = "
    SELECT content, SUM(amount) as totalAmount
    FROM $tablename
    WHERE inoutsep = '수입'
    AND registDate BETWEEN :startDate AND :endDate
    AND is_deleted = '0'
    GROUP BY content
";
$incomeStmt = $pdo->prepare($incomeSql);
$incomeStmt->bindParam(':startDate', $startDate);
$incomeStmt->bindParam(':endDate', $endDate);
$incomeStmt->execute();
$incomeData = $incomeStmt->fetchAll(PDO::FETCH_ASSOC);

// 지출 내역 조회
$expenseSql = "
    SELECT content, SUM(amount) as totalAmount
    FROM $tablename
    WHERE inoutsep = '지출'
    AND registDate BETWEEN :startDate AND :endDate
    AND is_deleted = '0'
    GROUP BY content
";
$expenseStmt = $pdo->prepare($expenseSql);
$expenseStmt->bindParam(':startDate', $startDate);
$expenseStmt->bindParam(':endDate', $endDate);
$expenseStmt->execute();
$expenseData = $expenseStmt->fetchAll(PDO::FETCH_ASSOC);

// 월수익 계산
$totalIncome = array_sum(array_column($incomeData, 'totalAmount'));
$totalExpense = array_sum(array_column($expenseData, 'totalAmount'));
$netIncome = $totalIncome - $totalExpense;

?>


    <div class="container-fluid">
        <!-- Modal -->
        <div id="myModal" class="modal">
            <div class="modal-content" style="width:800px;">
                <div class="modal-header">
                    <span class="modal-title"> <?=$title_message?> </span>
                    <span class="close">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="custom-card"></div>
                </div>
            </div>
        </div>
    </div>


<div class="container mt-5">
		<div class="card justify-content-center text-center mt-5">
			<div class="card-header">
				<span class="text-center fs-5">  <?=$title_message?> 
					<button type="button" class="btn btn-dark btn-sm me-1" onclick='location.reload()'>  <i class="bi bi-arrow-clockwise"></i> </button>      
				</span>
			</div>
			<div class="card-body">
			
		<!-- <div class="d-flex p-1 m-1 mt-1 mb-1 justify-content-center align-items-center">       
			<span class="badge bg-danger fs-3 me-3"> 현재개발중입니다. (미완성) </span>                    
		</div>  -->
				<div class="row mb-3">					                 
					<div class="d-flex justify-content-center align-items-center">					
						<select id="year" name="year" class="form-control me-2" style="width:80px;" onchange="loadDetails()">
							<?php for ($i = date('Y'); $i >= 2024; $i--): ?>
								<option value="<?=$i?>" <?=($year == $i) ? 'selected' : ''?>><?=$i?>년</option>
							<?php endfor; ?>
						</select>                
					  
						<select id="startMonth" name="startMonth" class="form-control me-1"  style="width:60px;" onchange="loadDetails()">
							<?php for ($i = 1; $i <= 12; $i++): ?>
								<option value="<?=$i?>" <?=($startMonth == $i) ? 'selected' : ''?>><?=$i?>월</option>
							<?php endfor; ?>
						</select>
						~               &nbsp;  
						<select id="endMonth" name="endMonth" class="form-control me-5 "  style="width:60px;"  onchange="loadDetails()">
							<?php for ($i = 1; $i <= 12; $i++): ?>
								<option value="<?=$i?>" <?=($endMonth == $i) ? 'selected' : ''?>><?=$i?>월</option>
							<?php endfor; ?>
						</select>					  
					  <button id="newBtn" type="button" class="btn btn-dark btn-sm me-2"> <i class="bi bi-pencil-square"></i> 신규 </button>									
					</div>
				  </div>

            <div class="row mb-3">
                <div class="d-flex justify-content-center">
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <button class="btn btn-outline-primary btn-sm me-1" onclick="loadSpecificMonth('<?=$i?>')">
                            <?=$i?>월
                        </button>
                    <?php endfor; ?>
                </div>
            </div>

            <table class="table table-hover" id="detailTable">
                <thead class="table-info">
                    <tr>
                        <th colspan="2" class="text-center">수입</th>
                        <th colspan="2" class="text-center">지출</th>
                    </tr>
                    <tr>
                        <th class="text-center">항목</th>
                        <th class="text-center">금액</th>
                        <th class="text-center">항목</th>
                        <th class="text-center">금액</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $maxRows = max(count($incomeData), count($expenseData));
                    for ($i = 0; $i < $maxRows; $i++):
                        $incomeContent = isset($incomeData[$i]) ? $incomeData[$i]['content'] : '';
                        $incomeAmount = isset($incomeData[$i]) ? number_format($incomeData[$i]['totalAmount']) : '';

                        $expenseContent = isset($expenseData[$i]) ? $expenseData[$i]['content'] : '';
                        $expenseAmount = isset($expenseData[$i]) ? number_format($expenseData[$i]['totalAmount']) : '';
                    ?>
                    <tr>
                        <td class="text-center"><?=$incomeContent?></td>
                        <td class="text-end text-primary"><?=$incomeAmount?></td>
                        <td class="text-center"><?=$expenseContent?></td>
                        <td class="text-end text-danger"><?=$expenseAmount?></td>
                    </tr>
                    <?php endfor; ?>
                </tbody>
				<tfoot class="table-secondary">
					<tr>
						<th class="text-end" > 수입 합계 &nbsp; </th>
						<th class="text-end text-primary"><?=number_format($totalIncome)?></th>					
						<th class="text-end"> 지출 합계 &nbsp; </th>
						<th class="text-end text-danger"><?=number_format($totalExpense)?></th>
					</tr>
					<tr>
						<th class="text-end" colspan="3"> 월수익 &nbsp; </th>
						<th class="text-end"><?=number_format($netIncome)?></th>
					</tr>
				</tfoot>

            </table>
        </div>
    </div>
</div>
</form>
</body>
</html>

<script>

let isSaving = false;
var ajaxRequest = null;

// 페이지 로딩
$(document).ready(function(){    
	var loader = document.getElementById('loadingOverlay');
	loader.style.display = 'none';
	
    $("#newBtn").on("click", function() {
        loadForm('insert');
    });	
});



//  모달창에 내용을 넣는 구조임 모달을 부르고 내용을 동적으로 넣는다.
function loadForm(mode, num = null) {
    if (num == null) {
        $("#mode").val('insert');
    } else {
        $("#mode").val('update');
        $("#num").val(num);
    }

    if (ajaxRequest !== null) {
        ajaxRequest.abort();
    }
    ajaxRequest = $.ajax({
        type: "POST",
        url: "fetch_modal_plan.php",
        data: { mode: mode, num: num },
        dataType: "html",
        success: function(response) {
            document.querySelector(".modal-body .custom-card").innerHTML = response;			

            $("#myModal").show();
                        
			const expenseOptions = {
				'급여(인건비)': '직원 급여',
				'접대비': '경조사비용',
				'통신비': '전화요금, 인터넷요금',
				'세금과공과금': '등록면허세, 취득세, 재산세등 각종세금',
				'차량유지비': '유류대, 통행료',
				'보험료': '차량보험료, 화재보험료등',
				'운반비': '택배운반비외 각종운반비',
				'소모품비': '각종 소모품 비용',
				'수수료비용': '이체수수료, 등기수수료등',
				'복리후생비': '직원 식대외 직원 작업복등',
				'개발비': '프로그램 개발비용',
				'이자비용': '이자비용',
				'카드대금': '카드대금',
				'통관비': '통관비',
				'자재비': '자재비',
				'기계장치' : '기계구입'
			};

			const incomeOptions = {
				'거래처 수금': '거래처에서 입금한 금액',
				'최초 현금 입력': '금전출납부 시작'
			};
						
			function updateDescription() {
				const contentSelect = document.getElementById('content');
				const descriptionDiv = document.getElementById('content_description');
				const selectedValue = contentSelect.value;
				
				// 수입인지 지출인지에 따라 설명 변경
				const descriptions = document.querySelector('input[name="inoutsep"]:checked').value === '수입' ? incomeOptions : expenseOptions;
				descriptionDiv.innerText = descriptions[selectedValue] || '';

				// '거래처 수금'이 선택되었을 때 전화번호 검색 화면을 띄움
				if (selectedValue === '거래처 수금') {
					phonebookBtn('');
				}
			}

			function updateContentOptions() {
				const contentSelect = document.getElementById('content');
				if (contentSelect) {
					contentSelect.innerHTML = '';

					const options = document.querySelector('input[name="inoutsep"]:checked').value === '수입' ? incomeOptions : expenseOptions;

					for (const [value, text] of Object.entries(options)) {
						const option = document.createElement('option');
						option.value = value;
						option.text = value;
						contentSelect.appendChild(option);
					}

					updateDescription();
				}
			}
			
			
            // 추가된 스크립트를 실행하도록 보장
            // updateContentOptions();					
            
            $(document).on("click", "#closeBtn", function() {
                $("#myModal").hide();
            });


    $(document).on("click", "#saveBtn", function() {
        // if (isSaving) return;
        // isSaving = true;

            // AJAX 요청을 보냄
        if (ajaxRequest !== null) {
            ajaxRequest.abort();
        }
        ajaxRequest = $.ajax({
                url: "/account/insert_plan.php",
                type: "post",
                data: {
                    mode: $("#mode").val(),
                    num: $("#num").val(),
                    update_log: $("#update_log").val(),
                    registDate: $("#registDate").val(),
                    inoutsep: $("input[name='inoutsep']:checked").val(),
                    content: $("#content").val(),
                    amount: $("#amount").val(),
                    memo: $("#memo").val(),
                    first_writer: $("#first_writer").val(),
                    content_detail: $("#content_detail").val(),
                    bankbook: $("#bankbook").val(),
                    secondordnum: $("#secondordnum").val()
                },
                dataType: "json",
                success: function(response) {
                    Toastify({
                        text: "저장 완료",
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "center",
                        backgroundColor: "#4fbe87",
                    }).showToast();
                                    
                    setTimeout(function() {
                        $("#myModal").hide();
                        location.reload();
                    }, 1500); // 1.5초 후 실행
                    
                },
                error: function(jqxhr, status, error) {
                    console.log("AJAX Error: ", status, error);
                    // isSaving = false;
                }
            });
        });

        $(document).on("click", "#deleteBtn", function() {    
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
                        url: "/account/insert_plan.php",
                        type: "post",
                        data: formData,
                        success: function(response) {
                            Toastify({
                                text: "파일 삭제완료",
                                duration: 2000,
                                close: true,
                                gravity: "top",
                                position: "center",
                                backgroundColor: "#4fbe87",
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

        
        $(".close").on("click", function() {
            $("#myModal").hide();
        });

        // 항목 선택 변경 시 설명 업데이트
        $(document).on("change", "#content", updateDescription);

        $(document).on("change", "input[name='inoutsep']", updateContentOptions);

        },
        error: function(jqxhr, status, error) {
            console.log("AJAX error in loadForm:", status, error);
        }
    });
}



// 기존 loadDetails 함수 유지
function loadDetails() {
    const year = document.getElementById('year').value;
    const startMonth = document.getElementById('startMonth').value;
    const endMonth = document.getElementById('endMonth').value;

    window.location.href = `MonthAccountPlan.php?year=${year}&startMonth=${startMonth}&endMonth=${endMonth}`;
}

// 새로운 함수 추가: 특정 월 선택 시 호출
function loadSpecificMonth(month) {
    const year = document.getElementById('year').value;
    window.location.href = `MonthAccountPlan.php?year=${year}&startMonth=${month}&endMonth=${month}`;
}
</script>
