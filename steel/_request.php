<?php


  if(isset($_REQUEST["search"]))   //목록표에 제목,이름 등 나오는 부분
	 $search=$_REQUEST["search"];
	 else
		 $search='';

  if(isset($_REQUEST["Bigsearch"]))   //목록표에 제목,이름 등 나오는 부분
	 $Bigsearch=$_REQUEST["Bigsearch"];	 	
	  else
		  $Bigsearch='';	 	
	 
// // 페이지 버튼을 누르면 공백문자가 사라지는 현상이 발생해서 추가로 논리계산 만듬      $BigsearchTag 변수 활용
// if(isset($_REQUEST["BigsearchTag"])!='')   //목록표에 제목,이름 등 나오는 부분  
    // $Bigsearch = str_replace('|',' ',$_REQUEST["BigsearchTag"]);	 
	
//	print $Bigsearch;
	 
// var_dump($search);
// var_dump($Bigsearch);
	 
 // $Bigsearch = str_replace('|',' ',$Bigsearch);	 
	  
 if(isset($_REQUEST["bad_choice"])) // $_REQUEST["bad_choice"]값이 없을 때에는 1로 지정 
 {
    $bad_choice=$_REQUEST["bad_choice"];  // 페이지 번호
 }
  else
  {
    $bad_choice='없음';	 
  }	 

  if(isset($_REQUEST["mode"]))
     $mode=$_REQUEST["mode"];
  else 
     $mode="";     
   
 // 기간을 정하는 구간
$fromdate=$_REQUEST["fromdate"];	 
$todate=$_REQUEST["todate"];	 

  
require_once("../lib/mydb.php");
$pdo = db_connect();	
  

?>