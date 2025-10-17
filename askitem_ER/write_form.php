<?php
include $_SERVER['DOCUMENT_ROOT'] . '/session.php';   

$titlemsg = '지출결의서';    

// 법인카드 목록 가져오기
$jsonFile = $_SERVER['DOCUMENT_ROOT'] . '/account/cardlist.json';
$cards = [];
if (file_exists($jsonFile)) {
    $jsonContent = file_get_contents($jsonFile);
    $cards = json_decode($jsonContent, true);
    if (!is_array($cards)) {
        $cards = [];
    }
}

// 카테고리 목록 가져오기
$categoryFile = $_SERVER['DOCUMENT_ROOT'] . '/askitem_ER/category.json';
$categories = [];
if (file_exists($categoryFile)) {
    $categoryContent = file_get_contents($categoryFile);
    $categories = json_decode($categoryContent, true);
    if (!is_array($categories)) {
        $categories = ['식비', '운반비', '자재비', '차량유지비', '기타'];
    }
} else {
    // 기본 카테고리 설정
    $categories = ['식비', '운반비', '자재비', '차량유지비', '기타'];
    $categoryContent = json_encode($categories, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($categoryFile, $categoryContent);
}
?>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/common.php' ?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php'; ?>  
<title> <?=$titlemsg?> </title>
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
  
  /* 파일 리스트, 이미지 리스트 공통 컨테이너 */
  .load-container {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin: 0 -4px;
  }

  .load-item {
    position: relative;
    flex: 0 0 auto;
    width: 120px;     /* 필요에 따라 조정 */
    height: 120px;    /* 필요에 따라 조정 */
    padding: 4px;
    box-sizing: border-box;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: #fafafa;
  }

  /* 이미지 비율에 따라 클래스 부여 */
  .load-item.portrait img {
    width: auto;
    height: 100%;
  }
  .load-item.landscape img {
    width: 100%;
    height: auto;
  }

  /* 우측 상단 삭제/회전 버튼 */
  .load-item .btn-remove,
  .load-item .btn-rotate {
    position: absolute;
    top: 2px;
    right: 2px;
    padding: 2px 4px;
    font-size: 0.75rem;
    line-height: 1;
  }

  /* 이미지 회전 버튼 스타일 */
  .btn-rotate {
    background-color: #007bff !important;
    border-color: #007bff !important;
    color: white !important;
    z-index: 10;
  }

  .btn-rotate:hover {
    background-color: #0056b3 !important;
    border-color: #0056b3 !important;
  }

  /* 이미지 클릭 가능한 스타일 */
  .image-container img {
    cursor: pointer;
    transition: transform 0.2s ease;
  }

  .image-container img:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
  }

  /* 이미지 컨테이너 스타일 */
  .image-container {
    display: inline-block;
    margin: 5px;
    position: relative;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 4px;
    background: #fafafa;
  }

  /* 이미지 Dialog 스타일 */
  .image-dialog {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 90vw;
    height: 90vh;
    max-width: 1200px;
    max-height: 800px;
    border: none;
    border-radius: 12px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    background: white;
    padding: 0;
    margin: 0;
    z-index: 1050;
  }

  .image-dialog::backdrop {
    background: rgba(0,0,0,0.7);
    backdrop-filter: blur(4px);
  }

  .dialog-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid #dee2e6;
    background: #f8f9fa;
    border-radius: 12px 12px 0 0;
  }

  .dialog-header h5 {
    margin: 0;
    font-weight: 600;
    color: #495057;
  }

  .dialog-controls {
    display: flex;
    gap: 8px;
  }

  .dialog-controls .btn {
    padding: 6px 10px;
    border-radius: 6px;
    transition: all 0.2s ease;
  }

  .dialog-controls .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
  }

  .dialog-body {
    padding: 20px;
    height: calc(100% - 80px);
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .image-container-zoom {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: auto;
    position: relative;
  }

  .image-container-zoom img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    transition: transform 0.3s ease;
    cursor: grab;
  }

  .image-container-zoom img:active {
    cursor: grabbing;
  }

  /* 확대/축소 애니메이션 */
  .image-container-zoom img.zoomed {
    transition: transform 0.2s ease;
  }

  /* 반응형 디자인 */
  @media (max-width: 768px) {
    .image-dialog {
      width: 95vw;
      height: 95vh;
    }
    
    .dialog-header {
      padding: 12px 16px;
    }
    
    .dialog-body {
      padding: 16px;
    }
    
    .dialog-controls .btn {
      padding: 4px 8px;
      font-size: 0.875rem;
    }
  }
</style>	
<body>
<?php include $_SERVER['DOCUMENT_ROOT'] . "/common/modal.php"; ?>   
<?php   

$tablename = 'eworks';  
  
$mode=  $_REQUEST["mode"] ?? '' ;
$num=  $_REQUEST["num"] ?? '' ;
$author=  $user_name ?? '' ;
$companyCard = ''; // 신규 작성 시 초기화 (modify/view 시에는 _row.php에서 재정의됨)

// ---------------------------------------------
// timekey: 임시 저장용 key 생성
// ---------------------------------------------
if (empty($_REQUEST['num'])) {
  // 32자리 랜덤 문자열 생성 (PHP 7 이상)
  $timekey = bin2hex(random_bytes(16));
} else {
  // 이미 생성된 key가 넘어오면 재사용
  $timekey = $_REQUEST['num'];
}

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

		$titlemsg = $mode === 'modify' ? '지출결의서(수정)' : '지출결의서(조회)'; 
    $parentid = $num;    
		
      }
     }catch (PDOException $Exception) {
       print "오류: ".$Exception->getMessage();
     } 
  }
  else{
    // 신규 작성일경우 초기화
    include $_SERVER['DOCUMENT_ROOT'] .'/eworks/_request.php';
    $titlemsg = '지출결의서 작성';
    $mytitle = $outworkplace ?? '';
		$content = $al_content ?? '';
		$content_reason = $request_comment ?? '';	
		$al_company = $mycompany ?? ''; // session의 회사명 가져오기
  }  
  
      
  if ($mode!="modify" and $mode!="view" and $mode!="copy"){    // 수정모드가 아닐때 신규 자료일때는 변수 초기화 한다.
          
        $indate=date("Y-m-d");
        $paymentdate=date("Y-m-d");
        $requestpaymentdate=date("Y-m-d");
			  $author = $user_name;
			  $titlemsg	= '지출결의서 작성';
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
		$paymentdate=date("Y-m-d");
		$requestpaymentdate=date("Y-m-d");
      }
     }catch (PDOException $Exception) {
         print "오류: ".$Exception->getMessage();
     }
	 
    $titlemsg	= '(데이터 복사) 지출결의서';	
    $num='';	 
    $id = $num;  
    $parentid = $num;    
    $author = $user_name;
    $update_log='';
  }  
      
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

// 초기 프로그램은 $num사용 이후 $id로 수정중임  
if(isset($num)){
    $id=$num;    
  }else{
    $id='';
  }
    
// 첨부파일 있는 것 불러오기 
$savefilename_arr=array(); 
$realname_arr=array(); 
$item = 'attached';
if(isset($id)){
$sql=" select * from ".$DB.".fileuploads where tablename ='$tablename' and item ='$item' and parentid ='$id' ";	

 try{  
   $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh   
   while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
			array_push($realname_arr, $row["realname"]);			
			array_push($savefilename_arr, $row["savename"]);			
        }		 
   } catch (PDOException $Exception) {
    print "오류: ".$Exception->getMessage();
  }   
}

// 첨부 이미지 있는 것 불러오기 
$realimagename_arr=array(); 
$saveimagename_arr=array(); 
$rotation_arr=array(); // 회전 정보 배열 추가
$fileid_arr=array(); // 파일 ID 배열 추가
$item = 'image';
if(isset($id)){
$sql=" select * from ".$DB.".fileuploads where tablename ='$tablename' and item ='$item' and parentid ='$id' ";	

 try{  
   $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh   
   while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
			array_push($realimagename_arr, $row["realname"]);							   
			array_push($saveimagename_arr, $row["savename"]);		
			array_push($rotation_arr, $row["rotate"] ?? 0); // 회전 정보 추가
			array_push($fileid_arr, $row["id"]); // 파일 ID 추가
        }		 
   } catch (PDOException $Exception) {
    print "오류: ".$Exception->getMessage();
  }   
}

?>

<form id="board_form" name="board_form" method="post"  onkeydown="return captureReturnKey(event)"  >	
  
<!-- 전달함수 설정 input hidden -->
<input type="hidden" id="id" name="id" value="<?php echo isset($id) ? $id : ''; ?>">
<input type="hidden" id="num" name="num" value="<?php echo isset($num) ? $num : ''; ?>">
<input type="hidden" id="parentid" name="parentid" value="<?php echo isset($parentid) ? $parentid : ''; ?>">
<input type="hidden" id="fileorimage" name="fileorimage" value="<?php echo isset($fileorimage) ? $fileorimage : ''; ?>">
<input type="hidden" id="item" name="item" value="<?php echo isset($item) ? $item : ''; ?>">
<input type="hidden" id="upfilename" name="upfilename" value="<?php echo isset($upfilename) ? $upfilename : ''; ?>">
<input type="hidden" id="tablename" name="tablename" value="<?php echo isset($tablename) ? $tablename : ''; ?>">
<input type="hidden" id="savetitle" name="savetitle" value="<?php echo isset($savetitle) ? $savetitle : ''; ?>">
<input type="hidden" id="pInput" name="pInput" value="<?php echo isset($pInput) ? $pInput : ''; ?>">
<input type="hidden" id="mode" name="mode" value="<?php echo isset($mode) ? $mode : ''; ?>">
<input type="hidden" id="timekey" name="timekey" value="<?php echo isset($timekey) ? $timekey : ''; ?>"> <!-- 신규데이터 작성시 parentid key값으로 사용 -->
<input type="hidden" id="searchtext" name="searchtext" value="<?php echo isset($searchtext) ? $searchtext : ''; ?>"> <!-- summernote text저장 -->
<input type="hidden" id="update_log" name="update_log" value="<?php echo isset($update_log) ? $update_log : ''; ?>">
<input type="hidden" id="first_writer" name="first_writer" value="<?php echo isset($first_writer) ? $first_writer : ''; ?>">
<input type="hidden" id="e_confirm" name="e_confirm" value="<?php echo isset($e_confirm) ? $e_confirm : ''; ?>">
<input type="hidden" id="e_confirm_id" name="e_confirm_id" value="<?php echo isset($e_confirm_id) ? $e_confirm_id : ''; ?>">
<input type="hidden" id="e_line_id" name="e_line_id" value="<?php echo isset($e_line_id) ? $e_line_id : ''; ?>">
<input type="hidden" id="status" name="status" value="<?php echo isset($status) ? $status : ''; ?>">
<input type="hidden" id="al_company" name="al_company" value="<?php echo isset($al_company) ? $al_company : ''; ?>">
<input type="hidden" id="itemcheck" name="itemcheck" value="<?php echo isset($itemcheck) ? $itemcheck : ''; ?>">
<input type="hidden" id="done" name="done" value="<?php echo isset($done) ? $done : ''; ?>">

<!-- 디버그 정보 표시 영역 -->
<div class="row mt-3" id="debugSection" style="display: none;"> 
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">디버그 정보</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>ID:</strong> <?php echo isset($id) ? htmlspecialchars($id) : 'N/A'; ?></p>
                        <p><strong>NUM:</strong> <?php echo isset($num) ? htmlspecialchars($num) : 'N/A'; ?></p>
                        <p><strong>Parent ID:</strong> <?php echo isset($parentid) ? htmlspecialchars($parentid) : 'N/A'; ?></p>
                        <p><strong>Mode:</strong> <?php echo isset($mode) ? htmlspecialchars($mode) : 'N/A'; ?></p>
                        <p><strong>Time Key:</strong> <?php echo isset($timekey) ? htmlspecialchars($timekey) : 'N/A'; ?></p>
                        <p><strong>Status:</strong> <?php echo isset($status) ? htmlspecialchars($status) : 'N/A'; ?></p>
                        <p><strong>Table Name:</strong> <?php echo isset($tablename) ? htmlspecialchars($tablename) : 'N/A'; ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Author:</strong> <?php echo isset($author) ? htmlspecialchars($author) : 'N/A'; ?></p>
                        <p><strong>Company:</strong> <?php echo isset($al_company) ? htmlspecialchars($al_company) : 'N/A'; ?></p>
                        <p><strong>E Confirm:</strong> <?php echo isset($e_confirm) ? htmlspecialchars($e_confirm) : 'N/A'; ?></p>
                        <p><strong>E Confirm ID:</strong> <?php echo isset($e_confirm_id) ? htmlspecialchars($e_confirm_id) : 'N/A'; ?></p>
                        <p><strong>E Line ID:</strong> <?php echo isset($e_line_id) ? htmlspecialchars($e_line_id) : 'N/A'; ?></p>
                        <p><strong>First Writer:</strong> <?php echo isset($first_writer) ? htmlspecialchars($first_writer) : 'N/A'; ?></p>
                        <p><strong>Update Log:</strong> <?php echo isset($update_log) ? htmlspecialchars($update_log) : 'N/A'; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

	
<div class="container-fluid" >
<div class="card">
<div class="card-body">			   

<div class="row">
	<div class="col-sm-7">
		<div class="d-flex mb-5 mt-5 justify-content-center align-items-center ">
			<h4> <?=$titlemsg?> 
    <span data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-html="true" 
      title="
    <b>제목:</b> 지출결의할 제목을 작성합니다.
    ">
      <i class="bi bi-info-circle-fill"></i>  
    </span>
					</h4> 
				</div>
			</div>
			
	   <div class="col-sm-5">		
	<?php			
	$approver_ids = explode('!', $e_confirm_id);
    $approver_details = explode('!', $e_confirm);

    $approvals = array();

    foreach($approver_ids as $index => $id) {
        if (isset($approver_details[$index])) {
            // Use regex to match the pattern (name title date time)
            // The pattern looks for any character until it hits a series of digits that resemble a date followed by a time
            preg_match("/^(.+ \d{4}-\d{2}-\d{2}) (\d{2}:\d{2}:\d{2})$/", $approver_details[$index], $matches);

            // Ensure that the full pattern and the two capturing groups are present
            if (count($matches) === 3) {
                $nameWithTitle = $matches[1]; // This is the name and title
                $time = $matches[2]; // This is the time
                $date = substr($nameWithTitle, -10); // Extract date from the end of the 'nameWithTitle' string
                $nameWithTitle = trim(str_replace($date, '', $nameWithTitle)); // Remove the date from the 'nameWithTitle' to get just the name and title
                $formattedDate = date("m/d H:i:s", strtotime("$date $time")); // Combining date and time

                $approvals[] = array("name" => $nameWithTitle, "date" => $formattedDate);
            }
        }
    }

// // Now $approvals contains the necessary details
// foreach ($approvals as $approval) {
  // echo "Approver: " . $approval['name'] . ", Date: " . $approval['date'] . "<br>";
// }
// }					

// 금액이 10만원 미만이고 신규작성이 아닐 경우 결재생략 표시
if(isset($suppliercost)){
  $suppliercost_numeric = str_replace(',', '', $suppliercost);
}else{
  $suppliercost_numeric = 0;
}
$show_skip_approval = ($mode != '' && $mode != 'copy' && $suppliercost_numeric < 100000);

if($status === 'end' and ($e_confirm !=='' && $e_confirm !== null) )
  {
?>				

    <div class="container mb-2">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th colspan="<?php echo count($approvals); ?>" class="text-center fs-6">결재</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <?php foreach ($approvals as $approval) { ?>
                        <td class="text-center fs-6" style="height: 60px;"><?php echo $approval["name"]; ?></td>
                    <?php } ?>
                </tr>
                <tr>
                    <?php foreach ($approvals as $approval) { ?>
                        <td class="text-center"><?php echo $approval["date"]; ?></td>
                    <?php } ?>
                </tr>
            </tbody>
        </table>
    </div>			  
  
  <?  } 
         else
         {
   ?>
            <div class="container mb-2">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th colspan="<?php echo $show_skip_approval ? 1 : count($approvals); ?>" class="text-center fs-6">
                        <?php echo $show_skip_approval ? '결재생략' : '결재 진행 전'; ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <?php if ($show_skip_approval) { ?>
                        <td class="text-center fs-6" style="height: 60px;">결재생략</td>
                    <?php } else { ?>
                        <?php foreach ($approvals as $approval) { ?>
                            <td class="text-center fs-6" style="height: 60px;"></td>
                        <?php } ?>
                    <?php } ?>
                </tr>
            </tbody>
        </table>
    </div>	
<?  }   ?>

        
</div> 			
</div> 
 		
<?php if($mode!='view') { ?>			
	<div class="row">
		<div class="col-sm-6">		   
			<div class="d-flex mb-1 justify-content-start  align-items-center"> 		   
				<button id="saveBtn" type="button" class="btn btn-dark  btn-sm me-2"  > <i class="bi bi-floppy"></i> 저장(결재상신)  </button> 
				<button id="debugToggleBtn" type="button" class="btn btn-info btn-sm me-2" onclick="toggleDebug()"> <i class="bi bi-bug"></i> 디버그 </button>
			</div> 			
		</div> 	
		<div class="col-sm-6">	
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
					<button type="button"   class="btn btn-dark btn-sm mx-1" onclick="location.href='write_form.php?mode=modify&num=<?=$num?>'" > <i class="bi bi-pencil-square"></i>  수정 </button> &nbsp;
				 <?php if($user_id === $author_id || $admin) { ?>
					<button type="button" class="btn btn-danger btn-sm mx-1"
							onclick="deleteFn()">
					  <i class="bi bi-trash"></i> 삭제
					</button>
				 <?php } ?>
					<button type="button"   class="btn btn-dark btn-sm mx-1" onclick="location.href='write_form.php'" > <i class="bi bi-pencil"></i>  신규 </button>		&nbsp;										
					<button type="button"   class="btn btn-primary btn-sm mx-1" onclick="location.href='write_form.php?mode=copy&num=<?=$num?>'" > <i class="bi bi-copy"></i> 복사 </button>	&nbsp;							
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
<?php } // end of elseif  ?>	

  <div class="row mt-2">  
      <table class="table table-bordered">
		<tr>
		  <td class=" text-center w-25 fw-bold">
			<label for="indate">작성일</label>
		  </td>          			
		 <td >				
			<input type="date" class="form-control w120px viewNoBtn" id="indate" name="indate" value="<?=$indate?>" >				
		  </td>	
		   <td class=" text-center w-25 fw-bold">
			<label for="author">기안자</label>
		  </td>          			
		 <td>				
			<input type="text" class="form-control text-center w80px viewNoBtn" id="author" name="author" value="<?=$author?>" >				
		  </td>					 
		</tr>
		<tr>
		  <td class=" text-center w-25 fw-bold">
			<label for="paymentdate">결재일자</label>
		  </td>          			
		 <td >				
			<input type="date" class="form-control w120px viewNoBtn" id="paymentdate" name="paymentdate" value="<?=$paymentdate?>" >				
		  </td>	
		   <td class=" text-center w-25 fw-bold">
			<label for="requestpaymentdate">지출요청일자</label>
		  </td>          			
		 <td>				
			<input type="date" class="form-control  w120px  text-center viewNoBtn" id="requestpaymentdate" name="requestpaymentdate" value="<?=$requestpaymentdate?>" >				
		  </td>					 
		</tr>		
		<tr>
		  <td colspan="4" class="text-center  bg-secondary-subtle  w-25 fw-bold">
			    지출결의서 내역
		  </td>		  
		</tr>
    </table>
    </div>

    <!-- 지출결의서 내역 테이블 -->
    <div class="row mt-2">
      <div class="col-12">
        <div class="d-flex justify-content-start mb-2">
          <button type="button" class="btn btn-primary btn-sm viewNoBtn me-2" onclick="addRow()">
            <i class="bi bi-plus-lg"></i> 행 추가
          </button>
        </div>
        <table class="table table-bordered" id="expenseTable">
          <thead>
            <tr>
              <th class="text-center" style="width: 10%">No</th>
              <th class="text-center" style="width: 15%">현장명</th>
              <th class="text-center" style="width: 15%">분류</th>
              <th class="text-center" style="width: 25%">적요</th>
              <th class="text-center" style="width: 15%">금액</th>
              <th class="text-center" style="width: 20%">비고</th>
            </tr>
          </thead>
          <tbody id="expenseTableBody">
            <!-- 동적으로 행이 추가될 위치 -->
          </tbody>
        </table>
      </div>
    </div>

    <!-- #region 예상 비용 -->
    <div class="row mt-2">  
      <table class="table table-bordered">
		<tr>
		  <td class="text-center w-15 fw-bold">
			<label for="suppliercost">법인카드</label>
		  </td>
		  <td>
			<div class="d-flex  justify-content-start align-items-center "> 
				<select class="form-select viewNoBtn text-end me-1" id="companyCard" name="companyCard" style="font-size: 0.9em; height: auto;">
					<option value="">법인카드 선택 </option>
					<?php 
					$userCardFound = false;
					foreach ($cards as $card): 
						$cardList = $card['company'] . ' - ' . $card['number'] . ' (' . $card['user'] . ')';
						$isUserCard = strpos($cardList, $user_name) !== false;

            // var_dump($isUserCard);
						
						// 사용자의 카드를 찾았고 아직 선택된 카드가 없는 경우
						// if ($isUserCard && !$userCardFound) {
						// 	$userCardFound = true;
						// }
						
						// 선택 로직: 
						// 1. 기존 선택된 카드가 있으면 그 카드 선택
						// 2. 기존 선택이 없고 현재 카드가 사용자의 카드이면서 처음 발견된 사용자 카드인 경우 선택
						$isSelected = (!empty($companyCard) && $companyCard == $card['number']) || 
									(empty($companyCard) && $isUserCard && !$userCardFound);
            if($isSelected) {
              $userCardFound = true;
            }
					?>
						<option value="<?= htmlspecialchars($card['number'], ENT_QUOTES, 'UTF-8') ?>" 
							<?= $isSelected ? 'selected' : '' ?>>
							<?= htmlspecialchars($cardList, ENT_QUOTES, 'UTF-8') ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		  </td>
		  <td class="text-center w-15 fw-bold">
			<label for="suppliercost">총 비용</label>
		  </td>
		  <td>
			<div class="d-flex  justify-content-start align-items-center "> 
				<input type="text" class="form-control w110px viewNoBtn text-end me-1" id="suppliercost" name="suppliercost"  placeholder="예상 총 비용"  value="<?=$suppliercost?>" style="text-align: right !important;" oninput="formatInput(this)"> 원
			</div>
		  </td>
		</tr>
	  </table>	  	  
	</div>
	</div> 
  
    <div class="d-flex mt-3 mb-1 justify-content-center">  	 			
		    <?php if($mode != 'view') { ?>
		    <label  for="upfileimage" class="btn btn-outline-dark btn-sm ">  사진 첨부 </label>	
				 <input id="upfileimage"  name="upfileimage[]" type="file" onchange="this.value" multiple accept=".gif, .jpg, .png" style="display:none">		
       <?php } ?>
       </div>
		<div class="d-flex  mb-1 justify-content-center"> 		   
					   <div class="d-flex  mb-1 justify-content-center fs-6"> 
							<div id ="displayImage" class="mt-5 mb-5 justify-content-center load-container" style="display:none;">  	 		 					 
							</div>								  
			</div>   
		</div>   
	
	    </div>	  
		</div>	  
		</div>	  
 </div>	  
</form>	

<div class="modal fade" id="loadingImageModal" tabindex="-1" aria-labelledby="loadingImageModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 mb-0">이미지를 불러오고 있습니다. 잠시만 기다려 주세요.</p>
            </div>
        </div>
    </div>
</div>


<script>
var ajaxRequest = null;

$("#pInput").val('50'); // 최초화면 사진파일 보여주기 100은 파일 업로드시 바로 화면에 보여주기

// 페이지 로딩
$(document).ready(function(){	
    var loader = document.getElementById('loadingOverlay');
	if(loader)
		loader.style.display = 'none';		 

  // 저장된 이미지 불러오기
  displayImageLoad();			

});
$(document).ready(function(){
		
	 $("#saveBtn").click(function(){ 
		// 조건 확인
		if($("#mytitle").val() === '' || $("#content").val() === '' || $("#content_reason").val()  === '' ) {
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
		if(Number(num) < 1 && $("#mode").val() !== 'copy') 				
				$("#mode").val('insert');     			  						
		// 폼데이터 전송시 사용함 Get form         
		var form = $('#board_form')[0];  	    	
		var datasource = new FormData(form); 

		// 지출결의서 내역 데이터를 JSON으로 처리하는 부분
		let expenseList = [];
		$('#expenseTableBody tr').each(function() {
			let rowData = {};
			$(this).find('input, select').each(function() {
				let name = $(this).attr('name').replace('[]', '');
				let value = $(this).val();
				// 금액의 경우 콤마 제거
				if (name === 'expense_amount') {
					value = value.replace(/,/g, '');
				}
				rowData[name] = value;
			});
			expenseList.push(rowData);
		});

		// 데이터를 JSON으로 설정
		datasource.set('expense_data', JSON.stringify(expenseList));

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
			success: function(data) {
				if (data.error) {
					Swal.fire({
						title: '오류 발생',
						text: data.message || '데이터 저장 중 오류가 발생했습니다.',
						icon: 'error'
					});
					return;
				}
				
				Swal.fire({
					title: '자료등록 완료',
					text: '데이터가 성공적으로 등록되었습니다.',
					icon: 'success'
				});
				
				setTimeout(function() {									
					if (window.opener && !window.opener.closed) {
						if (typeof window.opener.restorePageNumber === 'function') {
							window.opener.restorePageNumber();
						}								
					}
					setTimeout(function() {		
						hideMsgModal();	
						self.close();
					}, 1000);	
				}, 1000);						
			},
			error: function(jqxhr, status, error) {
				console.error('Error:', jqxhr, status, error);
				let errorMessage = '데이터 저장 중 오류가 발생했습니다.';
				try {
					const response = JSON.parse(jqxhr.responseText);
					if (response.message) {
						errorMessage = response.message;
					}
				} catch (e) {
					console.error('Error parsing response:', e);
				}
				
				Swal.fire({
					title: '오류 발생',
					text: errorMessage,
					icon: 'error'
				});
			} 			      		
		});					
	}	// end of saveData

}); // end of document.ready

// 기존 deleteFn(...) 함수를 아래로 교체
function deleteFn() {
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
      // soft-delete 모드 세팅
      $("#mode").val('delete');

      $.ajax({
        url: 'insert.php',    // ← delete.php 대신 insert.php
        type: 'POST',
        data: $("#board_form").serialize(),
        dataType: 'json',
      }).done(function(data) {
        Toastify({
          text: "자료 삭제 처리 완료",
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
            window.opener.restorePageNumber();
            window.opener.location.reload();
          }
          setTimeout(() => window.close(), 500);
        }, 1000);
      }).fail(function(jqxhr, status, error) {
        console.error("삭제 오류:", status, error);
        Swal.fire('오류', '삭제 처리 중 문제가 발생했습니다.', 'error');
      });
    }
  });
}

	 
function captureReturnKey(e) {
    if(e.keyCode==13 && e.srcElement.type != 'textarea')
    return false;
}

// 디버그 정보 토글 함수
function toggleDebug() {
    const debugSection = document.getElementById('debugSection');
    const debugBtn = document.getElementById('debugToggleBtn');
    
    if (debugSection.style.display === 'none') {
        debugSection.style.display = 'block';
        debugBtn.innerHTML = '<i class="bi bi-bug-fill"></i> 디버그 숨기기';
        debugBtn.classList.remove('btn-info');
        debugBtn.classList.add('btn-warning');
    } else {
        debugSection.style.display = 'none';
        debugBtn.innerHTML = '<i class="bi bi-bug"></i> 디버그';
        debugBtn.classList.remove('btn-warning');
        debugBtn.classList.add('btn-info');
    }
}
</script> 

<script>
$(document).ready(function () {	
  	  	 	 
// 첨부파일 멀티업로드 (대한에서 사용하는 서버 저장용, 구글드라이브 아님)
$("#upfile").change(function(e) {	    
	    $("#id").val('<?php echo $id;?>');
	    $("#parentid").val('<?php echo $id;?>');
	    $("#fileorimage").val('file');
	    $("#item").val('attached');
	    $("#upfilename").val('upfile');	    
	    $("#savetitle").val('지출결의서 첨부파일');		
	
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
					 // $("#pInput").val('100');						

				},
				error : function( jqxhr , status , error ){
					console.log( jqxhr , status , error );
							} 			      		
			   });	

});		 
	 
// 첨부 이미지 멀티업로드	 (대한에서 사용하는 서버 저장용, 구글드라이브 아님)
$("#upfileimage").change(function(e) {	    
	    $("#fileorimage").val('image');
	    $("#item").val('image');
	    $("#upfilename").val('upfileimage');	    
	    $("#savetitle").val('지출결의서 이미지');		
	
		// 임시번호 부여 id-> parentid 시간초까지 나타내는 변수로 저장 후 저장하지 않으면 삭제함	
	 if(Number($("#id").val()) == 0) 
	      $("#id").val($("#timekey").val());   // 임시번호 부여 id-> parentid        	  
	  
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
					console.log('image 업로드 후 data', data);
					// opener.location.reload();
					// window.close();	
					setTimeout(function() {
						$('#myModal').modal('hide');  
						}, 1000);	
					// 사진이 등록되었으면 100 입력됨
            displayImage();            

				},
				error : function( jqxhr , status , error ){
					console.log( jqxhr , status , error );
							} 			      		
			   });	

});		   

delPicimageFn = function(divID, delChoice) {
	console.log(divID, delChoice);

	// SweetAlert2로 삭제 확인 대화상자 표시
	Swal.fire({
		title: '이미지 삭제',
		text: '정말로 이 이미지를 삭제하시겠습니까?',
		icon: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#d33',
		cancelButtonColor: '#3085d6',
		confirmButtonText: '삭제',
		cancelButtonText: '취소'
	}).then((result) => {
		if (result.isConfirmed) {
			// 사용자가 삭제를 확인한 경우에만 AJAX 요청 실행
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
					
					// 삭제 완료 메시지 표시
					Swal.fire(
						'삭제 완료!',
						'이미지가 성공적으로 삭제되었습니다.',
						'success'
					);
					
		        }).fail(function(xhr, status, error) {
					// 삭제 실패 시 오류 메시지 표시
					Swal.fire(
						'삭제 실패!',
						'이미지 삭제 중 오류가 발생했습니다.',
						'error'
					);
					console.error('삭제 오류:', error);
		        });		
		}
	});
}


});

// 첨부된 이미지 불러오기
function displayImage() {       
  console.log('=== displayImage 함수 시작 ===');
  
  $('#displayImage').show();
  params = $("#id").val();
  // param이 숫자가 아니면 timekey 값을 사용
  if(isNaN(params)) {
    params = $("#timekey").val();
  }
  var tablename = $("#tablename").val();    
  var item = 'image';

  console.log('Parameters:', {
    id: params,
    tablename: tablename, 
    item: item
  });

  console.log('AJAX 요청 URL:', '../file/load_file.php?id=' + params + '&tablename=' + tablename + '&item=' + item);
  console.log('Form Data:', $("#board_form").serialize());

  $.ajax({
    url:'../file/load_file.php?id=' + params + '&tablename=' + tablename + '&item=' + item ,
    type:'post',
    data: $("board_form").serialize(),
    dataType: 'json',
    beforeSend: function() {
      console.log('AJAX 요청 시작');
    }
  }).done(function(data){						
    console.log('AJAX 응답 성공');
    console.log('응답 데이터:', data);
    
    const recid = data["recid"];		   
    console.log('이미지 개수:', recid);

    $("#displayImage").html('');

    if(recid > 0) {
      console.log('이미지 목록 렌더링 시작');
      for(i=0;i<recid;i++) {	
        console.log('이미지 ' + i + ' 처리:', data['file_arr'][i]);
        
        // 회전 정보 가져오기
        const rotation = data['rotation_arr'] && data['rotation_arr'][i] ? data['rotation_arr'][i] : 0;
        const fileId = data['id_arr'] && data['id_arr'][i] ? data['id_arr'][i] : i;
        
        // 이미지와 회전/삭제 버튼을 포함한 컨테이너 생성
        let buttonsHtml = '';
        
        // 조회 모드가 아닐 때만 버튼 표시
        if ($("#mode").val() !== 'view') {
          buttonsHtml = 
            "<div class='position-absolute top-0 end-0 mt-1 me-1'>" +
              "<button type='button' class='btn btn-primary btn-sm btn-rotate' onclick=\"rotateImage('" + fileId + "', 'image" + i + "')\" title='회전'>" +
                "<i class='bi bi-arrow-clockwise'></i>" +
              "</button>" +
            "</div>" +
            "<div class='position-absolute bottom-0 end-0 mb-1 me-1'>" +
              "<button type='button' class='btn btn-outline-danger btn-sm' id='delPicimage" + i + "' onclick=delPicimageFn('" + i + "','" + data["file_arr"][i] + "')\" title='삭제'>" +
                "<i class='bi bi-trash3-fill'></i>" +
              "</button>" +
            "</div>";
        }
        
        $("#displayImage").append(
          "<div class='image-container'>" +
            "<img id='image" + i + "' src='../uploads/" + data['file_arr'][i] + "' style='width:120px; height:120px; object-fit:contain; transform: rotate(" + rotation + "deg);' data-rotation='" + rotation + "' onclick='openImageDialog(this.src, this.style.transform)'>" +
            buttonsHtml +
          "</div>"
        );
      }
      console.log('이미지 목록 렌더링 완료');		   
    } else {
      console.log('표시할 이미지가 없음');
    }
  }).fail(function(jqXHR, textStatus, errorThrown) {
    console.error('AJAX 요청 실패');
    console.error('Status:', textStatus);
    console.error('Error:', errorThrown);
    console.error('Response:', jqXHR.responseText);
  });

  console.log('=== displayImage 함수 종료 ===');
}

// 기존 있는 이미지 화면에 보여주기
function displayImageLoad() {    

	$('#displayImage').show();			
	var saveimagename_arr = <?php echo json_encode($saveimagename_arr);?> ;	
	var realimagename_arr = <?php echo json_encode($realimagename_arr);?> ;	
	var rotation_arr = <?php echo json_encode($rotation_arr);?> ; // PHP 변수를 JavaScript 배열로 변환
	var fileid_arr = <?php echo json_encode($fileid_arr);?> ; // 파일 ID 배열

  console.log('saveimagename_arr', saveimagename_arr);
	if(saveimagename_arr.length > 0) {
    for(i=0;i<saveimagename_arr.length;i++) {
      // DB에서 가져온 회전 정보와 파일 ID 사용
      const rotation = rotation_arr[i] !== undefined ? rotation_arr[i] : 0;
      const fileId = fileid_arr[i] !== undefined ? fileid_arr[i] : i; // 실제 파일 ID 사용
      
      // 이미지와 회전/삭제 버튼을 포함한 컨테이너 생성
      let buttonsHtml = '';
      
      // 조회 모드가 아닐 때만 버튼 표시
      if ($("#mode").val() !== 'view') {
        buttonsHtml = 
          "<div class='position-absolute top-0 end-0 mt-1 me-1'>" +
            "<button type='button' class='btn btn-primary btn-sm btn-rotate' onclick=\"rotateImage('" + fileId + "', 'image" + i + "')\" title='회전'>" +
              "<i class='bi bi-arrow-clockwise'></i>" +
            "</button>" +
          "</div>" +
          "<div class='position-absolute bottom-0 end-0 mb-1 me-1'>" +
            "<button type='button' class='btn btn-outline-danger btn-sm' id='delPicimage" + i + "' onclick=delPicimageFn('" + i + "','" +  saveimagename_arr[i] + "')\" title='삭제'>" +
              "<i class='bi bi-trash3-fill'></i>" +
            "</button>" +
          "</div>";
      }
      
      $("#displayImage").append(
        "<div class='image-container'>" +
          "<img id='image" + i + "' src='../uploads/" + saveimagename_arr[i] + "' style='width:120px; height:120px; object-fit:contain; transform: rotate(" + rotation + "deg);' data-rotation='" + rotation + "' onclick='openImageDialog(this.src, this.style.transform)'>" +
          buttonsHtml +
        "</div>"
      );
	  }		   
	}  
}

// 첨부된 파일 불러오기
function displayFile() {       
	$('#displayFile').show();
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
		   $("#displayFile").html('');
		   for(i=0;i<recid;i++) {	
			   $("#displayFile").append("<div id=file" + i + ">  <a href='../uploads/" + data["file_arr"][i] + "' download='" +  data["realfile_arr"][i]+ "'>" +  data["realfile_arr"][i] + "</div> &nbsp;&nbsp;&nbsp;&nbsp;  " );			   
         	   $("#displayFile").append("&nbsp;<button type='button' class='btn btn-outline-danger btn-sm' id='delPic" + i + "' onclick=delPicFn('" + i + "','" + data["file_arr"][i] + "')> <i class='bi bi-trash3-fill'></i>  </button>&nbsp; <br>");					   
		      }		   
    });	
}

// 기존 있는 파일 화면에 보여주기
function displayFileLoad() {    
	$('#displayFile').show();	
	var savefilename_arr = <?php echo json_encode($savefilename_arr);?> ;	
	var realname_arr = <?php echo json_encode($realname_arr);?> ;	
	// if(savefilename_arr.length > 0) {
  //   for(i=0;i<savefilename_arr.length;i++) {
	// 		   $("#displayFile").append("<div id=file" + i + ">  <a href='../uploads/" + savefilename_arr[i] + "' download='" + realname_arr[i] + "'>" +  realname_arr[i] + "</div> &nbsp;&nbsp;&nbsp;&nbsp;  " );			   
  //        	   $("#displayFile").append("&nbsp;<button type='button' class='btn btn-outline-danger btn-sm' id='delPic" + i + "' onclick=delPicFn('" + i + "','" +  savefilename_arr[i] + "')> <i class='bi bi-trash3-fill'></i>  </button>&nbsp; <br>");					   
	//   }	   
	// }	
}

function deleteLastchar(str)
// 마지막 문자 제거하는 함수
{
  return str = str.substr(0, str.length - 1);		
}


// 이미지 회전 함수 추가
function rotateImage(fileId, imageId) {
    const img = document.getElementById(imageId);
    const currentRotation = parseInt(img.style.transform.replace('rotate(', '').replace('deg)', '')) || 0;
    const newRotation = (currentRotation + 90) % 360;
    img.style.transform = `rotate(${newRotation}deg)`;
    
    // 회전 각도를 서버에 저장        
    saveRotationAngle(fileId, newRotation);
}

// 회전 각도를 서버에 저장하는 함수 (dh2024용 - pic_insert.php 방식)
function saveRotationAngle(fileId, angle) {
    // 로딩 표시
    Toastify({
        text: "회전 상태 저장 중...",
        duration: 2000,
        close: true,
        gravity: "top",
        position: "center",
        style: {
            background: "linear-gradient(to right, #4a90e2, #67b26f)"
        }
    }).showToast();

    // AJAX 요청 전 데이터 확인
    const requestData = {
        action: 'saveRotation',        
        fileId: fileId,
        rotation: angle,
        tablename: $("#tablename").val(),
        parentid: $("#parentid").val() || $("#id").val() || $("#timekey").val()
    };

    console.log('전송 데이터:', JSON.stringify(requestData)); // 디버깅용

    $.ajax({
        url: '../file/pic_insert.php',
        type: 'POST',
        data: requestData,
        dataType: 'json',
        success: function(response) {
            console.log('서버 응답:', response); // 디버깅용
            
            if (response && response.status === 'success') {
                Toastify({
                    text: response.message || "회전 상태가 저장되었습니다",
                    duration: 2000,
                    close: true,
                    gravity: "top",
                    position: "center",
                    style: {
                        background: "linear-gradient(to right, #00b09b, #96c93d)"
                    }
                }).showToast();
            } else {
                const errorMessage = response ? response.message : '알 수 없는 오류가 발생했습니다.';
                console.error('회전 각도 저장 실패:', errorMessage);
                Toastify({
                    text: errorMessage,
                    duration: 2000,
                    close: true,
                    gravity: "top",
                    position: "center",
                    style: {
                        background: "linear-gradient(to right, #ff5f6d, #ffc371)"
                    }
                }).showToast();
            }
        },
        error: function(xhr, status, error) {
            console.error('회전 각도 저장 중 오류:', {
                status: status,
                error: error,
                response: xhr.responseText
            });

            let errorMessage = "회전 상태 저장 중 오류가 발생했습니다.";
            try {
                if (xhr.responseText) {
                    const response = JSON.parse(xhr.responseText);
                    if (response && response.message) {
                        errorMessage = response.message;
                    }
                }
            } catch (e) {
                console.error('응답 파싱 오류:', e);
            }
            
            Toastify({
                text: errorMessage,
                duration: 2000,
                close: true,
                gravity: "top",
                position: "center",
                style: {
                    background: "linear-gradient(to right, #ff5f6d, #ffc371)"
                }
            }).showToast();
        }
    });
}

// --- 여기부터 수정 및 추가된 함수 ---
// 로딩 모달을 보여주는 함수 (새로 추가)
function showImageLoadingModal() {
    $('#loadingImageModal').modal('show');
}

// 로딩 모달을 숨기는 함수 (새로 추가)
function hideImageLoadingModal() {
    $('#loadingImageModal').modal('hide');
}



// 이미지 확대 dialog를 여는 함수
function openImageDialog(imageSrc, imageTransform) {
    const dialogImage = document.getElementById('dialogImage');
    dialogImage.src = imageSrc;
    
    // 회전 정보 적용
    if (imageTransform) {
        dialogImage.style.transform = imageTransform;
    } else {
        dialogImage.style.transform = 'rotate(0deg)';
    }
    
    // 확대/축소 초기화
    resetZoom();
    
    // dialog 표시
    const imageDialog = document.getElementById('imageDialog');
    imageDialog.showModal();
}

// dialog 닫기
function closeImageDialog() {
    const imageDialog = document.getElementById('imageDialog');
    imageDialog.close();
}

// 확대
function zoomIn() {
    const dialogImage = document.getElementById('dialogImage');
    const currentTransform = dialogImage.style.transform || '';
    const currentScale = parseFloat(currentTransform.match(/scale\(([^)]+)\)/)?.[1] || 1);
    const newScale = Math.min(currentScale * 1.2, 5); // 최대 5배까지 확대
    
    // 기존 scale 제거하고 새로운 scale 추가
    const newTransform = currentTransform.replace(/scale\([^)]*\)/g, '') + ` scale(${newScale})`;
    dialogImage.style.transform = newTransform.trim();
    dialogImage.classList.add('zoomed');
}

// 축소
function zoomOut() {
    const dialogImage = document.getElementById('dialogImage');
    const currentTransform = dialogImage.style.transform || '';
    const currentScale = parseFloat(currentTransform.match(/scale\(([^)]+)\)/)?.[1] || 1);
    const newScale = Math.max(currentScale / 1.2, 0.1); // 최소 0.1배까지 축소
    
    // 기존 scale 제거하고 새로운 scale 추가
    const newTransform = currentTransform.replace(/scale\([^)]*\)/g, '') + ` scale(${newScale})`;
    dialogImage.style.transform = newTransform.trim();
    dialogImage.classList.add('zoomed');
}

// 원본 크기로 복원
function resetZoom() {
    const dialogImage = document.getElementById('dialogImage');
    const currentTransform = dialogImage.style.transform || '';
    const rotation = currentTransform.match(/rotate\([^)]*\)/);
    const rotationStyle = rotation ? rotation[0] : 'rotate(0deg)';
    
    dialogImage.style.transform = rotationStyle;
    dialogImage.classList.remove('zoomed');
}

// 마우스 휠로 확대/축소
document.addEventListener('DOMContentLoaded', function() {
    const dialogImage = document.getElementById('dialogImage');
    if (dialogImage) {
        dialogImage.addEventListener('wheel', function(e) {
            if (e.deltaY < 0) {
                zoomIn();
            } else {
                zoomOut();
            }
        });
    }
});
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
			$('.fetch_receiverBtn').prop('disabled', true);  // 수신자 버튼 비활성화
			$('.viewNoBtn').prop('disabled', true);  //버튼 비활성화
			$('.searchplace').prop('disabled', true);  // 수신자 버튼 비활성화
			$('.searchsecondord').prop('disabled', true);  // 수신자 버튼 비활성화
			$('.viewmode').prop('disabled', true);  // 버튼 비활성화
			
			// 레이블 텍스트 크게 설정
			$('label').css('font-size', '1.2em');
			
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
    // 숫자와 콤마만 허용
    let value = input.value.replace(/[^\d,]/g, '');
    // 콤마 제거
    value = value.replace(/,/g, '');
    // 숫자가 아닌 문자 제거
    value = value.replace(/[^\d]/g, '');
    // 3자리마다 콤마 추가
    if (value) {
        value = parseInt(value).toLocaleString('ko-KR');
    }
    input.value = value;
    // 금액이 변경될 때마다 총액 계산
    calculateTotal();
}

function calculateTotal() {
    const amounts = document.querySelectorAll('.expense-amount');
    let total = 0;
    
    amounts.forEach(input => {
        const value = input.value.replace(/,/g, '');
        total += parseInt(value) || 0;
    });
    
    const suppliercostInput = document.getElementById('suppliercost');
    if (suppliercostInput) {
        suppliercostInput.value = total.toLocaleString('ko-KR');
    }
}

// 금액 입력 필드에 이벤트 리스너 추가
document.addEventListener('DOMContentLoaded', function() {
    // 기존 금액 입력 필드에 이벤트 리스너 추가
    document.querySelectorAll('.expense-amount').forEach(input => {
        input.addEventListener('input', function() {
            formatInput(this);
        });
    });

    // 동적으로 추가되는 금액 입력 필드를 위한 이벤트 위임
    document.getElementById('expenseTableBody').addEventListener('input', function(e) {
        if (e.target.classList.contains('expense-amount')) {
            formatInput(e.target);
        }
    });
});

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

<script>
// 지출결의서 내역 관련 함수들
let rowCount = 0;

function addRow(data = null, afterRow = null) {
    const tbody = document.getElementById('expenseTableBody');
    const newRow = document.createElement('tr');
    rowCount++;
    
    // 카테고리 옵션 생성
    const categoryOptions = generateCategoryOptions(data ? data.category : '');
    
    newRow.innerHTML = `
        <td class="text-center">
            <div class="d-flex align-items-center justify-content-center">
                <span class="me-2">${rowCount}</span>
                <button type="button" class="btn btn-primary btn-sm me-1 viewNoBtn" style="padding:2px;" onclick="addRow(null, this.closest('tr'))">
                    <i class="bi bi-plus-lg"></i>
                </button>
                <button type="button" class="btn btn-success btn-sm me-1 viewNoBtn" style="padding:2px;" onclick="copyRow(this)">
                    <i class="bi bi-files"></i>
                </button>
                <button type="button" class="btn btn-danger btn-sm viewNoBtn" style="padding:2px;" onclick="deleteRow(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </td>
        <td>
            <input type="text" class="form-control expense-site" name="expense_site[]" value="${data ? data.site : ''}" placeholder="현장명을 입력하세요">
        </td>
        <td>
            <select class="form-select expense-category viewmode" name="expense_category[]" style="font-size : 0.8rem; height:28px; padding:4px;">
                <option value="">카테고리 선택</option>
                ${categoryOptions}
            </select>
        </td>
        <td>
            <input type="text" class="form-control expense-item" name="expense_item[]" value="${data ? data.item : ''}" placeholder="적요를 입력하세요">
        </td>
        <td>
            <input type="text" class="form-control expense-amount text-end" name="expense_amount[]"
                   value="${data ? data.amount : ''}" oninput="formatInput(this)" style="text-align: right !important;" placeholder="금액을 입력하세요">
        </td>
        <td>
            <input type="text" class="form-control expense-note" name="expense_note[]" value="${data ? data.note : ''}" placeholder="비고를 입력하세요">
        </td>
    `;
    
    if (afterRow) {
        // 특정 행 다음에 삽입
        afterRow.parentNode.insertBefore(newRow, afterRow.nextSibling);
    } else {
        // 테이블 끝에 추가
        tbody.appendChild(newRow);
    }
    
    updateRowNumbers();
    calculateTotal();
}

function deleteRow(button) {
    Swal.fire({
        title: '행 삭제',
        text: '이 행을 삭제하시겠습니까?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: '삭제',
        cancelButtonText: '취소'
    }).then((result) => {
        if (result.isConfirmed) {
            const row = button.closest('tr');
            row.remove();
            updateRowNumbers();
            calculateTotal();
        }
    });
}

function copyRow(button) {
    const row = button.closest('tr');
    const data = {
        site: row.querySelector('.expense-site').value,
        category: row.querySelector('.expense-category').value,
        item: row.querySelector('.expense-item').value,
        amount: row.querySelector('.expense-amount').value,
        note: row.querySelector('.expense-note').value
    };
    addRow(data, row);
}

function updateRowNumbers() {
    const rows = document.querySelectorAll('#expenseTableBody tr');
    rows.forEach((row, index) => {
        const numberSpan = row.querySelector('td:first-child span');
        if (numberSpan) {
            numberSpan.textContent = index + 1;
        }
    });
    rowCount = rows.length;
}

function calculateTotal() {
    const amounts = document.querySelectorAll('.expense-amount');
    let total = 0;
    
    amounts.forEach(input => {
        const value = input.value.replace(/,/g, '');
        total += parseInt(value) || 0;
    });
    
    document.getElementById('suppliercost').value = total.toLocaleString('ko-KR');
}

// 페이지 로드 시 기존 데이터 불러오기
document.addEventListener('DOMContentLoaded', function() {   
    
    // 모드가 신규작성인 경우에만 첫 행 추가
    const mode = '<?php echo $mode; ?>';
    if (mode !== 'modify' && mode !== 'view') {
        // 기존 데이터가 없는 경우에만 첫 행 추가
        if ($('#expenseTableBody tr').length === 0) {
            addRow();
        }
    } else {
        loadExpenseData();
    }
    
    // 기존 금액 입력 필드에 이벤트 리스너 추가
    document.querySelectorAll('.expense-amount').forEach(input => {
        input.addEventListener('input', function() {
            formatInput(this);
        });
    });

    // 동적으로 추가되는 금액 입력 필드를 위한 이벤트 위임
    document.getElementById('expenseTableBody').addEventListener('input', function(e) {
        if (e.target.classList.contains('expense-amount')) {
            formatInput(e.target);
        }
    });
});

// 카테고리 옵션 생성 함수
function generateCategoryOptions(selectedCategory = '') {
    const categories = loadCategories();
    let options = '';
    
    categories.forEach(category => {
        const selected = category === selectedCategory ? 'selected' : '';
        options += `<option value="${category}" ${selected}>${category}</option>`;
    });
    
    return options;
}

// 카테고리 데이터 로드 함수
function loadCategories() {
    // PHP에서 전달받은 카테고리 데이터 사용
    const categories = <?php echo json_encode($categories); ?>;
    return Array.isArray(categories) ? categories : ['식비', '운반비', '자재비', '차량유지비', '기타'];
}

// 기존 데이터 로드
function loadExpenseData() {
    let expenseData;
    try {
        // PHP에서 전달된 데이터를 JSON으로 파싱
        const rawData = <?php echo isset($expense_data) ? json_encode($expense_data) : '[]' ?>;
        
        // 문자열로 된 JSON인 경우 파싱
        if (typeof rawData === 'string') {
            expenseData = JSON.parse(rawData);
        } else {
            expenseData = rawData;
        }

        console.log('Loaded expense data:', expenseData); // 디버깅용 로그

        if (!Array.isArray(expenseData)) {
            console.warn('Expense data is not an array:', expenseData);
            expenseData = [];
        }

        if (expenseData.length > 0) {
            expenseData.forEach(data => {
                const rowData = {
                    site: data.expense_site || '',
                    category: data.expense_category || '',
                    item: data.expense_item || '',
                    amount: data.expense_amount ? parseInt(data.expense_amount).toLocaleString('ko-KR') : '',
                    note: data.expense_note || ''
                };
                console.log('Adding row with data:', rowData); // 디버깅용 로그
                addRow(rowData);
            });
        }
    } catch (e) {
        console.error('Error loading expense data:', e);
        expenseData = [];
    }
}
</script>

<script>
//기존 함수 재선언으로 새롭게 사용함
function popupCenter(url, title, w, h) {
    // URL에 이미 파라미터가 있는지 확인
    const separator = url.includes('?') ? '&' : '?';
    
    var left = (screen.width/2)-(w/2);
    var top = (screen.height/2)-(h/2);
    return window.open(url, title, 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width='+w+', height='+h+', top='+top+', left='+left);
}
</script>

<!-- 이미지 확대 모달 -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="imageModalLabel">이미지 보기</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <img id="modalImage" src="" alt="확대된 이미지" style="max-width: 100%; height: auto;">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
      </div>
    </div>
  </div>
</div>

<!-- 이미지 확대/축소 dialog -->
<dialog id="imageDialog" class="image-dialog">
  <div class="dialog-header">
    <h5>이미지 보기</h5>
    <div class="dialog-controls">
      <button type="button" class="btn btn-sm btn-outline-secondary" onclick="zoomOut()" title="축소">
        <i class="bi bi-zoom-out"></i>
      </button>
      <button type="button" class="btn btn-sm btn-outline-secondary" onclick="zoomIn()" title="확대">
        <i class="bi bi-zoom-in"></i>
      </button>
      <button type="button" class="btn btn-sm btn-outline-danger" onclick="closeImageDialog()" title="닫기">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>
  </div>
  <div class="dialog-body">
    <div class="image-container-zoom">
      <img id="dialogImage" src="" alt="확대된 이미지">
    </div>
  </div>
</dialog>

</body>
</html>