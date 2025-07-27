<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

$mode = isset($_POST['mode']) ? $_POST['mode'] : '';
$num = isset($_POST['num']) ? $_POST['num'] : '';
$plan_month = isset($_POST['plan_month']) ? $_POST['plan_month'] . '-01' : date("Y-m-01");
$seldate = isset($_POST['seldate']) ? $_POST['seldate'] :  date("Y-m-d", time());
$first_writer = $_SESSION['user_name'] ?? '';  

// 월간 업무일지 계획

require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

$monthlyPlan = '';

try {    
    // 해당 월의 계획이 있는지 먼저 확인
    $sql = "SELECT * FROM monthly_work WHERE plan_month = ? AND first_writer = ? order by num desc limit 1";
    $stmh = $pdo->prepare($sql);
    $stmh->bindValue(1, $plan_month, PDO::PARAM_STR);
    $stmh->bindValue(2, $user_name, PDO::PARAM_STR);
    $stmh->execute();
    $row = $stmh->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        $monthlyPlan = $row['monthlyPlan'];  // 이미 존재하면 해당 월간 계획을 가져옴        
    } 
	
} catch (PDOException $Exception) {
    echo "오류: " . $Exception->getMessage();
    exit;
}

// print_r($monthlyPlan);

$tablename = 'todos_work';

$orderdate = $seldate;
$deadline = '';
$towhom = '';
$reply = '';
$work_status = '작성';
$title = '';
$title_after = '';
$first_writer = $user_name;

if ($mode === 'update' && $num !=='undefined' ) {
    try {
		if($level == '1') // 관리자이면 전부 다 보여주고,
		{
			$sql = "SELECT * FROM ". $DB . "." . $tablename . " WHERE num=?";
			$stmh = $pdo->prepare($sql);
			$stmh->bindValue(1, $num, PDO::PARAM_INT);      
		}
		else
		{
			$sql = "SELECT * FROM ". $DB . "." . $tablename . " WHERE num=? and first_writer=? ";
			$stmh = $pdo->prepare($sql);
			$stmh->bindValue(1, $num, PDO::PARAM_INT);      
			$stmh->bindValue(2, $user_name, PDO::PARAM_INT);      
		}
        
        $stmh->execute();
        $row = $stmh->fetch(PDO::FETCH_ASSOC);

        include $_SERVER['DOCUMENT_ROOT'] . '/todo_work/_row.php';
    } catch (PDOException $Exception) {
        echo "오류: ".$Exception->getMessage();
        exit;
    }
} else {
    $mode = 'insert';	    
}
$title_message = ($mode === 'update') ? '업무일지 수정' : '업무일지 신규 등록';
?>

<input type="hidden" id="update_log" name="update_log" value="<?= isset($update_log) ? $update_log : '' ?>">   
<input type="hidden" id="num" name="num" value="<?= isset($num) ? $num : '' ?>">   
<input type="hidden" id="searchtag" name="searchtag" value="<?= isset($searchtag) ? $searchtag : '' ?>">   
<input type="hidden" id="user_name" name="user_name" value="<?= isset($user_name) ? $user_name : '' ?>">   
<input type="hidden" id="first_writer" name="first_writer" value="<?= isset($first_writer) ? $first_writer : '' ?>">   
<input type="hidden" id="towhom" name="towhom" value="<?= $towhom ?>">   
<!-- Modal Form for Monthly Plan -->
<input type="hidden" id="plan_month" name="plan_month" value="<?= $plan_month ?>">
	<div class="card justify-content-center">
		<div class="card-header text-center">
				<span class="text-center fs-5"><?= $title_message ?></span>
		</div>
            <div class="card-body">
				<div class="row justify-content-center text-center">
					<div class="d-flex align-items-center justify-content-center m-2">
						<table class="table table-bordered"  style="border: 2px solid;">
							<tbody>
								<tr>
									<td class="text-center fs-6 fw-bold" style="width:150px;">월간계획</td>
								<td class="text-center" colspan="3">
									<textarea class="form-control fs-6" id="monthlyPlan" name="monthlyPlan" style="height:150px;" autocomplete="off"><?= htmlspecialchars($monthlyPlan, ENT_QUOTES, 'UTF-8') ?></textarea>
								</td>
								</tr>
							</tbody>
						</table>									
					</div>
				</div>

				<div class="row justify-content-center text-center">
                    <div class="d-flex align-items-center justify-content-center m-2">						
						<table class="table table-bordered">
							<tbody>
								<tr>
									<td class="text-center fs-6 fw-bold" style="width:150px;">일자</td>
									<td class="text-center" style="width:120px;">
										<input type="date" class="form-control fs-6" id="orderdate" name="orderdate" style="width:130px;" value="<?= $orderdate ?>">
									</td>
									<td class="text-center fs-6 fw-bold" style="width:150px;">작성인</td>
									<td class="text-center">
										<input type="text" class="form-control fs-6 w100px" id="first_writer" name="first_writer" value="<?= $first_writer ?>" autocomplete="off">
									</td>
								</tr>
								<tr>
									<td class="text-center fs-6 fw-bold" style="width:150px;">
										오전일정
										<i class="bi bi-x-circle text-danger" id="removeMorning"></i>
									</td>
									<td class="text-center" colspan="3">
										<textarea class="form-control fs-6" id="title" name="title" style="height:100px;" autocomplete="off"><?= $title ?></textarea>
									</td>
								</tr>
								<tr>
									<td class="text-center fs-6 text-dark fw-bold" style="width:150px;">
										<input type="checkbox" id="sameAsMorning" class="form-check-input"> 상동
										<br><br>
										오후일정
										<i class="bi bi-x-circle text-danger" id="removeAfternoon"></i>
									</td>
									<td class="text-center" colspan="3">
										<textarea class="form-control fs-6" id="title_after" name="title_after" style="height:100px;" autocomplete="off"><?= $title_after ?></textarea>
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

					<button type="button" class="btn btn-outline-dark btn-sm me-3" id="showlogBtn"   > H
					</button>						
                    <?php } ?>
										
                    <button type="button" id="closeBtn" class="btn btn-outline-dark btn-sm me-2">
                        &times; 닫기
                    </button>
                </div>
            </div>
		</div>

