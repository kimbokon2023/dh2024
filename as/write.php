<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php"); 
    exit;
}   
include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';
$title_message = '결선/AS 등록/수정'; 
?> 
<title> <?=$title_message?> </title>
<?php if($chkMobile==true) { ?>
<style>
  /* 모바일 화면에서 폰트 크기를 20px로 설정 */
  @media (max-width: 1000px) {
    body {
      font-size: 25px;
    }

    .form-control, .fw-bold, .table td, .table th {
      font-size: 25px; /* 테이블, 입력 필드 등의 폰트 크기 조정 */
    }

    button {
      font-size: 30px; /* 버튼의 폰트 크기 조정 */
    }

    .modal-body, .modal-title {
      font-size: 30px; /* 모달 창 내부 폰트 크기 조정 */
    }
	
  }
</style>
<?php } ?>
</head>
<body>		 
<?php
$option = isset($_REQUEST['option']) ? $_REQUEST['option'] : '';  
$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';  
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  
$header = isset($_REQUEST['header']) ? $_REQUEST['header'] : '';  


$num = isset($_REQUEST["num"]) ? $_REQUEST["num"] : 0;
isset($_REQUEST["id"])  ? $id=$_REQUEST["id"] :   $id=$num; 
isset($_REQUEST["fileorimage"])  ? $fileorimage=$_REQUEST["fileorimage"] :   $fileorimage=''; // file or image
isset($_REQUEST["item"])  ? $item=$_REQUEST["item"] :   $item=''; 
isset($_REQUEST["upfilename"])  ? $upfilename=$_REQUEST["upfilename"] :   $upfilename=''; 
isset($_REQUEST["tablename"])  ? $tablename=$_REQUEST["tablename"] :  $tablename=''; 
isset($_REQUEST["savetitle"])  ? $savetitle=$_REQUEST["savetitle"] :  $savetitle='';   // log기록 저장 타이틀
  
$tablename = 'as';
  	  
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();
    
// 첨부파일 있는 것 불러오기 
$savefilename_arr=array(); 
$realname_arr=array(); 
$item = 'attached';

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

// 첨부 이미지 있는 것 불러오기 
$realimagename_arr=array(); 
$saveimagename_arr=array(); 
$item = 'image';

$sql=" select * from ".$DB.".fileuploads where tablename ='$tablename' and item ='$item' and parentid ='$id' ";	

 try{  
   $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh   
   while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
			array_push($realimagename_arr, $row["realname"]);							   
			array_push($saveimagename_arr, $row["savename"]);		
        }		 
   } catch (PDOException $Exception) {
    print "오류: ".$Exception->getMessage();
  }   


if($num > 0) {	
    try {
        $sql = "SELECT * FROM ". $DB . "." . $tablename . " WHERE num = ?";
        $stmh = $pdo->prepare($sql);  
        $stmh->bindValue(1, $num, PDO::PARAM_INT);      
        $stmh->execute();            
        $row = $stmh->fetch(PDO::FETCH_ASSOC); 	 
        include '_row.php';
    } catch (PDOException $Exception) {
        print "오류: ".$Exception->getMessage();
    }   
        $title_message = '결선/AS 등록내용 수정/삭제 '; 
		$mode = 'modify';
 } else {
    include '_request.php';
    $mode = 'insert';
	// 현재 날짜를 'Y-m-d' 형식으로 기록
	$asday = date('Y-m-d');
	// 현재 사용자 이름을 기록
	$aswriter = $user_name;
}

if ($asfee !== '' && $asfee !== null && strpos($asfee, ',') === false) {
    $asfee = number_format($asfee);
}

// echo '<pre>';
// print_r($row);
// echo '</pre>';

?>

<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/common/modal.php'); ?>   

<form id="board_form" name="board_form" method="post" enctype="multipart/form-data">	
		 
	<input type="hidden" id="id" name="id" value="<?= isset($id) ? $id : '' ?>" >
	<input type="hidden" id="num" name="num" value="<?= isset($num) ? $num : '' ?>" >
	<input type="hidden" id="parentid" name="parentid" value="<?= isset($parentid) ? $parentid : '' ?>" >
	<input type="hidden" id="fileorimage" name="fileorimage" value="<?= isset($fileorimage) ? $fileorimage : '' ?>" >
	<input type="hidden" id="item" name="item" value="<?= isset($item) ? $item : '' ?>" >
	<input type="hidden" id="upfilename" name="upfilename" value="<?= isset($upfilename) ? $upfilename : '' ?>" >
	<input type="hidden" id="tablename" name="tablename" value="<?= isset($tablename) ? $tablename : '' ?>" >
	<input type="hidden" id="savetitle" name="savetitle" value="<?= isset($savetitle) ? $savetitle : '' ?>" >
	<input type="hidden" id="pInput" name="pInput" value="<?= isset($pInput) ? $pInput : '' ?>" >
	<input type="hidden" id="mode" name="mode" value="<?= isset($mode) ? $mode : '' ?>" >
	<input type="hidden" id="timekey" name="timekey" value="<?= isset($timekey) ? $timekey : '' ?>" > <!-- 신규데이터 작성시 parentid key값으로 사용 -->
	<input type="hidden" id="searchtext" name="searchtext" value="<?= isset($searchtext) ? $searchtext : '' ?>" > <!-- summernote text저장 -->
	<input type="hidden" id="update_log" name="update_log" value="<?= isset($update_log) ? $update_log : '' ?>" >
	<input type="hidden" id="itemcheck" name="itemcheck"> <!-- 체크박스형태를 저장하려면 두개의 변수가 필요하다.  -->	
			

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
		
	<!-- Modal 구조 -->
	<div class="modal fade" id="workplaceModal" tabindex="-1" aria-labelledby="workplaceModalLabel" aria-hidden="false">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="workplaceModalLabel">현장 검색</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<table class="table table-hover">
						<thead>
							<tr>
								<th>현장명</th>
								<th>수주Code</th>
							</tr>
						</thead>
						<tbody id="workplaceTableBody">
							<!-- 데이터가 여기에 삽입됩니다. -->
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="container-fluid">					    
        <div class="card justify-content-center">
            <div class="card-header text-center">
                <span class="text-center fs-5"><?=$title_message?></span>								
            </div>
            <div class="card-body">                                
                <div class="row justify-content-center text-center">                    
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <td class="text-center fw-bold">출고현장 검색</td>
						<!-- 검색 버튼 및 입력 필드 -->
						<td colspan="3" class="text-center">
							<div class="d-flex">								
								<input type="text" class="form-control me-1" id="workplacename" name="workplacename" style="width:73%;" onkeyup="if(event.keyCode == 13) { EnterSearch(); }" >
								<button type="button" id="searchplace" class="btn btn-primary fetch_outworkplaceBtn btn-sm me-1">
									<i class="bi bi-search"></i>
								</button>
								<input type="text" class="form-control" id="workplaceCode" name="workplaceCode" style="width:15%;" readonly value="<?=$workplaceCode?>" placeholder="수주Code" onclick="openPopup()">
							</div>
						</td>												
                            </tr>	
                            <tr>
                                <td class="text-center fw-bold" colspan="2">
								  <div class="d-flex p-1 mb-1 justify-content-center align-items-center">
									<span class="text-center text-danger fs-6 me-5">     
										<?php
										$checkstep_options = ["결선", "AS"];
										$selected_value = !empty($itemcheck) ? explode(", ", $itemcheck) : ["AS"]; // 기본값을 "AS"로 설정

										foreach ($checkstep_options as $option) {
											echo '<span class="fw-bold text-primary ms-2">' . $option . '</span> &nbsp; 
												  <input type="radio" name="radio_as" value="' . $option . '"' . (in_array($option, $selected_value) ? ' checked' : '') . '> ';
										}
										?>
									</span>
								 
								     <span class="text-dark fw-bold ms-2 me-1"> 접수일 </span> &nbsp; <input type="date" class="form-control "  style="width:130px;" id="asday" name="asday" value="<?=$asday?>"> 
								   </div>
                                </td>		
                                <td class="text-center fw-bold">접수자</td>
                                <td class="text-center">
                                    <input type="text" class="form-control" id="aswriter" name="aswriter" value="<?=$aswriter?>">    
                                </td>												
                            </tr>	
                            <tr>
                                <td class="text-center fw-bold">현장 주소</td>
                                <td colspan="3" class="text-center">
                                    <input type="text" class="form-control" id="address" name="address" value="<?=$address?>">
                                </td>
                            </tr>	
							<tr>				
								<td class="text-center fw-bold">현장 담당자</td>
								<td class="text-center">
									<input type="text" class="form-control" id="spotman" name="spotman" value="<?=$spotman?>">       
								</td>							
								<td class="text-center fw-bold">현장 담당 <i class="bi bi-telephone-forward-fill"></i> </td>	
								<td class="text-center">
									<input type="text" class="form-control" id="spotmantel" name="spotmantel" value="<?=$spotmantel?>">    
								</td>																					
							</tr>							
                            <tr>
                                <td class="text-center fw-bold">요청업체</td>
                                <td class="text-center">
                                    <input type="text" class="form-control" id="as_step" name="as_step" value="<?=$as_step?>">    
                                </td>				
                                <td class="text-center fw-bold">요청인</td>
                                <td class="text-center">
                                    <input type="text" class="form-control" id="asorderman" name="asorderman" value="<?=$asorderman?>">  
                                </td>                            
                            </tr>							
                            <tr>
                                <td class="text-center fw-bold">요청인 연락처</td>
                                <td class="text-center">
                                    <input type="text" class="form-control" id="asordermantel" name="asordermantel" value="<?=$asordermantel?>">   							
                                </td>
                            </tr>							
                            <tr>								
                                <td class="text-center fw-bold">비용부담업체</td>
                                <td class="text-center">
                                    <input type="text" class="form-control" id="asfee_estimate" name="asfee_estimate" value="<?=$asfee_estimate?>">     
                                </td>							
                                <td class="text-center fw-bold">비용담당자</td>
                                <td class="text-center">
                                    <input type="text" class="form-control" id="payman" name="payman" value="<?=$payman?>">     
                                </td>	
                            </tr>							
                            <tr>									
                                <td class="text-center fw-bold">비용부담업체 <br> 연락처</td>	
                                <td class="text-center">
									<input type="text" class="form-control" id="as_refer" name="as_refer" value="<?=$as_refer?>">                                         
                                </td>																					
                            </tr>
                         <tr>	 
                                <td class="text-center fw-bold">AS 구체적 증상 및 <br> 추가 메모</td>
                                <td colspan="3" class="text-center">
                                    <textarea class="form-control" id="aslist" name="aslist"><?=$aslist?></textarea>  
                                </td>	
						 </tr>
                         <tr>				
								<td class="text-center fw-bold">처리 예정일</td>
								<td class="text-center">
									<input type="date" class="form-control" id="asproday" name="asproday" value="<?=$asproday?>">  
								</td>							                                
								<td class="text-center fw-bold">유상/무상</td>
								<td class="text-center">
									<input type="radio" id="free" name="payment" value="free" onchange="togglePaymentDetails()"> 무상
									<input type="radio" id="paid" name="payment" value="paid" onchange="togglePaymentDetails()"> 유상
								</td>							                                
							
							<tr>				
								<td class="text-center fw-bold">결선/AS 담당자</td>
								<td class="text-center">
									<input type="text" class="form-control" id="asman" name="asman" value="<?=$asman?>">       
								</td>									
							<!-- 유상일 경우에 추가될 tr -->
							<tr id="paidDetails" style="display: none;">
								<td class="text-center fw-bold">비용</td>
								<td class="text-center">
									<input type="text" class="form-control" id="asfee" name="asfee" 
										   value="<?= !empty($asfee) ? $asfee : '' ?>" 
										   onkeyup="inputNumberFormat(this)">

								</td>							
								<td class="text-center text-primary fw-bold">유상 처리 내용</td>
								<td colspan="3" class="text-center">
									<textarea class="form-control" id="asresult" name="asresult"><?=$asresult?></textarea>    							
								</td>
							</tr>	                            
							<tr>
								<td class="text-center text-danger fw-bold">처리방법 및 결과 <br> (구체적 기록)</td>
								<td colspan="3" class="text-center">
									<textarea class="form-control" id="note" name="note"><?=$note?></textarea>    							
								</td>
							</tr>
							<tr>
								<td class="text-center fw-bold"> 완료일</td>	
								<td class="text-center">
									<input type="date" class="form-control" id="asendday" name="asendday" value="<?=$asendday?>">    
								</td>	
								<td class="text-center fw-bold"> 청구일</td>
								<td class="text-center">
									<input type="date" class="form-control" id="demandDate" name="demandDate"  value="<?=$demandDate?>">
								</td>								
							</tr>
							<tr>
								<td class="text-center fw-bold"> 입금일</td>	
								<td colspan="1" class="text-center">
									<input type="date" class="form-control" id="paydate" name="paydate" value="<?=$paydate?>">    
								</td>									
								<td colspan="2" class="text-center">
									<!-- 첨부파일 / 사진 추가부분 -->
									<div class="d-flex justify-content-center">  	 		 
										 <label for="upfile" class="input-group-text btn btn-outline-primary btn-sm me-2"> 파일 첨부 </label>						  							
										 <input id="upfile"  name="upfile[]" type="file" onchange="this.value" multiple  style="display:none" >
									</div>	
								</td>									
							</tr>
                        </tbody>
                    </table>   
                </div>
            </div>

		<!-- 첨부파일 / 사진 추가부분 -->
		
		<div id ="displayFile" class="d-flex mt-1 mb-1 justify-content-center" style="display:none;">  	 		 					
			 
		</div>			
		<div id ="displayImage" class="row d-flex mt-1 mb-1 justify-content-center" style="display:none;">  	 		 								 
		</div>		
			

        </div>		
			<div class="row mt-2">
				<div class="d-flex justify-content-center">
				<button type="button" id="saveBtn"  class="btn btn-dark btn-sm me-3">
					<i class="bi bi-floppy-fill"></i> 저장
				</button>	
				<button type="button" id="deleteBtn" class="btn btn-danger btn-sm me-3">
					<i class="bi bi-trash"></i>  삭제 
				</button>	
				<button type="button"  id="closeBtn" class="btn btn-outline-dark btn-sm me-2">
					&times; 닫기
				</button>									
				</div>
			</div>			
    </div>

</div>
</div>
</form>

<script>
function togglePaymentDetails() {
    const paidOption = document.getElementById('paid');
    const paidDetailsRow = document.getElementById('paidDetails');
    
    if (paidOption.checked) {
        paidDetailsRow.style.display = '';
    } else {
        paidDetailsRow.style.display = 'none';
    }
}

// 초기 상태 설정
window.onload = function() {
    const paymentValue = "<?= isset($payment) ? $payment : 'free' ?>"; // payment 값이 없으면 기본값을 'free'로 설정
    
    if (paymentValue === 'paid') {
        document.getElementById('paid').checked = true;
        togglePaymentDetails(); // 유상 선택 시 상세 항목 표시
    } else {
        document.getElementById('free').checked = true;
        togglePaymentDetails(); // 무상 선택 시 상세 항목 숨김
    }
};
</script>

<!-- JavaScript -->
<script>
$(document).ready(function() {
	// 검색 버튼 클릭 시 모달 열기
	$("#searchplace").click(function() {
		searchplace();
	});
});

function searchplace() {
	const searchQuery = $("#workplacename").val();

	$.ajax({
		url: "fetch_workplace.php",
		type: "GET",
		data: { query: searchQuery },
		success: function(data) {
			// data는 fetch_workplace.php에서 반환된 HTML 테이블 행들입니다.
			$("#workplaceTableBody").html(data);
			$("#workplaceModal").modal('show');
		},
		error: function(xhr, status, error) {
			console.error("AJAX 오류:", status, error);
		}
	});
}

// 현장을 클릭했을 때 값을 input에 채우는 함수
function selectWorkplace(workplaceName, workplaceCode) {
	$("#workplacename").val(workplaceName);
	$("#workplaceCode").val(workplaceCode);
	$("#workplaceModal").modal('hide'); // 모달 닫기
}
</script>

<!-- 페이지 로딩 -->
<script>
$(document).ready(function(){	
    var loader = document.getElementById('loadingOverlay');
    loader.style.display = 'none';
});
</script>
	 
<script>
ajaxRequest_write = null;

$(document).ready(function(){	  
    $("#closeBtn").on("click", function() {
        self.close();
    });	
	
    $("#saveBtn").on("click", function() {
        let msg = '저장완료';
		
		showSavingModal();
		
		// 선택된 라디오 버튼 값을 수집
		var selectedCheckas = $("input[name='radio_as']:checked").val();
		$("#itemcheck").val(selectedCheckas);

        if (ajaxRequest_write !== null) {
            ajaxRequest_write.abort();
        }		 
        ajaxRequest_write = $.ajax({
            url: "process.php",
            type: "post",		
            data: $("#board_form").serialize(),								
            success: function(data) {	
				console.log(data);			
                // Toastify({
                    // text: msg,
                    // duration: 3000,
                    // close: true,
                    // gravity: "top",
                    // position: "center",
                    // backgroundColor: "#4fbe87",
                // }).showToast();			
                
                setTimeout(function() {
                   // self.close();								   
				   hideSavingModal();
				   ajaxRequest_write = null;
					if ($(opener.location).attr("href").includes("javascript:reloadlist();")) {
						hideSavingModal();
						// 조건이 만족할 때 실행할 코드
						console.log("reloadlist 함수가 부모 창에 존재합니다.");
					} else {
						// 조건이 만족하지 않을 때 실행할 코드
						hideSavingModal();
						console.log("reloadlist 함수가 부모 창에 없습니다.");
					}

                }, 1000);				
            },
            error: function(jqxhr, status, error) {
                console.log(jqxhr, status, error);
				ajaxRequest_write = null;
				hideSavingModal();
            } 			      		
        });												
    });			
});	 

$(document).ready(function() {
    // 삭제 버튼 클릭 이벤트
    $("#deleteBtn").on("click", function() {
        const delfirstitem = $("#num").val(); // 삭제할 항목의 num 값을 가져옴
        delFn(delfirstitem);
    });
});

function delFn(delfirstitem) {
    $("#mode").val("delete");
    $("#num").val(delfirstitem);

    Swal.fire({
        title: '해당 DATA 삭제',
        text: "DATA 삭제는 신중하셔야 합니다. 정말 삭제 하시겠습니까?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: '삭제',
        cancelButtonText: '취소'
    }).then((result) => {
        if (result.isConfirmed) {
			
			showSavingModal();
            if (ajaxRequest_write !== null) {
                ajaxRequest_write.abort();
            }		 
            ajaxRequest_write = $.ajax({
                url: "process.php",
                type: "post",
                data: $("#board_form").serialize(),
                success: function(data) {
                    console.log(data);
                    // Toastify({
                        // text: "파일 삭제 완료!",
                        // duration: 3000,
                        // close: true,
                        // gravity: "top",
                        // position: "center",
                        // backgroundColor: "#4fbe87"
                    // }).showToast();
                    
                    // 페이지를 1.5초 후에 새로고침
                    setTimeout(function() {
					    $(opener.location).attr("href", "javascript:reloadlist();");
						ajaxRequest_write = null;
						hideSavingModal();						
                        self.close();
                    }, 1000);										 
                },
                error: function(jqxhr, status, error) {
                    console.log(jqxhr, status, error);
					ajaxRequest_write = null;
					hideSavingModal();
                }
            });												
        } 
    });	
}

function openPopup() {
    // workplaceCode input 요소에서 값을 가져옵니다.
    var workplaceCode = document.getElementById('workplaceCode').value;

    // 가져온 값을 URL에 추가합니다.
    var url = "/motor/write_form.php?mode=view&num=" + workplaceCode;

    // customPopup 함수를 호출하여 팝업을 엽니다.
	if(workplaceCode>0)
		customPopup(url, '수주내역', 1850, 900);
}

function EnterSearch() {
    // 가져온 값을 URL에 추가합니다.
   searchplace();
}


function inputNumberFormat(obj) {
    // 숫자, 소수점 및 - 이외의 문자는 제거
    obj.value = obj.value.replace(/[^0-9.-]/g, '');

    // 콤마를 제거하고 숫자를 포맷팅
    let value = obj.value.replace(/,/g, '');

    // 부호가 앞에 오도록 하고 소수점을 포함한 포맷팅 처리
    if (value.startsWith('-')) {
        // 음수일 때의 처리
        value = '-' + formatNumber(value.slice(1));
    } else {
        // 양수일 때의 처리
        value = formatNumber(value);
    }

    obj.value = value;
}

// 3자리마다 콤마를 추가하는 함수
function formatNumber(value) {
    if (!value) return ''; // 값이 없으면 빈 문자열 반환
    let parts = value.split('.');
    // 정수 부분에만 콤마 추가
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    // 소수점이 있으면 정수 부분 + 소수점 부분을 반환
    return parts.length > 1 ? parts.join('.') : parts[0];
}

$(document).ready(function() {
    // AS 담당자 필드를 클릭하면 모달을 표시
    $('#asman').focus(function() {
        $('#memberModal').modal('show');
    });

    // 모달 열릴 때 직원 목록을 불러옴
    $('#memberModal').on('shown.bs.modal', function () {
        // 기존에 선택된 직원 이름을 배열로 분리
        var selectedNames = $('#asman').val().split(',').map(function(name) {
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
                    $('#asman').val(selectedNames.join(', '));
                });
            },
            error: function(xhr, status, error) {
                console.log("Error fetching member list: ", error);
            }
        });
    });

});

$("#pInput").val('50'); // 최초화면 사진파일 보여주기
$(document).ready(function() {
let timer3 = setInterval(() => {  // 2초 간격으로 사진업데이트 체크한다.
	      if($("#pInput").val()=='100')   // 사진이 등록된 경우
		  {
	             displayFile();  
	             displayImage();  
				 // console.log(100);
		  }	      
		  if($("#pInput").val()=='50')   // 사진이 등록된 경우
		  {
	             displayFileLoad();				 
	             displayImageLoad();				 
		  }		   
	 }, 500);	
	 
// 파일 첨부 관련 함수모음
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
	    $("#savetitle").val('결선AS 이미지');		
	
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
function displayImage() {       
	$('#displayImage').show();
	params = $("#id").val();	
	
    var tablename = $("#tablaname").val();    
    var item = 'image';
	
	$.ajax({
		url:'../file/load_file.php?id=' + params + '&tablename=' + tablename + '&item=' + item ,
		type:'post',
		data: $("board_form").serialize(),
		dataType: 'json',
		}).done(function(data){						
		   const recid = data["recid"];		   
		   console.log(data);
		   $("#displayImage").html('');
		   for(i=0;i<recid;i++) {	
			   $("#displayImage").append("<img id='image" + i + "' src='../uploads/" + data['file_arr'][i] + "' style='width:80%;' > &nbsp; <br> &nbsp;  " );			   
         	   $("#displayImage").append("&nbsp;<button type='button' class='btn btn-outline-danger btn-sm' id='delPicimage" + i + "' onclick=delPicimageFn('" + i + "','" + data["file_arr"][i] + "')><i class='bi bi-trash3-fill'></i> </button>&nbsp; <br>");					   
		      }		   
			    $("#pInput").val('');			
    });	
}

// 기존 있는 이미지 화면에 보여주기
function displayImageLoad() {    

	$('#displayImage').show();			
	var saveimagename_arr = <?php echo json_encode($saveimagename_arr);?> ;	
	
    for(i=0;i<saveimagename_arr.length;i++) {
			   $("#displayImage").append("<img id='image" + i + "'src='../uploads/" + saveimagename_arr[i] + "' style='width:80%;' >&nbsp;  <br> &nbsp; " );			   
         	   $("#displayImage").append("&nbsp;<button type='button' class='btn btn-outline-danger btn-sm' id='delPicimage" + i + "' onclick=delPicimageFn('" + i + "','" +  saveimagename_arr[i] + "')> <i class='bi bi-trash3-fill'></i>  </button>&nbsp; <br>");					   
	  }		   
		$("#pInput").val('');	
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
	
    for(i=0;i<savefilename_arr.length;i++) {
			   $("#displayFile").append("<div id=file" + i + ">  <a href='../uploads/" + savefilename_arr[i] + "' download='" + realname_arr[i] + "'>" +  realname_arr[i] + "</div> &nbsp;&nbsp;&nbsp;&nbsp;  " );			   
         	   $("#displayFile").append("&nbsp;<button type='button' class='btn btn-outline-danger btn-sm' id='delPic" + i + "' onclick=delPicFn('" + i + "','" +  savefilename_arr[i] + "')> <i class='bi bi-trash3-fill'></i>  </button>&nbsp; <br>");					   
	  }	   
		
}

function deleteLastchar(str)
// 마지막 문자 제거하는 함수
{
  return str = str.substr(0, str.length - 1);		
}

});
  	
document.addEventListener("DOMContentLoaded", function() {
  const textarea = document.getElementById("note");
  const textarea_aslist = document.getElementById("aslist");

  // textarea 높이를 조절하는 함수
  function adjustHeight(el) {
    el.style.height = "auto";             // 먼저 높이를 초기화
    el.style.height = el.scrollHeight + "px"; // 내용에 맞춰 높이 재설정
  }

  // 입력 이벤트 발생 시 높이 조절
  textarea.addEventListener("input", function() {
    adjustHeight(this);
  });

  // 페이지 로드시, 이미 입력된 내용이 있다면 높이 조절
  adjustHeight(textarea);
  
  // 입력 이벤트 발생 시 높이 조절
  textarea_aslist.addEventListener("input", function() {
    adjustHeight(this);
  });

  // 페이지 로드시, 이미 입력된 내용이 있다면 높이 조절
  adjustHeight(textarea_aslist);
});

</script>

</body>
</html>