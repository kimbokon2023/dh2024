<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");   
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

try {
    $num = isset($_POST['num']) ? $_POST['num'] : '';

    $sql = "SELECT num, closure_date, balance, memo, customer_name, invoice_issued
            FROM {$DB}.monthly_balances WHERE is_deleted IS NULL ";

    if (!empty($num)) {
        $sql .= " AND num = :num";
    }

    $stmt = $pdo->prepare($sql);

    if (!empty($num)) {
        $stmt->bindValue(':num', $num, PDO::PARAM_STR);
    }

    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($results);
} catch (PDOException $Exception) {
    echo json_encode(['error' => $Exception->getMessage()]);
    exit;
}
?>
