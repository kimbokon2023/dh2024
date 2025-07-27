<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");

if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php");
    exit;
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/lib/mydb.php');
$pdo = db_connect();

// GET으로 전달된 차입계정 값 (bank) 받기
$bank = isset($_GET['bank']) ? trim($_GET['bank']) : '';
if ($bank === '') {
    echo "차입계정 정보가 제공되지 않았습니다.";
    exit;
}

$rows = [];

/* 1. accountLoan 테이블 (차입원금 대출 내역)  
   - accountLoan 테이블에는 bank 컬럼이 없으므로 loanAccount 컬럼으로 필터링하고,
     SELECT 결과에서 loanAccount를 bank로 alias 처리 */
$sql = "SELECT * FROM {$DB}.accountLoan WHERE bank = :bank AND is_deleted ='' ORDER BY loanStartDate ASC";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':bank', $bank);
$stmt->execute();
$loan_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// echo '<pre>';
// print_r($loan_rows);
// echo '</pre>';

foreach ($loan_rows as $row) {
    $row['type'] = 'principal';
    // 원금은 숫자형으로 변환 (콤마 제거)
    $row['loanAmount'] = floatval(str_replace(',', '', $row['loanAmount']));
    $row['loanRepayment'] = 0;
    $rows[] = $row;
}

/* 2. accountBill 테이블 (세금계산서 발행 내역, 차입금 상환 처리) */
$sql_bill = "SELECT registDate AS loanStartDate, contentSub AS bank, amount, memo 
             FROM {$DB}.accountBill 
             WHERE contentSub = :bank 
               AND is_deleted = 0 
             ORDER BY registDate ASC";
$stmt_bill = $pdo->prepare($sql_bill);
$stmt_bill->bindParam(':bank', $bank);
$stmt_bill->execute();
$bill_rows = $stmt_bill->fetchAll(PDO::FETCH_ASSOC);
foreach ($bill_rows as $bill) {
    $rows[] = [
        'loanStartDate'       => $bill['loanStartDate'],
        'bank'                => $bill['bank'],
        'loanAmount'          => 0, // 상환이므로 원금 칸은 0
        'loanRepayment'       => floatval(str_replace(',', '', $bill['amount'])),
        'content'             => '세금계산서 발행',
        'interestRate'        => '',
        'interestPaymentDate' => '',
        'maturityDate'        => null,
        'loanAccount'         => '',
        'interestAccount'     => '',
        'memo'                => $bill['memo'],
        'type'                => 'repayment'
    ];
}

/* 3. account 테이블에서 차입금상환 내역 (상환 내역) */
$sql_repayment = "SELECT registDate, content_detail, contentSub, content, memo, SUM(REPLACE(amount, ',', '')) AS totalRepayment 
                  FROM {$DB}.account 
                  WHERE content = '차입금상환' 
                    AND contentSub = :bank 
                    AND is_deleted = 0
                  GROUP BY registDate, content_detail, contentSub, content, memo
                  ORDER BY registDate ASC";
$stmt_repayment = $pdo->prepare($sql_repayment);
$stmt_repayment->bindParam(':bank', $bank);
$stmt_repayment->execute();
$repayment_rows = $stmt_repayment->fetchAll(PDO::FETCH_ASSOC);
foreach ($repayment_rows as $rep) {
    if (!empty($rep['totalRepayment'])) {
        $rows[] = [
            'loanStartDate'       => $rep['registDate'],
            'bank'                => $rep['content'], // 필요에 따라 $bank로 대체 가능
            'loanAmount'          => 0,
            'loanRepayment'       => floatval(str_replace(',', '', $rep['totalRepayment'])),
            'content'             => $rep['content_detail'],
            'interestRate'        => '',
            'interestPaymentDate' => '',
            'maturityDate'        => null,
            'loanAccount'         => '',
            'interestAccount'     => '',
            'memo'                => $rep['memo'],
            'type'                => 'repayment'
        ];
    }
}

/* 4. 모든 거래 내역을 거래일(loanStartDate) 기준 오름차순으로 정렬 */
usort($rows, function($a, $b) {
    return strtotime($a['loanStartDate']) - strtotime($b['loanStartDate']);
});

$cumulativeBalance = 0;
?>

<!-- [차입금(원금) 및 상환 내역 테이블] -->
<div class="table-responsive">
    <table class="table table-bordered table-hover text-center">
        <thead class="table-secondary">
            <tr>
                <th>번호</th>
                <th>거래일</th>
                <th>차입금(원금)</th>
                <th>은행/기업</th>
                <th>용도</th>
                <th>이자율%</th>
                <th>이자납입일</th>
                <th>상환금액</th>
                <th>누적 잔액</th>
                <th>비고</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (count($rows) > 0) {
                $i = 1;
                foreach ($rows as $row) {
                    if ($row['type'] === 'principal') {
                        $cumulativeBalance += $row['loanAmount'];
                        $displayPrincipal = number_format($row['loanAmount']);
                        $displayRepayment = "";
                    } elseif ($row['type'] === 'repayment') {
                        $repayment = $row['loanRepayment'];
                        $cumulativeBalance -= $repayment;
                        $displayPrincipal = "";
                        $displayRepayment = number_format($repayment);
                    } else {
                        $displayPrincipal = "";
                        $displayRepayment = "";
                    }
                    ?>
                    <tr>
                        <td><?= $i ?></td>
                        <td><?= htmlspecialchars($row['loanStartDate']) ?></td>
                        <td class="text-end"><?= $displayPrincipal ?></td>
                        <td><?= htmlspecialchars($row['bank']) ?></td>
                        <td><?= htmlspecialchars($row['content']) ?></td>
                        <td class="text-end"><?= htmlspecialchars($row['interestRate']) ?>%</td>
                        <td><?= htmlspecialchars($row['interestPaymentDate']) ?></td>
                        <td class="text-end"><?= $displayRepayment ?></td>
                        <td class="text-end"><?= number_format($cumulativeBalance) ?></td>
                        <td><?= htmlspecialchars($row['memo']) ?></td>
                    </tr>
                    <?php
                    $i++;
                }
            } else {
                ?>
                <tr>
                    <td colspan="10" class="text-center">해당 차입계정에 대한 상세 내역이 없습니다.</td>
                </tr>
                <?php
            }
            ?>
        </tbody>
        <tfoot>
            <tr style="background-color: #e9ecef; font-weight: bold;">
                <td colspan="8">최종 차입금 잔액</td>
                <td class="text-end text-danger"><?= number_format($cumulativeBalance) ?></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>

<?php
/* 5. account 테이블에서 이자납입 내역 조회 (내림차순 날짜별) */
$sql_interest = "SELECT registDate AS interestPaymentDate, content_detail, REPLACE(amount, ',', '') AS interestAmount, memo 
                 FROM {$DB}.account 
                 WHERE content = '이자비용' 
                   AND contentSub = :bank 
                   AND is_deleted = 0
                 ORDER BY registDate DESC";
$stmt_interest = $pdo->prepare($sql_interest);
$stmt_interest->bindParam(':bank', $bank);
$stmt_interest->execute();
$interest_rows = $stmt_interest->fetchAll(PDO::FETCH_ASSOC);

// echo '<pre>';
// print_r($interest_rows);
// echo '</pre>';

$totalInterest = 0 ;
if (count($interest_rows) > 0) {
	foreach ($interest_rows as $ir) {	
		$totalInterest += floatval($ir['interestAmount']) ;
	}
}

?>

<!-- [이자납입 내역 테이블] -->
<div class="table-responsive mt-4">
    <table class="table table-bordered table-hover text-center">
        <thead class="table-secondary">
            <tr>
                <th>번호</th>
                <th>이자납입일</th>
                <th>이자금액</th>
                <th>비고</th>
            </tr>
        </thead>
        <tbody>
			<tr style="background-color: greay;">
				<td colspan="2" class="text-center bg-light fw-bold"> 전체 합계 </td>
				<td class="text-end bg-light fw-bold"><?= number_format($totalInterest) ?></td>					
				<td class="text-center bg-light"></td>
			</tr>					
            <?php
            if (count($interest_rows) > 0) {
                $j = 1;
                foreach ($interest_rows as $ir) {
                    ?>
                    <tr>
                        <td><?= $j ?></td>
                        <td><?= htmlspecialchars($ir['interestPaymentDate']) ?></td>
                        <td class="text-end"><?= number_format(floatval($ir['interestAmount'])) ?></td>
                        <td><?= htmlspecialchars($ir['memo']) ?></td>
                    </tr>
                    <?php
                    $j++;
                }
            } else {
                ?>
                <tr>
                    <td colspan="4" class="text-center">이자납입 내역이 없습니다.</td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
</div>
