<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

$tablename = isset($_REQUEST['tablename']) ? $_REQUEST['tablename'] : '';  
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  

header("Content-Type: application/json");  // json을 사용하기 위해 필요한 구문

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();	

include "_request.php";

// Construct searchtag
$searchtag = $vendor_name . ' ' .
              $representative_name . ' ' .
              $address . ' ' .
              $business_type . ' ' .
              $item_type . ' ' .
              $category . ' ' .
              $phone . ' ' .
              $mobile . ' ' .
              $email . ' ' .
              $fax . ' ' .
              $manager_name . ' ' .
              $contact_info . ' ' .
              $note . ' ' .
              $is_deleted . ' ' .
              $item . ' ' .
              $is_china_vendor . ' ' .
              $china_sort_order . ' ' .
              $update_log;

if ($mode == "update")  {
    $update_log = date("Y-m-d H:i:s") . " - " . $_SESSION["name"] . " " . $update_log . "&#10";
    try {
        $pdo->beginTransaction();
        // Prepare the SQL query for updating the vendor information
        $sql = "UPDATE " . $DB . "." . $tablename . " SET ";
        $sql .= "vendor_code = ?, vendor_name = ?, representative_name = ?, address = ?, ";
        $sql .= "business_type = ?, item_type = ?, phone = ?, mobile = ?, email = ?, ";
        $sql .= "fax = ?, manager_name = ?, contact_info = ?, note = ?, category = ?, searchtag = ?, update_log = ?, item = ?, ";
        $sql .= "is_china_vendor = ?, china_sort_order = ?, image_base64 = ? ";
        $sql .= " WHERE num = ? LIMIT 1"; // Update only one record matching the 'num'

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
        $stmh->bindValue(14, $category, PDO::PARAM_STR);
        $stmh->bindValue(15, $searchtag, PDO::PARAM_STR);
        $stmh->bindValue(16, $update_log, PDO::PARAM_STR);
        $stmh->bindValue(17, $item, PDO::PARAM_STR);
        $stmh->bindValue(18, $is_china_vendor, PDO::PARAM_INT);
        $stmh->bindValue(19, $china_sort_order, PDO::PARAM_INT);
        $stmh->bindValue(20, $image_base64, PDO::PARAM_STR);
        $stmh->bindValue(21, $num, PDO::PARAM_INT);

        // Execute the statement
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

        // Prepare the SQL query for inserting a new vendor
        $sql = "INSERT INTO " . $DB . "." . $tablename . " (";
        $sql .= "vendor_code, vendor_name, representative_name, address, ";
        $sql .= "business_type, item_type, phone, mobile, email, ";
        $sql .= "fax, manager_name, contact_info, note, category, searchtag, update_log, item, is_china_vendor, china_sort_order, image_base64 ";
        $sql .= ") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

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
        $stmh->bindValue(14, $category, PDO::PARAM_STR);
        $stmh->bindValue(15, $searchtag, PDO::PARAM_STR);
        $stmh->bindValue(16, $update_log, PDO::PARAM_STR);
        $stmh->bindValue(17, $item, PDO::PARAM_STR);
        $stmh->bindValue(18, $is_china_vendor, PDO::PARAM_INT);
        $stmh->bindValue(19, $china_sort_order, PDO::PARAM_INT);
        $stmh->bindValue(20, $image_base64, PDO::PARAM_STR);

        // Execute the statement
        $stmh->execute();
        $pdo->commit();
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        print "오류: " . $Exception->getMessage();
    }
} elseif ($mode == "copy")  {
    // copy는 현재 입력된 값으로 신규 레코드 생성
    $update_log = date("Y-m-d H:i:s") . " - " . $_SESSION["name"] . " 복사 생성\n" . $update_log . "&#10";

    try {
        $pdo->beginTransaction();

        $sql = "INSERT INTO " . $DB . "." . $tablename . " (";
        $sql .= "vendor_code, vendor_name, representative_name, address, ";
        $sql .= "business_type, item_type, phone, mobile, email, ";
        $sql .= "fax, manager_name, contact_info, note, category, searchtag, update_log, item, is_china_vendor, china_sort_order, image_base64 ";
        $sql .= ") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

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
        $stmh->bindValue(14, $category, PDO::PARAM_STR);
        $stmh->bindValue(15, $searchtag, PDO::PARAM_STR);
        $stmh->bindValue(16, $update_log, PDO::PARAM_STR);
        $stmh->bindValue(17, $item, PDO::PARAM_STR);
        $stmh->bindValue(18, $is_china_vendor, PDO::PARAM_INT);
        $stmh->bindValue(19, $china_sort_order, PDO::PARAM_INT);
        $stmh->bindValue(20, $image_base64, PDO::PARAM_STR);

        $stmh->execute();
        $pdo->commit();
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        print "오류: " . $Exception->getMessage();
    }

    // Retrieve the newly inserted row's num
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
