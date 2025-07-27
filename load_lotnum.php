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
$monthAgo = date('Y-m-d', strtotime('-1 month'));  // 한 달 전
$today    = date('Y-m-d', strtotime('+1 month'));  // 한 달 후

$a = " WHERE deadline BETWEEN '$monthAgo' AND '$today' AND is_deleted IS NULL ORDER BY num DESC";
$sql = "SELECT * FROM " . $DB . ".motor " . $a;				
?>
<div class="card rounded-card  mb-2 mt-3">
	<div class="card-header  text-center ">            
		<a href="motor/list.php"> <span class="text-danger fs-5">  로트번호 미등록  </span> </a>   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;			
	</div>
	<div class="card-body p-2 m-1 mb-3  d-flex justify-content-center">			
<table class="table table-bordered table-hover table-sm">
    <thead class="table-primary">	
            <tr >
				<th  style="width:80px; " >진행상황</th>
				<th class="text-center" style="width:100px;"> 출고일자 </th>	
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
		
       if($deliverymethod == '대신화물' || $deliverymethod == '경동화물'  )
		{
			// 상차지에 화물지점표기함
		  $address = $delbranch . (!empty($delbranchaddress) ? ' (' . $delbranchaddress . ')' : '');
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

if(!empty($registlotnum)) 
  {		
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
			<td class="text-center align-middle"> <?=$outputdate?>  </td>	
			<td class="text-center text-danger"><?=$registlotnum?></td>			
			<td class="text-center align-middle"> <?=$secondord?> </td>
			<td class="align-middle fw-bold text-primary"> <?=$workplacename?> </td>
			<?php
				if($deliverymethod  == '선/대신화물')
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
	  } // end of if
  } catch (PDOException $Exception) {
  print "오류: ".$Exception->getMessage();
  }  
	
 ?>

        </tbody>
    </table>
</div>
</div>
 
