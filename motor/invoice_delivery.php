<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");

// 첫 화면 표시 문구
$title_message = 'DH모터 화물택배';
$tablename = 'motor';

if (isset($_POST['recordIds'])) {
    $recordIds = $_POST['recordIds'];
} else {
    die('Invalid request.');
}

// echo '<pre>';
// print_r($recordIds);
// echo '</pre>';

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();
?>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php'; ?>

<title> <?=$title_message?> </title>

<style>
    .smallfont {
        border: 0.5px solid #ccc !important;
        font-size: 11px !important;
        padding: 1px;
    }
    table, th {
        border: 0.5px solid #ccc !important;
        font-size: 15px !important;
        padding: 0px;
    }
    @media print {
        body {
            width: 210mm; /* Approx width of A4 paper */
            height: 297mm; /* Height of A4 paper */
            margin: 5mm; /* Provide a margin */
            font-size: 10pt; /* Reduce font size for printing */
        }
        .table th, .table td {
            padding: 1px;
        }
        .text-center {
            text-align: center;
        }
    }
    td {
        padding-top: 1px;
        padding-bottom: 1px;
        border: 0.5px solid #ccc !important; /* 가늘고 옅은 회색 테두리 */
        font-size: 14px !important;
        padding-left: 1px; /* 좌측 여백 */
        padding-right: 1px; /* 우측 여백 */
    }
    .pagebreak { page-break-before: always; }
</style>

<div class="container mt-2">
    <div class="d-flex align-items-center justify-content-end mt-1 m-2">
        <button class="btn btn-dark btn-sm me-1" onclick="generatePDF()"> PDF 저장 </button>
        <button class="btn btn-secondary btn-sm" onclick="self.close();"> <i class="bi bi-x-lg"></i> 닫기 </button>&nbsp;
    </div>
</div>

<div id="content-to-print">
                <div class="container-fluid mt-2">
                    <div class="d-flex align-items-center justify-content-center mt-2 mb-2 ">
                        <h3> DH 모터 금일 출고(화물,택배) &nbsp; &nbsp; &nbsp; 발송처 : (주)대한  010-3966-2024</h3>
                    </div>
                    <div class="d-flex align-items-center justify-content-center mb-1 ">
                        <table class="table table-hover">
                            <thead class="table-primary">
                                <tr>
								<th class="text-center align-middle" style="width:100px;" >발주처</th>
								<th class="text-center align-middle" style="width:100px;" >받는분</th>
								<th class="text-center align-middle" style="width:160px;">연락처</th>
								<th class="text-center align-middle" style="width:200px;">도착지</th>            
								<th class="text-center align-middle" style="width:200px;">제품명</th>
								<th class="text-center align-middle" style="width:50px;" >포장</th>
								<th class="text-center align-middle" style="width:50px;">배송</th>
								<th class="text-center align-middle" style="width:50px;">수량</th>								
								<th class="text-center align-middle"  style="width:150px;" >운임</th>   
                                </tr>
                            </thead>
                            <tbody>
    <?php
    foreach ($recordIds as $num) {
        try {
            $sql = "select * from " . $DB . ".motor where num = ?";
            $stmh = $pdo->prepare($sql);
            $stmh->bindValue(1, $num, PDO::PARAM_STR);
            $stmh->execute();
            $count = $stmh->rowCount();
            if ($count < 1) {
                print "검색결과가 없습니다.<br>";
            } else {
                $row = $stmh->fetch(PDO::FETCH_ASSOC);
                include "_row.php";											
                       
            // 1) 삼항 연산자 한 줄 버전 (PHP 5.3+)
            $finalAddress = !empty($delbranch)
                ? $delbranch
                : (!empty($delbranchaddress)
                    ? $delbranchaddress
                    : $address);

            // 2) if/elseif 구문 버전
            if (!empty($delbranch)) {
                $finalAddress = $delbranch;
            } elseif (!empty($delbranchaddress)) {
                $finalAddress = $delbranchaddress;
            } else {
                $finalAddress = $address;
            }		  
            // 상차지에 화물지점표기함 
            $address = $finalAddress ;            
				
				// $deliverymethod = str_replace('경동','',$deliverymethod);
                if ($orderdate != "0000-00-00" && $orderdate != "1970-01-01" && $orderdate != "") $orderdate = date("Y-m-d", strtotime($orderdate));
                else $orderdate = "";
                if ($deadline != "0000-00-00" && $deadline != "1970-01-01" && $deadline != "") $deadline = date("Y-m-d", strtotime($deadline));
                else $deadline = "";
                if ($outputdate != "0000-00-00" && $outputdate != "1970-01-01" && $outputdate != "") $outputdate = date("Y-m-d", strtotime($outputdate));
                else $outputdate = "";
                if ($demand != "0000-00-00" && $demand != "1970-01-01" && $demand != "") $demand = date("Y-m-d", strtotime($demand));
                else $demand = "";

                $contentslist = '';
                $firstItemAdded = false;

                $items = [
                    'realscreensu' => '스크린M',
                    'realsteelsu' => '철재M',
                    'realprotectsu' => '방범M',
                    'realsmokesu' => '제연M',
                    'realexplosionsu' => '방폭M'
                ];
				
                foreach ($items as $key => $value) {
                    if (!empty($row[$key])) {
                        if (!$firstItemAdded) {
                            $contentslist .= '<span class="badge bg-primary"> 모,브 </span> ';
                            $firstItemAdded = true;
                        }
                        $contentslist .= $value . ' ' . $row[$key] . 'EA, ';
                    }
                }

                $contentslist = rtrim($contentslist, ', ');

                $conses = json_decode($controllerlist, true);
                $controllerlist = '';
                $firstAccessory = true;

                foreach ($conses as $cons) {
                    if ($firstAccessory) {
                        $controllerlist .= '<span class="badge bg-success"> 연동 </span>  ';
                        $firstAccessory = false;
                    }
                    $controllerlist .= $cons['col2'] . ':' . $cons['col3'] . 'EA, ';
                }

                $controllerlist = rtrim($controllerlist, ', ');

                $accessories = json_decode($accessorieslist, true);
                $accessorieslist = '';
                $firstAccessory = true;

                foreach ($accessories as $accessory) {
                    if ($firstAccessory) {
                        $accessorieslist .= '<span class="badge bg-secondary"> 부속 </span>  ';
                        $firstAccessory = false;
                    }
                    $accessorieslist .= $accessory['col1'] . ':' . $accessory['col2'] . 'EA, ';
                }

                $accessorieslist = rtrim($accessorieslist, ', ');

                $finalList = '';
                if (!empty($contentslist)) {
                    $finalList .= $contentslist . '<br>';
                }
                if (!empty($controllerlist)) {
                    $finalList .= $controllerlist . '<br>';
                }
                if (!empty($accessorieslist)) {
                    $finalList .= $accessorieslist . '<br>';
                }

                $finalList = rtrim($finalList, '<br>');

                $contentslist = $finalList;
				
				
				
                ?>

					<tr onclick="handleRowClick('<?=$num?>')" >
					<td class="text-center align-middle"><?= htmlspecialchars($secondord) ?></td>
					<td class="text-center align-middle"><?= htmlspecialchars($chargedman) ?></td>
					<td class="text-start align-middle"><?= htmlspecialchars(trim($chargedmantel)) ?></td>
					<td class="text-start align-middle"><?= htmlspecialchars($address) ?></td>
					<td class="text-start align-middle"><?= $contentslist ?></td>
					<td class="text-center align-middle"><?= htmlspecialchars($cargo_delwrapmethod) ?></td>					
					<?php
					if ($deliverymethod == '선/대신화물' || $deliverymethod == '착/대신화물' ) {
							echo '<td class="text-center align-middle"><span class="badge bg-danger">' . $deliverymethod . '</span></td>';
						} else if ($deliverymethod == '선/경동화물' || $deliverymethod == '착/경동화물') {
							echo '<td class="text-center align-middle"><span class="badge bg-primary">' . $deliverymethod . '</span></td>';
						} else if ($deliverymethod == '배차') {
							echo '<td class="text-center align-middle"><span class="badge bg-success">' . $deliverymethod . '[' . $delcompany . ']</span></td>';
						} else {
							echo '<td class="text-center align-middle">' . $deliverymethod . '</td>';
						}
					?>		
					<td class="text-center align-middle"><?= htmlspecialchars($cargo_delwrapsu) ?></td>                                    
					<td class="text-center align-middle"><?= htmlspecialchars($cargo_delwrapamount) ?></td>
				</tr>

                <?php
            }
        } catch (PDOException $Exception) {
            print "오류: " . $Exception->getMessage();
        }
    }
    ?>	
		   </tbody>
		</table>
	</div>
	</div>	
</div> <!-- end of content-to-print -->

   <div class="pagebreak"></div>
<script>
$(document).ready(function() {
    var loader = document.getElementById('loadingOverlay');
	if(loader)
		loader.style.display = 'none';
});

function generatePDF() {
    var d = new Date();
    var currentDate = (d.getMonth() + 1) + "-" + d.getDate() + "_";
    var currentTime = d.getHours() + "_" + d.getMinutes() + "_" + d.getSeconds();
    var result = 'DH모터 화물택배_' + currentDate + currentTime + '.pdf';

    var element = document.getElementById('content-to-print');
    var opt = {
        margin: 0,
        filename: result,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'in', format: 'letter', orientation: 'landscape' }
    };
    html2pdf().from(element).set(opt).save();
}

function handleRowClick(row) {
    
    if (row !== '') {
        var link = 'write_form.php?mode=view&num=' + row;
        customPopup(link, '수주내역', 1850, 900);
    }
}

</script>
