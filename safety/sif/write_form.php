<?php
if(!isset($_SESSION))      
	session_start(); 
if(isset($_SESSION["DB"]))
	$DB = $_SESSION["DB"] ;	
	$level= $_SESSION["level"];
	$user_name= $_SESSION["name"];
	$user_id= $_SESSION["userid"];	

// 첫 화면 표시 문구
$title_message = '안전보건';
	
?>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php' ?>

 
<title> <?=$title_message?> </title> 
 
 </head>
 

<body>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/common/modal.php'; ?>

<?php

 if(!isset($_SESSION["level"]) || $_SESSION["level"]>5) {
          /*   alert("관리자 승인이 필요합니다."); */
		 sleep(1);
         header("Location:".$_SESSION["WebSite"]."login/login_form.php"); 
         exit;
   }    


isset($_REQUEST["id"])  ? $id=$_REQUEST["id"] :   $id=''; 
isset($_REQUEST["fileorimage"])  ? $fileorimage=$_REQUEST["fileorimage"] :   $fileorimage=''; // file or image
isset($_REQUEST["item"])  ? $item=$_REQUEST["item"] :   $item=''; 
isset($_REQUEST["upfilename"])  ? $upfilename=$_REQUEST["upfilename"] :   $upfilename=''; 
isset($_REQUEST["tablename"])  ? $tablename=$_REQUEST["tablename"] :  $tablename=''; 
isset($_REQUEST["savetitle"])  ? $savetitle=$_REQUEST["savetitle"] :  $savetitle='';   // log기록 저장 타이틀

    
  if(isset($_REQUEST["mode"]))  //수정 버튼을 클릭해서 호출했는지 체크
   $mode=$_REQUEST["mode"];
  else
   $mode="";
  
  if(isset($_REQUEST["num"]))  //수정 버튼을 클릭해서 호출했는지 체크
   $num=$_REQUEST["num"];
  else
   $num="";
          
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
  $pdo = db_connect();

  if ($mode=="modify"){
    try{
      $sql = "select * from mirae8440." . $tablename . " where num = ? ";
      $stmh = $pdo->prepare($sql); 

    $stmh->bindValue(1,$num,PDO::PARAM_STR); 
      $stmh->execute();
      $count = $stmh->rowCount();              
    if($count<1){  
      print "검색결과가 없습니다.<br>";
     }else{
      $row = $stmh->fetch(PDO::FETCH_ASSOC);
      $item_subject = $row["subject"];
      $item_id = $row["id"];
      $is_html = $row["is_html"];
      $content = $row["content"];
      $qnacheck = $row["qnacheck"];
      }
     }catch (PDOException $Exception) {
       print "오류: ".$Exception->getMessage();
     }
  }


// 초기 프로그램은 $num사용 이후 $id로 수정중임  
$id = $num;  
$author_id = $item_id;

// 첨부파일 있는 것 불러오기 
$savefilename_arr=array(); 
$realname_arr=array(); 
$tablename=$tablename;
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


<form  id="board_form" name="board_form" method="post" enctype="multipart/form-data"> 

  <!-- 전달함수 설정 input hidden -->
	<input type="hidden" id="id" name="id" value="<?=$id?>" >			  								
	<input type="hidden" id="num" name="num" value="<?=$num?>" >			  								
	<input type="hidden" id="parentid" name="parentid" value="<?=$parentid?>" >			  								
	<input type="hidden" id="fileorimage" name="fileorimage" value="<?=$fileorimage?>" >			  								
	<input type="hidden" id="item" name="item" value="<?=$item?>" >			  								
	<input type="hidden" id="upfilename" name="upfilename" value="<?=$upfilename?>" >			  								
	<input type="hidden" id="tablename" name="tablename" value="<?=$tablename?>" >			  								
	<input type="hidden" id="savetitle" name="savetitle" value="<?=$savetitle?>" >			  								
	<input type="hidden" id="pInput" name="pInput" value="<?=$pInput?>" >			  								
	<input type="hidden" id="mode" name="mode" value="<?=$mode?>" >		
	<input type="hidden" id="timekey" name="timekey" value="<?=$timekey?>" >  <!-- 신규데이터 작성시 parentid key값으로 사용 -->				

<div class="container">  
	<div class="d-flex mt-3 mb-1 justify-content-center align-items-center"> 
			<span class="fs-5" > &nbsp;&nbsp;  <?=$title_message?> &nbsp;&nbsp;</span>	
	</div>	      
	<div class="d-flex mt-2 mb-1 justify-content-center align-items-center"> 		
		<div class="card mt-2" style="width:60%;">  
			<div class="card-body">  					
				 <div class="row"> 					 
						<div class="d-flex justify-content-center align-items-center"> 							 
							작성자  : &nbsp;    <?=$_SESSION["nick"]?>  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;							
						</div>					
						<div class="d-flex  mt-2 justify-content-center align-items-center"> 							 							
							<span class="form-control me-2" style="width: 50px;border:0px;" > 제목 </span>
							<input id="subject" name="subject" type="text" required class="form-control" style="width:500px;" <?php if($mode=="modify"){ ?> value="<?=$item_subject?>" <?php }?>>&nbsp;														
						</div>						
				</div>
				</div>			
			</div>
		</div>
	<div class="d-flex mt-1 mb-1 justify-content-start align-items-center"> 					 
		<button class="btn btn-dark btn-sm me-1" onclick="self.close();" > <ion-icon name="close-outline"></ion-icon> 창닫기 </button>
		<button type="button"   class="btn btn-dark btn-sm" id="saveBtn"  >   <ion-icon name="save-outline"></ion-icon> 저장 </button>	
		
	</div>
	 <div class="d-flex mt-3 mb-1 justify-content-center">  
	 <textarea id="summernote" name="content" rows="20" ><?=$content?></textarea>
	</div>
 
	<div class="d-flex mt-3 mb-1 justify-content-center">  	 		 
			 <label for="upfile" class="input-group-text btn btn-outline-primary btn-sm"> 파일(10M 이하) pdf파일 첨부 </label>						  							
			 <input id="upfile"  name="upfile[]" type="file" onchange="this.value" multiple  style="display:none" >
	</div>	
	
	<div id ="displayfile" class="d-flex mt-1 mb-1 justify-content-center" style="display:none;">  	 		 					
		 
	</div>			
	<div id ="displayimage" class="row d-flex mt-1 mb-1 justify-content-center" style="display:none;">  	 		 					
		 
	</div>		
	
	<div class="d-flex mt-1 mb-1 justify-content-center">  	 	
			<label  for="upfileimage" class="input-group-text btn btn-outline-dark btn-sm ">  사진 첨부 </label>	
			 <input id="upfileimage"  name="upfileimage[]" type="file" onchange="this.value" multiple accept=".gif, .jpg, .png" style="display:none">
	</div>	
	
	</div>  	
</form>	
</body>
</html>
	 
<script> 

$(document).ready(function(){	


      $('#summernote').summernote({
        placeholder: '내용 작성',
		// maximumImageFileSize: 500*1024, // 500 KB
		maximumImageFileSize: 1920*5000, 		
        tabsize: 2,
        height: 500,
        width: 1200,
        toolbar: [
          ['style', ['style']],
          ['font', ['bold', 'underline', 'clear']],
          ['color', ['color']],
          ['para', ['ul', 'ol', 'paragraph']],
          ['table', ['table']],
          ['insert', ['link', 'picture', 'video']],
          ['view', ['fullscreen', 'codeview', 'help']]
        ],
		
		callbacks: {
        onImageUpload: function(files) {
            if (files.length > 0) {
                var file = files[0];
                resizeImage(file, function(resizedImage) {
                    // resizedImage는 처리된 이미지의 데이터 URL입니다.
                    $('#summernote').summernote('insertImage', resizedImage);
                        });
                    }
                }
            }
      });
	  

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
	 }, 1000);	
	 
  
delPicFn = function(divID, delChoice) {
	console.log(divID, delChoice);

	$.ajax({
		url:'/file/del_file.php?savename=' + delChoice ,
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
		url:'/file/del_file.php?savename=' + delChoice ,
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
				url: "/file/file_insert.php",
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
				url: "/file/file_insert.php",
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
		url:'/file/load_file.php?id=' + params + '&tablename=' + tablename + '&item=' + item ,
		type:'post',
		data: $("board_form").serialize(),
		dataType: 'json',
		}).done(function(data){						
		   const recid = data["recid"];		   
		   console.log(data);
		   $("#displayimage").html('');
		   for(i=0;i<recid;i++) {	
			   $("#displayimage").append("<img id='image" + i + "' src='/uploads/" + data['file_arr'][i] + "' style='width:80%;' > &nbsp; <br> &nbsp;  " );			   
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
			   $("#displayimage").append("<img id='image" + i + "'src='/uploads/" + saveimagename_arr[i] + "' style='width:80%;' >&nbsp;  <br> &nbsp; " );			   
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
	self.close();
});	

	 	 	 
$("#saveBtn").click(function(){ 
    Fninsert();
}); 	
		
// 자료의 삽입/수정하는 모듈 
function Fninsert() {	 
		   
	console.log($("#mode").val());    

	// Summernote 초기화 후
	let content = $('#summernote').summernote('code'); // 에디터의 내용을 HTML 형태로 가져옵니다.

	// HTML 문자열을 DOM 요소로 변환
	let tempDiv = document.createElement('div');
	tempDiv.innerHTML = content;

	// 이제 tempDiv 내부에서 원하는 태그를 선택할 수 있습니다.
	let elements = tempDiv.querySelectorAll('p, b');

	let extractedTexts = [];
	elements.forEach(element => {
		extractedTexts.push(element.textContent);
	});

	console.log(extractedTexts.join(','));

    var extractedText = extractedTexts.join(',');

	console.log('extractedTexts');
	console.log(extractedTexts);
	$("#searchtext").val(extractedText);

    var form = $('#board_form')[0];
    var data = new FormData(form);
 
	ajaxRequest = $.ajax({
		enctype: 'multipart/form-data',    // file을 서버에 전송하려면 이렇게 해야 함 주의
		processData: false,    
		contentType: false,      
		cache: false,           
		timeout: 600000, 			
		url: "insert.php",
		type: "post",		
		data: data,			
		dataType:"json",  
		success : function(data){
			
				console.log(data);
				setTimeout(function() {						
						Toastify({
							text: "파일 저장완료 ",
							duration: 2000,
							close:true,
							gravity:"top",
							position: "center",
							style: {
								background: "linear-gradient(to right, #00b09b, #96c93d)"
							},
						}).showToast();	
						
						setTimeout(function(){
							if (window.opener && !window.opener.closed) {
								opener.location.reload();
							}		
						}, 1000);																		

						var num = data["num"];
						var tablename = data["tablename"];
						
						setTimeout(function(){
							location.href='view.php?page=1&num=' + num + "&tablename=" + tablename ;					
						}, 1000);													
							
					}, 1000);

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
		url:'/file/load_file.php?id=' + params + '&tablename=' + tablename + '&item=' + item ,
		type:'post',
		data: $("board_form").serialize(),
		dataType: 'json',
		}).done(function(data){						
		   const recid = data["recid"];		   
		   console.log(data);
		   $("#displayfile").html('');
		   for(i=0;i<recid;i++) {	
			   $("#displayfile").append("<div id=file" + i + ">  <a href='/uploads/" + data["file_arr"][i] + "' download='" +  data["realfile_arr"][i]+ "'>" +  data["realfile_arr"][i] + "</div> &nbsp;&nbsp;&nbsp;&nbsp;  " );			   
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
		   $("#displayfile").append("<div id=file" + i + ">  <a href='/uploads/" + savefilename_arr[i] + "' download='" + realname_arr[i] + "'>" +  realname_arr[i] + "</div> &nbsp;&nbsp;&nbsp;&nbsp;  " );			   
		   $("#displayfile").append("&nbsp;<button type='button' class='btn btn-outline-danger btn-sm' id='delPic" + i + "' onclick=delPicFn('" + i + "','" +  savefilename_arr[i] + "')> 삭제 </button>&nbsp; <br>");					   
	}	   
		
}

function deleteLastchar(str)
// 마지막 문자 제거하는 함수
{
  return str = str.substr(0, str.length - 1);		
}


   
</script>

