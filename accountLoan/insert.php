<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");

$tablename = 'accountLoan';  // 테이블명 고정
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';

header("Content-Type: application/json");  // JSON 형식 응답

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

// 입력값 받기
include "_request.php";

if ($mode == "update") {
    try {
        $pdo->beginTransaction();
        $update_log = date("Y-m-d H:i:s") . " - " . $_SESSION["name"] . " 수정됨\n";
        
        $sql = "UPDATE " . $tablename . " SET 
                    loanStartDate = ?, bank = ?, loanAmount = ?, content = ?, 
                    interestRate = ?, interestPaymentDate = ?, memo = ?, 
                    crew = ?, crewphone = ?, loanAccount = ?, interestAccount = ?, 
                    maturityDate = ?, is_deleted = ? 
                WHERE num = ? LIMIT 1";

        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $loanStartDate, PDO::PARAM_STR);
        $stmh->bindValue(2, $bank, PDO::PARAM_STR);
        $stmh->bindValue(3, $loanAmount, PDO::PARAM_STR);
        $stmh->bindValue(4, $content, PDO::PARAM_STR);
        $stmh->bindValue(5, $interestRate, PDO::PARAM_STR);
        $stmh->bindValue(6, $interestPaymentDate, PDO::PARAM_STR);
        $stmh->bindValue(7, $memo, PDO::PARAM_STR);
        $stmh->bindValue(8, $crew, PDO::PARAM_STR);
        $stmh->bindValue(9, $crewphone, PDO::PARAM_STR);
        $stmh->bindValue(10, $loanAccount, PDO::PARAM_STR);
        $stmh->bindValue(11, $interestAccount, PDO::PARAM_STR);
        $stmh->bindValue(12, $maturityDate, PDO::PARAM_STR);
        $stmh->bindValue(13, $is_deleted, PDO::PARAM_STR);
        $stmh->bindValue(14, $num, PDO::PARAM_INT);

        $stmh->execute();
        $pdo->commit();
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        print "오류: " . $Exception->getMessage();
    }
}

if ($mode == "insert" || empty($mode)) {
    try {
        $pdo->beginTransaction();
        $first_writer = date("Y-m-d H:i:s") . " - " . $_SESSION["name"];
        $update_log = date("Y-m-d H:i:s") . " - " . $_SESSION["name"] . " 등록됨\n";

        $sql = "INSERT INTO " . $tablename . " (
                    loanStartDate, bank, loanAmount, content, 
                    interestRate, interestPaymentDate, memo, 
                    crew, crewphone, loanAccount, interestAccount, 
                    maturityDate, is_deleted
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $loanStartDate, PDO::PARAM_STR);
        $stmh->bindValue(2, $bank, PDO::PARAM_STR);
        $stmh->bindValue(3, $loanAmount, PDO::PARAM_STR);
        $stmh->bindValue(4, $content, PDO::PARAM_STR);
        $stmh->bindValue(5, $interestRate, PDO::PARAM_STR);
        $stmh->bindValue(6, $interestPaymentDate, PDO::PARAM_STR);
        $stmh->bindValue(7, $memo, PDO::PARAM_STR);
        $stmh->bindValue(8, $crew, PDO::PARAM_STR);		
        $stmh->bindValue(9, $crewphone, PDO::PARAM_STR);
        $stmh->bindValue(10, $loanAccount, PDO::PARAM_STR);
        $stmh->bindValue(11, $interestAccount, PDO::PARAM_STR);
        $stmh->bindValue(12, $maturityDate, PDO::PARAM_STR);
        $stmh->bindValue(13, $is_deleted, PDO::PARAM_STR);

        $stmh->execute();
        $pdo->commit();
    } catch (PDOException $Exception) {
        $pdo->rollBack();
        print "오류: " . $Exception->getMessage();
    }
}

if ($mode == "delete") {
    try {
        $pdo->beginTransaction();
        $sql = "UPDATE " . $tablename . " SET is_deleted = 'Y' WHERE num = ?";
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $num, PDO::PARAM_INT);
        $stmh->execute();
        $pdo->commit();
    } catch (PDOException $ex) {
        $pdo->rollBack();
        print "오류: " . $ex->getMessage();
    }
}

// JSON 응답 반환
$data = [
    'num' => $num,
    'mode' => $mode
];

echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>
