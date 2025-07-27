<?php session_start();

  if(isset($_REQUEST["num"]))  //수정 버튼을 클릭해서 호출했는지 체크
   $num=$_REQUEST["num"];
  else
   $num="";

 ?>

<!DOCTYPE html>
<meta charset="UTF-8">
<html>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<link rel="stylesheet" href="https://uicdn.toast.com/tui.pagination/latest/tui-pagination.css" />
<script src="https://uicdn.toast.com/tui.pagination/latest/tui-pagination.js"></script>
<link rel="stylesheet" href="https://uicdn.toast.com/tui-grid/latest/tui-grid.css"/>
<script src="https://uicdn.toast.com/tui-grid/latest/tui-grid.js"></script>
<!-- CSS only -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.1/font/bootstrap-icons.css">
<!-- 화면에 UI창 알람창 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

<!-- JavaScript Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
<body>
<title> 레이저 작업완료 기록 </title>
<style>
   @import url("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.1/font/bootstrap-icons.css");
</style>


<!-- Modal -->
  <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog  modal-lg modal-center" >
    
      <!-- Modal content-->
      <div class="modal-content modal-lg">
        <div class="modal-header">          
          <h4 class="modal-title">현재 재고 수량 알림</h4>
        </div>
        <div class="modal-body">		
           <div class="row gx-4 gx-lg-4 align-items-center">		  
				   <br>
				   <br>
				   <div id="alertmsg" class="fs-3" > </div> <br>
				  <br>		  									
				  <br>		  									
				</div>
			</div>		  
        <div class="modal-footer">
          <button id="closeModalBtn" type="button" class="btn btn-default" data-dismiss="modal">닫기</button>
        </div>
		</div>
		</div>
      </div>     	
	

	<section class ="d-flex fex-column align-items-left flex-md-row p-1">
	 <div class="p-2 pt-md-3 pb-md-3 text-left" style="width:100%;">	  
		 <form id="mainFrm" method="post" enctype="multipart/form-data" >		
            <input type="hidden" id="num" name="num" value=<?=$num?> > 
		    <div class="card-header"> 	
                  <span class="text-primary">  (천장) 레이져 완료 기록하기 </span>
			</div>			
			<div class="card-header"> 
					 <div class="input-group p-2 mb-1">							 
						<label> <input type="checkbox" name="checkList" value="1"> 본천장 완료 &nbsp;	&nbsp;	</label>
						<label> <input type="checkbox" name="checkList" value="2"> LC 완료 &nbsp;	&nbsp;	</label>
						<label> <input type="checkbox" name="checkList" value="3"> 인테리어 완료 &nbsp;	&nbsp;	</label>
					 </div>
						
		 		</div>		
						 <div class="input-group p-2 mb-1">							 
						<button  type="button" id="saveBtn"  class="btn btn-secondary" > 기록하기 </button> &nbsp;								    						
						<button  type="button" id="closeBtn"  class="btn btn-outline-secondary" > 창닫기 </button> &nbsp;								    						
						
		 		</div>						
			
			 	     
			<div id="tmpdiv"> </div>
	     	
			</form>		
			
  <form id=Form1 name="Form1">
    <input type=hidden id="checkarr" name="checkarr[]" >
  </form>  			
              
</div>
		  
</section>
</body>
</html>

		  
 <script> 

$(document).ready(function(){

	$("#saveBtn").click(function(){    // 저장하고 창닫기	
	     var checkarr = [];
	    $('input:checkbox[name=checkList]').each(function (index) {
				if($(this).is(":checked")==true){
					console.log($(this).val());
					checkarr.push($(this).val());
				}
			});
	    
	    // data저장을 위한 ajax처리구문
		$.ajax({
			url: "record_process.php?num=" + $('#num').val() + "&checkarr=" + checkarr ,
			type: "post",					
			success : function( data ){
				console.log( data);
				alertmodal('자료를 저장합니다.', 1500);
				
			},
			error : function( jqxhr , status , error ){
				console.log( jqxhr , status , error );
			} 			      		
		   });		
	
		 });			
		 
		$("#closeBtn").click(function(){    // 저장하고 창닫기		  	
		  window.close();
		 });			
		 
function alertmodal(tmp, second)
{	
	$('#alertmsg').html(tmp); 			  
	$('#myModal').modal('show'); 	
	
	setTimeout(function() {
	$('#myModal').modal('hide');  
	window.close();
	}, second);		
	
}		 
		 

				
}); // end of ready document


  </script>
    

