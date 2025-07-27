<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if(!isset($_SESSION["level"]) || $_SESSION["level"]>5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php"); 
    exit;
}   

include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';

// 첫 화면 표시 문구
$title_message = '품목코드'; 
?>
 
<title> <?=$title_message?> </title>

</head>

<body>    
     
<?php

$option = isset($_REQUEST['option']) ? $_REQUEST['option'] : '';  
$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';  
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  
$header = isset($_REQUEST['header']) ? $_REQUEST['header'] : '';  
  
$tablename = 'material';
      
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();
    
$num = isset($_REQUEST["num"]) ? $_REQUEST["num"] : 0;

// 수정일 경우
if($num>0)
{    
    try {
        $sql = "SELECT * FROM ". $DB . "." . $tablename . " WHERE num=?";
        $stmh = $pdo->prepare($sql);  
        $stmh->bindValue(1, $num, PDO::PARAM_STR);      
        $stmh->execute();            
          
        $row = $stmh->fetch(PDO::FETCH_ASSOC);     
        include '_row.php';

    } catch (PDOException $Exception) {
        print "오류: ".$Exception->getMessage();
    }       
     
    if($option !=='add') {
        $mode = 'update';
    } else {
        $mode = 'insert';           
        $title_message = '품목 추가화면'; 
        $parentnum = $num;
    }
         
} else {
    include '_request.php';
    
    $mode = 'insert';
}

?>    


<form id="board_form" name="board_form" method="post" enctype="multipart/form-data">             

    <input type="hidden" id="mode" name="mode" value="<?=$mode?>">             
    <input type="hidden" id="num" name="num" value=<?=$num?> >     
    <input type="hidden" id="tablename" name="tablename" value=<?=$tablename?> >     
    <input type="hidden" id="header" name="header" value="<?=$header?>" >     
    <input type="hidden" id="update_log" name="update_log" value="<?=$update_log?>" >     


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
                            <td class="text-center fw-bold" style="width:150px;">품목코드</td>
                            <td class="text-center"> 
                                <input type="text" class="form-control" id="item_code" name="item_code" style="width:200px;" value="<?=$item_code?>"> 
                            </td>
                            <td class="text-center fw-bold" style="width:150px;">품목명</td>
                            <td class="text-center"> 
                                <input type="text" class="form-control" id="item_name" name="item_name" style="width:300px;"  value="<?=$item_name?>"> 
                            </td>                
                        </tr>
                        <tr>
                            <td class="text-center fw-bold" style="width:150px;">규격</td>
                            <td class="text-center"> 
                                <input type="text" class="form-control" id="spec" name="spec" value="<?=$spec?>"> 
							</td>								
                            <td class="text-center fw-bold" style="width:150px;">단위</td>
                            <td class="text-center"> 
                                <input type="text" class="form-control" id="unit" name="unit" value="<?=$unit?>"> 
                            </td>                                        
                        
                        </tr>
                        <tr>
                            
                            <td class="text-center fw-bold" style="width:150px;">입고단가</td>
                            <td class="text-center"> 
                                <input type="text" class="form-control" id="in_price" name="in_price" value="<?=$in_price?>"> 
                            </td>                                        
                            <td class="text-center fw-bold" style="width:150px;">출고단가</td>
                            <td class="text-center"> 
                                <input type="text" class="form-control" id="out_price" name="out_price" value="<?=$out_price?>"> 
                            </td>                
                        </tr>
                        <tr>
							                 

                        </tr>
                    </tbody>
                </table>   



		   </div>
        </div>

        <div class="d-flex justify-content-center">
            <button type="button" id="saveBtn" class="btn btn-dark btn-sm me-3">
                <ion-icon name="save-outline"></ion-icon> 저장 
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
                
        let msg = '저장완료';
        
        if (ajaxRequest_write !== null) {
            ajaxRequest_write.abort();
        }         
        ajaxRequest_write = $.ajax({
            url: "process.php",
            type: "post",        
            data: $("#board_form").serialize(),                                
            success : function(data) {        
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
                     if($("#item_name").val() !== '')
                            $("#search", opener.document).val($("#item_name").val()); 
                     else
                            $("#search", opener.document).val($("#item_code").val()); 
                }
                
                $(opener.location).attr("href", "javascript:reloadlist();");    

                setTimeout(function() {
                      // 창 닫기
                       self.close();                                   
                   }, 500);                

            },
            error : function(jqxhr, status, error) {
                console.log(jqxhr, status, error);
            }                   
        });                                                

    });            
    
}); // end of ready

</script>
