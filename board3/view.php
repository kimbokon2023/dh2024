<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

$title_message = '공지 사항'; 

include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';    


$DB = 'dbchandj';
$tablename = "board3";  
?>

<title> <?=$title_message?> </title>  
 
</head> 

<body>

<?php include $_SERVER['DOCUMENT_ROOT'] . "/common/modal.php"; ?>
    
 <?php
 
 $file_dir = '../uploads/'; 
  
$num = isset($_REQUEST['num']) ? $_REQUEST['num'] : '';
$tablename = isset($_REQUEST['tablename']) ? $_REQUEST['tablename'] : '';
   
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();
 
 try{
     $sql = "select * from " . $DB . "." . $tablename . " where num=?";
     $stmh = $pdo->prepare($sql);  
     $stmh->bindValue(1, $num, PDO::PARAM_STR);      
     $stmh->execute();            
      
     $row = $stmh->fetch(PDO::FETCH_ASSOC);
 	
     $num     = $row["num"];
     $item_id      = $row["id"];
     $item_name    = $row["name"];
     $item_nick    = $row["nick"];
     $item_hit     = $row["hit"];
 

     $item_date    = $row["regist_day"];
     $item_date    = substr($item_date,0,10);
     $item_subject = str_replace(" ", "&nbsp;", $row["subject"]);
     $item_content = $row["content"];
     $is_html      = $row["is_html"];     

      
     if ($is_html=='y'){
		// $item_content = str_replace(" ", "&nbsp;", $item_content);
     	// $item_content = str_replace("\n", "<br>", $item_content);
		$item_content =  htmlspecialchars_decode($item_content);     	
     }	
	 
	 // $item_content = str_replace("\n", "<br>", $item_content);
	 $item_content = str_replace("\r", "<br>", $item_content);
 
     $new_hit = $item_hit + 1;
     try{
       $pdo->beginTransaction(); 
       $sql = "update " . $DB . "." . $tablename . " set hit=? where num=?";   // 글 조회수 증가
       $stmh = $pdo->prepare($sql);  
       $stmh->bindValue(1, $new_hit, PDO::PARAM_STR);      
       $stmh->bindValue(2, $num, PDO::PARAM_STR);           
       $stmh->execute();
       $pdo->commit(); 
       } catch (PDOException $Exception) {
         $pdo->rollBack();
       print "오류: ".$Exception->getMessage();
      }
	  

// 초기 프로그램은 $num사용 이후 $id로 수정중임  
$id=$num;   
$author_id = $item_id  ;
 
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
$attached_count = count($realname_arr);

// var_dump($realname_arr);
// var_dump($savefilename_arr);
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
  
// var_dump($realimagename_arr);
// var_dump($saveimagename_arr);  
  
 ?>
 
<form name="board_form" id="board_form"  method="post" >
<!-- 전달함수 설정 input hidden -->
<input type="hidden" id="id" name="id"  >			  								
<input type="hidden" id="fileorimage" name="fileorimage" >			  								
<input type="hidden" id="upfilename" name="upfilename">			  								
<input type="hidden" id="pInput" name="pInput"  > 
 
<div class="container">  
	<div class="card mt-2 mb-4">  
	<div class="card-body">  
		<div class="d-flex mt-3 mb-2 justify-content-center">  
			<h5>  <?=$title_message?> </h5> 
		</div>	
	 <div class="d-flex  p-1 m-1 mt-2 mb-2 justify-content-left  align-items-center">  				
		<button type="button" id="closeBtn"  class="btn btn-dark btn-sm me-1" >  &times; 닫기 </button>
		<?php
		// 삭제 수정은 관리자와 글쓴이만 가능토록 함
		if($user_id ==$item_id || $user_id =="admin" || $level ==='1' )
			{
		?>			
				<button type="button" class="btn btn-dark btn-sm me-1" onclick="location.href='write_form.php?tablename=<?=$tablename?>&amp;mode=modify&amp;num=<?=$num?>';">  <i class="bi bi-pencil-square"></i>  수정  </button>
				<button type="button"   class="btn btn-dark btn-sm me-1" onclick="location.href='write_form.php?tablename=<?=$tablename?>'" >  <i class="bi bi-pencil"></i>  신규 </button>			
				<button type="button"  id="deleteBtn" class="btn btn-danger btn-sm me-1" > <i class="bi bi-trash"></i>  삭제   </button>
		<?php  }  ?>				
		
	</div>  
	 
	  
		<div class="card">  
			<div class="card-body">  	 
				<div class="row d-flex  p-2 m-2 mt-1 mb-1 justify-content-center bg-secondary text-white align-items-center"> 		   
				  <div class="col-7 text-start fw-bold fs-6" > <?= $item_subject ?> </div>
				  <div class="col-5 text-end" > <?= $item_nick ?> | 조회 : <?= $item_hit ?> | <?= $item_date ?>   </div>   
				</div>
	  
				<div class="row d-flex  p-2 m-2 mt-1 mb-1 justify-content-left"> 	  
					<?=$item_content ?>
				</div>
			</div>
		</div>
	   <div class="d-flex  p-2 mt-2 mb-2 justify-content-center "> 	
	   
		<div id ="displayimage" class="d-flex mt-2 mb-1 justify-content-center" style="display:none;">  
		
		</div>		
        <?php
		  if($attached_count>0) {
			  print '<span class="badge bg-primary fs-6 " > 첨부파일 </span> &nbsp;';
		   }
		?>		
		
		<div id ="displayfile" class="d-flex mt-2 mb-1 justify-content-center" style="display:none;">  	 		 					 
		</div>			
		</div>			
		
		
		<div class="row p-1 m-1 mt-1 mb-1 justify-content-center align-items-center">    
			<?php
			try{
			   $sql = "select * from " . $DB . ".notice_ripple where parent='$num'";
			   $stmh1 = $pdo->query($sql);   // ripple PDOStatement 변수명을 다르게      
			 } catch (PDOException $Exception) {
			   print "오류: ".$Exception->getMessage();
			 }
				while ($row_ripple = $stmh1->fetch(PDO::FETCH_ASSOC)) {
				   $ripple_num     = $row_ripple["num"];
				   $ripple_id      = $row_ripple["id"];
				   $ripple_nick    = $row_ripple["nick"];
				   $ripple_content = str_replace("\n", "", $row_ripple["content"]);
				   $ripple_content = str_replace(" ", "&nbsp;", $ripple_content);
				   $ripple_date    = $row_ripple["regist_day"];
			 ?>
			   <div class="card" style="width:80% "> 
			   <div class="row justify-content-center">
			   <div class="card-body"> 
			   <span class="mt-1 mb-2">  ▶ &nbsp;&nbsp;  <?=$ripple_content?> ✔&nbsp;&nbsp; 작성자 :
			  <?=$ripple_nick?> | <?=$ripple_date?> 
			  &nbsp; &nbsp;		  &nbsp; &nbsp;		  &nbsp; &nbsp;		  
	 <?php
			if (isset($_SESSION["userid"])) {
				if ($_SESSION["userid"] == "admin" || $_SESSION["userid"] == $ripple_id || $_SESSION["level"] === 1) {
					print "<button type='button' class='btn btn-danger btn-sm' onclick='rippledelete(\"$tablename\", \"$num\", \"$ripple_num\", \"$page\")'> <i class='bi bi-trash'></i> </button>"; 
				}
			}
			
		  print '</span> </div> </div> </div>';
		}
	 ?>     
		
	</div>		
		
<form id="ripple_form" name="ripple_form" method="post" action="insert_ripple.php?tablename=<?=$tablename?>&num=<?=$num?>&page=<?=$page?>&ripple_content=<?=$ripple_content?>"> 		
  <div class="row p-1 m-1 mt-1 mb-1 justify-content-center"> 	 
   <div class="card" style="width:80% "> 
	   <div class="row">
	   <div class="card-body"> 
		<div class="row d-flex mt-3 mb-1 justify-content-center align-items-center">    												
			<div class="d-flex align-items-center">     
					 <span class="badge bg-secondary text-center fs-6 me-1" style="width:10%;"> <i class="bi bi-reply-fill"></i>댓글 </span>
					 
					
					<textarea rows="1" class="form-control me-1" name="ripple_content" required></textarea>
					
					  <button type="button" class="btn btn-dark btn-sm"  style="width:15%;" onclick="document.getElementById('ripple_form').submit();"> <i class="bi bi-floppy-fill"></i> 댓글 저장</button>
										
				</div>			
			</div>			
			
			
			</div>			
			</div>			
			</div>			
		</div>	
</form>		

			
		 
	  
	 <?php
		
	  } catch (PDOException $Exception) {
		   print "오류: ".$Exception->getMessage();
	  }
	 ?>  

</div>
</div>

</div> 

</form>
  <form id="Form1" name="Form1">
    <input type="hidden" id="num" name="num" value="<?=$num?>" >
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

$(document).ready(function(){

	$('#closeBtn').click(function() {		
		window.close(); // 현재 창 닫기
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
					 displayimageLoad();				 
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
			 
// 첨부된 이미지 불러오기
function displayimage() {       
	$('#displayimage').show();
	params = $("#id").val();	
	
    var tablename = 'board3';    
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
         	  // $("#displayimage").append("&nbsp;<button type='button' class='btn btn-outline-danger btn-sm' id='delPicimage" + i + "' onclick=delPicimageFn('" + i + "','" + data["file_arr"][i] + "')> 삭제 </button>&nbsp; <br>");					   
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
				  // $("#displayimage").append("&nbsp;<button type='button' class='btn btn-outline-danger btn-sm' id='delPicimage" + i + "' onclick=delPicimageFn('" + i + "','" +  savefilename_arr[i] + "')> 삭제 </button>&nbsp; <br>");					   
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
	
$("#deleteBtn").click(function(){        
    var level  = '<?php echo $level ; ?>' ;
	if( level !== '1'  )
	{
        Swal.fire({
            title: '삭제불가',
            text: "작성자와 관리자만 삭제가능합니다.",
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
				$.ajax({
					url: "delete.php",
					type: "post",		
					data: $("#Form1").serialize(),
					dataType:"json",
					success : function( data ){
						console.log(data);
						Toastify({
							text: "파일 삭제완료 ",
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
								window.opener.restorePageNumber(); // 부모 창에서 페이지 번호 복원
								window.opener.location.reload(); // 부모 창 새로고침
								window.close();
							}							
							
						}, 1000);	
					},
					error : function( jqxhr , status , error ){
						console.log( jqxhr , status , error );
					} 			      		
				   });	
                    

            }
        });
    }

});	
	

}); // end of ready document
 

// 첨부된 파일 불러오기
// 첨부된 파일 불러오기
function displayfile() {       
	$('#displayfile').show();
	params = $("#id").val();	
	
    var tablename = 'board3';    
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
         	 //  $("#displayfile").append("&nbsp;<button type='button' class='btn btn-outline-danger btn-sm' id='delPic" + i + "' onclick=delPicFn('" + i + "','" + data["file_arr"][i] + "')> 삭제 </button>&nbsp; <br>");					   
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
         	 //  $("#displayfile").append("&nbsp;<button type='button' class='btn btn-outline-danger btn-sm' id='delPic" + i + "' onclick=delPicFn('" + i + "','" +  savefilename_arr[i] + "')> 삭제 </button>&nbsp; <br>");					   
	  }	   
		
}
	
function rippledelete(tablename, num, ripple_num, page) {
    Swal.fire({
        title: '댓글 삭제',
        text: "정말 삭제하시겠습니까?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: '삭제',
        cancelButtonText: '취소'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `delete_ripple.php?tablename=${tablename}&num=${num}&ripple_num=${ripple_num}&page=${page}`;
        }
    });
}

   
</script>

