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
 
 $file_dir = '../uploads/'; 
  
 $num=$_REQUEST["num"] ?? ''; 
 $tablename=$_REQUEST["tablename"] ?? ''; 
 $mode=$_REQUEST["mode"] ?? ''; 
   
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();
 
 try{
     $sql = "select * from " . $DB . "." . $tablename . " where num=?";
     $stmh = $pdo->prepare($sql);  
     $stmh->bindValue(1, $num, PDO::PARAM_STR);      
     $stmh->execute();            
      
     $row = $stmh->fetch(PDO::FETCH_ASSOC);
 	
     include '_row.php';
	 
	 if($noticecheck=='y')
		 $noticecheck_memo='(전체공지)';
	    else
			$noticecheck_memo='';
      
     if ($is_html=='y'){
		// $content = str_replace(" ", "&nbsp;", $content);
     	// $content = str_replace("\n", "<br>", $content);
		$content =  htmlspecialchars_decode($content);     	
     }	
	 
	 // $content = str_replace("\n", "<br>", $content);
	 $content = str_replace("\r", "<br>", $content);
 
     $new_hit = $hit + 1;
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
$author_id = $id  ;
 
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
  
// 버튼 표시 여부와 텍스트를 로그인 사용자와 비교해 결정
$loginUser = $_SESSION["name"] ?? '';
$canEditDoneDate = strpos($chargedPerson, $loginUser) !== false;
$doneBtnText = ($doneDate && $doneDate !== '0000-00-00') ? '취소' : '완료';  
  
 ?>
 
<div class="container">  
	<div class="card mt-2 mb-4">  
		<div class="card-header">
			<div class="d-flex justify-content-center text-center align-items-center">										 
				<span class="text-center fs-5"> <?=$title_message?> </span>		
				<button type="button" class="btn btn-dark btn-sm mx-3"  onclick='location.reload();' title="새로고침"> <i class="bi bi-arrow-clockwise"></i> </button>  		
			</div>		
	 <div class="d-flex  p-1 m-1 mt-2 mb-2 justify-content-left  align-items-center">  				
		
		<button type="button" id="closeBtn"  class="btn btn-dark btn-sm me-1" > &times; 닫기 </button>
		<?php
		// 삭제 수정은 관리자와 글쓴이만 가능토록 함
		if(isset($_SESSION["userid"])) {
		if($_SESSION["userid"]==$id || $_SESSION["userid"]=="admin" ||
			   $_SESSION["level"]==='1' )
			{
		?>			
				<button type="button"   class="btn btn-dark btn-sm me-1" onclick="location.href='write_form.php?tablename=<?=$tablename?>&mode=modify&num=<?=$num?>'" >  <i class="bi bi-pencil-square"></i>  수정 </button>							
				<button type="button"   class="btn btn-danger btn-sm me-1" onclick="javascript:del('delete.php?tablename=<?=$tablename?>&num=<?=$num?>')" > <i class="bi bi-trash"></i>  삭제   </button>
		<?php  }  ?>						
	</div>  	
		<div class="card">  
			<div class="card-body">  	 
				<div class="row"> 						 
				<div class="col-md-9">
					<div class="d-flex mt-2 justify-content-center align-items-center"> 						 
						<table class="table table-bordered align-middle table-sm">
						<tbody>
							<tr>
							<td class="table-light text-center" style="width: 8%;">작성자</td>
							<td colspan="1">
								<input id="first_writer" name="first_writer" readonly type="text" autocomplete="off" value="<?= $first_writer ?>" class="form-control w100px" >
							</td>	
                            <td class="table-light text-center" style="width: 8%;">작성일</td>
							<td colspan="1">
								<input id="regist_day" name="regist_day" type="date" autocomplete="off" readonly value="<?= $regist_day ?>" class="form-control w120px" >
							</td>	
							<td class="table-light text-center" style="width: 8%;">업무담당자</td>
							<td colspan="1">
								<input id="chargedPerson" name="chargedPerson" type="text" autocomplete="off" readonly value="<?= $chargedPerson ?>" class="form-control" >
							</td>		
							<td class="table-light text-center  text-primary" style="width: 8%;">처리기한</td>
							<td colspan="1">
								<input id="dueDate" name="dueDate" type="date" autocomplete="off"  readonly value="<?= $dueDate ?>" class="form-control w100px" >
							</td>									
							</tr>
							<tr>
							<td class="table-light text-center">제목</td>
							<td colspan="5">
								<input id="subject" name="subject" type="text" autocomplete="off"  readonly  class="form-control"
								style="width: 100%;" value="<?= $subject ?>" >
							</td>
							<td class="table-light text-center text-danger" style="width: 8%;">처리완료일
								<?php if ($canEditDoneDate): ?>
								<button type="button" id="doneBtn" class="btn btn-danger btn-sm mx-2"> <?= $doneBtnText ?> </button>
								<?php endif; ?>
							</td>
							<td colspan="1">
								<input id="doneDate" name="doneDate" type="date" autocomplete="off"  readonly value="<?= $doneDate ?>" class="form-control w100px" >
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
	  
				<div class="row d-flex  p-2 m-2 mt-1 mb-1 justify-content-left"> 	  
					<?=$content ?>
				</div>
			</div>
		</div>
	   <div class="row d-flex  p-2 m-2 mt-1 mb-1 justify-content-left "> 	
		<div id ="displayimage" class="row d-flex mt-1 mb-1 justify-content-center" style="display:none;">  	 		 					 
		</div>				
		
		<div id ="displayfile" class="d-flex mt-1 mb-1 justify-content-center" style="display:none;">  	 		 					 
		</div>			
		</div>			
		
		<div class="row p-1 m-1 mt-1 mb-1 justify-content-center align-items-center">    
			<?php
			try{
			   $sql = "select * from " . $DB . ".workprocess_ripple where parent='$num'";
			   $stmh1 = $pdo->query($sql);   // ripple PDOStatement 변수명을 다르게      
			 } catch (PDOException $Exception) {
			   print "오류: ".$Exception->getMessage();
			 }
				while ($row_ripple = $stmh1->fetch(PDO::FETCH_ASSOC)) {
				   $ripple_num     = $row_ripple["num"];
				   $ripple_id      = $row_ripple["id"];
				   $ripple_name    = $row_ripple["name"];
				   $ripple_content = str_replace("\n", "", $row_ripple["content"]);
				   $ripple_content = str_replace(" ", "&nbsp;", $ripple_content);
				   $ripple_date    = $row_ripple["regist_day"];
			 ?>
			   <div class="card" style="width:80% "> 
			   <div class="row justify-content-center">
			   <div class="card-body"> 
			   <span class="mt-1 mb-2">  ▶ &nbsp;&nbsp;  <?=$ripple_content?> ✔&nbsp;&nbsp; 작성 :
			  <span class="text-primary"> <?=$ripple_name?> </span> | <?=$ripple_date?> 
			  &nbsp; &nbsp;	
	 <?php
			if (isset($_SESSION["userid"])) {
				if ($_SESSION["userid"] == "admin" || $_SESSION["userid"] == $ripple_id || $_SESSION["level"] == 1) {
					print "<button type='button' class='btn btn-outline-primary btn-sm mx-2' onclick='rippleEdit(\"$ripple_num\", \"" . addslashes(htmlspecialchars_decode($row_ripple["content"])) . "\")'><i class='bi bi-pencil'></i></button>";
					print "<button type='button' class='btn btn-outline-danger btn-sm' onclick='rippledelete(\"$tablename\", \"$num\", \"$ripple_num\")'> <i class='bi bi-trash'></i> </button>"; 

				}
			}
			
		  print '</span> </div> </div> </div>';
		}
	 ?>     
		
	</div>		
		
<form id="ripple_form" name="ripple_form" method="post" action="insert_ripple.php"> 	

  <!-- 전달함수 설정 input hidden -->
<input type="hidden" id="id" name="num" value="<?= isset($num) ? $num : '' ?>" >
<input type="hidden" id="id" name="id" value="<?= isset($id) ? $id : '' ?>" >
<input type="hidden" id="parentid" name="parentid" value="<?= isset($parentid) ? $parentid : '' ?>" >
<input type="hidden" id="fileorimage" name="fileorimage" value="<?= isset($fileorimage) ? $fileorimage : '' ?>" >
<input type="hidden" id="item" name="item" value="<?= isset($item) ? $item : '' ?>" >
<input type="hidden" id="upfilename" name="upfilename" value="<?= isset($upfilename) ? $upfilename : '' ?>" >
<input type="hidden" id="tablename" name="tablename" value="<?= isset($tablename) ? $tablename : '' ?>" >
<input type="hidden" id="savetitle" name="savetitle" value="<?= isset($savetitle) ? $savetitle : '' ?>" >
<input type="hidden" id="pInput" name="pInput" value="<?= isset($pInput) ? $pInput : '' ?>" >
<input type="hidden" id="mode" name="mode" value="<?= isset($mode) ? $mode : '' ?>" >
		
  <div class="row p-1 m-1 mt-1 mb-1 justify-content-center"> 	 
   <div class="card" style="width:80% "> 
	   <div class="row">
	   <div class="card-body"> 
		<div class="row d-flex mt-3 mb-1 justify-content-center align-items-center">    												
			<div class="d-flex align-items-center">     
				 <span class="badge bg-secondary text-center fs-6 me-1" style="width:10%;">  댓글 </span>
					
					<textarea rows="1" class="form-control me-1" id="ripple_content" name="ripple_content" required></textarea>
					
					  <button type="button" class="btn btn-dark btn-sm"  style="width:15%;" onclick="document.getElementById('ripple_form').submit();">  <i class="bi bi-floppy-fill"></i>  댓글 저장</button>
										
				</div>			
			</div>			
			
			
			</div>			
			</div>			
			</div>			
		</div>	
</form>		
	 
	  
	 <?php
		}
	  } catch (PDOException $Exception) {
		   print "오류: ".$Exception->getMessage();
	  }
	 ?>  

</div>
</div>

<!-- 완료 날짜 입력용 모달 -->
<div class="modal fade" id="doneDateModal" tabindex="-1" aria-labelledby="doneDateModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="doneDateModalLabel">처리 날짜 선택</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="닫기"></button>
      </div>
      <div class="modal-body text-center">
        <label for="customDoneDate" class="form-label">처리 날짜를 선택하세요</label>
        <input type="date" id="customDoneDate" class="form-control">
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">취소</button>
        <button type="button" class="btn btn-primary btn-sm" id="confirmDoneDateBtn">확인</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="rippleEditModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">댓글 수정</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <textarea id="editRippleContent" class="form-control" rows="3"></textarea>
        <input type="hidden" id="editRippleNum">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" onclick="saveRippleEdit()">확인</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
      </div>
    </div>
  </div>
</div>

</div> 

  <form id="Form1" name="Form1">
    <input type="hidden" id="num" name="num" value="<?=$num?>" >
  </form>  	
 
 
 <!-- 페이지로딩 -->
<script>
// 페이지 로딩
$(document).ready(function(){	
    var loader = document.getElementById('loadingOverlay');
	if(loader)
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
			 
// 첨부된 이미지 불러오기
function displayimage() {       
	$('#displayimage').show();
	params = $("#id").val();	
	
    var tablename = 'notice';    
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
	

}); // end of ready document
 

// 첨부된 파일 불러오기
function displayfile() {       
	$('#displayfile').show();
	params = $("#id").val();	
	
    var tablename = 'notice';    
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
			   $("#displayfile").append(" <span class='badge text-success fs-5'> 첨부파일 :  <div id=file" + i + ">  <a href='../uploads/" + data["file_arr"][i] + "' download='" +  data["realfile_arr"][i]+ "'>" +  data["realfile_arr"][i] + "</div> </span> &nbsp;&nbsp;&nbsp;&nbsp;  " );			   
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
			   $("#displayfile").append("<span class='badge text-success fs-5'>  첨부파일 :  <div id=file" + i + ">  <a href='../uploads/" + savefilename_arr[i] + "' download='" + realname_arr[i] + "'>" +  realname_arr[i] + "</div> </span> &nbsp;&nbsp;&nbsp;&nbsp;  " );			   
         	 //  $("#displayfile").append("&nbsp;<button type='button' class='btn btn-outline-danger btn-sm' id='delPic" + i + "' onclick=delPicFn('" + i + "','" +  savefilename_arr[i] + "')> 삭제 </button>&nbsp; <br>");					   
	  }	   
		
}


function del(href) {    
    var user_id  = '<?php echo  $user_id ; ?>' ;
    var author_id  = '<?php echo  $author_id ; ?>' ;
    var admin  = '<?php echo  $admin ; ?>' ;
	if( user_id !== author_id && admin !== '1' )
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
}

function rippledelete(tablename, num, ripple_num) {
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
            window.location.href = `delete_ripple.php?tablename=${tablename}&num=${num}&ripple_num=${ripple_num}`;
        }
    });
}

$(document).ready(function () {
    $('#doneBtn').click(function () {
        const currentDoneDate = $('#doneDate').val();
        const action = currentDoneDate && currentDoneDate !== '0000-00-00' ? 'clear' : 'set';

        if (action === 'clear') {
            // 기존 완료일 있으면 제거
            updateDoneDate('clear');
        } else {
            // 완료 설정이면 모달 열기
            const today = new Date().toISOString().split('T')[0];
            $('#customDoneDate').val(today); // 기본값 오늘로 설정
            const modal = new bootstrap.Modal(document.getElementById('doneDateModal'));
            modal.show();
        }
    });

    // 모달 내 '확인' 버튼 클릭 시
    $('#confirmDoneDateBtn').click(function () {
        const selectedDate = $('#customDoneDate').val();
        if (!selectedDate) {
            Swal.fire('오류', '날짜를 선택해주세요.', 'warning');
            return;
        }

        $('#doneDateModal').modal('hide');
        updateDoneDate('set', selectedDate);
    });

    // 서버에 완료일 전송
    function updateDoneDate(action, selectedDate = '') {
        $.ajax({
            url: 'insert_done.php',
            type: 'POST',
            data: {
                num: '<?= $num ?>',
                tablename: '<?= $tablename ?>',
                action: action,
                selectedDate: selectedDate
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    if (action === 'set') {
                        $('#doneDate').val(response.doneDate);
                        $('#doneBtn').text('취소');
                    } else {
                        $('#doneDate').val('');
                        $('#doneBtn').text('완료');
                    }
                    Swal.fire('성공', response.message, 'success');
                } else {
                    Swal.fire('오류', response.message, 'error');
                }
            },
            error: function () {
                Swal.fire('오류', '서버 요청 실패', 'error');
            }
        });
    }
});

function rippleEdit(ripple_num, content) {
    $('#editRippleNum').val(ripple_num);
    $('#editRippleContent').val(content);
    $('#rippleEditModal').modal('show');
}


function saveRippleEdit() {
    const ripple_num = $('#editRippleNum').val();
    const content = $('#editRippleContent').val();
    const tablename = '<?=$tablename?>'; // PHP 변수 JS로 전달
    const num = '<?=$num?>';

    $.ajax({
        url: 'update_ripple.php',
        type: 'POST',
        data: {
            ripple_num: ripple_num,
            content: content,
            tablename: tablename,
            num: num
        },
        success: function (response) {
            Swal.fire({
                icon: 'success',
                title: '수정 완료',
                text: '댓글이 수정되었습니다.'
            }).then(() => {
                location.reload();
            });
        },
        error: function () {
            Swal.fire('에러', '수정 중 문제가 발생했습니다.', 'error');
        }
    });
}

$(document).ready(function() {
    // 담당자별 상태 테이블 생성 함수 (view: 본인만 버튼 노출)
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
        var currentUser = '<?php echo $loginUser; ?>';
        
        var tableBody = $('#chargedPersonStatusBody');
        tableBody.empty();
        
        persons.forEach(function(person) {
            var personStatus = statusData[person] || {checked: '', done: ''};
            var checkedHtml = personStatus.checked
                ? personStatus.checked + (person === currentUser ? '<br><button type="button" class="btn btn-outline-secondary btn-sm mt-1" onclick="updatePersonStatus(\'' + person + '\', \'checked_cancel\')">취소</button>' : '')
                : "<span class='badge text-danger'>미확인</span>" + (person === currentUser ? '<br><button type="button" class="btn btn-outline-primary btn-sm mt-1" onclick="updatePersonStatus(\'' + person + '\', \'checked\')">확인</button>' : '');
            var doneHtml = personStatus.done
                ? personStatus.done + (person === currentUser ? '<br><button type="button" class="btn btn-outline-secondary btn-sm mt-1" onclick="updatePersonStatus(\'' + person + '\', \'done_cancel\')">취소</button>' : '')
                : "<span class='badge text-danger'>미완료</span>" + (person === currentUser ? '<br><button type="button" class="btn btn-outline-success btn-sm mt-1" onclick="updatePersonStatus(\'' + person + '\', \'done\')">완료</button>' : '');
            var row = '<tr>' +
                '<td class="text-center">' + person + '</td>' +
                '<td class="text-center">' + checkedHtml + '</td>' +
                '<td class="text-center">' + doneHtml + '</td>' +
                '</tr>';
            tableBody.append(row);
        });
        
        $('#chargedPersonStatusSection').show();
    }
    
    // 페이지 로드 시 테이블 생성
    updateChargedPersonStatusTable();

    // 전역에서 사용할 수 있도록 함수 등록
    window.updateChargedPersonStatusTable = updateChargedPersonStatusTable;
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
                            window.updateChargedPersonStatusTable();
                        }
                    },
                    error: function() {
                        // 실패 시에도 테이블 다시 그리기 (기존 데이터로)
                        window.updateChargedPersonStatusTable();
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

 </body>
 </html>   