<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

$num = isset($_POST['num']) ? $_POST['num'] : '';

$tablename = 'material_reg';
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/mydb.php");
$pdo = db_connect();

if ($num) {
    try {
        $sql = "SELECT * FROM ". $DB . "." . $tablename . " WHERE num=?";
        $stmh = $pdo->prepare($sql);
        $stmh->bindValue(1, $num, PDO::PARAM_INT);      
        $stmh->execute();
        $row = $stmh->fetch(PDO::FETCH_ASSOC);

        // Load the form for editing existing stock entry
        include '_row.php';  // Existing row data for editing
    } catch (PDOException $Exception) {
        echo "오류: ".$Exception->getMessage();
        exit;
    }
} else {
    echo "데이터가 없습니다.";  // Handle error if no num is passed
}

$title_message = '입고 수정';  // Update only, no insert option

?>

<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-center">
        <div class="card justify-content-center">
            <div class="card-header text-center">
                <span class="text-center fs-5 me-3"><?=$title_message?></span> ( <?=$row['num']?> )
				<input type="hidden" id="mode" name="mode" value="<?= isset($mode) ? $mode : '' ?>">
				<input type="hidden" id="num" name="num" value="<?= isset($num) ? $num : '' ?>">
            </div>
            <div class="card-body">
                <div class="row justify-content-center text-center">
                    <div class="d-flex align-items-center justify-content-center m-2">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td class="text-center fs-6 fw-bold" style="width:150px;">등록일자</td>
                                    <td class="text-center" style="width:200px;">
                                        <input type="date" class="form-control fs-6" id="registedate" name="registedate" value="<?=$row['registedate']?>" readonly>
                                    </td>
                                    <td class="text-center fs-6 fw-bold" style="width:150px;">입고 일자</td>
                                    <td class="text-center" style="width:500px;">
                                        <input type="date" class="form-control fs-6" id="inoutdate" name="inoutdate" value="<?=$row['inoutdate']?>">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center fs-6 fw-bold" style="width:150px;">품목코드</td>
                                    <td class="text-center">
                                        <div class="specialinputWrap">
                                            <input type="text" class="form-control inputcode fs-6" id="inout_item_code" name="inout_item_code" value="<?=$row['inout_item_code']?>" readonly>
                                        </div>
                                    </td>

                                    <td class="text-center fs-6 fw-bold" style="width:150px;">품목명</td>
                                    <td class="text-center" style="width:400px;">
                                        <div class="specialinputWrap">
                                        <input type="text" class="form-control inputitemname fs-6" id="item_name" name="item_name" value="<?=$row['item_name']?>" readonly>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center fs-6 fw-bold" style="width:150px;">매입처</td>
                                    <td class="text-center" style="width:400px;">
                                       <input class="form-control fs-6" id="secondord" name="secondord" value="<?=$row['secondord']?>" autocomplete="off">
                                    </td>
                                    <td class="text-center fs-6 fw-bold" style="width:150px;">입고 단가</td>
                                    <td class="text-center">                                        
                                        <input type="text" class="form-control fs-6" id="unitprice" name="unitprice" value="<?=$row['unitprice']?>" autocomplete="off" onkeyup="inputNumberFormat(this)">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center fs-6 fw-bold" style="width:150px;">수량</td>
                                    <td class="text-center">
                                        <input type="text" class="form-control fs-6" id="surang" name="surang" value="<?=$row['surang']?>" autocomplete="off" onkeyup="inputNumberFormat(this)">
                                    </td>
                                    <td class="text-center fs-6 fw-bold" style="width:150px;">로트번호</td>
                                    <td class="text-center">
                                        <div class="specialinputWrap">
                                            <input type="text" class="form-control inputlot fs-6" id="lotnum" name="lotnum" value="<?=$row['lotnum']?>" autocomplete="off">
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-center fs-6 fw-bold" style="width:150px;">비고</td>
                                    <td class="text-center" colspan="3">
                                        <input type="text" class="form-control fs-6" id="comment" name="comment" value="<?=$row['comment']?>" autocomplete="off">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

