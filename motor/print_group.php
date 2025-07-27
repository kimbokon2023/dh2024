<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");

if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php");
    exit;
}

include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';

$title_message = '출고증 묶음 출력';
$tablename = 'motor';
?>

<style>
table, th, td {
	border: 1px solid black !important;
	font-size: 13px !important;
}
    @media print {
        body {
            width: 210mm;
            height: 297mm;
            margin: 5mm;
            font-size: 10pt;
        }
        .table {
            width: 100%;
            table-layout: fixed;
        }
        .table th, .table td {
            padding: 2px;
            border: 1px solid #ddd;
        }
        .text-center {
            text-align: center;
        }
        .fw-bold {
            font-weight: bold;
        }
    }
	
  
input[type="checkbox"] {
    transform: scale(1.6); /* 크기를 1.5배로 확대 */
    margin-right: 10px;   /* 확대 후의 여백 조정 */
}
	
	
</style>

</head>

<title> <?=$title_message?> </title>

<body>

<html lang="ko">

<?php
$fromdate = isset($_REQUEST['fromdate']) ? $_REQUEST['fromdate'] : '';
$todate = isset($_REQUEST['todate']) ? $_REQUEST['todate'] : '';

// 현재 날짜
$currentDate = date("Y-m-d");

// fromdate 또는 todate가 빈 문자열이거나 null인 경우
if ($fromdate === "" || $fromdate === null || $todate === "" || $todate === null) {
    $fromdate = date("Y-m-d", strtotime($currentDate)); // 오늘날짜
    $todate = $currentDate; // 현재 날짜
    $Transtodate = $todate;
} else {
    // fromdate와 todate가 모두 설정된 경우 (기존 로직 유지)
    $Transtodate = $todate;
}

$now = date("Y-m-d"); // 현재일자 변수지정

// 납기일(deadline)이 오늘 자정부터 미래인 경우를 조건으로 설정
$common = " WHERE deadline between '$fromdate' and '$Transtodate' AND is_deleted IS NULL  ORDER BY orderdate ASC ";

$sql = "select * from " . $DB . "." . $tablename . " " . $common;

$nowday = date("Y-m-d");   // 현재일자 변수지정
$counter = 1;

$sum = array();

$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';

?>

<div class="container mt-2">

    <form id="board_form" name="board_form" method="post" action="print_group.php">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-end mt-1 m-1">
                    <button type="button" class="btn btn-secondary btn-sm me-1" id="refresh"> <i class="bi bi-arrow-clockwise"></i> 새로고침 </button>
                    <button type="button" class="btn btn-dark btn-sm me-1" id="generate-pdf-btn"> PDF 저장 </button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="self.close();"> <i class="bi bi-x-lg"></i> 닫기 </button>&nbsp;
                </div>
                <div class="d-flex p-1 m-1 mb-1 justify-content-center align-items-center">
                    <span id="showdate" class="btn btn-dark btn-sm"> 기간 </span>&nbsp;
                    <div id="showframe" class="card">
                        <div class="card-header" style="padding:2px;">
                            <div class="d-flex justify-content-center align-items-center">
                                기간 설정
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-center align-items-center">
                                <button type="button" class="btn btn-outline-success btn-sm me-1 change_dateRange" onclick='alldatesearch()'> 전체 </button>
                                <button type="button" id="preyear" class="btn btn-outline-primary btn-sm me-1 change_dateRange" onclick='pre_year()'> 전년도 </button>
                                <button type="button" id="three_month" class="btn btn-dark btn-sm me-1 change_dateRange" onclick='pre_month()'> 전월 </button>
                                <button type="button" id="prepremonth" class="btn btn-dark btn-sm me-1 change_dateRange" onclick='dayBeforeYesterday()'> 전전일 </button>
                                <button type="button" id="premonth" class="btn btn-dark btn-sm me-1 change_dateRange" onclick='yesterday()'> 전일 </button>
                                <button type="button" class="btn btn-outline-danger btn-sm me-1 change_dateRange" onclick='this_today()'> 오늘 </button>
                                <button type="button" id="thismonth" class="btn btn-dark btn-sm me-1 change_dateRange" onclick='this_month()'> 당월 </button>
                                <button type="button" id="thisyear" class="btn btn-dark btn-sm me-1 change_dateRange" onclick='this_year()'> 당해년도 </button>
                            </div>
                        </div>
                    </div>
                    <input type="date" id="fromdate" name="fromdate" class="form-control" style="width:100px;" value="<?=$fromdate?>"> &nbsp; ~ &nbsp;
                    <input type="date" id="todate" name="todate" class="form-control" style="width:100px;" value="<?=$todate?>"> &nbsp;
                    <button id="searchBtn" type="button" class="btn btn-dark btn-sm"> <i class="bi bi-search"></i> 검색 </button>
                </div>
            </div>
        </div>
    </form>

    <div id="content-to-print">
        <br>
        <div class="container-fluid mt-2">
            <div class="d-flex align-items-center justify-content-center mb-1 m-2">
                <h3> <?=$title_message?> </h3>
            </div>
            <div class="d-flex align-items-center justify-content-center mb-1 m-1">
                <table class="table table-hover" id="myTable">
                    <thead class="table-primary">
                        <tr>
                            <th class="text-center align-middle" style="width:120px;">
                                <input type="checkbox" id="select-all"> 
								<label for="select-all" > 전체 선택 </label>
                            </th>
                            <th class="text-center align-middle" style="width:80px;">출고예정</th>
                            <th class="text-center align-middle" style="width:150px;">발주처</th>
                            <th class="text-center align-middle" style="width:150px;">배송방법</th>
                            <th class="text-center align-middle" style="width:300px;">현장명</th>
                            <th class="text-center align-middle" style="width:300px;">내역</th>
                            <th class="text-center align-middle" style="width:200px;">전달사항</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php
                        try {
                            $stmh = $pdo->query($sql); // 검색조건에 맞는글 stmh
                            $rowNum = $stmh->rowCount();

                            while ($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
								include '_contentload.php';


								if (strpos($deliverymethod, '화물') === true)
								{
									// 상차지에 화물지점표기함
								  $address = $delbranch . (!empty($delbranchaddress) ? ' (' . $delbranchaddress . ')' : '');
								}			
								
								if(empty($loadplace))
										  $loadplace = '(주)대한 본사';								

                                print '<tr>';
                                print '<td class="text-center align-middle"><input type="checkbox" class="record-checkbox" value="' . $num . '"></td>';
                                print '<td class="text-center align-middle">' . iconv_substr($deadline, 5, 5, "utf-8") . ' </td>';
                                print '<td class="text-center align-middle">' . $secondord . ' </td>';
								
								if ($deliverymethod == '선/대신화물') {
									echo '<td class="text-center align-middle"><span class="badge bg-danger">' . $deliverymethod . '</span></td>';
								} else if ($deliverymethod == '선/경동화물' || $deliverymethod == '착/경동화물') {
									echo '<td class="text-center align-middle"><span class="badge bg-primary">' . $deliverymethod . '</span></td>';
								} else if ($deliverymethod == '배차') {
									echo '<td class="text-center align-middle"><span class="badge bg-success">' . $deliverymethod . '[' . $delcompany . ']</span></td>';
								} else {
									echo '<td class="text-center align-middle">' . $deliverymethod . '</td>';
								}		
							
                                
                                print '<td class="text-start align-middle">' . $workplacename_arr[$counter] . ' </td>';
                                print '<td class="text-start align-middle">' . $contentslist_arr[$counter] . ' </td>';
                                print '<td class="text-center align-middle">' . $comment_arr[$counter] . ' </td>';
                                print '</tr>';

                                $counter++;
                            }
                        } catch (PDOException $Exception) {
                            print "오류: " . $Exception->getMessage();
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div> <!-- end of content-to-print -->

</body>
</html>

<!-- 페이지로딩 -->
<script>
// 페이지 로딩
$(document).ready(function() {
    var loader = document.getElementById('loadingOverlay');
    loader.style.display = 'none';
});

$(document).ready(function() {
    $("#refresh").click(function() {
        location.reload();
    }); // refresh
});

document.getElementById('select-all').addEventListener('change', function() {
    var checkboxes = document.querySelectorAll('.record-checkbox');
    for (var checkbox of checkboxes) {
        checkbox.checked = this.checked;
    }
});

document.getElementById('generate-pdf-btn').addEventListener('click', function() {
    var selectedRecords = [];
    var checkboxes = document.querySelectorAll('.record-checkbox:checked');
    for (var checkbox of checkboxes) {
        selectedRecords.push(checkbox.value);
    }

    if (selectedRecords.length > 0) {
        generatePDFs(selectedRecords);
    } else {
        alert('Please select at least one record.');
    }
});

function generatePDFs(recordIds) {
    // 팝업 창 열기
    var popup = window.open("", "popupForm", "width=1000,height=800");

    // 팝업 창 내부에 폼 생성
    var form = popup.document.createElement("form");
    form.setAttribute("method", "post");
    form.setAttribute("action", "invoice_content.php");

    // 레코드 ID를 히든 필드로 추가
    recordIds.forEach(function(recordId) {
        var hiddenField = popup.document.createElement("input");
        hiddenField.setAttribute("type", "hidden");
        hiddenField.setAttribute("name", "recordIds[]");
        hiddenField.setAttribute("value", recordId);
        form.appendChild(hiddenField);
    });

    // 폼을 팝업 창에 추가
    popup.document.body.appendChild(form);

    // 폼 제출
    form.submit();
}



function SearchEnter() {
    if (event.keyCode == 13) {
        saveSearch();
    }
}

function saveSearch() {
    document.getElementById('board_form').submit();
}
</script>


<script> 
var dataTable; // DataTables 인스턴스 전역 변수
var print_grouppageNumber; // 현재 페이지 번호 저장을 위한 전역 변수

$(document).ready(function() {			
    // DataTables 초기 설정
    dataTable = $('#myTable').DataTable({
        "paging": true,
        "ordering": true,
        "searching": true,
        "pageLength": 500,
        "lengthMenu": [500, 1000],
        "language": {
            "lengthMenu": "Show _MENU_ entries",
            "search": "Live Search:"
        },
        "order": [[1, 'desc']], // 출고예정기준 내림정렬
        "dom": 't<"bottom"ip>' // search 창과 lengthMenu 숨기기		
    });

    // 페이지 번호 복원 (초기 로드 시)
    var savedPageNumber = getCookie('print_grouppageNumber');
    if (savedPageNumber) {
        dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
    }

    // 페이지 변경 이벤트 리스너
    dataTable.on('page.dt', function() {
        var print_grouppageNumber = dataTable.page.info().page + 1;
        setCookie('print_grouppageNumber', print_grouppageNumber, 10); // 쿠키에 페이지 번호 저장
    });

    // 페이지 길이 셀렉트 박스 변경 이벤트 처리
    $('#myTable_length select').on('change', function() {
        var selectedValue = $(this).val();
        dataTable.page.len(selectedValue).draw(); // 페이지 길이 변경 (DataTable 파괴 및 재초기화 없이)

        // 변경 후 현재 페이지 번호 복원
        savedPageNumber = getCookie('print_grouppageNumber');
        if (savedPageNumber) {
            dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
        }
    });
});

function restorePageNumber() {
    var savedPageNumber = getCookie('print_grouppageNumber');
    // if (savedPageNumber) {
        // dataTable.page(parseInt(savedPageNumber) - 1).draw('page');
    // }
	location.reload(true);
}

</script>
