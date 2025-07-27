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

$fromdate = $_REQUEST['fromdate'] ?? '';
$todate   = $_REQUEST['todate']   ?? '';
$currentDate = date("Y-m-d");
if ($fromdate === "" || $todate === "") {
    $fromdate = date("Y-m-d", strtotime("-1 month", strtotime($currentDate)));
    $todate   = $currentDate;
}

// DB 연결
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

// 계좌 한 개만 bankbook.txt에서 읽어오기
$bankbookFile = $_SERVER['DOCUMENT_ROOT'] . "/account/bankbook.txt";
$bankbookOptions = file_exists($bankbookFile)
    ? file($bankbookFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
    : [];
$bankbookName = $bankbookOptions[0] ?? '';

// 1) 초기 잔액 계산 (검색 시작 전까지)
$initialSql = "
    SELECT
      SUM(CASE WHEN inoutsep='수입' THEN REPLACE(amount,',','') ELSE 0 END)
    - SUM(CASE WHEN inoutsep='지출' THEN REPLACE(amount,',','') ELSE 0 END)
    AS balance
    FROM account
    WHERE (is_deleted=0 OR is_deleted IS NULL OR is_deleted = '')
      AND registDate < :fromdate
";
$st = $pdo->prepare($initialSql);
$st->execute([':fromdate' => $fromdate]);
$balance = floatval($st->fetchColumn());  // 최초 잔액

// 2) 날짜별 데이터 수집 (오름차순)
$dailyData = [];
$current = strtotime($fromdate);
$end     = strtotime($todate);
$order   = " ORDER BY num ASC ";

while ($current <= $end) {
    $dateStr = date("Y-m-d", $current);

    // 당일 거래 내역 조회
    $sql = "SELECT * FROM account
            WHERE registDate = :d
              AND (is_deleted=0 OR is_deleted IS NULL OR is_deleted = '')
            $order";
    $st = $pdo->prepare($sql);
    $st->execute([':d' => $dateStr]);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);

    // 전일 잔액
    $prevBalance = $balance;

    // 당일 수입·지출 합계
    $dailyIncome  = 0;
    $dailyExpense = 0;
    foreach ($rows as $r) {
        $amt = floatval(str_replace(',', '', $r['amount']));
        if ($r['inoutsep'] === '수입') {
            $dailyIncome  += $amt;
        } else {
            $dailyExpense += $amt;
        }
    }

    // 잔액 갱신
    $balance += ($dailyIncome - $dailyExpense);

    // 거래가 있는 날만 저장
    if (!empty($rows)) {
        $dailyData[] = [
            'date'    => $dateStr,
            'rows'    => $rows,
            'prev'    => $prevBalance,
            'income'  => $dailyIncome,
            'expense' => $dailyExpense,
            'balance' => $balance,
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
        <div class="card-header">
            <span class="fs-5"><?=$title_message?></span>
            <button type="button" class="btn btn-dark btn-sm float-end" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-center align-items-center">
                <input type="date" name="fromdate" class="form-control" style="width:130px;" value="<?=$fromdate?>">
                <span class="mx-2">~</span>
                <input type="date" name="todate"   class="form-control" style="width:130px;" value="<?=$todate?>">
                <button type="submit" class="btn btn-outline-dark btn-sm ms-2">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </div>
    </div>

    <?php if (empty($dailyData)): ?>
        <div class="alert alert-warning text-center mt-4">해당 기간에 거래 내역이 없습니다.</div>
    <?php else: ?>
        <?php foreach ($dailyData as $day): ?>
            <div class="card mt-4 mb-4">
                <div class="card-body">
                    <!-- 일자 & 작성자 -->
                    <div class="alert alert-primary text-center fs-4">
                        <?php
                        $days = ['일', '월', '화', '수', '목', '금', '토'];
                        $timestamp = strtotime($day['date']);
                        $weekday = $days[date('w', $timestamp)];
                        echo "일자: " . date('Y년 n월 j일 ', $timestamp) . $weekday . "요일 / 작성자: 최정인";
                        ?>
                    </div>

                    <!-- 요약 테이블 -->
                    <div class="table-responsive">
                        <table class="table detail-table">
                            <thead class="table-secondary">
                                <tr>
                                    <th>구분</th>
                                    <th>전일이월</th>
                                    <th>수입</th>
                                    <th>지출</th>
                                    <th>잔액</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>예금입출금 현황</td>
                                    <td><?=number_format($day['prev'])?></td>
                                    <td><?=number_format($day['income'])?></td>
                                    <td><?=number_format($day['expense'])?></td>
                                    <td><?=number_format($day['balance'])?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- 입출금 내역 테이블 -->
                    <div class="table-responsive mt-4">
                        <?php
                        $inc = array_values(array_filter($day['rows'], function($r) { return $r['inoutsep'] !== '지출'; }));
                        $exp = array_values(array_filter($day['rows'], function($r) { return $r['inoutsep'] === '지출'; }));
                        $max = max(count($inc), count($exp));
                        ?>
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
                                    <td class="text-end text-primary"><?=number_format($day['income'])?></td>
                                    <td>출금 합계</td>
                                    <td class="text-end text-danger"><?=number_format($day['expense'])?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
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
});

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


