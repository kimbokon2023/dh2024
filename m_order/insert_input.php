<?php   
// POST 데이터 크기 제한 늘리기 (413 에러 해결)
ini_set('post_max_size', '50M');
ini_set('upload_max_filesize', '50M');
ini_set('max_execution_time', 300);
ini_set('memory_limit', '256M');
// 서버 설정 확인
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");

$mode = $_REQUEST['mode'] ?? '';  
$num = $_REQUEST['num'] ?? '';
$tablename = 'm_order';

// SQL 쿼리에서 사용되는 컬럼들의 $_REQUEST 변수 정의
$orderDate = $_REQUEST['orderDate'] ?? '';
$memo = $_REQUEST['memo'] ?? '';
$orderlist = $_REQUEST['orderlist'] ?? '';
$update_log = $_REQUEST['update_log'] ?? '';
$first_writer = $_REQUEST['first_writer'] ?? '';
$totalsurang = $_REQUEST['totalsurang'] ?? '';
$totalamount = $_REQUEST['totalamount'] ?? '';
$inputSum1 = $_REQUEST['inputSum1'] ?? '';
$inputSum2 = $_REQUEST['inputSum2'] ?? '';
$inputSum3 = $_REQUEST['inputSum3'] ?? '';
$inputSum4 = $_REQUEST['inputSum4'] ?? '';
$inputSum5 = $_REQUEST['inputSum5'] ?? '';
$inputSum6 = $_REQUEST['inputSum6'] ?? '';
$inputSum7 = $_REQUEST['inputSum7'] ?? '';

header("Content-Type: application/json");

// 콤마 제거 관련 정리
$unitprice = isset($unitprice) ? floatval(str_replace(',', '', $unitprice)) : '';
$amount = isset($amount) ? floatval(str_replace(',', '', $amount)) : '';
$invoiceAmount = isset($invoiceAmount) ? floatval(str_replace(',', '', $invoiceAmount)) : '';

// DB 연결
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

$orderlist_jsondata = isset($_POST['orderlist']) ? json_decode($_POST['orderlist'], true) : null;

if ($mode == "modify") {
    $update_log = date("Y-m-d H:i:s") . " - " . $_SESSION["name"] . "&#10" . $update_log;

    try {
        $pdo->beginTransaction();
        $sql = "UPDATE m_order SET 
            orderDate=?, memo=?, orderlist=?, update_log=?, first_writer=?, 
            totalsurang=?, totalamount=?, 
            inputSum1=?, inputSum2=?, inputSum3=?, inputSum4=?, inputSum5=?, inputSum6=?, inputSum7=?              
            WHERE num=? LIMIT 1";

        $stmh = $pdo->prepare($sql);

        $params = [
            $orderDate, $memo, json_encode($orderlist_jsondata), $update_log, $first_writer,
            $totalsurang, $totalamount,
            $inputSum1, $inputSum2, $inputSum3, $inputSum4, $inputSum5, $inputSum6, $inputSum7,
            $num
        ];

        $stmh->execute($params);
        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => '수정 완료']);
        exit;
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => '오류: ' . $Exception->getMessage()]);
        exit;
    }
}

if ($mode == "insert" || $mode == "copy") {
    $update_log = '';
    $first_writer = $_SESSION["name"];

    try {
        $pdo->beginTransaction();

        $sql = "INSERT INTO m_order (
            orderDate, memo, orderlist, update_log, first_writer, totalsurang, totalamount, inputSum1, inputSum2, inputSum3, inputSum4, inputSum5, inputSum6, inputSum7
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )";

        $stmh = $pdo->prepare($sql);

        $params = [
            $orderDate, $memo, json_encode($orderlist_jsondata), $update_log, $first_writer, $totalsurang, $totalamount, 
            $inputSum1, $inputSum2, $inputSum3, $inputSum4, $inputSum5, $inputSum6, $inputSum7
        ];

        $stmh->execute($params);
        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => '저장 완료']);
        exit;
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => '오류: ' . $Exception->getMessage()]);
        exit;
    }
}

if ($mode == "copy") {
    try {  
        $stmh = $pdo->query("SELECT * FROM m_order ORDER BY num DESC");    
        $row = $stmh->fetch(PDO::FETCH_ASSOC); 
        $num = $row["num"];
        echo json_encode(['status' => 'success', 'num' => $num]);
        exit;
    } catch (PDOException $Exception) {
        echo json_encode(['status' => 'error', 'message' => '오류: ' . $Exception->getMessage()]);
        exit;
    }
}

if ($mode == "delete") {
    try {
        $pdo->beginTransaction();
        $query = "UPDATE m_order SET is_deleted=1 WHERE num=? LIMIT 1";
        $stmh = $pdo->prepare($query);
        $stmh->execute([$num]);
        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => '삭제 완료']);
        exit;
    } catch (PDOException $Exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['status' => 'error', 'message' => '오류: ' . $Exception->getMessage()]);
        exit;
    }
}

// insert나 copy 모드에서 num 가져오기
if ($mode == "insert" || $mode == "copy") {
    try {
        $stmh = $pdo->query("SELECT * FROM m_order ORDER BY num DESC LIMIT 1");
        $row = $stmh->fetch(PDO::FETCH_ASSOC); 	 
        $num = $row["num"]; 	
    } catch (PDOException $Exception) {
        error_log("오류: " . $Exception->getMessage());
        echo json_encode(['status' => 'error', 'message' => '시스템 오류가 발생했습니다. 관리자에게 문의하세요.']);
        exit;
    }
}

// 기본 응답 데이터 (모드가 지정되지 않은 경우)
$data = [   
    'num' => $num,
    'mode' => $mode,
    'totalsurang' => $totalsurang,
    'totalamount' => $totalamount,
    'inputSum1' => $inputSum1,
    'inputSum2' => $inputSum2,
    'inputSum3' => $inputSum3,
    'inputSum4' => $inputSum4,
    'inputSum5' => $inputSum5,
    'inputSum6' => $inputSum6,
    'inputSum7' => $inputSum7
];

echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>
