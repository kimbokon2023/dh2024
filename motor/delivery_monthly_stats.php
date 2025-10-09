<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php"); 
    exit;
}

include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';

$title_message = 'DH모터 월별 운임 통계';
$tablename = 'motor';

// 연도 선택 처리
$selected_year = isset($_REQUEST['year']) ? $_REQUEST['year'] : date('Y');
$current_year = date('Y');
?>

<title><?=$title_message?></title>
<link href="css/style.css" rel="stylesheet">

<style>
table, th, td {
    border: 1px solid #ccc !important;
    font-size: 14px !important;
    padding: 8px;
}

.total-row {
    background-color: #f0f0f0;
    font-weight: bold;
}

.delivery-type-header {
    background-color: #e3f2fd;
    font-weight: bold;
}
</style>
</head>

<body>

<div class="container-fluid mt-3">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-bar-chart-fill"></i> <?=$title_message?></h5>
        </div>
        <div class="card-body">
            <!-- 연도 선택 -->
            <div class="d-flex align-items-center justify-content-center mb-3">
                <label class="me-2"><strong>연도 선택:</strong></label>
                <select id="yearSelect" class="form-select" style="width: 150px;">
                    <option value="<?=$current_year?>" <?=$selected_year == $current_year ? 'selected' : ''?>><?=$current_year?>년</option>
                    <option value="<?=$current_year - 1?>" <?=$selected_year == $current_year - 1 ? 'selected' : ''?>><?=$current_year - 1?>년</option>
                    <option value="<?=$current_year - 2?>" <?=$selected_year == $current_year - 2 ? 'selected' : ''?>><?=$current_year - 2?>년</option>
                </select>
                <button type="button" class="btn btn-primary btn-sm ms-2" onclick="loadStats()">
                    <i class="bi bi-search"></i> 조회
                </button>
                <button type="button" class="btn btn-secondary btn-sm ms-2" onclick="window.close();">
                    <i class="bi bi-x-lg"></i> 닫기
                </button>
            </div>

            <!-- 통계 테이블 -->
            <div id="statsTable"></div>
        </div>
    </div>
</div>

</body>
</html>

<script>
$(document).ready(function(){
    loader = document.getElementById('loadingOverlay');
    loader.style.display = 'none';
    loadStats();
});

function loadStats() {
    var selectedYear = $('#yearSelect').val();
    
    $.ajax({
        url: 'fetch_monthly_stats.php',
        type: 'POST',
        data: { year: selectedYear },
        success: function(response) {
            $('#statsTable').html(response);
        },
        error: function() {
            Toastify({
                text: "통계 데이터를 불러오는데 실패했습니다.",
                duration: 2000,
                close: true,
                gravity: "top",
                position: "center",
                style: { background: "linear-gradient(to right, #ff6b6b, #f06595)" }
            }).showToast();
        }
    });
}
</script>

