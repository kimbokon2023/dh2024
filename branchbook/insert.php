<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  
              
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();	

$tablename = isset($_REQUEST['tablename']) ? $_REQUEST['tablename'] : '';  
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  

include "_request.php";

if($mode=="update")  {			
    try {
        $pdo->beginTransaction();
        // Prepare the SQL query for updating the vendor information
        $sql = "UPDATE " . $DB . "." . $tablename . " SET ";
        $sql .= "vendor_name = ?, address = ?, ";
        $sql .= "phone = ?, ";
        $sql .= "note = ? ";
        $sql .= "WHERE num = ? LIMIT 1"; // Update only one record matching the 'num'

        $stmh = $pdo->prepare($sql);

        // Bind the variables to the prepared statement as parameters
        $stmh->bindValue(1, $vendor_name, PDO::PARAM_STR);
        $stmh->bindValue(2, $address, PDO::PARAM_STR);
        $stmh->bindValue(3, $phone, PDO::PARAM_STR);
        $stmh->bindValue(4, $note, PDO::PARAM_STR);        
        $stmh->bindValue(5, $num, PDO::PARAM_STR);

        // Execute the statement
        $stmh->execute();
        $pdo->commit();
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        print "오류: " . $Exception->getMessage();
    }
		
}   // end of $mode if-statement
				
if( $mode=="insert")  {	 
	try {
		$pdo->beginTransaction();

		// Updated columns and values to be inserted
		$sql = "INSERT INTO " . $DB . "." . $tablename . " (";
		$sql .= "vendor_name, address, ";
		$sql .= "phone, ";
		$sql .= "note";
		$sql .= ") VALUES (?, ?, ?, ? )";

		$stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $vendor_name, PDO::PARAM_STR);
        $stmh->bindValue(2, $address, PDO::PARAM_STR);
        $stmh->bindValue(3, $phone, PDO::PARAM_STR);
        $stmh->bindValue(4, $note, PDO::PARAM_STR);  

		// Execute the statement
		$stmh->execute();
		$pdo->commit();
	} catch (PDOException $Exception) {
		$pdo->rollBack();
		print "오류: " . $Exception->getMessage();
	}
 
// parentKey 추출

        $sql = "select * from  " .  $DB . "." . $tablename . "  order by num desc ";
		 try{  
			  $stmh = $pdo->query($sql);      // 검색조건에 맞는글 stmh
			  $temp = $stmh->rowCount();
			  $row = $stmh->fetch(PDO::FETCH_ASSOC);
			  $num=$row["num"];
			  
			 } catch (PDOException $Exception) {
					  print "오류: ".$Exception->getMessage();
					  }			  			 					  
  
}   // end of $mode if-statement 


if($mode=="delete")  {   // data 삭제시 
	   		  
	try {
		$pdo->beginTransaction();
		$sql = "UPDATE " .  $DB . "." . $tablename . " SET is_deleted = 1 WHERE num = ? LIMIT 1";
		$stmh = $pdo->prepare($sql);
		$stmh->bindValue(1, $num, PDO::PARAM_INT);

		if (!$stmh->execute()) {
			print "SQL 실행 오류";
		}

		$pdo->commit();
	} catch (Exception $ex) {
		$pdo->rollBack();
		print "오류: " . $ex->getMessage();
	}

}

 $data = [   
 'mode' => $mode, 
 'num' => $num
 ]; 
 
 echo json_encode($data, JSON_UNESCAPED_UNICODE);


?>

