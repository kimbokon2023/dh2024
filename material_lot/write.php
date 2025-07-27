<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php");
    exit;
}   

include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';

// 첫 화면 표시 문구
$title_message = '로트번호 생성'; 
?>
 
<title> <?=$title_message?> </title>

</head>

<body>    
     
<?php

$option = isset($_REQUEST['option']) ? $_REQUEST['option'] : '';  
$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';  
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  
$header = isset($_REQUEST['header']) ? $_REQUEST['header'] : '';  
  
$tablename = 'material_lot';
      
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();
    
$num = isset($_REQUEST["num"]) ? $_REQUEST["num"] : 0;

// Default values

$lotnum = '';
$china_num = '';
$korea_date = date('md');
$lot_type_checked = ['M', 'C', 'B'];

if ($num > 0) {    
    try {
        $sql = "SELECT * FROM ". $DB . "." . $tablename . " WHERE num=?";
        $stmh = $pdo->prepare($sql);  
        $stmh->bindValue(1, $num, PDO::PARAM_STR);      
        $stmh->execute();            
          
        $row = $stmh->fetch(PDO::FETCH_ASSOC);     
        include '_row.php';

        // Parse lotnum to populate form fields
        $lotnum_parts = explode('-', $lotnum);
        if (count($lotnum_parts) === 4) {
            $lot_type_checked = [$lotnum_parts[1]];
            $china_num = $lotnum_parts[2];
            $korea_date = $lotnum_parts[3];
        }

    } catch (PDOException $Exception) {
        print "오류: ".$Exception->getMessage();
    }       
     
    if ($option !== 'add') {
        $mode = 'update';
    } else {
        $mode = 'insert';           
        $title_message = '로트번호 추가화면'; 
        $parentnum = $num;
    }
         
} else {
    include '_request.php';
    
    $mode = 'insert';
	$registedate = date('Y-m-d');
}

?>    

<form id="board_form" name="board_form" method="post" enctype="multipart/form-data">             

    <input type="hidden" id="mode" name="mode" value="<?=$mode?>">             
    <input type="hidden" id="num" name="num" value="<?=$num?>">     
    <input type="hidden" id="tablename" name="tablename" value="<?=$tablename?>">     
    <input type="hidden" id="header" name="header" value="<?=$header?>">     
    <input type="hidden" id="update_log" name="update_log" value="<?=$update_log?>">     

<div class="container-fluid">                    
    <div class="d-flex align-items-center justify-content-center">
    <div class="card justify-content-center">
        <div class="card-header text-center">
            <span class="text-center fs-5"><?=$title_message?></span>                                
        </div>
    <div class="card-body">                                
        <div class="row justify-content-center text-center">  
            <div class="d-flex align-items-center justify-content-center m-2">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <td class="text-center fs-5 fw-bold" style="width:150px;">등록일자</td>
                            <td class="text-center"> 
                                <input type="date" class="form-control fs-5" id="registedate" name="registedate" style="width:150px;" value="<?=$registedate?>"> 
                            </td>
							
                        </tr>
                        <tr>
                            <td class="text-center fs-5 fw-bold" style="width:150px;">로트번호 생성</td>
                            <td class="text-center fs-5" >
							  <div class="d-flex align-items-center justify-content-start">
									<span class="fs-4 me-2"> DH-</span>
									<label><input type="checkbox" name="lot_type[]" class="fs-4 me-2" value="M" <?= in_array('M', $lot_type_checked) ? 'checked' : '' ?>> M (모터)     &nbsp;&nbsp;</label>
									<label><input type="checkbox" name="lot_type[]" class="fs-4 me-2" value="C" <?= in_array('C', $lot_type_checked) ? 'checked' : '' ?>> C (연동제어기) &nbsp;&nbsp;</label>
									<label><input type="checkbox" name="lot_type[]" class="fs-4 me-2" value="B" <?= in_array('B', $lot_type_checked) ? 'checked' : '' ?>> B (브라켓트)  &nbsp;&nbsp; </label>
									<span class="fs-4"> - </span>
								</div>
								<div class="d-flex align-items-center justify-content-start">
										<span class="text-center " style="width:150px;">중국 제조번호</span>
										<span class="text-center"> 
											<input type="text" class="form-control fs-5" id="china_num" name="china_num" style="width:100px;" value="<?=$china_num?>"> 
										</span>
									</div>
									<div class="d-flex align-items-center justify-content-start mt-2 mb-2">
										<span class="text-center " style="width:150px;">대한 입고월일</span>
										<span class="text-center"> 
											<input type="text" class="form-control fs-5" id="korea_date" name="korea_date" style="width:100px;" value="<?=$korea_date?>"> 
										</span> 
									</div>
                            </td>
                        </tr>
                    </tbody>
                </table>   
            </div>
        </div>

        <div class="d-flex justify-content-center">
            <button type="button" id="saveBtn" class="btn btn-dark btn-sm me-3">
                <ion-icon name="save-outline"></ion-icon> 저장 
            </button>  
            <button type="button" id="deleteBtn" class="btn btn-danger btn-sm me-3">
                <ion-icon name="trash-outline"></ion-icon> 삭제
            </button>
            <button type="button" id="closeBtn" class="btn btn-outline-dark btn-sm me-2">
                <ion-icon name="close-circle-outline"></ion-icon> Close
            </button>
        </div>
    </div>
    </div>
    </div>
</div>

</form>
</body>
</html>

<!-- 페이지로딩 -->
<script>
// 페이지 로딩
$(document).ready(function(){    
    var loader = document.getElementById('loadingOverlay');
    loader.style.display = 'none';
});

ajaxRequest_write = null;

$(document).ready(function(){      
     
    // 창닫기 버튼
    $("#closeBtn").on("click", function() {
        self.close();
    });    
    
    // 저장 버튼 서버에 저장하고 Ecount 전송함
    $("#saveBtn").on("click", function() {
        
        var header = $("#header").val();
        var china_num = $("#china_num").val();
        var korea_date = $("#korea_date").val();
        var lot_types = [];
        $("input[name='lot_type[]']:checked").each(function() {
            lot_types.push($(this).val());
        });

        if (china_num === '' || korea_date === '' || lot_types.length === 0) {
            alert('모든 필드를 입력해주세요.');
            return;
        }

        let msg = '저장완료';
        
        if (ajaxRequest_write !== null) {
            ajaxRequest_write.abort();
        }         

        let requests = lot_types.map(type => {
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
                    lotnum: `DH-${type}-${china_num}-${korea_date}`
                },
                dataType: "json"
            });
        });

        $.when.apply($, requests).then(function() {
            Toastify({
                text: msg,
                duration: 3000,
                close: true,
                gravity: "top",
                position: "center",
                backgroundColor: "#4fbe87",
            }).showToast();

            $(opener.location).attr("href", "javascript:reloadlist();");

            setTimeout(function() {
                self.close();
            }, 1000);
        }).fail(function(jqxhr, status, error) {
            console.log(jqxhr, status, error);
        });
    });            
    

    // 삭제 버튼 클릭 시
    $("#deleteBtn").click(function() {
        var level = '<?php echo $_SESSION["level"]; ?>';
        
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

                    if ((typeof ajaxRequest_write !== 'undefined' && ajaxRequest_write) || ajaxRequest_write !== null) {
                        ajaxRequest_write.abort();
                    }

                    ajaxRequest_write = $.ajax({
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
                            setTimeout(function() {
                                if (window.opener && !window.opener.closed) {
                                    if (typeof window.opener.restorePageNumber === 'function') {
                                        window.opener.restorePageNumber();
                                    }
                                    window.opener.location.reload();
                                    window.close();
                                }
                            }, 1000);
                        },
                        error: function(jqxhr, status, error) {
                            console.log(jqxhr, status, error);
                        }
                    });
                }
            });
        }
    });
	
	
}); // end of ready

</script>
