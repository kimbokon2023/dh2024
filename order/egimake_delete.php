  <?php   
   session_start();
   $num=$_REQUEST["num"];
         
    require_once("../lib/mydb.php");
    $pdo = db_connect();
   
   if($num=="all")
   {
   try{	      
     $pdo->beginTransaction();	   
	 $sql = "truncate chandj.egicut";
     $stmh = $pdo->prepare($sql); 
     $stmh->execute();
     $pdo->commit();	 
   	 header("Location:http://5130.co.kr/order/egimake_list.php");    // 리스트로 이동	 
                         
     } catch (Exception $ex) {
        $pdo->rollBack();
        print "오류: ".$Exception->getMessage();
     }	   

   }
 else 
 {	 
	 
   try{
     $sql = "select * from chandj.egicut where num = ? ";
     $stmh = $pdo->prepare($sql); 
     $stmh->bindValue(1,$num,PDO::PARAM_STR); 
     $stmh->execute();
     $count = $stmh->rowCount();      

   }catch (PDOException $Exception) {
        print "오류: ".$Exception->getMessage();
   }
 
   try{
     $pdo->beginTransaction();
     $sql = "delete from chandj.egicut where num = ?";  
     $stmh = $pdo->prepare($sql);
     $stmh->bindValue(1,$num,PDO::PARAM_STR);      
     $stmh->execute();   
     $pdo->commit();
 
   	   header("Location:http://5130.co.kr/order/egimake_list.php");    // 신규가입일때는 리스트로 이동
                         
     } catch (Exception $ex) {
        $pdo->rollBack();
        print "오류: ".$Exception->getMessage();
   }
 }
 
?>