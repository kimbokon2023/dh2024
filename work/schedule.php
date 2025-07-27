<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");    

if(!isset($_SESSION["level"]) ||intval($_SESSION["level"]) > 7) {
          /*   alert("관리자 승인이 필요합니다."); */
		 sleep(1);
	     header("Location:" . $WebSite . "login/login_form.php"); 
         exit;
}  

$today = date("Y-m-d");
require_once($_SERVER['DOCUMENT_ROOT'] . "/load_header.php");
$titlemessage = '업무일지';
?>  
<script src="https://dh2024.co.kr/js/todolist_work.js?v=<?$version?>"></script>  <!-- 업무일지 -->
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
<link rel="icon" type="image/x-icon" href="favicon.ico">   <!-- 33 x 33 -->
<link rel="shortcut icon" type="image/x-icon" href="favicon.ico">    <!-- 144 x 144 -->
<link rel="apple-touch-icon" type="image/x-icon" href="favicon.ico"> 
</head>
 
<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php'); ?>

<form id="board_form" name="board_form" method="post" enctype="multipart/form-data" >	

<input type="hidden" id="num" name="num" value="<?= isset($num) ? $num : '' ?>" >
<input type="hidden" id="mode" name="mode" value="<?= isset($mode) ? $mode : '' ?>" >

<!-- todo모달 컨테이너 -->
<div class="container-fluid">
	<!-- Modal -->
	<div id="todoModal" class="modal">
		<div class="modal-content"  style="width:1000px;">
			<div class="modal-header">
				<span class="modal-title">업무일지</span>
				<span class="todo-close">&times;</span>
			</div>
			<div class="modal-body">
				<div class="custom-card"></div>
			</div>
		</div>
	</div>
</div>

<div class="container-fluid">     

<!-- todo Calendar -->
<?php if($chkMobile==false) { ?>
    <div class="container-fluid">     
<?php } else { ?>
    <div class="container-fluid">      
<?php } ?>  

<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

?>

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
			<h5> <업무일지> &nbsp;&nbsp; 작성자 : <?=$user_name?> </h5> 
		  </div>
        </div>
        <div class="col-sm-6">
            <div class="d-flex justify-content-center align-items-center mb-2">
                <button type="button" id="todo-prev-month_work" class="btn btn-primary  btn-sm me-2"><i class="bi bi-arrow-left"></i> </button>
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

<div class="container-fluid">     
<?php include $_SERVER['DOCUMENT_ROOT'] .'/footer.php'; ?>
</div> 
</div>
</div>
</div>
</div>
</div> <!-- container-fulid end -->
</form> 
</body>
</html>

<script>
// 페이지 로딩
$(document).ready(function(){	
    var loader = document.getElementById('loadingOverlay');
    loader.style.display = 'none';
});

alreadyShown = getCookie("notificationShown");   

var intervalId; // 인터벌 식별자를 저장할 변수
	
function closeMsg(){
	var dialog = document.getElementById("myMsgDialog");
	dialog.close();
}
  	
function restorePageNumber(){
    window.location.reload();
}

$(document).on("click", "#closeBtn_month", function() {
	$("#todoModalMonthly").hide();
});


$(document).ready(function(){
	saveLogData('업무일지 작성'); 
});

 </script> 
  