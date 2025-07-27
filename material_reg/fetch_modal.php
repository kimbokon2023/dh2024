<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

$mode = isset($_POST['mode']) ? $_POST['mode'] : '';
$num = isset($_POST['num']) ? $_POST['num'] : '';

$tablename = 'material_reg';
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
    include '_request.php';
    $mode = 'insert';    
    $registedate = date('Y-m-d');
    $inoutdate = date('Y-m-d');
    $secondord = '안린';
}

$title_message = ($mode === 'update') ? '입고 수정' : '입고 신규 등록';
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
                                    <td class="text-center fs-6 fw-bold" style="width:150px;">등록일자</td>
                                    <td class="text-center" style="width:200px;">
                                        <input type="date" class="form-control fs-6" id="registedate" name="registedate" style="width:130px;" value="<?=$registedate?>">
                                    </td>
                                    <td class="text-center fs-6 fw-bold" style="width:150px;">입고 일자</td>
                                    <td class="text-center" style="width:500px;">
                                        <input type="date" class="form-control fs-6" id="inoutdate" name="inoutdate" style="width:130px;" value="<?=$inoutdate?>">
                                    </td>
                                </tr>
                                <?php // if ($mode === 'insert') { ?>
                                <tr>
                                    <td colspan="4" class="text-center">
                                        <input type="checkbox" id="continuous_registration" name="continuous_registration">
                                        <label for="continuous_registration" class="fs-6 fw-bold">자료연속등록</label>
                                    </td>
                                </tr>
                                <?php // } ?>
                                <tr>
                                    <td class="text-center fs-6 fw-bold" style="width:150px;">품목코드</td>
                                    <td class="text-center">
                                        <div class="specialinputWrap">
                                            <input type="text" class="form-control inputcode fs-6" id="inout_item_code" name="inout_item_code" value="<?=$inout_item_code?>" autocomplete="off">
                                            <button class="specialbtnClear"></button>
                                        </div>
                                    </td>

                                    <td class="text-center fs-6 fw-bold" style="width:150px;">품목명</td>
                                    <td class="text-center" style="width:400px;">
                                        <div class="specialinputWrap">
                                        <input type="text" class="form-control inputitemname fs-6" id="item_name" name="item_name" value="<?=$item_name?>" autocomplete="off">
                                        <button class="specialbtnClear"></button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center fs-6 fw-bold" style="width:150px;">매입처</td>
                                    <td class="text-center" style="width:400px;">
                                       <div class="d-flex">
                                        <input class="form-control fs-6" id="secondord" name="secondord" value="<?=$secondord?>" style="width:80%;" autocomplete="off" onkeydown="if(event.keyCode == 13) { phonebookBtn('secondord'); }"  > &nbsp;                
                                        <button type="button" class="btn btn-dark-outline btn-sm " onclick="phonebookBtn('secondord');">  <i class="bi bi-gear"></i> </button>
                                    </div>
                                        
                                    </td>
                                    <td class="text-center fs-6 fw-bold" style="width:150px;">입고 단가</td>
                                    <td class="text-center">                                        
                                        <input type="text" class="form-control fs-6" id="unitprice" name="unitprice" value="<?=$unitprice ?>"  autocomplete="off" onkeyup="inputNumberFormat(this)" >
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center fs-6 fw-bold" style="width:150px;">수량</td>
                                    <td class="text-center">
                                        <input type="text" class="form-control fs-6" id="surang" name="surang" style="width:60px;" value="<?=$surang?>"  autocomplete="off" onkeyup="inputNumberFormat(this)"  >
                                    </td>
                                    <td class="text-center fs-6 fw-bold" style="width:150px;">로트번호</td>
                                    <td class="text-center">
                                        <div class="specialinputWrap">
                                            <input type="text" class="form-control inputlot fs-6" id="lotnum" name="lotnum" value="<?=$lotnum?>"  autocomplete="off">
                                            <button class="btnClear_lot"></button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center fs-6 fw-bold" style="width:150px;">비고</td>
                                    <td class="text-center" colspan="3">
                                        <input type="text" class="form-control fs-6" id="comment" name="comment" value="<?=$comment?>" autocomplete="off" >
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    let isSaving = false;
	
    $("#saveBtn").on("click", function() {
        if (isSaving) return;
        isSaving = true;
        
        let continuousRegistration = $("#continuous_registration").is(':checked');
        
        // AJAX 요청을 보냄
        $.ajax({
            url: "process.php",
            type: "post",
            data: {
                mode: $("#mode").val(),
                num: $("#num").val(),
                update_log: $("#update_log").val(),
                registedate: $("#registedate").val(),
                inoutdate: $("#inoutdate").val(),
                secondord: $("#secondord").val(),
                unitprice: $("#unitprice").val(),
                surang: $("#surang").val(),
                lotnum: $("#lotnum").val(),
                comment: $("#comment").val()
            },
            dataType: "json",
            success: function(response) {
                Toastify({
                    text: "저장 완료",
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "center",
                    backgroundColor: "#4fbe87",
                }).showToast();

                if (!continuousRegistration) {
                    $("#myModal").hide();
                    location.reload();
                } else {
                    // 입력 필드 초기화
                    $("#inout_item_code").val('');
                    $("#item_name").val('');
                    $("#secondord").val('');
                    $("#unitprice").val('');
                    $("#surang").val('');
                    $("#lotnum").val('');
                    $("#comment").val('');
                    isSaving = false;
                }
            },
            error: function(jqxhr, status, error) {
                console.log("AJAX Error: ", status, error);
                isSaving = false;
            }
        });
    });

    $("#closeBtn").on("click", function() {
        $("#myModal").hide();
    });
});
</script>
