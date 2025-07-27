<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");

$mode = isset($_POST['mode']) ? $_POST['mode'] : '';
$num = isset($_POST['num']) ? $_POST['num'] : '';
$tablename = isset($_POST['tablename']) ? $_POST['tablename'] : '';

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

if ($mode === 'update' && $num) {
    try {
        $sql = "SELECT * FROM " . $tablename . " WHERE num=?";
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $num, PDO::PARAM_INT);
        $stmh->execute();
        $row = $stmh->fetch(PDO::FETCH_ASSOC);

        $content = $row['content'];  // 저장된 content 값을 가져옴
        include '_row.php';
		// 콤마 제거 후 숫자로 변환
		$amount = floatval(str_replace(',', '', $row['amount']));
    } catch (PDOException $Exception) {
        echo "오류: " . $Exception->getMessage();
        exit;
    }
} else {
    include '_request.php';
    $mode = 'insert';
    $registDate = date('Y-m-d');
    $inoutsep = '지출';
    $amount = 0;
    $content = '';  // 기본값 설정
}

$title_message = ($mode === 'update') ? '지출 계획 수정' : '지출 계획 신규 등록';

?>

<input type="hidden" id="update_log" name="update_log" value="<?=$update_log?>">
<input type="hidden" id="first_writer" name="first_writer" value="<?=$first_writer?>">

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
                                    <td class="text-center fs-6 fw-bold" style="width:150px;">등록일자</td>
                                    <td class="text-center" style="width:200px;">
                                        <input type="date" class="form-control fs-6" id="registDate" name="registDate" style="width:130px;" value="<?=$registDate?>">
                                    </td>
                                    <td class="text-center fs-6 fw-bold" style="width:150px;">구분</td>
                                    <td class="text-center" style="width:200px;">
                                        <div>
                                            <input type="radio" class="form-check-input" id="expense" name="inoutsep" value="지출" <?= $inoutsep === '지출' ? 'checked' : '' ?>>
                                            <label for="expense" class="form-check-label fs-6">지출</label>
                                        </div>
                                    </td>
                                </tr>             
                                <tr>
                                    <td class="text-center fs-6 fw-bold">내역</td>
                                    <td class="text-start" colspan="3">
                                        <input type="text" class="form-control fs-6" id="content" name="content" value="<?=$content?>" autocomplete="off">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center fs-6 fw-bold" style="width:150px;">금액</td>
                                    <td class="text-center">
                                        <input type="text" class="form-control fs-6 text-end" id="amount" name="amount" value="<?= isset($amount) ? number_format($amount) : '' ?>" autocomplete="off" onkeyup="inputNumberFormat(this)">
                                    </td>
                                    <td class="text-center" colspan="2">                                                                                
                                    </td>                                    
                                </tr>
                                <tr>
                                    <td class="text-center fs-6 fw-bold" style="width:150px;">예상지급일</td>
                                    <td class="text-center">
                                        <input type="date" class="form-control fs-6 text-end" id="ForeDate" name="ForeDate" value="<?= isset($ForeDate) ? $ForeDate : '' ?>" >
                                    </td>
                                    <td class="text-center" colspan="2">                                                                                
                                    </td>                                    
                                </tr>
                                <tr>
                                    <td class="text-center fs-6 fw-bold" style="width:150px;">비고</td>
                                    <td class="text-center" colspan="3">
                                        <input type="text" class="form-control fs-6" id="memo" name="memo" value="<?=$memo?>" autocomplete="off">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="d-flex justify-content-center">
                    <button type="button" id="saveBtn" class="btn btn-dark btn-sm me-3">
                        <i class="bi bi-floppy-fill"></i> 저장
                    </button>
                    <?php if($mode != 'insert') { ?>
                    <button type="button" id="deleteBtn" class="btn btn-danger btn-sm me-3">
                        <i class="bi bi-trash"></i>  삭제 
                    </button>
                    <?php } ?>
                    <button type="button" id="closeBtn" class="btn btn-outline-dark btn-sm me-2">
                        &times; 닫기
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
