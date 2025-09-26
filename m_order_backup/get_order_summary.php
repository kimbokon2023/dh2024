<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if(!isset($_SESSION["level"]) || $_SESSION["level"]>5) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '권한이 없습니다.']);
    exit;
}

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

$tablename = 'm_order';
$fromdate = $_REQUEST['fromdate'] ?? '';
$todate = $_REQUEST['todate'] ?? '';
$orderDate = $_REQUEST['orderDate'] ?? '';
$num = $_REQUEST['num'] ?? '';

try {
    $SettingDate = " orderDate ";
    $orderby = " ORDER BY orderDate ASC, num ASC";
    
    if ($orderDate && $num) {
        // 특정 발주일자 요약
        $wherePhrase = " WHERE orderDate = '$orderDate' AND num = '$num' AND (is_deleted IS NULL OR is_deleted = '') " . $orderby;
    } else {
        // 전체 발주일자별 요약
        $common = $SettingDate . " BETWEEN '$fromdate' AND '$todate' AND (is_deleted IS NULL OR is_deleted = '') ";
        $wherePhrase = " WHERE " . $common . $orderby;
    }
    
    $sql = "SELECT * FROM " . $tablename . " " . $wherePhrase;
    $stmh = $pdo->query($sql);
    $rows = $stmh->fetchAll(PDO::FETCH_ASSOC);
    
    $html = '';
    
    if ($orderDate && $num) {
        // 특정 발주일자 상세 요약
        $html = generateSingleOrderSummary($rows);
    } else {
        // 전체 발주일자별 요약
        $html = generateAllOrderSummary($rows);
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'html' => $html]);
    
} catch (PDOException $Exception) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => '오류: ' . $Exception->getMessage()]);
}

function generateSingleOrderSummary($rows) {
    $html = '<div class="row">';
    
    foreach ($rows as $row) {
        include $_SERVER['DOCUMENT_ROOT'] . '/m_order/_row.php';
        $orderDate = $orderDate ?? '';

        // JSON 디코딩
        $decoded = json_decode($row['orderlist'], true);

        // 합계 초기화
        $totalamount = 0;
        $inputSum = 0;

        if (is_array($decoded)) {
            foreach ($decoded as $item) {
                // 발주 금액(col6) 합계
                $amt = str_replace(',', '', ($item['col6'] ?? '0'));
                $amt_float = is_numeric($amt) ? (float)$amt : 0;
                $totalamount += $amt_float;

                // 입력 항목(col23,25,27,29) 합계
                foreach (['col23','col25','col27','col29'] as $c) {
                    $v = str_replace(',', '', trim(isset($item[$c]) ? $item[$c] : '0'));
                    $v_float = is_numeric($v) ? (float)$v : 0;
                    $inputSum += $v_float;
                }
            }
        }

        // 누적입고물량대금합(CNY) 계산
        $customs_input_amount_cny1 = str_replace(',', '', ($customs_input_amount_cny1 ?? '0'));
        $customs_input_amount_cny2 = str_replace(',', '', ($customs_input_amount_cny2 ?? '0')); 
        $customs_input_amount_cny3 = str_replace(',', '', ($customs_input_amount_cny3 ?? '0'));
        $customs_input_amount_cny4 = str_replace(',', '', ($customs_input_amount_cny4 ?? '0'));

        // 숫자 체크 및 형변환
        $customs_input_amount_cny1 = is_numeric($customs_input_amount_cny1) ? (float)$customs_input_amount_cny1 : 0;
        $customs_input_amount_cny2 = is_numeric($customs_input_amount_cny2) ? (float)$customs_input_amount_cny2 : 0;
        $customs_input_amount_cny3 = is_numeric($customs_input_amount_cny3) ? (float)$customs_input_amount_cny3 : 0;
        $customs_input_amount_cny4 = is_numeric($customs_input_amount_cny4) ? (float)$customs_input_amount_cny4 : 0;

        $customs_input_amount_cny_sum = $customs_input_amount_cny1 + $customs_input_amount_cny2 + $customs_input_amount_cny3 + $customs_input_amount_cny4;

        // 차액 계산
        $difference = round($totalamount - $customs_input_amount_cny_sum, 2);
        
        $html .= '<div class="col-md-12 mb-3">';
        $html .= '<div class="card h-100">';
        $html .= '<div class="card-header bg-primary text-white">';
        $html .= '<h6 class="mb-0 text-center">발주일: ' . htmlspecialchars($orderDate) . '</h6>';
        $html .= '</div>';
        $html .= '<div class="card-body p-0">';
        $html .= '<table class="table table-sm table-bordered mb-0">';
        $html .= '<thead class="table-light">';
        $html .= '<tr>';
        $html .= '<th class="text-center" style="width: 60%;">구분</th>';
        $html .= '<th class="text-center" style="width: 40%;">금액(CNY)</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
        $html .= '<tr>';
        $html .= '<td class="text-start">발주금액(CNY)</td>';
        $html .= '<td class="text-end">' . number_format($totalamount, 2) . '</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td class="text-start">누적입고물량대금합(CNY)</td>';
        $html .= '<td class="text-end">' . number_format($customs_input_amount_cny_sum, 2) . '</td>';
        $html .= '</tr>';
        $html .= '<tr class="' . ($difference > 0 ? 'table-warning' : 'table-success') . '">';
        $html .= '<td class="text-start fw-bold">차액</td>';
        $html .= '<td class="text-end fw-bold">' . number_format($difference, 2) . '</td>';
        $html .= '</tr>';
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
    }
    
    $html .= '</div>';
    return $html;
}

function generateAllOrderSummary($rows) {
    $html = '<div class="row">';
    
    foreach ($rows as $row) {
        include $_SERVER['DOCUMENT_ROOT'] . '/m_order/_row.php';
        $orderDate = $orderDate ?? '';

        // JSON 디코딩
        $decoded = json_decode($row['orderlist'], true);

        // 합계 초기화
        $totalamount = 0;
        $inputSum = 0;

        if (is_array($decoded)) {
            foreach ($decoded as $item) {
                // 발주 금액(col6) 합계
                $amt = str_replace(',', '', ($item['col6'] ?? '0'));
                $amt_float = is_numeric($amt) ? (float)$amt : 0;
                $totalamount += $amt_float;

                // 입력 항목(col23,25,27,29) 합계
                foreach (['col23','col25','col27','col29'] as $c) {
                    $v = str_replace(',', '', trim(isset($item[$c]) ? $item[$c] : '0'));
                    $v_float = is_numeric($v) ? (float)$v : 0;
                    $inputSum += $v_float;
                }
            }
        }

        // 누적입고물량대금합(CNY) 계산
        $customs_input_amount_cny1 = str_replace(',', '', ($customs_input_amount_cny1 ?? '0'));
        $customs_input_amount_cny2 = str_replace(',', '', ($customs_input_amount_cny2 ?? '0')); 
        $customs_input_amount_cny3 = str_replace(',', '', ($customs_input_amount_cny3 ?? '0'));
        $customs_input_amount_cny4 = str_replace(',', '', ($customs_input_amount_cny4 ?? '0'));

        // 숫자 체크 및 형변환
        $customs_input_amount_cny1 = is_numeric($customs_input_amount_cny1) ? (float)$customs_input_amount_cny1 : 0;
        $customs_input_amount_cny2 = is_numeric($customs_input_amount_cny2) ? (float)$customs_input_amount_cny2 : 0;
        $customs_input_amount_cny3 = is_numeric($customs_input_amount_cny3) ? (float)$customs_input_amount_cny3 : 0;
        $customs_input_amount_cny4 = is_numeric($customs_input_amount_cny4) ? (float)$customs_input_amount_cny4 : 0;

        $customs_input_amount_cny_sum = $customs_input_amount_cny1 + $customs_input_amount_cny2 + $customs_input_amount_cny3 + $customs_input_amount_cny4;

        // 차액 계산
        $difference = round($totalamount - $customs_input_amount_cny_sum, 2);
        
        $html .= '<div class="col-md-4 mb-3">';
        $html .= '<div class="card h-100">';
        $html .= '<div class="card-header bg-info text-white">';
        $html .= '<h6 class="mb-0 text-center">발주일: ' . htmlspecialchars($orderDate) . '</h6>';
        $html .= '</div>';
        $html .= '<div class="card-body p-0">';
        $html .= '<table class="table table-sm table-bordered mb-0">';
        $html .= '<thead class="table-light">';
        $html .= '<tr>';
        $html .= '<th class="text-center" style="width: 60%;">구분</th>';
        $html .= '<th class="text-center" style="width: 40%;">금액(CNY)</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
        $html .= '<tr>';
        $html .= '<td class="text-start">발주금액(CNY)</td>';
        $html .= '<td class="text-end">' . number_format($totalamount, 2) . '</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td class="text-start">누적입고물량대금합(CNY)</td>';
        $html .= '<td class="text-end">' . number_format($customs_input_amount_cny_sum, 2) . '</td>';
        $html .= '</tr>';
        $html .= '<tr class="' . ($difference > 0 ? 'table-warning' : 'table-success') . '">';
        $html .= '<td class="text-start fw-bold">차액</td>';
        $html .= '<td class="text-end fw-bold">' . number_format($difference, 2) . '</td>';
        $html .= '</tr>';
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
    }
    
    $html .= '</div>';
    return $html;
}
?>
