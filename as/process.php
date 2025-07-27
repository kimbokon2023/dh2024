<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

$tablename = 'as'; 

$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  

header("Content-Type: application/json");  // json을 사용하기 위해 필요한 구문

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();	

include "_request.php";

$searchtag = $asorderman . ' ' .
             $aswriter . ' ' .
             $aslist . ' ' .
             $as_refer . ' ' .
             $as_step . ' ' .
             $asordermantel . ' ' .
             $asman . ' ' .
             $note . ' ' .
             $is_deleted . ' ' .
             $ashistory . ' ' .
             $address . ' ' .
             $workplacename . ' ' .
             $workplaceCode . ' ' .
             $payman . ' ' .
             $itemcheck . ' ' .
             $update_log;

if ($mode == "modify")  {
    $update_log = date("Y-m-d H:i:s") . " - " . $_SESSION["name"] . " " . $update_log . "&#10";
    try {
        $pdo->beginTransaction();
        $sql = "UPDATE " . $DB . "." . $tablename . " SET ";
        $sql .= "asday = ?, aswriter = ?, asorderman = ?, asordermantel = ?, as_step = ?, ";
        $sql .= "asfee = ?, asfee_estimate = ?, aslist = ?, as_refer = ?, asproday = ?, ";
        $sql .= "setdate = ?, asman = ?, asendday = ?, asresult = ?, ashistory = ?, note = ?, searchtag = ?, update_log = ?, address = ?, ";
        $sql .= "payment = ?, demandDate = ?, spotman = ?, spotmantel = ? , workplacename = ? , workplaceCode = ? , payman = ?, paydate = ?, itemcheck = ? ";
        $sql .= "WHERE num = ? LIMIT 1"; 

        $stmh = $pdo->prepare($sql);

        $stmh->bindValue(1, $asday, PDO::PARAM_STR);
        $stmh->bindValue(2, $aswriter, PDO::PARAM_STR);
        $stmh->bindValue(3, $asorderman, PDO::PARAM_STR);
        $stmh->bindValue(4, $asordermantel, PDO::PARAM_STR);
        $stmh->bindValue(5, $as_step, PDO::PARAM_STR);
        $stmh->bindValue(6, $asfee, PDO::PARAM_INT);
        $stmh->bindValue(7, $asfee_estimate, PDO::PARAM_INT);
        $stmh->bindValue(8, $aslist, PDO::PARAM_STR);
        $stmh->bindValue(9, $as_refer, PDO::PARAM_STR);
        $stmh->bindValue(10, $asproday, PDO::PARAM_STR);
        $stmh->bindValue(11, $setdate, PDO::PARAM_STR);
        $stmh->bindValue(12, $asman, PDO::PARAM_STR);
        $stmh->bindValue(13, $asendday, PDO::PARAM_STR);
        $stmh->bindValue(14, $asresult, PDO::PARAM_STR);
        $stmh->bindValue(15, $ashistory, PDO::PARAM_STR);
        $stmh->bindValue(16, $note, PDO::PARAM_STR);
        $stmh->bindValue(17, $searchtag, PDO::PARAM_STR);
        $stmh->bindValue(18, $update_log, PDO::PARAM_STR);
        $stmh->bindValue(19, $address, PDO::PARAM_STR);
        $stmh->bindValue(20, $payment, PDO::PARAM_STR); // 추가된 필드
        $stmh->bindValue(21, $demandDate, PDO::PARAM_STR); // 추가된 필드
        $stmh->bindValue(22, $spotman, PDO::PARAM_STR); // 추가된 필드
        $stmh->bindValue(23, $spotmantel, PDO::PARAM_STR); // 추가된 필드
        $stmh->bindValue(24, $workplacename, PDO::PARAM_STR); 
        $stmh->bindValue(25, $workplaceCode, PDO::PARAM_STR); 
        $stmh->bindValue(26, $payman, PDO::PARAM_STR); 
        $stmh->bindValue(27, $paydate, PDO::PARAM_STR); 
        $stmh->bindValue(28, $itemcheck, PDO::PARAM_STR); 
        $stmh->bindValue(29, $num, PDO::PARAM_INT);

        $stmh->execute();
        $pdo->commit();
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        print "오류: " . $Exception->getMessage();
    }
} elseif ($mode == "insert")  {	 
    $update_log = date("Y-m-d H:i:s") . " - " . $_SESSION["name"] . " " . $update_log . "&#10";	

    try {
        $pdo->beginTransaction();

        $sql = "INSERT INTO " . $DB . "." . $tablename . " (";
        $sql .= "asday, aswriter, asorderman, asordermantel, as_step, ";
        $sql .= "asfee, asfee_estimate, aslist, as_refer, asproday, ";
        $sql .= "setdate, asman, asendday, asresult, ashistory, note, searchtag, update_log, address, ";
        $sql .= "payment, demandDate, spotman, spotmantel, workplacename, workplaceCode, payman, paydate, itemcheck "; // 추가된 필드들
        $sql .= ") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmh = $pdo->prepare($sql);

        $stmh->bindValue(1, $asday, PDO::PARAM_STR);
        $stmh->bindValue(2, $aswriter, PDO::PARAM_STR);
        $stmh->bindValue(3, $asorderman, PDO::PARAM_STR);
        $stmh->bindValue(4, $asordermantel, PDO::PARAM_STR);
        $stmh->bindValue(5, $as_step, PDO::PARAM_STR);
        $stmh->bindValue(6, $asfee, PDO::PARAM_INT);
        $stmh->bindValue(7, $asfee_estimate, PDO::PARAM_INT);
        $stmh->bindValue(8, $aslist, PDO::PARAM_STR);
        $stmh->bindValue(9, $as_refer, PDO::PARAM_STR);
        $stmh->bindValue(10, $asproday, PDO::PARAM_STR);
        $stmh->bindValue(11, $setdate, PDO::PARAM_STR);
        $stmh->bindValue(12, $asman, PDO::PARAM_STR);
        $stmh->bindValue(13, $asendday, PDO::PARAM_STR);
        $stmh->bindValue(14, $asresult, PDO::PARAM_STR);
        $stmh->bindValue(15, $ashistory, PDO::PARAM_STR);
        $stmh->bindValue(16, $note, PDO::PARAM_STR);
        $stmh->bindValue(17, $searchtag, PDO::PARAM_STR);
        $stmh->bindValue(18, $update_log, PDO::PARAM_STR);
        $stmh->bindValue(19, $address, PDO::PARAM_STR);
        $stmh->bindValue(20, $payment, PDO::PARAM_STR); // 추가된 필드
        $stmh->bindValue(21, $demandDate, PDO::PARAM_STR); // 추가된 필드
        $stmh->bindValue(22, $spotman, PDO::PARAM_STR); // 추가된 필드
        $stmh->bindValue(23, $spotmantel, PDO::PARAM_STR); // 추가된 필드
        $stmh->bindValue(24, $workplacename, PDO::PARAM_STR); 
        $stmh->bindValue(25, $workplaceCode, PDO::PARAM_STR); 		
        $stmh->bindValue(26, $payman, PDO::PARAM_STR); 		
        $stmh->bindValue(27, $paydate, PDO::PARAM_STR); 		
        $stmh->bindValue(28, $itemcheck, PDO::PARAM_STR); 		

        $stmh->execute();
        $pdo->commit();
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        print "오류: " . $Exception->getMessage();
    }

    $sql = "SELECT num FROM " . $DB . "." . $tablename . " ORDER BY num DESC LIMIT 1";
    try {
        $stmh = $pdo->query($sql);
        $row = $stmh->fetch(PDO::FETCH_ASSOC);
        $num = $row["num"];
    } catch (PDOException $Exception) {
        print "오류: " . $Exception->getMessage();
    }
} elseif ($mode == "delete")  {   
    try {
        $pdo->beginTransaction();
        $sql = "UPDATE " . $DB . "." . $tablename . " SET is_deleted = 1 WHERE num = ?";  
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $num, PDO::PARAM_INT);      
        $stmh->execute();  
        $pdo->commit();
    } catch (Exception $ex) {
        $pdo->rollBack();
        print "오류: " . $ex->getMessage();
    }
}

$data = [   
    'num' => $num,
    'mode' => $mode
]; 

echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>
