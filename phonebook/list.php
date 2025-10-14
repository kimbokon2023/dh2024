<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if(!isset($_SESSION["level"]) || $_SESSION["level"]>5) {
	sleep(1);
	header("Location:" . $WebSite . "login/login_form.php"); 
	exit;
}   

include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';

// 첫 화면 표시 문구
$title_message = '발주처 주소록'; 
?>
 
<link href="css/style.css" rel="stylesheet" >   
<title> <?=$title_message?> </title>

</head>
<body>		 
<?php

// 메뉴를 표현할지 판단하는 header
$header = isset($_REQUEST['header']) ? $_REQUEST['header'] : '';  
$getmoney = isset($_REQUEST['getmoney']) ? $_REQUEST['getmoney'] : '';  
// 돌려주는 id를 기록한다.
$returnID = isset($_REQUEST['returnID']) ? $_REQUEST['returnID'] : '';  
// 인정업체는 단순히 업체 이름만 돌려준다.
$certified_company = isset($_REQUEST['certified_company']) ? $_REQUEST['certified_company'] : '';  

if($header == 'header')
	require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php');

// 배송지 띄울폼 모달
include $_SERVER['DOCUMENT_ROOT'] . '/form/order_form.php'; 

function checkNull($strtmp) {
    if ($strtmp === null || trim($strtmp) === '') {
        return false;
    } else {
        return true;
    }
}

$search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';  
$enterpress = isset($_REQUEST["enterpress"]) ? $_REQUEST["enterpress"] : '';    
$belong = isset($_REQUEST["belong"]) ? $_REQUEST["belong"] : '';    
$vendor_name = isset($_REQUEST["vendor_name"]) ? $_REQUEST["vendor_name"] : '';
$mode = isset($_REQUEST["mode"]) ? $_REQUEST["mode"] : '';

$tablename = 'phonebook';

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();
 
$a=" order by num desc";
	
if(checkNull($search))
{
	$sql = "SELECT * FROM ".$DB.".".$tablename." 
        WHERE searchtag LIKE '%$search%' AND is_deleted IS NULL " . $a;	
}
else
	{
		$sql ="select * from ".$DB.".".$tablename . " where is_deleted IS NULL "  . $a;	;	
	}

// print 'mode : ' . $mode;   
// print 'search : ' . $search;   
// print $sql;   
   
 try{  	  
	$stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh
	$total_row=$stmh->rowCount();	
?>	
<form id="board_form" name="board_form" method="post" enctype="multipart/form-data"   >		
	<input type="hidden" id="mode" name="mode" value="<?=$mode?>">             
	<input type="hidden" id="num" name="num"  > 
	<input type="hidden" id="tablename" name="tablename" value="<?=$tablename?>" > 				
	<input type="hidden" id="header" name="header" value="<?=$header?>" > 				
	<input type="hidden" id="secondordnum" name="secondordnum" value="<?=$secondordnum?>" > 				
	<input type="hidden" id="getmoney" name="getmoney" value="<?=$getmoney?>" > 				
	<input type="hidden" id="returnID" name="returnID" value="<?=$returnID?>" > 		
	<input type="hidden" id="certified_company" name="certified_company" value="<?=$certified_company?>" > 		
				
<?php if($header !== 'header') 
		{
			print '<div class="container-fluid" >	';
			print '<div class="card justify-content-center text-center mt-1" >';
		}
		else
		{
			print '<div class="container-fluid" >	';
			print '<div class="card justify-content-center text-center mt-5" >';
		}
?>	 
	<div class="card-header">
		<div class="d-flex  justify-content-center text-center align-items-center " >										
			<span class="text-center fs-5" >  <?=$title_message?>   </span>		
			<button type="button" class="btn btn-dark btn-sm mx-3"  onclick='location.reload();' title="새로고침"> <i class="bi bi-arrow-clockwise"></i> </button>  
            <small class="ms-5 text-muted"> 거래처를 검색하고 신규로 등록할 수 있습니다.(거래처별 담당자 등록은 거래처 클릭하고 +버튼을 눌러 등록합니다)
            </small>  
		</div>
		<div class="d-flex  justify-content-center text-center align-items-center mt-1 " >										
			<h5> <span class="text-center badge bg-danger" > (주의사항) 담당자 아이디 생성시 반드시 + 버튼을 이용해야 합니다.  </span>	</h5>
		</div>
	</div>
	<div class="card-body" >								
	<div class="d-flex  justify-content-center text-center align-items-center mb-2" >										
	▷ <?= $total_row ?> &nbsp; 
	<div class="inputWrap30">			
		<input type="text" id="search" class="form-control" style="width:150px;" name="search" value="<?=$search?>"  autocomplete="off" onKeyPress="if (event.keyCode==13){ enter(); }" >
		<button class="btnClear">  </button>
	</div>							
	&nbsp;&nbsp;
	<button class="btn btn-outline-dark btn-sm " type="button" id="searchBtn" > <i class="bi bi-search"></i> </button> </span> &nbsp;&nbsp;&nbsp;&nbsp;		
	
	<button id="uploadBtn" type="button" class="btn btn-dark btn-sm me-2"> <i class="bi bi-box-arrow-up"></i> 업로드 </button>	
	<button id="newBtn" type="button" class="btn btn-dark btn-sm me-2"> <i class="bi bi-pencil-square"></i> 신규 </button>	
	<?php if($header !== 'header') 
			print '<button id="closeBtn" type="button" class="btn btn-outline-dark btn-sm"> <i class="bi bi-x-lg"></i> 창닫기 </button>';
	?>			
	</div>		
		
	<div class="table-reponsive" >	
	 <table class="table table-hover">		 
			<thead class="table-primary">
			   <tr>
				 <th class="text-center" >번호</th>
				 <th class="text-center" >아이디부여 </th>
				 <th class="text-center" >고유코드 </th>
				 <th class="text-center" >거래처명</th>
				 <th class="text-center" >대표자</th>
				 <th class="text-center" >담당자</th>
				 <th class="text-center" >전화번호</th>
				 <th class="text-center text-primary w150px"> 결제일</th>				 
				 <th class="text-center text-primary w300px"> 비고</th>				 
				 <th class="text-center w130px"> 수정/삭제</th>
			 </tr>			   
			</thead>
	<tbody>		      	 
	<?php  		
		$start_num=$total_row;  			    
		while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
			include '_row.php';		
			if(empty($contact_info))
				$contact_info = $phone;
			
			if(intval($secondordnum) > 0)
				$savenum= $secondordnum;
			else
				$savenum= $num;
	?>					 
<tr onclick="maketext(
    '<?= addslashes($manager_name) ?>', 
    '<?= addslashes($representative_name) ?>', 
    '<?= addslashes($phone) ?>', 
    '<?= addslashes($vendor_name) ?>', 
    '<?= addslashes($contact_info) ?>', 
    '<?= addslashes($savenum) ?>', 
    '<?= addslashes($screendc) ?>', 
    '<?= addslashes($etcdc) ?>', 
    '<?= addslashes($controllerdc) ?>', 
    '<?= addslashes($fabricdc) ?>', 
    '<?= addslashes($num) ?>', 
    '<?= addslashes(str_replace(array("\r\n", "\n", "\r"), "\\n", $note)) ?>'   // 줄바꿈 문자 처리     
);">
	<td class="text-center" ><?= $start_num ?></td>
	<td class="text-center text-primary fw-bold" ><?= $represent ?></td>
	<td class="text-center  text-secondary" ><?= $secondordnum ?></td>
	<td class="text-start text-dark fw-bold" title="<?=$vendor_name?>"><?= $vendor_name ?></td>
	<td  class="text-center" title="<?=$representative_name?>"><?= $representative_name ?></td>
	<td class="text-center" title="<?=$manager_name?>"><?= $manager_name ?></td>
	<td class="text-center" title="<?=$contact_info?>"><?= $contact_info ?></td>
	<td class="text-center text-primary fw-bold">
		<?= $represent == '아이디부여' ? $paydate : '' ?>
	</td>
	<td class="text-start" >
		<?= $note ?>
	</td>
	<td class="text-end" >
	   <?php if(!empty($represent)) { ?>
			<button type="button" class="btn btn-dark btn-sm" onclick="addFn('<?=$num?>'); event.stopPropagation();">
				<i class="bi bi-plus-square"></i>
			</button>					
	   <?php } ?>					
			<button type="button" class="btn btn-primary btn-sm" onclick="updateFn('<?=$num?>'); event.stopPropagation();">
				 <i class="bi bi-pencil-square"></i> 
			</button>
			<button type="button" class="btn btn-danger btn-sm" onclick="delFn('<?=$num?>'); event.stopPropagation();">
				<i class="bi bi-x-circle"></i>
			</button>
	</td>
	</tr>
	<?php
	$start_num--;
	} 
	} catch (PDOException $Exception) {
	print "오류: ".$Exception->getMessage();
	}  
	?>		 
</tbody>
</table>
</div>
</div>
</div>		
</form>


<!-- 페이지로딩 -->
<script>
$(document).ready(function() {
    var loader = document.getElementById('loadingOverlay');
    if (loader) {
        loader.style.display = 'none';
    } else {
        console.warn("Element 'loadingOverlay' not found.");
    }

    if (opener && opener.document && $("#workplacename", opener.document).length) {
        $("#workplacename", opener.document).focus();
    } else {
        console.warn("Element 'workplacename' not found or 'opener' is null.");
    }
});

</script>

<script>
var ajaxRequest_write = null;
var dataTable; // DataTables 인스턴스 전역 변수
var pbpageNumber; // 현재 페이지 번호 저장을 위한 전역 변수

$(document).ready(function() {		
	
	$("#searchBtn").on("click", function() {
		$("#board_form").submit();
	});	

	$("#search_directinput").on("click", function() {
		$("#custreg_search").hide();
	});	
	// upload
	$("#uploadBtn").on("click", function() {	
		  popupCenter('uploadgrid.php' , '업로드', 1800, 800);	
	});	
	// 신규 버튼
	$("#newBtn").on("click", function() {	
		  popupCenter('./write.php' , '발주처 등록', 800, 850);	
	});	
	// 창닫기 버튼
	$("#closeBtn").on("click", function() {
		self.close();
	});	

	
});

if ($(document).length) {
    $(document).keydown(function(e){
        // keyCode 구 브라우저, which 현재 브라우저
        var code = e.keyCode || e.which;

        if (code == 27) { // 27은 ESC 키번호
            self.close();
        }
    });
}


function restorePageNumber() {
	location.reload(true);
}

// Enterkey 동작
function enter()
{
	$("#board_form").submit();	       		
}


		
	function maketext(managerName, representativeName, phone, vendorName, contact_info, savenum, screendc, etcdc, controllerdc, fabricdc, num, note)  
	{
		// note 변수의 \n을 <br>로 변환하여 줄바꿈이 HTML에서 적용되도록 처리
		if (typeof note === 'string') {
			note = note.replace(/\n/g, '<br>');
		}

        // console.log(note);
        // 인정업체 반환시는 그냥 업체명만 돌려준다.
        if($("#certified_company").val() == 'certified_company')
        {
            $("#certified_company", opener.document).val(vendorName); 
            self.close();	
        }
        

		var managerFieldID = 'secondordman'; // ID of the manager input field in the parent document
		var repFieldID = 'representative'; // ID of the representative input field in the parent document
		var phoneFieldID = 'secondordmantel'; // ID of the phone input field in the parent document
		var vendorFieldID = 'secondord'; // ID of the vendor input field in the parent document
		var vendorFieldCode = 'secondordnum'; 
		var opener_screendc = 'screen_company_dc_value'; 
		var opener_etcdc = 'company_dc_value'; 
		var opener_controllerdc = 'controller_company_dc_value'; 		
		var opener_fabricdc = 'fabric_company_dc_value'; 
		var textmsg;
        var opener_note = 'custNote';
		var header = $("#header").val();
		var getmoney = $("#getmoney").val();
		var returnID = $("#returnID").val();        
		
		// 업체코드 세팅
		$("#secondordnum").val(savenum);
		
		if(header=='header'){			
			updateFn(num);
			return;
		}
			
		if(managerName ==='') // 담당자가 없으면 대표자
		{		
			managerName = representativeName;
		}
		if(contact_info ==='') // 담당자 전화가 없으면 대표전화
		{		
			contact_info = phone;
		}
		if( getmoney == "ok")
		{
			if (opener && opener.document) {
				$("#secondord", opener.document).val(vendorName); 
				$("#secondordnum", opener.document).val(savenum); 
				// console.log(savenum);
				self.close();	
			 }
		}
		else if( returnID == "수금등록")
		{
			if (opener && opener.document) {
				$("#content_detail", opener.document).val(vendorName); 
				$("#secondordnum", opener.document).val(savenum); 
				// console.log(savenum);
				self.close();	
			 }
		}
		else if( returnID == "수입등록")
		{
			if (opener && opener.document) {
				$("#content", opener.document).val(vendorName); 
				$("#secondordnum", opener.document).val(savenum); 
				// console.log(savenum);
				self.close();	
			 }
		}
		else
		{
			if (opener && opener.document) {
				// Set the values in the parent document
				$("#" + managerFieldID, opener.document).val(managerName); 
				$("#" + repFieldID, opener.document).val(representativeName); 
				$("#" + phoneFieldID, opener.document).val(contact_info); 
				$("#" + vendorFieldID, opener.document).val(vendorName); 
				$("#" + vendorFieldCode, opener.document).val(savenum); 
				$("#" + opener_screendc, opener.document).val(screendc); 
				$("#" + opener_etcdc, opener.document).val(etcdc); 
				$("#" + opener_controllerdc, opener.document).val(controllerdc); 
				$("#" + opener_fabricdc, opener.document).val(fabricdc); 
                
                // 발주주소록 비고 적용 (수주서에 적용)
				$("#" + opener_note, opener.document).val(note); 
                					
				if (opener && !opener.closed) {
					opener.companydc();  // Calls the function `companydc()` defined in the parent window
				}
				
			}
						
			textmsg = '스크린SET DC : ' + screendc + '% <br> 철재 및 단품 DC : ' + etcdc +  '% <br> 연동제어기 DC : ' + controllerdc + '%'  +  '<br> 원단 DC : ' + fabricdc + '% 적용 ' ;

			Swal.fire({
				title: '기업할인 자동적용',
				html: textmsg,  // 여기서 'text' 대신 'html' 속성을 사용합니다.
				icon: 'info',
				confirmButtonText: '확인'
			}).then((result) => {
				if (result.isConfirmed) {					
					loadFavoritesData();	// 즐겨찾기 추가
				}
			});
		}

	 }
	
	function  updateFn(num) {	
		var header = $("#header").val();
		
		popupCenter('./write.php?num=' + num + '&header=' + header , '수정', 800, 900);	
	}
			
	function  addFn(num) {	
		popupCenter('./write.php?option=add&num=' + num , '자료 추가등록', 800, 900);	
	}
			
	function  delFn(delfirstitem) {
		console.log(delfirstitem);
		// console.log($("#board_form").serialize());
		$("#mode").val("delete");
		$("#num").val(delfirstitem);

		// DATA 삭제버튼 클릭시
			Swal.fire({ 
				   title: '해당 DATA 삭제', 
				   text: " DATA 삭제는 신중하셔야 합니다. '\n 정말 삭제 하시겠습니까?", 
				   icon: 'warning', 
				   showCancelButton: true, 
				   confirmButtonColor: '#3085d6', 
				   cancelButtonColor: '#d33', 
				   confirmButtonText: '삭제', 
				   cancelButtonText: '취소' })
				   .then((result) => { if (result.isConfirmed) { 
													
						if (ajaxRequest_write !== null) {
							ajaxRequest_write.abort();
						}		 
						ajaxRequest_write = $.ajax({
								url: "process.php",
								type: "post",		
								data: $("#board_form").serialize(),								
								success : function( data ){	

										  console.log(data);							
																			
										 Toastify({
												text: "파일 삭제 완료!",
												duration: 3000,
												close:true,
												gravity:"top",
												position: "center",
												backgroundColor: "#4fbe87",
											}).showToast();		
											
									  setTimeout(function() {
												location.reload();	
										   }, 1500);										 
													
									},
									error : function( jqxhr , status , error ){
										console.log( jqxhr , status , error );
								} 			      		
							   });												
				   } });	
	}
		
	// 자식창에서 돌아와서 이걸 실행한다
	function reloadlist() {
		const search = $("#search").val();
		$("#board_form").submit();				
	}	
</script>

<!-- 발주처 배송지 선택 모달창 -->
<script>
// ajax 중복처리를 위한 구문
var ajaxRequest = null;
var ajaxRequest1 = null;
var ajaxRequest2 = null;
var ajaxRequest3 = null;
var ajaxRequest4 = null;
var ajaxRequest5 = null;
var ajaxRequest6 = null;
var ajaxRequest7 = null;
var ajaxRequest8 = null;

$(document).ready(function() {	
    $('.closemodal').click(function() {
        // Hide the modal with the id 'deliveryModal'
        $('#deliveryModal').modal('hide');		
		self.close(); // Close the popup window after confirming
    });	
  
});

$(document).ready(function() {
    $('#saveExceptListBtn').on('click', function() {
        let selectedItems = [];
        $('.exclude-checkbox:checked').each(function() {
            selectedItems.push({
                deliverymethod: $(this).data('deliverymethod'),
                address: $(this).data('address'),
                receiver: $(this).data('receiver'),
                tel: $(this).data('tel')
            });
        });

        if (selectedItems.length > 0) {
            $.ajax({
                url: 'save_except_list.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ 
                    exceptList: selectedItems, 
                    secondordnum: $('#secondordnum').val() 
                }),
                success: function(data) {
					console.log(data);
                    if (data.success) {
                        alert('제외 리스트가 저장되었습니다.');
                        loadAllData(); // 저장 후 전체 리스트 다시 로드
                    } else {
                        alert('제외 리스트 저장에 실패했습니다.');
                    }
                },
                error: function(error) {
                    console.error('Error:', error);
                }
            });
        } else {
            alert('제외할 항목을 선택하세요.');
        }
    });
});

function loadAllData() {
    var secondordnum = document.getElementById('secondordnum').value;
    const allDeliveryInfo = document.getElementById('allDeliveryInfo');
    fetch('fetch_delivery_info.php' + '?secondordnum=' + secondordnum)
        .then(response => response.json())
        .then(data => {
            if (!Array.isArray(data.results)) {
                throw new Error('Results is not an array');
            }

            fetch('fetch_except_info.php' + '?secondordnum=' + secondordnum)
                .then(response => response.json())
                .then(exceptData => {
					console.log('전체 데이터:', data.results);
					console.log('제외 데이터:', exceptData.results);
                    if (Array.isArray(exceptData.results)) {
                        // 제외 리스트를 기준으로 전체 데이터 필터링
                        data.results = data.results.filter(item => {
                            return !exceptData.results.some(exceptItem => {
                                return item.address === exceptItem.address;
                            });
                        });
                    }
                    renderDeliveryTable(allDeliveryInfo, data.results, false);
                });
        })
        .catch(error => {
            console.error('Error parsing JSON:', error);
            alert('Error: ' + error.message);
        });
}


function loadFavoritesData() {
    var secondordnum = document.getElementById('secondordnum').value;
    const favoritesDeliveryInfo = document.getElementById('favoritesDeliveryInfo');
    fetch('fetch_favorites_info.php' + '?secondordnum=' + secondordnum)
        .then(response => response.json()) // JSON 응답으로 처리
        .then(data => {
            if (!Array.isArray(data.results)) {
                throw new Error('Results is not an array');
            }
            console.log('loadFavoritesData - results', data.results);
            console.log('loadFavoritesData - favorites', data.favorites);
            renderDeliveryTable(favoritesDeliveryInfo, data.results, true);
        })
        .catch(error => {
            console.error('Error parsing JSON:', error);
            alert('Error: ' + error.message);
        });
}

function loadExceptData() {
    var secondordnum = document.getElementById('secondordnum').value;
    const exceptDeliveryInfo = document.getElementById('exceptDeliveryInfo');
    fetch('fetch_except_info.php' + '?secondordnum=' + secondordnum)
        .then(response => response.json())
        .then(data => {
            if (!Array.isArray(data.results)) {
                throw new Error('Results is not an array');
            }
            console.log('loadExceptData - results', data.results);
            renderExceptTable(exceptDeliveryInfo, data.results, false, true);
        })
        .catch(error => {
            console.error('Error parsing JSON:', error);
            alert('Error: ' + error.message);
        });
}



function renderExceptTable(container, data, isFavoriteTab, isExceptTab) {
    var secondordnum = document.getElementById('secondordnum').value;
    let tableHTML = `<table class="table table-bordered table-hover">
        <thead class="table-secondary text-center">
            <tr>
                ${isExceptTab ? '<th>선택</th>' : ''}
                <th>즐겨찾기</th>
                <th>배송방법</th>
                <th>주소 or 지점명</th>
                <th>받는분</th>
                <th>연락처</th>
                <th>배차인 경우 정보(운송회사,차종)</th>
            </tr>
        </thead>
        <tbody class="text-center">`;

    data.forEach(item => {
        let isFavorite = item.isFavorite ? '★' : '☆';
        tableHTML += `<tr onclick="selectNum('${item.deliverymethod}', '${item.address}', '${item.receiver}', '${item.tel}', '${item.chargedman}', '${item.chargedmantel}', '${item.delcaritem}', '${item.delcompany}')">
            ${isExceptTab ? `<td><input type="checkbox" onchange="toggleExcept('${secondordnum}', '${item.deliverymethod}', '${item.address}', '${item.receiver}', '${item.tel}')"></td>` : ''}
            <td class="favorite" onclick="event.stopPropagation(); toggleFavorite('${secondordnum}', '${item.deliverymethod}', '${item.address}', '${item.receiver}', '${item.tel}', '${item.carinfo}', '${item.delcompany}', '${item.chargedman}', '${item.chargedmantel}', '${item.delcaritem}', '${item.delbranch}', ${isFavoriteTab})">${isFavorite}</td>
            <td>${item.deliverymethod}</td>
            <td>${item.address}</td>
            <td>${item.receiver}</td>
            <td>${item.tel}</td>
            <td>${item.carinfo}</td>
        </tr>`;
    });

    tableHTML += `</tbody></table>`;
    container.innerHTML = tableHTML;

    // 주문서 모달 뒷배경 눌렀을때 닫히지 않게 하기
    $('#deliveryModal').modal({
        backdrop: 'static', keyboard: false
    });

    $("#deliveryModal").modal("show");
}


    function renderDeliveryTable(container, data, isFavoriteTab) { 
        const secondordnum = document.getElementById('secondordnum').value;
        console.log('renderDeliveryTable - secondordnum', secondordnum);
        let tableHTML = `<table class="table table-bordered table-hover">
            <thead class="table-secondary text-center">
                <tr>
				    <th>선택</th>
                    <th>즐겨찾기</th>
                    <th>배송방법</th>
                    <th>주소 or 지점명</th>
                    <th>받는분</th>
                    <th>연락처</th>
                    <th>배차인 경우 정보(운송회사,차종)</th>
                </tr>
            </thead>
            <tbody class="text-center">`;

        let uniqueData = {};
        let counter = 0;
        data.forEach(item => {
            var address = '';
            var receiver = '';
            var tel = '';
            var carinfo = '';
            var delcompany = '';

            if (item.deliverymethod === '배차') {
                address = item.address;
                tel = item.chargedmantel;
                receiver = item.chargedman;
                carinfo = '(' + item.delcompany + ') 차량종류(' + item.delcaritem + ')';
                delcompany = item.delcompany;
            } else if (['선/택배','착/택배', '직배송', '직접 수령', '경동공장 입고'].includes(item.deliverymethod)) {
                address = item.address;
                tel = item.chargedmantel;
                receiver = item.chargedman;
                delcompany = '';
            } else if (item.deliverymethod.includes('화물')) {
                address = item.delbranch;
                tel = item.chargedmantel;
                receiver = item.chargedman;
                delcompany = '';
            }
            let uniqueKey = `${address}-${receiver}-${tel}-${item.deliverymethod}`;
            if (!uniqueData[uniqueKey]) {
                uniqueData[uniqueKey] = {
                    secondordnum: secondordnum,
                    deliverymethod: item.deliverymethod,
                    address: address,
                    receiver: receiver,
                    tel: tel,
                    carinfo: carinfo,
                    delcompany: delcompany,
                    isFavorite: isFavoriteTab ? true : false,
                    chargedman: item.chargedman,  // 추가
                    chargedmantel: item.chargedmantel,  // 추가
                    delcaritem: item.delcaritem,  // 추가
                    delbranch: item.delbranch  // 추가
                };
                counter++;
            }
        });

        Object.values(uniqueData).forEach(item => {
            let isFavorite = item.isFavorite ? '★' : '☆';
            tableHTML += `<tr>
				<td><input type="checkbox" class="exclude-checkbox" data-deliverymethod="${item.deliverymethod}" data-address="${item.address}" data-receiver="${item.receiver}" data-tel="${item.tel}"></td>
                <td class="favorite" onclick="toggleFavorite('${item.secondordnum}', '${item.deliverymethod}', '${item.address}', '${item.receiver}', '${item.tel}', '${item.carinfo}', '${item.delcompany}', '${item.chargedman}', '${item.chargedmantel}', '${item.delcaritem}', '${item.delbranch}', ${isFavoriteTab})">${isFavorite}</td>
                <td onclick="selectNum('${item.deliverymethod}', '${item.address}', '${item.receiver}', '${item.tel}', '${item.chargedman}', '${item.chargedmantel}', '${item.delcaritem}', '${item.delcompany}');">${item.deliverymethod}</td>
                <td onclick="selectNum('${item.deliverymethod}', '${item.address}', '${item.receiver}', '${item.tel}', '${item.chargedman}', '${item.chargedmantel}', '${item.delcaritem}', '${item.delcompany}');">${item.address}</td>
                <td onclick="selectNum('${item.deliverymethod}', '${item.address}', '${item.receiver}', '${item.tel}', '${item.chargedman}', '${item.chargedmantel}', '${item.delcaritem}', '${item.delcompany}');">${item.receiver}</td>
                <td onclick="selectNum('${item.deliverymethod}', '${item.address}', '${item.receiver}', '${item.tel}', '${item.chargedman}', '${item.chargedmantel}', '${item.delcaritem}', '${item.delcompany}');">${item.tel}</td>
                <td onclick="selectNum('${item.deliverymethod}', '${item.address}', '${item.receiver}', '${item.tel}', '${item.chargedman}', '${item.chargedmantel}', '${item.delcaritem}', '${item.delcompany}');">${item.carinfo}</td>
            </tr>`;
        });

        tableHTML += `</tbody></table>`;
        container.innerHTML = tableHTML; // 한 번에 모든 HTML을 추가

        // 주문서 모달 뒷배경 눌렀을때 닫히지 않게 하기
        $('#deliveryModal').modal({
            backdrop: 'static', keyboard: false
        });

        $("#deliveryModal").modal("show");
    }

    $('#favorites-tab').on('shown.bs.tab', function () {
        loadFavoritesData();
    });

    $('#all-tab').on('shown.bs.tab', function () {
        loadAllData();
    });   

	$('#except-tab').on('shown.bs.tab', function () {
		loadExceptData();
	});

    
// 즐겨찾기 기능 구현 함수
function toggleFavorite(secondordnum, deliverymethod, address, receiver, tel, carinfo, delcompany, chargedman, chargedmantel, delcaritem, delbranch, isFavoriteTab = false) {
    const payload = {
        secondordnum: parseInt(secondordnum) || 0,
        deliverymethod: deliverymethod || '',
        address: address || '',
        receiver: receiver || '',
        tel: tel || '',
        carinfo: carinfo || '',
        delcompany: delcompany || '',
        chargedman: chargedman || '',
        chargedmantel: chargedmantel || '',
        delcaritem: delcaritem || '',
        delbranch: delbranch || ''
    };

    console.log('toggleFavorite 호출값 :', payload);
    console.log('toggleFavorite JSON string:', JSON.stringify(payload));

    $.ajax({
        url: './toggle_favorite.php',
        type: 'POST',
        // contentType을 지정하지 않음(기본: application/x-www-form-urlencoded)
        // processData 기본값 true 유지
        data: { data: JSON.stringify(payload) },  // 폼 필드로 JSON 문자열 전송
        dataType: 'json',
        timeout: 10000,
        success: function (result) {
            console.log('toggleFavorite 응답 데이터:', result);

            if (result.error) {
                display(`오류: ${result.error}`);
                return;
            }

            if (result.success) {
                if (isFavoriteTab) {
                    loadFavoritesData();
                    display('즐겨찾기에서 제거되었습니다.');
                } else {
                    loadAllData();
                    display('즐겨찾기 추가되었습니다.');
                }
            } else {
                display('처리 결과를 확인할 수 없습니다.');
            }
        },
        error: function (xhr, status, error) {
            console.error('toggleFavorite 오류:', error);
            console.error('상태 코드:', xhr.status);
            console.error('상태 텍스트:', xhr.statusText);
            console.error('서버 응답:', xhr.responseText);
            console.error('요청 URL:', xhr.responseURL);
            console.error('응답 헤더:', xhr.getAllResponseHeaders());

            let errorMessage = '서버와 통신 중 오류가 발생했습니다.';
            if (xhr.status === 400) {
                errorMessage = '잘못된 요청입니다. (400 Bad Request)';
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.error) errorMessage = `요청 오류: ${response.error}`;
                } catch (e) {
                    errorMessage = `요청 오류: ${xhr.responseText}`;
                }
            } else if (xhr.status === 500) {
                errorMessage = '서버 내부 오류입니다. (500 Internal Server Error)';
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.error) errorMessage = `서버 오류: ${response.error}`;
                } catch (e) {
                    errorMessage = `서버 오류: ${xhr.responseText}`;
                }
            } else if (xhr.status === 0) {
                errorMessage = '네트워크 연결 오류입니다.';
            } else if (xhr.status === 404) {
                errorMessage = '요청한 파일을 찾을 수 없습니다. (404 Not Found)';
            } else if (xhr.status === 'timeout') {
                errorMessage = '요청 시간이 초과되었습니다. (10초)';
            }

            display(errorMessage);
        }
    });
}
    

    function selectNum(deliverymethod,address,receiver,tel,chargedman, chargedmantel, delcaritem, delcompany) {
        $("#deliverymethod", opener.document).val(deliverymethod); 	

        opener.showFields();

        if (deliverymethod === '배차') {
            $("#address", opener.document).val(address); 
            $("#chargedman", opener.document).val(receiver); 
            $("#chargedmantel", opener.document).val(tel); 
            $("#delcaritem", opener.document).val(delcaritem); 
            $("#delcompany", opener.document).val(delcompany); 
        } else if (['선/택배', '착/택배', '직배송', '직접 수령', '경동공장 입고'].includes(deliverymethod)) {
            $("#address", opener.document).val(address); 
            $("#chargedman", opener.document).val(receiver); 
            $("#chargedmantel", opener.document).val(tel); 		
        } else if (deliverymethod.includes('화물')) {
            $("#address", opener.document).val(address); 
            $("#chargedman", opener.document).val(receiver); 
            $("#chargedmantel", opener.document).val(tel); 
            $("#delbranch", opener.document).val(address); 
        }

        Toastify({
            text: "배송정보 적용완료",
            duration: 2000,
            close: true,
            gravity: "top",
            position: "center",
            style: {
                background: "linear-gradient(to right, #00b09b, #96c93d)",
                fontSize: '25px' // 글씨 크기를 20px로 설정
            },
        }).showToast();

        setTimeout(function(){	
			$("#workplacename", opener.document).focus(); // 포커스 설정		
            self.close();
        }, 1500);	
    }

    function display(text) {								
        Toastify({
            text: text,
            duration: 2000,
            close: true,
            gravity: "top",
            position: "center",
            style: {
                background: "linear-gradient(to right, #00b09b, #96c93d)",
                fontSize: '20px' // 글씨 크기를 20px로 설정
            },
        }).showToast();
    }


</script>



</body>
</html>
