 <?php session_start(); 
   
isset($_REQUEST["mcname"])  ? $mcname=$_REQUEST["mcname"] :   $mcname=''; 
isset($_REQUEST["selnum"])  ? $selnum=$_REQUEST["selnum"] :   $selnum=1;  

header("Location:https://dh2024.co.kr/qc/laser.php?mcname=$mcname&selnum=$selnum");  
	
 ?>