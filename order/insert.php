 
 <?php
      $motor1 = $_REQUEST["motor1"];
      $motor2 = $_REQUEST["motor2"];
      $motor3 = $_REQUEST["motor3"];
      $motor4 = $_REQUEST["motor4"];
      $motor5 = $_REQUEST["motor5"];
      $motor6 = $_REQUEST["motor6"];
      $motor7 = $_REQUEST["motor7"];
      $motor8 = $_REQUEST["motor8"];
      $motor9 = $_REQUEST["motor9"];
      $motor10= $_REQUEST["motor10"];

 require_once("../lib/mydb.php");
 $pdo = db_connect();
			  
	/* 	print "접속완료"	  ; */
     try{
        $pdo->beginTransaction();   
        $sql = "update chandj.settings set motor1=?,motor2=?,motor3=?,motor4=?,motor5=?,motor6=?,motor7=?,motor8=?,motor9=?,motor10=?" ;
    
     $num=1; 
     $stmh = $pdo->prepare($sql); 
     $stmh->bindValue(1, $motor1, PDO::PARAM_STR);  	 
     $stmh->bindValue(2, $motor2, PDO::PARAM_STR);  	 
     $stmh->bindValue(3, $motor3, PDO::PARAM_STR);  	 
     $stmh->bindValue(4, $motor4, PDO::PARAM_STR);  	 
     $stmh->bindValue(5, $motor5, PDO::PARAM_STR);  	 
     $stmh->bindValue(6, $motor6, PDO::PARAM_STR);  	 
     $stmh->bindValue(7, $motor7, PDO::PARAM_STR);  	 
     $stmh->bindValue(8, $motor8, PDO::PARAM_STR);  	 
     $stmh->bindValue(9, $motor9, PDO::PARAM_STR);  	 
     $stmh->bindValue(10, $motor10, PDO::PARAM_STR);  	 
     $stmh->bindValue(11, $num, PDO::PARAM_STR);  	 
	     	   //고유키값이 같나?의 의미로 ?로 num으로 맞춰야 합니다.
     $stmh->execute();
     $pdo->commit(); 
        } catch (PDOException $Exception) {
           $pdo->rollBack();
           print "오류: ".$Exception->getMessage();
       }        
  header("Location: http://5130.co.kr/order/write_form.php");    
 ?>