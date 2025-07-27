<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

$data = json_decode(file_get_contents('php://input'), true);

if (!empty($data)) {
    try {
		foreach ($data as $item) {
			$secondordnum = $item['secondordnum'];
			$customer_name = $item['customer_name'];
			$balance = $item['balance'];
			$memo = $item['memo'];
			$closure_date = $item['closure_date'];
			
			// // 한빛에스티 25일 마감 업체코드 '66'번 예외처리
			// if ($secondordnum == '66') {
				// // 기존 closure_date에서 연도와 월을 추출
				// $year = date('Y', strtotime($closure_date));
				// $month = date('m', strtotime($closure_date));
				
				// // 25일로 closure_date 강제 설정
				// $closure_date = "$year-$month-25";
			// }

			// 기존 데이터가 있는지 확인
			$checkStmt = $pdo->prepare("SELECT num FROM monthly_balances WHERE secondordnum = :secondordnum AND closure_date = :closure_date");
			$checkStmt->bindValue(':secondordnum', $secondordnum, PDO::PARAM_STR);
			$checkStmt->bindValue(':closure_date', $closure_date, PDO::PARAM_STR);
			$checkStmt->execute();
			$existingRow = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($existingRow) {
                // 기존 데이터가 있으면 업데이트
                $updateStmt = $pdo->prepare("
                    UPDATE monthly_balances
                    SET balance = :balance,
                        memo = :memo,
                        customer_name = :customer_name
                    WHERE num = :num
                ");
                $updateStmt->bindValue(':balance', $balance, PDO::PARAM_STR);
                $updateStmt->bindValue(':memo', $memo, PDO::PARAM_STR);
                $updateStmt->bindValue(':customer_name', $customer_name, PDO::PARAM_STR);
                $updateStmt->bindValue(':num', $existingRow['num'], PDO::PARAM_INT);
                $updateStmt->execute();
            } else {
                // 기존 데이터가 없으면 삽입
                $insertStmt = $pdo->prepare("
                    INSERT INTO monthly_balances (closure_date, secondordnum, customer_name, balance, memo)
                    VALUES (:closure_date, :secondordnum, :customer_name, :balance, :memo)
                ");
                $insertStmt->bindValue(':closure_date', $closure_date, PDO::PARAM_STR);
                $insertStmt->bindValue(':secondordnum', $secondordnum, PDO::PARAM_STR);
                $insertStmt->bindValue(':customer_name', $customer_name, PDO::PARAM_STR);                
                $insertStmt->bindValue(':balance', $balance, PDO::PARAM_STR);
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
