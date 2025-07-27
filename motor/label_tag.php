<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/session.php');

if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php");
    exit;
}

include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';

$title_message = 'DH모터 출고';
$tablename = 'motor';

if (isset($_POST['recordIds'])) {
    $recordIds = $_POST['recordIds'];
} else {
    die('Invalid request.');
}

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();
?>

<style>
    table, th, td {
        border: 1px solid black !important; /* 굵은 테두리 적용 */
        font-size: 22px !important;
    }
    @media print {
        body {
            width: 210mm; /* Approx width of A4 paper */
            height: 297mm; /* Height of A4 paper */
            margin: 5mm; /* Provide a margin */
            font-size: 10pt; /* Reduce font size for printing */
        }
        .table {
            width: 100%; /* Full width tables */
            table-layout: fixed; /* Uniform column sizing */
        }
        .table th, .table td {
            padding: 2px; /* Reduce padding */
            border: 1px solid #ddd; /* Ensure borders are visible */
        }
        .text-center {
            text-align: center; /* Maintain center alignment */
        }
        .fw-bold {
            font-weight: bold; /* Ensure bold text is printed */
        }
    }
</style>

</head>

<title> <?=$title_message?> </title>

<body>

<html lang="ko">

<div class="container mt-2">
    <div class="d-flex align-items-center justify-content-end mt-1 m-2">        
        <button class="btn btn-dark btn-sm me-1" onclick="generatePDF()"> PDF 저장 </button>
        <button class="btn btn-secondary btn-sm" onclick="self.close();"> <i class="bi bi-x-lg"></i> 닫기 </button>&nbsp;
    </div>
</div>

<div id="content-to-print">
    <br>
    <div class="container-fluid mt-3">
        <div class="d-flex align-items-center justify-content-center mb-1 m-2">
            <table class="table table-hover">
                <tbody>
                    <?php
					$counter = 0; // counter 변수 초기화
                    foreach ($recordIds as $num) {
                        try {
                            $sql = "SELECT * FROM " . $DB . ".motor WHERE num = ?";
                            $stmh = $pdo->prepare($sql);
                            $stmh->bindValue(1, $num, PDO::PARAM_STR);
                            $stmh->execute();
                            $count = $stmh->rowCount();
                            if ($count < 1) {
                                print "검색결과가 없습니다.<br>";
                            } else {
                                $row = $stmh->fetch(PDO::FETCH_ASSOC);
                                include "_row.php";

                                if ($orderdate != "0000-00-00" && $orderdate != "1970-01-01" && $orderdate != "") $orderdate = date("Y-m-d", strtotime($orderdate));
                                else $orderdate = "";
                                if ($deadline != "0000-00-00" && $deadline != "1970-01-01" && $deadline != "") $deadline = date("Y-m-d", strtotime($deadline));
                                else $deadline = "";
                                if ($outputdate != "0000-00-00" && $outputdate != "1970-01-01" && $outputdate != "") $outputdate = date("Y-m-d", strtotime($outputdate));
                                else $outputdate = "";
                                if ($demand != "0000-00-00" && $demand != "1970-01-01" && $demand != "") $demand = date("Y-m-d", strtotime($demand));
                                else $demand = "";

                                $num_arr[$counter] = $num;
                                $deadline_arr[$counter] = $deadline;
                                $outputdate_arr[$counter] = $outputdate;
                                $workplacename_arr[$counter] = $workplacename;
                                $secondord_arr[$counter] = $secondord;
                                $address_arr[$counter] = $address;
                                $loadplace_arr[$counter] = $loadplace;
                                $deliverymethod_arr[$counter] = $deliverymethod;
                                $chargedman_arr[$counter] = $chargedman;
                                $chargedmantel_arr[$counter] = $chargedmantel;
                                $comment_arr[$counter] = $comment;
                                $delipay_arr[$counter] = $delipay;
                                $delwrapmethod_arr[$counter] = $delwrapmethod;
                                $delwrapsu_arr[$counter] = $delwrapsu;
                                $delwrapamount_arr[$counter] = $delwrapamount;
                                $delwrapweight_arr[$counter] = $delwrapweight;
                                $delwrappaymethod_arr[$counter] = $delwrappaymethod;

                                if ($deliverymethod == '택배') {
                                    $address_arr[$counter] = $address;
                                } else if (strpos($deliverymethod, '화물') !== false) {
                                    $address_arr[$counter] = $delbranch;
                                }

                                // 주자재 합계 문자열 생성
                                $contentslist = '';
                                $firstItemAdded = false;

                                $items = [
                                    'realscreensu' => '모터',
                                    'realsteelsu' => '모터',
                                    'realprotectsu' => '모터',
                                    'realsmokesu' => '모터',
                                    'realexplosionsu' => '모터'
                                ];

                                foreach ($items as $key => $value) {
                                    if (!empty($row[$key])) {
                                        if (!$firstItemAdded) {
                                            $contentslist .= ' ';
                                            $firstItemAdded = true;
                                        }
                                        $contentslist .= $value . '' . ', ';
                                    }
                                }

                                // 부자재 합계 문자열 생성
                                $accessories = json_decode($accessorieslist, true);
                                $accessorieslist = '';
                                $firstAccessory = true;

                                foreach ($accessories as $accessory) {
                                    if ($firstAccessory) {
                                        $accessorieslist .= ' ';
                                        $firstAccessory = false;
                                    }
                                    $accessorieslist .= $accessory['col2'] . ', ';
                                }

                                // 마지막 쉼표 제거
                                $accessorieslist = rtrim($accessorieslist, ', ');
                                $chargedmantel = trim($chargedmantel, ' ');

                                $contentslist = $contentslist . " " . $accessorieslist;

                                $contentslist_arr[$counter] = $contentslist;

                                // 새로운 출력 로직
                                if ($counter % 2 == 0) {  // 새로운 행을 시작하는 조건
                                    if ($counter > 0) {
                                        print '</tr>';  // 이전 행을 닫기
                                    }
                                    print '<tr>';  // 새로운 행 시작
                                }

                                // 데이터 출력
                                print ' <td class="text-center align-middle" style="width:450px; height:120px;"> 받는분 : ' . $chargedman_arr[$counter] . ' <br> ';
                                print ' 연락처 : ' . $chargedmantel . ' </td>';

                                $counter++;
                            }
                        } catch (PDOException $Exception) {
                            print "오류: " . $Exception->getMessage();
                        }
                    }
                    if ($counter % 2 != 0) {
                        print '</tr>';  // 마지막 행 닫기
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div> <!-- end of content-to-print -->

</body>

</html>

<!-- 페이지로딩 -->
<script>
$(document).ready(function() {
    var loader = document.getElementById('loadingOverlay');
    loader.style.display = 'none';
});
</script>

<script>
function generatePDF() {
    var workplace = '택배화물';
    var d = new Date();
    var currentDate = (d.getMonth() + 1) + "-" + d.getDate() + "_";
    var currentTime = d.getHours() + "_" + d.getMinutes() + "_" + d.getSeconds();
    var result = 'DH모터 (' + workplace + ')' + currentDate + currentTime + '.pdf';

    var element = document.getElementById('content-to-print');
    var opt = {
        margin: 0,
        filename: result,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
    };
    html2pdf().from(element).set(opt).save();
}

</script>
