<?php
include $_SERVER['DOCUMENT_ROOT'] . '/session.php';   

header("Content-Type: application/json");  //json을 사용하기 위해 필요한 구문

isset($_REQUEST["id"])  ? $id=$_REQUEST["id"] :   $id=''; 
isset($_REQUEST["item"])  ? $item=$_REQUEST["item"] :   $item=''; 
isset($_REQUEST["tablename"])  ? $tablename=$_REQUEST["tablename"] :  $tablename=''; 

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();	

$now = date("Y-m-d");	     // 현재 날짜와 크거나 같으면 출고예정으로 구분
$nowtime = date("H:i:s");	 // 현재시간	

$sql=" select * from " . $DB . ".fileuploads where tablename ='$tablename' and item ='$item' and parentid ='$id' ";	

$id_arr=array(); 
$parentid_arr=array(); 
$realfile_arr=array(); 
$file_arr=array(); 
$rotation_arr=array(); 

 try{  
	// 레코드 전체 sql 설정
   $stmh = $pdo->query($sql);            // 검색조건에 맞는글 stmh

   while($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
            $id_arr[]        = $row["id"];			
			$parentid_arr[]  = $row["parentid"];
			$realfile_arr[]  = $row["realname"];
			$file_arr[]      = $row["savename"];			
			$rotation_arr[]  = $row["rotate"] ?? 0;			
        }		 
   } catch (PDOException $Exception) {
    print "오류: ".$Exception->getMessage();
}  

//각각의 정보를 하나의 배열 변수에 넣어준다.
$data = array(
	"recid"=>           count($id_arr),
	"id_arr" =>         $id_arr,
	"parentid_arr" =>   $parentid_arr,
	"file_arr" =>       $file_arr,
	"realfile_arr" =>    $realfile_arr,
	"rotation_arr" =>   $rotation_arr,
	"DB" =>             $DB,
	"tablename" =>      $tablename,
	"item" =>           $item,
	"id" =>             $id
);   

//json 출력
echo(json_encode($data, JSON_UNESCAPED_UNICODE));