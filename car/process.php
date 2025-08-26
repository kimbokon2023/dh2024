<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php"); 
    exit;
}   

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

$response = array('success' => false, 'message' => '');

try {
    $mode = isset($_POST['mode']) ? $_POST['mode'] : '';
    $num = isset($_POST['num']) ? $_POST['num'] : '';
    $tablename = isset($_POST['tablename']) ? $_POST['tablename'] : '';
    $mylist = isset($_POST['mylist']) ? $_POST['mylist'] : '';
    $created_by = isset($_POST['created_by']) ? $_POST['created_by'] : '';
    
    if (empty($tablename) || empty($mylist)) {
        throw new Exception('필수 데이터가 누락되었습니다.');
    }
    
    // JSON 데이터 검증
    $product_list = json_decode($mylist, true);
    if (!is_array($product_list)) {
        throw new Exception('제품 데이터 형식이 올바르지 않습니다.');
    }
    
    if ($mode === 'insert') {
        // 신규 등록
        $sql = "INSERT INTO " . $DB . "." . $tablename . " 
                (mylist, created_by, created_at) 
                VALUES (?, ?, NOW())";
        
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $mylist, PDO::PARAM_STR);
        $stmh->bindValue(2, $created_by, PDO::PARAM_STR);
        
        if ($stmh->execute()) {
            $response['success'] = true;
            $response['message'] = '신규 등록이 완료되었습니다.';
        } else {
            throw new Exception('데이터 저장에 실패했습니다.');
        }
        
    } elseif ($mode === 'modify' && !empty($num)) {
        // 수정
        $sql = "UPDATE " . $DB . "." . $tablename . " 
                SET mylist = ?, updated_by = ?, updated_at = NOW() 
                WHERE num = ? AND is_deleted != 'Y'";
        
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $mylist, PDO::PARAM_STR);
        $stmh->bindValue(2, $created_by, PDO::PARAM_STR);
        $stmh->bindValue(3, $num, PDO::PARAM_INT);
        
        if ($stmh->execute()) {
            $response['success'] = true;
            $response['message'] = '수정이 완료되었습니다.';
        } else {
            throw new Exception('데이터 수정에 실패했습니다.');
        }
        
    } elseif ($mode === 'delete' && !empty($num)) {
        // 삭제 (논리 삭제)
        $sql = "UPDATE " . $DB . "." . $tablename . " 
                SET is_deleted = 'Y', deleted_by = ?, deleted_at = NOW() 
                WHERE num = ? AND is_deleted != 'Y'";
        
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $created_by, PDO::PARAM_STR);
        $stmh->bindValue(2, $num, PDO::PARAM_INT);
        
        if ($stmh->execute()) {
            $response['success'] = true;
            $response['message'] = '삭제가 완료되었습니다.';
        } else {
            throw new Exception('데이터 삭제에 실패했습니다.');
        }
        
    } else {
        throw new Exception('잘못된 요청입니다.');
    }
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
} catch (PDOException $e) {
    $response['success'] = false;
    $response['message'] = '데이터베이스 오류: ' . $e->getMessage();
}

// JSON 응답 반환
header('Content-Type: application/json; charset=utf-8');
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
