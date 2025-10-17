<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/session.php'; // 세션 파일 포함
require_once($_SERVER['DOCUMENT_ROOT'] . '/lib/mydb.php');

$title_message = '카드 상세 내역';      
$card = $_REQUEST["card"] ?? '';
$fromdate = $_REQUEST['fromdate'] ?? '';
$todate = $_REQUEST['todate'] ?? '';

// fromdate 또는 todate가 빈 문자열이거나 null인 경우
if ($fromdate === "" || $fromdate === null || $todate === "" || $todate === null) {
    // 현재 달의 첫날
    $fromdate = date('Y-m-01');
    // 현재 달의 마지막 날
    $todate = date('Y-m-t');
    $Transtodate = $todate;
} else {
    // fromdate와 todate가 모두 설정된 경우 (기존 로직 유지)
    $Transtodate = $todate;
}

// 법인카드 목록 가져오기
$jsonFile = $_SERVER['DOCUMENT_ROOT'] . '/account/cardlist.json';
$cards = [];
if (file_exists($jsonFile)) {
    $jsonContent = file_get_contents($jsonFile);
    $cards = json_decode($jsonContent, true);
    if (!is_array($cards)) {
        $cards = [];
    }
}

// 법인카드 정보를 포맷팅하는 함수
function formatCompanyCard($companyCard, $cardList) {
    if (empty($companyCard)) {
        return '';
    }
    
    // JSON 배열에서 해당 카드번호 찾기
    foreach ($cardList as $card) {
        if (isset($card['number']) && strpos($card['number'], $companyCard) !== false) {
            // 카드번호 끝 4자리 추출
            $lastThree = substr($card['number'], -8, 3);
            return "({$card['company']}) {$lastThree} ({$card['user']})";
        }
    }
    
    // 매칭되는 카드가 없으면 원본 반환
    return $companyCard;
}

// 카드 사용자 정보 가져오기
$cardUser = '';
$cardCompany = '';
foreach ($cards as $cardInfo) {
    if (isset($cardInfo['number']) && strpos($cardInfo['number'], $card) !== false) {
        $cardUser = $cardInfo['user'] ?? '';
        $cardCompany = $cardInfo['company'] ?? '';
        break;
    }
}

$SettingDate = "indate";
$Andis_deleted = " AND (is_deleted IS NULL or is_deleted='0') AND eworks_item='지출결의서' AND companyCard = '" . addslashes($card) . "' ";
$Whereis_deleted = " WHERE (is_deleted IS NULL or is_deleted='0') AND eworks_item='지출결의서' AND companyCard = '" . addslashes($card) . "' ";

$common = " WHERE " . $SettingDate . " BETWEEN '$fromdate' AND '$Transtodate' " . $Andis_deleted . " ORDER BY ";
$a = $common . " num DESC "; // 내림차순 전체

$sql = "select * from ".$DB.".eworks " . $a; 	

$pdo = db_connect();

try{  
    $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh
    $total_row = $stmh->rowCount();	
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title_message ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        body {
            font-size: 14px;
        }
        .table th, .table td {
            font-size: 12px;
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
    </style>
</head>
<body>

<div class="container-fluid p-3">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><?= $title_message ?></h5>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.close()">
                    <i class="bi bi-x-lg"></i> 닫기
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- 카드 정보 요약 -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>카드번호:</strong> <?= htmlspecialchars($card) ?><br>
                    <strong>카드사:</strong> <?= htmlspecialchars($cardCompany) ?><br>
                    <strong>사용자:</strong> <?= htmlspecialchars($cardUser) ?>
                </div>
                <div class="col-md-6">
                    <strong>조회기간:</strong> <?= $fromdate ?> ~ <?= $todate ?><br>
                    <strong>총 건수:</strong> <?= number_format($total_row) ?>건
                </div>
            </div>

            <!-- 상세 리스트 테이블 -->
            <div class="table-responsive">
                <table class="table table-hover table-sm" id="cardDetailTable">
                    <thead class="table-primary">
                        <tr>
                            <th class="text-center" style="width:5%;">번호</th>
                            <th class="text-center" style="width:100px;">작성일</th>
                            <th class="text-center" style="width:100px;">지출요청일</th>
                            <th class="text-center" style="width:100px;">결재일</th>
                            <th class="text-center">기안자</th>
                            <th class="text-center">현장명</th>
                            <th class="text-center">분류</th>
                            <th class="text-center">적요</th>
                            <th class="text-end">금액</th>
                            <th class="text-center">비고</th>
                            <th class="text-center">결재완료</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $start_num = $total_row; // 페이지당 표시되는 첫번째 글순번
                        
                        while ($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
                            include $_SERVER['DOCUMENT_ROOT'] .'/eworks/_row.php';		
                            
                            // expense_data JSON 파싱
                            $expense_data = json_decode($expense_data ?? '[]', true);
                            $items = [];
                            $notes = [];
                            $category = [];
                            $site = [];
                            $total_amount = 0;

                            if (is_array($expense_data)) {
                                foreach ($expense_data as $expense) {
                                    if (!empty($expense['expense_item'])) {
                                        $items[] = $expense['expense_item'];
                                    }
                                    if (!empty($expense['expense_note'])) {
                                        $notes[] = $expense['expense_note'];
                                    }
                                    if (!empty($expense['expense_amount'])) {
                                        $total_amount += intval(str_replace(',', '', $expense['expense_amount']));
                                    }
                                    if (!empty($expense['expense_category'])) {
                                        $category[] = $expense['expense_category'];
                                    }
                                    if (!empty($expense['expense_site'])) {
                                        $site[] = $expense['expense_site'];
                                    }
                                }
                            }

                            // 적요와 비고를 콤마로 구분된 문자열로 변환
                            $items_str = implode(', ', $items);
                            $notes_str = implode(', ', $notes);
                            $category_str = implode(', ', $category);
                            $site_str = implode(', ', $site);   
                            echo '<tr style="cursor:pointer;" data-id="'.  $num . '" onclick="redirectToView(' . $num . ')">';
                        ?>
                            <td class="text-center"><?= $start_num ?></td>
                            <td class="text-center" data-order="<?= $indate ?>"> <?=$indate?> </td>
                            <td class="text-center" data-order="<?= $requestpaymentdate ?>"> <?= $requestpaymentdate ?> </td>
                            <td class="text-center" data-order="<?= $paymentdate ?>"> <?= $paymentdate ?> </td>
                            <td class="text-center"> <?= $author ?> </td>
                            <td class="text-start"> <?= $site_str ?></td>
                            <td class="text-start"> <?= $category_str ?></td>
                            <td class="text-start"><?= $items_str ?></td>
                            <td class="text-end"><?= number_format($total_amount) ?></td>
                            <td class="text-start"><?= $notes_str ?></td>
                            <td class="text-center">
                                <?= ( ($status === 'end' && !empty($e_confirm)) || empty($e_line_id) ) ? '✅' : '' ?>
                            </td>
                        </tr>
                        <?php
                            $start_num--;  
                        } 
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    // DataTables 초기 설정
    $('#cardDetailTable').DataTable({
        "paging": true,
        "ordering": true,
        "searching": true,
        "pageLength": 25,
        "lengthMenu": [10, 25, 50, 100],
        "language": {
            "lengthMenu": "페이지당 _MENU_개씩 보기",
            "search": "검색:",
            "info": "_START_ - _END_ / _TOTAL_",
            "paginate": {
                "first": "처음",
                "last": "마지막",
                "next": "다음",
                "previous": "이전"
            }
        },
        "order": [[0, 'desc']]
    });
});

function redirectToView(num) {
    var url = "write_form.php?mode=view&num=" + num + "&tablename=eworks";
    // 팝업에서 팝업을 열기 위해 window.open 사용
    var popup = window.open(url, 'detail_view_' + num, 'width=800,height=800,scrollbars=yes,resizable=yes');
    if (popup) {
        popup.focus();
    }
}
</script>

</body>
</html>

<?php
} catch (PDOException $Exception) {
    print "오류: ".$Exception->getMessage();
}   
?> 