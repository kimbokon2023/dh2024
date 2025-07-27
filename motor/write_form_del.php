<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';

$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  
$num = isset($_REQUEST["num"]) ? $_REQUEST["num"] : "";

$first_writer = '';

$title_message = '(화물회사) (주)대한 배차 현황' ;
	
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
</style>

</head>

<body>  

<?   


include $_SERVER['DOCUMENT_ROOT'] . '/mymodal.php';
  
// 첨부 이미지에 대한 부분
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();	
 
$URLsave = "https://dh2024.co.kr/motorloadpic.php?num=" . $num;  

$todate = date("Y-m-d"); // 현재일자 변수지정
  
  if ($mode=="modify" || $mode=="view"){
    try{
	    $sql = "select * from " . $DB . ".motor where num = ? ";
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
  
if ($mode !== "modify" and $mode !== "copy" and $mode !== "split"  and $mode !== "view"  ) {
    
	include '_request.php';
	$first_writer = $user_name ;

}

  if ($mode=="copy" or $mode=='split'){
    try{
      $sql = "select * from " . $DB . ".motor where num = ? ";
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
	 $orderdate = $today;	
    if($registerdate==null or $registerdate == '0000-00-00'	)
		$registerdate = $orderdate;
	 
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

// 부자재에 대한 배열 가져오기
$sql = "select * from " . $DB . ".fee_sub order by basicdate desc limit 1";

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

$controllerOptions = $sub_item;


?>

<form id="board_form" name="board_form" method="post" enctype="multipart/form-data"   >	
		      
<input type="hidden" id="first_writer" name="first_writer" value="<?=$first_writer?>"  >
<input type="hidden" id="update_log" name="update_log" value="<?=$update_log?>"  >
<input type="hidden" id="num" name="num" value="<?=$num?>"  >
<input type="hidden" id="tablename" name="tablename" value="<?=$tablename?>"  >
<input type="hidden" id="item" name="item" value="<?=$item?>"  >
<input type="hidden" id="mode" name="mode" value="<?=$mode?>"  >
<input type="hidden" id="secondordnum" name="secondordnum" value="<?=$secondordnum?>"  >  <!-- 발주처의 코드 기록 -->
<input type="hidden" id="orderlist" name="orderlist" >
<input type="hidden" id="accessorieslist" name="accessorieslist" >
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
		<div class="row d-flex ">		
			<div class="col-sm-9 ">		
			<div class="d-flex justify-content-center align-items-center mt-3 mb-2 ">		
			<span class="fs-5 me-5">  <?=$title_message?>  (<?=$mode?>) </span>           
			<!-- 이 부분 조회 화면과 다름 -->   	
			<?php if($mode !=='view') { 
				print '<button id="saveBtn" type="button" class="btn btn-dark  btn-sm me-2"  > <ion-icon name="save-outline"></ion-icon> 배차지정 완료(저장)  </button> ';
			}
				else  { ?>	
					<button type="button" class="btn btn-dark btn-sm me-1" onclick="location.href='write_form_del.php?mode=modify&num=<?=$num?>';" > <ion-icon name="color-wand-outline"></ion-icon> 배차정보 기록하기 </button>				
				<?php } ?>
			
			 </div>
			 </div>
			 <div class="col-sm-3 ">
				<div class="d-flex justify-content-end align-items-center mt-3 mb-2 ">				 				
					<button class="btn btn-secondary btn-sm" onclick="self.close();" > <i class="bi bi-x-lg"></i> 닫기 </button>&nbsp;					
			</div> 		
			</div> 		
		</div> 		
<div class="d-flex row justify-content-center mt-1"> 	
<div class="col-sm-12 p-1 rounded" style=" border: 1px solid #392f31; " > 	

<div class="d-flex row justify-content-center p-1"> 	
	<div class="col-sm-5 rounded p-1 " > 	
	<table class="table table-bordered ">		 
		  <tbody>
			<tr>
				<td colspan="1" style="width:80px;"  >
				현장명
				</td>
				<td colspan="2" class="text-start"  style="width:200px;"><input type="text" id="workplacename" name="workplacename" value="<?=$workplacename?>" class="form-control text-start" required></td>							  			  
			</tr>   
		  </tbody>
		</table>	

	</div>			
	
</div>

<?php

// 발주처에 대한 정보를 가져온다.

$tablenamesub = 'phonebook';
  	  
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();
         
try{
	 $sql = "select * from ". $DB . "." . $tablenamesub . " where secondordnum=?";
	 $stmh = $pdo->prepare($sql);  
	 $stmh->bindValue(1, $secondordnum, PDO::PARAM_STR);      
	 $stmh->execute();            
	  
	 $row = $stmh->fetch(PDO::FETCH_ASSOC); 	 
		include '../phonebook/_row.php';

	 }catch (PDOException $Exception) {
	   print "오류: ".$Exception->getMessage();
	 }      	
	
if($deliverypaymethod == '발주처')
{
?>
	<div class="d-flex align-items-center justify-content-center ">	
	    <span class="badge bg-primary fs-6" >  발주처 정보 </span>
	</div>

	<div class="row justify-content-center p-1 rounded" > 	
	<div class="col-sm-3"> </div>
		<div class="col-sm-6">
			<div class="d-flex align-items-center justify-content-center m-2"  style=" border: 1px solid #392f31;" >
				<table class="table table-bordered" >
					<tbody>
						<tr>
							<td colspan="2" class="text-center fw-bold"  >거래처코드(사업자번호) </td>
							<td colspan="2" class="text-center" > 
								<input type="text" class="form-control" id="vendor_code" name="vendor_code" value="<?=$vendor_code?>"> 
							</td>		
						</tr>	
						<tr>
							<td class="text-center fw-bold" style="width:150px;" >거래처 명</td>
							<td class="text-center"> 
									<input type="text" class="form-control" id="vendor_name" name="vendor_name" style="width:250px;" value="<?=$vendor_name?>">    
							</td>				
							<td class="text-center fw-bold" style="width:170px;" >대표자 성함</td>
							<td class="text-center fw-bold" >
								<input type="text" class="form-control" id="representative_name" name="representative_name"  style="width:200px;" value="<?=$representative_name?>">  
							</td>
						</tr>							
							
						<tr>
							<td class="text-center fw-bold">주소</td>
							<td colspan="3" class="text-center"> 
								<input type="text" class="form-control" id="address" name="address" value="<?=$address?>">   							
						</tr>
						<tr>				
							<td  class="text-center fw-bold">업태</td>
							<td class="text-center"> 
								<input type="text" class="form-control" id="business_type" name="business_type" value="<?=$business_type?>">     
							</td>							
							<td class="text-center fw-bold" > 종목 </td>	
							<td class="text-center"> 
								<input type="text" class="form-control" id="item_type" name="item_type" value="<?=$item_type?>">  
							</td>																					
						</tr>
						<tr>				
							<td  class="text-center fw-bold">전화</td>
							<td class="text-center"> 
								<input type="text" class="form-control" id="phone" name="phone" value="<?=$phone?>">  
							</td>							
							<td class="text-center fw-bold" > 모바일 </td>	
							<td class="text-center"> 
								<input type="text" class="form-control" id="mobile" name="mobile" value="<?=$mobile?>"> 
							</td>																					
						</tr>
						<tr>				
							<td  class="text-center fw-bold">이메일</td>
							<td class="text-center"> 
								<input type="email" class="form-control" id="email" name="email" value="<?=$email?>">       
							</td>							
							<td class="text-center fw-bold" > 팩스 </td>	
							<td class="text-center"> 
								<input type="text" class="form-control" id="fax" name="fax" value="<?=$fax?>">    
							</td>																					
						</tr>
						<tr>				
							<td  class="text-center fw-bold">담당자명</td>
							<td class="text-center"> 
								<input type="text" class="form-control" id="manager_name" name="manager_name" value="<?=$manager_name?>">       
							</td>							
							<td class="text-center fw-bold" > 담당자Tel </td>	
							<td class="text-center"> 
								<input type="text" class="form-control" id="contact_info" name="contact_info" value="<?=$contact_info?>">      
							</td>																					
						</tr>
				
						
					</tbody>
				</table>   
			</div>
		</div>
		<div class="col-sm-3"> </div>
	</div>

<?php } ?>	

<div class="container-fluid" style=" border: 1px solid #392f31;" >	
<div class="d-flex row justify-content-center p-3 rounded" >	
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
					$deliveryOptions = ["","경동공장","직접 수령", "직배송", "경동화물","대신화물","택배", "배차"];
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
					  <button type="button" class="btn btn-dark-outline btn-sm restrictbtn" onclick="workbookBtn('chargedman');">  <ion-icon name="settings-outline"></ion-icon> </button>					  
					</div>
				</td><td>					  
				     <i class="bi bi-telephone-forward-fill"></i> 연락 </td><td> <input type="text" name="chargedmantel" id="chargedmantel" value="<?=$chargedmantel?>"  class="form-control" onkeydown="if(event.keyCode == 13) { workbookBtn('chargedmantel'); }" >  </td><td>
						배송주소</td><td>
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
    <td style="width:80px;">상차일시</td>
    <td class="text-center"><input type="datetime-local" name="deltime" class="form-control" style="width:135px;" value="<?= $deltime ?>"></td>
    <td style="width:80px;">하차일시</td>
    <td class="text-center"><input type="datetime-local" name="deldowntime" class="form-control" style="width:135px;" value="<?= $deldowntime ?>"></td>
    <td style="width:80px;">차량종류</td>
    <td><input type="text" name="delcaritem" placeholder="차량종류" class="form-control" style="width:80px;" value="<?= $delcaritem ?>"></td>
    <td class="text-center" style="width:80px;">화물회사<br>전달사항<br>대한작성</td>
    <td><textarea name="delmemo" rows="2" class="form-control" style="width:300px;"><?= $delmemo ?></textarea></td>
    <td>
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
    <td class="text-primary text-center" style="width:80px;">진행상태</td>
    <td class="text-primary text-center">
        <div class="d-flex justify-content-center">
            <input type="text" id="del_status" name="del_status" class="form-control text-primary text-center" style="width:100px;" value="<?= $del_status ?>">
        </div>
    </td>
    <td class="text-danger">차량번호</td>
    <td><input type="text" id="delcarnumber" name="delcarnumber" placeholder="차량번호" class="form-control" value="<?= $delcarnumber ?>"></td>
    <td class="text-danger"><i class="bi bi-telephone-forward-fill"></i> 기사</td>
    <td><input type="text" id="delcartel" name="delcartel" placeholder="기사연락처" class="form-control" value="<?= $delcartel ?>"></td>
    <td class="text-danger">운송료</td>
    <td><input type="text" id="delipay"  name="delipay" value="<?= $delipay ?>" class="form-control" onkeyup="inputNumberFormat(this)"></td>
    <td class="text-center" style="width:80px;">화물회사<br>메모작성<br></td>
    <td colspan="2"><textarea id="del_writememo" name="del_writememo" rows="2" class="form-control" style="width:300px;"><?= $del_writememo ?></textarea></td>
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
                <ion-icon name="settings-outline"></ion-icon>
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

<div class="row d-flex justify-content-center">
			
	<?php	if($chkMobile) 	{	?>
		<div class="col-sm-12 p-1 rounded" style=" border: 2px solid #392f31; " > 			
	<?php	} else 	{	?>	
		<div class="col-sm-4 p-1 rounded" style=" border: 2px solid #392f31; " > 			
	<?php	}  ?>	

	<?php

	$counter = 1;   
	// 버튼 생성
	createImageInputAndButton($counter, 'beforeArr', 'secondary', $picDataArr);  
				
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
			echo '<div class="col text-center justify-content-center align-items-center mt-1 mb-1 fs-3">';			
		}		
		
		
		echo '(대한) 포장(출하) 사진 ';
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

					if(itemType === 'before')
					{
						var imgElement = `<img id="${uniqueId}" src="./uploads/${imgSrc}" style="width:80%;" class="mb-3 mt-3"> </div>`;

						$(containerId).append(imgElement );
					}
					if(itemType === 'mid')
					{
						var imgElement = `<img id="${uniqueId}" src="./uploads/${imgSrc}" style="width:80%;" class="mb-3 mt-3">`;
						var rotateButton = `<div class="d-flex justify-content-center"> <button type="button" class="btn btn-primary btn-sm me-2" id="rotate${uniqueId}" onclick="rotateFn(event, '${uniqueId}', '${cleanedPath}', '${itemType}')"><ion-icon name="refresh-outline"></ion-icon></button>`;
						var deleteButton = `<button type="button" class="btn btn-danger btn-sm" id="del${uniqueId}" onclick="delPicFn('${uniqueId}', '${cleanedPath}', '${itemType}')"><ion-icon name="trash-bin-outline"></ion-icon></button> </div>`;

						$(containerId).append(imgElement + rotateButton + deleteButton);
					}
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
		
		   $msg = '등록';  
		
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
		</div>
		</div>


</div> <!-- end of 첨부파일 row -->
</div>
</div>
</div>
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

if(delcompany =='')
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
	if(deliverymethod === '직접 수령' || deliverymethod === '직배송') {
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
	console.log(divID, delChoice);
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

function captureReturnKey(e) {
    if(e.keyCode==13 && e.srcElement.type != 'textarea')
    return false;
}

function recaptureReturnKey(e) {
    if(e.keyCode==13 && e.srcElement.type != 'textarea')
    return false;
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
		
		// console.log('orderlist json : ', orderlist);
		
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
			url: "insert_del.php",
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
								// location.href = "write_form.php?mode=view&num=" + data["num"];
								
								hideOverlay();
								self.close();
						}, 1000);	
						
						            
			 
			
			},
			error : function( jqxhr , status , error ){
				console.log( jqxhr , status , error );
						} 			      		
		   });		
			
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
                console.log('Upload successful:', data);		

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
						
						console.log(response);
												
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
            $(containerId).append('&nbsp;<button type="button" class="btn btn-danger btn-sm" id="del' + uniqueId + '" onclick="delPicFn(\'' + uniqueId + '\',\'' + cleanedPath + '\', \'' + itemType + '\')" ><ion-icon name="trash-bin-outline"></ion-icon></button>');
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
        $(containerId).append('&nbsp;<button type="button" class="btn btn-danger btn-sm mt-1 mb-2" id="del' + uniqueId + '" onclick="delPicFn(\'' + uniqueId + '\',\'' + cleanedPath + '\', \'' + itemType + '\')"><ion-icon name="trash-bin-outline"></ion-icon></button>');
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
 
	
	$("#closemodalBtn").click(function() {	    
			$('#myModal').modal('hide'); 	
	});	 

		 		
}); // end of ready document
</script>



<!-- 부트스트랩 툴팁 -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
    
});

</script>


<!-- 차량번호를 넣는 순간 배차완료로 수정됨 -->
<!-- mode == 'view' 조회 화면일때 사용금지 시키는 구문 -->
<script>
$(document).ready(function() {
    showFields();
    var mode = '<?php echo $mode; ?>';

    // 모든 input, textarea 요소를 readonly로 설정
    $('input, textarea').prop('readonly', true);
    // select 요소와 특정 클래스를 가진 버튼들을 비활성화
    $('select, .restrictbtn, .sub_add, .add').prop('disabled', true);
    // file input 요소는 비활성화 상태를 유지하지 않음
    $('input[type=file]').prop('readonly', false);
    // checkbox 요소는 disabled 처리
    $('input[type=checkbox]').prop('disabled', true);

    // mode가 'view'가 아닐 경우 일부 input 요소의 readonly 속성 해제
    if (mode !== 'view') {
        $('input[name=delcarnumber], input[name=delcartel], input[name=delipay] , textarea[name=del_writememo] ').prop('readonly', false);
    }

    // 차량 번호 입력 필드와 상태 표시 div
    var delCarNumberInput = $('#delcarnumber');
    var delStatusDiv = $('#del_status');

    // 요소가 존재하는지 확인
    if (!delCarNumberInput.length || !delStatusDiv.length) {
        console.error('Some elements are not found!');
        return;
    }

    // 차량번호 입력 필드에 대한 이벤트 리스너 설정
    delCarNumberInput.on('change keyup', function() {
        updateDelStatus();
    });

    // 상태 업데이트 함수
    function updateDelStatus() {
        if (delCarNumberInput.val().trim() !== '') {
            delStatusDiv.val('배차 완료');
        } else {
            delStatusDiv.val('배차접수 완료');
        }
    }

  // 운송료관련 자바스크립트 코드
    var deliverypayOptions = ["대한", "발주처"];
    var deliverypaymethod = "<?= $deliverypaymethod ?>"; // PHP 변수를 사용하여 현재 선택된 값을 설정

    var selectElement = $('#deliverypaymethod');
    deliverypayOptions.forEach(function(option) {
        var selected = (deliverypaymethod === option) ? ' selected' : '';
        selectElement.append("<option value='" + option + "'" + selected + ">" + option + "</option>");
    });
	
	
});
</script>






</body>
</html> 