<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php");
    exit;
}   
include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';
$title_message = '거래처 수금현황'; 

?>
 
<link href="css/style.css" rel="stylesheet">   
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
        $fromdate = date("Y-m-01", strtotime("-1 month"));	
        $todate = $currentDate;
    }
}

$Transtodate = $todate;

$tablename = 'account';

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();
 
$order = " ORDER BY total_amount DESC ";
    
if (checkNull($search)) {
    $sql = "SELECT content_detail, 
                   SUM(CAST(REPLACE(amount, ',', '') AS DECIMAL(15,2))) as total_amount,
                   COUNT(*) as transaction_count,
                   MIN(registDate) as first_date,
                   MAX(registDate) as last_date
            FROM {$DB}.{$tablename}  
            WHERE searchtag LIKE '%$search%' AND (is_deleted IS NULL or is_deleted =0) 
            AND content='거래처 수금' AND registDate BETWEEN '$fromdate' AND '$todate'
            GROUP BY content_detail " . $order;    
} else {
    $sql = "SELECT content_detail, 
                   SUM(CAST(REPLACE(amount, ',', '') AS DECIMAL(15,2))) as total_amount,
                   COUNT(*) as transaction_count,
                   MIN(registDate) as first_date,
                   MAX(registDate) as last_date
            FROM {$DB}.{$tablename}  
            WHERE (is_deleted IS NULL or is_deleted =0) AND content='거래처 수금' 
            AND registDate BETWEEN '$fromdate' AND '$todate'
            GROUP BY content_detail " . $order;   
}

// 연도 옵션 생성 (현재년도 + 과거 3년)
$current_year = date('Y');
$year_options = '';
for ($i = 0; $i < 4; $i++) {
    $year = $current_year - $i;
    $selected = ($year == $selected_year) ? 'selected' : '';
    $year_options .= "<option value='$year' $selected>" . $year . "년</option>";
}

try {      
    $stmh = $pdo->query($sql);
    $total_row = $stmh->rowCount();   
    // print $total_row;	
?>    

<form id="board_form" name="board_form" method="post" enctype="multipart/form-data">             
    <input type="hidden" id="mode" name="mode" value="<?=$mode?>">             
    <input type="hidden" id="num" name="num"> 
    <input type="hidden" id="tablename" name="tablename" value="<?=$tablename?>">                 
    <input type="hidden" id="header" name="header" value="<?=$header?>">      

<div class="container-fluid">
	<!-- Modal -->
	<div id="myModal" class="modal">
		<div class="modal-content"  style="width:900px;">
			<div class="modal-header">
				<span class="modal-title">수금</span>
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
    <div class="card-header">
        <span class="text-center fs-5 me-4"><?=$title_message?></span>    
		<button type="button" class="btn btn-dark btn-sm me-1" onclick='location.href="list.php?header=header"'> 
			<i class="bi bi-arrow-clockwise"></i>            
		</button>		                         
        <button type="button" class="btn btn-dark btn-sm mx-1" onclick="location.href='../motor/customer.php'"> <i class="bi bi-journal-x"></i> 거래원장 </button>    
        <button type="button" class="btn btn-dark btn-sm mx-1" onclick="location.href='../motor/month_sales.php?header=header'"> <i class="bi bi-journal-x"></i> 판매일괄회계반영 </button>    
        <button type="button" class="btn btn-danger btn-sm mx-1" onclick="location.href='../motor/receivable.php?header=header'"> <i class="bi bi-journal-x"></i> 미수금 </button>    
        <?php if($header !== 'header') 
                print '<button id="closeBtn" type="button" class="btn btn-outline-dark btn-sm mx-1"> <i class="bi bi-x-lg"></i> 창닫기 </button>';
        ?>                       
    </div>
     <div class="card-body">  
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- 검색 결과 개수 -->
            <div class="text-center mb-3">
                <span class="text-center text-primary fs-6">▷ <?= $total_row ?> 건</span>
            </div>

            <!-- 검색 타입 선택 -->
            <div class="row justify-content-center mb-3">
                <div class="col-auto">
                    <div class="search-type-container">
                        <label class="me-3">
                            <input type="radio" name="search_type" value="year" <?= $search_type === 'year' ? 'checked' : '' ?> onchange="toggleSearchType()"> 연도별
                        </label>
                        <label class="me-3">
                            <input type="radio" name="search_type" value="month" <?= $search_type === 'month' ? 'checked' : '' ?> onchange="toggleSearchType()"> 월별
                        </label>
                        <label>
                            <input type="radio" name="search_type" value="period" <?= $search_type === 'period' ? 'checked' : '' ?> onchange="toggleSearchType()"> 기간별
                        </label>
                    </div>
                </div>
            </div>

            <!-- 동적 검색 컨트롤 -->
            <div class="row justify-content-center mb-3">
                <div class="col-auto">
                    <!-- 연도별 검색 -->
                    <div class="year-select">
                        <select id="selected_year" name="selected_year" class="form-select form-select-sm">
                            <?= $year_options ?>
                        </select>
                    </div>

                    <!-- 월별 검색 -->
                    <div class="month-select">
                        <input type="month" id="selected_month" name="selected_month" class="form-control" value="<?=$selected_month?>">
                    </div>

                    <!-- 기간별 검색 -->
                    <div class="period-select">
                        <div class="d-flex align-items-center">
                            <input type="date" id="fromdate" name="fromdate" class="form-control me-2" value="<?=$fromdate?>">
                            <span class="me-2">~</span>
                            <input type="date" id="todate" name="todate" class="form-control" value="<?=$todate?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 검색어 입력 및 검색 버튼 -->
            <div class="row justify-content-center mb-3">
                <div class="col-auto">
                    <div class="d-flex align-items-center">
                        <div class="inputWrap30 me-2">            
                            <input type="text" id="search" class="form-control" style="width:150px;" name="search" value="<?=$search?>" onKeyPress="if (event.keyCode==13){ enter(); }" placeholder="거래처명 검색">
                            <button class="btnClear"></button>
                        </div>
                        <button class="btn btn-outline-dark btn-sm" type="button" id="searchBtn"> 
                            <i class="bi bi-search"></i> 검색
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>                
    
    <div class="table-responsive mt-2">   
       <table class="table table-hover w-75" id="myTable">         
            <thead class="table-primary">
                 <th class="text-center w60px" >번호</th>
                 <th class="text-center w200px" >거래처명</th>
                 <th class="text-center w130px" >누계 수금액</th>
                 <th class="text-center w100px" >거래건수</th>
                 <th class="text-center w120px" >최초 수금일</th>
                 <th class="text-center w120px" >최근 수금일</th>                 
            </thead>
            <tbody>                  
            <?php          
            $start_num = 1;                  
            while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
                $content_detail = $row['content_detail'];
                $total_amount = floatval($row['total_amount']);
                $transaction_count = $row['transaction_count'];
                $first_date = $row['first_date'];
                $last_date = $row['last_date'];
            ?>                     
            <tr>
                <td class="text-center"><?= $start_num ?></td>
                <td class="text-start text-primary fw-bold"><?= $content_detail ?></td>                
                <td class="text-end text-dark fw-bold">
					<?= !empty($total_amount) ? number_format($total_amount) : '0' ?>
				</td>
                <td class="text-center"><?= $transaction_count ?></td>
                <td class="text-center"><?= $first_date ?></td>
                <td class="text-center"><?= $last_date ?></td>
            </tr>
        <?php
            $start_num++;
                 } 
              } catch (PDOException $Exception) {
                  print "오류: ".$Exception->getMessage();
              }  
        ?>         
      </tbody>
		<tfoot class="table-secondary">
			<tr>
				<th class="text-end" colspan="2"> 합계 &nbsp; </th>
				<th class="text-end" id="totalPaymentAmount"></th>
				<th class="text-end" id="totalTransactionCount"></th>
				<th class="text-end" colspan="2"></th>
			</tr>
		</tfoot>

     </table>
     </table>
    </div>
    </div>
    </div>        
</form>

</body>
</html>


<script>
var ajaxRequest = null;
var itemData = [];
var lotnumData = [];

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

$(document).ready(function() {
    fetchItemData();
    toggleSearchType(); // 초기 로드 시 검색 타입에 맞는 컨트롤 표시
});

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

            // $(document).on('input', '.inputcode .inputitemname .inputlot', function() {
            $(document).on('input', '.inputcode ', function() {
                initializeAutocomplete(this);
            });     
			
            $(document).on('input', '.inputitemname ', function() {
                initializeAutocomplete(this);
            });       
			
            $(document).on('input', '.inputlot ', function() {
                initializeAutocomplete(this);
            });       
			

            // 저장 버튼
            $("#saveBtn").on("click", function() {
                var header = $("#header").val();
                var formData = $("#board_form").serialize();

                $.ajax({
                    url: "process.php",
                    type: "post",
                    data: formData,
                    success: function(response) {
                        // console.log(response);
                        Toastify({
                            text: "저장완료",
                            duration: 3000,
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
        "pageLength": 100,
        "lengthMenu": [ 100, 200, 500, 1000],
        "language": {
            "lengthMenu": "Show _MENU_ entries",
            "search": "Live Search:"
        },
        "order": [[2, 'desc']], // 누계 수금액 기준으로 내림차순 정렬
        "dom": 't<"bottom"ip>', // search 창과 lengthMenu 숨기기
        "footerCallback": function ( row, data, start, end, display ) {
            var api = this.api(), data;
            
            // 합계를 계산하는 함수
            var intVal = function (i) {
                return typeof i === 'string' ?
                    i.replace(/[\$,]/g, '')*1 :
                    typeof i === 'number' ?
                        i : 0;
            };
            
            // 누계 수금액 합계 계산 (컬럼 2)
            totalPaymentAmount = api.column(2, { page: 'current'}).data().reduce(function (a, b) { return intVal(a) + intVal(b); }, 0);

            // 거래건수 합계 계산 (컬럼 3)
            totalTransactionCount = api.column(3, { page: 'current'}).data().reduce(function (a, b) { return intVal(a) + intVal(b); }, 0);

            // 합계 출력
            $('#totalPaymentAmount').html(numberWithCommas(totalPaymentAmount));
            $('#totalTransactionCount').html(numberWithCommas(totalTransactionCount));
        }
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
    // if (savedPageNumber) {
    // dataTable.page(parseInt(savedPageNumber) - 1).draw('page');
    // }
    location.reload(true);
}
</script>

<!-- 페이지로딩 및 Modal Script -->
<script>
$(document).ready(function(){    
    var loader = document.getElementById('loadingOverlay');
    loader.style.display = 'none';

    var modal = document.getElementById("myModal");
    var span = document.getElementsByClassName("close")[0];

    span.onclick = function() {
        modal.style.display = "none";
    }

    // 모달창 밖을 클릭하면 닫히는 현상을 빼려고 주석처리
    // window.onclick = function(event) {
        // if (event.target == modal) {
            // modal.style.display = "none";
        // }
    // }

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
    // 숫자와 마이너스 기호 이외의 문자는 제거
    obj.value = obj.value.replace(/[^0-9\-]/g, '');
    
    // 콤마를 제거하고 숫자를 포맷팅
    let value = obj.value.replace(/,/g, '');

    // 입력값이 음수인지 확인
    let isNegative = value.charAt(0) === '-';
    
    // 숫자 포맷팅 (음수일 경우에도 올바르게 포맷팅)
    obj.value = (isNegative ? '-' : '') + value.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}


// Prevent form submission on Enter key
$(document).on("keypress", "input", function(event) {
	return event.keyCode != 13;
});		

function phonebookBtn(searchfield)
{	    
    var search = $("#" + searchfield).val();				
    href = '../phonebook/list.php?getmoney=ok&search=' + search ;				
	popupCenter(href, '전화번호 검색', 1600, 800);
}


function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
</script>