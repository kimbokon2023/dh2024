﻿<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  


header("Content-Type: application/json");  //json을 사용하기 위해 필요한 구문        

$num=$_REQUEST["num"];   

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();   
   
   // 첨부파일 삭제
   try{
     $sql = "select * from " . $DB . ".fileuploads where parentid = ? ";
     $stmh = $pdo->prepare($sql); 
     $stmh->bindValue(1,$num,PDO::PARAM_STR); 
     $stmh->execute();
     $count = $stmh->rowCount();              
     if($count>0) {
       while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
		   $savename = $row["savename"];

			   $upload_dir = '../uploads/';    //물리적 저장위치   
			   $made_name = $upload_dir . $savename;
			   unlink($made_name); 
				
			   try{									
				 $pdo->beginTransaction();
				 $sql1 = "delete from " . $DB . ".fileuploads where savename = ?";  
				 $stmh1 = $pdo->prepare($sql1);
				 $stmh1->bindValue(1,$savename,PDO::PARAM_STR);      
				 $stmh1->execute();  

				 $pdo->commit();
				 
				 } catch (Exception $ex) {
					$pdo->rollBack();
					print "오류: ".$Exception->getMessage();
			   } 
        }
	 }
   }catch (PDOException $Exception) {
        print "오류: ".$Exception->getMessage();
   } 
    
   try{
     $pdo->beginTransaction();
     $sql = "delete from " . $DB . ".notice where num = ?";  
     $stmh = $pdo->prepare($sql);
     $stmh->bindValue(1,$num,PDO::PARAM_STR);      
     $stmh->execute();   
     $pdo->commit();
                          
     } catch (Exception $ex) {
        $pdo->rollBack();
        print "오류: ".$Exception->getMessage();
   }
   
//각각의 정보를 하나의 배열 변수에 넣어준다.
$data = array(
		"num" =>  $num
);

//json 출력
echo(json_encode($data, JSON_UNESCAPED_UNICODE));     
   
?>