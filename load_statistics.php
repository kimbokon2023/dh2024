<!-- chartjs는 canvas로 태그 설정하고 highcharts는 div로 한다. 주의 -->
<div id="salesChart" style="height: 200px;"> </div>
<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  
$tablename = 'motor';    

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

// $fromdate = isset($_REQUEST['fromdate']) && $_REQUEST['fromdate'] ? $_REQUEST['fromdate'] : date("Y") . "-01-01";
// $todate = isset($_REQUEST['todate']) && $_REQUEST['todate'] ? $_REQUEST['todate'] : date("Y") . "-12-31";
// $transtodate = date("Y-m-d", strtotime($todate . '+1 day'));

// 현재부터 1년 후
// $fromdate = isset($_REQUEST['fromdate']) && $_REQUEST['fromdate'] 
    // ? $_REQUEST['fromdate'] 
    // : date("Y-m-d");

// $todate = isset($_REQUEST['todate']) && $_REQUEST['todate'] 
    // ? $_REQUEST['todate'] 
    // : date("Y-m-d", strtotime("+1 year -1 day"));

// $transtodate = date("Y-m-d", strtotime($todate . ' +1 day'));

// 현재부터 1년전까지 기간 찾기
$fromdate = isset($_REQUEST['fromdate']) && $_REQUEST['fromdate'] 
    ? $_REQUEST['fromdate'] 
    : date("Y-m-d", strtotime("-1 year"));

$todate = isset($_REQUEST['todate']) && $_REQUEST['todate'] 
    ? $_REQUEST['todate'] 
    : date("Y-m-d");

$transtodate = date("Y-m-d", strtotime($todate . ' +1 day'));


// SQL 쿼리
$orderby = "ORDER BY outputdate DESC"; 
$sql = "SELECT * FROM " . $DB . "." . $tablename . " WHERE outputdate BETWEEN date('$fromdate') AND date('$transtodate') AND is_deleted IS NULL " . $orderby; 

try {
    $stmh = $pdo->prepare($sql);
    $stmh->execute();
    $rows = $stmh->fetchAll(PDO::FETCH_ASSOC);

    $chartData = [];
	foreach ($rows as $row) {
		$orderlist = json_decode($row['orderlist'], true);
		$accessorieslist = json_decode($row['accessorieslist'], true);
		$controllerlist = json_decode($row['controllerlist'], true);		
		$fabriclist = json_decode($row['fabriclist'], true);
		
		$month = date("Y-m", strtotime($row['outputdate']));
		if (!isset($chartData[$month])) {
			$chartData[$month] = 0;
		}
        if (is_array($orderlist)) {						
			foreach ($orderlist as $item) {
				if (isset($item['col12'])) {
					$amount = str_replace(',', '', $item['col12']); // Remove commas
					if (is_numeric($amount)) { // Ensure the value is numeric
						$chartData[$month] += $amount * 1.1;
					}
				}
			}
		}
        if (is_array($accessorieslist)) {				
			foreach ($accessorieslist as $item) {
				if (isset($item['col4'])) {
					$amount = str_replace(',', '', $item['col4']); // Remove commas
					if (is_numeric($amount)) { // Ensure the value is numeric
						$chartData[$month] += $amount* 1.1;
					}
				}
			}
		}
        if (is_array($controllerlist)) {		
			foreach ($controllerlist as $item) {
				if (isset($item['col7'])) {
					$amount = str_replace(',', '', $item['col7']); // Remove commas
					if (is_numeric($amount)) { // Ensure the value is numeric
						$chartData[$month] += $amount* 1.1;
					}
				}
			}
		}
        if (is_array($fabriclist)) {		
			foreach ($fabriclist as $item) {
				if (isset($item['col9'])) {
					$amount = str_replace(',', '', $item['col9']); // Remove commas
					if (is_numeric($amount)) { // Ensure the value is numeric
						$chartData[$month] += $amount* 1.1;
					}
				}
			}
		}
	}

    $jsonChartData = json_encode($chartData);

} catch (PDOException $Exception) {
    print "오류: ".$Exception->getMessage();
}  
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const salesData = <?= $jsonChartData ?>;	
	// console.log(salesData);    
    // 원래의 labels를 날짜로 파싱하여 정렬
    const sortedLabels = Object.keys(salesData).sort((a, b) => new Date(a) - new Date(b));
    const sortedData = sortedLabels.map(label => parseFloat(salesData[label]));               // 정렬된 레이블에 맞게 데이터 재구성

	  Highcharts.chart('salesChart', {
		chart: {
			type: 'column'
		},
		title: {
			text: '지난 12개월 매출 추이'
		},
		xAxis: {
			categories: sortedLabels,
			crosshair: true,
			labels: {
				formatter: function() {
					const date = new Date(this.value);
					// return (date.getMonth() + 1) + '/' + date.getDate() ;
					return (date.getMonth() + 1) + '월' ;
				}
			}
		},
		yAxis: {
			min: 0,
			title: {
				text: '매출액(원)'
			}
		},
		tooltip: {
			headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
			pointFormatter: function() {
				return '<tr><td style="color:' + this.series.color + ';padding:0">' + this.series.name + ': </td>' +
					'<td style="padding:0;"><b>' + Highcharts.numberFormat(this.y, 0, '.', ',') + ' 원</b></td></tr>';
			},
			footerFormat: '</table>',
			shared: true,
			useHTML: true,
			style: {
				padding: '1px',   // 툴팁 내부 패딩 조정
				minWidth: '200px' // 최소 툴팁 너비 설정
			}
		},
		plotOptions: {
			column: {
				pointPadding: 0.1,
				borderWidth: 1
			}
		},
		series: [{
			name: '매출',
			data: sortedData
		}]
	});
});
</script>

