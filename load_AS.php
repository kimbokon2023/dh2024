<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

$sum_motor = array();
 
$now = date("Y-m-d",time());
  
// 출고완료 수량 체크  
$a = " where (asendday='0000-00-00' or asendday IS NULL) and is_deleted IS NULL order by num desc ";   
$sql="select * from " . $DB . ".as " . $a; 					
	   
try{   
    $stmh = $pdo->query($sql);             
    $total_row = $stmh->rowCount();  	    
  	  
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
		<span id="dis_text2" class="text-danger fs-6"> <a href="as/list.php"> AS 처리 현황   </a> </span>  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;			
	</div>
        <div class="card-body p-2 m-1 mb-3 justify-content-center">		
        <div class="d-flex justify-content-center">				
			<table class="table table-hover table-bordered" >		 
				<thead class="table-danger">
					<th class="text-center w30px">번호</th>
					<th class="text-center w50px">구분</th>						
					<th class="text-center w90px">처리예정일</th>															
					<th class="text-center w150px">주소</th>
					<th class="text-center w100px">발주처</th>
					<th class="text-center w90px">요청인</th>
					<th class="text-center w250px">구체적증상 및 추가메모</th>
					<th class="text-center w60px">유/무상</th>
					<th class="text-center w60px">비용</th>
					<th class="text-center w50px">실행</th>				
					<th class="text-center w150px">처리방법 및 결과(구체적)</th>					
				</thead>
				<tbody>		      	 
				<?php  		
				$start_num = $total_row;  			     
				while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
					include $_SERVER['DOCUMENT_ROOT'] . '/as/_row.php';		
				?>					 
				<tr onclick="redirectToView('<?=$num?>')">  
					<td class="text-center"><?= $start_num ?></td>		
					<td class="text-center fw-bold"><?= $itemcheck ?></td>	
					<td class="text-center"><?= $asproday == '0000-00-00' ? '' : $asproday ?></td> <!-- 처리 예정일 -->
					<td class="text-start"><?= $address ?></td>					
					<td class="text-center"><?= $as_step ?></td>
					<td class="text-center"><?= $asorderman ?></td>
					<td class="text-start fw-bold text-primary"><?= $aslist ?></td> <!-- 구체적 증상 -->
					<td class="text-center">
						<?php 
						if ($payment == 'free') {
							echo '무상';
						} elseif ($payment == 'paid') {
							echo '<span class="badge bg-danger"> 유상 </span>';
						} else {
							echo '알 수 없음'; // 'free'나 'paid'가 아닌 경우에 표시될 값
						}
						?>
					</td>
					<td class="text-end fw-bold text-danger">
						<?= (strpos($asfee, ',') !== false || $asfee === null || $asfee === '') ? $asfee : number_format($asfee) ?>
					</td> 						
					<td class="text-center"><?= $asman ?></td>										
					<td class="text-start"><?= $note ?></td> <!-- 메모 -->
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

<script> 

function redirectToView(num) {
	popupCenter("./as/write.php?mode=view&num=" + num, "AS 내역", 1000, 980); 	      
}

function restorePageNumber() {
    var savedPageNumber = getCookie('motorpageNumber');
    // if (savedPageNumber) {
        // dataTable.page(parseInt(savedPageNumber) - 1).draw('page');
    // }
	location.reload(true);
}


</script> 
  
