<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");    
$title_message = '업무요청사항';      

 if(!isset($_SESSION["level"]) || $_SESSION["level"]>5) {
		 sleep(1);
		  header("Location:" . $WebSite . "login/login_form.php"); 
         exit;
   }   
include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';      
?>    
<title> <?=$title_message?> </title>   
</head> 
<body>
<?php include $_SERVER['DOCUMENT_ROOT'] . "/common/modal.php"; ?>

<?php

isset($_REQUEST["id"])  ? $id=$_REQUEST["id"] :   $id=''; 
isset($_REQUEST["num"])  ? $num=$_REQUEST["num"] :   $num=''; 
isset($_REQUEST["fileorimage"])  ? $fileorimage=$_REQUEST["fileorimage"] :   $fileorimage=''; // file or image
isset($_REQUEST["item"]) ? $item=$_REQUEST["item"] :   $item=''; 
isset($_REQUEST["upfilename"]) ? $upfilename=$_REQUEST["upfilename"] :   $upfilename=''; 
isset($_REQUEST["tablename"]) ? $tablename=$_REQUEST["tablename"] :  $tablename=''; 
isset($_REQUEST["savetitle"]) ? $savetitle=$_REQUEST["savetitle"] :  $savetitle='';   // log기록 저장 타이틀

$mode = $_REQUEST["mode"] ?? "";

$is_html = $_REQUEST["is_html"] ?? "";
$noticecheck = $_REQUEST["noticecheck"] ?? "";
          
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

  if ($mode=="modify"){
    try{
      $sql = "select * from " . $DB . "." . $tablename . " where num = ? ";
      $stmh = $pdo->prepare($sql); 

    $stmh->bindValue(1,$num,PDO::PARAM_STR); 
      $stmh->execute();
      $count = $stmh->rowCount();              
    if($count<1){  
      print "검색결과가 없습니다.<br>";
     }else{
      $row = $stmh->fetch(PDO::FETCH_ASSOC);
         include '_row.php';
      }
     }catch (PDOException $Exception) {
       print "오류: ".$Exception->getMessage();
     }
  }
  else
  {
	   $content = null;
	   $first_writer = $user_name;
	   $chargedPerson = null;
  }

// 초기 프로그램은 $num사용 이후 $id로 수정중임  
$id=$num;  

// 첨부파일 있는 것 불러오기 
$savefilename_arr=array(); 
$realname_arr=array(); 
$item = 'attached';

$sql=" select * from " . $DB . ".fileuploads where tablename ='$tablename' and item ='$item' and parentid ='$id' ";	

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

$sql=" select * from " . $DB . ".fileuploads where tablename ='$tablename' and item ='$item' and parentid ='$id' ";	

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
	<input type="hidden" id="tablename" name="tablename" value="<?=$tablename?>" >			  								
	<input type="hidden" id="id" name="id" value="<?=$id?>" >			  								
	<input type="hidden" id="num" name="num" value="<?=$num?>" >			  								
	<input type="hidden" id="parentid" name="parentid" value="<?=$parentid?>" >			  								
	<input type="hidden" id="fileorimage" name="fileorimage" value="<?=$fileorimage?>" >			  								
	<input type="hidden" id="item" name="item" value="<?=$item?>" >			  								
	<input type="hidden" id="upfilename" name="upfilename" value="<?=$upfilename?>" >			  									
	<input type="hidden" id="savetitle" name="savetitle" value="<?=$savetitle?>" >			  								
	<input type="hidden" id="pInput" name="pInput" value="<?=$pInput?>" >			  								
	<input type="hidden" id="mode" name="mode" value="<?=$mode?>" >		
	<input type="hidden" id="timekey" name="timekey" value="<?=$timekey?>" >  <!-- 신규데이터 작성시 parentid key값으로 사용 -->		
	<input type="hidden" id="searchtext" name="searchtext" value="<?=$searchtext?>" >  <!-- summernote text저장 -->	

<div class="container-fluid">  
	<!-- 모달 시작 -->
	<div class="modal fade" id="memberModal" tabindex="-1" aria-labelledby="memberModalLabel" aria-hidden="false">
	  <div class="modal-dialog modal-lg">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title" id="memberModalLabel">직원 선택</h5>
			<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
		  </div>
		  <div class="modal-body">
			<ul id="memberList" class="list-group">
			  <!-- 직원 목록이 여기에 동적으로 추가됩니다. -->
			</ul>
			 <div class="row justify-content-end text-end mt-2 mb-2">    
				<button type="button" class="btn-close me-2" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
		  </div>
		</div>
	  </div>
	</div>
	<!-- 모달 끝 -->

	<div class="d-flex mt-3 mb-1 justify-content-center align-items-center"> 
		<h5>  <?=$title_message?>  </h5>  
	</div>	      
	<div class="d-flex mt-2 mb-1 justify-content-center align-items-center"> 		
		<div class="card mt-2" style="width:100%;">  
			<div class="card-body">  	
				<div class="row"> 						 
				<div class="col-md-9">
					<div class="d-flex mt-2 justify-content-center align-items-center"> 						 
						<table class="table table-bordered align-middle table-sm">
						<tbody>
							<tr>
							<td class="table-light text-center" style="width: 8%;">작성자</td>
							<td colspan="1">
								<input id="first_writer" name="first_writer" type="text" autocomplete="off" value="<?= $first_writer ?>" class="form-control w60px" >
							</td>	
							<td class="table-light text-center" style="width: 8%;">작성일</td>
							<td colspan="1">
								<input id="regist_day" name="regist_day" type="date" autocomplete="off" value="<?= $regist_day ?>" class="form-control w120px" >
							</td>	
							<td class="table-light text-center" style="width: 8%;">업무담당자</td>
							<td colspan="1">
								<input id="chargedPerson" name="chargedPerson" type="text" autocomplete="off" value="<?= $chargedPerson ?>" class="form-control" >
							</td>		
							<td class="table-light text-center  text-primary" style="width: 8%;">처리기한</td>
							<td colspan="1">
								<input id="dueDate" name="dueDate" type="date" autocomplete="off" value="<?= $dueDate ?>" class="form-control w100px" >
							</td>									
							</tr>
							<tr>
							<td class="table-light text-center">제목</td>
							<td colspan="5">
								<input id="subject" name="subject" type="text" autocomplete="off" required class="form-control"
								style="width: 100%;" <?php if ($mode == "modify") { ?> value="<?= $subject ?>" <?php } ?>>
							</td>
							<td class="table-light text-center text-danger" style="width: 8%;">처리완료일</td>
							<td colspan="1">
								<input id="doneDate" name="doneDate" type="date" autocomplete="off" value="<?= $doneDate ?>" class="form-control w100px" >
							</td>
							</tr>
						</tbody>
						</table>
					</div>
				</div>
				<div class="col-md-3">
				<!-- 담당자별 상태 테이블 -->
				<div class="d-flex mb-1 justify-content-end align-items-center" id="chargedPersonStatusSection" style="display:none; padding: 4px;">											
					<table class="table table-bordered align-middle table-sm" id="chargedPersonStatusTable">
					<thead class="table-info">
						<tr>
						<th class="text-center">담당자</th>
						<th class="text-center">확인</th>
						<th class="text-center">처리완료</th>
						</tr>
					</thead>
					<tbody id="chargedPersonStatusBody">
						<!-- 담당자별 상태가 여기에 동적으로 추가됩니다 -->
					</tbody>
					</table>							
				</div>
				</div>
				</div>
			</div>
			</div>
	</div>
	
	<div class="d-flex mt-2 mb-2 justify-content-start align-items-center"> 					 
		<button class="btn btn-dark btn-sm me-1" onclick="self.close();" > &times; 닫기 </button>
		<button type="button"   class="btn btn-dark btn-sm" id="saveBtn"  >  <i class="bi bi-floppy-fill"></i> 저장 </button>			
	</div>
	<div class="d-flex justify-content-center align-items-center"> 		
		<textarea id="summernote" name="content" rows="20" required><?=$content?></textarea>
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

<!-- 페이지로딩 -->
<script>
// 페이지 로딩
$(document).ready(function(){	
    var loader = document.getElementById('loadingOverlay');
    loader.style.display = 'none';
});
</script>

<script>

var ajaxRequest_notice = null;

$(document).ready(function(){	

      $('#summernote').summernote({
        placeholder: '내용 작성',
		// maximumImageFileSize: 500*1024, // 500 KB
		maximumImageFileSize: 1920*5000, 		
        tabsize: 2,
        height: 500,
        width: 1400,
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
	  
function resizeImage(file, callback) {
    var reader = new FileReader();
    reader.onloadend = function(e) {
        var tempImg = new Image();
        tempImg.src = reader.result;
        tempImg.onload = function() {
            // 여기서 원하는 이미지 크기로 설정
            var MAX_WIDTH = 800;
            var MAX_HEIGHT = 500;
            var tempW = tempImg.width;
            var tempH = tempImg.height;

            if (tempW > tempH) {
                if (tempW > MAX_WIDTH) {
                    tempH *= MAX_WIDTH / tempW;
                    tempW = MAX_WIDTH;
                }
            } else {
                if (tempH > MAX_HEIGHT) {
                    tempW *= MAX_HEIGHT / tempH;
                    tempH = MAX_HEIGHT;
                }
            }

            var canvas = document.createElement('canvas');
            canvas.width = tempW;
            canvas.height = tempH;
            var ctx = canvas.getContext("2d");
            ctx.drawImage(tempImg, 0, 0, tempW, tempH);
            var dataURL = canvas.toDataURL("image/jpeg");

            callback(dataURL);
        };
    };
    reader.readAsDataURL(file);
}	  

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
	$("#savetitle").val('주요안건 첨부파일');		

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
				// console.log(data);
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
	$("#savetitle").val('주요안건 이미지');		

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

	// console.log(extractedTexts.join(','));

    var extractedText = extractedTexts.join(',');

	// console.log('extractedTexts');
	// console.log(extractedTexts);
	$("#searchtext").val(extractedText);

    var form = $('#board_form')[0];
    var data = new FormData(form);

    // 폼 데이터를 콘솔에 출력하여 확인합니다.
    // for (var pair of data.entries()) {
        // console.log(pair[0] + ', ' + pair[1]);
    // }	
   // console.log(data);   
   
	showMsgModal(2); // 저장중
	
	if (ajaxRequest_notice !== null) {
		ajaxRequest_notice.abort();
	}	
   
	ajaxRequest_notice = $.ajax({
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
			
			    ajaxRequest_notice = null ;
				
				var num = data["num"];
				var tablename = data["tablename"];				

				setTimeout(function(){		
					hideMsgModal();						
					setTimeout(function(){		
						location.href='view.php?page=1&num=' + num + "&tablename=" + tablename ;					
					}, 1000);						
				}, 1000);						

		},
		error : function( jqxhr , status , error ){
			  console.log( jqxhr , status , error );
			  ajaxRequest_notice = null ;
			  hideMsgModal();	
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

function deleteLastchar(str)
// 마지막 문자 제거하는 함수
{
  return str = str.substr(0, str.length - 1);		
}   



$(document).ready(function() {
    // AS 담당자 필드를 클릭하면 모달을 표시
    $('#chargedPerson').focus(function() {
        $('#memberModal').modal('show');
    });

    // 담당자별 상태 테이블 생성 함수
    function updateChargedPersonStatusTable() {
        var chargedPerson = $('#chargedPerson').val();
        var persons = chargedPerson.split(',').map(function(name) {
            return name.trim();
        }).filter(function(name) {
            return name.length > 0;
        });
        
        if (persons.length === 0) {
            $('#chargedPersonStatusSection').hide();
            return;
        }
        
        // 기존 상태 데이터 가져오기
        var currentStatus = window.currentChargedPersonStatus || <?php echo isset($chargedPersonStatus) && $chargedPersonStatus ? json_encode($chargedPersonStatus) : '""'; ?>;
        var statusData = currentStatus ? JSON.parse(currentStatus) : {};
        
        var tableBody = $('#chargedPersonStatusBody');
        tableBody.empty();
        
        persons.forEach(function(person) {
            var personStatus = statusData[person] || {checked: '', done: ''};
            var currentUser = '<?php echo $user_name; ?>';
            var isCurrentUser = person === currentUser;
            
            var row = '<tr>' +
                '<td class="text-center">' + person + '</td>' +
                '<td class="text-center">' +
                    '<span id="checked_' + person.replace(/\s+/g, '_') + '">' + 
                        (personStatus.checked || '미확인') + 
                    '</span>';
            
            if (isCurrentUser) {
                row += '<br><button type="button" class="btn btn-outline-primary btn-sm mt-1" ' +
                       'onclick="updatePersonStatus(\'' + person + '\', \'checked\')">확인</button>';
            }
            
            row += '</td>' +
                '<td class="text-center">' +
                    '<span id="done_' + person.replace(/\s+/g, '_') + '">' + 
                        (personStatus.done || '미완료') + 
                    '</span>';
            
            if (isCurrentUser) {
                row += '<br><button type="button" class="btn btn-outline-success btn-sm mt-1" ' +
                       'onclick="updatePersonStatus(\'' + person + '\', \'done\')">완료</button>';
            }
            
            row += '</td>' +
                '</tr>';
            
            tableBody.append(row);
        });
        
        $('#chargedPersonStatusSection').show();
    }
    
    // 담당자 필드 변경 시 테이블 업데이트
    $('#chargedPerson').on('input', function() {
        updateChargedPersonStatusTable();
    });
    
    // 페이지 로드 시 초기 테이블 생성
    updateChargedPersonStatusTable();

    // 모달 열릴 때 직원 목록을 불러옴
    $('#memberModal').on('shown.bs.modal', function () {
        // 기존에 선택된 직원 이름을 배열로 분리
        var selectedNames = $('#chargedPerson').val().split(',').map(function(name) {
            return name.trim();
        });

        $.ajax({
            url: "/as/fetch_modal_member.php", // 직원 목록을 불러오는 PHP 파일
            type: "GET",
            dataType: "json",
            success: function(response) {
                var memberList = $('#memberList');
                memberList.empty(); // 기존 리스트 초기화

                // 직원 목록을 추가
                response.forEach(function(member) {
                    var isChecked = selectedNames.includes(member.name) ? 'checked' : ''; // 기존 선택된 값 반영
                    var listItem = '<li class="list-group-item">' +
                        '<input type="checkbox" name="memberSelect" class="me-2" value="' + member.name + '" ' + isChecked + '>' +
                        member.name + ' (' + member.position + ')' +
                        '</li>';
                    memberList.append(listItem);
                });

                // 직원 선택 시 처리
                $('input[name="memberSelect"]').change(function() {
                    var selectedNames = [];

                    // 선택된 직원들의 이름을 배열에 저장
                    $('input[name="memberSelect"]:checked').each(function() {
                        selectedNames.push($(this).val());
                    });

                    // 선택된 직원 이름을 ','로 구분하여 입력
                    $('#chargedPerson').val(selectedNames.join(', '));
                    updateChargedPersonStatusTable(); // 테이블 업데이트
                });
            },
            error: function(xhr, status, error) {
                console.log("Error fetching member list: ", error);
            }
        });
    });

});

// 담당자별 상태 업데이트 함수 (전역 함수)
function updatePersonStatus(person, type) {
    var num = $('#num').val();
    var tablename = $('#tablename').val();
    var currentDate = new Date().toISOString().split('T')[0]; // YYYY-MM-DD 형식
    var sendDate = (type === 'checked_cancel' || type === 'done_cancel') ? '' : currentDate;
    var sendType = type.replace('_cancel', '');
    
    $.ajax({
        url: 'update_person_status.php',
        type: 'POST',
        data: {
            num: num,
            tablename: tablename,
            person: person,
            type: sendType,
            date: sendDate
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // 서버에서 최신 데이터를 가져와서 테이블 업데이트
                $.ajax({
                    url: 'get_updated_status.php',
                    type: 'POST',
                    data: {
                        num: num,
                        tablename: tablename
                    },
                    dataType: 'json',
                    success: function(statusResponse) {
                        if (statusResponse.success) {
                            // 전역 변수에 최신 상태 저장
                            window.currentChargedPersonStatus = statusResponse.chargedPersonStatus;
                            // 테이블 다시 그리기
                            updateChargedPersonStatusTable();
                        }
                    },
                    error: function() {
                        // 실패 시에도 테이블 다시 그리기 (기존 데이터로)
                        updateChargedPersonStatusTable();
                    }
                });
            } else {
                alert('오류가 발생했습니다: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.log('Error:', error);
            alert('서버 오류가 발생했습니다.');
        }
    });
}


</script>

