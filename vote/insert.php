﻿<?php require_once($_SERVER['DOCUMENT_ROOT'] . '/session_header.php'); 


header("Content-Type: application/json");  //json을 사용하기 위해 필요한 구문 받는측에서 필요한 정보임 ajax로 보내는 쪽에서 type : json

// 임시저장된 첨부파일을 확정하기 위해 검사하기  
isset($_REQUEST["timekey"])  ? $timekey=$_REQUEST["timekey"] :  $timekey='';   // 신규데이터에 생성할때 임시저장키    

$mode = $_REQUEST["mode"] ?? "";
$tablename = $_REQUEST["tablename"] ?? "";
$num = $_REQUEST["num"] ?? "";
$is_html = $_REQUEST["is_html"] ?? "";
$noticecheck = $_REQUEST["noticecheck"] ?? "";
$status = $_REQUEST["status"] ?? "";
$subject = $_REQUEST["subject"] ?? "";
$content = $_REQUEST["content"] ?? "";
$searchtext = $_REQUEST["searchtext"] ?? "";
$deadline = $_REQUEST["deadline"] ?? "";

        
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();
    
 if ($mode=="modify"){
         
     try{
        $sql = "select * from " . $DB . "." . $tablename . " where num=?";  // get target record
        $stmh = $pdo->prepare($sql); 
        $stmh->bindValue(1,$num,PDO::PARAM_STR); 
        $stmh->execute(); 
        $row = $stmh->fetch(PDO::FETCH_ASSOC);
     } catch (PDOException $Exception) {
        $pdo->rollBack();
        print "오류: ".$Exception->getMessage();
     } 
              
     try{		 
        $pdo->beginTransaction();   
        $sql = "update " . $DB . "." . $tablename . " set subject=?, content=?, is_html=?, noticecheck=?, searchtext=?, status=?, deadline=? where num=? ";
        $stmh = $pdo->prepare($sql); 
        $stmh->bindValue(1, $subject, PDO::PARAM_STR);  
        $stmh->bindValue(2, $content, PDO::PARAM_STR);  
        $stmh->bindValue(3, $is_html, PDO::PARAM_STR);     
        $stmh->bindValue(4, $noticecheck, PDO::PARAM_STR);     
        $stmh->bindValue(5, $searchtext, PDO::PARAM_STR);     
        $stmh->bindValue(6, $status, PDO::PARAM_STR);     
        $stmh->bindValue(7, $deadline, PDO::PARAM_STR);     
        $stmh->bindValue(8, $num, PDO::PARAM_INT);   
        $stmh->execute();   
        $pdo->commit(); 
        } catch (PDOException $Exception) {
           $pdo->rollBack();
           print "오류: ".$Exception->getMessage();
       }                         
       
 } else	{
	 
	 
    if ($is_html =="y"){
	         $content = htmlspecialchars($content);
       }
	   
   try{
     $pdo->beginTransaction();
     $sql = "insert into " . $DB . "." . $tablename . " (id, name, nick, subject, content, is_html, ";
     $sql .= " noticecheck, searchtext, status, regist_day, deadline) ";
     $sql .= "values(?, ?, ?, ?, ?, ?, ?, ?, ?, now(),?)";
     $stmh = $pdo->prepare($sql); 
     $stmh->bindValue(1, $_SESSION["userid"], PDO::PARAM_STR);  
     $stmh->bindValue(2, $_SESSION["name"], PDO::PARAM_STR);  
     $stmh->bindValue(3, $_SESSION["nick"], PDO::PARAM_STR);   
     $stmh->bindValue(4, $subject, PDO::PARAM_STR);  
     $stmh->bindValue(5, $content, PDO::PARAM_STR);  
     $stmh->bindValue(6, $is_html, PDO::PARAM_STR);         
     $stmh->bindValue(7, $noticecheck, PDO::PARAM_STR);        
     $stmh->bindValue(8, $searchtext, PDO::PARAM_STR);        
     $stmh->bindValue(9, $status, PDO::PARAM_STR);        
     $stmh->bindValue(10, $deadline, PDO::PARAM_STR);        
     $stmh->execute();
     $pdo->commit(); 
     } catch (PDOException $Exception) {
          $pdo->rollBack();
       print "오류: ".$Exception->getMessage();
     }   
   }
   
if ($mode!="modify"){
	
// 신규데이터인경우 num을 추출한 후 view로 보여주기
 $sql="select * from " . $DB . "." . $tablename . "  order by num asc"; 					

  try{  
   $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh
   $rowNum = $stmh->rowCount();  
   while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {	   
		  $num=$row["num"];			   			 
	 } 	 
   } catch (PDOException $Exception) {
    print "오류: ".$Exception->getMessage();
}    

// 신규데이터인 경우 첨부파일/첨부이미지 추가한 것이 있으면 parentid 변경해줌
// 신규데이터인경우 num을 추출한 후 view로 보여주기
 
 $id = $num;
 
  try{
        $pdo->beginTransaction();   
        $sql = "update " . $DB . ".fileuploads set parentid=? where parentid=?";
        $stmh = $pdo->prepare($sql); 
        $stmh->bindValue(1, $id, PDO::PARAM_STR);  
        $stmh->bindValue(2, $timekey, PDO::PARAM_STR);   
        $stmh->execute();
        $pdo->commit(); 
        } catch (PDOException $Exception) {
           $pdo->rollBack();
           print "오류: ".$Exception->getMessage();
       }                         

}

 $data = [   
 'num' => $num, 
 'tablename' => $tablename
 ]; 
 
 echo json_encode($data, JSON_UNESCAPED_UNICODE);

 ?>

