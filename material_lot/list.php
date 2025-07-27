<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php");
    exit;
}   
include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';
$title_message = '로트번호 생성관리'; 
?>
 
<link href="css/style.css" rel="stylesheet">   
<title> <?=$title_message?> </title>

<style>
.container-move {
    font-family: Arial, sans-serif;
    display: flex;
    font-size: 1.4rem;
    justify-content: center;
    align-items: center;
    overflow: hidden;
    width: 100%;
}
.description-move {
    font-family: Arial, sans-serif;
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: hidden;
    width: 100%;
}
.container-move .part, .description-move .desc-part {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.5s ease-in-out, transform 0.5s ease-in-out;
}
.container-move .part.show, .description-move .desc-part.show {
    opacity: 1;
    transform: translateY(0);
}

.description-move {
    font-size: 1rem;
    color: #666;
}
/* 체크박스 크기 크게 */
input[type="checkbox"] {
    transform: scale(1.6); /* 크기를 1.5배로 확대 */
    margin-right: 10px;   /* 확대 후의 여백 조정 */
}
</style>
</head>
<body>         

<?php
$header = isset($_REQUEST['header']) ? $_REQUEST['header'] : '';  

if ($header == 'header')
    require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php');

function checkNull($strtmp) {
    return ($strtmp !== null && trim($strtmp) !== '');
}

$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';  
$mode = isset($_REQUEST["mode"]) ? $_REQUEST["mode"] : '';

$tablename = 'material_lot';

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();
 
$order = " ORDER BY registedate DESC";
    
if (checkNull($search)) {
    $sql = "SELECT * FROM ".$DB.".".$tablename." 
            WHERE searchtag LIKE '%$search%' AND is_deleted IS NULL " . $order;    
} else {
    $sql = "SELECT * FROM ".$DB.".".$tablename . " WHERE is_deleted IS NULL " . $order;   
}

try {      
    $stmh = $pdo->query($sql);
    $total_row = $stmh->rowCount();        
?>    

<form id="board_form" name="board_form" method="post" enctype="multipart/form-data">             
    <input type="hidden" id="mode" name="mode" value="<?=$mode?>">             
    <input type="hidden" id="num" name="num"> 
    <input type="hidden" id="tablename" name="tablename" value="<?=$tablename?>">                 
    <input type="hidden" id="header" name="header" value="<?=$header?>">      

<div class="container-fluid">
    <!-- Modal -->
    <div id="myModal" class="modal">
        <div class="modal-content"  style="width:1000px;">
            <div class="modal-header">
                <span class="modal-title">로트번호 관리(생성/수정)</span>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <div class="custom-card"></div>
            </div>
        </div>
    </div>
</div>

<?php 
if ($header !== 'header') {
    print '<div class="container-fluid">';
    print '<div class="card justify-content-center text-center mt-1">';
} else {
    print '<div class="container">';
    print '<div class="card justify-content-center text-center mt-5">';
}
?>     
    <div class="card-header align-items-center ">
        <span class="text-center fs-5">  <?=$title_message?>   </span>     
		<button type="button" class="btn btn-dark btn-sm mx-1" onclick='location.reload()'>  <i class="bi bi-arrow-clockwise"></i> </button>      						 
		 <?php if($chkMobile==false) { ?>
			<div class="container">     
		 <?php } else { ?>
			<div class="container-fluid">     
			<?php } ?>
		 
		<?php
			// Get current date in MMDD format
			$now_tmp = date('md');
		?>
		<div class="card mt-2 mb-2">
		<div class="card-body">
		<div class="row">	

		  <div class="container-move">
				<div id="part1" class="part">(주) 대한 DH모터 로트번호 생성규칙 : </div>
				<div id="part2" class="part">DH</div>
				<div id="part3" class="part">-</div>
				<div id="part4" class="part">M</div>
				<div id="part5" class="part">-</div>
				<div id="part6" class="part">0424</div>				
			</div>
			<div class="description-move">
				<div id="desc1" class="desc-part">DH</div>
				<div id="desc2" class="desc-part">-</div>
				<div id="desc3" class="desc-part">M , M(방범), C , B , T , F </div>
				<div id="desc4" class="desc-part">-</div>
				<div id="desc5" class="desc-part">0424(중국제조번호)</div>				
				<div id="desc6" class="desc-part"> 참고바랍니다. M-모터, M(방범) - 방범용 모터, C-연동제어기, B-브라켓트, T-콘트롤박스, F-원단, 수동입력 </div>
			</div>
		</div>
		</div>
		</div>
		</div>

    </div>
    <div class="card-body">  
    <div class="container mt-2 mb-2">   
    ▷ <?= $total_row ?> &nbsp; 
    <div class="inputWrap30">            
        <input type="text" id="search" class="form-control" style="width:150px;" name="search" value="<?=$search?>" onKeyPress="if (event.keyCode==13){ enter(); }">
        <button class="btnClear"></button>
    </div>                            
    &nbsp;&nbsp;
    <button class="btn btn-outline-dark btn-sm" type="button" id="searchBtn"> <i class="bi bi-search"></i> </button> &nbsp;&nbsp;&nbsp;&nbsp;        
    
    <button id="newBtn" type="button" class="btn btn-dark btn-sm me-2"> <i class="bi bi-pencil-square"></i> 신규 </button>    
    <?php if($header !== 'header') 
            print '<button id="closeBtn" type="button" class="btn btn-outline-dark btn-sm"> <i class="bi bi-x-lg"></i> 창닫기 </button>';
    ?>            
    </div>        
        
    <div class="table-responsive">   
       <table class="table table-hover" id="myTable">         
            <thead class="table-primary">
                 <th class="text-center">번호</th>
                 <th class="text-center">등록일자</th>
                 <th class="text-center w-25" >로트번호</th>                 
                 <th class="text-center">업데이트 로그</th>
            </thead>
            <tbody>                  
            <?php          
            $start_num = $total_row;                  
            while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
                include '_row.php';                                        
            ?>                     
            <tr onclick="loadForm('update', '<?=$num?>');">
                <td class="text-center"><?= $start_num ?></td>
                <td class="text-center"><?= $registedate ?></td>
                <td class="text-center fs-6 fw-bold"><?= $lotnum ?></td>                
                <td class="text-center"><?= $update_log ?></td>
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
    </div>
    </div>        
</form>

</body>
</html>

<script>
var ajaxRequest = null;

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

			document.getElementById('select-all').addEventListener('change', function() {
				var checkboxes = document.querySelectorAll('.record-checkbox');
				for (var checkbox of checkboxes) {
					checkbox.checked = this.checked;
				}
			});			

			var manualInputCheckbox = document.getElementById('manual_input_checkbox');
			var selectAllCheckbox = document.getElementById('select-all');

            $("#closeBtn").on("click", function() {
                $("#myModal").hide();
            });
            
			// 저장 버튼
			$("#saveBtn").on("click", function() {
				var header = $("#header").val();
				var china_num = $("#china_num").val();
				var manual_input_checkbox = $("#manual_input_checkbox").is(':checked') ? 'checked' : '';
				var manual_input = $("#manual_input").val();
				var lot_types = [];

				$("input[name='lot_type[]']:checked").each(function() {
					lot_types.push($(this).val());
				});

				if (lot_types.length === 0 && manual_input_checkbox === '') {
					alert('하나 이상 필드를 선택하거나 수동 입력을 체크해주세요.');
					return;
				}

				let msg = '저장완료';
				let lotnum;
				let requests = [];

				if (lot_types.length > 0 && manual_input_checkbox === '') {
					requests = lot_types.map(type => {
						if (china_num == '') {
							lotnum = `${type}`;
						} else {
							lotnum = `DH-${type}-${china_num}`;
						}

						console.log('Request Data:', {
							mode: $("#mode").val(),
							num: $("#num").val(),
							tablename: $("#tablename").val(),
							header: $("#header").val(),
							update_log: $("#update_log").val(),
							registedate: $("#registedate").val(),
							lotnum: lotnum,
							manual_input_checkbox: manual_input_checkbox,
							manual_input: manual_input
						});

						return $.ajax({
							url: "process.php",
							type: "post",
							data: {
								mode: $("#mode").val(),
								num: $("#num").val(),
								tablename: $("#tablename").val(),
								header: $("#header").val(),
								update_log: $("#update_log").val(),
								registedate: $("#registedate").val(),
								lotnum: lotnum,
								manual_input_checkbox: manual_input_checkbox,
								manual_input: manual_input
							},
							dataType: "json"
						});
					});
				} else if (manual_input_checkbox === 'checked') {
					lotnum = `DH-${manual_input}`;

					console.log('Request Data:', {
						mode: $("#mode").val(),
						num: $("#num").val(),
						tablename: $("#tablename").val(),
						header: $("#header").val(),
						update_log: $("#update_log").val(),
						registedate: $("#registedate").val(),
						lotnum: lotnum,
						manual_input_checkbox: manual_input_checkbox,
						manual_input: manual_input
					});

					requests.push($.ajax({
						url: "process.php",
						type: "post",
						data: {
							mode: $("#mode").val(),
							num: $("#num").val(),
							tablename: $("#tablename").val(),
							header: $("#header").val(),
							update_log: $("#update_log").val(),
							registedate: $("#registedate").val(),
							lotnum: lotnum,
							manual_input_checkbox: manual_input_checkbox,
							manual_input: manual_input
						},
						dataType: "json"
					}));
				}

				$.when.apply($, requests).then(function() {
					Toastify({
						text: msg,
						duration: 3000,
						close: true,
						gravity: "top",
						position: "center"
					}).showToast();

					$("#myModal").hide();
					location.reload();
				}).fail(function(jqxhr, status, error) {
					console.log('AJAX Error:', jqxhr, status, error);
				});
			});

            // 삭제 버튼
            $("#deleteBtn").click(function() {
                var level = '<?= $_SESSION["level"] ?>';

                if (level !== '1') {
                    Swal.fire({
                        title: '삭제불가',
                        text: "관리자만 삭제 가능합니다.",
                        icon: 'error',
                        confirmButtonText: '확인'
                    });
                } else {
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
                            var form = $('#board_form')[0];
                            var formData = new FormData(form);

                            formData.set('mode', $("#mode").val());
                            formData.set('num', $("#num").val());

                            $.ajax({
                                enctype: 'multipart/form-data',
                                processData: false,
                                contentType: false,
                                cache: false,
                                timeout: 1000000,
                                url: "process.php",
                                type: "post",
                                data: formData,
                                dataType: "json",
                                success: function(data) {
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
                                    location.reload();
                                },
                                error: function(jqxhr, status, error) {
                                    console.log(jqxhr, status, error);
                                }
                            });
                        }
                    });
                }
            });

            $("#closeBtn").on("click", function() {
                var modal = document.getElementById("myModal");
                modal.style.display = "none";
            });
			
		
				manualInputCheckbox.addEventListener('change', () => {
					if (manualInputCheckbox.checked) {
						selectAllCheckbox.checked = false;
						const checkboxes = document.querySelectorAll('.record-checkbox');
						checkboxes.forEach(checkbox => {
							checkbox.checked = false;
						});
					}
				});

				selectAllCheckbox.addEventListener('change', () => {
					if (selectAllCheckbox.checked) {
						manualInputCheckbox.checked = false;
					}
				});
        },
        error: function(jqxhr, status, error) {
            console.log("AJAX Error: ", status, error);
        }
    });
}

function restorePageNumber() {
    var savedPageNumber = getCookie('material_lotpageNumber');
    location.reload(true);
}

$(document).ready(function(){    
    var loader = document.getElementById('loadingOverlay');
    loader.style.display = 'none';

    var modal = document.getElementById("myModal");
    var span = document.getElementsByClassName("close")[0];

    span.onclick = function() {
        modal.style.display = "none";
    }

    $("#newBtn").on("click", function() {        
        loadForm('insert');
    });

    $("#searchBtn").on("click", function() {
        $("#board_form").submit();
    });
    
    $("#closeBtn").on("click", function() {
        var modal = document.getElementById("myModal");
        modal.style.display = "none";
    });
	
		const parts = document.querySelectorAll('.part');
				const descParts = document.querySelectorAll('.desc-part');
				let index = 0;

				function showNextPart() {
					if (index < parts.length) {
						parts[index].classList.add('show');
						index++;
						setTimeout(showNextPart, 500); // Adjust the delay for each part to show up
					} else if (index - parts.length < descParts.length) {
						descParts[index - parts.length].classList.add('show');
						index++;
						setTimeout(showNextPart, 500); // Adjust the delay for each desc part to show up
					}
				}

				showNextPart();

	
	
});

function enter() {
    $("#board_form").submit();
}

$(document).ready(function(){
	saveLogData('로트번호 생성관리'); 
});

</script>
