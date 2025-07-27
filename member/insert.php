<?php   

require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

header("Content-Type: application/json");  //json을 사용하기 위해 필요한 구문  

isset($_REQUEST["mode"]) ? $mode = $_REQUEST["mode"] : $mode=""; 

include '_request.php';
			  
// 체크박스 처리에 유의할것			  
			  
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();
     
if ($mode == "modify") {      
    try {
        $pdo->beginTransaction();   
        $sql = "UPDATE " . $DB . ".member SET id=?, name=?, pass=?, userlevel=?, part=?, hp=?, numorder=?, eworks_level=?, ecountID=?, regist_day=?, position=?, enterDate=?, quitDate=?, birthday=?, address=?, dailyworkcheck=?, company=? WHERE num = ? LIMIT 1";		
		   
        $stmh = $pdo->prepare($sql); 	
        $stmh->bindValue(1, $id, PDO::PARAM_STR);  
        $stmh->bindValue(2, $name, PDO::PARAM_STR);  
        $stmh->bindValue(3, $pass, PDO::PARAM_STR);  
        $stmh->bindValue(4, $userlevel, PDO::PARAM_STR);  
        $stmh->bindValue(5, $part, PDO::PARAM_STR);  
        $stmh->bindValue(6, $hp, PDO::PARAM_STR);      
        $stmh->bindValue(7, $numorder, PDO::PARAM_STR);       
        $stmh->bindValue(8, $eworks_level, PDO::PARAM_STR);       
        $stmh->bindValue(9, $ecountID, PDO::PARAM_STR);       
        $stmh->bindValue(10, $regist_day, PDO::PARAM_STR);       
        $stmh->bindValue(11, $position, PDO::PARAM_STR);       
        $stmh->bindValue(12, $enterDate, PDO::PARAM_STR);       
        $stmh->bindValue(13, $quitDate, PDO::PARAM_STR);       
        $stmh->bindValue(14, $birthday, PDO::PARAM_STR);       
        $stmh->bindValue(15, $address, PDO::PARAM_STR);       
        $stmh->bindValue(16, $dailyworkcheck, PDO::PARAM_STR);       
        $stmh->bindValue(17, $company, PDO::PARAM_STR);       
        $stmh->bindValue(18, $num, PDO::PARAM_INT);       
	 
        $stmh->execute();
        $pdo->commit(); 
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        print "오류: ".$Exception->getMessage();
    }                         
} 
 
if ($mode == "insert") {	 	 

    $regist_day = date('Y-m-d'); // 현재날짜 입력

    try {
        $pdo->beginTransaction();
  	 
        $sql = "INSERT INTO " . $DB . ".member(id, name, pass, userlevel, part, hp, numorder, eworks_level, ecountID, regist_day, position, enterDate, quitDate, birthday, address, dailyworkcheck, company) ";     
        $sql .= "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";  
	
        $stmh = $pdo->prepare($sql); 
	   
        $stmh->bindValue(1, $id, PDO::PARAM_STR);  
        $stmh->bindValue(2, $name, PDO::PARAM_STR);  
        $stmh->bindValue(3, $pass, PDO::PARAM_STR);  
        $stmh->bindValue(4, $userlevel, PDO::PARAM_STR);  
        $stmh->bindValue(5, $part, PDO::PARAM_STR);  
        $stmh->bindValue(6, $hp, PDO::PARAM_STR);      
        $stmh->bindValue(7, $numorder, PDO::PARAM_STR);       
        $stmh->bindValue(8, $eworks_level, PDO::PARAM_STR);   
        $stmh->bindValue(9, $ecountID, PDO::PARAM_STR);       
        $stmh->bindValue(10, $regist_day, PDO::PARAM_STR);    	
        $stmh->bindValue(11, $position, PDO::PARAM_STR);       
        $stmh->bindValue(12, $enterDate, PDO::PARAM_STR);       
        $stmh->bindValue(13, $quitDate, PDO::PARAM_STR);       
        $stmh->bindValue(14, $birthday, PDO::PARAM_STR);       
        $stmh->bindValue(15, $address, PDO::PARAM_STR);       
        $stmh->bindValue(16, $dailyworkcheck, PDO::PARAM_STR);       
        $stmh->bindValue(17, $company, PDO::PARAM_STR);       
	 
        $stmh->execute();
        $pdo->commit(); 
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        print "오류: ".$Exception->getMessage();
    }   
}

if ($mode == "delete") {	 	 
    try {
        $pdo->beginTransaction();
  	 
        $sql = "DELETE FROM " . $DB . ".member WHERE num = ?";  
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $num, PDO::PARAM_INT);      
        $stmh->execute();   
        $pdo->commit();	 
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        print "오류: ".$Exception->getMessage();
    }   
}

// 기존의 $data 배열에 추가 정보를 포함합니다.
$data = array(
    "num" => $num,
    "mode" => $mode,
    "id" => $id,
    "name" => $name,
    "pass" => $pass,
    "userlevel" => $userlevel,
    "part" => $part,
    "hp" => $hp,
    "numorder" => $numorder,
    "eworks_level" => $eworks_level,
    "ecountID" => $ecountID,
    "regist_day" => $regist_day,
    "position" => $position,
    "enterDate" => $enterDate,
    "quitDate" => $quitDate,
    "birthday" => $birthday,
    "address" => $address,
    "dailyworkcheck" => $dailyworkcheck,
    "company" => $company,
);

//json 출력
echo(json_encode($data, JSON_UNESCAPED_UNICODE));   
?>
