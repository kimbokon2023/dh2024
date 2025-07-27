<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");
$title_message = 'DH모터 매출 통계';
if(!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    sleep(1);
    header("Location: " . $WebSite . "login/login_form.php");
    exit;
}
include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';
?>

<title><?= $title_message ?></title>
</head>
<body>

<?php
$header = isset($_REQUEST['header']) ? $_REQUEST['header'] : '';
$active_tab = isset($_REQUEST['active_tab']) ? $_REQUEST['active_tab'] : 'sales';

require_once($_SERVER['DOCUMENT_ROOT'] . '/myheader.php');

$tablename = 'motor';

// 매년 1월1일부터 현재일까지 계산
// $fromdate = isset($_REQUEST['fromdate']) && $_REQUEST['fromdate'] ? $_REQUEST['fromdate'] : date("Y") . "-01-01";
// $todate = isset($_REQUEST['todate']) && $_REQUEST['todate'] ? $_REQUEST['todate'] : date("Y") . "-12-31";
// $transtodate = date("Y-m-d", strtotime($todate . '+1 day'));

$fromdate = isset($_REQUEST['fromdate']) && $_REQUEST['fromdate'] ? $_REQUEST['fromdate'] : date("Y-m-01"); // 이번 달 1일
$todate = isset($_REQUEST['todate']) && $_REQUEST['todate'] ? $_REQUEST['todate'] : date("Y-m-d"); // 오늘 날짜
$transtodate = date("Y-m-d", strtotime($todate . '+1 day')); // todate의 다음 날

$orderby = "ORDER BY deadline DESC";
$sql = "SELECT * FROM {$DB}.{$tablename} WHERE deadline BETWEEN date('$fromdate') AND date('$transtodate') AND (is_deleted IS null or is_deleted = '0') " . $orderby;

try {
    $stmh = $pdo->prepare($sql);
    $stmh->execute();
    $rows = $stmh->fetchAll(PDO::FETCH_ASSOC);

    $chartData = [];
    $itemData = [];

    foreach ($rows as $row) {
        $orderlist = json_decode($row['orderlist'], true);
        $accessorieslist = json_decode($row['accessorieslist'], true);
        $controllerlist = json_decode($row['controllerlist'], true);
        $fabriclist = json_decode($row['fabriclist'], true);

        $month = date("Y-m", strtotime($row['deadline']));
        if (!isset($chartData[$month])) {
            $chartData[$month] = 0;
            $itemData[$month] = [
                'motorSum' => 0,
                'bracketSum' => 0,
                'controllerSum' => 0,
                'accesorieSum' => 0,
                'fabricSum' => 0
            ];
        }

        // Orderlist Processing (Motors and Brackets)
        if (is_array($orderlist)) {
            foreach ($orderlist as $item) {
                if (isset($item['col12']) && is_numeric(str_replace(',', '', $item['col12']))) {
                    $amount = (float)str_replace(',', '', $item['col12']);
                    $amount_with_vat = $amount * 1.1; // VAT 포함
                    $chartData[$month] += $amount_with_vat;

                    if (isset($item['col8']) && isset($item['col5'])) {
                        $value = (int)str_replace(',', '', $item['col8']);
                        if ($item['col5'] === 'SET') {
                            $itemData[$month]['motorSum'] += $value;
                            $itemData[$month]['bracketSum'] += $value;
                        } elseif ($item['col5'] === '모터단품') {
                            $itemData[$month]['motorSum'] += $value;
                        } elseif ($item['col5'] === '브라켓트') {
                            $itemData[$month]['bracketSum'] += $value;
                        }
                    }
                }
            }
        }

        // Accessorieslist Processing (Accessories)
        if (is_array($accessorieslist)) {
            foreach ($accessorieslist as $item) {
                if (isset($item['col4']) && is_numeric(str_replace(',', '', $item['col4']))) {
                    $amount = (float)str_replace(',', '', $item['col4']);
                    $amount_with_vat = $amount * 1.1; // VAT 포함
                    $chartData[$month] += $amount_with_vat;
                }

                if (isset($item['col2']) && is_numeric(str_replace(',', '', $item['col2']))) {
                    $value = (float)str_replace(',', '', $item['col2']);
                    $itemData[$month]['accesorieSum'] += $value;
                }
            }
        }

        // Controllerlist Processing (Controllers)
        if (is_array($controllerlist)) {
            foreach ($controllerlist as $item) {
                if (isset($item['col7']) && is_numeric(str_replace(',', '', $item['col7']))) {
                    $amount = (float)str_replace(',', '', $item['col7']);
                    $amount_with_vat = $amount * 1.1; // VAT 포함
                    $chartData[$month] += $amount_with_vat;
                }
                if (isset($item['col3'])) {
                    $value = (int)str_replace(',', '', $item['col3']);
                    $itemData[$month]['controllerSum'] += $value;
                }
            }
        }

        // Fabriclist Processing (Fabric)
        if (is_array($fabriclist)) {
            foreach ($fabriclist as $item) {
                if (isset($item['col9']) && is_numeric(str_replace(',', '', $item['col9']))) {
                    $amount = (float)str_replace(',', '', $item['col9']);
                    $amount_with_vat = $amount * 1.1; // VAT 포함
                    $chartData[$month] += $amount_with_vat;
                }
                // 수량은 총길이로 산출한다. 원단은 특수한 경우
                if (isset($item['col5']) && is_numeric(str_replace(',', '', $item['col5']))) {
                    $value = (float)str_replace(',', '', $item['col5']);
                    $itemData[$month]['fabricSum'] += $value;
                }
            }
        }
    }

    $jsonChartData = json_encode($chartData);
    $jsonItemData = json_encode($itemData);

    // 이하의 부분도 같은 방식으로 VAT 포함 처리해줍니다.
    // Aggregation of primary materials, controllers, and fabrics data
    $primaryMaterialsData = [];
    $controllerMaterialsData = [];
    $fabricMaterialsData = [];
    $etcMaterialsData = [];
    $totalQuantitySum = 0;
    $totalPriceSum = 0;
    $totalControllerQuantitySum = 0;
    $totalControllerPriceSum = 0;
    $totalfabricQuantitySum = 0;
    $totalfabricPriceSum = 0;
    $totalaccessoriesQuantitySum = 0; // 부속자재금액 추가
    $totalaccessoriesPriceSum = 0;

    foreach ($rows as $row) {
        $orderlist = json_decode($row['orderlist'], true);
        $controllerlist = json_decode($row['controllerlist'], true);
        $fabriclist = json_decode($row['fabriclist'], true);
        $accessorieslist = json_decode($row['accessorieslist'], true);

        // Process orderlist (Motors and Brackets)
        if (is_array($orderlist)) {
            foreach ($orderlist as $item) {
                if (isset($item['col1'], $item['col2'], $item['col4'], $item['col8'], $item['col12'])) {
                    $motorItemCode = '모터-' . $item['col1'] . '-' . $item['col2'] . '-' . $item['col4'];
                    $quantity = (float)str_replace(',', '', $item['col8']);
                    $amount = (float)str_replace(',', '', $item['col12']);
                    $amount_with_vat = $amount * 1.1; // VAT 포함

                    if ($item['col5'] === '모터단품' || $item['col5'] === 'SET') {
                        if (!isset($primaryMaterialsData[$motorItemCode])) {
                            $primaryMaterialsData[$motorItemCode] = [
                                'totalQuantity' => 0,
                                'totalPrice' => 0
                            ];
                        }
                        $primaryMaterialsData[$motorItemCode]['totalQuantity'] += $quantity;
                        $primaryMaterialsData[$motorItemCode]['totalPrice'] += $amount_with_vat;
                        $totalQuantitySum += $quantity;
                        $totalPriceSum += $amount_with_vat;
                    }
                }

                // Process bracket items
                if (isset($item['col6'], $item['col8'], $item['col12'])) {
                    $bracketItemCode = '브라켓트-' . $item['col6'];
                    $quantity = (float)str_replace(',', '', $item['col8']);
                    $amount = (float)str_replace(',', '', $item['col12']);
                    $amount_with_vat = $amount * 1.1; // VAT 포함

                    if ($item['col5'] === '브라켓트' || $item['col5'] === 'SET') {
                        if (!isset($primaryMaterialsData[$bracketItemCode])) {
                            $primaryMaterialsData[$bracketItemCode] = [
                                'totalQuantity' => 0,
                                'totalPrice' => 0
                            ];
                        }
                        $primaryMaterialsData[$bracketItemCode]['totalQuantity'] += $quantity;
                        $totalQuantitySum += $quantity;
						// 브라켓트 자체만 있는 경우 금액 합산, SET인 경우는 이미 모터에서 합산한 것임
						if ($item['col5'] === '브라켓트') {
							$primaryMaterialsData[$bracketItemCode]['totalPrice'] += $amount_with_vat;
							$totalPriceSum += $amount_with_vat;
                        }
					}
                }
            }
        }

        // Process controllerlist
        if (is_array($controllerlist)) {
            foreach ($controllerlist as $item) {
                if (isset($item['col2'], $item['col3'], $item['col7'])) {
                    $controllerItemCode = $item['col2'];
                    $quantity = (float)str_replace(',', '', $item['col3']);
                    $amount = (float)str_replace(',', '', $item['col7']);
                    $amount_with_vat = $amount * 1.1; // VAT 포함

                    if (!isset($controllerMaterialsData[$controllerItemCode])) {
                        $controllerMaterialsData[$controllerItemCode] = [
                            'totalQuantity' => 0,
                            'totalPrice' => 0
                        ];
                    }
                    $controllerMaterialsData[$controllerItemCode]['totalQuantity'] += $quantity;
                    $controllerMaterialsData[$controllerItemCode]['totalPrice'] += $amount_with_vat;
                    $totalControllerQuantitySum += $quantity;
                    $totalControllerPriceSum += $amount_with_vat;
                }
            }
        }

        // Process fabriclist
        if (is_array($fabriclist)) {
            foreach ($fabriclist as $item) {
                if (isset($item['col9'])) {
                    $fabricItemCode = $item['col1'];
                    $quantity = (float)str_replace(',', '', $item['col5']);
                    $amount = (float)str_replace(',', '', $item['col9']);
                    $amount_with_vat = $amount * 1.1; // VAT 포함

                    if (!isset($fabricMaterialsData[$fabricItemCode])) {
                        $fabricMaterialsData[$fabricItemCode] = [
                            'totalQuantity' => 0,
                            'totalPrice' => 0
                        ];
                    }
                    $fabricMaterialsData[$fabricItemCode]['totalQuantity'] += $quantity;
                    $fabricMaterialsData[$fabricItemCode]['totalPrice'] += $amount_with_vat;
                    $totalfabricQuantitySum += $quantity;
                    $totalfabricPriceSum += $amount_with_vat;
                }
            }
        }
        // Process accessorieslist
        if (is_array($accessorieslist)) {
            foreach ($accessorieslist as $item) {
                if (isset($item['col9'])) {
                    $accessoriesItemCode = $item['col1'];
                    $quantity = (float)str_replace(',', '', $item['col5']);
                    $amount = (float)str_replace(',', '', $item['col9']);
                    $amount_with_vat = $amount * 1.1; // VAT 포함

                    if (!isset($accessoriesMaterialsData[$accessoriesItemCode])) {
                        $accessoriesMaterialsData[$accessoriesItemCode] = [
                            'totalQuantity' => 0,
                            'totalPrice' => 0
                        ];
                    }
                    $accessoriesMaterialsData[$accessoriesItemCode]['totalQuantity'] += $quantity;
                    $accessoriesMaterialsData[$accessoriesItemCode]['totalPrice'] += $amount_with_vat;
                    $totalaccessoriesQuantitySum += $quantity;
                    $totalaccessoriesPriceSum += $amount_with_vat;
                }
            }
        }
    }

	 // 매출 세부내역을 산출하기 위한 코드
	try {
		// Ensure all material data arrays are initialized to prevent undefined variable warnings
		$primaryMaterialsData = isset($primaryMaterialsData) && is_array($primaryMaterialsData) ? $primaryMaterialsData : [];
		$controllerMaterialsData = isset($controllerMaterialsData) && is_array($controllerMaterialsData) ? $controllerMaterialsData : [];
		$fabricMaterialsData = isset($fabricMaterialsData) && is_array($fabricMaterialsData) ? $fabricMaterialsData : [];
		$accessoriesMaterialsData = isset($accessoriesMaterialsData) && is_array($accessoriesMaterialsData) ? $accessoriesMaterialsData : [];

		// Process and sort material data safely
		$primaryMaterialsData = array_map(function ($itemcode, $data) {
			return ['itemcode' => $itemcode] + $data;
		}, array_keys($primaryMaterialsData), $primaryMaterialsData);

		usort($primaryMaterialsData, function ($a, $b) {
			if (strpos($a['itemcode'], '모터-') === 0 && strpos($b['itemcode'], '브라켓트-') === 0) {
				return -1;
			} elseif (strpos($a['itemcode'], '브라켓트-') === 0 && strpos($b['itemcode'], '모터-') === 0) {
				return 1;
			} else {
				return strcmp($a['itemcode'], $b['itemcode']);
			}
		});

		$controllerMaterialsData = array_map(function ($itemcode, $data) {
			return ['itemcode' => $itemcode] + $data;
		}, array_keys($controllerMaterialsData), $controllerMaterialsData);

		usort($controllerMaterialsData, function ($a, $b) {
			return strcmp($a['itemcode'], $b['itemcode']);
		});

		$fabricMaterialsData = array_map(function ($itemcode, $data) {
			return ['itemcode' => $itemcode] + $data;
		}, array_keys($fabricMaterialsData), $fabricMaterialsData);

		usort($fabricMaterialsData, function ($a, $b) {
			return strcmp($a['itemcode'], $b['itemcode']);
		});

		$accessoriesMaterialsData = array_map(function ($itemcode, $data) {
			return ['itemcode' => $itemcode] + $data;
		}, array_keys($accessoriesMaterialsData), $accessoriesMaterialsData);

		usort($accessoriesMaterialsData, function ($a, $b) {
			return strcmp($a['itemcode'], $b['itemcode']);
		});

	} catch (PDOException $Exception) {
		print "오류: " . $Exception->getMessage();
	}

 
 

} catch (PDOException $Exception) {
    print "오류: " . $Exception->getMessage();
}
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const salesData = <?= $jsonChartData ?>;
    const itemData = <?= $jsonItemData ?>;

    const sortedLabels = Object.keys(salesData).sort((a, b) => new Date(a) - new Date(b));
    const sortedData = sortedLabels.map(label => parseFloat(salesData[label]));
    const sortedItemData = sortedLabels.map(label => itemData[label]);

    Highcharts.chart('salesChart', {
        chart: {
            type: 'column'
        },
        title: {
            text: '월별 매출'
        },
        xAxis: {
            categories: sortedLabels,
            crosshair: true
        },
        yAxis: {
            min: 0,
            title: {
                text: '매출액 (원)'
            }
        },
        tooltip: {
            headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
            pointFormatter: function() {
                return '<tr><td style="color:' + this.series.color + ';padding:0">' + this.series.name + ': </td>' +
                    '<td style="padding:0"><b>' + Highcharts.numberFormat(this.y, 0, '.', ',') + ' 원</b></td></tr>';
            },
            footerFormat: '</table>',
            shared: true,
            useHTML: true,
            style: {
                padding: '10px',
                minWidth: '200px'
            }
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                borderWidth: 0
            }
        },
        series: [{
            name: '매출액',
            data: sortedData
        }]
    });

   // 전체합계 table tbody 내용 계산
    const tableBody = document.getElementById('myTable').getElementsByTagName('tbody')[0];
    let totalSum = 0;
    let totalMotorSum = 0;
    let totalBracketSum = 0;
    let totalControllerSum = 0;
    let totalFabricSum = 0;
    let totalAccesorieSum = 0;

    sortedLabels.forEach((label, index) => {
        let row = tableBody.insertRow();
        let cell1 = row.insertCell(0);
        let cell2 = row.insertCell(1);
        let cell3 = row.insertCell(2);
        let cell4 = row.insertCell(3);
        let cell5 = row.insertCell(4);
        let cell6 = row.insertCell(5);
        let cell7 = row.insertCell(6);

        cell1.innerHTML = label;
        cell1.className = 'text-center fw-bold';

        // Formatting numbers with commas
        const formattedMotorSum = new Intl.NumberFormat('ko-KR').format(sortedItemData[index].motorSum);
        const formattedBracketSum = new Intl.NumberFormat('ko-KR').format(sortedItemData[index].bracketSum);
        const formattedControllerSum = new Intl.NumberFormat('ko-KR').format(sortedItemData[index].controllerSum);
        const formattedFabricSum = new Intl.NumberFormat('ko-KR').format(sortedItemData[index].fabricSum);
        const formattedAccesorieSum = new Intl.NumberFormat('ko-KR').format(sortedItemData[index].accesorieSum);
        const formattedAmount = new Intl.NumberFormat('ko-KR', { style: 'currency', currency: 'KRW' }).format(sortedData[index]);

        cell2.innerHTML = formattedMotorSum;
        cell2.className = 'text-center';

        cell3.innerHTML = formattedBracketSum;
        cell3.className = 'text-center';

        cell4.innerHTML = formattedControllerSum;
        cell4.className = 'text-center';

        cell5.innerHTML = formattedFabricSum;
        cell5.className = 'text-center';

        cell6.innerHTML = formattedAccesorieSum;
        cell6.className = 'text-center';

        cell7.innerHTML = formattedAmount;
        cell7.className = 'text-end';

        totalSum += sortedData[index];
        totalMotorSum += sortedItemData[index].motorSum;
        totalBracketSum += sortedItemData[index].bracketSum;
        totalControllerSum += sortedItemData[index].controllerSum;
        totalFabricSum += sortedItemData[index].fabricSum;
        totalAccesorieSum += sortedItemData[index].accesorieSum;
    });

    let totalRow = tableBody.insertRow();
    let totalCell1 = totalRow.insertCell(0);
    let totalCell2 = totalRow.insertCell(1);
    let totalCell3 = totalRow.insertCell(2);
    let totalCell4 = totalRow.insertCell(3);
    let totalCell5 = totalRow.insertCell(4);
    let totalCell6 = totalRow.insertCell(5);
    let totalCell7 = totalRow.insertCell(6);

    totalCell1.innerHTML = '합계';
    totalCell1.className = 'text-center fw-bold';

    // Formatting numbers with commas
    const formattedTotalMotorSum = new Intl.NumberFormat('ko-KR').format(totalMotorSum);
    const formattedTotalBracketSum = new Intl.NumberFormat('ko-KR').format(totalBracketSum);
    const formattedTotalControllerSum = new Intl.NumberFormat('ko-KR').format(totalControllerSum);
    const formattedTotalFabricSum = new Intl.NumberFormat('ko-KR').format(totalFabricSum);
    const formattedTotalAccesorieSum = new Intl.NumberFormat('ko-KR').format(totalAccesorieSum);
    const formattedTotalSum = new Intl.NumberFormat('ko-KR', { style: 'currency', currency: 'KRW' }).format(totalSum);

    totalCell2.innerHTML = formattedTotalMotorSum;
    totalCell2.className = 'text-center fw-bold';

    totalCell3.innerHTML = formattedTotalBracketSum;
    totalCell3.className = 'text-center fw-bold';

    totalCell4.innerHTML = formattedTotalControllerSum;
    totalCell4.className = 'text-center fw-bold';

    totalCell5.innerHTML = formattedTotalFabricSum;
    totalCell5.className = 'text-center fw-bold';

    totalCell6.innerHTML = formattedTotalAccesorieSum;
    totalCell6.className = 'text-center fw-bold';

    totalCell7.innerHTML = formattedTotalSum;
    totalCell7.className = 'text-end font-weight-bold';
});
</script>


<form id="board_form" name="board_form" method="post" action="statistics.php?mode=search">
    <input type="hidden" id="active_tab" name="active_tab" value="<?= $active_tab ?>">

    <div class="container mt-3 mb-5">
        <div class="card mb-2 mt-2">
            <div class="card-body">

                <div class="d-flex  p-1 m-1 mt-1 justify-content-center align-items-center ">
                    <h5>  <?=$title_message?> </h5>
					<button type="button" class="btn btn-dark btn-sm mx-3"  onclick='location.reload();' title="새로고침"> <i class="bi bi-arrow-clockwise"></i> </button>  
                </div>

                <div class="d-flex  p-1 m-1 mt-1 mb-1 justify-content-center align-items-center">
                    <span id="showdate" class="btn btn-dark btn-sm "> 기간 </span>  &nbsp;
                    <div id="showframe" class="card">
                        <div class="card-header " style="padding:2px;">
                            <div class="d-flex justify-content-center align-items-center">
                                기간 설정
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-center align-items-center">
                                <button type="button" class="btn btn-outline-success btn-sm me-1 change_dateRange"   onclick='alldatesearch()' > 전체 </button>
                                <button type="button" id="preyear" class="btn btn-outline-primary btn-sm me-1 change_dateRange"   onclick='pre_year()' > 전년도 </button>
                                <button type="button" id="three_month" class="btn btn-dark btn-sm me-1  change_dateRange"  onclick='three_month_ago()' > M-3월 </button>
                                <button type="button" id="prepremonth" class="btn btn-dark btn-sm me-1  change_dateRange"  onclick='prepre_month()' > 전전월 </button>
                                <button type="button" id="premonth" class="btn btn-dark btn-sm me-1  change_dateRange"  onclick='pre_month()' > 전월 </button>
                                <button type="button" class="btn btn-outline-danger btn-sm me-1  change_dateRange"  onclick='this_today()' > 오늘 </button>
                                <button type="button" id="thismonth" class="btn btn-dark btn-sm me-1  change_dateRange"  onclick='this_month()' > 당월 </button>
                                <button type="button" id="thisyear" class="btn btn-dark btn-sm me-1  change_dateRange"  onclick='this_year()' > 당해년도 </button>
                            </div>
                        </div>
                    </div>

                    <input type="date" id="fromdate" name="fromdate"   class="form-control"   style="width:100px;" value="<?=$fromdate?>" >  &nbsp;   ~ &nbsp;
                    <input type="date" id="todate" name="todate"  class="form-control me-1"   style="width:100px;" value="<?=$todate?>" >  &nbsp;     </span>

                    <input type="hidden" id="search" name="search" value="<?=$search?>" onkeydown="JavaScript:SearchEnter();" autocomplete="off"  class="form-control" style="width:200px;" >

                    <button id="searchBtn" type="button" class="btn btn-dark  btn-sm" > <i class="bi bi-search"></i> 검색 </button>
                </div>
                <div class="card mb-2 mt-2">
                    <div class="card-body">

                        <!-- Nav tabs -->
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?= $active_tab == 'sales' ? 'active' : '' ?>" id="sales-tab" data-bs-toggle="tab" data-bs-target="#sales" type="button" role="tab" aria-controls="sales" aria-selected="<?= $active_tab == 'sales' ? 'true' : 'false' ?>">전체 매출</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?= $active_tab == 'details' ? 'active' : '' ?>" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab" aria-controls="details" aria-selected="<?= $active_tab == 'details' ? 'true' : 'false' ?>">매출 세부내역</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?= $active_tab == 'contractors' ? 'active' : '' ?>" id="contractors-tab" data-bs-toggle="tab" data-bs-target="#contractors" type="button" role="tab" aria-controls="contractors" aria-selected="<?= $active_tab == 'contractors' ? 'true' : 'false' ?>">거래처별 매출현황</button>
                            </li>
                        </ul>

                        <!-- Tab panes -->
                        <div class="tab-content">
                            <div class="tab-pane fade show <?= $active_tab == 'sales' ? 'active' : '' ?>" id="sales" role="tabpanel" aria-labelledby="sales-tab">
                                <div class="row d-flex  p-1 m-1 mt-1 justify-content-center align-items-center ">
                                    <div class="col-sm-6 ">
                                        <div class="card mb-2 mt-2">
                                            <div class="card-body">
                                                <div class="d-flex  p-1 m-1 mt-1 justify-content-center align-items-center ">
                                                    <div id="salesChart" style="width: 100%; height: 400px;"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 ">
                                        <div class="card mb-2 mt-2">
                                            <div class="card-body">
                                                <div class="d-flex  p-1 m-1 mt-1 justify-content-end align-items-center ">
                                                    (수량 : EA, 금액: 원)
                                                </div>
                                                <div class="d-flex  p-1 m-1 mt-1 justify-content-center align-items-center ">
                                                    <table class="table table-hover" id="myTable">
                                                        <thead class="table-primary">
                                                            <tr>
                                                                <th class="text-center">해당 월</th>
                                                                <th class="text-center">모터</th>
                                                                <th class="text-center">브라켓</th>
                                                                <th class="text-center">연동제어기</th>
                                                                <th class="text-center">원단(단위:m)</th>
                                                                <th class="text-center">부속기타</th>
                                                                <th class="text-end">매출금액</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade <?= $active_tab == 'details' ? 'show active' : '' ?>" id="details" role="tabpanel" aria-labelledby="details-tab">
                                <!-- New Table for Primary Material Shipments -->
                                <div class="container mt-3">
                                    <div class="row">
                                        <div class="col-lg-6 col-sm-6">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-center">
                                                        <h4 class="mt-3 mb-2">(모터,브라켓트) 품목별 출고 통계</h4>
                                                    </div>
                                                    <table class="table table-hover" id="primaryMaterialsTable">
                                                        <thead class="table-secondary">
                                                            <tr>
                                                                <th class="text-center">품목코드</th>
                                                                <th class="text-center">출고수량</th>
                                                                <th class="text-end">금액 (₩)</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($primaryMaterialsData as $item): ?>
                                                                <tr>
                                                                    <td class="text-center"><?= htmlspecialchars($item['itemcode']) ?></td>
                                                                    <td class="text-center"><?= number_format($item['totalQuantity']) ?></td>
                                                                    <td class="text-end">₩<?= number_format($item['totalPrice']) ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                            <tr class="font-weight-bold">
                                                                <td class="text-center">총합계</td>
                                                                <td class="text-center"><?= number_format($totalQuantitySum) ?></td>
                                                                <td class="text-end">₩<?= number_format($totalPriceSum) ?></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-lg-6 col-sm-6">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-center">
                                                        <h4 class="mt-3 mb-2">(연동제어기) 품목별 출고 통계</h4>
                                                    </div>
                                                    <table class="table table-hover" id="controllerMaterialsTable">
                                                        <thead class="table-secondary">
                                                            <tr>
                                                                <th class="text-center">품목코드</th>
                                                                <th class="text-center">출고수량</th>
                                                                <th class="text-end">금액 (₩)</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($controllerMaterialsData as $item): ?>
                                                                <tr>
                                                                    <td class="text-center"><?= htmlspecialchars($item['itemcode']) ?></td>
                                                                    <td class="text-center"><?= number_format($item['totalQuantity']) ?></td>
                                                                    <td class="text-end">₩<?= number_format($item['totalPrice']) ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                            <tr class="font-weight-bold">
                                                                <td class="text-center">총합계</td>
                                                                <td class="text-center"><?= number_format($totalControllerQuantitySum) ?></td>
                                                                <td class="text-end">₩<?= number_format($totalControllerPriceSum) ?></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-center">
                                                        <h4 class="mt-3 mb-2">(원단) 품목별 출고 통계</h4>
                                                    </div>
                                                    <table class="table table-hover" id="controllerMaterialsTable">
                                                        <thead class="table-secondary">
                                                            <tr>
                                                                <th class="text-center">품목코드</th>
                                                                <th class="text-center">출고수량</th>
                                                                <th class="text-end">금액 (₩)</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($fabricMaterialsData as $item): ?>
                                                                <tr>
                                                                    <td class="text-center"><?= htmlspecialchars($item['itemcode']) ?></td>
                                                                    <td class="text-center"><?= number_format($item['totalQuantity']) ?></td>
                                                                    <td class="text-end">₩<?= number_format($item['totalPrice']) ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                            <tr class="font-weight-bold">
                                                                <td class="text-center">총합계</td>
                                                                <td class="text-center"><?= number_format($totalfabricQuantitySum) ?></td>
                                                                <td class="text-end">₩<?= number_format($totalfabricPriceSum) ?></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade <?= $active_tab == 'contractors' ? 'show active' : '' ?>" id="contractors" role="tabpanel" aria-labelledby="contractors-tab">
                                <div class=" d-flex p-1 m-1 mt-5 mb-5 justify-content-center align-items-center ">
                                    <span class="badge bg-primary fs-4"> 거래처별 매출현황 </span>
                                </div>

                                <?php
                                require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
                                $pdo = db_connect();

                                $sql = "SELECT secondord, DATE_FORMAT(deadline, '%Y-%m') AS month, secondordnum, orderlist, accessorieslist, controllerlist, fabriclist 
                                        FROM " . $DB . "." . $tablename . "
                                        WHERE deadline BETWEEN date('$fromdate') AND date('$transtodate')
                                          AND secondordnum IS NOT NULL  AND (is_deleted IS null or is_deleted = '0')  
                                        ORDER BY secondord, month";

                                try {
                                    $stmh = $pdo->prepare($sql);
                                    $stmh->execute();
                                    $contractorChartData = [];
                                    $totalSalesByContractor = [];
                                    $contractorItemsData = [];

                                    while ($row = $stmh->fetch(PDO::FETCH_ASSOC)) {
                                        $contractor = $row['secondord'];
                                        $month = $row['month'];

                                        $orderItems = json_decode($row['orderlist'], true);
                                        $accessorieslist = json_decode($row['accessorieslist'], true);
                                        $fabriclist = json_decode($row['fabriclist'], true);
                                        $controllerlist = json_decode($row['controllerlist'], true);
                                        $total_sales = 0;

                                        $motorSum = 0;
                                        $bracketSum = 0;
                                        $controllerSum = 0;
                                        $fabricSum = 0;
                                        $accesorieSum = 0;

                                        // Process orderItems
                                        if (is_array($orderItems)) {
                                            foreach ($orderItems as $item) {
                                                if (isset($item['col12']) && is_numeric(str_replace(',', '', $item['col12']))) {
                                                    $sales = (float)str_replace(',', '', $item['col12']);
                                                    $total_sales += $sales;
                                                }
                                                if (isset($item['col8']) && isset($item['col5'])) {
                                                    $value = (float)str_replace(',', '', $item['col8']);
                                                    if ($item['col5'] === 'SET') {
                                                        $motorSum += $value;
                                                        $bracketSum += $value;
                                                    } elseif ($item['col5'] === '모터단품') {
                                                        $motorSum += $value;
                                                    } elseif ($item['col5'] === '브라켓트') {
                                                        $bracketSum += $value;
                                                    }
                                                }
                                            }
                                        }

                                        // Process controllerlist
                                        if (is_array($controllerlist)) {
                                            foreach ($controllerlist as $item) {
                                                if (isset($item['col7']) && is_numeric(str_replace(',', '', $item['col7']))) {
                                                    $sales = (float)str_replace(',', '', $item['col7']);
                                                    $total_sales += $sales;
                                                }
                                                if (isset($item['col3'])) {
                                                    $value = (float)str_replace(',', '', $item['col3']);
                                                    $controllerSum += $value;
                                                }
                                            }
                                        }

                                        // Process fabriclist
                                        if (is_array($fabriclist)) {
                                            foreach ($fabriclist as $item) {
                                                if (isset($item['col9']) && is_numeric(str_replace(',', '', $item['col9']))) {
                                                    $sales = (float)str_replace(',', '', $item['col9']);
                                                    $total_sales += $sales;
                                                }
                                                if (isset($item['col5'])) {
                                                    $value = (float)str_replace(',', '', $item['col5']);
                                                    $fabricSum += $value;
                                                }
                                            }
                                        }
										
                                        // Process accessorieslist
                                        if (is_array($accessorieslist)) {
                                            foreach ($accessorieslist as $item) {
                                                if (isset($item['col4']) && is_numeric(str_replace(',', '', $item['col4']))) {
                                                    $sales = (float)str_replace(',', '', $item['col4']);
                                                    $total_sales += $sales;
                                                }
                                                if (isset($item['col2'])) {
                                                    $value = (float)str_replace(',', '', $item['col2']);
                                                    $accesorieSum += $value;
                                                }
                                            }
                                        }

                                        if (!isset($contractorChartData[$contractor][$month])) {
                                            $contractorChartData[$contractor][$month] = 0;
                                        }
                                        $contractorChartData[$contractor][$month] += $total_sales * 1.1;

                                        if (!isset($contractorItemsData[$contractor][$month])) {
                                            $contractorItemsData[$contractor][$month] = [
                                                'motorSum' => 0,
                                                'bracketSum' => 0,
                                                'controllerSum' => 0,
                                                'fabricSum' => 0,
                                                'accesorieSum' => 0
                                            ];
                                        }
                                        $contractorItemsData[$contractor][$month]['motorSum'] += $motorSum;
                                        $contractorItemsData[$contractor][$month]['bracketSum'] += $bracketSum;
                                        $contractorItemsData[$contractor][$month]['controllerSum'] += $controllerSum;
                                        $contractorItemsData[$contractor][$month]['fabricSum'] += $fabricSum;
                                        $contractorItemsData[$contractor][$month]['accesorieSum'] += $accesorieSum;

                                        if (!isset($totalSalesByContractor[$contractor])) {
                                            $totalSalesByContractor[$contractor] = 0;
                                        }
                                        $totalSalesByContractor[$contractor] += $total_sales;
                                    }

                                    foreach ($contractorChartData as $contractor => $monthsData) {
                                        if ($totalSalesByContractor[$contractor] == 0) {
                                            unset($contractorChartData[$contractor]);
                                        }
                                    }

                                    uasort($totalSalesByContractor, function ($a, $b) {
                                        return $b - $a;
                                    });

                                } catch (PDOException $Exception) {
                                    echo "오류: " . $Exception->getMessage();
                                }

                                $counter = 1;
                                ?>

                                <div class="container">
                                    <?php foreach ($totalSalesByContractor as $contractor => $totalSales): ?>
                                        <?php if (isset($contractorChartData[$contractor])): ?>
                                            <div class="row">
                                                <div class="card mb-2 mt-2">
                                                    <div class="card-body">
                                                        <div class="col-sm-12">
                                                            <div class="d-flex p-1 mt-4 mb-4 justify-content-center align-items-center">
                                                                <h4 class="mt-3"><?= $counter ?>위 <?= htmlspecialchars($contractor) ?></h4>
                                                            </div>
                                                        </div>
                                                        <div class="row d-flex p-1 m-1 mt-1 justify-content-center align-items-center">
                                                            <div class="col-sm-5">
                                                                <div class="card mb-2 mt-2">
                                                                    <div class="card-body">
                                                                        <div class="d-flex p-1 m-1 mt-1 justify-content-center align-items-center">
                                                                            <div id="chart-<?= htmlspecialchars($contractor) ?>" style="height: 400px;"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-7">
                                                                <div class="card mb-2 mt-2">
                                                                    <div class="card-body">
                                                                        <div class="d-flex p-1 m-1 mt-1 justify-content-center align-items-center">
                                                                            <table class="table table-hover">
                                                                                <thead class="table-success">
                                                                                    <tr>
                                                                                        <th class="text-center">월</th>
                                                                                        <th class="text-center">모터</th>
                                                                                        <th class="text-center">브라켓</th>
                                                                                        <th class="text-center">연동제어기</th>
                                                                                        <th class="text-center">원단(단위:M)</th>
																						  <th class="text-center">부속기타</th>
                                                                                        <th class="text-end">매출금액</th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                    <?php 
                                                                                    $totalSum = 0;
                                                                                    $totalMotorSum = 0;
                                                                                    $totalBracketSum = 0;
                                                                                    $totalControllerSum = 0;
                                                                                    $totalFabricSum = 0;
                                                                                    $totalAccesorieSum = 0;
                                                                                    foreach ($contractorChartData[$contractor] as $month => $total_sales): 
                                                                                        $totalSum += $total_sales;
                                                                                        $totalMotorSum += $contractorItemsData[$contractor][$month]['motorSum'];
                                                                                        $totalBracketSum += $contractorItemsData[$contractor][$month]['bracketSum'];
                                                                                        $totalControllerSum += $contractorItemsData[$contractor][$month]['controllerSum'];
                                                                                        $totalFabricSum += $contractorItemsData[$contractor][$month]['fabricSum'];
                                                                                        $totalAccesorieSum += $contractorItemsData[$contractor][$month]['accesorieSum'];
                                                                                    ?>
                                                                                    <tr>
                                                                                        <td class="text-center fw-bold"><?= $month ?></td>
                                                                                        <td class="text-center"><?= number_format($contractorItemsData[$contractor][$month]['motorSum']) ?></td>
                                                                                        <td class="text-center"><?= number_format($contractorItemsData[$contractor][$month]['bracketSum']) ?></td>
                                                                                        <td class="text-center"><?= number_format($contractorItemsData[$contractor][$month]['controllerSum']) ?></td>
                                                                                        <td class="text-center"><?= number_format($contractorItemsData[$contractor][$month]['fabricSum']) ?></td>
                                                                                        <td class="text-center"><?= number_format($contractorItemsData[$contractor][$month]['accesorieSum']) ?></td>
                                                                                        <td class="text-end">￦<?= number_format($total_sales) ?></td>
                                                                                    </tr>
                                                                                    <?php endforeach; ?>
                                                                                    <tr class="font-weight-bold">
                                                                                        <td class="text-center fw-bold">합계</td>
                                                                                        <td class="text-center fw-bold"><?= number_format($totalMotorSum) ?></td>
                                                                                        <td class="text-center fw-bold"><?= number_format($totalBracketSum) ?></td>
                                                                                        <td class="text-center fw-bold"><?= number_format($totalControllerSum) ?></td>
                                                                                        <td class="text-center fw-bold"><?= number_format($totalFabricSum) ?></td>
                                                                                        <td class="text-center fw-bold"><?= number_format($totalAccesorieSum) ?></td>
                                                                                        <td class="text-end fw-bold">￦<?= number_format($totalSum) ?></td>
                                                                                    </tr>
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <script>
                                            Highcharts.chart('chart-<?= htmlspecialchars($contractor) ?>', {
                                                chart: {
                                                    type: 'column'
                                                },
                                                title: {
                                                    text: ''
                                                },
                                                xAxis: {
                                                    categories: <?= json_encode(array_keys($contractorChartData[$contractor])) ?>
                                                },
                                                yAxis: {
                                                    title: {
                                                        text: '매출액 (원)'
                                                    }
                                                },
                                                series: [{
                                                    name: '매출액',
                                                    data: <?= json_encode(array_values($contractorChartData[$contractor])) ?>
                                                }]
                                            });
                                            </script>
                                            <?php $counter++; ?>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="container mt-3 mb-3">
    <? include '../footer_sub.php'; ?>
</div>

</body>
</html>

<!-- 페이지 로딩 -->
<script>
$(document).ready(function(){
    var loader = document.getElementById('loadingOverlay');
	if(loader) 
		loader.style.display = 'none';
});
</script>

<!-- 기간설정 -->
<script>
$(document).ready(function() {
    var savedDateRange = getCookie('dateRange');
    if (savedDateRange) {
        $('#dateRange').val(savedDateRange);
    }

    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        var tabId = $(e.target).attr('id');
        $('#active_tab').val(tabId.split('-')[0]);
    });    
});

$(document).ready(function(){
	saveLogData('DH모터 매출 통계'); 
});
</script>
