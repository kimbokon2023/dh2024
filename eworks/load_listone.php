<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

header("Content-Type: application/json");  //json을 사용하기 위해 필요한 구문

// Suppress error output to prevent JSON corruption
error_reporting(0);
ini_set('display_errors', 0);

isset($_REQUEST["e_num"]) ? $e_num = $_REQUEST["e_num"] :   $e_num=""; 

// Initialize all variables with default values to prevent undefined variable notices
$eworks_item = '일반';
$e_title = '';
$contents = '';
$registdate = '';
$status = '';
$e_line = '';
$e_line_id = '';
$e_confirm = '';
$e_confirm_id = '';
$r_line = '';
$r_line_id = '';
$recordtime = '';
$author = '';
$author_id = '';
$done = '';

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();	

 try{
      $sql = "select * from  " . $DB . ".eworks where num='$e_num' and is_deleted IS NULL ";
      $stmh = $pdo->prepare($sql);       
      $stmh->execute();
      $count = $stmh->rowCount();         	  
    if($count<1){  
     // print "검색결과가 없습니다.<br>";
	  $eworks_item = '일반';
     }   else    {      
		while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
			include $_SERVER['DOCUMENT_ROOT'] . "/eworks/_row.php";		
            if($eworks_item==='연차')
                $contents = urldecode($contents);			
		}
	 }     											  		      										

     }catch (PDOException $Exception) {
       // Return error as JSON instead of printing
       $data = array(
           "error" => "오류: " . $Exception->getMessage(),
           "e_num" => $e_num,
           "eworks_item" => $eworks_item
       );
       echo(json_encode($data, JSON_UNESCAPED_UNICODE));
       exit;
 }
 
//각각의 정보를 하나의 배열 변수에 넣어준다.
$data = array(
	"e_num" => $e_num, 
	"eworks_item" => $eworks_item, 
	"e_title" => $e_title, 
	"contents" => $contents, 
	"registdate" => $registdate, 
	"status" => $status, 
	"e_line" => $e_line, 
	"e_line_id" => $e_line_id, 
	"e_confirm" => $e_confirm, 
	"e_confirm_id" => $e_confirm_id, 
	"r_line" => $r_line, 
	"r_line_id" => $r_line_id, 
	"recordtime" => $recordtime, 
	"author" => $author, 	
	"author_id" => $author_id, 	
	"done" => $done 	
);
//json 출력
echo(json_encode($data, JSON_UNESCAPED_UNICODE));