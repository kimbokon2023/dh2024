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
$titlemessage = '회계 일정관리';

?>
  
<script src="https://dh2024.co.kr/js/todolist_account.js"></script> 
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
		<div class="modal-content"  style="width:800px;">
			<div class="modal-header">
				<span class="modal-title">회계일정</span>
				<span class="todo-close">&times;</span>
			</div>
			<div class="modal-body">
				<div class="custom-card"></div>
			</div>
		</div>
	</div>
</div>
<!-- 매월 -->
<div class="container-fluid">
	<!-- Modal -->
	<div id="todoModalMonthly" class="modal">
		<div class="modal-content"  style="width:800px;">
			<div class="modal-header">
				<span class="modal-title">월별 고정 회계일정</span>
				<span class="todo-close">&times;</span>
			</div>
			<div class="modal-body">
				<div class="custom-card"></div>
			</div>
		</div>
	</div>
</div>

<?php // include $_SERVER['DOCUMENT_ROOT'] . '/mymodal.php'; ?>  

<div class="container-fluid">     

<!-- todo Calendar -->
<?php if($chkMobile==false) { ?>
    <div class="container">     
<?php } else { ?>
    <div class="container-fluid">      
<?php } ?>  

<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

try {
    $sql = "SELECT num, specialday, title FROM todos_monthly WHERE is_deleted IS NULL ORDER BY specialday ASC";
    $stmh = $pdo->prepare($sql);
    $stmh->execute();
    $data = $stmh->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $Exception) {
    echo "오류: " . $Exception->getMessage();
    exit;
}
?>
   <div class="card mt-1">
    <div class="card-body">
        <div class="row">
            <?php
            $counter = 0;
            foreach ($data as $row) {
                $specialday = htmlspecialchars($row['specialday']);
                $title = htmlspecialchars($row['title']);
                $num = isset($row['num']) ? htmlspecialchars($row['num']) : ''; // Check if 'num' exists

                echo '<div class="col-sm-3 mb-3">';
                echo '<div class="d-flex justify-content-start align-items-center fs-5">';
                echo '<span class="badge bg-success me-2"> 매월 ' . $specialday . '일 </span>';
                echo '<span class="editable-item" data-num="' . $num . '" style="cursor: pointer;">' . $title . '</span>';
                echo '</div>';
                echo '</div>';
                
                // Move to the next row after every 4 columns
                $counter++;
                if ($counter % 4 == 0) {
                    echo '</div><div class="row">';
                }
            }
            ?>
        </div>
    </div>
</div>


<div class="card mt-1">
<div class="card-body">
    <div class="row d-flex ">
        <!-- Calendar Controls -->
        <div class="col-sm-3">
		  <div class="d-flex justify-content-start align-items-center ">            
			<h5> <회계부분 상세일정> </h5> &nbsp; &nbsp; (매월)&nbsp;
			<button type='button' class='btn btn-danger btn-sm add-row me-2' data-table='acountTable' style='margin-right: 5px;'>+</button>
		  </div>
        </div>
        <div class="col-sm-6">
            <div class="d-flex justify-content-center align-items-center mb-2">
                <button type="button" id="todo-prev-month_account" class="btn btn-danger  btn-sm me-2"><ion-icon name="arrow-back-outline"></ion-icon> </button>
                 <span id="todo-current-period" class="text-dark fs-6 me-2"></span>
                <button  type="button" id="todo-next-month_account" class="btn btn-danger btn-sm me-2"> <ion-icon name="arrow-forward-outline"></ion-icon></button>
                <button  type="button" id="todo-current-month_account" class="btn btn-outline-danger fw-bold btn-sm me-5"> <?php echo date("m",time()); ?> 월</button>                
				<button type="button" class="btn btn-dark btn-sm me-1" onclick='location.reload()'>  <i class="bi bi-arrow-clockwise"></i> </button>      
            </div>        
        </div>       
        <div class="col-sm-3"> </div>
        </div>        
        <div id="todo-calendar-container_account" class="d-flex p-1 justify-content-center"></div>
    </div>
</div>
</div>

<div class="container-fluid">     
<?php include $_SERVER['DOCUMENT_ROOT'] .'/footer.php'; ?>
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

document.querySelector('.add-row').addEventListener('click', function() {
    // Open the modal
    var modal = document.getElementById('todoModalMonthly');
    modal.style.display = 'block';
    
    // Fetch data
    fetch('/account/fetch_todoMonthly.php')
        .then(response => response.text())
        .then(data => {
            // Insert the fetched data into the modal's body
            document.querySelector('#todoModalMonthly .custom-card').innerHTML = data;
	               
            $(".todo-close").on("click", function() {
				$("#todoModalMonthly").hide();
			});
            $("#saveBtn_month").on("click", function() {
				// alert('asdfaf');
                    var formData = $("#board_form").serialize();
                    console.log(formData);
                    $.ajax({
                        url: "/todo_account/process_month.php",
                        type: "post",
                        data: formData,
                        success: function(response) {
							console.log(response);
                            Toastify({
                                text: "저장완료",
                                duration: 3000,
                                close: true,
                                gravity: "top",
                                position: "center",
                                backgroundColor: "#4fbe87",
                            }).showToast();
                            $("#todoModalMonthly").hide();
                            
							location.reload();
                        },
                        error: function(jqxhr, status, error) {
                            console.log(jqxhr, status, error);
                        }
                    });
                });
           
                // 월별일정 삭제 버튼
                $("#deleteBtn_month").on("click", function() {                    
                    var user_name = $("#user_name").val();
                    var first_writer = $("#first_writer").val();

                    if (user_name !== first_writer) {
                        Swal.fire({
                            title: '삭제불가',
                            text: "작성자만 삭제 가능합니다.",
                            icon: 'error',
                            confirmButtonText: '확인'
                        });
                        return;
                    }

                    Swal.fire({
                        title: '자료 삭제',
                        text: "삭제는 신중! 정말 삭제하시겠습니까?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: '삭제',
                        cancelButtonText: '취소'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $("#mode").val('delete');
                            var formData = $("#board_form").serialize();

                            $.ajax({
                                url: "/todo_account/process_month.php",
                                type: "post",
                                data: formData,
                                success: function(response) {
                                    Toastify({
                                        text: "일정 삭제완료",
                                        duration: 2000,
                                        close: true,
                                        gravity: "top",
                                        position: "center",
                                        style: {
                                            background: "linear-gradient(to right, #00b09b, #96c93d)"
                                        },
                                    }).showToast();

                                    $("#todoModalMonthly").hide();
							          location.reload();
                                },
                                error: function(jqxhr, status, error) {
                                    console.log(jqxhr, status, error);
                                }
                            });
                        }
                    });
                });
            
			
        })
        .catch(error => console.error('Error fetching the monthly schedule:', error));
});



$(document).on("click", "#closeBtn_month", function() {
	$("#todoModalMonthly").hide();
});


$(document).on('click', '.editable-item', function() {
    var num = $(this).data('num');
    console.log('num',num);
    // Fetch data for the specific item
    $.ajax({
        url: '/account/fetch_todoMonthly.php',
        type: 'post',
        data: { num: num , mode : 'modify' },
        success: function(response) {
            // Populate the modal with the fetched data
            $('#todoModalMonthly .custom-card').html(response);

            // Show the modal
            var modal = document.getElementById('todoModalMonthly');
            modal.style.display = 'block';
			
            $(".todo-close").on("click", function() {
				$("#todoModalMonthly").hide();
			});			

            // Set up the save and delete buttons inside the modal
            $("#saveBtn_month").on("click", function() {
                var formData = $("#board_form").serialize();

                $.ajax({
                    url: "/todo_account/process_month.php",
                    type: "post",
                    data: formData,
                    success: function(response) {
                        console.log(response);
                        Toastify({
                            text: "저장완료",
                            duration: 3000,
                            close: true,
                            gravity: "top",
                            position: "center",
                            backgroundColor: "#4fbe87",
                        }).showToast();
                                    $("#todoModalMonthly").hide();
							          location.reload();
                    },
                    error: function(jqxhr, status, error) {
                        console.log(jqxhr, status, error);
                    }
                });
            });

            $("#deleteBtn_month").on("click", function() {                    
                var user_name = $("#user_name").val();
                var first_writer = $("#first_writer").val();

                if (user_name !== first_writer) {
                    Swal.fire({
                        title: '삭제불가',
                        text: "작성자만 삭제 가능합니다.",
                        icon: 'error',
                        confirmButtonText: '확인'
                    });
                    return;
                }

                Swal.fire({
                    title: '자료 삭제',
                    text: "삭제는 신중! 정말 삭제하시겠습니까?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: '삭제',
                    cancelButtonText: '취소'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $("#mode").val('delete');
                        var formData = $("#board_form").serialize();

                        $.ajax({
                            url: "/todo_account/process_month.php",
                            type: "post",
                            data: formData,
                            success: function(response) {
                                Toastify({
                                    text: "일정 삭제완료",
                                    duration: 2000,
                                    close: true,
                                    gravity: "top",
                                    position: "center",
                                    style: {
                                        background: "linear-gradient(to right, #00b09b, #96c93d)"
                                    },
                                }).showToast();
                                    $("#todoModalMonthly").hide();
							          location.reload();
                            },
                            error: function(jqxhr, status, error) {
                                console.log(jqxhr, status, error);
                            }
                        });
                    }
                });
            });

            $("#closeBtn_month").on("click", function() {
                $("#todoModalMonthly").hide();
            });
        },
        error: function(jqxhr, status, error) {
            console.log(jqxhr, status, error);
        }
    });
});



 </script> 
  