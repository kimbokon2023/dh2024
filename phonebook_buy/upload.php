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
isset($_REQUEST["col6"])  ? $col6=$_REQUEST["col6"] :   $col6=''; 
isset($_REQUEST["col7"])  ? $col7=$_REQUEST["col7"] :   $col7=''; 
isset($_REQUEST["col8"])  ? $col8=$_REQUEST["col8"] :   $col8=''; 
isset($_REQUEST["col9"])  ? $col9=$_REQUEST["col9"] :   $col9=''; 
isset($_REQUEST["col10"])  ? $col10=$_REQUEST["col10"] :   $col10=''; 
isset($_REQUEST["col11"])  ? $col11=$_REQUEST["col11"] :   $col11=''; 
isset($_REQUEST["col12"])  ? $col12=$_REQUEST["col12"] :   $col12=''; 
isset($_REQUEST["col13"])  ? $col12=$_REQUEST["col13"] :   $col13=''; 


$colarr1 = explode(",",$col1[0]);
$colarr2 = explode(",",$col2[0]);
$colarr3 = explode(",",$col3[0]);
$colarr4 = explode(",",$col4[0]);
$colarr5 = explode(",",$col5[0]);
$colarr6 = explode(",",$col6[0]);
$colarr7 = explode(",",$col7[0]);
$colarr8 = explode(",",$col8[0]);
$colarr9 = explode(",",$col9[0]);
$colarr10 = explode(",",$col10[0]);
$colarr11 = explode(",",$col11[0]);
$colarr12 = explode(",",$col12[0]);
$colarr13 = explode(",",$col13[0]);


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
     $colarr6[$i] = pipetocomma($colarr6[$i]);
     $colarr7[$i] = pipetocomma($colarr7[$i]);
     $colarr8[$i] = pipetocomma($colarr8[$i]);
     $colarr9[$i] = pipetocomma($colarr9[$i]);
     $colarr10[$i] = pipetocomma($colarr10[$i]);
     $colarr11[$i] = pipetocomma($colarr11[$i]);
     $colarr12[$i] = pipetocomma($colarr12[$i]);
     $colarr13[$i] = pipetocomma($colarr13[$i]);

		
	try {
		$pdo->beginTransaction();

		// Collect input data
		$vendor_code = $colarr1[$i];
		$vendor_name = $colarr2[$i];
		$representative_name = $colarr3[$i];
		$address = $colarr4[$i];
		$business_type = $colarr5[$i];
		$item_type = $colarr6[$i];
		$phone = $colarr7[$i];
		$mobile = $colarr8[$i];
		$email = $colarr9[$i];
		$fax = $colarr10[$i];
		$manager_name = $colarr11[$i];
		$contact_info = $colarr12[$i];
		$note = $colarr13[$i];		

		// Prepare SQL insert statement
		$sql = "INSERT INTO " . $DB . "." . $tablename . " (";
		$sql .= "vendor_code, vendor_name, representative_name, address, ";
		$sql .= "business_type, item_type, phone, mobile, email, ";
		$sql .= "fax, manager_name, contact_info, note";
		$sql .= ") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

		$stmh = $pdo->prepare($sql);

		// Bind the values to the prepared statement
		$stmh->bindValue(1, $vendor_code, PDO::PARAM_STR);
		$stmh->bindValue(2, $vendor_name, PDO::PARAM_STR);
		$stmh->bindValue(3, $representative_name, PDO::PARAM_STR);
		$stmh->bindValue(4, $address, PDO::PARAM_STR);
		$stmh->bindValue(5, $business_type, PDO::PARAM_STR);
		$stmh->bindValue(6, $item_type, PDO::PARAM_STR);
		$stmh->bindValue(7, $phone, PDO::PARAM_STR);
		$stmh->bindValue(8, $mobile, PDO::PARAM_STR);
		$stmh->bindValue(9, $email, PDO::PARAM_STR);
		$stmh->bindValue(10, $fax, PDO::PARAM_STR);
		$stmh->bindValue(11, $manager_name, PDO::PARAM_STR);
		$stmh->bindValue(12, $contact_info, PDO::PARAM_STR);
		$stmh->bindValue(13, $note, PDO::PARAM_STR);		

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