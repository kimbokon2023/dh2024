<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

// 첫 화면 표시 문구
$title_message = '거래처 원장'; 
$tablename = 'motor'; 

// 견적서, 거래명세서, 총거래원장등 설정 $item으로 설정하면 됨.
$item_title ='거래처 원장';

include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';   
?>
<title> <?= $title_message ?> </title>
<style>
table, th, td {
	border: 1px solid black !important; /* Bold border */
	font-size: 12px !important;
	white-space: nowrap;
}  
/* Add background color for date rows */
.date-row {
	background-color: #f0f0f0!important; /* Light gray background */        
}   
.date-row-date {
	background-color: #f0f0f0!important; /* Light gray background */        
	color: blue !important;
}    

@media print {
	body {
		width: 210mm; /* A4 width */
		height: 297mm; /* A4 height */
		margin: 0; /* Remove default margin */
		font-size: 10pt; /* Font size for printing */
	}
	.table {
		width: 100%; /* Full width tables */
		table-layout: fixed; /* Uniform column sizing */
		border-collapse: collapse; /* Ensure borders collapse */
	}
	.table th, .table td {
		padding: 1px; /* Reduce padding */
		border: 1px solid #ddd; /* Ensure borders are visible */
	}
	.text-center {
		text-align: center; /* Maintain center alignment */
	}

	/* Prevent table row splitting */
	.table tr {
		page-break-inside: avoid; /* Prevent breaking inside rows */
		page-break-after: avoid; /* Allow breaking after rows */
	}
	.table thead {
		display: table-header-group; /* Ensure table headers are repeated */
	}
	.table tbody {
		display: table-row-group; /* Ensure table rows are grouped */
	}
	.table tfoot {
		display: table-footer-group; /* Ensure table footers are repeated */
	}

	/* Add top and bottom margins to each page */
	.table tbody:before,
	.table tbody:after {
		content: "";
		display: table-row;
		height: 5mm; /* Adjust as needed for top and bottom margins */
	}

	/* Remove border from the before and after elements */
	.table tbody:before td,
	.table tbody:after td {
		border: none; /* Remove borders */
	}

	/* Adjust the border of the last row on the page */
	.table tbody tr:last-child td {
		border-bottom: none; /* Remove the bottom border */
		border-left: none; /* Remove the bottom border */
		border-right: none; /* Remove the bottom border */
		border-top: none; /* Remove the bottom border */
	}

	/* Prevent border at the connection of two pages */
	.table tbody tr:last-child td:first-child {
		border-bottom: none; /* Remove the bottom border */
		border-left: none; /* Remove the bottom border */
		border-right: none; /* Remove the bottom border */
		border-top: none; /* Remove the bottom border */
	}
	.table tbody tr:last-child td:last-child {
		border-bottom: none; /* Remove the bottom border */
		border-left: none; /* Remove the bottom border */
		border-right: none; /* Remove the bottom border */
		border-top: none; /* Remove the bottom border */
	}
}
</style>
</head>
<body>
<?php

$num = isset($_REQUEST['num']) ? $_REQUEST['num'] : '';  
$fromdate = isset($_REQUEST['fromdate']) ? $_REQUEST['fromdate'] : '';  
$todate = isset($_REQUEST['todate']) ? $_REQUEST['todate'] : '';  
$book_issued = isset($_REQUEST['book_issued']) ? $_REQUEST['book_issued'] : '';
$uniqueNum = isset($_REQUEST['uniqueNum']) ? $_REQUEST['uniqueNum'] : ''; // 판매일괄 회계반영 고유번호
$secondordnum = $num;

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

try {
    // Fetch customer details from phonebook
    $sql = "SELECT * FROM " . $DB . ".phonebook WHERE secondordnum = ?";
    $stmh = $pdo->prepare($sql);
    $stmh->bindValue(1, $num, PDO::PARAM_STR);
    $stmh->execute();
    $customer = $stmh->fetch(PDO::FETCH_ASSOC);
    
    if (!$customer) {
        throw new Exception("거래처 정보를 찾을 수 없습니다.");
    }

    // Fetch sales details from motor table
    $sql = "SELECT * FROM " . $DB . ".motor WHERE secondordnum = ? AND deadline BETWEEN ? AND ? AND (is_deleted IS NULL OR is_deleted = 0) ORDER BY deadline ASC";
    $stmh = $pdo->prepare($sql);
    $stmh->bindValue(1, $num, PDO::PARAM_STR);
    $stmh->bindValue(2, $fromdate, PDO::PARAM_STR);
    $stmh->bindValue(3, $todate, PDO::PARAM_STR);
    $stmh->execute();
    $rows = $stmh->fetchAll(PDO::FETCH_ASSOC);


    // Fetch payments from account table
    $sql = "SELECT registDate, amount FROM " . $DB . ".account 
        WHERE secondordnum = ? 
        AND registDate BETWEEN ? AND ? 
        AND (is_deleted IS NULL OR is_deleted = 0)
        AND content = '거래처 수금' 
        ORDER BY registDate ASC";
	
    $stmh = $pdo->prepare($sql);
    $stmh->bindValue(1, $num, PDO::PARAM_STR);
    $stmh->bindValue(2, $fromdate, PDO::PARAM_STR);
    $stmh->bindValue(3, $todate, PDO::PARAM_STR);
    $stmh->execute();
    $payments = $stmh->fetchAll(PDO::FETCH_ASSOC);
	    
    // 다른 거래처는 기본적으로 마감 기준일 -1일
    $lastMonthEnd = date("Y-m-t", strtotime($fromdate . " -1 days"));
       		
    // echo '<pre>';
    // print_r($lastMonthEnd);
    // echo '</pre>';

    // Calculate initial balance (before the start of the period)
    $salesBeforeSql = "
        SELECT SUM(CAST(REPLACE(totalprice, ',', '') AS SIGNED)) AS total_sales 
        FROM " . $DB . ".motor
        WHERE secondordnum = :secondordnum AND deadline <= :lastMonthEnd AND (is_deleted IS NULL OR is_deleted = 0)
    ";
    $paymentBeforeSql = "
        SELECT SUM(CAST(REPLACE(amount, ',', '') AS SIGNED)) AS total_payment 
        FROM " . $DB . ".account
        WHERE secondordnum = :secondordnum AND registDate <= :lastMonthEnd AND (is_deleted IS NULL OR is_deleted = 0) AND content = '거래처 수금'
    ";

    $salesBeforeStmt = $pdo->prepare($salesBeforeSql);
    $salesBeforeStmt->execute([':secondordnum' => $secondordnum, ':lastMonthEnd' => $lastMonthEnd]);
    $salesBeforeData = $salesBeforeStmt->fetch(PDO::FETCH_ASSOC);

    $paymentBeforeStmt = $pdo->prepare($paymentBeforeSql);
    $paymentBeforeStmt->execute([':secondordnum' => $secondordnum, ':lastMonthEnd' => $lastMonthEnd]);
    $paymentBeforeData = $paymentBeforeStmt->fetch(PDO::FETCH_ASSOC);

    $initialSales = isset($salesBeforeData['total_sales']) ? (float)$salesBeforeData['total_sales'] : 0;
    $initialPayments = isset($paymentBeforeData['total_payment']) ? (float)$paymentBeforeData['total_payment'] : 0;

    // Calculate the initial balance 
    $initialBalance = intval(round($initialSales * 1.1, 2) - round($initialPayments));
	
	// 마지막 자릿수가 1인지 확인
	if (floatval($initialBalance) % 10 === 1) {
		// 마지막 자릿수를 제거 (정수로 처리)
		$initialBalance = floor($initialBalance / 10) * 10;
	}	

} catch (Exception $e) {
    echo "오류: " . $e->getMessage();
}

?>

<div class="container mt-2">
    <div class="d-flex align-items-center justify-content-end mt-1 m-2">        
		<i class="bi bi-info-circle-fill"></i> <?=$secondordnum?> &nbsp;
        <button type="button" class="btn btn-dark btn-sm me-1" onclick="location.reload();"> <i class="bi bi-arrow-clockwise"></i> </button> 
        <button class="btn btn-dark btn-sm me-1" onclick="generatePDF()"> PDF 저장 </button>
        <button class="btn btn-dark btn-sm me-1" onclick="exportTableToExcel()"> Excel 저장 </button>
        <button class="btn btn-dark btn-sm me-1" onclick="sendmail();"> <i class="bi bi-envelope-arrow-up"></i> 전송 </button>
        <button class="btn btn-info btn-sm me-1" onclick="checkUploadLimits()"> <i class="bi bi-info-circle"></i> S </button>        
        <button class="btn btn-secondary btn-sm" onclick="self.close();"> <i class="bi bi-x-lg"></i> 닫기 </button>&nbsp;
    </div>
    <!-- 거래원장 발행 여부 'write' 일때 거래원장 발행 버튼 표시 -->    
    <input type="hidden" id="book_issued" name="book_issued" value="<?= $book_issued ?>">    
    <!-- 판매일괄 회계반영 고유번호 -->      
    <input type="hidden" id="uniqueNum" name="uniqueNum" value="<?= $uniqueNum ?>">    
</div>
 <div id="content-to-print">        
    <div class="container" >        
            <div class="d-flex align-items-center justify-content-center mb-3">
                <h2><?= $customer['vendor_name'] ?> 관리대장</h2> 
                <h5>(거래명세서별)</h5>
            </div>
            <div class="row align-items-center justify-content-center mb-1 mt-2">
                <div class="col-sm-6 text-start">
                    <?php
                    // 저장된 담당자 정보 불러오기
                    $chargePerson = '최정인 과장'; // 기본값
                    $accountChargedFile = __DIR__ . '/accountCharged.txt';
                    if (file_exists($accountChargedFile)) {
                        $savedChargePerson = trim(file_get_contents($accountChargedFile));
                        if (!empty($savedChargePerson)) {
                            $chargePerson = $savedChargePerson;
                        }
                    }
                    ?>
                    회사명 : 주식회사 대한 / 담당 : <?= $chargePerson ?>
                </div>
                <div class="col-sm-6 text-end">
                    <?= $fromdate ?> ~ <?= $todate ?>
                </div>
            </div>

        <div class="d-flex align-items-center justify-content-center ">
                <table class="table" id="myTable1" style="border-collapse: collapse;">
                    <thead>
                        <tr>             
                            <th class="text-start fw-bold" style="width:20%;">사업자등록번호</th>
                            <th class="text-start fw-bold text-primary" style="width:30%;"><?= $customer['vendor_code'] ?></th>            
                            <th class="text-start fw-bold">대표자</th>
                            <th class="text-start"><?= $customer['representative_name'] ?></th>
                        </tr>
                        <tr>
                            <th class="text-start fw-bold">여신한도</th>
                            <th class="text-start">0</th>
                            <th class="text-start fw-bold">전화</th>
                            <th class="text-start"><?= $customer['phone'] ?> (모바일: <?= $customer['mobile'] ?>)</th>                
                        </tr>
                        <tr>
                            <th class="text-start fw-bold">Email</th>
                            <th class="text-start"><?= $customer['email'] ?></th>
                            <th class="text-start fw-bold">Fax</th>
                            <th class="text-start"><?= $customer['fax'] ?></th>
                        </tr>
                        <tr>                
                            <th class="text-start fw-bold">주소</th>
                            <th colspan="3" class="text-start"><?= $customer['address'] ?></th>                
                        </tr>
                        <tr>
                            <th class="text-start fw-bold">적요</th>
                            <th colspan="3" class="text-start"></th>                
                        </tr>
                    </thead>
                </table>
        </div>

        <div class="d-flex align-items-center justify-content-center ">
            <table class="table" id="myTable2" style="border-collapse: collapse;">
                <thead>
                    <tr>
                        <th colspan="5" class="text-center">판매/수금내역</th>
                    </tr>
                    <tr>
                        <th class="text-center">일자</th>
                        <th class="text-center">적요</th>
                        <th class="text-center">판매</th>
                        <th class="text-center">수금</th>
                        <th class="text-center">잔액</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="2" class="text-center  fw-bold text-primary">이월잔액</td>
                        <td class="text-center"></td>
                        <td class="text-center"></td>
                        <td class="text-end fw-bold text-primary"><?= number_format($initialBalance) ?></td>
                    </tr>
                    <?php
                    $total_balance = $initialBalance; // 초기 잔액 설정
                    $monthly_sales = [];
                    $current_month = '';
                    $grand_total = 0;
                    $sale_count = 0;
                    $total_payment_sum = 0; // 수금 합계 초기화

                    // Merge sales and payments into one array
                    $events = [];

                    foreach ($rows as $row) {
                        $events[] = [
                            'date' => $row['deadline'],
                            'type' => 'sale',
                            'data' => $row
                        ];
                    }
					// 수금부분
					foreach ($payments as $payment) {
						$events[] = [
							'date' => $payment['registDate'],  // 올바른 키 이름 사용
							'type' => 'payment',
							'data' => [
								'amount' => $payment['amount']  // 올바른 키 이름 사용
							]
						];
					}

                    // Sort events by date
                    usort($events, function($a, $b) {
                        return strcmp($a['date'], $b['date']);
                    });

                // 출고예정일 첫번째 요소 저장변수
                $Last_deadline = '';                
                 foreach ($events as $event) {
                        $event_date = $event['date'];
                        $event_type = $event['type'];
                        $month = date('Y/m', strtotime($event_date));                        

					if ($event_type == 'payment') {
						$payment = $event['data'];
						$payment_date = $event_date; // 날짜는 이벤트의 날짜를 사용
						$payment_amount = (float)str_replace(',', '', $payment['amount']); // payment 대신 amount 키 사용
						$total_balance -= $payment_amount;
						$total_payment_sum += $payment_amount; // 수금 합계에 추가

						if (!isset($monthly_sales[$month])) {
							$monthly_sales[$month] = 0;
						}

                            echo "<tr class='date-row'>
                                <td class='date-row-date text-start'>{$payment_date}</td>
                                <td class='date-row text-start'>입금</td>
                                <td class='date-row text-end'></td>
                                <td class='date-row text-end'>" . number_format($payment_amount) . "</td>
                                <td class='date-row text-end'>" . number_format($total_balance) . "</td>
                            </tr>";
                        }

                        if ($current_month !== $month) {
                            if ($current_month !== '') {
                                echo "<tr>
                                    <td colspan='2' class='text-center fw-bold'>{$current_month} 계</td>
                                    <td class='text-end'>" . number_format($monthly_sales[$current_month]) . "</td>
                                    <td class='text-start'></td>
                                    <td class='text-start'></td>
                                </tr>";
                            }
                            $current_month = $month;
                        }

                    if ($event_type == 'sale') {
                        $row = $event['data'];
                        $deadline = $row['deadline'];
                        // 마지막데이터가 저장됨
                        $Last_deadline = $deadline ;
                            
                        $workplacename = $row['workplacename'];
                        $amount = (float)str_replace(',', '', trim($row['totalprice'])) * 1.1; // 숫자 외 문자를 제거하고 float로 변환                    
                        $total_balance += round($amount) ;            
                        $sale_count++;

                        if (!isset($monthly_sales[$month])) {
                            $monthly_sales[$month] = 0;
                        }
                        $monthly_sales[$month] += $amount;
                        $grand_total += $amount;

                        echo "<tr class='date-row' onclick=\"redirectToView('{$row['num']}', '{$tablename}')\">
                            <td class='date-row-date text-start'>{$deadline}</td>
                            <td class='date-row text-start'>{$workplacename}</td>
                            <td class='date-row text-end'>" . number_format($amount) . "</td>
                            <td class='date-row text-start'></td>
                            <td class='date-row text-end'>" . number_format($total_balance) . "</td>
                        </tr>";

                            $orderlist = json_decode($row['orderlist'], true);
                            if (is_array($orderlist)) {
                                foreach ($orderlist as $item) {
                                    if (isset($item['col1']) && isset($item['col12'])) {
                                        // 쉼표를 제거한 후 숫자로 변환
                                        $col12 = floatval(str_replace(',', '', $item['col12']));
                                        $col8 = floatval(str_replace(',', '', $item['col8']));

										// 단가 계산 (0 나누기 방지)
										$unitprice = ($col8 != 0) ? number_format($col12 / $col8) : "0";


                                        $item_name = $item['col1'] . '-' . $item['col2'] . '-' . $item['col3'] . ' ' . $item['col4'] . ' ' . $item['col5'] . ' ' . $item['col6'] . ' ' . $item['col7'] . ' / ' . $item['col8'] . ' * ' . $unitprice;
                                        $item_amount = (float)trim(str_replace(',', '', trim($item['col12']))) * 1.1;
                                        echo "<tr>
                                            <td class='text-start'></td>
                                            <td class='text-start'>{$item_name}</td>
                                            <td class='text-end'>" . number_format($item_amount) . "</td>
                                            <td class='text-start'></td>
                                            <td class='text-start'></td>
                                        </tr>";
                                    }
                                }
                            }

                            $controllerlist = json_decode($row['controllerlist'], true);
                            if (is_array($controllerlist)) {
                                foreach ($controllerlist as $item) {
                                    if (isset($item['col2']) && isset($item['col3'])) {
                                        // 쉼표를 제거한 후 숫자로 변환
                                        $col7 = floatval(str_replace(',', '', $item['col7']));
                                        $col3 = floatval(str_replace(',', '', $item['col3']));

                                        // 단가 계산
                                        $unitprice = number_format($col7 / $col3);                                        
                                        $item_name = $item['col2'] . ' / ' . $item['col3'] . ' * ' . $unitprice;
                                        $item_amount = (float)str_replace(',', '', trim($item['col7'])) * 1.1;
                                        echo "<tr>
                                            <td class='text-start'></td>
                                            <td class='text-start'>{$item_name}</td>
                                            <td class='text-end'>" . number_format($item_amount) . "</td>
                                            <td class='text-start'></td>
                                            <td class='text-start'></td>
                                        </tr>";
                                    }
                                }
                            }
                            // 원단 내용 추가
                            $fabriclist = json_decode($row['fabriclist'], true);
                            if (is_array($fabriclist)) {
                                foreach ($fabriclist as $item) {
                                    if (isset($item['col1']) && isset($item['col3'])) {
                                        // 쉼표를 제거한 후 숫자로 변환
                                        $col9 = floatval(str_replace(',', '', $item['col9']));
                                        $col5 = floatval(str_replace(',', '', $item['col5']));

                                        // 단가 계산
                                        $unitprice = number_format($col9 / $col5);                                            
                                        $item_name = $item['col1'] . ' / ' . $item['col5'] . ' * ' . $unitprice;
                                        $item_amount = (float)str_replace(',', '', trim($item['col9'])) * 1.1;
                                        echo "<tr>
                                            <td class='text-start'></td>
                                            <td class='text-start'>{$item_name}</td>
                                            <td class='text-end'>" . number_format($item_amount) . "</td>
                                            <td class='text-start'></td>
                                            <td class='text-start'></td>
                                        </tr>";
                                    }
                                }
                            }

                            $accessorieslist = json_decode($row['accessorieslist'], true);
                            if (is_array($accessorieslist)) {
                                foreach ($accessorieslist as $item) {
                                    if (isset($item['col1']) && isset($item['col2'])) {
                                        // 쉼표를 제거한 후 숫자로 변환
                                        $col4 = floatval(str_replace(',', '', $item['col4']));
                                        $col2 = floatval(str_replace(',', '', $item['col2']));

                                        // 단가 계산
                                        $unitprice = number_format($col4 / $col2);                                            
                                        $item_name = $item['col1'] . ' / ' . $item['col2'] . ' * ' . $unitprice ;
                                        $item_amount = (float)str_replace(',', '', trim($item['col4'])) * 1.1;     // VAT 포함금액
										
										
                                        echo "<tr>
                                            <td class='text-start'></td>
                                            <td class='text-start'>{$item_name}</td>
                                            <td class='text-end'>" . number_format($item_amount) . "</td>
                                            <td class='text-start'></td>
                                            <td class='text-start'></td>
                                        </tr>";
                                    }
                                }
                            }
 
                            // Add discount if exists
                            $dcadd = (float)str_replace(',', '', trim($row['dcadd'])) * 1.1;
                            if ($dcadd != 0) {
                                // $total_balance -= round($dcadd);
                                // $grand_total -= round($dcadd) ;
                                echo "<tr>
                                    <td class='text-start date-row'></td>
                                    <td class='text-start date-row'>추가할인</td>
                                    <td class='text-end date-row  fw-bold'>- " . number_format($dcadd) . "</td>
                                    <td class='text-start date-row'></td>
                                    <td class='text-end date-row  fw-bold'> </td>
                                </tr>";
                            }
                        }
                    }

                    if ($current_month !== '') {
						
						// 마지막 1원 정리
						if (round($monthly_sales[$current_month]) % 10 === 1) {
							$monthly_sales[$current_month] -= 1;  // 1을 빼서 마지막 자리를 0으로 만듭니다.
						}	
							
                        echo "<tr>
                            <td colspan='2' class='text-center fw-bold date-row'>{$current_month} 계 <span style='font-size:9px;'> (VAT 포함) </span></td>
                            <td class='text-end date-row  fw-bold'>" . number_format($monthly_sales[$current_month]) . "</td>
                            <td class='text-start date-row'></td>
                            <td class='text-start date-row'></td>
                        </tr>";
                    }
					
								
					if (round($grand_total) % 10 === 1) {
						$grand_total -= 1;  // 1을 빼서 마지막 자리를 0으로 만듭니다.
					}

					if (round($total_balance) % 10 === 1) {
						$total_balance -= 1;  // 1을 빼서 마지막 자리를 0으로 만듭니다.
					}					
					
                    ?>
                    <tr class="date-row" >
                        <td colspan="2" class="text-center fw-bold date-row" >총 <?= $sale_count ?>건 누계 <span style='font-size:11px;'> (VAT 포함) </span></td>
                        <td class="text-end date-row fw-bold"><?= number_format($grand_total) ?></td>
                        <td class="text-end date-row fw-bold"><?= number_format($total_payment_sum) ?></td> <!-- 수금 합계 표시 -->
                        <td class="text-end date-row fw-bold"><?= number_format($total_balance) ?></td> <!-- 잔액 합계 표시 -->
                    </tr>
                </tbody>
            </table>
          </div>
    </div>
</div>


<!-- 페이지로딩 -->
<script>
$(document).ready(function(){    
    var loader = document.getElementById('loadingOverlay');
    loader.style.display = 'none';
});

function generatePDF() {
    var workplace = '<?= $customer['vendor_name'] ?>';
    var deadline = '<?php echo $Last_deadline; ?>';
    var deadlineDate = new Date(deadline);
    var formattedDate = "(" + String(deadlineDate.getFullYear()).slice(-2) + "." + ("0" + (deadlineDate.getMonth() + 1)).slice(-2) + "." + ("0" + deadlineDate.getDate()).slice(-2) + ")";
    var result = '(주)대한 거래원장(' + workplace + ')' + formattedDate + '.pdf';    
    
    var element = document.getElementById('content-to-print');
    var opt = {
        margin: [15, 8, 17, 8], // Top, right, bottom, left margins
        filename: result,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
        pagebreak: { mode: [''] }
    };
    html2pdf().from(element).set(opt).save();
}


function redirectToView(num, tablename) {
    var url = "write_form.php?mode=view&num=" + num + "&tablename=" + tablename;
    customPopup(url, '수주내역', 1850, 900);
}


ajaxRequest = null;

// Function to check server upload limits
function checkServerLimits() {
    return new Promise((resolve, reject) => {
        $.ajax({
            type: 'GET',
            url: 'save_pdf.php?status=check',
            dataType: 'json',
            success: function(response) {
                console.log('[Server Limits]', response);
                resolve(response);
            },
            error: function(xhr, status, error) {
                console.error('[Server Limits Error]', xhr.responseText);
                reject(error);
            }
        });
    });
}

function generatePDF_server(callback) {
    var workplace = '<?= $customer['vendor_name'] ?>';
    var item = '<?php echo $item_title; ?>';    
    var deadline = '<?php echo $Last_deadline; ?>';
    var deadlineDate = new Date(deadline);
    var formattedDate = "(" + String(deadlineDate.getFullYear()).slice(-2) + "." + ("0" + (deadlineDate.getMonth() + 1)).slice(-2) + "." + ("0" + deadlineDate.getDate()).slice(-2) + ")";
    var result = 'DH ' + item +'(' + workplace + ')' + formattedDate + '.pdf';        

    var element = document.getElementById('content-to-print');
    var opt = {
        margin: [15, 8, 17, 8], // Top, right, bottom, left margins
        filename: result,
        image: { type: 'jpeg', quality: 0.98 }, // 품질을 0.70으로 낮춤
        html2canvas: { 
            scale: 2,
            useCORS: true,
            allowTaint: true,
            backgroundColor: '#ffffff'
        },
        jsPDF: { 
            unit: 'mm', 
            format: 'a4', 
            orientation: 'portrait',
            compress: true // PDF 압축 활성화
        },
        pagebreak: { mode: [''] }
    };

    // Show loading message and store the instance
    var loadingDialog = Swal.fire({
        title: '메일 전송중',
        text: '메일을 전송중입니다...',
        icon: 'info',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });    

    html2pdf().from(element).set(opt).output('datauristring').then(function (pdfDataUri) {
        var pdfBase64 = pdfDataUri.split(',')[1]; // Base64 인코딩된 PDF 데이터 추출
        
        // Calculate and log PDF size
        var pdfSizeInBytes = Math.ceil((pdfBase64.length * 3) / 4);
        var pdfSizeInMB = (pdfSizeInBytes / (1024 * 1024)).toFixed(2);
        var pdfSizeInKB = (pdfSizeInBytes / 1024).toFixed(2);
        console.log('PDF Size Details:');
        console.log('- Base64 length:', pdfBase64.length, 'characters');
        console.log('- Estimated actual size:', pdfSizeInBytes, 'bytes');
        console.log('- Size in KB:', pdfSizeInKB, 'KB');
        console.log('- Size in MB:', pdfSizeInMB, 'MB');

        // Try multiple upload methods
        uploadPDFWithFallback(pdfBase64, result, callback);
    });
}

function uploadPDFWithFallback(pdfBase64, filename, callback) {
    // Method 1: Try regular save_pdf.php
    uploadPDF(pdfBase64, filename, 'save_pdf.php', function(success, response) {
        if (success) {
            if (callback) callback(response.filename);
            return;
        }
        
        // Method 2: Try alternative method
        console.log('Method 1 failed, trying alternative...');
        uploadPDF(pdfBase64, filename, 'save_pdf_alternative.php', function(success, response) {
            if (success) {
                if (callback) callback(response.filename);
                return;
            }
            
            // Method 3: Try test upload method (more conservative)
            console.log('Method 2 failed, trying test upload...');
            uploadPDF(pdfBase64, filename, 'test_upload.php', function(success, response) {
                if (success) {
                    if (callback) callback(response.filename);
                    return;
                }
                
                // Method 4: Try chunked upload for larger files
                console.log('Method 3 failed, trying chunked upload...');
                uploadPDFChunked(pdfBase64, filename, function(chunkedFilename) {
                    if (chunkedFilename) {
                        if (callback) callback(chunkedFilename);
                    } else {
                        // Method 5: Create a simple PDF on server side as last resort
                        console.log('All upload methods failed, creating server-side PDF...');
                        createServerSidePDF(filename, callback);
                    }
                });
            });
        });
    });
}

function uploadPDF(pdfBase64, filename, endpoint, callback) {
    // Try FormData first
    var formData = new FormData();
    formData.append('pdf', pdfBase64);
    formData.append('filename', filename);

    $.ajax({
        type: 'POST',
        url: endpoint,
        data: formData,
        processData: false,
        contentType: false,
        timeout: 60000, // 1분 타임아웃
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function (response) {
            try {
                var res = JSON.parse(response);
                if (res.error) {
                    console.error('Upload error:', res.error);
                    callback(false, res);
                } else {
                    console.log('Upload success:', res);
                    callback(true, res);
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                callback(false, { error: '서버 응답을 처리할 수 없습니다.' });
            }
        },
        error: function (xhr, status, error) {
            console.error('FormData upload failed:', xhr.status, status, error);
            
            // If FormData fails, try JSON approach
            if (xhr.status === 413 || xhr.status === 0) {
                console.log('Trying JSON upload method...');
                uploadPDFJSON(pdfBase64, filename, endpoint, callback);
            } else {
                var errorMsg = 'PDF 저장에 실패했습니다. (상태: ' + xhr.status + ')';
                if (xhr.status === 413) {
                    errorMsg = '서버에서 파일 크기 제한을 초과했습니다.';
                }
                callback(false, { error: errorMsg });
            }
        }
    });
}

function uploadPDFJSON(pdfBase64, filename, endpoint, callback) {
    var data = {
        pdf: pdfBase64,
        filename: filename
    };

    $.ajax({
        type: 'POST',
        url: endpoint,
        data: JSON.stringify(data),
        contentType: 'application/json',
        timeout: 60000,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function (response) {
            try {
                var res = JSON.parse(response);
                if (res.error) {
                    console.error('JSON upload error:', res.error);
                    callback(false, res);
                } else {
                    console.log('JSON upload success:', res);
                    callback(true, res);
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                callback(false, { error: '서버 응답을 처리할 수 없습니다.' });
            }
        },
        error: function (xhr, status, error) {
            console.error('JSON upload failed:', xhr.status, status, error);
            var errorMsg = 'PDF 저장에 실패했습니다. (상태: ' + xhr.status + ')';
            callback(false, { error: errorMsg });
        }
    });
}

function uploadPDFChunked(pdfBase64, filename, callback) {
    var chunkSize = 50000; // 50KB chunks
    var totalChunks = Math.ceil(pdfBase64.length / chunkSize);
    var currentChunk = 0;
    
    function uploadChunk() {
        if (currentChunk >= totalChunks) {
            console.log('All chunks uploaded successfully');
            if (callback) callback(filename);
            return;
        }
        
        var start = currentChunk * chunkSize;
        var end = Math.min(start + chunkSize, pdfBase64.length);
        var chunk = pdfBase64.substring(start, end);
        
        var formData = new FormData();
        formData.append('tiny_chunk', chunk);
        formData.append('filename', filename);
        formData.append('chunkIndex', currentChunk);
        formData.append('totalChunks', totalChunks);
        
        console.log('Sending chunk:', {
            chunkIndex: currentChunk,
            totalChunks: totalChunks,
            chunkLength: chunk.length,
            filename: filename
        });
        
        $.ajax({
            type: 'POST',
            url: 'save_pdf_tiny.php',
            data: formData,
            processData: false,
            contentType: false,
            timeout: 30000,
            success: function(response) {
                console.log('Chunk upload response received:', {
                    type: typeof response,
                    value: response,
                    stringified: JSON.stringify(response)
                });
                
                try {
                    // Handle both string and object responses
                    var res;
                    if (typeof response === 'string') {
                        res = JSON.parse(response);
                    } else {
                        res = response; // Already an object
                    }
                    
                    if (res.error) {
                        console.error('Chunk upload error:', res.error);
                        Swal.fire('Error', res.error, 'error');
                        return;
                    }
                    currentChunk++;
                    uploadChunk();
                } catch (e) {
                    console.error('JSON parse error:', e);
                    console.error('Response type:', typeof response);
                    console.error('Response value:', response);
                    Swal.fire('Error', '청크 업로드 응답을 처리할 수 없습니다.', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Chunk upload failed:', xhr.status, error);
                Swal.fire('Error', '청크 업로드에 실패했습니다.', 'error');
            }
        });
    }
    
    uploadChunk();
}

function createServerSidePDF(filename, callback) {
    var workplace = '<?= $customer['vendor_name'] ?>';
    var deadline = '<?php echo $Last_deadline; ?>';
    
    $.ajax({
        type: 'POST',
        url: 'generate_pdf_server.php',
        data: {
            filename: filename,
            workplace: workplace,
            deadline: deadline,
            fromdate: '<?php echo $fromdate; ?>',
            todate: '<?php echo $todate; ?>',
            secondordnum: '<?php echo $secondordnum; ?>'
        },
        dataType: 'json',
        timeout: 120000,
        success: function(response) {
            if (response.success && response.filename) {
                console.log('Server-side PDF created:', response.filename);
                if (callback) callback(response.filename);
            } else {
                console.error('Server-side PDF creation failed:', response.error);
                // Try creating a simple text-based PDF as last resort
                console.log('Trying simple text PDF creation...');
                createSimpleTextPDF(filename, callback);
            }
        },
        error: function(xhr, status, error) {
            console.error('Server-side PDF creation error:', xhr.status, error);
            // Try creating a simple text-based PDF as last resort
            console.log('Trying simple text PDF creation...');
            createSimpleTextPDF(filename, callback);
        }
    });
}

function createSimpleTextPDF(filename, callback) {
    var workplace = '<?= $customer['vendor_name'] ?>';
    var fromdate = '<?php echo $fromdate; ?>';
    var todate = '<?php echo $todate; ?>';
    var secondordnum = '<?php echo $secondordnum; ?>';
    
    $.ajax({
        type: 'POST',
        url: 'generate_pdf_tcpdf.php',
        data: {
            filename: filename,
            workplace: workplace,
            fromdate: fromdate,
            todate: todate,
            secondordnum: secondordnum
        },
        dataType: 'json',
        timeout: 60000,
        success: function(response) {
            if (response.success && response.filename) {
                console.log('Simple text PDF created:', response.filename);
                if (callback) callback(response.filename);
            } else {
                console.error('Simple text PDF creation failed:', response.error);
                Swal.fire('Error', 'PDF 생성에 실패했습니다: ' + (response.error || '알 수 없는 오류'), 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('Simple text PDF creation error:', xhr.status, error);
            Swal.fire('Error', '모든 PDF 생성 방법이 실패했습니다. 관리자에게 문의하세요.', 'error');
        }
    });
}
function sendmail() {
    var secondordnum = '<?php echo $secondordnum; ?>'; // 서버에서 가져온 값
    var item = '<?php echo preg_replace('/[\/\\\\:*?"<>|]/u', '_', $item_title); ?>'; 
    console.log('[sendmail] secondordnum:', secondordnum);
    
    if (typeof ajaxRequest !== 'undefined' && ajaxRequest !== null) {
        ajaxRequest.abort();
    }
    
    ajaxRequest = $.ajax({
        type: 'POST',
        url: 'get_companyCode.php',
        data: { secondordnum: secondordnum },
        dataType: 'json',
        success: function(response) {
            console.log('[get_companyCode.php 응답]', response);
            if (response.error) {
                Swal.fire('Error', response.error, 'error');
            } else {
                var email = response.email;
                var vendorName = response.vendor_name;

                console.log('[get_companyCode] email:', email, '| vendorName:', vendorName);

                Swal.fire({
                    title: 'E메일 보내기',
                    text: vendorName + ' 전송후 거래처 원장 전송시간이 기록됩니다.',
                    icon: 'warning',
                    input: 'text',
                    inputLabel: 'Email 주소 수정 가능',
                    inputValue: email,
                    showCancelButton: true,
                    confirmButtonText: '보내기',
                    cancelButtonText: '취소',
                    reverseButtons: true,
                    inputValidator: (value) => {
                        if (!value) {
                            return '이메일 주소를 입력해주세요!';
                        }
                        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailPattern.test(value)) {
                            return '올바른 이메일 형식을 입력해주세요!';
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const updatedEmail = result.value;
                        console.log('[Swal Confirmed] updatedEmail:', updatedEmail);

                        generatePDF_server(function(filename) {
                            console.log('[generatePDF_server 완료] filename:', filename);
                            sendEmail(updatedEmail, vendorName, item, filename);
                        });
                    }
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('[get_companyCode.php 에러]', xhr.responseText);
            Swal.fire('Error', '전송중 오류가 발생했습니다.', 'error');
        }
    });
}

function sendEmail(recipientEmail, vendorName, item, filename) {
    var deadline = '<?php echo $Last_deadline; ?>';
    var deadlineDate = new Date(deadline);
    var formattedDate = "(" + String(deadlineDate.getFullYear()).slice(-2) + "." + ("0" + (deadlineDate.getMonth() + 1)).slice(-2) + "." + ("0" + deadlineDate.getDate()).slice(-2) + ")";

    console.log('[sendEmail 호출]', {
        recipientEmail,
        vendorName,
        item,
        filename,
        formattedDate
    });

    if (typeof ajaxRequest !== 'undefined' && ajaxRequest !== null) {
        ajaxRequest.abort();
    }

    ajaxRequest = $.ajax({
        type: 'POST',
        url: 'send_email.php',
        data: {
            email: recipientEmail,
            vendorName: vendorName,
            filename: filename,
            item: item,
            formattedDate: formattedDate
        },
        dataType: 'json',
        success: function(response) {
            console.log('[send_email.php 응답]', response);

            if (typeof response === 'string') {
                try {
                    response = JSON.parse(response);
                } catch (e) {
                    console.error('[JSON 파싱 실패]', response);
                    Swal.fire('Error', '서버 응답이 올바르지 않습니다.', 'error');
                    return;
                }
            }

            if (response.error) {
                // Close loading dialog first
                Swal.close();
                Swal.fire('Error', response.error, 'error');
            } else {
                // Close loading dialog first
                Swal.close();
                Swal.fire({
                    title: '전송 완료',
                    text: '전송이 완료되었습니다.',
                    icon: 'success',
                    confirmButtonText: '확인'
                });

                // 거래원장 발행 여부 'write' 일때 거래원장 발행 버튼 표시
                if ($('#book_issued').val() == 'write') {
                    // AJAX를 통해 insert_monthly_sales_book.php 호출하여 전송시간 기록
                    var book_issued = new Date().toISOString();
                    var uniqueNum = $('#uniqueNum').val();
                    $.ajax({
                        url: 'insert_monthly_sales_book.php',
                        type: 'POST',
                        data: {
                            uniqueNum: uniqueNum,
                            book_issued: book_issued
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                console.log('[monthly_sales 테이블 book_issued 업데이트 성공]', response);
                                setTimeout(function() {
                                    window.opener.location.reload();
                                }, 500);                                
                            } else {
                                console.error('[monthly_sales 테이블 book_issued 업데이트 실패]', response.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('[insert_monthly_sales_book.php 에러]', xhr.responseText);
                        }
                    });
                }
            } 
        },
        error: function(xhr, status, error) {
            console.error('[send_email.php 에러]', xhr.responseText);
            // Close loading dialog first
            Swal.close();
            Swal.fire('Error', '전송에 실패했습니다. 확인바랍니다.', 'error');
        }
    });
}

// 테스트 업로드 기능
function testUpload() {
    // 간단한 테스트 데이터 생성 (1KB)
    var testData = 'A'.repeat(1024);
    var testBase64 = btoa(testData); // Base64 인코딩
    
    console.log('[Test Upload] Testing with 1KB data...');
    
    // Test all upload methods
    uploadPDFWithFallback(testBase64, 'test_document.pdf', function(filename) {
        console.log('[Test Upload Success]', filename);
        Swal.fire('Success', '테스트 업로드 성공: ' + filename, 'success');
    });
}

// 서버 업로드 제한 상태 확인
function checkUploadLimits() {
    checkServerLimits().then(function(serverInfo) {
        var limits = serverInfo.limits;
        var message = '서버 업로드 제한 상태:\n\n';
        message += 'POST 최대 크기: ' + limits.post_max_size + '\n';
        message += '업로드 최대 크기: ' + limits.upload_max_filesize + '\n';
        message += '실행 시간 제한: ' + limits.max_execution_time + '초\n';
        message += '메모리 제한: ' + limits.memory_limit + '\n';
        message += '입력 시간 제한: ' + limits.max_input_time + '초\n';
        message += '최대 파일 업로드 수: ' + limits.max_file_uploads + '개';
        
        Swal.fire({
            title: '서버 업로드 제한',
            text: message,
            icon: 'info',
            confirmButtonText: '확인'
        });
    }).catch(function(error) {
        Swal.fire('Error', '서버 상태 확인에 실패했습니다.', 'error');
    });
}

// 엑셀 파일 다운로드
function exportTableToExcel(tableID = 'myTable') {
    var table1 = document.getElementById("myTable1");
    var table2 = document.getElementById("myTable2");

    // 첫 번째 테이블을 시트로
    var ws = XLSX.utils.table_to_sheet(table1);

    // 두 번째 테이블을 2차원 배열로 추출
    var data2 = [];
    for (let row of table2.rows) {
        let rowData = [];
        for (let cell of row.cells) {
            rowData.push(cell.innerText);
        }
        data2.push(rowData);
    }

    // 두 번째 테이블 데이터를 기존 시트 아래에 추가
    let startRow = XLSX.utils.decode_range(ws['!ref']).e.r + 2;  // 한 줄 띄우고 시작
    XLSX.utils.sheet_add_aoa(ws, data2, { origin: { r: startRow, c: 0 } });

    // 엑셀 파일로 저장
    var wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "합쳐진시트");

    // 스타일 지원을 위해 옵션 지정 필요
    XLSX.writeFile(wb, "DH거래명세표.xlsx");
}

</script>

</body>
</html>