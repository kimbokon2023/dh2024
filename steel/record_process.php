<?php session_start(); ?>
  
 <?php

 function trans_date($tdate) {
		      if($tdate!="0000-00-00" and $tdate!="1900-01-01" and $tdate!="")  $tdate = date("Y-m-d", strtotime( $tdate) );
					else $tdate="";							
				return $tdate;	
}

 if(isset($_REQUEST["num"]))
    $num=$_REQUEST["num"];
 else 
    $num="";

 if(isset($_REQUEST["checkarr"]))
    $checkarr=$_REQUEST["checkarr"];
 else 
    $checkarr="";

$check_Arr = explode(",", $checkarr);
	
// 체크된 값 받아오기	
print $num . '\r';

var_dump($check_Arr);

echo gettype($check_Arr[0]);
echo gettype($check_Arr[1]);
echo gettype($check_Arr[2]);

print $check_Arr[1] . '\r';
print $check_Arr[2] . '\r';
print $check_Arr[3] . '\r';
  
 require_once("../lib/mydb.php");
 $pdo = db_connect();
    
 $data=date("Y-m-d H:i:s") . " - "  . $_SESSION["name"] . "  " ;	
 $update_log = $data . $update_log . "&#10";  // 개행문자 Textarea
 $now = date("Y-m-d");
  
	 try{		 
		$pdo->beginTransaction();   
		$sql = "update mirae8440.ceiling set ";
		$sql .=" update_log=? "; 
	   
		$sql .= " where num=? LIMIT 1" ;         
		   
		 $stmh = $pdo->prepare($sql); 

		 $stmh->bindValue(1, $update_log, PDO::PARAM_STR);        
		 $stmh->bindValue(2, $num, PDO::PARAM_STR);
		 
		 $stmh->execute();
		 $pdo->commit(); 
			} catch (PDOException $Exception) {
			   $pdo->rollBack();
			   print "오류: ".$Exception->getMessage();
		   }   
	  
for($i=0;$i<3;$i++)
{	
if(trim($check_Arr[$i])=='1')
{	
	 try{		 
		$pdo->beginTransaction();   
		$sql = "update mirae8440.ceiling set ";
		$sql .=" eunsung_laser_date=? "; 
	   
		$sql .= " where num=? LIMIT 1" ;         
		   
		 $stmh = $pdo->prepare($sql); 

		 $stmh->bindValue(1, $now, PDO::PARAM_STR);        
		 $stmh->bindValue(2, $num, PDO::PARAM_STR);
		 
		 $stmh->execute();
		 $pdo->commit(); 
			} catch (PDOException $Exception) {
			   $pdo->rollBack();
			   print "오류: ".$Exception->getMessage();
		   }   
   }	  
}


for($i=0;$i<3;$i++)
{	
if(trim($check_Arr[$i])=='2')
{		
	 try{		 
		$pdo->beginTransaction();   
		$sql = "update mirae8440.ceiling set ";
		$sql .=" lclaser_date=? "; 
	   
		$sql .= " where num=? LIMIT 1" ;         
		   
		 $stmh = $pdo->prepare($sql); 

		 $stmh->bindValue(1, $now, PDO::PARAM_STR);        
		 $stmh->bindValue(2, $num, PDO::PARAM_STR);
		 
		 $stmh->execute();
		 $pdo->commit(); 
			} catch (PDOException $Exception) {
			   $pdo->rollBack();
			   print "오류: ".$Exception->getMessage();
		   }   
}	   
}
 
for($i=0;$i<3;$i++)
{	
if(trim($check_Arr[$i])=='3')
	{	
		 try{		 
		$pdo->beginTransaction();   
		$sql = "update mirae8440.ceiling set ";
		$sql .=" etclaser_date=? "; 
	   
		$sql .= " where num=? LIMIT 1" ;         
		   
		 $stmh = $pdo->prepare($sql); 

		 $stmh->bindValue(1, $now, PDO::PARAM_STR);        
		 $stmh->bindValue(2, $num, PDO::PARAM_STR);
		 
		 $stmh->execute();
		 $pdo->commit(); 
			} catch (PDOException $Exception) {
			   $pdo->rollBack();
			   print "오류: ".$Exception->getMessage();
		   }   
	}	   
}	   
 
    ?>
