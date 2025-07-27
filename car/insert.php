<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  
$tablename = isset($_REQUEST['tablename']) ? $_REQUEST['tablename'] : '';  

header("Content-Type: application/json");  // json을 사용하기 위해 필요한 구문

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();	

include "_request.php";

// engine_oil_change_data와 maintenance_data를 JSON으로 디코드
$engine_oil_change_data_json = isset($_POST['engine_oil_change_data']) ? json_decode($_POST['engine_oil_change_data'], true) : null;
$maintenance_data_json = isset($_POST['maintenance_data']) ? json_decode($_POST['maintenance_data'], true) : null;

// 검색 태그를 구성하는 부분 (필요한 필드를 조합해서 검색 태그로 만듦)
$searchtag = $vehicle_number . ' ' .
             $vehicle_type . ' ' .
             $responsible_person . ' ' .
             $assistant . ' ' .
             $insurance . ' ' .
             $note . ' ' .
             $update_log;

if ($mode == "modify")  {
    $update_log = date("Y-m-d H:i:s") . " - " . $_SESSION["name"] . " " . $update_log . "&#10";
    try {
        $pdo->beginTransaction();
        $sql = "UPDATE " . $DB . "." . $tablename . " SET ";
        $sql .= "vehicle_number = ?, vehicle_type = ?, responsible_person = ?, assistant = ?, ";
        $sql .= "insurance = ?, insurance_contact = ?, total_distance_km = ?, manufacturing_date = ?, purchase_date = ?, purchase_type = ?, ";
        $sql .= "engine_oil_change_data = ?, engine_oil_change_cycle = ?, maintenance_data = ?, note = ?, searchtag = ?, update_log = ?, ";
        $sql .= "is_deleted = ?, KMrecordDate = ?, inspectionDate = ?, inspectionDateTo=?, fromInsuDate=?, toInsuDate=?  WHERE num = ? LIMIT 1 ";

        $stmh = $pdo->prepare($sql);

        // 바인딩
        $stmh->bindValue(1, $vehicle_number, PDO::PARAM_STR);
        $stmh->bindValue(2, $vehicle_type, PDO::PARAM_STR);
        $stmh->bindValue(3, $responsible_person, PDO::PARAM_STR);
        $stmh->bindValue(4, $assistant, PDO::PARAM_STR);
        $stmh->bindValue(5, $insurance, PDO::PARAM_STR);
        $stmh->bindValue(6, $insurance_contact, PDO::PARAM_STR); // 추가: 보험사 연락처
        $stmh->bindValue(7, $total_distance_km, PDO::PARAM_STR);
        $stmh->bindValue(8, $manufacturing_date, PDO::PARAM_STR);
        $stmh->bindValue(9, $purchase_date, PDO::PARAM_STR);
        $stmh->bindValue(10, $purchase_type, PDO::PARAM_STR); // 추가: 구매 유형
        // JSON으로 인코딩된 engine_oil_change_data
        $stmh->bindValue(11, json_encode($engine_oil_change_data_json), PDO::PARAM_STR);
        $stmh->bindValue(12, $engine_oil_change_cycle, PDO::PARAM_STR); // 추가: 엔진오일 교환주기
        // JSON으로 인코딩된 maintenance_data
        $stmh->bindValue(13, json_encode($maintenance_data_json), PDO::PARAM_STR);
        $stmh->bindValue(14, $note, PDO::PARAM_STR);
        $stmh->bindValue(15, $searchtag, PDO::PARAM_STR);
        $stmh->bindValue(16, $update_log, PDO::PARAM_STR);
        $stmh->bindValue(17, $is_deleted, PDO::PARAM_STR);
        $stmh->bindValue(18, $KMrecordDate, PDO::PARAM_STR);
        $stmh->bindValue(19, $inspectionDate, PDO::PARAM_STR);
        $stmh->bindValue(20, $inspectionDateTo, PDO::PARAM_STR);
        $stmh->bindValue(21, $fromInsuDate, PDO::PARAM_STR);
        $stmh->bindValue(22, $toInsuDate, PDO::PARAM_STR);
        $stmh->bindValue(23, $num, PDO::PARAM_INT);

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
        $sql .= "vehicle_number, vehicle_type, responsible_person, assistant, ";
        $sql .= "insurance, insurance_contact, total_distance_km, manufacturing_date, purchase_date, purchase_type, ";
        $sql .= "engine_oil_change_data, engine_oil_change_cycle, maintenance_data, note, searchtag, update_log, is_deleted, KMrecordDate, inspectionDate, inspectionDateTo, fromInsuDate, toInsuDate ";
        $sql .= ") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmh = $pdo->prepare($sql);

        // 바인딩
        $stmh->bindValue(1, $vehicle_number, PDO::PARAM_STR);
        $stmh->bindValue(2, $vehicle_type, PDO::PARAM_STR);
        $stmh->bindValue(3, $responsible_person, PDO::PARAM_STR);
        $stmh->bindValue(4, $assistant, PDO::PARAM_STR);
        $stmh->bindValue(5, $insurance, PDO::PARAM_STR);
        $stmh->bindValue(6, $insurance_contact, PDO::PARAM_STR); // 추가: 보험사 연락처
        $stmh->bindValue(7, $total_distance_km, PDO::PARAM_STR);
        $stmh->bindValue(8, $manufacturing_date, PDO::PARAM_STR);
        $stmh->bindValue(9, $purchase_date, PDO::PARAM_STR);
        $stmh->bindValue(10, $purchase_type, PDO::PARAM_STR); // 추가: 구매 유형
        // JSON으로 인코딩된 engine_oil_change_data
        $stmh->bindValue(11, json_encode($engine_oil_change_data_json), PDO::PARAM_STR);
        $stmh->bindValue(12, $engine_oil_change_cycle, PDO::PARAM_STR); // 추가: 엔진오일 교환주기
        // JSON으로 인코딩된 maintenance_data
        $stmh->bindValue(13, json_encode($maintenance_data_json), PDO::PARAM_STR);
        $stmh->bindValue(14, $note, PDO::PARAM_STR);
        $stmh->bindValue(15, $searchtag, PDO::PARAM_STR);
        $stmh->bindValue(16, $update_log, PDO::PARAM_STR);
        $stmh->bindValue(17, $is_deleted, PDO::PARAM_STR);
        $stmh->bindValue(18, $KMrecordDate, PDO::PARAM_STR);
        $stmh->bindValue(19, $inspectionDate, PDO::PARAM_STR);
        $stmh->bindValue(20, $inspectionDateTo, PDO::PARAM_STR);	
        $stmh->bindValue(21, $fromInsuDate, PDO::PARAM_STR);
        $stmh->bindValue(22, $toInsuDate, PDO::PARAM_STR);		

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
        $sql = "UPDATE " . $DB . "." . $tablename . " SET is_deleted = '1' WHERE num = ?";  
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
