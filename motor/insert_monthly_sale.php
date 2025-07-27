<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");   
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

// 폼 데이터로 전달된 배열 받기
$modes = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : [];
$nums = isset($_REQUEST['num']) ? $_REQUEST['num'] : [];
$closure_dates = isset($_REQUEST['closure_date']) ? $_REQUEST['closure_date'] : [];
$customer_names = isset($_REQUEST['customer_name']) ? $_REQUEST['customer_name'] : [];
$sales = isset($_REQUEST['sales']) ? $_REQUEST['sales'] : [];
$invoice_issueds = isset($_REQUEST['invoice_issued']) ? $_REQUEST['invoice_issued'] : [];
$secondordnums = isset($_REQUEST['secondordnum']) ? $_REQUEST['secondordnum'] : [];
$memos = isset($_REQUEST['memo']) ? $_REQUEST['memo'] : [];

$response = [
    'status' => 'error',
    'message' => 'No data to save',
    'received_count' => count($modes)
];

if (!empty($modes) && is_array($modes)) {
    try {
        $results = [];
        for ($i = 0; $i < count($modes); $i++) {
            $mode = isset($modes[$i]) ? $modes[$i] : '';
            $num = isset($nums[$i]) ? $nums[$i] : '';
            $closure_date = isset($closure_dates[$i]) ? $closure_dates[$i] : null;
            $customer_name = isset($customer_names[$i]) ? $customer_names[$i] : null;
            $sale = isset($sales[$i]) ? $sales[$i] : null;
            $invoice_issued = isset($invoice_issueds[$i]) && ($invoice_issueds[$i] === 'true' || $invoice_issueds[$i] === '1' || $invoice_issueds[$i] === 'on') ? '발행' : '';
            $secondordnum = isset($secondordnums[$i]) ? $secondordnums[$i] : null;
            $memo = isset($memos[$i]) ? $memos[$i] : null;

            if ($mode === 'delete') {
                // 삭제 모드일 경우 is_deleted 값을 1로 업데이트
                $deleteStmt = $pdo->prepare("UPDATE monthly_sales SET is_deleted = '1' WHERE num = :num");
                $deleteStmt->bindValue(':num', $num, PDO::PARAM_INT);
                $deleteStmt->execute();
                $results[] = ['mode' => 'delete', 'num' => $num];
            } else {
                // 기존 데이터가 있는지 확인
                $checkStmt = $pdo->prepare("SELECT num FROM monthly_sales WHERE num = :num");
                $checkStmt->bindValue(':num', $num, PDO::PARAM_INT);
                $checkStmt->execute();
                $existingRow = $checkStmt->fetch(PDO::FETCH_ASSOC);

                if (!empty($existingRow)) {
                    // 기존 데이터가 있으면 업데이트
                    $updateStmt = $pdo->prepare("
                        UPDATE monthly_sales
                        SET sales = :sales, invoice_issued = :invoice_issued, memo = :memo, customer_name = :customer_name, closure_date = :closure_date 
                        WHERE num = :num
                    ");
                    $updateStmt->bindValue(':sales', $sale, PDO::PARAM_STR);
                    $updateStmt->bindValue(':invoice_issued', $invoice_issued, PDO::PARAM_STR);
                    $updateStmt->bindValue(':memo', $memo, PDO::PARAM_STR);
                    $updateStmt->bindValue(':customer_name', $customer_name, PDO::PARAM_STR);
                    $updateStmt->bindValue(':closure_date', $closure_date, PDO::PARAM_STR);
                    $updateStmt->bindValue(':num', $num, PDO::PARAM_INT);
                    $updateStmt->execute();
                    $results[] = ['mode' => 'update', 'num' => $num];
                } else {
                    // 기존 데이터가 없으면 삽입
                    $insertStmt = $pdo->prepare("
                        INSERT INTO monthly_sales (closure_date, secondordnum, customer_name, sales, invoice_issued, memo)
                        VALUES (:closure_date, :secondordnum, :customer_name, :sales, :invoice_issued, :memo)
                    ");
                    $insertStmt->bindValue(':closure_date', $closure_date, PDO::PARAM_STR);
                    $insertStmt->bindValue(':secondordnum', $secondordnum, PDO::PARAM_STR);
                    $insertStmt->bindValue(':customer_name', $customer_name, PDO::PARAM_STR);
                    $insertStmt->bindValue(':sales', $sale, PDO::PARAM_STR);
                    $insertStmt->bindValue(':invoice_issued', $invoice_issued, PDO::PARAM_STR);
                    $insertStmt->bindValue(':memo', $memo, PDO::PARAM_STR);
                    $insertStmt->execute();
                    $new_num = $pdo->lastInsertId();
                    $results[] = ['mode' => 'insert', 'num' => $new_num];
                }
            }
        }
        $response = [
            'status' => 'success',
            'results' => $results
        ];
    } catch (PDOException $Exception) {
        $response = [
            'status' => 'error',
            'message' => $Exception->getMessage()
        ];
    }
}

echo json_encode($response);
?>
