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
  $sql = "select * from chandj.bending where bendnum = ? ";
      $stmh = $pdo->prepare($sql); 
    $stmh->bindValue(1,$bendnum,PDO::PARAM_STR); 
      $stmh->execute();
      $count = $stmh->rowCount();              
    if($count<1){  
      print "검색결과가 없습니다.<br>";
     }else{
      $row = $stmh->fetch(PDO::FETCH_ASSOC);
      $bendnum = $row["bendnum"];
      $bendname = $row["bendname"];
	  $no=array();
	  $railsum=0;
	  for($i=1;$i<=20;$i++) {
	  $temp="no" . $i;
      $no[$i] = $row[$temp];
	  $railsum+=$no[$i];
	  }	  
      $sum= $row["sum"];	  
	 }
 $arraynum=1;
 
//  print "레일합은 " . $railsum;
 for($i=1;$i<=20;$i++)
 {
 print "<script> $('#no" . $i . "').val('" . $no[$i] . "'); </script>";
 }
 print "<script> $('#railsum').val('" . $railsum . "'); </script>";
 ?>


