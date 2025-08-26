<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");

$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';
$num = isset($_REQUEST['num']) ? $_REQUEST['num'] : '';
$tablename = isset($_REQUEST['tablename']) ? $_REQUEST['tablename'] : '';

include '_request.php';

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();
 
try {
    if ($mode == "modify") {
        $itemList = isset($_REQUEST['itemList']) ? $_REQUEST['itemList'] : ''; // 이미 JSON 형태로 받음
        $update_log = isset($_REQUEST['update_log']) ? $_REQUEST['update_log'] : '';
        $searchtag = isset($_REQUEST['searchtag']) ? $_REQUEST['searchtag'] : '';
        $is_deleted = isset($_REQUEST['is_deleted']) ? $_REQUEST['is_deleted'] : null;

        $update_log = date("Y-m-d H:i:s") . " - " . $_SESSION["name"] . " " . $update_log . "&#10";

        $pdo->beginTransaction();
        $sql = "UPDATE $DB.$tablename SET itemList=?, is_deleted=?, update_log=?, searchtag=?, memo=?, registedate=?  WHERE num=? LIMIT 1";

        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $itemList, PDO::PARAM_STR); // 이미 JSON 인코딩된 데이터
        $stmh->bindValue(2, $is_deleted, PDO::PARAM_BOOL);
        $stmh->bindValue(3, $update_log, PDO::PARAM_STR);
        $stmh->bindValue(4, $searchtag, PDO::PARAM_STR);
        $stmh->bindValue(5, $memo, PDO::PARAM_STR);
        $stmh->bindValue(6, $registedate, PDO::PARAM_STR);
        $stmh->bindValue(7, $num, PDO::PARAM_INT);
        $stmh->execute();
        $pdo->commit();
    }

    if ($mode == "insert") {
        $itemList = isset($_REQUEST['itemList']) ? $_REQUEST['itemList'] : ''; // 이미 JSON 형태로 받음
        $update_log = date("Y-m-d H:i:s") . " - " . $_SESSION["name"];
        $searchtag = isset($_REQUEST['searchtag']) ? $_REQUEST['searchtag'] : '';

        $pdo->beginTransaction();
        $sql = "INSERT INTO $DB.$tablename (itemList, is_deleted, update_log, searchtag, memo, registedate) VALUES (?, ?, ?, ?, ?, ?)";

        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $itemList, PDO::PARAM_STR); // 이미 JSON 인코딩된 데이터
        $stmh->bindValue(2, $is_deleted, PDO::PARAM_BOOL);
        $stmh->bindValue(3, $update_log, PDO::PARAM_STR);
        $stmh->bindValue(4, $searchtag, PDO::PARAM_STR);
        $stmh->bindValue(5, $memo, PDO::PARAM_STR);
        $stmh->bindValue(6, $registedate, PDO::PARAM_STR);
        $stmh->execute();
        $pdo->commit();

        // 가장 최근에 삽입된 레코드를 가져오기 위해 num 값을 조회
        $sql = "SELECT num FROM $DB.$tablename ORDER BY num DESC LIMIT 1";
        $stmh = $pdo->prepare($sql);
        $stmh->execute();
        $num = $stmh->fetchColumn(); // num 값 가져오기
    }


    if ($mode == "delete") {
        $pdo->beginTransaction();
        $sql = "UPDATE $DB.$tablename SET is_deleted='1' WHERE num=? LIMIT 1";
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $num, PDO::PARAM_INT);
        $stmh->execute();
        $pdo->commit();
    }

    $data = [
        'num' => $num,
        'mode' => $mode,
        'is_deleted' => $is_deleted
    ];

    echo json_encode($data, JSON_UNESCAPED_UNICODE);
} catch (PDOException $Exception) {
    error_log("오류: " . $Exception->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $Exception->getMessage()]);
} catch (Exception $e) {
    error_log("오류: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
