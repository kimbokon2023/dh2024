<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

$data = json_decode(file_get_contents('php://input'), true);

if (!empty($data)) {
    try {
        foreach ($data as $item) {
            $mode = $item['mode'];
            $num = $item['num'];
            $closure_date = $item['closure_date'];
            $customer_name = $item['customer_name'];
            $balance = $item['balance'];
            $invoice_issued = $item['invoice_issued'] ? '발행' : ''; // BOOLEAN 값을 VARCHAR로 변환
            $memo = $item['memo'];

            // 기존 데이터가 있는지 확인
            $checkStmt = $pdo->prepare("SELECT num FROM monthly_balances WHERE num = :num");
            $checkStmt->bindValue(':num', $num, PDO::PARAM_INT);
            $checkStmt->execute();
            $existingRow = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($mode!='insert') {
                // 기존 데이터가 있으면 업데이트
                $updateStmt = $pdo->prepare("
                    UPDATE monthly_balances
                    SET balance = :balance, invoice_issued = :invoice_issued, memo = :memo, customer_name = :customer_name, closure_date = :closure_date
                    WHERE num = :num
                ");
                $updateStmt->bindValue(':balance', $balance, PDO::PARAM_STR);
                $updateStmt->bindValue(':invoice_issued', $invoice_issued, PDO::PARAM_STR);
                $updateStmt->bindValue(':memo', $memo, PDO::PARAM_STR);
                $updateStmt->bindValue(':customer_name', $customer_name, PDO::PARAM_STR);
                $updateStmt->bindValue(':closure_date', $closure_date, PDO::PARAM_STR);
                $updateStmt->bindValue(':num', $num, PDO::PARAM_INT);
                $updateStmt->execute();
            } else {
                // 기존 데이터가 없으면 삽입
                $insertStmt = $pdo->prepare("
                    INSERT INTO monthly_balances (closure_date, secondordnum, customer_name, balance, invoice_issued, memo)
                    VALUES (:closure_date, :secondordnum, :customer_name, :balance, :invoice_issued, :memo)
                ");
                $insertStmt->bindValue(':closure_date', $closure_date, PDO::PARAM_STR);
                $insertStmt->bindValue(':secondordnum', $item['secondordnum'], PDO::PARAM_STR);
                $insertStmt->bindValue(':customer_name', $customer_name, PDO::PARAM_STR);
                $insertStmt->bindValue(':balance', $balance, PDO::PARAM_STR);
                $insertStmt->bindValue(':invoice_issued', $invoice_issued, PDO::PARAM_STR);
                $insertStmt->bindValue(':memo', $memo, PDO::PARAM_STR);
                $insertStmt->execute();
            }
        }

        echo json_encode(['status' => 'success', 'data' => $data]);
    } catch (PDOException $Exception) {
        echo json_encode(['status' => 'error', 'message' => $Exception->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No data to save']);
}
?>
