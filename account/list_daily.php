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
$title_message = '일일 일보';
?>

<link href="css/style.css" rel="stylesheet">
<title> <?=$title_message?> </title>
<style>
/* 요약·내역 테이블 공통 스타일 */
.detail-table th, .detail-table td {
    border: 1px solid black;
    border-collapse: collapse;
    padding: 4px;
    text-align: center;
    font-size: 0.9em;
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
    <div id="loadingScreen">잠시만 기다려 주세요...</div>
    <script>
        // 페이지 이동 또는 새로고침 시 로딩 메시지 표시
        window.addEventListener('beforeunload', function () {
            document.getElementById('loadingScreen').style.display = 'block';
        });
    </script>

<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php');

$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
$search_type = isset($_REQUEST['search_type']) ? $_REQUEST['search_type'] : 'period';
$selected_year = isset($_REQUEST['selected_year']) ? $_REQUEST['selected_year'] : date('Y');
$selected_month = isset($_REQUEST['selected_month']) ? $_REQUEST['selected_month'] : date('Y-m');
$fromdate = isset($_REQUEST['fromdate']) ? $_REQUEST['fromdate'] : '';
$todate   = isset($_REQUEST['todate'])   ? $_REQUEST['todate']   : '';
$currentDate = date("Y-m-d");

// 검색 타입에 따른 날짜 설정
if ($search_type === 'year') {
    $fromdate = $selected_year . "-01-01";
    $todate = $selected_year . "-12-31";
} elseif ($search_type === 'month') {
    $fromdate = $selected_month . "-01";
    $todate = date("Y-m-t", strtotime($selected_month . "-01"));
} else {
    if ($fromdate === "" || $todate === "") {
        // 이전달의 1일부터 시작
        $fromdate = date("Y-m-01", strtotime("first day of last month"));
        $todate   = $currentDate;
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

// DB 연결
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

// // 계좌 한 개만 bankbook.txt에서 읽어오기
// $bankbookFile = $_SERVER['DOCUMENT_ROOT'] . "/account/bankbook.txt";
// $bankbookOptions = file_exists($bankbookFile)
//     ? file($bankbookFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
//     : [];
// $bankbookName = $bankbookOptions[0] ?? '';

// 1. 계좌 목록 로드
$jsonFile = $_SERVER['DOCUMENT_ROOT'] . "/account/accountlist.json";
$accounts = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
$accountNames = [];
if (is_array($accounts)) {
    foreach ($accounts as $acc) {
        $accountNames[] = $acc['company'] . ' ' . $acc['number'] ;
    }
}
// '전자어음' 계좌를 항상 추가
if (!in_array('전자어음', $accountNames)) {
    $accountNames[] = '전자어음';
}

// 1) 초기 잔액 계산 (검색 시작 전까지)
// 멀티계좌 러닝 잔액 초기화: 계좌별 (기간 시작 이전) 잔액 계산
$runningBalances = [];
foreach ($accountNames as $accountDisplay) {
    $initialSqlByAccount = "
        SELECT
          (SUM(CASE WHEN (inoutsep='수입' OR inoutsep='최초전월이월') THEN REPLACE(amount,',','') ELSE 0 END)
         - SUM(CASE WHEN inoutsep='지출' THEN REPLACE(amount,',','') ELSE 0 END)) AS balance
        FROM account
        WHERE (is_deleted=0 OR is_deleted IS NULL OR is_deleted = '')
          AND registDate < :fromdate
          AND bankbook = :bankbook
    ";
    $stInit = $pdo->prepare($initialSqlByAccount);
    $stInit->bindValue(':fromdate', $fromdate);
    $stInit->bindValue(':bankbook', $accountDisplay);
    $stInit->execute();
    $runningBalances[$accountDisplay] = floatval($stInit->fetchColumn());
}

// 2) 날짜별 데이터 수집 (오름차순)
$dailyData = [];
$current = strtotime($fromdate);
$end     = strtotime($todate);
$order   = " ORDER BY num ASC ";

while ($current <= $end) {
    $dateStr = date("Y-m-d", $current);

    // 당일 거래 내역 조회 (전체), 이후 계좌별로 그룹핑
    $sql = "SELECT * FROM account
            WHERE registDate = :d
              AND (is_deleted=0 OR is_deleted IS NULL OR is_deleted = '')
            $order";
    $st = $pdo->prepare($sql);
    $st->execute([':d' => $dateStr]);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);

    // 계좌별로 그룹핑 (허용된 계좌만)
    $rowsByAccount = [];
    foreach ($rows as $r) {
        $bk = isset($r['bankbook']) ? $r['bankbook'] : '';
        if ($bk === '' || !in_array($bk, $accountNames, true)) {
            continue;
        }
        if (!isset($rowsByAccount[$bk])) $rowsByAccount[$bk] = [];
        $rowsByAccount[$bk][] = $r;
    }

    // 거래가 있는 계좌별 요약 생성 후, 날짜 단위로 저장
    $accountSummaries = [];
    foreach ($accountNames as $accountDisplay) {
        if (empty($rowsByAccount[$accountDisplay])) {
            continue;
        }

        $accountRows = $rowsByAccount[$accountDisplay];

        // 전일(해당 계좌) 잔액
        $prevBalance = isset($runningBalances[$accountDisplay]) ? $runningBalances[$accountDisplay] : 0;

        // 당일(해당 계좌) 수입·지출 합계
        $dailyIncome  = 0;
        $dailyExpense = 0;
        foreach ($accountRows as $r) {
            $amt = floatval(str_replace(',', '', $r['amount']));
            if ($r['inoutsep'] === '지출') {
                $dailyExpense += $amt;
            } else {
                $dailyIncome  += $amt; // 수입/최초전월이월 등은 수입 취급
            }
        }

        // 해당 계좌 러닝 잔액 갱신
        $runningBalances[$accountDisplay] = $prevBalance + ($dailyIncome - $dailyExpense);

        $accountSummaries[] = [
            'account' => $accountDisplay,
            'rows'    => $accountRows,
            'prev'    => $prevBalance,
            'income'  => $dailyIncome,
            'expense' => $dailyExpense,
            'balance' => $runningBalances[$accountDisplay],
        ];
    }

    if (!empty($accountSummaries)) {
        $dailyData[] = [
            'date'     => $dateStr,
            'accounts' => $accountSummaries,
        ];
    }

    // 다음 날로
    $current = strtotime('+1 day', $current);
}

// 3) 최신일자 우선으로 보여주려면
$dailyData = array_reverse($dailyData);
?>

<form id="board_form" method="post">
<div class="container">
    <div class="card text-center mt-5">
        <div class="card-header d-flex justify-content-center align-items-center">
            <span class="fs-5"><?=$title_message?></span>
            <button type="button" class="btn btn-dark btn-sm float-end mx-2" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
            <small class="ms-5 text-muted"> 금전출납부에 작성된 내용을 일별로 정리되어 볼 수 있습니다. </small>  
        </div>
        <div class="card-body">
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
                            <input type="date" id="fromdate" name="fromdate" class="form-control me-2" style="width:130px;" value="<?=$fromdate?>" onchange="autoSubmit()">
                            <span class="me-2">~</span>
                            <input type="date" id="todate" name="todate" class="form-control" style="width:130px;" value="<?=$todate?>" onchange="autoSubmit()">
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
                        <button type="button" class="btn btn-outline-dark btn-sm ms-2" id="searchBtn">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($dailyData)): ?>
        <div class="alert alert-warning text-center mt-4">해당 기간에 거래 내역이 없습니다.</div>
    <?php else: ?>
        <?php foreach ($dailyData as $day): ?>
            <div class="card mt-4 mb-4">
                <div class="card-body">
                    <!-- 일자 -->
                    <div class="alert alert-primary text-end fs-7">
                        <?php
                        $days = ['일', '월', '화', '수', '목', '금', '토'];
                        $timestamp = strtotime($day['date']);
                        $weekday = $days[date('w', $timestamp)];
                        echo date('Y년 n월 j일 ', $timestamp) . $weekday . "요일 ";
                        ?>
                    </div>

                    <!-- 계좌별 요약 테이블 (한 테이블에 모두 표시) -->
                    <div class="row d-flex justify-content-center align-items-center">
                    <div class="table-responsive w-50">
                        <table class="table detail-table" >
                            <thead class="table-info">
                                <tr>
                                    <th>구분</th>
                                    <th>전일이월</th>
                                    <th>수입</th>
                                    <th>지출</th>
                                    <th>잔액</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($day['accounts'] as $acc): ?>
                                <tr>
                                    <td><?= htmlspecialchars($acc['account']) ?></td>
                                    <td class="text-end"><?=number_format($acc['prev'])?></td>
                                    <td class="text-end"><?=number_format($acc['income'])?></td>
                                    <td class="text-end"><?=number_format($acc['expense'])?></td>
                                    <td class="text-end"><?=number_format($acc['balance'])?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    </div>
                    <!-- 계좌별 입출금 내역 테이블 (계좌별로 반복) -->
                    <?php foreach ($day['accounts'] as $acc): ?>
                    <div class="row d-flex justify-content-center align-items-center">
                    <div class="table-responsive mt-4 w-75">
                        <?php
                        $inc = array_values(array_filter($acc['rows'], function($r) { return $r['inoutsep'] !== '지출'; }));
                        $exp = array_values(array_filter($acc['rows'], function($r) { return $r['inoutsep'] === '지출'; }));
                        $max = max(count($inc), count($exp));
                        ?>
                        <h6 class="text-start mb-2">계좌: <?= htmlspecialchars($acc['account']) ?></h6>
                        <table class="table table-hover table-bordered">
                            <thead class="table-secondary">
                                <tr>
                                    <th style="width:35%;">입금내역</th>
                                    <th style="width:15%;">금액</th>
                                    <th style="width:35%;">출금내역</th>
                                    <th style="width:15%;">금액</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php for ($i=0; $i<$max; $i++): ?>
                                    <tr>
                                        <?php if (isset($inc[$i])): ?>
                                            <td class="text-start"><?=htmlspecialchars($inc[$i]['content_detail'])?></td>
                                            <td class="text-end"><?=number_format($inc[$i]['amount'])?></td>
                                        <?php else: ?>
                                            <td></td><td></td>
                                        <?php endif; ?>

                                        <?php if (isset($exp[$i])): ?>
                                            <td class="text-start"><?=htmlspecialchars($exp[$i]['content_detail'])?></td>
                                            <td class="text-end"><?=number_format($exp[$i]['amount'])?></td>
                                        <?php else: ?>
                                            <td></td><td></td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endfor; ?>
                            </tbody>
                            <tfoot class="table-group-divider">
                                <tr class="fw-bold">
                                    <td>입금 합계</td>
                                    <td class="text-end text-primary"><?=number_format($acc['income'])?></td>
                                    <td>출금 합계</td>
                                    <td class="text-end text-danger"><?=number_format($acc['expense'])?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</form>

<script>
// 페이지 로딩
$(document).ready(function(){    
    var loader = document.getElementById('loadingOverlay');
    if(loader)
        loader.style.display = 'none';

    toggleSearchType(); // 초기 로드 시 검색 타입에 맞는 컨트롤 표시
});

// 검색 타입에 따른 동적 컨트롤 표시/숨김
function toggleSearchType() {
    var searchType = $('input[name="search_type"]:checked').val();
    $('.year-select, .month-select, .period-select').hide();
    if (searchType === 'year') {
        $('.year-select').show();
    } else if (searchType === 'month') {
        $('.month-select').show();
    } else {
        $('.period-select').show();
    }
}

// 검색 타입 변경 시 자동 검색 실행
function toggleSearchTypeAndSubmit() {
    toggleSearchType();
    setTimeout(function() { $("#board_form").submit(); }, 100);
}

// 검색 조건 변경 시 자동 검색 실행
function autoSubmit() {
    setTimeout(function() { $("#board_form").submit(); }, 300);
}

function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

let isSaving = false;
var ajaxRequest = null;

document.addEventListener('DOMContentLoaded', function() {
   
    $("#searchBtn").on("click", function() {
        $("#board_form").submit();
    });

});

function enter() {
    $("#board_form").submit();
}

$(document).ready(function(){
    saveLogData('회계 일일 일보'); 
});
</script>

</body>
</html>



