 <?php
  	
if(!isset($_SESSION))      
		session_start(); 
if(isset($_SESSION["DB"]))
		$DB = $_SESSION["DB"] ;	
 $level= $_SESSION["level"];
 $user_name= $_SESSION["name"];
 $user_id= $_SESSION["userid"];	  

  
	 
  $now = date("Y-m-d",time()) ;
  
  $a="   where done is null order by num desc ";  	
 
  $sql="select * from " . $DB . ".myarealist " . $a; 		

  $NocheckAreaNum = 0;  // 사무실 미점검 숫자	    
  
  $NocheckOfficePerson = [];
	   
 try{  

	  $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh
      $temp1=$stmh->rowCount();    
      $total_row=$temp1;			 
	  
	  
?>							
			 
<style>
.rounded-card {
border-radius: 15px !important;  /* 조절하고 싶은 라운드 크기로 설정하세요. */
}

.custom-thead {
background-color: #FFDAB9; /* 원하는 배경색 */
/* 기타 원하는 스타일 속성 */
}

.my-gradient {
background: linear-gradient(to right, #f2f2f2, #dddddd);
}
	
</style>			

    <div class="card rounded-card mb-2 mt-1">
		<div class="card-header  text-center ">   
		  <div id="toggleOfficeBtn"  ><h6>  <span style="cursor:pointer;" > 사무실 청소 미점검 <span class="badge bg-danger"><?=$total_row?></span> 건 </span> </h6> </div>
		</div>        
		
     <div class="card-body p-2 m-1 mb-3 justify-content-center" >
     <div id="Officetable" style="display:none;"  >        
		 <table class="table table-bordered table-hover table-sm">
		   <thead class="table-danger text-center">			   
				<th scope="col">번호</th>
				<th scope="col">점검예정일</th>
				<th scope="col">청소구역</th>
				<th scope="col">점검구분</th>
				<th scope="col">담당(정)</th>
				<th scope="col">담당(부)</th>           
		</thead>
    <tbody>		
	 
<?php  		  
		$start_num=$total_row;    // 페이지당 표시되는 첫번째 글순번

	       while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {			   
	       include "rowDB.php";	   							   
				switch ($item){
				  case 'section1' :   
				  case 'section2' :   
				  case 'section3' :   
				  case 'section4' :   
				  case 'section5' :   
				  case 'section6' :   
				  case 'section7' :   
				    $itemstr = '내책상';
					break;
				  case 'comsection1' :   
				    $itemstr = '사무실공용통로';
					break;
				  case 'comsection2' :   
				    $itemstr = '사무실 가구 및 집기류';
					break;
				  case 'comsection3' :   
				    $itemstr = '사장실내부';
					break;
				}
					
		   // 미점검자 정만 배열에 넣음
		   array_push($NocheckOfficePerson , $writer);
		   
		   
		   ?>
			<tr onclick="popupCenter('../qcoffice/view.php?num=<?=$num?>', '사무실 청소 점검', 1200, 700); return false;" style="cursor:pointer;">
				<td class="text-center"><?=$start_num?></td>
				<td class="text-center"><?=$checkdate?></td>
				<td class="text-center"><?=$itemstr?></td>
				<td class="text-center"><?=$term?></td>
				<td class="text-center"><?=$writer?></td>
				<td class="text-center"><?=$writer2?></td>
			</tr>

			<?php
			$start_num--;
			$NocheckAreaNum ++ ;
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
  
  			
			
	 

  