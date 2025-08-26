<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php"); 
    exit;
}   

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");

$num = isset($_POST["num"]) ? $_POST["num"] : '';
$tablename = isset($_POST["tablename"]) ? $_POST["tablename"] : '';
$mylist = isset($_POST["mylist"]) ? $_POST["mylist"] : '';

if (empty($tablename) || empty($mylist)) {
    echo json_encode([
        'success' => false,
        'message' => '필수 데이터가 누락되었습니다.'
    ]);
    exit;
}

try {
    $pdo = db_connect();
    
    if ($num > 0) {
        // 수정 모드 (num=1인 레코드 업데이트)
        $sql = "UPDATE " . $DB . "." . $tablename . " 
                SET mylist = ?, updated_at = NOW(), updated_by = ? 
                WHERE num = ?";
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $mylist, PDO::PARAM_STR);
        $stmh->bindValue(2, $_SESSION["name"], PDO::PARAM_STR);
        $stmh->bindValue(3, $num, PDO::PARAM_INT);
        $stmh->execute();
        
        if ($stmh->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => '수정이 완료되었습니다.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => '수정할 데이터를 찾을 수 없습니다.'
            ]);
        }
    } else {
        // 신규 등록 모드 (num=1로 고정)
        $sql = "INSERT INTO " . $DB . "." . $tablename . " 
                (num, mylist, created_by, created_at) 
                VALUES (1, ?, ?, NOW())";
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $mylist, PDO::PARAM_STR);
        $stmh->bindValue(2, $_SESSION["name"], PDO::PARAM_STR);
        $stmh->execute();
        
        echo json_encode([
            'success' => true,
            'message' => '신규 등록이 완료되었습니다.'
        ]);
    }
    
} catch (PDOException $Exception) {
    echo json_encode([
        'success' => false,
        'message' => '오류가 발생했습니다: ' . $Exception->getMessage()
    ]);
}
?>
