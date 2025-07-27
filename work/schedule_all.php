<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");

if (!isset($_SESSION["level"]) || intval($_SESSION["level"]) > 7) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php");
    exit;
}

$today = date("Y-m-d");
require_once($_SERVER['DOCUMENT_ROOT'] . "/load_header.php");
$titlemessage = '작업일지';
?>

<script src="https://dh2024.co.kr/js/todolist_work.js"></script>
<style>
	.editable-item {
		cursor: pointer;
	}
	td {
    vertical-align: top;
}	
</style>
<title> <?=$titlemessage?>  </title>
<!-- Favicon-->    
<link rel="icon" type="image/x-icon" href="favicon.ico">
<link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
<link rel="apple-touch-icon" type="image/x-icon" href="favicon.ico">
</head>

<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php'); ?>

<form id="board_form" name="board_form" method="post" enctype="multipart/form-data">

	<input type="hidden" id="num" name="num" value="<?= isset($num) ? $num : '' ?>">
	<input type="hidden" id="mode" name="mode" value="<?= isset($mode) ? $mode : '' ?>">
	
<!-- 작업일지 작성자 선택 -->
<div class="container w-25">
    <div class="card mt-2">
        <div class="card-body">
		  <div class="d-flex justify-content-center align-items-center mt-1 mb-3">         
			   <h5>작업일지 선택</h5>
		  </div>
            <div class="row">
			<?php
                require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
                $pdo = db_connect();

                // dailyworkcheck가 '작성'인 회원들 불러오기
                $sql = "SELECT name FROM member WHERE dailyworkcheck = '작성'";
                $stmh = $pdo->prepare($sql);
                $stmh->execute();
                $members = $stmh->fetchAll(PDO::FETCH_ASSOC);

				foreach ($members as $member) {
					$name = htmlspecialchars($member['name']);
					if($name!=='개발자') {
					$id = 'member_' . htmlspecialchars($member['name']); // input과 label 연결을 위한 id 설정
					echo '<div class="col-sm-3 mb-3">';
					echo '<div class="d-flex align-items-center">';
					echo '<input type="radio" id="' . $id . '" name="selected_member" value="' . $name . '" class="form-check-input me-2" onclick="loadMemberWork(\'' . $name . '\')">';
					echo '<label for="' . $id . '" class="form-check-label fs-6">' . $name . '</label>'; // label 태그로 감싸서 클릭 가능하게 변경
					echo '</div>';
					echo '</div>';
					}
				}

			?>
            </div>
        </div>
    </div>
</div>


<!-- 월간계획 -->
<div class="container w-50">
    <div class="card mt-1">
        <div class="card-body">
            <!-- 월간 계획 표시 -->
            <div class="row">
                <div class="col-12 mb-3">
                    <div class="p-3 border rounded">
                        <h6 class="fw-bold mb-2">월간 계획</h6>
                        <!-- 이 부분이 AJAX로 업데이트됨 -->
                        <div id="monthly-plan-content" class="mb-0 fs-6">
                            <!-- 여기에 멤버별 월간 계획이 출력됩니다 -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 작업일지 내용 -->
<div class="container w-50">
    <div class="card mt-1">
        <div class="card-body">
            <h5>월간 작업 리스트</h5>
            <div class="row" id="work_schedule">
                <!-- 작업일정이 여기에 동적으로 표시됨 -->
            </div>
        </div>
    </div>
</div>



<!-- todo Calendar -->
<?php if($chkMobile==false) { ?>
    <div class="container-fluid">     
<?php } else { ?>
    <div class="container-fluid">      
<?php } ?>  
	<div class="card mt-1">
        <div class="card-body">
    <div class="row mt-3">
        <!-- Calendar Controls -->
        <div class="col-sm-3">
		  <div class="d-flex justify-content-start align-items-center mt-3 ">            
			<h5> <전직원 업무일지> &nbsp;&nbsp; </h5> 
		  </div>
        </div>
        <div class="col-sm-6">
            <div class="d-flex justify-content-center align-items-center mb-2">
                <button type="button" id="todo-prev-month_work" class="btn btn-primary  btn-sm me-2"> <i class="bi bi-arrow-left"></i>  </button>
                 <span id="todo-current-period" class="text-dark fs-6 me-2"></span>
                <button  type="button" id="todo-next-month_work" class="btn btn-primary btn-sm me-2"> <i class="bi bi-arrow-right"></i> </button>
                <button  type="button" id="todo-current-month_work" class="btn btn-outline-primary fw-bold btn-sm me-5"> <?php echo date("m",time()); ?> 월</button>                
				<button type="button" class="btn btn-dark btn-sm me-1" onclick='location.reload()'>  <i class="bi bi-arrow-clockwise"></i> </button>      
            </div>        
        </div>       
        <div class="col-sm-3"> </div>
        </div>        
        <div id="todo-calendar-container_work" class="d-flex p-1 justify-content-center"></div>
    </div>

</div>
</div>

<div class="container-fluid">
    <?php include $_SERVER['DOCUMENT_ROOT'] .'/footer.php'; ?>
</div>
</form>

<script>

// 페이지 로딩
$(document).ready(function() {
    var loader = document.getElementById('loadingOverlay');
    loader.style.display = 'none';
});

// 동적으로 선택한 사용자의 작업일정을 불러오는 함수
function loadMemberWork(memberName) {
    $.ajax({
        url: '/work/load_member_work.php', // 작업일정을 불러올 PHP 파일
        type: 'POST',
        data: { member_name: memberName },
        success: function(response) {
            $('#work_schedule').html(response); // 작업일정을 동적으로 표시			
        },
        error: function(xhr, status, error) {
            console.error('작업일정을 불러오는 중 오류가 발생했습니다: ' + error);
        }
    });
}

$(document).ready(function(){
	saveLogData('전체 업무일지 보기'); 
});

</script>

</body>
</html>