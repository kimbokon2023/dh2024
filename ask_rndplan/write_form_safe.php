<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/session.php';
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");

$title_message = '연구개발계획서';
?>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/common.php' ?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php'; ?>

<title> <?php echo $title_message; ?> </title>
</head>

<body>
<?php include $_SERVER['DOCUMENT_ROOT'] . "/common/modal.php"; ?>

<?php
$tablename = 'eworks';
if(!$chkMobile) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php');
}

// 모바일이면 특정 CSS 적용
if ($chkMobile) {
    echo '<style>
        table th, table td, h4, .form-control, span {
            font-size: 22px;
        }
        h4 {
            font-size: 40px;
        }
        .btn-sm {
            font-size: 30px;
        }
    </style>';
}

include $_SERVER['DOCUMENT_ROOT'] .'/eworks/_request.php';

$pdo = db_connect();

// 기본 변수 초기화
$id = isset($_REQUEST['num']) ? $_REQUEST['num'] : '';
$num = $id;
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';
$parentid = isset($_REQUEST['parentid']) ? $_REQUEST['parentid'] : '';

// 수정/조회 모드일 때 데이터 로드
if ($mode == "modify" || $mode == "view" || $mode == "copy") {
    $sql = "SELECT * FROM {$DB}.eworks WHERE num = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$num]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $mytitle = $row['outworkplace'] ?? '';
        $content = $row['al_content'] ?? '';
        $author = $row['author'] ?? '';
        $indate = $row['indate'] ?? date('Y-m-d');
    }
} else {
    // 신규 작성 모드
    $mytitle = '';
    $content = '';
    $author = $_SESSION['name'] ?? '';
    $indate = date('Y-m-d');
}

$item = 'attached';
$savetitle = '연구개발계획서 첨부파일';
$pInput = '';
$update_log = '';
$first_writer = $author;
$titlemsg = $title_message;
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-sm-7">
            <div class="d-flex mb-5 mt-5 justify-content-center align-items-center">
                <h4><?php echo $titlemsg; ?></h4>
            </div>
        </div>
    </div>
</div>

<form id="board_form" name="board_form" method="post">
    <input type="hidden" id="id" name="id" value="<?php echo $id; ?>">
    <input type="hidden" id="num" name="num" value="<?php echo $num; ?>">
    <input type="hidden" id="mode" name="mode" value="<?php echo $mode; ?>">
    <input type="hidden" id="tablename" name="tablename" value="<?php echo $tablename; ?>">

    <div class="container">
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <td class="text-center w-25 fw-bold">
                            <label for="indate">작성일</label>
                        </td>
                        <td>
                            <input type="date" class="form-control w120px" id="indate" name="indate" value="<?php echo $indate; ?>">
                        </td>
                        <td class="text-center w-25 fw-bold">
                            <label for="author">작성자</label>
                        </td>
                        <td>
                            <input type="text" class="form-control text-center w80px" id="author" name="author" value="<?php echo $author; ?>">
                        </td>
                    </tr>
                    <tr>
                        <td class="text-center w-25 fw-bold">
                            <label for="mytitle">제목</label>
                        </td>
                        <td colspan="3">
                            <input type="text" class="form-control" id="mytitle" name="mytitle" value="<?php echo $mytitle; ?>" placeholder="연구개발계획서 제목">
                        </td>
                    </tr>
                    <tr>
                        <td class="text-center w-25 fw-bold">
                            <label for="content">내용</label>
                        </td>
                        <td colspan="3">
                            <textarea class="form-control" id="content" name="content" rows="10" placeholder="내용"><?php echo $content; ?></textarea>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="container mt-3">
        <div class="d-flex justify-content-center">
            <button type="button" id="saveBtn" class="btn btn-primary me-2">
                <i class="bi bi-floppy"></i> 저장
            </button>
            <button type="button" class="btn btn-secondary" onclick="self.close();">
                <i class="bi bi-x-lg"></i> 창닫기
            </button>
        </div>
    </div>
</form>

<script>
$(document).ready(function() {
    $("#saveBtn").click(function() {
        alert('저장 기능은 추후 구현됩니다.');
    });
});
</script>

</body>
</html>