<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

$tablename = isset($_REQUEST['tablename']) ? $_REQUEST['tablename'] : '';  
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  

header("Content-Type: application/json");  //json을 사용하기 위해 필요한 구문 받는측에서 필요한 정보임 ajax로 보내는 쪽에서 type : json
              
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();	


include "_request.php";

$searchtag = $vendor_name . ' ' .
              $representative_name . ' ' .
              $address . ' ' .
              $business_type . ' ' .
              $item_type . ' ' .
              $phone . ' ' .
              $mobile . ' ' .
              $email . ' ' .
              $fax . ' ' .
              $manager_name . ' ' .
              $contact_info . ' ' .
              $note . ' ' .
              $is_deleted . ' ' .
              $represent . ' ' .
              $update_log . ' ' .              
              $pid . ' ' . 
              $secondordnum . ' ' . 
              $ppw;


if($mode=="update")  {			
    $update_log = date("Y-m-d H:i:s") . " - " . $_SESSION["name"] . " " . $update_log . "&#10";
    try {
        $pdo->beginTransaction();
        // Prepare the SQL query for updating the vendor information
        $sql = "UPDATE " . $DB . "." . $tablename . " SET ";
        $sql .= "vendor_code = ?, vendor_name = ?, representative_name = ?, address = ?, ";
        $sql .= "business_type = ?, item_type = ?, phone = ?, mobile = ?, email = ?, ";
        $sql .= "fax = ?, manager_name = ?, contact_info = ?, note = ? , represent = ?, searchtag = ?, update_log = ?, pid = ?, ppw = ?, parentnum=?, screendc=?, etcdc=?, secondordnum=?, controllerdc=?, fabricdc=?, paydate=?  " ;
        $sql .= "WHERE num = ? LIMIT 1"; // Update only one record matching the 'num'

        $stmh = $pdo->prepare($sql);

        // Bind the variables to the prepared statement as parameters
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
        $stmh->bindValue(14, $represent , PDO::PARAM_STR);        
        $stmh->bindValue(15, $searchtag , PDO::PARAM_STR);        
        $stmh->bindValue(16, $update_log , PDO::PARAM_STR);        
        $stmh->bindValue(17, $pid , PDO::PARAM_STR);        
        $stmh->bindValue(18, $ppw , PDO::PARAM_STR);        
        $stmh->bindValue(19, $parentnum , PDO::PARAM_STR);        
        $stmh->bindValue(20, $screendc , PDO::PARAM_STR);        
        $stmh->bindValue(21, $etcdc , PDO::PARAM_STR);        
        $stmh->bindValue(22, $secondordnum , PDO::PARAM_STR);                
        $stmh->bindValue(23, $controllerdc , PDO::PARAM_STR);                
        $stmh->bindValue(24, $fabricdc , PDO::PARAM_STR);                
        $stmh->bindValue(25, $paydate , PDO::PARAM_STR);                
        $stmh->bindValue(26, $num, PDO::PARAM_STR);

        // Execute the statement
        $stmh->execute();
        $pdo->commit();
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        print "오류: " . $Exception->getMessage();
    }
		
}   // end of $mode if-statement
	

			
if($mode=="insert")  {	 

    $update_log = date("Y-m-d H:i:s") . " - " . $_SESSION["name"] . " " . $update_log . "&#10";		
	// 데이터 신규 등록하는 구간		
	
try {
    $pdo->beginTransaction();

    // Updated columns and values to be inserted
    $sql = "INSERT INTO " . $DB . "." . $tablename . " (";
    $sql .= "vendor_code, vendor_name, representative_name, address, ";
    $sql .= "business_type, item_type, phone, mobile, email, ";
    $sql .= "fax, manager_name, contact_info, note , represent , searchtag , update_log , pid , ppw, parentnum, screendc, etcdc, secondordnum, controllerdc, fabricdc, paydate ";
    $sql .= ") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; 

    $stmh = $pdo->prepare($sql);

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
	$stmh->bindValue(14, $represent , PDO::PARAM_STR);        
	$stmh->bindValue(15, $searchtag , PDO::PARAM_STR);        
	$stmh->bindValue(16, $update_log , PDO::PARAM_STR);        
	$stmh->bindValue(17, $pid , PDO::PARAM_STR);        
	$stmh->bindValue(18, $ppw , PDO::PARAM_STR);  	
	$stmh->bindValue(19, $parentnum , PDO::PARAM_STR);  
	$stmh->bindValue(20, $screendc , PDO::PARAM_STR);        
	$stmh->bindValue(21, $etcdc , PDO::PARAM_STR); 
	$stmh->bindValue(22, $secondordnum , PDO::PARAM_STR); 
	$stmh->bindValue(23, $controllerdc , PDO::PARAM_STR); 
	$stmh->bindValue(24, $fabricdc , PDO::PARAM_STR); 
	$stmh->bindValue(25, $paydate , PDO::PARAM_STR); 

    // Execute the statement
    $stmh->execute();
    $pdo->commit();
} catch (PDOException $Exception) {
    $pdo->rollBack();
    print "오류: " . $Exception->getMessage();
}
 
// 신규입력된 num 추출
	$sql = "select * from  " .  $DB . "." . $tablename . "  order by num desc ";
	 try{  
		  $stmh = $pdo->query($sql);      // 검색조건에 맞는글 stmh
		  $temp = $stmh->rowCount();
		  $row = $stmh->fetch(PDO::FETCH_ASSOC);
		  $num=$row["num"];		  
		 } catch (PDOException $Exception) {
		   print "오류: ".$Exception->getMessage();
	  }			

	if(empty($secondordnum))
	{
		// 대표아이디 설정하기 새로 생성된 코드가 secondordnum이 된다.
		 try {
				$pdo->beginTransaction();			
				$sql = " UPDATE " . $DB . "." . $tablename . " SET ";
				$sql .= " secondordnum=? " ;
				$sql .= " WHERE num = ? LIMIT 1";

				$stmh = $pdo->prepare($sql);     
				$stmh->bindValue(1, $num , PDO::PARAM_STR);                        
				$stmh->bindValue(2, $num, PDO::PARAM_STR);
				$stmh->execute();
				$pdo->commit();
			} catch (PDOException $Exception) {
				$pdo->rollBack();
				print "오류: " . $Exception->getMessage();
			}
	}
}   // end of $mode if-statement 

if($mode=="delete")  {   // data 삭제시 
   
  
   try{									// esmaindb의 자료를 삭제한다.
     $pdo->beginTransaction();
     $sql = "UPDATE " .  $DB . "." . $tablename . " set is_deleted=1 where num = ?";  
     $stmh = $pdo->prepare($sql);
     $stmh->bindValue(1,$num,PDO::PARAM_STR);      
     $stmh->execute();  

     $pdo->commit();
	 
     } catch (Exception $ex) {
        $pdo->rollBack();
        print "오류: ".$Exception->getMessage();
   }

}


$data = [   
 'num' => $num,
 'mode' => $mode
 
 ]; 
 
 echo json_encode($data, JSON_UNESCAPED_UNICODE);

?>

