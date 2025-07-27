<?php   
   session_start();
   $num=$_REQUEST["num"];
   $DB=$_REQUEST["DB"];
         
   require_once("../lib/mydb.php");
   $pdo = db_connect();   
   
   // 첨부파일 삭제
   try{
     $sql = "select * from mirae8440.fileuploads where parentid = ? ";
     $stmh = $pdo->prepare($sql); 
     $stmh->bindValue(1,$num,PDO::PARAM_STR); 
     $stmh->execute();
     $count = $stmh->rowCount();              
 
       while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
		   $savename = $row["savename"];

			   $upload_dir = '../uploads/';    //물리적 저장위치   
			   $made_name = $upload_dir . $savename;
			   unlink($made_name); 
				
			   try{									
				 $pdo->beginTransaction();
				 $sql1 = "delete from mirae8440.fileuploads where savename = ?";  
				 $stmh1 = $pdo->prepare($sql1);
				 $stmh1->bindValue(1,$savename,PDO::PARAM_STR);      
				 $stmh1->execute();  

				 $pdo->commit();
				 
				 } catch (Exception $ex) {
					$pdo->rollBack();
					print "오류: ".$Exception->getMessage();
			   } 
     }
   }catch (PDOException $Exception) {
        print "오류: ".$Exception->getMessage();
   } 
    
   try{
     $pdo->beginTransaction();
     $sql = "delete from mirae8440." . $DB . " where num = ?";  
     $stmh = $pdo->prepare($sql);
     $stmh->bindValue(1,$num,PDO::PARAM_STR);      
     $stmh->execute();   
     $pdo->commit();
 
     header("Location:http://8440.co.kr/" . $DB . "/list.php?DB=$DB");
                         
     } catch (Exception $ex) {
        $pdo->rollBack();
        print "오류: ".$Exception->getMessage();
   }
?>