<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

$mode = isset($_POST['mode']) ? $_POST['mode'] : '';
$num = isset($_POST['num']) ? $_POST['num'] : '';
$seldate = isset($_POST['seldate']) ? $_POST['seldate'] :  date("Y-m-d", time());

$tablename = 'todos_account';
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

$orderdate = $seldate;
$deadline = '';
$towhom = '';
$reply = '';
$work_status = '작성';
$title = '';
$first_writer = $user_name;

if ($mode === 'update' && $num !=='undefined' ) {
    try {
        $sql = "SELECT * FROM ". $DB . "." . $tablename . " WHERE num=?";
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $num, PDO::PARAM_INT);      
        $stmh->execute();
        $row = $stmh->fetch(PDO::FETCH_ASSOC);

        include $_SERVER['DOCUMENT_ROOT'] . '/todo_account/_row.php';
    } catch (PDOException $Exception) {
        echo "오류: ".$Exception->getMessage();
        exit;
    }
} else {
    $mode = 'insert';	    
}

$title_message = ($mode === 'update') ? '회계일정 수정' : '회계일정 신규 등록';


?>

<input type="hidden" id="update_log" name="update_log" value="<?= isset($update_log) ? $update_log : '' ?>">   
<input type="hidden" id="num" name="num" value="<?= isset($num) ? $num : '' ?>">   
<input type="hidden" id="searchtag" name="searchtag" value="<?= isset($searchtag) ? $searchtag : '' ?>">   
<input type="hidden" id="user_name" name="user_name" value="<?= isset($user_name) ? $user_name : '' ?>">   
<input type="hidden" id="first_writer" name="first_writer" value="<?= isset($first_writer) ? $first_writer : '' ?>">   
<input type="hidden" id="towhom" name="towhom" value="<?= $towhom ?>">   

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
                                    <td class="text-center fs-6 fw-bold" style="width:150px;">일자</td>
                                    <td class="text-center" style="width:120px;">
                                        <input type="date" class="form-control fs-6" id="orderdate" name="orderdate" style="width:130px;" value="<?= $orderdate ?>">
                                    </td>
                                    <td class="text-center fs-6 fw-bold" style="width:150px;">작성인</td>
                                    <td class="text-center">
                                        <input type="text" class="form-control fs-6" id="first_writer" name="first_writer" value="<?= $first_writer ?>" autocomplete="off">
                                    </td>
                                </tr>
							    <tr>
                                    <td class="text-center fs-6 fw-bold" style="width:150px;">내용</td>
                                    <td class="text-center" colspan="3">
                                        <input type="text" class="form-control fs-6" id="title" name="title" value="<?= $title ?>" autocomplete="off">
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
    </div>
</div>

