<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

$mode = isset($_POST['mode']) ? $_POST['mode'] : '';
$num = isset($_POST['num']) ? $_POST['num'] : '';

$tablename = 'account';
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

if ($mode === 'update' && $num) {
    try {
        $sql = "SELECT * FROM ". $DB . "." . $tablename . " WHERE num=?";
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $num, PDO::PARAM_INT);      
        $stmh->execute();
        $row = $stmh->fetch(PDO::FETCH_ASSOC);

		include $_SERVER['DOCUMENT_ROOT'] . '/account/_row.php';    
		$amount = floatval(str_replace(',', '', $row['amount'])); 	
		$amount = number_format($amount);
    } catch (PDOException $Exception) {
        echo "오류: ".$Exception->getMessage();
        exit;
    }
}

$title_message = ($mode === 'update') ? '수금 내역' : '수금 내역 등록';

?>

<input type="hidden" id="update_log" name="update_log" value="<?=$update_log?>">   
 
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-center">
            <div class="card justify-content-center">
                <div class="card-header text-center">
                    <span class="text-center fs-5"><?=$title_message?></span>
                </div>
                <div class="card-body">
                    <div class="row justify-content-center text-center">
                        <div class="d-flex align-items-center justify-content-center m-2">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <td class="text-center fs-6 fw-bold" style="width:150px;">거래처명</td>
                                        <td class="text-center" style="width:500px;">
										   <div class="d-flex align-items-center justify-content-center ">
												<input type="text" class="form-control fs-6 me-1 w-90" id="content_detail" name="content_detail" value="<?=$content_detail?>" onkeydown="if(event.keyCode == 13) { phonebookBtn('secondord'); }"   autocomplete="off" >												
											</div>
                                        </td>
                                        <td class="text-center fs-6 fw-bold" style="width:200px;">거래처코드</td>
                                        <td class="text-center" >
                                            <input type="type" class="form-control fs-6" id="secondordnum" name="secondordnum"  readonly value="<?=$secondordnum?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center fs-6 fw-bold" style="width:150px;">등록일자</td>
                                        <td class="text-center" >
                                            <input type="date" class="form-control fs-6" id="registDate" name="registDate" style="width:130px;" value="<?=$registDate?>">
                                        </td>
                                        <td class="text-center fs-6 fw-bold" style="width:200px;">수금액</td>
                                        <td class="text-center" style="width:500px;">
                                            <input type="type" class="form-control fs-6" id="amount" name="amount" style="width:130px;" value="<?=$amount?>" onkeyup="inputNumberFormat(this)">
                                        </td>
                                    </tr>
									<tr>									
                                        <td class="text-center fs-6 fw-bold" style="width:150px;">비고</td>
                                        <td class="text-center" colspan="3">
                                            <input type="text" class="form-control fs-6" id="memo" name="memo" value="<?=$memo?>" autocomplete="off" >
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="d-flex justify-content-center">                        
                        <button type="button" id="closeBtn" class="btn btn-outline-dark btn-sm me-2">
                            &times; 닫기
                       </button>
                </div>
            </div>
        </div>
    </div>
</div>
