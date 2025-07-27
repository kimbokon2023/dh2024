 <?php session_start(); ?>
  
 <meta charset="utf-8">
 <?php
 
 $text1=$_REQUEST["callname"];  // 전달인수가 변수명과 겹치면 오류가 발생해서 받아들이지 않는다.
 $text2=$_REQUEST["text2"];
 $cutwidth=$_REQUEST["cutwidth"]; 
 $cutheight=$_REQUEST["cutheight"];  
 
 $text3="";
 $text4="";
 $text5="";

  
 require_once("../lib/mydb.php");
  $pdo = db_connect();
    
	 // 데이터 신규 등록하는 구간
	 
   try{
     $pdo->beginTransaction();
  	 
     $sql = "insert into chandj.egicut(text1, text2, text3, text4, text5) ";	 

     $sql .= "values(?, ?, ?, ?, ? )"; //      
	   
     $stmh = $pdo->prepare($sql); 
     $stmh->bindValue(1, $text1, PDO::PARAM_STR);  
     $stmh->bindValue(2, $text2, PDO::PARAM_STR);  
     $stmh->bindValue(3, $text3, PDO::PARAM_STR);       
		
     $stmh->bindValue(4, $text4, PDO::PARAM_STR);        
     $stmh->bindValue(5, $text5, PDO::PARAM_STR);         	 
	 
     $stmh->execute();
     $pdo->commit(); 
     } catch (PDOException $Exception) {
          $pdo->rollBack();
       print "오류: ".$Exception->getMessage();
     }   
   	   header("Location:http://5130.co.kr/order/egimake_list.php?callname=$text1&cutwidth=$cutwidth&cutheight=$cutheight");    // 신규가입일때는 리스트로 이동
 ?>
