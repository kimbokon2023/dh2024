<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

$sum_motor = array();
 
$now = date("Y-m-d",time()) ;
  
// 출고완료 수량 체크  
$a="   where outputdate='$now'  and is_deleted IS NULL ";    
$sql="select * from " . $DB . ".motor " . $a; 					
	   
 try{   
    $stmh = $pdo->query($sql);             
    $total_row = $stmh->rowCount();  
  } catch (PDOException $Exception) {
  print "오류: ".$Exception->getMessage();
  } 
  
$motor_outputdonedate = $total_row ;
	$a="   where deadline='$now'  and is_deleted IS NULL  order by num desc ";  
	$sql="select * from " . $DB . ".motor " . $a; 					
?>
<style>
    .rounded-card {
        border-radius: 15px !important;  /* 조절하고 싶은 라운드 크기로 설정하세요. */
    }
	th {		
	   text-align : center;	
	}
  
.table-hover tbody tr:hover {
	cursor: pointer;
}	
</style>
	
    <div class="card rounded-card  mb-2 mt-3">
        <div class="card-header  text-center ">           
 		<!--	<button class="btn btn-primary btn-sm " type="button" id="batchBtn" > <ion-icon name="print-outline"></ion-icon></button> &nbsp;&nbsp;		 		 
		 <button class="btn btn-secondary btn-sm " type="button" id="lasermotorplanBtn" > <ion-icon name="calendar-outline"></ion-icon>  </button> &nbsp;&nbsp;		  

		 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;			-->		 

            <span id="dis_text2" class="text-dark fs-6"> <a href="motor/list.php"> DH모터 금일(<?=$today?>) 출고 예정   </a> </span>  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<button type="button" class="btn btn-dark btn-sm me-1" onclick="popupCenter('motor/register.php','일일접수',1500,900);">  <i class="bi bi-r-square-fill"></i> 일일접수 </button>    							 
			<button type="button" class="btn btn-dark btn-sm me-1" onclick="popupCenter('motor/plan_making_kd.php','경동입고',1500,900);">  <i class="bi bi-truck-flatbed"></i> 경동입고 </button>    							 
			<button type="button" class="btn btn-dark btn-sm me-1" onclick="popupCenter('motor/delivery.php','화물택배',1700,900);">  <i class="bi bi-truck"></i> 화물택배 </button>    							 
			<button type="button" class="btn btn-dark btn-sm me-1" onclick="popupCenter('motor/print_group.php','출고증 묵음',1500,900);">  <i class="bi bi-printer"></i> 출고증 묶음 </button>    							 
			<!--<button type="button" class="btn btn-dark btn-sm me-1" onclick="popupCenter('motor/plan_making.php','출고예정',1500,900);">  <i class="bi bi-calendar-check"></i> 출고예정 </button>    							 -->
			<button type="button" class="btn btn-dark btn-sm me-1" onclick="popupCenter('motor/plan_done.php','출고완료',1500,900);">  <i class="bi bi-calendar-check"></i> 출고완료 </button> 
			<button type="button" class="btn btn-secondary btn-sm me-1" onclick="popupCenter('motor/none_deadline.php','출고일 미지정',1500,900);">  <i class="bi bi-calendar-check"></i> 출고일 미지정 </button> 
        </div>
        <div class="card-body p-2 m-1 mb-3  d-flex justify-content-center">			
<table class="table table-bordered table-hover table-sm">
    <thead class="table-primary">	
            <tr >
				<th  style="width:80px; " >진행상황</th>
				<th class="text-center" style="width:60px;"> 출하 <i class="bi bi-image"></i> </th>	
				<th class="text-center text-danger" style="width:100px;">  로트번호 </th>		  
				<th  style="width:120px; ">발주처</th>                
                <th  style="width:15%; "  > 현장명 </th>
				<th class="text-center"  style="width:60px;"> 배송방법 </th>                                												                
				<th class="text-center"  style="width:150px;"> 받는분 </th>                                
				<th class="text-center"  style="width:15%;"> 배송주소 </th>                             
				<th class="text-center"  style="width:20%;"> 내역 </th>
            </tr> 
        </thead>
        <tbody>	 
<?php  	
try{  
	$stmh = $pdo->query($sql);             
	$total_row = $stmh->rowCount(); 

	$start_num=$total_row;    // 페이지당 표시되는 첫번째 글순번

	while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
		
	  include $_SERVER['DOCUMENT_ROOT'] . '/motor/_contentload.php';  
	    
	  // 사진등록 찾기
	  $registpicture = '' ;
	  $sqltmp=" select * from ".$DB.".picuploads where parentnum ='$num'";		  
		 try{  
		// 레코드 전체 sql 설정
		   $stmhtmp = $pdo->query($sqltmp);    		   
		   while($rowtmp = $stmhtmp->fetch(PDO::FETCH_ASSOC)) {
				$registpicture = "등록" ;
				}		 
		   } catch (PDOException $Exception) {
			 print "오류: ".$Exception->getMessage();		  			
		   }		

        //'대신화물' 또는 '경동화물'이라는 단어가 포함되어 있으면 으로 조건		
		if (
			strpos($deliverymethod, '대신화물') !== false ||
			strpos($deliverymethod, '경동화물') !== false
		) {
	  
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
		  
		  $loadplace = '(주)대한 본사';
		}
       if($deliverymethod == '택배'  )
		{
			// 상차지에 화물지점표기함		  
		  $loadplace = '(주)대한 본사';
		}
       if($deliverymethod == '직배송' )
		{
			// 상차지에 화물지점표기함		  
		  $loadplace = '(주)대한 본사';
		}
		
        // 로트번호 등록/미등록 가져오기
		include $_SERVER['DOCUMENT_ROOT'] . '/motor/load_lotnum.php';  		

?>			
		 
		<tr onclick="redirectToView_motor('<?=$num?>')">			
			<td class="text-center align-middle">
				<?php
				if ($status == '접수대기') {
					echo '<span class="badge bg-warning">' . $status . '</span>';
				} else if ($status == '접수확인') {
					echo '<span class="badge bg-success">' . $status . '</span>';
				} else if ($status == '준비중') {
					echo '<span class="badge bg-info">' . $status . '</span>';
				}else if ($status == '출고대기') {
					echo '<span class="badge bg-secondary">' . $status . '</span>';
				}else if ($status == '출고완료') {
					echo '<span class="badge bg-danger">' . $status . '</span>';
				}
				?>
			</td>
			<td class="text-center align-middle fw-bold"> <?=$registpicture?>  </td>	
			<td class="text-center text-danger fw-bold"><?=$registlotnum?></td>			
			<td class="text-center align-middle"> <?=$secondord?> </td>
			<td class="align-middle fw-bold text-primary"> <?=$workplacename?> </td>
			<?php
				if($deliverymethod  == '선/대신화물' || $deliverymethod  == '착/대신화물')
					print ' <td class="text-center align-middle"> <span class="badge bg-danger"> ' . $deliverymethod . ' </span> </td>';	
				else if ($deliverymethod  == '선/경동화물' || $deliverymethod  == '착/경동화물')		
					print ' <td class="text-center align-middle"> <span class="badge bg-primary"> ' .  $deliverymethod . ' </span> </td>';			
				else if ($deliverymethod  == '배차')		
					print ' <td class="text-center align-middle"> <span class="badge bg-success"> ' .  $deliverymethod . '[' . $delcompany . '] </span> </td>';			
				  else
					print ' <td class="text-center align-middle">' .  $deliverymethod . ' </td>';			
			?>			
			<td class="text-center align-middle"><?= $chargedman?> </td>
			<td class="text-start align-middle fw-bold text-dark"><?= $address?> </td>
			<td class="text-start align-middle"> <?=$contentslist?> </td>
			
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

<script> 

$(document).ready(function(){
	
	$("#batchBtn").click(function(){
		popupCenter('./motor/batchDB_invoice.php','묶음출고증',1800,780);  
	});
	$("#mobileBtn").click(function(){
		popupCenter('./mmotor/list.php','모바일 관리화면',1920,1000);  
	});

});

function restorePageNumber() {
    var savedPageNumber = getCookie('motorpageNumber');
    // if (savedPageNumber) {
        // dataTable.page(parseInt(savedPageNumber) - 1).draw('page');
    // }
	location.reload(true);
}


</script> 
  
