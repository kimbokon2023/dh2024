<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if(!isset($_SESSION["level"]) || $_SESSION["level"]>5) {
	sleep(1);
	header("Location:" . $WebSite . "login/login_form.php"); 
	exit;
}   

include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';

// 첫 화면 표시 문구
$title_message = '발주처 주소록'; 
 ?>
 
<link href="css/style.css" rel="stylesheet" >   

<title> <?=$title_message?> </title>

</head>

<body>	
	 
<?php

$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';  
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  
  
$tablename = 'workbook';
  	  
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();
    
$num = isset($_REQUEST["num"]) ? $_REQUEST["num"] : 0;

// 수정일 경우
if($num>0)
{	
 $SelectWork = 'update';
   
	try{
     $sql = "select * from ". $DB . "." . $tablename . " where num=?";
     $stmh = $pdo->prepare($sql);  
     $stmh->bindValue(1, $num, PDO::PARAM_STR);      
     $stmh->execute();            
      
     $row = $stmh->fetch(PDO::FETCH_ASSOC); 	 
		include '_row.php';

     }catch (PDOException $Exception) {
       print "오류: ".$Exception->getMessage();
     }      	
}

else
{
	$SelectWork = 'insert';
	$phone = '010-';
	$vendor_name = $_REQUEST['vendor_name'] ?? '';	
	$address = $_REQUEST['address'] ?? '';	
	$contact_info = $_REQUEST['contact_info'] ?? '';
	$note = $_REQUEST['note'] ?? '';	
	
}
 
 
	?>	


<form id="board_form" name="board_form" method="post" enctype="multipart/form-data">			 

	<input type="hidden" id="SelectWork" name="SelectWork" value="<?=$SelectWork?>">             
	<input type="hidden" id="num" name="num" value=<?=$num?> > 	
	<input type="hidden" id="mode" name="mode" value=<?=$mode?> > 
	<input type="hidden" id="tablename" name="tablename" value=<?=$tablename?> > 	


<div class="container-fluid" >					
<div class="card justify-content-center text-center mt-1 mb-2" >
	<div class="card-header">
		<span class="text-center fs-5" > 연락처  </span>								
	</div>
	<div class="card-body">                                
		<div class="row justify-content-center text-center">  

			<div class="d-flex row">    
				<div class="input-group mb-1">                                        
					<span class="input-group-text" style="width:250px;">성명</span>
					<input type="text" class="form-control" id="vendor_name" name="vendor_name" value="<?=$vendor_name?>">                                                    
				</div>                                            
			</div>   

			<div class="d-flex row">    
				<div class="input-group mb-1">                                        
					<span class="input-group-text" style="width:250px;">전화</span>
					<input type="text" class="form-control" id="phone" name="phone" value="<?=$phone?>">                                                    
				</div>                                            
			</div>   
			

			<div class="d-flex row">    
				<div class="input-group mb-1">                                        
					<span class="input-group-text" style="width:250px;">소재지</span>
					<input type="text" class="form-control" id="address" name="address" value="<?=$address?>">                                                    
				</div>                                            
			</div>   			


			<div class="d-flex row">    
				<div class="input-group mb-1">                                        
					<span class="input-group-text" style="width:250px;"> 팀인원 </span>
					<input type="text" class="form-control" id="contact_info" name="contact_info" value="<?=$contact_info?>">                                                    
				</div>                                            
			</div>   

			<div class="d-flex row">    
				<div class="input-group mb-1">                                        
					<span class="input-group-text" style="width:250px;">비고</span>
					<textarea class="form-control" id="note" name="note"><?=$note?></textarea>                                                    
				</div>                                            
			</div>                                            

		</div>
	</div>

	<div class="card-footer justify-content-start">
		<button type="button" id="saveBtn"  class="btn btn-dark btn-sm mx-2">
			<i class="bi bi-floppy-fill"></i> 저장
		</button>
		<button type="button"  id="closeBtn" class="btn btn-outline-dark btn-sm me-2">
			 &times; 닫기
		</button>
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
/* ESC 키 누를시 팝업 닫기 */
// $(document).keydown(function(e){
		// //keyCode 구 브라우저, which 현재 브라우저
		// var code = e.keyCode || e.which;
		
		// if (code == 27) { // 27은 ESC 키번호
			// self.close();
		// }
// });

$(document).ready(function(){	  
	 
	// 창닫기 버튼
	$("#closeBtn").on("click", function() {
		self.close();
	});	
	
// 저장 버튼 서버에 저장하고 Ecount 전송함
$("#saveBtn").on("click", function() {
		
	$("#SelectWork").val("<?php echo $SelectWork; ?>");		
	
	let msg = '저장완료';
									
		$.ajax({
			url: "process.php",
			type: "post",		
			data: $("#board_form").serialize(),								
			success : function( data ){		

		    console.log(data);			
														
			 Toastify({
					text: msg ,
					duration: 3000,
					close:true,
					gravity:"top",
					position: "center",
					backgroundColor: "#4fbe87",
				}).showToast();			
						
                 // 부모창 실행
				 if($("#manager_name").val() !== '')
						$("#search", opener.document).val(  $("#vendor_name").val()  ); 
					else
						$("#search", opener.document).val(  $("#vendor_name").val()  ); 
				
				$(opener.location).attr("href", "javascript:reloadlist();");	

				setTimeout(function() {
					  // 창 닫기
					   self.close();								   
				   }, 500);				

			},
			error : function( jqxhr , status , error ){
				console.log( jqxhr , status , error );
				   } 			      		
		});												

	});			
	
});	  // end of ready
	
</script>