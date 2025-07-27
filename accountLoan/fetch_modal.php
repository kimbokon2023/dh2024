<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");

$mode = isset($_POST['mode']) ? $_POST['mode'] : '';
$num = isset($_POST['num']) ? $_POST['num'] : '';

$tablename = 'accountLoan';
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

if ($mode === 'update' && $num) {
    try {
        $sql = "SELECT * FROM " . $tablename . " WHERE num=?";
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $num, PDO::PARAM_INT);
        $stmh->execute();
        $row = $stmh->fetch(PDO::FETCH_ASSOC);

        include '_row.php';

    } catch (PDOException $Exception) {
        echo "오류: " . $Exception->getMessage();
        exit;
    }
} else {
    $mode = 'insert';
    $loanStartDate = date('Y-m-d');
    $bank = '';
    $loanAmount = '';
    $content = '';
    $interestRate = '';
    $interestPaymentDate = '';
    $memo = '';
    $crew = '';
    $crewphone = '';
    $loanAccount = '';
    $interestAccount = '';
    $maturityDate = date('Y-m-d');
    $is_deleted = null ;
}

$title_message = ($mode === 'update') ? '대출 정보 수정' : '대출 정보 신규 등록';


// 수입/지출 계정 정보 가져오기
include $_SERVER['DOCUMENT_ROOT'] . '/account/fetch_options.php';
  
// 선택된 항목의 세부항목 가져오기
$selectedKey = '차입금';
$details = null;

if ($selectedKey) {
	// 수입에서 검색
	if (isset($jsonData['수입'][$selectedKey])) {
		$details = $jsonData['수입'][$selectedKey]['하위계정'];
	}
	// // 지출에서 검색
	// if (isset($jsonData['지출'][$selectedKey])) {
		// $details = $jsonData['지출'][$selectedKey]['하위계정'];
	// }
}  

// '개인대출'과 '주일기업' 등 키만 추출
$Suboptions = [];
if ($details) {
    foreach ($details as $detail) {
        foreach ($detail as $key => $value) {
            $Suboptions[] = $key;
        }
    }
}

// echo '<pre>';
// print_r($Suboptions);
// echo '</pre>';
// echo '<pre>';
// print_r($contentSub);
// echo '</pre>';

// $Suboptions 배열을 키를 기준으로 오름차순 정렬
ksort($Suboptions); 

?>

<input type="hidden" id="num" name="num" value="<?= $num ?>">
<input type="hidden" id="is_deleted" name="is_deleted" value="<?= $is_deleted ?>">

<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-center">
        <div class="card justify-content-center">
            <div class="card-header text-center">
                <span class="text-center fs-5"><?= $title_message ?></span>
            </div>
            <div class="card-body">
                <div class="row justify-content-center text-center">
                    <div class="d-flex align-items-center justify-content-center m-2">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td class="text-center fs-6 fw-bold" style="width:100px;">대출일</td>
                                    <td class="text-center">
                                        <input type="date" class="form-control fs-6  w130px" id="loanStartDate" name="loanStartDate" value="<?= $loanStartDate ?>">
                                    <td class="text-center fs-6 fw-bold">만기일</td>
                                    <td class="text-center">
                                        <input type="date" class="form-control fs-6  w130px" id="maturityDate" name="maturityDate" value="<?= $maturityDate ?>">
                                    </td>
                                </tr>
                                <tr>
                                    </td>
                                    <td class="text-center fs-6 fw-bold">차입계정</td>
                                    <td class="text-center">
										<div class="d-flex justify-content-start align-items-center">											
											<select id="bank" name="bank" class="form-select form-select-sm mx-1 d-block w-auto mx-1" >
												<?php foreach ($Suboptions as $value): // 키를 사용하지 않고 값만 사용 ?>
													<option value="<?= htmlspecialchars($value) ?>" <?= $bank === $value ? 'selected' : '' ?>>
														<?= htmlspecialchars($value) ?>
													</option>
												<?php endforeach; ?>
											</select>																						
										</div>
                                    </td>
                                    <td class="text-center fs-6 fw-bold">차입금</td>
                                    <td class="text-center">
                                        <input type="text" class="form-control fs-6 text-end w130px" id="loanAmount" name="loanAmount" value="<?= $loanAmount ?>" onkeyup="inputNumberFormat(this)">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center fs-6 fw-bold">용도</td>
                                    <td class="text-center" colspan="3">
                                        <input type="text" class="form-control fs-6" id="content" name="content" value="<?= $content ?>">
                                    </td>                                    
                                </tr>
                                <tr>
                                    <td class="text-center fs-6 fw-bold">담당자</td>
                                    <td class="text-center">
                                        <input type="text" class="form-control fs-6 w100px" id="crew" name="crew" value="<?= $crew ?>">
                                    </td>
                                    <td class="text-center fs-6 fw-bold">담당자<br> 연락처</td>
                                    <td class="text-center">
                                        <input type="text" class="form-control fs-6" id="crewphone" name="crewphone" value="<?= $crewphone ?>">
                                    </td>
                                </tr>
                                <tr>									
                                    <td class="text-center fs-6 fw-bold">이자율(%)</td>
                                    <td class="text-center">
                                        <input type="text" class="form-control fs-6 text-end w50px" id="interestRate" name="interestRate" value="<?= $interestRate ?>" onkeyup="inputNumberFormat(this)"> 
                                    </td>
								<td class="text-center fs-6 fw-bold">이자<br>납입일</td>
								<td class="text-center">
									<select id="interestPaymentDate" name="interestPaymentDate"  class="form-select form-select-sm mx-1 d-block w-auto mx-1" >
										<option value="">선택</option>
										<?php 
										for ($i = 1; $i <= 31; $i++) {
											$selected = ($interestPaymentDate == $i) ? 'selected' : '';
											echo "<option value='{$i}' {$selected}>매월 {$i}일</option>";
										}
										?>
									</select>
								</td>

                                </tr>
                                <tr>
                                    <td class="text-center fs-6 fw-bold">대출계좌</td>
                                    <td class="text-center">
                                        <input type="text" class="form-control " id="loanAccount" name="loanAccount" value="<?= $loanAccount ?>">
                                    </td>
                                    <td class="text-center fs-6 fw-bold">이자계좌</td>
                                    <td class="text-center">
                                        <input type="text" class="form-control " id="interestAccount" name="interestAccount" value="<?= $interestAccount ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center fs-6 fw-bold">비고</td>
                                    <td class="text-center" colspan="3">
                                        <input type="text" class="form-control fs-6" id="memo" name="memo" value="<?= $memo ?>">
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
                    <?php if ($mode !== 'insert') { ?>
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
