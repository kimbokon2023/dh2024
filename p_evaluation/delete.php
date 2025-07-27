﻿<?php

header("Content-Type: application/json");  //json을 사용하기 위해 필요한 구문  

if(!isset($_SESSION))      
		session_start(); 
if(isset($_SESSION["DB"]))
		$DB = $_SESSION["DB"] ;	
 $level= $_SESSION["level"];
 $user_name= $_SESSION["name"];
 $user_id= $_SESSION["userid"];	
      
$num=$_REQUEST["num"];
$tablename=$_REQUEST["tablename"];

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();   
           
try{
     $pdo->beginTransaction();
     $sql = "delete from mirae8440." . $tablename . " where num = ?";  
     $stmh = $pdo->prepare($sql);
     $stmh->bindValue(1,$num,PDO::PARAM_STR);      
     $stmh->execute();   
     $pdo->commit();	 
     } catch (PDOException $Exception) {
          $pdo->rollBack();
       print "오류: ".$Exception->getMessage();
     }   
   
   
$data = [   'num' => $num ,
		    'tablename' => $tablename
 ];
 
 echo json_encode($data, JSON_UNESCAPED_UNICODE);   
   
?>