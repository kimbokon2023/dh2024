 <?php session_start(); ?>
  
 <meta charset="utf-8">
 <?php
  
  $text1=$_POST["text1"];
  $text2=$_POST["text2"];
  $text3=$_POST["text3"];
  $text4=$_POST["text4"];
  $text5=$_POST["text5"]; 
  
  print "exe insrtdate.php";
 
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
   
 ?>
  <script>
        alert('자료등록/수정 완료');      
  </script>
