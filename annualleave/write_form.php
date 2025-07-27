<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if(!isset($_SESSION["level"]) || intval($_SESSION["level"])> 1) {
	sleep(1);
	header("Location:" . $WebSite . "login/login_form.php"); 
	exit;
}   

include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';

$title_message = '연차 직원';

$admin = 0;
if(intval($_SESSION["level"]) == 1 )
	$admin = 1;

$tablename='almember';
										  
isset($_REQUEST["num"])  ? $num=$_REQUEST["num"] :   $num=''; 
isset($_REQUEST["mode"])  ? $mode=$_REQUEST["mode"] :  $mode=''; 

 try{
	  $sql = "select * from $DB.$tablename where num = ? ";
	  $stmh = $pdo->prepare($sql); 
      $stmh->bindValue(1,$num,PDO::PARAM_STR); 
      $stmh->execute();
      $count = $stmh->rowCount();            
	  $row = $stmh->fetch(PDO::FETCH_ASSOC);  // $row 배열로 DB 정보를 불러온다.		
		 
	  include $_SERVER['DOCUMENT_ROOT'] . '/almember/_row.php';
	  
	 }catch (PDOException $Exception) {
	   print "오류: ".$Exception->getMessage();
	 }
 // end of if	

// 배열로 기본정보 불러옴
require_once($_SERVER['DOCUMENT_ROOT'] . "/almember/load_DB.php");							
?>  
<title> <?=$title_message?> </title> 
<body>
<form name="board_form" id="board_form" method="post" >
<div class="container-fluid" style="width:400px;">    
	<div class="row d-flex justify-content-center align-items-center" >		
	<div class="card align-middle">		
	 <h4 class="card-title text-center" style="color:#113366;"> 연차 설정 </h4>
		
	 <div class="card-body text-center justify-content-center">	  
		<input type="hidden" id="mode" name="mode">
		<input type="hidden" id="num" name="num" value="<?=$num?>" >
		<input type="hidden" id="user_name" name="user_name" value="<?=$user_name?>" > 	
		<input type="hidden" id="admin" name="admin" value=<?=$admin?>  > 	
	  
		<h5 class="form-signin-heading mb-2">성명</h5>				
			<input type="text" id="name" name="name" class="form-control text-center mb-2" placeholder="성명" required value="<?=$name?>" >
		<h5 class="form-signin-heading mb-2">구분</h5>			 
			<input type="text" id="comment"  name="comment" class="form-control text-center mb-2" placeholder="재직/퇴사" required value="<?=$comment?>" >
		<h5 class="form-signin-heading mb-2">회사</h5>			 
			<input type="text" id="company"  name="company" class="form-control text-center mb-2" placeholder="회사" required value="<?=$company?>" >
		<h5 class="form-signin-heading mb-2">부서</h5>
			<select name="part" id="part" class="form-control  text-center mb-2" >
				<?php	   
					$unique_parts = array_unique($basic_part_arr); // 배열의 고유 값만 추출
					for ($i = 0; $i < count($unique_parts); $i++) {
						if ($part == $unique_parts[$i]) {
							print "<option selected value='" . $unique_parts[$i] . "'> " . $unique_parts[$i] . "</option>";
						} else {   
							print "<option value='" . $unique_parts[$i] . "'> " . $unique_parts[$i] . "</option>";
						}
					} 		   
				?>	  
			</select> 		
		<h5 class="form-signin-heading mb-2">입사일</h5>
		<input type="date" name="dateofentry" class="form-control  text-center mb-2" placeholder="입사일" required value="<?=$dateofentry?>" >
		<h5 class="form-signin-heading mb-2">해당연도</h5>				
		<input type="number" name="referencedate" class="form-control  text-center mb-2" placeholder="해당연도" required value="<?=$referencedate?>" >
		<h5 class="form-signin-heading mb-2">연차 발생일수</h5>			
		<div class="d-flex justify-content-center mb-3">		
			<input type="number" name="availableday" class="form-control  text-center mb-2 w80px" placeholder="발생일수" required  autofocus value="<?=$availableday?>" ><br>
		</div>
											  
		<button id="saveBtn" class="btn btn-dark btn-sm " type="button">
		<? if((int)$num>0) print '수정';  else print '등록'; ?></button>
		<? if((int)$num>0) {  ?>
		<button id="copyBtn" class="btn btn-primary btn-sm" type="button">데이터복사</button>
		<button id="delBtn" class="btn  btn-danger btn-sm" type="button">삭제</button>
		<button id="closeBtn" class="btn btn-secondary btn-sm" type="button">닫기</button>
		<? } ?>			  
		</div>
	</div>
	</div>							  
	</div>							  
</div>				
</form>		
	  	
<!-- 페이지로딩 -->
<script>
var ajaxRequest = null;

$(document).ready(function(){	
    var loader = document.getElementById('loadingOverlay');
	if(loader)
		loader.style.display = 'none';
});
</script>
		  
<script> 
$(document).ready(function(){
	
$("#closeBtn").click(function(){    // 저장하고 창닫기	
	 self.close();
 });	

				
$("#saveBtn").click(function(){      // DATA 저장버튼 누름
   // alert($("#admin").val());
   var admin = $("#admin").val();    
	if(admin=='1')
	{	
	   var num = $("#num").val();  
	   if(Number(num)>0) 
		   $("#mode").val('modify');     
		  else
			  $("#mode").val('insert');     
		  
		 // ajax 요청 생성
		if (ajaxRequest !== null) {
			ajaxRequest.abort();
		} 
		 
		ajaxRequest = $.ajax({
			url: "insert.php",
			type: "post",		
			data: $("#board_form").serialize(),
			dataType:"json",
			success : function( data ){
				console.log( data);
				ajaxRequest = null;
							
				setTimeout(function() {
					opener.location.reload();
					window.close();			
				   }, 1000);				
			},
			error : function( jqxhr , status , error ){
				console.log( jqxhr , status , error );
				ajaxRequest = null;
			} 			      		
		   });		
	  }
		
 }); 
 
$("#copyBtn").click(function(){      // DATA 복사
   // alert($("#admin").val());
   var admin = $("#admin").val();    
	if(admin=='1')
	{	
	   var num = $("#num").val();  
	   
	   $("#mode").val('copy');     
		  
		 // ajax 요청 생성
		if (ajaxRequest !== null) {
			ajaxRequest.abort();
		} 
		 
		ajaxRequest = $.ajax({
			url: "insert.php",
			type: "post",		
			data: $("#board_form").serialize(),
			dataType:"json",
			success : function( data ){
				console.log(data);
				ajaxRequest = null;
							
				setTimeout(function() {
					opener.location.reload();
					window.close();			
				   }, 1000);				
			},
			error : function( jqxhr , status , error ){
				console.log( jqxhr , status , error );
				ajaxRequest = null;
			} 			      		
		   });		
	  }
		
 }); 
 
 $("#delBtn").click(function(){     
   var admin = $("#admin").val();    
	if(admin=='1')
	{	
	   var num = $("#num").val();    	   
			// DATA 삭제버튼 클릭시
				Swal.fire({ 
					   title: '해당 DATA 삭제', 
					   text: " DATA 삭제는 신중하셔야 합니다. '\n 정말 삭제 하시겠습니까?", 
					   icon: 'warning', 
					   showCancelButton: true, 
					   confirmButtonColor: '#3085d6', 
					   cancelButtonColor: '#d33', 
					   confirmButtonText: '삭제', 
					   cancelButtonText: '취소' })
					   .then((result) => { if (result.isConfirmed) { 
					   
					    // 대진표관련 자료 초기화 후 update	
					   $("#mode").val('delete');     
						  
						$.ajax({
							url: "insert.php",
							type: "post",		
							data: $("#board_form").serialize(),
							dataType:"json",
							success : function( data ){
								console.log( data);
								opener.location.reload();
								window.close();			
							},
							error : function( jqxhr , status , error ){
								console.log( jqxhr , status , error );
							} 			      		
						   });	
			   
		   } });		   	   
	}		
 });
}); // end of ready document

</script>
</body>
</html>

