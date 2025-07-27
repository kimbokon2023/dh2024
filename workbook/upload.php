<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

header("Content-Type: application/json");  //json을 사용하기 위해 필요한 구문

$tablename = isset($_REQUEST['tablename']) ? $_REQUEST['tablename'] : '';  

function conv_num($num) {
$number = (int)str_replace(',', '', $num);
return $number;
}

function pipetocomma($str) {
$strtmp = str_replace('|', ',', $str);
return $strtmp;
}

isset($_REQUEST["col1"])  ? $col1=$_REQUEST["col1"] :   $col1=''; 
isset($_REQUEST["col2"])  ? $col2=$_REQUEST["col2"] :   $col2=''; 
isset($_REQUEST["col3"])  ? $col3=$_REQUEST["col3"] :   $col3=''; 
isset($_REQUEST["col4"])  ? $col4=$_REQUEST["col4"] :   $col4=''; 
isset($_REQUEST["col5"])  ? $col5=$_REQUEST["col5"] :   $col5=''; 


$colarr1 = explode(",",$col1[0]);
$colarr2 = explode(",",$col2[0]);
$colarr3 = explode(",",$col3[0]);
$colarr4 = explode(",",$col4[0]);
$colarr5 = explode(",",$col5[0]);


$orderday = date("Y-m-d"); // 현재날짜 2022-01-20 형태로 지정	

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

for($i=0;$i<count($colarr1);$i++) {	
if($colarr1[$i]!='')
{
	 // | -> , 로 변환함  
     $colarr1[$i] = pipetocomma($colarr1[$i]);
     $colarr2[$i] = pipetocomma($colarr2[$i]);
     $colarr3[$i] = pipetocomma($colarr3[$i]);
     $colarr4[$i] = pipetocomma($colarr4[$i]);
     $colarr5[$i] = pipetocomma($colarr5[$i]);
		
	try {

		// Collect input data
		$vendor_name = $colarr1[$i];
		$address = $colarr2[$i];
		$phone = $colarr3[$i];
		$contact_info = $colarr4[$i];
		$note = $colarr5[$i];		

		$pdo->beginTransaction();

		// Updated columns and values to be inserted
		$sql = "INSERT INTO " . $DB . "." . $tablename . " (";
		$sql .= "vendor_name, address, ";
		$sql .= "phone, ";
		$sql .= "contact_info, note";
		$sql .= ") VALUES (?, ?, ?, ?, ? )";

		$stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $vendor_name, PDO::PARAM_STR);
        $stmh->bindValue(2, $address, PDO::PARAM_STR);
        $stmh->bindValue(3, $phone, PDO::PARAM_STR);
        $stmh->bindValue(4, $contact_info, PDO::PARAM_STR);
        $stmh->bindValue(5, $note, PDO::PARAM_STR);  		

		// Execute the statement
		$stmh->execute();
		$pdo->commit();
	} catch (PDOException $Exception) {
		$pdo->rollBack();
		print "오류: " . $Exception->getMessage();
	}
	
	
	}
}
//각각의 정보를 하나의 배열 변수에 넣어준다.
$data = array(
		"colarr1" => $colarr1
);

//json 출력
echo(json_encode($data, JSON_UNESCAPED_UNICODE));

?>