<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    exit;
}

$year = isset($_POST['year']) ? $_POST['year'] : date('Y');
$tablename = 'motor';

// 운송방법 목록
$delivery_methods = [
    '배차' => '배차',
    '선/대신화물' => '선/대신화물',
    '착/대신화물' => '착/대신화물',
    '선/경동화물' => '선/경동화물',
    '착/경동화물' => '착/경동화물',
    '선/택배' => '선/택배',
    '착/택배' => '착/택배'
];

// 월별 데이터 저장 배열
$monthly_data = [];
$monthly_totals = array_fill(1, 12, 0);
$method_totals = [];

// 각 운송방법별로 데이터 수집
foreach ($delivery_methods as $key => $method) {
    $monthly_amounts = array_fill(1, 12, 0);
    
    for ($month = 1; $month <= 12; $month++) {
        $start_date = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
        $end_date = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . date('t', strtotime($start_date));
        
        // 배차인 경우 delipay 사용
        if ($method == '배차') {
            $sql = "SELECT SUM(CAST(REPLACE(REPLACE(delipay, ',', ''), '원', '') AS DECIMAL(15,2))) as total
                    FROM " . $DB . "." . $tablename . "
                    WHERE deliverymethod = :method
                    AND deadline BETWEEN :start_date AND :end_date
                    AND is_deleted IS NULL
                    AND delipay IS NOT NULL AND delipay != '' AND delipay != '0'";
        } 
        // 화물인 경우 cargo_delwrapamount 사용
        else if (strpos($method, '화물') !== false) {
            $sql = "SELECT SUM(CAST(REPLACE(REPLACE(cargo_delwrapamount, ',', ''), '원', '') AS DECIMAL(15,2))) as total
                    FROM " . $DB . "." . $tablename . "
                    WHERE deliverymethod = :method
                    AND deadline BETWEEN :start_date AND :end_date
                    AND is_deleted IS NULL
                    AND cargo_delwrapamount IS NOT NULL AND cargo_delwrapamount != '' AND cargo_delwrapamount != '0'";
        }
        // 택배인 경우 delwrapamount 사용
        else {
            $sql = "SELECT SUM(CAST(REPLACE(REPLACE(delwrapamount, ',', ''), '원', '') AS DECIMAL(15,2))) as total
                    FROM " . $DB . "." . $tablename . "
                    WHERE deliverymethod = :method
                    AND deadline BETWEEN :start_date AND :end_date
                    AND is_deleted IS NULL
                    AND delwrapamount IS NOT NULL AND delwrapamount != '' AND delwrapamount != '0'";
        }
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':method', $method, PDO::PARAM_STR);
            $stmt->bindParam(':start_date', $start_date, PDO::PARAM_STR);
            $stmt->bindParam(':end_date', $end_date, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $amount = $result['total'] ? floatval($result['total']) : 0;
            $monthly_amounts[$month] = $amount;
            $monthly_totals[$month] += $amount;
        } catch (PDOException $e) {
            $monthly_amounts[$month] = 0;
        }
    }
    
    $method_totals[$method] = array_sum($monthly_amounts);
    $monthly_data[$method] = $monthly_amounts;
}

// 테이블 HTML 생성
?>
<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead class="table-primary">
            <tr>
                <th class="text-center" style="width: 150px;">운송방법</th>
                <?php for ($i = 1; $i <= 12; $i++): ?>
                    <th class="text-center" style="width: 100px;"><?=$i?>월</th>
                <?php endfor; ?>
                <th class="text-center" style="width: 120px;">연간 합계</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($delivery_methods as $key => $method): ?>
                <tr class="delivery-type-header">
                    <td class="text-center"><?=$method?></td>
                    <?php for ($month = 1; $month <= 12; $month++): ?>
                        <td class="text-end">
                            <?php 
                            $amount = $monthly_data[$method][$month];
                            echo $amount > 0 ? number_format($amount) . '원' : '-';
                            ?>
                        </td>
                    <?php endfor; ?>
                    <td class="text-end bg-light">
                        <strong><?=number_format($method_totals[$method])?>원</strong>
                    </td>
                </tr>
            <?php endforeach; ?>
            
            <!-- 월별 합계 -->
            <tr class="total-row">
                <td class="text-center">월별 합계</td>
                <?php for ($month = 1; $month <= 12; $month++): ?>
                    <td class="text-end">
                        <strong><?=number_format($monthly_totals[$month])?>원</strong>
                    </td>
                <?php endfor; ?>
                <td class="text-end bg-warning">
                    <strong><?=number_format(array_sum($monthly_totals))?>원</strong>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div class="alert alert-info mt-3">
    <i class="bi bi-info-circle"></i> 
    <strong><?=$year?>년</strong> 전체 운임 통계: 
    <strong class="text-primary"><?=number_format(array_sum($monthly_totals))?>원</strong>
</div>

