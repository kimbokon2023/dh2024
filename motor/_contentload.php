<?php
if(!isset($counter))
{
	$counter=0;
}

// 배열 변수 초기화
$num_arr = array();
$deadline_arr = array();
$outputdate_arr = array();
$workplacename_arr = array();
$secondord_arr = array();
$contentslist_arr = array();
$loadplace_arr = array();
$address_arr = array();
$deliverymethod_arr = array();
$chargedman_arr = array();
$chargedmantel_arr = array();
$comment_arr = array();
$delipay_arr = array();
$delwrapmethod_arr = array();  // 포장방식 배열
$delwrapsu_arr = array();      // 포장수량 배열
$delwrapamount_arr = array();  // 금액(만원) 배열
$delwrapweight_arr = array();  // 무게(kg) 배열
$delwrappaymethod_arr = array();  // 결제방식 배열

include $_SERVER['DOCUMENT_ROOT'] . '/motor/_row.php';  

	if($orderdate!="0000-00-00" and $orderdate!="1970-01-01"  and $orderdate!="") $orderdate = date("Y-m-d", strtotime( $orderdate) );
		else $orderdate="";
	if($deadline!="0000-00-00" and $deadline!="1970-01-01" and $deadline!="")  $deadline = date("Y-m-d", strtotime( $deadline) );
		else $deadline="";
	if($outputdate!="0000-00-00" and $outputdate!="1970-01-01" and $outputdate!="")  $outputdate = date("Y-m-d", strtotime( $outputdate) );
		else $outputdate="";              
	if($demand!="0000-00-00" and $demand!="1970-01-01" and $demand!="")  $demand = date("Y-m-d", strtotime( $demand) );
		else $demand="";                        

$num_arr[$counter] = $num;
$deadline_arr[$counter] = $deadline;
$outputdate_arr[$counter] = $outputdate;
$workplacename_arr[$counter] = $workplacename;
$secondord_arr[$counter]=$secondord;
$address_arr[$counter]=$address;
$loadplace_arr[$counter]=$loadplace ;
$deliverymethod_arr[$counter]=$deliverymethod ;
$chargedman_arr[$counter]=$chargedman ;
$chargedmantel_arr[$counter]=$chargedmantel ;
$comment_arr[$counter]=$comment ;
$delipay_arr[$counter]=$delipay ;
$delwrapmethod_arr[$counter] = $delwrapmethod;  // 포장방식 값을 배열에 할당
$delwrapsu_arr[$counter] = $delwrapsu;          // 포장수량 값을 배열에 할당
$delwrapamount_arr[$counter] = $delwrapamount;  // 금액(만원) 값을 배열에 할당
$delwrapweight_arr[$counter] = $delwrapweight;  // 무게(kg) 값을 배열에 할당
$delwrappaymethod_arr[$counter] = $delwrappaymethod;  // 결제방식 값을 배열에 할당
   

// 1) 삼항 연산자 한 줄 버전 (PHP 5.3+)
$finalAddress = !empty($delbranch)
	? $delbranch
	: (!empty($delbranchaddress)
		? $delbranchaddress
		: $address);

// 2) if/elseif 구문 버전
if (!empty($delbranch)) {
	$finalAddress = $delbranch;
} elseif (!empty($delbranchaddress)) {
	$finalAddress = $delbranchaddress;
} else {
	$finalAddress = $address;
}		  
// 상차지에 화물지점표기함 
$address = $finalAddress ;
	 $address_arr[$counter]=$address;
// 주자재 합계 문자열 생성
	$contentslist = '';
	// $firstItemAdded = false;

	// $items = [
		// 'realscreensu' => '스크린M',
		// 'realsteelsu' => '철재M',
		// 'realprotectsu' => '방범M',
		// 'realsmokesu' => '제연M',
		// 'realexplosionsu' => '방폭M',
		// 'realpolesu' => '무기둥M'
	// ];

	// foreach ($items as $key => $value) {
		// if (!empty($row[$key])) {
			// if (!$firstItemAdded) {
				// $contentslist .= '<span class="badge bg-primary"> M </span> ';
				// $firstItemAdded = true;
			// }
			// $contentslist .= $value . ' ' . $row[$key] . 'EA, ';
		// }
	// }

$conses = json_decode($orderlist, true);
$firstItemAdded = true;

foreach ($conses as $cons) {	
	if($cons['col5'] !== '브라켓트')
	{
		$contentslist .= '<span class="badge bg-primary"> M </span>  ';
		$contentslist .= $cons['col3'] . ' ' . $cons['col8'] . 'EA, ';
	}
	  else
	  {
		$contentslist .= '<span class="badge bg-danger"> B </span>  ';
		$contentslist .= $cons['col3'] . ' ' . $cons['col8'] . 'EA, ';
	  }
}


// 마지막 쉼표 제거
$contentslist = rtrim($contentslist, ', ');

// 연동제어기
$conses = json_decode($controllerlist, true);
$controllerlist = '';
$firstAccessory = true;

foreach ($conses as $cons) {
	if ($firstAccessory) {
		$controllerlist .= '<span class="badge bg-success"> 연동 </span>  ';
		$firstAccessory = false;
	}
	$controllerlist .= $cons['col2'] . ':' . $cons['col3'] . 'EA, ';
}

// 마지막 쉼표 제거
$controllerlist = rtrim($controllerlist, ', ');

// 원단
$conses = json_decode($fabriclist, true);
$fabriclist = '';
$firstAccessory = true;
$aggregatedConses = [];

// 동일한 col1 값을 가진 항목들을 합산
foreach ($conses as $cons) {
    $col1 = $cons['col1'];
    $col5 = $cons['col5'];
    
    if (!isset($aggregatedConses[$col1])) {
        $aggregatedConses[$col1] = 0;
    }
		
	$col5 = str_replace(',', '', $col5);  // 콤마 제거
	$aggregatedConses[$col1] += (float)$col5;  // 숫자로 변환 후 더하기

}

// 결과 문자열 생성
foreach ($aggregatedConses as $col1 => $totalCol5) {
    if ($firstAccessory) {
        $fabriclist .= '<span class="badge bg-info"> 원단 </span>  ';
        $firstAccessory = false;
    }
    $fabriclist .= $col1 . ':' . $totalCol5 . '(M), ';
}

// 마지막 쉼표 제거
$fabriclist = rtrim($fabriclist, ', ');

// 부속자재 합계 문자열 생성
$accessories = json_decode($accessorieslist, true);
$accessorieslist = '';
$firstAccessory = true;

foreach ($accessories as $accessory) {
	if ($firstAccessory) {
		$accessorieslist .= '<span class="badge bg-secondary"> 부속 </span>  ';
		$firstAccessory = false;
	}
	$accessorieslist .= $accessory['col1'] . ':' . $accessory['col2'] . 'EA, ';
}

// 마지막 쉼표 제거
$accessorieslist = rtrim($accessorieslist, ', ');

// 각 리스트를 합치고 <br> 추가
$finalList = '';
if (!empty($contentslist)) {
	$finalList .= $contentslist . '<br>';
}
if (!empty($controllerlist)) {
	$finalList .= $controllerlist . '<br>';
}
if (!empty($fabriclist)) {
	$finalList .= $fabriclist . '<br>';
}
if (!empty($accessorieslist)) {
	$finalList .= $accessorieslist . '<br>';
}

// 마지막 <br> 제거
$finalList = rtrim($finalList, '<br>');

// 'DH-' 제거
$finalList = str_replace('DH-', '', $finalList);
$contentslist =  $finalList;           	   
$contentslist_arr[$counter]=$contentslist ;

?>