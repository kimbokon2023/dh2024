<meta charset="utf-8">
 
 <?php
 session_start(); 
  
 $num=$_REQUEST["num"]; 
 $parent=$num;
 $workername = $_REQUEST["workername"];
 
  if(isset($_REQUEST["check"])) 
	 $check=$_REQUEST["check"]; 
   else
     $check=$_POST["check"]; 
 
 require_once("../lib/mydb.php");
 $pdo = db_connect();
	 
 try{
     $sql = "select * from mirae8440.work where num=?";
     $stmh = $pdo->prepare($sql);  
     $stmh->bindValue(1, $num, PDO::PARAM_STR);      
     $stmh->execute();            
      
     $row = $stmh->fetch(PDO::FETCH_ASSOC); 	 
	 
     $workplacename=$row["workplacename"]; 
		 
     }catch (PDOException $Exception) {
       print "오류: ".$Exception->getMessage();
  } 
  
// 첨부이미지에 대한 부분
 
if($num!=null && $num!=0)
{	
 require_once("../lib/mydb.php");
 $pdo = db_connect();
 
 // 실측서 이미지 이미 있는 것 불러오기 
$picData=array(); 
$tablename='work';
$item = 'measure';

$sql=" select * from mirae8440.picuploads where tablename ='$tablename' and item ='$item' and parentnum ='$num' ";	

 try{  
   $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh   
   while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
			array_push($picData, $row["picname"]);			
        }		 
   } catch (PDOException $Exception) {
    print "오류: ".$Exception->getMessage();
  }  
$picNum=count($picData);  
}
 ?>
 <!DOCTYPE HTML>
 <html>
 <head> 
 <meta charset="utf-8">
    
<!-- Bootstrap CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" >
	<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>	    
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" ></script> 
<link rel="stylesheet" href="../css/partner.css" type="text/css" />

 <title> 실측서 이미지 등록/수정/삭제 </title>
 </head>
  <body>
<div id="top-menu">
<?php
    if(!isset($_SESSION["userid"]))
	{
?>
          <a href="../login/login_form.php">로그인</a> | <a href="../member/insertForm.php">회원가입</a>
<?php
	}
	else
	 {
?>
			<div class="row">           
			<div class="col">           
		         <h1 class="display-5 font-center text-left"> <br>
	<?=$_SESSION["name"]?> | 
		<a href="../login/logout.php">로그아웃</a> | <a href="../member/updateForm.php?id=<?=$_SESSION["userid"]?>">정보수정</a>
		
<?php
	 }
?>
</h1>
</div>
</div>

    <!-- Modal -->
  <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog  modal-lg modal-center" >
    
      <!-- Modal content-->
      <div class="modal-content modal-lg">
        <div class="modal-header">          
          <h4 class="modal-title">알림</h4>
        </div>
        <div class="modal-body">		
		   <div id=alertmsg class="fs-1 mb-5 justify-content-center" >
		     결재가 진행중입니다. <br> 
		   <br> 
		  수정사항이 있으면 결재권자에게 말씀해 주세요.
			</div>
        </div>
        <div class="modal-footer">
          <button type="button" id="closeModalBtn" class="btn btn-default" data-dismiss="modal">닫기</button>
        </div>
      </div>
      
    </div>
  </div>
			




<br> 
			<div class="row">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<h1 class="display-1  text-left">
				<input type="button" class="btn btn-secondary btn-lg " value="이전화면으로 돌아가기" onclick="location.href='./view.php?num=<?=$num?>&check=<?=$check?>&workername=<?=$workername ?>'"> </h1> 
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			</div>
<br>
<br>
<form id="board_form" name="board_form" method="post" enctype="multipart/form-data">  
     
	 
	 <input type="hidden" id=check name=check value="<?=$check?>" >	 
	 <input type="hidden" id=num name=num value="<?=$num?>" >
	 <input type="hidden" id=mode name=mode value="<?=$mode?>" >	 	 
	 <input type="hidden" id=item name=item value="<?=$item?>" >	 
	 <input type="hidden" id="tablename" name="tablename" value="<?=$tablename?>"  > 
	 <input id="pInput" name="pInput" type=hidden value="0" >		 
	 
	 <div  class="container">
			<div class="row">
				 <H1  class="display-5 font-center text-center" > 실측서 이미지 등록/수정/삭제 </H1> 
				 <H2  class="display-5 text-danger" > 설계자에게 전달할 추가 내용이 있으면 등록합니다. </H2> 
			</div>
 
		
			<br>
			<div class="d-flex p-2">

			 <h1 class="display-5 font-center text-left"> 		   
					현장명 :   <?=$workplacename?> 	    </h1>
			</div>
			<div class="row">
			
 				<span class="form-control">
				<h2 class="display-5 text-left text-secondary" > 				
				<span style="color:gray">실측서 이미지파일 </span>	                  
                   <input id="upfile"  name="upfile[]" class="input" type="file" onchange="this.value" multiple accept=".gif, .jpg, .png">
				  </h2>
				</span>	
	     </div>    		
   
	   </div> 
<div  class="container">	   
				<div class="card-body text-center">
				 <h2 class="display-5 text-center text-secondary" > 	
				    실측서 이미지
				 </h2>
					<div id = "displayPicture" class="mb-2" style="display:none;" >  </div>   
					<br>					
				</div>	   
</div>
	   
	   
 </form>
	 
 </body>
</html>    

<script>
$(document).ready(function(){
	
$("#pInput").val('50'); // 최초화면 사진파일 보여주기
	
let timer3 = setInterval(() => {  // 2초 간격으로 사진업데이트 체크한다.
	      if($("#pInput").val()=='100')   // 사진이 등록된 경우
		  {
	             displayPicture();  
				 // console.log(100);
		  }	      
		  if($("#pInput").val()=='50')   // 사진이 등록된 경우
		  {
	             displayPictureLoad();				 
		  }	     
		   
	 }, 2000);	
	 
  
delPicFn = function(divID, delChoice) {
	console.log(divID, delChoice);
if(confirm("한번 삭제한 자료는 복구할 방법이 없습니다.\n\n정말 삭제하시겠습니까?")) {
	$.ajax({
		url:'delpic.php?picname=' + delChoice ,
		type:'post',
		data: $("board_form").serialize(),
		dataType: 'json',
		}).done(function(data){						
		   const picname = data["picname"];		   
		   
		  // 시공전사진 삭제 
			$("#pic" + divID).remove();  // 그림요소 삭제
			$("#delPic" + divID).remove();  // 그림요소 삭제
		    $("#pInput").val('');						
			
        });		
    }
}
	  	 
	 
// 시공전 사진 멀티업로드	
$("#upfile").change(function(e) {	    
        // 실측서 이미지 선택
	    $("#item").val('measure');
	    var item = $("#item").val();
		FileProcess(item);	
});	 
	
function FileProcess(item) {
//do whatever you want here
num = $("#num").val();

  // 사진 서버에 저장하는 구간	
  // 사진 서버에 저장하는 구간	
        //tablename 설정
       $("#tablename").val('work');  
		// 폼데이터 전송시 사용함 Get form         
		var form = $('#board_form')[0];  	    
		// Create an FormData object          
		var data = new FormData(form); 

		tmp='사진을 저장중입니다. 잠시만 기다려주세요.';		
		$('#alertmsg').html(tmp); 			  
		$('#myModal').modal('show'); 	

		$.ajax({
			enctype: 'multipart/form-data',  // file을 서버에 전송하려면 이렇게 해야 함 주의
			processData: false,    
			contentType: false,      
			cache: false,           
			timeout: 600000, 			
			url: "mspic_insert.php",
			type: "post",		
			data: data,						
			success : function(data){
				console.log(data);
				// opener.location.reload();
				// window.close();	
				setTimeout(function() {
					$('#myModal').modal('hide');  
					}, 1000);	
                // 사진이 등록되었으면 100 입력됨
                 $("#pInput").val('100');							

			},
			error : function( jqxhr , status , error ){
				console.log( jqxhr , status , error );
						} 			      		
		   });	

}		   
 
 
	 	 
$("#closeModalBtn").click(function(){ 
    $('#myModal').modal('hide');
});
		
$("#closeBtn").click(function(){    // 저장하고 창닫기	
	 });	
	
	

}); // end of ready document
 

// 시공전 이미지 불러오기
function displayPicture() {       
	$('#displayPicture').show();
	params = $("#num").val();	
	$("#tablename").val('work');
	$("#item").val('measure');	
	
    var tablename = $("#tablename").val();    
    var item = $("#item").val();	
	
	$.ajax({
		url:'load_pic.php?num=' + params + '&tablename=' + tablename + '&item=' + item ,
		type:'post',
		data: $("board_form").serialize(),
		dataType: 'json',
		}).done(function(data){						
		   const recnum = data["recnum"];		   
		   console.log(data);
		   $("#displayPicture").html('');
		   for(i=0;i<recnum;i++) {			   
			   $("#displayPicture").append("<img id=pic" + i + " src ='../uploads/" + data["img_arr"][i] + "' style='width:100%; '  > <br> " );			   
         	   $("#displayPicture").append("&nbsp;<button type='button' class='mt-2 btn btn-secondary' id='delPic" + i + "' onclick=delPicFn('" + i + "','" +  data["img_arr"][i] + "')> 삭제 </button>&nbsp; <br>");					   
		      }		   
			    $("#pInput").val('');			
    });	
}

// 시공전 기존 있는 이미지 화면에 보여주기
function displayPictureLoad() {    
	$('#displayPicture').show();
	var picNum = "<? echo $picNum; ?>"; 					
	var picData = <?php echo json_encode($picData);?> ;	
	console.log(picNum);
	console.log(picData);
    for(i=0;i<picNum;i++) {
       $("#displayPicture").append("<img id=pic" + i + " src ='../uploads/" + picData[i] + "' style='width:100%;' > <br>" );			
	   $("#displayPicture").append("&nbsp;<button type='button' class='mt-2 btn btn-secondary' id='delPic" + i + "' onclick=delPicFn('" + i + "','" + picData[i] + "')> 삭제 </button>&nbsp;<br>");			   
	  }		   
		$("#pInput").val('');	
}


</script>
