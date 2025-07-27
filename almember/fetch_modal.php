<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

$mode = isset($_POST['mode']) ? $_POST['mode'] : '';
$num = isset($_POST['num']) ? $_POST['num'] : '';

$tablename = 'almember';
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

if ($mode === 'update' && $num) {
    try {
        $sql = "SELECT * FROM ". $DB . "." . $tablename . " WHERE num=?";
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $num, PDO::PARAM_INT);      
        $stmh->execute();
        $row = $stmh->fetch(PDO::FETCH_ASSOC);

        include '_row.php';
    } catch (PDOException $Exception) {
        echo "오류: ".$Exception->getMessage();
        exit;
    }
}
else {
	include '_row.php';	
    $mode = 'insert';	
	$dateofentry = date('Y-m-d');
	$referencedate = date('Y');
}

$title_message = ($mode === 'update') ? '연차 수정' : '연차 신규 등록';
?>

<input type="hidden" id="update_log" name="update_log" value="<?=$update_log?>">   
<input type="hidden" id="secondordnum" name="secondordnum" value="<?=$secondordnum?>">   

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
                                    <td class="text-center   fw-bold"  style="width:200px;" >성명</td>
                                    <td class="text-center">
                                        <input type="text" class="form-control" id="name" name="name"  autocomplete="off"  value="<?=$name?>">
                                    </td>                                    
                                    <td colspan="2">                                    
                                    </td>                                    
                                </tr>
                                <tr>
                                    <td class="text-center   fw-bold"  style="width:200px;" > 소속 </td>
                                    <td class="text-center">
                                        <input type="text" class="form-control" id="company" name="company"  autocomplete="off"  value="<?=$company?>">
                                    </td>
                                    <td class="text-center   fw-bold"  style="width:200px;"  >부서</td>
                                    <td class="text-center">
                                        <input type="text" class="form-control  " id="part" name="part" style="width:130px;"  autocomplete="off" value="<?=$part?>">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center   fw-bold" >입사일</td>
                                    <td class="text-center">
                                        <input type="date" class="form-control  " id="dateofentry" name="dateofentry"  autocomplete="off" value="<?=$dateofentry?>">
                                    </td>
                                    <td class="text-center   fw-bold" >해당연도</td>
                                    <td class="text-center">
                                        <input type="text" class="form-control  " id="referencedate" name="referencedate"  autocomplete="off" value="<?=$referencedate?>">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center   fw-bold" >연차 발생일수</td>
                                    <td class="text-center">
                                        <input type="text" class="form-control  " id="availableday" name="availableday" value="<?=$availableday?>"  autocomplete="off" onkeyup="inputNumberFormat(this)">
                                    </td>
                                    <td class="text-center   fw-bold" >비고</td>
                                    <td class="text-center">
                                        <input type="text" class="form-control  " id="comment" name="comment" autocomplete="off" value="<?=$comment?>">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="d-flex justify-content-center">
                    <button type="button" id="saveBtn" class="btn btn-dark btn-sm me-3">
                        <ion-icon name="save-outline"></ion-icon> 저장 
                    </button>
                    <button type="button" id="deleteBtn" class="btn btn-danger btn-sm me-3">
                        <ion-icon name="trash-outline"></ion-icon> 삭제
                    </button>
                    <button type="button" id="closeBtn" class="btn btn-outline-dark btn-sm me-2">
                        <ion-icon name="close-circle-outline"></ion-icon> Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
