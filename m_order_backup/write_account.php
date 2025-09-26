<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if (!isset($_SESSION["level"]) || $_SESSION["level"] > 5) {
    sleep(1);
    header("Location:" . $WebSite . "login/login_form.php"); 
    exit;
}

include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';

$mode = $_REQUEST['mode'] ?? '';  
$num =  $_REQUEST["num"] ?? '';

$title_message = "발주 입고차수별 송금액  ";
$tablename = 'm_order';

?> 

<link href="css/style.css" rel="stylesheet">    
<title> <?=$title_message?> </title>

<style>
    .hidden {
        display: none;
    }
    .scrollable-modal-body {
        max-height: 500px;
        overflow-y: auto;
    }

    .hidden {
        display: none;
    }
    .expanded {
        /* 예시: 남은 공간을 꽉 채우도록 처리 (필요에 따라 수정) */
        width: auto;
    }
</style>
</head>
<body>

<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

$todate = date("Y-m-d"); // 현재일자 변수지정
$todayDate = date("Y-m-d"); // 현재일자 

if ($mode == "modify" || $mode == "view") {
    try {
        $sql = "select * from m_order where num = ?";
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $num, PDO::PARAM_STR);
        $stmh->execute();
        $count = $stmh->rowCount();
        if ($count < 1) {
            print "검색결과가 없습니다.<br>";
        } else {
            $row = $stmh->fetch(PDO::FETCH_ASSOC);
            include "_row.php";

        // 차수별 입고일 초기화
        $inputDate1 = '';
        $inputDate2 = '';
        $inputDate3 = '';
        $inputDate4 = '';
        $inputDate5 = '';
        $inputDate6 = '';
        $inputDate7 = '';

        // JSON 디코딩
        $orderlist = json_decode($row['orderlist'] ?? '[]', true);

        foreach ($orderlist as $item) {
            // 1차 입고일 (col7)
            if (empty($inputDate1) 
                && !empty($item['col7']) 
                && $item['col7'] !== '0000-00-00'
            ) {
                $inputDate1 = $item['col7'];
            }
            // 2차 입고일 (col10)
            if (empty($inputDate2) 
                && !empty($item['col10']) 
                && $item['col10'] !== '0000-00-00'
            ) {
                $inputDate2 = $item['col10'];
            }
            // 3차 입고일 (col13)
            if (empty($inputDate3) 
                && !empty($item['col13']) 
                && $item['col13'] !== '0000-00-00'
            ) {
                $inputDate3 = $item['col13'];
            }
            // 4차 입고일 (col16)
            if (empty($inputDate4) 
                && !empty($item['col16']) 
                && $item['col16'] !== '0000-00-00'
            ) {
                $inputDate4 = $item['col16'];
            }
            // 5차 입고일 (col19)
            if (empty($inputDate5) 
                && !empty($item['col19']) 
                && $item['col19'] !== '0000-00-00'
            ) {
                $inputDate5 = $item['col19'];
            }
            // 6차 입고일 (col22)
            if (empty($inputDate6) 
                && !empty($item['col22']) 
                && $item['col22'] !== '0000-00-00'
            ) {
                $inputDate6 = $item['col22'];
            }
                // 7차 입고일 (col25)
            if (empty($inputDate7) 
                && !empty($item['col25']) 
                && $item['col25'] !== '0000-00-00'
            ) {
                $inputDate7 = $item['col25'];
            }

            // 네 개 모두 채워졌으면 더 이상 반복할 필요 없음
            if ($inputDate1 && $inputDate2 && $inputDate3 && $inputDate4) {
                break;
            }
        }	


        }
    } catch (PDOException $Exception) {
        print "오류: " . $Exception->getMessage();
    }
}

if ($mode !== "modify" && $mode !== "copy" && $mode !== "split" && $mode !== "view") {
    include '_request.php';
    $first_writer = $user_name;
    $orderDate = $todate;
}

if ($mode == "copy" || $mode == 'split') {
    try {
        $sql = "select * from m_order where num = ?";
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $num, PDO::PARAM_STR);
        $stmh->execute();
        $count = $stmh->rowCount();
        if ($count < 1) {
            print "검색결과가 없습니다.<br>";
        } else {
            $row = $stmh->fetch(PDO::FETCH_ASSOC);
        }
        include '_row.php';
    } catch (PDOException $Exception) {
        print "오류: " . $Exception->getMessage();
    }
    $num = 0;
    $orderDate = $todate;
    $mode = "insert";
}
?>

<form id="board_form" name="board_form" method="post" enctype="multipart/form-data" onkeydown="return captureReturnKey(event)">
    <input type="hidden" id="first_writer" name="first_writer" value="<?= isset($first_writer) ? $first_writer : '' ?>">
    <input type="hidden" id="update_log" name="update_log" value="<?= isset($update_log) ? $update_log : '' ?>">
    <input type="hidden" id="num" name="num" value="<?= isset($num) ? $num : '' ?>">
    <input type="hidden" id="tablename" name="tablename" value="<?= isset($tablename) ? $tablename : '' ?>">
    <input type="hidden" id="mode" name="mode" value="<?= isset($mode) ? $mode : '' ?>">
	
    <input type="hidden" id="orderlist" name="orderlist">	     
	
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/m_order/modal.php'; ?>
	
    <div class="container">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-center align-items-center mt-3 mb-5">
                    <span class="fs-5 me-5"> <?=$title_message?> (<?=$mode?>) </span>
                    <?php if ($mode !== 'view') { ?>
                        <button type="button" class="btn btn-dark btn-sm me-2 saveBtn"> <i class="bi bi-floppy-fill"></i> 저장 </button>
                    <?php } else { ?>
						<button type="button" class="btn btn-dark btn-sm mx-2"      onclick='location.reload();' > <i class="bi bi-arrow-clockwise"></i> </button>  
                        <button type="button" class="btn btn-dark btn-sm me-1"      onclick="location.href='write_account.php?mode=modify&num=<?=$num?>';">  <i class="bi bi-pencil-square"></i>  수정  </button>                        
                        <button type="button" class="btn btn-secondary btn-sm me-1" onclick="generateExcel();"> Excel 저장 </button>
                    <?php } ?>
                    &nbsp;&nbsp;
                    최초 : <?=$first_writer?>
                    <br>
                    <?php $update_log_extract = substr($update_log, 0, 31); ?>
                    &nbsp;&nbsp; 수정 : <?=$update_log_extract?> &nbsp;&nbsp;&nbsp;
                    <span class="text-end" style="width:10%;">
                        <button type="button" class="btn btn-outline-dark btn-sm me-2" id="showlogBtn"> H </button>
                        <button class="btn btn-secondary btn-sm" onclick="self.close();"> &times; 닫기 </button> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    </span>
                </div>                                    
                    <div class="d-flex justify-content-center">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td class="text-end" style="width:80px;"> 구매발주일 </td>
                                    <td class="w150px">
                                        <div class="d-flex align-items-center justify-content-start">
                                            <input type="date" name="orderDate" id="orderDate" value="<?=$orderDate?>" class="form-control" style="width:100px;" readonly>
                                        </div>
                                    </td>
									<td class="text-end" style="width:80px;"> 비고 </td>
                                    <td>                                       
                                        <textarea name="memo" id="memo" class="form-control text-start" style="height:20px;"><?=$memo?></textarea>                                        
                                    </td>
                                </tr>
                            </tbody>
                        </table>
					<div class="alert alert-primary mx-3 w-25" role="alert">
						단가, 금액은 위엔화(CNY) 기준입니다.
					</div>							
				</div>   
				
            </div> <!-- end of card body -->
        </div>
    </div>

<div class="container-fluid">			
    <div class='d-flex justify-content-center  align-items-center  mt-4 mb-2'>		
        <span class='badge bg-dark fs-6 me-3'> 송금액 </span>
    </div>    
    <div class='table-responsive mt-2 mb-2'>		
    <table class="table table-bordered w-auto align-middle text-center">
        <thead>
            <tr>
                <th colspan="1" class="bg-light fw-bold text-center"> 구분  </th>
                <th colspan="1" class="bg-light fw-bold text-center"> 1차 </th>
                <th colspan="1" class="bg-light fw-bold text-center"> 2차 </th>  
                <th colspan="1" class="bg-light fw-bold text-center"> 3차 </th>  
                <th colspan="1" class="bg-light fw-bold text-center"> 4차 </th>  
                <th colspan="1" class="bg-light fw-bold text-center"> 5차 </th>  
                <th colspan="1" class="bg-light fw-bold text-center"> 6차 </th>  
                <th colspan="1" class="bg-light fw-bold text-center"> 7차 </th>  
                <th colspan="1" class="bg-light fw-bold text-center"> 합계 </th>  
            </tr>
        </thead>
        <tbody>
        <tr>
            <!-- 총 발주수량 -->
            <td class="bg-light fw-bold">총 발주수량</td>
            <td colspan="7" class="text-start">
                <span id="totalsurangDisplay" class="form-control noborder-input text-start text-dark fw-bold fs-6"></span>
            </td>
        </tr>
        <tr>
            <!-- 입고일 -->
            <td class="bg-light fw-bold text-primary">입고일</td>
            <td>
            <span id="inputDate1" class="form-control noborder-input text-center text-primary" readonly> <?= $inputDate1 ?></span>
            </td>            
            <td>
            <span id="inputDate2" class="form-control  noborder-input text-center text-primary" readonly> <?= $inputDate2 ?></span>
            </td>
            <td>
            <span id="inputDate3" class="form-control  noborder-input  text-center text-primary" readonly> <?= $inputDate3 ?></span>
            </td>
            <td>
            <span id="inputDate4" class="form-control   noborder-input text-center text-primary" readonly> <?= $inputDate4 ?></span>
            </td>
            <td>
            <span id="inputDate5" class="form-control   noborder-input text-center text-primary" readonly> <?= $inputDate5 ?></span>
            </td>
            <td>
            <span id="inputDate6" class="form-control   noborder-input text-center text-primary" readonly> <?= $inputDate6 ?></span>
            </td>
            <td>            
            <span id="inputDate7" class="form-control   noborder-input text-center text-primary" readonly> <?= $inputDate7 ?></span>
            </td>
        </tr>
        <tr>
            <!-- 입고금액 -->
            <td class="bg-light fw-bold text-primary">입고금액(CNY)</td>
            <td>
            <span id="inputSumDisplay1" class="form-control noborder-input text-end text-primary fw-bold"></span>
            </td>            
            <td>
            <span id="inputSumDisplay2" class="form-control  noborder-input text-end text-primary fw-bold"></span>
            </td>
            <td>
            <span id="inputSumDisplay3" class="form-control  noborder-input  text-end text-primary fw-bold"></span>
            </td>
            <td>
            <span id="inputSumDisplay4" class="form-control   noborder-input text-end text-primary fw-bold"></span>
            </td>
            <td>
            <span id="inputSumDisplay5" class="form-control   noborder-input text-end text-primary fw-bold"></span>
            </td>
            <td>
            <span id="inputSumDisplay6" class="form-control   noborder-input text-end text-primary fw-bold"></span>
            </td>
            <td>
            <span id="inputSumDisplay7" class="form-control   noborder-input text-end text-primary fw-bold"></span>
            </td>
            <td>
            <span id="totalInputSum" class="form-control noborder-input text-end text-primary fw-bold"></span>
            </td>
        </tr>
        <tr>
            <!-- 3~10열: 송금일 입력 -->			  
            <td class="bg-light">송금일</td>
            <td>
            <input type="date" id="sendDate1" name="sendDate1" class="form-control noborder-input text-center fw-bold  " 
                    value="<?= $sendDate1 ?? '' ?>">
            </td>
            <td>
            <input type="date" id="sendDate2" name="sendDate2" class="form-control noborder-input text-center fw-bold  " 
                    value="<?= $sendDate2 ?? '' ?>">
            </td>
            <td>
            <input type="date" id="sendDate3" name="sendDate3" class="form-control noborder-input text-center fw-bold  " 
                    value="<?= $sendDate3 ?? '' ?>">
            </td>
            <td>
            <input type="date" id="sendDate4" name="sendDate4" class="form-control noborder-input text-center fw-bold  " 
                    value="<?= $sendDate4 ?? '' ?>">
            </td>
            <td>
            <input type="date" id="sendDate5" name="sendDate5" class="form-control noborder-input text-center fw-bold  " 
                    value="<?= $sendDate5 ?? '' ?>">
            </td>
            <td>
            <input type="date" id="sendDate6" name="sendDate6" class="form-control noborder-input text-center fw-bold  " 
                    value="<?= $sendDate6 ?? '' ?>">
            </td>
            <td>
            <input type="date" id="sendDate7" name="sendDate7" class="form-control noborder-input text-center fw-bold  " 
                    value="<?= $sendDate7 ?? '' ?>">
            </td>
            <td>            
            </td>
        </tr>
                
        <tr>			
        <!-- 3~10열: 환율 입력 -->			  
        <td class="bg-light">환율</td>
        <td>
            <input type="text" id="exchange_rate1" name="exchange_rate1" class="form-control noborder-input text-end fw-bold text-dark" 
                    value="<?= (isset($exchange_rate1) && $exchange_rate1 != 0 && $exchange_rate1 !== '0' && $exchange_rate1 !== 0.0 && $exchange_rate1 !== '0.0') ? number_format(floatval($exchange_rate1),2) : '' ?>"  oninput="formatNumber(this)">
        </td>        
        <td>
            <input type="text" id="exchange_rate2" name="exchange_rate2" class="form-control noborder-input text-end fw-bold text-dark" 
                    value="<?= (isset($exchange_rate2) && $exchange_rate2 != 0 && $exchange_rate2 !== '0' && $exchange_rate2 !== 0.0 && $exchange_rate2 !== '0.0') ? number_format(floatval($exchange_rate2),2) : '' ?>"  oninput="formatNumber(this)">
        </td>        
        <td>    
            <input type="text" id="exchange_rate3" name="exchange_rate3" class="form-control noborder-input text-end fw-bold text-dark" 
                    value="<?= (isset($exchange_rate3) && $exchange_rate3 != 0 && $exchange_rate3 !== '0' && $exchange_rate3 !== 0.0 && $exchange_rate3 !== '0.0') ? number_format(floatval($exchange_rate3),2) : '' ?>"  oninput="formatNumber(this)">
        </td>        
        <td>
            <input type="text" id="exchange_rate4" name="exchange_rate4" class="form-control noborder-input text-end fw-bold text-dark" 
                    value="<?= (isset($exchange_rate4) && $exchange_rate4 != 0 && $exchange_rate4 !== '0' && $exchange_rate4 !== 0.0 && $exchange_rate4 !== '0.0') ? number_format(floatval($exchange_rate4),2) : '' ?>"  oninput="formatNumber(this)">
        </td>
        <td>
            <input type="text" id="exchange_rate5" name="exchange_rate5" class="form-control noborder-input text-end fw-bold text-dark" 
                    value="<?= (isset($exchange_rate5) && $exchange_rate5 != 0 && $exchange_rate5 !== '0' && $exchange_rate5 !== 0.0 && $exchange_rate5 !== '0.0') ? number_format(floatval($exchange_rate5),2) : '' ?>"  oninput="formatNumber(this)">
        </td>
        <td>
            <input type="text" id="exchange_rate6" name="exchange_rate6" class="form-control noborder-input text-end fw-bold text-dark" 
                    value="<?= (isset($exchange_rate6) && $exchange_rate6 != 0 && $exchange_rate6 !== '0' && $exchange_rate6 !== 0.0 && $exchange_rate6 !== '0.0') ? number_format(floatval($exchange_rate6),2) : '' ?>"  oninput="formatNumber(this)">
        </td>
        <td>
            <input type="text" id="exchange_rate7" name="exchange_rate7" class="form-control noborder-input text-end fw-bold text-dark" 
                    value="<?= (isset($exchange_rate7) && $exchange_rate7 != 0 && $exchange_rate7 !== '0' && $exchange_rate7 !== 0.0 && $exchange_rate7 !== '0.0') ? number_format(floatval($exchange_rate7),2) : '' ?>"  oninput="formatNumber(this)">
        </td>
        </tr>
        <tr>			
            <!-- 원화 입력 -->			  
            <td class="bg-light">원화입력(KRW)</td>
            <td>
                <input type="text" id="send_amount_krw1" name="send_amount_krw1" class="form-control noborder-input text-end fw-bold text-dark" 
                        value="<?= (isset($send_amount_krw1) && is_numeric(str_replace(',', '', $send_amount_krw1)) && floatval(str_replace(',', '', $send_amount_krw1)) != 0) ? number_format(floatval(str_replace(',', '', $send_amount_krw1))) : '' ?>" 
                        oninput="this.value=this.value.replace(/[^0-9]/g,'');">
            </td>        
            <td>
                <input type="text" id="send_amount_krw2" name="send_amount_krw2" class="form-control noborder-input text-end fw-bold text-dark" 
                        value="<?= (isset($send_amount_krw2) && is_numeric(str_replace(',', '', $send_amount_krw2)) && floatval(str_replace(',', '', $send_amount_krw2)) != 0) ? number_format(floatval(str_replace(',', '', $send_amount_krw2))) : '' ?>" 
                        oninput="this.value=this.value.replace(/[^0-9]/g,'');">
            </td>        
            <td>
                <input type="text" id="send_amount_krw3" name="send_amount_krw3" class="form-control noborder-input text-end fw-bold text-dark" 
                        value="<?= (isset($send_amount_krw3) && is_numeric(str_replace(',', '', $send_amount_krw3)) && floatval(str_replace(',', '', $send_amount_krw3)) != 0) ? number_format(floatval(str_replace(',', '', $send_amount_krw3))) : '' ?>" 
                        oninput="this.value=this.value.replace(/[^0-9]/g,'');">
            </td>        
            <td>
                <input type="text" id="send_amount_krw4" name="send_amount_krw4" class="form-control noborder-input text-end fw-bold text-dark" 
                        value="<?= (isset($send_amount_krw4) && is_numeric(str_replace(',', '', $send_amount_krw4)) && floatval(str_replace(',', '', $send_amount_krw4)) != 0) ? number_format(floatval(str_replace(',', '', $send_amount_krw4))) : '' ?>" 
                        oninput="this.value=this.value.replace(/[^0-9]/g,'');">
            </td>
            <td>
                <input type="text" id="send_amount_krw5" name="send_amount_krw5" class="form-control noborder-input text-end fw-bold text-dark" 
                        value="<?= (isset($send_amount_krw5) && is_numeric(str_replace(',', '', $send_amount_krw5)) && floatval(str_replace(',', '', $send_amount_krw5)) != 0) ? number_format(floatval(str_replace(',', '', $send_amount_krw5))) : '' ?>" 
                        oninput="this.value=this.value.replace(/[^0-9]/g,'');">
            </td>
            <td>
                <input type="text" id="send_amount_krw6" name="send_amount_krw6" class="form-control noborder-input text-end fw-bold text-dark" 
                        value="<?= (isset($send_amount_krw6) && is_numeric(str_replace(',', '', $send_amount_krw6)) && floatval(str_replace(',', '', $send_amount_krw6)) != 0) ? number_format(floatval(str_replace(',', '', $send_amount_krw6))) : '' ?>" 
                        oninput="this.value=this.value.replace(/[^0-9]/g,'');">
            </td>
            <td>
                <input type="text" id="send_amount_krw7" name="send_amount_krw7" class="form-control noborder-input text-end fw-bold text-dark" 
                        value="<?= (isset($send_amount_krw7) && is_numeric(str_replace(',', '', $send_amount_krw7)) && floatval(str_replace(',', '', $send_amount_krw7)) != 0) ? number_format(floatval(str_replace(',', '', $send_amount_krw7))) : '' ?>" 
                        oninput="this.value=this.value.replace(/[^0-9]/g,'');">
            </td>
            <td>
                <input type="text" id="send_amount_krw_sum" name="send_amount_krw_sum" class="form-control noborder-input text-end fw-bold text-primary" 
                        value="<?= (isset($send_amount_krw_sum) && is_numeric(str_replace(',', '', $send_amount_krw_sum)) && floatval(str_replace(',', '', $send_amount_krw_sum)) != 0) ? number_format(floatval(str_replace(',', '', $send_amount_krw_sum))) : '' ?>" 
                        readonly    >
            </td>

        </tr>
        <tr>			
            <!-- 송금수수료  입력 -->			  
            <td class="bg-light">송금수수료(KRW)</td>
            <td>
                <input type="text" id="remittance_fee1" name="remittance_fee1" class="form-control noborder-input text-end fw-bold text-dark" 
                        value="<?= (isset($remittance_fee1) && is_numeric(str_replace(',', '', $remittance_fee1)) && floatval(str_replace(',', '', $remittance_fee1)) != 0) ? number_format(floatval(str_replace(',', '', $remittance_fee1))) : '' ?>" 
                        oninput="this.value=this.value.replace(/[^0-9]/g,'');">
            </td>        
            <td>
                <input type="text" id="remittance_fee2" name="remittance_fee2" class="form-control noborder-input text-end fw-bold text-dark" 
                        value="<?= (isset($remittance_fee2) && is_numeric(str_replace(',', '', $remittance_fee2)) && floatval(str_replace(',', '', $remittance_fee2)) != 0) ? number_format(floatval(str_replace(',', '', $remittance_fee2))) : '' ?>" 
                        oninput="this.value=this.value.replace(/[^0-9]/g,'');">
            </td>        
            <td>
                <input type="text" id="remittance_fee3" name="remittance_fee3" class="form-control noborder-input text-end fw-bold text-dark" 
                        value="<?= (isset($remittance_fee3) && is_numeric(str_replace(',', '', $remittance_fee3)) && floatval(str_replace(',', '', $remittance_fee3)) != 0) ? number_format(floatval(str_replace(',', '', $remittance_fee3))) : '' ?>" 
                        oninput="this.value=this.value.replace(/[^0-9]/g,'');">
            </td>        
            <td>
                <input type="text" id="remittance_fee4" name="remittance_fee4" class="form-control noborder-input text-end fw-bold text-dark" 
                        value="<?= (isset($remittance_fee4) && is_numeric(str_replace(',', '', $remittance_fee4)) && floatval(str_replace(',', '', $remittance_fee4)) != 0) ? number_format(floatval(str_replace(',', '', $remittance_fee4))) : '' ?>" 
                        oninput="this.value=this.value.replace(/[^0-9]/g,'');">
            </td>
            <td>
                <input type="text" id="remittance_fee5" name="remittance_fee5" class="form-control noborder-input text-end fw-bold text-dark" 
                        value="<?= (isset($remittance_fee5) && is_numeric(str_replace(',', '', $remittance_fee5)) && floatval(str_replace(',', '', $remittance_fee5)) != 0) ? number_format(floatval(str_replace(',', '', $remittance_fee5))) : '' ?>" 
                        oninput="this.value=this.value.replace(/[^0-9]/g,'');">
            </td>
            <td>
                <input type="text" id="remittance_fee6" name="remittance_fee6" class="form-control noborder-input text-end fw-bold text-dark" 
                        value="<?= (isset($remittance_fee6) && is_numeric(str_replace(',', '', $remittance_fee6)) && floatval(str_replace(',', '', $remittance_fee6)) != 0) ? number_format(floatval(str_replace(',', '', $remittance_fee6))) : '' ?>" 
                        oninput="this.value=this.value.replace(/[^0-9]/g,'');">
            </td>
            <td>
                <input type="text" id="remittance_fee7" name="remittance_fee7" class="form-control noborder-input text-end fw-bold text-dark" 
                        value="<?= (isset($remittance_fee7) && is_numeric(str_replace(',', '', $remittance_fee7)) && floatval(str_replace(',', '', $remittance_fee7)) != 0) ? number_format(floatval(str_replace(',', '', $remittance_fee7))) : '' ?>" 
                        oninput="this.value=this.value.replace(/[^0-9]/g,'');">
            </td>
            <td>
                <input type="text" id="remittance_fee_sum" name="remittance_fee_sum" class="form-control noborder-input text-end fw-bold text-primary" 
                        value="<?= (isset($remittance_fee_sum) && is_numeric(str_replace(',', '', $remittance_fee_sum)) && floatval(str_replace(',', '', $remittance_fee_sum)) != 0) ? number_format(floatval(str_replace(',', '', $remittance_fee_sum))) : '' ?>" 
                        readonly    >
            </td>
        </tr>
        <tr>			
            <!-- 원화입력 + 송금수수료 합계 -->			  
            <td class="bg-light fw-bold text-primary">원화입력 + 송금수수료 합계(KRW)</td>
            <td>
                <input type="text" id="krw_plus_remittance_fee1" name="krw_plus_remittance_fee1" class="form-control noborder-input text-end fw-bold text-dark" readonly>                         
            </td>        
            <td>
                <input type="text" id="krw_plus_remittance_fee2" name="krw_plus_remittance_fee2" class="form-control noborder-input text-end fw-bold text-dark" readonly>   
            </td>        
            <td>
                <input type="text" id="krw_plus_remittance_fee3" name="krw_plus_remittance_fee3" class="form-control noborder-input text-end fw-bold text-dark" readonly>   
            </td>        
            <td>
                <input type="text" id="krw_plus_remittance_fee4" name="krw_plus_remittance_fee4" class="form-control noborder-input text-end fw-bold text-dark" readonly>   
            </td>
            <td>
                <input type="text" id="krw_plus_remittance_fee5" name="krw_plus_remittance_fee5" class="form-control noborder-input text-end fw-bold text-dark" readonly>   
            </td>
            <td>
                <input type="text" id="krw_plus_remittance_fee6" name="krw_plus_remittance_fee6" class="form-control noborder-input text-end fw-bold text-dark" readonly>   
            </td>
            <td>
                <input type="text" id="krw_plus_remittance_fee7" name="krw_plus_remittance_fee7" class="form-control noborder-input text-end fw-bold text-dark" readonly>   
            </td>
            <td>
                <input type="text" id="krw_plus_remittance_fee_sum" name="krw_plus_remittance_fee_sum" class="form-control noborder-input text-end fw-bold text-primary" readonly >
            </td>
        </tr>

        <tr>			
        <!-- 3~10열: 송금액 입력 -->			  
        <td class="bg-light">송금액(CNY)</td>
        <td>
            <input type="text" id="sendMoney1" name="sendMoney1" class="form-control noborder-input text-end fw-bold text-dark" 
                    value="<?= is_numeric(str_replace(',', '', $sendMoney1)) ? number_format(floatval(str_replace(',', '', $sendMoney1)), 2) : $sendMoney1 ?>" 
                    oninput="formatNumber(this)">
        </td>        
        <td>
            <input type="text" id="sendMoney2" name="sendMoney2" class="form-control noborder-input text-end fw-bold text-dark" 
                    value="<?= is_numeric(str_replace(',', '', $sendMoney2)) ? number_format(floatval(str_replace(',', '', $sendMoney2)), 2) : $sendMoney2 ?>" 
                    oninput="formatNumber(this)">
        </td>        
        <td>
            <input type="text" id="sendMoney3" name="sendMoney3" class="form-control noborder-input text-end fw-bold text-dark" 
                    value="<?= is_numeric(str_replace(',', '', $sendMoney3)) ? number_format(floatval(str_replace(',', '', $sendMoney3)), 2) : $sendMoney3 ?>" 
                    oninput="formatNumber(this)">
        </td>        
        <td>
            <input type="text" id="sendMoney4" name="sendMoney4" class="form-control noborder-input text-end fw-bold text-dark" 
                    value="<?= is_numeric(str_replace(',', '', $sendMoney4)) ? number_format(floatval(str_replace(',', '', $sendMoney4)), 2) : $sendMoney4 ?>" 
                    oninput="formatNumber(this)">
        </td>
        <td>
            <input type="text" id="sendMoney5" name="sendMoney5" class="form-control noborder-input text-end fw-bold text-dark" 
                    value="<?= is_numeric(str_replace(',', '', $sendMoney5)) ? number_format(floatval(str_replace(',', '', $sendMoney5)), 2) : $sendMoney5 ?>" 
                    oninput="formatNumber(this)">
        </td>
        <td>
            <input type="text" id="sendMoney6" name="sendMoney6" class="form-control noborder-input text-end fw-bold text-dark" 
                    value="<?= is_numeric(str_replace(',', '', $sendMoney6)) ? number_format(floatval(str_replace(',', '', $sendMoney6)), 2) : $sendMoney6 ?>" 
                    oninput="formatNumber(this)">
        </td>
        <td>
            <input type="text" id="sendMoney7" name="sendMoney7" class="form-control noborder-input text-end fw-bold text-dark" 
                    value="<?= is_numeric(str_replace(',', '', $sendMoney7)) ? number_format(floatval(str_replace(',', '', $sendMoney7)), 2) : $sendMoney7 ?>" 
                    oninput="formatNumber(this)">
        </td>
        <td>
        <input type="text" id="totalSendMoney" name="totalSendMoney" class="form-control noborder-input text-end fw-bold text-dark" 
                value="<?= is_numeric(str_replace(',', '', $totalSendMoney)) ? number_format(floatval(str_replace(',', '', $totalSendMoney)), 2) : $totalSendMoney ?>" 
                readonly>                
        </td>
        </tr>
                        
        <tr id="sumRow">
            <!-- 차이 -->
            <td colspan="6" class="text-end fw-bold text-danger" id="gapAmountCell">
            차이(CNY): <span id="diffAmount" class="ms-2 text-danger">0</span>
            </td>
        </tr>
    
        </tbody>
    </table>				
    </div>

    <!-- 통관비용 관련 테이블 -->
    <div class='d-flex justify-content-center  align-items-center  mt-4 mb-2'>		
        <span class='badge bg-secondary fs-6 me-3'> 통관비용 </span>
    </div>
    <div class='d-flex justify-content-center  align-items-center  mt-4 mb-2'>		
    <table class="table table-bordered w-auto align-middle text-center" id="customs_table">
        <thead>
        <tr>            
            <th colspan="1" class="bg-light fw-bold text-center"> 구분 </th>
            <th colspan="1" class="bg-light fw-bold text-center"> 1차 </th>
            <th colspan="1" class="bg-light fw-bold text-center"> 2차 </th>  
            <th colspan="1" class="bg-light fw-bold text-center"> 3차 </th>  
            <th colspan="1" class="bg-light fw-bold text-center"> 4차 </th>  
            <th colspan="1" class="bg-light fw-bold text-center"> 5차 </th>  
            <th colspan="1" class="bg-light fw-bold text-center"> 6차 </th>  
            <th colspan="1" class="bg-light fw-bold text-center"> 7차 </th>  
            <th colspan="1" class="bg-light fw-bold text-center"> 소계 </th>  
            <th colspan="1" class="bg-light fw-bold text-center"> 누계 </th>  
        </tr>
        </thead>
        <tbody>
        <tr>            
            <td class="bg-light">통관일</td>
            <td>
            <input type="date" id="customs_date1" name="customs_date1" class="form-control noborder-input text-center text-dark  " 
                    value="<?= $customs_date1 ?? '' ?>">
            </td>
            <td>
            <input type="date" id="customs_date2" name="customs_date2" class="form-control  noborder-input text-center text-dark  " 
                    value="<?= $customs_date2 ?? '' ?>">
            </td>
            <td>
            <input type="date" id="customs_date3" name="customs_date3" class="form-control  noborder-input  text-center text-dark  " 
                    value="<?= $customs_date3 ?? '' ?>">
            </td>
            <td>
            <input type="date" id="customs_date4" name="customs_date4" class="form-control   noborder-input text-center text-dark  " 
                    value="<?= $customs_date4 ?? '' ?>">
            </td>
            <td>
            <input type="date" id="customs_date5" name="customs_date5" class="form-control  noborder-input text-center text-dark  " 
                    value="<?= $customs_date5 ?? '' ?>">
            </td>
            <td>
            <input type="date" id="customs_date6" name="customs_date6" class="form-control  noborder-input  text-center text-dark  " 
                    value="<?= $customs_date6 ?? '' ?>">
            </td>
            <td>
            <input type="date" id="customs_date7" name="customs_date7" class="form-control   noborder-input text-center text-dark  " 
                    value="<?= $customs_date7 ?? '' ?>">
            </td>

            <td>    </td>
            <td>    </td>
        </tr>
        <tr>            
            <td class="bg-light">부가세(원)</td>
                <td>
                <input type="text" id="customs_vat1" name="customs_vat1" class="form-control noborder-input text-end fw-bold customs_vat" 
                        value="<?= isset($customs_vat1) ? number_format(intval(str_replace(',', '', $customs_vat1))) : '' ?>" 
                        oninput="formatNumber(this)">
                </td>
                <td>
                <input type="text" id="customs_vat2" name="customs_vat2" class="form-control noborder-input text-end fw-bold customs_vat" 
                        value="<?= isset($customs_vat2) ? number_format(intval(str_replace(',', '', $customs_vat2))) : '' ?>" 
                        oninput="formatNumber(this)">
                </td>            
                <td>
                <input type="text" id="customs_vat3" name="customs_vat3" class="form-control noborder-input text-end fw-bold customs_vat" 
                        value="<?= isset($customs_vat3) ? number_format(intval(str_replace(',', '', $customs_vat3))) : '' ?>" 
                        oninput="formatNumber(this)">
                </td>            
                <td>
                <input type="text" id="customs_vat4" name="customs_vat4" class="form-control noborder-input text-end fw-bold customs_vat" 
                        value="<?= isset($customs_vat4) ? number_format(intval(str_replace(',', '', $customs_vat4))) : '' ?>" 
                        oninput="formatNumber(this)">
                </td>                
                <td>
                <input type="text" id="customs_vat5" name="customs_vat5" class="form-control noborder-input text-end fw-bold customs_vat" 
                        value="<?= isset($customs_vat5) ? number_format(intval(str_replace(',', '', $customs_vat5))) : '' ?>" 
                        oninput="formatNumber(this)">
                </td>            
                <td>
                <input type="text" id="customs_vat6" name="customs_vat6" class="form-control noborder-input text-end fw-bold customs_vat" 
                        value="<?= isset($customs_vat6) ? number_format(intval(str_replace(',', '', $customs_vat6))) : '' ?>" 
                        oninput="formatNumber(this)">
                </td>            
                <td>
                <input type="text" id="customs_vat7" name="customs_vat7" class="form-control noborder-input text-end fw-bold customs_vat" 
                        value="<?= isset($customs_vat7) ? number_format(intval(str_replace(',', '', $customs_vat7))) : '' ?>" 
                        oninput="formatNumber(this)">
                </td>

                <td>
                <input type="text" id="customs_vat_sum" name="customs_vat_sum" class="form-control noborder-input text-end fw-bold customs_vat_sum" 
                        value="<?= isset($customs_vat_sum) ? number_format(intval(str_replace(',', '', $customs_vat_sum))) : '' ?>" 
                        oninput="formatNumber(this)">
                </td>
                <td>
                <input type="text" id="customs_vat_total" name="customs_vat_total" class="form-control noborder-input text-end fw-bold customs_vat_total" 
                        value="<?= isset($customs_vat_total) ? number_format(intval(str_replace(',', '', $customs_vat_total))) : '' ?>" 
                        oninput="formatNumber(this)">
                </td>
            </tr>
        
        <tr>			
            <!--  선임 및 부대비 -->			  
            <td class="bg-light">선임 및 부대비(원)</td>
            <td>
            <input type="text" id="customs_miscellaneous_fee1" name="customs_miscellaneous_fee1" class="form-control noborder-input text-end fw-bold text-dark customs_miscellaneous" 
                    value="<?= isset($customs_miscellaneous_fee1) ? number_format(intval(str_replace(',', '', $customs_miscellaneous_fee1))) : '' ?>" 
                    oninput="formatNumber(this)">
        </td>        
        <td>
            <input type="text" id="customs_miscellaneous_fee2" name="customs_miscellaneous_fee2" class="form-control noborder-input text-end fw-bold text-dark customs_miscellaneous" 
                    value="<?= isset($customs_miscellaneous_fee2) ? number_format(intval(str_replace(',', '', $customs_miscellaneous_fee2))) : '' ?>" 
                    oninput="formatNumber(this)">
        </td>        
        <td>
            <input type="text" id="customs_miscellaneous_fee3" name="customs_miscellaneous_fee3" class="form-control noborder-input text-end fw-bold text-dark customs_miscellaneous" 
                    value="<?= isset($customs_miscellaneous_fee3) ? number_format(intval(str_replace(',', '', $customs_miscellaneous_fee3))) : '' ?>" 
                    oninput="formatNumber(this)">
        </td>        
        <td>
            <input type="text" id="customs_miscellaneous_fee4" name="customs_miscellaneous_fee4" class="form-control noborder-input text-end fw-bold text-dark customs_miscellaneous" 
                    value="<?= isset($customs_miscellaneous_fee4) ? number_format(intval(str_replace(',', '', $customs_miscellaneous_fee4))) : '' ?>" 
                    oninput="formatNumber(this)">
        </td>
        <td>
            <input type="text" id="customs_miscellaneous_fee5" name="customs_miscellaneous_fee5" class="form-control noborder-input text-end fw-bold text-dark customs_miscellaneous" 
                    value="<?= isset($customs_miscellaneous_fee5) ? number_format(intval(str_replace(',', '', $customs_miscellaneous_fee5))) : '' ?>" 
                    oninput="formatNumber(this)">
        </td>        
        <td>
            <input type="text" id="customs_miscellaneous_fee6" name="customs_miscellaneous_fee6" class="form-control noborder-input text-end fw-bold text-dark customs_miscellaneous" 
                    value="<?= isset($customs_miscellaneous_fee6) ? number_format(intval(str_replace(',', '', $customs_miscellaneous_fee6))) : '' ?>" 
                    oninput="formatNumber(this)">
        </td>        
        <td>
            <input type="text" id="customs_miscellaneous_fee7" name="customs_miscellaneous_fee7" class="form-control noborder-input text-end fw-bold text-dark customs_miscellaneous" 
                    value="<?= isset($customs_miscellaneous_fee7) ? number_format(intval(str_replace(',', '', $customs_miscellaneous_fee7))) : '' ?>" 
                    oninput="formatNumber(this)">
        </td>
        <td>
            <input type="text" id="customs_miscellaneous_fee_sum" name="customs_miscellaneous_fee_sum" class="form-control noborder-input text-end fw-bold text-dark customs_miscellaneous_fee_sum" 
                    value="<?= isset($customs_miscellaneous_fee_sum) ? number_format(intval(str_replace(',', '', $customs_miscellaneous_fee_sum))) : '' ?>" 
                    oninput="formatNumber(this)">
        </td>    
        <td>
                <input type="text" id="customs_miscellaneous_fee_total" name="customs_miscellaneous_fee_total" class="form-control noborder-input text-end fw-bold customs_miscellaneous_fee_total" 
                        value="<?= isset($customs_miscellaneous_fee_total) ? number_format(intval(str_replace(',', '', $customs_miscellaneous_fee_total))) : '' ?>" 
                        oninput="formatNumber(this)">
        </td>        
        </tr>
        <!-- 컨테이너운송 -->			  
        <td class="bg-light">컨테이너운송(원)</td>
        <td>
            <input type="text" id="customs_container_fee1" name="customs_container_fee1" class="form-control noborder-input text-end fw-bold text-dark customs_container_fee" 
                    value="<?= isset($customs_container_fee1) ? number_format(intval(str_replace(',', '', $customs_container_fee1))) :  '' ?>" 
                    oninput="formatNumber(this)">
        </td>        
        <td>
            <input type="text" id="customs_container_fee2" name="customs_container_fee2" class="form-control noborder-input text-end fw-bold text-dark customs_container_fee" 
                    value="<?= isset($customs_container_fee2) ? number_format(intval(str_replace(',', '', $customs_container_fee2))) : '' ?>" 
                    oninput="formatNumber(this)">
        </td>        
        <td>
            <input type="text" id="customs_container_fee3" name="customs_container_fee3" class="form-control noborder-input text-end fw-bold text-dark customs_container_fee" 
                    value="<?= isset($customs_container_fee3) ? number_format(intval(str_replace(',', '', $customs_container_fee3))) : '' ?>" 
                    oninput="formatNumber(this)">
        </td>        
        <td>
            <input type="text" id="customs_container_fee4" name="customs_container_fee4" class="form-control noborder-input text-end fw-bold text-dark customs_container_fee" 
                    value="<?= isset($customs_container_fee4) ? number_format(intval(str_replace(',', '', $customs_container_fee4))) : '' ?>" 
                    oninput="formatNumber(this)">
        </td>
        <td>
            <input type="text" id="customs_container_fee5" name="customs_container_fee5" class="form-control noborder-input text-end fw-bold text-dark customs_container_fee" 
                    value="<?= isset($customs_container_fee5) ? number_format(intval(str_replace(',', '', $customs_container_fee5))) : '' ?>" 
                    oninput="formatNumber(this)">
        </td>        
        <td>
            <input type="text" id="customs_container_fee6" name="customs_container_fee6" class="form-control noborder-input text-end fw-bold text-dark customs_container_fee" 
                    value="<?= isset($customs_container_fee6) ? number_format(intval(str_replace(',', '', $customs_container_fee6))) : '' ?>" 
                    oninput="formatNumber(this)">
        </td>        
        <td>
            <input type="text" id="customs_container_fee7" name="customs_container_fee7" class="form-control noborder-input text-end fw-bold text-dark customs_container_fee" 
                    value="<?= isset($customs_container_fee7) ? number_format(intval(str_replace(',', '', $customs_container_fee7))) : '' ?>" 
                    oninput="formatNumber(this)">
        </td>
        <td>
            <input type="text" id="customs_container_fee_sum" name="customs_container_fee_sum" class="form-control noborder-input text-end fw-bold text-dark customs_container_fee_sum" 
                    value="<?= isset($customs_container_fee_sum) ? number_format(intval(str_replace(',', '', $customs_container_fee_sum))) : '' ?>" 
                    oninput="formatNumber(this)">
        </td>
        <td>
                <input type="text" id="customs_container_fee_total" name="customs_container_fee_total" class="form-control noborder-input text-end fw-bold customs_container_fee_total" 
                        value="<?= isset($customs_container_fee_total) ? number_format(intval(str_replace(',', '', $customs_container_fee_total))) : '' ?>" 
                        oninput="formatNumber(this)">
                </td>
        </tr>
        
        <!-- 통관수수료 -->			  
        <td class="bg-light">통관수수료(원)</td>
        <td>
                <input type="text" id="customs_commission1" name="customs_commission1" class="form-control noborder-input text-end fw-bold text-secondary customs_commission" 
                        value="<?= isset($customs_commission1) ? number_format(intval(str_replace(',', '', $customs_commission1))) : '' ?>" 
                        oninput="formatNumber(this)">
            </td>        
            <td>
                <input type="text" id="customs_commission2" name="customs_commission2" class="form-control noborder-input text-end fw-bold text-secondary customs_commission" 
                        value="<?= isset($customs_commission2) ? number_format(intval(str_replace(',', '', $customs_commission2))) : '' ?>" 
                        oninput="formatNumber(this)">
            </td>        
            <td>
                <input type="text" id="customs_commission3" name="customs_commission3" class="form-control noborder-input text-end fw-bold text-secondary customs_commission" 
                        value="<?= isset($customs_commission3) ? number_format(intval(str_replace(',', '', $customs_commission3))) : '' ?>" 
                        oninput="formatNumber(this)">
            </td>        
            <td>
                <input type="text" id="customs_commission4" name="customs_commission4" class="form-control noborder-input text-end fw-bold text-secondary customs_commission" 
                        value="<?= isset($customs_commission4) ? number_format(intval(str_replace(',', '', $customs_commission4))) : '' ?>" 
                        oninput="formatNumber(this)">
            </td>
            <td>
                <input type="text" id="customs_commission5" name="customs_commission5" class="form-control noborder-input text-end fw-bold text-secondary customs_commission" 
                        value="<?= isset($customs_commission5) ? number_format(intval(str_replace(',', '', $customs_commission5))) : '' ?>" 
                        oninput="formatNumber(this)">
            </td>
            <td>
                <input type="text" id="customs_commission6" name="customs_commission6" class="form-control noborder-input text-end fw-bold text-secondary customs_commission" 
                        value="<?= isset($customs_commission6) ? number_format(intval(str_replace(',', '', $customs_commission6))) : '' ?>" 
                        oninput="formatNumber(this)">
            </td>
            <td>
                <input type="text" id="customs_commission7" name="customs_commission7" class="form-control noborder-input text-end fw-bold text-secondary customs_commission" 
                        value="<?= isset($customs_commission7) ? number_format(intval(str_replace(',', '', $customs_commission7))) : '' ?>" 
                        oninput="formatNumber(this)">
            </td>            
            <td>
                <input type="text" id="customs_commission_sum" name="customs_commission_sum" class="form-control noborder-input text-end fw-bold text-secondary customs_commission_sum" 
                        value="<?= isset($customs_commission_sum) ? number_format(intval(str_replace(',', '', $customs_commission_sum))) : '' ?>" 
                        oninput="formatNumber(this)">
            </td>        
            <td>
                <input type="text" id="customs_commission_total" name="customs_commission_total" class="form-control  bg-primary  text-white noborder-input text-end fw-bold customs_commission_total" 
                        value="<?= isset($customs_commission_total) ? number_format(intval(str_replace(',', '', $customs_commission_total))) : '' ?>" >
            </td>
        </tr>
        
        <!-- 통관비용 합계 -->			  
        <td class="bg-light bg-primary text-primary fw-bold"> 통관비용 합계 (원)</td>
            <td>
                <input type="text" id="customs_detail_total1" name="customs_detail_total1" class="form-control noborder-input text-end fw-bold text-secondary " 
                        value="<?= isset($customs_detail_total1) ? number_format(intval(str_replace(',', '', $customs_detail_total1))) : '' ?>" 
                        readonly>
            </td>        
            <td>
                <input type="text" id="customs_detail_total2" name="customs_detail_total2" class="form-control noborder-input text-end fw-bold text-secondary " 
                        value="<?= isset($customs_detail_total2) ? number_format(intval(str_replace(',', '', $customs_detail_total2))) : '' ?>" 
                        readonly>
            </td>        
            <td>
                <input type="text" id="customs_detail_total3" name="customs_detail_total3" class="form-control noborder-input text-end fw-bold text-secondary " 
                        value="<?= isset($customs_detail_total3) ? number_format(intval(str_replace(',', '', $customs_detail_total3))) : '' ?>" 
                        readonly>
            </td>        
            <td>
                <input type="text" id="customs_detail_total4" name="customs_detail_total4" class="form-control noborder-input text-end fw-bold text-secondary " 
                        value="<?= isset($customs_detail_total4) ? number_format(intval(str_replace(',', '', $customs_detail_total4))) : '' ?>" 
                        readonly>
            </td>                        
            <td>
                <input type="text" id="customs_detail_total5" name="customs_detail_total5" class="form-control noborder-input text-end fw-bold text-secondary " 
                        value="<?= isset($customs_detail_total5) ? number_format(intval(str_replace(',', '', $customs_detail_total5))) : '' ?>" 
                        readonly>
            </td>        
            <td>
                <input type="text" id="customs_detail_total6" name="customs_detail_total6" class="form-control noborder-input text-end fw-bold text-secondary " 
                        value="<?= isset($customs_detail_total6) ? number_format(intval(str_replace(',', '', $customs_detail_total6))) : '' ?>" 
                        readonly>
            </td>        
            <td>
                <input type="text" id="customs_detail_total7" name="customs_detail_total7" class="form-control noborder-input text-end fw-bold text-secondary " 
                        value="<?= isset($customs_detail_total7) ? number_format(intval(str_replace(',', '', $customs_detail_total7))) : '' ?>" 
                        readonly>
            </td>  
            <td colspan="2"></td>
        </tr>
        
        <!-- 입고물량대금(CNY) -->			  
        <td class="bg-light">입고물량대금(CNY)</td>
        <td>
            <input type="text" id="customs_input_amount_cny1" name="customs_input_amount_cny1" class="form-control noborder-input text-end fw-bold text-dark customs_input_amount_cny" 
                    value="<?= isset($customs_input_amount_cny1) ? number_format(floatval(str_replace(',', '', $customs_input_amount_cny1)),2) : '' ?>" 
                    oninput="formatNumber(this)">
        </td>        
        <td>
            <input type="text" id="customs_input_amount_cny2" name="customs_input_amount_cny2" class="form-control noborder-input text-end fw-bold text-dark customs_input_amount_cny" 
                    value="<?= isset($customs_input_amount_cny2) ? number_format(floatval(str_replace(',', '', $customs_input_amount_cny2)),2) : '' ?>" 
                    oninput="formatNumber(this)">
        </td>        
        <td>
            <input type="text" id="customs_input_amount_cny3" name="customs_input_amount_cny3" class="form-control noborder-input text-end fw-bold text-dark customs_input_amount_cny" 
                    value="<?= isset($customs_input_amount_cny3) ? number_format(floatval(str_replace(',', '', $customs_input_amount_cny3)),2) : '' ?>" 
                    oninput="formatNumber(this)">
        </td>        
        <td>
            <input type="text" id="customs_input_amount_cny4" name="customs_input_amount_cny4" class="form-control noborder-input text-end fw-bold text-dark customs_input_amount_cny" 
                    value="<?= isset($customs_input_amount_cny4) ? number_format(floatval(str_replace(',', '', $customs_input_amount_cny4)),2) : '' ?>" 
                    oninput="formatNumber(this)">
        </td>        
        <td>
            <input type="text" id="customs_input_amount_cny5" name="customs_input_amount_cny5" class="form-control noborder-input text-end fw-bold text-dark customs_input_amount_cny" 
                    value="<?= isset($customs_input_amount_cny5) ? number_format(floatval(str_replace(',', '', $customs_input_amount_cny5)),2) : '' ?>" 
                    oninput="formatNumber(this)">
        </td>        
        <td>
            <input type="text" id="customs_input_amount_cny6" name="customs_input_amount_cny6" class="form-control noborder-input text-end fw-bold text-dark customs_input_amount_cny" 
                    value="<?= isset($customs_input_amount_cny6) ? number_format(floatval(str_replace(',', '', $customs_input_amount_cny6)),2) : '' ?>" 
                    oninput="formatNumber(this)">
        </td>        
        <td>
            <input type="text" id="customs_input_amount_cny7" name="customs_input_amount_cny7" class="form-control noborder-input text-end fw-bold text-dark customs_input_amount_cny" 
                    value="<?= isset($customs_input_amount_cny7) ? number_format(floatval(str_replace(',', '', $customs_input_amount_cny7)),2) : '' ?>" 
                    oninput="formatNumber(this)">
        </td>

        <td colspan="2">
            <div class="d-flex justify-content-end">
                (CNY) 통화  &nbsp;
                <input type="text" id="customs_input_amount_cny_sum" name="customs_input_amount_cny_sum" class="form-control noborder-input text-end fw-bold text-dark customs_input_amount_cny_sum" style="width: 100px;" 
                    value="<?= isset($customs_input_amount_cny_sum) ? number_format(floatval(str_replace(',', '', $customs_input_amount_cny_sum)),2) : '' ?>" >
            </div>
        </td>        
        </tr>           
        </tbody>
    </table>				
    </div>    
	<div class='table-responsive'>
		<table class='table table-bordered table-hover' id='orderlistTable' style='min-width:2300px;'>
			<thead id='thead_orderlist'>
				<tr>
					<th class='text-center' style='width:50px;'>NO</th>
					<th class='text-center' style='width:60px;'>카테고리</th>
					<th class='text-center' style='width:230px;display:none;'>품목코드</th>
					<th class='text-center' style='width:230px;'>품목명</th>
					<th class='text-center' style='width:60px;display:none;'>구매수량</th>
					<th class='text-center' style='width:60px;display:none;'>단가<br>(위엔)</th>
					<th class='text-center' style='width:450px;'>비고</th>
					<th class='text-center' style='width:70px;'>금액<br>(위엔)</th>

					<!-- 1차 -->
					<th class='text-center' style='width:80px;'>1차 입고일</th>
					<th class='text-center' style='width:80px;'>1차 <br> 금액</th>

					<!-- 2차 -->
					<th class='text-center' style='width:60px;'>2차 입고일</th>
					<th class='text-center' style='width:80px;'>2차 <br> 금액</th>

					<!-- 3차 -->
					<th class='text-center' style='width:80px;'>3차 입고일</th>
					<th class='text-center' style='width:80px;'>3차 <br> 금액</th>

					<!-- 4차 -->
					<th class='text-center' style='width:80px;'>4차 입고일</th>
					<th class='text-center' style='width:80px;'>4차 <br> 금액</th>

					<!-- 5차 -->
					<th class='text-center' style='width:80px;'>5차 입고일</th>
					<th class='text-center' style='width:80px;'>5차 <br> 금액</th>

					<!-- 6차 -->
					<th class='text-center' style='width:80px;'>6차 입고일</th>
					<th class='text-center' style='width:80px;'>6차 <br> 금액</th>

					<!-- 7차 -->
					<th class='text-center' style='width:80px;'>7차 입고일</th>
					<th class='text-center' style='width:80px;'>7차 <br> 금액</th>

					<!-- 계산 및 상태 -->
					<th class='text-center' style='width:60px;'>구매<br> 수량합</th>
					<th class='text-center' style='width:60px;'>입고합</th>
					<th class='text-center' style='width:80px;'>구매입고<br>차이</th>
					<th class='text-center' style='width:60px;'>상태</th>
				</tr>
			</thead>
			<tbody id='orderlistGroup'>
				<!-- 동적으로 행 추가 -->
			</tbody>
		</table>
	</div> 
	</div>
</div>
</form>

<script>
var ajaxRequest = null;
var ajaxRequest_write = null;

// 전역 변수에 품목 데이터 저장 (material_reg 폴더의 fetch_itemcode.php 재활용)
var itemData = [];

// 품목 데이터를 Ajax로 로드하는 함수
function loadItemData() {
    $.ajax({
        url: "/material_reg/fetch_itemcode.php", // 재활용: material_reg 폴더의 fetch_itemcode.php
        type: "GET",
        dataType: "json",
        success: function(data) {
			console.log('fetch_itemcode : ',data);
            if(data.items) {
                itemData = data.items;
            }
        },
        error: function(xhr, status, error) {
            console.error("품목 데이터 로드 에러:", error);
        }
    });
}

// initializeAutocomplete 함수: 입력 필드에 자동완성 기능을 설정
function initializeAutocomplete($input) {
    $input.autocomplete({
        source: function(request, response) {
            var term = request.term.toLowerCase();
			var filteredOptions = $.grep(itemData, function(item) {
				var code = item.item_code ? item.item_code.toLowerCase() : "";
				var name = item.item_name ? item.item_name.toLowerCase() : "";
				return code.indexOf(term) !== -1 || name.indexOf(term) !== -1;
			}).map(function(item) {
				return {
					label: item.item_code + " - " + item.item_name,
					value: $input.hasClass("item-code") ? item.item_code : item.item_name,
					item_code: item.item_code,
					item_name: item.item_name,
					item_yuan: item.item_yuan
				};
			});

            response(filteredOptions);
        },
       select: function(event, ui) {
			var $this = $(this);
			    console.log("선택된 항목:", ui.item);

			if ($this.hasClass("item-code")) {
				$this.val(ui.item.item_code);
				$this.closest("tr").find("input.item-name").val(ui.item.item_name);
				$this.closest("tr").find("input.unit-price").val(ui.item.item_yuan);
			} else if ($this.hasClass("item-name")) {
				$this.val(ui.item.item_name);
				$this.closest("tr").find("input.item-code").val(ui.item.item_code);
				$this.closest("tr").find("input.unit-price").val(ui.item.item_yuan);
			}
			return false;
		},
		
        minLength: 0,
        open: function() {
            $(this).autocomplete("widget").css({
                "max-height": "200px",
                "overflow-y": "auto",
                "overflow-x": "hidden"
            });
        }
    }).on("keydown", function(event) {
        if (event.keyCode === $.ui.keyCode.ENTER && $(this).autocomplete("instance").menu.active) {
            event.preventDefault();
        }
    });
}

// 문서 로드 시 품목 데이터를 먼저 로드합니다.

$(document).ready(function() {		

	loadItemData();  		
	initializePage();
	bindEventHandlers();			
    updateTotalSummary(); // 페이지 로드 후 최초 계산	

	$("#showlogBtn").click(function() {
		var num = '<?= $num ?>';
		var workitem = 'm_order';
		var btn = $(this);
		popupCenter("../Showlog.php?num=" + num + "&workitem=" + workitem, '로그기록 보기', 500, 500);
		btn.prop('disabled', false);
	});

	$(".saveBtn").click(function() {
		saveData();
	});

	document.querySelectorAll('input').forEach(function(input) {
		input.setAttribute('autocomplete', 'off');
	});
		
});

function initializePage() {
    var loader = document.getElementById('loadingOverlay');
	if(loader)
		loader.style.display = 'none';

    var orderlist = <?= json_encode($orderlist ?? []) ?>;

    loadTableData('#orderlistTable', orderlist, 'orderlistTable');

    if ('<?= $mode ?>' === 'view') {
        disableInputsForViewMode();
    }
}

function bindEventHandlers() {
	$(document).on("click", ".remove-row", function() {
		$(this).closest("tr").remove();
		updateTotalSummary(); // 삭제 후 합계 재계산
	});
		
	$(document).on('click', '.add-row', function() {
		var table = $(this).closest('div').siblings('div').find('table'); // 현재 버튼과 가까운 테이블 찾기
		var tableBody = table.find('tbody');
		var tableId = table.attr('id') || ''; // 테이블 ID가 존재할 경우만 사용

		addRow(tableBody, {}, tableId);
	});
		
	$(document).on('click', '.add-row_new', function() {
		var table = $(this).closest('table'); // 버튼이 속한 테이블 찾기
		var tableBody = table.find('tbody');
		var tableId = table.attr('id') || ''; // id 존재 시 사용

		addRow(tableBody, {}, tableId);
	});

}

function addRow(tableBody, rowData = {}, typebutton = '') {
    var showInout = true; // 입고 열 항상 표시
    var table = tableBody.closest('table');
    var thead = table.find('thead');
    if (thead.length !== 0) {
        thead.css('display', 'table-header-group');
    }

    var newRow = $('<tr>');

    // 0. NO
    newRow.append(`
        <td class="text-center">
            <div class="d-flex justify-content-center align-items-center">
                <span class="serial-number me-4"></span>
            </div>
        </td>
    `);

    // 1. 카테고리 select (readonly/disabled)
    var categoryValue = rowData.col0 || '모터';
    var categorySelect = '<select name="col0[]" class="form-select w-auto item-category" style="font-size: 0.7rem;" disabled readonly>' +
        '<option value="모터"' + (categoryValue === '모터' ? ' selected' : '') + '>모터</option>' +
        '<option value="연동제어기"' + (categoryValue === '연동제어기' ? ' selected' : '') + '>연동제어기</option>' +
        '<option value="운송비"' + (categoryValue === '운송비' ? ' selected' : '') + '>운송비</option>' +
        '<option value="부속자재"' + (categoryValue === '부속자재' ? ' selected' : '') + '>부속자재</option>' +
        '</select>';
    newRow.append('<td class="text-center">' + categorySelect + '</td>');

    // 2. 품목코드 (col1, 숨김)
    newRow.append(`
        <td class="text-center" style="display:none;"><input type="text" name="col1[]" class="form-control item-code" readonly value="${rowData.col1 || ''}"></td>
    `);

    // 3. 품목명 (col2)
    newRow.append(`
        <td class="text-center"><input type="text" name="col2[]" class="form-control item-name" readonly value="${rowData.col2 || ''}"></td>
    `);

    // 4. 구매수량 (col3 숨김)
    newRow.append(`
        <td class="text-center" style="display:none;"><input type="number" name="col3[]" class="form-control text-end purchase-qty" readonly value="${rowData.col3 || ''}" onkeyup="updateRowCalculation(this);"></td>
    `);

    // 5. 단가 (col4, 숨김)
    newRow.append(`
        <td class="text-center" style="display:none;"><input type="number" name="col4[]" class="form-control text-end unit-price" readonly value="${rowData.col4 || ''}" onkeyup="updateRowCalculation(this);"></td>
    `);

    // 6. 비고 (col5)
    newRow.append(`
        <td class="text-center"><input type="text" name="col5[]" class="form-control" readonly value="${rowData.col5 || ''}"></td>
    `);

    // 7. 금액 (col6)
    newRow.append(`
        <td class="text-center" ><input type="text" name="col6[]" class="form-control text-end amount" readonly value="${rowData.col6 ? Number(rowData.col6).toLocaleString() : ''}"></td>
    `);

    // 8~25: 1~7차 입고일(col7,10,13,16,19,22,25), 입고수량(col8,11,14,17,20,23,26), 로트번호(col9,12,15,18,21,24,27)
    for (let i = 1; i <= 7; i++) {
        const base = 6 + (i - 1) * 3;

        // 입고일은 표시
        newRow.append(`
            <td class="text-center" style="width:100px!important;"><input type="date" name="col${base + 1}[]" class="form-control noborder-input" value="${rowData[`col${base + 1}`] || ''}" readonly></td>
        `);

        // 입고수량, 로트번호는 숨김
        newRow.append(`
            <td class="text-center" style="display:none;"><input type="number" name="col${base + 2}[]" class="form-control text-end inqty${i}" value="${rowData[`col${base + 2}`] || ''}" onkeyup="updateRowCalculation(this);"></td>
        `);
        newRow.append(`
            <td class="text-center" style="display:none;"><input type="text" name="col${base + 3}[]" class="form-control lotnum${i}" value="${rowData[`col${base + 3}`] || ''}"></td>
        `);

        // 입고금액과 송금액은 표시
        const amountCol = 22 + (i - 1) * 2 + 1;
        newRow.append(`
            <td class="text-center"><input type="text" name="col${amountCol}[]" class="form-control text-end amountIn${i}" value="${rowData[`col${amountCol}`] || ''}" readonly></td>
        `);
		// 송금액은 전체로 표시, 숨김
        newRow.append(`
            <td class="text-center" style="display:none;"><input type="text" name="col${amountCol + 1}[]" class="form-control text-end sendMoney${i}" value="${rowData[`col${amountCol + 1}`] || ''}"></td>
        `);
    }

        // 20. 구매수량합 (col20)
        newRow.append(`
            <td class="text-center"><input type="text" name="col20[]" class="form-control text-end total-purchase" readonly></td>
        `);

        // 21. 입고합 (col21)
        newRow.append(`
            <td class="text-center"><input type="text" name="col21[]" class="form-control text-end total-inqty" readonly></td>
        `);

        // 22. 구매입고차이 (col22)
        newRow.append(`
            <td class="text-center"><input type="text" name="col22[]" class="form-control text-end diff" readonly></td>
        `);

        // 23. 상태 (col23)
        newRow.append(`
            <td class="text-center"><input type="text" name="col23[]" class="form-control fw-bold text-center status" readonly></td>
        `);

        tableBody.append(newRow);

        // 일련번호 업데이트
        updateSerialNumbers(tableBody);

        // 계산 실행
        updateRowCalculation(newRow.find('input.purchase-qty')[0]);
}

function updateSerialNumbers(tableBody) {
    tableBody.find('tr').each(function(index) {
        $(this).find('.serial-number').text(index + 1);
    });
}


function updateRowCalculation(input) {
    const $row = $(input).closest("tr");

    const purchaseQty = parseFloat($row.find("input.purchase-qty").val().replace(/,/g, '')) || 0;
    const unitPrice = parseFloat($row.find("input.unit-price").val().replace(/,/g, '')) || 0;
    const amount = purchaseQty * unitPrice;
    $row.find("input.amount").val(amount.toLocaleString());

    let totalInQty = 0;

    for (let i = 1; i <= 7; i++) {
        const inQty = parseFloat($row.find(`.inqty${i}`).val().replace(/,/g, '')) || 0;
        const inAmount = inQty * unitPrice;
        $row.find(`.amountIn${i}`).val(inAmount.toLocaleString());
        totalInQty += inQty;
    }

    $row.find("input.total-inqty").val(totalInQty.toLocaleString());
    $row.find("input.total-purchase").val(purchaseQty.toLocaleString());

    const diff = purchaseQty - totalInQty;
    $row.find("input.diff").val(diff.toLocaleString());

    if (diff === 0) {
        $row.find("input.diff").css("background-color", "#000").css("color", "#fff");
    } else if (diff > 0) {
        $row.find("input.diff").css("background-color", "#007bff").css("color", "#fff");
    } else {
        $row.find("input.diff").css("background-color", "#dc3545").css("color", "#fff");
    }

    const status = (diff === 0 && purchaseQty > 0) ? '완료' : '';
    $row.find("input.status").val(status);

    updateTotalSummary();
}



function updateTotalSummary() {
    let totalQty = 0;
    let totalAmount = 0;

    let sum1 = 0;
    let sum2 = 0;
    let sum3 = 0;
    let sum4 = 0;
    let sum5 = 0;
    let sum6 = 0;
    let sum7 = 0;

    $("input.purchase-qty").each(function () {
        const qty = parseFloat($(this).val().replace(/,/g, "")) || 0;
        totalQty += qty;
    });

    $("input.amount").each(function () {
        const amount = parseFloat($(this).val().replace(/,/g, "")) || 0;
        totalAmount += amount;
    });

    $("input.amountIn1").each(function () {
        const val = parseFloat($(this).val().replace(/,/g, "")) || 0;
        sum1 += val;
    });

    $("input.amountIn2").each(function () {
        const val = parseFloat($(this).val().replace(/,/g, "")) || 0;
        sum2 += val;
    });

    $("input.amountIn3").each(function () {
        const val = parseFloat($(this).val().replace(/,/g, "")) || 0;
        sum3 += val;
    });

    $("input.amountIn4").each(function () {
        const val = parseFloat($(this).val().replace(/,/g, "")) || 0;
        sum4 += val;
    });

    $("input.amountIn5").each(function () {
        const val = parseFloat($(this).val().replace(/,/g, "")) || 0;
        sum5 += val;
    });

    $("input.amountIn6").each(function () {
        const val = parseFloat($(this).val().replace(/,/g, "")) || 0;
        sum6 += val;
    });

    $("input.amountIn7").each(function () {
        const val = parseFloat($(this).val().replace(/,/g, "")) || 0;
        sum7 += val;
    });

    // 총 구매 수량 표시
    $("#totalsurangDisplay").text(totalQty.toLocaleString());

    // 각 차수 입고 합계를 hidden input에 반영
    // $("#inputSum1").val(sum1);
    // $("#inputSum2").val(sum2);
    // $("#inputSum3").val(sum3);
    // $("#inputSum4").val(sum4);

    // 각 차수 입고 합계를 span에도 표시
    $("#inputSumDisplay1").text(sum1 === 0 ? '' : sum1.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    $("#inputSumDisplay2").text(sum2 === 0 ? '' : sum2.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    $("#inputSumDisplay3").text(sum3 === 0 ? '' : sum3.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    $("#inputSumDisplay4").text(sum4 === 0 ? '' : sum4.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    $("#inputSumDisplay5").text(sum5 === 0 ? '' : sum5.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    $("#inputSumDisplay6").text(sum6 === 0 ? '' : sum6.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    $("#inputSumDisplay7").text(sum7 === 0 ? '' : sum7.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    $("#inputSumDisplay").text(totalAmount === 0 ? '' : totalAmount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
	
	// 최초 로드시 계산 (입고합계)
    calculateSummary();
    calculateKrwPlusRemittanceFee();
}



function loadTableData(tableId, dataList, typebutton) {
    var tableBody = $(tableId).find('tbody');
    var theadId;

    switch (tableId) {
        case '#orderlistTable':
            theadId = '#thead_orderlist';
            break;
        default:
            theadId = null;
    }

    if (typeof dataList === 'string') {
        try {
            dataList = JSON.parse(dataList);
        } catch (e) {
            console.error('Failed to parse dataList:', e);
            dataList = [];
        }
    }

    if (theadId) {
        if (dataList.length === 0) {
            $(theadId).hide();
        } else {
            $(theadId).show();
        }
    }

    if (!Array.isArray(dataList)) {
        dataList = [];
    }

    if (dataList.length === 0) {
        console.log('no record');
    } else {
        dataList.forEach(function(item) {
            addRow(tableBody, item, typebutton);
        });
    }
}

function formatNumber(input) {
    // Remove all non-digit characters except decimal point
    input.value = input.value.replace(/[^\d.]/g, '');
    
    // Ensure only one decimal point exists
    let parts = input.value.split('.');
    if (parts.length > 2) {
        input.value = parts[0] + '.' + parts.slice(1).join('');
    }
    
    // Add commas to the whole number part
    parts = input.value.split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    input.value = parts.join('.');
}

function saveData() {
    const myform = document.getElementById('board_form');
    const inputs = myform.querySelectorAll('input[required]');
    let allValid = true;

    // console.log(inputs);	
    inputs.forEach(input => {
        if (!input.value) {
            allValid = false;
            Toastify({
                text: "수량 등 필수입력 부분을 확인해 주세요.",
                duration: 2000,
                close: true,
                gravity: "top",
                position: "center",
                style: {
                    background: "linear-gradient(to right, #00b09b, #96c93d)"
                },
            }).showToast();
            return;
        }
    });

    if (!allValid) return;

    var num = $("#num").val();    
    $("button").prop("disabled", true);
  
    if (Number(num) < 1) {
        $("#mode").val('insert');
    } else {
        $("#mode").val('modify');
    }

    let formData = [];
    $('#orderlistTable tbody tr').each(function() {
        let rowData = {};
        $(this).find('input, select').each(function() {
            let name = $(this).attr('name').replace('[]', '');
            let value = $(this).val();
            rowData[name] = value;
        });
        formData.push(rowData);
    });
    let jsonString = JSON.stringify(formData);

    // 필수 기본 폼 데이터만 수집 - 모든 필드를 수동으로 추가
    var basicFormData = {
        num: $("#num").val(),
        mode: $("#mode").val(),
        tablename: $("#tablename").val(),
        first_writer: $("#first_writer").val(),
        update_log: $("#update_log").val(),
        orderlist: jsonString,
        orderDate: $("#orderDate").val(),
        memo: $("#memo").val() || '',
        
        // 송금일자
        sendDate1: $("#sendDate1").val() || '',
        sendDate2: $("#sendDate2").val() || '',
        sendDate3: $("#sendDate3").val() || '',
        sendDate4: $("#sendDate4").val() || '',
        sendDate5: $("#sendDate5").val() || '',
        sendDate6: $("#sendDate6").val() || '',
        sendDate7: $("#sendDate7").val() || '',
        
        // 통관일자
        customs_date1: $("#customs_date1").val() || '',
        customs_date2: $("#customs_date2").val() || '',
        customs_date3: $("#customs_date3").val() || '',
        customs_date4: $("#customs_date4").val() || '',
        customs_date5: $("#customs_date5").val() || '',
        customs_date6: $("#customs_date6").val() || '',
        customs_date7: $("#customs_date7").val() || '',
        
        // 수량 및 금액 관련 필드들
        totalsurang: $("#totalsurang").val() || '',
        totalamount: $("#totalamount").val() || '',
        inputSum1: $("#inputSum1").val() || '',
        inputSum2: $("#inputSum2").val() || '',
        inputSum3: $("#inputSum3").val() || '',
        inputSum4: $("#inputSum4").val() || '',
        inputSum5: $("#inputSum5").val() || '',
        inputSum6: $("#inputSum6").val() || '',
        inputSum7: $("#inputSum7").val() || '',
        
        // 송금 금액
        sendMoney1: $("#sendMoney1").val() || '',
        sendMoney2: $("#sendMoney2").val() || '',
        sendMoney3: $("#sendMoney3").val() || '',
        sendMoney4: $("#sendMoney4").val() || '',
        sendMoney5: $("#sendMoney5").val() || '',
        sendMoney6: $("#sendMoney6").val() || '',
        sendMoney7: $("#sendMoney7").val() || '',
        
        // 통관비용 관련
        customs_fee1: $("#customs_fee1").val() || '',
        customs_fee2: $("#customs_fee2").val() || '',
        customs_fee3: $("#customs_fee3").val() || '',
        customs_fee4: $("#customs_fee4").val() || '',
        customs_fee5: $("#customs_fee5").val() || '',
        customs_fee6: $("#customs_fee6").val() || '',
        customs_fee7: $("#customs_fee7").val() || '',
        
        customs_fee_total1: $("#customs_fee_total1").val() || '',
        customs_fee_total2: $("#customs_fee_total2").val() || '',
        customs_fee_total3: $("#customs_fee_total3").val() || '',
        customs_fee_total4: $("#customs_fee_total4").val() || '',
        customs_fee_total5: $("#customs_fee_total5").val() || '',
        customs_fee_total6: $("#customs_fee_total6").val() || '',
        customs_fee_total7: $("#customs_fee_total7").val() || '',
        
        // 부가세
        customs_vat1: $("#customs_vat1").val() || '',
        customs_vat2: $("#customs_vat2").val() || '',
        customs_vat3: $("#customs_vat3").val() || '',
        customs_vat4: $("#customs_vat4").val() || '',
        customs_vat5: $("#customs_vat5").val() || '',
        customs_vat6: $("#customs_vat6").val() || '',
        customs_vat7: $("#customs_vat7").val() || '',
        
        // 선임 및 부대비
        customs_miscellaneous_fee1: $("#customs_miscellaneous_fee1").val() || '',
        customs_miscellaneous_fee2: $("#customs_miscellaneous_fee2").val() || '',
        customs_miscellaneous_fee3: $("#customs_miscellaneous_fee3").val() || '',
        customs_miscellaneous_fee4: $("#customs_miscellaneous_fee4").val() || '',
        customs_miscellaneous_fee5: $("#customs_miscellaneous_fee5").val() || '',
        customs_miscellaneous_fee6: $("#customs_miscellaneous_fee6").val() || '',
        customs_miscellaneous_fee7: $("#customs_miscellaneous_fee7").val() || '',
        
        // 컨테이너 운송
        customs_container_fee1: $("#customs_container_fee1").val() || '',
        customs_container_fee2: $("#customs_container_fee2").val() || '',
        customs_container_fee3: $("#customs_container_fee3").val() || '',
        customs_container_fee4: $("#customs_container_fee4").val() || '',
        customs_container_fee5: $("#customs_container_fee5").val() || '',
        customs_container_fee6: $("#customs_container_fee6").val() || '',
        customs_container_fee7: $("#customs_container_fee7").val() || '',
        
        // 통관수수료
        customs_commission1: $("#customs_commission1").val() || '',
        customs_commission2: $("#customs_commission2").val() || '',
        customs_commission3: $("#customs_commission3").val() || '',
        customs_commission4: $("#customs_commission4").val() || '',
        customs_commission5: $("#customs_commission5").val() || '',
        customs_commission6: $("#customs_commission6").val() || '',
        customs_commission7: $("#customs_commission7").val() || '',
        
        // 세부 총합
        customs_detail_total1: $("#customs_detail_total1").val() || '',
        customs_detail_total2: $("#customs_detail_total2").val() || '',
        customs_detail_total3: $("#customs_detail_total3").val() || '',
        customs_detail_total4: $("#customs_detail_total4").val() || '',
        customs_detail_total5: $("#customs_detail_total5").val() || '',
        customs_detail_total6: $("#customs_detail_total6").val() || '',
        customs_detail_total7: $("#customs_detail_total7").val() || '',
        
        // 입고물량대금 CNY
        customs_input_amount_cny1: $("#customs_input_amount_cny1").val() || '',
        customs_input_amount_cny2: $("#customs_input_amount_cny2").val() || '',
        customs_input_amount_cny3: $("#customs_input_amount_cny3").val() || '',
        customs_input_amount_cny4: $("#customs_input_amount_cny4").val() || '',
        customs_input_amount_cny5: $("#customs_input_amount_cny5").val() || '',
        customs_input_amount_cny6: $("#customs_input_amount_cny6").val() || '',
        customs_input_amount_cny7: $("#customs_input_amount_cny7").val() || '',
        
        // 환율
        exchange_rate1: $("#exchange_rate1").val() || '',
        exchange_rate2: $("#exchange_rate2").val() || '',
        exchange_rate3: $("#exchange_rate3").val() || '',
        exchange_rate4: $("#exchange_rate4").val() || '',
        exchange_rate5: $("#exchange_rate5").val() || '',
        exchange_rate6: $("#exchange_rate6").val() || '',
        exchange_rate7: $("#exchange_rate7").val() || '',
        
        // 송금액(원화)
        send_amount_krw1: $("#send_amount_krw1").val() || '',
        send_amount_krw2: $("#send_amount_krw2").val() || '',
        send_amount_krw3: $("#send_amount_krw3").val() || '',
        send_amount_krw4: $("#send_amount_krw4").val() || '',
        send_amount_krw5: $("#send_amount_krw5").val() || '',
        send_amount_krw6: $("#send_amount_krw6").val() || '',
        send_amount_krw7: $("#send_amount_krw7").val() || '',
        
        // 송금수수료
        remittance_fee1: $("#remittance_fee1").val() || '',
        remittance_fee2: $("#remittance_fee2").val() || '',
        remittance_fee3: $("#remittance_fee3").val() || '',
        remittance_fee4: $("#remittance_fee4").val() || '',
        remittance_fee5: $("#remittance_fee5").val() || '',
        remittance_fee6: $("#remittance_fee6").val() || '',
        remittance_fee7: $("#remittance_fee7").val() || ''
    };

    if (ajaxRequest_write !== null) {
        ajaxRequest_write.abort();
    }
	
	showMsgModal(2); // 1 이미지 저장, 2 파일저장
	
    ajaxRequest_write = $.ajax({
        cache: false,
        timeout: 600000,
        url: "insert_account.php",
        type: "post",
        data: basicFormData,
        dataType: "json",
        success: function(data) {
			console.log(data);
            setTimeout(function() {
                if (window.opener && !window.opener.closed) {
					hideMsgModal();
                    if (typeof window.opener.restorePageNumber === 'function') {
                        window.opener.restorePageNumber();
                    }
                }
            }, 1000);
            ajaxRequest_write = null;
            setTimeout(function() {
                hideMsgModal();
                self.close();
            }, 1000);
        },
        error: function(jqxhr, status, error) {
            console.log(jqxhr, status, error);
			ajaxRequest_write = null;
			hideMsgModal();
        }
    });
}

function disableInputsForViewMode() {
    // readonly 처리 (passView 제외)
    $('input, textarea').not('.passView').prop('readonly', true);

    // select, 버튼 등 (passView 제외)
    $('select, .restrictbtn, .sub_add, .add').not('.passView').prop('disabled', true);

    // 파일 업로드는 항상 readonly false 유지
    $('input[type=file]').prop('readonly', false);

    // 체크박스는 passView 제외
    $('input[type=checkbox]').not('.passView').prop('disabled', true);

    // 기타 버튼
    $('.viewNoBtn').not('.passView').prop('disabled', true);
    $('.specialbtnClear').not('.passView').prop('disabled', true);
}

function captureReturnKey(e) {
    if (e.keyCode == 13 && e.srcElement.type != 'textarea') {
        return false;
    }
}

function closePopup() {
    if (popupWindow && !popupWindow.closed) {
        popupWindow.close();
        isWindowOpen = false;
    }
}

function showWarningModal() {
    Swal.fire({
        title: '등록 오류 알림',
        text: '필수입력 요소를 확인바랍니다.',
        icon: 'warning',
    }).then(result => {
        if (result.isConfirmed) {
            return;
        }
    });
}

function showlotError() {
    Swal.fire({
        title: '등록 오류 알림',
        text: '입력 항목들을 점검해주세요.',
        icon: 'warning',
    }).then(result => {
        if (result.isConfirmed) {
            return;
        }
    });
}

function inputNumber(input) {
    const cursorPosition = input.selectionStart;
    const value = input.value.replace(/,/g, '');
    const formattedValue = Number(value).toLocaleString();
    input.value = formattedValue;
    input.setSelectionRange(cursorPosition, cursorPosition);
}
</script>

<script>
function generateExcel() {
    // 1) 테이블 요소 가져오기
    const table = document.getElementById('orderlistTable');
    if (!table) return alert('테이블이 없습니다.');

	// 2) 헤더에서 컬럼명 배열 추출 (숨김 컬럼 제외)
	const ths = table.querySelectorAll('thead tr th');
	const headers = Array.from(ths)
		.filter(th => window.getComputedStyle(th).display !== 'none')
		.map(th =>
			th.innerText.trim().replace(/\r?\n/g, ' ')  // 줄바꿈 제거
		);
	
	// 아래처럼 reduce 안에서 display: none인 td는 건너뛰도록 조건문을 추가하시면 됩니다:
	const trs = table.querySelectorAll('tbody tr');
	const items = Array.from(trs).map(tr => {
		const tds = tr.querySelectorAll('td');
		let counter = 0;
		return Array.from(tds).reduce((obj, td, idx) => {
			// 숨김 컬럼 건너뛰기
			console.log('idx : ',idx);
			if (window.getComputedStyle(td).display === 'none') {
				return obj;
			}			
			// input/select/textarea 있으면 그 값을, 없으면 innerText
			const formElem = td.querySelector('input, select, textarea');
			const val = formElem
				? formElem.value.trim()
				: td.innerText.trim().replace(/\r?\n/g, ' ');
			obj[`col${counter}`] = val;
			counter++ ;
			// obj[headers[idx] || `col${idx}`] = val;
			// obj[headers[idx]] = val;
			return obj;
		}, {});
	});

	
	console.log('헤더들 ', headers);
	
	console.log(items);

    // 4) payload 에 orderDate, headers, items 담기
    const payload = {
        orderDate: document.getElementById('orderDate').value || '',
        headers:   headers,
        items:     items
    };

    // 5) AJAX 전송
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "account_saveExcel.php", true);
    xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
    xhr.onreadystatechange = () => {
        if (xhr.readyState !== 4) return;
        if (xhr.status === 200) {
            try {
                const res = JSON.parse(xhr.responseText);
                if (res.success) {
                    // 다운로드 페이지로 이동
                    const file = encodeURIComponent(res.filename.split('/').pop());
                    window.location.href = `downloadExcel.php?filename=${file}`;
                } else {
                    console.error('엑셀 생성 실패:', res.message);
                }
            } catch (e) {
                console.error('응답 파싱 오류:', e.message, xhr.responseText);
            }
        } else {
            console.error('서버 오류 상태:', xhr.status);
        }
    };
    xhr.send(JSON.stringify(payload));
}

function calculateSummary() {
  // 입고금액합
  let inputSumTotal = 0;
  for (let i = 1; i <= 7; i++) {
    const $span = $(`#inputSumDisplay${i}`);
    if ($span.length) {
      const val = parseFloat($span.text().replace(/,/g, '')) || 0;
      inputSumTotal += val;
    }
  }

  // 송금액합
  let sendMoneyTotal = 0;
  for (let i = 1; i <= 7; i++) {
    const $input = $(`#sendMoney${i}`);
    if ($input.length) {
      const val = parseFloat($input.val().replace(/,/g, '')) || 0;
      sendMoneyTotal += val;
    }
  }
  // 원화입력 자동합계  
  let send_amount_krw_sum = 0;
  for (let i = 1; i <= 7; i++) {
    const $input = $(`#send_amount_krw${i}`);
    if ($input.length) {
      const val = parseFloat($input.val().replace(/,/g, '')) || 0;
      send_amount_krw_sum += val;
    }
  }
  // 송금수수료 자동 합계  
  let remittance_fee_sum = 0;
  for (let i = 1; i <= 7; i++) {
    const $input = $(`#remittance_fee${i}`);
    if ($input.length) {
      const val = parseFloat($input.val().replace(/,/g, '')) || 0;
      remittance_fee_sum += val;
    }
  }
  // 원화입력 + 송금수수료 자동 합계  
  let krw_plus_remittance_fee_sum = 0;
  for (let i = 1; i <= 7; i++) {
    const $input = $(`#krw_plus_remittance_fee${i}`);
    if ($input.length) {
      const val = parseFloat($input.val().replace(/,/g, '')) || 0;
      krw_plus_remittance_fee_sum += val;
    }
  }

  // 차이 계산 (입고금액 - 송금액)
  const diff = inputSumTotal - sendMoneyTotal;

  // 표시
  $("#totalInputSum").text(inputSumTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
  $("#totalSendMoney").val(sendMoneyTotal.toLocaleString());
  $("#send_amount_krw_sum").val(send_amount_krw_sum.toLocaleString());
  $("#remittance_fee_sum").val(remittance_fee_sum.toLocaleString());
  $("#krw_plus_remittance_fee_sum").val(krw_plus_remittance_fee_sum.toLocaleString());

  const $diffEl = $("#diffAmount");
  $diffEl.text(diff.toLocaleString());

  // 색상 처리
  const $diffCell = $("#gapAmountCell");
  $diffCell.removeClass("text-primary text-danger text-info text-success").addClass("text-dark");
}

// 원화입력 + 송금수수료 합계 계산 함수
function calculateKrwPlusRemittanceFee() {
  let totalSum = 0;
  
  for (let i = 1; i <= 7; i++) {
    // 원화입력 값 가져오기
    const krwValue = parseFloat($(`#send_amount_krw${i}`).val().replace(/,/g, '')) || 0;
    
    // 송금수수료 값 가져오기
    const feeValue = parseFloat($(`#remittance_fee${i}`).val().replace(/,/g, '')) || 0;
    
    // 각 차수별 합계 계산
    const sum = krwValue + feeValue;
    
    // 각 차수별 합계 표시
    $(`#krw_plus_remittance_fee${i}`).val(sum > 0 ? sum.toLocaleString() : '');
    
    // 전체 합계에 추가
    totalSum += sum;
  }
  
  // 전체 합계 표시
  $("#krw_plus_remittance_fee_sum").val(totalSum > 0 ? totalSum.toLocaleString() : '');
}

</script>

<script>
$(document).ready(function() {
    // 1~4차 송금액 input에 이벤트 연결
    for (let i = 1; i <= 7; i++) {
        const $input = $(`#sendMoney${i}`);
        if ($input.length) {
            $input.on('input', function() {
                formatNumber(this);
                calculateSummary();
            });
        }
    }

    // 송금액 합계 input에 이벤트 연결
    const $totalInput = $('#totalSendMoney');
    if ($totalInput.length) {
        $totalInput.on('input', function() {
            formatNumber(this);
            calculateSummary();
        });
    }

    // 원화입력 + 송금수수료 합계 자동 계산 이벤트 연결
    for (let i = 1; i <= 7; i++) {
        // 원화입력 이벤트
        $(`#send_amount_krw${i}`).on('input', function() {
            formatNumber(this);
            calculateKrwPlusRemittanceFee();
        });
        
        // 송금수수료 이벤트
        $(`#remittance_fee${i}`).on('input', function() {
            formatNumber(this);
            calculateKrwPlusRemittanceFee();
        });
    }
});
</script>

<script>
// 중복 함수 정의 제거: 아래 class 기반 함수만 남기고, 앞쪽의 calculateCustomsSums, calculateAllRunningTotals, calculateVerticalRunningTotal 함수 정의는 모두 삭제

// 소계 계산 (class 기반)
function calculateCustomsSums() {
  console.log('=== 소계 계산 시작 ===');
  
  // 부가세
  let vatSum = 0;
  console.log('부가세 입력 요소들:');
  $('.customs_vat').each(function(idx) {
    const rawValue = $(this).val();
    const val = parseFloat(rawValue.replace(/,/g, '')) || 0;
    vatSum += val;
    console.log(`  ${idx}: "${rawValue}" -> ${val}`);
  });
  console.log('부가세 소계 vatSum:', vatSum);
  
  const vatSumElement = $('.customs_vat_sum').last();
  console.log('부가세 소계 요소:', vatSumElement.length > 0 ? '존재' : '없음', vatSumElement.attr('id') || 'ID없음');
  vatSumElement.val(vatSum ? vatSum.toLocaleString() : '');

  // 선임 및 부대비
  let miscSum = 0;
  console.log('선임 및 부대비 입력 요소들:');
  $('.customs_miscellaneous').each(function(idx) {
    const rawValue = $(this).val();
    const val = parseFloat(rawValue.replace(/,/g, '')) || 0;
    miscSum += val;
    console.log(`  ${idx}: "${rawValue}" -> ${val}`);
  });
  console.log('선임 및 부대비 소계 miscSum:', miscSum);
  
  const miscSumElement = $('.customs_miscellaneous_fee_sum').last();
  console.log('선임 및 부대비 소계 요소:', miscSumElement.length > 0 ? '존재' : '없음', miscSumElement.attr('id') || 'ID없음');
  miscSumElement.val(miscSum ? miscSum.toLocaleString() : '');

  // 컨테이너운송
  let containerSum = 0;
  console.log('컨테이너운송 입력 요소들:');
  $('.customs_container_fee').each(function(idx) {
    const rawValue = $(this).val();
    const val = parseFloat(rawValue.replace(/,/g, '')) || 0;
    containerSum += val;
    console.log(`  ${idx}: "${rawValue}" -> ${val}`);
  });
  console.log('컨테이너운송 소계 containerSum:', containerSum);
  
  const containerSumElement = $('.customs_container_fee_sum').last();
  console.log('컨테이너운송 소계 요소:', containerSumElement.length > 0 ? '존재' : '없음', containerSumElement.attr('id') || 'ID없음');
  containerSumElement.val(containerSum ? containerSum.toLocaleString() : '');

  // 통관수수료
  let commissionSum = 0;
  console.log('통관수수료 입력 요소들:');
  $('.customs_commission').each(function(idx) {
    const rawValue = $(this).val();
    const val = parseFloat(rawValue.replace(/,/g, '')) || 0;
    commissionSum += val;
    console.log(`  ${idx}: "${rawValue}" -> ${val}`);
  });
  console.log('통관수수료 소계 commissionSum:', commissionSum);
  
  const commissionSumElement = $('.customs_commission_sum').last();
  console.log('통관수수료 소계 요소:', commissionSumElement.length > 0 ? '존재' : '없음', commissionSumElement.attr('id') || 'ID없음');
  commissionSumElement.val(commissionSum ? commissionSum.toLocaleString() : '');

  // 입고물량대금(CNY) (누계 제외)
  let cnySum = 0;
  console.log('입고물량대금(CNY) 입력 요소들:');
  $('.customs_input_amount_cny').each(function(idx) {
    const rawValue = $(this).val();
    const val = parseFloat(rawValue.replace(/,/g, '')) || 0;
    cnySum += val;
    console.log(`  ${idx}: "${rawValue}" -> ${val}`);
  });
  console.log('입고물량대금(CNY) 소계 cnySum:', cnySum);
  
  const cnySumElement = $('.customs_input_amount_cny_sum').last();
  console.log('입고물량대금(CNY) 소계 요소:', cnySumElement.length > 0 ? '존재' : '없음', cnySumElement.attr('id') || 'ID없음');
  cnySumElement.val(cnySum ? cnySum.toLocaleString() : '');

  console.log('=== 소계 계산 완료, 누계 계산 호출 ===');
  // 누계 계산 호출
  calculateAllRunningTotals();
}

// 누계 계산 (class 기반)
function calculateVerticalRunningTotal(subtotalClass, totalClass) {  
  let runningTotal = 0;

  $(subtotalClass).each(function(idx) {
    const rawValue = $(this).val();
    const val = parseFloat(rawValue.replace(/,/g, '')) || 0;
    runningTotal += val;

    const targetElement = $(totalClass).eq(idx);    
    
    if (targetElement.length > 0) {
      let formattedValue = runningTotal ? runningTotal.toLocaleString() : '';
      if(totalClass == '.customs_miscellaneous_fee_total'){
        runningTotal += parseFloat($('.customs_vat_sum').val().replace(/,/g, '')) || 0;        
        formattedValue = runningTotal ? runningTotal.toLocaleString() : '';
      }
    else if(totalClass == '.customs_container_fee_total'){
        runningTotal += parseFloat($('.customs_vat_sum').val().replace(/,/g, '')) || 0;
        runningTotal += parseFloat($('.customs_miscellaneous_fee_sum').val().replace(/,/g, '')) || 0;        
        formattedValue = runningTotal ? runningTotal.toLocaleString() : '';        
    }
    else if(totalClass == '.customs_commission_total'){
        runningTotal += parseFloat($('.customs_vat_sum').val().replace(/,/g, '')) || 0;
        runningTotal += parseFloat($('.customs_miscellaneous_fee_sum').val().replace(/,/g, '')) || 0;
        runningTotal += parseFloat($('.customs_container_fee_sum').val().replace(/,/g, '')) || 0;        
        formattedValue = runningTotal ? runningTotal.toLocaleString() : '';
    }
      else{
        formattedValue = runningTotal ? runningTotal.toLocaleString() : '';
      }
      targetElement.val(formattedValue);
      
    } else {
      console.warn(`대상 요소 ${idx}를 찾을 수 없음`);
    }
  });
  
}

function calculateAllRunningTotals() {
  console.log('=== 모든 누계 계산 시작 ===');
  
  calculateVerticalRunningTotal('.customs_vat_sum', '.customs_vat_total');
  calculateVerticalRunningTotal('.customs_miscellaneous_fee_sum', '.customs_miscellaneous_fee_total');
  calculateVerticalRunningTotal('.customs_container_fee_sum', '.customs_container_fee_total');
  calculateVerticalRunningTotal('.customs_commission_sum', '.customs_commission_total');
  
  console.log('=== 모든 누계 계산 완료 ===');
  // 입고물량대금(CNY)은 누계 계산에서 제외
  
  // 통관비용 차수별 합계 계산 추가
  calculateCustomsDetailTotals();
}

// 통관비용 차수별 합계 계산 함수
function calculateCustomsDetailTotals() {
  console.log('=== 통관비용 차수별 합계 계산 시작 ===');
  
  for (let i = 1; i <= 7; i++) {
    let total = 0;
    
    // 부가세
    const vatValue = parseFloat($(`#customs_vat${i}`).val().replace(/,/g, '')) || 0;
    total += vatValue;
    
    // 선임 및 부대비
    const miscValue = parseFloat($(`#customs_miscellaneous_fee${i}`).val().replace(/,/g, '')) || 0;
    total += miscValue;
    
    // 컨테이너운송
    const containerValue = parseFloat($(`#customs_container_fee${i}`).val().replace(/,/g, '')) || 0;
    total += containerValue;
    
    // 통관수수료
    const commissionValue = parseFloat($(`#customs_commission${i}`).val().replace(/,/g, '')) || 0;
    total += commissionValue;
    
    // 결과를 해당 필드에 설정
    const formattedValue = total > 0 ? total.toLocaleString() : '';
    $(`#customs_detail_total${i}`).val(formattedValue);
    
    console.log(`차수 ${i} 합계: ${total.toLocaleString()} (부가세: ${vatValue}, 선임: ${miscValue}, 컨테이너: ${containerValue}, 수수료: ${commissionValue})`);
  }
  
  console.log('=== 통관비용 차수별 합계 계산 완료 ===');
}

// 이벤트 리스너 등에서 정리된 함수만 호출하도록 유지
$(document).ready(function() {
  for (let i = 1; i <= 7; i++) {
    $(`#customs_vat${i}`).on('input', function() {
      formatNumber(this);
      calculateCustomsSums();
    });
    $(`#customs_miscellaneous_fee${i}`).on('input', function() {
      formatNumber(this);
      calculateCustomsSums();
    });
    $(`#customs_container_fee${i}`).on('input', function() {
      formatNumber(this);
      calculateCustomsSums();
    });
    $(`#customs_commission${i}`).on('input', function() {
      formatNumber(this);
      calculateCustomsSums();
    });
    $(`#customs_input_amount_cny${i}`).on('input', function() {
      formatNumber(this);
      calculateCustomsSums();
    });
  }
  calculateCustomsSums();
});
// ... 기존 중복 함수 정의 모두 제거 ...
</script>
</body>
</html>