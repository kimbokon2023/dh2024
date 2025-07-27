<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  
	
$readIni = array();   // 환경파일 불러오기
$readIni = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . "/motor/estimate.ini",false);	
	
$sql="select * from " . $DB . ".motor WHERE outputdate = CURDATE() - INTERVAL 1 DAY " ;

// print $lcearning;			 
?>
		