<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  
	
$num=$_REQUEST["num"];
$page=$_REQUEST["page"];
$ripple_num=$_REQUEST["ripple_num"];    
$tablename=$_REQUEST["tablename"];   //tablename 이름
	
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();
        
     try{
       $pdo->beginTransaction();
       $sql = "delete from " . $DB . ".suggestion_ripple where num = ?";  //db만 수정 
       $stmh = $pdo->prepare($sql);
       $stmh->bindValue(1,$ripple_num,PDO::PARAM_STR);
       $stmh->execute();   
       $pdo->commit();
                
          header("Location:https://dh2024.co.kr/suggestion/view.php?tablename=$tablename&num=$num&page=$page");
       } catch (Exception $ex) {
                $pdo->rollBack();
                print "오류: ".$Exception->getMessage();
       }
  ?> 