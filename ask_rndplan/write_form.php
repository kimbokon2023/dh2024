<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/session.php'; // 세션 파일 포함

if(!isset($_SESSION["level"]) || $_SESSION["level"]>5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php"); 
    exit;
} 

require_once($_SERVER['DOCUMENT_ROOT'] . '/lib/mydb.php');
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
</style>
<body>
<?php include $_SERVER['DOCUMENT_ROOT'] . "/common/modal.php"; ?>
<?php

$tablename = 'eworks';

$mode = $_REQUEST["mode"] ?? '' ;
$num = $_REQUEST["num"] ?? '' ;
$author = $user_name ?? '' ;

// timekey: 임시 저장용 key 생성
if (empty($_REQUEST['num'])) {
  $timekey = bin2hex(random_bytes(16));
} else {
  $timekey = $_REQUEST['num'];
}

$indate = date("Y-m-d") ?? '' ;

if ($mode=="modify" or $mode=="view"){
    try{
      $sql = "select * from {$DB}.eworks where num = ? ";
      $stmh = $pdo->prepare($sql);
      $stmh->bindValue(1,$num,PDO::PARAM_STR);
      $stmh->execute();
      $count = $stmh->rowCount();
	  $row = $stmh->fetch(PDO::FETCH_ASSOC);
    if($count<1){
      print "결과가 없습니다.<br>";
     }else{
 		include  $_SERVER['DOCUMENT_ROOT'] . '/eworks/_row.php';
		$mytitle = $outworkplace ?? '';
		$content = $al_content ?? '';
		$content_reason = $request_comment ?? '';
		$titlemsg = $mode === 'modify' ? '연구개발계획서(수정)' : '연구개발계획서(조회)';
      }
     }catch (PDOException $Exception) {
       print "오류: ".$Exception->getMessage();
     }
  }
  else{
    include $_SERVER['DOCUMENT_ROOT'] .'/eworks/_request.php';
    $titlemsg = '연구개발계획서 작성';
    $mytitle = $outworkplace ?? '';
	$content = $al_content ?? '';
	$content_reason = $request_comment ?? '';
	$al_company = $mycompany ?? '';
  }

if ($mode!="modify" and $mode!="view" and $mode!="copy"){
    $indate=date("Y-m-d");
	$author = $user_name;
	$titlemsg = '연구개발계획서 작성';
}

if ($mode=="copy"){
    try{
      $sql = "select * from {$DB}.eworks where num = ? ";
      $stmh = $pdo->prepare($sql);
      $stmh->bindValue(1,$num,PDO::PARAM_STR);
      $stmh->execute();
      $count = $stmh->rowCount();
	  $row = $stmh->fetch(PDO::FETCH_ASSOC);
    if($count<1){
      print "결과가 없습니다.<br>";
     }else{
		 include $_SERVER['DOCUMENT_ROOT'] .'/eworks/_row.php';
		$mytitle = $outworkplace ?? '';
		$content = $al_content ?? '';
		$content_reason = $request_comment ?? '';
		$indate=date("Y-m-d");
      }
     }catch (PDOException $Exception) {
         print "오류: ".$Exception->getMessage();
     }
    $titlemsg = '(데이터 복사) 연구개발계획서';
    $num='';
    $id = $num;
    $author = $user_name;
    $update_log='';
}

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

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
   $stmh = $pdo->query($sql);
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
$rotation_arr=array();
$fileid_arr=array();
$item = 'image';
if(isset($id)){
$sql=" select * from ".$DB.".fileuploads where tablename ='$tablename' and item ='$item' and parentid ='$id' ";

 try{
   $stmh = $pdo->query($sql);
   while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
		array_push($realimagename_arr, $row["realname"]);
		array_push($saveimagename_arr, $row["savename"]);
		array_push($rotation_arr, $row["rotate"] ?? 0);
		array_push($fileid_arr, $row["id"]);
        }
   } catch (PDOException $Exception) {
    print "오류: ".$Exception->getMessage();
  }
}

?>

<form id="board_form" name="board_form" method="post" onkeydown="return captureReturnKey(event)">

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
<input type="hidden" id="timekey" name="timekey" value="<?php echo isset($timekey) ? $timekey : ''; ?>">
<input type="hidden" id="searchtext" name="searchtext" value="<?php echo isset($searchtext) ? $searchtext : ''; ?>">
<input type="hidden" id="update_log" name="update_log" value="<?php echo isset($update_log) ? $update_log : ''; ?>">
<input type="hidden" id="first_writer" name="first_writer" value="<?php echo isset($first_writer) ? $first_writer : ''; ?>">
<input type="hidden" id="e_confirm" name="e_confirm" value="<?php echo isset($e_confirm) ? $e_confirm : ''; ?>">
<input type="hidden" id="e_confirm_id" name="e_confirm_id" value="<?php echo isset($e_confirm_id) ? $e_confirm_id : ''; ?>">
<input type="hidden" id="e_line_id" name="e_line_id" value="<?php echo isset($e_line_id) ? $e_line_id : ''; ?>">
<input type="hidden" id="status" name="status" value="<?php echo isset($status) ? $status : ''; ?>">
<input type="hidden" id="al_company" name="al_company" value="<?php echo isset($al_company) ? $al_company : ''; ?>">
<input type="hidden" id="itemcheck" name="itemcheck" value="<?php echo isset($itemcheck) ? $itemcheck : ''; ?>">
<input type="hidden" id="done" name="done" value="<?php echo isset($done) ? $done : ''; ?>">

<div class="container-fluid">
<div class="card">
<div class="card-body">

<div class="row">
	<div class="col-sm-7">
		<div class="d-flex mb-5 mt-5 justify-content-center align-items-center">
			<h4> <?php echo $titlemsg; ?> </h4>
		</div>
	</div>
	<div class="col-sm-5">
<?php
	// 결재 정보 표시 로직 (sample.php 참조)
	if($e_confirm === '' || $e_confirm === null) {
		// 결재 진행 전 상태
		$approvals = array(
			array("name" => "신지환 대표이사", "date" => "")
		);
	} else {
		// 결재 완료 상태 - e_confirm에 '신지환'이 포함되어 있으면 결재 정보 표시
		if(strpos($e_confirm, '신지환') !== false) {
			$approver_details = explode('!', $e_confirm);
			$approvals = array();

			foreach($approver_details as $index => $detail) {
				if (!empty($detail)) {
					// 이름과 날짜 시간 분리
					preg_match("/^(.+) (\d{4}-\d{2}-\d{2}) (\d{2}:\d{2}:\d{2})$/", $detail, $matches);
					if (count($matches) === 4) {
						$nameWithTitle = $matches[1];
						$date = $matches[2];
						$time = $matches[3];
						$formattedDate = date("m/d H:i:s", strtotime("$date $time"));
						$approvals[] = array("name" => $nameWithTitle, "date" => $formattedDate);
					}
				}
			}
		} else {
			// 결재 대기 상태
			$approvals = array(
				array("name" => "신지환 대표이사", "date" => "")
			);
		}
	}

	if($status === 'end' && ($e_confirm !== '' && $e_confirm !== null) && strpos($e_confirm, '신지환') !== false) {
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
<?php
	} else {
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
						<?php foreach ($approvals as $approval) { ?>
							<td class="text-center fs-6" style="height: 60px;"><?php echo $approval["name"]; ?></td>
						<?php } ?>
					</tr>
					<tr>
						<?php foreach ($approvals as $approval) { ?>
							<td class="text-center"><?php echo empty($approval["date"]) ? '' : $approval["date"]; ?></td>
						<?php } ?>
					</tr>
				</tbody>
			</table>
		</div>
<?php
	}
?>
	</div>
</div>

<?php if($mode!='view') { ?>
	<div class="row">
		<div class="col-sm-6">
			<div class="d-flex mb-1 justify-content-start align-items-center">
				<button id="saveBtn" type="button" class="btn btn-dark btn-sm me-2"> <i class="bi bi-floppy"></i> 저장  </button>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="d-flex mb-1 justify-content-end">
			   <button class="btn btn-secondary btn-sm" onclick="self.close();"> <i class="bi bi-x-lg"></i> 창닫기 </button>&nbsp;
			</div>
		</div>
	</div>
<?php } else {  ?>
       <div class="row">
		<div class="col-sm-7">
			<div class="d-flex justify-content-start">
				<button type="button" class="btn btn-dark btn-sm" onclick="location.href='write_form.php?mode=modify&num=<?php echo $num; ?>'" > <i class="bi bi-pencil-square"></i>  수정 </button> &nbsp;
				<?php if($user_id === $author_id || $admin) { ?>
					<button type="button" class="btn btn-danger btn-sm" onclick="javascript:deleteFn('delete.php?num=<?php echo $num; ?>')" ><i class="bi bi-trash"></i>  삭제 </button>	 &nbsp;
				<?php } ?>
				<button type="button" class="btn btn-dark btn-sm" onclick="location.href='write_form.php'" > <i class="bi bi-pencil"></i>  신규 </button>		&nbsp;
				<button type="button" class="btn btn-primary btn-sm" onclick="location.href='write_form.php?mode=copy&num=<?php echo $num; ?>'" > <i class="bi bi-copy"></i> 복사 </button>	&nbsp;
			 </div>
		 </div>
		 <div class="col-sm-5 text-end">
			<div class="d-flex mb-1 justify-content-end">
				<button class="btn btn-secondary btn-sm" type="button" onclick="self.close();" >  &times; 창닫기 </button>&nbsp;
			</div>
		</div>
	 </div> 
<?php } ?> 

  <div class="row mt-2">
      <table class="table table-bordered">
		<tr>
		  <td class="text-center w-25 fw-bold">
			<label for="indate">작성일</label>
		  </td>
		 <td>
			<input type="date" class="form-control w120px viewNoBtn" id="indate" name="indate" value="<?php echo $indate; ?>">
		  </td>
		   <td class="text-center w-25 fw-bold">
			<label for="author">기안자</label>
		  </td>
		 <td>
			<input type="text" class="form-control text-center w80px viewNoBtn" id="author" name="author" value="<?php echo $author; ?>">
		  </td>
		</tr>
		<tr>
		  <td class="text-center w-25 fw-bold">
			<label for="mytitle">제목</label>
		  </td>
		 <td colspan="3">
			<input type="text" class="form-control viewNoBtn" id="mytitle" name="mytitle" value="<?php echo $mytitle; ?>" placeholder="연구개발계획서 제목을 입력하세요">
		  </td>
		</tr>
    </table>
    </div>

	<div class="row mt-2">
      <table class="table table-bordered">
		<tr>
		  <td class="text-center fw-bold" style="width:15%;">
			<label for="content">계획서 내용</label>
		  </td>
		 <td>
			<textarea class="form-control viewNoBtn" id="content" name="content" rows="10" placeholder="연구개발계획서 내용을 입력하세요"><?php echo $content; ?></textarea>
		  </td>
		</tr>
		<tr>
		  <td class="text-center fw-bold">
			<label for="content_reason">개발 목적</label>
		  </td>
		 <td>
			<textarea class="form-control viewNoBtn" id="content_reason" name="content_reason" rows="5" placeholder="개발 목적을 입력하세요"><?php echo $content_reason; ?></textarea>
		  </td>
		</tr>
    </table>
    </div>

    <div class="d-flex mt-3 mb-1 justify-content-center">
	    <?php if($mode != 'view') { ?>
	    <label for="upfile" class="btn btn-outline-dark btn-sm me-2"> 파일 첨부 </label>
		 <input id="upfile" name="upfile[]" type="file" onchange="this.value" multiple style="display:none">
	    <label for="upfileimage" class="btn btn-outline-dark btn-sm"> 사진 첨부 </label>
		 <input id="upfileimage" name="upfileimage[]" type="file" onchange="this.value" multiple accept=".gif, .jpg, .png" style="display:none">
       <?php } ?>
    </div>

	<div class="d-flex mb-1 justify-content-center">
		<div class="d-flex mb-1 justify-content-center fs-6">
			<div id="displayFile" class="mt-5 mb-5 justify-content-center" style="display:none;"></div>
		</div>
	</div>

	<div class="d-flex mb-1 justify-content-center">
		<div class="d-flex mb-1 justify-content-center fs-6">
			<div id="displayImage" class="mt-5 mb-5 justify-content-center" style="display:none;"></div>
		</div>
	</div>

    </div>
	</div>
	</div>
 </div>
</form>

<script>
var ajaxRequest = null;

$("#pInput").val('50');

// 페이지 로딩
$(document).ready(function(){
    var loader = document.getElementById('loadingOverlay');
	if(loader)
		loader.style.display = 'none';

  displayFileLoad();
  displayImageLoad();
});

$(document).ready(function(){
	 $("#saveBtn").click(function(){
		if($("#mytitle").val() === '' || $("#content").val() === '' || $("#content_reason").val() === '' ) {
			Swal.fire({
				title: '등록 오류 알림',
				text: '제목, 내용, 개발 목적은 필수입력 요소입니다.',
				icon: 'warning'
			});
		} else {
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

function saveData() {
	var num = $("#num").val();
	if(Number(num) < 1 && $("#mode").val() !== 'copy')
		$("#mode").val('insert');
	var form = $('#board_form')[0];
	var datasource = new FormData(form);

	if (ajaxRequest !== null) {
		ajaxRequest.abort();
	}
	ajaxRequest = $.ajax({
		enctype: 'multipart/form-data',
		processData: false,
		contentType: false,
		cache: false,
		timeout: 600000,
		url: "insert.php",
		type: "post",
		data: datasource,
		dataType: "json",
		success: function(data) {
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
					self.close();
				}, 1000);
			}, 1000);
		},
		error: function(jqxhr, status, error) {
			console.error('Error:', jqxhr, status, error);
			Swal.fire({
				title: '오류 발생',
				text: '데이터 저장 중 오류가 발생했습니다.',
				icon: 'error'
			});
		}
	});
}

});

function captureReturnKey(e) {
    if(e.keyCode==13 && e.srcElement.type != 'textarea')
    return false;
}
</script>

<script>
$(document).ready(function () {

// 첨부파일 멀티업로드
$("#upfile").change(function(e) {
    $("#id").val('<?php echo $id;?>');
    $("#parentid").val('<?php echo $id;?>');
    $("#fileorimage").val('file');
    $("#item").val('attached');
    $("#upfilename").val('upfile');
    $("#savetitle").val('연구개발계획서 첨부파일');

	if(Number($("#id").val()) == 0)
		$("#id").val($("#timekey").val());

	var form = $('#board_form')[0];
	var data = new FormData(form);

	tmp='파일을 저장중입니다. 잠시만 기다려주세요.';
	$('#alertmsg').html(tmp);
	$('#myModal').modal('show');

	$.ajax({
		enctype: 'multipart/form-data',
		processData: false,
		contentType: false,
		cache: false,
		timeout: 600000,
		url: "../file/file_insert.php",
		type: "post",
		data: data,
		success : function(data){
			console.log(data);
			setTimeout(function() {
				$('#myModal').modal('hide');
			}, 1000);
			displayFile();
		},
		error : function( jqxhr , status , error ){
			console.log( jqxhr , status , error );
		}
	});
});

// 첨부 이미지 멀티업로드
$("#upfileimage").change(function(e) {
    $("#fileorimage").val('image');
    $("#item").val('image');
    $("#upfilename").val('upfileimage');
    $("#savetitle").val('연구개발계획서 이미지');

	if(Number($("#id").val()) == 0)
		$("#id").val($("#timekey").val());

	var form = $('#board_form')[0];
	var data = new FormData(form);

	tmp='파일을 저장중입니다. 잠시만 기다려주세요.';
	$('#alertmsg').html(tmp);
	$('#myModal').modal('show');

	$.ajax({
		enctype: 'multipart/form-data',
		processData: false,
		contentType: false,
		cache: false,
		timeout: 600000,
		url: "../file/file_insert.php",
		type: "post",
		data: data,
		success : function(data){
			console.log('image 업로드 후 data', data);
			setTimeout(function() {
				$('#myModal').modal('hide');
			}, 1000);
			displayImage();
		},
		error : function( jqxhr , status , error ){
			console.log( jqxhr , status , error );
		}
	});
});

delPicimageFn = function(divID, delChoice) {
	console.log(divID, delChoice);
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
			$.ajax({
				url:'../file/del_file.php?savename=' + delChoice ,
				type:'post',
				data: $("#board_form").serialize(),
				dataType: 'json',
				}).done(function(data){
				   const savename = data["savename"];

					$("#image" + divID).closest('.image-container').remove();

					Swal.fire(
						'삭제 완료!',
						'이미지가 성공적으로 삭제되었습니다.',
						'success'
					);

		        }).fail(function(xhr, status, error) {
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
  $('#displayImage').show();
  params = $("#id").val();
  if(isNaN(params)) {
    params = $("#timekey").val();
  }
  var tablename = $("#tablename").val();
  var item = 'image';

  $.ajax({
    url:'../file/load_file.php?id=' + params + '&tablename=' + tablename + '&item=' + item ,
    type:'post',
    data: $("#board_form").serialize(),
    dataType: 'json',
  }).done(function(data){
    const recid = data["recid"];
    $("#displayImage").html('');

    if(recid > 0) {
      for(i=0;i<recid;i++) {
        const rotation = data['rotation_arr'] && data['rotation_arr'][i] ? data['rotation_arr'][i] : 0;
        const fileId = data['id_arr'] && data['id_arr'][i] ? data['id_arr'][i] : i;

        let buttonsHtml = '';
        if ($("#mode").val() !== 'view') {
          buttonsHtml =
            "<div class='position-absolute top-0 end-0 mt-1 me-1'>" +
              "<button type='button' class='btn btn-danger btn-sm' id='delPicimage" + i + "' onclick='delPicimageFn(" + i + ",\"" + data["file_arr"][i] + "\")' title='이미지 삭제' style='padding: 2px 6px; font-size: 12px;'>" +
                "<i class='bi bi-trash3-fill'></i>" +
              "</button>" +
            "</div>";
        }

        $("#displayImage").append(
          "<div class='image-container position-relative d-inline-block m-2 border rounded' style='background-color: #f8f9fa;'>" +
            "<img id='image" + i + "' src='../uploads/" + data['file_arr'][i] + "' style='width:120px; height:120px; object-fit:contain; transform: rotate(" + rotation + "deg); border-radius: 4px;' data-rotation='" + rotation + "'>" +
            buttonsHtml +
          "</div>"
        );
      }
    }
  }).fail(function(jqXHR, textStatus, errorThrown) {
    console.error('AJAX 요청 실패');
  });
}

// 기존 있는 이미지 화면에 보여주기
function displayImageLoad() {
	$('#displayImage').show();
	var saveimagename_arr = <?php echo json_encode($saveimagename_arr);?> ;
	var realimagename_arr = <?php echo json_encode($realimagename_arr);?> ;
	var rotation_arr = <?php echo json_encode($rotation_arr);?> ;
	var fileid_arr = <?php echo json_encode($fileid_arr);?> ;

	if(saveimagename_arr.length > 0) {
    for(i=0;i<saveimagename_arr.length;i++) {
      const rotation = rotation_arr[i] !== undefined ? rotation_arr[i] : 0;
      const fileId = fileid_arr[i] !== undefined ? fileid_arr[i] : i;

      let buttonsHtml = '';
      if ($("#mode").val() !== 'view') {
        buttonsHtml =
          "<div class='position-absolute top-0 end-0 mt-1 me-1'>" +
            "<button type='button' class='btn btn-danger btn-sm' id='delPicimage" + i + "' onclick='delPicimageFn(" + i + ",\"" + saveimagename_arr[i] + "\")' title='이미지 삭제' style='padding: 2px 6px; font-size: 12px;'>" +
              "<i class='bi bi-trash3-fill'></i>" +
            "</button>" +
          "</div>";
      }

      $("#displayImage").append(
        "<div class='image-container position-relative d-inline-block m-2 border rounded' style='background-color: #f8f9fa;'>" +
          "<img id='image" + i + "' src='../uploads/" + saveimagename_arr[i] + "' style='width:120px; height:120px; object-fit:contain; transform: rotate(" + rotation + "deg); border-radius: 4px;' data-rotation='" + rotation + "'>" +
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
	if(isNaN(params)) {
		params = $("#timekey").val();
	}

    var tablename = $("#tablename").val();
    var item = 'attached';

	$.ajax({
		url:'../file/load_file.php?id=' + params + '&tablename=' + tablename + '&item=' + item ,
		type:'post',
		data: $("#board_form").serialize(),
		dataType: 'json',
		}).done(function(data){
		   const recid = data["recid"];
		   console.log(data);
		   $("#displayFile").html('');
		   for(i=0;i<recid;i++) {
			   $("#displayFile").append("<div id=file" + i + ">  <a href='../uploads/" + data["file_arr"][i] + "' download='" +  data["realfile_arr"][i]+ "'>" +  data["realfile_arr"][i] + "</div> &nbsp;&nbsp;&nbsp;&nbsp;  " );

         	   // 조회 모드가 아닐 때만 삭제 버튼 표시
         	   if ($("#mode").val() !== 'view') {
         	   	   $("#displayFile").append("&nbsp;<button type='button' class='btn btn-outline-danger btn-sm' id='delPic" + i + "' onclick=delPicFn('" + i + "','" + data["file_arr"][i] + "')> <i class='bi bi-trash3-fill'></i>  </button>&nbsp;");
         	   }
         	   $("#displayFile").append("<br>");
		      }
    });
}

// 기존 있는 파일 화면에 보여주기
function displayFileLoad() {
	$('#displayFile').show();
	var savefilename_arr = <?php echo json_encode($savefilename_arr);?> ;
	var realname_arr = <?php echo json_encode($realname_arr);?> ;
	if(savefilename_arr.length > 0) {
		for(i=0;i<savefilename_arr.length;i++) {
			$("#displayFile").append("<div id=file" + i + ">  <a href='../uploads/" + savefilename_arr[i] + "' download='" + realname_arr[i] + "'>" +  realname_arr[i] + "</div> &nbsp;&nbsp;&nbsp;&nbsp;  " );

			// 조회 모드가 아닐 때만 삭제 버튼 표시
			if ($("#mode").val() !== 'view') {
				$("#displayFile").append("&nbsp;<button type='button' class='btn btn-outline-danger btn-sm' id='delPic" + i + "' onclick=delPicFn('" + i + "','" +  savefilename_arr[i] + "')> <i class='bi bi-trash3-fill'></i>  </button>&nbsp;");
			}
			$("#displayFile").append("<br>");
		}
	}
}

// 파일 삭제 함수
delPicFn = function(divID, delChoice) {
	console.log(divID, delChoice);
	Swal.fire({
		title: '파일 삭제',
		text: '정말로 이 파일을 삭제하시겠습니까?',
		icon: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#d33',
		cancelButtonColor: '#3085d6',
		confirmButtonText: '삭제',
		cancelButtonText: '취소'
	}).then((result) => {
		if (result.isConfirmed) {
			$.ajax({
				url:'../file/del_file.php?savename=' + delChoice ,
				type:'post',
				data: $("#board_form").serialize(),
				dataType: 'json',
				}).done(function(data){
				   const savename = data["savename"];

					$("#file" + divID).remove();
					$("#delPic" + divID).remove();

					Swal.fire(
						'삭제 완료!',
						'파일이 성공적으로 삭제되었습니다.',
						'success'
					);

		        }).fail(function(xhr, status, error) {
					Swal.fire(
						'삭제 실패!',
						'파일 삭제 중 오류가 발생했습니다.',
						'error'
					);
					console.error('삭제 오류:', error);
		        });
		}
	});
}

// 자료 삭제 함수
function deleteFn(href) {
    Swal.fire({
        title: '자료 삭제',
        text: '삭제는 신중! 정말 삭제하시겠습니까?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: '삭제',
        cancelButtonText: '취소'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'delete.php',
                type: 'post',
                data: $("#board_form").serialize(),
                dataType: 'json'
            }).done(function(data) {
                Toastify({
                    text: "자료 삭제 완료",
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
                        if (typeof window.opener.restorePageNumber === 'function') {
                            window.opener.restorePageNumber();
                        }
                        window.opener.location.reload();
                    }
                    setTimeout(function() { self.close(); }, 500);
                }, 1000);
            }).fail(function(xhr, status, error) {
                Swal.fire({
                    title: '삭제 실패!',
                    text: '자료 삭제 중 오류가 발생했습니다.',
                    icon: 'error'
                });
                console.error('삭제 오류:', xhr, status, error);
            });
        }
    });
}

$(document).ready(function () {
	var mode = '<?php echo $mode; ?>';
	if (mode === 'view') {
		disableView();
	}

	function disableView() {
		$('input, textarea ').prop('readonly', true);
		$('input[type=hidden]').prop('readonly', false);
		$('input[type=file]').prop('disabled', true);
		$('.viewNoBtn').prop('disabled', true);
	}
});

</script>
</body>
</html>