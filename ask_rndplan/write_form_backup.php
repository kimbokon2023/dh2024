<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/session.php';	
$title_message = '연구개발계획서';   
?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/common.php' ?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php'; ?>  
<title> <?php echo $title_message; ?> </title>
</head>
<style>
.show {display:block} /*보여주기*/
.hide {display:none} /*숨기기*/
  input[type="text"] {
    text-align: left !important ;
  }
  input[type="number"] {
    text-align: left !important ;
  }
 td, th, tr, span, input {
    vertical-align: middle;
  }

/* Enhanced File Processing Styles */
.file-display-area, .image-display-area {
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid #e9ecef;
    border-radius: 0.375rem;
    padding: 10px;
    background-color: #f8f9fa;
}

.file-item, .image-item {
    transition: all 0.2s ease;
    background-color: white;
}

.file-item:hover, .image-item:hover {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transform: translateY(-1px);
}

.file-info {
    flex-grow: 1;
}

.file-info a {
    font-weight: 500;
    color: #0d6efd;
}

.file-info a:hover {
    color: #0b5ed7;
    text-decoration: underline !important;
}

.progress {
    height: 8px;
}

.progress-bar-animated {
    animation: progress-bar-stripes 1s linear infinite;
}

.modal-lg {
    max-width: 900px;
}

#fileUploadModal .modal-body {
    max-height: 70vh;
    overflow-y: auto;
}

.btn-sm {
    font-size: 0.875rem;
}

.img-thumbnail {
    border: 2px solid #dee2e6;
    transition: border-color 0.15s ease-in-out;
}

.img-thumbnail:hover {
    border-color: #0d6efd;
}

/* Summernote customization */
.note-editor {
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
}

.note-editing-area {
    background-color: white;
}

/* File status summary */
#fileStatusSummary {
    font-size: 0.875rem;
    padding: 0.25rem 0.5rem;
    background-color: #e9ecef;
    border-radius: 0.25rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .modal-lg {
        max-width: 95%;
        margin: 1rem auto;
    }

    .file-display-area, .image-display-area {
        max-height: 200px;
    }

    .file-item .d-flex {
        flex-direction: column;
        align-items: flex-start !important;
    }

    .file-item .btn {
        margin-top: 0.5rem;
        align-self: flex-end;
    }
}

/* Loading animations */
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

.alert-info {
    border-left: 4px solid #0dcaf0;
}

/* Enhanced button styles */
.btn-outline-primary:hover, .btn-outline-success:hover, .btn-outline-info:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Download section styles */
.download-file-card, .download-image-card {
    transition: all 0.2s ease;
    border: 1px solid #e9ecef;
}

.download-file-card:hover, .download-image-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.download-file-card .file-icon {
    display: flex;
    align-items: center;
    justify-content: center;
}

.card-img-top-wrapper {
    background-color: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.download-image-card .card-body {
    padding: 15px;
}

.download-image-card .card-title {
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 8px;
}
</style>	

<body>

<?php include $_SERVER['DOCUMENT_ROOT'] . "/common/modal.php"; ?>
   
<?php   

$tablename = 'eworks';  
  
$mode=  $_REQUEST["mode"] ?? '' ;
$num=  $_REQUEST["num"] ?? '' ;
$author=  $user_name ?? '' ;
$indate=date("Y-m-d") ?? '' ;
 
  if ($mode=="modify" or $mode=="view"){
    try{
      $sql = "select * from {$DB}.eworks where num = ? ";
      $stmh = $pdo->prepare($sql); 

      $stmh->bindValue(1,$num,PDO::PARAM_STR); 
      $stmh->execute();
      $count = $stmh->rowCount();            
	  $row = $stmh->fetch(PDO::FETCH_ASSOC);  // $row 배열로 DB 정보를 불러온다.
    if($count<1){  
      print "결과가 없습니다.<br>";
     }else{		 
 		include  $_SERVER['DOCUMENT_ROOT'] . '/eworks/_row.php';	
		// 전자결재의 정보를 다시 변환해 준다.		
		$mytitle = $outworkplace ?? '';
		$content = $al_content ?? '';
		$content_reason = $request_comment ?? '';	

		$titlemsg = $mode === 'modify' ? '연구개발계획서(수정)' : '연구개발계획서(조회)'; 
		
      }
     }catch (PDOException $Exception) {
       print "오류: ".$Exception->getMessage();
     } 
  }
  
      
  if ($mode!="modify" and $mode!="view" and $mode!="copy"){    // 수정모드가 아닐때 신규 자료일때는 변수 초기화 한다.

	$indate=date("Y-m-d");
	$author = $user_name;
	$titlemsg = '연구개발계획서 작성';

	// 변수 초기화
	$al_part = '';
	$e_confirm = '';
	$registdate = date("Y-m-d");
	$exam = array("name" => "", "date" => "");
	$status = '';
  } 
  
  if ($mode=="copy"){
    try{
      $sql = "select * from {$DB}.eworks where num = ? ";
      $stmh = $pdo->prepare($sql); 

      $stmh->bindValue(1,$num,PDO::PARAM_STR); 
      $stmh->execute();
      $count = $stmh->rowCount();            
	  $row = $stmh->fetch(PDO::FETCH_ASSOC);  // $row 배열로 DB 정보를 불러온다.
    if($count<1){  
      print "결과가 없습니다.<br>";
     }else{
		 include $_SERVER['DOCUMENT_ROOT'] .'/eworks/_row.php';		
		// 전자결재의 정보를 다시 변환해 준다.		
		$mytitle = $outworkplace ?? '';
		$content = $al_content ?? '';
		$content_reason = $request_comment ?? '';	
		$indate=date("Y-m-d");
      }
     }catch (PDOException $Exception) {
       print "오류: ".$Exception->getMessage();
     }
	 
     $titlemsg	= '(데이터 복사) 연구개발계획서';	
	 $num='';	 
	 $id = $num;  
	 $parentid = $num;    
	 $author = $user_name;
	 $update_log='';
  }  
  
// 초기 프로그램은 $num사용 이후 $id로 수정중임
$id=$num;

// 전역 변수 초기화 (Notice 오류 방지)
$al_part = $al_part ?? '';
$e_confirm = $e_confirm ?? '';
$registdate = $registdate ?? date("Y-m-d");
$exam = $exam ?? array("name" => "", "date" => "");
$status = $status ?? '';
$mytitle = $mytitle ?? '';
$content = $content ?? '';    
// Load existing attached files and images (only for modify/view/copy modes)
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

// 첨부파일 있는 것 불러오기 (신규 모드가 아닐 때만)
$savefilename_arr=array();
$realname_arr=array();
$realimagename_arr=array();
$saveimagename_arr=array();

if ($mode == "modify" || $mode == "view" || $mode == "copy") {
    $tablename = 'eworks';

    // 첨부파일 불러오기
    $item = 'attached';
    $sql=" select * from ".$DB.".fileuploads where tablename ='$tablename' and item ='$item' and parentid ='$id' ";

    try{
       $stmh = $pdo->query($sql);
       while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
            array_push($realname_arr, $row["realname"]);
            array_push($savefilename_arr, $row["savename"]);
        }
    } catch (PDOException $Exception) {
        // 신규 모드가 아닐 때만 오류 출력
        if ($mode != "") {
            print "첨부파일 불러오기 오류: ".$Exception->getMessage();
        }
    }

    // 첨부 이미지 불러오기
    $item = 'image';
    $sql=" select * from ".$DB.".fileuploads where tablename ='$tablename' and item ='$item' and parentid ='$id' ";

    try{
       $stmh = $pdo->query($sql);
       while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
            array_push($realimagename_arr, $row["realname"]);
            array_push($saveimagename_arr, $row["savename"]);
        }
    } catch (PDOException $Exception) {
        // 신규 모드가 아닐 때만 오류 출력
        if ($mode != "") {
            print "이미지 불러오기 오류: ".$Exception->getMessage();
        }
    }
}  
?>

<form id="board_form" name="board_form" method="post"  onkeydown="return captureReturnKey(event)"  >	
    
	<!-- 전달함수 설정 input hidden -->
	<input type="hidden" id="id" name="id" value="<?php echo $id; ?>" >
	<input type="hidden" id="num" name="num" value="<?php echo $num; ?>" >
	<input type="hidden" id="parentid" name="parentid" value="<?php echo $parentid ?? ''; ?>" >
	<input type="hidden" id="item" name="item" value="<?php echo $item; ?>" >
	<input type="hidden" id="tablename" name="tablename" value="<?php echo $tablename; ?>" >
	<input type="hidden" id="savetitle" name="savetitle" value="<?php echo $savetitle; ?>" >
	<input type="hidden" id="pInput" name="pInput" value="<?php echo $pInput; ?>" >
	<input type="hidden" id="mode" name="mode" value="<?php echo $mode; ?>" >
	<input type="hidden" id="timekey" name="timekey" value="<?php echo date('Y_m_d_H_i_s'); ?>" >  <!-- 신규데이터 작성시 parentid key값으로 사용 -->
	<input type="hidden" id="update_log" name="update_log" value="<?php echo $update_log ?? ''; ?>"  >
	<input type="hidden" id="first_writer" name="first_writer" value="<?php echo $first_writer; ?>"  >

	<!-- dev.md 패턴에 따른 파일 처리 필드 -->
	<input type="hidden" id="fileorimage" name="fileorimage" value="">
	<input type="hidden" id="upfilename" name="upfilename" value="">
	<input type="hidden" id="searchtext" name="searchtext" value="">  <!-- summernote text저장 -->		
	
<div class="container-fluid" >
<div class="card">
<div class="card-body">			   

<div class="row">
	<div class="col-sm-7">
		<div class="d-flex mb-5 mt-5 justify-content-center align-items-center ">
			<h4> <?php echo $titlemsg; ?> 
			</h4> 
		</div>
	</div>
    <div class="col-sm-5">		
	<?php
		//var_dump($al_part);			

		$al_part=='지원파트';
	   if($e_confirm ==='' || $e_confirm === null) 
	   {
			$formattedDate = date("Y-m-d", strtotime($registdate)); // 월/일 형식으로 변환			
			
			if($al_part=='지원파트')
			{
				$approvals = array(
					array("name" => "이사 최장중", "date" =>  $formattedDate),
					array("name" => "대표 소현철", "date" =>  $formattedDate),
					// 더 많은 결재권자가 있을 수 있음...
				);	
			}
	   }	   
	else
		{
			$approver_ids = explode('!', $e_confirm_id);
			$approver_details = explode('!', $e_confirm);

			$approvals = array();
			$exam = array();

			if (empty($store)) {
				$exam['name'] = '안현섭 연구원';
				$exam['date'] = '';
			} else {
				// 공백을 기준으로 마지막 값을 날짜로 분리
				$parts = explode(' ', $store);
				$exam['date'] = array_pop($parts); // 마지막 요소: '10:04:48'
				$exam['date'] = array_pop($parts) . ' ' . $exam['date']; // 하나 더 꺼내서 날짜와 합치기
				$exam['name'] = implode(' ', $parts); // 나머지는 이름
			}
			
			foreach($approver_ids as $index => $id) {
				if (isset($approver_details[$index])) {

					// 날짜 기준 (예: "2025-02-01")
					$baseDate = $indate;

					// 대상자별 시간대 설정
					if ($index === 0) { // 첫 번째는 연구원
						$hour = 11;					
					} else { // 나머지는 최종결재권자 (14시~17시)
						$hour = rand(14, 17);
					}

					$minute = rand(0, 59);
					$second = rand(0, 59);
					$randomTime = sprintf('%02d:%02d:%02d', $hour, $minute, $second);
					$timestamp = strtotime("$baseDate $randomTime");
					$formattedDate = date("Y-m-d H:i:s", $timestamp);

					// 첫 번째는 연구원 정보 저장
					if (empty($exam['date'])) {						
						 // 첫 번째는 연구원
						$hour = 10;					
						$minute = rand(0, 59);
						$second = rand(0, 59);
						$randomTime = sprintf('%02d:%02d:%02d', $hour, $minute, $second);
						$timestamp = strtotime("$baseDate $randomTime");
						$FirstformattedDate = date("Y-m-d H:i:s", $timestamp);	
						$exam['date'] = $FirstformattedDate;
					}

					// 원래 문자열에서 이름 + 직함 추출
					preg_match("/^(.+ \d{4}-\d{2}-\d{2}) (\d{2}:\d{2}:\d{2})$/", $approver_details[$index], $matches);
					if (count($matches) === 3) {
						$nameWithTitle = $matches[1];
						$date = substr($nameWithTitle, -10); // '2024-02-28'
						$nameWithTitle = trim(str_replace($date, '', $nameWithTitle));
						if (preg_match('/\d{4}-\d{2}-\d{2}/',$nameWithTitle , $secondmatches)) {
								$date = $secondmatches[0]; // 결과: 2025-02-28
							}
						
						// 날짜가 다르면만 처리
						if ($date !== $indate) 
							$approvals[] = array("name" => $nameWithTitle, "date" => $formattedDate);
						else
							$approvals[] = array("name" => $nameWithTitle, "date" => ($date . ' '. $matches[2]));
					}

				}
			}
		}	

    // 검토자 결재정보 생성
	$store =  $exam["name"] . ' ' .  $exam['date'] ;
		
	if($status === 'end' and ($e_confirm !=='' && $e_confirm !== null) )
		{
	?>				
		
	<input type="hidden" id="store" name="store" value="<?php echo $store; ?>"  >		  <!--store는 검토자와 날짜시간 기록 -->
	<input type="hidden" id="firstTime" name="firstTime" value="<?php echo $approvals[0]["date"]; ?>" >
	<input type="hidden" id="secondTime" name="secondTime" value="<?php echo $approvals[1]["date"]; ?>" >
	
			<div class="container mb-2">
				<table class="table table-bordered">
					<thead>
						<tr>
							<th colspan="1" class="text-center fs-6">검토</th>
							<th id="setApprovalTime" colspan="<?php echo count($approvals); ?>" class="text-center fs-6">결재 <i class="bi bi-gear"></i></th>
						</tr>
					</thead>
					<tbody>
						<tr>
						    <td class="text-center fs-6" style="height: 60px;"><?php echo $exam["name"]; ?></td>
							<?php foreach ($approvals as $approval) { ?>
								<td class="text-center fs-6" style="height: 60px;"><?php echo $approval["name"]; ?></td>
							<?php } ?>
						</tr>
						<tr>
							<td class="text-center"><?php echo $exam["date"]; ?></td>
							<?php foreach ($approvals as $approval) { ?>
								<td class="text-center"><?php echo $approval["date"]; ?></td>
							<?php } ?>
						</tr>
					</tbody>
				</table>
			</div>			  
		  
		  <?php }
		 else
			 {
		   ?>
	<div class="container mb-2">
		<table class="table table-bordered">
			<thead>
				<tr>
					<th colspan="<?php echo count($approvals); ?>" class="text-center fs-6">결재 진행 전</th>
				</tr>
			</thead>
			<tbody>
				<tr>								
				</tr>
			</tbody>
		</table>
	</div>	
  <?php } ?>
	  
 </div> 			
</div> 
 	
	
<?php if($mode!='view') { ?>		
	
	<div class="row">
		<div class="col-sm-9">		   
			<div class="d-flex  mb-1 justify-content-start  align-items-center"> 		   
				<button id="saveBtn" type="button" class="btn btn-dark  btn-sm me-2"  > <i class="bi bi-floppy"></i> 저장(결재상신)  </button> 
			</div> 			
		</div> 	
		<div class="col-sm-3">	
				<div class="d-flex  mb-1 justify-content-end"> 	
				   <button class="btn btn-secondary btn-sm" onclick="self.close();"  > <i class="bi bi-x-lg"></i> 창닫기 </button>&nbsp;					
				</div> 			
		</div> 			
	</div> 
<?php } else {  ?>		
       <div class="row">
		<?php if($chkMobile) { ?>	
		  <div class="col-sm-12">
		<?php } if(!$chkMobile) { ?>	
		  <div class="col-sm-7">
		<?php  } ?>			 		   
			<div class="d-flex  justify-content-start"> 							
				<?php if($chkMobile==true)	{ ?>
					<button class="btn btn-dark btn-sm" onclick="location.href='list.php'" > <i class="bi bi-card-list"></i> 목록 </button>&nbsp;	
				<?php } ?>						
					<button type="button"   class="btn btn-dark btn-sm" onclick="location.href='write_form.php?mode=modify&num=<?php echo $num; ?>'" > <i class="bi bi-pencil-square"></i>  수정 </button> &nbsp;
				<?php if($user_id === $author_id || $admin)	{ ?>
						<button type="button"  class="btn btn-danger btn-sm" onclick="javascript:deleteFn('delete.php?num=<?php echo $num; ?>&page=<?php echo $page; ?>')" ><i class="bi bi-trash"></i>  삭제 </button>	 &nbsp;
				<?php } ?>									
					<button type="button"   class="btn btn-dark btn-sm" onclick="location.href='write_form.php'" > <i class="bi bi-pencil"></i>  신규 </button>		&nbsp;										
					<button type="button"   class="btn btn-primary btn-sm" onclick="location.href='write_form.php?mode=copy&num=<?php echo $num; ?>'" > <i class="bi bi-copy"></i> 복사 </button>	&nbsp;							
			 </div> 			
		 </div> 			
		<?php if($chkMobile) { ?>	
		  <div class="col-sm-12">
		<?php } if(!$chkMobile) { ?>	
		  <div class="col-sm-5 text-end">
		<?php  } ?>	
				<div class="d-flex  mb-1 justify-content-end"> 	
					<button class="btn btn-secondary btn-sm" type="button" onclick="self.close();" >  &times; 창닫기 </button>&nbsp;									
				</div> 					 
			</div>
	 </div> <!-- end of row -->
<?php } // end of if status === 'end'  ?>

  <div class="row mt-2">  
      <table class="table table-bordered">
		<tr>
		  <td class=" text-center w-25 fw-bold">
			<label for="indate">작성일</label>
		  </td>          			
		 <td >				
			<input type="date" class="form-control w120px viewNoBtn" id="indate" name="indate" value="<?php echo $indate; ?>" >				
		  </td>	
		   <td class=" text-center w-25 fw-bold">
			<label for="author">작성자</label>
		  </td>          			
		 <td>				
			<input type="text" class="form-control text-center w80px viewNoBtn" id="author" name="author" value="<?php echo $author; ?>" >				
		  </td>					 
		</tr>
		<tr>
		  <td class=" text-center w-25 fw-bold">
			<label for="mytitle">제목</label>
		  </td>
		  <td colspan="3">
				<input type="text" class="form-control viewNoBtn" id="mytitle" name="mytitle" value="<?php echo $mytitle; ?>"  placeholder ="연구개발계획서 제목 " >
		  </td>
		</tr>		
      </table>
    </div>
	<div class="row mt-2">  	
	  <table class="table table-bordered">             
		<tr>
		  <td class="text-center w-10 fw-bold">
			<label for="content">내용</label>
		  </td>
		  <td>
			<div id="summernote-container">
				<textarea class="form-control auto-expand viewNoBtn" id="content" name="content" autocomplete="off" placeholder="내용" required rows="10" style="resize: none;"><?php echo $content; ?></textarea>
			</div>
		  </td>
		</tr>
	  </table>	  	  
	</div>   
   <div class="d-flex mb-3 justify-content-center align-items-center">
		<button type="button" class="btn btn-outline-primary btn-sm mx-2 viewNoSpan" data-bs-toggle="modal" data-bs-target="#fileUploadModal">
			<i class="bi bi-paperclip"></i> 파일 및 이미지 관리
		</button>
		<button type="button" id="insertImageToEditor" class="btn btn-outline-success btn-sm mx-2 viewNoSpan">
			<i class="bi bi-image"></i> 에디터에 이미지 삽입
		</button>
		<div id="fileStatusSummary" class="mx-2 text-muted"></div>
	</div>

	<!-- Hidden file inputs for enhanced processing -->
	<input id="upfile" name="upfile[]" type="file" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.hwp,.txt" style="display:none">
	<input id="upfileimage" name="upfileimage[]" type="file" multiple accept=".jpg,.jpeg,.png,.gif,.webp" style="display:none">

	<!-- Additional hidden fields for enhanced processing -->
	<input type="hidden" id="fileProcessingMode" name="fileProcessingMode" value="enhanced">
	
	 </div>
		</div>
		</div>
		</div>
 </div>
</form>

<?php /*
if(isset($mode) && ($mode == 'modify' || $mode == 'view')): ?>
<!-- 첨부파일 다운로드 섹션 -->
<div class="container-fluid mt-3">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="bi bi-paperclip"></i> 첨부파일</h6>
            <small class="text-muted">총 <span id="downloadFileCount">0</span>개 파일</small>
        </div>
        <div class="card-body">
            <div id="downloadFileList" class="row">
                <!-- 파일 목록이 여기에 동적으로 로드됩니다 -->
            </div>
        </div>
    </div>
</div>

<!-- 첨부이미지 다운로드 섹션 -->
<div class="container-fluid mt-3">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="bi bi-image"></i> 첨부이미지</h6>
            <small class="text-muted">총 <span id="downloadImageCount">0</span>개 이미지</small>
        </div>
        <div class="card-body">
            <div id="downloadImageList" class="row">
                <!-- 이미지 목록이 여기에 동적으로 로드됩니다 -->
            </div>
        </div>
    </div>
</div>
<?php endif;
*/ ?>

<!-- Enhanced File Upload Modal -->
<div class="modal fade" id="fileUploadModal" tabindex="-1" aria-labelledby="fileUploadModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="fileUploadModalLabel">파일 및 이미지 관리</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6">
            <h6>📎 첨부파일 업로드</h6>
            <div class="border rounded p-3 mb-3">
              <input type="file" id="modalUpfile" name="upfile[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.hwp,.txt" style="display:none">
              <button type="button" class="btn btn-outline-primary btn-sm w-100 mb-2" onclick="$('#upfile').click();">
                <i class="bi bi-paperclip"></i> 파일 첨부 (PDF, DOC, XLS, HWP 등)
              </button>
              <div id="fileUploadProgress" class="progress mb-2" style="display:none;">
                <div class="progress-bar" role="progressbar" style="width: 0%"></div>
              </div>
              <div id="displayFile" class="file-display-area"></div>
            </div>
          </div>
          <div class="col-md-6">
            <h6>🖼️ 이미지 업로드</h6>
            <div class="border rounded p-3 mb-3">
              <input type="file" id="modalUpfileimage" name="upfileimage[]" multiple accept=".jpg,.jpeg,.png,.gif,.webp" style="display:none">
              <button type="button" class="btn btn-outline-success btn-sm w-100 mb-2" onclick="$('#upfileimage').click();">
                <i class="bi bi-image"></i> 이미지 첨부 (JPG, PNG, GIF 등)
              </button>
              <div id="imageUploadProgress" class="progress mb-2" style="display:none;">
                <div class="progress-bar" role="progressbar" style="width: 0%"></div>
              </div>
              <div id="displayImage" class="image-display-area"></div>
            </div>
          </div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
        <button type="button" id="saveAllFiles" class="btn btn-primary">모든 파일 저장</button>
      </div>
    </div>
  </div>
</div>
<script>
// Global variable for AJAX request handling
var ajaxRequest = null;

// 페이지 로딩
$(document).ready(function(){
    var loader = document.getElementById('loadingOverlay');
	if(loader)
		loader.style.display = 'none';
});    
$(document).ready(function(){		
	 $("#saveBtn").click(function(){ 
		// 조건 확인
		if($("#mytitle").val() === '' || $("#content").val() === ''  ) {
			showWarningModal();
		} else {
		   showMsgModal(2); // 파일저장중
			Toastify({
				text: "변경사항 저장중...",
				duration: 2000,
				close:true,
				gravity:"top",
				position: "center",
				style: {
					background: "linear-gradient(to right, #00b09b, #96c93d)"
				},
			}).showToast();	
			setTimeout(function(){
					 saveData();
			}, 1000);
		  
		}
	});
	
	// 강제 결재시간 세팅 동작	
	$("#setApprovalTime").click(function() {
	    showMsgModal(2); // 파일저장중
			Toastify({
				text: "결재시간 조정중 ..",
				duration: 2000,
				close:true,
				gravity:"top",
				position: "center",
				style: {
					background: "linear-gradient(to right, #00b09b, #96c93d)"
				},
			}).showToast();	
			setTimeout(function(){
					 updateData();
			}, 1000);
	});	

	function showWarningModal() {
		Swal.fire({                                    
			title: '등록 오류 알림',
			text: '제목, 내용, 사유는 필수입력 요소입니다.',
			icon: 'warning',
			// ... 기타 설정 ...
		}).then(result => {
			if (result.isConfirmed) { 
				return; // 사용자가 확인 버튼을 누르면 아무것도 하지 않고 종료
			}         
		});
	}

	function saveData() {		
		var num = $("#num").val();  		
		// 결재상신이 아닌경우 수정안됨     
		if(Number(num) < 1) 				
				$("#mode").val('insert');     			  						
		//  console.log($("#mode").val());    
		// 폼데이터 전송시 사용함 Get form         
		var form = $('#board_form')[0];  	    	
		var datasource = new FormData(form); 

		// console.log(data);
		if (ajaxRequest !== null) {
			ajaxRequest.abort();
		}		 
		ajaxRequest = $.ajax({
			enctype: 'multipart/form-data',    // file을 서버에 전송하려면 이렇게 해야 함 주의
			processData: false,    
			contentType: false,      
			cache: false,           
			timeout: 600000, 			
			url: "insert.php",
			type: "post",		 
			data: datasource,			
			dataType: "json", 
			success : function(data){
				  console.log('data :' , data);
				  Swal.fire(
					  '자료등록 완료',
					  '데이터가 성공적으로 등록되었습니다.',
					  'success'
					);
				setTimeout(function(){									
					if (window.opener && !window.opener.closed) {
						// 부모 창에 restorePageNumber 함수가 있는지 확인
						if (typeof window.opener.restorePageNumber === 'function') {
							window.opener.restorePageNumber(); // 함수가 있으면 실행
						}								
					}
				setTimeout(function(){		
					hideMsgModal();	
					// location.href = "view.php?num=" + data["num"];
					self.close();
				}, 1000);	
							
				}, 1000);						
			},
			error : function( jqxhr , status , error ){
				console.log( jqxhr , status , error );
						} 			      		
		   });					
	}	
	
	function updateData() {		
		var num = $("#num").val();  				
		var form = $('#board_form')[0];  	    	
		var datasource = new FormData(form); 

		// console.log(data);
		if (ajaxRequest !== null) {
			ajaxRequest.abort();
		}		 
		ajaxRequest = $.ajax({
			enctype: 'multipart/form-data',    // file을 서버에 전송하려면 이렇게 해야 함 주의
			processData: false,    
			contentType: false,      
			cache: false,           
			timeout: 600000, 			
			url: "/ask_rndreport/approval_update.php",
			type: "post",		 
			data: datasource,			
			dataType: "json", 
			success : function(data){
				  console.log('data :' , data);
				  Swal.fire(
					  '자료수정 완료',
					  '결재시간이 성공적으로 수정되었습니다.',
					  'success'
					);
				setTimeout(function(){									
					if (window.opener && !window.opener.closed) {
						// 부모 창에 restorePageNumber 함수가 있는지 확인
						if (typeof window.opener.restorePageNumber === 'function') {
							window.opener.restorePageNumber(); // 함수가 있으면 실행
						}								
					}
				setTimeout(function(){		
					hideMsgModal();											
				  }, 1000);								
				}, 1000);						
			},
			error : function( jqxhr , status , error ){
				console.log( jqxhr , status , error );
						} 			      		
		   });					
	}	
	
	
});

// 파일삭제
function deleteFn(href) {    
	// 삭제 확인
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
				url: 'delete.php',
				type: 'post',
				data: $("#board_form").serialize(),
				dataType: 'json',
			}).done(function(data) {
				// 삭제 후 처리
				Toastify({
					text: "파일 삭제완료 ",
					duration: 2000,
					close: true,
					gravity: "top",
					position: "center",
					style: {
						background: "linear-gradient(to right, #00b09b, #96c93d)"
					},
				}).showToast();
				setTimeout(function() {
					if (window.opener && !window.opener.closed) {
						window.opener.restorePageNumber(); // 부모 창에서 페이지 번호 복원
						window.opener.location.reload(); // 부모 창 새로고침
					}
					setTimeout(function() {  window.close(); }, 500);
					
				}, 1000);
			});
		}
	});
}
	 
function captureReturnKey(e) {
    if(e.keyCode==13 && e.srcElement.type != 'textarea')
    return false;
}
</script> 

<script>
// Enhanced File Processing System
var fileProcessingSystem = {
    // Configuration
    config: {
        maxFileSize: 100 * 1024 * 1024, // 100MB
        maxImageSize: 10 * 1024 * 1024,  // 10MB
        allowedFileTypes: ['.pdf', '.doc', '.docx', '.xls', '.xlsx', '.hwp', '.txt', '.zip', '.rar'],
        allowedImageTypes: ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.bmp'],
        uploadTimeout: 600000
    },

    // State management
    state: {
        uploadedFiles: [],
        uploadedImages: [],
        processingFiles: false
    },

    // Initialize system
    init: function() {
        this.bindEvents();
        this.initializeSummernote();
        this.loadExistingFiles();
        this.startFileMonitoring();
    },

    // Enhanced Summernote with image processing
    initializeSummernote: function() {
        if ($('#content').length && !$('#content').hasClass('summernote-initialized')) {
            $('#content').summernote({
                placeholder: '연구개발계획서 내용을 작성하세요...',
                height: 400,
                width: '100%',
                maximumImageFileSize: 5 * 1024 * 1024, // 5MB
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ],
                callbacks: {
                    onImageUpload: function(files) {
                        if (files.length > 0) {
                            fileProcessingSystem.processImageForEditor(files[0]);
                        }
                    },
                    onChange: function(contents) {
                        fileProcessingSystem.extractSearchText(contents);
                    }
                }
            }).addClass('summernote-initialized');
        }
    },

    // Process image for Summernote editor
    processImageForEditor: function(file) {
        if (file.size > this.config.maxImageSize) {
            this.showError('이미지 크기가 너무 큽니다. 10MB 이하로 업로드해주세요.');
            return;
        }

        this.resizeImage(file, function(resizedDataURL) {
            $('#content').summernote('insertImage', resizedDataURL);
        });
    },

    // Enhanced image resizing
    resizeImage: function(file, callback) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var img = new Image();
            img.onload = function() {
                var canvas = document.createElement('canvas');
                var ctx = canvas.getContext('2d');

                // Calculate optimal dimensions
                var MAX_WIDTH = 800;
                var MAX_HEIGHT = 600;
                var width = img.width;
                var height = img.height;

                if (width > height) {
                    if (width > MAX_WIDTH) {
                        height *= MAX_WIDTH / width;
                        width = MAX_WIDTH;
                    }
                } else {
                    if (height > MAX_HEIGHT) {
                        width *= MAX_HEIGHT / height;
                        height = MAX_HEIGHT;
                    }
                }

                canvas.width = width;
                canvas.height = height;
                ctx.drawImage(img, 0, 0, width, height);

                // High quality JPEG output
                var dataURL = canvas.toDataURL('image/jpeg', 0.85);
                callback(dataURL);
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    },

    // Bind all event handlers
    bindEvents: function() {
        var self = this;

        // Motor RND 패턴 파일 업로드 (dev.md 기반)
        $('#upfile').on('change', function(e) {
            // 파일 선택 확인
            if (!e.target.files || e.target.files.length === 0) {
                self.showError('파일을 선택해주세요.');
                return;
            }

            $("#fileorimage").val('file');
            $("#item").val('attached');
            $("#upfilename").val('upfile');
            $("#savetitle").val('연구개발계획서 첨부파일');

            // 임시번호 부여 (신규 작성시)
            if(Number($("#id").val()) == 0)
                $("#id").val($("#timekey").val());

            // FormData 생성 (askitem_ER 패턴)
            var form = $('#board_form')[0];
            var data = new FormData(form);

            $.ajax({
                enctype: 'multipart/form-data',
                processData: false,
                contentType: false,
                cache: false,
                timeout: 600000,
                url: "../file/file_insert.php",
                type: "post",
                data: data,
                success: function(data) {
                    $("#pInput").val('100'); // 업로드 완료 플래그

                    // 즉시 파일 목록 새로고침
                    setTimeout(function() {
                        fileProcessingSystem.refreshAll();
                    }, 100);

                    Toastify({
                        text: "파일 업로드 완료",
                        duration: 2000,
                        close: true,
                        gravity: "top",
                        position: "center",
                        style: {
                            background: "linear-gradient(to right, #00b09b, #96c93d)"
                        }
                    }).showToast();
                },
                error: function(jqxhr, status, error) {
                    console.log('Upload error:', jqxhr, status, error);
                    Toastify({
                        text: "파일 업로드 실패: " + error,
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "center",
                        style: {
                            background: "linear-gradient(to right, #ff5f6d, #ffc371)"
                        }
                    }).showToast();
                }
            });
        });

        // Motor RND 패턴 이미지 업로드 (dev.md 기반)
        $('#upfileimage').on('change', function(e) {
            // 이미지 파일 선택 확인
            if (!e.target.files || e.target.files.length === 0) {
                self.showError('이미지 파일을 선택해주세요.');
                return;
            }

            $("#fileorimage").val('file');
            $("#item").val('image');
            $("#upfilename").val('upfileimage');
            $("#savetitle").val('연구개발계획서 이미지');

            // 임시번호 부여 (신규 작성시)
            if(Number($("#id").val()) == 0)
                $("#id").val($("#timekey").val());

            // FormData 생성 (askitem_ER 패턴)
            var form = $('#board_form')[0];
            var data = new FormData(form);

            $.ajax({
                enctype: 'multipart/form-data',
                processData: false,
                contentType: false,
                cache: false,
                timeout: 600000,
                url: "../file/file_insert.php",
                type: "post",
                data: data,
                success: function(data) {
                    console.log('Image upload response:', data);

                    // 서버 응답에서 오류 확인
                    if (data.includes('Notice:') || data.includes('Warning:') || data.includes('Error:')) {
                        console.error('Server error in response:', data);
                        self.showError('이미지 업로드 중 서버 오류가 발생했습니다. 콘솔을 확인해주세요.');
                        return;
                    }

                    $("#pInput").val('100'); // 업로드 완료 플래그

                    // 즉시 이미지 목록 새로고침
                    setTimeout(function() {
                        fileProcessingSystem.refreshAll();
                    }, 100);

                    Toastify({
                        text: "이미지 업로드 완료",
                        duration: 2000,
                        close: true,
                        gravity: "top",
                        position: "center",
                        style: {
                            background: "linear-gradient(to right, #00b09b, #96c93d)"
                        }
                    }).showToast();
                },
                error: function(jqxhr, status, error) {
                    console.log('Image upload error:', jqxhr, status, error);
                    Toastify({
                        text: "이미지 업로드 실패: " + error,
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "center",
                        style: {
                            background: "linear-gradient(to right, #ff5f6d, #ffc371)"
                        }
                    }).showToast();
                }
            });
        });


        // Insert image to editor
        $('#insertImageToEditor').on('click', function() {
            $('#upfileimage').click();
        });

        // Save all files
        $('#saveAllFiles').on('click', function() {
            self.saveAllPendingFiles();
        });

        // Modal file upload handlers (process directly)
        $('#modalUpfile').on('change', function(e) {
            // 파일 선택 확인
            if (!e.target.files || e.target.files.length === 0) {
                self.showError('파일을 선택해주세요.');
                return;
            }

            $("#fileorimage").val('file');
            $("#item").val('attached');
            $("#upfilename").val('modalUpfile');
            $("#savetitle").val('연구개발계획서 첨부파일');

            // 임시번호 부여 (신규 작성시)
            if(Number($("#id").val()) == 0)
                $("#id").val($("#timekey").val());

            console.log('Modal Upload parameters:', {
                id: $("#id").val(),
                num: $("#num").val(),
                tablename: $("#tablename").val(),
                upfilename: $("#upfilename").val(),
                item: $("#item").val(),
                fileorimage: $("#fileorimage").val()
            });

            // FormData 생성 및 AJAX 전송
            var form = $('#board_form')[0];
            var data = new FormData(form);

            $.ajax({
                enctype: 'multipart/form-data',
                processData: false,
                contentType: false,
                cache: false,
                timeout: 600000,
                url: "../file/file_insert.php",
                type: "post",
                data: data,
                success: function(data) {
                    console.log('Modal Upload response:', data);

                    // 서버 응답에서 오류 확인
                    if (data.includes('Notice:') || data.includes('Warning:') || data.includes('Error:')) {
                        console.error('Server error in response:', data);
                        self.showError('파일 업로드 중 서버 오류가 발생했습니다. 콘솔을 확인해주세요.');
                        return;
                    }

                    $("#pInput").val('100'); // 업로드 완료 플래그

                    Toastify({
                        text: "파일 업로드 완료",
                        duration: 2000,
                        close: true,
                        gravity: "top",
                        position: "center",
                        style: {
                            background: "linear-gradient(to right, #00b09b, #96c93d)"
                        }
                    }).showToast();
                },
                error: function(jqxhr, status, error) {
                    console.log('Modal Upload error:', jqxhr, status, error);
                    Toastify({
                        text: "파일 업로드 실패: " + error,
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "center",
                        style: {
                            background: "linear-gradient(to right, #ff5f6d, #ffc371)"
                        }
                    }).showToast();
                }
            });
        });

        $('#modalUpfileimage').on('change', function(e) {
            // 이미지 파일 선택 확인
            if (!e.target.files || e.target.files.length === 0) {
                self.showError('이미지 파일을 선택해주세요.');
                return;
            }

            $("#fileorimage").val('file');
            $("#item").val('image');
            $("#upfilename").val('modalUpfileimage');
            $("#savetitle").val('연구개발계획서 이미지');

            // 임시번호 부여 (신규 작성시)
            if(Number($("#id").val()) == 0)
                $("#id").val($("#timekey").val());

            console.log('Modal Image Upload parameters:', {
                id: $("#id").val(),
                num: $("#num").val(),
                tablename: $("#tablename").val(),
                upfilename: $("#upfilename").val(),
                item: $("#item").val(),
                fileorimage: $("#fileorimage").val()
            });

            // FormData 생성 및 AJAX 전송
            var form = $('#board_form')[0];
            var data = new FormData(form);

            $.ajax({
                enctype: 'multipart/form-data',
                processData: false,
                contentType: false,
                cache: false,
                timeout: 600000,
                url: "../file/file_insert.php",
                type: "post",
                data: data,
                success: function(data) {
                    console.log('Modal Image Upload response:', data);

                    // 서버 응답에서 오류 확인
                    if (data.includes('Notice:') || data.includes('Warning:') || data.includes('Error:')) {
                        console.error('Server error in response:', data);
                        self.showError('이미지 업로드 중 서버 오류가 발생했습니다. 콘솔을 확인해주세요.');
                        return;
                    }

                    $("#pInput").val('100'); // 업로드 완료 플래그

                    // 즉시 이미지 목록 새로고침
                    setTimeout(function() {
                        fileProcessingSystem.refreshAll();
                    }, 100);

                    Toastify({
                        text: "이미지 업로드 완료",
                        duration: 2000,
                        close: true,
                        gravity: "top",
                        position: "center",
                        style: {
                            background: "linear-gradient(to right, #00b09b, #96c93d)"
                        }
                    }).showToast();
                },
                error: function(jqxhr, status, error) {
                    console.log('Modal Image Upload error:', jqxhr, status, error);
                    Toastify({
                        text: "이미지 업로드 실패: " + error,
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "center",
                        style: {
                            background: "linear-gradient(to right, #ff5f6d, #ffc371)"
                        }
                    }).showToast();
                }
            });
        });

        // File upload modal events
        $('#fileUploadModal').on('shown.bs.modal', function() {
            self.refreshFileDisplays();
        });
    },

    // Enhanced file upload handler
    handleFileUpload: function(event, itemType) {
        var files = event.target.files;
        if (!files || files.length === 0) return;

        this.state.processingFiles = true;
        this.showProgress(itemType);

        // Set up form data
        $("#fileorimage").val(itemType === 'image' ? 'image' : 'file');
        $("#item").val(itemType);
        $("#upfilename").val(event.target.id);
        $("#savetitle").val('연구개발계획서 ' + (itemType === 'image' ? '이미지' : '첨부파일'));

        // Ensure ID is set
        if (Number($("#id").val()) == 0 || $("#id").val() === '') {
            $("#id").val($("#timekey").val());
        }

        var form = $('#board_form')[0];
        var formData = new FormData(form);

        // Add action parameter for enhanced processor
        formData.append('action', 'upload');

        $.ajax({
            enctype: 'multipart/form-data',
            processData: false,
            contentType: false,
            cache: false,
            timeout: this.config.uploadTimeout,
            url: "../file/file_insert.php",
            type: "POST",
            data: formData,
            success: function(response) {
                fileProcessingSystem.handleUploadSuccess(response, itemType);
            },
            error: function(xhr, status, error) {
                fileProcessingSystem.handleUploadError(error, itemType);
            }
        });
    },

    // Handle successful upload
    handleUploadSuccess: function(response, itemType) {
        this.state.processingFiles = false;
        this.hideProgress(itemType);

        $("#pInput").val('100'); // Signal upload completion

        // Refresh displays
        this.refreshFileDisplays();
        this.updateFileStatusSummary();

        this.showSuccess('파일 업로드가 완료되었습니다.');
    },

    // Handle upload error
    handleUploadError: function(error, itemType) {
        this.state.processingFiles = false;
        this.hideProgress(itemType);
        this.showError('파일 업로드 중 오류가 발생했습니다: ' + error);
    },

    // Load existing files
    loadExistingFiles: function() {
        $("#pInput").val('50'); // Signal to load existing files
        this.refreshFileDisplays();
    },

    // Refresh all file displays
    refreshFileDisplays: function() {
        this.displayFiles();
        this.displayImages();
        this.updateFileStatusSummary();
    },

    // Display files with enhanced UI
    displayFiles: function() {
        $('#displayFile').show();
        var params = $("#id").val();
        // param이 숫자가 아니면 timekey 값을 사용 (askitem_ER 패턴)
        if(isNaN(params) || !params) {
            params = $("#timekey").val();
        }
        if (!params) return;

        var tablename = $("#tablename").val();
        var item = 'attached';

        $.ajax({
            url: '../file/load_file.php?id=' + params + '&tablename=' + tablename + '&item=' + item,
            type: 'post',
            dataType: 'json',
            success: function(data) {
                fileProcessingSystem.renderFileList(data, '#displayFile');
            },
            error: function() {
                fileProcessingSystem.showError('파일 목록을 불러오는데 실패했습니다.');
            }
        });
    }, 

    // Display images with enhanced UI
    displayImages: function() {
        $('#displayImage').show();
        var params = $("#id").val();
        // param이 숫자가 아니면 timekey 값을 사용 (askitem_ER 패턴)
        if(isNaN(params) || !params) {
            params = $("#timekey").val();
        }
        if (!params) return;

        var tablename = $("#tablename").val();
        var item = 'image';

        $.ajax({
            url: '../file/load_file.php?id=' + params + '&tablename=' + tablename + '&item=' + item,
            type: 'post',
            dataType: 'json',
            success: function(data) {
                fileProcessingSystem.renderImageList(data, '#displayImage');
            },
            error: function() {
                fileProcessingSystem.showError('이미지 목록을 불러오는데 실패했습니다.');
            }
        });
    },

    // Render enhanced file list
    renderFileList: function(data, container) {
        var html = '';
        if (data.recid > 0) {
            for (var i = 0; i < data.recid; i++) {
                var fileSize = this.formatFileSize(data.filesizes ? data.filesizes[i] : 0);
                html +=
                    '<div class="file-item border rounded p-2 mb-2" id="file' + i + '">' +
                        '<div class="d-flex justify-content-between align-items-center">' +
                            '<div class="file-info">' +
                                '<i class="bi bi-file-earmark text-primary"></i>' +
                                '<a href="../uploads/' + data.file_arr[i] + '" download="' + data.realfile_arr[i] + '" class="text-decoration-none">' +
                                    data.realfile_arr[i] +
                                '</a>' +
                                '<small class="text-muted d-block">' + fileSize + '</small>' +
                            '</div>' +
                            '<button type="button" class="btn btn-outline-danger btn-sm" onclick="fileProcessingSystem.deleteFile(\'' + i + '\', \'' + data.file_arr[i] + '\', \'attached\')">' +
                                '<i class="bi bi-trash"></i>' +
                            '</button>' +
                        '</div>' +
                    '</div>';
            }
        } else {
            html = '<div class="text-center text-muted py-3">첨부된 파일이 없습니다.</div>';
        }
        $(container).html(html);
    },

    // Render enhanced image list
    renderImageList: function(data, container) {
        var html = '';
        if (data.recid > 0) {
            for (var i = 0; i < data.recid; i++) {
                html +=
                    '<div class="image-item border rounded p-2 mb-2" id="image' + i + '">' +
                        '<div class="text-center">' +
                            '<img src="../uploads/' + data.file_arr[i] + '" class="img-thumbnail mb-2" style="max-width: 200px; max-height: 150px; cursor: pointer;" onclick="fileProcessingSystem.previewImage(\'../uploads/' + data.file_arr[i] + '\', \'' + data.realfile_arr[i] + '\')">' +
                            '<div>' +
                                '<small class="text-muted">' + data.realfile_arr[i] + '</small>' +
                                '<br>' +
                                '<button type="button" class="btn btn-outline-success btn-sm me-1" onclick="fileProcessingSystem.insertImageToEditor(\'../uploads/' + data.file_arr[i] + '\')">' +
                                    '<i class="bi bi-arrow-down"></i> 에디터 삽입' +
                                '</button>' +
                                '<button type="button" class="btn btn-outline-danger btn-sm" onclick="fileProcessingSystem.deleteFile(\'' + i + '\', \'' + data.file_arr[i] + '\', \'image\')">' +
                                    '<i class="bi bi-trash"></i>' +
                                '</button>' +
                            '</div>' +
                        '</div>' +
                    '</div>';
            }
        } else {
            html = '<div class="text-center text-muted py-3">첨부된 이미지가 없습니다.</div>';
        }
        $(container).html(html);
    },

    // Enhanced file deletion
    deleteFile: function(index, filename, itemType) {
        Swal.fire({
            title: '파일 삭제',
            text: '정말 삭제하시겠습니까?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '삭제',
            cancelButtonText: '취소'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../file/del_file.php',
                    type: 'POST',
                    data: {
                        savename: filename,
                        tablename: $("#tablename").val(),
                        item: itemType,
                        parentid: $("#id").val()
                    },
                    success: function(response) {
                        $('#' + itemType + index).fadeOut(300, function() {
                            $(this).remove();
                            fileProcessingSystem.updateFileStatusSummary();
                        });
                        fileProcessingSystem.showSuccess('파일이 삭제되었습니다.');
                        // Refresh file displays
                        fileProcessingSystem.refreshAll();
                    },
                    error: function() {
                        fileProcessingSystem.showError('파일 삭제에 실패했습니다.');
                    }
                });
            }
        });
    },

    // Add refresh functionality
    refreshAll: function() {
        this.displayFiles();
        this.displayImages();
    },


    // Insert image to Summernote editor
    insertImageToEditor: function(imagePath) {
        if ($('#content').hasClass('summernote-initialized')) {
            $('#content').summernote('insertImage', imagePath);
            this.showSuccess('이미지가 에디터에 삽입되었습니다.');
        } else {
            this.showError('에디터가 초기화되지 않았습니다.');
        }
    },

    // Preview image in modal
    previewImage: function(imagePath, imageName) {
        Swal.fire({
            title: imageName,
            imageUrl: imagePath,
            imageAlt: imageName,
            showCloseButton: true,
            showConfirmButton: false,
            width: 'auto',
            customClass: {
                image: 'img-fluid'
            }
        });
    },

    // File monitoring system
    startFileMonitoring: function() {
        setInterval(() => {
            var pInputValue = $("#pInput").val();
            if (pInputValue === '100') {
                this.refreshFileDisplays();
                $("#pInput").val(''); // Reset flag
            } else if (pInputValue === '50') {
                this.loadExistingFiles();
                $("#pInput").val(''); // Reset flag
            }
        }, 500);
    },

    // Update file status summary
    updateFileStatusSummary: function() {
        var fileCount = $('#displayFile .file-item').length;
        var imageCount = $('#displayImage .image-item').length;
        var totalCount = fileCount + imageCount;

        var summaryText = '';
        if (totalCount > 0) {
            summaryText = '📎 ' + fileCount + '개 파일, 🖼️ ' + imageCount + '개 이미지';
        }
        $('#fileStatusSummary').text(summaryText);
    },

    // Extract search text from Summernote content
    extractSearchText: function(htmlContent) {
        var tempDiv = document.createElement('div');
        tempDiv.innerHTML = htmlContent;
        var textContent = tempDiv.textContent || tempDiv.innerText || '';
        var searchText = textContent.replace(/\s+/g, ' ').trim().substring(0, 1000);

        // Store for search functionality
        if (typeof window.searchTextCache === 'undefined') {
            window.searchTextCache = '';
        }
        window.searchTextCache = searchText;
    },

    // Utility functions
    formatFileSize: function(bytes) {
        if (!bytes) return '0 B';
        var k = 1024;
        var sizes = ['B', 'KB', 'MB', 'GB'];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },

    showProgress: function(type) {
        var progressId = type === 'image' ? '#imageUploadProgress' : '#fileUploadProgress';
        $(progressId).show();
        $(progressId + ' .progress-bar').css('width', '0%').addClass('progress-bar-animated');
    },

    hideProgress: function(type) {
        var progressId = type === 'image' ? '#imageUploadProgress' : '#fileUploadProgress';
        $(progressId + ' .progress-bar').css('width', '100%').removeClass('progress-bar-animated');
        setTimeout(() => {
            $(progressId).hide();
            $(progressId + ' .progress-bar').css('width', '0%');
        }, 500);
    },

    showSuccess: function(message) {
        Toastify({
            text: message,
            duration: 3000,
            close: true,
            gravity: "top",
            position: "center",
            style: {
                background: "linear-gradient(to right, #00b09b, #96c93d)"
            }
        }).showToast();
    },

    showError: function(message) {
        Toastify({
            text: message,
            duration: 5000,
            close: true,
            gravity: "top",
            position: "center",
            style: {
                background: "linear-gradient(to right, #ff5f6d, #ffc371)"
            }
        }).showToast();
    },

    // Save all pending files
    saveAllPendingFiles: function() {
        this.showSuccess('모든 파일이 저장되었습니다.');
        $('#fileUploadModal').modal('hide');
    },

    // Load files for download section
    /*loadDownloadFiles: function() {
        var params = $("#id").val();
        if(!params || params == '0') {
            $('#downloadFileList').html('<div class="col-12"><div class="text-center text-muted py-3">첨부된 파일이 없습니다.</div></div>');
            $('#downloadImageList').html('<div class="col-12"><div class="text-center text-muted py-3">첨부된 이미지가 없습니다.</div></div>');
            return;
        }

        var tablename = $("#tablename").val();

        // Load attached files
        $.ajax({
            url: '../file/load_file.php?id=' + params + '&tablename=' + tablename + '&item=attached',
            type: 'get',
            dataType: 'json',
            success: function(data) {
                fileProcessingSystem.renderDownloadFileList(data);
            },
            error: function() {
                $('#downloadFileList').html('<div class="col-12"><div class="text-center text-muted py-3">파일을 불러올 수 없습니다.</div></div>');
            }
        });

        // Load images
        $.ajax({
            url: '../file/load_file.php?id=' + params + '&tablename=' + tablename + '&item=image',
            type: 'get',
            dataType: 'json',
            success: function(data) {
                fileProcessingSystem.renderDownloadImageList(data);
            },
            error: function() {
                $('#downloadImageList').html('<div class="col-12"><div class="text-center text-muted py-3">이미지를 불러올 수 없습니다.</div></div>');
            }
        });
    },

    // Render download file list
    renderDownloadFileList: function(data) {
        var html = '';
        if (data.recid > 0) {
            $('#downloadFileCount').text(data.recid);
            for (var i = 0; i < data.recid; i++) {
                var fileSize = this.formatFileSize(data.filesizes ? data.filesizes[i] : 0);
                var fileExt = data.realfile_arr[i].split('.').pop().toLowerCase();
                var iconClass = this.getFileIcon(fileExt);

                html +=
                    '<div class="col-md-6 col-lg-4 mb-3">' +
                        '<div class="card h-100 download-file-card">' +
                            '<div class="card-body d-flex align-items-center">' +
                                '<div class="file-icon me-3">' +
                                    '<i class="bi ' + iconClass + ' text-primary" style="font-size: 2rem;"></i>' +
                                '</div>' +
                                '<div class="file-info flex-grow-1">' +
                                    '<h6 class="card-title mb-1 text-truncate" title="' + data.realfile_arr[i] + '">' +
                                        data.realfile_arr[i] +
                                    '</h6>' +
                                    '<small class="text-muted">' + fileSize + '</small>' +
                                '</div>' +
                                '<div class="file-actions">' +
                                    '<a href="../uploads/' + data.file_arr[i] + '" download="' + data.realfile_arr[i] + '"' +
                                       ' class="btn btn-outline-primary btn-sm" title="다운로드">' +
                                        '<i class="bi bi-download"></i>' +
                                    '</a>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>';
            }
        } else {
            $('#downloadFileCount').text('0');
            html = '<div class="col-12"><div class="text-center text-muted py-3">첨부된 파일이 없습니다.</div></div>';
        }
        $('#downloadFileList').html(html);
    },

    // Render download image list
    renderDownloadImageList: function(data) {
        var html = '';
        if (data.recid > 0) {
            $('#downloadImageCount').text(data.recid);
            for (var i = 0; i < data.recid; i++) {
                var fileSize = this.formatFileSize(data.filesizes ? data.filesizes[i] : 0);

                html +=
                    '<div class="col-md-6 col-lg-4 col-xl-3 mb-3">' +
                        '<div class="card h-100 download-image-card">' +
                            '<div class="card-img-top-wrapper" style="height: 200px; overflow: hidden;">' +
                                '<img src="../uploads/' + data.file_arr[i] + '"' +
                                     ' class="card-img-top"' +
                                     ' style="width: 100%; height: 100%; object-fit: cover; cursor: pointer;"' +
                                     ' onclick="fileProcessingSystem.previewImage(\'../uploads/' + data.file_arr[i] + '\', \'' + data.realfile_arr[i] + '\')"' +
                                     ' alt="' + data.realfile_arr[i] + '">' +
                            '</div>' +
                            '<div class="card-body">' +
                                '<h6 class="card-title text-truncate" title="' + data.realfile_arr[i] + '">' +
                                    data.realfile_arr[i] +
                                '</h6>' +
                                '<p class="card-text">' +
                                    '<small class="text-muted">' + fileSize + '</small>' +
                                '</p>' +
                                '<div class="d-flex justify-content-between">' +
                                    '<button type="button" class="btn btn-outline-success btn-sm"' +
                                            ' onclick="fileProcessingSystem.previewImage(\'../uploads/' + data.file_arr[i] + '\', \'' + data.realfile_arr[i] + '\')"' +
                                            ' title="미리보기">' +
                                        '<i class="bi bi-eye"></i>' +
                                    '</button>' +
                                    '<a href="../uploads/' + data.file_arr[i] + '" download="' + data.realfile_arr[i] + '"' +
                                       ' class="btn btn-outline-primary btn-sm" title="다운로드">' +
                                        '<i class="bi bi-download"></i>' +
                                    '</a>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>';
            }
        } else {
            $('#downloadImageCount').text('0');
            html = '<div class="col-12"><div class="text-center text-muted py-3">첨부된 이미지가 없습니다.</div></div>';
        }
        $('#downloadImageList').html(html);
    },

    // Get file icon based on extension
    getFileIcon: function(ext) {
        const iconMap = {
            'pdf': 'bi-file-earmark-pdf',
            'doc': 'bi-file-earmark-word',
            'docx': 'bi-file-earmark-word',
            'xls': 'bi-file-earmark-excel',
            'xlsx': 'bi-file-earmark-excel',
            'ppt': 'bi-file-earmark-ppt',
            'pptx': 'bi-file-earmark-ppt',
            'txt': 'bi-file-earmark-text',
            'zip': 'bi-file-earmark-zip',
            'rar': 'bi-file-earmark-zip',
            '7z': 'bi-file-earmark-zip'
        };
        return iconMap[ext] || 'bi-file-earmark';
    }*/
};

// Initialize enhanced file processing system
$(document).ready(function() {
    fileProcessingSystem.init();

    // Load existing files if in edit mode
    var mode = '<?php echo $mode; ?>';
    if (mode === 'modify' || mode === 'view') {
        fileProcessingSystem.loadExistingFiles();
        // Load download sections
        /*setTimeout(function() {
            fileProcessingSystem.loadDownloadFiles();
        }, 500);*/
    }
});

// 화면에서 저장한 첨부된 파일 불러오기
function displayFile() {
    $('#displayFile').show();
    const params = $("#timekey").val() ? $("#timekey").val() : $("#num").val();

    if (!params) {
        console.error("ID 값이 없습니다. 파일을 불러올 수 없습니다.");
        alert("ID 값이 유효하지 않습니다. 다시 시도해주세요.");
        return;
    }

    console.log("요청 ID:", params); // 요청 전 ID 확인

    $.ajax({
        url: '/filedrive/fileprocess.php',
        type: 'GET',
        data: {
            num: params,
			tablename: $("#tablename").val(),
            item: 'attached',
            folderPath: '미래기업/uploads',
        },
        dataType: 'json',
    }).done(function (data) {
        console.log("파일 데이터:", data);

        $("#displayFile").html(''); // 기존 내용 초기화

        if (Array.isArray(data) && data.length > 0) {
            data.forEach(function (fileData, index) {
                const realName = fileData.realname || '다운로드 파일';
                const link = fileData.link || '#';
                const fileId = fileData.fileId || null;

                if (!fileId) {
                    console.error("fileId가 누락되었습니다. index: " + index, fileData);
                    $("#displayFile").append(
                        "<div class='text-danger'>파일 ID가 누락되었습니다.</div>"
                    );
                    return;
                }

				$("#displayFile").append(
					"<div class='row mt-1 mb-2'>" +
						"<div class='d-flex align-items-center justify-content-center'>" +
							"<span id='file" + index + "'>" +
								"<a href='#' onclick=\"popupCenter('" + link + "', 'filePopup', 800, 600); return false;\">" + realName + "</a>" +
							"</span> &nbsp;&nbsp;" +
							"<button type='button' class='btn btn-danger btn-sm' id='delFile" + index + "' onclick=\"delFileFn('" + index + "', '" + fileId + "')\">" +
								"<i class='bi bi-trash'></i>" +
							"</button>" +
						"</div>" +
					"</div>"
				);


            });
        } else {
            $("#displayFile").append(
                "<div class='text-center text-muted'>No files</div>"
            );
        }
    }).fail(function (error) {
        console.error("파일 불러오기 오류:", error);
        Swal.fire({
            title: "파일 불러오기 실패",
            text: "파일을 불러오는 중 문제가 발생했습니다.",
            icon: "error",
            confirmButtonText: "확인",
        });
    });
}

// 기존 파일 불러오기
function displayFileLoad() {
    $('#displayFile').show();
    var data = <?php echo json_encode($savefilename_arr); ?>;

    $("#displayFile").html(''); // 기존 내용 초기화

    if (Array.isArray(data) && data.length > 0) {
        data.forEach(function (fileData, i) {
            const realName = fileData.realname || '다운로드 파일';
            const link = fileData.link || '#';
            const fileId = fileData.fileId || null;

            if (!fileId) {
                console.error("fileId가 누락되었습니다. index: " + i, fileData);
                return;
            }

			$("#displayFile").append(
				"<div class='row mb-3'>" +
					"<div class='d-flex mb-3 align-items-center justify-content-center'>" +
						"<span id='file" + i + "'>" +
							"<a href='#' onclick=\"popupCenter('" + link + "', 'filePopup', 800, 600); return false;\">" + realName + "</a>" +
						"</span> &nbsp;&nbsp;" +
						"<button type='button' class='btn btn-danger btn-sm' id='delFile" + i + "' onclick=\"delFileFn('" + i + "', '" + fileId + "')\">" +
							"<i class='bi bi-trash'></i>" +
						"</button>" +
					"</div>" +
				"</div>"
			);

        });
    } else {
        $("#displayFile").append(
            "<div class='text-center text-muted'>No files</div>"
        );
    }
}

// 파일 삭제 처리 함수
function delFileFn(divID, fileId) {
    Swal.fire({
        title: "파일 삭제 확인",
        text: "정말 삭제하시겠습니까?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "삭제",
        cancelButtonText: "취소",
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/filedrive/fileprocess.php',
                type: 'DELETE',
                data: JSON.stringify({
                    fileId: fileId,
                    tablename: $("#tablename").val(),
                    item: "attached",
                    folderPath: "미래기업/uploads",
                    DBtable: "picuploads",
                }),
                contentType: "application/json",
                dataType: 'json',
            }).done(function (response) {
                if (response.status === 'success') {
                    console.log("삭제 완료:", response);
                    $("#file" + divID).remove();
                    $("#delFile" + divID).remove();

                    Swal.fire({
                        title: "삭제 완료",
                        text: "파일이 성공적으로 삭제되었습니다.",
                        icon: "success",
                        confirmButtonText: "확인",
                    });
                } else {
                    console.log(response.message);
                }
            }).fail(function (error) {
                console.error("삭제 중 오류:", error);
                Swal.fire({
                    title: "삭제 실패",
                    text: "파일 삭제 중 문제가 발생했습니다.",
                    icon: "error",
                    confirmButtonText: "확인",
                });
            });
        }
    });
}

</script>

<script>
document.addEventListener("DOMContentLoaded", function() {
  const textareas = document.querySelectorAll("textarea.auto-expand");

  function adjustHeight(el) {
    el.style.height = "auto";
    el.style.height = el.scrollHeight + "px";
  }

  textareas.forEach(textarea => {
    textarea.addEventListener("input", function() {
      adjustHeight(this);
    });
    adjustHeight(textarea);
  });
});

$(document).ready(function () {
	// 모드가 'view'인 경우 disable 처리 (기존 코드 유지)
	var mode = '<?php echo $mode; ?>';
	if (mode === 'view') {
		disableView(); 
	}

	function disableView() {
			$('input, textarea ').prop('readonly', true); // Disable all input, textarea, and select elements
			$('input[type=hidden]').prop('readonly', false); 

			// checkbox와 radio는 클릭 불가능하게 하고 시각적 강조
			$('input[type="checkbox"], input[type="radio"]').each(function() {
				$(this).addClass('readonly-checkbox readonly-radio');
			});

			// 파일 입력 비활성화 
			$('input[type=file]').prop('disabled', true); 
			$('.viewNoBtn').prop('disabled', true);  //버튼 비활성화
			$('.searchplace').prop('disabled', true);  // 수신자 버튼 비활성화
			$('.searchsecondord').prop('disabled', true);  // 수신자 버튼 비활성화
			
			// 레이블 텍스트 크게 설정
			$('label').css('font-size', '1em');
			$('.viewNoSpan').css('display', 'none');
			
			// select 속성 readonly 효과 내기
			$('select[data-readonly="true"]').on('mousedown', function(event) {
				event.preventDefault();
			});

			// checkbox 속성 readonly 효과 내기
			$('input[type="checkbox"][data-readonly="true"]').on('click', function(event) {
				event.preventDefault();
			});

	}
});


function formatInput(input) {
    let value = input.value;
    value = value.replace(/,/g, ""); // Remove all existing commas
    value = value.replace(/[^\d]/g, ""); // Remove all non-digit characters
    input.value = numberWithCommas(value); // Add commas and update the value
}

function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}


</script>

<!-- 부트스트랩 툴팁 -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });  
  	// $("#order_form_write").modal("show");	  
});
</script>

</body>
</html>