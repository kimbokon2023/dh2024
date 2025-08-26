<?php

// Bankbook options
$bankbookOptions = [];
$jsonFile = $_SERVER['DOCUMENT_ROOT'] . "/account/accountlist.json";
$accounts = [];
$selectedAccount = null;
$accountBalances = [];

$tablename_account = 'account';

if (file_exists($jsonFile)) {
    $jsonContent = file_get_contents($jsonFile);
    $accounts = json_decode($jsonContent, true);
    if (is_array($accounts) && !empty($accounts)) {
        // 선택된 계좌 또는 기본 계좌(첫 번째) 설정
        $selectedAccountIndex = isset($_REQUEST['selected_account']) ? intval($_REQUEST['selected_account']) : 0;
        $selectedAccount = $accounts[$selectedAccountIndex] ?? $accounts[0];
        
        // 각 계좌별 잔액 계산
        foreach ($accounts as $index => $account) {
            $accountDisplay = $account['company'] . ' ' . $account['number'];
            // if (!empty($account['memo'])) {
            //     $accountDisplay .= ' (' . $account['memo'] . ')';
            // }
            
            // 해당 계좌의 잔액 계산
            $accountBalanceSql = "SELECT 
                SUM(CASE WHEN inoutsep = '수입' AND bankbook = ? THEN REPLACE(amount, ',', '') ELSE 0 END) +
                SUM(CASE WHEN inoutsep = '최초전월이월' AND bankbook = ? THEN REPLACE(amount, ',', '') ELSE 0 END) -
                SUM(CASE WHEN inoutsep = '지출' AND bankbook = ? THEN REPLACE(amount, ',', '') ELSE 0 END) AS balance
                FROM $tablename_account 
                WHERE is_deleted = '0' ";
            $accountBalanceStmh = $pdo->prepare($accountBalanceSql);
            $accountBalanceStmh->bindValue(1, $accountDisplay, PDO::PARAM_STR);
            $accountBalanceStmh->bindValue(2, $accountDisplay, PDO::PARAM_STR);
            $accountBalanceStmh->bindValue(3, $accountDisplay, PDO::PARAM_STR);
            $accountBalanceStmh->execute();
            $accountBalances[$index] = $accountBalanceStmh->fetch(PDO::FETCH_ASSOC)['balance'] ?? 0;
        }
    }
}

// echo '<pre>';
// print_r($jsonContent);
// echo '</pre>';

// 각 계좌별 요약 정보를 담을 배열 초기화
$accountSummaries = [];
if (is_array($accounts)) {
    foreach ($accounts as $index => $account) {
        $accountDisplay = $account['company'] . ' ' . $account['number'];
        if (!empty($account['memo'])) {
            $accountDisplay .= ' (' . $account['memo'] . ')';
        }

        // 1. 계좌별 기초 잔액 (기간 이전)
        $initialSql = "SELECT 
            (SUM(CASE WHEN inoutsep = '수입' OR inoutsep = '최초전월이월' THEN REPLACE(amount, ',', '') ELSE 0 END) -
             SUM(CASE WHEN inoutsep = '지출' THEN REPLACE(amount, ',', '') ELSE 0 END)) AS balance
            FROM $tablename_account 
            WHERE is_deleted = '0' AND registDate < :fromdate AND bankbook = :bankbook ";
        $initialStmh = $pdo->prepare($initialSql);
        $initialStmh->bindParam(':fromdate', $fromdate);
        $initialStmh->bindParam(':bankbook', $accountDisplay);
        $initialStmh->execute();
        $initialBalanceAccount = $initialStmh->fetch(PDO::FETCH_ASSOC)['balance'] ?? 0;

        // 2. 기간 내 수입
        $incomeSql = "SELECT SUM(REPLACE(amount, ',', '')) AS totalIncome 
            FROM $tablename_account 
            WHERE is_deleted = '0' AND (inoutsep = '수입' OR inoutsep = '최초전월이월')
            AND registDate BETWEEN :fromdate AND :todate AND bankbook = :bankbook ";
        $incomeStmh = $pdo->prepare($incomeSql);
        $incomeStmh->bindParam(':fromdate', $fromdate);
        $incomeStmh->bindParam(':todate', $todate);
        $incomeStmh->bindParam(':bankbook', $accountDisplay);
        $incomeStmh->execute();
        $totalIncomeAccount = $incomeStmh->fetch(PDO::FETCH_ASSOC)['totalIncome'] ?? 0;

        // 3. 기간 내 지출
        $expenseSql = "SELECT SUM(REPLACE(amount, ',', '')) AS totalExpense 
            FROM $tablename_account 
            WHERE is_deleted = '0' AND inoutsep = '지출' 
            AND registDate BETWEEN :fromdate AND :todate AND bankbook = :bankbook ";
        $expenseStmh = $pdo->prepare($expenseSql);
        $expenseStmh->bindParam(':fromdate', $fromdate);
        $expenseStmh->bindParam(':todate', $todate);
        $expenseStmh->bindParam(':bankbook', $accountDisplay);
        $expenseStmh->execute();
        $totalExpenseAccount = $expenseStmh->fetch(PDO::FETCH_ASSOC)['totalExpense'] ?? 0;

        // 4. 최종 잔액
        $finalBalanceAccount = $initialBalanceAccount + $totalIncomeAccount - $totalExpenseAccount;

        // 계산된 정보를 배열에 저장
        $accountSummaries[$index] = [
            'name' => $accountDisplay,
            'income' => $totalIncomeAccount,
            'expense' => $totalExpenseAccount,
            'balance' => $finalBalanceAccount
        ];
    }
}

// 각 계좌별 최종 잔액 정보를 담을 배열 초기화
$accountFinalBalances = [];
if (is_array($accounts)) {
    foreach ($accounts as $index => $account) {
        $accountDisplay = $account['company'] . ' ' . $account['number'];
        if (!empty($account['memo'])) {
            $accountDisplay .= ' (' . $account['memo'] . ')';
        }

        // 계좌별 전체 기간의 최종 잔액 계산
        $balanceSql = "SELECT 
            (SUM(CASE WHEN inoutsep = '수입' OR inoutsep = '최초전월이월' THEN REPLACE(amount, ',', '') ELSE 0 END) -
             SUM(CASE WHEN inoutsep = '지출' THEN REPLACE(amount, ',', '') ELSE 0 END)) AS balance
            FROM $tablename_account 
            WHERE is_deleted = '0' AND bankbook = :bankbook ";
        
        $balanceStmh = $pdo->prepare($balanceSql);
        $balanceStmh->bindParam(':bankbook', $accountDisplay);
        $balanceStmh->execute();
        $balanceResult = $balanceStmh->fetch(PDO::FETCH_ASSOC);

        // 계산된 정보를 배열에 저장
        $accountFinalBalances[$index] = [
            'name' => $accountDisplay,
            'balance' => $balanceResult['balance'] ?? 0
        ];
    }
}
?>