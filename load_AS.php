<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

$sum_motor = array();
 
$now = date("Y-m-d",time());
  
// 출고완료 수량 체크  
$a = " where (asendday='0000-00-00' or asendday IS NULL) and is_deleted IS NULL order by num desc ";   
$sql="select * from " . $DB . ".as " . $a; 					
	   
try{   
    $stmh = $pdo->query($sql);             
    $total_row = $stmh->rowCount();  	    
  	  
?>

<style>
    .rounded-card {
        border-radius: 15px !important;  /* 조절하고 싶은 라운드 크기로 설정하세요. */
    }
	th {		 
	   text-align : center;	
	}
  
.table-hover tbody tr:hover {
	cursor: pointer;
}	
</style>

<style>
    /* 카드 전체에 파스텔톤의 은은한 배경과 부드러운 테두리 적용 */
    .as-pastel-card {
        border-radius: 15px !important;
        background: #f8fafc;
        box-shadow: 0 2px 8px rgba(120,120,120,0.06);
        border: 1px solid #e6eaf0;
    }
    .as-pastel-card .card-header {
        background: #f3f6fb;
        border-bottom: 1px solid #e6eaf0;
        border-radius: 15px 15px 0 0 !important;
    }
    .as-pastel-card .card-body {
        background: transparent;
    }
    .as-pastel-table thead th {
        background: #eaf1fb;
        color: #495057;
        border-bottom: 2px solid #e6eaf0;
    }
    .as-pastel-table tbody tr {
        background: #fcfdff;
        transition: background 0.2s;
    }
    .as-pastel-table tbody tr:hover {
        background: #f2f7fa;
        cursor: pointer;
    }
    .as-pastel-table td, .as-pastel-table th {
        border: 1px solid #e6eaf0 !important;
        vertical-align: middle;
    }
    .as-pastel-table .text-primary {
        color: #5b7fa3 !important;
    }
    .as-pastel-table .text-danger {
        color: #e57373 !important;
    }
    .as-pastel-table .badge.bg-danger {
        background: #ffb3b3;
        color: #fff;
        font-weight: 600;
        border-radius: 8px;
        box-shadow: none;
    }
    /* 링크 색상 은은하게 */
    #dis_text2 a {
        color: #495057;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.2s;
    }
    #dis_text2 a:hover {
        color: #4a90e2;
        text-decoration: underline;
    }
</style>

<div class="card as-pastel-card mb-2 mt-3">
    <div class="card-header text-center">
        <div class="d-flex align-items-center">
            <button type="button" id="as_view_toggle" class="btn btn-secondary btn-sm me-2 fw-bold">
                <i class="bi bi-chevron-down"></i>
            </button>
            <div class="flex-grow-1 d-flex justify-content-center">
                <span id="dis_text2" class="fs-6">
                    <a href="as/list.php">AS 처리 현황</a>
                </span>
            </div>
        </div>
    </div>
    <div class="card-body p-1 m-1 mb-3 justify-content-center" id="as-content">
        <div class="d-flex justify-content-center">
            <table class="table as-pastel-table table-hover table-bordered table-sm p-0">
                <thead>
                    <tr>
                        <th class="text-center w30px">번호</th>
                        <th class="text-center w50px">구분</th>
                        <th class="text-center w90px">처리예정일</th>
                        <th class="text-center w150px">주소</th>
                        <th class="text-center w100px">발주처</th>
                        <th class="text-center w90px">요청인</th>
                        <th class="text-center w250px">구체적증상 및 추가메모</th>
                        <th class="text-center w60px">유/무상</th>
                        <th class="text-center w60px">비용</th>
                        <th class="text-center w50px">실행</th>
                        <th class="text-center w150px">처리방법 및 결과(구체적)</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $start_num = $total_row;
                while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
                    include $_SERVER['DOCUMENT_ROOT'] . '/as/_row.php';
                ?>
                <tr onclick="redirectToView('<?=$num?>')">
                    <td class="text-center"><?= $start_num ?></td>
                    <td class="text-center fw-bold"><?= $itemcheck ?></td>
                    <td class="text-center"><?= $asproday == '0000-00-00' ? '' : $asproday ?></td>
                    <td class="text-start"><?= $address ?></td>
                    <td class="text-center"><?= $as_step ?></td>
                    <td class="text-center"><?= $asorderman ?></td>
                    <td class="text-start fw-bold text-primary"><?= $aslist ?></td>
                    <td class="text-center">
                        <?php
                        if ($payment == 'free') {
                            echo '무상';
                        } elseif ($payment == 'paid') {
                            echo '<span class="badge bg-danger"> 유상 </span>';
                        } else {
                            echo '알 수 없음';
                        }
                        ?>
                    </td>
                    <td class="text-end fw-bold text-danger">
                        <?= (strpos($asfee, ',') !== false || $asfee === null || $asfee === '') ? $asfee : number_format($asfee) ?>
                    </td>
                    <td class="text-center"><?= $asman ?></td>
                    <td class="text-start"><?= $note ?></td>
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

<script>

function redirectToView(num) {
	popupCenter("./as/write.php?mode=view&num=" + num, "AS 내역", 1000, 980);
}

function restorePageNumber() {
    var savedPageNumber = getCookie('motorpageNumber');
    // if (savedPageNumber) {
        // dataTable.page(parseInt(savedPageNumber) - 1).draw('page');
    // }
	location.reload(true);
}

// AS 처리 현황 접기/펼치기 기능
$(document).ready(function() {
    // 쿠키에서 AS 처리 현황 상태 복원
    var asViewState = getCookie("showAsView");
    if (asViewState === "hide") {
        $("#as-content").hide();
        $("#as_view_toggle i").removeClass("bi-chevron-down").addClass("bi-chevron-up");
    }

    // AS 처리 현황 토글 버튼 클릭 이벤트
    $('#as_view_toggle').on('click', function() {
        var isVisible = $("#as-content").is(":visible");
        $("#as-content").toggle();

        // 아이콘 변경
        if (isVisible) {
            $("#as_view_toggle i").removeClass("bi-chevron-down").addClass("bi-chevron-up");
            setCookie("showAsView", "hide", 10);
        } else {
            $("#as_view_toggle i").removeClass("bi-chevron-up").addClass("bi-chevron-down");
            setCookie("showAsView", "show", 10);
        }
    });
});

</script> 
  
