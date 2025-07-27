<?php
session_start();
$bendnum=$_REQUEST["bendnum"];  
$sel=$_REQUEST["sel"]; 

   $level= $_SESSION["level"];
 if(!isset($_SESSION["level"]) || $level>10) {
          /*   alert("관리자 승인이 필요합니다."); */
		 sleep(2);
         header ("Location:http://5130.co.kr/login/logout.php");
         exit;
   }
// 절곡물에 대한 정보 불러오기

  require_once("../lib/mydb.php");
  $pdo = db_connect();	
  $sql = "select * from chandj.bendingData where bendnum = ? limit 1";
      $stmh = $pdo->prepare($sql); 
    $stmh->bindValue(1,$bendnum,PDO::PARAM_STR); 
      $stmh->execute();
      $count = $stmh->rowCount();              
    if($count<1){  
      print "검색결과가 없습니다.<br>";
     }else{
      $row = $stmh->fetch(PDO::FETCH_ASSOC);
      $bendnum = $row["bendnum"];
	  
	  $sidecoverName=$row["sidecoverName"];
	  $sidecoverWidth=$row["sidecoverWidth"];
	  $sidecoverHeight=$row["sidecoverHeight"];

	
	  
	  
	  
	  $no=array();
	  $steelsum=array();
	  $steeltype=array();
	  $b_sum=array();
	  $b_total=0; //총 가로합
  for($j=1;$j<=9;$j++) {     //  90번까지 데이터있음
  	  $tempstr="steeltype" . $j;
	  $steeltype[$j] = $row[$tempstr];
	   for($i=($j-1)*10+1;$i<=$j*10;$i++) {
	  $temp="no" . $i;
      $no[$i] = $row[$temp];
	  $b_sum[$j]+=$no[$i];
	  }	  
	  $b_total+= $b_sum[$j];
  }
	 }
 $arraynum=1;
 
 // 초기화
 	for($j=1;$j<=9;$j++) {
           print "<script> $('#b_title" . $j . "').text(''); </script>"; 		   
	    for($i=($j-1)*10+1;$i<=$j*10;$i++) {
           print "<script> $('#b" . $i . "').text(''); </script>";  
	   }
	           print "<script> $('#b_sum" . $j . "').text(''); </script>";    
			   
			   }
	print "<script> $('#b_total').text(''); </script>";  		   
 if($count!=0) {
	for($j=1;$j<=9;$j++) {
           print "<script> $('#b_title" . $j . "').text('" . $j . "번 : " . $steeltype[$j] . "'); </script>";   // input문이 아니면 .text로 제이쿼리 전달해야 한다. (중요)
		   
	    for($i=($j-1)*10+1;$i<=$j*10;$i++) {
           print "<script> $('#b" . $i . "').text('" . $no[$i] . "'); </script>";   // input문이 아니면 .text로 제이쿼리 전달해야 한다. (중요)
	   }
	           print "<script> $('#b_sum" . $j . "').text('" . $j . "번합계 : " . $b_sum[$j] . "'); </script>";    
			   
			   }
		
			    $dummy=array();
			    $dummy[0] = "egi1.6t";
				$dummy[1] = "egi1.2t";
				$dummy[2] =  "sus1.5t";
				$dummy[3] =  "sus1.2t";	 
				$dummy[4] =  "옆커버egi1.6t";	 
				$dummy[5] =  "옆커버egi1.2t";	 
				
			        		$arr_save=array();  // 합계 저장변수

			for($i=0;$i<=5;$i++) {
			   	for($j=1;$j<=9;$j++) {					
					if($dummy[$i]==$steeltype[$j]) {
						 $arr_save[$i] +=$b_sum[$j]; // 치환
					}			
				}	
				
				}
				$b_text="";
				for($j=0;$j<=5;$j++) {
					if($arr_save[$j]>0) {
						
			           $b_text .= $dummy[$j] . " :  " . $arr_save[$j] . "   ,  ";
					}
				}
			   		           $b_text .= $sidecoverName . "  가로: " . $sidecoverWidth . "  세로: "  . $sidecoverHeight;
			           print "<script> $('#b_total').text('" . $b_text . "'); </script>";  
					   
 }			   
	   ?>
