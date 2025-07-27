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
<script src="https://dh2024.co.kr/js/todolist_work.js"></script>  <!-- 업무일지 -->
<style>
    .editable-item {
        cursor: pointer;
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

<div class="container-fluid">
 <!-- <div class="d-flex p-1 m-1 mt-1 mb-3 justify-content-center align-items-center">       
	<span class="badge bg-danger fs-3 me-3"> 월간계획 입력은 현재개발중입니다. (미완성) </span>                    
</div> 
-->
    <div class="card mt-1">
        <div class="card-body">
            <h5>월간계획</h5>
            <div class="row">
                <?php
                require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
                $pdo = db_connect();

                // 현재 월의 시작일과 종료일 계산
                $first_day_of_month = date("Y-m-01");
                $last_day_of_month = date("Y-m-t");

                // 해당 월의 업무일정 가져오기
                $sql = "SELECT orderdate, title, title_after FROM todos_work WHERE orderdate BETWEEN :first_day AND :last_day AND ( is_deleted = 0 or is_deleted IS NULL) and first_writer = :first_writer  ";
                $stmh = $pdo->prepare($sql);
                $stmh->bindValue(':first_day', $first_day_of_month, PDO::PARAM_STR);
                $stmh->bindValue(':last_day', $last_day_of_month, PDO::PARAM_STR);
                $stmh->bindValue(':first_writer', $user_name, PDO::PARAM_STR);
                $stmh->execute();

                $counter = 0;
                while ($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
                    $orderdate = htmlspecialchars($row['orderdate']);
                    $title = htmlspecialchars($row['title']);
                    $title_after = htmlspecialchars($row['title_after']);

                    echo '<div class="col-sm-12 mb-1">';
                    echo '<div class="d-flex justify-content-start align-items-center fs-6">';
                    echo '<span class="badge bg-success me-2">' . $orderdate . '</span>';
                    echo '<span class="editable-item"> 오전: ' . $title . ' / 오후: ' . $title_after . '</span>';
                    echo '</div>';
                    echo '</div>';

                    // 매 4개의 열마다 새로운 행을 시작
                    $counter++;
                    if ($counter % 4 == 0) {
                        echo '</div><div class="row">';
                    }
                }
                ?>
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
                <button type="button" id="todo-prev-month_work" class="btn btn-primary  btn-sm me-2"><ion-icon name="arrow-back-outline"></ion-icon> </button>
                 <span id="todo-current-period" class="text-dark fs-6 me-2"></span>
                <button  type="button" id="todo-next-month_work" class="btn btn-primary btn-sm me-2"> <ion-icon name="arrow-forward-outline"></ion-icon></button>
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



 </script> 
  