<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

// 발주처 화면으로 코드 수정할시 처리사항
// 1. write_form.php -> write_form_sub.PHP
// 2. 발주처 선택하는 화면 구성 (담당자 및 배송지 선택) php 및 자바스크립트 추가해야 함.
// 3. insert.php -> insert_sub.php 수정    대한에서만 보는 비고란 저장로직에서 뺌
// 4. 삭제시 접수대기일때만 가능하게 한다.
// 5. 사진등록 기능을 보는것만 가능하게 수정
// 6. 수정 버튼을 누르면 '접수대기'인 상태만 동작하기


include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';

$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  
$num = isset($_REQUEST["num"]) ? $_REQUEST["num"] : "";

$first_writer = '';

if($mode === 'copy')
	$title_message = "(데이터복사) 모터 수주" ;
else if($mode === 'split')  
	$title_message = "(분할 & 복사) 모터 수주" ;
else
	$title_message = "DH모터 수주" ;

$tablename = 'motor';
 ?>
 
<link href="css/style.css?v=1" rel="stylesheet" >    
<title> <?=$title_message?> </title>


<style>
.tooltip-inner {
    background-color: black !important; /* 배경색 */
    color: white !important; /* 글자색 */
}
.tooltip-arrow {
    color: black !important; /* 화살표 색상 */
}

/* 입력창에 대한 설계 */
.ui-autocomplete {
	max-height: 200px;
	overflow-y: auto;
	overflow-x: hidden;
}

.specialinputWrap {
	position: relative;
	display: inline-block;
	width:100%;
}

.specialbtnClear {
	position: absolute;
	right: 5px;
	top: 50%;
	transform: translateY(-50%);
	background: transparent;
	border: none;
	color: red;
	cursor: pointer;
}

.specialbtnClear:before {
	content: '✖';
	font-size: 12px;
}

.specialbtnClear:hover {
	color: darkred;
}
</style>

</head>

<body>  

<?   

// 업체용 화면에 필요한 것 
include $_SERVER['DOCUMENT_ROOT'] . '/mymodal.php';
// 배송지 띄울폼 모달
include $_SERVER['DOCUMENT_ROOT'] . '/form/order_form.php'; 
// 발주처 정보 form
include $_SERVER['DOCUMENT_ROOT'] . '/form/secondord_form.php'; 
  
// 첨부 이미지에 대한 부분
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();	
 
$URLsave = "https://dh2024.co.kr/motorloadpic.php?num=" . $num;  

$todate = date("Y-m-d"); // 현재일자 변수지정
  
  if ($mode=="modify" || $mode=="view"){
    try{
	    $sql = "select * from " . $DB . ".{$tablename}  where num = ? ";
	    $stmh = $pdo->prepare($sql); 
		$stmh->bindValue(1,$num,PDO::PARAM_STR); 
		$stmh->execute();
		$count = $stmh->rowCount();              
    if($count<1){  
      print "검색결과가 없습니다.<br>";
     }else{
      $row = $stmh->fetch(PDO::FETCH_ASSOC);	  
		include "_row.php";

	 }

     }catch (PDOException $Exception) {
       print "오류: ".$Exception->getMessage();
     }
 
  }
  
if ($mode !== "modify" and $mode !== "copy" and $mode !== "view"  ) {
    
	include '_request.php';
	$first_writer = $user_name ;
	$secondordnum = $_SESSION["secondordnum"];

}

  if ($mode=="copy" ){
    try{
      $sql = "select * from " . $DB . ".{$tablename}  where num = ? ";
      $stmh = $pdo->prepare($sql); 

    $stmh->bindValue(1,$num,PDO::PARAM_STR); 
      $stmh->execute();
      $count = $stmh->rowCount();              
    if($count<1){  
		print "검색결과가 없습니다.<br>";
     }else{
		$row = $stmh->fetch(PDO::FETCH_ASSOC);
	 }  
		include '_row.php';
		
     }catch (PDOException $Exception) {
       print "오류: ".$Exception->getMessage();
     }
	 
	// 자료번호 초기화 
	$num = 0;		 
	// 등록일 오늘로 자동등록
	$registerdate = $today;	
	$orderdate = null;
	 
  }
         
// 주자재 단가표 읽어오기        
$sql = "SELECT * FROM " . $DB . ".fee where is_deleted is NULL ORDER BY basicdate DESC LIMIT 1";

try {
    $stmh = $pdo->query($sql);
    $row = $stmh->fetch(PDO::FETCH_ASSOC);
    // var_dump($row);

	// Decode JSON strings to PHP arrays (or parse however these are structured)
	$wires = json_decode($row['wire']);
	$volts = json_decode($row['volt']);
	$items = json_decode($row['item']);
	$upweights = json_decode($row['upweight']);
	$units = json_decode($row['unit']);
	$prices = json_decode($row['price']);
	
	// Use array_filter to remove empty entries from the array
	$non_empty_items = array_filter($items, function($value) {
		return !empty($value) && $value !== null && $value !== '';
	});	

	$unitnames = [];
	$unitprices = [];

	for ($i = 0; $i < count($non_empty_items); $i++) {
		// Combine the strings for unitname and remove all spaces
		$combinedString =  $volts[$i] . $wires[$i] . $items[$i] . $upweights[$i] . $units[$i];
		$cleanString = str_replace(' ', '', $combinedString);  // Remove spaces
		$upperString = strtoupper($cleanString);  // Convert to uppercase

		// Append the processed string to the unitnames array
		$unitnames[] = $upperString;

		// Assume $prices is already an array of integers/floats
		$unitprices[] = $prices[$i];
	}
		
	$priceData = [];  // Initialize an empty array to hold the price data

	for ($i = 0; $i < count($non_empty_items); $i++) {
		// Combine the strings for unitname and remove all spaces
		$volts[$i] =  strtoupper(str_replace(' ', '', $volts[$i])); 
		$wires[$i] =  strtoupper(str_replace(' ', '', $wires[$i])); 
		$items[$i] =  strtoupper(str_replace(' ', '', $items[$i])); 
		$upweights[$i] =  strtoupper(str_replace(' ', '', $upweights[$i])); 
		$units[$i] =  strtoupper(str_replace(' ', '', $units[$i])); 

		if (!isset($priceData[$volts[$i]])) {
			$priceData[$volts[$i]] = [];
		}
		if (!isset($priceData[$volts[$i]][$wires[$i]])) {
			$priceData[$volts[$i]][$wires[$i]] = [];
		}
		if (!isset($priceData[$volts[$i]][$wires[$i]][$items[$i]])) {
			$priceData[$volts[$i]][$wires[$i]][$items[$i]] = [];
		}
		if (!isset($priceData[$volts[$i]][$wires[$i]][$items[$i]][$upweights[$i]])) {
			$priceData[$volts[$i]][$wires[$i]][$items[$i]][$upweights[$i]] = [];
		}

		// Now set the price for this specific configuration
		$priceData[$volts[$i]][$wires[$i]][$items[$i]][$upweights[$i]][$units[$i]] = $prices[$i];
	}

	// Encode the price data array into JSON to be used in JavaScript
	// print_r(json_encode($priceData));	

	// Now $unitnames and $unitprices are populated as required
	// print_r($unitnames);
	// print_r($unitprices);	
} catch (PDOException $Exception) {
    print "오류: " . $Exception->getMessage();
}

	
// 시공전후 데이터 파일경로등을 읽어오는 구문
    $picsGroupedByItem = [];
    $tablename = 'motor';
    $item = 'beforeArr';

    // 모든 사진을 한 번의 쿼리로 가져오기
    $sql = "SELECT * FROM " . $DB . ".picuploads WHERE tablename = '$tablename' AND parentnum = '$num'";

    try {
        $stmh = $pdo->query($sql);
        while ($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
            $picsGroupedByItem[$row['item']][] = $row;
        }
    } catch (PDOException $Exception) {
        print "오류: " . $Exception->getMessage();
    }

    // 각 아이템별로 사진 개수 세기
    $picNum = isset($picsGroupedByItem['before']) ? count($picsGroupedByItem['before']) : 0;
    $MidpicNum = isset($picsGroupedByItem['mid']) ? count($picsGroupedByItem['mid']) : 0;
    $AfterpicNum = isset($picsGroupedByItem['after']) ? count($picsGroupedByItem['after']) : 0;

    // 사진명만을 포함하는 배열 생성
    $picData = isset($picsGroupedByItem['before']) ? array_column($picsGroupedByItem['before'], 'picname') : [];
    $MidpicData = isset($picsGroupedByItem['mid']) ? array_column($picsGroupedByItem['mid'], 'picname') : [];
    $AfterpicData = isset($picsGroupedByItem['after']) ? array_column($picsGroupedByItem['after'], 'picname') : [];
	
    // 목록 구성된 사진배열
    $picDataArr = isset($picsGroupedByItem['beforeArr']) ? array_column($picsGroupedByItem['beforeArr'], 'picname') : [];
    $MidpicDataArr = isset($picsGroupedByItem['midArr']) ? array_column($picsGroupedByItem['midArr'], 'picname') : [];
    $AfterpicDataArr = isset($picsGroupedByItem['afterArr']) ? array_column($picsGroupedByItem['afterArr'], 'picname') : [];
	
	$picIdx = isset($picsGroupedByItem['beforeArr']) ? array_column($picsGroupedByItem['beforeArr'], 'idx') : [];
	$MidpicIdx = isset($picsGroupedByItem['midArr']) ? array_column($picsGroupedByItem['midArr'], 'idx') : [];
	$AfterpicIdx = isset($picsGroupedByItem['afterArr']) ? array_column($picsGroupedByItem['afterArr'], 'idx') : [];
		 
		 
// 제품금액 합계표 (할인 적용하지 않은 금액)
$sumrawprice = 0;
// Remove commas and convert to integers (if you're sure these are whole numbers)
$sumrawprice = intval(str_replace(',', '', $price)) + intval(str_replace(',', '', $screen_price));

// 비할인 제품 초기화
$notdcprice_dummy= 0;	

$powerOptions = ['220', '380'];
$capacityOptions = ['150k', '300k', '400k', '500k', '600k', '800k', '1000k', '1500k', '2000k'];
$unitOptions = ['SET', '모터단품', '브라켓트'];
$bracketSizeOptions = ['380*180','530*320', '600*350', '690*390', '910*600'];
$flangeSizeOptions = ['2-4″', '2-5″', '2-6″', '3-4″', '3-5″', '3-6″', '4-5″', '4-6″'];
$flangeSizeOptionsAlt = ['4″', '5″', '6″', '8″', '10″'];
$securityOptions = ['스크린','철재','방범', '제연', '방폭'];
$wirelessOptions = ['유선', '무선'];

// 연동제어기 단가표의 배열 가져오기
$sql = "select * from " . $DB . ".fee_controller  where is_deleted is NULL order by basicdate desc limit 1";

try {
    $stmh = $pdo->query($sql);
    $rows = $stmh->fetchAll(PDO::FETCH_ASSOC); // 모든 데이터를 한 번에 가져옴
    $total_row = count($rows); // 가져온 행의 수를 계산

    $rows = array_reverse($rows); // 배열을 역순으로 정렬

    foreach ($rows as $row) {
        // 각 행에 대한 JSON 데이터를 디코드하고 필요에 따라 필터링
        $sub_item = array_filter(json_decode($row['item'], true) ?? [], function ($value) {
            return trim($value) !== '';
        });
    }
} catch (PDOException $Exception) {
    echo "오류: " . $Exception->getMessage();
}

array_unshift($sub_item, ''); // 배열의 맨 앞에 빈 문자열 추가

$controllerOptions = $sub_item;

// 부속자재에 대한 배열 가져오기
$sql = "select * from " . $DB . ".fee_sub  where is_deleted is NULL  order by basicdate desc limit 1";

try {
    $stmh = $pdo->query($sql);
    $rows = $stmh->fetchAll(PDO::FETCH_ASSOC); // 모든 데이터를 한 번에 가져옴
    $total_row = count($rows); // 가져온 행의 수를 계산

    $rows = array_reverse($rows); // 배열을 역순으로 정렬

    foreach ($rows as $row) {
        // 각 행에 대한 JSON 데이터를 디코드하고 필요에 따라 필터링
        $sub_item = array_filter(json_decode($row['item'], true) ?? [], function ($value) {
            return trim($value) !== '';
        });

        // // 필요한 배열 구조로 $item 변형
        // $item = [
            // 'controllerOptions' => $item, // 원하는 추가 데이터 포함
            // 'is_dc' => json_decode($row['is_dc'], true) ?? [],
            // 'originalcost' => json_decode($row['originalcost'], true) ?? [],
            // 'price' => json_decode($row['price'], true) ?? []
        // ];

        // // 사용 예: $item 배열을 활용
        // // 예를 들어, $item 배열의 내용을 출력
        // echo "<pre>";
        // print_r($sub_item);
        // echo "</pre>";
    }
} catch (PDOException $Exception) {
    echo "오류: " . $Exception->getMessage();
}

array_unshift($sub_item, ''); // 배열의 맨 앞에 빈 문자열 추가

$subOptions = $sub_item;

// 할인총액 계산하는 루틴 콤마를 제거하고 숫자로 변환
$totalprice = str_replace(',', '', $totalprice);
$dctotal = str_replace(',', '', $dctotal);
$dcadd = str_replace(',', '', $dcadd);

// 숫자 연산을 위해 float로 변환
$totalprice = floatval($totalprice);
$dctotal = floatval($dctotal);
$dcadd = floatval($dcadd);

// 연산 수행
$afterdctotal =number_format( $totalprice - $dctotal - $dcadd );

// 배차회사 기본 지정할
if(empty($delcompay))
	$delcompany = '25시콜';

?>

<form id="board_form" name="board_form" method="post" enctype="multipart/form-data"  onkeydown="return captureReturnKey(event)"  >	
		      
<input type="hidden" id="first_writer" name="first_writer" value="<?=$first_writer?>"  >
<input type="hidden" id="update_log" name="update_log" value="<?=$update_log?>"  >
<input type="hidden" id="num" name="num" value="<?=$num?>"  >
<input type="hidden" id="tablename" name="tablename" value="<?=$tablename?>"  >
<input type="hidden" id="item" name="item" value="<?=$item?>"  >
<input type="hidden" id="mode" name="mode" value="<?=$mode?>"  >
<input type="hidden" id="secondordnum" name="secondordnum" value="<?=$secondordnum?>"  >  <!-- 발주처의 코드 기록 -->
<input type="hidden" id="orderlist" name="orderlist" >
<input type="hidden" id="accessorieslist" name="accessorieslist" >
<input type="hidden" id="controllerlist" name="controllerlist" >
<input id="pInput" name="pInput" type="hidden" value="0" >	

<div class="container-fluid">	  

	<div id="overlay" style="display: none; position: fixed; width: 100%; height: 100%; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.5); z-index: 2; cursor: pointer;">
	</div>
	<div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="false">
		<!-- 토스트 내용 -->
		사진을 저장중입니다. 잠시만 기다려주세요.
	</div>

<div class="card">	  
	<div class="card-body">	  
		<div class="d-flex justify-content-center align-items-center mt-3 mb-2 ">		
		<span class="fs-5 me-5">  <?=$title_message?>  (<?=$mode?>) </span>           
		<!-- 이 부분 조회 화면과 다름 -->   	
		<?php if($mode !=='view') { 
			print '<button id="saveBtn" type="button" class="btn btn-dark  btn-sm me-2"  >  <i class="bi bi-floppy-fill"></i> 저장    </button> ';
		}
			else  { ?>	
				<button type="button" class="btn btn-dark btn-sm me-1" id="modifyBtn">
					<ion-icon name="color-wand-outline"></ion-icon> 수정 </button>
				<button type="button" class="btn btn-danger btn-sm me-1" id="deleteBtn" >   <i class="bi bi-trash"></i> 삭제  </button>
				<button type="button" class="btn btn-dark btn-sm me-1" onclick="location.href='write_form_sub.php';" >  <i class="bi bi-pencil"></i>  신규 </button>
				<button type="button" class="btn btn-primary btn-sm me-1" onclick="location.href='write_form_sub.php?mode=copy&num=<?=$num?>';" >  <i class="bi bi-copy"></i> 복사</button>
				<button type="button" class="btn btn-success btn-sm me-1" onclick="popupCenter('invoice.php?num=<?=$num?>','출고증 인쇄',800,900);" > <i class="bi bi-printer"></i> 출고증 </button>
				<button type="button" class="btn btn-success btn-sm me-1" onclick="popupCenter('invoice_sales.php?num=<?=$num?>','거래명세서',1000,900);" > <i class="bi bi-printer"></i> 거래명세 </button>	
			<?php } ?>
		
		&nbsp;&nbsp;
		
		최초 : <?=$first_writer?> 
		<br>    
		  <?php
				  $update_log_extract = substr($update_log, 0, 31); 
		  ?>
		&nbsp;&nbsp; 수정 : <?=$update_log_extract?> &nbsp;&nbsp;&nbsp;
			
		 <span class="text-end" style="width:10%;" >	
				<button type="button" class="btn btn-outline-dark btn-sm me-2" id="showlogBtn"   > H
				</button>		
			<button class="btn btn-secondary btn-sm" onclick="self.close();" > <i class="bi bi-x-lg"></i> 닫기 </button>&nbsp;	
		 </span>
		</div> 		
	<div class="d-flex row justify-content-center mt-1 p-2 rounded"  style=" border: 2px solid #392f31; "> 			
		<table class="table table-bordered ">		 
	  <tbody>
		<tr>
		  <td >발주처</td>
		  <td > 
			<div class="d-flex align-items-center justify-content-center"> 
				<input type="text" id="secondord" name="secondord" value="<?=$secondord?>" class="form-control"  autocomplete="off"  style="width:70%;"  onkeydown="if(event.keyCode == 13) { phonebookBtn('secondord'); }"  > &nbsp;
				<button type="button" class="btn btn-dark-outline btn-sm restrictbtn" onclick="phonebookBtn('secondord');">  <ion-icon name="settings-outline"></ion-icon> </button>
			</div>
		  </td>
		  <td>
		  <div class="d-flex align-items-center justify-content-center"> 
			   담당자 &nbsp;
			  <input type="text" id="secondordman" name="secondordman" class="form-control"  autocomplete="off"  style="width:50%;" value="<?=$secondordman?>" onkeydown="if(event.keyCode == 13) { phonebookBtn('secondordman'); }">				  
		  </div>
			  
		  </td>
		  <td>
		  <div class="d-flex align-items-center justify-content-center"> 
			 <i class="bi bi-telephone-forward-fill"></i> &nbsp;&nbsp;
			<input type="text" id="secondordmantel" name="secondordmantel" value="<?=$secondordmantel?>"  autocomplete="off"  class="form-control" style="width:70%;"  onkeydown="if(event.keyCode == 13) { phonebookBtn('secondordmantel'); }"></td>			  
		   </div>
		  
		  <td colspan="3">
			<div class="d-flex align-items-center justify-content-center">
				<span id="status-badge" class="fw-bold fs-6"  data-bs-toggle="tooltip" data-bs-placement="right" title="접수대기: 업체 발주서 등록, 접수확인: 대한직원 접수확인, 준비중: (출고예정일 등록) 포장전, 출고대기: 포장완료 상태 , &nbsp;&nbsp; 출고완료: 상차,화물운송, 택배 등 사진첨부">
					진행상태 
				</span> &nbsp;
				<span id="status-span" class="badge fs-6"></span> &nbsp;

				<select id="status" name="status" class="form-control" style="width: 75%;">					
					<option value="접수대기" <?= $status == '접수대기' ? 'selected' : '' ?>>접수대기: 업체에서 발주서 발송</option>
					<option value="접수확인" <?= $status == '접수확인' ? 'selected' : '' ?>>접수확인: 대한직원이 접수일 등록</option>
					<option value="준비중" <?= $status == '준비중' ? 'selected' : '' ?>>준비중: (출고예정 등록시) 포장전 상태</option>
					<option value="출고대기" <?= $status == '출고대기' ? 'selected' : '' ?>>출고대기: 포장완료 (출고담당) ☑</option>
					<option value="출고완료" <?= $status == '출고완료' ? 'selected' : '' ?>>출고완료: 상차 후 사진 첨부, 택배/화물 송장 첨부하면 출고완료 ☑</option>
				</select>
			</div>
		  
		  </td>							  			  
		</tr>	
	</tbody>
</table>
<div class="d-flex row justify-content-center p-1"> 	
	<div class="col-sm-4 rounded p-1 " > 	
	<table class="table table-bordered ">		 
		  <tbody>
			<tr>
				<td colspan="1" style="width:80px;"  >
				현장명
				</td>
				<td colspan="2" class="text-start"  style="width:200px;"><input type="text" id="workplacename" name="workplacename" value="<?=$workplacename?>"  autocomplete="off"  class="form-control text-start" required></td>							  			  
			</tr>   
		  </tbody>
		</table>	

			<table class="table table-bordered">		 
			  <tbody>
				<tr>
				   <td rowspan="3" style="width:80px;" data-bs-toggle="tooltip" data-bs-placement="top" title="발주처가 등록, 추후 발주량 예측"> 
				   현장<br> 출고수량 
				   </td>
				  
					<td  data-bs-toggle="tooltip" data-bs-placement="bottom" title="전체 발주한 수량을 의미함" > 				   
					  <div class="col-sm-10" > 
						  <div class="d-flex align-items-center justify-content-end" > 					   
								전체 수량 합  &nbsp; <input type="text" id="order_total" name="order_total" value="<?=$order_total?>" readonly style="width:30px;"  class="form-control calculate_set" >
							</div>
						</div>
					</td>					  

				  <td  > 스크린M  <br> 
						<div class="d-flex align-items-center justify-content-center"> 
							<input type="text" id="screensu" name="screensu" value="<?=$screensu?>"   style="width:30px;"  autocomplete="off" onkeyup="inputNumberFormat(this)"  class=" form-control calculate_set" >
						</div>
						</td>  						  
				  <td  > 철재M <br>  
						<div class="d-flex align-items-center justify-content-center"> 
							<input type="text" id="steelsu" name="steelsu" value="<?=$steelsu?>"   style="width:30px;"  autocomplete="off" onkeyup="inputNumberFormat(this)"  class=" form-control calculate_set" ></td>  							  
						</div>
				  <td  > 방범M  <br> 
						<div class="d-flex align-items-center justify-content-center"> 			  
							<input type="text" id="protectsu" name="protectsu" value="<?=$protectsu?>"   style="width:30px;"  autocomplete="off" onkeyup="inputNumberFormat(this)"  class="form-control calculate_set" ></td>  							  
						</div>	
				  <td  > 제연M <br>  
						<div class="d-flex align-items-center justify-content-center"> 			  
							<input type="text" id="smokesu" name="smokesu" value="<?=$smokesu?>"   style="width:30px;"  autocomplete="off" onkeyup="inputNumberFormat(this)"  class=" form-control calculate_set" ></td>  						  
						</div>
				  <td  > 방폭M <br> 
						<div class="d-flex align-items-center justify-content-center"> 			  
							<input type="text" id="explosionsu" name="explosionsu" value="<?=$explosionsu?>"  style="width:30px;"  autocomplete="off" onkeyup="inputNumberFormat(this)"  class=" form-control calculate_set" ></td>  						  
						</div>
				</tr>			
				<tr>			  
				  <td colspan="1" class="text-center fw-bold"   data-bs-toggle="tooltip" data-bs-placement="bottom" title="보고 있는 수주내역에 발주된 수량" >  
					<div class="col-sm-10"> 
						<div class="d-flex align-items-center justify-content-end"> 
								발주 수량 합 &nbsp; <input type="text" id="realendsu" name="realendsu"  readonly  style="width:30px;" class="form-control text-primary calculate_set"> </td>	
						</div>
					</div>
				  <td class="text-center">  
					  <div class="d-flex align-items-center justify-content-center"> 	
						<input type="text" id="realscreensu" name="realscreensu"  value="<?=$realscreensu?>"  style="width:30px;"  readonly onkeyup="inputNumberFormat(this)" class=" form-control text-primary calculate_set"></td>  						  
					  </div>					
				  <td class="text-center"> 
						<div class="d-flex align-items-center justify-content-center"> 
							<input type="text" id="realsteelsu" name="realsteelsu"  value="<?=$realsteelsu?>"  style="width:30px;"  readonly onkeyup="inputNumberFormat(this)" class=" form-control text-primary calculate_set"></td>  							  
						</div>
				  <td class="text-center"> 
						<div class="d-flex align-items-center justify-content-center"> 
							<input type="text" id="realprotectsu" name="realprotectsu"   value="<?=$realprotectsu?>"  style="width:30px;"  readonly onkeyup="inputNumberFormat(this)" class=" form-control text-primary calculate_set"></td>  							  
						</div>
				  <td class="text-center"> 
						<div class="d-flex align-items-center justify-content-center"> 
							<input type="text" id="realsmokesu" name="realsmokesu"   value="<?=$realsmokesu?>" style="width:30px;"  readonly onkeyup="inputNumberFormat(this)" class=" form-control text-primary calculate_set"></td>  						  
						</div>
				  <td class="text-center"> 
						<div class="d-flex align-items-center justify-content-center"> 
							<input type="text" id="realexplosionsu" name="realexplosionsu"  value="<?=$realexplosionsu?>"  style="width:30px;"  readonly onkeyup="inputNumberFormat(this)" class=" form-control text-primary calculate_set"></td>  						  
						</div>
				  
				</tr>		
				<tr>			  
				  <td colspan="1" class="text-center fw-bold"   data-bs-toggle="tooltip" data-bs-placement="right" title="발주된 수량에서 수주한 수량을 뺀 수량" > 
					<div class="col-sm-10"> 
						  <div class="d-flex align-items-center justify-content-end"> 
							미출고 수량 합 &nbsp; <input type="text" id="noendsu" name="noendsu"  readonly  value="<?=$noendsu?>" style="width:30px;" class=" form-control text-danger calculate_set"> 
						 </div>
					</div>
				  </td>	
				  <td class="text-center">  
						<div class="d-flex align-items-center justify-content-center"> 
							<input type="text" id="noscreensu" name="noscreensu" value="<?=$noscreensu?>" style="width:30px;"  readonly onkeyup="inputNumberFormat(this)" class=" form-control text-danger calculate_set"></td>  						  
					   </div>
				  <td class="text-center">  
						<div class="d-flex align-items-center justify-content-center"> 
							<input type="text" id="nosteelsu" name="nosteelsu" value="<?=$nosteelsu?>" style="width:30px;"  readonly onkeyup="inputNumberFormat(this)" class=" form-control text-danger calculate_set"></td>  							  
						</div>
				  <td class="text-center">  
						<div class="d-flex align-items-center justify-content-center"> 
							<input type="text" id="noprotectsu" name="noprotectsu" value="<?=$noprotectsu?>"  style="width:30px;"  readonly  onkeyup="inputNumberFormat(this)" class=" form-control text-danger calculate_set"></td>  							  
						</div>
				  <td class="text-center"> 
					<div class="d-flex align-items-center justify-content-center"> 
						<input type="text" id="nosmokesu" name="nosmokesu" value="<?=$nosmokesu?>" style="width:30px;"  readonly onkeyup="inputNumberFormat(this)" class=" form-control text-danger calculate_set"></td>  						  
					</div>
				  <td class="text-center">
					<div class="d-flex align-items-center justify-content-center"> 
						<input type="text" id="noexplosionsu" name="noexplosionsu" value="<?=$noexplosionsu?>" style="width:30px;"  readonly onkeyup="inputNumberFormat(this)" class=" form-control text-danger calculate_set"></td>  						  
					</div>
				  
				</tr>	
	   
		</tbody>
	</table>		

		
	</div>			
			
	<div class="col-sm-8 rounded p-1" > 	
		<table class="table table-bordered ">		 		
			<tr>
				<td class="text-center fw-bold " style="width:220px;"> 구분 </td>      
				<td class="text-center fw-bold " style="width:110px;" data-bs-toggle="tooltip" data-bs-placement="right" title="할인 적용이 안된 표준단가">
				표준금액 </td>      
				<td class="text-center fw-bold " style="width:110px;" data-bs-toggle="tooltip" data-bs-placement="left" title="할인 종류에 따른 할인을 뺀 금액">
				할인 적용금액</td>     								
				<td class="text-center fw-bold " colspan="6" style="width:200px;"   data-bs-toggle="tooltip" data-bs-placement="bottom" title="발주처 정보의 할인 기록을 가져옴, 스크린모터 SET, 철재모터 및 단품 등 할인정보"> 
				할인 종류 선택 </td>
				
			</tr>
			<tr>			
				<td class="text-center "   data-bs-toggle="tooltip" data-bs-placement="right" title="스크린모터 SET 표준가격" > 스크린모터 SET </td>      			
				<td>
				<div class="d-flex align-items-center justify-content-center">
					<input type="text" id="screen_price" name="screen_price" class="form-control" value="<?=$screen_price?>"  readonly oninput="this.value = this.value.replace(/[^\d]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, ','); screen_updatedcPrice();">
				</div>
				</td>
				<td>
					<div class="d-flex align-items-center justify-content-center">
						<input type="text" id="screen_dcprice" name="screen_dcprice" class="form-control  text-primary fw-bold" value="<?=$screen_dcprice?>" readonly>
					</div>
				</td>					
				<td  colspan="7" >
					<div class="d-flex align-items-center justify-content-center mt-1">
						<label><input type="radio" id="screen_no_dc" name="screen_dc_type" value="screen_no_dc" onclick="screen_setdcType('no');" <?= empty($screen_dc_type) || $screen_dc_type == "screen_no_dc" ? 'checked' : '' ?>> 할인무</label> &nbsp; &nbsp;
						<label class="text-primary"><input type="radio" id="screen_company_dc" name="screen_dc_type" value="screen_company_dc" onclick="screen_handledcType();" <?= $screen_dc_type == "screen_company_dc" ? 'checked' : '' ?>> 업체할인</label> &nbsp;
						<input type="text" id="screen_company_dc_value" name="screen_company_dc_value" class="form-control" style="width:30px;" value="<?=$screen_company_dc_value?>" oninput="screen_setdcType('company');" > &nbsp;% &nbsp;
						<label class="text-primary"><input type="radio" id="screen_site_dc" name="screen_dc_type" value="screen_site_dc" onclick="screen_handledcType();" <?= $screen_dc_type == "screen_site_dc" ? 'checked' : '' ?>> 현장할인</label> &nbsp;
						<input type="text" id="screen_site_dc_value" name="screen_site_dc_value" class="form-control " style="width:30px;" value="<?=$screen_site_dc_value?>" oninput="screen_setdcType('site');" > &nbsp;%
					</div>

				</td>
		
			</tr>
			<tr>
				<td class="text-center  "    data-bs-toggle="tooltip" data-bs-placement="right" title="스크린모터 (SET)를 제외한 모터들의 표준가격"  > 철재모터 및 단품 </td>      
				<td>
					<div class="d-flex align-items-center justify-content-center">
						<input type="text" id="price" name="price" class="form-control" value="<?=$price ?>" readonly >
					</div>
				</td>	
			  <td>
				<div class="d-flex align-items-center justify-content-center">
					<input type="text" id="dcprice" name="dcprice" class="form-control  text-primary fw-bold" value="<?=$dcprice?>" readonly >
				</div>
			  </td>					
				<td colspan="6" >
					<div class="d-flex align-items-center justify-content-center mt-1">
						<label><input type="radio" id="no_dc" name="dc_type" value="no_dc" onclick="setdcType('no')" <?= empty($dc_type) || $dc_type == "no_dc" ? 'checked' : '' ?>> 할인무</label> &nbsp; &nbsp;
						<label class="text-primary"><input type="radio" id="company_dc" name="dc_type" value="company_dc" onclick="handledcType();" <?= $dc_type == "company_dc" ? 'checked' : '' ?>> 업체할인</label> &nbsp;
						<input type="text" id="company_dc_value" name="company_dc_value" class="form-control" style="width:30px;" value="<?=$company_dc_value?>" oninput="setdcType('company');" > &nbsp;% &nbsp;
						<label class="text-primary"><input type="radio" id="site_dc" name="dc_type" value="site_dc" onclick="handledcType();" <?= $dc_type == "site_dc" ? 'checked' : '' ?>> 현장할인</label> &nbsp;
						<input type="text" id="site_dc_value" name="site_dc_value" class="form-control " style="width:30px;" value="<?=$site_dc_value?>" oninput="setdcType('site');" > &nbsp;%
					</div>
				</td>
		
			</tr>			
	
			<tr>
				<td class="text-center" data-bs-toggle="tooltip" data-bs-placement="right" title="연동제어기  기준가격"  > 연동제어기 </td>      
				<td>
					<div class="d-flex align-items-center justify-content-center">
						<input type="text" id="controller_price" name="controller_price" class="form-control" value="<?=$controller_price ?>" readonly >
					</div>
				</td>	
			  <td>
				<div class="d-flex align-items-center justify-content-center">
					<input type="text" id="controller_dcprice" name="controller_dcprice" class="form-control  text-primary fw-bold" value="<?=$controller_dcprice?>" readonly >
				</div>
			  </td>					
				<td colspan="6" >
					<div class="d-flex align-items-center justify-content-center mt-1">
						<label><input type="radio" name="controller_dc_type" id="controller_no_dc" value="controller_no_dc" onclick="controller_setdcType('no')" <?= empty($controller_dc_type) || $controller_dc_type == "controller_no_dc" ? 'checked' : '' ?>> 할인무</label> &nbsp; &nbsp;
						<label class="text-primary"><input type="radio" id="controller_company_dc" name="controller_dc_type" value="controller_company_dc" onclick="controller_handledcType();" <?= $controller_dc_type == "controller_company_dc" ? 'checked' : '' ?>> 업체할인</label> &nbsp;
						<input type="text" id="controller_company_dc_value" name="controller_company_dc_value" class="form-control" style="width:30px;" value="<?=$controller_company_dc_value?>" oninput="controller_setdcType('company');" > &nbsp;% &nbsp;
						<label class="text-primary"><input type="radio" id="controller_site_dc" name="controller_dc_type" value="controller_site_dc" onclick="controller_handledcType();" <?= $controller_dc_type == "controller_site_dc" ? 'checked' : '' ?>> 현장할인</label> &nbsp;
						<input type="text" id="controller_site_dc_value" name="controller_site_dc_value" class="form-control " style="width:30px;" value="<?=$controller_site_dc_value?>" oninput="controller_setdcType('site');" > &nbsp;%
					</div>
				</td>		
			</tr>			
		
			<tr>   
				<td class="text-center " style="width:150px;"   data-bs-toggle="tooltip" data-bs-placement="right" title="부속품은 할인적용 안됨"> 부속품(할인미적용) </td>     
				<td colspan="2">
					<div class="d-flex align-items-center justify-content-center">
						<input type="text" id="notdcprice" name="notdcprice" class="form-control text-dark fw-bold notdcprice" value="<?=number_format($notdcprice) ?>" readonly>
					</div>    			
				</td>
				<td class="text-center fw-bold " colspan="6" > </td>  								
			</tr>
			<tr>				
				<td class="text-center fw-bold"   data-bs-toggle="tooltip" data-bs-placement="right" title="수주내역 전체의 할인전 가격 합계 금액"  >  합계 </td>    
				<td>
					<div class="d-flex align-items-center justify-content-center">
						<input type="text" id="sumrawprice" name="sumrawprice" class="form-control text-dark fw-bold" value="<?=number_format($sumrawprice) ?>" readonly>
					</div>
				</td>				
				<td>
					<div class="d-flex align-items-center justify-content-center">
						<input type="text" id="afterdctotal" name="afterdctotal" class="form-control text-primary fw-bold optionSelector" value="<?=$afterdctotal ?>"  readonly >
					</div>
				</td>				
				<td class="text-center text-primary fw-bold" style="width:100px;" data-bs-toggle="tooltip" data-bs-placement="right" title="할인받은 금액의 합계"  > 
					할인 합계 
				</td>    		
				<td>
					<div class="d-flex align-items-center justify-content-center">
						<input type="text" id="dctotal" name="dctotal" class="form-control text-primary fw-bold optionSelector" value="<?=$dctotal ?>"  readonly >
					</div>
				</td>	
				<td class="text-center text-danger fw-bold" style="width:100px;"    data-bs-toggle="tooltip" data-bs-placement="right" title="총금액의 뒷단위를 맞추는 등의 추가할인 강제입력"> 
					추가 할인
				</td>      
				<td>
					<div class="d-flex align-items-center justify-content-center">
						<input type="text" id="dcadd" name="dcadd" class="form-control text-danger fw-bold optionSelector" value="<?=$dcadd ?>" oninput="this.value = this.value.replace(/[^\d]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, ','); updateTotalPrice();" >
					</div>
				</td>	
				<td class="text-center text-success fw-bold" style="width:130px;"  data-bs-toggle="tooltip" data-bs-placement="left" title="거래명세서, 세금계산서 공급가액에 해당. 부과되는 결정금액"> 최종 확정금액</td>      														
				<td>
					<div class="d-flex align-items-center justify-content-center">
						<input type="text" id="totalprice" name="totalprice" class="form-control text-success fw-bold" value="<?=$totalprice ?>" readonly>
					</div>
				</td>			

			</tr>        							
		</tbody>
	</table>	
	</div>
<div class="d-flex ">
<div class="col-sm-4"> 
	<table class="table table-bordered">		 
	  <tbody >		  
		<tr>  
		<td colspan="1" style="width:10%;"   data-bs-toggle="tooltip" data-bs-placement="left" title="대한직원들이 내부적으로 공유할 정보를 기록합니다."> 비고</td>
			<td colspan="5">
				<textarea id="memo"  name="memo"  class="form-control" style="height:30px;" ><?=$memo?></textarea>
			  </td>
		</tr>	
		  </tbody>
		</table>
	</div>
	<div class="col-sm-4"> 
		<table class="table table-bordered">		 
		<tbody>		  	
			<tr>
			<td colspan="1"  class="fw-bold"  style="width:25%;"  data-bs-toggle="tooltip" data-bs-placement="left" title="발주처에 전달하고자 하는 내용을 기록합니다."> (대한) 전달사항</td>
			<td colspan="5">
				<textarea  id="comment"  name="comment" class="form-control"  style="height:30px;" ><?=$comment?></textarea>
			</td>          
			</tr>
		  </tbody>
		</table>
	</div>
	<div class="col-sm-4"> 
		<table class="table table-bordered">		 
		<tbody>		  	
			<tr>
			<td colspan="1" class="fw-bold" style="width:25%;"  data-bs-toggle="tooltip" data-bs-placement="left" title="발주처에서 대한에 요청사항, 특이사항등을 기록합니다. "> (발주처) 기록사항</td>
			<td colspan="5">
				<textarea  id="secondordmemo"  name="secondordmemo" class="form-control"  style="height:30px;" ><?=$secondordmemo?></textarea>
			</td>          
			</tr>
		  </tbody>
		</table>
	</div>
</div>

</div>
</div>
<!-- 모터,브라켓 -->
<div class="d-flex row justify-content-center p-2 rounded"  style=" border: 1px solid #392f31; "> 		
	<div class="d-flex row">	
	<div class="d-flex mb-2">	
		<span class="badge bg-primary fs-6 me-3" > 모터,브라켓 </span>	
		<button type="button" class="btn btn-dark btn-sm add" style="margin-right: 5px;"> <i class="bi bi-plus-square"></i> </button>	
	</div>
	</div>

	<div class="d-flex row ">
		<div class="col-sm-12" > 
			<table class="table table-bordered" id="dynamicTable">
				<thead id="thead_main">
					<tr>
						<th rowspan="2" class="text-center"  style="width:60px;" >전원사양</th>
						<th rowspan="2" class="text-center"  style="width:60px;" >유무선</th>
						<th rowspan="2" class="text-center"  style="width:80px;" >용도</th>			
						<th rowspan="2" class="text-center"  style="width:70px;" >용량</th>
						<th rowspan="2" class="text-center"  style="width:80px;" >단위</th>
						<th rowspan="2" class="text-center" style="width:90px;">브라켓 크기</th>
						<th rowspan="2" class="text-center" style="width:90px;">후렌지 크기</th>									
						<th rowspan="2" class="text-center" style="width:60px;">수량</th>
						<th rowspan="2" class="text-center" style="width:100px;">단가</th>
						<th class="text-center" style="width:100px;">금액</th>				
						<th class="text-center text-primary" style="width:100px;">할인</th>
						<th class="text-center" style="width:100px;">확정금액</th>					
						<th rowspan="2" class="text-center" style="width:150px;" data-bs-toggle="tooltip" data-bs-placement="left" title="로트번호 생성방식 DH - M or C or B - 0424(중국제조번호) - 0507(대한입고월일) : 약자설명) M-모터, C-연동제어기, B-브라켓트"> 로트번호 </th>
						<th rowspan="2" class="text-center" style="width:150px;" data-bs-toggle="tooltip" data-bs-placement="left" title="출고증에 표시될 내용, 업체에 제공할 내용"  >						
							전달사항						
						</th>
						<?php if($mode!=='view') { ?>
						<th rowspan="2" class="text-center" style="width:70px;">+/-</th>
						<?php } ?>
					</tr>					
					<tr>										
						<th class="text-center" style="width:100px;">               <input type="text" id="main_total1" class="form-control text-secondary" readonly   > </th>
						<th class="text-center text-primary" style="width:100px;">  <input type="text" id="main_total2" class="form-control text-primary"  readonly   > </th>
						<th class="text-center" style="width:100px;">               <input type="text" id="main_total3" class="form-control fw-bold"  readonly   > </th>										
					</tr>				
				</thead>
			<tbody>
			<!-- 자동생성 -->
			</tbody>
			</table>	
		</div>	  
	</div>  
</div>

<!-- 연동제어기 -->
<div class="d-flex row justify-content-center p-2 rounded"  style=" border: 1px solid #392f31; "> 		
	<div class="d-flex row">	
	<div class="d-flex mb-2">	
		<span class="badge bg-success fs-6 me-3" > 연동제어기 </span>	
		<button type="button" class="btn btn-dark btn-sm controller_add restrictbtn" style="margin-right: 5px;"> <i class="bi bi-plus-square"></i> </button>	
	</div>
	</div>
<div class="d-flex row">
	<div class="col-sm-9" > 	
		<table class="table table-bordered" id="controller_dynamicTable">
			<thead id="thead_controller">
				<tr>					
					<th rowspan="2" class="text-center" style="width:180px;"  data-bs-toggle="tooltip" data-bs-placement="bottom" title="연동제어기 단가를 지정. 옆의 톱니바퀴로 수정가능">
						구분
						<button type="button" class="btn btn-dark-outline btn-sm restrictbtn" onclick="fee_controllerBtn();"> <i class="bi bi-gear"></i> </button>
					</th>
					<th rowspan="2" class="text-center" style="width:60px;">수량</th>
					<th rowspan="2" class="text-center" style="width:100px;">단가</th>
					<th class="text-center" style="width:100px;">금액</th>				
					<th class="text-center text-primary" style="width:100px;">할인</th>
					<th class="text-center" style="width:100px;">확정금액</th>						
					<th rowspan="2" class="text-center" style="width:200px;"   data-bs-toggle="tooltip" data-bs-placement="right" title="로트번호 생성방식 DH - M or C or B - 0424(중국제조번호) - 0507(대한입고월일) : 약자설명) M-모터, C-연동제어기, B-브라켓트"> 로트번호</th>				
					<th rowspan="2" class="text-center" style="width:15%;"   data-bs-toggle="tooltip" data-bs-placement="left" title="출고증에 표시될 내용, 업체에 제공할 내용">
						전달사항
					</th>
					<?php if($mode!=='view') { ?>
					<th rowspan="2" class="text-center" style="width:90px;">+/-</th>
				    <?php } ?>
				</tr>				
				<tr>										
					<th class="text-center" style="width:100px;">               <input type="text" id="controller_total1" class="form-control text-secondary" readonly > </th>
					<th class="text-center text-primary" style="width:100px;">  <input type="text" id="controller_total2" class="form-control text-primary" readonly  > </th>
					<th class="text-center" style="width:100px;">               <input type="text" id="controller_total3" class="form-control fw-bold"  readonly > </th>				
					
				</tr>				
			</thead>			
			
		<tbody>
		<!-- 자동생성 -->
			</tbody>
		</table>	
	</div>
	<div class="col-sm-3" >  
	</div>	
</div>  
</div>

<div class="d-flex row justify-content-center p-2 rounded"  style=" border: 1px solid #392f31; "> 	
	<div class="d-flex row">	
	<div class="d-flex mb-2">	
	<span class="badge bg-secondary fs-6 me-3" > 부속 자재 </span>
		<button type="button" class="btn btn-dark btn-sm sub_add" style="margin-right: 5px;"><i class="bi bi-plus-square"></i></button>
</div>	
</div>	
<div class="d-flex row">
	<div class="col-sm-6" > 	
		<table class="table table-bordered" id="sub_dynamicTable">
			<thead id="thead_sub">
				<tr>					
					<th rowspan="2" class="text-center" style="width:230px;"  data-bs-toggle="tooltip" data-bs-placement="bottom" title="부속 자재 전체 단가를 지정할 수 있음. 옆의 톱니바퀴로 수정가능">
					구분
						<button type="button" class="btn btn-dark-outline btn-sm restrictbtn me-2" onclick="fee_subBtn();"><i class="bi bi-gear"></i></button>
						<button type="button" id="refreshButton" class="btn btn-outline-dark btn-sm restrictbtn"   data-bs-toggle="tooltip" data-bs-placement="right" title="부속 자재 단가를 다시 읽음"> 
						<i class="bi bi-arrow-clockwise"></i> </button>
					</th>
					<th rowspan="2" class="text-center" style="width:60px;">수량</th>
					<th rowspan="2" class="text-center" style="width:100px;">단가</th>
					<th class="text-center" style="width:100px;">확정금액</th>											
					<th rowspan="2" class="text-center" style="width:200px;"   data-bs-toggle="tooltip" data-bs-placement="left" title="출고증에 표시될 내용, 업체에 제공할 내용">
						전달사항
					</th>
					<?php if($mode!=='view') { ?>
					<th rowspan="2" class="text-center" style="width:70px;">+/-</th>
				    <?php } ?>
				</tr>				
				<tr>										
					<th class="text-center" style="width:100px;">               <input type="text" id="sub_total3" class="form-control text-secondary" readonly > </th>					
			</thead>			
			
		<tbody>
		<!-- 자동생성 -->
		 <tbody id="subAccessoryGroup">
		</table>	
	</div>
	<div class="col-sm-6" >  
	</div>	
</div>  
</div>	
<div class="d-flex row justify-content-center p-2 rounded"  style=" border: 1px solid #392f31; "> 	
<div class="d-flex row">
	<div class="col-sm-12"> 
	<table class="table table-bordered">		  
	  <tbody>
			<tr>		  
			  <td> 
				  <div class="d-flex align-items-center justify-content-center">		
					<span class="badge bg-dark fs-6 " > 일정(일자) </span>	 
				  </div>
				</td>		
				<td data-bs-toggle="tooltip" data-bs-placement="bottom" title="발주처가 발주서를 등록하는 날짜"  > 
					<div class="d-flex align-items-center justify-content-center"> 
						등록 &nbsp;
						<input type="date" name="registerdate" id="registerdate" value="<?=$registerdate?>"  class="form-control"  style="width:120px;" >
					</div>	
				</td>  	
				<td  data-bs-toggle="tooltip" data-bs-placement="bottom" title="(주) 대한 접수 시점 날짜" > 
					<div class="d-flex align-items-center justify-content-center"> 
						접수 &nbsp;
						<input type="date" name="orderdate" id="orderdate" value="<?=$orderdate?>"  class="form-control"  style="width:120px;" >
					</div>	
				</td>  
			  <td  class="text-danger"  data-bs-toggle="tooltip" data-bs-placement="bottom" title="대한에서 출고를 예상하는 날짜임. 납기일은 발주처와 받기로 약속한 날을 의미함"  > 
					<div class="d-flex align-items-center justify-content-center"> 
						출고예정 &nbsp; <input type="date" name="deadline" id="deadline" value="<?=$deadline?>"  class="form-control"  style="width:120px;">				
					</div>
				</td>    
			  <td style="color:blue;"   data-bs-toggle="tooltip" data-bs-placement="bottom" title="실제 물건이 출고되면 기록하는 날짜. 처리완료 의미 (보통 출고하는 분이 기록)">
					<div class="d-flex align-items-center justify-content-center"> 
						출고완료 &nbsp;<input type="date" name="outputdate" id="outputdate" value="<?=$outputdate?>"   class="form-control" style="width:120px;">
					</div>
			  </td>   		  
			  <td class="text-info"   data-bs-toggle="tooltip" data-bs-placement="bottom" title="경리부에서 계산서발행등 청구 행위를 했을때 일자 기록"> 
				<div class="d-flex align-items-center justify-content-center"> 
					청구 &nbsp;<input type="date" name="demand" id="demand" value="<?=$demand?>"   class="form-control" style="width:120px;">
				</div>
			  </td>  	  
			</tr>		
		</tbody>
	</table>
	</div>
 </div>
</div>

<div class="d-flex row justify-content-center p-2 rounded"  style=" border: 1px solid #392f31; "> 	
<div class="d-flex row">
<div class="col-sm-12"> 
	<table class="table table-bordered">		 
	  <tbody>		  
		<tr>
			  <td  style="width:130px;" > 
				  <div class="d-flex align-items-center justify-content-center">		
					<span class="badge bg-dark fs-6 " > 운송 방식(선택) </span>	 
				  </div>
				</td>
			  <td class="text-center">
			 
				<div class="d-flex align-items-center justify-content-center">			 
					<?php				
					$deliveryOptions = ["","경동공장","직접수령", "직배송", "경동화물","대신화물","택배", "배차"];
					// if($deliverymethod == '')
						// $deliverymethod = ""; 
					?>
					<select name="deliverymethod" id="deliverymethod" class="form-control text-center fw-bold"  style="width:120px;" onchange="showFields();">
					  <?php				  
					  foreach ($deliveryOptions as $option) {					
						$selected = ($deliverymethod == $option) ? ' selected' : '';
						echo "<option value='" . $option . "'" . $selected . ">" . $option . "</option>";
					  }
					  ?>
					</select>
				</div>
			  </td>	
			  <td >				
			  받는 분 
			  </td><td>					  
				<div class="d-flex align-items-center justify-content-center">	
				   <input type="text" id="chargedman" name="chargedman"  class="form-control" style="width:180px;" value="<?=$chargedman?>" onkeydown="if(event.keyCode == 13) { workbookBtn('chargedman'); }">&nbsp; 				  
					  <button type="button" class="btn btn-dark-outline btn-sm restrictbtn" onclick="workbookBtn('chargedman');">  <i class="bi bi-gear"></i> </button>					  
					</div>
				</td><td>					  
				     <i class="bi bi-telephone-forward-fill"></i> 연락 </td><td> <input type="text" name="chargedmantel" id="chargedmantel" value="<?=$chargedmantel?>"  class="form-control" onkeydown="if(event.keyCode == 13) { workbookBtn('chargedmantel'); }" >  </td><td>
						배송주소</td>
			     <td>
					<input type="text" id="address" name="address" value="<?=$address?>" class="form-control text-start"  style="width:550px;"  >						
				</td>				  			  
			</tr>		
		  </tbody>
		</table>
	</div>	
</div>	

<table class="table table-bordered">
	<tbody id="deliveryFields">         
		<!-- 배차 -->
		<tr id="dispatch" style="display:none;">
			<td style="width:250px;">
				<div class="d-flex align-items-center justify-content-center">
					<span class="badge bg-success fs-6 me-4">(대한) 입력</span>
					<div class="custom-control custom-switch">
						<input type="checkbox" class="custom-control-input" id="sendcheckToggle" name="sendcheckToggle" <?= $sendcheck == '1' ? 'checked' : '' ?>>
						<input type="hidden" name="sendcheck" value="<?= $sendcheck == '1' ? '1' : '' ?>">
						<label class="custom-control-label" for="sendcheckToggle"><i class="bi bi-bell-fill"></i> 배차 접수</label>
					</div>
				</div>
			</td>
			<td>상차지</td>
			<td class="text-center"><input type="text" name="loadplace" placeholder="상차지" class="form-control" value="<?= $loadplace ?>"></td>
			<td style="width:80px;"  data-bs-toggle="tooltip" data-bs-placement="bottom" title="상차일자와 시간을 입력. 시간은 마우스 휠을 이용해서 돌리면 편리합니다. 화면이 닫히지 않아도 클릭하면 설정됩니다. " >
				상차일시
			</td>
			<td class="text-center"><input type="datetime-local" name="deltime" class="form-control" style="width:135px;" value="<?= $deltime ?>"></td>
			<td style="width:80px;"   data-bs-toggle="tooltip" data-bs-placement="bottom" title="하차일자와 시간을 입력. 시간은 마우스 휠을 이용해서 돌리면 편리합니다. 화면이 닫히지 않아도 클릭하면 설정됩니다. " >
				하차일시
			</td>
			<td class="text-center"><input type="datetime-local" name="deldowntime" class="form-control" style="width:135px;" value="<?= $deldowntime ?>"></td>
			<td style="width:80px;">차량종류</td>
			<td><input type="text" name="delcaritem" placeholder="차량종류" class="form-control" style="width:80px;" value="<?= $delcaritem ?>"></td>
			<td class="text-center" style="width:80px;"  data-bs-toggle="tooltip" data-bs-placement="bottom" title="회물회사에 요청사항을 대한직원이 기록합니다.">
				화물회사<br>전달사항<br>대한작성
			</td>
			<td><textarea name="delmemo" rows="2" class="form-control" style="width:300px;"><?= $delmemo ?></textarea></td>
			<td  data-bs-toggle="tooltip" data-bs-placement="bottom" title="'대한'이 아닌 발주처로 선택하면 업체에 발주처 정보가 나타납니다.">
				<div class="d-flex align-items-center justify-content-center">
					<span class="badge bg-danger fs-6">배송비 지급</span>&nbsp;
					<select name="deliverypaymethod" id="deliverypaymethod" class="form-control text-center fw-bold" style="width:80px;">
						<!-- 옵션들은 JavaScript에서 추가됩니다. -->
					</select>
				</div>
			</td>
		</tr>
		<tr id="dispatch-company" class="mt-5" style="display:none;">
			<td class="text-center">
				<div class="d-flex align-items-center justify-content-center">
					<span class="badge bg-primary fs-6 mb-2">(운송회사) 입력</span>
				</div>
				<div class="d-flex align-items-center justify-content-center">
					운송회사 &nbsp;<input type="text" id="delcompany" name="delcompany" placeholder="운송회사" class="form-control text-center" style="width:100px;" value="<?= $delcompany ?>">
				</div>
			</td>
			<td class="text-primary text-center" style="width:80px;"  data-bs-toggle="tooltip" data-bs-placement="bottom" title="대한에서 배차 접수를 클릭하면 진행상태가 '배차접수'로 변경되면서 화물회사에서 배차진행">
				진행상태
			</td>
			<td class="text-primary text-center">
				<div class="d-flex justify-content-center">
					<input type="text" id="del_status"  name="del_status" class="form-control text-primary text-center" style="width:100px;" value="<?= $del_status ?>">
				</div>
			</td>
			<td class="text-danger">차량번호</td>
			<td><input type="text" id="delcarnumber"  name="delcarnumber"  placeholder="차량번호" class="form-control" value="<?= $delcarnumber ?>"></td>
			<td class="text-danger"><i class="bi bi-telephone-forward-fill"></i> 기사</td>
			<td><input type="text" id="delcartel" name="delcartel" placeholder="기사연락처" class="form-control" value="<?= $delcartel ?>"></td>
			<td class="text-danger">운송료</td>
			<td><input type="text" id="delipay"  name="delipay" value="<?= $delipay ?>" class="form-control" onkeyup="inputNumberFormat(this)"></td>
			<td class="text-center" style="width:80px;"  data-bs-toggle="tooltip" data-bs-placement="bottom" title="화물회사에서 대한의 요청에 대한 다른 사항이 발생하면 기록합니다.">
				화물회사<br>메모작성<br></td>
			<td colspan="2"><textarea name="del_writememo" rows="2" class="form-control" style="width:300px;"><?= $del_writememo ?></textarea></td>
		</tr>
		<!-- 화물 -->
		<tr id="cargo" style="display:none;">
			<td rowspan="2" style="width:250px;">
				<span class="badge bg-success fs-6">(대한) 입력</span>
			</td>
			<td>송장번호</td>
			<td><input type="text" name="delbranchinvoice" placeholder="송장번호" class="form-control" value="<?= $delbranchinvoice ?>"></td>
			<td>포장방식</td>
			<td>
				<select id="delwrapmethod" name="delwrapmethod" class="form-control">
					<option value="박스" <?= $delwrapmethod == '박스' ? 'selected' : '' ?>>박스</option>
					<option value="파렛트" <?= $delwrapmethod == '파렛트' ? 'selected' : '' ?>>파렛트</option>
					<option value="" <?= $delwrapmethod == '' ? 'selected' : '' ?>>(선택없음)</option>
				</select>
			</td>
			<td>포장수량</td>
			<td><input type="text" id="delwrapsu" name="delwrapsu" class="form-control" value="<?= $delwrapsu ?>" onkeyup="inputNumberFormat(this)"></td>
			<td>금액(만원)</td>
			<td><input type="text" id="delwrapamount" name="delwrapamount" class="form-control text-center" value="<?= $delwrapamount ?>" onkeyup="inputNumberFormat(this)"></td>
			<td>무게(kg)</td>
			<td><input type="text" id="delwrapweight" name="delwrapweight" class="form-control text-center" value="<?= $delwrapweight ?>" onkeyup="inputNumberFormat(this)"></td>
			<td class="text-danger fw-bold">결재방식</td>
			<td>
				<select id="delwrappaymethod" name="delwrappaymethod" class="form-control">
					<option value="카드선불" <?= $delwrappaymethod == '카드선불' ? 'selected' : '' ?>>카드선불</option>
					<option value="" <?= $delwrappaymethod == '' ? 'selected' : '' ?>>(선택없음)</option>
				</select>
			</td>
		</tr>
		<tr id="cargo-extra" style="display:none;">
			<td>화물지점</td>
			<td colspan="2">
				<div class="d-flex align-items-center justify-content-start">
					<input type="text" id="delbranch" name="delbranch" placeholder="화물지점" class="form-control me-1" value="<?= $delbranch ?>" style="width:85%;" onkeydown="if(event.keyCode == 13) { branchBtn('delbranch'); }">
					<button type="button" class="btn btn-dark-outline btn-sm restrictbtn" onclick="branchBtn('delbranch');">
						<i class="bi bi-gear"></i>
					</button>
				</div>
			</td>
			<td>지점주소</td>
			<td colspan="2"><input type="text" id="delbranchaddress" name="delbranchaddress" placeholder="지점주소" class="form-control" value="<?= $delbranchaddress ?>" onkeydown="if(event.keyCode == 13) { branchBtn('delbranch'); }"></td>
			<td>연락처</td>
			<td colspan="2"><input type="text" id="delbranchtel" name="delbranchtel" placeholder="연락처" class="form-control" value="<?= $delbranchtel ?>" onkeydown="if(event.keyCode == 13) { branchBtn('delbranch'); }"></td>
		</tr>
		<!-- 택배 -->
		<tr id="courier" style="display:none;">
			<td rowspan="2"><span class="badge bg-success fs-6">(대한) 입력</span></td>
			<td>송장번호</td>
			<td><input type="text" name="delbranchinvoice" placeholder="송장번호" class="form-control" value="<?= $delbranchinvoice ?>"></td>
			<td>포장방식</td>
			<td>
				<select id="delwrapmethod" name="delwrapmethod" class="form-control">
					<option value="박스" <?= $delwrapmethod == '박스' ? 'selected' : '' ?>>박스</option>
					<option value="파렛트" <?= $delwrapmethod == '파렛트' ? 'selected' : '' ?>>파렛트</option>
					<option value="" <?= $delwrapmethod == '' ? 'selected' : '' ?>>(선택없음)</option>
				</select>
			</td>
			<td>포장수량</td>
			<td><input type="text" id="delwrapsu" name="delwrapsu" class="form-control" value="<?= $delwrapsu ?>" onkeyup="inputNumberFormat(this)"></td>
			<td>금액(만원)</td>
			<td><input type="text" id="delwrapamount" name="delwrapamount" class="form-control text-center" value="<?= $delwrapamount ?>" onkeyup="inputNumberFormat(this)"></td>
			<td>무게(kg)</td>
			<td><input type="text" id="delwrapweight" name="delwrapweight" class="form-control text-center" value="<?= $delwrapweight ?>" onkeyup="inputNumberFormat(this)"></td>
			<td class="text-danger fw-bold">결재방식</td>
			<td>
				<select id="delwrappaymethod" name="delwrappaymethod" class="form-control">
					<option value="카드선불" <?= $delwrappaymethod == '카드선불' ? 'selected' : '' ?>>카드선불</option>
					<option value="" <?= $delwrappaymethod == '' ? 'selected' : '' ?>>(선택없음)</option>
				</select>
			</td>
		</tr>


            </tbody>
        </table>
    
	</div>
</div>
</div>

<div class="row d-flex justify-content-center">
			
	<?php	if($chkMobile) 	{	?>
		<div class="col-sm-12 p-1 rounded" style=" border: 2px solid #392f31; " > 			
	<?php	} else 	{	?>	
		<div class="col-sm-4 p-1 rounded" style=" border: 2px solid #392f31; " > 			
	<?php	}  ?>	

	<?php

	$counter = 1;   
	// 버튼 생성
	createImageInputAndButton($counter, 'beforeArr', 'success', $picDataArr);  
				
	function createImageInputAndButton($counter, $type, $btnColor, $picData) {
		// 모바일 사용여부 확인하는 루틴
		$mAgent = array("iPhone","iPod","Android","Blackberry", 
			"Opera Mini", "Windows ce", "Nokia", "sony" );
			
		$chkMobile = false;
		for($i=0; $i<sizeof($mAgent); $i++){
			if(stripos( $_SERVER['HTTP_USER_AGENT'], $mAgent[$i] )){
				$chkMobile = true;		
				break;
			}
		}			
		
		echo '<div style="display:none;">';
		echo '<input type="file" multiple accept=".gif, .jpg, .png" id="'.$type.'Input_'.$counter.'" onchange="FileProcess(\''.$type.'\', '.$counter.', this)">';
		echo '</div>';
			
		if($chkMobile)
		{		
			echo '<div class="col text-center justify-content-center align-items-center mt-1 mb-1">';
			echo '<button type="button" class=" justify-content-center align-items-center btn btn-lg btn-'.$btnColor.'  text-center " onclick="document.getElementById(\''.$type.'Input_'.$counter.'\').click()">';
		}
		else
		{		
			echo '<div class="col text-center justify-content-center align-items-center mt-1 mb-1">';
			echo '<button type="button" class=" justify-content-center align-items-center btn btn-lg btn-'.$btnColor.'  text-center " onclick="document.getElementById(\''.$type.'Input_'.$counter.'\').click()">';
		}		
		
		echo '(대한) 포장(출하) 사진 </button>';
	 if ($chkMobile)   	   		
			echo '<div class="col mt-4 mb-4 me-2" id="'.$type.'Images_'.$counter.'"  >';
		else
			echo '<div class="mt-5 mb-5" id="'.$type.'Images_'.$counter.'"  >';
		echo '</div>';	
		echo '</div>';	
	}

	?>

			<?php if($num != 0): ?>
			<!-- 출하관련 사진 화면 출력 -->
			<script>
			document.addEventListener("DOMContentLoaded", function() {
				let picIdx = <?php echo json_encode($picIdx); ?>;
				let MidpicIdx = <?php echo json_encode($MidpicIdx); ?>;				
				
				let picDataArr = <?php echo json_encode($picDataArr); ?>;
				let MidpicDataArr = <?php echo json_encode($MidpicDataArr); ?>;				

				['beforeArr', 'midArr'].forEach(type => {
					let currentData, currentIdx, itemType;

					switch (type) {
						case 'beforeArr':
							currentData = picDataArr;
							currentIdx = picIdx;
							itemType = 'before';
							break;
						case 'midArr':
							currentData = MidpicDataArr;
							currentIdx = MidpicIdx;
							itemType = 'mid';
							break;
					}

					currentData.forEach((picName, index) => {
						if (currentIdx[index] && picName) {
							let containerId = `#${type}Images_${currentIdx[index]}`;
							let uniqueId = `${itemType}Images_${currentIdx[index]}_${index}`;
							let cleanedPath = picName.replace('./uploads/', '');
							AdddisplayImagesArray(containerId, [picName], itemType, currentIdx[index]);
						}
					});
				});
			});


			function AdddisplayImagesArray(containerId, filepaths, itemType, itemIdx) {
				var startingIndex = $(containerId).children('img').length;

				filepaths.forEach((imgSrc, i) => {
					var currentIndex = startingIndex + i;
					var uniqueId = `${itemType}Images_${itemIdx}_${currentIndex}`;
					var cleanedPath = imgSrc.replace('./uploads/', '');

					var imgElement = `<img id="${uniqueId}" src="./uploads/${imgSrc}" style="width:80%;" class="mb-3 mt-3">`;
					var rotateButton = `<div class="d-flex justify-content-center"> <button type="button" class="btn btn-primary btn-sm me-2" id="rotate${uniqueId}" onclick="rotateFn(event, '${uniqueId}', '${cleanedPath}', '${itemType}')"> <i class="bi bi-arrow-clockwise"></i> </button>`;
					var deleteButton = `<button type="button" class="btn btn-danger btn-sm" id="del${uniqueId}" onclick="delPicFn('${uniqueId}', '${cleanedPath}', '${itemType}')"><i class="bi bi-trash"></i> </button> </div>`;

					$(containerId).append(imgElement + rotateButton + deleteButton);
				});
			}

			</script>
			<?php endif; ?>

			</div>
			
		
	<!-- 화물사진 -->		
	<?php	if($chkMobile) 	{	?>
		<div class="col-sm-12 p-1 rounded" style=" border: 2px solid #392f31; " > 			
	<?php	} else 	{	?>	
		<div class="col-sm-4 p-1 rounded" style=" border: 2px solid #392f31; " > 			
	<?php	}  ?>	

	<?php

	$counter = 1;   
	// 버튼 생성
	createImage_cargo($counter, 'midArr', 'primary', $picDataArr);            
				
	function createImage_cargo($counter, $type, $btnColor, $picData) {
		// 모바일 사용여부 확인하는 루틴
		$mAgent = array("iPhone","iPod","Android","Blackberry", 
			"Opera Mini", "Windows ce", "Nokia", "sony" );
			
		$chkMobile = false;
		for($i=0; $i<sizeof($mAgent); $i++){
			if(stripos( $_SERVER['HTTP_USER_AGENT'], $mAgent[$i] )){
				$chkMobile = true;		
				break;
			}
		}			
		
		echo '<div style="display:none;">';
		echo '<input type="file" multiple accept=".gif, .jpg, .png" id="'.$type.'Input_'.$counter.'" onchange="FileProcess(\''.$type.'\', '.$counter.', this)">';
		echo '</div>';
			
		if($chkMobile)
		{		
			echo '<div class="col text-center justify-content-center align-items-center mt-1 mb-1">';
			echo '<button type="button" class=" justify-content-center align-items-center btn btn-lg btn-'.$btnColor.'  text-center " onclick="document.getElementById(\''.$type.'Input_'.$counter.'\').click()">';
		}
		else
		{		
			echo '<div class="col text-center justify-content-center align-items-center mt-1 mb-1">';
			echo '<button type="button" class=" justify-content-center align-items-center btn btn-lg btn-'.$btnColor.'  text-center " onclick="document.getElementById(\''.$type.'Input_'.$counter.'\').click()">';
		}		
				
		echo '(화물회사) 상차 사진 </button>';
	 if ($chkMobile)   	   		
			echo '<div class="col mt-4 mb-4 me-2" id="'.$type.'Images_'.$counter.'"  >';
		else
			echo '<div class="mt-5 mb-5" id="'.$type.'Images_'.$counter.'"  >';
		echo '</div>';	
		echo '</div>';	
	}

	?>


			</div>
			
			
			</div>


			</div>
		</div>
		</div>


</div> <!-- end of 첨부파일 row -->
</div>
			 
</form>

<!-- 페이지로딩 -->
<script>
// 페이지 로딩
$(document).ready(function(){	
    var loader = document.getElementById('loadingOverlay');
    loader.style.display = 'none';
});
</script>

<!-- 배송관련 배차 화물 택배 -->
<script>
ajaxRequest_write = null;
ajaxRequest_picinsert  = null;
ajaxRequest_fee_sub  = null;
ajaxRequest_controller  = null;

// PHP에서 가져온 변수를 JavaScript 변수로 설정
var deliverymethod = '<?php echo $deliverymethod; ?>';
var deliverypaymethod = '<?php echo $deliverypaymethod; ?>';
var delbranch = '<?php echo htmlspecialchars($delbranch); ?>';
var delbranchaddress = '<?php echo htmlspecialchars($delbranchaddress); ?>';
var delbranchtel = '<?php echo htmlspecialchars($delbranchtel); ?>';
var delbranchinvoice = '<?php echo htmlspecialchars($delbranchinvoice); ?>';
var delCarNumber = '<?php echo htmlspecialchars($delcarnumber); ?>';
var delcaritem = '<?php echo htmlspecialchars($delcaritem); ?>';
var delCarTel = '<?php echo htmlspecialchars($delcartel); ?>';
var loadplace = '<?php echo htmlspecialchars($loadplace); ?>';
var delcompany = '<?php echo htmlspecialchars($delcompany); ?>';
var delipay = '<?php echo htmlspecialchars($delipay); ?>';
var delwrappaymethod = '<?php echo htmlspecialchars($delwrappaymethod); ?>';
var delwrapmethod = '<?php echo htmlspecialchars($delwrapmethod); ?>';
var delwrapsu = '<?php echo htmlspecialchars($delwrapsu); ?>';
var delwrapamount = '<?php echo htmlspecialchars($delwrapamount); ?>';
var delwrapweight = '<?php echo htmlspecialchars($delwrapweight); ?>';
var deltime = '<?php echo htmlspecialchars($deltime); ?>';
var deldowntime = '<?php echo htmlspecialchars($deldowntime); ?>';
var delmemo = '<?php echo htmlspecialchars($delmemo); ?>';
var sendcheck = '<?php echo htmlspecialchars($sendcheck); ?>'; // 화물차 회사에 전송여부 체크
var del_status = '<?php echo htmlspecialchars($del_status); ?>';
var del_writememo = '<?php echo htmlspecialchars($del_writememo); ?>';
var deliverypayOptions = ["대한", "발주처"];

if(delcompany =='' || delcompany ==null)
  {
	delcompany ='25시콜';	
  }

function showFields() {
	var deliverymethod = $("#deliverymethod").val();  // ID를 정확하게 맞춥니다.

	// 모든 tr 요소를 숨깁니다.
	$("#direct-receipt").hide();
	$("#factory-receipt").hide();
	$("#dispatch").hide();
	$("#dispatch-company").hide();
	$("#cargo").hide();
	$("#courier").hide();

	// 조건에 따라 tr 요소를 보입니다.
	if(deliverymethod === '직접수령' || deliverymethod === '직배송') {
		$("#direct-receipt").show();
	} else if(deliverymethod === '경동공장') {
		$("#factory-receipt").show();
	} else if(deliverymethod === '배차') {
		$("#dispatch").show();
		$("#dispatch-company").show();
	} else if (deliverymethod.includes('화물')) {
		$("#cargo").show();
		$("#cargo-extra").show();
	} else if(deliverymethod === '택배') {
			$("#courier").show();
		}
}

// deliverymethod 변경 시 showFields 함수 호출
$("#deliverymethod").change(showFields);

// 초기 로딩 시 선택된 값에 따라 showFields 함수 호출
$(document).ready(function() {
	showFields();
});

// 숫자 형식 입력 포맷 함수 (필요한 경우 정의)
function inputNumberFormat(obj) {
	obj.value = obj.value.replace(/[^0-9]/g, '');
}
    </script>
 
<!-- 이미지(사진) 보여주는 루틴 -->
<script>
$(document).ready(function(){	
		$(document).on("click","img",function(){
			var path = $(this).attr('src')
			showImage(path);
		});//end click event
		
		function showImage(fileCallPath){
		    
		    $(".bigPictureWrapper").css("display","flex").show();
		    
		    $(".bigPicture")
		    .html("<img src='"+fileCallPath+"' >")
		    .animate({width:'100%', height: '100%'}, 1000);
		    
		  }//end fileCallPath
		  
		$(".bigPictureWrapper").on("click", function(e){
		    $(".bigPicture").animate({width:'0%', height: '0%'}, 1000);
		    setTimeout(function(){
		      $('.bigPictureWrapper').hide();
		    }, 1000);
		  });//end bigWrapperClick event	
		  

	// 매초 검사해서 이미지가 있으면 보여주기
	$("#pInput").val('50'); // 최초화면 사진파일 보여주기
		
	 
  
delFileFn = function(divID, delChoice) {
	// console.log(divID, delChoice);
	if(confirm("한번 삭제한 자료는 복구할 방법이 없습니다.\n\n정말 삭제하시겠습니까?")) {
	$.ajax({
		url:'../file/del_file.php?savename=' + delChoice ,
		type:'post',
		data: $("#board_form").serialize(),
		dataType: 'json',
		}).done(function(data){						
		   const savename = data["savename"];		   
		   
		  // 시공전사진 삭제 
			$("#file" + divID).remove();  // 그림요소 삭제
			$("#delFile" + divID).remove();  // 그림요소 삭제
		    $("#pInput").val('');					
			
        });	
	}		

}		 
		 	 	  	
	
});
function Enter_Check(){
        // 엔터키의 코드는 13입니다.
    if(event.keyCode == 13){
      exe_search();  // 실행할 이벤트 담당자 연락처 찾기
    }
}
function Enter_firstCheck(){
    if(event.keyCode == 13){
	const data1 = "motor";
	const data2 = "firstordman";
	const data3 = "firstordmantel";	
	const search = $("#" + data2).val();
    if(event.keyCode == 13){     
     window.open('load_tel.php?search=' + search +'&data1=' + data1 + '&data2=' + data2 + '&data3=' + data3,'전번 조회','top=0, left=0, width=1500px, height=600px, scrollbars=yes');	  
    }
    }
}

function captureReturnKey(e) {
    if(e.keyCode==13 && e.srcElement.type != 'textarea')
    return false;
}

function recaptureReturnKey(e) {
    if(e.keyCode==13 && e.srcElement.type != 'textarea')
    return false;
}

function phonebookBtn(searchfield)
{	    
    var search = $("#" + searchfield).val();				
    href = '../phonebook/list.php?search=' + search ;				
	popupCenter(href, '전화번호 검색', 1600, 800);
}

function branchBtn(searchfield)
{	    
    var search = $("#" + searchfield).val();				
    href = '../branchbook/list.php?search=' + search ;				
	popupCenter(href, '화물지점 검색', 1600, 800);
}

function fee_subBtn()
{	    
    href = '../fee_sub/list.php?header=noheader' ;				
	popupCenter(href, '', 1400, 800);

}
function fee_controllerBtn()
{	    
    href = '../fee_controller/list.php?header=noheader' ;				
	popupCenter(href, '', 1400, 800);

}
// 받는분 소장
function workbookBtn(searchfield)
{	
    
    var search = $("#" + searchfield).val();			
	
    href = '../workbook/list.php?search=' + search ;				
	popupCenter(href, '전화번호 검색', 1600, 800);

}

$(document).ready(function(){
		
	 $("#saveBtn").click(function(e){ 
		e.preventDefault(); // 기본 폼 제출 방지
		
		// 조건 확인
		if($("#workplacename").val() === '' ) {
			showWarningModal();
		} else {									
			saveData();
		}
	});


});


	function showWarningModal() {
		Swal.fire({                                    
			title: '등록 오류 알림',
			text: '현장명은 필수입력 요소입니다.',
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
		
      // json 형태로 form문에 저장하기
		let formData = [];

		$('#dynamicTable tbody tr').each(function() {
			let rowData = {};
			$(this).find('input, select').each(function() {
				let name = $(this).attr('name').replace('[]', ''); // 이름에서 '[]' 제거
				let value = $(this).val();
				rowData[name] = value;
			});
			formData.push(rowData);
		});

		// JSON 문자열로 변환
		let jsonString = JSON.stringify(formData);
		// console.log('orderlist json : ', orderlist);		

		// 숨겨진 필드에 JSON 데이터 설정
		$('#orderlist').val(jsonString);	      
		
		// accessorieslist json 형태로 form문에 저장하기
		formData = [];

		$('#sub_dynamicTable tbody tr').each(function() {
			let rowData = {};
			$(this).find('input, select').each(function() {
				let name = $(this).attr('name').replace('[]', ''); // 이름에서 '[]' 제거
				let value = $(this).val();
				rowData[name] = value;
			});
			formData.push(rowData);
		});

		jsonString = JSON.stringify(formData);

		// 숨겨진 필드에 JSON 데이터 설정
		$('#accessorieslist').val(jsonString);	  	

		// controllerlist json 형태로 form문에 저장하기
		formData = [];

		$('#controller_dynamicTable tbody tr').each(function() {
			let rowData = {};
			$(this).find('input, select').each(function() {
				let name = $(this).attr('name').replace('[]', ''); // 이름에서 '[]' 제거
				let value = $(this).val();
				rowData[name] = value;
			});
			formData.push(rowData);
		});

		jsonString = JSON.stringify(formData);
		 console.log('controller_dynamicTable json : ', jsonString);	
		

		// 숨겨진 필드에 JSON 데이터 설정
		$('#controllerlist').val(jsonString);	  
				
		$("#overlay").show(); // 오버레이 표시
		$("button").prop("disabled", true); // 모든 버튼 비활성화		
				
			Toastify({
					text: "저장중...",
					duration: 2000,
					close:true,
					gravity:"top",
					position: "center",
					style: {
						background: "linear-gradient(to right, #00b09b, #96c93d)"
					},
				}).showToast();	
		
		
		// 결재상신이 아닌경우 수정안됨     
		if(Number(num) < 1) 				
				$("#mode").val('insert'); 
             else			
				$("#mode").val('modify'); 
			
		//  console.log($("#mode").val());    
		// 폼데이터 전송시 사용함 Get form         
		var form = $('#board_form')[0];  	    	
		var datasource = new FormData(form); 

		// console.log(data);
		if (ajaxRequest_write !== null) {
			ajaxRequest_write.abort();
		}		 
		ajaxRequest_write = $.ajax({
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
				// console.log(data);
				setTimeout(function(){									
							if (window.opener && !window.opener.closed) {
								// 부모 창에 restorePageNumber 함수가 있는지 확인
								if (typeof window.opener.restorePageNumber === 'function') {
									window.opener.restorePageNumber(); // 함수가 있으면 실행
								}								
							}								
				}, 1000);	

					ajaxRequest_write = null ;	
			
						setTimeout(function(){															
								// location.href = "write_form_sub.php?mode=view&num=" + data["num"];
								
								hideOverlay();
								self.close();
						}, 1000);	
						
						            
			 
			
			},
			error : function( jqxhr , status , error ){
				console.log( jqxhr , status , error );
						} 			      		
		   });		
			
	}	



// add, remove 버튼 처리부분
$(document).ready(function() {
    $(document).on('click', '.add', function() {
        addNewRow();
    });

    $(document).on('click', '.remove', function() {
        $(this).closest('#dynamicTable tr').remove();		
		updateOrderQuantities();  // 실제 발주량 업데이트
		calculateConditionalSums();
    });

    // Initialize options for existing rows on page load
    $('#dynamicTable tbody tr').each(function() {
        updateOptions(this);
    });
	

    $(document).on('click', '.sub_remove', function() {
        $(this).closest('#sub_dynamicTable tr').remove();
		calculateConditionalSums();
    });

    // Initialize options for existing rows on page load
    $('#sub_dynamicTable tbody tr').each(function() {
        sub_updateOptions(this);
    });
	
	// 연동제어기 버튼에 대한 처리
    $(document).on('click', '.controller_add', function() {
        controller_addNewRow();
    });

    $(document).on('click', '.controller_remove', function() {
        $(this).closest('#controller_dynamicTable tr').remove();				
		calculateConditionalSums();
    });

    // Initialize options for existing rows on page load
    $('#controller_dynamicTable tbody tr').each(function() {
        controller_updateOptions(this);
    });
		
	
});

$(document).ready(function(){
	
var mode = '<?php echo $mode; ?>';
var level = '<?php echo $level; ?>';

	if(mode==='copy')
	{
	// data 초기화
		Swal.fire({
		  title: '데이터 복사',
		  text: "기본사항을 제외하고 초기화 하실래요?",
		  icon: 'warning',
		  showCancelButton: true,
		  confirmButtonColor: '#3085d6',
		  cancelButtonColor: '#d33',
		  confirmButtonText: '네 그렇게 합시다!'
		}).then((result) => {
		  if (result.isConfirmed) {
			  // 실제 코드입력
				$('#board_form').find('input').each(function(){ $(this).val(''); });
				$('#board_form').find('textarea').each(function(){ $(this).val(''); });

				$('#workplacename').val("<? echo $workplacename; ?>");
				$('#address').val("<? echo $address; ?>");
				$('#orderdate').val(getToday());
				if(Number(level) < 5)
					$('#registerdate').val(getToday());				

			Swal.fire(
			  '처리되었습니다.',
			  '데이터가 성공적으로 복사되었습니다.',
			  'success'
			)
		  }
		  else
		  {  
				$('#board_form').find('input').each(function(){ $(this).val(''); });
				$('#board_form').find('textarea').each(function(){ $(this).val(''); });

				$('#workplacename').val("<? echo $workplacename; ?>");
				$('#address').val("<? echo $address; ?>");
				$('#secondord').val("<? echo $secondord; ?>");
				$('#secondordman').val("<? echo $secondordman; ?>");
				$('#secondordmantel').val("<? echo $secondordmantel; ?>");

				$('#orderdate').val(getToday());
				if(Number(level) < 5)
					$('#registerdate').val(getToday());
	
		  }	  
		});		
	  }

	if(mode==='split') // 분할
	{
			// data 초기화
		Swal.fire({
		  title: '데이터 분할&복사',
		  text: "기존 데이터를 여러개로 나눌때 사용합니다. \n\r 기존의 모든 데이터를 그대로 복사하는 개념입니다. ",
		  icon: 'warning',
		  showCancelButton: true,
		  confirmButtonColor: '#3085d6',
		  cancelButtonColor: '#d33',
		  confirmButtonText: '네 합시다!'
		}).then((result) => {
		  if (result.isConfirmed) {
			  // 실제 코드입력

			Swal.fire(
			  '처리되었습니다.',
			  '데이터가 성공적으로 복사되었습니다.',
			  'success'
			)
		  }
		  
		});	
    }
	
});
</script>

<!-- 모터, 브라켓 초기 보여주는 루틴 -->
<script>

function getSafeInputValue(input) {
    if (input instanceof jQuery) {
        return input.val() ? input.val().trim() : '';
    } else if (input && input.value) {
        return input.value.trim();
    }
    return '';
}


$(document).ready(function() {    
    var powerOptions = <?php echo json_encode($powerOptions); ?>;    
    var capacityOptions = <?php echo json_encode($capacityOptions); ?>;
    var unitOptions = <?php echo json_encode($unitOptions); ?>;
    var securityOptions = <?php echo json_encode($securityOptions); ?>;
    var wirelessOptions = <?php echo json_encode($wirelessOptions); ?>;
    var mode = '<?php echo $mode; ?>';
	
	$("#thead_main").hide();

    var order_jsonObj = [];
    try {
        order_jsonObj = JSON.parse('<?php echo addslashes($orderlist); ?>');
        // console.log('Initial read json orderlist :', order_jsonObj);

        // Dynamically setting Row_COUNT based on the length of the order_jsonObj
        const Row_COUNT = order_jsonObj ? order_jsonObj.length : 0;
        // console.log('Row_COUNT :', Row_COUNT);
        const COL_NAMES = 14;    
        const columns = Array.from({ length: COL_NAMES }, (_, i) => 'col' + (i + 1));

        if (Row_COUNT !== 0 && typeof(Row_COUNT) !== 'undefined') {
			$("#thead_main").show();
            // Iterating over each item in the JSON object to append rows
            order_jsonObj.forEach(function(item, index) {
                var newRow = $('<tr>');
                columns.forEach(function(col) {
                    var value = item[col] || '';
                    var inputHTML;

                    // Check if the column should use a select input
                    if (['col1', 'col2', 'col3', 'col4', 'col5', 'col6', 'col7'].includes(col)) {
                        var options = (col === 'col1') ? powerOptions :
                                      (col === 'col2') ? wirelessOptions :
                                      (col === 'col3') ? securityOptions :                                      
                                      (col === 'col4') ? capacityOptions :
                                      (col === 'col5') ? unitOptions : null;
                            // 브라켓 결정          
							if (col === 'col6') {								
								var typeDependentValue = item['col3'];
								options = (typeDependentValue.includes('스크린') || typeDependentValue.includes('제연')) ? 
										  ['','380*180'] :['','530*320', '600*350', '690*390', '910*600'] ;
							}          
							// 후렌지 결정                            
							if (col === 'col7') {								
								var typeDependentValue = item['col3']; 
								options = (typeDependentValue.includes('스크린') || typeDependentValue.includes('제연')) ? 
										  ['','2-4″', '2-5″', '2-6″', '3-4″', '3-5″', '3-6″', '4-5″', '4-6″'] : ['', '4″', '5″', '6″', '8″', '10″'];
							}
                        
                        if (col === 'col3' ||  col === 'col4' || col === 'col5' ) {
                            inputHTML = '<select name="' + col + '[]" class="form-control text-center  optionSelector ' + 
                                        (col === 'col3' ? 'type securityField' : col === 'col4' ? 'capacity' :  col === 'col5' ? 'unit' : '') + 
                                        '" onchange="updateOptions(this.closest(\'tr\'))">' + generateOptions(options, value) + '</select>';
                        } else {
                            inputHTML = '<select name="' + col + '[]" class="form-control text-center   ' + 
                                        (col === 'col6' ? 'bracketSize' : 
                                         col === 'col7' ? 'flangeSize' : '' ) + '" onchange="updatePrice(this.closest(\'tr\'))">'  + generateOptions(options, value) + '</select>';
                        }
                    } else {                        
                        if (col === 'col8'  ) { // (수량) 숫자만 입력가능하게
                            inputHTML = '<input type="text" name="' + col + '[]" class="form-control text-center unitField"  autocomplete="off"  value="' + value + '" onkeyup="inputNumberFormat(this); updatePrice(this.closest(\'tr\'));" />';
							} else if ( col === 'col9' ) { // 단가 숫자만 입력가능, 읽기만 가능
									inputHTML = '<input type="text" name="' + col + '[]" class="form-control text-center  priceField" readonly value="' + value + '" />';
								} else if ( col === 'col13' || col === 'col14' ) {  // 로트번호 전달사항
									inputHTML = '<input type="text" name="' + col + '[]" class="form-control text-center"  autocomplete="off" value="' + value + '" />';
								}  else if ( col === 'col11'  ) {  // 할인
									inputHTML = '<input type="text" name="' + col + '[]" class="form-control text-center text-primary dcField" value="' + value + '"  readonly />';
								}  else if ( col === 'col10'  ) {  // 금액
									inputHTML = '<input type="text" name="' + col + '[]" class="form-control text-center text-secondary amountField" value="' + value + '"  readonly />';
								}	 else {
									inputHTML = '<input type="text" name="' + col + '[]" class="form-control text-center fw-bold  totalField" value="' + value + '" readonly />';
								}
                    }
                    newRow.append('<td class="text-center">' + inputHTML + '</td>');
                });

                if (mode !== 'view') {
                    newRow.append('<td class="text-center"><button type="button" class="btn btn-dark btn-sm add" style="margin-right: 5px;">+</button><button type="button" class="btn btn-danger btn-sm remove">-</button></td>');
                }
				
				
                $('#dynamicTable tbody').append(newRow);

				newRow.find('.optionSelector').on('input change', function() {
					updatePrice(newRow);
				});
		
				
            });     
        }
    } catch (e) {
        console.error("JSON parsing error: ", e);
    }
});


// Helper function to generate options with a selected option
function generateOptions(options, selected) {
    return options.map(function(option) {
        var isSelected = option === selected ? ' selected' : '';
        return '<option value="' + option + '"' + isSelected + '>' + option + '</option>';
    }).join('');
}

function addNewRow() {
    var $tableBody = $('#dynamicTable tbody');
    var $newRow;
	
	$("#thead_main").show();

    // Check if the table has any rows
    if ($tableBody.find('tr').length > 0) {
        $newRow = $tableBody.find('tr:first').clone(true); // Clone the first row with event handlers
		$newRow = createDefaultRow(); // Call function to create a default row
    } else {
        $newRow = createDefaultRow(); // Call function to create a default row
    }

    // Clear the values and reset the first option if needed
    $newRow.find('input').val('');
    $newRow.find('select').each(function() {
        this.selectedIndex = 0; // Reset select to the first option
    });

    // Update the add and remove buttons
    $newRow.find('td:last').html('<button type="button" class="btn btn-dark btn-sm add" style="margin-right: 5px;">+</button>' +
                                  '<button type="button" class="btn btn-danger btn-sm remove">-</button>');

    // Append the new row
    $newRow.appendTo($tableBody);

    // Call updateOptions explicitly to set up the row correctly
    updateOptions($newRow);
}

// 모터,브라켓 추가
function createDefaultRow() {
    var powerOptions = <?php echo json_encode($powerOptions); ?>;    
    var capacityOptions = <?php echo json_encode($capacityOptions); ?>;
    var unitOptions = <?php echo json_encode($unitOptions); ?>;
    var securityOptions = <?php echo json_encode($securityOptions); ?>;
    var wirelessOptions = <?php echo json_encode($wirelessOptions); ?>;	
    var mode = '<?php echo $mode; ?>';	
	var priceData = <?php echo json_encode($priceData); ?>;
	
	// console.log(priceData);
	
    // Assuming Row_COUNT is globally accessible or passed as an argument if needed
    var newRow = $('<tr>');
    newRow.append('<td class="text-center" ><select name="col1[]" class="form-control  text-center optionSelector">' + generateOptions(powerOptions) + '</select></td>');
    newRow.append('<td class="text-center"><select name="col2[]" class="form-control  text-center optionSelector" >' + generateOptions(wirelessOptions) + '</select></td>');
    newRow.append('<td class="text-center"><select name="col3[]" class="form-control  text-center type securityField optionSelector" onchange="updateOptions(this.closest(\'tr\'))">' + generateOptions(securityOptions) + '</select></td>');			
    newRow.append('<td class="text-center"><select name="col4[]" class="form-control text-center capacity optionSelector"  onchange="updateOptions(this.closest(\'tr\'))">' + generateOptions(capacityOptions) + '</select></td>');
    newRow.append('<td class="text-center"><select name="col5[]" class="form-control text-center unit optionSelector" onchange="updateOptions(this.closest(\'tr\'))">' + generateOptions(unitOptions) + '</select></td>');
    newRow.append('<td class="text-center"><select name="col6[]" class="form-control  text-center bracketSize " onchange="updatePrice(this.closest(\'tr\'))" ></select></td>'); // Assuming options will be added dynamically
    newRow.append('<td class="text-center"><select name="col7[]" class="form-control  text-center flangeSize " ></select></td>'); // Assuming options will be added dynamically    
    newRow.append('<td><input type="text" name="col8[]" class="form-control text-center unitField "   autocomplete="off" onkeyup="inputNumberFormat(this); updatePrice(this.closest(\'tr\'));" /></td>');
    newRow.append('<td><input type="text" name="col9[]" class="form-control text-center optionSelector priceField "  readonly > </td>'); 
    newRow.append('<td><input type="text" name="col10[]" class="form-control text-center text-secondary amountField" readonly > </td>'); // 금액
    newRow.append('<td><input type="text" name="col11[]" class="form-control text-center text-primary dcField" readonly > </td>'); // 할인
    newRow.append('<td><input type="text" name="col12[]" class="form-control text-center fw-bold totalField" readonly > </td>'); // 확정금액
	newRow.append('<td><input type="text" name="col13[]" class="form-control text-center " autocomplete="off" /></td>'); // 로트번호
    newRow.append('<td><input type="text" name="col14[]" class="form-control text-center "  autocomplete="off"> </td>'); // 전달사항
    if (mode !== 'view') {
        newRow.append('<td class="text-center"><button type="button" class="btn btn-dark btn-sm add" style="margin-right: 5px;">+</button></td>');
    }
	
	newRow.find('.optionSelector').on('input change', function() {
		updateOptions(newRow);
	});
	
	updatePrice(newRow);
	
    return newRow;
}

var initialSetup = true; // Set this based on your app's logic


function updateOptions(rowElement) {
    var $row = $(rowElement);
    var $typeSelect = $row.find('.type');
    var $unitSelect = $row.find('.unit');
    var $capacitySelect = $row.find('.capacity');
    var $bracketSizeSelect = $row.find('.bracketSize').empty();
    var $flangeSizeSelect = $row.find('.flangeSize').empty();

    // 타입이 변경되었는지 체크하기 위해 이전 타입 저장
    var oldType = $typeSelect.data('oldType');
    var typeSelected = $typeSelect.val();

    if ($typeSelect.length === 0 || typeSelected === undefined) {
        console.error('Type select element is not found or value is not set in the row:', $row);
        return;
    }

    var unitSelected = $unitSelect.val();
    var capacityOptions = ['150k', '300k', '400k', '500k', '600k', '800k', '1000k', '1500k', '2000k'];
    var bracketSizeOptions = ['530*320', '600*350', '690*390', '910*600'];
    var flangeSizeOptions = ['4″', '5″', '6″', '8″', '10″'];

    if (typeSelected === '스크린' || typeSelected === '제연') {
        capacityOptions = ['150k', '300k', '400k'];
        bracketSizeOptions = ['380*180'];
        flangeSizeOptions = typeSelected === '스크린' ? ['2-4″', '2-5″', '2-6″', '3-4″', '3-5″', '3-6″', '4-5″', '4-6″'] : ['4″'];
    }

    // 이전에 선택된 용량 저장, 타입 변경 이전에 값을 가져와야 함
    var currentCapacity = $capacitySelect.val(); 

    $capacitySelect.empty();
    capacityOptions.forEach(function(size) {
        $capacitySelect.append($('<option>', { value: size, text: size }));
    });

    if (oldType !== typeSelected && capacityOptions.includes(currentCapacity)) {
        $capacitySelect.val(currentCapacity);
    } else if (oldType === typeSelected) {
        // 타입이 변경되지 않았다면 사용자의 새로운 선택을 유지
        $capacitySelect.val(currentCapacity);
    } else {
        $capacitySelect.val(capacityOptions[0]); // 타입 변경시 첫 번째 옵션 선택
    }

    $typeSelect.data('oldType', typeSelected); // 현재 타입을 이전 타입으로 저장

    if (unitSelected === '모터단품') {
        $bracketSizeSelect.empty().append($('<option>', { value: '', text: '' }));
        $flangeSizeSelect.empty().append($('<option>', { value: '', text: '' }));
    } else {    
        $bracketSizeSelect.empty();
        bracketSizeOptions.forEach(function(size) {
            $bracketSizeSelect.append($('<option>', { value: size, text: size }));
        });

        $flangeSizeSelect.empty();
        flangeSizeOptions.forEach(function(size) {
            $flangeSizeSelect.append($('<option>', { value: size, text: size }));
        });
    }
	// console.log("updateOptions called with row:", $row);
	updatePrice($row);
}
// 주자재의 각 행이 변동할 때 재계산하는 로직
function updatePrice(row) {
    row = $(row); // 이 코드를 추가하여 row를 jQuery 객체로 변환
    var priceData = <?php echo json_encode($priceData); ?>;
    
    // Safely fetch and process input values
    var power = getSafeInputValue(row.find('[name="col1[]"]')).replace(/\s+/g, '').toUpperCase();
    var wireless = getSafeInputValue(row.find('[name="col2[]"]')).replace(/\s+/g, '').toUpperCase();
    var security = getSafeInputValue(row.find('[name="col3[]"]')).replace(/\s+/g, '').toUpperCase();
    var capacity = getSafeInputValue(row.find('[name="col4[]"]')).replace(/\s+/g, '').toUpperCase();
    var unit = getSafeInputValue(row.find('[name="col5[]"]')).replace(/\s+/g, '').toUpperCase();
    var bracket = getSafeInputValue(row.find('[name="col6[]"]')).replace(/\s+/g, '').toUpperCase();
    var unitsu = getSafeInputValue(row.find('[name="col8[]"]')).replace(/\s+/g, '').toUpperCase();
    
    // 콤마를 제거한 후 숫자로 변환
    unitsu = unitsu.replace(/,/g, '');
    unitsu = parseFloat(unitsu);

    var amountField = 0;
    var dcField = 0;
    var totalField = 0;

    // console.log("priceData values:", priceData);
    // console.log("row values:", row);
    // console.log("Processed values:", power, wireless, security, capacity, unit, bracket);

    var computedPrice = 0;
    if (unit === '브라켓트') {
        // Simplify the check to focus on security and unit only
        var bracketKey = bracket.replace(/\s+/g, '').toUpperCase();
        if (priceData[""] && priceData[""][""] && priceData[""][""][bracketKey] && priceData[""][""][bracketKey][""] && priceData[""][""][bracketKey][""][unit]) {
            computedPrice = priceData[""][""][bracketKey][""][unit];
        }
    } else {
        var powerKey = power.replace(/\s+/g, '').toUpperCase();
        var wirelessKey = wireless.replace(/\s+/g, '').toUpperCase();
        var securityKey = security.replace(/\s+/g, '').toUpperCase();
        var capacityKey = capacity.replace(/\s+/g, '').toUpperCase();
        var unitKey = unit.replace(/\s+/g, '').toUpperCase();
        
        if (priceData[powerKey] && priceData[powerKey][wirelessKey] && priceData[powerKey][wirelessKey][securityKey] && priceData[powerKey][wirelessKey][securityKey][capacityKey] && priceData[powerKey][wirelessKey][securityKey][capacityKey][unitKey]) {
            computedPrice = priceData[powerKey][wirelessKey][securityKey][capacityKey][unitKey];
        }
    }
    
    amountField = computedPrice * Number(unitsu);
    
    // console.log("computedPrice values:", computedPrice);
    
    // Set the computed price with formatting for thousands separators
    row.find('.priceField').val(computedPrice.toLocaleString());

    // 스크린 할인
    var screenDcType = document.querySelector('input[name="screen_dc_type"]:checked').value;
    var screenDcValue = 0; // 기본 할인율은 0으로 설정

    if (screenDcType === "screen_company_dc") {
        screenDcValue = parseFloat(document.getElementById("screen_company_dc_value").value) || 0;
    } else if (screenDcType === "screen_site_dc") {
        screenDcValue = parseFloat(document.getElementById("screen_site_dc_value").value) || 0;
    }
    
    // 일반 할인
    var dcType = document.querySelector('input[name="dc_type"]:checked').value;
    var dcValue = 0; // 기본 할인율은 0으로 설정

    if (dcType === "company_dc") {
        dcValue = parseFloat(document.getElementById("company_dc_value").value) || 0;
    } else if (dcType === "site_dc") {
        dcValue = parseFloat(document.getElementById("site_dc_value").value) || 0;
    }
    
    row.find('.amountField').val(amountField.toLocaleString());
    if (security === '스크린' && unit === 'SET') {
        dcField = (amountField * screenDcValue / 100) * -1;
    } else {
        dcField = (amountField * dcValue / 100) * -1;
    }

    // dcField가 0 또는 -0일 경우 빈 문자열로 설정
    if (dcField === 0 || Object.is(dcField, -0)) {
        dcField = '';
    }

    // 결과값을 형식에 맞게 변환하고 입력 필드에 설정
    row.find('.dcField').val(dcField ? dcField.toLocaleString() : dcField);

    // totalField 계산 전에 dcField가 빈 문자열인지 확인하고 0으로 처리
    totalField = amountField + (dcField === '' ? 0 : dcField);
    row.find('.totalField').val(totalField.toLocaleString());

    calculateConditionalSums();
}



</script>

<!-- 연동제어기 보여주는 루틴 -->
<script>
var controllerData = {};
function fetchControllerData(callback) {
    if (ajaxRequest_controller !== null) {
        ajaxRequest_controller.abort();
    }         
    ajaxRequest_controller = $.ajax({
        enctype: 'multipart/form-data',    // file을 서버에 전송하려면 이렇게 해야 함 주의
        processData: false,    
        contentType: false,      
        cache: false,           
        timeout: 600000,             
        url: "fetch_fee_controller.php",
        type: "post",        
        data: '',            
        dataType: "json", 
        success : function(data){
            // console.log(data);        
            controllerData = data["fee_controller"];
            if (callback) {
                callback();
            }
        },
        error : function( jqxhr , status , error ){
            console.log( jqxhr , status , error );
        }                           
    });
}

// controller_dynamicTable 처리
$(document).ready(function(){
    
    var controllerOptions = <?php echo json_encode($controllerOptions); ?>;    
	
	// alert(controllerOptions);

    var mode = '<?php echo $mode; ?>';

    var controllejsonrObj = [];
	$("#thead_controller").hide();
    try {
        controllejsonrObj = JSON.parse('<?php echo addslashes($controllerlist); ?>');
        // console.log('Initial read json controllejsonrObj :', controllejsonrObj);

		// Dynamically setting Row_COUNT based on the length of the controllejsonrObj
		const Row_COUNT = controllejsonrObj ? controllejsonrObj.length : 0;
		// console.log('Row_COUNT :', Row_COUNT);
		const COL_NAMES = 9;    
		const columns = Array.from({ length: COL_NAMES }, (_, i) => 'col' + (i + 1));

		if (Row_COUNT !== 0 && typeof(Row_COUNT) !== 'undefined') {
			$("#thead_controller").show();
			// Iterating over each item in the JSON object to append rows
			controllejsonrObj.forEach(function(item, index) {
				var newRow = $('<tr>');
				columns.forEach(function(col) {
					var value = item[col] || '';
					var inputHTML; // 올바른 범위 내에서 선언되어야 함
						switch (col) {
							case 'col1':									
									inputHTML = '<input type="hidden" name="' + col + '[]" class="form-control text-center">'; // 기존 옵션을 제거해서 hidden 속성으로 남겨둔다.
								break;

							case 'col2':
								// col2는 col1의 선택에 따라 달라짐
								var accessoryOptions = controllerOptions;
								inputHTML = '<select name="' + col + '[]" class="form-control text-center  sub_optionSelector">' + generateOptions(accessoryOptions, value) + '</select>';
								break;

							case 'col3':
								// col3의 경우, 수량을 입력하며 숫자만 입력 가능하게 처리
								inputHTML = '<input type="text" name="' + col + '[]" class="form-control text-center unitField sub_optionSelector"  autocomplete="off" value="' + value + '">';
								break;

							case 'col4':
								// 단가
								inputHTML = '<input type="text" name="' + col + '[]" class="form-control text-center priceField " readonly value="' + value + '">';
								break;
								// 금액
							case 'col5':								
								inputHTML = '<input type="text" name="' + col + '[]" class="form-control text-center  text-secondary amountField" readonly value="' + value + '">';
								break;
							    // 할인
							case 'col6':								
								inputHTML = '<input type="text" name="' + col + '[]" class="form-control text-center text-primary dcField" readonly value="' + value + '">';
								break;
								// 확정금액 
							case 'col7':								
								inputHTML = '<input type="text" name="' + col + '[]" class="form-control text-center  fw-bold totalField" readonly value="' + value + '">';
								break;
								// 로트번호
							case 'col8':								
								inputHTML = '<input type="text" name="' + col + '[]" class="form-control text-center  "  autocomplete="off" value="' + value + '">';
								break;

							case 'col9':
								// 전달사항
								inputHTML = '<input type="text" name="' + col + '[]" class="form-control text-center "  autocomplete="off" value="' + value + '">';
								break;
						}

					if (col !=='col1')
						newRow.append('<td>' + inputHTML + '</td>');
				});

				if (mode !== 'view') {
					newRow.append('<td class="text-center"><button type="button" class="btn btn-dark btn-sm controller_add" style="margin-right: 5px;">+</button><button type="button" class="btn btn-danger btn-sm controller_remove">-</button></td>');
				}
				$('#controller_dynamicTable tbody').append(newRow);
				
                   // 숫자를 넣자마자 움직이는 하는 동적 구문
					$(document).on('change input', '.sub_optionSelector', function() {
						controller_updateOptions($(this).closest('tr'));
					});

			});

		}	
    } catch (e) {
        console.error("JSON parsing error: ", e);
    }
	


});



// Helper function to generate options with a selected option
function generateOptions(options, selected) {
    return options.map(function(option) {
        var isSelected = option === selected ? ' selected' : '';
        return '<option value="' + option + '"' + isSelected + '>' + option + '</option>';
    }).join('');
}

function controller_addNewRow() {
    var $tableBody = $('#controller_dynamicTable tbody');
    var $newRow;	
	
	$("#thead_controller").show();
    // Check if the table has any rows
    if ($tableBody.find('tr').length > 0) {
        $newRow = $tableBody.find('tr:first').clone(true); // Clone the first row with event handlers
		$newRow = create_controllerRow(); 
		 calculateConditionalSums();
    } else {
        $newRow = create_controllerRow(); 
		 calculateConditionalSums();
    }	
    // Clear the values and reset the first option if needed
    $newRow.find('input').val('');
    $newRow.find('select').each(function() {
        this.selectedIndex = 0; // Reset select to the first option
    });

    // Update the add and remove buttons
    $newRow.find('td:last').html('<button type="button" class="btn btn-dark btn-sm controller_add" style="margin-right: 5px;">+</button>' +
                                  '<button type="button" class="btn btn-danger btn-sm controller_remove">-</button>');
    // Append the new row
    $newRow.appendTo($tableBody);

    // Call sub_updateOptions explicitly to set up the row correctly
    controller_updateOptions($newRow);
}

function create_controllerRow() {  // 연동제어기
	
    var controllerOptions = <?php echo json_encode($controllerOptions); ?>;    

    var mode = '<?php echo $mode; ?>';
	
    // Create a new row element
    var newRow = $('<tr>');

    // Append a select element for controller options
	// controllerDCOptions = ['','스크린모터 SET','철재모터 및 단품'];									
    // newRow.append('<td class="text-center"><select name="col1[]" class="form-control text-center sub_optionSelector">' + generateOptions(controllerDCOptions, '') + '</select></td>');

    // Append an empty select for accessory items - assuming options are added dynamically elsewhere
    newRow.append('<td><select name="col2[]" class="form-control text-center sub_optionSelector" > ' + generateOptions(controllerOptions, '') + '</select></td>');

    // Append input fields with specific classes and events
    newRow.append('<td><input type="text" name="col3[]" class="form-control text-center  unitField sub_optionSelector"  autocomplete="off" onkeyup="inputNumberFormat(this)"  /></td>');
    newRow.append('<td><input type="text" name="col4[]" class="form-control text-center  priceField " readonly /></td>');
    newRow.append('<td><input type="text" name="col5[]" class="form-control text-center  text-secondary amountField" readonly/></td>');
    newRow.append('<td><input type="text" name="col6[]" class="form-control text-center  text-primary dcField" readonly /></td>');
    newRow.append('<td><input type="text" name="col7[]" class="form-control text-center  fw-bold totalField" readonly /></td>');
    newRow.append('<td><input type="text" name="col8[]" class="form-control text-center  "  autocomplete="off" /></td>');
    newRow.append('<td><input type="text" name="col9[]" class="form-control text-center  "   autocomplete="off" /></td>');

    // Add a button cell if the mode is not 'view'
    if (mode !== 'view') {
        newRow.append('<td class="text-center"><button type="button" class="btn btn-dark btn-sm controller_add" style="margin-right: 5px;">+</button></td>');
    }
	    // 숫자를 넣자마자 움직이는 하는 동적 구문
		$(document).on('change input', '.sub_optionSelector', function() {
			controller_updateOptions($(this).closest('tr'));
		});

    // Return the newly created row
    return newRow;
}

function controller_updateOptions(row) {   
		// console.log(data);
		if (ajaxRequest_controller !== null) {
			ajaxRequest_controller.abort();
		}		 
		ajaxRequest_controller = $.ajax({
			enctype: 'multipart/form-data',    // file을 서버에 전송하려면 이렇게 해야 함 주의
			processData: false,    
			contentType: false,      
			cache: false,           
			timeout: 600000, 			
			url: "fetch_fee_controller.php",
			type: "post",		
			data: '',			
			dataType: "json", 
			success : function(data){
				// console.log(data);		
				controllerData = data["fee_controller"];				
			 
				// Fetch and process jQuery input elements using getSafeInputValue				
				var itemValue = getSafeInputValue(row.find('[name="col2[]"]')); // Using getSafeInputValue to process input
				var unitsu = getSafeInputValue(row.find('[name="col3[]"]'));
				var usageValue = getSafeInputValue(row.find('[name="col1[]"]'));
										 
				// console.log(controllerData); // find 호출 전에 row가 무엇인지 확인				
								
				var computedPrice = 0;
				if (controllerData.hasOwnProperty(itemValue)) {
					var itemDetails = controllerData[itemValue];					
					computedPrice = itemDetails;
				}
				
				var amountField = 0;
				var dcField = 0;
				var totalField = 0;

				amountField = computedPrice * Number(unitsu);
				
				// console.log('amountField', amountField);
				
				var dcType = document.querySelector('input[name="controller_dc_type"]:checked').value;
				var dcValue = 0; // 기본 할인율은 0으로 설정

				if (dcType === "controller_company_dc") {
					dcValue = parseFloat(document.getElementById("controller_company_dc_value").value) || 0;
				} else if (dcType === "controller_site_dc") {
				dcValue = parseFloat(document.getElementById("controller_site_dc_value").value) || 0;
				}	
				
				if (dcValue > 0) {
					dcField = (amountField * dcValue / 100) * -1;
				} 
				
				row.find('.amountField').val(amountField.toLocaleString());

				// dcField가 0 또는 -0일 경우 빈 문자열로 설정
				if (dcField === 0 || Object.is(dcField, -0)) {
					dcField = '';
				}

				// 결과값을 형식에 맞게 변환하고 입력 필드에 설정
				row.find('.dcField').val(dcField ? dcField.toLocaleString() : dcField);

				// totalField 계산 전에 dcField가 빈 문자열인지 확인하고 0으로 처리
				totalField = amountField + (dcField === '' ? 0 : dcField);
				row.find('.totalField').val(totalField.toLocaleString());				
				
				row.find('.priceField').val(computedPrice.toLocaleString());
				calculateConditionalSums();

				ajaxRequest_controller = null ;		
			
			},
			error : function( jqxhr , status , error ){
				console.log( jqxhr , status , error );
						} 			      		
		   });		
}
</script>

<!-- 부속 자재 보여주는 루틴 -->
<script>
$(document).ready(function() {
	// PHP 변수를 JSON으로 인코딩하여 JavaScript 변수로 전달
	var subOptions = <?php echo json_encode(array_map(function($item) {
		return array('label' => $item);
	}, $subOptions)); ?>;
	
	// console.log(subOptions);

	var mode = '<?php echo $mode; ?>';

	var accessoryjsonObj = [];
	
	$("#thead_sub").hide();
	try {
		accessoryjsonObj = JSON.parse('<?php echo addslashes($accessorieslist); ?>');
		
		const Row_COUNT = accessoryjsonObj ? accessoryjsonObj.length : 0;
		const COL_NAMES = 5;
		const columns = Array.from({ length: COL_NAMES }, (_, i) => 'col' + (i + 1));

		if (Row_COUNT !== 0 && typeof(Row_COUNT) !== 'undefined') {
			$("#thead_sub").show();
			accessoryjsonObj.forEach(function(item) {
				var newRow = $('<tr>');
				columns.forEach(function(col) {
					var value = item[col] || '';
					var inputHTML;

					switch (col) {
						case 'col1':
							inputHTML = ' <div class="specialinputWrap"> ' +
							'<input type="text" name="' + col + '[]" class="form-control text-center sub_optionSelector" value="' + value + '">' +
							' <button class="specialbtnClear"></button> </div>';
							break;
						case 'col2':
							inputHTML = '<input type="text" name="' + col + '[]" class="form-control text-center sub_optionSelector surang"  autocomplete="off" value="' + value + '">';
							break;
						case 'col3':
							inputHTML = '<input type="text" name="' + col + '[]" class="form-control text-center unitpriceField" readonly value="' + value + '">';
							break;
						case 'col4':
							inputHTML = '<input type="text" name="' + col + '[]" class="form-control text-center totalField " readonly value="' + value + '">';
							break;
						case 'col5': // 전달사항
							inputHTML = '<input type="text" name="' + col + '[]" class="form-control text-center text-secondary"  autocomplete="off" value="' + value + '">';
							break;
					}

					newRow.append('<td>' + inputHTML + '</td>');
				});

				if (mode !== 'view') {
					newRow.append('<td class="text-center"><button type="button" class="btn btn-dark btn-sm sub_add" style="margin-right: 5px;">+</button><button type="button" class="btn btn-danger btn-sm sub_remove">-</button></td>');
				}
				$('#subAccessoryGroup').append(newRow);

				initializeAutocomplete(newRow.find('input[name="col1[]"]'));

				$(document).on('change input', '.sub_optionSelector', function() {
					sub_updateOptions($(this).closest('tr'));
				});
			});
		}
	} catch (e) {
		console.error("JSON parsing error: ", e);
	}

	function initializeAutocomplete(input) {
		// console.log("initializeAutocomplete ", input);
		$(input).autocomplete({
			source: function(request, response) {
				try {
					var filteredOptions = $.grep(subOptions, function(option) {
						return option.label && option.label.toLowerCase().indexOf(request.term.toLowerCase()) !== -1;
					}).map(function(option) {
						return option.label;
					});
					// console.log("Filtered Options: ", filteredOptions);
					response(filteredOptions);
				} catch (e) {
					console.error("Error in autocomplete source function: ", e);
					response([]);
				}
			},
			select: function(event, ui) {
				// console.log("Selected: ", ui.item.value);
				$(this).val(ui.item.value);
				var row = $(this).closest('tr');
				sub_updateOptions(row);
				return false;
			},
			focus: function(event, ui) {
				$(this).val(ui.item.value);
				var row = $(this).closest('tr');
				sub_updateOptions(row);
				return false;
			}
		});
	}

	$(document).on('click', '.sub_add', function() {
		var newRow = createDefaultSubRow();
		$('#subAccessoryGroup').append(newRow);
		initializeAutocomplete(newRow.find('input[name="col1[]"]'));
	});

	$(document).on('click', '.sub_remove', function() {
		$(this).closest('tr').remove();
	});

	function createDefaultSubRow() {
		$("#thead_sub").show();
		var newRow = $('<tr>');

		newRow.append('<td><div class="specialinputWrap">' +
						'<input type="text" name="col1[]" class="form-control text-center sub_optionSelector" >' +
						'<button class="specialbtnClear"></button></div></td>');

		newRow.append('<td><input type="text" name="col2[]" class="form-control text-center surang sub_optionSelector"  autocomplete="off" ></td>');
		newRow.append('<td><input type="text" name="col3[]" class="form-control text-center unitpriceField" readonly> </td>');
		newRow.append('<td><input type="text" name="col4[]" class="form-control text-center totalField  " readonly > </td>');
		newRow.append('<td><input type="text" name="col5[]" class="form-control text-center"  autocomplete="off"> </td>');

		if (mode !== 'view') {
			newRow.append('<td class="text-center"><button type="button" class="btn btn-dark btn-sm sub_add" style="margin-right: 5px;">+</button><button type="button" class="btn btn-danger btn-sm sub_remove">-</button></td>');
		}

		return newRow;
	}

	// Initialize autocomplete for existing rows in subAccessoryGroup
	$('#subAccessoryGroup input[name="col1[]"]').each(function() {
		initializeAutocomplete(this);
	});

	// Initialize autocomplete for new rows in subAccessoryGroup on input event
	$(document).on('input', '#subAccessoryGroup input[name="col1[]"]', function() {
		initializeAutocomplete(this);
	});

	$(document).on('click', '.specialbtnClear', function(e) {
		e.preventDefault(); // 기본 동작을 방지합니다.
		$(this).siblings('input').val('');
	});
	
	$(document).on('change input', '.sub_optionSelector', function() {
		sub_updateOptions($(this).closest('tr'));
	});	
	
});

var sub_priceData = {};

function fetchSubPriceData() {
    if (ajaxRequest_fee_sub !== null) {
        ajaxRequest_fee_sub.abort();
    }
    ajaxRequest_fee_sub = $.ajax({
        enctype: 'multipart/form-data',    // file을 서버에 전송하려면 이렇게 해야 함 주의
        processData: false,
        contentType: false,
        cache: false,
        timeout: 600000,
        url: "fetch_fee_sub.php",
        type: "post",
        data: '',
        dataType: "json",
        success: function(data) {
            // console.log(data);
            sub_priceData = data["fee_sub"];
            ajaxRequest_fee_sub = null;
        },
        error: function(jqxhr, status, error) {
            console.log(jqxhr, status, error);
        }
    });
}

function sub_updateOptions(row) {
    var itemValue = getSafeInputValue(row.find('[name="col1[]"]')).replace(/\s+/g, '').toUpperCase(); // 공백 제거 및 대문자 변환
    var unitsu = getSafeInputValue(row.find('[name="col2[]"]')).replace(/\s+/g, '').toUpperCase();

    // 콤마를 제거한 후 숫자로 변환
    unitsu = unitsu.replace(/,/g, '');
    unitsu = parseFloat(unitsu);

    var discountType = '';

    // itemValue가 존재하고 sub_priceData에 해당 항목이 있는 경우 가격을 가져옵니다.
    var computedPrice = 0;
    
    // console.log('찾음');
    // console.log('sub_priceData', sub_priceData);
    for (var key in sub_priceData) {
        if (sub_priceData.hasOwnProperty(key)) {
            // 공백 제거 및 대문자 변환 후 비교
            if (key.replace(/\s+/g, '').toUpperCase() === itemValue) {
                //  console.log('찾음');
                var price1 = sub_priceData[key][''];
                var price2 = sub_priceData[key]['할인'];

                if (price1 !== undefined) {
                    computedPrice = parseInt(price1, 10);
                } else if (price2 !== undefined) {
                    computedPrice = parseInt(price2, 10);
                }
                break; // 일치하는 항목을 찾으면 반복문을 중단
            }
        }
    }
    
    var totalField = 0;

    if (!isNaN(computedPrice) && !isNaN(unitsu)) {
        totalField = computedPrice * unitsu;
    } else {
        console.log('Invalid computedPrice or unitsu:', computedPrice, unitsu);
    }

    row.find('.unitpriceField').val(computedPrice.toLocaleString());
    row.find('.totalField').val(totalField.toLocaleString());

    calculateConditionalSums();
}


// 페이지 로드 시 데이터 가져오기
$(document).ready(function() {
    fetchSubPriceData();
});

// 새로 고침 버튼 클릭 시 데이터 다시 가져오기
$('#refreshButton').on('click', function() {
    fetchSubPriceData();
});

</script>

<!-- 주자재 수량을 추가 삭제할때 자동업데이트-->
<script>
// 주자재 수량을 추가 삭제할때 자동업데이트 코드
$(document).ready(function(){
    var powerOptions = <?php echo json_encode($powerOptions); ?>;
    // ... other initializations

    // Event handler for changes in the dynamic table
    $('#dynamicTable').on('change keyup', 'input, select', function() {
        updateOrderQuantities();
    });

      // Initialize the table once
    updateOrderQuantities();
	
    // 초기 계산 수행
    updateAllTotals();

    // 입력 필드의 변경사항 감지 및 계산
    $('.calculate_set').on('keyup change', function() {
        updateAllTotals();
    });
    
});

function updateOrderQuantities() {
	// Define object to hold total quantities for each type
	var totals = {
		screens: 0,
		steels: 0,
		protects: 0,
		smokes: 0,
		explosions : 0
	};

	// Iterate through each row of the table to accumulate quantities
	$('#dynamicTable tbody tr').each(function() {
		var type = $(this).find('select[name="col3[]"]').val(); // Assuming col3 is the type
		var quantity = parseInt($(this).find('input[name="col8[]"]').val()) || 0; // Assuming col8 is the quantity

		switch (type) {
			case '스크린': totals.screens += quantity; break;
			case '철재': totals.steels += quantity; break;
			case '방범': totals.protects += quantity; break;
			case '제연': totals.smokes += quantity; break;
			case '방폭': totals.explosions += quantity; break;
		}
	});

	// Update the main table inputs
	$('#realscreensu').val(totals.screens);
	$('#realsteelsu').val(totals.steels);
	$('#realprotectsu').val(totals.protects);
	$('#realsmokesu').val(totals.smokes);
	$('#realexplosionsu').val(totals.explosions);

	// Re-calculate totals and differences
	updateAllTotals();
}

function updateAllTotals() {
	// 주문 수량 합계 계산
	calculateTotalForRow($('#order_total'), ['#screensu', '#steelsu', '#protectsu', '#smokesu', '#explosionsu']);

	// 발주 수량 합계 계산
	calculateTotalForRow($('#realendsu'), ['#realscreensu', '#realsteelsu', '#realprotectsu', '#realsmokesu', '#realexplosionsu']);

	// 미출고 수량 합계 계산
	calculateDifferencesForRow($('#noendsu'), ['#noscreensu', '#nosteelsu', '#noprotectsu', '#nosmokesu', '#noexplosionsu'],
		['#screensu', '#steelsu', '#protectsu', '#smokesu', '#explosionsu'], ['#realscreensu', '#realsteelsu', '#realprotectsu', '#realsmokesu', '#realexplosionsu']);
}

function calculateTotalForRow(totalField, fields) {
	var total = 0;
	fields.forEach(function(field) {
		var value = $(field).val();
		total += parseFloat(value) || 0;
	});
	totalField.val(total);
}

function calculateDifferencesForRow(totalField, differenceFields, orderFields, actualFields) {
	differenceFields.forEach(function(field, index) {
		var orderedValue = parseFloat($(orderFields[index]).val()) || 0;
		var actualValue = parseFloat($(actualFields[index]).val()) || 0;
		var difference = orderedValue - actualValue;
		// $(field).val(difference.toFixed(2)); // 소수점 두 자리로 반올림
		$(field).val(difference); 
	});
    // 미출고 수량 합계 재계산
    calculateTotalForRow(totalField, differenceFields);	
	updatedcPrice(); // 할인율에 따라 할인가를 계산
	screen_updatedcPrice(); // 할인율에 따라 할인가를 계산
}

$(document).ready(function() {
    // 색을 칠할 ID들을 배열에 저장
    var ids = [ "order_total" ,"realendsu", "noendsu" , "realscreensu", "noscreensu"  , "realsteelsu", "nosteelsu"  , "realprotectsu", "noprotectsu" , "realsmokesu", "nosmokesu" , "realexplosionsu", "noexplosionsu" ];  

    // 배열을 순회하면서 각 요소에 스타일 적용
    ids.forEach(function(id) {
        $('#' + id).css('background-color', '#FFEBF0');
    });
});
</script>

<!-- log보기 삭제버튼 처리 등 -->
<script>
$(document).ready(function(){	
	
	// Log 파일보기
	$("#showlogBtn").click( function() {     	
		var num = '<?php echo $num; ?>' 
		// table 이름을 넣어야 함
		var workitem =  'motor' ;
		// 버튼 비활성화
		var btn = $(this);						
			popupCenter("../Showlog.php?num=" + num + "&workitem=" + workitem , '로그기록 보기', 500, 500);									 
		btn.prop('disabled', false);					 					 
	});		
		
// 삭제버튼
$("#deleteBtn").click(function () {
    var first_writer = '<?php echo $first_writer; ?>';
    var level = '<?php echo $level; ?>';
    var status = $('#status').val(); // 현재 select의 값

    if (status !== '접수대기') {
        Swal.fire({
            title: '삭제불가',
            text: "접수대기 상태일 때만 삭제가 가능합니다.",
            icon: 'error',
            confirmButtonText: '확인'
        });
        return; // 함수 종료
    }

    if (!first_writer.includes(first_writer) && level !== '1') {
        Swal.fire({
            title: '삭제불가',
            text: "등록상태일 때만 삭제 가능합니다.",
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
                $("#mode").val('delete'); // `mode` 값을 설정
                var form = $('#board_form')[0];
                var formData = new FormData(form); // `formData`를 여기에서 정의합니다.

                // `formData`에 필요한 추가 데이터를 수동으로 설정
                formData.set('mode', $("#mode").val());
                formData.set('num', $("#num").val());

                // console.log('mode', $("#mode").val());
                // console.log('num', $("#num").val());

                if ((typeof ajaxRequest_write !== 'undefined' && ajaxRequest_write) || ajaxRequest_write !== null) {
                    ajaxRequest_write.abort();
                }

                ajaxRequest_write = $.ajax({
                    enctype: 'multipart/form-data',    // file을 서버에 전송하려면 이렇게 해야 함 주의
                    processData: false,
                    contentType: false,
                    cache: false,
                    timeout: 1000000,
                    url: "insert.php",
                    type: "post",
                    data: formData,
                    dataType: "json",
                    success: function (data) {
                        // console.log(data);
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
                        setTimeout(function () {
                            if (window.opener && !window.opener.closed) {
                                // 부모 창에 restorePageNumber 함수가 있는지 확인
                                if (typeof window.opener.restorePageNumber === 'function') {
                                    window.opener.restorePageNumber(); // 함수가 있으면 실행
                                }
                                window.opener.location.reload(); // 부모 창 새로고침
                                window.close();
                            }

                        }, 1000);

                    },
                    error: function (jqxhr, status, error) {
                        console.log(jqxhr, status, error);
                    }
                });
            }
        });
    }

});

}); // end of ready document   
</script>

<!-- 단가변동될때마다 합계표 내주기 calculateConditionalSums -->
<script>
function calculateConditionalSums() {
	
    var totalScreenSet = 0;
    var totalOther = 0;
    var totalNodc = 0;
    var totalcontrollerNodc = 0;
    var totalcontrollerdc = 0;

    $("#dynamicTable tbody tr").each(function() {
        var purposeElement = $(this).find('select[name="col3[]"]');
        var unitElement = $(this).find('select[name="col5[]"]');
        var surangElement = $(this).find('input[name="col8[]"]');   
        var unitpriceElement = $(this).find('input[name="col9[]"]'); 

        // Ensure elements exist and have values before processing
        if (purposeElement.length > 0 && unitElement.length > 0 && surangElement.length > 0 && unitpriceElement.length > 0) {
            var purpose = (purposeElement.val() || '').replace(/\s+/g, '').toUpperCase();
            var unit = (unitElement.val() || '').replace(/\s+/g, '').toUpperCase();
            var unitprice = (surangElement.val() || '').replace(/\s+/g, '').toUpperCase();
            var price = (unitpriceElement.val() || '').replace(/,/g, ''); // Correct the regex to handle comma removal for price formatting
            
            price = parseFloat(price) || 0;

            // Check conditions and sum accordingly
            if (purpose === '스크린' && unit === 'SET') {
                totalScreenSet += price * unitprice;
            } else {
                totalOther += price * unitprice;
            }
        }
    });

    // 연동제어기 금액 연산하기
    $("#controller_dynamicTable tbody tr").each(function() {
        var purposeElement = $(this).find('input[name="col1[]"]');        
        var priceElement = $(this).find('input[name="col5[]"]'); 
        var DcElement = $(this).find('input[name="col6[]"]'); 

        // Ensure elements exist and have values before processing
        if (priceElement.length > 0 ) {
            var purpose = (purposeElement.val() || '').replace(/\s+/g, '').toUpperCase();                        
            var price = (priceElement.val() || '').replace(/,/g, ''); // Correct the regex to handle comma removal for price formatting
            var Dcprice = (DcElement.val() || '').replace(/,/g, ''); 

            // Convert price from string to number after ensuring non-empty
            price = parseFloat(price) || 0;
            Dcprice = parseFloat(Dcprice) || 0;
			
			totalcontrollerNodc += price ;
			totalcontrollerdc += (price + Dcprice) ;

        }
    });

    // 부속자재 금액 연산하기
    $("#sub_dynamicTable tbody tr").each(function() {
        var unitElement = $(this).find('select[name="col1[]"]');
        var unitpriceElement = $(this).find('input[name="col2[]"]'); // Assuming price is in an input element, not a select
        var priceElement = $(this).find('input[name="col3[]"]'); // Assuming price is in an input element, not a select

        // Ensure elements exist and have values before processing
        if (unitpriceElement.length > 0 && priceElement.length > 0) {            
            var unit = (unitElement.val() || '').replace(/\s+/g, '').toUpperCase();
            var unitprice = (unitpriceElement.val() || '').replace(/\s+/g, '').toUpperCase();
            var price = (priceElement.val() || '').replace(/,/g, ''); // Correct the regex to handle comma removal for price formatting

            // Convert price from string to number after ensuring non-empty
            price = parseFloat(price) || 0;

            // 할인없음
            totalNodc += price * unitprice;
            
        }
    });

    $('#screen_price').val(totalScreenSet.toLocaleString());
    $('#notdcprice').val(totalNodc.toLocaleString());    
    $('#controller_price').val(totalcontrollerNodc.toLocaleString());    
    $('#controller_dcprice').val(totalcontrollerdc.toLocaleString());    
    $('#price').val(totalOther.toLocaleString());

    // 연동제어기 
    updatedcPrice();
    screen_updatedcPrice();
}

// Call this function on page load and setup triggers if the table changes
$(document).ready(function() {
    calculateConditionalSums();
    // Bind the input event to inputs and change event to selects within elements having the class 'optionSelector'
    $('.optionSelector').on('input change', 'input[type="text"]', calculateConditionalSums);
    $('.sub_optionSelector').on('input change', 'input[type="text"]', calculateConditionalSums);
});
</script>

<!-- DC 처리 부분 -->
<script>
// 기타DC에 대한 함수처리
function screen_handledcType() {
    screen_updatedcPrice(); // 할인율에 따라 할인가를 계산
}

// 스크린SET DC에 대한 함수처리
function screen_setdcType(type) {
    if (type === 'company') {
        document.getElementById('screen_company_dc').checked = true; // 업체DC 라디오 선택
        document.getElementById('screen_site_dc_value').value = ''; 
		
    } else if (type === 'site') {
        document.getElementById('screen_site_dc').checked = true; // 현장DC 라디오 선택
		document.getElementById('screen_company_dc_value').value = ''; 
    }
	else
	{
		document.getElementById('screen_site_dc_value').value = ''; 
	    document.getElementById('screen_company_dc_value').value = ''; 
	}
	
    screen_updatedcPrice(); // 할인율 입력 시 할인가 계산
}

function screen_updatedcPrice() {
    var price = parseFloat(document.getElementById('screen_price').value.replace(/,/g, '')) || 0;
    var dcType = document.querySelector('input[name="screen_dc_type"]:checked').value;
    var dcedPrice = price;

    if (dcType === 'screen_company_dc') {
        var dc = parseFloat(document.getElementById('screen_company_dc_value').value) || 0;
        dcedPrice = screen_calculatedcedPrice(price, dc);
    } else if (dcType === 'screen_site_dc') {
        var dc = parseFloat(document.getElementById('screen_site_dc_value').value) || 0;
        dcedPrice = screen_calculatedcedPrice(price, dc);
    }

    document.getElementById('screen_dcprice').value = screen_formatPrice(dcedPrice); // 결과 출력
	updateTotalPrice(); // add this line to update the total price
}

// 기타DC에 대한 함수처리
function controller_handledcType() {
    controller_updatedcPrice(); // 할인율에 따라 할인가를 계산
}

// 연동제어기 할인에 대한 함수처리
function controller_setdcType(type) {
    if (type === 'company') {
        document.getElementById('controller_company_dc').checked = true; // 업체DC 라디오 선택
        document.getElementById('controller_site_dc_value').value = ''; 
		
    } else if (type === 'site') {
        document.getElementById('controller_site_dc').checked = true; // 현장DC 라디오 선택
		document.getElementById('controller_company_dc_value').value = ''; 
    }
	else
	{
		document.getElementById('controller_site_dc_value').value = ''; 
	    document.getElementById('controller_company_dc_value').value = ''; 
	}
	
    controller_updatedcPrice(); // 할인율 입력 시 할인가 계산
}

function controller_updatedcPrice() {
    var price = parseFloat(document.getElementById('controller_price').value.replace(/,/g, '')) || 0;
    var dcType = document.querySelector('input[name="controller_dc_type"]:checked').value;
    var dcedPrice = price;

    if (dcType === 'controller_company_dc') {
        var dc = parseFloat(document.getElementById('controller_company_dc_value').value) || 0;
        dcedPrice = screen_calculatedcedPrice(price, dc);
    } else if (dcType === 'controller_site_dc') {
        var dc = parseFloat(document.getElementById('controller_site_dc_value').value) || 0;
        dcedPrice = screen_calculatedcedPrice(price, dc);
    }

    document.getElementById('controller_dcprice').value = screen_formatPrice(dcedPrice); // 결과 출력
	updateTotalPrice(); // add this line to update the total price
}

function screen_calculatedcedPrice(price, dc) {
    return price - (price * (dc / 100));
}

function screen_formatPrice(price) {
    return price.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

// 기타DC에 대한 함수처리
function handledcType() {
    updatedcPrice(); // 할인율에 따라 할인가를 계산
}

function setdcType(type) {
    if (type === 'company') {
        document.getElementById('company_dc').checked = true; // 업체DC 라디오 선택
		document.getElementById('site_dc_value').value = ''; 
    } else if (type === 'site') {
        document.getElementById('site_dc').checked = true; // 현장DC 라디오 선택
		document.getElementById('company_dc_value').value = ''; 
    }
	else
	{
		document.getElementById('site_dc_value').value = ''; 
		document.getElementById('company_dc_value').value = ''; 		
	}
    updatedcPrice(); // 할인율 입력 시 할인가 계산
}

function updatedcPrice() {
    var price = parseFloat(document.getElementById('price').value.replace(/,/g, '')) || 0;
    var dcType = document.querySelector('input[name="dc_type"]:checked').value;
    var dcedPrice = price;

    if (dcType === 'company_dc') {
        var dc = parseFloat(document.getElementById('company_dc_value').value) || 0;
        dcedPrice = calculatedcedPrice(price, dc);
    } else if (dcType === 'site_dc') {
        var dc = parseFloat(document.getElementById('site_dc_value').value) || 0;
        dcedPrice = calculatedcedPrice(price, dc);
    }

    document.getElementById('dcprice').value = formatPrice(dcedPrice); // 결과 출력
	updateTotalPrice(); // add this line to update the total price
}

function calculatedcedPrice(price, dc) {
    return price - (price * (dc / 100));
}

function formatPrice(price) {
    return price.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

// 3가지 유형의 자료 전체 합계표를 계산
// 합계표를 계산해서 넣는다.
var controllerData = {};

function fetchControllerData(callback) {
    if (ajaxRequest_controller !== null) {
        ajaxRequest_controller.abort();
    }         
    ajaxRequest_controller = $.ajax({
        enctype: 'multipart/form-data',    // file을 서버에 전송하려면 이렇게 해야 함 주의
        processData: false,    
        contentType: false,      
        cache: false,           
        timeout: 600000,             
        url: "fetch_fee_controller.php",
        type: "post",        
        data: '',            
        dataType: "json", 
        success : function(data){
           //  console.log(data);        
            controllerData = data["fee_controller"];
            if (callback) {
                callback();
            }
        },
        error : function( jqxhr , status , error ){
            console.log( jqxhr , status , error );
        }                           
    });
}


// 합계표를 계산해서 넣는다.
// 전체 테이블의 가격을 재계산
function updateTotalPrice() {
    // 데이터가 없는 경우 데이터 로드
    if (Object.keys(controllerData).length === 0) {
        fetchControllerData(updateTotalPrice);
        return;
    }

    // 테이블의 각 행에 대해 처리
    // 일반 할인
    
    var main_total1 = 0;  // 주자재 금액합
    var main_total2 = 0;  // 주자재 할인합
    var main_total3 = 0;  // 주자재 확정금액
    
    var controller_total1 = 0;  // 연동제어기 금액합
    var controller_total2 = 0;  // 연동제어기 할인합
    var controller_total3 = 0;  // 연동제어기 확정금액
    
    var sub_total3 = 0;  // 부속자재 금액합

	// 부속자재
    var dcTypeElement = document.querySelector('input[name="dc_type"]:checked');
    var dcValue = 0; // 기본 할인율은 0으로 설정

    if (dcTypeElement) {
        var dcType = dcTypeElement.value;
        if (dcType === "company_dc") {
            var companyDcElement = document.getElementById("company_dc_value");
            if (companyDcElement) {
                dcValue = parseFloat(companyDcElement.value) || 0;
            }
        } else if (dcType === "site_dc") {
            var siteDcElement = document.getElementById("site_dc_value");
            if (siteDcElement) {
                dcValue = parseFloat(siteDcElement.value) || 0;
            }
        }
    }

    // 스크린 할인
    var screenDcTypeElement = document.querySelector('input[name="screen_dc_type"]:checked');
    var screenDcValue = 0; // 기본 할인율은 0으로 설정

    if (screenDcTypeElement) {
        var screenDcType = screenDcTypeElement.value;
        if (screenDcType === "screen_company_dc") {
            var screenCompanyDcElement = document.getElementById("screen_company_dc_value");
            if (screenCompanyDcElement) {
                screenDcValue = parseFloat(screenCompanyDcElement.value) || 0;
            }
        } else if (screenDcType === "screen_site_dc") {
            var screenSiteDcElement = document.getElementById("screen_site_dc_value");
            if (screenSiteDcElement) {
                screenDcValue = parseFloat(screenSiteDcElement.value) || 0;
            }
        }
    }

    // 연동제어기 할인
    var controllerDcTypeElement = document.querySelector('input[name="controller_dc_type"]:checked');
    var controllerDcValue = 0; // 기본 할인율은 0으로 설정

    if (controllerDcTypeElement) {
        var controllerDcType = controllerDcTypeElement.value;
        if (controllerDcType === "controller_company_dc") {
            var controllerCompanyDcElement = document.getElementById("controller_company_dc_value");
            if (controllerCompanyDcElement) {
                controllerDcValue = parseFloat(controllerCompanyDcElement.value) || 0;
            }
        } else if (controllerDcType === "controller_site_dc") {
            var controllerSiteDcElement = document.getElementById("controller_site_dc_value");
            if (controllerSiteDcElement) {
                controllerDcValue = parseFloat(controllerSiteDcElement.value) || 0;
            }
        }
    }

    // 부속 자재에 대한 합계표 작성
    document.querySelectorAll('#sub_dynamicTable tbody tr').forEach(function(row) {

        var surangFieldElement = row.querySelector('.surang');
        var surang = 0;
        if (surangFieldElement) {
            surang = parseFloat(surangFieldElement.value.replace(/,/g, '')) || 0;
        }
		
        var unitpriceFieldElement = row.querySelector('.unitpriceField');
        var unitprice = 0;
        if (unitpriceFieldElement) {
            unitprice = parseFloat(unitpriceFieldElement.value.replace(/,/g, '')) || 0;
        }

        var total = surang * unitprice;

        var totalFieldElement = row.querySelector('.totalField');
        if (totalFieldElement) {            
            totalFieldElement.value = total.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        sub_total3 += total;
    });

    // 주자재에 대한 합계표 작성
    document.querySelectorAll('#dynamicTable tbody tr').forEach(function(row) {
        var amountFieldElement = row.querySelector('.amountField');
        var amountField = 0;
        if (amountFieldElement) {
            amountField = parseFloat(amountFieldElement.value.replace(/,/g, '')) || 0;
        }

        var securityElement = row.querySelector('.securityField');
        var security = securityElement ? securityElement.value : '';

        var unitElement = row.querySelector('.unit');
        var unit = unitElement ? unitElement.value : '';

        var priceFieldElement = row.querySelector('.priceField');
        var priceField = 0;
        if (priceFieldElement) {
            priceField = parseFloat(priceFieldElement.value.replace(/,/g, '')) || 0;
        }

        var unitFieldElement = row.querySelector('.unitField');
        var unitField = 0;
        if (unitFieldElement) {
            unitField = parseFloat(unitFieldElement.value.replace(/,/g, '')) || 0;
        }
        
        amountField = unitField * priceField;

        var dcField;
        if (security === '스크린' && unit === 'SET') {
            dcField = (amountField * screenDcValue / 100) * -1;
        } else {
            dcField = (amountField * dcValue / 100) * -1;
        }

        // dcField가 0 또는 -0일 경우 빈 문자열로 설정
        if (dcField === 0 || Object.is(dcField, -0)) {
            dcField = '';
        }

        // 결과값을 형식에 맞게 변환하고 입력 필드에 설정
        if (amountFieldElement) {
            amountFieldElement.value = amountField.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }
        var dcFieldElement = row.querySelector('.dcField');
        if (dcFieldElement) {
            dcFieldElement.value = dcField ? dcField.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',') : dcField;
        }
        var totalFieldElement = row.querySelector('.totalField');
        if (totalFieldElement) {
            var totalField = amountField + (dcField === '' ? 0 : dcField);
            totalFieldElement.value = totalField.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        main_total1 += amountField;
        main_total2 += (dcField === '' ? 0 : dcField);
        main_total3 += (totalField === '' ? 0 : totalField);
    });

    // 연동제어기 자재에 대한 합계표 작성
    document.querySelectorAll('#controller_dynamicTable tbody tr').forEach(function(row) {
        var itemValue = getSafeInputValue(row.querySelector('[name="col2[]"]')); // Using getSafeInputValue to process input
        var unitsu = getSafeInputValue(row.querySelector('[name="col3[]"]'));
        var usageValue = getSafeInputValue(row.querySelector('[name="col1[]"]'));

        var computedPrice = 0;
        if (controllerData.hasOwnProperty(itemValue)) {
            computedPrice = parseFloat(controllerData[itemValue]) || 0;
        }

        var amountField = 0;
        var dcField = 0;
        var totalField = 0;

        unitsu = parseFloat(unitsu.replace(/,/g, '')) || 0;
        amountField = computedPrice * unitsu;

        var dcType, dcValue = 0;

        dcType = document.querySelector('input[name="controller_dc_type"]:checked');
		if (dcType) {
			if (dcType.value === "controller_company_dc") {
				dcValue = parseFloat(document.getElementById("controller_company_dc_value").value) || 0;
			} else if (dcType.value === "controller_site_dc") {
				dcValue = parseFloat(document.getElementById("controller_site_dc_value").value) || 0;
			}
		}
       

        if (dcValue > 0) {
            dcField = (amountField * dcValue / 100) * -1;
        }

        // dcField가 0 또는 -0일 경우 빈 문자열로 설정
        if (dcField === 0 || Object.is(dcField, -0)) {
            dcField = '';
        }

        // totalField 계산 전에 dcField가 빈 문자열인지 확인하고 0으로 처리
        totalField = amountField + (dcField === '' ? 0 : dcField);

        // 결과값을 형식에 맞게 변환하고 입력 필드에 설정
        row.querySelector('.amountField').value = amountField.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        row.querySelector('.dcField').value = dcField ? dcField.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',') : dcField;
        row.querySelector('.totalField').value = totalField.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        row.querySelector('.priceField').value = computedPrice.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        
        controller_total1 += amountField;
        controller_total2 += (dcField === '' ? 0 : dcField);
        controller_total3 += (totalField === '' ? 0 : totalField);
		
    });

    // 각 입력 필드에서 값을 가져옴
    function getSafeElementValue(id) {
        var element = document.getElementById(id);
        return element ? parseFloat(element.value.replace(/,/g, '')) || 0 : 0;
    }

    var price = getSafeElementValue('price');
    var screen_dcprice = getSafeElementValue('screen_dcprice');
    var dcadd = getSafeElementValue('dcadd');
    var screen_price = getSafeElementValue('screen_price');
    var dcPrice = getSafeElementValue('dcprice');
    var notdcprice = getSafeElementValue('notdcprice');
    var controller_price = getSafeElementValue('controller_price');
    var controller_dcprice = getSafeElementValue('controller_dcprice');
    
    // 전체 가격을 계산
    var sumrawprice = price + screen_price + controller_price + notdcprice;   
	// 할인적용금액 합계
	var afterdctotal = (screen_dcprice + dcPrice + controller_dcprice + notdcprice) ;
    var dctotal = sumrawprice - afterdctotal ;
	var totalPrice = sumrawprice - dctotal - dcadd;


    // 결과를 형식화하여 입력 필드에 설정
    function setFormattedValue(id, value) {
        var element = document.getElementById(id);
        if (element) {
            element.value = value.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }
    }

    setFormattedValue('sumrawprice', sumrawprice);
    setFormattedValue('totalprice', totalPrice);    
    // 모터, 브라켓 합산금액
    setFormattedValue('main_total1', main_total1);
    setFormattedValue('main_total2', main_total2);
    setFormattedValue('main_total3', main_total3);
    
    // 연동제어기 합산금액
    setFormattedValue('controller_total1', controller_total1);    
    setFormattedValue('controller_total2', controller_total2);
    setFormattedValue('controller_total3', controller_total3);
    
    // 부속자재 합산금액
    setFormattedValue('sub_total3', sub_total3);
	
		// 값을 가져와서 콤마 제거 후 숫자로 변환
	var main_total2 = $('#main_total2').val().replace(/,/g, '');
	var controller_total2 = $('#controller_total2').val().replace(/,/g, '');

	// 숫자로 변환
	main_total2 = parseFloat(main_total2);
	controller_total2 = parseFloat(controller_total2);

	// 두 값 더하기
	var dctotal = main_total2 + controller_total2;

	// 할인금액 합계
	setFormattedValue('dctotal', dctotal);
	// 할인 적용금액 합계표
	setFormattedValue('afterdctotal', afterdctotal);
}


function companydc()
{	
	screen_setdcType('company');
	setdcType('company');
	controller_setdcType('company');
}

function selectdelivery()
{
	screen_setdcType('company');
	setdcType('company');
	controller_setdcType('company');
	
}


</script>
 
<!-- 이미지 회전등 사진 등록 삭제 관련 루틴 -->
<script>
window.rotateFn = function(event, uniqueId, imageName, itemType) {
    event.stopPropagation(); // 이벤트 버블링 방지
    var imageElement = document.getElementById(uniqueId);

    // 현재 회전 각도 계산
    var currentRotation = parseInt(imageElement.dataset.rotation) || 0;
    var newRotation = (currentRotation + 90) % 360;
    imageElement.style.transform = 'rotate(' + newRotation + 'deg)';
    imageElement.dataset.rotation = newRotation; // 회전 각도 저장

	// 파일명 그대로 저장하기
	rotateImageAndUpload(imageElement, 'upload_rotated_image.php', imageName, uniqueId, itemType);			

}

function rotateImageAndUpload(imageElement, uploadUrl, originalFileName, uniqueId, itemType) {
    var canvas = document.createElement('canvas');
    var ctx = canvas.getContext('2d');

    var image = new Image();
    image.src = imageElement.src;
    image.onload = function() {
        // 이미지 요소의 현재 변환(transform) 상태에 따라 캔버스 크기를 설정
        var currentTransform = window.getComputedStyle(imageElement).transform;
        if (currentTransform !== 'none') {
            var values = currentTransform.split('(')[1].split(')')[0].split(',');
            var a = values[0];
            var b = values[1];

            // 회전 각도 계산
            var angle = Math.round(Math.atan2(b, a) * (180 / Math.PI));

            // 회전에 따라 캔버스 크기 설정
            if (angle === 90 || angle === 270) {
                canvas.width = image.naturalHeight;
                canvas.height = image.naturalWidth;
            } else {
                canvas.width = image.naturalWidth;
                canvas.height = image.naturalHeight;
            }

            // 이미지 회전 및 그리기
            ctx.translate(canvas.width / 2, canvas.height / 2);
            ctx.rotate(angle * Math.PI / 180);
            ctx.drawImage(image, -image.naturalWidth / 2, -image.naturalHeight / 2);
        } else {
            canvas.width = image.naturalWidth;
            canvas.height = image.naturalHeight;
            ctx.drawImage(image, 0, 0);
        }

        // 캔버스의 이미지를 Blob으로 변환
        canvas.toBlob(function(blob) {
            var formData = new FormData();
            formData.append('rotatedImage', blob, originalFileName);

            // Blob을 서버에 업로드
            fetch(uploadUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // console.log('Upload successful:', data);		

				if (data.status === "success") {
					imageElement.src = data.targetFilePath; // 새 이미지 경로로 업데이트
					imageElement.style.transform = ''; // 회전 상태 초기화
					imageElement.dataset.rotation = 0; // 회전 데이터 초기화

					// 회전 및 삭제 버튼 업데이트
					updateButtonsAfterUpload(uniqueId, data.targetFilePath, itemType);
					
					toastAlert("이미지가 회전되었습니다.");
				}		
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }, 'image/jpeg');
    };
}

function updateButtonsAfterUpload(uniqueId, newImagePath, itemType) {
    var cleanedNewPath = newImagePath.replace('./uploads/', '');

    // 회전 버튼 업데이트
    var rotateButton = document.getElementById('rotate' + uniqueId);
    if (rotateButton) {
        rotateButton.setAttribute('onclick', `rotateFn(event, '${uniqueId}', '${cleanedNewPath}', '${itemType}')`);
    }

    // 삭제 버튼 업데이트
    var deleteButton = document.getElementById('del' + uniqueId);
    if (deleteButton) {
        deleteButton.setAttribute('onclick', `delPicFn('${uniqueId}', '${cleanedNewPath}', '${itemType}')`);
    }
}

// 출하사진 처리를 위한 부분
window.delPicFn = function(uniqueId, picName, itemType) {
    // console.log(uniqueId, picName);

    if (confirm("한번 삭제한 자료는 복구할 방법이 없습니다.\n\n정말 삭제하시겠습니까?")) {
		
		alertmodal("삭제 중입니다. 잠시만 기다려주세요.");
		// console.log('ajaxRequest_write',ajaxRequest_write);
		// console.log('전달한 uniqueId',uniqueId);
		
		if (ajaxRequest_write !== null) {
			ajaxRequest_write.abort();
		}		 
		ajaxRequest_write = $.ajax({		        
            url: 'delpic.php?picname=' + picName,
            type: 'post',
            data: $("board_form").serialize(),
            dataType: 'json',    	
			success : function(data){
				
				// console.log(data);
							
				const picname = data["picname"];
				
				// console.log('실행후 uniqueId',uniqueId);

				$("#" + uniqueId).remove(); // 이미지 삭제
				$("#rotate" + uniqueId).remove(); // 회전 버튼 삭제
				$("#del" + uniqueId).remove(); // 삭제 버튼 삭제
				
				setTimeout(function(){ closealertmodal(); }, 1000);
							
			  },
			error : function( jqxhr , status , error ){
				console.log( jqxhr , status , error );
						} 			      		
		   });	

		
    }
}
 
 window.FileProcess = function(item, idx, inputElement) {  // 전역함수 선언
	
	$('#item').val(item);
	
	   //do whatever you want here
    	num = $("#num").val();
	
		if(Number(num)==0) {
			Swal.fire({                                    
				title: '출하사진은 최초 저장된 후 등록 가능(레코드 번호필요)',
				text: '저장 후 실행하세요.',
				icon: 'warning',
				// ... 기타 설정 ...
			}).then(result => {
				if (result.isConfirmed) { 
					return; // 사용자가 확인 버튼을 누르면 아무것도 하지 않고 종료
				}         
			});
			 
		   }
		  // 사진 서버에 저장하는 구간			  		  
			// 폼데이터 전송시 사용함 Get form         
			var form = $('#board_form')[0];  	    
			// Create an FormData object          
			var data = new FormData(form); 				
						
			data.append('idx', idx);    			
				
			// 선택한 파일들을 FormData에 추가
			var files = inputElement.files;
			for (var i = 0; i < files.length; i++) {
				data.append('file[]', files[i]);
			}		

            // console.log('formdata : ', data);			
			// toastAlert("업로드 중입니다. 잠시만 기다려주세요.");	
			
		    alertmodal("업로드 중입니다. 잠시만 기다려주세요.");

			if (ajaxRequest_write !== null) {
				ajaxRequest_write.abort();
			}		 
			ajaxRequest_write = $.ajax({
					enctype: 'multipart/form-data',  // file을 서버에 전송하려면 이렇게 해야 함 주의
					processData: false,    
					contentType: false,      
					cache: false,           
					timeout: 800000, 			
					url: "pic_insert.php",
					type: "post",		
					data: data,	
					dataType : 'json',
					success : function(response){
						
						// console.log(response);
												
						if(response.status == "array") {
								// 파일 경로에서 './uploads/' 부분 제거
								var cleanedFilePaths = response.filepaths.map(function(filepath) {
									return filepath.replace('./uploads/', '');
								});
							switch (item) {
								case 'beforeArr':
									AdddisplayImagesArray('#beforeArrImages_' + idx, cleanedFilePaths, 'before', idx);
									break;
								case 'midArr':
									AdddisplayImagesArray('#midArrImages_' + idx, cleanedFilePaths, 'mid', idx);
									break;
								case 'afterArr':
									AdddisplayImagesArray('#afterArrImages_' + idx, cleanedFilePaths, 'after', idx);
									break;
							}                            
						}                         
						setTimeout(function(){
							closealertmodal();
						}, 1000);
						
						setTimeout(function(){
							// 오늘 날짜를 YYYY-MM-DD 형식으로 가져오기
							var today = new Date();
							var dd = String(today.getDate()).padStart(2, '0');
							var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
							var yyyy = today.getFullYear();

							today = yyyy + '-' + mm + '-' + dd;

							// input 요소에 오늘 날짜 설정하고 change 이벤트 트리거
							$("#outputdate").val(today).trigger("change");							
							$("#status").val("출고완료").trigger("change");
							
							// 파일 저장처리함
									// console.log(data);
								ajaxRequest_picinsert = $.ajax({
									url: "insert_few.php",
									type: "post",
									data: {
										num: $("#num").val(),
										status: $("#status").val(),
										update_log: $("#update_log").val(),  										
										outputdate : $("#outputdate").val()
									},   
									dataType: "json",
									success: function(data) {
										// console.log(data);
										ajaxRequest_picinsert = null;
									},
									error: function(jqxhr, status, error) {
										console.log(jqxhr, status, error);
									}
								});
							
						}, 1000);	
						
						// console.log('실행결과 ', data);
						// opener.location.reload();
						// window.close();							
					  },
					error : function( jqxhr , status , error ){
						console.log( jqxhr , status , error );
								} 			      		
				   });	

	}

function displayPictureLoad() {    
    // 시공전 이미지 화면에 보여주기
    var picNum = "<?php echo $picNum; ?>";                     
    var picData = <?php echo json_encode($picData);?> ;   
    displayImagesMain('#displayBeforePicture', picNum, picData, 'before');
	
    // 시공중 이미지 화면에 보여주기
    var midPicNum = "<?php echo $MidpicNum; ?>";                     
    var midPicData = <?php echo json_encode($MidpicData);?> ;   
    displayImagesMain('#displayMidPicture', midPicNum, midPicData, 'mid');
		
	// console.log('picData', picData);
	
}

function displayImagesMain(containerId, picNum, picData, itemType) {
    $(containerId).empty(); // 현재 내용을 지워줍니다.

    for(var i = 0; i < picNum; i++) {
        if (picData[i]) { // 이미지 데이터가 있는 경우에만 처리
            var imgSrc = './uploads/' + picData[i];
            var uniqueId = itemType + 'Pic_' + i; // 고유 식별자 생성
            var cleanedPath = picData[i].replace('./uploads/', '');

            $(containerId).append('<img id="' + uniqueId + '" src="' + imgSrc + '" style="width:100%; height:100%" class="mb-1 mt-1">');
            $(containerId).append('&nbsp;<button type="button" class="btn btn-danger btn-sm" id="del' + uniqueId + '" onclick="delPicFn(\'' + uniqueId + '\',\'' + cleanedPath + '\', \'' + itemType + '\')" > <i class="bi bi-trash"></i>  </button>');
        }
    }
}

function AdddisplayImagesMain(containerId, filepaths, itemType) {
    var startingIndex = $(containerId).children('img').length;

    for (var i = 0; i < filepaths.length; i++) {
        var imgSrc = filepaths[i];
        var currentIndex = startingIndex + i;
        var uniqueId = itemType + 'Pic_' + currentIndex;  // 고유 ID 생성
        
        var cleanedPath = filepaths[i].replace('./uploads/', '');
        $(containerId).append('<img id="' + uniqueId + '" src="' + imgSrc + '" style="width:100%;" class="mb-1 mt-1">');
        $(containerId).append('&nbsp;<button type="button" class="btn btn-danger btn-sm mt-1 mb-2" id="del' + uniqueId + '" onclick="delPicFn(\'' + uniqueId + '\',\'' + cleanedPath + '\', \'' + itemType + '\')"> <i class="bi bi-trash"></i>  </button>');
    }
}

// 화면에 toastAlert() 표시
function toastAlert(Str){
    // 오버레이 활성화
    document.getElementById("overlay").style.display = "block";

    Toastify({
        text: Str,
        duration: 3000,
        close: true,
        gravity: "top",
        position: 'center',           
    }).showToast();

    // 1초 후에 오버레이 비활성화
    setTimeout(function(){
        document.getElementById("overlay").style.display = "none";
    }, 1000);
}

function alertmodalsecond(tmp, second)
{	
	$('#alertmsg').html(tmp); 			  
	$('#myModal').modal('show'); 	
	
	setTimeout(function() {
	$('#myModal').modal('hide');  
	}, second);		
	
}

function alertmodal(tmp)
{	
	$('#alertmsg').html(tmp); 			  
	$('#myModal').modal('show'); 	
}

function closealertmodal()
{		
	$('#myModal').modal('hide'); 	
}


$(document).ready(function() {   		 
	// 시공전 사진 멀티업로드	
	$("#upfile").change(function(e) {	    
		var item = 'before';
		FileProcess(item, '', this); // 'this'는 현재 선택된 입력 요소를 참조합니다.
	});	  		 
	
	// 시공 중간 사진 멀티업로드		
	$("#Midupfile").change(function(e) {	    
		var item = 'mid';
		FileProcess(item, '', this); 
	});		
	
	$("#closemodalBtn").click(function() {	    
			$('#myModal').modal('hide'); 	
	});	 

		 		
}); // end of ready document
</script>

<!-- 접수상태 변경 등 -->
<script>
function updateBadge(status) {
	var badgeClass = "";
	if (status === '접수대기') {
		badgeClass = "bg-warning";
	} else if (status === '접수확인') {
		badgeClass = "bg-success";
	} else if (status === '준비중') {
		badgeClass = "bg-info";
	} else if (status === '출고대기') {
		badgeClass = "bg-secondary";
	} else if (status === '출고완료') {
		badgeClass = "bg-danger";
	}

	$("#status-span").removeClass().addClass("badge fs-6 " + badgeClass).text(status);
}

$(document).ready(function () {
	// Initialize the badge on page load
	updateBadge($("#status").val());

	// Update the badge on selection change
	$("#status").change(function () {
		updateBadge($(this).val());
	});
	

	// Change status to '접수확인' when deadline changes
	$("#deadline").change(function() {
		if($("#deadline").val()!== null && $("#outputdate").val()!== '')
			$("#status").val("접수확인").trigger("change");
		else
			$("#status").val("준비중").trigger("change");				
	});

	// Change status to '출고일자' when output date changes
	$("#outputdate").change(function() {
		if($("#outputdate").val()!== null && $("#outputdate").val()!== '')
			$("#status").val("출고완료").trigger("change");
		else
			$("#status").val("준비중").trigger("change");
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
  
  	$("#order_form_write").modal("show");	
  
});

</script>


<!-- 화물회사에 알람보내기 -->
<!-- mode == 'view' 조회 화면일때 사용금지 시키는 구문 -->
<script>
$(document).ready(function(){
	showFields();
	var mode = '<?php echo $mode; ?>';
	// 마지막에 넣어줘야 전체를 적용할 수 있다.
	if (mode === 'view') {
		$('input, textarea').prop('readonly', true); // input과 textarea 요소를 readonly로 설정
		$('select, .restrictbtn, .sub_add, .add').prop('disabled', true); // select 요소와 특정 클래스를 가진 버튼들을 비활성화		
		$('input[type=file]').prop('readonly', false); // file input 요소는 비활성화 상태를 유지하지 않음		
		$('input[type=checkbox]').prop('disabled', true); // file input 요소는 비활성화 상태를 유지하지 않음		
	}	
	else
	{
		// 새로 작성시 발주처 선택, 배송지 선택 표현
		loadAllData() ;
	}	

    // 모든 input 요소의 autocomplete 속성을 off로 설정
    $('input').attr('autocomplete', 'off');

    $('#sendcheckToggle').change(function() {
		// console.log('toggle');
        var checkbox = this;
        var isChecked = checkbox.checked;
        var confirmMessage = isChecked ? '화물회사에 배차 접수' : '화물회사에 배차 접수 취소';

        Swal.fire({
            title: confirmMessage,
            text: "이 작업을 수행하시겠습니까?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '예',
            cancelButtonText: '아니오'
        }).then((result) => {
            if (result.isConfirmed) {
                // 사용자가 '예'를 누를 경우, 변경을 유지하고 숨겨진 입력 필드 값을 업데이트
                $('input[name="sendcheck"]').val(isChecked ? '1' : '');
                $('input[name="del_status"]').val(isChecked ? '배차접수 완료' : '');
            } else {
                // 사용자가 '아니오'를 누를 경우, 체크 상태를 되돌림
                checkbox.checked = !isChecked;
                $('input[name="sendcheck"]').val(checkbox.checked ? '1' : '');
                $('input[name="del_status"]').val(checkbox.checked ? '배차접수 완료' : '');
            }
        });
    });		

  // 운송료관련 자바스크립트 코드
    var deliverypayOptions = ["대한", "발주처"];
    var deliverypaymethod = "<?= $deliverypaymethod ?>"; // PHP 변수를 사용하여 현재 선택된 값을 설정

    var selectElement = $('#deliverypaymethod');
    deliverypayOptions.forEach(function(option) {
        var selected = (deliverypaymethod === option) ? ' selected' : '';
        selectElement.append("<option value='" + option + "'" + selected + ">" + option + "</option>");
    });

	
});

// 부속자재 검색하는 창의 이벤트 핸들러를 추가하여 specialbtnClear 버튼이 클릭될 때 해당 input 값을 초기화합니다.     
$(document).on('click', '.specialbtnClear', function(e) {
	e.preventDefault(); // 기본 동작을 방지합니다.
	$(this).siblings('input').val('');
	// 지우기 누르면 단가 초기화
	var row = $(this).closest('tr');
	row.find(".sub_unitField").val('');		
});
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

document.addEventListener('DOMContentLoaded', function () {  
	
    $('.closemodal').click(function() {
        // Hide the modal with the id 'deliveryModal'
        $('#deliveryModal').modal('hide');		
        $('#secondordModal').modal('hide');		
		
    });	
  
});

</script>


<!-- 발주처 명단 불러오기 함수 -->
<script>

function loadAllData() {	
	loadAllData_secondord();	
	loadAllData_delivery(); 	
}
	
function loadAllData_secondord() {	
    const secondordInfo = document.getElementById('secondordInfo'); 
    const secondordnum = $('#secondordnum').val(); 
 	
    console.log('secondordnum', secondordnum);
	if (ajaxRequest_write !== null) {
    ajaxRequest_write.abort();
	}

	ajaxRequest_write = $.ajax({
		url: '../phonebook/fetch_company_info.php',
		type: 'GET',
		data: { secondordnum: secondordnum },
		dataType: 'json',
		success: function(data) {
			console.log(data);

			let tableHTML = `<table class="table table-bordered table-hover">
				<thead class="table-secondary text-center">
					<tr>
						<th>발주처</th>
						<th>담당자</th>                    
						<th>연락처</th>
					</tr>
				</thead>
				<tbody class="text-center">`;
			let uniqueData = {};
			let counter = 0;
			
			data.forEach(item => {            
				let uniqueKey = `${item.secondord}-${item.secondordman}-${item.secondordmantel}`;
				if (!uniqueData[uniqueKey]) {
					uniqueData[uniqueKey] = {
						secondord: item.secondord,
						secondordman: item.secondordman,
						secondordmantel: item.secondordmantel
					};
					counter++;
				}
			});

			// 이제 uniqueData 객체의 값만 배열에 추가합니다.
			Object.values(uniqueData).forEach(item => {
				tableHTML += `<tr onclick="selectNum_secondord('${item.secondord}','${item.secondordman}','${item.secondordmantel}');">
					<td>${item.secondord}</td>
					<td>${item.secondordman}</td>
					<td>${item.secondordmantel}</td>
				</tr>`;
			});

			tableHTML += `</tbody></table>`;

			secondordInfo.innerHTML = tableHTML; // 한 번에 모든 HTML을 추가

			if (counter > 0) {
				// 주문서 모달 뒷배경 눌렀을때 닫히지 않게 하기
				$('#secondordModal').modal({
					backdrop: 'static',
					keyboard: false
				});

				$("#secondordModal").modal("show");
			} else {
				$("#secondordModal").modal("hide");
			}
		},
		error: function(error) {
			console.error('Error:', error);
		}
	});
}


function loadAllData_delivery() {
	
    const deliveryInfo = document.getElementById('deliveryInfo'); 
    const secondordnum = $('#secondordnum').val(); 
 	
    console.log('secondordnum', secondordnum);
	
    fetch('/phonebook/fetch_delivery_info.php' + '?secondordnum=' + secondordnum)
    .then(response => response.json())
    .then(data => {
        // console.log(data);

        let tableHTML = `<table class="table table-bordered table-hover">
            <thead class="table-secondary text-center">
                <tr>
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
            if (item.deliverymethod === '배차') {
                address = item.address;
                tel = item.chargedmantel;
                receiver = item.chargedman;
                carinfo = '(' + item.delcompany + ') 차량종류(' + item.delcaritem + ')';
            } else if (['택배', '직배송', '직접수령', '경동공장'].includes(item.deliverymethod)) {
                address = item.address;
                tel = item.chargedmantel;
                receiver = item.chargedman;
            } else if (item.deliverymethod.includes('화물')) {
                address = item.delbranch;
                tel = item.chargedmantel;
                receiver = item.chargedman;
            }
            let uniqueKey = `${address}-${receiver}-${tel}-${item.deliverymethod}`;
            if (!uniqueData[uniqueKey]) {
                uniqueData[uniqueKey] = {
                    deliverymethod: item.deliverymethod,
                    address: address,
                    receiver: receiver,
                    tel: tel,
                    carinfo: carinfo
                };
				counter ++;
            }
        });

        // 이제 uniqueData 객체의 값만 배열에 추가합니다.
        Object.values(uniqueData).forEach(item => {
            tableHTML += `<tr onclick="selectNum('${item.deliverymethod}','${item.address}','${item.receiver}','${item.tel}','${item.chargedman}','${item.chargedmantel}' ,'${item.delcaritem}','${item.delcompany}' );" >
                <td>${item.deliverymethod}</td>
                <td>${item.address}</td>
                <td>${item.receiver}</td>
                <td>${item.tel}</td>
                <td>${item.carinfo}</td>
            </tr>`;
        });

        tableHTML += `</tbody></table>`;

        deliveryInfo.innerHTML = tableHTML; // 한 번에 모든 HTML을 추가

		if(counter>0) {
			// 주문서 모달 뒷배경 눌렀을때 닫히지 않게 하기
			$('#deliveryModal').modal({
				backdrop: 'static', keyboard: false
			});

			$("#deliveryModal").modal("show");
		}
		 else
		 {
			 $("#deliveryModal").modal("hide");
		 }
			 
    })
	
    .catch(error => console.error('Error:', error));
}

function selectNum_secondord(secondord, secondordman , secondordmantel )
 {
	
	$("#secondord").val(secondord); 
	$("#secondordman").val(secondordman); 
	$("#secondordmantel").val(secondordmantel); 	
							
	Toastify({
		text: "발주처 적용완료",
		duration: 2000,
		close: true,
		gravity: "top",
		position: "center",
		style: {
			background: "linear-gradient(to right, #00b09b, #96c93d)",
			fontSize: '22px' // 글씨 크기
		},
	}).showToast();
	
	$("#secondordModal").modal("hide");

}

function selectNum(deliverymethod,address,receiver,tel,chargedman, chargedmantel, delcaritem, delcompany )
 {
	
	$("#deliverymethod").val(deliverymethod); 	
	
	showFields();
	
	if (deliverymethod === '배차') {
		$("#address").val(address); 
		$("#chargedman").val(receiver); 
		$("#chargedmantel").val(tel); 
		$("#delcaritem").val(delcaritem); 
		$("#delcompany").val(delcompany); 
		
	} else if (['택배', '직배송', '직접수령', '경동공장'].includes(deliverymethod)) {
		$("#address").val(address); 
		$("#chargedman").val(receiver); 
		$("#chargedmantel").val(tel); 		
		
	} else if (deliverymethod.includes('화물')) {
		$("#address").val(address); 
		$("#chargedman").val(receiver); 
		$("#chargedmantel").val(tel); 
		$("#delbranch").val(address); 
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
	
	$("#deliveryModal").modal("hide");


}

</script>

<!-- 협력사에서 사용하는 수정모듈에 넣을 내용 -->
<script>
  document.getElementById('modifyBtn').addEventListener('click', function() {
        var status = document.getElementById('status').value; // 현재 select의 값

        if (status !== '접수대기') {
            Swal.fire({
                title: '수정불가',
                text: "접수대기 상태일 때만 수정이 가능합니다.",
                icon: 'error',
                confirmButtonText: '확인'
            });
        } else {
            location.href = 'write_form_sub.php?mode=modify&num=<?=$num?>';
        }
    });
</script>

</body>
</html> 
