<?php

session_start(); 

isset($_REQUEST["id"])  ? $id=$_REQUEST["id"] :   $id=''; 
isset($_REQUEST["fileorimage"])  ? $fileorimage=$_REQUEST["fileorimage"] :   $fileorimage=''; // file or image
isset($_REQUEST["item"])  ? $item=$_REQUEST["item"] :   $item=''; 
isset($_REQUEST["upfilename"])  ? $upfilename=$_REQUEST["upfilename"] :   $upfilename=''; 
isset($_REQUEST["tablename"])  ? $tablename=$_REQUEST["tablename"] :  $tablename=''; 
isset($_REQUEST["savetitle"])  ? $savetitle=$_REQUEST["savetitle"] :  $savetitle='';   // log기록 저장 타이틀
isset($_REQUEST["DB"])  ? $DB=$_REQUEST["DB"] :  $DB='';  

// print '$DB' . $DB;
    
  if(isset($_REQUEST["mode"]))  //수정 버튼을 클릭해서 호출했는지 체크
   $mode=$_REQUEST["mode"];
  else
   $mode="";
  
  if(isset($_REQUEST["num"]))  //수정 버튼을 클릭해서 호출했는지 체크
   $num=$_REQUEST["num"];
  else
   $num="";
          
  require_once("../lib/mydb.php");
  $pdo = db_connect();

  if ($mode=="modify"){
    try{
      $sql = "select * from mirae8440." . $DB . " where num = ? ";
      $stmh = $pdo->prepare($sql); 

    $stmh->bindValue(1,$num,PDO::PARAM_STR); 
      $stmh->execute();
      $count = $stmh->rowCount();              
    if($count<1){  
      print "검색결과가 없습니다.<br>";
     }else{
      $row = $stmh->fetch(PDO::FETCH_ASSOC);
      $item_subject = $row["subject"];
      $is_html = $row["is_html"];
      $item_content = $row["content"];
      $qnacheck = $row["qnacheck"];
      }
     }catch (PDOException $Exception) {
       print "오류: ".$Exception->getMessage();
     }
  }

// 초기 프로그램은 $num사용 이후 $id로 수정중임  
$id=$num;  

// 첨부 파일에 대한 읽어오는 부분
 require_once("../lib/mydb.php");
 $pdo = db_connect();
 
// 첨부파일 있는 것 불러오기 
$savefilename_arr=array(); 
$realname_arr=array(); 
$tablename=$DB;
$item = 'attached';

$sql=" select * from mirae8440.fileuploads where tablename ='$tablename' and item ='$item' and parentid ='$id' ";	

 try{  
   $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh   
   while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
			array_push($realname_arr, $row["realname"]);			
			array_push($savefilename_arr, $row["savename"]);			
        }		 
   } catch (PDOException $Exception) {
    print "오류: ".$Exception->getMessage();
  }   

// 첨부 이미지 있는 것 불러오기 
$realimagename_arr=array(); 
$saveimagename_arr=array(); 
$item = 'image';

$sql=" select * from mirae8440.fileuploads where tablename ='$tablename' and item ='$item' and parentid ='$id' ";	

 try{  
   $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh   
   while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
			array_push($realimagename_arr, $row["realname"]);							   
			array_push($saveimagename_arr, $row["savename"]);		
        }		 
   } catch (PDOException $Exception) {
    print "오류: ".$Exception->getMessage();
  }   

// 신규데이터 작성시 키값지정 parentid값이 없으면 데이터 저장안됨
$timekey = date("Y_m_d_H_i_s");
  
?>

 <!DOCTYPE HTML>
 <html>
 <head>
 <meta charset="UTF-8">
 <link rel="stylesheet" type="text/css" href="../css/common.css"> 
 <!-- JavaScript -->
<script src="//cdn.jsdelivr.net/npm/alertifyjs@1.12.0/build/alertify.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<!-- CSS -->
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.12.0/build/css/alertify.min.css"/>
<!-- Default theme -->
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.12.0/build/css/themes/default.min.css"/>
<!-- Semantic UI theme -->
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.12.0/build/css/themes/semantic.min.css"/>
<!-- Bootstrap theme -->
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.12.0/build/css/themes/bootstrap.min.css"/>	
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">   <!--날짜 선택 창 UI 필요 -->


<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
 
<!-- 최초화면에서 보여주는 상단메뉴 -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" >
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.1/font/bootstrap-icons.css">
<style>
a { text-decoration:none;
  color:gray;
}
</style>
  
<title> QC 공정표 </title> 
 
 </head>
 

<body>

<?php include "../common/modal.php"; ?>

<div class="container-fluid">  
<div class="d-flex mb-1 justify-content-center">    
  <a href="../index.php"><img src="../img/toplogo.jpg" style="width:100%;" ></a>	
</div>

<? include '../myheader.php'; ?>   

</div>  

<div class="container">  
 <div class="d-flex mt-3 mb-1 justify-content-center">  
  <h3> QC 공정표 </h3>  </div>	 
   
  <?php
    if($mode=="modify"){
	// form id 반드시 확인 
  ?>
	<form  id="board_form" name="board_form" method="post" action="insert.php?mode=modify&num=<?=$num?>&DB=<?=$DB?>" enctype="multipart/form-data"> 
  <?php  } else {
  ?>
	<form  id="board_form" name="board_form" method="post" action="insert.php?DB=<?=$DB?>" enctype="multipart/form-data"> 
  <?php
	}
  ?>
  
  <!-- 전달함수 설정 input hidden -->
	<input type="hidden" id="id" name="id" value="<?=$id?>" >			  								
	<input type="hidden" id="parentid" name="parentid" value="<?=$parentid?>" >			  								
	<input type="hidden" id="fileorimage" name="fileorimage" value="<?=$fileorimage?>" >			  								
	<input type="hidden" id="item" name="item" value="<?=$item?>" >			  								
	<input type="hidden" id="upfilename" name="upfilename" value="<?=$upfilename?>" >			  								
	<input type="hidden" id="tablename" name="tablename" value="<?=$tablename?>" >			  								
	<input type="hidden" id="savetitle" name="savetitle" value="<?=$savetitle?>" >			  								
	<input type="hidden" id="pInput" name="pInput" value="<?=$pInput?>" >			  								
	<input type="hidden" id="mode" name="mode" value="<?=$mode?>" >		
	<input type="hidden" id="timekey" name="timekey" value="<?=$timekey?>" >  <!-- 신규데이터 작성시 parentid key값으로 사용 -->				
	
  
 <div class="d-flex mt-3 mb-1 justify-content-center">  
 작성자  : &nbsp;    <?=$_SESSION["nick"]?>  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;        
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<button type="button"   class="btn btn-dark btn-sm" onclick="form.submit();" > 완료 </button>		&nbsp;										
        <button type="button"   class="btn btn-secondary  btn-sm" onclick="location.href='list.php'" > 목록(List) </button>	&nbsp;
</div>
 <div class="d-flex mt-3 mb-1 justify-content-center">  
	
		 QC 공정표명  &nbsp;      
		 <input name="subject" type="text" required class="col3"  size="80" <?php if($mode=="modify"){ ?>value="<?=$item_subject?>" <?php }?>>&nbsp;
 </div>
 <div class="d-flex mt-3 mb-1 justify-content-center fs-4">  	  
            내 용 
</div>
 <div class="d-flex mt-3 mb-1 justify-content-center">  	 
 <textarea rows="5" cols="120" name="content" required><?php if($mode=="modify") print trim($item_content); ?></textarea>
 </div>
 
 <div class="d-flex mt-3 mb-1 justify-content-center">  	 		 
			 <label for="upfile" class="input-group-text btn btn-outline-primary btn-sm"> 파일(10M 이하) pdf파일 첨부 </label>						  							
			 <input id="upfile"  name="upfile[]" type="file" onchange="this.value" multiple  style="display:none" >
	</div>	
	
<div id ="displayfile" class="d-flex mt-3 mb-1 justify-content-center" style="display:none;">  	 		 					
	 
</div>			
<div id ="displayimage" class="row d-flex mt-3 mb-1 justify-content-center" style="display:none;">  	 		 					
	 
</div>		
	
	<div class="d-flex mt-3 mb-1 justify-content-center">  	 	
			<label  for="upfileimage" class="input-group-text btn btn-outline-dark btn-sm ">  사진 첨부 </label>	
			 <input id="upfileimage"  name="upfileimage[]" type="file" onchange="this.value" multiple accept=".gif, .jpg, .png" style="display:none">
	</div>	
	
	</form>
  	</div>  
 </body>
 </html>
 
<script> 

$(document).ready(function(){	

	$("#pInput").val('50'); // 최초화면 사진파일 보여주기
	
let timer3 = setInterval(() => {  // 2초 간격으로 사진업데이트 체크한다.
	      if($("#pInput").val()=='100')   // 사진이 등록된 경우
		  {
	             displayfile();  
	             displayimage();  
				 // console.log(100);
		  }	      
		  if($("#pInput").val()=='50')   // 사진이 등록된 경우
		  {
	             displayfileLoad();				 
	             displayimage();				 
		  }		   
	 }, 500);	
	 
  
delPicFn = function(divID, delChoice) {
	console.log(divID, delChoice);

	$.ajax({
		url:'../file/del_file.php?savename=' + delChoice ,
		type:'post',
		data: $("board_form").serialize(),
		dataType: 'json',
		}).done(function(data){						
		   const savename = data["savename"];		   
		   
		  // 시공전사진 삭제 
			$("#file" + divID).remove();  // 그림요소 삭제
			$("#delPic" + divID).remove();  // 그림요소 삭제
		    $("#pInput").val('');					
			
        });		

}
	    
delPicimageFn = function(divID, delChoice) {
	console.log(divID, delChoice);

	$.ajax({
		url:'../file/del_file.php?savename=' + delChoice ,
		type:'post',
		data: $("board_form").serialize(),
		dataType: 'json',
		}).done(function(data){						
		   const savename = data["savename"];		   
		   
		  // 시공전사진 삭제 
			$("#image" + divID).remove();  // 그림요소 삭제
			$("#delPicimage" + divID).remove();  // 그림요소 삭제
		    $("#pInput").val('');					
			
        });		

}
	  	 
	 
// 첨부파일 멀티업로드	
$("#upfile").change(function(e) {	    
	    $("#id").val('<?php echo $id;?>');
	    $("#parentid").val('<?php echo $id;?>');
	    $("#fileorimage").val('file');
	    $("#item").val('attached');
	    $("#upfilename").val('upfile');
	    $("#savetitle").val('자료실 첨부파일');		
	
		// 임시번호 부여 id-> parentid 시간초까지 나타내는 변수로 저장 후 저장하지 않으면 삭제함	
	 if(Number($("#id").val()) == 0) 
	      $("#id").val($("#timekey").val());   // 임시번호 부여 id-> parentid
	  
	  // 파일 서버에 저장하는 구간	
			// 폼데이터 전송시 사용함 Get form         
			var form = $('#board_form')[0];  	    
			// Create an FormData object          
			var data = new FormData(form); 			

			tmp='파일을 저장중입니다. 잠시만 기다려주세요.';		
			$('#alertmsg').html(tmp); 			  
			$('#myModal').modal('show'); 		

			$.ajax({
				enctype: 'multipart/form-data',  // file을 서버에 전송하려면 이렇게 해야 함 주의
				processData: false,    
				contentType: false,      
				cache: false,           
				timeout: 600000, 			
				url: "../file/file_insert.php",
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

});		   
 
 	 	  	 
	 
// 첨부 이미지 멀티업로드	
$("#upfileimage").change(function(e) {	    
	    $("#id").val('<?php echo $id;?>');
	    $("#parentid").val('<?php echo $id;?>');
	    $("#fileorimage").val('file');
	    $("#item").val('image');
	    $("#upfilename").val('upfileimage');
	    $("#savetitle").val('자료실 이미지');		
	
		// 임시번호 부여 id-> parentid 시간초까지 나타내는 변수로 저장 후 저장하지 않으면 삭제함	
	 if(Number($("#id").val()) == 0) 
	      $("#id").val($("#timekey").val());   // 임시번호 부여 id-> parentid
	  
	  // 파일 서버에 저장하는 구간	
			// 폼데이터 전송시 사용함 Get form         
			var form = $('#board_form')[0];  	    
			// Create an FormData object          
			var data = new FormData(form); 			

			tmp='파일을 저장중입니다. 잠시만 기다려주세요.';		
			$('#alertmsg').html(tmp); 			  
			$('#myModal').modal('show'); 		

			$.ajax({
				enctype: 'multipart/form-data',  // file을 서버에 전송하려면 이렇게 해야 함 주의
				processData: false,    
				contentType: false,      
				cache: false,           
				timeout: 600000, 			
				url: "../file/file_insert.php",
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

});		   
 

// 첨부된 이미지 불러오기
function displayimage() {       
	$('#displayimage').show();
	params = $("#id").val();	
	
	var tablename = $("#tablename").val();      
    var item = 'image';
	
	$.ajax({
		url:'../file/load_file.php?id=' + params + '&tablename=' + tablename + '&item=' + item ,
		type:'post',
		data: $("board_form").serialize(),
		dataType: 'json',
		}).done(function(data){						
		   const recid = data["recid"];		   
		   console.log(data);
		   $("#displayimage").html('');
		   for(i=0;i<recid;i++) {	
			   $("#displayimage").append("<img id='image" + i + "' src='../uploads/" + data['file_arr'][i] + "' style='width:80%;' > &nbsp; <br> &nbsp;  " );			   
         	   $("#displayimage").append("&nbsp;<button type='button' class='btn btn-outline-danger btn-sm' id='delPicimage" + i + "' onclick=delPicimageFn('" + i + "','" + data["file_arr"][i] + "')> 삭제 </button>&nbsp; <br>");					   
		      }		   
			    $("#pInput").val('');			
    });	
}

// 기존 있는 이미지 화면에 보여주기
function displayimageLoad() {    

	$('#displayimage').show();			
	var saveimagename_arr = <?php echo json_encode($saveimagename_arr);?> ;	
	
    for(i=0;i<saveimagename_arr.length;i++) {
			   $("#displayimage").append("<img id='image" + i + "'src='../uploads/" + saveimagename_arr[i] + "' style='width:80%;' >&nbsp;  <br> &nbsp; " );			   
         	   $("#displayimage").append("&nbsp;<button type='button' class='btn btn-outline-danger btn-sm' id='delPicimage" + i + "' onclick=delPicimageFn('" + i + "','" +  savefilename_arr[i] + "')> 삭제 </button>&nbsp; <br>");					   
	  }		   
		$("#pInput").val('');	
}
	 
 
 	 	 
$("#closeModalBtn").click(function(){ 
    $('#myModal').modal('hide');
}); 	 

// 하단복사 버튼
$("#closeBtn1").click(function(){ 
   $("#closeBtn").click();
})
	
$("#closeBtn").click(function(){    // 저장하고 창닫기	
    opener.location.reload();	
	self.close();
});	

			
// 자료의 삽입/수정하는 모듈 
function Fninsert() {	 
		   
	console.log($("#mode").val());    
	
// item을 다중으로 입력하기 위한 루틴
	var data2 = $('#board_form').serializeArray();
	// var data3 = $('#board_form').serialize();
	
	// var arr = {};
		// Object.keys(data2).forEach(d => {    
			// arr[data2[d]] = d;
		// });
		// console.log(arr);

	// 폼데이터 전송시 사용함 Get form         
	var form = $('#board_form')[0];  	    
	// Create an FormData object          
	var data = new FormData(form); 
  
	tmp='파일을 저장중입니다. 잠시만 기다려주세요.';		
	$('#alertmsg').html(tmp); 			  
	$('#myModal').modal('show'); 	    

	$.ajax({
		enctype: 'multipart/form-data',    // file을 서버에 전송하려면 이렇게 해야 함 주의
		processData: false,    
		contentType: false,      
		cache: false,           
		timeout: 600000, 			
		url: "insert.php",
		type: "post",		
		data: data,			
		// dataType:"text",  // text형태로 보냄
		success : function(data){
			
				console.log(data);

				// opener.location.reload();
				// window.close();	
				setTimeout(function() {
					$('#myModal').modal('hide');  
					}, 1000);
				
				if($("#mode").val()=='insert' || $("#mode").val()=='copy' || $("#mode").val()=='makesub')  // 삽입인 경우는 목록으로 이동
				   {					   
									   
						  //var str=data;
						  var words = data.split('|');				  
						  console.log(words);
						  location.href='write_form.php?id=' + words[0] +'&parent_id=' + words[1] ;							  
				   }

		},
		error : function( jqxhr , status , error ){
			console.log( jqxhr , status , error );
					} 			      		
	   });		

		
 }
 
 	
}); // end of ready document
 

// 첨부된 파일 불러오기
function displayfile() {       
	$('#displayfile').show();
	params = $("#id").val();	
	
    var tablename = $("#tablename").val();    
    var item = 'attached';
	
	$.ajax({
		url:'../file/load_file.php?id=' + params + '&tablename=' + tablename + '&item=' + item ,
		type:'post',
		data: $("board_form").serialize(),
		dataType: 'json',
		}).done(function(data){						
		   const recid = data["recid"];		   
		   console.log(data);
		   $("#displayfile").html('');
		   for(i=0;i<recid;i++) {	
			   $("#displayfile").append("<div id=file" + i + ">  <a href='../uploads/" + data["file_arr"][i] + "' download='" +  data["realfile_arr"][i]+ "'>" +  data["realfile_arr"][i] + "</div> &nbsp;&nbsp;&nbsp;&nbsp;  " );			   
         	   $("#displayfile").append("&nbsp;<button type='button' class='btn btn-outline-danger btn-sm' id='delPic" + i + "' onclick=delPicFn('" + i + "','" + data["file_arr"][i] + "')> 삭제 </button>&nbsp; <br>");					   
		      }		   
    });	
}

// 기존 있는 파일 화면에 보여주기
function displayfileLoad() {    
	$('#displayfile').show();	
	var savefilename_arr = <?php echo json_encode($savefilename_arr);?> ;	
	var realname_arr = <?php echo json_encode($realname_arr);?> ;	
	
    for(i=0;i<savefilename_arr.length;i++) {
			   $("#displayfile").append("<div id=file" + i + ">  <a href='../uploads/" + savefilename_arr[i] + "' download='" + realname_arr[i] + "'>" +  realname_arr[i] + "</div> &nbsp;&nbsp;&nbsp;&nbsp;  " );			   
         	   $("#displayfile").append("&nbsp;<button type='button' class='btn btn-outline-danger btn-sm' id='delPic" + i + "' onclick=delPicFn('" + i + "','" +  savefilename_arr[i] + "')> 삭제 </button>&nbsp; <br>");					   
	  }	   
		
}
	
     function del(href) 
     {
		 var level=Number($('#session_level').val());
		 if(level>2)
		     alert("삭제하려면 관리자에게 문의해 주세요");
		 else {
         if(confirm("한번 삭제한 자료는 복구할 방법이 없습니다.\n\n정말 삭제하시겠습니까?")) {
           document.location.href = href;
          } 
		 }

     }


function load_item() {
	
}

function deleteLastchar(str)
// 마지막 문자 제거하는 함수
{
  return str = str.substr(0, str.length - 1);		
}


   
</script>

