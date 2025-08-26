<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  
   
// 첫 화면 표시 문구
$title_message = '연간 계획 문구';

if(!isset($_SESSION["level"]) || $_SESSION["level"]>5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php"); 
    exit;
}   

// 파일 경로 설정
$title_file = __DIR__ . '/title.txt';
$current_title = '';
$message = '';
$message_type = '';

// POST 요청 처리 (저장)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_title'])) {
    $new_title = trim($_POST['title_content']);
    
    if (!empty($new_title)) {
        try {
            if (file_put_contents($title_file, $new_title) !== false) {
                $message = '연간 계획이 성공적으로 저장되었습니다.';
                $message_type = 'success';
                $current_title = $new_title;
            } else {
                $message = '파일 저장 중 오류가 발생했습니다.';
                $message_type = 'danger';
            }
        } catch (Exception $e) {
            $message = '파일 저장 중 오류가 발생했습니다: ' . $e->getMessage();
            $message_type = 'danger';
        }
    } else {
        $message = '연간 계획를 입력해주세요.';
        $message_type = 'warning';
    }
}

// 현재 저장된 연간 계획 읽기
if (file_exists($title_file)) {
    $current_title = file_get_contents($title_file);
    $current_title = trim($current_title);
}

include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';   
?>
  
<title><?=$title_message?></title> 

<style>
    .table-hover tbody tr:hover {
        cursor: pointer;
    }
    .title-input {
        min-height: 120px;
        resize: vertical;
    }
    .message-alert {
        animation: fadeIn 0.5s ease-in;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style> 
 
</head> 
 
<body>

<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php'); ?>   

<div class="container">  
    <div class="card mt-1">
        <div class="card-header d-flex justify-content-center align-items-center">
            <span class="text-center fs-5"> <?=$title_message?> </span>	
            <button type="button" class="btn btn-dark btn-sm mx-3" onclick='location.reload();' title="새로고침"> <i class="bi bi-arrow-clockwise"></i> </button>  		
            <small class="mx-3 text-muted"> 대한 전산프로그램 카테고리 아랫쪽 문구생성 </small>  
        </div>
        <div class="card-body">

            <?php if (!empty($message)): ?>
            <div class="alert alert-<?=$message_type?> message-alert alert-dismissible fade show" role="alert">
                <?=$message?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="title_content" class="form-label">
                                <strong>연간 계획 문구</strong>
                                <span class="text-muted">(초기화면에 표시될 문구)</span>
                            </label>
                            <textarea 
                                class="form-control title-input" 
                                id="title_content" 
                                name="title_content" 
                                placeholder="올해의 목표를 입력해주세요..."
                                required
                            ><?=htmlspecialchars($current_title)?></textarea>
                            <div class="form-text">
                                현재 글자 수: <span id="char_count">0</span>자
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-center gap-2">
                            <button type="submit" name="save_title" class="btn btn-primary">
                                <i class="bi bi-save"></i> 저장
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                <i class="bi bi-arrow-counterclockwise"></i> 초기화
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <?php if (!empty($current_title)): ?>
            <div class="row justify-content-center mt-4">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-eye"></i> 미리보기</h6>
                        </div>
                        <div class="card-body">
                            <div class="border rounded p-3 bg-light">
                                <?=nl2br(htmlspecialchars($current_title))?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- 페이지로딩 -->
<script>
$(document).ready(function(){	
    var loader = document.getElementById('loadingOverlay');
    if (loader) {
        loader.style.display = 'none';
    }
    
    // 글자 수 카운터
    $('#title_content').on('input', function() {
        var length = $(this).val().length;
        $('#char_count').text(length);
    });
    
    // 초기 글자 수 설정
    $('#char_count').text($('#title_content').val().length);
    
    // 자동 저장 확인
    $('#title_content').on('input', function() {
        var originalValue = <?=json_encode($current_title)?>;
        var hasChanges = $(this).val() !== originalValue;
        if (hasChanges) {
            $('button[name="save_title"]').removeClass('btn-primary').addClass('btn-warning');
        } else {
            $('button[name="save_title"]').removeClass('btn-warning').addClass('btn-primary');
        }
    });
});

function resetForm() {
    if (confirm('입력한 내용을 모두 지우시겠습니까?')) {
        $('#title_content').val('');
        $('#char_count').text('0');
        $('button[name="save_title"]').removeClass('btn-warning').addClass('btn-primary');
    }
}

// saveLogData 함수가 정의되지 않은 경우를 위한 안전장치
if (typeof saveLogData === 'function') {
    $(document).ready(function(){
        var title_message = '<?=$title_message?>';
        saveLogData(title_message); 
    });
}
</script>

</body> 
</html>   