<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

  require_once("./lib/mydb.php");
  $pdo = db_connect();	
  
  $now = date("Y-m-d",time()) ;
  
// 접수일자로 접수수량 체크  
$a="   where orderdate='$now' and is_deleted IS NULL order by num desc ";    
$sql="select * from " . $DB . ".motor " . $a; 					
	   
	 try{  

	  $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh
      $temp1=$stmh->rowCount();    
      $total_row=$temp1;	  
  } catch (PDOException $Exception) {
  print "오류: ".$Exception->getMessage();
  }  	  
	  
$motor_registedate = $total_row;	  
  
// 납기예정 수량 체크  
$a="   where deadline='$now' and is_deleted IS NULL  order by num desc ";    
$sql="select * from " . $DB . ".motor " . $a; 					
	   
	 try{  

	  $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh
      $temp1=$stmh->rowCount();    
      $total_row=$temp1;	  
  } catch (PDOException $Exception) {
  print "오류: ".$Exception->getMessage();
  }  	  
	  
$motor_duedate = $total_row;	
 
// 회수예정 체크  
$a="   where (returndue = '회수예정' ) and is_deleted IS NULL  order by num desc ";    
$sql="select * from " . $DB . ".motor " . $a; 					
	   
	 try{  

	  $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh
      $temp1=$stmh->rowCount();    
      $total_row=$temp1;	  
  } catch (PDOException $Exception) {
  print "오류: ".$Exception->getMessage();
  }  	  
	  
$motor_returndue = $total_row;	  
	  
?>