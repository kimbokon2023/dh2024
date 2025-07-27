<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");

$mode = isset($_POST['mode']) ? $_POST['mode'] : '';
$num = isset($_POST['num']) ? $_POST['num'] : '';

// Now, $mode will contain 'modify', and $num will contain the value from the data attribute
// echo "Mode: " . $mode . "<br>";
// echo "Num: " . $num . "<br>";

$tablename = 'todos_monthly';
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

try {
    // Prepare SQL query to fetch data from todos_monthly table
    if ($mode === 'modify' && $num) {
        $sql = "SELECT * FROM " . $tablename . " WHERE num=?";
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $num, PDO::PARAM_INT);
        $stmh->execute();
        $row = $stmh->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Existing record
            $title = $row['title'];
            $registdate = $row['registdate'];
            $itemsep = $row['itemsep'];
            $specialday = $row['specialday'];
            $first_writer = $row['first_writer'];
            $update_log = $row['update_log'];
            $searchtag = $row['searchtag'];
        } else {
            echo "Record not found.";
            exit;
        }
    } else {
        // New record
        $title = '';
        $registdate = date('Y-m-d');
        $itemsep = '';
        $specialday = '';
        $first_writer = $user_name ;
        $update_log = '';
        $searchtag = '';
    }
} catch (PDOException $Exception) {
    echo "오류: " . $Exception->getMessage();
    exit;
}


$incomeOptions = [
    '거래처 수금' => '거래처에서 입금한 금액',
    '최초 현금 입력' => '금전출납부 시작'
];

$expenseOptions = [
    '급여(인건비)' => '직원 급여',
    '접대비' => '경조사비용',
    '통신비' => '전화요금, 인터넷요금',
    '세금과공과금' => '등록면허세, 취득세, 재산세등 각종세금',
    '차량유지비' => '유류대, 통행료',
    '보험료' => '차량보험료, 화재보험료등',
    '운반비' => '택배운반비외 각종운반비',
    '소모품비' => '각종 소모품 비용',
    '수수료비용' => '이체수수료, 등기수수료등',
    '복리후생비' => '직원 식대외 직원 작업복등',
    '개발비' => '프로그램 개발비용',
    '이자비용' => '이자비용',
    '카드대금' => '카드대금',
    '통관비' => '통관비',
    '자재비' => '자재비',
    '기계장치' => '기계구입',
    '선급금' => '미리 지급하는 금액',
	'지급임차료' => '지급임차료',
	'지급수수료' => '지금수수료'	
];

?>

<input type="hidden" id="update_log" name="update_log" value="<?= isset($update_log) ? $update_log : '' ?>">   
<input type="hidden" id="num" name="num" value="<?= isset($num) ? $num : '' ?>">   
<input type="hidden" id="searchtag" name="searchtag" value="<?= isset($searchtag) ? $searchtag : '' ?>">   
<input type="hidden" id="user_name" name="user_name" value="<?= isset($user_name) ? $user_name : '' ?>">   
<input type="hidden" id="first_writer" name="first_writer" value="<?= isset($first_writer) ? $first_writer : '' ?>">   


<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-center">
        <div class="card justify-content-center">
            <div class="card-header text-center">
                <span class="text-center fs-5"><?= $mode === 'update' ? '회계 월별 할일 수정' : '회계 월별 할일 신규 등록' ?></span>
            </div>
            <div class="card-body">
                <div class="row justify-content-center text-center">
                    <div class="d-flex align-items-center justify-content-center m-2">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td class="text-center fs-6 fw-bold" style="width:150px;">등록일자</td>
                                    <td class="text-center" style="width:200px;">
                                        <input type="date" class="form-control fs-6" id="registdate" name="registdate" style="width:130px;" value="<?= htmlspecialchars($registdate) ?>">
                                    </td>                                    
                                    <td class="text-center fs-6 fw-bold" style="width:150px;">작성자</td>
                                    <td class="text-center" colspan="1">
                                        <input type="text" class="form-control fs-6" id="first_writer" name="first_writer" value="<?= htmlspecialchars($first_writer) ?>" readonly>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center fs-6 fw-bold" style="width:150px;">매월</td>
									<td class="text-center" style="width:200px;">
									  <div class="d-flex align-items-center justify-content-center ">
											<select class="form-control fs-6" style="width:60px;" id="specialday" name="specialday">
												<?php for ($day = 1; $day <= 31; $day++): ?>
													<option value="<?= $day ?>" <?= $specialday == $day ? 'selected' : '' ?>>
														<?= $day ?>
													</option>
												<?php endfor; ?>
											</select>
											&nbsp;<span class="fs-6" > 일 </span>
										</div>
									</td>
                                    <td class="text-center fs-6 fw-bold" style="width:150px;">항목</td>
									<td class="text-center" colspan="3">
										<select class="form-control fs-6" id="itemsep" name="itemsep">
											<optgroup label="수입 항목">
												<?php foreach ($incomeOptions as $key => $value): ?>
													<option value="<?= htmlspecialchars($key) ?>" <?= $itemsep === $key ? 'selected' : '' ?>>
														<?= htmlspecialchars($key) ?>
													</option>
												<?php endforeach; ?>
											</optgroup>
											<optgroup label="지출 항목">
												<?php foreach ($expenseOptions as $key => $value): ?>
													<option value="<?= htmlspecialchars($key) ?>" <?= $itemsep === $key ? 'selected' : '' ?>>
														<?= htmlspecialchars($key) ?>
													</option>
												<?php endforeach; ?>
											</optgroup>
										</select>
									</td>

                                </tr>
                                <tr>
                                    <td class="text-center fs-6 fw-bold" style="width:150px;">내용</td>
                                    <td class="text-center" colspan="3" >
                                        <input type="text" class="form-control fs-6" id="title" name="title" value="<?= htmlspecialchars($title) ?>" autocomplete="off">
                                    </td>
                                </tr>                                
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="d-flex justify-content-center">
                    <button type="button" id="saveBtn_month" class="btn btn-dark btn-sm me-3">
                        <i class="bi bi-floppy-fill"></i> 저장
                    </button>
                    <?php if ($mode === 'modify') { ?>
                    <button type="button" id="deleteBtn_month" class="btn btn-danger btn-sm me-3">
                        <i class="bi bi-trash"></i>  삭제 
                    </button>
                    <?php } ?>
                    <button type="button" id="closeBtn_month" class="btn btn-outline-dark btn-sm me-2">
                        &times; 닫기
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

