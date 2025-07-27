<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if(!isset($_SESSION["level"]) || $_SESSION["level"]>5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php"); 
    exit;
}   
include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';
// 첫 화면 표시 문구
$title_message = '매입처 주소록'; ?>
 
<title> <?=$title_message?> </title>

</head>

<body>	
	 
<?php

$option = isset($_REQUEST['option']) ? $_REQUEST['option'] : '';  
$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';  
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  
$header = isset($_REQUEST['header']) ? $_REQUEST['header'] : '';  
  
$tablename = 'phonebook_buy';
  	  
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();
    
$num = isset($_REQUEST["num"]) ? $_REQUEST["num"] : 0;

// 수정일 경우
if($num > 0) {	
    try {
        $sql = "SELECT * FROM ". $DB . "." . $tablename . " WHERE num = ?";
        $stmh = $pdo->prepare($sql);  
        $stmh->bindValue(1, $num, PDO::PARAM_INT);      
        $stmh->execute();            
        $row = $stmh->fetch(PDO::FETCH_ASSOC); 	 
        include '_row.php';
    } catch (PDOException $Exception) {
        print "오류: ".$Exception->getMessage();
    }      	

    if($option !== 'add') {
        $mode = 'update';
    } else {
        $mode = 'insert';		 	
        $manager_name = '';
        $contact_info = '010-';	
        $title_message = '업체 담당자 추가화면 '; 
        $parentnum = $num;
    }
} else {
    include '_request.php';
    $mode = 'insert';
    $representative_name = $search;
    $manager_name = $search;
    $phone = '010-';
}

?>

<form id="board_form" name="board_form" method="post" enctype="multipart/form-data">			 

    <input type="hidden" id="mode" name="mode" value="<?=$mode?>">             
    <input type="hidden" id="num" name="num" value="<?=$num?>"> 	
    <input type="hidden" id="tablename" name="tablename" value="<?=$tablename?>"> 	
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
                                <td class="text-center fw-bold">거래처코드<br>(사업자번호)</td>
                                <td class="text-center">
                                    <input type="text" class="form-control" id="vendor_code" name="vendor_code" value="<?=$vendor_code?>"> 
                                </td>		
                                <td class="text-center fw-bold" style="width:150px;">거래처 명</td>
                                <td class="text-center">
                                    <input type="text" class="form-control" id="vendor_name" name="vendor_name" style="width:250px;" value="<?=$vendor_name?>">    
                                </td>												
                            </tr>	
                            <tr>

                                <td class="text-center fw-bold" style="width:170px;">대표자 성함</td>
                                <td class="text-center fw-bold">
                                    <input type="text" class="form-control" id="representative_name" name="representative_name" style="width:200px;" value="<?=$representative_name?>">  
                                </td>                            
                                <td class="text-center fw-bold" style="width:150px;">대상품목(아이템)</td>
                                <td class="text-center">
                                    <input type="text" class="form-control" id="item" name="item" style="width:250px;" value="<?=$item?>">    
                                </td>				
                            </tr>							
                            <tr>
                                <td class="text-center fw-bold">주소</td>
                                <td colspan="3" class="text-center">
                                    <input type="text" class="form-control" id="address" name="address" value="<?=$address?>">   							
                                </td>
                            </tr>
                            <tr>				
                                <td class="text-center fw-bold">업태</td>
                                <td class="text-center">
                                    <input type="text" class="form-control" id="business_type" name="business_type" value="<?=$business_type?>">     
                                </td>							
                                <td class="text-center fw-bold">종목</td>	
                                <td class="text-center">
                                    <input type="text" class="form-control" id="item_type" name="item_type" value="<?=$item_type?>">  
                                </td>																					
                            </tr>
                            <tr>				
                                <td class="text-center fw-bold">전화</td>
                                <td class="text-center">
                                    <input type="text" class="form-control" id="phone" name="phone" value="<?=$phone?>">  
                                </td>							
                                <td class="text-center fw-bold">모바일</td>	
                                <td class="text-center">
                                    <input type="text" class="form-control" id="mobile" name="mobile" value="<?=$mobile?>"> 
                                </td>																					
                            </tr>
                            <tr>				
                                <td class="text-center fw-bold">이메일</td>
                                <td class="text-center">
                                    <input type="email" class="form-control" id="email" name="email" value="<?=$email?>">       
                                </td>							
                                <td class="text-center fw-bold">팩스</td>	
                                <td class="text-center">
                                    <input type="text" class="form-control" id="fax" name="fax" value="<?=$fax?>">    
                                </td>																					
                            </tr>
                            <tr>				
                                <td class="text-center fw-bold">담당자명</td>
                                <td class="text-center">
                                    <input type="text" class="form-control" id="manager_name" name="manager_name" value="<?=$manager_name?>">       
                                </td>							
                                <td class="text-center fw-bold">담당자Tel</td>	
                                <td class="text-center">
                                    <input type="text" class="form-control" id="contact_info" name="contact_info" value="<?=$contact_info?>">      
                                </td>																					
                            </tr>
                            <tr>
                                <td class="text-center fw-bold">비고</td>
                                <td colspan="3" class="text-center">
                                    <textarea class="form-control" id="note" name="note"><?=$note?></textarea>    							
                                </td>
                            </tr>						
                        </tbody>
                    </table>   
                </div>
            </div>            
				<div class="d-flex justify-content-center">
					<button type="button" id="saveBtn"  class="btn btn-dark btn-sm me-3">
						<i class="bi bi-floppy-fill"></i> 저장
					</button>					
					<button type="button"  id="closeBtn" class="btn btn-outline-dark btn-sm me-2">
						&times; 닫기
					</button>									
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
</script>
	 
<script>
ajaxRequest_write = null;

$(document).ready(function(){	  
    // 창닫기 버튼
    $("#closeBtn").on("click", function() {
        self.close();
    });	
	
    // 저장 버튼 서버에 저장하고 Ecount 전송함
    $("#saveBtn").on("click", function() {
        var header = $("#header").val();
        let msg = '저장완료';

        if (ajaxRequest_write !== null) {
            ajaxRequest_write.abort();
        }		 
        ajaxRequest_write = $.ajax({
            url: "process.php",
            type: "post",		
            data: $("#board_form").serialize(),								
            success: function(data) {		
                console.log(data);			
                Toastify({
                    text: msg,
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "center",
                    backgroundColor: "#4fbe87",
                }).showToast();			
                
                if(header !== 'header'){											
                    // 부모창 실행
                    if($("#manager_name").val() !== '')
                        $("#search", opener.document).val($("#manager_name").val()); 
                    else
                        $("#search", opener.document).val($("#representative_name").val()); 
                }
                
                $(opener.location).attr("href", "javascript:reloadlist();");	

                setTimeout(function() {
                    // 창 닫기
                    self.close();								   
                }, 500);				
            },
            error: function(jqxhr, status, error) {
                console.log(jqxhr, status, error);
            } 			      		
        });												
    });			
});	 
</script>
