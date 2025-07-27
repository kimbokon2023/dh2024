<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if(!isset($_SESSION["name"])) {	          
	$_SESSION["url"] = 'https://dh2024.co.kr/annualleave/index.php?user_name=' . $user_name; 	
	sleep(1);
    header ("Location:https://dh2024.co.kr/login/logout.php");
    exit;
} 

$title_message = '연차 직원 관리'; 
include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php'; 
?>

<title>  <?=$title_message?> </title>
<link href="css/style.css" rel="stylesheet">   
</head>

<body>

<?php  
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();		

$admin = ($level === '1') ? 1 : 0;

$tablename = "almember";
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';
$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';

$AndisDeleted = " AND is_deleted IS NULL ";
$WhereisDeleted = " WHERE is_deleted IS NULL ";

if($search == "") {		
	$sql = "SELECT * FROM $DB.$tablename $WhereisDeleted ORDER BY dateofentry DESC";		
}
else
	$sql = "SELECT * FROM $DB.$tablename WHERE name LIKE '%$search%' $AndisDeleted ORDER BY dateofentry DESC";		
try {  
	$stmh = $pdo->query($sql);
    $total_row = $stmh->rowCount();	 									 
?>   

<form name="board_form" id="board_form" method="post" action="list.php">  

	<input type="hidden" id="mode" name="mode" value="<?=$mode?>"  > 					
	<input type="hidden" id="level" name="level" value="<?=$level?>"  > 					
	<input type="hidden" id="num" name="num" value="<?=$num?>"  > 					

	<?php if($chkMobile == false) { ?>
		<div class="container-fluid">     
	<?php } else { ?>
		<div class="container-fluid">     
	<?php } ?>	 

	<div class="card"> 						
	<div class="card-body"> 							
	<div class="d-flex justify-content-center mt-3 mb-3">
		<span class=" fs-5">  <?=$title_message?> </span>				
	</div>				


<div class="d-flex justify-content-center align-items-center mt-2 mb-2">   
	&nbsp;&nbsp;&nbsp; ▷ <?= $total_row ?>  &nbsp;&nbsp;&nbsp; 		    
	 
	<input type="text" name="search" id="search" class="form-control me-1" style="width:150px;" value="<?=$search?>" onkeydown="JavaScript:SearchEnter();" placeholder="검색어"> 	   
	<button type="button" id="searchBtn" class="btn btn-dark btn-sm me-1"> <ion-icon name="search-outline"></ion-icon> </button>	
	<button type="button" id="writeBtn" class="btn btn-dark btn-sm mx-2"> <i class="bi bi-pencil-square"></i> 등록 </button> 	
	<button  type="button"  class="btn btn-dark btn-sm me-1" onclick="window.close();"> <ion-icon name="close-outline"></ion-icon> 창닫기 </button>
</div>
   	
<div class="d-flex justify-content-center">  	
	 <table class="table table-hover" id="myTable">		
	     <thead class="table-primary" >
		     <tr>
				<th class="text-center" style="width:70px;" >번호</th>
				<th class="text-center" style="width:100px;" >성명</th>
				<th class="text-center" style="width:100px;" >회사</th>
				<th class="text-center" style="width:100px;" >부서</th>
				<th class="text-center" style="width:100px;" >입사일</th>
				<th class="text-center" style="width:100px;" >해당연도</th>
				<th class="text-center" style="width:180px;" >연차 발생일수</th>
				<th class="text-center" style="width:150px;" >구분</th>
			</tr>
		 </thead>
	 <tbody>
	 <?php
		$start_num = $total_row; 	    
		while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
			include "_row.php";
		?>
			<tr onclick="view('<?=$num?>');">        
				<td class="text-center"><?=$start_num?></td>
				<td class="text-center"><?=$name?></td>
				<td class="text-center"><?=$company?></td>
				<td class="text-center"><?=$part?></td>
				<td class="text-center"><?=$dateofentry?></td>                        
				<td class="text-center"><?=$referencedate?></td>                        
				<td class="text-center"><?=$availableday?></td>                        
				<td class="text-center"><?=$comment?></td>                        
			</tr>	
		<?php
			$start_num--;  
		} 
	} catch (PDOException $Exception) {
	    print "오류: ".$Exception->getMessage();
	}  
 ?>
       </tbody>
      </table>
	</div>
	<div class="d-flex justify-content-center mt-5 mb-5"></div>   
 </div>   
 </div>   
 </div>   
 </div>   

 <!-- Modal -->
 <div id="myModal" class="modal">
	<div class="modal-content">
		<div class="modal-header">
			<span class="modal-title">회원관리</span>
			<span class="close">&times;</span>
		</div>
		<div class="modal-body">
			<div class="custom-card"></div>
		</div>
	</div>
</div>

</form>
</body>
</html> 

<script>
$(document).ready(function(){	
	var loader = document.getElementById('loadingOverlay');
	loader.style.display = 'none';

	$("#writeBtn").on("click", function() {		
        loadForm('insert');
    });

	$("#searchBtn").on("click", function() {
        $("#board_form").submit();
    });

    $(document).on('click', '.specialbtnClear', function(e) {
        e.preventDefault(); 
        $(this).siblings('input').val('').focus();
    });

    $(document).on('click', '.btnClear_lot', function(e) {
        e.preventDefault(); 
        $(this).siblings('input').val('').focus();
    });

});

var ajaxRequest_write = null;
var dataTable;
var annualleavepageNumber;

$(document).ready(function() {			
	dataTable = $('#myTable').DataTable({
		"paging": true,
		"ordering": true,
		"searching": true,
		"pageLength": 15,
		"lengthMenu": [15, 50, 100, 200, 500, 1000],
		"language": {
			"lengthMenu": "Show _MENU_ entries",
			"search": "Live Search:"
		},
		"order": [[5, 'desc']]
	});

	var savedPageNumber = getCookie('annualleavepageNumber');
	if (savedPageNumber) {
		dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
	}

	dataTable.on('page.dt', function() {
		var annualleavepageNumber = dataTable.page.info().page + 1;
		setCookie('annualleavepageNumber', annualleavepageNumber, 10);
	});

	$('#myTable_length select').on('change', function() {
		var selectedValue = $(this).val();
		dataTable.page.len(selectedValue).draw();

		savedPageNumber = getCookie('annualleavepageNumber');
		if (savedPageNumber) {
			dataTable.page(parseInt(savedPageNumber) - 1).draw(false);
		}
	});
});

function restorePageNumber() {
	var savedPageNumber = getCookie('annualleavepageNumber');
	location.reload(true);
}

function view(num) {
	loadForm('update', num);
}

function loadForm(mode, num = null) {
    if (num == null) {
        $("#mode").val('insert');
    } else {
        $("#mode").val('update');
        $("#num").val(num);
    }       

    $.ajax({
        type: "POST",
        url: "fetch_modal.php",
        data: { mode: mode, num: num },
        dataType: "html",
        success: function(response) {
            document.querySelector(".modal-body .custom-card").innerHTML = response;
            $("#myModal").show();

            $("#closeBtn").on("click", function() {
                $("#myModal").hide();
            });

            $(document).on('click', '.specialbtnClear', function(e) {
                e.preventDefault(); 
                $(this).siblings('input').val('').focus();
            });

            $(document).on('click', '.btnClear_lot', function(e) {
                e.preventDefault(); 
                $(this).siblings('input').val('').focus();
            });

            $("#saveBtn").on("click", function() {
                var formData = $("#board_form").serialize();

                $.ajax({
                    url: "process.php",
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

                        $("#myModal").hide();
                        location.reload();
                    },
                    error: function(jqxhr, status, error) {
                        console.log(jqxhr, status, error);
                    }
                });
            });

            $("#deleteBtn").on("click", function() {
                var level = '<?= $_SESSION["level"] ?>';

                if (level !== '1') {
                    Swal.fire({
                        title: '삭제불가',
                        text: "관리자만 삭제 가능합니다.",
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
                            url: "process.php",
                            type: "post",
                            data: formData,
                            success: function(response) {
								console.log(response);
                                Toastify({
                                    text: "파일 삭제완료",
                                    duration: 2000,
                                    close: true,
                                    gravity: "top",
                                    position: "center",
                                    style: {
                                        background: "linear-gradient(to right, #00b09b, #96c93d)"
                                    },
                                }).showToast();

                                $("#myModal").hide();
                               // location.reload();
                            },
                            error: function(jqxhr, status, error) {
                                console.log(jqxhr, status, error);
                            }
                        });
                    }
                });
            });
        },
        error: function(jqxhr, status, error) {
            console.log("AJAX Error: ", status, error);
        }
    });
}

function SearchEnter(){
    if(event.keyCode == 13){
		document.getElementById('board_form').submit(); 
    }
}
</script>
