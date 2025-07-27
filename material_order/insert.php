<?php   
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");

$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';  
$num = isset($_REQUEST['num']) ? $_REQUEST['num'] : '';
$tablename = 'material_order';

header("Content-Type: application/json");

include '_request.php';

// Fetch JSON data from POST request
$motorlist_jsondata = isset($_POST['motorlist']) ? json_decode($_POST['motorlist'], true) : null;
$wirelessClist_jsondata = isset($_POST['wirelessClist']) ? json_decode($_POST['wirelessClist'], true) : null;
$wireClist_jsondata = isset($_POST['wireClist']) ? json_decode($_POST['wireClist'], true) : null;
$wirelessLinklist_jsondata = isset($_POST['wirelessLinklist']) ? json_decode($_POST['wirelessLinklist'], true) : null;
$wireLinklist_jsondata = isset($_POST['wireLinklist']) ? json_decode($_POST['wireLinklist'], true) : null;
$bracketlist_jsondata = isset($_POST['bracketlist']) ? json_decode($_POST['bracketlist'], true) : null;

// 검색 태그 초기화
$searchtag = "$num $is_deleted $orderDate $memo $first_writer $update_log";

// 계산서 관련 콤마 제거
$unitprice = isset($unitprice) ? floatval(str_replace(',', '', $unitprice)) : '';
$amount = isset($amount) ? floatval(str_replace(',', '', $amount)) : '';
$invoiceAmount = isset($invoiceAmount) ? floatval(str_replace(',', '', $invoiceAmount)) : '';

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

if ($mode == "modify") {
    $update_log = date("Y-m-d H:i:s") . " - " . $_SESSION["name"] . " " . $update_log . "&#10";

    try {
        $pdo->beginTransaction();
        $sql = "UPDATE material_order SET 
                is_deleted=?, orderDate=?, memo=?, 
                motorlist=?, wirelessClist=?, wireClist=?, wirelessLinklist=?, wireLinklist=?, bracketlist=?,  
                update_log=?, searchtag=?, first_writer=?
                WHERE num=? LIMIT 1";

        $stmh = $pdo->prepare($sql);

        $params = [
            $is_deleted, $orderDate, $memo, 
            json_encode($motorlist_jsondata), json_encode($wirelessClist_jsondata), json_encode($wireClist_jsondata),
            json_encode($wirelessLinklist_jsondata), json_encode($wireLinklist_jsondata), json_encode($bracketlist_jsondata),
            $update_log, $searchtag, $first_writer, $num
        ];

        $stmh->execute($params);
        $pdo->commit();
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        print "오류: " . $Exception->getMessage();
    }
}

if ($mode == "insert") {
    $update_log = '';
    $first_writer = $_SESSION["name"];

    try {
        $pdo->beginTransaction();

        $sql = "INSERT INTO material_order (
            is_deleted, orderDate, memo, 
            motorlist, wirelessClist, wireClist, wirelessLinklist, wireLinklist, bracketlist, 
            update_log, searchtag, first_writer
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? 
        )";

        $stmh = $pdo->prepare($sql);

        $params = [
            $is_deleted, $orderDate, $memo,
            json_encode($motorlist_jsondata), json_encode($wirelessClist_jsondata), json_encode($wireClist_jsondata),
            json_encode($wirelessLinklist_jsondata), json_encode($wireLinklist_jsondata), json_encode($bracketlist_jsondata),
            $update_log, $searchtag, $first_writer
        ];

        $stmh->execute($params);
        $pdo->commit();
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        print "오류: " . $Exception->getMessage();
    }
}

if ($mode == "copy") {
    $sql = "select * from material_order ORDER BY num DESC";

    try {  
        $stmh = $pdo->query($sql); // 검색조건에 맞는 글 stmh    
        $row = $stmh->fetch(PDO::FETCH_ASSOC); 
        $num = $row["num"];
    } catch (PDOException $Exception) {
        print "오류: " . $Exception->getMessage();
    }
}

if ($mode == "delete") {
    try {
        $pdo->beginTransaction();  // 트랜잭션 시작
        $query = "UPDATE material_order SET is_deleted=1 WHERE num=? LIMIT 1";
        $stmh = $pdo->prepare($query);
        $params = [$num];
        $stmh->execute($params);
        $pdo->commit();  // 데이터 변경 사항을 커밋
    } catch (PDOException $Exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();  // 오류 발생 시 롤백
        }
        print "오류: " . $Exception->getMessage();
    }
}

if ($mode == "insert" || $mode == "copy") {
    try {
        $sql = "SELECT * FROM material_order ORDER BY num DESC LIMIT 1";
        $stmh = $pdo->prepare($sql);  
        $stmh->execute();                  
        $row = $stmh->fetch(PDO::FETCH_ASSOC);	 
        $num = $row["num"];	 
    } catch (PDOException $Exception) {
        error_log("오류: " . $Exception->getMessage());  // 오류 로깅
        echo "시스템 오류가 발생했습니다. 관리자에게 문의하세요.";  // 사용자 친화적 메시지
    }
}
 
$data = [   
    'num' => $num,
    'mode' => $mode
];

echo json_encode($data, JSON_UNESCAPED_UNICODE);

?>
